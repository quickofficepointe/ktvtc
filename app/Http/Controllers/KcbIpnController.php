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
        // Get the signature from headers
        $signature = $request->header('signature');
        $payload = $request->getContent();

        Log::info('KCB IPN Received', [
            'signature_present' => !empty($signature),
            'payload' => $request->all()
        ]);

        // Extract from KCB IPN payload (using correct field names from docs)
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
        $transactionDate = $notificationData['transactionDate'] ?? null;
        $transactionID = $notificationData['transactionID'] ?? null;
        $firstName = $notificationData['firstName'] ?? null;
        $middleName = $notificationData['middleName'] ?? null;
        $lastName = $notificationData['lastName'] ?? null;
        $currency = $notificationData['currency'] ?? null;
        $transactionType = $notificationData['transactionType'] ?? null;
        $balance = $notificationData['balance'] ?? null;

        // Validate required fields
        if (!$transactionID || !$amount || !$invoiceNumber) {
            Log::error('KCB IPN: Missing required fields', [
                'transactionID' => $transactionID,
                'amount' => $amount,
                'invoiceNumber' => $invoiceNumber
            ]);

            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Missing required fields', null);
        }

        // ============================================================
        // SIGNATURE VERIFICATION - TEMPORARILY DISABLED FOR TESTING
        // TODO: Set $bypassSignature = false after KCB provides public key
        // ============================================================
        $bypassSignature = true; // CHANGE TO false WHEN KCB PROVIDES PUBLIC KEY

        if (!$bypassSignature && config('app.env') === 'production') {
            $publicKey = $this->getKcbPublicKey();
            if (!$this->verifySignature($payload, $signature, $publicKey)) {
                Log::error('KCB IPN: Invalid signature', [
                    'transactionID' => $transactionID,
                    'signature' => $signature
                ]);
                return $this->sendResponse($messageId, $originatorConversationID, '1', 'Invalid signature', null);
            }
            Log::info('KCB IPN: Signature verified successfully', ['transactionID' => $transactionID]);
        } else {
            Log::warning('KCB IPN: Signature verification TEMPORARILY BYPASSED for testing - Waiting for KCB public key');
        }
        // ============================================================

        // Check for duplicate IPN
        $existingTransaction = KcbTransaction::where('transaction_reference', $transactionID)->first();
        if ($existingTransaction) {
            Log::info('KCB IPN: Duplicate ignored', ['transactionID' => $transactionID]);
            return $this->sendResponse($messageId, $originatorConversationID, '0', 'Duplicate - Already processed', $transactionID);
        }

        // Extract student number from invoice number
        // Format: "7664166#HDBT/18/2020" -> "HDBT/18/2020"
        $studentNumber = $this->extractStudentNumber($invoiceNumber);

        if (!$studentNumber) {
            Log::warning('KCB IPN: Could not extract student number', ['invoiceNumber' => $invoiceNumber]);
            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Invalid invoice number format', null);
        }

        // Find the student
        $student = Student::where('student_number', $studentNumber)->first();

        if (!$student) {
            Log::warning('KCB IPN: Student not found', ['student_number' => $studentNumber]);

            // Store the raw transaction for manual reconciliation
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
                'balance' => $balance,
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
                'transaction_code' => $transactionID,
                'bill_reference_number' => $invoiceNumber,
                'payer_name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'payer_phone' => $debitMSISDN,
                'payer_type' => 'student',
                'status' => 'completed',
                'is_verified' => true,
                'verified_at' => now(),
                'import_source' => 'kcb_ipn',
                'notes' => "KCB Payment via Till {$businessKey} | Ref: {$invoiceNumber}"
            ]);

            // Update enrollment
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
                'balance' => $balance,
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
            Log::error('KCB IPN: Processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendResponse($messageId, $originatorConversationID, '1', 'Internal processing error', null);
        }
    }

    /**
     * Send response in KCB expected format
     */
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

    /**
     * Extract student number from KCB invoice number
     * Format: "7664166#HDBT/18/2020" -> "HDBT/18/2020"
     */
    private function extractStudentNumber($invoiceNumber)
    {
        if (!$invoiceNumber) {
            return null;
        }

        // If there's a #, take everything after it
        if (strpos($invoiceNumber, '#') !== false) {
            $parts = explode('#', $invoiceNumber);
            return trim(end($parts));
        }

        return trim($invoiceNumber);
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
     * Generate message ID
     */
    private function generateMessageId()
    {
        return uuid_create();
    }

    /**
     * Generate transaction ID
     */
    private function generateTransactionId()
    {
        return 'TXN_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get KCB public key for signature verification
     */
    private function getKcbPublicKey()
    {
        // Store your KCB public key in .env or config
        $publicKeyPath = storage_path('kcb_public_key.pem');

        if (file_exists($publicKeyPath)) {
            return file_get_contents($publicKeyPath);
        }

        // Or return the key as string from config
        return config('services.kcb.public_key');
    }

    /**
     * Verify RSA signature
     */
    private function verifySignature($payload, $signature, $publicKey)
    {
        if (empty($signature) || empty($publicKey)) {
            return false;
        }

        // Decode base64 signature
        $decodedSignature = base64_decode($signature);

        // Verify using openssl
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        $result = openssl_verify($payload, $decodedSignature, $publicKeyResource, OPENSSL_ALGO_SHA256);

        openssl_free_key($publicKeyResource);

        return $result === 1;
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
     */
    public function test()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'KCB IPN endpoint is working',
            'signature_verification' => 'TEMPORARILY DISABLED FOR TESTING',
            'endpoint' => '/api/kcb/ipn/payment-notification',
            'method' => 'POST',
            'required_response_format' => [
                'header' => [
                    'messageID' => 'string',
                    'originatorConversationID' => 'string',
                    'statusCode' => '0 or 1',
                    'statusMessage' => 'string'
                ],
                'responsePayload' => [
                    'transactionInfo' => [
                        'transactionId' => 'string'
                    ]
                ]
            ]
        ]);
    }
}
