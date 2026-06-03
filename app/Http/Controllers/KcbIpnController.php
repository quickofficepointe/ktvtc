<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbTransaction;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KcbIpnController extends Controller
{
    /**
     * Handle KCB IPN payment notification
     * POST /api/kcb/ipn/payment-notification
     */
    public function handlePaymentNotification(Request $request)
    {
        Log::info('KCB IPN Received', ['payload' => $request->all()]);

        try {
            // Extract from KCB IPN payload
            $transactionReference = $request->input('transactionReference');
            $requestId = $request->input('requestId');
            $channelCode = $request->input('channelCode');
            $timestamp = $request->input('timestamp');
            $amount = $request->input('transactionAmount');
            $currency = $request->input('currency');
            $customerReference = $request->input('customerReference');
            $customerName = $request->input('customerName');
            $customerMobileNumber = $request->input('customerMobileNumber');
            $balance = $request->input('balance');
            $narration = $request->input('narration');
            $tillNumber = $request->input('tillNumber');
            $organizationShortCode = $request->input('organizationShortCode');

            // Validate required fields
            if (!$transactionReference || !$amount || !$customerReference) {
                Log::error('KCB IPN: Missing required fields', [
                    'transactionReference' => $transactionReference,
                    'amount' => $amount,
                    'customerReference' => $customerReference
                ]);
                return response()->json([
                    'statusCode' => '1',
                    'statusMessage' => 'Missing required fields'
                ], 200);
            }

            // Verify this is for your till
            if ($tillNumber && $tillNumber != '7664166') {
                Log::warning('KCB IPN: Wrong till number', ['till' => $tillNumber]);
                return response()->json([
                    'statusCode' => '1',
                    'statusMessage' => 'Invalid till number'
                ], 200);
            }

            // Check for duplicate IPN
            $existingTransaction = KcbTransaction::where('transaction_reference', $transactionReference)
                ->orWhere('request_id', $requestId)
                ->first();

            if ($existingTransaction) {
                Log::info('Duplicate KCB IPN ignored', ['transactionReference' => $transactionReference]);
                return response()->json([
                    'statusCode' => '0',
                    'statusMessage' => 'Duplicate IPN - Already processed'
                ], 200);
            }

            // ========== FIX: Extract student number correctly ==========
            // Handle both formats:
            // - "7664166#FBOM/01653" -> "FBOM/01653"
            // - "#FBOM/01653" -> "FBOM/01653"
            // - "FBOM/01653" -> "FBOM/01653"
            $studentNumber = $customerReference;

            // Remove till number prefix if present (7664166#)
            $studentNumber = preg_replace('/^7664166#/', '', $studentNumber);

            // Remove any # prefix
            $studentNumber = ltrim($studentNumber, '#');

            // Remove any trailing spaces
            $studentNumber = trim($studentNumber);

            Log::info('KCB IPN: Extracted student number', [
                'original' => $customerReference,
                'extracted' => $studentNumber
            ]);
            // ========== END OF FIX ==========

            // Find the student
            $student = Student::where('student_number', $studentNumber)->first();

            if (!$student) {
                Log::warning('KCB IPN: Student not found', ['student_number' => $studentNumber]);

                // Log the raw transaction first
                $kcbTransaction = KcbTransaction::create([
                    'transaction_reference' => $transactionReference,
                    'request_id' => $requestId,
                    'channel_code' => $channelCode,
                    'timestamp' => $timestamp,
                    'transaction_amount' => $amount,
                    'currency' => $currency,
                    'customer_reference' => $customerReference,
                    'customer_name' => $customerName,
                    'customer_mobile_number' => $customerMobileNumber,
                    'balance' => $balance,
                    'narration' => $narration,
                    'till_number' => $tillNumber,
                    'organization_short_code' => $organizationShortCode,
                    'raw_payload' => $request->all(),
                    'ip_address' => $request->ip(),
                    'student_number' => $studentNumber,
                ]);

                return response()->json([
                    'statusCode' => '2',
                    'statusMessage' => "Student not found: {$studentNumber}"
                ], 200);
            }

            // Log the raw transaction with student ID
            $kcbTransaction = KcbTransaction::create([
                'transaction_reference' => $transactionReference,
                'request_id' => $requestId,
                'channel_code' => $channelCode,
                'timestamp' => $timestamp,
                'transaction_amount' => $amount,
                'currency' => $currency,
                'customer_reference' => $customerReference,
                'customer_name' => $customerName,
                'customer_mobile_number' => $customerMobileNumber,
                'balance' => $balance,
                'narration' => $narration,
                'till_number' => $tillNumber,
                'organization_short_code' => $organizationShortCode,
                'raw_payload' => $request->all(),
                'ip_address' => $request->ip(),
                'student_number' => $studentNumber,
                'student_id' => $student->id,
            ]);

            // Find active enrollment with balance
            $enrollment = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->where('balance', '>', 0)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$enrollment) {
                Log::warning('KCB IPN: No active enrollment with balance', [
                    'student_id' => $student->id,
                    'student_number' => $studentNumber
                ]);

                return response()->json([
                    'statusCode' => '3',
                    'statusMessage' => 'No active enrollment with outstanding balance'
                ], 200);
            }

            // Check for duplicate payment
            $existingPayment = FeePayment::where('transaction_code', $transactionReference)
                ->orWhere('kcb_transaction_id', $transactionReference)
                ->first();

            if ($existingPayment) {
                Log::info('KCB IPN: Duplicate payment ignored', ['transactionReference' => $transactionReference]);
                return response()->json([
                    'statusCode' => '0',
                    'statusMessage' => 'Duplicate payment - Already recorded'
                ], 200);
            }

            // Process the payment
            DB::beginTransaction();

            try {
                // Generate receipt number
                $receiptNumber = $this->generateReceiptNumber();

                // Create payment record
                $payment = FeePayment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'amount' => (float) $amount,
                    'payment_date' => now(),
                    'receipt_number' => $receiptNumber,
                    'payment_method' => 'kcb',
                    'transaction_code' => $transactionReference,
                    'kcb_transaction_id' => $transactionReference,
                    'bill_reference_number' => $customerReference,
                    'payer_name' => $customerName ?? ($student->first_name . ' ' . $student->last_name),
                    'payer_phone' => $customerMobileNumber ?? $student->phone,
                    'payer_type' => 'student',
                    'status' => 'completed',
                    'is_verified' => true,
                    'verified_at' => now(),
                    'import_source' => 'kcb_ipn',
                    'notes' => "KCB Payment via Till {$tillNumber} | Ref: {$customerReference}"
                ]);

                // Update enrollment
                $oldBalance = $enrollment->balance;
                $enrollment->amount_paid = $enrollment->amount_paid + (float) $amount;
                $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
                $enrollment->save();

                // Update KCB transaction record
                $kcbTransaction->update([
                    'fee_payment_id' => $payment->id,
                    'processed_at' => now(),
                ]);

                DB::commit();

                // Send SMS confirmation
                $this->sendPaymentConfirmation($student, $enrollment, $amount, $receiptNumber);

                Log::info('KCB IPN: Payment processed successfully', [
                    'payment_id' => $payment->id,
                    'student_number' => $studentNumber,
                    'amount' => $amount,
                    'receipt' => $receiptNumber
                ]);

                return response()->json([
                    'statusCode' => '0',
                    'statusMessage' => 'IPN processed successfully'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('KCB IPN Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'statusCode' => '99',
                'statusMessage' => 'Internal server error'
            ], 200);
        }
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $lastPayment = FeePayment::whereDate('created_at', today())->count();
        $sequence = str_pad($lastPayment + 1, 4, '0', STR_PAD_LEFT);
        return "KCB-{$year}{$month}{$day}-{$sequence}";
    }

    /**
     * Send SMS confirmation
     */
    private function sendPaymentConfirmation($student, $enrollment, $amount, $receiptNumber)
    {
        try {
            $smsService = new SmsService();

            $message = "Dear {$student->first_name}, we have received your KCB payment of KES " .
                      number_format($amount, 2) . " for {$enrollment->course_name}. " .
                      "Receipt: {$receiptNumber}. Balance: KES " .
                      number_format($enrollment->balance, 2) . ". Thank you for choosing KTVTC.";

            if ($student->phone) {
                $smsService->sendSingleSms($student->phone, $message);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send payment confirmation SMS', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check transaction status (for debugging)
     */
    public function checkStatus($transactionId)
    {
        $transaction = KcbTransaction::where('transaction_reference', $transactionId)
            ->orWhere('request_id', $transactionId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'transaction_reference' => $transaction->transaction_reference,
                'amount' => $transaction->transaction_amount,
                'student_number' => $transaction->student_number,
                'status' => $transaction->fee_payment_id ? 'processed' : 'pending',
                'payment_id' => $transaction->fee_payment_id,
                'created_at' => $transaction->created_at
            ]
        ]);
    }

    /**
     * Test endpoint (for debugging)
     * GET /api/kcb/ipn/test
     */
    public function test()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'KCB IPN endpoint is working',
            'endpoint' => '/api/kcb/ipn/payment-notification',
            'method' => 'POST',
            'till_number' => '7664166',
            'reference_format' => '7664166#STUDENTNUMBER or #STUDENTNUMBER'
        ]);
    }
}
