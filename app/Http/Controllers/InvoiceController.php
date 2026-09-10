<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Invoice::with(['student', 'enrollment.course', 'enrollment.campus'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            })
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('enrollment_id')) {
            $query->where('enrollment_id', $request->enrollment_id);
        }

        if ($request->filled('invoice_date_from')) {
            $query->whereDate('invoice_date', '>=', $request->invoice_date_from);
        }

        if ($request->filled('invoice_date_to')) {
            $query->whereDate('invoice_date', '<=', $request->invoice_date_to);
        }

        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('student_number', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $totalInvoices = (clone $query)->count();
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = (clone $query)->sum('amount_paid');
        $totalBalance = (clone $query)->sum('balance');

        $sentCount = (clone $query)->where('status', 'sent')->count();
        $partialCount = (clone $query)->where('status', 'partial')->count();
        $paidCount = (clone $query)->where('status', 'paid')->count();
        $overdueCount = (clone $query)->where('status', 'overdue')->count();

        $invoices = $query->paginate(15);

        // Get students for filter
        $students = Student::orderBy('first_name')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->get();

        $enrollments = Enrollment::with(['student', 'course'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $statuses = ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'];

        return view('ktvtc.admin.invoices.index', compact(
            'invoices',
            'students',
            'enrollments',
            'statuses',
            'totalInvoices',
            'totalAmount',
            'totalPaid',
            'totalBalance',
            'sentCount',
            'partialCount',
            'paidCount',
            'overdueCount'
        ));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        $enrollments = Enrollment::with(['student', 'course', 'campus', 'feeItems'])
            ->whereIn('status', ['registered', 'in_progress'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $students = Student::orderBy('first_name')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->get();

        // If enrollment_id is provided, load its pending fee items
        $enrollmentId = $request->input('enrollment_id');
        $pendingFeeItems = [];
        if ($enrollmentId) {
            $enrollment = Enrollment::with('feeItems.feeCategory')->find($enrollmentId);
            if ($enrollment) {
                $pendingFeeItems = $enrollment->feeItems()
                    ->where('status', 'pending')
                    ->where('balance', '>', 0)
                    ->get();
            }
        }

        return view('ktvtc.admin.invoices.create', compact(
            'enrollments',
            'students',
            'pendingFeeItems'
        ));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|exists:enrollments,id',
            'student_id' => 'required|exists:students,id',
            'description' => 'required|string|max:255',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.enrollment_fee_item_id' => 'nullable|exists:enrollment_fee_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            // Create invoice
            $invoice = Invoice::create([
                'enrollment_id' => $request->enrollment_id,
                'student_id' => $request->student_id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => $request->due_date,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_reason' => $request->discount_reason,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance' => $totalAmount,
                'status' => 'sent',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                $total = $item['unit_price'] * $item['quantity'];

                $invoiceItem = $invoice->items()->create([
                    'enrollment_fee_item_id' => $item['enrollment_fee_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $total,
                ]);

                // If linked to enrollment fee item, update its status
                if (!empty($item['enrollment_fee_item_id'])) {
                    $feeItem = \App\Models\EnrollmentFeeItem::find($item['enrollment_fee_item_id']);
                    // We'll update payment status when payment is received
                }
            }

            DB::commit();

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice created successfully. Invoice Number: ' . $invoice->invoice_number);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'student',
            'enrollment.course',
            'enrollment.campus',
            'items',
            'payments' => function($q) {
                $q->orderBy('payment_date', 'desc');
            },
            'creator',
            'approver'
        ]);

        return view('ktvtc.admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('error', 'Cannot edit a ' . $invoice->status . ' invoice.');
        }

        $invoice->load(['items', 'enrollment']);

        $enrollments = Enrollment::with(['student', 'course', 'campus'])
            ->whereIn('status', ['registered', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ktvtc.admin.invoices.edit', compact('invoice', 'enrollments'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Check if invoice can be updated
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot update a ' . $invoice->status . ' invoice.');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Recalculate totals from items
            $subtotal = $invoice->items()->sum('total');
            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            $invoice->update([
                'description' => $request->description,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_reason' => $request->discount_reason,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount - $invoice->amount_paid,
                'notes' => $request->notes,
            ]);

            // Update payment status
            $invoice->updatePaymentStatus();

            DB::commit();

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete invoice with payments. Cancel it instead.');
        }

        DB::beginTransaction();

        try {
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('admin.tvet.invoices.index')
                ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice to student.
     */
    public function send(Invoice $invoice)
    {
        try {
            // Generate PDF
            $pdf = Pdf::loadView('ktvtc.admin.invoices.pdf', compact('invoice'))
                ->setPaper('A4', 'portrait');

            // Save PDF temporarily
            $path = 'invoices/' . $invoice->invoice_number . '.pdf';
            $pdf->save(storage_path('app/public/' . $path));

            // Send email with PDF attachment
            // Mail::to($invoice->student->email)->send(new InvoiceMail($invoice, $path));

            $invoice->update(['status' => 'sent']);

            return redirect()->back()
                ->with('success', 'Invoice sent successfully to ' . $invoice->student->email);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);

        return redirect()->back()
            ->with('success', 'Invoice marked as sent.');
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice)
    {
        DB::beginTransaction();

        try {
            $invoice->update([
                'amount_paid' => $invoice->total_amount,
                'balance' => 0,
                'status' => 'paid',
            ]);

            // Update enrollment balance
            $invoice->enrollment->amount_paid = $invoice->enrollment->invoices()->sum('amount_paid');
            $invoice->enrollment->balance = $invoice->enrollment->total_course_fee - $invoice->enrollment->amount_paid;
            $invoice->enrollment->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Invoice marked as paid.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to mark invoice as paid: ' . $e->getMessage());
        }
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Invoice $invoice)
    {
        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot cancel invoice with payments.');
        }

        $invoice->update(['status' => 'cancelled']);

        return redirect()->back()
            ->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Print invoice.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['student', 'enrollment.course', 'enrollment.campus', 'items']);

        return view('ktvtc.admin.invoices.print', compact('invoice'));
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['student', 'enrollment.course', 'enrollment.campus', 'items']);

        $pdf = Pdf::loadView('ktvtc.admin.invoices.pdf', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Bulk generate invoices from enrollment fee items.
     */
    public function bulkGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
            'due_date' => 'required|date|after_or_equal:today',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $generatedCount = 0;
            $errors = [];

            foreach ($request->enrollment_ids as $enrollmentId) {
                $enrollment = Enrollment::with(['student', 'feeItems' => function($q) {
                    $q->where('status', 'pending')
                      ->where('balance', '>', 0);
                }])->find($enrollmentId);

                if ($enrollment->feeItems->isEmpty()) {
                    $errors[] = "Enrollment {$enrollment->enrollment_number}: No pending fee items";
                    continue;
                }

                // Calculate totals
                $subtotal = $enrollment->feeItems->sum('balance');
                $totalAmount = $subtotal;

                // Create invoice
                $invoice = Invoice::create([
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'invoice_date' => now(),
                    'due_date' => $request->due_date,
                    'description' => $request->description . ' - ' . $enrollment->course->name,
                    'subtotal' => $subtotal,
                    'total_amount' => $totalAmount,
                    'amount_paid' => 0,
                    'balance' => $totalAmount,
                    'status' => 'sent',
                    'created_by' => Auth::id(),
                ]);

                // Create invoice items
                foreach ($enrollment->feeItems as $feeItem) {
                    $invoice->items()->create([
                        'enrollment_fee_item_id' => $feeItem->id,
                        'description' => $feeItem->item_name,
                        'quantity' => $feeItem->quantity,
                        'unit_price' => $feeItem->amount,
                        'total' => $feeItem->balance,
                    ]);
                }

                $generatedCount++;
            }

            DB::commit();

            $message = "{$generatedCount} invoices generated successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to generate invoices: ' . $e->getMessage());
        }
    }

    /**
     * Bulk send invoices.
     */
    public function bulkSend(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        try {
            $sentCount = 0;
            $errors = [];

            foreach ($request->invoice_ids as $invoiceId) {
                $invoice = Invoice::with('student')->find($invoiceId);

                if (empty($invoice->student->email)) {
                    $errors[] = "Invoice {$invoice->invoice_number}: No email address";
                    continue;
                }

                // Generate PDF
                $pdf = Pdf::loadView('ktvtc.admin.invoices.pdf', compact('invoice'))
                    ->setPaper('A4', 'portrait');

                $path = 'invoices/' . $invoice->invoice_number . '.pdf';
                $pdf->save(storage_path('app/public/' . $path));

                // Send email
                // Mail::to($invoice->student->email)->send(new InvoiceMail($invoice, $path));

                $invoice->update(['status' => 'sent']);
                $sentCount++;
            }

            $message = "{$sentCount} invoices sent successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send invoices: ' . $e->getMessage());
        }
    }

    /**
     * Get invoices by enrollment (API).
     */
    public function getByEnrollment(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
        ]);

        $invoices = Invoice::where('enrollment_id', $request->enrollment_id)
            ->with(['items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date->format('Y-m-d'),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'description' => $invoice->description,
                    'total_amount' => $invoice->total_amount,
                    'amount_paid' => $invoice->amount_paid,
                    'balance' => $invoice->balance,
                    'status' => $invoice->status,
                    'status_color' => $this->getStatusColor($invoice->status),
                    'items_count' => $invoice->items->count(),
                    'payments_count' => $invoice->payments->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'invoices' => $invoices,
            'summary' => [
                'total_invoices' => $invoices->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'total_paid' => $invoices->sum('amount_paid'),
                'total_balance' => $invoices->sum('balance'),
            ]
        ]);
    }

    /**
     * Get outstanding invoices (API).
     */
    public function getOutstanding(Request $request)
    {
        $user = auth()->user();

        $invoices = Invoice::with(['student', 'enrollment.course'])
            ->whereIn('status', ['sent', 'partial'])
            ->where('balance', '>', 0)
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            })
            ->orderBy('due_date')
            ->limit(50)
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_name' => $invoice->student->full_name,
                    'student_number' => $invoice->student->student_number,
                    'course' => $invoice->enrollment->course->name ?? 'N/A',
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'total_amount' => $invoice->total_amount,
                    'balance' => $invoice->balance,
                    'days_overdue' => $invoice->due_date->diffInDays(now(), false),
                ];
            });

        return response()->json([
            'success' => true,
            'outstanding_invoices' => $invoices,
            'total_outstanding' => $invoices->sum('balance'),
            'count' => $invoices->count(),
        ]);
    }

    /**
     * Get overdue invoices (API).
     */
    public function getOverdue(Request $request)
    {
        $user = auth()->user();

        $invoices = Invoice::with(['student', 'enrollment.course'])
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->where('balance', '>', 0)
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            })
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_name' => $invoice->student->full_name,
                    'student_number' => $invoice->student->student_number,
                    'course' => $invoice->enrollment->course->name ?? 'N/A',
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'total_amount' => $invoice->total_amount,
                    'balance' => $invoice->balance,
                    'days_overdue' => $invoice->due_date->diffInDays(now(), false),
                ];
            });

        return response()->json([
            'success' => true,
            'overdue_invoices' => $invoices,
            'total_overdue' => $invoices->sum('balance'),
            'count' => $invoices->count(),
        ]);
    }

    /**
     * Get status color helper.
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'partial' => 'yellow',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }
}
