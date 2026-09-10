<?php

namespace App\Http\Controllers;

use App\Models\StudentFee;
use App\Models\User;
use App\Models\FeeStructure;
use App\Models\Registration;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentFeeController extends Controller
{
    /* =========================================================
     | INDEX
     ========================================================= */
    public function index(Request $request)
{
    $query = StudentFee::with(['student', 'registration', 'feeStructure', 'payments'])
        ->latest();

    // Apply filters
    foreach ([
        'student_id',
        'registration_id',
        'academic_year',
        'billing_month',
        'payment_status',
        'fee_category',
        'is_cdacc_fee'
    ] as $field) {
        if ($request->filled($field)) {
            $query->where($field, $request->$field);
        }
    }

    if ($request->filled('date_from')) {
        $query->whereDate('due_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('due_date', '<=', $request->date_to);
    }

    // Calculate statistics
    $stats = [
        'totalFees'    => StudentFee::count(),
        'totalAmount'  => StudentFee::sum('amount'),
        'totalPaid'    => StudentFee::sum('amount_paid'),
        'totalBalance' => StudentFee::sum('amount') - StudentFee::sum('amount_paid'),
        'overdueFees'  => StudentFee::where('payment_status', 'overdue')->count(),
        'pendingFees'  => StudentFee::where('payment_status', 'pending')->count(),
    ];

    return view('ktvtc.admin.student-fees.index', [
        'studentFees'    => $query->paginate(50),
        'students'       => User::where('role', 5)->orderBy('name')->get(),
       // In the index() method:
'feeStructures' => FeeStructure::where('is_active', true)
    ->where('is_approved', true)
    ->with(['course', 'campus'])
    ->get(),
        'feeCategories'  => StudentFee::FEE_CATEGORIES,
        'paymentStatuses' => StudentFee::PAYMENT_STATUSES,
        'billingMonths'  => StudentFee::BILLING_MONTHS,
        'stats'          => $stats,
    ]);
}

    /* =========================================================
     | CREATE
     ========================================================= */
    public function create()
    {
        return view('ktvtc.admin.student-fees.create', [
            'students'       => User::where('role', 5)->orderBy('name')->get(),
            'feeStructures'  => FeeStructure::where('status', 'active')
                ->where('approval_status', 'approved')
                ->with(['course', 'campus'])
                ->get(),
            'feeCategories'  => StudentFee::FEE_CATEGORIES,
            'feeTypes'       => StudentFee::FEE_TYPES,
            'billingMonths'  => StudentFee::BILLING_MONTHS,
            'billingCycles'  => StudentFee::BILLING_CYCLES,
        ]);
    }

    /* =========================================================
     | STORE
     ========================================================= */
    public function store(Request $request)
    {
        // Get base validation rules
        $rules = StudentFee::validationRules();

        // Customize rules if needed
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, &$studentFee) {
            $studentFee = StudentFee::create(array_merge($data, [
                'payment_status' => 'pending',
                'created_by' => Auth::id(),
                'invoice_date' => $data['invoice_date'] ?? now()->format('Y-m-d'),
            ]));

            $studentFee->generateInvoiceNumber();
            $studentFee->save();
        });

        return redirect()
            ->route('admin.fees.student-fees.index')
            ->with('success', "Student fee created. Invoice #{$studentFee->invoice_number}");
    }

    /* =========================================================
     | SHOW
     ========================================================= */
    public function show(StudentFee $studentFee)
    {
        $studentFee->load(['student', 'registration', 'feeStructure', 'payments']);

        return view('ktvtc.admin.student-fees.show', [
            'studentFee' => $studentFee,
            'payments'   => $studentFee->payments()->latest()->get(),
            'summary'    => [
                'subtotal' => $studentFee->subtotal,
                'total_amount' => $studentFee->total_amount,
                'amount_paid' => $studentFee->amount_paid,
                'balance' => $studentFee->balance,
                'payment_progress' => $studentFee->getPaymentProgressAttribute(),
            ],
        ]);
    }

    /* =========================================================
     | EDIT
     ========================================================= */
    public function edit(StudentFee $studentFee)
    {
        abort_if(in_array($studentFee->payment_status, ['paid', 'cancelled']), 403);

        return view('ktvtc.admin.student-fees.edit', [
            'studentFee'    => $studentFee,
            'students'      => User::where('role', 5)->orderBy('name')->get(),
            'feeStructures' => FeeStructure::where('status', 'active')
                ->where('approval_status', 'approved')
                ->with(['course', 'campus'])
                ->get(),
            'feeCategories' => StudentFee::FEE_CATEGORIES,
            'feeTypes'      => StudentFee::FEE_TYPES,
            'billingMonths' => StudentFee::BILLING_MONTHS,
            'billingCycles' => StudentFee::BILLING_CYCLES,
        ]);
    }

    /* =========================================================
     | UPDATE
     ========================================================= */
    public function update(Request $request, StudentFee $studentFee)
    {
        abort_if(in_array($studentFee->payment_status, ['paid', 'cancelled']), 403);

        // Get validation rules and make some fields optional for updates
        $rules = StudentFee::validationRules();

        // Make some fields optional for updates
        $rules['invoice_date'] = 'nullable|date';
        $rules['due_date'] = 'nullable|date';

        $data = $request->validate($rules);

        DB::transaction(function () use ($studentFee, $data) {
            $studentFee->update(array_merge($data, [
                'updated_by' => Auth::id(),
            ]));

            $studentFee->updatePaymentStatus();
        });

        return redirect()
            ->route('admin.fees.student-fees.index')
            ->with('success', 'Student fee updated successfully');
    }

    /* =========================================================
     | DELETE
     ========================================================= */
    public function destroy(StudentFee $studentFee)
    {
        abort_if($studentFee->payments()->exists(), 403, 'Fee has payments');

        $studentFee->delete();

        return back()->with('success', 'Student fee deleted');
    }

    /* =========================================================
     | MARK PAID
     ========================================================= */
    public function markAsPaid(StudentFee $studentFee)
    {
        $studentFee->markAsPaid();

        return back()->with('success', 'Fee marked as paid');
    }

    /* =========================================================
     | RECORD PAYMENT
     ========================================================= */
    public function recordPayment(Request $request, StudentFee $studentFee)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $studentFee->balance,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,mpesa,bank_transfer,cheque,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $studentFee) {
            // Create payment record
            FeePayment::create([
                'student_fee_id' => $studentFee->id,
                'amount' => $request->payment_amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
            ]);

            // Update fee
            $studentFee->recordPayment($request->payment_amount);
        });

        return back()->with('success', 'Payment recorded successfully');
    }

    /* =========================================================
     | APPLY DISCOUNT
     ========================================================= */
    public function applyDiscount(Request $request, StudentFee $studentFee)
    {
        $request->validate([
            'discount_amount' => 'required|numeric|min:0|max:' . $studentFee->amount,
            'discount_reason' => 'required|string|max:255',
        ]);

        $studentFee->applyDiscount(
            $request->discount_amount,
            $request->discount_reason
        );

        return back()->with('success', 'Discount applied successfully');
    }

    /* =========================================================
     | GENERATE FEES (from fee structure)
     ========================================================= */
    public function generateFromStructure(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'payment_plan' => 'required|in:full,monthly,quarterly,semester',
            'start_month' => 'required|string|in:' . implode(',', StudentFee::BILLING_MONTHS),
            'installments' => 'nullable|integer|min:1|max:12',
        ]);

        $feeStructure = FeeStructure::findOrFail($request->fee_structure_id);
        $student = User::findOrFail($request->student_id);

        DB::beginTransaction();

        try {
            $feesGenerated = $this->generateFeesForStudent(
                $student,
                $feeStructure,
                $request->academic_year,
                $request->payment_plan,
                $request->start_month,
                $request->installments
            );

            DB::commit();

            return redirect()
                ->route('admin.fees.student-fees.index')
                ->with('success', "Generated {$feesGenerated} fees for {$student->name}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to generate fees: ' . $e->getMessage());
        }
    }

    /* =========================================================
     | BULK ACTIONS
     ========================================================= */
    public function bulkActions(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:mark_paid,apply_discount,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:student_fees,id',
            'discount_amount' => 'required_if:action,apply_discount|numeric|min:0',
            'discount_reason' => 'required_if:action,apply_discount|string|max:255',
        ]);

        DB::beginTransaction();

        $success = 0;
        $errors = [];

        foreach (StudentFee::whereIn('id', $data['ids'])->get() as $fee) {
            try {
                match ($data['action']) {
                    'mark_paid' => $fee->payment_status === 'paid'
                        ? throw new \Exception('Already paid')
                        : $fee->markAsPaid(),

                    'apply_discount' => $fee->applyDiscount(
                        $data['discount_amount'],
                        $data['discount_reason']
                    ),

                    'delete' => $fee->payments()->exists()
                        ? throw new \Exception('Has payments')
                        : $fee->delete(),
                };

                $success++;

            } catch (\Throwable $e) {
                $errors[] = "Invoice {$fee->invoice_number}: {$e->getMessage()}";
                continue;
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Processed {$success} fees successfully",
            'processed' => $success,
            'failed' => count($errors),
            'errors' => $errors,
        ]);
    }

    /* =========================================================
     | EXPORT TO EXCEL
     ========================================================= */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,csv,pdf',
            'filters' => 'nullable|array',
        ]);

        $query = StudentFee::query();

        if ($request->has('filters')) {
            $filters = $request->filters;

            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    $query->where($field, $value);
                }
            }
        }

        $studentFees = $query->with(['student', 'feeStructure'])->get();

        // This would typically use a package like Maatwebsite/Laravel-Excel
        // For now, we'll just return a message
        // You should implement actual export logic here

        return back()->with('info', 'Export feature coming soon. ' . count($studentFees) . ' records found.');
    }

    /* =========================================================
     | SEND REMINDER
     ========================================================= */
    public function sendReminder(StudentFee $studentFee)
    {
        // Check if fee is overdue or pending
        if (!in_array($studentFee->payment_status, ['pending', 'overdue'])) {
            return back()->with('error', 'Only pending or overdue fees can be reminded.');
        }

        // Here you would implement email/SMS reminder logic
        // For now, we'll just log it

        \Log::info('Reminder sent for invoice', [
            'invoice' => $studentFee->invoice_number,
            'student' => $studentFee->student->name,
            'amount' => $studentFee->balance,
            'due_date' => $studentFee->due_date,
        ]);

        return back()->with('success', 'Reminder sent to ' . $studentFee->student->email);
    }

    /* =========================================================
     | PRIVATE HELPER METHODS
     ========================================================= */
    private function generateFeesForStudent($student, $feeStructure, $academicYear, $paymentPlan, $startMonth, $installments = null)
    {
        $totalAmount = $feeStructure->total_amount ?? 0;
        $fees = [];

        // Determine number of installments
        if ($paymentPlan === 'full') {
            $installmentCount = 1;
            $installmentAmount = $totalAmount;
        } else {
            $installmentCount = match ($paymentPlan) {
                'monthly' => 12,
                'quarterly' => 4,
                'semester' => 2,
                default => 1,
            };

            if ($installments) {
                $installmentCount = $installments;
            }

            $installmentAmount = round($totalAmount / $installmentCount, 2);
        }

        // Get starting month index
        $monthIndex = array_search($startMonth, StudentFee::BILLING_MONTHS);

        // Generate fees
        for ($i = 1; $i <= $installmentCount; $i++) {
            $currentMonthIndex = ($monthIndex + $i - 1) % 12;
            $billingMonth = StudentFee::BILLING_MONTHS[$currentMonthIndex];

            // Calculate due date (first day of the month, 30 days from invoice)
            $dueDate = Carbon::create($academicYear, $currentMonthIndex + 1, 1)->addDays(30);

            $fees[] = [
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'description' => "{$feeStructure->name} - Installment {$i}/{$installmentCount}",
                'detailed_description' => "Tuition fee for {$billingMonth} {$academicYear}",
                'fee_category' => 'tuition',
                'fee_type' => 'installment',
                'academic_year' => $academicYear,
                'billing_month' => $billingMonth,
                'month_number' => $i,
                'billing_cycle' => $paymentPlan,
                'amount' => $installmentAmount,
                'discount' => 0,
                'tax' => 0,
                'invoice_date' => now(),
                'due_date' => $dueDate,
                'is_installment' => $installmentCount > 1,
                'installment_number' => $i,
                'total_installments' => $installmentCount,
                'is_refundable' => false,
                'payment_status' => 'pending',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all fees
        StudentFee::insert($fees);

        // Generate invoice numbers for the new fees
        $newFees = StudentFee::where('student_id', $student->id)
            ->where('fee_structure_id', $feeStructure->id)
            ->where('academic_year', $academicYear)
            ->whereNull('invoice_number')
            ->get();

        foreach ($newFees as $fee) {
            $fee->generateInvoiceNumber();
            $fee->save();
        }

        return count($fees);
    }

    /* =========================================================
     | GET STUDENT FEES SUMMARY (AJAX)
     ========================================================= */
    public function getFeeSummary($id)
    {
        $studentFee = StudentFee::with(['student', 'payments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'student_fee' => $studentFee,
                'summary' => [
                    'subtotal' => $studentFee->subtotal,
                    'total_amount' => $studentFee->total_amount,
                    'amount_paid' => $studentFee->amount_paid,
                    'balance' => $studentFee->balance,
                    'payment_progress' => $studentFee->getPaymentProgressAttribute(),
                ],
                'payments' => $studentFee->payments,
            ]
        ]);
    }

    /* =========================================================
     | CANCEL INVOICE
     ========================================================= */
    public function cancelInvoice(Request $request, StudentFee $studentFee)
    {
        $request->validate([
            'cancelled_reason' => 'required|string|max:255',
        ]);

        abort_if(in_array($studentFee->payment_status, ['paid', 'cancelled']), 403,
            'Cannot cancel a paid or already cancelled invoice');

        DB::transaction(function () use ($studentFee, $request) {
            $studentFee->update([
                'payment_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_reason' => $request->cancelled_reason,
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Invoice cancelled successfully');
    }
}
