<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use App\Models\AcademicTerm;
use App\Models\CourseFeeTemplate;
use App\Models\EnrollmentFeeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * ============ ADMIN INDEX ============
     */
    public function index (Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::with(['student', 'course', 'campus', 'academicTerm'])
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

        if ($request->filled('intake_period')) {
            $query->where('intake_period', $request->intake_period);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('fee_structure_type')) {
            $query->where('fee_structure_type', $request->fee_structure_type);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
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

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
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
                $q->where('enrollment_number', 'like', "%{$search}%")
                  ->orWhere('legacy_enrollment_code', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('student_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('course', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics for cards
        $totalEnrollments = (clone $query)->count();
        $activeEnrollments = (clone $query)->where('status', 'in_progress')->count();
        $completedEnrollments = (clone $query)->where('status', 'completed')->count();
        $pendingPayment = (clone $query)->where('balance', '>', 0)->count();
        $requiresExamRegistration = (clone $query)->where('requires_external_exam', true)
            ->whereNull('exam_registration_number')
            ->count();

        // Status breakdown for chart
        $statusBreakdown = [
            'registered' => (clone $query)->where('status', 'registered')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'dropped' => (clone $query)->where('status', 'dropped')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'deferred' => (clone $query)->where('status', 'deferred')->count(),
        ];

        // Fee structure breakdown
        $feeStructureBreakdown = [
            'nita' => (clone $query)->where('fee_structure_type', 'nita')->count(),
            'cdacc' => (clone $query)->where('fee_structure_type', 'cdacc')->count(),
            'school_assessment' => (clone $query)->where('fee_structure_type', 'school_assessment')->count(),
            'mixed' => (clone $query)->where('fee_structure_type', 'mixed')->count(),
        ];

        // Intake breakdown
        $currentYear = date('Y');
        $intakeBreakdown = [
            'Jan' => (clone $query)->where('intake_period', 'Jan')->where('intake_year', $currentYear)->count(),
            'May' => (clone $query)->where('intake_period', 'May')->where('intake_year', $currentYear)->count(),
            'Sept' => (clone $query)->where('intake_period', 'Sept')->where('intake_year', $currentYear)->count(),
        ];

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter dropdown data
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $courses = Course::orderBy('name')->get();
        $academicTerms = AcademicTerm::where('is_active', true)->orderBy('start_date', 'desc')->get();

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $intakeYears = range(date('Y') - 2, date('Y') + 1);
        $examTypes = ['nita', 'cdacc', 'school_assessment', 'mixed'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $statuses = ['registered', 'in_progress', 'completed', 'dropped', 'discontinued', 'suspended', 'deferred', 'transferred'];

        return view('ktvtc.admin.enrollments.index', compact(
            'user',
            'enrollments',
            'campuses',
            'courses',
            'academicTerms',
            'intakePeriods',
            'intakeYears',
            'examTypes',
            'studentTypes',
            'sponsorshipTypes',
            'statuses',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingPayment',
            'requiresExamRegistration',
            'statusBreakdown',
            'feeStructureBreakdown',
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
            ->whereIn('status', ['active', 'historical', 'prospective'])
            ->orderBy('first_name')
            ->get();

        // Get courses
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        // Get fee templates for quick selection
        $feeTemplates = CourseFeeTemplate::with('course')
            ->where('is_active', true)
            ->get()
            ->groupBy('course_id');

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $intakeYear = date('Y');
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $feeStructureTypes = ['nita', 'cdacc', 'school_assessment', 'mixed'];

        return view('ktvtc.admin.enrollments.create', compact(
            'students',
            'courses',
            'campuses',
            'feeTemplates',
            'intakePeriods',
            'intakeYear',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'feeStructureTypes'
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
            'department_id' => 'nullable|exists:departments,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'intake_period' => 'required|in:Jan,May,Sept',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'expected_duration_months' => 'nullable|integer|min:1',
            'number_of_terms' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:registered,in_progress,completed,dropped,discontinued,suspended,deferred,transferred',
            'fee_structure_type' => 'nullable|in:nita,cdacc,school_assessment,mixed',
            'total_course_fee' => 'nullable|numeric|min:0',
            'requires_external_exam' => 'boolean',
            'external_exam_body' => 'required_if:requires_external_exam,true|nullable|string|max:100',
            'enrollment_date' => 'required|date',
            'use_template' => 'nullable|exists:course_fee_templates,id',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['use_template']);

            // Generate enrollment number
            $data['enrollment_number'] = $this->generateEnrollmentNumber($request->intake_year);

            // Set default status if not provided
            if (empty($data['status'])) {
                $data['status'] = 'registered';
            }

            // Set enrollment date if not provided
            if (empty($data['enrollment_date'])) {
                $data['enrollment_date'] = now();
            }

            // Set default values
            $data['is_active'] = true;
            $data['balance'] = $data['total_course_fee'] ?? 0;
            $data['amount_paid'] = 0;
            $data['completion_percentage'] = 0;

            // Create enrollment
            $enrollment = Enrollment::create($data);

            // If using a fee template, create fee items
            if ($request->filled('use_template')) {
                $this->applyFeeTemplate($enrollment, $request->use_template);
            }

            // Update student status if this is their first enrollment
            $student = Student::find($request->student_id);
            if ($student->status === 'prospective' || $student->status === 'historical') {
                $student->update(['status' => 'active']);
            }

            DB::commit();

            return redirect()->route('admin.tvet.enrollments.show', $enrollment)
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
        $enrollment->load([
            'student',
            'course',
            'campus',
            'department',
            'academicTerm',
            'feeTemplate',
            'feeItems.feeCategory'
        ]);

        return view('ktvtc.admin.enrollments.show', compact('enrollment'));
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
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $statuses = ['registered', 'in_progress', 'completed', 'dropped', 'discontinued', 'suspended', 'deferred', 'transferred'];
        $feeStructureTypes = ['nita', 'cdacc', 'school_assessment', 'mixed'];

        return view('ktvtc.admin.enrollments.edit', compact(
            'enrollment',
            'students',
            'courses',
            'campuses',
            'intakePeriods',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'statuses',
            'feeStructureTypes'
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
            'department_id' => 'nullable|exists:departments,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'intake_period' => 'required|in:Jan,May,Sept',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'status' => 'required|in:registered,in_progress,completed,dropped,discontinued,suspended,deferred,transferred',
            'expected_duration_months' => 'nullable|integer|min:1',
            'number_of_terms' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'total_course_fee' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'fee_structure_type' => 'nullable|in:nita,cdacc,school_assessment,mixed',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'requires_external_exam' => 'boolean',
            'external_exam_body' => 'required_if:requires_external_exam,true|nullable|string|max:100',
            'exam_registration_number' => 'nullable|string|max:50',
            'exam_registration_date' => 'nullable|date',
            'final_grade' => 'nullable|string|max:10',
            'certificate_number' => 'nullable|string|max:50',
            'certificate_issue_date' => 'nullable|date',
            'class_award' => 'nullable|string|max:50',
            'is_active' => 'boolean',
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
            if (isset($data['total_course_fee']) || isset($data['amount_paid'])) {
                $totalFee = $data['total_course_fee'] ?? $enrollment->total_course_fee;
                $amountPaid = $data['amount_paid'] ?? $enrollment->amount_paid;
                $data['balance'] = $totalFee - $amountPaid;
            }

            $enrollment->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.enrollments.show', $enrollment)
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
            $enrollmentNumber = $enrollment->enrollment_number;
            $enrollment->delete();

            DB::commit();

            return redirect()->route('admin.tvet.enrollments.index')
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
            'status' => 'in_progress',
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
            'completion_percentage' => 100,
            'actual_end_date' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment marked as completed.');
    }

    public function defer(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'new_intake_period' => 'required|in:Jan,May,Sept',
            'new_intake_year' => 'required|integer|min:' . date('Y'),
            'defer_reason' => 'required|string|max:500'
        ]);

        $enrollment->update([
            'status' => 'deferred',
            'is_active' => false,
            'deferred_to_period' => $request->new_intake_period,
            'deferred_to_year' => $request->new_intake_year,
            'defer_reason' => $request->defer_reason,
            'deferred_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment deferred successfully.');
    }

    /**
     * ============ EXAM REGISTRATION ============
     */
    public function registerExam(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'exam_registration_number' => 'required|string|max:50',
            'exam_registration_date' => 'required|date',
            'external_exam_body' => 'required|string|max:100'
        ]);

        $enrollment->update([
            'exam_registration_number' => $request->exam_registration_number,
            'exam_registration_date' => $request->exam_registration_date,
            'external_exam_body' => $request->external_exam_body,
            'requires_external_exam' => true
        ]);

        return redirect()->back()
            ->with('success', 'Exam registration recorded successfully.');
    }

    /**
     * ============ CERTIFICATE ISSUANCE ============
     */
    public function issueCertificate(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'certificate_number' => 'required|string|max:50',
            'certificate_issue_date' => 'required|date',
            'final_grade' => 'nullable|string|max:10',
            'class_award' => 'nullable|string|max:50'
        ]);

        $enrollment->update([
            'certificate_number' => $request->certificate_number,
            'certificate_issue_date' => $request->certificate_issue_date,
            'final_grade' => $request->final_grade,
            'class_award' => $request->class_award,
            'status' => 'completed',
            'completion_percentage' => 100,
            'actual_end_date' => $request->certificate_issue_date
        ]);

        return redirect()->back()
            ->with('success', 'Certificate issued successfully.');
    }

    /**
     * ============ BULK ACTIONS ============
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id'
        ]);

        $count = Enrollment::whereIn('id', $request->enrollment_ids)
            ->update([
                'status' => 'in_progress',
                'is_active' => true
            ]);

        return redirect()->back()
            ->with('success', "{$count} enrollments activated successfully.");
    }

    public function bulkComplete(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id'
        ]);

        $count = Enrollment::whereIn('id', $request->enrollment_ids)
            ->update([
                'status' => 'completed',
                'is_active' => false,
                'completion_percentage' => 100,
                'actual_end_date' => now()
            ]);

        return redirect()->back()
            ->with('success', "{$count} enrollments marked as completed.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id'
        ]);

        $count = Enrollment::whereIn('id', $request->enrollment_ids)->count();
        Enrollment::whereIn('id', $request->enrollment_ids)->delete();

        return redirect()->back()
            ->with('success', "{$count} enrollments deleted successfully.");
    }

    /**
     * ============ REPORTS ============
     */
    public function enrollmentReport(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::query()
            ->select(
                DB::raw('DATE(enrollment_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_course_fee) as total_fees'),
                DB::raw('AVG(total_course_fee) as average_fee')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->date_to);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        $enrollmentData = $query->get();

        $totalEnrollments = (clone $query)->count();
        $totalFees = (clone $query)->sum('total_course_fee');
        $averageFee = (clone $query)->avg('total_course_fee');

        $courseBreakdown = Enrollment::select('course_id', DB::raw('COUNT(*) as count'))
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                return $q->whereDate('enrollment_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                return $q->whereDate('enrollment_date', '<=', $request->date_to);
            })
            ->with('course')
            ->groupBy('course_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $courses = Course::orderBy('name')->get();

        return view('ktvtc.admin.enrollments.reports.enrollment', compact(
            'enrollmentData',
            'totalEnrollments',
            'totalFees',
            'averageFee',
            'courseBreakdown',
            'campuses',
            'courses'
        ));
    }
/**
 * Add fee item to enrollment
 */
public function addFeeItem(Request $request, Enrollment $enrollment)
{
    $request->validate([
        'fee_category_id' => 'required|exists:fee_categories,id',
        'item_name' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'quantity' => 'nullable|integer|min:1',
        'applicable_terms' => 'nullable|string',
        'term_number' => 'nullable|integer|between:1,4',
        'due_date' => 'nullable|date',
        'is_required' => 'boolean',
        'is_refundable' => 'boolean',
    ]);

    $quantity = $request->quantity ?? 1;
    $totalAmount = $request->amount * $quantity;

    $feeItem = $enrollment->feeItems()->create([
        'fee_category_id' => $request->fee_category_id,
        'item_name' => $request->item_name,
        'description' => $request->description,
        'amount' => $request->amount,
        'quantity' => $quantity,
        'total_amount' => $totalAmount,
        'amount_paid' => 0,
        'balance' => $totalAmount,
        'applicable_terms' => $request->applicable_terms,
        'term_number' => $request->term_number,
        'due_date' => $request->due_date,
        'is_required' => $request->is_required ?? true,
        'is_refundable' => $request->is_refundable ?? false,
        'due_day_offset' => $request->due_day_offset ?? 0,
        'status' => 'pending',
        'sort_order' => $enrollment->feeItems()->count() + 1,
        'created_by' => auth()->id(),
    ]);

    // Recalculate enrollment totals
    $this->recalculateEnrollmentTotals($enrollment);

    return redirect()->back()->with('success', 'Fee item added successfully.');
}

/**
 * Update fee item
 */
public function updateFeeItem(Request $request, Enrollment $enrollment, EnrollmentFeeItem $feeItem)
{
    $request->validate([
        'amount' => 'required|numeric|min:0',
        'quantity' => 'nullable|integer|min:1',
        'due_date' => 'nullable|date',
        'is_required' => 'boolean',
    ]);

    $quantity = $request->quantity ?? 1;
    $totalAmount = $request->amount * $quantity;
    $balance = $totalAmount - $feeItem->amount_paid;

    $feeItem->update([
        'amount' => $request->amount,
        'quantity' => $quantity,
        'total_amount' => $totalAmount,
        'balance' => $balance,
        'due_date' => $request->due_date,
        'is_required' => $request->is_required ?? true,
        'updated_by' => auth()->id(),
    ]);

    // Update status based on balance
    if ($feeItem->balance <= 0) {
        $feeItem->update(['status' => 'paid']);
    } elseif ($feeItem->amount_paid > 0) {
        $feeItem->update(['status' => 'partially_paid']);
    }

    // Recalculate enrollment totals
    $this->recalculateEnrollmentTotals($enrollment);

    return redirect()->back()->with('success', 'Fee item updated successfully.');
}

/**
 * Delete fee item
 */
public function deleteFeeItem(Enrollment $enrollment, EnrollmentFeeItem $feeItem)
{
    // Can only delete if no payment has been made
    if ($feeItem->amount_paid > 0) {
        return redirect()->back()->with('error', 'Cannot delete fee item with payments. Cancel it instead.');
    }

    $feeItem->delete();

    // Recalculate enrollment totals
    $this->recalculateEnrollmentTotals($enrollment);

    return redirect()->back()->with('success', 'Fee item deleted successfully.');
}

/**
 * Mark fee item as paid
 */
public function markFeeItemPaid(Request $request, Enrollment $enrollment, EnrollmentFeeItem $feeItem)
{
    $request->validate([
        'amount' => 'required|numeric|min:0|max:' . $feeItem->balance,
        'payment_date' => 'required|date',
        'payment_method' => 'required|in:cash,mpesa,bank,cheque',
        'reference' => 'nullable|string|max:100',
    ]);

    $newPaid = $feeItem->amount_paid + $request->amount;
    $balance = $feeItem->total_amount - $newPaid;

    $feeItem->update([
        'amount_paid' => $newPaid,
        'balance' => $balance,
        'status' => $balance <= 0 ? 'paid' : 'partially_paid',
        'updated_by' => auth()->id(),
    ]);

    // TODO: Create payment record
    // Payment::create([...]);

    // Recalculate enrollment totals
    $this->recalculateEnrollmentTotals($enrollment);

    return redirect()->back()->with('success', 'Payment recorded successfully.');
}

/**
 * Waive fee item
 */
public function waiveFeeItem(Enrollment $enrollment, EnrollmentFeeItem $feeItem)
{
    if ($feeItem->amount_paid > 0) {
        return redirect()->back()->with('error', 'Cannot waive fee item with existing payments.');
    }

    $feeItem->update([
        'status' => 'waived',
        'balance' => 0,
        'updated_by' => auth()->id(),
    ]);

    // Recalculate enrollment totals
    $this->recalculateEnrollmentTotals($enrollment);

    return redirect()->back()->with('success', 'Fee item waived successfully.');
}

/**
 * Recalculate enrollment totals from all fee items
 */
private function recalculateEnrollmentTotals(Enrollment $enrollment)
{
    $totalFee = $enrollment->feeItems()->sum('total_amount');
    $totalPaid = $enrollment->feeItems()->sum('amount_paid');
    $balance = $totalFee - $totalPaid;

    $enrollment->update([
        'total_course_fee' => $totalFee,
        'amount_paid' => $totalPaid,
        'balance' => $balance,
    ]);

    return $enrollment;
}
    public function financialReport(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::query()
            ->select(
                DB::raw('DATE(enrollment_date) as date'),
                DB::raw('SUM(total_course_fee) as total_billed'),
                DB::raw('SUM(amount_paid) as total_paid'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->date_to);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        $financialData = $query->get();

        $totalBilled = (clone $query)->sum('total_course_fee');
        $totalPaid = (clone $query)->sum('amount_paid');
        $totalBalance = (clone $query)->sum('balance');
        $collectionRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 2) : 0;

        $paymentMethodBreakdown = [
            'cash' => 0, // Would come from payments table
            'mpesa' => 0,
            'bank' => 0,
            'cheque' => 0,
        ];

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        return view('ktvtc.admin.enrollments.reports.financial', compact(
            'financialData',
            'totalBilled',
            'totalPaid',
            'totalBalance',
            'collectionRate',
            'paymentMethodBreakdown',
            'campuses'
        ));
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $enrollments = Enrollment::with(['course', 'academicTerm'])
            ->where('student_id', $request->student_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($enrollments);
    }

    public function getFeeTemplates(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_type' => 'nullable|in:nita,cdacc,school_assessment,mixed'
        ]);

        $query = CourseFeeTemplate::with('feeItems.feeCategory')
            ->where('course_id', $request->course_id)
            ->where('is_active', true);

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        $templates = $query->get();

        return response()->json($templates);
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

    private function applyFeeTemplate(Enrollment $enrollment, $templateId)
    {
        $template = CourseFeeTemplate::with('feeItems')->findOrFail($templateId);

        // Update enrollment with template info
        $enrollment->update([
            'fee_structure_type' => $template->exam_type,
            'total_course_fee' => $template->total_amount,
            'total_terms' => $template->total_terms,
            'duration_months' => $template->duration_months,
            'fee_template_id' => $template->id,
            'balance' => $template->total_amount
        ]);

        // Create fee items for this enrollment
        foreach ($template->feeItems as $item) {
            $enrollment->feeItems()->create([
                'fee_category_id' => $item->fee_category_id,
                'fee_template_item_id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'amount' => $item->amount,
                'quantity' => $item->quantity,
                'total_amount' => $item->amount * $item->quantity,
                'applicable_terms' => $item->applicable_terms,
                'is_required' => $item->is_required,
                'is_refundable' => $item->is_refundable,
                'due_day_offset' => $item->due_day_offset,
                'is_advance_payment' => $item->is_advance_payment,
                'sort_order' => $item->sort_order,
            ]);
        }

        return $enrollment;
    }
}
