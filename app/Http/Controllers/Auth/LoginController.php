<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;

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

        // Check if login is email format
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
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        $loginField = $this->username();
        $credentials = [$loginField => $request->input('login'), 'password' => $request->input('password')];

        return $this->guard()->attempt(
            $credentials, $request->boolean('remember')
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
                // Attempt login again with auto-created account
                $loginField = $this->username();
                $credentials = [$loginField => $user->username ?? $user->email, 'password' => $request->input('password')];

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
     * Create user account from student record
     */
    private function createUserFromStudent(Student $student): ?User
    {
        // Check if user already exists
        $existingUser = User::where('student_id', $student->id)->first();
        if ($existingUser) {
            return $existingUser;
        }

        // Get student number
        $studentNumber = $student->student_number ?? $student->legacy_student_code;
        if (!$studentNumber) {
            return null;
        }

        // Generate default password (student number in uppercase)
        $defaultPassword = strtoupper($studentNumber);

        // Get email from student or create default
        $email = $student->email ?? strtolower($studentNumber) . '@student.ktvtc.ac.ke';

        // Get full name
        $name = $student->full_name ?? trim($student->first_name . ' ' . $student->last_name);

        // Create user
        return User::create([
            'student_id' => $student->id,
            'name' => $name,
            'username' => $studentNumber,
            'email' => $email,
            'phone' => $student->phone,
            'bio' => $student->remarks ?? 'Student account automatically created',
            'role' => 5, // Student role
            'is_verified' => true,
            'is_active' => true,
            'password' => Hash::make($defaultPassword),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if student is using default password
        if ($user->student_id) {
            $student = $user->student;
            if ($student) {
                $studentNumber = $student->student_number ?? $student->legacy_student_code;
                $defaultPassword = strtoupper($studentNumber ?? '');

                if ($studentNumber && Hash::check($defaultPassword, $user->password)) {
                    // Force password change for default password
                    return redirect()->route('student.force-password-change');
                }
            }
        }

        // Update last login info
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
            'password' => $request->input('password'),
        ];
    }
}
