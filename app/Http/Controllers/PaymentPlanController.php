<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\Enrollment; // CHANGED from Registration
use App\Models\Student;
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
        $query = PaymentPlan::with(['student', 'enrollment.course', 'enrollment.campus', 'installments']) // CHANGED
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('enrollment_id')) { // CHANGED
            $query->where('enrollment_id', $request->enrollment_id);
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

        // Get filter data
        $students = Student::orderBy('first_name')->get();

        $enrollments = Enrollment::with(['student', 'course']) // CHANGED
            ->where('status', 'in_progress')
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
        $overduePlans = PaymentPlan::whereHas('installments', function($q) {
            $q->where('due_date', '<', now())
              ->whereIn('status', ['upcoming', 'pending']);
        })->count();

        $totalAmount = PaymentPlan::sum('total_amount');
        $totalPaid = PaymentPlan::sum('amount_paid');
        $totalBalance = PaymentPlan::sum(DB::raw('total_amount - amount_paid'));

        return view('ktvtc.admin.payment-plans.index', compact(
            'paymentPlans',
            'students',
            'enrollments',
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
 * Bulk approve payment plans.
 */
public function bulkApprove(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:payment_plans,id',
    ]);

    try {
        DB::beginTransaction();

        $count = PaymentPlan::whereIn('id', $request->ids)
            ->where('is_approved', false)
            ->update([
                'is_approved' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} payment plans approved successfully.",
                'count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} payment plans approved successfully.");

    } catch (\Exception $e) {
        DB::rollBack();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment plans: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Failed to approve payment plans: ' . $e->getMessage());
    }
}

/**
 * Bulk activate payment plans.
 */
public function bulkActivate(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:payment_plans,id',
    ]);

    try {
        DB::beginTransaction();

        $count = PaymentPlan::whereIn('id', $request->ids)
            ->where('status', '!=', 'active')
            ->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} payment plans activated successfully.",
                'count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} payment plans activated successfully.");

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()->with('error', 'Failed to activate payment plans: ' . $e->getMessage());
    }
}

/**
 * Bulk suspend payment plans.
 */
public function bulkSuspend(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:payment_plans,id',
    ]);

    try {
        DB::beginTransaction();

        $count = PaymentPlan::whereIn('id', $request->ids)
            ->where('status', 'active')
            ->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspended_by' => auth()->id(),
            ]);

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} payment plans suspended successfully.",
                'count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} payment plans suspended successfully.");

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()->with('error', 'Failed to suspend payment plans: ' . $e->getMessage());
    }
}

/**
 * Bulk delete payment plans.
 */
public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:payment_plans,id',
    ]);

    try {
        DB::beginTransaction();

        // Check if any plans have payments
        $plansWithPayments = PaymentPlan::whereIn('id', $request->ids)
            ->whereHas('installments', function($q) {
                $q->where('amount_paid', '>', 0);
            })
            ->count();

        if ($plansWithPayments > 0) {
            throw new \Exception("Cannot delete {$plansWithPayments} payment plan(s) that have payments recorded.");
        }

        $count = PaymentPlan::whereIn('id', $request->ids)->delete();

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} payment plans deleted successfully.",
                'count' => $count
            ]);
        }

        return redirect()->back()->with('success', "{$count} payment plans deleted successfully.");

    } catch (\Exception $e) {
        DB::rollBack();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', $e->getMessage());
    }
}

/**
 * Bulk generate invoices for payment plans.
 */
