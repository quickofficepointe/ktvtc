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
        $student = Student::where('student_number', $request->input('login'))
                         ->orWhere('legacy_student_code', $request->input('login'))
                         ->first();

        if ($student) {
            // Try to auto-create user account for student
            $user = $this->createUserFromStudent($student);

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
     * Create user account from student record.
     *
     * ✅ FIXED:
     *   - 'phone' → 'phone_number' (was silently dropped)
     *   - Added 'is_approved' => true (was missing)
     *   - Added 'is_verified' => true
     *   - Collision-safe email generation
     *   - Wrapped in try/catch so failures don't break login
     *   - Sends welcome SMS with initial password
     */
    private function createUserFromStudent(Student $student): ?User
    {
        // Return existing user if already linked
        $existingUser = User::where('student_id', $student->id)->first();
        if ($existingUser) {
            return $existingUser;
        }

        $studentNumber = $student->student_number ?? $student->legacy_student_code;
        if (!$studentNumber) {
            Log::warning("Cannot create user — student {$student->id} has no student number");
            return null;
        }

        $defaultPassword = strtoupper($studentNumber);

        // Collision-safe email
        $email = $student->email;
        if (!$email || User::where('email', $email)->exists()) {
            $base  = strtolower(preg_replace('/[^a-z0-9]/i', '', $studentNumber));
            $email = "{$base}@student.ktvtc.ac.ke";

            if (User::where('email', $email)->exists()) {
                $email = "{$base}+{$student->id}@student.ktvtc.ac.ke";
            }
        }

        $name = $student->full_name
              ?? trim($student->first_name . ' ' . $student->last_name);

        try {
            $user = User::create([
                'student_id'        => $student->id,
                'name'              => $name,
                'username'          => $studentNumber,
                'email'             => $email,
                'phone_number'      => $student->phone,   // ✅ FIXED: was 'phone'
                'bio'               => $student->remarks ?? 'Student account automatically created',
                'role'              => User::ROLE_STUDENT,
                'is_verified'       => true,              // ✅ FIXED
                'is_active'         => true,
                'is_approved'       => true,              // ✅ FIXED: was missing
                'password'          => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);

            Log::info('User auto-created from login attempt', [
                'student_id' => $student->id,
                'user_id'    => $user->id,
                'username'   => $user->username,
            ]);

            // Welcome SMS — non-fatal if it fails
            $this->sendWelcomeSms($student, $defaultPassword);

            return $user;

        } catch (\Exception $e) {
            Log::error("Failed to create user for student {$student->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send welcome SMS with initial credentials.
     */
    private function sendWelcomeSms(Student $student, string $password): void
    {
        if (empty($student->phone)) {
            Log::warning("Welcome SMS skipped — no phone for student {$student->id}");
            return;
        }

        try {
            $fullName = $student->full_name
                      ?? trim($student->first_name . ' ' . $student->last_name);

            $message  = "Dear {$fullName},\n\n";
            $message .= "Your KTVTC student account has been created.\n";
            $message .= "Student Number: {$student->student_number}\n";
            $message .= "Password: {$password}\n\n";
            $message .= "Login: " . url('/login') . "\n";
            $message .= "Please change your password after first login.\n\n";
            $message .= "KTVTC Team";

            $sms = app(SmsService::class);
            $result = $sms->sendSingleSms($student->phone, $message);

            if ($result['success'] ?? false) {
                Log::info("Welcome SMS sent to student {$student->id}");
            } else {
                Log::error("Welcome SMS failed for student {$student->id}: "
                    . ($result['message'] ?? 'unknown'));
            }
        } catch (\Exception $e) {
            Log::error("Welcome SMS exception for student {$student->id}: " . $e->getMessage());
        }
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
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

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

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
