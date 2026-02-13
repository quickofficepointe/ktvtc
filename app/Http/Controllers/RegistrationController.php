<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\User;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $query = Registration::with(['student', 'campus', 'course', 'feeStructure', 'application'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('study_mode')) {
            $query->where('study_mode', $request->study_mode);
        }

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Get registrations
        $registrations = $query->paginate(20);

        // Get filter data
        $students = User::students()->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->where('is_approved', true)
            ->with(['course', 'campus'])
            ->get();

        $applications = Application::where('status', 'approved')
            ->with(['student', 'course', 'campus'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Status options
        $statuses = [
            'pending', 'provisional', 'registered', 'active',
            'behind_payment', 'completed', 'suspended', 'withdrawn'
        ];

        $studyModes = ['full_time', 'part_time', 'online'];
        $paymentPlans = ['monthly', 'quarterly', 'semester', 'annual', 'custom'];

        $academicYears = Registration::distinct()->pluck('academic_year')->sort();
        $intakeMonths = Registration::distinct()->pluck('intake_month')->sort();

        // Calculate statistics
        $totalRegistrations = Registration::count();
        $activeRegistrations = Registration::whereIn('status', ['active', 'registered'])->count();
        $pendingRegistrations = Registration::where('status', 'pending')->count();
        $completedRegistrations = Registration::where('status', 'completed')->count();
        $overdueRegistrations = Registration::overdue()->count();

        $totalFee = Registration::sum('total_course_fee');
        $totalPaid = Registration::sum('amount_paid');
        $totalBalance = Registration::sum('balance');

        return view('ktvtc.admin.registrations.index', compact(
            'registrations',
            'students',
            'campuses',
            'courses',
            'feeStructures',
            'applications',
            'statuses',
            'studyModes',
            'paymentPlans',
            'academicYears',
            'intakeMonths',
            'totalRegistrations',
            'activeRegistrations',
            'pendingRegistrations',
            'completedRegistrations',
            'overdueRegistrations',
            'totalFee',
            'totalPaid',
            'totalBalance'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = User::students()->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->where('is_approved', true)
            ->with(['course', 'campus'])
            ->get();

        $applications = Application::where('status', 'approved')
            ->with(['student', 'course', 'campus'])
            ->orderBy('created_at', 'desc')
            ->get();

        $academicYears = [
            now()->year . '/' . (now()->year + 1),
            (now()->year + 1) . '/' . (now()->year + 2),
            (now()->year + 2) . '/' . (now()->year + 3),
        ];

        $intakeMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $studyModes = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'online' => 'Online'
        ];

        $paymentPlans = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semester' => 'Semester',
            'annual' => 'Annual',
            'custom' => 'Custom'
        ];

        return view('ktvtc.admin.registrations.create', compact(
            'students',
            'campuses',
            'courses',
            'feeStructures',
            'applications',
            'academicYears',
            'intakeMonths',
            'studyModes',
            'paymentPlans'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'application_id' => 'nullable|exists:applications,id',
            'student_id' => 'required|exists:users,id',
            'campus_id' => 'required|exists:campuses,id',
            'course_id' => 'required|exists:courses,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'official_email' => 'nullable|email|unique:registrations,official_email',
            'academic_year' => 'required|string',
            'intake_month' => 'required|string',
            'start_date' => 'required|date',
            'expected_completion_date' => 'required|date|after:start_date',
            'total_course_months' => 'required|integer|min:1',
            'study_mode' => 'required|in:full_time,part_time,online',
            'registration_fee' => 'nullable|numeric|min:0',
            'tuition_per_month' => 'nullable|numeric|min:0',
            'caution_money' => 'nullable|numeric|min:0',
            'cdacc_registration_fee' => 'nullable|numeric|min:0',
            'cdacc_examination_fee' => 'nullable|numeric|min:0',
            'payment_plan' => 'required|in:monthly,quarterly,semester,annual,custom',
            'monthly_due_day' => 'nullable|integer|min:1|max:31',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate total course fee if not provided
            $totalCourseFee = $request->total_course_fee ?? 0;
            if (!$totalCourseFee && $request->total_course_months && $request->tuition_per_month) {
                $totalCourseFee = ($request->tuition_per_month * $request->total_course_months) +
                    ($request->registration_fee ?? 0) +
                    ($request->caution_money ?? 0) +
                    ($request->cdacc_registration_fee ?? 0) +
                    ($request->cdacc_examination_fee ?? 0);
            }

            // Create registration
            $registration = Registration::create([
                'application_id' => $request->application_id,
                'student_id' => $request->student_id,
                'campus_id' => $request->campus_id,
                'course_id' => $request->course_id,
                'fee_structure_id' => $request->fee_structure_id,
                'official_email' => $request->official_email,
                'academic_year' => $request->academic_year,
                'intake_month' => $request->intake_month,
                'start_date' => $request->start_date,
                'expected_completion_date' => $request->expected_completion_date,
                'total_course_months' => $request->total_course_months,
                'current_month' => 1,
                'study_mode' => $request->study_mode,
                'registration_fee' => $request->registration_fee ?? 0,
                'tuition_per_month' => $request->tuition_per_month ?? 0,
                'caution_money' => $request->caution_money ?? 0,
                'cdacc_registration_fee' => $request->cdacc_registration_fee ?? 0,
                'cdacc_examination_fee' => $request->cdacc_examination_fee ?? 0,
                'total_course_fee' => $totalCourseFee,
                'amount_paid' => 0,
                'balance' => $totalCourseFee,
                'payment_plan' => $request->payment_plan,
                'monthly_due_day' => $request->monthly_due_day ?? 5,
                'cdacc_status' => 'pending',
                'cdacc_fee_paid' => false,
                'status' => 'pending',
                'processed_by' => Auth::id(),
                'registration_date' => now(),
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration created successfully!',
                'data' => [
                    'id' => $registration->id,
                    'registration_number' => $registration->registration_number,
                    'student_number' => $registration->student_number,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Registration $registration)
    {
        $registration->load([
            'student',
            'campus',
            'course',
            'feeStructure',
            'application',
            'processor',
            'academicAdvisor',
            'studentFees' => function($q) {
                $q->orderBy('due_date');
            },
            'feePayments' => function($q) {
                $q->orderBy('payment_date', 'desc');
            },
            'paymentPlan',
            'cdaccRegistration',
            'studentDetail'
        ]);

        // Get progress summary
        $progress = $registration->getProgressSummary();

        return response()->json([
            'success' => true,
            'data' => [
                'registration' => $registration,
                'progress' => $progress,
                'monthly_payments' => $registration->monthly_payments ?? [],
                'requirements' => $registration->requirements_checklist ?? [],
                'documents' => $registration->documents_submitted ?? [],
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Registration $registration)
    {
        $registration->load(['student', 'campus', 'course', 'feeStructure', 'application']);

        $students = User::students()->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->where('is_approved', true)
            ->with(['course', 'campus'])
            ->get();

        $academicYears = [
            now()->year . '/' . (now()->year + 1),
            (now()->year + 1) . '/' . (now()->year + 2),
            (now()->year + 2) . '/' . (now()->year + 3),
        ];

        $intakeMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $studyModes = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'online' => 'Online'
        ];

        $paymentPlans = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semester' => 'Semester',
            'annual' => 'Annual',
            'custom' => 'Custom'
        ];

        $statuses = [
            'pending' => 'Pending',
            'provisional' => 'Provisional',
            'registered' => 'Registered',
            'active' => 'Active',
            'behind_payment' => 'Behind Payment',
            'completed' => 'Completed',
            'suspended' => 'Suspended',
            'withdrawn' => 'Withdrawn'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'registration' => $registration,
                'students' => $students,
                'campuses' => $campuses,
                'courses' => $courses,
                'feeStructures' => $feeStructures,
                'academicYears' => $academicYears,
                'intakeMonths' => $intakeMonths,
                'studyModes' => $studyModes,
                'paymentPlans' => $paymentPlans,
                'statuses' => $statuses,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Registration $registration)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'application_id' => 'nullable|exists:applications,id',
            'student_id' => 'required|exists:users,id',
            'campus_id' => 'required|exists:campuses,id',
            'course_id' => 'required|exists:courses,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'official_email' => 'nullable|email|unique:registrations,official_email,' . $registration->id,
            'academic_year' => 'required|string',
            'intake_month' => 'required|string',
            'start_date' => 'required|date',
            'expected_completion_date' => 'required|date|after:start_date',
            'actual_completion_date' => 'nullable|date|after:start_date',
            'total_course_months' => 'required|integer|min:1',
            'current_month' => 'nullable|integer|min:1|max:' . $request->total_course_months,
            'study_mode' => 'required|in:full_time,part_time,online',
            'registration_fee' => 'nullable|numeric|min:0',
            'tuition_per_month' => 'nullable|numeric|min:0',
            'caution_money' => 'nullable|numeric|min:0',
            'cdacc_registration_fee' => 'nullable|numeric|min:0',
            'cdacc_examination_fee' => 'nullable|numeric|min:0',
            'total_course_fee' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'payment_plan' => 'required|in:monthly,quarterly,semester,annual,custom',
            'cdacc_index_number' => 'nullable|string',
            'cdacc_registration_number' => 'nullable|string',
            'cdacc_registration_date' => 'nullable|date',
            'cdacc_status' => 'nullable|in:pending,registered,active,completed',
            'cdacc_fee_paid' => 'nullable|boolean',
            'status' => 'required|in:pending,provisional,registered,active,behind_payment,completed,suspended,withdrawn',
            'processed_by' => 'nullable|exists:users,id',
            'academic_advisor_id' => 'nullable|exists:users,id',
            'monthly_due_day' => 'nullable|integer|min:1|max:31',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Recalculate balance if amount_paid or total_course_fee changed
            $amountPaid = $request->amount_paid ?? $registration->amount_paid;
            $totalCourseFee = $request->total_course_fee ?? $registration->total_course_fee;
            $balance = $totalCourseFee - $amountPaid;

            $updateData = [
                'application_id' => $request->application_id,
                'student_id' => $request->student_id,
                'campus_id' => $request->campus_id,
                'course_id' => $request->course_id,
                'fee_structure_id' => $request->fee_structure_id,
                'official_email' => $request->official_email,
                'academic_year' => $request->academic_year,
                'intake_month' => $request->intake_month,
                'start_date' => $request->start_date,
                'expected_completion_date' => $request->expected_completion_date,
                'actual_completion_date' => $request->actual_completion_date,
                'total_course_months' => $request->total_course_months,
                'current_month' => $request->current_month ?? $registration->current_month,
                'study_mode' => $request->study_mode,
                'registration_fee' => $request->registration_fee ?? $registration->registration_fee,
                'tuition_per_month' => $request->tuition_per_month ?? $registration->tuition_per_month,
                'caution_money' => $request->caution_money ?? $registration->caution_money,
                'cdacc_registration_fee' => $request->cdacc_registration_fee ?? $registration->cdacc_registration_fee,
                'cdacc_examination_fee' => $request->cdacc_examination_fee ?? $registration->cdacc_examination_fee,
                'total_course_fee' => $totalCourseFee,
                'amount_paid' => $amountPaid,
                'balance' => max(0, $balance),
                'payment_plan' => $request->payment_plan,
                'cdacc_index_number' => $request->cdacc_index_number,
                'cdacc_registration_number' => $request->cdacc_registration_number,
                'cdacc_registration_date' => $request->cdacc_registration_date,
                'cdacc_status' => $request->cdacc_status ?? $registration->cdacc_status,
                'cdacc_fee_paid' => $request->cdacc_fee_paid ?? $registration->cdacc_fee_paid,
                'status' => $request->status,
                'processed_by' => $request->processed_by ?? $registration->processed_by,
                'academic_advisor_id' => $request->academic_advisor_id,
                'monthly_due_day' => $request->monthly_due_day ?? $registration->monthly_due_day,
                'notes' => $request->notes,
            ];

            $registration->update($updateData);

            // Update monthly payments if total course months changed
            if ($request->total_course_months != $registration->total_course_months) {
                $registration->initializeMonthlyPayments();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration updated successfully!',
                'data' => $registration
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Registration $registration)
    {
        try {
            // Check if registration has related records
            if ($registration->studentFees()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete registration with associated student fees.'
                ], 400);
            }

            if ($registration->feePayments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete registration with associated fee payments.'
                ], 400);
            }

            if ($registration->paymentPlan()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete registration with associated payment plan.'
                ], 400);
            }

            $registration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registration deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve registration
     */
    public function approve(Registration $registration)
    {
        try {
            $registration->update([
                'status' => 'registered',
                'processed_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration approved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject registration
     */
    public function reject(Registration $registration, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $registration->update([
                'status' => 'withdrawn',
                'notes' => $registration->notes . "\nRejected: " . $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration rejected successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate registration
     */
    public function activate(Registration $registration)
    {
        try {
            // Check if all requirements are complete
            $checklist = $registration->requirements_checklist ?? [];
            $allComplete = true;

            foreach ($checklist as $requirement => $status) {
                if (!$status) {
                    $allComplete = false;
                    break;
                }
            }

            if (!$allComplete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate registration. All requirements must be completed first.'
                ], 400);
            }

            $registration->update([
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration activated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete registration
     */
    public function complete(Registration $registration)
    {
        try {
            // Check if all months are completed
            if ($registration->current_month < $registration->total_course_months) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete registration. Course not yet finished.'
                ], 400);
            }

            // Check if all fees are paid
            if ($registration->balance > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete registration. Outstanding balance exists.'
                ], 400);
            }

            $registration->update([
                'status' => 'completed',
                'actual_completion_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive registration
     */
    public function archive(Registration $registration)
    {
        try {
            $registration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registration archived successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore archived registration
     */
    public function restore($id)
    {
        try {
            $registration = Registration::withTrashed()->findOrFail($id);
            $registration->restore();

            return response()->json([
                'success' => true,
                'message' => 'Registration restored successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update requirement status
     */
    public function updateRequirement(Registration $registration, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requirement' => 'required|string',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $registration->updateRequirement($request->requirement, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Requirement updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update requirement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment for registration
     */
    public function recordPayment(Registration $registration, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Record payment
            $registration->recordPayment($request->amount);

            // Create fee payment record
            $registration->feePayments()->create([
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'description' => $request->description,
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advance to next month
     */
    public function advanceToNextMonth(Registration $registration)
    {
        try {
            $registration->advanceToNextMonth();

            return response()->json([
                'success' => true,
                'message' => 'Advanced to next month successfully!',
                'data' => [
                    'current_month' => $registration->current_month,
                    'completion_percentage' => $registration->completion_percentage,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to advance to next month: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registrations by student (AJAX)
     */
    public function getByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $registrations = Registration::where('student_id', $request->student_id)
            ->with(['course', 'campus'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * Get applications by student (AJAX)
     */
    public function getApplicationsByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $applications = Application::where('student_id', $request->student_id)
            ->where('status', 'approved')
            ->with(['course', 'campus'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    /**
     * Get fee structure by course and campus (AJAX)
     */
    public function getFeeStructure(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        $feeStructure = FeeStructure::where('course_id', $request->course_id)
            ->where('campus_id', $request->campus_id)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$feeStructure) {
            return response()->json([
                'success' => false,
                'message' => 'No active fee structure found for this course and campus.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $feeStructure
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkActions(Request $request)
{
    $request->validate([
        'action' => 'required|in:approve,activate,complete,archive,delete',
        'ids' => 'required|array',
        'ids.*' => 'exists:registrations,id',
        'reason' => 'required_if:action,reject|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        $registrations = Registration::whereIn('id', $request->ids)->get();
        $successCount = 0;
        $errors = [];

        foreach ($registrations as $registration) {
            try {
                switch ($request->action) {
                    case 'approve':
                        $registration->update(['status' => 'registered']);
                        break;

                    case 'activate':
                        $registration->update(['status' => 'active']);
                        break;

                    case 'complete':
                        $registration->update([
                            'status' => 'completed',
                            'actual_completion_date' => now(),
                        ]);
                        break;

                    case 'archive':
                        $registration->delete();
                        break;

                    case 'delete':
                        if ($registration->studentFees()->exists() ||
                            $registration->feePayments()->exists() ||
                            $registration->paymentPlan()->exists()) {
                            $errors[] = "Registration {$registration->registration_number} has related records";
                            continue 2; // Fixed: changed to continue 2
                        }
                        $registration->forceDelete();
                        break;
                }

                $successCount++;

            } catch (\Exception $e) {
                $errors[] = "Registration {$registration->registration_number}: " . $e->getMessage();
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "{$successCount} registrations processed successfully.",
            'errors' => $errors,
            'processed' => $successCount,
            'failed' => count($errors)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Bulk action failed: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Export registrations
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'filters' => 'nullable|array',
        ]);

        try {
            // Build query based on filters
            $query = Registration::with(['student', 'campus', 'course']);

            if ($request->filled('filters')) {
                $filters = $request->filters;

                if (isset($filters['student_id'])) {
                    $query->where('student_id', $filters['student_id']);
                }

                if (isset($filters['campus_id'])) {
                    $query->where('campus_id', $filters['campus_id']);
                }

                if (isset($filters['course_id'])) {
                    $query->where('course_id', $filters['course_id']);
                }

                if (isset($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                if (isset($filters['date_from'])) {
                    $query->where('start_date', '>=', $filters['date_from']);
                }

                if (isset($filters['date_to'])) {
                    $query->where('start_date', '<=', $filters['date_to']);
                }
            }

            $registrations = $query->get();

            // Format data for export
            $data = $registrations->map(function ($registration) {
                return [
                    'Registration Number' => $registration->registration_number,
                    'Student Number' => $registration->student_number,
                    'Student Name' => $registration->student->name ?? 'N/A',
                    'Campus' => $registration->campus->name ?? 'N/A',
                    'Course' => $registration->course->name ?? 'N/A',
                    'Academic Year' => $registration->academic_year,
                    'Intake Month' => $registration->intake_month,
                    'Start Date' => $registration->start_date->format('Y-m-d'),
                    'Expected Completion' => $registration->expected_completion_date->format('Y-m-d'),
                    'Status' => ucfirst(str_replace('_', ' ', $registration->status)),
                    'Study Mode' => ucfirst(str_replace('_', ' ', $registration->study_mode)),
                    'Total Fee' => $registration->total_course_fee,
                    'Amount Paid' => $registration->amount_paid,
                    'Balance' => $registration->balance,
                    'Current Month' => $registration->current_month,
                    'Total Months' => $registration->total_course_months,
                ];
            });

            // Generate filename
            $filename = 'registrations_' . date('Y-m-d_H-i-s');

            // Return response based on format
            switch ($request->format) {
                case 'csv':
                    return $this->exportToCsv($data, $filename);
                case 'excel':
                    return $this->exportToExcel($data, $filename);
                case 'pdf':
                    return $this->exportToPdf($data, $filename);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid export format'
                    ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export registrations: ' . $e->getMessage()
            ], 500);
        }
    }

    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            if (count($data) > 0) {
                fputcsv($file, array_keys($data[0]));
            }

            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($data, $filename)
    {
        // You would implement Excel export using Laravel Excel package
        // For now, we'll return CSV
        return $this->exportToCsv($data, $filename);
    }

    private function exportToPdf($data, $filename)
    {
        // You would implement PDF export using DomPDF or similar
        // For now, we'll return JSON response with data
        return response()->json([
            'success' => true,
            'message' => 'PDF export not implemented yet. Data available for download.',
            'data' => $data
        ]);
    }

    /**
     * Get registration statistics
     */
    public function getStatistics()
    {
        try {
            $totalRegistrations = Registration::count();
            $activeRegistrations = Registration::whereIn('status', ['active', 'registered'])->count();
            $pendingRegistrations = Registration::where('status', 'pending')->count();
            $completedRegistrations = Registration::where('status', 'completed')->count();
            $overdueRegistrations = Registration::overdue()->count();

            // By campus
            $byCampus = Registration::with('campus')
                ->select('campus_id', DB::raw('count(*) as count'))
                ->groupBy('campus_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'campus' => $item->campus->name ?? 'Unknown',
                        'count' => $item->count,
                    ];
                });

            // By course
            $byCourse = Registration::with('course')
                ->select('course_id', DB::raw('count(*) as count'))
                ->groupBy('course_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'course' => $item->course->name ?? 'Unknown',
                        'count' => $item->count,
                    ];
                });

            // By status
            $byStatus = Registration::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => ucfirst(str_replace('_', ' ', $item->status)),
                        'count' => $item->count,
                    ];
                });

            // Financial summary
            $totalFee = Registration::sum('total_course_fee');
            $totalPaid = Registration::sum('amount_paid');
            $totalBalance = Registration::sum('balance');

            // Recent registrations
            $recentRegistrations = Registration::with(['student', 'course'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'totals' => [
                        'total' => $totalRegistrations,
                        'active' => $activeRegistrations,
                        'pending' => $pendingRegistrations,
                        'completed' => $completedRegistrations,
                        'overdue' => $overdueRegistrations,
                    ],
                    'by_campus' => $byCampus,
                    'by_course' => $byCourse,
                    'by_status' => $byStatus,
                    'financials' => [
                        'total_fee' => $totalFee,
                        'total_paid' => $totalPaid,
                        'total_balance' => $totalBalance,
                        'collection_rate' => $totalFee > 0 ? round(($totalPaid / $totalFee) * 100, 2) : 0,
                    ],
                    'recent_registrations' => $recentRegistrations,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate admission letter
     */
    public function generateAdmissionLetter(Registration $registration)
    {
        try {
            $path = $registration->generateAdmissionLetter();

            return response()->json([
                'success' => true,
                'message' => 'Admission letter generated successfully!',
                'data' => [
                    'path' => $path,
                    'download_url' => asset('storage/' . $path),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate admission letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate fee structure
     */
    public function generateFeeStructure(Registration $registration)
    {
        try {
            $path = $registration->generateFeeStructure();

            return response()->json([
                'success' => true,
                'message' => 'Fee structure generated successfully!',
                'data' => [
                    'path' => $path,
                    'download_url' => asset('storage/' . $path),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate fee structure: ' . $e->getMessage()
            ], 500);
        }
    }
}
