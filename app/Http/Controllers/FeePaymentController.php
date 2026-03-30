<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Campus;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FeePaymentController extends Controller
{
    /**
     * ============ INDEX PAGE ============
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = FeePayment::with(['student', 'enrollment.course', 'verifier'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function ($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            });

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

        // Statistics for cards
        $totalPayments = (clone $query)->count();
        $totalAmount = (clone $query)->sum('amount');
        $todayAmount = (clone $query)->whereDate('payment_date', today())->sum('amount');
        $pendingVerification = (clone $query)
            ->where('status', 'completed')
            ->where('is_verified', false)
            ->count();
        $monthlyAmount = (clone $query)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $monthlyCount = (clone $query)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->count();

       $totalFees = Enrollment::sum('total_fees') ?: 1;
        $collected = FeePayment::where('status', 'completed')->sum('amount');
        $collectionRate = round(($collected / $totalFees) * 100, 1);

        // Get campuses for filter
        $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

        // Paginate
        $perPage = $request->get('per_page', 15);
        $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage)->withQueryString();

        return view('ktvtc.admin.fee-payments.index', compact(
            'payments',
            'totalPayments',
            'totalAmount',
            'todayAmount',
            'pendingVerification',
            'monthlyAmount',
            'monthlyCount',
            'collectionRate',
            'campuses'
        ));
    }

    /**
     * ============ CREATE PAGE ============
     */
    public function create(Request $request)
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

        return view('ktvtc.admin.fee-payments.create', compact('students', 'selectedEnrollment'));
    }

    /**
     * ============ STORE PAYMENT ============
     */
    public function store(Request $request)
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

            // Check if amount exceeds balance
            if ($request->amount > $enrollment->balance) {
                return redirect()->back()
                    ->with('error', 'Payment amount exceeds the outstanding balance of KES ' . number_format($enrollment->balance, 2))
                    ->withInput();
            }

            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

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
                'is_verified' => false,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
                'import_source' => $request->import_source ?? 'manual',
            ]);

            // Update enrollment balance
            $enrollment->amount_paid += $request->amount;
            $enrollment->balance = $enrollment->total_course_fee - $enrollment->amount_paid;
            $enrollment->save();

            DB::commit();

            return redirect()->route('admin.fee-payments.show', $payment)
                ->with('success', 'Payment recorded successfully. Receipt: ' . $receiptNumber);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW PAYMENT ============
     */
    public function show(FeePayment $feePayment)
    {
        $feePayment->load(['student', 'enrollment.course', 'verifier', 'recorder']);

        return view('ktvtc.admin.fee-payments.show', compact('feePayment'));
    }

    /**
     * ============ DESTROY PAYMENT ============
     */
    public function destroy(FeePayment $feePayment)
    {
        if ($feePayment->is_verified) {
            return redirect()->back()
                ->with('error', 'Cannot delete a verified payment. Reverse it instead.');
        }

        DB::beginTransaction();

        try {
            // Reverse the payment from enrollment
            $enrollment = $feePayment->enrollment;
            $enrollment->amount_paid -= $feePayment->amount;
            $enrollment->balance = $enrollment->total_course_fee - $enrollment->amount_paid;
            $enrollment->save();

            // Delete payment
            $feePayment->delete();

            DB::commit();

            return redirect()->route('admin.fee-payments.index')
                ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * ============ VERIFY PAYMENT ============
     */
    public function verify(Request $request, FeePayment $feePayment)
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        if ($feePayment->is_verified) {
            return redirect()->back()->with('error', 'Payment is already verified.');
        }

        $feePayment->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Payment verified successfully.');
    }

    /**
     * ============ REVERSE PAYMENT ============
     */
    public function reverse(Request $request, FeePayment $feePayment)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($feePayment->status == 'reversed') {
            return redirect()->back()->with('error', 'Payment is already reversed.');
        }

        DB::beginTransaction();

        try {
            // Reverse from enrollment
            $enrollment = $feePayment->enrollment;
            $enrollment->amount_paid -= $feePayment->amount;
            $enrollment->balance = $enrollment->total_course_fee - $enrollment->amount_paid;
            $enrollment->save();

            // Update payment
            $feePayment->update([
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
     * ============ RECEIPT ============
     */
    public function receipt(FeePayment $feePayment)
    {
        $feePayment->load(['student', 'enrollment.course']);

        return view('ktvtc.admin.fee-payments.receipt', compact('feePayment'));
    }

    /**
     * ============ BULK VERIFY ============
     */
    public function bulkVerify(Request $request)
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
     * ============ DAILY REPORT ============
     */

public function dailyReport(Request $request)
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
    $pendingCount = FeePayment::whereDate('payment_date', $date)
        ->where('status', 'completed')
        ->where('is_verified', false)
        ->count();

    // Hourly breakdown
    $hourlyData = array_fill(0, 24, 0);
    $hourlyLabels = [];
    foreach ($transactions as $t) {
        $hour = (int)$t->created_at->format('H');
        $hourlyData[$hour] += $t->amount;
    }
    for ($i = 0; $i < 24; $i++) {
        $hourlyLabels[] = $i . ':00';
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

    // Campus breakdown
    $campusBreakdown = [];
    $campuses = $user->role == 2 ? Campus::all() : Campus::where('id', $user->campus_id)->get();
    foreach ($campuses as $campus) {
        $campusTransactions = $transactions->filter(function ($t) use ($campus) {
            return $t->enrollment->campus_id == $campus->id;
        });
        $total = $campusTransactions->sum('amount');
        $campusBreakdown[$campus->name] = [
            'total' => $total,
            'count' => $campusTransactions->count(),
            'percentage' => $totalCollected > 0 ? round(($total / $totalCollected) * 100, 1) : 0,
        ];
    }

    // Campuses for filter
    $filterCampuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];

    return view('ktvtc.admin.fee-payments.reports.daily', compact(
        'date',                 // Changed from 'selectedDate' => $date
        'transactions',
        'totalCollected',
        'transactionCount',
        'averageTransaction',
        'pendingCount',
        'hourlyData',
        'hourlyLabels',
        'methodBreakdown',
        'campusBreakdown',
        'filterCampuses'        // Changed from 'campuses' => $filterCampuses
    ));
}

    /**
     * ============ MONTHLY REPORT ============
     */
/**
 * ============ MONTHLY REPORT ============
 */
public function monthlyReport(Request $request)
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
    $averagePerDay = $payments->groupBy(function ($p) {
        return $p->payment_date->format('Y-m-d');
    })->map->sum('amount')->average() ?? 0;

    // Best day
    $dailyTotals = $payments->groupBy(function ($p) {
        return $p->payment_date->format('Y-m-d');
    })->map->sum('amount');
    $bestDayAmount = $dailyTotals->max();
    $bestDayDate = $dailyTotals->search($bestDayAmount)
        ? Carbon::parse($dailyTotals->search($bestDayAmount))->format('M j, Y')
        : 'N/A';

    // Daily breakdown
    $dailyLabels = [];
    $dailyData = [];
    $dailyBreakdown = [];

    for ($day = 1; $day <= $startDate->daysInMonth; $day++) {
        $date = Carbon::create($year, $month, $day);
        $dailyLabels[] = $date->format('M j');

        $dayPayments = $payments->filter(function ($p) use ($date) {
            return $p->payment_date->format('Y-m-d') == $date->format('Y-m-d');
        });

        $dayTotal = $dayPayments->sum('amount');
        $dailyData[] = $dayTotal;

        $dailyBreakdown[] = [
            'date' => $date,
            'count' => $dayPayments->count(),
            'total' => $dayTotal,
            'cash' => $dayPayments->where('payment_method', 'cash')->sum('amount'),
            'mpesa' => $dayPayments->where('payment_method', 'mpesa')->sum('amount'),
            'bank' => $dayPayments->where('payment_method', 'bank')->sum('amount'),
            'kcb' => $dayPayments->where('payment_method', 'kcb')->sum('amount'),
        ];
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

    // Top courses
    $courseSummary = [];
    $courseGroups = $payments->groupBy(function ($p) {
        return $p->enrollment->course_id;
    });
    foreach ($courseGroups as $courseId => $items) {
        $course = Course::find($courseId);
        if ($course) {
            $total = $items->sum('amount');
            $courseSummary[] = [
                'name' => $course->name,
                'total' => $total,
                'count' => $items->count(),
                'percentage' => $totalCollected > 0 ? round(($total / $totalCollected) * 100, 1) : 0,
            ];
        }
    }
    $topCourses = collect($courseSummary)->sortByDesc('total')->take(5)->values()->toArray();

    // Campuses for filter
    $campuses = $user->role == 2 ? Campus::orderBy('name')->get() : [];
    $monthName = $startDate->format('F');

    return view('ktvtc.admin.fee-payments.reports.monthly', compact(
        'month',
        'year',
        'monthName',
        'totalCollected',
        'transactionCount',
        'averagePerDay',
        'bestDayAmount',
        'bestDayDate',
        'dailyLabels',
        'dailyData',
        'dailyBreakdown',
        'monthlyLabels',
        'monthlyData',
        'methodSummary',
        'topCourses',
        'campuses'
    ));
}

    /**
     * ============ API: Get By Student ============
     */
    public function getByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $enrollments = Enrollment::with('course')
            ->where('student_id', $request->student_id)
            ->where('balance', '>', 0)
            ->where('is_active', true)
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'course' => $e->course->name,
                    'balance' => $e->balance,
                    'intake' => $e->intake_period . ' ' . $e->intake_year,
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * ============ API: Get Today Stats ============
     */
    public function getTodayStats(Request $request)
    {
        $user = Auth::user();
        $days = $request->get('days', 7);

        $query = FeePayment::where('status', 'completed')
            ->whereDate('payment_date', '>=', now()->subDays($days));

        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        $dailyTotals = $query->get()
            ->groupBy(function ($p) {
                return $p->payment_date->format('Y-m-d');
            })
            ->map->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'daily_totals' => $dailyTotals,
            ]
        ]);
    }

    /**
     * ============ EXPORT ============
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = FeePayment::with(['student', 'enrollment.course', 'enrollment.campus'])
            ->where('status', 'completed')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function ($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            });

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

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        $payments = $query->orderBy('payment_date')->get();

        // Generate CSV
        $filename = 'payments_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Headers
        fputcsv($handle, [
            'Receipt Number',
            'Date',
            'Student Name',
            'Student Number',
            'Course',
            'Intake',
            'Campus',
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
                ($payment->enrollment->intake_period ?? '') . ' ' . ($payment->enrollment->intake_year ?? ''),
                $payment->enrollment->campus->name ?? 'N/A',
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
     * ============ M-PESA CALLBACK ============
     */
    public function mpesaCallback(Request $request)
    {
        // Process M-Pesa callback
        // This would handle automatic payment recording from M-Pesa API
        // For now, just log or return success
        return response()->json(['success' => true]);
    }

    /**
     * ============ HELPER METHODS ============
     */
    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');

        $last = FeePayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return sprintf('RCT-%s%s-%04d', $year, $month, $last + 1);
    }
}
