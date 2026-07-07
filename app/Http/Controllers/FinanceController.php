<?php
// app/Http/Controllers/FinanceController.php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\KcbBuniTransaction;
use App\Models\EventApplication;
use App\Models\CardFundingRequest;
use App\Models\Course;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Campus;

class FinanceController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * ============================================================
     * 1. DASHBOARD - COMPLETE WITH ALL PAYMENT SOURCES
     * ============================================================
     */
    public function dashboard()
    {
        $today = now()->format('Y-m-d');
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // ============================================================
        // 1. APPLICATION FEES (Event Applications)
        // ============================================================
        $applicationFeesTotal = EventApplication::where('application_status', 'confirmed')
            ->sum('total_amount');

        $applicationFeesToday = EventApplication::whereDate('created_at', $today)
            ->where('application_status', 'confirmed')
            ->sum('total_amount');

        $applicationFeesMonth = EventApplication::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('application_status', 'confirmed')
            ->sum('total_amount');

        $pendingApplications = EventApplication::where('application_status', 'pending_payment')
            ->count();

        // ============================================================
        // 2. SCHOOL FEES (Fee Payments - Manual)
        // ============================================================
        $schoolFeesTotal = FeePayment::where('status', 'completed')
            ->where('payment_method', '!=', 'kcb')
            ->sum('amount');

        $schoolFeesToday = FeePayment::whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->where('payment_method', '!=', 'kcb')
            ->sum('amount');

        $schoolFeesMonth = FeePayment::whereYear('payment_date', $currentYear)
            ->whereMonth('payment_date', $currentMonth)
            ->where('status', 'completed')
            ->where('payment_method', '!=', 'kcb')
            ->sum('amount');

        $pendingVerifications = FeePayment::where('status', 'completed')
            ->where('is_verified', false)
            ->count();

        // ============================================================
        // 3. KCB IPN (Auto-reconciled School Fees)
        // ============================================================
        $kcbIpnTotal = FeePayment::where('status', 'completed')
            ->where('payment_method', 'kcb')
            ->sum('amount');

        $kcbIpnToday = FeePayment::whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->where('payment_method', 'kcb')
            ->sum('amount');

        $kcbIpnMonth = FeePayment::whereYear('payment_date', $currentYear)
            ->whereMonth('payment_date', $currentMonth)
            ->where('status', 'completed')
            ->where('payment_method', 'kcb')
            ->sum('amount');

        // ============================================================
        // 4. CAFETERIA SALES (STK Push & Other Methods)
        // ============================================================
        $cafeteriaSalesTotal = PaymentTransaction::where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('channel', 'cafeteria')
                  ->orWhere('sale_type', 'pos');
            })
            ->sum('amount');

        $cafeteriaSalesToday = PaymentTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('channel', 'cafeteria')
                  ->orWhere('sale_type', 'pos');
            })
            ->sum('amount');

        $cafeteriaSalesMonth = PaymentTransaction::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('channel', 'cafeteria')
                  ->orWhere('sale_type', 'pos');
            })
            ->sum('amount');

        $cafeteriaTransactionsToday = PaymentTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('channel', 'cafeteria')
                  ->orWhere('sale_type', 'pos');
            })
            ->count();

        // ============================================================
        // 5. EVENT FEES (Event Applications Payments)
        // ============================================================
        $eventFeesTotal = EventApplication::where('application_status', 'confirmed')
            ->where('total_amount', '>', 0)
            ->sum('total_amount');

        $eventFeesToday = EventApplication::whereDate('created_at', $today)
            ->where('application_status', 'confirmed')
            ->where('total_amount', '>', 0)
            ->sum('total_amount');

        $eventFeesMonth = EventApplication::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('application_status', 'confirmed')
            ->where('total_amount', '>', 0)
            ->sum('total_amount');

        $eventTransactionsToday = KcbBuniTransaction::whereDate('created_at', $today)
            ->where('transaction_type', 'event_registration')
            ->where('status', 'completed')
            ->count();

        // ============================================================
        // 6. GRAND TOTALS
        // ============================================================
        $totalCollected = $schoolFeesTotal + $kcbIpnTotal + $cafeteriaSalesTotal + $eventFeesTotal + $applicationFeesTotal;
        $todayCollection = $schoolFeesToday + $kcbIpnToday + $cafeteriaSalesToday + $eventFeesToday + $applicationFeesToday;
        $monthlyCollection = $schoolFeesMonth + $kcbIpnMonth + $cafeteriaSalesMonth + $eventFeesMonth + $applicationFeesMonth;

        // ============================================================
        // 7. OUTSTANDING BALANCE (Student Fees)
        // ============================================================
        $outstandingBalance = Enrollment::sum(DB::raw('total_fees - amount_paid'));

        // ============================================================
        // 8. TRANSACTION STATISTICS
        // ============================================================
        $todayTransactions = PaymentTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $todayTransactionAmount = PaymentTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $pendingTransactions = PaymentTransaction::where('status', 'pending')->count();

        // ============================================================
        // 9. COLLECTION RATE
        // ============================================================
        $totalFees = Enrollment::sum('total_fees') ?: 1;
        $collectionRate = round(($schoolFeesTotal / $totalFees) * 100, 1);

        // ============================================================
        // 10. TODAY'S PAYMENTS COUNT
        // ============================================================
        $todayPaymentsCount = FeePayment::whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->count();

        // ============================================================
        // 11. RECENT ACTIVITY (All Payment Types)
        // ============================================================

        // Recent School Fee Payments
        $recentFeePayments = FeePayment::with(['student', 'enrollment.course'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent KCB IPN Payments
        $recentKcbIpnPayments = FeePayment::with(['student', 'enrollment.course'])
            ->where('status', 'completed')
            ->where('payment_method', 'kcb')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Cafeteria Transactions
        $recentCafeteriaTransactions = PaymentTransaction::with(['sale', 'sale.shop'])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('channel', 'cafeteria')
                  ->orWhere('sale_type', 'pos');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent Event Applications
        $recentEventApplications = EventApplication::with(['event'])
            ->where('application_status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Application Fees
        $recentApplicationFees = EventApplication::with(['event'])
            ->where('application_status', 'confirmed')
            ->where('total_amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // 12. CHART DATA - Monthly Breakdown
        // ============================================================

        $monthlyLabels = [];
        $monthlyData = [];
        $monthlySchoolFees = [];
        $monthlyKcbIpn = [];
        $monthlyCafeteria = [];
        $monthlyEvents = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');

            // School Fees
            $schoolAmount = FeePayment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->where('payment_method', '!=', 'kcb')
                ->sum('amount');
            $monthlySchoolFees[] = (float) $schoolAmount;

            // KCB IPN
            $kcbAmount = FeePayment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->where('payment_method', 'kcb')
                ->sum('amount');
            $monthlyKcbIpn[] = (float) $kcbAmount;

            // Cafeteria
            $cafeteriaAmount = PaymentTransaction::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'completed')
                ->whereHas('sale', function($q) {
                    $q->where('channel', 'cafeteria')
                      ->orWhere('sale_type', 'pos');
                })
                ->sum('amount');
            $monthlyCafeteria[] = (float) $cafeteriaAmount;

            // Events
            $eventAmount = EventApplication::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('application_status', 'confirmed')
                ->where('total_amount', '>', 0)
                ->sum('total_amount');
            $monthlyEvents[] = (float) $eventAmount;

            // Total
            $total = $schoolAmount + $kcbAmount + $cafeteriaAmount + $eventAmount;
            $monthlyData[] = (float) $total;
        }

        // ============================================================
        // 13. PAYMENT METHOD BREAKDOWN
        // ============================================================
        $paymentMethods = collect([
            [
                'payment_method' => 'School Fees (Manual)',
                'total' => $schoolFeesTotal,
                'count' => FeePayment::where('status', 'completed')
                    ->where('payment_method', '!=', 'kcb')
                    ->count(),
                'color' => '#B91C1C'
            ],
            [
                'payment_method' => 'KCB IPN (Auto)',
                'total' => $kcbIpnTotal,
                'count' => FeePayment::where('status', 'completed')
                    ->where('payment_method', 'kcb')
                    ->count(),
                'color' => '#3B82F6'
            ],
            [
                'payment_method' => 'Cafeteria Sales',
                'total' => $cafeteriaSalesTotal,
                'count' => PaymentTransaction::where('status', 'completed')
                    ->whereHas('sale', function($q) {
                        $q->where('channel', 'cafeteria')
                          ->orWhere('sale_type', 'pos');
                    })
                    ->count(),
                'color' => '#10B981'
            ],
            [
                'payment_method' => 'Event Fees',
                'total' => $eventFeesTotal,
                'count' => EventApplication::where('application_status', 'confirmed')
                    ->where('total_amount', '>', 0)
                    ->count(),
                'color' => '#8B5CF6'
            ],
            [
                'payment_method' => 'Application Fees',
                'total' => $applicationFeesTotal,
                'count' => EventApplication::where('application_status', 'confirmed')
                    ->where('total_amount', '>', 0)
                    ->count(),
                'color' => '#F59E0B'
            ]
        ]);

        // ============================================================
        // 14. PENDING ITEMS COUNT
        // ============================================================
        $pendingFeeVerifications = FeePayment::where('status', 'completed')
            ->where('is_verified', false)
            ->count();

        $pendingFundingRequests = CardFundingRequest::where('status', 'pending')->count();

        // ============================================================
        // 15. FEE STRUCTURE PENDING CHANGES
        // ============================================================
        $pendingFeeChanges = Course::whereNotNull('fee_modified_by')
            ->whereNull('fee_modification_approved_by')
            ->count();

        // ============================================================
        // 16. TOTAL STUDENTS AND ENROLLMENTS
        // ============================================================
        $totalStudents = Student::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $totalPayments = FeePayment::where('status', 'completed')->count();

        // ============================================================
        // 17. CAMPUSES FOR FILTER
        // ============================================================
        $campuses = Campus::orderBy('name')->get();

        // ============================================================
        // 18. RECENT PAYMENTS (Combined for table)
        // ============================================================
        $recentPayments = FeePayment::with(['student', 'enrollment.course'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // ============================================================
        // 19. RECENT TRANSACTIONS (Combined for table)
        // ============================================================
        $recentTransactions = PaymentTransaction::with(['sale'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('ktvtc.finance.dashboard', compact(
            // Grand Totals
            'totalCollected',
            'todayCollection',
            'monthlyCollection',
            'outstandingBalance',
            'collectionRate',
            'todayPaymentsCount',

            // School Fees
            'schoolFeesTotal',
            'schoolFeesToday',
            'schoolFeesMonth',
            'pendingVerifications',

            // KCB IPN
            'kcbIpnTotal',
            'kcbIpnToday',
            'kcbIpnMonth',

            // Cafeteria
            'cafeteriaSalesTotal',
            'cafeteriaSalesToday',
            'cafeteriaSalesMonth',
            'cafeteriaTransactionsToday',

            // Event Fees
            'eventFeesTotal',
            'eventFeesToday',
            'eventFeesMonth',
            'eventTransactionsToday',

            // Application Fees
            'applicationFeesTotal',
            'applicationFeesToday',
            'applicationFeesMonth',
            'pendingApplications',

            // Transaction Stats
            'todayTransactions',
            'todayTransactionAmount',
            'pendingTransactions',

            // Pending Items
            'pendingFeeVerifications',
            'pendingFundingRequests',
            'pendingFeeChanges',

            // Recent Activity
            'recentPayments',
            'recentTransactions',
            'recentFeePayments',
            'recentKcbIpnPayments',
            'recentCafeteriaTransactions',
            'recentEventApplications',
            'recentApplicationFees',

            // Chart Data
            'monthlyLabels',
            'monthlyData',
            'monthlySchoolFees',
            'monthlyKcbIpn',
            'monthlyCafeteria',
            'monthlyEvents',

            // Payment Methods
            'paymentMethods',

            // Stats
            'totalStudents',
            'activeEnrollments',
            'totalPayments',

            // Filters
            'campuses'
        ));
    }

    /**
     * ============================================================
     * 2. STUDENT FINANCIAL VIEWS
     * ============================================================
     */

    /**
     * Search and list students for finance module
     */
    public function searchStudents(Request $request)
    {
        $user = Auth::user();

        $query = Student::query();

        // Filter by campus for non-admin users
        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        // Apply search filter
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
            });
        }

        // Apply campus filter for admin
        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        // Get students with pagination
        $students = $query->with(['campus', 'enrollments'])
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        // Get campus list for filter
        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        return view('ktvtc.finance.students.search', compact('students', 'campuses'));
    }

    /**
     * List all students with financial summary
     */
    public function studentList(Request $request)
    {
        $user = Auth::user();

        $query = Student::with(['campus']);

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        // Add financial summary for each student
        $students = $query->paginate(20)->withQueryString();

        // Calculate financial stats for each student
        foreach ($students as $student) {
            $student->total_fees = Enrollment::where('student_id', $student->id)->sum('total_fees');
            $student->total_paid = FeePayment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');
            $student->balance = $student->total_fees - $student->total_paid;
            $student->active_enrollments = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->count();
        }

        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        return view('ktvtc.finance.students.index', compact('students', 'campuses'));
    }

    /**
     * View student financial details
     */
    public function studentFinancials(Student $student)
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with(['course'])
            ->get();

        $payments = FeePayment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPaid = $payments->sum('amount');
        $totalFees = $enrollments->sum('total_fees');
        $balance = $totalFees - $totalPaid;

        return view('ktvtc.finance.students.financial', compact(
            'student',
            'enrollments',
            'payments',
            'totalPaid',
            'totalFees',
            'balance'
        ));
    }

    /**
     * View student transactions
     */
    public function studentTransactions(Student $student)
    {
        $transactions = FeePayment::where('student_id', $student->id)
            ->with(['enrollment.course'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.students.transactions', compact('student', 'transactions'));
    }

    /**
     * Get student balance
     */
    public function studentBalance(Student $student)
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->get();

        $totalFees = $enrollments->sum('total_fees');
        $totalPaid = FeePayment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->sum('amount');

        $balance = $totalFees - $totalPaid;

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_number' => $student->student_number,
            'total_fees' => (float) $totalFees,
            'total_paid' => (float) $totalPaid,
            'balance' => (float) $balance,
            'enrollments' => $enrollments->map(function($e) {
                return [
                    'course_name' => $e->course->name ?? 'N/A',
                    'total_fees' => $e->total_fees,
                    'amount_paid' => $e->amount_paid,
                    'balance' => $e->balance,
                    'status' => $e->status,
                ];
            })
        ]);
    }

    /**
     * Generate student statement
     */
    public function studentStatement(Student $student)
    {
        $payments = FeePayment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with(['enrollment.course'])
            ->orderBy('payment_date', 'asc')
            ->get();

        return view('ktvtc.finance.students.statement', compact('student', 'payments'));
    }

    /**
     * Get student enrollments with financial info
     */
    public function studentEnrollments(Student $student)
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with(['course', 'campus'])
            ->get()
            ->map(function($e) {
                return [
                    'id' => $e->id,
                    'course_name' => $e->course->name ?? 'N/A',
                    'total_fees' => $e->total_fees,
                    'amount_paid' => $e->amount_paid,
                    'balance' => $e->balance,
                    'status' => $e->status,
                    'enrollment_date' => $e->enrollment_date,
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * ============================================================
     * 3. STUDENT FEE MANAGEMENT
     * ============================================================
     */

    /**
     * Display all student fee payments
     */
    public function studentFees(Request $request)
    {
        $user = Auth::user();

        $query = FeePayment::with(['student', 'enrollment.course', 'verifier']);

        // Campus filter for non-admin users
        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }
        if ($request->filled('campus_id') && $user->role == 2) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('student_number', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $totalPayments = (clone $query)->count();
        $totalAmount = (clone $query)->sum('amount');
        $todayAmount = (clone $query)->whereDate('payment_date', today())->sum('amount');
        $pendingVerification = (clone $query)
            ->where('status', 'completed')
            ->where('is_verified', false)
            ->count();

        // Campuses for filter
        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        // Paginate
        $perPage = $request->get('per_page', 15);
        $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage)->withQueryString();

        return view('ktvtc.finance.student-fees.index', compact(
            'payments',
            'totalPayments',
            'totalAmount',
            'todayAmount',
            'pendingVerification',
            'campuses'
        ));
    }

    /**
     * Show form to create a new fee payment
     */
    public function createStudentFee(Request $request)
    {
        $user = Auth::user();

        $students = Student::when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('first_name')
            ->get();

        $selectedEnrollment = null;
        if ($request->filled('enrollment_id')) {
            $selectedEnrollment = Enrollment::with('student', 'course')
                ->find($request->enrollment_id);
        }

        return view('ktvtc.finance.student-fees.create', compact('students', 'selectedEnrollment'));
    }

    /**
     * Store a new fee payment
     */
    public function storeStudentFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'enrollment_id' => 'required|exists:enrollments,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,mpesa,bank,kcb,other',
            'payment_date' => 'required|date',
            'transaction_code' => 'nullable|string|max:100',
            'payment_for_month' => 'nullable|string|max:20',
            'payer_name' => 'nullable|string|max:255',
            'payer_phone' => 'nullable|string|max:20',
            'payer_type' => 'nullable|in:student,parent,sponsor,employer,other',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $enrollment = Enrollment::find($request->enrollment_id);

            // Calculate current balance
            $currentBalance = $enrollment->balance;

            // Check if amount exceeds balance
            if ($request->amount > $currentBalance) {
                return redirect()->back()
                    ->with('error', 'Payment amount exceeds the outstanding balance of KES ' . number_format($currentBalance, 2))
                    ->withInput();
            }

            // Generate receipt number
            $receiptNumber = FeePayment::generateReceiptNumber();

            // Create payment
            $payment = FeePayment::create([
                'student_id' => $request->student_id,
                'enrollment_id' => $request->enrollment_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'receipt_number' => $receiptNumber,
                'payment_method' => $request->payment_method,
                'transaction_code' => $request->transaction_code,
                'payment_for_month' => $request->payment_for_month,
                'payer_name' => $request->payer_name,
                'payer_phone' => $request->payer_phone,
                'payer_type' => $request->payer_type ?? 'student',
                'status' => 'completed',
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
                'import_source' => 'manual',
            ]);

            // Update enrollment - update BOTH amount_paid AND balance
            $enrollment->amount_paid = $enrollment->amount_paid + $request->amount;
            $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
            $enrollment->save();

            DB::commit();

            $newBalance = $enrollment->balance;
            $message = "Payment recorded successfully. Receipt: {$receiptNumber}";
            if ($newBalance <= 0) {
                $message .= " The enrollment is now fully paid!";
            }

            return redirect()->route('finance.student-fees.show', $payment)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show a single student fee payment
     */
    public function showStudentFee(FeePayment $payment)
    {
        $payment->load(['student', 'enrollment.course', 'verifier', 'recorder']);
        return view('ktvtc.finance.student-fees.show', compact('payment'));
    }

    /**
     * Generate receipt for a student fee payment
     */
    public function studentFeeReceipt(FeePayment $payment)
    {
        $payment->load(['student', 'enrollment.course']);
        return view('ktvtc.finance.student-fees.receipt', compact('payment'));
    }

    /**
     * Verify a student fee payment
     */
    public function verifyStudentFee(Request $request, FeePayment $payment)
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        if ($payment->is_verified) {
            return redirect()->back()->with('error', 'Payment is already verified.');
        }

        $payment->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Payment verified successfully.');
    }

    /**
     * Reverse a student fee payment
     */
    public function reverseStudentFee(Request $request, FeePayment $payment)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($payment->status == 'reversed') {
            return redirect()->back()->with('error', 'Payment is already reversed.');
        }

        DB::beginTransaction();

        try {
            // Reverse from enrollment
            $enrollment = $payment->enrollment;
            $enrollment->amount_paid = $enrollment->amount_paid - $payment->amount;
            $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
            $enrollment->save();

            // Update payment
            $payment->update([
                'status' => 'reversed',
                'reversal_reason' => $request->reason,
                'reversed_at' => now(),
                'reversed_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Payment reversed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to reverse payment: ' . $e->getMessage());
        }
    }

    /**
     * Reconcile a student fee payment
     */
    public function reconcileStudentFee(Request $request, FeePayment $payment)
    {
        $request->validate([
            'reconciled_by' => 'required|string',
            'reconciliation_notes' => 'nullable|string',
        ]);

        $payment->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => Auth::id(),
            'reconciliation_notes' => $request->reconciliation_notes,
        ]);

        return redirect()->back()->with('success', 'Payment reconciled successfully.');
    }

    /**
     * Bulk verify fee payments
     */
    public function bulkVerifyFees(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fee_payments,id',
        ]);

        $count = FeePayment::whereIn('id', $request->ids)
            ->where('is_verified', false)
            ->update([
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

        return redirect()->back()
            ->with('success', "{$count} payments verified successfully.");
    }

    /**
     * Bulk reconcile fee payments
     */
    public function bulkReconcileFees(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fee_payments,id',
        ]);

        $count = FeePayment::whereIn('id', $request->ids)
            ->where('is_reconciled', false)
            ->update([
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => Auth::id(),
            ]);

        return redirect()->back()
            ->with('success', "{$count} payments reconciled successfully.");
    }

    /**
     * Daily fee report
     */
    public function dailyFeeReport(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : today();

        $query = FeePayment::with(['student', 'enrollment.course'])
            ->whereDate('payment_date', $date)
            ->where('status', 'completed');

        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        $transactions = $query->orderBy('created_at')->get();

        $totalCollected = $transactions->sum('amount');
        $transactionCount = $transactions->count();
        $averageTransaction = $transactionCount > 0 ? $totalCollected / $transactionCount : 0;

        // Hourly breakdown
        $hourlyData = array_fill(0, 24, 0);
        foreach ($transactions as $t) {
            $hour = (int)$t->created_at->format('H');
            $hourlyData[$hour] += $t->amount;
        }

        // Method breakdown
        $methodBreakdown = [];
        foreach ($transactions->groupBy('payment_method') as $method => $items) {
            $total = $items->sum('amount');
            $methodBreakdown[$method] = [
                'total' => $total,
                'count' => $items->count(),
                'percentage' => $totalCollected > 0 ? round(($total / $totalCollected) * 100, 1) : 0,
            ];
        }

        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        return view('ktvtc.finance.student-fees.reports.daily', compact(
            'date',
            'transactions',
            'totalCollected',
            'transactionCount',
            'averageTransaction',
            'hourlyData',
            'methodBreakdown',
            'campuses'
        ));
    }

    /**
     * Monthly fee report
     */
    public function monthlyFeeReport(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $query = FeePayment::with(['student', 'enrollment.course'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        $payments = $query->get();

        $totalCollected = $payments->sum('amount');
        $transactionCount = $payments->count();

        // Daily breakdown
        $dailyLabels = [];
        $dailyData = [];

        for ($day = 1; $day <= $startDate->daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dailyLabels[] = $date->format('M j');

            $dayTotal = $payments->filter(function ($p) use ($date) {
                return $p->payment_date->format('Y-m-d') == $date->format('Y-m-d');
            })->sum('amount');

            $dailyData[] = $dayTotal;
        }

        // Monthly comparison (last 12 months)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');

            $monthTotal = FeePayment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->where('status', 'completed')
                ->when($user->role != 2, function ($q) use ($user) {
                    return $q->whereHas('enrollment', function ($sq) use ($user) {
                        $sq->where('campus_id', $user->campus_id);
                    });
                })
                ->when($request->filled('campus_id') && $user->role == 2, function ($q) use ($request) {
                    return $q->whereHas('enrollment', function ($sq) use ($request) {
                        $sq->where('campus_id', $request->campus_id);
                    });
                })
                ->sum('amount');

            $monthlyData[] = $monthTotal;
        }

        // Method summary
        $methodSummary = [];
        foreach ($payments->groupBy('payment_method') as $method => $items) {
            $total = $items->sum('amount');
            $methodSummary[$method] = [
                'total' => $total,
                'count' => $items->count(),
                'percentage' => $totalCollected > 0 ? round(($total / $totalCollected) * 100, 1) : 0,
            ];
        }

        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];
        $monthName = $startDate->format('F');

        return view('ktvtc.finance.student-fees.reports.monthly', compact(
            'month',
            'year',
            'monthName',
            'totalCollected',
            'transactionCount',
            'dailyLabels',
            'dailyData',
            'monthlyLabels',
            'monthlyData',
            'methodSummary',
            'campuses'
        ));
    }

    /**
     * Outstanding fee report
     */
    public function outstandingFeeReport(Request $request)
    {
        $user = Auth::user();

        $query = Enrollment::with(['student', 'course'])
            ->whereRaw('total_fees > amount_paid')
            ->where('status', 'active');

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('balance', 'desc')->paginate(20);

        $totalOutstanding = $query->sum(DB::raw('total_fees - amount_paid'));
        $totalStudents = $query->count();

        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        return view('ktvtc.finance.student-fees.reports.outstanding', compact(
            'enrollments',
            'totalOutstanding',
            'totalStudents',
            'campuses'
        ));
    }

    /**
     * Export student fee payments
     */
    public function exportStudentFees(Request $request)
    {
        $user = Auth::user();

        $query = FeePayment::with(['student', 'enrollment.course', 'enrollment.campus'])
            ->where('status', 'completed');

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        if ($request->report == 'daily' && $request->filled('date')) {
            $query->whereDate('payment_date', $request->date);
        }

        if ($request->report == 'monthly' && $request->filled('month') && $request->filled('year')) {
            $query->whereMonth('payment_date', $request->month)
                  ->whereYear('payment_date', $request->year);
        }

        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        $payments = $query->orderBy('payment_date')->get();

        // Generate CSV
        $filename = 'fee_payments_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Headers
        fputcsv($handle, [
            'Receipt Number',
            'Date',
            'Student Name',
            'Student Number',
            'Course',
            'Amount',
            'Payment Method',
            'Transaction Code',
            'Status',
            'Verified',
        ]);

        // Data
        foreach ($payments as $payment) {
            fputcsv($handle, [
                $payment->receipt_number,
                $payment->payment_date->format('Y-m-d'),
                $payment->student->full_name ?? 'N/A',
                $payment->student->student_number ?? 'N/A',
                $payment->enrollment->course->name ?? 'N/A',
                $payment->amount,
                strtoupper($payment->payment_method),
                $payment->transaction_code ?? '',
                $payment->status,
                $payment->is_verified ? 'Yes' : 'No',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * ============================================================
     * 4. TRANSACTION MANAGEMENT
     * ============================================================
     */

    /**
     * Display all transactions
     */
    public function transactions(Request $request)
    {
        $query = PaymentTransaction::with(['sale', 'sale.shop', 'sale.items.product'])
            ->latest();

        // Apply filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filtering
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        } else {
            // Default to today
            $query->whereDate('created_at', today());
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhere('mpesa_receipt', 'like', '%' . $request->search . '%')
                  ->orWhereHas('sale', function($sq) use ($request) {
                      $sq->where('invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                        ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $transactions = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateTransactionStats($request);

        return view('ktvtc.finance.transactions.index', compact(
            'transactions',
            'stats'
        ));
    }

    /**
     * Calculate transaction statistics
     */
    private function calculateTransactionStats($request)
    {
        $query = PaymentTransaction::query();

        // Apply date filters
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        } else {
            $query->whereDate('created_at', today());
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return [
            'total_amount' => $query->clone()->where('status', 'completed')->sum('amount'),
            'total_count' => $query->clone()->where('status', 'completed')->count(),
            'mpesa_amount' => $query->clone()->where('payment_method', 'mpesa')->where('status', 'completed')->sum('amount'),
            'mpesa_count' => $query->clone()->where('payment_method', 'mpesa')->where('status', 'completed')->count(),
            'cash_amount' => $query->clone()->where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
            'cash_count' => $query->clone()->where('payment_method', 'cash')->where('status', 'completed')->count(),
            'card_amount' => $query->clone()->where('payment_method', 'card')->where('status', 'completed')->sum('amount'),
            'card_count' => $query->clone()->where('payment_method', 'card')->where('status', 'completed')->count(),
            'pending_count' => $query->clone()->where('status', 'pending')->count(),
            'pending_amount' => $query->clone()->where('status', 'pending')->sum('amount'),
            'failed_count' => $query->clone()->where('status', 'failed')->count(),
            'failed_amount' => $query->clone()->where('status', 'failed')->sum('amount'),
        ];
    }

    /**
     * Show a single transaction
     */
    public function showTransaction(PaymentTransaction $transaction)
    {
        $transaction->load(['sale', 'sale.items.product', 'sale.shop']);
        return view('ktvtc.finance.transactions.show', compact('transaction'));
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction(Request $request, PaymentTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Only pending transactions can be verified'], 422);
        }

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $request->notes ?? 'Verified by finance',
        ]);

        if ($transaction->sale) {
            $transaction->sale->update([
                'payment_status' => 'paid',
                'payment_confirmed_at' => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Transaction verified successfully']);
    }

    /**
     * Reverse a transaction
     */
    public function reverseTransaction(Request $request, PaymentTransaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return response()->json(['error' => 'Only completed transactions can be reversed'], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $transaction->update([
                'status' => 'reversed',
                'notes' => ($transaction->notes ? $transaction->notes . ' | ' : '') . 'Reversed: ' . $request->reason
            ]);

            if ($transaction->sale) {
                $transaction->sale->update([
                    'payment_status' => 'refunded'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction reversed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reconcile a transaction
     */
    public function reconcileTransaction(Request $request, PaymentTransaction $transaction)
    {
        $request->validate([
            'reconciled_by' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $transaction->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => Auth::id(),
            'reconciliation_notes' => $request->notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Transaction reconciled successfully']);
    }

    /**
     * Print transaction receipt
     */
    public function printTransactionReceipt(PaymentTransaction $transaction)
    {
        $transaction->load(['sale', 'sale.items.product', 'sale.shop']);
        return view('ktvtc.finance.transactions.receipt', compact('transaction'));
    }

    /**
     * Get M-Pesa transactions
     */
    public function mpesaTransactions(Request $request)
    {
        $transactions = KcbBuniTransaction::with(['sale'])
            ->latest()
            ->paginate(20);

        return view('ktvtc.finance.transactions.mpesa', compact('transactions'));
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleMpesaCallback(Request $request)
    {
        Log::info('M-Pesa callback received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    /**
     * Get transactions by payment method
     */
    public function transactionsByMethod($method)
    {
        $transactions = PaymentTransaction::where('payment_method', $method)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.transactions.by-method', compact('transactions', 'method'));
    }

    /**
     * Get pending transactions
     */
    public function pendingTransactions()
    {
        $transactions = PaymentTransaction::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.transactions.pending', compact('transactions'));
    }

    /**
     * Get today's transactions
     */
    public function todayTransactions()
    {
        $transactions = PaymentTransaction::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.transactions.today', compact('transactions'));
    }

    /**
     * Export transactions
     */
    public function exportTransactions(Request $request)
    {
        $query = PaymentTransaction::with(['sale'])
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->get();

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Add headers
        fputcsv($handle, [
            'Transaction #', 'Sale Invoice', 'Customer', 'Phone', 'Payment Method',
            'Amount', 'M-Pesa Receipt', 'Status', 'Date'
        ]);

        foreach ($transactions as $transaction) {
            $sale = $transaction->sale;
            fputcsv($handle, [
                $transaction->transaction_number,
                $sale?->invoice_number ?? 'N/A',
                $sale?->customer_name ?? 'N/A',
                $transaction->phone_number ?? $sale?->customer_phone ?? 'N/A',
                ucfirst($transaction->payment_method),
                number_format($transaction->amount, 2),
                $transaction->mpesa_receipt ?? 'N/A',
                ucfirst($transaction->status),
                $transaction->created_at->format('Y-m-d H:i:s')
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->withHeaders([
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }

    /**
     * Bulk reconcile transactions
     */
    public function bulkReconcileTransactions(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:payment_transactions,id',
        ]);

        $count = PaymentTransaction::whereIn('id', $request->ids)
            ->where('is_reconciled', false)
            ->update([
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => Auth::id(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} transactions reconciled successfully"
        ]);
    }

    /**
     * Bulk process payments
     */
    public function bulkProcessPayments(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:payment_transactions,id',
        ]);

        $count = PaymentTransaction::whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} payments processed successfully"
        ]);
    }

    /**
     * ============================================================
     * 5. FINANCIAL REPORTS
     * ============================================================
     */

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Revenue from student fees (including KCB IPN)
        $feeRevenue = FeePayment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');

        // Revenue from cafeteria sales (from PaymentTransaction)
        $salesRevenue = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where('sale_type', 'pos')
                  ->orWhere('channel', 'cafeteria');
            })
            ->sum('amount');

        // Revenue from events
        $eventRevenue = EventApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('application_status', 'confirmed')
            ->where('total_amount', '>', 0)
            ->sum('total_amount');

        $totalRevenue = $feeRevenue + $salesRevenue + $eventRevenue;

        // COGS for cafeteria (from sales items)
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', 'paid')
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Expenses from purchase orders
        $expenses = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Calculate profit metrics
        $grossProfit = $totalRevenue - $cogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit - $expenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return view('ktvtc.finance.reports.profit-loss', compact(
            'startDate',
            'endDate',
            'feeRevenue',
            'salesRevenue',
            'eventRevenue',
            'totalRevenue',
            'cogs',
            'expenses',
            'grossProfit',
            'grossMargin',
            'netProfit',
            'netMargin'
        ));
    }

    /**
     * Revenue Report
     */
    public function revenueReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Daily revenue breakdown - All sources
        $dailyRevenue = FeePayment::select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw("'school_fees' as source")
            )
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('date')

            ->union(
                PaymentTransaction::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(amount) as total'),
                    DB::raw('COUNT(*) as transactions'),
                    DB::raw("'cafeteria' as source")
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->whereHas('sale', function($q) {
                    $q->where('sale_type', 'pos')
                      ->orWhere('channel', 'cafeteria');
                })
                ->groupBy('date')
            )

            ->union(
                EventApplication::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('COUNT(*) as transactions'),
                    DB::raw("'events' as source")
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('application_status', 'confirmed')
                ->where('total_amount', '>', 0)
                ->groupBy('date')
            )
            ->orderBy('date')
            ->get();

        // Revenue by payment method
        $methodRevenue = FeePayment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $totalRevenue = $dailyRevenue->sum('total');

        return view('ktvtc.finance.reports.revenue', compact(
            'startDate',
            'endDate',
            'dailyRevenue',
            'methodRevenue',
            'totalRevenue'
        ));
    }

    /**
     * Expenses Report
     */
    public function expensesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Purchase expenses
        $purchaseExpenses = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Supplier expense breakdown
        $supplierExpenses = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('supplier_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->orderBy('total', 'desc')
            ->get();

        // Monthly expense trend
        $monthlyExpenses = PurchaseOrder::select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('ktvtc.finance.reports.expenses', compact(
            'startDate',
            'endDate',
            'purchaseExpenses',
            'supplierExpenses',
            'monthlyExpenses'
        ));
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $asAtDate = $request->get('date', now()->format('Y-m-d'));
        $asAt = Carbon::parse($asAtDate);

        // Assets: Total fees collected
        $totalFeesCollected = FeePayment::whereDate('payment_date', '<=', $asAtDate)
            ->where('status', 'completed')
            ->sum('amount');

        // Liabilities: Outstanding balance
        $outstandingBalance = Enrollment::where('created_at', '<=', $asAtDate)
            ->sum(DB::raw('total_fees - amount_paid'));

        // Equity: Net position
        $equity = $totalFeesCollected - $outstandingBalance;

        return view('ktvtc.finance.reports.balance-sheet', compact(
            'asAt',
            'totalFeesCollected',
            'outstandingBalance',
            'equity'
        ));
    }

    /**
     * Cash Flow Report
     */
    public function cashflowReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Cash inflows (collections)
        $cashInflows = FeePayment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('amount');

        // Cash outflows (purchases)
        $cashOutflows = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Net cash flow
        $netCashFlow = $cashInflows - $cashOutflows;

        // Daily cash flow trend
        $dailyCashFlow = FeePayment::select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as inflow'),
                DB::raw('0 as outflow')
            )
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('ktvtc.finance.reports.cashflow', compact(
            'startDate',
            'endDate',
            'cashInflows',
            'cashOutflows',
            'netCashFlow',
            'dailyCashFlow'
        ));
    }

    /**
     * Revenue Trends (from AnalyticController)
     */
    public function revenueTrends(Request $request)
    {
        $period = $request->get('period', 'month');

        $labels = [];
        $data = [];

        if ($period === 'month') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $labels[] = $month->format('M Y');
                $amount = FeePayment::whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->where('status', 'completed')
                    ->sum('amount');
                $data[] = (float) $amount;
            }
        } elseif ($period === 'year') {
            // Last 5 years
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $labels[] = (string) $year;
                $amount = FeePayment::whereYear('payment_date', $year)
                    ->where('status', 'completed')
                    ->sum('amount');
                $data[] = (float) $amount;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Export financial report
     */
    public function exportFinancialReport(Request $request, $type)
    {
        return redirect()->back()->with('info', 'Export functionality coming soon.');
    }

    /**
     * ============================================================
     * 6. FEE REMINDERS
     * ============================================================
     */

    /**
     * Send fee reminders to selected students
     */
    public function sendFeeReminders(Request $request)
    {
        $enrollmentIds = $request->enrollment_ids;
        $template = $request->template ?? 'standard';
        $customMessage = $request->custom_message ?? '';

        if (empty($enrollmentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No students selected.'
            ], 400);
        }

        $enrollments = Enrollment::whereIn('id', $enrollmentIds)
            ->with(['student'])
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $failed = [];

        foreach ($enrollments as $enrollment) {
            if (!$enrollment->student) {
                $failedCount++;
                $failed[] = [
                    'name' => $enrollment->student_name ?? 'Unknown',
                    'reason' => 'No student record found'
                ];
                continue;
            }

            if (!$enrollment->student->phone) {
                $failedCount++;
                $failed[] = [
                    'name' => $enrollment->student->full_name,
                    'reason' => 'No phone number'
                ];
                continue;
            }

            $balance = $enrollment->total_fees - $enrollment->amount_paid;
            $name = $enrollment->student->full_name;

            if ($template === 'custom' && !empty($customMessage)) {
                $message = $this->parseMessage($customMessage, $name, $balance, $enrollment);
            } else {
                $message = $this->generateTemplateMessage($template, $name, $balance, $enrollment);
            }

            $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);

            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
                $failed[] = [
                    'name' => $name,
                    'reason' => $result['message'] ?? 'SMS sending failed'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total_count' => $enrollments->count(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failed' => $failed
        ]);
    }

    /**
     * Send a single fee reminder to a student
     */
    public function sendSingleFeeReminder($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        if (!$enrollment->student || !$enrollment->student->phone) {
            return redirect()->back()->with('error', 'Student has no phone number.');
        }

        $balance = $enrollment->total_fees - $enrollment->amount_paid;
        $message = "Dear {$enrollment->student->full_name},\n\n";
        $message .= "This is a reminder that you have an outstanding balance of KES " . number_format($balance, 2) . " for your enrollment in {$enrollment->course_name}.\n\n";
        $message .= "Please settle your fees to avoid any interruptions.\n\n";
        $message .= "Thank you,\nKTVTC Team";

        $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Fee reminder sent successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to send fee reminder: ' . $result['message']);
        }
    }

    /**
     * Send bulk balance reminders
     */
    public function sendBulkBalanceReminders(Request $request)
    {
        $ids = $request->enrollment_ids;
        $enrollments = Enrollment::whereIn('id', $ids)->with(['student'])->get();

        $sent = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            if ($enrollment->student && $enrollment->student->phone) {
                $balance = $enrollment->total_fees - $enrollment->amount_paid;
                $message = "Dear {$enrollment->student->full_name},\n\n";
                $message .= "This is a reminder that you have an outstanding balance of KES " . number_format($balance, 2) . " for your enrollment in {$enrollment->course_name}.\n\n";
                $message .= "Please settle your fees to avoid any interruptions.\n\n";
                $message .= "Thank you,\nKTVTC Team";

                $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return redirect()->back()->with('success', "Bulk reminders sent: {$sent} sent, {$failed} failed.");
    }

    /**
     * Get students eligible for fee reminder
     */
    public function getEligibleForReminder()
    {
        $enrollments = Enrollment::whereRaw('total_fees > amount_paid')
            ->where('status', 'active')
            ->with(['student'])
            ->get(['id', 'student_id', 'student_name', 'total_fees', 'amount_paid']);

        return response()->json($enrollments);
    }

    /**
     * Generate template message for fee reminder
     */
    private function generateTemplateMessage($template, $name, $balance, $enrollment)
    {
        $balanceFormatted = number_format($balance, 2);
        $link = 'www.ktvtc.ac.ke/pay';
        $course = $enrollment->course_name ?? 'course';
        $studentNumber = $enrollment->student_number ?? 'N/A';

        switch ($template) {
            case 'urgent':
                return "URGENT: Dear {$name}, your fee balance of KES {$balanceFormatted} is now overdue. Please clear your fees immediately to avoid interruption. Pay via {$link} or visit the finance office. KTVTC Admin.";
            case 'friendly':
                return "Hello {$name}! Friendly reminder: Your outstanding balance is KES {$balanceFormatted}. Pay conveniently at {$link}. Thank you for choosing KTVTC.";
            case 'standard':
            default:
                return "Dear {$name}, your current fee balance is KES {$balanceFormatted}. Please clear your fees promptly. Pay online: {$link}. Thank you. KTVTC.";
        }
    }

    /**
     * Parse custom message template
     */
    private function parseMessage($message, $name, $balance, $enrollment)
    {
        $replacements = [
            '{name}' => $name,
            '{balance}' => number_format($balance, 2),
            '{link}' => 'www.ktvtc.ac.ke/pay',
            '{course}' => $enrollment->course_name ?? 'course',
            '{student_number}' => $enrollment->student_number ?? 'N/A',
            '{total_fees}' => number_format($enrollment->total_fees, 2),
            '{paid}' => number_format($enrollment->amount_paid, 2),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * ============================================================
     * 7. STATISTICS & API
     * ============================================================
     */

    /**
     * Get finance statistics
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'today');

        $stats = [];

        if ($period === 'today') {
            $stats = [
                'today_collections' => FeePayment::whereDate('payment_date', today())
                    ->where('status', 'completed')
                    ->sum('amount'),
                'today_transactions' => FeePayment::whereDate('payment_date', today())
                    ->where('status', 'completed')
                    ->count(),
                'pending_verifications' => FeePayment::where('status', 'completed')
                    ->where('is_verified', false)
                    ->count(),
                'outstanding_balance' => Enrollment::sum(DB::raw('total_fees - amount_paid')),
                'pending_applications' => EventApplication::where('application_status', 'pending_payment')->count(),
            ];
        } elseif ($period === 'month') {
            $stats = [
                'monthly_collections' => FeePayment::whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'monthly_transactions' => FeePayment::whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month)
                    ->where('status', 'completed')
                    ->count(),
                'collection_rate' => $this->calculateCollectionRate(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * Dashboard statistics API
     */
    public function dashboardStats()
    {
        $today = today();

        return response()->json([
            'today_collection' => FeePayment::whereDate('payment_date', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'today_count' => FeePayment::whereDate('payment_date', $today)
                ->where('status', 'completed')
                ->count(),
            'monthly_collection' => FeePayment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->where('status', 'completed')
                ->sum('amount'),
            'outstanding_balance' => Enrollment::sum(DB::raw('total_fees - amount_paid')),
            'pending_verifications' => FeePayment::where('status', 'completed')
                ->where('is_verified', false)
                ->count(),
            'collection_rate' => $this->calculateCollectionRate(),
        ]);
    }

    /**
     * Payment statistics API
     */
    public function paymentStats()
    {
        $today = today();

        return response()->json([
            'total_collected' => FeePayment::where('status', 'completed')->sum('amount'),
            'today_collected' => FeePayment::whereDate('payment_date', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_collected' => FeePayment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->where('status', 'completed')
                ->sum('amount'),
            'weekly_collected' => FeePayment::whereBetween('payment_date', [
                    now()->startOfWeek(), now()->endOfWeek()
                ])
                ->where('status', 'completed')
                ->sum('amount'),
            'by_method' => FeePayment::where('status', 'completed')
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->get(),
        ]);
    }

    /**
     * Transaction statistics API
     */
    public function transactionStats()
    {
        $today = today();

        return response()->json([
            'total_transactions' => PaymentTransaction::where('status', 'completed')->count(),
            'today_transactions' => PaymentTransaction::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'today_amount' => PaymentTransaction::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_transactions' => PaymentTransaction::where('status', 'pending')->count(),
            'failed_transactions' => PaymentTransaction::where('status', 'failed')->count(),
            'by_method' => PaymentTransaction::where('status', 'completed')
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->get(),
        ]);
    }

    /**
     * Get student balance API
     */
    public function getStudentBalance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::find($request->student_id);
        return $this->studentBalance($student);
    }

    /**
     * Check payment status API
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|string',
        ]);

        $payment = FeePayment::where('receipt_number', $request->receipt_number)
            ->with(['student', 'enrollment.course'])
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payment' => [
                'receipt_number' => $payment->receipt_number,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date->format('Y-m-d H:i:s'),
                'status' => $payment->status,
                'is_verified' => $payment->is_verified,
                'student_name' => $payment->student->full_name ?? 'N/A',
                'course_name' => $payment->enrollment->course->name ?? 'N/A',
            ]
        ]);
    }

    /**
     * ============================================================
     * 8. SETTINGS
     * ============================================================
     */

    /**
     * Finance settings
     */
    public function settings()
    {
        return view('ktvtc.finance.settings.index');
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Update payment settings
     */
    public function updatePaymentSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Payment settings updated successfully.');
    }

    /**
     * Update tax settings
     */
    public function updateTaxSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Tax settings updated successfully.');
    }

    /**
     * Fee structure settings
     */
    public function feeStructure()
    {
        return view('ktvtc.finance.settings.fee-structure');
    }

    /**
     * Update fee structure
     */
    public function updateFeeStructure(Request $request)
    {
        return redirect()->back()->with('success', 'Fee structure updated successfully.');
    }

    /**
     * Financial year settings
     */
    public function financialYear()
    {
        return view('ktvtc.finance.settings.financial-year');
    }

    /**
     * Update financial year
     */
    public function updateFinancialYear(Request $request)
    {
        return redirect()->back()->with('success', 'Financial year updated successfully.');
    }

    /**
     * ============================================================
     * 9. HELPER METHODS
     * ============================================================
     */

    /**
     * Calculate collection rate
     */
    private function calculateCollectionRate()
    {
        $totalFees = Enrollment::sum('total_fees') ?: 1;
        $collected = FeePayment::where('status', 'completed')->sum('amount');
        return round(($collected / $totalFees) * 100, 1);
    }
}
