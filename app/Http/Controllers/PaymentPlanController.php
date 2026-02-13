<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\Registration;
use App\Models\User;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentPlanController extends Controller
{
    /**
     * Display a listing of payment plans.
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $query = PaymentPlan::with(['student', 'registration', 'feeStructure', 'installments'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('registration_id')) {
            $query->where('registration_id', $request->registration_id);
        }

        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved == '1');
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Get payment plans
        $paymentPlans = $query->paginate(20);

        // Get filter data - FIXED: Using scopeStudents() instead of roles relationship
        $students = User::students()->orderBy('name')->get();

        $registrations = Registration::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        $planTypes = [
            'monthly', 'quarterly', 'semester', 'annual',
            'full_course', 'custom'
        ];

        $statuses = [
            'draft', 'pending_approval', 'approved', 'active',
            'completed', 'cancelled', 'suspended', 'defaulted'
        ];

        // Calculate statistics
        $totalPlans = PaymentPlan::count();
        $activePlans = PaymentPlan::where('status', 'active')->count();
        $approvedPlans = PaymentPlan::where('is_approved', true)->count();
        $overduePlans = PaymentPlan::overdue()->count();
        $totalAmount = PaymentPlan::sum('total_course_amount');
        $totalPaid = PaymentPlan::sum('amount_paid');
        $totalBalance = PaymentPlan::sum('total_balance');

        return view('ktvtc.admin.payment-plans.index', compact(
            'paymentPlans',
            'students',
            'registrations',
            'planTypes',
            'statuses',
            'totalPlans',
            'activePlans',
            'approvedPlans',
            'overduePlans',
            'totalAmount',
            'totalPaid',
            'totalBalance'
        ));
    }

    /**
     * Show the form for creating a new payment plan.
     */
    public function create(Request $request)
    {
        // FIXED: Using scopeStudents() instead of roles relationship
        $students = User::students()->orderBy('name')->get();

        $registrations = Registration::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->where('is_approved', true)
            ->with(['course', 'campus'])
            ->get();

        $planTypes = [
            'monthly', 'quarterly', 'semester', 'annual',
            'full_course', 'custom'
        ];

        $installmentFrequencies = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semester' => 'Every 6 Months',
            'annual' => 'Annual',
            'custom' => 'Custom Schedule'
        ];

        return view('ktvtc.admin.payment-plans.create', compact(
            'students',
            'registrations',
            'feeStructures',
            'planTypes',
            'installmentFrequencies'
        ));
    }

    /**
     * Store a newly created payment plan.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:registrations,id|unique:payment_plans,registration_id',
            'student_id' => 'required|exists:users,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:monthly,quarterly,semester,annual,full_course,custom',
            'total_course_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'number_of_installments' => 'required|integer|min:1|max:60',
            'installment_frequency' => 'nullable|in:monthly,quarterly,semester,annual,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'first_payment_date' => 'required|date|after_or_equal:start_date',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
            'terms_and_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create payment plan
            $paymentPlan = PaymentPlan::create([
                'registration_id' => $request->registration_id,
                'student_id' => $request->student_id,
                'fee_structure_id' => $request->fee_structure_id,
                'plan_name' => $request->plan_name,
                'plan_type' => $request->plan_type,
                'total_course_amount' => $request->total_course_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_reason' => $request->discount_reason,
                'number_of_installments' => $request->number_of_installments,
                'installment_frequency' => $request->installment_frequency ?? 'monthly',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'first_payment_date' => $request->first_payment_date,
                'late_fee_percentage' => $request->late_fee_percentage ?? 5.00,
                'grace_period_days' => $request->grace_period_days ?? 7,
                'auto_generate_invoices' => $request->has('auto_generate_invoices'),
                'invoice_days_before_due' => $request->invoice_days_before_due ?? 7,
                'terms_and_conditions' => $request->terms_and_conditions,
                'status' => 'draft',
                'is_approved' => false,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // If custom schedule is provided, store it
            if ($request->filled('installment_schedule')) {
                $paymentPlan->update([
                    'installment_schedule' => json_decode($request->installment_schedule, true)
                ]);
            }

            // Generate plan code if not set
            if (empty($paymentPlan->plan_code)) {
                $paymentPlan->plan_code = PaymentPlan::generatePlanCode();
                $paymentPlan->save();
            }

            DB::commit();

            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('success', 'Payment plan created successfully! Plan Code: ' . $paymentPlan->plan_code);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create payment plan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified payment plan.
     */
    public function show(PaymentPlan $paymentPlan)
    {
        $paymentPlan->load([
            'student',
            'registration.course',
            'registration.campus',
            'feeStructure',
            'installments' => function($q) {
                $q->orderBy('installment_number');
            },
            'studentFees' => function($q) {
                $q->orderBy('due_date');
            },
            'creator',
            'approver',
            'studentSignatory',
            'parentSignatory',
            'institutionSignatory'
        ]);

        // Get statistics
        $statistics = [
            'paid_installments' => $paymentPlan->installments()->where('status', 'paid')->count(),
            'pending_installments' => $paymentPlan->installments()->where('status', 'pending')->count(),
            'overdue_installments' => $paymentPlan->installments()
                ->where('due_date', '<', now())
                ->whereIn('status', ['pending', 'partial'])
                ->count(),
            'next_payment_date' => $paymentPlan->installments()
                ->whereIn('status', ['pending', 'upcoming'])
                ->orderBy('due_date')
                ->first()?->due_date,
            'is_fully_signed' => $paymentPlan->isFullySigned(),
        ];

        return view('ktvtc.admin.payment-plans.show', compact('paymentPlan', 'statistics'));
    }

    /**
     * Show the form for editing the specified payment plan.
     */
    public function edit(PaymentPlan $paymentPlan)
    {
        // Check if plan can be edited
        if (in_array($paymentPlan->status, ['approved', 'active', 'completed'])) {
            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('error', 'Cannot edit an ' . $paymentPlan->status . ' payment plan.');
        }

        // FIXED: Using scopeStudents() instead of roles relationship
        $students = User::students()->orderBy('name')->get();

        $registrations = Registration::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        $feeStructures = FeeStructure::where('is_active', true)
            ->where('is_approved', true)
            ->with(['course', 'campus'])
            ->get();

        $planTypes = [
            'monthly', 'quarterly', 'semester', 'annual',
            'full_course', 'custom'
        ];

        $installmentFrequencies = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semester' => 'Every 6 Months',
            'annual' => 'Annual',
            'custom' => 'Custom Schedule'
        ];

        return view('ktvtc.admin.payment-plans.edit', compact(
            'paymentPlan',
            'students',
            'registrations',
            'feeStructures',
            'planTypes',
            'installmentFrequencies'
        ));
    }

    /**
     * Update the specified payment plan.
     */
    public function update(Request $request, PaymentPlan $paymentPlan)
    {
        // Check if plan can be edited
        if (in_array($paymentPlan->status, ['approved', 'active', 'completed'])) {
            return redirect()->back()
                ->with('error', 'Cannot edit an ' . $paymentPlan->status . ' payment plan.');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:registrations,id|unique:payment_plans,registration_id,' . $paymentPlan->id,
            'student_id' => 'required|exists:users,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:monthly,quarterly,semester,annual,full_course,custom',
            'total_course_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'number_of_installments' => 'required|integer|min:1|max:60',
            'installment_frequency' => 'nullable|in:monthly,quarterly,semester,annual,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'first_payment_date' => 'required|date|after_or_equal:start_date',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
            'terms_and_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Delete existing installments if changing plan type
            if ($paymentPlan->plan_type !== $request->plan_type ||
                $paymentPlan->number_of_installments !== $request->number_of_installments ||
                $paymentPlan->installment_frequency !== $request->installment_frequency) {
                $paymentPlan->installments()->delete();
            }

            // Update payment plan
            $paymentPlan->update([
                'registration_id' => $request->registration_id,
                'student_id' => $request->student_id,
                'fee_structure_id' => $request->fee_structure_id,
                'plan_name' => $request->plan_name,
                'plan_type' => $request->plan_type,
                'total_course_amount' => $request->total_course_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_reason' => $request->discount_reason,
                'number_of_installments' => $request->number_of_installments,
                'installment_frequency' => $request->installment_frequency ?? 'monthly',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'first_payment_date' => $request->first_payment_date,
                'late_fee_percentage' => $request->late_fee_percentage ?? 5.00,
                'grace_period_days' => $request->grace_period_days ?? 7,
                'auto_generate_invoices' => $request->has('auto_generate_invoices'),
                'invoice_days_before_due' => $request->invoice_days_before_due ?? 7,
                'terms_and_conditions' => $request->terms_and_conditions,
                'notes' => $request->notes,
            ]);

            // If custom schedule is provided, store it
            if ($request->filled('installment_schedule')) {
                $paymentPlan->update([
                    'installment_schedule' => json_decode($request->installment_schedule, true)
                ]);
            }

            // Regenerate installments
            if ($paymentPlan->plan_type !== 'custom') {
                $paymentPlan->generateInstallments();
            }

            DB::commit();

            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('success', 'Payment plan updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update payment plan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified payment plan.
     */
    public function destroy(PaymentPlan $paymentPlan)
    {
        try {
            // Check if plan can be deleted
            if (in_array($paymentPlan->status, ['active', 'completed'])) {
                return redirect()->back()
                    ->with('error', 'Cannot delete an ' . $paymentPlan->status . ' payment plan.');
            }

            if ($paymentPlan->studentFees()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete payment plan because it has associated student fees.');
            }

            $paymentPlan->delete();

            return redirect()->route('admin.payment-plans.index')
                ->with('success', 'Payment plan deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete payment plan: ' . $e->getMessage());
        }
    }

    /**
     * Submit payment plan for approval.
     */
    public function submitForApproval(PaymentPlan $paymentPlan)
    {
        try {
            $paymentPlan->update([
                'status' => 'pending_approval'
            ]);

            return redirect()->back()
                ->with('success', 'Payment plan submitted for approval successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to submit for approval: ' . $e->getMessage());
        }
    }

    /**
     * Approve payment plan.
     */
    public function approve(PaymentPlan $paymentPlan, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if user has permission to approve
            if (!Auth::user()->can('approve-payment-plans')) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to approve payment plans.');
            }

            $paymentPlan->approve(
                Auth::id(),
                $request->approval_notes
            );

            return redirect()->back()
                ->with('success', 'Payment plan approved successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to approve payment plan: ' . $e->getMessage());
        }
    }

    /**
     * Reject payment plan.
     */
    public function reject(PaymentPlan $paymentPlan, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if user has permission to reject
            if (!Auth::user()->can('approve-payment-plans')) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to reject payment plans.');
            }

            $paymentPlan->update([
                'status' => 'draft',
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
                'notes' => $paymentPlan->notes . "\nRejected: " . $request->rejection_reason,
            ]);

            return redirect()->back()
                ->with('success', 'Payment plan rejected successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject payment plan: ' . $e->getMessage());
        }
    }

    /**
     * Activate payment plan.
     */
    public function activate(PaymentPlan $paymentPlan)
    {
        try {
            $paymentPlan->activate();

            return redirect()->back()
                ->with('success', 'Payment plan activated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to activate payment plan: ' . $e->getMessage());
        }
    }

    /**
     * Generate invoices for payment plan.
     */
    public function generateInvoices(PaymentPlan $paymentPlan)
    {
        try {
            $paymentPlan->generateInvoicesForDueInstallments();

            $generatedCount = $paymentPlan->installments()
                ->where('status', 'pending')
                ->where('due_date', '<=', now()->addDays($paymentPlan->invoice_days_before_due))
                ->count();

            return redirect()->back()
                ->with('success', $generatedCount . ' invoices generated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate invoices: ' . $e->getMessage());
        }
    }

    /**
     * Sign payment plan by institution.
     */
    public function signByInstitution(PaymentPlan $paymentPlan)
    {
        try {
            $paymentPlan->signByInstitution(Auth::id());

            return redirect()->back()
                ->with('success', 'Payment plan signed by institution successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to sign payment plan: ' . $e->getMessage());
        }
    }

    /**
     * Get registrations by student (AJAX).
     */
    public function getRegistrationsByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $registrations = Registration::where('student_id', $request->student_id)
            ->with(['course', 'campus'])
            ->get();

        return response()->json([
            'success' => true,
            'registrations' => $registrations
        ]);
    }

    /**
     * Get fee structure by registration (AJAX).
     */
    public function getFeeStructureByRegistration(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
        ]);

        $registration = Registration::with(['course', 'campus'])->findOrFail($request->registration_id);

        $feeStructure = FeeStructure::where('course_id', $registration->course_id)
            ->where('campus_id', $registration->campus_id)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$feeStructure) {
            return response()->json([
                'success' => false,
                'message' => 'No active fee structure found for this registration.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'fee_structure' => $feeStructure,
            'total_course_fee' => $feeStructure->total_course_fee,
        ]);
    }

    /**
     * Calculate payment plan installments (AJAX).
     */
    public function calculateInstallments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1|max:60',
            'plan_type' => 'required|in:monthly,quarterly,semester,annual,custom',
            'first_payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $netAmount = $request->total_amount - ($request->discount_amount ?? 0);
        $installmentAmount = $netAmount / $request->number_of_installments;
        $installmentAmount = round($installmentAmount, 2);

        // Adjust last installment for rounding differences
        $totalAllocated = $installmentAmount * ($request->number_of_installments - 1);
        $lastInstallmentAmount = $netAmount - $totalAllocated;

        $installments = [];
        $dueDate = Carbon::parse($request->first_payment_date);

        for ($i = 1; $i <= $request->number_of_installments; $i++) {
            $amount = ($i === $request->number_of_installments) ?
                $lastInstallmentAmount : $installmentAmount;

            if ($i > 1) {
                switch ($request->plan_type) {
                    case 'monthly':
                        $dueDate->addMonth();
                        break;
                    case 'quarterly':
                        $dueDate->addMonths(3);
                        break;
                    case 'semester':
                        $dueDate->addMonths(6);
                        break;
                    case 'annual':
                        $dueDate->addYear();
                        break;
                    default:
                        $dueDate->addMonth();
                }
            }

            $installments[] = [
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $dueDate->format('Y-m-d'),
                'formatted_due_date' => $dueDate->format('F j, Y'),
                'formatted_amount' => 'KES ' . number_format($amount, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'installments' => $installments,
            'summary' => [
                'total_amount' => $request->total_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'net_amount' => $netAmount,
                'number_of_installments' => $request->number_of_installments,
                'installment_amount' => $installmentAmount,
                'last_payment_date' => $dueDate->format('Y-m-d'),
            ]
        ]);
    }

    /**
     * Bulk actions on payment plans.
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,activate,generate_invoices,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:payment_plans,id',
            'approval_notes' => 'required_if:action,approve|string|max:500',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $paymentPlans = PaymentPlan::whereIn('id', $request->ids)->get();
            $successCount = 0;
            $errors = [];

            foreach ($paymentPlans as $plan) {
                try {
                    $shouldSkip = false;

                    switch ($request->action) {
                        case 'approve':
                            if (!Auth::user()->can('approve-payment-plans')) {
                                $errors[] = "You don't have permission to approve payment plans";
                                $shouldSkip = true;
                                break;
                            }
                            $plan->approve(Auth::id(), $request->approval_notes);
                            break;

                        case 'reject':
                            if (!Auth::user()->can('approve-payment-plans')) {
                                $errors[] = "You don't have permission to reject payment plans";
                                $shouldSkip = true;
                                break;
                            }
                            $plan->update([
                                'status' => 'draft',
                                'is_approved' => false,
                                'notes' => $plan->notes . "\nRejected: " . $request->rejection_reason,
                            ]);
                            break;

                        case 'activate':
                            if (!$plan->is_approved) {
                                $errors[] = "Plan {$plan->plan_code} cannot be activated (not approved)";
                                $shouldSkip = true;
                                break;
                            }
                            $plan->activate();
                            break;

                        case 'generate_invoices':
                            if ($plan->status !== 'active') {
                                $errors[] = "Plan {$plan->plan_code} must be active to generate invoices";
                                $shouldSkip = true;
                                break;
                            }
                            $plan->generateInvoicesForDueInstallments();
                            break;

                        case 'delete':
                            if (in_array($plan->status, ['active', 'completed'])) {
                                $errors[] = "Plan {$plan->plan_code} cannot be deleted";
                                $shouldSkip = true;
                                break;
                            }
                            if ($plan->studentFees()->exists()) {
                                $errors[] = "Plan {$plan->plan_code} has associated fees";
                                $shouldSkip = true;
                                break;
                            }
                            $plan->delete();
                            break;
                    }

                    if ($shouldSkip) {
                        continue;
                    }

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Plan {$plan->plan_code}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} payment plans processed successfully.",
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
     * Print payment plan agreement.
     */
    public function printAgreement(PaymentPlan $paymentPlan)
    {
        $paymentPlan->load([
            'student',
            'registration.course',
            'registration.campus',
            'feeStructure',
            'installments' => function($q) {
                $q->orderBy('installment_number');
            },
            'studentSignatory',
            'parentSignatory',
            'institutionSignatory'
        ]);

        return view('ktvtc.admin.payment-plans.print-agreement', compact('paymentPlan'));
    }

    /**
     * Get payment plan statistics.
     */
    public function getStatistics()
    {
        $totalPlans = PaymentPlan::count();
        $activePlans = PaymentPlan::where('status', 'active')->count();
        $completedPlans = PaymentPlan::where('status', 'completed')->count();
        $overduePlans = PaymentPlan::overdue()->count();

        $monthlyPlans = PaymentPlan::where('plan_type', 'monthly')->count();
        $quarterlyPlans = PaymentPlan::where('plan_type', 'quarterly')->count();
        $semesterPlans = PaymentPlan::where('plan_type', 'semester')->count();

        $totalAmount = PaymentPlan::sum('total_course_amount');
        $totalPaid = PaymentPlan::sum('amount_paid');
        $totalBalance = PaymentPlan::sum('total_balance');

        $recentPlans = PaymentPlan::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'totals' => [
                    'total_plans' => $totalPlans,
                    'active_plans' => $activePlans,
                    'completed_plans' => $completedPlans,
                    'overdue_plans' => $overduePlans,
                ],
                'types' => [
                    'monthly' => $monthlyPlans,
                    'quarterly' => $quarterlyPlans,
                    'semester' => $semesterPlans,
                ],
                'financials' => [
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'collection_rate' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 2) : 0,
                ],
                'recent_plans' => $recentPlans,
            ]
        ]);
    }
}
