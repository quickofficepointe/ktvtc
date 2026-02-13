<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\StudentFee;
use App\Models\Student;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class FeePaymentController extends Controller
{
    /**
     * Display a listing of fee payments.
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $query = FeePayment::with(['student', 'studentFee', 'registration.course'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('registration_id')) {
            $query->where('registration_id', $request->registration_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified == '1');
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        if ($request->filled('receipt_number')) {
            $query->where('receipt_number', 'like', '%' . $request->receipt_number . '%');
        }

        // Get fee payments
        $feePayments = $query->paginate(50);

        // Get filter data
        $students = User::where('role', 5) // 5 = Student role in your system
    ->orderBy('name')
    ->get();


        $registrations = Registration::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        $paymentMethods = [
            'kcb_stk_push' => 'KCB STK Push',
            'paybill' => 'Paybill',
            'bank_deposit' => 'Bank Deposit',
            'cash' => 'Cash',
            'helb' => 'HELB',
            'sponsor' => 'Sponsor',
            'other' => 'Other',
        ];

        $paymentStatuses = [
            'initiated' => 'Initiated',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'reversed' => 'Reversed',
            'disputed' => 'Disputed',
            'refunded' => 'Refunded',
        ];

        // Calculate statistics
        $totalPayments = FeePayment::count();
        $totalAmount = FeePayment::where('status', 'completed')->sum('amount');
        $todayAmount = FeePayment::whereDate('payment_date', today())
            ->where('status', 'completed')
            ->sum('amount');
        $pendingVerification = FeePayment::where('status', 'completed')
            ->where('is_verified', false)
            ->count();
        $kcbPayments = FeePayment::where('payment_method', 'kcb_stk_push')
            ->where('status', 'completed')
            ->count();

        return view('ktvtc.admin.fee-payments.index', compact(
            'feePayments',
            'students',
            'registrations',
            'paymentMethods',
            'paymentStatuses',
            'totalPayments',
            'totalAmount',
            'todayAmount',
            'pendingVerification',
            'kcbPayments'
        ));
    }

    /**
     * Show the form for creating a new fee payment.
     */
    public function create(Request $request)
    {
        $students = User::where('role', 5) // 5 = Student role in your system
    ->orderBy('name')
    ->get();


        $studentFees = StudentFee::whereIn('payment_status', ['pending', 'partial'])
            ->with(['student', 'registration'])
            ->orderBy('due_date')
            ->get();

        $paymentMethods = [
            'kcb_stk_push' => 'KCB STK Push',
            'paybill' => 'Paybill',
            'bank_deposit' => 'Bank Deposit',
            'cash' => 'Cash',
            'helb' => 'HELB',
            'sponsor' => 'Sponsor',
            'other' => 'Other',
        ];

        $payerTypes = [
            'student' => 'Student',
            'parent' => 'Parent/Guardian',
            'sponsor' => 'Sponsor',
            'employer' => 'Employer',
            'other' => 'Other',
        ];

        // If student_id is provided, load their pending fees
        $studentId = $request->input('student_id');
        $pendingFees = [];
        if ($studentId) {
            $pendingFees = StudentFee::where('student_id', $studentId)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->with('registration.course')
                ->get();
        }

        return view('ktvtc.admin.fee-payments.create', compact(
            'students',
            'studentFees',
            'paymentMethods',
            'payerTypes',
            'pendingFees'
        ));
    }

    /**
     * Store a newly created fee payment.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'student_fee_id' => 'required|exists:student_fees,id',
            'student_id' => 'required|exists:users,id',
            'registration_id' => 'required|exists:registrations,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:kcb_stk_push,paybill,bank_deposit,cash,helb,sponsor,other',
            'payment_date' => 'required|date',
            'payment_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',

            // Payer information
            'payer_name' => 'required|string|max:255',
            'payer_type' => 'required|in:student,parent,sponsor,employer,other',
            'payer_phone' => 'required|string|max:20',
            'payer_email' => 'nullable|email|max:255',
            'payer_id_number' => 'nullable|string|max:50',
            'payer_address' => 'nullable|string|max:500',

            // KCB STK Push details
            'kcb_transaction_code' => 'required_if:payment_method,kcb_stk_push|nullable|string|max:50',
            'kcb_phone_number' => 'required_if:payment_method,kcb_stk_push|nullable|string|max:20',
            'kcb_account_number' => 'nullable|string|max:50',

            // Paybill details
            'paybill_number' => 'required_if:payment_method,paybill|nullable|string|max:20',
            'paybill_account_number' => 'required_if:payment_method,paybill|nullable|string|max:50',
            'paybill_transaction_code' => 'required_if:payment_method,paybill|nullable|string|max:50',

            // Bank deposit details
            'bank_name' => 'required_if:payment_method,bank_deposit|nullable|string|max:100',
            'bank_branch' => 'required_if:payment_method,bank_deposit|nullable|string|max:100',
            'deposit_slip_number' => 'required_if:payment_method,bank_deposit|nullable|string|max:50',
            'deposit_date' => 'required_if:payment_method,bank_deposit|nullable|date',

            // Cash details
            'cash_receipt_number' => 'required_if:payment_method,cash|nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get student fee details
            $studentFee = StudentFee::findOrFail($request->student_fee_id);

            // Check if payment amount exceeds balance
            if ($request->amount > $studentFee->balance) {
                return redirect()->back()
                    ->with('error', 'Payment amount cannot exceed the invoice balance of KES ' . number_format($studentFee->balance, 2))
                    ->withInput();
            }

            // Calculate balances
            $balanceBefore = $studentFee->balance;
            $balanceAfter = $balanceBefore - $request->amount;

            // Create fee payment
            $feePayment = FeePayment::create([
                'student_fee_id' => $request->student_fee_id,
                'student_id' => $request->student_id,
                'registration_id' => $request->registration_id,
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => 'KES',
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'payment_time' => $request->payment_time,
                'description' => $request->description,
                'notes' => $request->notes,
                'status' => 'completed', // Auto-complete for manual entries
                'is_verified' => $request->payment_method != 'kcb_stk_push', // Auto-verify non-KCB payments
                'payer_name' => $request->payer_name,
                'payer_type' => $request->payer_type,
                'payer_phone' => $request->payer_phone,
                'payer_email' => $request->payer_email,
                'payer_id_number' => $request->payer_id_number,
                'payer_address' => $request->payer_address,
                'recorded_by' => Auth::id(),
            ]);

            // Set method-specific details
            switch ($request->payment_method) {
                case 'kcb_stk_push':
                    $feePayment->update([
                        'kcb_transaction_code' => $request->kcb_transaction_code,
                        'kcb_phone_number' => $request->kcb_phone_number,
                        'kcb_account_number' => $request->kcb_account_number,
                        'is_verified' => false, // KCB payments need verification
                    ]);
                    break;

                case 'paybill':
                    $feePayment->update([
                        'paybill_number' => $request->paybill_number,
                        'paybill_account_number' => $request->paybill_account_number,
                        'paybill_transaction_code' => $request->paybill_transaction_code,
                    ]);
                    break;

                case 'bank_deposit':
                    $feePayment->update([
                        'bank_name' => $request->bank_name,
                        'bank_branch' => $request->bank_branch,
                        'deposit_slip_number' => $request->deposit_slip_number,
                        'deposit_date' => $request->deposit_date,
                        'reference_number' => $request->deposit_slip_number,
                    ]);
                    break;

                case 'cash':
                    $feePayment->update([
                        'reference_number' => $request->cash_receipt_number,
                    ]);
                    break;
            }

            // Update student fee
            $studentFee->recordPayment($request->amount);

            // Update registration balance
            $registration = Registration::find($request->registration_id);
            if ($registration) {
                $registration->calculateBalance();
            }

            DB::commit();

            // Generate receipt if auto-generate is enabled
            if ($request->has('generate_receipt')) {
                $feePayment->sendReceipt(
                    $request->has('send_email'),
                    $request->has('send_sms')
                );
            }

            return redirect()->route('admin.fee-payments.show', $feePayment)
                ->with('success', 'Payment recorded successfully! Receipt #' . $feePayment->receipt_number);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified fee payment.
     */
    public function show(FeePayment $feePayment)
    {
        $feePayment->load([
            'student',
            'studentFee',
            'registration.course',
            'registration.campus',
            'recorder',
            'verifier',
            'approver'
        ]);

        // Get related payments
        $relatedPayments = FeePayment::where('student_id', $feePayment->student_id)
            ->where('id', '!=', $feePayment->id)
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        // Get payment details based on method
        $paymentDetails = $this->getPaymentDetails($feePayment);

        return view('ktvtc.admin.fee-payments.show', compact(
            'feePayment',
            'relatedPayments',
            'paymentDetails'
        ));
    }

    /**
     * Verify a payment.
     */
    public function verify(FeePayment $feePayment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if payment is already verified
            if ($feePayment->is_verified) {
                return redirect()->back()
                    ->with('error', 'Payment is already verified.');
            }

            $feePayment->verifyPayment(
                Auth::id(),
                $request->verification_notes
            );

            return redirect()->back()
                ->with('success', 'Payment verified successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Reverse a payment.
     */
    public function reverse(FeePayment $feePayment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if payment can be reversed
            if ($feePayment->status === 'reversed') {
                return redirect()->back()
                    ->with('error', 'Payment is already reversed.');
            }

            $feePayment->reversePayment(
                $request->reason,
                Auth::id()
            );

            return redirect()->back()
                ->with('success', 'Payment reversed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reverse payment: ' . $e->getMessage());
        }
    }

    /**
     * Send receipt for payment.
     */
    public function sendReceipt(FeePayment $feePayment, Request $request)
    {
        try {
            $feePayment->sendReceipt(
                $request->has('send_email'),
                $request->has('send_sms')
            );

            return redirect()->back()
                ->with('success', 'Receipt sent successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send receipt: ' . $e->getMessage());
        }
    }

    /**
     * Download receipt PDF.
     */
    public function downloadReceipt(FeePayment $feePayment)
    {
        $feePayment->load(['student', 'registration.course', 'registration.campus', 'studentFee']);

        $data = $feePayment->getReceiptData();

        $pdf = PDF::loadView('ktvtc.admin.fee-payments.receipt-pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download('receipt-' . $feePayment->receipt_number . '.pdf');
    }

    /**
     * Show receipt.
     */
    public function showReceipt(FeePayment $feePayment)
    {
        $feePayment->load(['student', 'registration.course', 'registration.campus', 'studentFee']);

        $data = $feePayment->getReceiptData();

        return view('ktvtc.admin.fee-payments.receipt', $data);
    }

    /**
     * Process KCB STK Push callback.
     */
    public function processKcbCallback(Request $request)
    {
        // Validate callback data
        $validator = Validator::make($request->all(), [
            'MerchantRequestID' => 'required|string',
            'CheckoutRequestID' => 'required|string',
            'ResultCode' => 'required|integer',
            'ResultDesc' => 'required|string',
            'TransactionID' => 'nullable|string',
            'PhoneNumber' => 'nullable|string',
            'Amount' => 'nullable|numeric',
            'MpesaReceiptNumber' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Validation failed'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Find payment by merchant request ID
            $feePayment = FeePayment::where('kcb_merchant_request_id', $request->MerchantRequestID)
                ->orWhere('kcb_checkout_request_id', $request->CheckoutRequestID)
                ->first();

            if (!$feePayment) {
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Payment not found'
                ], 404);
            }

            if ($request->ResultCode == 0) {
                // Payment successful
                $feePayment->update([
                    'kcb_transaction_code' => $request->MpesaReceiptNumber,
                    'kcb_phone_number' => $request->PhoneNumber,
                    'status' => 'completed',
                    'processed_at' => now(),
                    'amount' => $request->Amount ?? $feePayment->amount,
                    'metadata' => array_merge($feePayment->metadata ?? [], [
                        'callback_data' => $request->all(),
                        'callback_time' => now()->toISOString(),
                    ]),
                ]);

                // Update student fee
                if ($feePayment->studentFee) {
                    $feePayment->studentFee->recordPayment($feePayment->amount);
                }

                DB::commit();

                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Payment processed successfully'
                ]);

            } else {
                // Payment failed
                $feePayment->update([
                    'status' => 'failed',
                    'notes' => $feePayment->notes . "\nPayment failed: " . $request->ResultDesc,
                    'metadata' => array_merge($feePayment->metadata ?? [], [
                        'callback_data' => $request->all(),
                        'callback_time' => now()->toISOString(),
                    ]),
                ]);

                DB::commit();

                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Payment status updated'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk verify payments.
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fee_payments,id',
            'verification_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $feePayments = FeePayment::whereIn('id', $request->ids)
                ->where('is_verified', false)
                ->get();

            $successCount = 0;
            $errors = [];

            foreach ($feePayments as $payment) {
                try {
                    $payment->verifyPayment(
                        Auth::id(),
                        $request->verification_notes
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Payment {$payment->receipt_number}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} payments verified successfully.",
                'errors' => $errors,
                'verified' => $successCount,
                'failed' => count($errors)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Bulk verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics.
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $query = FeePayment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Daily totals
        $dailyTotals = $query->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->total];
            });

        // Method breakdown
        $methodBreakdown = $query->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Payer type breakdown
        $payerBreakdown = $query->selectRaw('payer_type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payer_type')
            ->get();

        // Top students by payment
        $topStudents = $query->selectRaw('student_id, SUM(amount) as total')
            ->with('student')
            ->groupBy('student_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_totals' => $dailyTotals,
                'method_breakdown' => $methodBreakdown,
                'payer_breakdown' => $payerBreakdown,
                'top_students' => $topStudents,
                'summary' => [
                    'total_payments' => $query->count(),
                    'total_amount' => $query->sum('amount'),
                    'average_payment' => $query->avg('amount'),
                ]
            ]
        ]);
    }

    /**
     * Get student pending fees for payment.
     */
    public function getStudentPendingFees(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $pendingFees = StudentFee::where('student_id', $request->student_id)
            ->whereIn('payment_status', ['pending', 'partial'])
            ->with(['registration.course', 'registration.campus'])
            ->get()
            ->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'invoice_number' => $fee->invoice_number,
                    'description' => $fee->description,
                    'due_date' => $fee->due_date->format('M j, Y'),
                    'total_amount' => $fee->total_amount,
                    'amount_paid' => $fee->amount_paid,
                    'balance' => $fee->balance,
                    'course' => $fee->registration->course->name ?? 'N/A',
                    'campus' => $fee->registration->campus->name ?? 'N/A',
                ];
            });

        $student = User::find($request->student_id);

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'phone' => $student->phone,
                'email' => $student->email,
            ],
            'pending_fees' => $pendingFees,
            'total_balance' => $pendingFees->sum('balance'),
        ]);
    }

    /**
     * Get payment details for display.
     */
    private function getPaymentDetails(FeePayment $feePayment)
    {
        $details = [];

        switch ($feePayment->payment_method) {
            case 'kcb_stk_push':
                $details = [
                    ['label' => 'Transaction Code', 'value' => $feePayment->kcb_transaction_code],
                    ['label' => 'Phone Number', 'value' => $feePayment->kcb_phone_number],
                    ['label' => 'Account Number', 'value' => $feePayment->kcb_account_number],
                    ['label' => 'Merchant Request ID', 'value' => $feePayment->kcb_merchant_request_id],
                    ['label' => 'Checkout Request ID', 'value' => $feePayment->kcb_checkout_request_id],
                ];
                break;

            case 'paybill':
                $details = [
                    ['label' => 'Paybill Number', 'value' => $feePayment->paybill_number],
                    ['label' => 'Account Number', 'value' => $feePayment->paybill_account_number],
                    ['label' => 'Transaction Code', 'value' => $feePayment->paybill_transaction_code],
                ];
                break;

            case 'bank_deposit':
                $details = [
                    ['label' => 'Bank Name', 'value' => $feePayment->bank_name],
                    ['label' => 'Branch', 'value' => $feePayment->bank_branch],
                    ['label' => 'Deposit Slip Number', 'value' => $feePayment->deposit_slip_number],
                    ['label' => 'Deposit Date', 'value' => $feePayment->deposit_date?->format('M j, Y')],
                ];
                break;

            case 'cash':
                $details = [
                    ['label' => 'Receipt Number', 'value' => $feePayment->reference_number],
                ];
                break;

            case 'helb':
                $details = [
                    ['label' => 'HELB Reference', 'value' => $feePayment->reference_number],
                ];
                break;

            case 'sponsor':
                $details = [
                    ['label' => 'Sponsor Reference', 'value' => $feePayment->reference_number],
                ];
                break;
        }

        // Add common details
        $details = array_merge($details, [
            ['label' => 'Reference Number', 'value' => $feePayment->reference_number],
            ['label' => 'Transaction ID', 'value' => $feePayment->transaction_id],
        ]);

        return array_filter($details, function ($item) {
            return !empty($item['value']);
        });
    }

    /**
     * Export payments.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_method' => 'nullable|in:kcb_stk_push,paybill,bank_deposit,cash,helb,sponsor,other',
            'status' => 'nullable|in:initiated,pending,completed,failed,reversed,disputed,refunded',
        ]);

        $query = FeePayment::with(['student', 'registration.course', 'studentFee']);

        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $feePayments = $query->orderBy('payment_date', 'desc')
            ->orderBy('payment_time', 'desc')
            ->get();

        // Generate filename
        $filename = 'fee-payments-' . date('Y-m-d-H-i-s');

        switch ($request->format) {
            case 'csv':
                return $this->exportToCsv($feePayments, $filename);
            case 'excel':
                return $this->exportToExcel($feePayments, $filename);
            case 'pdf':
                return $this->exportToPdf($feePayments, $filename);
            default:
                return redirect()->back()
                    ->with('error', 'Unsupported export format.');
        }
    }

    /**
     * Export to CSV.
     */
    private function exportToCsv($payments, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Receipt Number',
                'Transaction ID',
                'Date',
                'Time',
                'Student Name',
                'Student Number',
                'Course',
                'Invoice Number',
                'Payment Method',
                'Amount',
                'Payer Name',
                'Payer Phone',
                'Status',
                'Verified',
                'Recorded By',
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->receipt_number,
                    $payment->transaction_id,
                    $payment->payment_date->format('Y-m-d'),
                    $payment->payment_time,
                    $payment->student->name ?? 'N/A',
                    $payment->student->student_number ?? 'N/A',
                    $payment->registration->course->name ?? 'N/A',
                    $payment->studentFee->invoice_number ?? 'N/A',
                    $payment->payment_method_label,
                    $payment->amount,
                    $payment->payer_name,
                    $payment->payer_phone,
                    ucfirst($payment->status),
                    $payment->is_verified ? 'Yes' : 'No',
                    $payment->recorder->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Remove the specified fee payment.
     */
    public function destroy(FeePayment $feePayment)
    {
        try {
            // Check if payment can be deleted
            if ($feePayment->status === 'completed' && $feePayment->is_verified) {
                return redirect()->back()
                    ->with('error', 'Cannot delete a verified completed payment. Reverse it instead.');
            }

            $feePayment->delete();

            return redirect()->route('admin.fee-payments.index')
                ->with('success', 'Payment deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }
}
