<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * ============ INDEX ============
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::with(['student', 'course', 'campus'])
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

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('student_type')) {
            $query->where('student_type', $request->student_type);
        }

        if ($request->filled('sponsorship_type')) {
            $query->where('sponsorship_type', $request->sponsorship_type);
        }

        if ($request->filled('requires_external_exam')) {
            $query->where('requires_external_exam', $request->requires_external_exam === 'yes');
        }

        if ($request->filled('exam_body')) {
            $query->where('exam_body', $request->exam_body);
        }

        if ($request->filled('enrollment_date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->enrollment_date_from);
        }

        if ($request->filled('enrollment_date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->enrollment_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%")
                  ->orWhere('legacy_code', 'like', "%{$search}%");
            });
        }

        // Statistics for cards
        $totalEnrollments = (clone $query)->count();
        $activeEnrollments = (clone $query)->where('status', 'active')->count();
        $completedEnrollments = (clone $query)->where('status', 'completed')->count();
        $pendingPayment = (clone $query)->where('balance', '>', 0)->count();

        // Uses ExamRegistration table
        $requiresExamRegistration = (clone $query)
            ->where('requires_external_exam', true)
            ->whereDoesntHave('examRegistrations', function($q) {
                $q->whereIn('status', ['registered', 'submitted', 'completed']);
            })
            ->count();

        // Status breakdown for chart
        $statusBreakdown = [
            'active' => (clone $query)->where('status', 'active')->count(),
            'graduated' => (clone $query)->where('status', 'graduated')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'dropped' => (clone $query)->where('status', 'dropped')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'deferred' => (clone $query)->where('status', 'deferred')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];

        // Exam body breakdown (using the exam_body field directly)
        $examBodyBreakdown = [];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA']; // Fixed list of exam bodies
        foreach ($examBodies as $body) {
            $examBodyBreakdown[$body] = (clone $query)
                ->where('exam_body', $body)
                ->count();
        }

        // Intake breakdown
        $currentYear = date('Y');
        $intakeBreakdown = [
            'January' => (clone $query)->where('intake_month', 'January')->where('intake_year', $currentYear)->count(),
            'May' => (clone $query)->where('intake_month', 'May')->where('intake_year', $currentYear)->count(),
            'September' => (clone $query)->where('intake_month', 'September')->where('intake_year', $currentYear)->count(),
        ];

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter dropdown data
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $courses = Course::orderBy('name')->get();

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $intakeYears = range(date('Y') - 2, date('Y') + 1);
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];
        $statuses = ['active', 'graduated', 'completed', 'dropped', 'suspended', 'deferred', 'pending'];

        return view('ktvtc.admin.enrollments.index', compact(
            'user',
            'enrollments',
            'campuses',
            'courses',
            'examBodies',
            'intakeMonths',
            'intakeYears',
            'studentTypes',
            'sponsorshipTypes',
            'statuses',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingPayment',
            'requiresExamRegistration',
            'statusBreakdown',
            'examBodyBreakdown',
            'intakeBreakdown'
        ));
    }

    /**
     * ============ CREATE FORM ============
     */
    public function create()
    {
        $user = auth()->user();

        // Get students based on role
        $students = Student::when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('first_name')
            ->get();

        // Get courses
        $courses = Course::orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $intakeYear = date('Y');
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];

        return view('ktvtc.admin.enrollments.create', compact(
            'students',
            'courses',
            'campuses',
            'intakeMonths',
            'intakeYear',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'examBodies'
        ));
    }

    /**
     * ============ STORE ============
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'student_name' => 'required|string|max:255',
            'student_number' => 'nullable|string|max:50',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'intake_month' => 'required|string|max:20',
            'enrollment_date' => 'nullable|date',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,graduated,completed,dropped,suspended,deferred,pending',
            'requires_external_exam' => 'boolean',
            'exam_body' => 'nullable|in:KNEC,CDACC,NITA,TVETA',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Calculate balance
            $data['balance'] = ($data['total_fees'] ?? 0) - ($data['amount_paid'] ?? 0);

            // Set is_active based on status
            $data['is_active'] = in_array($data['status'], ['active', 'pending']);

            // Create enrollment
            $enrollment = Enrollment::create($data);

            DB::commit();

            return redirect()->route('enrollments.show', $enrollment)
                ->with('success', 'Enrollment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW ============
     */
  public function show(Enrollment $enrollment)
{
    $enrollment->load(['student', 'course', 'campus', 'payments', 'examRegistrations']);

    // Get payments separately to use in the view
    $payments = $enrollment->payments; // This is already loaded via load('payments')

    return view('ktvtc.admin.enrollments.show', compact('enrollment', 'payments'));
}

    /**
     * ============ EDIT ============
     */
    public function edit(Enrollment $enrollment)
    {
        $user = auth()->user();

        // Get students based on role
        $students = Student::when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('first_name')
            ->get();

        // Get courses
        $courses = Course::orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];
        $statuses = ['active', 'graduated', 'completed', 'dropped', 'suspended', 'deferred', 'pending'];

        return view('ktvtc.admin.enrollments.edit', compact(
            'enrollment',
            'students',
            'courses',
            'campuses',
            'intakeMonths',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'examBodies',
            'statuses'
        ));
    }

    /**
     * ============ UPDATE ============
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'student_name' => 'required|string|max:255',
            'student_number' => 'nullable|string|max:50',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'intake_month' => 'required|string|max:20',
            'enrollment_date' => 'nullable|date',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,graduated,completed,dropped,suspended,deferred,pending',
            'requires_external_exam' => 'boolean',
            'exam_body' => 'nullable|in:KNEC,CDACC,NITA,TVETA',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Calculate balance
            $data['balance'] = ($data['total_fees'] ?? 0) - ($data['amount_paid'] ?? 0);

            // Set is_active based on status
            $data['is_active'] = in_array($data['status'], ['active', 'pending']);

            $enrollment->update($data);

            DB::commit();

            return redirect()->route('enrollments.show', $enrollment)
                ->with('success', 'Enrollment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ DESTROY ============
     */
    public function destroy(Enrollment $enrollment)
    {
        DB::beginTransaction();

        try {
            // Check if there are any payments
            if ($enrollment->payments()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete enrollment with payments.');
            }

            $enrollmentNumber = $enrollment->enrollment_number;
            $enrollment->delete();

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', "Enrollment {$enrollmentNumber} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
        }
    }

    /**
     * ============ STATUS ACTIONS ============
     */
    public function activate(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'active',
            'is_active' => true
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment activated successfully.');
    }

    public function suspend(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'suspended',
            'is_active' => false
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment suspended successfully.');
    }

    public function complete(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'completed',
            'is_active' => false,
            'actual_end_date' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment marked as completed.');
    }

    public function defer(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'deferred',
            'is_active' => false
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment deferred successfully.');
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $enrollments = Enrollment::where('student_id', $request->student_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student_name,
                    'student_number' => $enrollment->student_number,
                    'course' => $enrollment->course_name,
                    'intake' => $enrollment->intake_month . ' ' . $enrollment->intake_year,
                    'status' => $enrollment->status,
                    'total_fee' => $enrollment->total_fees,
                    'paid' => $enrollment->amount_paid,
                    'balance' => $enrollment->balance,
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * ============ HELPER METHODS ============
     */
    private function generateEnrollmentNumber($year = null)
    {
        $year = $year ?? date('Y');
        $prefix = 'ENR';

        $lastEnrollment = Enrollment::where('enrollment_number', 'LIKE', "{$prefix}/{$year}/%")
            ->orderBy('enrollment_number', 'desc')
            ->first();

        if ($lastEnrollment) {
            $parts = explode('/', $lastEnrollment->enrollment_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}/{$year}/{$newNumber}";
    }

    /**
     * ============ EXPORT ============
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::with(['student', 'course', 'campus'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'enrollments_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Add headers
        fputcsv($handle, [
            'Enrollment Number',
            'Student Name',
            'Student Number',
            'Course',
            'Intake',
            'Campus',
            'Exam Body',
            'Status',
            'Total Fee',
            'Amount Paid',
            'Balance',
            'Enrollment Date',
        ]);

        // Add data
        foreach ($enrollments as $enrollment) {
            fputcsv($handle, [
                $enrollment->enrollment_number ?? 'N/A',
                $enrollment->student_name,
                $enrollment->student_number ?? 'N/A',
                $enrollment->course_name,
                $enrollment->intake_month . ' ' . $enrollment->intake_year,
                $enrollment->campus->name ?? 'N/A',
                $enrollment->exam_body ?? 'N/A',
                ucfirst($enrollment->status),
                $enrollment->total_fees,
                $enrollment->amount_paid,
                $enrollment->balance,
                $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : 'N/A',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
