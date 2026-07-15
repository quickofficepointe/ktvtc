<?php
// app/Services/CardFundingService.php

namespace App\Services;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Models\KcbBuniTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardFundingService
{
    protected $kcbSalesService;
    protected $smsService;
    protected $cardService;

    public function __construct(
        KcbSalesService $kcbSalesService,
        SmsService $smsService,
        CardService $cardService
    ) {
        $this->kcbSalesService = $kcbSalesService;
        $this->smsService = $smsService;
        $this->cardService = $cardService;
    }

    /**
     * Initiate card funding via M-Pesa STK Push
     *
     * @param HighSchoolStudent $student
     * @param CardAccount $card
     * @param string $phoneNumber
     * @param float $amount
     * @param string $fundingType T=Transport, C=General
     * @return array
     */
    public function initiateFunding(
        HighSchoolStudent $student,
        CardAccount $card,
        $phoneNumber,
        $amount,
        $fundingType = 'T'
    ) {
        try {
            Log::info('Initiating card funding via M-Pesa', [
                'student_id' => $student->id,
                'admission' => $student->admission_number,
                'card_id' => $card->id,
                'amount' => $amount,
                'type' => $fundingType
            ]);

            // 1. Validate amount
            $validation = $this->validateAmount($amount);
            if ($validation !== true) {
                return $validation;
            }

            // 2. Validate phone number
            $formattedPhone = $this->kcbSalesService->validatePhoneNumber($phoneNumber);
            if (!$formattedPhone) {
                return ['error' => 'Invalid phone number format. Use format: 0712 345 678'];
            }

            // 3. Check for pending request
            $pendingRequest = $this->hasPendingRequest($student);
            if ($pendingRequest) {
                return [
                    'error' => 'You have a pending funding request. Please wait or check status.',
                    'pending_checkout_id' => $pendingRequest->checkout_request_id
                ];
            }

            // 4. Generate invoice number
            $typePrefix = $fundingType === 'T' ? 'T' : 'C';
            $invoiceNumber = '7722609#' . $typePrefix . $student->admission_number;
            $typeLabel = $fundingType === 'T' ? 'Transport' : 'General';
            $description = "Card Funding - {$typeLabel} - {$student->admission_number}";

            // 5. Build callback URL
            $callbackUrl = rtrim(config('app.url'), '/') . '/api/kcb/sales/callback';

            // 6. Initiate STK Push
            $stkResponse = $this->kcbSalesService->initiateStkPush(
                $formattedPhone,
                $amount,
                $invoiceNumber,
                $callbackUrl,
                $description
            );

            if (isset($stkResponse['error'])) {
                Log::error('Card funding STK Push failed', [
                    'student_id' => $student->id,
                    'error' => $stkResponse['error']
                ]);
                return ['error' => $stkResponse['error']];
            }

            // 7. Create transaction record
            DB::beginTransaction();

            $transaction = KcbBuniTransaction::create([
                'student_id' => $student->id,
                'card_account_id' => $card->id,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                'checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
                'phone_number' => $formattedPhone,
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'transaction_type' => 'card_funding',
                'status' => 'initiated',
                'request_data' => json_encode([
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'student_name' => $student->full_name,
                    'card_number' => $card->card_number,
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'amount' => $amount,
                    'phone_number' => $formattedPhone,
                ]),
            ]);

            // 8. Log audit
            CardAuditLog::log(
                $card->id,
                'funding_initiated',
                "Card funding initiated: KES {$amount} ({$typeLabel}) via M-Pesa",
                null,
                $card->card_number,
                [
                    'amount' => $amount,
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'checkout_request_id' => $transaction->checkout_request_id,
                    'phone_number' => $formattedPhone,
                    'transaction_id' => $transaction->id
                ]
            );

            DB::commit();

            Log::info('Card funding initiated successfully', [
                'student_id' => $student->id,
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $transaction->checkout_request_id
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'merchant_request_id' => $transaction->merchant_request_id,
                'checkout_request_id' => $transaction->checkout_request_id,
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'funding_type' => $fundingType,
                'type_label' => $typeLabel,
                'message' => 'Payment request sent. Check your phone to complete payment.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card funding initiation exception: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Payment service temporarily unavailable. Please try again.'];
        }
    }

    /**
     * Complete card funding after successful M-Pesa payment
     *
     * @param KcbBuniTransaction $transaction
     * @param array $callbackData
     * @param array $metadata
     * @return bool
     */
    public function completeFunding(
        KcbBuniTransaction $transaction,
        $callbackData,
        $metadata
    ) {
        DB::beginTransaction();

        try {
            $requestData = json_decode($transaction->request_data, true);
            $studentId = $requestData['student_id'] ?? null;
            $cardId = $transaction->card_account_id;
            $amount = $metadata['Amount'] ?? $transaction->amount;
            $mpesaReceipt = $metadata['MpesaReceiptNumber'] ?? null;
            $typeLabel = $requestData['type_label'] ?? 'Funding';

            // 1. Find the card
            $card = CardAccount::find($cardId);
            if (!$card) {
                Log::error('Card not found for funding completion', [
                    'card_id' => $cardId,
                    'transaction_id' => $transaction->id
                ]);
                DB::rollBack();
                return false;
            }

            // 2. Find the student
            $student = HighSchoolStudent::find($studentId);
            if (!$student) {
                Log::error('Student not found for funding completion', [
                    'student_id' => $studentId,
                    'transaction_id' => $transaction->id
                ]);
                DB::rollBack();
                return false;
            }

            // 3. Check for duplicate transaction
            $existingTransaction = CardTransaction::where('mpesa_receipt', $mpesaReceipt)
                ->orWhere('reference', $transaction->invoice_number)
                ->first();

            if ($existingTransaction) {
                Log::warning('Duplicate card funding detected', [
                    'mpesa_receipt' => $mpesaReceipt,
                    'existing_transaction_id' => $existingTransaction->id
                ]);

                // Update KCB transaction status
                $transaction->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReceipt,
                    'result_code' => 0,
                    'result_description' => 'Duplicate transaction - already processed',
                    'callback_data' => json_encode($callbackData),
                ]);

                DB::commit();
                return true;
            }

            // 4. Fund the card
            $card->balance += $amount;
            $card->total_funded += $amount;
            $card->last_funded_at = now();
            $card->last_funding_amount = $amount;
            $card->last_funding_source = $typeLabel;
            $card->save();

            // 5. Create card transaction
            $cardTransaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'balance_before' => $card->balance - $amount,
                'balance_after' => $card->balance,
                'description' => "{$typeLabel} funding via M-Pesa - Receipt: {$mpesaReceipt}",
                'mpesa_receipt' => $mpesaReceipt,
                'reference' => $transaction->invoice_number,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $transaction->checkout_request_id,
                    'funding_type' => $requestData['funding_type'] ?? 'T',
                    'type_label' => $typeLabel,
                    'phone_number' => $transaction->phone_number,
                ])
            ]);

            // 6. Update KCB transaction
            $transaction->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $mpesaReceipt,
                'transaction_date' => now(),
                'result_code' => 0,
                'result_description' => 'Payment successful',
                'callback_data' => json_encode($callbackData),
                'card_transaction_id' => $cardTransaction->id,
            ]);

            // 7. Log audit
            CardAuditLog::log(
                $card->id,
                'funding_completed',
                "Card funded successfully: KES {$amount} ({$typeLabel}) via M-Pesa. Receipt: {$mpesaReceipt}",
                null,
                $card->card_number,
                [
                    'amount' => $amount,
                    'funding_type' => $requestData['funding_type'] ?? 'T',
                    'type_label' => $typeLabel,
                    'mpesa_receipt' => $mpesaReceipt,
                    'transaction_id' => $transaction->id,
                    'card_transaction_id' => $cardTransaction->id,
                    'new_balance' => $card->balance,
                ]
            );

            DB::commit();

            Log::info('Card funding completed successfully', [
                'student_id' => $student->id,
                'card_id' => $card->id,
                'amount' => $amount,
                'receipt' => $mpesaReceipt,
                'new_balance' => $card->balance,
                'card_transaction_id' => $cardTransaction->id
            ]);

            // 8. Send notifications
            $this->sendFundingNotifications($student, $card, $amount, $mpesaReceipt, $typeLabel);

            // 9. Check low balance alert
            if ($card->balance > 0 && $card->balance < $card->low_balance_threshold) {
                $this->cardService->triggerLowBalanceAlert($card);
            }

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card funding completion failed: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Handle failed funding
     */
    public function failFunding(
        KcbBuniTransaction $transaction,
        $callbackData,
        $resultCode,
        $resultDescription
    ) {
        DB::beginTransaction();

        try {
            $requestData = json_decode($transaction->request_data, true);
            $cardId = $transaction->card_account_id;
            $card = CardAccount::find($cardId);

            // Update KCB transaction
            $transaction->update([
                'status' => 'failed',
                'result_code' => $resultCode,
                'result_description' => $resultDescription,
                'callback_data' => json_encode($callbackData),
            ]);

            // Log audit
            if ($card) {
                CardAuditLog::log(
                    $card->id,
                    'funding_failed',
                    "Card funding failed: {$resultDescription}",
                    null,
                    $card->card_number,
                    [
                        'amount' => $transaction->amount,
                        'result_code' => $resultCode,
                        'result_description' => $resultDescription,
                        'transaction_id' => $transaction->id,
                        'funding_type' => $requestData['funding_type'] ?? 'T',
                        'type_label' => $requestData['type_label'] ?? 'Funding',
                    ]
                );
            }

            DB::commit();

            Log::warning('Card funding failed', [
                'transaction_id' => $transaction->id,
                'result_code' => $resultCode,
                'result_description' => $resultDescription
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card funding failure handling failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check funding status
     */
    public function checkFundingStatus($checkoutRequestId)
    {
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->where('transaction_type', 'card_funding')
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        // If still pending, check with KCB
        if ($transaction->status === 'initiated') {
            $statusResponse = $this->kcbSalesService->checkTransactionStatus($checkoutRequestId);

            if (isset($statusResponse['response']['Body']['stkCallback'])) {
                $stkCallback = $statusResponse['response']['Body']['stkCallback'];
                $resultCode = $stkCallback['ResultCode'] ?? null;

                if ($resultCode !== null) {
                    if ($resultCode == 0) {
                        // Payment successful - process completion
                        $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
                        $metadata = [];
                        foreach ($callbackMetadata as $item) {
                            if (isset($item['Name']) && isset($item['Value'])) {
                                $metadata[$item['Name']] = $item['Value'];
                            }
                        }
                        $this->completeFunding($transaction, $statusResponse['response'], $metadata);
                    } else {
                        // Payment failed
                        $this->failFunding(
                            $transaction,
                            $statusResponse['response'],
                            $resultCode,
                            $stkCallback['ResultDesc'] ?? 'Payment failed'
                        );
                    }
                    $transaction->refresh();
                }
            }
        }

        return [
            'success' => true,
            'status' => $transaction->status,
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'amount' => $transaction->amount,
            'invoice_number' => $transaction->invoice_number,
            'funding_type' => json_decode($transaction->request_data, true)['type_label'] ?? 'Funding',
            'message' => $this->getStatusMessage($transaction->status, $transaction->result_description)
        ];
    }

    /**
     * Get funding history for a student
     */
    public function getFundingHistory(HighSchoolStudent $student, $limit = 50)
    {
        return KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get funding summary for a student
     */
    public function getFundingSummary(HighSchoolStudent $student)
    {
        $totalFunded = KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingCount = KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'initiated')
            ->count();

        $failedCount = KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'failed')
            ->count();

        $lastFunding = KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        $card = CardAccount::where('high_school_student_id', $student->id)->first();

        return [
            'total_funded' => $totalFunded,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'current_balance' => $card ? $card->balance : 0,
            'last_funding_amount' => $lastFunding ? $lastFunding->amount : 0,
            'last_funding_date' => $lastFunding ? $lastFunding->created_at : null,
            'last_funding_receipt' => $lastFunding ? $lastFunding->mpesa_receipt_number : null,
        ];
    }

    /**
     * Validate amount
     */
    private function validateAmount($amount)
    {
        if ($amount < 10) {
            return ['error' => 'Minimum funding amount is KES 10'];
        }

        if ($amount > 50000) {
            return ['error' => 'Maximum funding amount is KES 50,000'];
        }

        return true;
    }

    /**
     * Check if student has pending request
     */
    private function hasPendingRequest(HighSchoolStudent $student)
    {
        return KcbBuniTransaction::where('student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'initiated')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();
    }

    /**
     * Send funding notifications
     */
    private function sendFundingNotifications($student, $card, $amount, $receipt, $type)
    {
        try {
            // Student message
            $message = "Kenswed Card Funding\n";
            $message .= "Student: {$student->full_name}\n";
            $message .= "Admission: {$student->admission_number}\n";
            $message .= "Type: {$type}\n";
            $message .= "Amount: KES " . number_format($amount, 2) . "\n";
            $message .= "Receipt: {$receipt}\n";
            $message .= "Balance: KES " . number_format($card->balance, 2) . "\n";
            $message .= "Thank you! www.ktvtc.ac.ke";

            if ($student->phone) {
                $this->smsService->sendSingleSms($student->phone, $message);
            }

            // Guardian message
            if ($student->guardian_phone) {
                $guardianMessage = "Kenswed: Card funded for {$student->full_name}\n";
                $guardianMessage .= "Type: {$type}\n";
                $guardianMessage .= "Amount: KES " . number_format($amount, 2) . "\n";
                $guardianMessage .= "Receipt: {$receipt}\n";
                $guardianMessage .= "Balance: KES " . number_format($card->balance, 2);
                $this->smsService->sendSingleSms($student->guardian_phone, $guardianMessage);
            }

            // Admin alert for large amounts (>10,000)
            if ($amount > 10000) {
                $adminMessage = "⚠️ LARGE CARD FUNDING\n";
                $adminMessage .= "Student: {$student->full_name}\n";
                $adminMessage .= "Admission: {$student->admission_number}\n";
                $adminMessage .= "Type: {$type}\n";
                $adminMessage .= "Amount: KES " . number_format($amount, 2) . "\n";
                $adminMessage .= "Receipt: {$receipt}";

                foreach ($this->smsService->getAdminPhones() as $adminPhone) {
                    $this->smsService->sendSingleSms($adminPhone, $adminMessage);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send funding notifications: ' . $e->getMessage(), [
                'student_id' => $student->id
            ]);
        }
    }

    /**
     * Get status message
     */
    private function getStatusMessage($status, $description)
    {
        switch ($status) {
            case 'completed':
                return 'Card funded successfully!';
            case 'failed':
                return 'Funding failed: ' . ($description ?? 'Unknown error');
            case 'initiated':
                return 'Waiting for payment confirmation...';
            default:
                return 'Checking payment status...';
        }
    }

    /**
     * Get recent funding transactions (admin)
     */
    public function getRecentFunding($limit = 20)
    {
        return KcbBuniTransaction::where('transaction_type', 'card_funding')
            ->with(['student', 'cardAccount'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get funding statistics (admin)
     */
    public function getFundingStats($startDate = null, $endDate = null)
    {
        $query = KcbBuniTransaction::where('transaction_type', 'card_funding');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $completed = clone $query;
        $failed = clone $query;
        $pending = clone $query;

        return [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'completed_count' => $completed->where('status', 'completed')->count(),
            'completed_amount' => $completed->where('status', 'completed')->sum('amount'),
            'failed_count' => $failed->where('status', 'failed')->count(),
            'pending_count' => $pending->where('status', 'initiated')->count(),
            'unique_students' => $query->distinct('student_id')->count('student_id'),
        ];
    }
}
