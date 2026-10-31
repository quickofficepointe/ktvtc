<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Application;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    // ============================================================
    // DASHBOARD
    // ============================================================

    /**
     * Admissions and academics overview (campus staff see their own campus).
     */
    public function dashboard(\App\Services\Dashboards\RoleDashboards $dashboards)
    {
        $user = auth()->user();
        $campusId = $user->role != 2 ? $user->campus_id : null;

        return view('ktvtc.admin.dashboard', $dashboards->admin($campusId));
    }


    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $sortField     = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 20);
        $users   = $query->paginate($perPage)->withQueryString();

        $studentUserIds = $users->getCollection()
            ->where('role', 5)
            ->pluck('id')
            ->all();

        if (!empty($studentUserIds)) {
            $userStudentMap = User::whereIn('id', $studentUserIds)
                ->pluck('student_id', 'id');

            $studentIds = $userStudentMap->filter()->values()->all();

            $studentsByEmail = [];
            if (empty($studentIds)) {
                $emails = $users->getCollection()
                    ->where('role', 5)
                    ->pluck('email')
                    ->filter()
                    ->all();

                $studentsByEmail = Student::whereIn('email', $emails)
                    ->get()
                    ->keyBy(fn ($s) => strtolower($s->email));
            }

            $students = Student::with(['enrollments.course'])
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');

            foreach ($users as $user) {
                if ($user->role != 5) {
                    continue;
                }

                $student = null;

                if ($user->student_id && isset($students[$user->student_id])) {
                    $student = $students[$user->student_id];
                } elseif (!empty($studentsByEmail) && $user->email) {
                    $student = $studentsByEmail[strtolower($user->email)] ?? null;
                }

                $user->student_profile = $this->buildStudentProfile($student);
            }
        }

        $totalUsers   = User::count();
        $activeUsers  = User::where('is_approved', true)->count();
        $pendingUsers = User::where('is_approved', false)->count();
        $adminUsers   = User::where('role', 2)->count();

        // Admin can create every staff role except super admin
        $roleNames = \Illuminate\Support\Arr::except(User::getRoles(), [User::ROLE_SUPER_ADMIN]);

        return view('ktvtc.admin.users.index', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'pendingUsers',
            'adminUsers',
            'perPage',
            'roleNames'
        ));
    }

    protected function buildStudentProfile(?Student $student): array
    {
        if (!$student) {
            return [
                'exists'            => false,
                'student_number'    => null,
                'course_name'       => null,
                'course_code'       => null,
                'enrollment_status' => 'not_enrolled',
                'enrollment_label'  => 'Not enrolled',
            ];
        }

        $enrollments = $student->relationLoaded('enrollments')
            ? $student->enrollments
            : $student->enrollments()->with('course')->get();

        $finished = $enrollments->first(fn ($e) =>
            in_array($e->status, ['completed', 'graduated'])
        );

        $active = $enrollments->first(fn ($e) =>
            in_array($e->status, ['active', 'in_progress'])
        );

        if ($finished) {
            $status = 'finished';
            $label  = 'Finished';
            $course = $finished->course;
        } elseif ($active) {
            $status = 'enrolled';
            $label  = 'Enrolled';
            $course = $active->course;
        } else {
            $status = 'not_enrolled';
            $label  = 'Not enrolled';
            $course = $enrollments->first()?->course;
        }

        return [
            'exists'            => true,
            'student_number'    => $student->student_number,
            'course_name'       => $course->name ?? $student->course_name ?? null,
            'course_code'       => $course->code ?? $student->course_code ?? null,
            'enrollment_status' => $status,
            'enrollment_label'  => $label,
        ];
    }

    // ============================================================
    // USERS — SHOW / EDIT / UPDATE
    // ============================================================

    public function showUser($id)
    {
        $user = User::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            $payload = $user->toArray();

            if ($user->role == 5) {
                $student = null;

                if ($user->student_id) {
                    $student = Student::with(['enrollments.course'])->find($user->student_id);
                }

                if (!$student && $user->email) {
                    $student = Student::with(['enrollments.course'])
                        ->where('email', $user->email)
                        ->first();
                }

                $payload['student_profile'] = $this->buildStudentProfile($student);
            }

            return response()->json($payload);
        }

        // Details and editing live in modals on the users list.
        return redirect()->route('admin.users.index', ['search' => $user->email]);
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($user);
        }

        // Details and editing live in modals on the users list.
        return redirect()->route('admin.users.index', ['search' => $user->email]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|string',
            'role'         => 'required|integer|min:1|max:11',
            'bio'          => 'nullable|string',
        ]);

        $user->update([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'role'         => $request->role,
            'bio'          => $request->bio,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_approved = $request->is_approved;
        $user->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User status updated successfully.');
    }

    // ============================================================
    // USERS — CREATE / BULK / APPROVE
    // ============================================================

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'phone_number' => 'nullable|string',
            // Admins can't create Super Admins (role 0)
            'role'         => 'required|integer|min:1|max:11',
            'bio'          => 'nullable|string',
        ]);

        // Every account used to get the same password ("defaultpassword"); give each its own
        $password = \Illuminate\Support\Str::password(10, symbols: false);

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'role'         => $request->role,
            'bio'          => $request->bio,
            'password'     => bcrypt($password),
            'is_approved'  => true,
        ]);
        // Staff-created accounts don't go through email verification
        $user->markEmailAsVerified();

        return redirect()->route('admin.users.index')
            ->with('success', "User created. Temporary password for {$user->email}: {$password} (shown once; ask them to change it).");
    }

    public function bulkUserActions(Request $request)
    {
        $action  = $request->action;
        $userIds = $request->user_ids;

        switch ($action) {
            case 'approve':
            case 'activate':
                User::whereIn('id', $userIds)->update(['is_approved' => true]);
                break;
            case 'deactivate':
                User::whereIn('id', $userIds)->update(['is_approved' => false]);
                break;
            case 'delete':
                User::whereIn('id', $userIds)->delete();
                break;
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Bulk action completed successfully.');
    }

    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role        = $request->role;
        $user->is_approved = true;
        $user->save();

        Mail::raw(
            "Hello {$user->name},\n\nYour account has been approved successfully.\nYou can now access your dashboard based on your assigned role.\n\nThank you,\nKTVTC Team",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Account Approval - KTVTC');
            }
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User approved successfully and email sent.');
    }

    // ============================================================
    // PASSWORDS
    // ============================================================

    /**
     * ✅ FIXED:
     *   - Uses filter_var(... FILTER_VALIDATE_BOOLEAN) for send_sms
     *     so "0" is properly treated as false
     *   - SMS only fires when user has phone AND admin ticked the box
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $newPassword = $request->password;
        $user->password = Hash::make($newPassword);
        $user->save();

        // ✅ FIXED: proper boolean cast — "0" is now false
        $sendSms = filter_var($request->input('send_sms', false), FILTER_VALIDATE_BOOLEAN);

        $smsSent = false;
        if ($user->phone_number && $sendSms) {
            $result  = $this->sendPasswordResetSms($user, $newPassword);
            $smsSent = $result['success'] ?? false;
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Password updated successfully',
                'sms_sent' => $smsSent,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Password updated successfully.' . ($smsSent ? ' SMS sent.' : ''));
    }

    /**
     * ✅ FIXED: same boolean cast for bulk resets
     */
    public function bulkResetPasswords(Request $request)
    {
        $userIds     = $request->user_ids;
        $newPassword = $request->password ?? $this->generateRandomPassword();

        // ✅ FIXED: "0" now means false
        $sendSms = filter_var($request->input('send_sms', true), FILTER_VALIDATE_BOOLEAN);

        $users   = User::whereIn('id', $userIds)->get();
        $results = [
            'total'      => $users->count(),
            'updated'    => 0,
            'sms_sent'   => 0,
            'sms_failed' => 0,
            'errors'     => [],
        ];

        foreach ($users as $user) {
            try {
                $user->password = Hash::make($newPassword);
                $user->save();
                $results['updated']++;

                if ($sendSms && $user->phone_number) {
                    $smsResult = $this->sendPasswordResetSms($user, $newPassword);
                    if ($smsResult['success']) {
                        $results['sms_sent']++;
                    } else {
                        $results['sms_failed']++;
                        $results['errors'][] = [
                            'user_id' => $user->id,
                            'name'    => $user->name,
                            'error'   => $smsResult['message'] ?? 'Unknown error',
                        ];
                    }
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        }

        $message = "{$results['updated']} user passwords reset successfully.";
        if ($results['sms_sent'] > 0) {
            $message .= " SMS sent to {$results['sms_sent']} users.";
        }
        if ($results['sms_failed'] > 0) {
            $message .= " SMS failed for {$results['sms_failed']} users.";
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    protected function sendPasswordResetSms($user, $newPassword)
    {
        try {
            $phone = $user->phone_number;

            if (!$phone) {
                return ['success' => false, 'message' => 'No phone number available'];
            }

            $message  = "Hello {$user->name},\n\n";
            $message .= "Your KTVTC account password has been reset.\n";
            $message .= "New Password: {$newPassword}\n\n";
            $message .= "Please login and change your password immediately.\n\n";
            $message .= "Login URL: " . url('/login') . "\n\n";
            $message .= "Thank you,\nKTVTC Team";

            $result = $this->smsService->sendSingleSms($phone, $message);

            if ($result['success']) {
                \Log::info("Password reset SMS sent to {$phone}", [
                    'user_id'    => $user->id,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                \Log::error("Failed to send password reset SMS to {$phone}", [
                    'user_id' => $user->id,
                    'error'   => $result['message'] ?? 'Unknown error',
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error("Password reset SMS exception: " . $e->getMessage(), [
                'user_id' => $user->id,
                'phone'   => $user->phone_number,
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
            ];
        }
    }

    private function generateRandomPassword($length = 10)
    {
        $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
