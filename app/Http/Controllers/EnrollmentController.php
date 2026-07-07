<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\SmsService;

class EnrollmentController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display a listing of enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'course', 'campus', 'feeLockedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereRaw('total_fees <= amount_paid');
            } elseif ($request->payment_status === 'partial') {
                $query->whereRaw('total_fees > amount_paid AND amount_paid > 0');
            } elseif ($request->payment_status === 'unpaid') {
                $query->where('amount_paid', 0);
            }
        }

        if ($request->filled('exam_required')) {
            $query->where('requires_external_exam', $request->exam_required === 'yes');
        }

        // 🔒 Fee lock filter
        if ($request->filled('fee_locked')) {
            $query->where('fee_locked', $request->fee_locked === 'yes');
        }

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $pendingPayment = Enrollment::whereRaw('total_fees > amount_paid')->count();
        $requiresExamRegistration = Enrollment::where('requires_external_exam', true)->count();
        $feeLockedCount = Enrollment::where('fee_locked', true)->count();

        // Data for filters
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $courses = Course::orderBy('name')->get();
        $campuses = Campus::orderBy('name')->get();

        // Get intake years
        $intakeYears = Enrollment::select('intake_year')
            ->distinct()
            ->orderBy('intake_year', 'desc')
            ->pluck('intake_year')
            ->toArray();

        if (empty($intakeYears)) {
            $currentYear = date('Y');
            $intakeYears = range($currentYear - 5, $currentYear + 1);
        }

        return view('ktvtc.admin.enrollments.index', compact(
            'enrollments',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingPayment',
            'requiresExamRegistration',
            'feeLockedCount',
            'students',
            'courses',
            'campuses',
            'intakeYears'
        ));
    }

    /**
     * Show the form for creating a new enrollment
     */
   /**
 * Show the form for creating a new enrollment
 */
