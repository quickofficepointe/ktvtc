<?php

namespace App\Services\Students;

use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Student portal accounts: one place that creates them, reports their status,
 * and texts the login details.
 *
 * Username = the student's number; first password = the number in capitals, which
 * the student must change on first login (LoginController checks for it).
 */
class StudentAccountService
{
    public const STATE_NONE = 'none';        // no account yet
    public const STATE_NEVER = 'never';      // account exists, never signed in
    public const STATE_ACTIVE = 'active';    // has signed in

    public function __construct(private SmsService $sms)
    {
    }

    /** ['state' => none|never|active, 'user' => ?User, 'lastLogin' => ?Carbon, 'sentAt' => ?Carbon] */
    public function status(Student $student, ?User $user = null): array
    {
        $user ??= User::where('student_id', $student->id)->first();


        return [
            'state' => !$user ? self::STATE_NONE : ($user->last_login_at ? self::STATE_ACTIVE : self::STATE_NEVER),
            'user' => $user,
            'lastLogin' => $user?->last_login_at,
            'sentAt' => $user?->login_details_sent_at,
        ];
    }

    /**
     * The student's account, created if missing. Returns [User|null, bool created].
     * Null when the student has no number to sign in with yet.
     */
    public function ensureAccount(Student $student): array
    {
        $user = User::withTrashed()->where('student_id', $student->id)->first();
        if ($user?->trashed()) {
            // A deleted login still holds the student's link: bring it back rather than clash
            $user->restore();
        }
        if ($user) {
            // Keep the username in step with the student's current number
            if ($student->student_number && $user->username !== $student->student_number
                && !User::where('username', $student->student_number)->whereKeyNot($user->id)->exists()) {
                $user->forceFill(['username' => $student->student_number])->save();
            }
            return [$user, false];
        }

        $number = $student->student_number ?: $student->legacy_student_code;
        if (!$number) {
            return [null, false];
        }

        $user = User::create([
            'student_id' => $student->id,
            'name' => trim($student->first_name . ' ' . $student->last_name),
            'username' => $number,
            'email' => $this->freeEmail($student, $number),
            'phone_number' => $student->phone,
            'bio' => 'Student account',
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
            'is_approved' => true,
            'password' => Hash::make($this->defaultPassword($student)),
        ]);
        // Not fillable; without it the 'verified' middleware locks the student out
        $user->markEmailAsVerified();

        Log::info('Student account created', ['student_id' => $student->id, 'user_id' => $user->id]);

        return [$user, true];
    }

    /**
     * Create the account if needed, reset the password to the student number and
     * text the details. Refused once the student has signed in (they have their own
     * password by then; use a password reset instead).
     *
     * @return array{ok: bool, message: string}
     */
    public function sendLoginDetails(Student $student, bool $evenIfActive = false): array
    {
        [$user] = $this->ensureAccount($student);
        if (!$user) {
            return ['ok' => false, 'message' => 'The student has no student number yet; enroll them first.'];
        }
        if ($user->last_login_at && !$evenIfActive) {
            return ['ok' => false, 'message' => "{$user->name} already signs in (last on {$user->last_login_at->format('d M Y')}). Use reset password instead."];
        }
        if (!$student->phone) {
            return ['ok' => false, 'message' => "{$user->name} has no phone number to text the details to."];
        }

        $password = $this->defaultPassword($student);
        $user->forceFill(['password' => Hash::make($password)])->save();

        $message = "Dear {$student->first_name}, your KTVTC student portal is ready.\n"
            . "Login: " . url('/login') . "\n"
            . "Student number: {$student->student_number}\n"
            . "Password: {$password}\n"
            . "You will choose your own password when you first sign in.";

        $result = rescue(fn () => $this->sms->sendSingleSms($student->phone, $message), ['success' => false, 'message' => 'SMS error'], false);
        if (!($result['success'] ?? false)) {
            Log::warning('Login details SMS failed', ['student_id' => $student->id, 'error' => $result['message'] ?? null]);
            return ['ok' => false, 'message' => "The SMS to {$student->phone} failed: " . ($result['message'] ?? 'unknown error') . '. The account is ready; try again later.'];
        }

        $user->forceFill(['login_details_sent_at' => now()])->save();

        return ['ok' => true, 'message' => "Login details sent to {$student->phone}."];
    }

    /**
     * After an enrollment: make sure the student can sign in, and text the details
     * once (not again if they were already sent or the student already signs in).
     * Runs after the enrollment is committed so a rolled-back enrollment sends nothing.
     */
    public function afterEnrollment(Student $student): void
    {
        DB::afterCommit(function () use ($student) {
            // A login problem must never undo or fail an enrollment that is already saved
            try {
                $student = $student->fresh();
                [$user] = $this->ensureAccount($student);
                if (!$user || $user->last_login_at) {
                    return;
                }
                // Text once; again only if the details they were sent no longer work
                // (e.g. texted with the running number before the course number was given)
                $stillValid = $user->login_details_sent_at && Hash::check($this->defaultPassword($student), $user->password);
                if ($stillValid) {
                    return;
                }
                $result = $this->sendLoginDetails($student);
                if (!$result['ok']) {
                    Log::info('Login details not sent after enrollment', ['student_id' => $student->id, 'reason' => $result['message']]);
                }
            } catch (\Throwable $e) {
                Log::error('Student account setup after enrollment failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            }
        });
    }

    public function defaultPassword(Student $student): string
    {
        return strtoupper((string) ($student->student_number ?: $student->legacy_student_code));
    }

    private function freeEmail(Student $student, string $number): string
    {
        if ($student->email && !User::where('email', $student->email)->exists()) {
            return $student->email;
        }
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $number));
        $email = "{$base}@student.ktvtc.ac.ke";

        return User::where('email', $email)->exists() ? "{$base}+{$student->id}@student.ktvtc.ac.ke" : $email;
    }
}
