<?php

namespace App\Http\Controllers;

use App\Models\PlanInstallment;
use App\Models\PaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PlanInstallmentController extends Controller
{
    /**
     * Display a listing of installments for a payment plan.
     */
    public function index(Request $request, PaymentPlan $paymentPlan)
    {
        $installments = $paymentPlan->installments()
            ->orderBy('installment_number')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'installments' => $installments
            ]);
        }

        return view('ktvtc.admin.payment-plans.installments.index', compact('paymentPlan', 'installments'));
    }

    /**
     * Show the form for creating a new installment.
     */
    public function create(PaymentPlan $paymentPlan)
    {
        // Check if plan is active
        if ($paymentPlan->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Cannot add installments to a non-active payment plan.');
        }

        $nextNumber = $paymentPlan->installments()->max('installment_number') + 1;

        return view('ktvtc.admin.payment-plans.installments.create', compact('paymentPlan', 'nextNumber'));
    }

    /**
     * Store a newly created installment.
     */
    public function store(Request $request, PaymentPlan $paymentPlan)
    {
        $validator = Validator::make($request->all(), [
            'installment_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Check if installment number already exists
            $exists = $paymentPlan->installments()
                ->where('installment_number', $request->installment_number)
                ->exists();

            if ($exists) {
                throw new \Exception("Installment number {$request->installment_number} already exists.");
            }

            $installment = $paymentPlan->installments()->create([
                'installment_number' => $request->installment_number,
                'description' => $request->description,
                'amount' => $request->amount,
                'amount_paid' => 0,
                'balance' => $request->amount,
                'due_date' => $request->due_date,
                'status' => 'upcoming',
                'late_fee_applied' => false,
                'late_fee_amount' => 0,
                'days_overdue' => 0,
            ]);

            // Update payment plan totals
            $paymentPlan->update([
                'number_of_installments' => $paymentPlan->installments()->count()
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Installment created successfully.',
                    'installment' => $installment
                ]);
            }

            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('success', 'Installment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create installment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified installment.
     */
    public function show(PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        $installment->load(['invoice', 'paymentPlan']);

        return view('ktvtc.admin.payment-plans.installments.show', compact('paymentPlan', 'installment'));
    }

    /**
     * Show the form for editing the specified installment.
     */
    public function edit(PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        // Check if installment can be edited
        if (in_array($installment->status, ['paid', 'waived'])) {
            return redirect()->back()
                ->with('error', 'Cannot edit a paid or waived installment.');
        }

        return view('ktvtc.admin.payment-plans.installments.edit', compact('paymentPlan', 'installment'));
    }

    /**
     * Update the specified installment.
     */
    public function update(Request $request, PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        // Check if installment can be updated
        if (in_array($installment->status, ['paid', 'waived'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot edit a paid or waived installment.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot edit a paid or waived installment.');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $oldAmount = $installment->amount;
            $newAmount = $request->amount;

            $installment->update([
                'description' => $request->description,
                'amount' => $newAmount,
                'balance' => $newAmount - $installment->amount_paid,
                'due_date' => $request->due_date,
            ]);

            // Update payment plan totals
            $paymentPlan->updateBalance();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Installment updated successfully.',
                    'installment' => $installment
                ]);
            }

            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('success', 'Installment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update installment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified installment.
     */
    public function destroy(PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        // Check if installment can be deleted
        if ($installment->amount_paid > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete installment with payments.');
        }

        if ($installment->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Cannot delete a paid installment.');
        }

        DB::beginTransaction();

        try {
            $installment->delete();

            // Re-number remaining installments
            $remaining = $paymentPlan->installments()->orderBy('installment_number')->get();
            foreach ($remaining as $index => $item) {
                $item->update(['installment_number' => $index + 1]);
            }

            // Update payment plan totals
            $paymentPlan->update([
                'number_of_installments' => $paymentPlan->installments()->count()
            ]);
            $paymentPlan->updateBalance();

            DB::commit();

            return redirect()->route('admin.payment-plans.show', $paymentPlan)
                ->with('success', 'Installment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete installment: ' . $e->getMessage());
        }
    }

    /**
     * Mark installment as paid.
     */
    public function markAsPaid(Request $request, PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|max:' . $installment->balance,
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $installment->markAsPaid(
                $request->amount,
                $request->payment_reference
            );

            // Update payment plan balance
            $paymentPlan->updateBalance();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Installment marked as paid successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to mark installment as paid: ' . $e->getMessage());
        }
    }

    /**
     * Apply late fee to installment.
     */
    public function applyLateFee(PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $installment->applyLateFee();

            // Update payment plan balance
            $paymentPlan->updateBalance();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Late fee applied successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to apply late fee: ' . $e->getMessage());
        }
    }

    /**
     * Generate invoice for installment.
     */
    public function generateInvoice(PaymentPlan $paymentPlan, PlanInstallment $installment)
    {
        // Ensure installment belongs to the payment plan
        if ($installment->payment_plan_id !== $paymentPlan->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Check if invoice already exists
            if ($installment->invoice_id) {
                throw new \Exception('Invoice already exists for this installment.');
            }

            // Create invoice
            $invoice = $paymentPlan->enrollment->invoices()->create([
                'student_id' => $paymentPlan->student_id,
                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => $installment->due_date,
                'description' => "Payment Plan Installment {$installment->installment_number} - {$paymentPlan->plan_name}",
                'subtotal' => $installment->amount,
                'total_amount' => $installment->amount + $installment->late_fee_amount,
                'status' => 'sent',
                'created_by' => auth()->id(),
            ]);

            // Create invoice item
            $invoice->items()->create([
                'description' => $installment->description ?? "Installment {$installment->installment_number}",
                'quantity' => 1,
                'unit_price' => $installment->amount,
                'total' => $installment->amount,
            ]);

            // Add late fee as separate item if exists
            if ($installment->late_fee_amount > 0) {
                $invoice->items()->create([
                    'description' => 'Late Fee',
                    'quantity' => 1,
                    'unit_price' => $installment->late_fee_amount,
                    'total' => $installment->late_fee_amount,
                ]);
            }

            // Link invoice to installment
            $installment->update([
                'invoice_id' => $invoice->id,
                'invoice_generated' => true,
                'invoice_generated_date' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Invoice generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get installments for API.
     */
    public function getInstallments(PaymentPlan $paymentPlan)
    {
        $installments = $paymentPlan->installments()
            ->orderBy('installment_number')
            ->get()
            ->map(function ($installment) {
                return [
                    'id' => $installment->id,
                    'number' => $installment->installment_number,
                    'description' => $installment->description,
                    'amount' => $installment->amount,
                    'formatted_amount' => 'KES ' . number_format($installment->amount, 2),
                    'amount_paid' => $installment->amount_paid,
                    'balance' => $installment->balance,
                    'due_date' => $installment->due_date->format('Y-m-d'),
                    'formatted_due_date' => $installment->due_date->format('M j, Y'),
                    'status' => $installment->status,
                    'status_color' => $installment->status_color,
                    'status_label' => $installment->status_label,
                    'is_overdue' => $installment->isOverdue(),
                    'can_pay' => !in_array($installment->status, ['paid', 'waived']),
                    'has_invoice' => !is_null($installment->invoice_id),
                    'invoice_id' => $installment->invoice_id,
                    'late_fee_applied' => $installment->late_fee_applied,
                    'late_fee_amount' => $installment->late_fee_amount,
                    'formatted_late_fee' => 'KES ' . number_format($installment->late_fee_amount, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'installments' => $installments,
            'summary' => [
                'total_installments' => $installments->count(),
                'total_amount' => $installments->sum('amount'),
                'total_paid' => $installments->sum('amount_paid'),
                'total_balance' => $installments->sum('balance'),
                'paid_count' => $installments->where('status', 'paid')->count(),
                'pending_count' => $installments->where('status', 'pending')->count(),
                'overdue_count' => $installments->where('status', 'overdue')->count(),
            ]
        ]);
    }
}
