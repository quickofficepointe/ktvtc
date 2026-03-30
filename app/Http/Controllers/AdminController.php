<?php
namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; // <-- ADD THIS LINE

class AdminController extends Controller
{
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
    $outstandingBalance = Enrollment::sum(DB::raw('total_fees - amount_paid')); // Now works

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

    public function users()
    {
        // Use paginate() instead of get() to get Paginator instance
        $users = User::latest()->paginate(25); // 25 users per page

        $totalUsers = User::count();
        $activeUsers = User::where('is_approved', true)->count();
        $pendingUsers = User::where('is_approved', false)->count();
        $adminUsers = User::where('role', 2)->count();

        return view('ktvtc.admin.users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'pendingUsers', 'adminUsers'
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
            'password' => bcrypt('defaultpassword'), // You should generate a random password or send email to set password
            'is_approved' => true, // Auto-approve or set based on logic
        ]);

        // Optionally send email to user with login details

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->is_approved = true;
        $user->save();

        // Send approval email using raw
        Mail::raw(
            "Hello {$user->name},\n\nYour account has been approved successfully.
            You can now access your dashboard based on your assigned role.\n\nThank you,\nKTVTC Team",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Account Approval - KTVTC');
            }
        );

        return redirect()->route('admin.dashboard')
            ->with('success', 'User approved successfully and email sent.');
    }
}
