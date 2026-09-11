<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsurePreOrderAuthenticated;
use App\Models\HighSchoolStudent;
use App\Models\PreOrderAccount;
use App\Models\Student;
use App\Models\User;
use App\Services\PreOrderSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PreOrderAuthController extends Controller
{
    protected PreOrderSmsService $sms;

    public function __construct(PreOrderSmsService $sms)
    {
        $this->sms = $sms;
    }

    // ─── LOGIN ───────────────────────────────────────────────

    public function showLogin()
    {
        return view('preorder.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:150',
            'pin' => 'required|digits:4',
        ]);

        $identifier = trim($request->input('identifier'));
        $account = PreOrderAccount::where('identifier', $identifier)->first();

        if (!$account) {
            return redirect()
                ->route('pre-order.setup', ['identifier' => $identifier])
                ->with('info', 'First time here? Set up your 4-digit PIN.');
        }

        if (!$account->is_active) {
            throw ValidationException::withMessages([
                'identifier' => 'This account is inactive. Contact the cafeteria.',
            ]);
        }

        if (!Hash::check($request->input('pin'), $account->pin_hash)) {
            throw ValidationException::withMessages([
                'pin' => 'Incorrect PIN. Try again or reset it via SMS.',
            ]);
        }

        $this->establishSession($request, $account);

        return redirect()
            ->intended(route('pre-order.home'))
            ->with(
                'success',
                'Welcome back, ' . ($account->display_name ?? 'there') . '!'
            );
    }

    // ─── SETUP — STEP 1 ──────────────────────────────────────

    public function showSetup(Request $request)
    {
        $identifier = $request->input('identifier');
        return view('preorder.setup', compact('identifier'));
    }

    public function initiateSetup(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:150',
        ]);

        $identifier = trim($request->input('identifier'));

        if (PreOrderAccount::where('identifier', $identifier)->exists()) {
            return redirect()
                ->route('pre-order.login')
                ->with('info', 'You already have an account. Log in with your PIN.')
                ->withInput(['identifier' => $identifier]);
        }

        $detected = $this->detectIdentifier($identifier);

        if (!$detected) {
            throw ValidationException::withMessages([
                'identifier' =>
                    'We could not find you in the system. Check the number/email and try again.',
            ]);
        }

        // HIGH SCHOOL STUDENTS — SKIP SMS VERIFICATION
        if ($detected['user_type'] === 'high_school_student') {
            $request->session()->put('preorder_setup_pending', [
                'identifier'        => $identifier,
                'user_type'         => $detected['user_type'],
                'user_id'           => $detected['user_id'],
                'display_name'      => $detected['display_name'],
                'phone'             => $detected['phone'],
                'code'              => null,
                'sent_at'           => now()->timestamp,
                'skip_verification' => true,
            ]);

            return redirect()
                ->route('pre-order.setup.verify')
                ->with('info', 'Welcome! Just set your 4-digit PIN below.');
        }

        // STAFF + COLLEGE — SMS VERIFICATION
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        if (!$detected['phone']) {
            $request->session()->put('preorder_setup_pending', [
                'identifier'        => $identifier,
                'user_type'         => $detected['user_type'],
                'user_id'           => $detected['user_id'],
                'display_name'      => $detected['display_name'],
                'phone'             => null,
                'code'              => $code,
                'sent_at'           => now()->timestamp,
                'skip_verification' => false,
            ]);

            return redirect()
                ->route('pre-order.setup.phone')
                ->with('warning',
                    "We don't have a phone number on file. Please enter one to receive your verification code.");
        }

        $smsResult = $this->sms->sendVerificationCode(
            $detected['phone'],
            $code,
            $detected['first_name'] ?? ''
        );

        if (!($smsResult['success'] ?? false)) {
            throw ValidationException::withMessages([
                'identifier' =>
                    'Could not send SMS to your phone on file. Contact the cafeteria.',
            ]);
        }

        $request->session()->put('preorder_setup_pending', [
            'identifier'        => $identifier,
            'user_type'         => $detected['user_type'],
            'user_id'           => $detected['user_id'],
            'display_name'      => $detected['display_name'],
            'phone'             => $detected['phone'],
            'code'              => $code,
            'sent_at'           => now()->timestamp,
            'skip_verification' => false,
        ]);

        $maskedPhone = $this->maskPhone($detected['phone']);

        return redirect()
            ->route('pre-order.setup.verify')
            ->with('success', "Verification code sent to {$maskedPhone}.");
    }

    // ─── SETUP — STEP 2 ──────────────────────────────────────

    public function showSetupPhone()
    {
        if (!session()->has('preorder_setup_pending')) {
            return redirect()->route('pre-order.setup')
                ->with('error', 'Session expired. Please start over.');
        }

        return view('preorder.setup-phone');
    }

    public function submitSetupPhone(Request $request)
    {
        $pending = session('preorder_setup_pending');

        if (!$pending) {
            return redirect()->route('pre-order.setup')
                ->with('error', 'Session expired. Please start over.');
        }

        $request->validate([
            'phone' => ['required', 'regex:/^(?:\+?254|0)[17]\d{8}$/'],
        ]);

        $phone = $this->normalizePhone($request->input('phone'));
        $code  = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $smsResult = $this->sms->sendVerificationCode($phone, $code);

        if (!($smsResult['success'] ?? false)) {
            throw ValidationException::withMessages([
                'phone' => 'Failed to send SMS. Check the number and try again.',
            ]);
        }

        $pending['phone']             = $phone;
        $pending['code']              = $code;
        $pending['sent_at']           = now()->timestamp;
        $pending['skip_verification'] = false;

        session()->put('preorder_setup_pending', $pending);

        $maskedPhone = $this->maskPhone($phone);

        return redirect()
            ->route('pre-order.setup.verify')
            ->with('success', "Verification code sent to {$maskedPhone}.");
    }

    // ─── SETUP — STEP 3 ──────────────────────────────────────

    public function showSetupVerify()
    {
        if (!session()->has('preorder_setup_pending')) {
            return redirect()->route('pre-order.setup')
                ->with('error', 'Session expired. Please start over.');
        }

        $pending     = session('preorder_setup_pending');
        $maskedPhone = $this->maskPhone($pending['phone'] ?? '');

        return view('preorder.setup-verify', compact('maskedPhone'));
    }

    public function submitSetupVerify(Request $request)
    {
        $pending = session('preorder_setup_pending');

        if (!$pending) {
            return redirect()->route('pre-order.setup')
                ->with('error', 'Session expired. Please start over.');
        }

        $skipVerification = $pending['skip_verification'] ?? false;

        if (!$skipVerification && now()->timestamp - ($pending['sent_at'] ?? 0) > 600) {
            session()->forget('preorder_setup_pending');
            return redirect()->route('pre-order.setup')
                ->with('error', 'Verification code expired. Please start over.');
        }

        $rules = ['pin' => 'required|digits:4|confirmed'];
        if (!$skipVerification) {
            $rules['code'] = 'required|digits:6';
        }
        $request->validate($rules);

        if (!$skipVerification) {
            if ($request->input('code') !== ($pending['code'] ?? '')) {
                throw ValidationException::withMessages([
                    'code' => 'Incorrect verification code.',
                ]);
            }
        }

        // Prevent duplicate account creation
        $existingAccount = PreOrderAccount::where('identifier', $pending['identifier'])->first();
        if ($existingAccount) {
            session()->forget('preorder_setup_pending');
            return redirect()
                ->route('pre-order.login')
                ->with('info', 'You already have an account. Please log in with your PIN.');
        }

        $account = PreOrderAccount::create([
            'user_type'    => $pending['user_type'],
            'user_id'      => $pending['user_id'],
            'identifier'   => $pending['identifier'],
            'display_name' => $pending['display_name'],
            'phone'        => $pending['phone'] ?? null,
            'pin_hash'     => Hash::make($request->input('pin')),
            'pin_set_at'   => now(),
            'is_verified'  => true,
            'is_active'    => true,
        ]);

        session()->forget('preorder_setup_pending');

        $this->establishSession($request, $account);

        return redirect()
            ->route('pre-order.home')
            ->with('success', 'PIN set! Welcome to the Pre-Order portal.');
    }

    // ─── FORGOT PIN ─────────────────────────────────────────

    public function showForgot()
    {
        return view('preorder.forgot');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:150',
        ]);

        $identifier = trim($request->input('identifier'));
        $account    = PreOrderAccount::where('identifier', $identifier)->first();

        if (!$account) {
            return redirect()
                ->route('pre-order.forgot')
                ->with('info', 'If this identifier exists, a reset code has been sent to the phone on file.');
        }

        if (!$account->hasPhone()) {
            throw ValidationException::withMessages([
                'identifier' => 'No phone number on file. Contact the cafeteria to reset your PIN.',
            ]);
        }

        $code      = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $smsResult = $this->sms->sendPinResetCode($account->phone, $code, '');

        if (!($smsResult['success'] ?? false)) {
            throw ValidationException::withMessages([
                'identifier' => 'Could not send SMS. Please try again.',
            ]);
        }

        $request->session()->put('preorder_reset', [
            'account_id' => $account->id,
            'code'       => $code,
            'sent_at'    => now()->timestamp,
            'attempts'   => 0,
        ]);

        return redirect()
            ->route('pre-order.reset')
            ->with('success', 'Reset code sent to ' . $this->maskPhone($account->phone));
    }

    public function showReset()
    {
        if (!session()->has('preorder_reset')) {
            return redirect()->route('pre-order.forgot');
        }

        return view('preorder.reset');
    }

    public function submitReset(Request $request)
    {
        $reset = session('preorder_reset');

        if (!$reset) {
            return redirect()->route('pre-order.forgot');
        }

        if (now()->timestamp - ($reset['sent_at'] ?? 0) > 600) {
            session()->forget('preorder_reset');
            return redirect()->route('pre-order.forgot')
                ->with('error', 'Reset code expired. Please request a new one.');
        }

        if (($reset['attempts'] ?? 0) >= 5) {
            session()->forget('preorder_reset');
            return redirect()->route('pre-order.forgot')
                ->with('error', 'Too many attempts. Please request a new code.');
        }

        $request->validate([
            'code' => 'required|digits:6',
            'pin'  => 'required|digits:4|confirmed',
        ]);

        if ($request->input('code') !== ($reset['code'] ?? '')) {
            $reset['attempts']++;
            session()->put('preorder_reset', $reset);

            throw ValidationException::withMessages([
                'code' => 'Incorrect reset code.',
            ]);
        }

        $account = PreOrderAccount::find($reset['account_id']);
        if (!$account) {
            session()->forget('preorder_reset');
            return redirect()
                ->route('pre-order.login')
                ->with('error', 'Account not found.');
        }

        $account->update([
            'pin_hash'   => Hash::make($request->input('pin')),
            'pin_set_at' => now(),
        ]);

        session()->forget('preorder_reset');

        return redirect()
            ->route('pre-order.login')
            ->with('success', 'PIN reset successfully. Log in with your new PIN.');
    }

    // ─── LOGOUT ─────────────────────────────────────────────

    public function logout(Request $request)
    {
        $request->session()->forget(EnsurePreOrderAuthenticated::SESSION_KEY);
        $request->session()->regenerate();

        return redirect()
            ->route('pre-order.login')
            ->with('success', 'Logged out.');
    }

    // ─── IDENTIFIER DETECTION ───────────────────────────────

    protected function detectIdentifier(string $identifier): ?array
    {
        if (str_contains($identifier, '@')) {
            $email = strtolower($identifier);

            $allowedDomains = ['@ktvtc.ac.ke', '@kenswed.org'];
            $domainOk = false;
            foreach ($allowedDomains as $domain) {
                if (str_ends_with($email, $domain)) {
                    $domainOk = true;
                    break;
                }
            }

            if (!$domainOk) return null;

            $user = User::where('email', $email)->first();
            if (!$user) return null;

            return [
                'user_type'    => 'staff',
                'user_id'      => $user->id,
                'display_name' => $user->name,
                'first_name'   => explode(' ', $user->name)[0] ?? '',
                'phone'        => $user->phone_number ?: null,
            ];
        }

        $student = Student::where('student_number', $identifier)->first();
        if ($student) {
            return [
                'user_type'    => 'college_student',
                'user_id'      => $student->id,
                'display_name' => $student->full_name,
                'first_name'   => $student->first_name ?? '',
                'phone'        => $student->phone ?: null,
            ];
        }

        $hs = HighSchoolStudent::where('admission_number', $identifier)->first();
        if ($hs) {
            return [
                'user_type'    => 'high_school_student',
                'user_id'      => $hs->id,
                'display_name' => $hs->full_name,
                'first_name'   => explode(' ', $hs->full_name)[0] ?? '',
                'phone'        => $hs->parent_phone ?: null,
            ];
        }

        return null;
    }

    // ─── SESSION ESTABLISHMENT ──────────────────────────────

    /**
     * Establish a logged-in session for a pre-order account.
     * Named `establishSession` instead of `logIn` to avoid a
     * case-insensitive collision with the public `login()` method.
     */
    protected function establishSession(Request $request, PreOrderAccount $account): void
    {
        $request->session()->regenerate();
        $request->session()->put(EnsurePreOrderAuthenticated::SESSION_KEY, $account->id);

        $account->update([
            'last_login_at' => now(),
            'phone'         => $account->phone ?: $this->phoneFromOwner($account),
        ]);

        Log::info('Pre-order user logged in', [
            'account_id' => $account->id,
            'user_type'  => $account->user_type,
            'identifier' => $account->identifier,
        ]);
    }

    protected function phoneFromOwner(PreOrderAccount $account): ?string
    {
        return match ($account->user_type) {
            'staff'               => User::find($account->user_id)?->phone_number,
            'college_student'     => Student::find($account->user_id)?->phone,
            'high_school_student' => HighSchoolStudent::find($account->user_id)?->parent_phone,
            default               => null,
        };
    }

    protected function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);

        if (str_starts_with($clean, '254')) return $clean;
        if (str_starts_with($clean, '0'))   return '254' . substr($clean, 1);
        if (strlen($clean) === 9)           return '254' . $clean;

        return $clean;
    }

    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) return $phone;

        return substr($phone, 0, 6)
             . str_repeat('*', max(0, strlen($phone) - 8))
             . substr($phone, -2);
    }
}