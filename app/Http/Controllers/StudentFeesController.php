<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbBuniTransaction;
use App\Models\Student;
use App\Services\KcbService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class StudentFeesController extends Controller
{
    protected $kcbService;
    protected $smsService;

    public function __construct(KcbService $kcbService, SmsService $smsService)
    {
        // paymentCallback is called by KCB's server, which has no login
        $this->middleware('auth')->except('paymentCallback');
        $this->middleware('role.student')->except('paymentCallback');
        $this->kcbService = $kcbService;
        $this->smsService = $smsService;
    }

    /**
     * Show fee statement and payment page
     */
    public function index()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student record not found. Please contact administration.');
        }

        // Get all enrollments for this student
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with(['course', 'payments' => function($q) {
                $q->orderBy('payment_date', 'desc')->limit(10);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalFees = $enrollments->sum('total_fees');
        $totalPaid = $enrollments->sum('amount_paid');
        $totalBalance = $enrollments->sum('balance');

        // Get recent payments
        $recentPayments = FeePayment::where('student_id', $student->id)
            ->with('enrollment.course')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        return view('ktvtc.students.fees.index', compact(
            'student',
            'enrollments',
            'totalFees',
            'totalPaid',
            'totalBalance',
            'recentPayments'
        ));
    }

    /**
     * Show payment form for a specific enrollment
     */
    public function pay(Enrollment $enrollment)
    {
        $user = Auth::user();
        $student = $user->student;

        // Verify ownership
        if ($enrollment->student_id != $student->id) {
            abort(403, 'Unauthorized access to this enrollment');
        }

        if ($enrollment->balance <= 0) {
            return redirect()->route('student.fees.index')
                ->with('error', 'This enrollment has no outstanding balance.');
        }

        return view('ktvtc.students.fees.pay', compact('enrollment'));
    }

    /**
     * Initiate KCB payment
     */
    public function initiatePayment(Request $request, Enrollment $enrollment)
    {
        $user = Auth::user();
        $student = $user->student;

        // Verify ownership
        if ($enrollment->student_id != $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $enrollment->balance,
            'phone' => 'required|string'
        ]);

        // Generate invoice number: 7664166#STUDENT_NUMBER
        $invoiceNumber = '7664166#' . $student->student_number;

        // Validate and format phone number
        $formattedPhone = $this->kcbService->validatePhoneNumber($request->phone);
        if (!$formattedPhone) {
            return response()->json([
                'error' => 'Invalid phone number format. Please use format: 0712 345 678'
            ], 422);
        }

        // Prepare callback URL
        // Public URL: KCB's server can't log in, so the callback must sit outside the student area
        $callbackUrl = route('kcb.fees.callback');

        // Initiate STK Push
        $stkResponse = $this->kcbService->initiateStkPush(
            $formattedPhone,
            $request->amount,
            $invoiceNumber,
            $callbackUrl,
            'School Fees Payment - ' . $student->student_number
        );

        if (isset($stkResponse['error'])) {
            Log::error('Student fee payment initiation failed', [
                'student_id' => $student->id,
                'error' => $stkResponse['error']
            ]);
            return response()->json(['error' => $stkResponse['error']], 500);
        }

        // Create transaction record
        $transaction = KcbBuniTransaction::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
            'checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
            'phone_number' => $formattedPhone,
            'amount' => $request->amount,
            'invoice_number' => $invoiceNumber,
            'transaction_type' => 'school_fees',
            'status' => 'initiated',
            'request_data' => json_encode([
                'student_id' => $student->id,
                'student_number' => $student->student_number,
                'student_name' => $student->full_name,
                'enrollment_id' => $enrollment->id,
                'course_name' => $enrollment->course_name,
                'balance_before' => $enrollment->balance,
            ]),
        ]);

        Log::info('Student fee payment initiated', [
            'student_id' => $student->id,
            'transaction_id' => $transaction->id,
            'amount' => $request->amount
        ]);

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'merchant_request_id' => $transaction->merchant_request_id,
            'checkout_request_id' => $transaction->checkout_request_id,
            'message' => 'Payment request sent. Please check your phone to complete payment.',
        ]);
    }

    /**
     * Handle KCB payment callback
     */
    public function paymentCallback(Request $request)
    {
        Log::info('Student Fee Payment Callback Received', $request->all());

        $stkCallback = $request->input('Body.stkCallback', []);

        if (!$stkCallback) {
            Log::error('Invalid KCB callback format for student fees');
            return response()->json(['error' => 'Invalid callback format'], 400);
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        if (!$checkoutRequestId || $resultCode === null) {
            Log::warning('Missing required callback fields', $stkCallback);
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        // Find transaction
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$transaction) {
            Log::error('Transaction not found for callback', ['checkout_request_id' => $checkoutRequestId]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        DB::beginTransaction();

        try {
            $updateData = [
                'result_code' => $resultCode,
                'result_description' => $stkCallback['ResultDesc'] ?? '',
                'callback_data' => json_encode($request->all()),
            ];

            if ($resultCode == 0) {
                // Payment successful
                $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
                $metadata = [];

                foreach ($callbackMetadata as $item) {
                    if (isset($item['Name']) && isset($item['Value'])) {
                        $metadata[$item['Name']] = $item['Value'];
                    }
                }

                // Status only. The money is applied to the fee account when KCB's IPN
                // arrives (KcbIpnRouter), so a payment is never counted twice.
                $updateData['status'] = 'completed';
                $updateData['mpesa_receipt_number'] = $metadata['MpesaReceiptNumber'] ?? null;
                $updateData['transaction_date'] = isset($metadata['TransactionDate']) ?
                    $this->formatTransactionDate($metadata['TransactionDate']) : now();

                $transaction->update($updateData);

                Log::info('Student fee STK payment confirmed', [
                    'transaction_id' => $transaction->id,
                    'receipt' => $updateData['mpesa_receipt_number']
                ]);

            } else {
                // Payment failed
                $updateData['status'] = 'failed';
                $transaction->update($updateData);

                Log::warning('Student fee payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $resultCode,
                    'result_description' => $stkCallback['ResultDesc'] ?? 'Unknown error'
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student fee payment callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus($transactionId)
    {
        $transaction = KcbBuniTransaction::where('checkout_request_id', $transactionId)
            ->orWhere('id', $transactionId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $transaction->status,
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'amount' => $transaction->amount,
            'message' => $this->getStatusMessage($transaction->status, $transaction->result_description)
        ]);
    }

    /**
     * Download fee statement as PDF
     */
   /**
 * Download fee statement as PDF
 */
public function downloadStatement()
{
    $user = Auth::user();
    $student = $user->student;

    if (!$student) {
        return redirect()->back()->with('error', 'Student record not found.');
    }

    // Get all enrollments for this student
    $enrollments = Enrollment::where('student_id', $student->id)
        ->with(['course', 'payments'])
        ->get();

    // Get all payments
    $payments = FeePayment::where('student_id', $student->id)
        ->with('enrollment.course')
        ->orderBy('payment_date', 'desc')
        ->get();

    // Calculate totals
    $totalFees = $enrollments->sum('total_fees');
    $totalPaid = $enrollments->sum('amount_paid');
    $totalBalance = $enrollments->sum('balance');

    // Clean filename - replace slashes with underscores
    $cleanStudentNumber = str_replace('/', '_', $student->student_number);
    $filename = 'fee_statement_' . $cleanStudentNumber . '.pdf';

    // Load the PDF view
    $pdf = Pdf::loadView('ktvtc.students.fees.statement-pdf', compact(
        'student',
        'enrollments',
        'payments',
        'totalFees',
        'totalPaid',
        'totalBalance'
    ));

    // Set PDF options
    $pdf->setPaper('a4', 'portrait');
    $pdf->setOptions([
        'defaultFont' => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    // Download the PDF
    return $pdf->download($filename);
}

    /**
     * Format transaction date
     */
    private function formatTransactionDate($transactionDate)
    {
        if (strlen($transactionDate) === 14) {
            try {
                return \Carbon\Carbon::createFromFormat('YmdHis', $transactionDate);
            } catch (\Exception $e) {
                return now();
            }
        }
        return now();
    }

    /**
     * Get status message
     */
    private function getStatusMessage($status, $description)
    {
        switch ($status) {
            case 'completed':
                return 'Payment confirmed successfully!';
            case 'failed':
                return 'Payment failed: ' . ($description ?? 'Unknown error');
            case 'initiated':
                return 'Waiting for payment confirmation...';
            default:
                return 'Checking payment status...';
        }
    }
}