public function bulkGenerateInvoices(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:payment_plans,id',
    ]);

    try {
        DB::beginTransaction();

        $plans = PaymentPlan::whereIn('id', $request->ids)
            ->where('status', 'active')
            ->get();

        $successCount = 0;
        $errors = [];

        foreach ($plans as $plan) {
            try {
                $plan->generateInvoices();
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Plan {$plan->plan_code}: " . $e->getMessage();
            }
        }

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Invoices generated for {$successCount} plan(s).",
                'errors' => $errors
            ]);
        }

        $message = "Invoices generated for {$successCount} plan(s).";
        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoices: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Failed to generate invoices: ' . $e->getMessage());
    }
}
    /**
     * Show the form for creating a new payment plan.
     */
    public function create(Request $request)
    {
        $students = Student::orderBy('first_name')->get();

        $enrollments = Enrollment::with(['student', 'course', 'campus']) // CHANGED
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
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
            'enrollments',
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
            'enrollment_id' => 'required|exists:enrollments,id|unique:payment_plans,enrollment_id', // CHANGED
            'student_id' => 'required|exists:students,id',
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:monthly,quarterly,semester,annual,full_course,custom',
            'total_amount' => 'required|numeric|min:0',
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

            // Calculate net amount
            $netAmount = $request->total_amount - ($request->discount_amount ?? 0);

            // Create payment plan
            $paymentPlan = PaymentPlan::create([
                'enrollment_id' => $request->enrollment_id, // CHANGED
                'student_id' => $request->student_id,
                'plan_name' => $request->plan_name,
                'plan_type' => $request->plan_type,
                'total_amount' => $request->total_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_reason' => $request->discount_reason,
                'net_amount' => $netAmount,
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

            // Generate plan code if not set
            if (empty($paymentPlan->plan_code)) {
                $paymentPlan->plan_code = PaymentPlan::generatePlanCode();
                $paymentPlan->save();
            }

            // Generate installments
            if ($request->plan_type !== 'custom') {
                $paymentPlan->generateInstallments();
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
            'enrollment.course', // CHANGED
            'enrollment.campus', // CHANGED
            'installments' => function($q) {
                $q->orderBy('installment_number');
            },
            'creator',
            'approver',
        ]);

        // Get statistics
        $statistics = [
            'paid_installments' => $paymentPlan->installments()->where('status', 'paid')->count(),
            'pending_installments' => $paymentPlan->installments()->whereIn('status', ['pending', 'upcoming'])->count(),
            'overdue_installments' => $paymentPlan->installments()
                ->where('due_date', '<', now())
                ->whereIn('status', ['pending', 'upcoming'])
                ->count(),
            'next_payment_date' => $paymentPlan->installments()
                ->whereIn('status', ['pending', 'upcoming'])
                ->orderBy('due_date')
                ->first()?->due_date,
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

        $students = Student::orderBy('first_name')->get();

        $enrollments = Enrollment::with(['student', 'course']) // CHANGED
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
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
            'enrollments',
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
            'enrollment_id' => 'required|exists:enrollments,id|unique:payment_plans,enrollment_id,' . $paymentPlan->id, // CHANGED
            'student_id' => 'required|exists:students,id',
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:monthly,quarterly,semester,annual,full_course,custom',
            'total_amount' => 'required|numeric|min:0',
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

            // Delete existing installments if changing plan structure
            if ($paymentPlan->plan_type !== $request->plan_type ||
                $paymentPlan->number_of_installments !== $request->number_of_installments ||
                $paymentPlan->installment_frequency !== $request->installment_frequency) {
                $paymentPlan->installments()->delete();
            }

            // Calculate net amount
            $netAmount = $request->total_amount - ($request->discount_amount ?? 0);

            // Update payment plan
            $paymentPlan->update([
                'enrollment_id' => $request->enrollment_id, // CHANGED
                'student_id' => $request->student_id,
                'plan_name' => $request->plan_name,
                'plan_type' => $request->plan_type,
                'total_amount' => $request->total_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_reason' => $request->discount_reason,
                'net_amount' => $netAmount,
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

            // Regenerate installments if needed
            if ($paymentPlan->installments()->count() === 0 && $request->plan_type !== 'custom') {
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

            if ($paymentPlan->installments()->where('amount_paid', '>', 0)->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete payment plan because it has payments made.');
            }

            $paymentPlan->installments()->delete();
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
            // Check if plan has installments
            if ($paymentPlan->installments()->count() === 0) {
                return redirect()->back()
                    ->with('error', 'Cannot submit for approval without installments.');
            }

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
            $paymentPlan->update([
                'is_approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status' => 'active',
                'notes' => $paymentPlan->notes . "\nApproved: " . ($request->approval_notes ?? ''),
            ]);

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
            if (!$paymentPlan->is_approved) {
                return redirect()->back()
                    ->with('error', 'Cannot activate an unapproved payment plan.');
            }

            $paymentPlan->update([
                'status' => 'active'
            ]);

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
            if ($paymentPlan->status !== 'active') {
                return redirect()->back()
                    ->with('error', 'Only active payment plans can generate invoices.');
            }

            $dueInstallments = $paymentPlan->installments()
                ->whereIn('status', ['pending', 'upcoming'])
                ->where('due_date', '<=', now()->addDays($paymentPlan->invoice_days_before_due))
                ->get();

            $generatedCount = 0;
            foreach ($dueInstallments as $installment) {
                $installment->generateInvoice();
                $generatedCount++;
            }

            return redirect()->back()
                ->with('success', $generatedCount . ' invoices generated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate invoices: ' . $e->getMessage());
        }
    }

    /**
     * Get enrollments by student (AJAX).
     */
    public function getEnrollmentsByStudent(Request $request) // CHANGED method name
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $enrollments = Enrollment::where('student_id', $request->student_id) // CHANGED
            ->with(['course', 'campus'])
            ->whereIn('status', ['registered', 'in_progress'])
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'text' => $enrollment->course->name . ' (' . $enrollment->enrollment_number . ') - ' . $enrollment->intake_period . ' ' . $enrollment->intake_year,
                    'course_name' => $enrollment->course->name,
                    'course_code' => $enrollment->course->code,
                    'campus' => $enrollment->campus->name ?? 'N/A',
                    'total_fee' => $enrollment->total_course_fee,
                    'balance' => $enrollment->balance,
                ];
            });

        return response()->json([
            'success' => true,
            'enrollments' => $enrollments
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
        $installmentAmount = floor(($netAmount / $request->number_of_installments) * 100) / 100; // Round down to 2 decimals

        // Calculate last installment to account for rounding
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
     * Get payment plan statistics.
     */
    public function getStatistics()
    {
        $totalPlans = PaymentPlan::count();
        $activePlans = PaymentPlan::where('status', 'active')->count();
        $completedPlans = PaymentPlan::where('status', 'completed')->count();

        $overduePlans = PaymentPlan::whereHas('installments', function($q) {
            $q->where('due_date', '<', now())
              ->whereIn('status', ['pending', 'upcoming']);
        })->count();

        $monthlyPlans = PaymentPlan::where('plan_type', 'monthly')->count();
        $quarterlyPlans = PaymentPlan::where('plan_type', 'quarterly')->count();
        $semesterPlans = PaymentPlan::where('plan_type', 'semester')->count();

        $totalAmount = PaymentPlan::sum('total_amount');
        $totalPaid = PaymentPlan::sum('amount_paid');
        $totalBalance = PaymentPlan::sum(DB::raw('total_amount - amount_paid'));

        $recentPlans = PaymentPlan::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'plan_code' => $plan->plan_code,
                    'plan_name' => $plan->plan_name,
                    'student_name' => $plan->student->full_name ?? 'N/A',
                    'status' => $plan->status,
                    'total_amount' => $plan->total_amount,
                    'amount_paid' => $plan->amount_paid,
                    'created_at' => $plan->created_at->format('Y-m-d'),
                ];
            });

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

    /**
     * Print payment plan agreement.
     */
    public function printAgreement(PaymentPlan $paymentPlan)
    {
        $paymentPlan->load([
            'student',
            'enrollment.course',
            'enrollment.campus',
            'installments' => function($q) {
                $q->orderBy('installment_number');
            }
        ]);

        return view('ktvtc.admin.payment-plans.print-agreement', compact('paymentPlan'));
    }
}