public function create()
{
    // Get students WITHOUT active enrollments
    $students = Student::where('status', 'active')
        ->whereDoesntHave('enrollments', function($q) {
            $q->where('status', 'active');
        })
        ->orderBy('first_name')
        ->get();

    // ✅ FIX: Use 'fees_breakdown' (plural) - matches the database column
    $courses = Course::select('id', 'name', 'code', 'fees_breakdown')
        ->orderBy('name')
        ->get();

    $campuses = Campus::orderBy('name')->get();

    return view('ktvtc.admin.enrollments.create', compact('students', 'courses', 'campuses'));
}

    /**
     * Store a new enrollment - Formats student number with course code and year
     * 🔒 Automatically locks fees on creation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'total_fees' => 'required|numeric|min:0',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'intake_month' => 'required|string',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,graduated,completed,dropped,suspended,pending',
            'study_mode' => 'required|string|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|string|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|string|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $student = Student::findOrFail($request->student_id);
            $course = Course::findOrFail($request->course_id);

            // Get the base student number
            $baseNumber = $student->student_number;

            // If student number is already formatted, extract the number part
            if (strpos($baseNumber, '/') !== false) {
                $parts = explode('/', $baseNumber);
                $baseNumber = $parts[1] ?? $parts[0];
            }

            // Format student number: COURSECODE/BASE_NUMBER/YEAR
            $courseCode = $course->code ?? 'STU';
            $year = $request->intake_year ?? date('Y');
            $formattedStudentNumber = strtoupper($courseCode) . '/' . $baseNumber . '/' . $year;

            // Update student with formatted number
            $student->student_number = $formattedStudentNumber;
            $student->save();

            // Prepare fee snapshot
            $feeSnapshot = [
                'total_fees' => (float) $request->total_fees,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'fee_structure' => $course->fee_breakdown ?? [],
                'fee_version' => $course->fee_version ?? 'v1.0',
                'created_at' => now()->toDateTimeString(),
                'created_by' => auth()->user()?->name ?? 'System',
                'intake_year' => $request->intake_year,
                'intake_month' => $request->intake_month,
                'study_mode' => $request->study_mode,
                'student_type' => $request->student_type,
                'sponsorship_type' => $request->sponsorship_type,
            ];

            // Create enrollment - fee_locked will be set to true automatically via model boot
            $enrollment = Enrollment::create([
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'campus_id' => $request->campus_id,
                'student_name' => $student->full_name,
                'student_number' => $formattedStudentNumber,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'department' => $course->department?->name,
                'total_fees' => $request->total_fees,
                'amount_paid' => $request->amount_paid ?? 0,
                'intake_year' => $request->intake_year,
                'intake_month' => $request->intake_month,
                'enrollment_date' => $request->enrollment_date,
                'start_date' => $request->start_date,
                'expected_end_date' => $request->expected_end_date,
                'status' => $request->status,
                'study_mode' => $request->study_mode,
                'student_type' => $request->student_type,
                'sponsorship_type' => $request->sponsorship_type,
                'duration_months' => $request->duration_months,
                'remarks' => $request->remarks,
                'is_active' => $request->status === 'active',
                // 🔒 Fee locking fields (model boot will handle these)
                'fee_locked' => true,
                'fee_locked_at' => now(),
                'fee_locked_by' => auth()->id(),
                'fee_snapshot' => $feeSnapshot,
                'fee_version_at_enrollment' => $course->fee_version ?? 'v1.0',
            ]);

            DB::commit();

            Log::info('Enrollment created with fees locked', [
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->student_name,
                'student_number' => $formattedStudentNumber,
                'total_fees' => $enrollment->total_fees,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.enrollments.index')
                ->with('success', "Enrollment created successfully for {$enrollment->student_name}. Student Number: {$formattedStudentNumber}. 🔒 Fees are locked.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('_token')
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified enrollment
     */
    public function show($id)
    {
        $enrollment = Enrollment::with([
            'student',
            'course',
            'campus',
            'payments',
            'feeLockedBy',
            'feesModifiedBy'
        ])->findOrFail($id);

        $payments = $enrollment->payments;

        // Get fee lock status
        $feeLockStatus = [
            'is_locked' => $enrollment->isFeeLocked(),
            'locked_by' => $enrollment->feeLockedBy?->name,
            'locked_at' => $enrollment->fee_locked_at?->toDateTimeString(),
            'can_modify' => $enrollment->canModifyFees(),
            'has_snapshot' => !empty($enrollment->fee_snapshot),
            'original_fees' => $enrollment->original_fees,
        ];

        return view('ktvtc.admin.enrollments.show', compact(
            'enrollment',
            'payments',
            'feeLockStatus'
        ));
    }

    /**
     * Show the form for editing the specified enrollment
     */
    public function edit($id)
    {
        $enrollment = Enrollment::with([
            'feeLockedBy',
            'feesModifiedBy'
        ])->findOrFail($id);

        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $courses = Course::orderBy('name')->get();
        $campuses = Campus::orderBy('name')->get();

        // Check if fees can be modified
        $canModifyFees = $enrollment->canModifyFees();

        return view('ktvtc.admin.enrollments.edit', compact(
            'enrollment',
            'students',
            'courses',
            'campuses',
            'canModifyFees'
        ));
    }

    /**
     * Update the specified enrollment
     * 🔒 Checks fee lock before allowing fee modifications
     */
    public function update(Request $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        // 🔒 Check if fees can be modified BEFORE validation
        $isFeeChange = $request->has('total_fees') && $request->total_fees != $enrollment->total_fees;

        if ($isFeeChange && !$enrollment->canModifyFees()) {
            $errorMessage = 'Fees cannot be modified for this enrollment.';

            if ($enrollment->isFeeLocked()) {
                $errorMessage .= ' Fees are locked.';
            }

            if (in_array($enrollment->status, ['completed', 'graduated'])) {
                $errorMessage .= ' Enrollment is ' . $enrollment->status . '.';
            }

            if ($enrollment->payments()->where('status', 'completed')->exists()) {
                $errorMessage .= ' Payments have been made.';
            }

            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'intake_month' => 'required|string',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,graduated,completed,dropped,suspended,pending',
            'study_mode' => 'required|string|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|string|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|string|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            // 🔒 Required when modifying fees
            'fee_modification_reason' => $isFeeChange ? 'required|string|min:5|max:500' : 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $student = Student::findOrFail($request->student_id);
            $course = Course::findOrFail($request->course_id);

            // Store old values for audit
            $oldTotal = $enrollment->total_fees;
            $oldStatus = $enrollment->status;
            $newTotal = $request->total_fees;

            // Prepare update data
            $updateData = [
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'campus_id' => $request->campus_id,
                'student_name' => $student->full_name,
                'student_number' => $student->student_number,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'department' => $course->department?->name,
                'amount_paid' => $request->amount_paid ?? $enrollment->amount_paid,
                'intake_year' => $request->intake_year,
                'intake_month' => $request->intake_month,
                'enrollment_date' => $request->enrollment_date,
                'start_date' => $request->start_date,
                'expected_end_date' => $request->expected_end_date,
                'status' => $request->status,
                'study_mode' => $request->study_mode,
                'student_type' => $request->student_type,
                'sponsorship_type' => $request->sponsorship_type,
                'duration_months' => $request->duration_months,
                'remarks' => $request->remarks,
                'is_active' => $request->status === 'active',
            ];

            // 🔒 Handle fee changes with audit trail
            if ($oldTotal != $newTotal) {
                $reason = $request->fee_modification_reason ?? 'Updated via enrollment edit form';
                $enrollment->updateFeesWithAudit($newTotal, $reason);
                // Update amount_paid if needed
                if ($request->has('amount_paid') && $request->amount_paid > $newTotal) {
                    $updateData['amount_paid'] = $newTotal; // Can't pay more than total
                }
            }

            // Update the enrollment (excluding total_fees which was updated above)
            $enrollment->update($updateData);

            // 🔒 If status changed to completed/graduated, ensure fees are locked
            if (in_array($request->status, ['completed', 'graduated']) && !$enrollment->isFeeLocked()) {
                $enrollment->lockFees('Auto-locked on status change to ' . $request->status);
            }

            DB::commit();

            // Log the update
            Log::info('Enrollment updated', [
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->student_name,
                'old_total' => $oldTotal,
                'new_total' => $newTotal,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updated_by' => auth()->id(),
                'fee_modified' => $oldTotal != $newTotal,
                'fee_modification_reason' => $request->fee_modification_reason
            ]);

            $message = "Enrollment updated successfully.";
            if ($oldTotal != $newTotal) {
                $message .= " Fees changed from KES " . number_format($oldTotal, 2) . " to KES " . number_format($newTotal, 2) . ".";
            }

            return redirect()->route('admin.enrollments.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Enrollment update failed', [
                'enrollment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('_token')
            ]);

            if (str_contains($e->getMessage(), 'Fees are locked')) {
                return redirect()->back()
                    ->with('error', 'Fees are locked for this enrollment and cannot be changed.')
                    ->withInput();
            }

            return redirect()->back()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete the specified enrollment
     */
    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        // Check if enrollment has payments
        if ($enrollment->payments()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete enrollment with existing payments. Please reverse payments first.');
        }

        $studentName = $enrollment->student_name;

        // Log before deletion
        Log::warning('Enrollment deleted', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'student_number' => $enrollment->student_number,
            'course' => $enrollment->course_name,
            'total_fees' => $enrollment->total_fees,
            'deleted_by' => auth()->id()
        ]);

        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')
            ->with('success', "Enrollment for {$studentName} deleted successfully.");
    }

    /**
     * Activate an enrollment
     */
    public function activate($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->status = 'active';
        $enrollment->is_active = true;
        $enrollment->save();

        Log::info('Enrollment activated', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'activated_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Enrollment activated successfully.');
    }

    /**
     * Suspend an enrollment
     */
    public function suspend($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->status = 'suspended';
        $enrollment->is_active = false;
        $enrollment->save();

        Log::info('Enrollment suspended', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'suspended_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Enrollment suspended successfully.');
    }

    /**
     * Complete an enrollment
     * 🔒 Auto-locks fees on completion
     */
    public function complete($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->status = 'completed';
        $enrollment->actual_end_date = now();
        $enrollment->is_active = false;

        // 🔒 Lock fees if not already locked
        if (!$enrollment->isFeeLocked()) {
            $enrollment->lockFees('Auto-locked on completion');
        }

        $enrollment->save();

        Log::info('Enrollment completed', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'completed_by' => auth()->id(),
            'fees_locked' => $enrollment->isFeeLocked()
        ]);

        return redirect()->back()->with('success', 'Enrollment completed successfully. 🔒 Fees are now locked.');
    }

    /**
     * Defer an enrollment
     */
    public function defer($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->status = 'deferred';
        $enrollment->is_active = false;
        $enrollment->save();

        Log::info('Enrollment deferred', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'deferred_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Enrollment deferred successfully.');
    }

    /**
     * Register for external exam
     */
    public function registerExam($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $enrollment->requires_external_exam = true;
        $enrollment->save();

        Log::info('External exam registration enabled', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'registered_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Student registered for external exam.');
    }

    /**
     * 🔒 Lock fees for an enrollment (manual lock)
     */
    public function lockFees($id, Request $request)
    {
        $enrollment = Enrollment::findOrFail($id);

        if ($enrollment->isFeeLocked()) {
            return redirect()->back()->with('info', 'Fees are already locked for this enrollment.');
        }

        $reason = $request->input('reason', 'Manually locked by admin');
        $enrollment->lockFees($reason);

        Log::warning('Fees manually locked', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'reason' => $reason,
            'locked_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', '🔒 Fees locked successfully.');
    }

    /**
     * 🔒 Unlock fees for an enrollment (admin only - with caution)
     */
    public function unlockFees($id, Request $request)
    {
        $enrollment = Enrollment::findOrFail($id);

        if (!$enrollment->isFeeLocked()) {
            return redirect()->back()->with('info', 'Fees are already unlocked for this enrollment.');
        }

        // Check if enrollment has payments
        if ($enrollment->payments()->where('status', 'completed')->exists()) {
            return redirect()->back()->with('error', 'Cannot unlock fees. Payments have been made against this enrollment.');
        }

        // Check if enrollment is completed
        if (in_array($enrollment->status, ['completed', 'graduated'])) {
            return redirect()->back()->with('error', 'Cannot unlock fees. Enrollment is ' . $enrollment->status . '.');
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $enrollment->unlockFees($request->reason);

        Log::warning('🔓 Fees manually unlocked', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'reason' => $request->reason,
            'unlocked_by' => auth()->id(),
            'ip' => $request->ip()
        ]);

        return redirect()->back()->with('success', '🔓 Fees unlocked successfully. Please ensure you have a valid reason for this action.');
    }

    /**
     * Bulk activate enrollments
     */
    public function bulkActivate(Request $request)
    {
        $ids = $request->enrollment_ids;

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No enrollments selected.');
        }

        $count = Enrollment::whereIn('id', $ids)->update([
            'status' => 'active',
            'is_active' => true
        ]);

        Log::info('Bulk activation', [
            'enrollment_ids' => $ids,
            'count' => $count,
            'activated_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', $count . ' enrollments activated.');
    }

    /**
     * Bulk complete enrollments
     * 🔒 Auto-locks fees on completion
     */
    public function bulkComplete(Request $request)
    {
        $ids = $request->enrollment_ids;

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No enrollments selected.');
        }

        $enrollments = Enrollment::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($enrollments as $enrollment) {
            $enrollment->status = 'completed';
            $enrollment->actual_end_date = now();
            $enrollment->is_active = false;

            // 🔒 Lock fees if not already locked
            if (!$enrollment->isFeeLocked()) {
                $enrollment->lockFees('Auto-locked on bulk completion');
            }

            $enrollment->save();
            $count++;
        }

        Log::info('Bulk completion', [
            'enrollment_ids' => $ids,
            'count' => $count,
            'completed_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', $count . ' enrollments completed. 🔒 Fees are now locked.');
    }

    /**
     * Bulk delete enrollments
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->enrollment_ids;

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No enrollments selected.');
        }

        // Check if any have payments
        $hasPayments = Enrollment::whereIn('id', $ids)
            ->whereHas('payments')
            ->exists();

        if ($hasPayments) {
            return redirect()->back()->with('error', 'Some enrollments have payments and cannot be deleted.');
        }

        $count = Enrollment::whereIn('id', $ids)->delete();

        Log::warning('Bulk deletion', [
            'enrollment_ids' => $ids,
            'count' => $count,
            'deleted_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', $count . ' enrollments deleted.');
    }

    /**
     * Export enrollments (placeholder)
     */
    public function export()
    {
        return redirect()->back()->with('success', 'Export functionality coming soon.');
    }

    /**
     * Enrollment report
     */
    public function enrollmentReport()
    {
        $data = Enrollment::with(['student', 'course'])->get();
        return view('ktvtc.admin.enrollments.report', compact('data'));
    }

    /**
     * Financial report
     */
    public function financialReport()
    {
        $data = Enrollment::with(['student', 'course', 'payments'])->get();
        return view('ktvtc.admin.enrollments.financial-report', compact('data'));
    }

    /**
     * API: Get enrollments by student
     */
    public function getByStudent(Request $request)
    {
        $studentId = $request->student_id;
        $enrollments = Enrollment::where('student_id', $studentId)
            ->with(['course', 'payments'])
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_name' => $enrollment->course_name,
                    'course_code' => $enrollment->course_code,
                    'status' => $enrollment->status,
                    'total_fees' => $enrollment->total_fees,
                    'amount_paid' => $enrollment->amount_paid,
                    'balance' => $enrollment->balance,
                    'payment_progress' => $enrollment->payment_progress,
                    'is_fee_locked' => $enrollment->isFeeLocked(),
                    'can_modify_fees' => $enrollment->canModifyFees(),
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * API: Get enrollment statistics
     */
    public function getStats(Request $request)
    {
        $stats = [
            'total' => Enrollment::count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'graduated' => Enrollment::where('status', 'graduated')->count(),
            'has_balance' => Enrollment::whereRaw('total_fees > amount_paid')->count(),
            'fully_paid' => Enrollment::whereRaw('total_fees <= amount_paid')->count(),
            'fee_locked' => Enrollment::where('fee_locked', true)->count(),
            'fee_unlocked' => Enrollment::where('fee_locked', false)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * API: Get students for select2 dropdown (exclude already enrolled)
     */
    public function apiStudents(Request $request)
    {
        $search = $request->get('q', '');
        $excludeEnrolled = $request->get('exclude_enrolled', true);

        $query = Student::where('status', 'active');

        // Exclude students with active enrollments
        if ($excludeEnrolled) {
            $query->whereDoesntHave('enrollments', function($q) {
                $q->where('status', 'active');
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('first_name')
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'student_number']);

        return response()->json($students->map(function($student) {
            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'student_number' => $student->student_number
            ];
        }));
    }

    /**
     * 🔒 API: Check if fees can be modified for an enrollment
     */
    public function checkFeeModifiable($id)
    {
        $enrollment = Enrollment::with(['feeLockedBy', 'feesModifiedBy'])->findOrFail($id);

        return response()->json([
            'can_modify' => $enrollment->canModifyFees(),
            'is_locked' => $enrollment->isFeeLocked(),
            'has_payments' => $enrollment->payments()->where('status', 'completed')->exists(),
            'status' => $enrollment->status,
            'fee_locked_by' => $enrollment->feeLockedBy?->name,
            'fee_locked_at' => $enrollment->fee_locked_at?->toDateTimeString(),
            'fee_snapshot' => $enrollment->fee_snapshot,
            'original_fees' => $enrollment->original_fees,
            'total_fees' => $enrollment->total_fees,
            'amount_paid' => $enrollment->amount_paid,
            'balance' => $enrollment->balance,
        ]);
    }

    /**
     * 🔒 API: Get fee lock status for multiple enrollments
     */
    public function getFeeLockStatus(Request $request)
    {
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            return response()->json([]);
        }

        $enrollments = Enrollment::whereIn('id', $ids)
            ->with(['feeLockedBy'])
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student_name,
                    'student_number' => $enrollment->student_number,
                    'is_locked' => $enrollment->isFeeLocked(),
                    'locked_by' => $enrollment->feeLockedBy?->name,
                    'locked_at' => $enrollment->fee_locked_at?->toDateTimeString(),
                    'can_modify' => $enrollment->canModifyFees(),
                ];
            });

        return response()->json($enrollments);
    }

    // ============ SMS / FEE REMINDER METHODS ============

    /**
     * Send fee reminders to selected enrollments
     * NOTE: These methods are kept but should ideally be moved to Finance module
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
     * Generate template message for fee reminder
     */
    private function generateTemplateMessage($template, $name, $balance, $enrollment)
    {
        $balanceFormatted = number_format($balance, 2);
        $link = 'www.ktvtc.ac.ke/pay';
        $course = $enrollment->course_name ?? 'course';
        $studentNumber = $enrollment->student_number ?? 'N/A';
        $totalFees = number_format($enrollment->total_fees, 2);
        $paid = number_format($enrollment->amount_paid, 2);

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
     * Parse custom message with placeholders
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
            '{status}' => $enrollment->status ?? 'active',
            '{intake}' => $enrollment->intake_month . ' ' . $enrollment->intake_year,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Send single fee reminder
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
            Log::info('Fee reminder sent', [
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->student_name,
                'phone' => $enrollment->student->phone,
                'balance' => $balance
            ]);
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

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No enrollments selected.');
        }

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

        Log::info('Bulk fee reminders sent', [
            'total' => $enrollments->count(),
            'sent' => $sent,
            'failed' => $failed,
            'sent_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', "Bulk reminders sent: {$sent} sent, {$failed} failed.");
    }

    /**
     * Get enrollments eligible for fee reminder
     */
    public function getEligibleForReminder()
    {
        $enrollments = Enrollment::whereRaw('total_fees > amount_paid')
            ->where('status', 'active')
            ->with(['student'])
            ->get(['id', 'student_id', 'student_name', 'student_number', 'total_fees', 'amount_paid', 'course_name']);

        return response()->json($enrollments);
    }

    /**
     * Update student number for an enrollment with course code and year
     */
    public function updateStudentNumber($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $student = $enrollment->student;
        $course = $enrollment->course;

        if (!$student || !$course) {
            return redirect()->back()->with('error', 'Student or course not found.');
        }

        // Get base number
        $baseNumber = $student->student_number;
        if (strpos($baseNumber, '/') !== false) {
            $parts = explode('/', $baseNumber);
            $baseNumber = $parts[1] ?? $parts[0];
        }

        // Format new number
        $courseCode = $course->code ?? 'STU';
        $year = $enrollment->intake_year ?? date('Y');
        $newStudentNumber = strtoupper($courseCode) . '/' . $baseNumber . '/' . $year;

        // Update student
        $student->student_number = $newStudentNumber;
        $student->save();

        // Update enrollment
        $enrollment->student_number = $newStudentNumber;
        $enrollment->save();

        Log::info('Student number updated', [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student_name,
            'old_number' => $enrollment->getOriginal('student_number'),
            'new_number' => $newStudentNumber,
            'updated_by' => auth()->id()
        ]);

        return redirect()->back()
            ->with('success', "Student number updated to: {$newStudentNumber}");
    }
}
