<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbTransaction;
use App\Models\KcbBuniTransaction;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KcbIpnController extends Controller
{
    public function handlePaymentNotification(Request $request)
    {
        Log::info('KCB IPN Received', $request->all());

        // Extract from KCB IPN payload
        $header = $request->input('header', []);
        $requestPayload = $request->input('requestPayload', []);
        $primaryData = $requestPayload['primaryData'] ?? [];
        $notificationData = $requestPayload['additionalData']['notificationData'] ?? [];

        $messageId = $header['messageID'] ?? null;
        $originatorConversationID = $header['originatorConversationID'] ?? null;
        $channelCode = $header['channelCode'] ?? null;
        $timestamp = $header['timeStamp'] ?? null;

        $businessKey = $primaryData['businessKey'] ?? null;
        $invoiceNumber = $notificationData['businessKey'] ?? null;
        $debitMSISDN = $notificationData['debitMSISDN'] ?? null;
        $amount = $notificationData['transactionAmt'] ?? null;
        $transactionID = $notificationData['transactionID'] ?? null;
        $firstName = $notificationData['firstName'] ?? null;
        $middleName = $notificationData['middleName'] ?? null;
        $lastName = $notificationData['lastName'] ?? null;
        $currency = $notificationData['currency'] ?? null;
        $kcbBalance = $notificationData['balance'] ?? null;

        // Validate required fields
        if (!$transactionID || !$amount || !$invoiceNumber) {
            Log::error('KCB IPN: Missing required fields');
            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Missing required fields', null);
        }

        // Check for duplicate IPN in KcbTransaction
        $existingTransaction = KcbTransaction::where('transaction_reference', $transactionID)->first();
        if ($existingTransaction) {
            Log::info('KCB IPN: Duplicate ignored', ['transactionID' => $transactionID]);
            return $this->sendResponse($messageId, $originatorConversationID, '0', 'Duplicate - Already processed', $transactionID);
        }

        // Extract student number from invoice number
        $studentNumber = $this->extractStudentNumber($invoiceNumber);

        if (!$studentNumber) {
            Log::warning('KCB IPN: Could not extract student number', ['invoiceNumber' => $invoiceNumber]);
            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Invalid invoice number format', null);
        }

        // Find the student
        $student = Student::where('student_number', $studentNumber)
            ->orWhere('legacy_student_code', $studentNumber)
            ->first();

        if (!$student) {
            Log::warning('KCB IPN: Student not found', ['student_number' => $studentNumber]);

            KcbTransaction::create([
                'transaction_reference' => $transactionID,
                'request_id' => $messageId,
                'channel_code' => $channelCode,
                'timestamp' => $timestamp,
                'transaction_amount' => $amount,
                'currency' => $currency,
                'customer_reference' => $invoiceNumber,
                'customer_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'customer_mobile_number' => $debitMSISDN,
                'balance' => $kcbBalance,
                'narration' => $notificationData['narration'] ?? null,
                'till_number' => $businessKey,
                'raw_payload' => $request->all(),
                'ip_address' => $request->ip(),
                'student_number' => $studentNumber,
                'processed_at' => now(),
            ]);

            return $this->sendResponse($messageId, $originatorConversationID, '1', "Student not found: {$studentNumber}", null);
        }

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

            KcbTransaction::create([
                'transaction_reference' => $transactionID,
                'request_id' => $messageId,
                'channel_code' => $channelCode,
                'timestamp' => $timestamp,
                'transaction_amount' => $amount,
                'currency' => $currency,
                'customer_reference' => $invoiceNumber,
                'customer_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'customer_mobile_number' => $debitMSISDN,
                'balance' => $kcbBalance,
                'narration' => $notificationData['narration'] ?? null,
                'till_number' => $businessKey,
                'raw_payload' => $request->all(),
                'ip_address' => $request->ip(),
                'student_number' => $studentNumber,
                'student_id' => $student->id,
                'processed_at' => now(),
            ]);

            return $this->sendResponse($messageId, $originatorConversationID, '1', 'No active enrollment with outstanding balance', null);
        }

        // Check for duplicate payment
        $existingPayment = FeePayment::where('transaction_code', $transactionID)->first();
        if ($existingPayment) {
            Log::info('KCB IPN: Duplicate payment ignored', ['transactionID' => $transactionID]);
            return $this->sendResponse($messageId, $originatorConversationID, '0', 'Duplicate payment - Already recorded', $transactionID);
        }

        // Process the payment
        DB::beginTransaction();

        try {
            $receiptNumber = $this->generateReceiptNumber();

            // Create payment record
            $payment = FeePayment::create([
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount' => (float) $amount,
                'payment_date' => now(),
                'receipt_number' => $receiptNumber,
                'payment_method' => 'kcb',
                'transaction_code' => $transactionID,
                'bill_reference_number' => $invoiceNumber,
                'kcb_transaction_id' => $transactionID,
                'payer_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'payer_phone' => $debitMSISDN,
                'payer_type' => 'student',
                'status' => 'completed',
                'is_verified' => true,
                'verified_at' => now(),
                'import_source' => 'kcb_ipn',
                'notes' => "KCB Payment via Till {$businessKey} | Ref: {$invoiceNumber}"
            ]);

            // Update enrollment - update BOTH amount_paid AND balance
            $enrollment->amount_paid = $enrollment->amount_paid + (float) $amount;
            $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
            $enrollment->save();

            // Store the KCB transaction
            KcbTransaction::create([
                'transaction_reference' => $transactionID,
                'request_id' => $messageId,
                'channel_code' => $channelCode,
                'timestamp' => $timestamp,
                'transaction_amount' => $amount,
                'currency' => $currency,
                'customer_reference' => $invoiceNumber,
                'customer_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'customer_mobile_number' => $debitMSISDN,
                'balance' => $kcbBalance,
                'narration' => $notificationData['narration'] ?? null,
                'till_number' => $businessKey,
                'raw_payload' => $request->all(),
                'ip_address' => $request->ip(),
                'student_number' => $studentNumber,
                'student_id' => $student->id,
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
                'receipt' => $receiptNumber,
                'new_balance' => $enrollment->balance
            ]);

            return $this->sendResponse($messageId, $originatorConversationID, '0', 'Payment processed successfully', $transactionID);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KCB IPN: Processing error', ['error' => $e->getMessage()]);
            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Internal processing error', null);
        }
    }

    private function extractStudentNumber($invoiceNumber)
    {
        if (!$invoiceNumber) return null;

        if (strpos($invoiceNumber, '#') !== false) {
            $parts = explode('#', $invoiceNumber);
            return trim(end($parts));
        }

        return trim($invoiceNumber);
    }

    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $lastPayment = FeePayment::whereDate('created_at', today())->count();
        $sequence = str_pad($lastPayment + 1, 4, '0', STR_PAD_LEFT);
        return "KCB-{$year}{$month}{$day}-{$sequence}";
    }

    private function generateMessageId()
    {
        return uuid_create();
    }

    private function generateTransactionId()
    {
        return 'TXN_' . time() . '_' . rand(1000, 9999);
    }

    private function sendResponse($messageId, $originatorConversationID, $statusCode, $statusMessage, $transactionId)
    {
        $response = [
            'header' => [
                'messageID' => $messageId ?? $this->generateMessageId(),
                'originatorConversationID' => $originatorConversationID ?? '',
                'statusCode' => $statusCode,
                'statusMessage' => $statusMessage
            ],
            'responsePayload' => [
                'transactionInfo' => [
                    'transactionId' => $transactionId ?? $this->generateTransactionId()
                ]
            ]
        ];

        return response()->json($response, 200);
    }

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
            Log::warning('Failed to send payment confirmation SMS', ['error' => $e->getMessage()]);
        }
    }

    public function checkStatus($transactionId)
    {
        $transaction = KcbTransaction::where('transaction_reference', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
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

    public function test()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'KCB IPN endpoint is working',
            'endpoint' => '/api/kcb/ipn/payment-notification',
            'method' => 'POST'
        ]);
    }
}
