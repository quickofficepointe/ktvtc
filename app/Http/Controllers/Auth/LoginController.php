<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Services\SmsService;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the login username to be used by the controller.
     */
    public function username()
    {
        $login = request()->input('login');

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        return 'username';
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        $loginField = $this->username();
        $credentials = [
            $loginField => $request->input('login'),
            'password'  => $request->input('password'),
        ];

        return $this->guard()->attempt(
            $credentials,
            $request->boolean('remember')
        );
    }

    /**
     * Send the response after authentication was attempted.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Check if this is a student number login attempt
        // Current number first. An old (legacy) code only counts if exactly one live
        // student has it: some codes were reused for different people.
        $login = trim((string) $request->input('login'));
        $student = Student::whereNull('merged_into_id')->where('student_number', $login)->first();
        if (!$student && $login !== '') {
            $byLegacy = Student::whereNull('merged_into_id')->where('legacy_student_code', $login)->limit(2)->get();
            $student = $byLegacy->count() === 1 ? $byLegacy->first() : null;
        }

        if ($student) {
            // Try to auto-create user account for student
            [$user] = app(\App\Services\Students\StudentAccountService::class)->ensureAccount($student);

            if ($user) {
                $loginField = $this->username();
                $credentials = [
                    $loginField => $user->username ?? $user->email,
                    'password'  => $request->input('password'),
                ];

                if ($this->guard()->attempt($credentials, $request->boolean('remember'))) {
                    return $this->sendLoginResponse($request);
                }
            }
        }

        return redirect()->back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'These credentials do not match our records.',
            ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Never let audit logging block a login (e.g. system_logs not migrated yet).
        rescue(fn () => \App\Models\SystemLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => $user->name . ' logged in',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]), null, false);

        // Force password change if student is still on default password
        if ($user->student_id) {
            $student = $user->student;
            if ($student) {
                $studentNumber = $student->student_number ?? $student->legacy_student_code;
                $defaultPassword = strtoupper($studentNumber ?? '');

                if ($studentNumber && Hash::check($defaultPassword, $user->password)) {
                    return redirect()->route('student.force-password-change');
                }
            }
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the needed authorization credentials from the request.
     */
    protected function credentials(Request $request)
    {
        $loginField = $this->username();
        return [
            $loginField => $request->input('login'),
            'password'  => $request->input('password'),
        ];
    }
}
