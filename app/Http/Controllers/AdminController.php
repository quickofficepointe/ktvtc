<?php
namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\SmsService;

class AdminController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function dashboard()
    {
        $user = auth()->user();

        // Student statistics
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $graduatedStudents = Student::where('status', 'graduated')->count();
        $inactiveStudents = Student::whereIn('status', ['dropped', 'suspended', 'inactive'])->count();

        $total = max($totalStudents, 1);
        $activePercentage = round(($activeStudents / $total) * 100);
        $graduatedPercentage = round(($graduatedStudents / $total) * 100);
        $inactivePercentage = round(($inactiveStudents / $total) * 100);

        // Enrollment statistics
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $inProgressEnrollments = Enrollment::where('status', 'in_progress')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $pendingPaymentEnrollments = Enrollment::whereRaw('total_fees > amount_paid')->count();

        $enrollmentTotal = max($totalEnrollments, 1);
        $inProgressPercentage = round(($inProgressEnrollments / $enrollmentTotal) * 100);
        $completedPercentage = round(($completedEnrollments / $enrollmentTotal) * 100);
        $pendingPaymentPercentage = round(($pendingPaymentEnrollments / $enrollmentTotal) * 100);

        // Payment statistics
        $totalCollected = FeePayment::where('status', 'completed')->sum('amount');
        $totalPaid = FeePayment::where('status', 'completed')->sum('amount');
        $outstandingBalance = Enrollment::sum(DB::raw('total_fees - amount_paid'));

        $todayCollection = FeePayment::whereDate('payment_date', today())
            ->where('status', 'completed')
            ->sum('amount');

        $todayPayments = FeePayment::whereDate('payment_date', today())
            ->where('status', 'completed')
            ->count();

        $todayPaymentsList = FeePayment::with('student')
            ->whereDate('payment_date', today())
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent data for tables
        $recentEnrollments = Enrollment::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPayments = FeePayment::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart data (last 6 months)
        $chartLabels = [];
        $enrollmentChartData = [];
        $paymentChartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartLabels[] = $month->format('M');

            $enrollmentChartData[] = Enrollment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $paymentChartData[] = FeePayment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->sum('amount');
        }

        return view('ktvtc.admin.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'graduatedStudents',
            'inactiveStudents',
            'activePercentage',
            'graduatedPercentage',
            'inactivePercentage',
            'totalEnrollments',
            'activeEnrollments',
            'inProgressEnrollments',
            'completedEnrollments',
            'pendingPaymentEnrollments',
            'inProgressPercentage',
            'completedPercentage',
            'pendingPaymentPercentage',
            'totalCollected',
            'outstandingBalance',
            'todayCollection',
            'todayPayments',
            'todayPaymentsList',
            'recentEnrollments',
            'recentPayments',
            'chartLabels',
            'enrollmentChartData',
            'paymentChartData'
        ));
    }

    public function showUser($id)
    {
        $user = User::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($user);
        }

        return view('ktvtc.admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($user);
        }

        return view('ktvtc.admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|string',
            'role' => 'required|integer',
            'bio' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'bio' => $request->bio,
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

    public function bulkUserActions(Request $request)
    {
        $action = $request->action;
        $userIds = $request->user_ids;

        switch ($action) {
            case 'approve':
                User::whereIn('id', $userIds)->update(['is_approved' => true]);
                break;
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

public function users(Request $request)
{
    // Get per_page from request or default to 100
    $perPage = $request->input('per_page', 100);

    // Validate per_page to prevent abuse (min 10, max 200)
    $perPage = max(10, min(200, (int)$perPage));

    // Order by name alphabetically (A-Z)
    $users = User::orderBy('name', 'asc')->paginate($perPage);

    // Alternative: If you want to sort by first name specifically
    // $users = User::orderByRaw("SUBSTRING_INDEX(name, ' ', 1) ASC")->paginate($perPage);

    $totalUsers = User::count();
    $activeUsers = User::where('is_approved', true)->count();
    $pendingUsers = User::where('is_approved', false)->count();
    $adminUsers = User::where('role', 2)->count();

    return view('ktvtc.admin.users.index', compact(
        'users', 'totalUsers', 'activeUsers', 'pendingUsers', 'adminUsers', 'perPage'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone_number' => 'nullable|string',
            'role' => 'required|integer',
            'bio' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'bio' => $request->bio,
            'password' => bcrypt('defaultpassword'),
            'is_approved' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->is_approved = true;
        $user->save();

        Mail::raw(
            "Hello {$user->name},\n\nYour account has been approved successfully.\nYou can now access your dashboard based on your assigned role.\n\nThank you,\nKTVTC Team",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Account Approval - KTVTC');
            }
        );

        return redirect()->route('admin.dashboard')
            ->with('success', 'User approved successfully and email sent.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $newPassword = $request->password;
        $user->password = Hash::make($newPassword);
        $user->save();

        $smsSent = false;
        if ($user->phone_number && ($request->send_sms || $request->has('send_sms'))) {
            $result = $this->sendPasswordResetSms($user, $newPassword);
            $smsSent = $result['success'] ?? false;
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
                'sms_sent' => $smsSent
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Password updated successfully.' . ($smsSent ? ' SMS sent.' : ''));
    }

    public function bulkResetPasswords(Request $request)
    {
        $userIds = $request->user_ids;
        $newPassword = $request->password ?? $this->generateRandomPassword();
        $sendSms = $request->send_sms ?? true;

        $users = User::whereIn('id', $userIds)->get();
        $results = [
            'total' => $users->count(),
            'updated' => 0,
            'sms_sent' => 0,
            'sms_failed' => 0,
            'errors' => []
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
                            'name' => $user->name,
                            'error' => $smsResult['message'] ?? 'Unknown error'
                        ];
                    }
                }

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results
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

            $message = "Hello {$user->name},\n\n";
            $message .= "Your KTVTC account password has been reset.\n";
            $message .= "New Password: {$newPassword}\n\n";
            $message .= "Please login and change your password immediately.\n\n";
            $message .= "Login URL: " . url('/login') . "\n\n";
            $message .= "Thank you,\nKTVTC Team";

            $result = $this->smsService->sendSingleSms($phone, $message);

            if ($result['success']) {
                \Log::info("Password reset SMS sent to {$phone}", [
                    'user_id' => $user->id,
                    'message_id' => $result['message_id'] ?? null
                ]);
            } else {
                \Log::error("Failed to send password reset SMS to {$phone}", [
                    'user_id' => $user->id,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error("Password reset SMS exception: " . $e->getMessage(), [
                'user_id' => $user->id,
                'phone' => $user->phone_number
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    private function generateRandomPassword($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
