<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Course;
use App\Models\ExamRegistration;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    /**
     * Analytics Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        // Get date range based on filter
        $range = $request->get('range', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($range == 'custom' && $startDate && $endDate) {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
        } else {
            switch ($range) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->startOfQuarter();
                    $endDate = Carbon::now()->endOfQuarter();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                default:
                    $startDate = Carbon::now()->subDays(30);
                    $endDate = Carbon::now();
            }
        }

        // Get chart period for enrollment trend
        $chartPeriod = $request->get('chart_period', '12months');

        // ============ STUDENT STATISTICS ============
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $graduatedStudents = Student::where('status', 'graduated')->count();
        $droppedStudents = Student::where('status', 'dropped')->count();

        // New students this month
        $newStudentsThisMonth = Student::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // ============ ENROLLMENT STATISTICS ============
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $pendingEnrollments = Enrollment::where('status', 'pending')->count();

        $activeEnrollmentsPercentage = $totalEnrollments > 0 ? round(($activeEnrollments / $totalEnrollments) * 100, 1) : 0;

        // Enrollments this year
        $enrollmentsThisYear = Enrollment::whereYear('created_at', Carbon::now()->year)->count();

        // ============ REVENUE STATISTICS ============
        // Get date range for revenue
        $revenueQuery = FeePayment::where('status', 'completed');

        // Apply campus filter if not admin
        if ($user->role != 2) {
            $revenueQuery->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        $totalRevenue = (clone $revenueQuery)->sum('amount');
        $totalPaid = (clone $revenueQuery)->sum('amount');

        // Outstanding balance from enrollments
        $outstandingBalance = Enrollment::sum(DB::raw('total_fees - amount_paid'));

        // Collection rate
        $totalFees = Enrollment::sum('total_fees');
        $collectionRate = $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 1) : 0;
        $defaultRate = max(0, 100 - $collectionRate);

        // ============ EXAM REGISTRATION STATISTICS ============
        $totalExamRegistrations = ExamRegistration::count();
        $pendingExamRegistrations = ExamRegistration::where('status', 'pending')->count();
        $registeredExams = ExamRegistration::where('status', 'registered')->count();
        $completedExams = ExamRegistration::where('status', 'completed')->count();
        $upcomingExams = ExamRegistration::where('exam_date', '>=', Carbon::now())
            ->whereIn('status', ['registered', 'active'])
            ->count();

        // Exam body breakdown
        $examBodyBreakdown = ExamRegistration::select('exam_body', DB::raw('count(*) as count'))
            ->groupBy('exam_body')
            ->get();

        // ============ ENROLLMENT TREND (Monthly) ============
        $monthsToShow = $chartPeriod == '6months' ? 6 : ($chartPeriod == '24months' ? 24 : 12);
        $chartLabels = [];
        $enrollmentTrend = [];

        for ($i = $monthsToShow - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $chartLabels[] = $month->format('M Y');

            $count = Enrollment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $enrollmentTrend[] = $count;
        }

        // ============ PAYMENT METHODS BREAKDOWN ============
        $paymentMethods = FeePayment::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        // ============ POPULAR COURSES ============
        $popularCourses = Enrollment::select('course_name', DB::raw('count(*) as total'))
            ->groupBy('course_name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // ============ ENROLLMENT STATUS BREAKDOWN ============
        $enrollmentStatuses = Enrollment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // ============ RECENT PAYMENTS ============
        $recentPayments = FeePayment::with(['student', 'enrollment'])
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        // ============ AGE RANGES (if student has date_of_birth field) ============
        $ageRanges = [
            'under_18' => 0,
            '18_25' => 0,
            '26_35' => 0,
            '36_45' => 0,
            '46_plus' => 0,
        ];

        // Check if students have date_of_birth column
        if (Schema::hasColumn('students', 'date_of_birth')) {
            $students = Student::all();
            foreach ($students as $student) {
                if ($student->date_of_birth) {
                    $age = Carbon::parse($student->date_of_birth)->age;
                    if ($age < 18) $ageRanges['under_18']++;
                    elseif ($age <= 25) $ageRanges['18_25']++;
                    elseif ($age <= 35) $ageRanges['26_35']++;
                    elseif ($age <= 45) $ageRanges['36_45']++;
                    else $ageRanges['46_plus']++;
                }
            }
        }

        // ============ GENDER DISTRIBUTION (if student has gender field) ============
        $maleStudents = 0;
        $femaleStudents = 0;
        $otherGender = 0;

        if (Schema::hasColumn('students', 'gender')) {
            $maleStudents = Student::where('gender', 'male')->count();
            $femaleStudents = Student::where('gender', 'female')->count();
            $otherGender = Student::whereNotIn('gender', ['male', 'female'])->count();
        }

        // ============ STUDENT STATUS BREAKDOWN ============
        $studentStatuses = Student::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('ktvtc.admin.analytics.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'graduatedStudents',
            'droppedStudents',
            'newStudentsThisMonth',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingEnrollments',
            'activeEnrollmentsPercentage',
            'enrollmentsThisYear',
            'totalRevenue',
            'totalPaid',
            'outstandingBalance',
            'collectionRate',
            'defaultRate',
            'totalExamRegistrations',
            'pendingExamRegistrations',
            'registeredExams',
            'completedExams',
            'upcomingExams',
            'examBodyBreakdown',
            'chartLabels',
            'enrollmentTrend',
            'chartPeriod',
            'paymentMethods',
            'popularCourses',
            'enrollmentStatuses',
            'recentPayments',
            'ageRanges',
            'maleStudents',
            'femaleStudents',
            'otherGender',
            'studentStatuses',
            'startDate',
            'endDate',
            'range'
        ));
    }

    /**
     * Enrollment Trends API
     */
    public function enrollmentTrends(Request $request)
    {
        $months = $request->get('months', 12);
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $count = Enrollment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $data[] = $count;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Revenue Trends API
     */
    public function revenueTrends(Request $request)
    {
        $user = auth()->user();
        $months = $request->get('months', 12);
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $query = FeePayment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed');

            if ($user->role != 2) {
                $query->whereHas('enrollment', function ($q) use ($user) {
                    $q->where('campus_id', $user->campus_id);
                });
            }

            $total = $query->sum('amount');
            $data[] = $total;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Export Analytics
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'payments');
        $format = $request->get('format', 'csv');

        if ($type == 'enrollments') {
            $data = Enrollment::with(['student', 'course'])->get();

            $filename = 'enrollments_export_' . Carbon::now()->format('Y-m-d') . '.csv';
            $headers = ['ID', 'Student Name', 'Student Number', 'Course', 'Intake', 'Status', 'Total Fees', 'Amount Paid', 'Balance'];

            $rows = $data->map(function ($item) {
                return [
                    $item->id,
                    $item->student_name,
                    $item->student_number,
                    $item->course_name,
                    $item->intake_month . ' ' . $item->intake_year,
                    $item->status,
                    $item->total_fees,
                    $item->amount_paid,
                    $item->balance,
                ];
            });
        } else {
            // Payments export
            $data = FeePayment::with(['student', 'enrollment'])
                ->where('status', 'completed')
                ->get();

            $filename = 'payments_export_' . Carbon::now()->format('Y-m-d') . '.csv';
            $headers = ['Receipt No', 'Date', 'Student Name', 'Course', 'Amount', 'Payment Method', 'Transaction Code', 'Status'];

            $rows = $data->map(function ($item) {
                return [
                    $item->receipt_number,
                    $item->payment_date->format('Y-m-d'),
                    $item->student->full_name ?? 'N/A',
                    $item->enrollment->course_name ?? 'N/A',
                    $item->amount,
                    $item->payment_method,
                    $item->transaction_code ?? '',
                    $item->status,
                ];
            });
        }

        // Generate CSV
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Student Statistics API
     */
    public function studentStats(Request $request)
    {
        $total = Student::count();
        $active = Student::where('status', 'active')->count();
        $inactive = Student::where('status', 'inactive')->count();
        $graduated = Student::where('status', 'graduated')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'graduated' => $graduated,
            ]
        ]);
    }

    /**
     * Revenue Statistics API
     */
    public function revenueStats(Request $request)
    {
        $user = auth()->user();

        $query = FeePayment::where('status', 'completed');

        if ($user->role != 2) {
            $query->whereHas('enrollment', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            });
        }

        $total = (clone $query)->sum('amount');
        $thisMonth = (clone $query)->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
        $today = (clone $query)->whereDate('payment_date', Carbon::today())->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'this_month' => $thisMonth,
                'today' => $today,
            ]
        ]);
    }
}
