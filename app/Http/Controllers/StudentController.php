<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Campus;
use App\Models\Application;
use App\Models\Course;
use App\Models\Enrollment;     // ✅ ADD THIS
use App\Models\FeePayment;     // ✅ ADD THIS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Show student dashboard with fee information
     */
    /**
 * Show student dashboard with fee information
 */
public function dashboard()
{
    $user = auth()->user();

    // Remove the approval check - students can always access dashboard
    // $isApproved = $user->is_approved === true;

    // Initialize default values
    $totalFees = 0;
    $totalPaid = 0;
    $totalBalance = 0;
    $enrollments = collect();
    $recentPayments = collect();
    $student = null;
    $enrollmentCount = 0;  // ✅ ADD THIS

    // Get student record
    $student = $user->student;

    if ($student) {
        // Get enrollments for this student
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with(['course', 'payments'])
            ->get();

        // Calculate totals
        $totalFees = $enrollments->sum('total_fees');
        $totalPaid = $enrollments->sum('amount_paid');
        $totalBalance = $enrollments->sum('balance');
        $enrollmentCount = $enrollments->count();  // ✅ ADD THIS

        // Get recent payments
        $recentPayments = FeePayment::where('student_id', $student->id)
            ->with('enrollment.course')
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();
    }

    return view('ktvtc.students.dashboard', compact(
        'enrollments',
        'totalFees',
        'totalPaid',
        'totalBalance',
        'recentPayments',
        'student',
        'enrollmentCount'  // ✅ ADD THIS
    ));
}

    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Student::query()
            ->with(['campus', 'application'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('student_category')) {
            $query->where('student_category', $request->student_category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('legacy_student_code', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('registration_date_from')) {
            $query->whereDate('registration_date', '>=', $request->registration_date_from);
        }

        if ($request->filled('registration_date_to')) {
            $query->whereDate('registration_date', '<=', $request->registration_date_to);
        }

        if ($request->filled('requires_cleanup')) {
            $query->where('requires_cleanup', $request->requires_cleanup === 'yes');
        }

        // Statistics
        $totalStudents = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'active')->count();
        $graduatedStudents = (clone $query)->where('status', 'graduated')->count();
        $historicalStudents = (clone $query)->where('status', 'historical')->count();
        $requiresCleanup = (clone $query)->where('requires_cleanup', true)->count();

        // Status breakdown for chart
        $statusBreakdown = [
            'active' => (clone $query)->where('status', 'active')->count(),
            'inactive' => (clone $query)->where('status', 'inactive')->count(),
            'graduated' => (clone $query)->where('status', 'graduated')->count(),
            'dropped' => (clone $query)->where('status', 'dropped')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'alumnus' => (clone $query)->where('status', 'alumnus')->count(),
            'prospective' => (clone $query)->where('status', 'prospective')->count(),
            'historical' => (clone $query)->where('status', 'historical')->count(),
        ];

        $genderBreakdown = [
            'male' => (clone $query)->where('gender', 'male')->count(),
            'female' => (clone $query)->where('gender', 'female')->count(),
            'other' => (clone $query)->where('gender', 'other')->count(),
        ];

        $categoryBreakdown = [
            'regular' => (clone $query)->where('student_category', 'regular')->count(),
            'alumnus' => (clone $query)->where('student_category', 'alumnus')->count(),
            'staff_child' => (clone $query)->where('student_category', 'staff_child')->count(),
            'sponsored' => (clone $query)->where('student_category', 'sponsored')->count(),
            'scholarship' => (clone $query)->where('student_category', 'scholarship')->count(),
        ];

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        // Campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $applications = Application::orderBy('application_number')->get();

        return view('ktvtc.admin.students.index', compact(
            'user',
            'students',
            'campuses',
            'applications',
            'totalStudents',
            'activeStudents',
            'graduatedStudents',
            'historicalStudents',
            'requiresCleanup',
            'statusBreakdown',
            'genderBreakdown',
            'categoryBreakdown'
        ));
    }

    // ... keep all your existing methods (create, store, show, edit, update, destroy, etc.) ...
    // They remain unchanged from your current code

    // ============ HELPER METHODS ============

    private function generateStudentNumber()
    {
        $prefix = 'STU';
        $year = date('Y');
        $month = date('m');

        $lastStudent = Student::where('student_number', 'LIKE', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('/', $lastStudent->student_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}/{$year}/{$month}/{$newNumber}";
    }
}
