<?php
// app/Services/CardFundingService.php

namespace App\Services;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Models\HighSchoolKcbTransaction;
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
            $pendingRequest = $this->hasPendingRequest($card);
            if ($pendingRequest) {
                return [
                    'error' => 'You have a pending funding request. Please wait or check status.',
                    'pending_checkout_id' => $pendingRequest->checkout_request_id
                ];
            }

            // 4. Generate invoice number
            $typePrefix = $fundingType === 'T' ? 'T' : 'C';
            $invoiceNumber = '8118445#' . $typePrefix . $student->admission_number;
            $typeLabel = $fundingType === 'T' ? 'Transport' : 'Canteen';
            $description = "Card Funding - {$typeLabel} - {$student->admission_number}";

            // 5. Build callback URL - ✅ FIXED: Use card funding endpoint (NOT sales)
            $callbackUrl = rtrim(config('app.url'), '/') . '/api/kcb/card/funding/callback';

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

            $transaction = HighSchoolKcbTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                'checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
                'phone_number' => $formattedPhone,
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'transaction_type' => 'card_funding',
                'status' => 'initiated',
                'customer_reference' => $student->admission_number,
                'customer_name' => $student->full_name,
                'customer_mobile_number' => $formattedPhone,
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
     * Check if card has pending request
     */
    private function hasPendingRequest(CardAccount $card)
    {
        return HighSchoolKcbTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'card_funding')
            ->whereIn('status', ['initiated', 'processing'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();
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
     * Complete card funding after successful M-Pesa payment
     */
    public function completeFunding(
        $checkoutRequestId,
        $paymentData,
        $additionalData = []
    ) {
        try {
            DB::beginTransaction();

            // Find the transaction
            $transaction = HighSchoolKcbTransaction::where('checkout_request_id', $checkoutRequestId)
                ->where('transaction_type', 'card_funding')
                ->first();

            if (!$transaction) {
                Log::error('Transaction not found for completion', [
                    'checkout_request_id' => $checkoutRequestId
                ]);
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            if ($transaction->status === 'completed') {
                return ['success' => true, 'message' => 'Transaction already completed'];
            }

            // Get the card
            $card = CardAccount::find($transaction->card_account_id);
            if (!$card) {
                Log::error('Card not found for funding completion', [
                    'card_id' => $transaction->card_account_id
                ]);
                DB::rollBack();
                return ['success' => false, 'message' => 'Card not found'];
            }

            // Get the student
            $student = HighSchoolStudent::find($transaction->high_school_student_id);
            if (!$student) {
                Log::error('Student not found for funding completion', [
                    'student_id' => $transaction->high_school_student_id
                ]);
                DB::rollBack();
                return ['success' => false, 'message' => 'Student not found'];
            }

            $amount = $transaction->amount;
            $mpesaReceipt = $paymentData['mpesa_receipt'] ?? $additionalData['mpesa_receipt'] ?? 'MANUAL-' . time();

            // Check for duplicate transaction
            $existingTransaction = CardTransaction::where('mpesa_receipt', $mpesaReceipt)
                ->orWhere('reference', $transaction->invoice_number)
                ->first();

            if ($existingTransaction) {
                Log::warning('Duplicate card funding detected', [
                    'mpesa_receipt' => $mpesaReceipt,
                    'existing_transaction_id' => $existingTransaction->id
                ]);

                $transaction->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReceipt,
                    'result_code' => 0,
                    'result_description' => 'Duplicate transaction - already processed',
                    'completed_at' => now(),
                ]);

                DB::commit();
                return ['success' => true, 'message' => 'Duplicate transaction already processed'];
            }

            // Fund the card
            $card->balance += $amount;
            $card->total_funded += $amount;
            $card->last_funded_at = now();
            $card->last_funding_amount = $amount;
            $card->last_funding_source = $transaction->request_data['type_label'] ?? 'Funding';
            $card->save();

            // Create card transaction
            $cardTransaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'balance_before' => $card->balance - $amount,
                'balance_after' => $card->balance,
                'description' => "Card funding via M-Pesa - Receipt: {$mpesaReceipt}",
                'mpesa_receipt' => $mpesaReceipt,
                'reference' => $transaction->invoice_number,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $transaction->checkout_request_id,
                    'funding_type' => $transaction->request_data['funding_type'] ?? 'T',
                    'type_label' => $transaction->request_data['type_label'] ?? 'Funding',
                    'phone_number' => $transaction->phone_number,
                ])
            ]);

            // Update transaction
            $transaction->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $mpesaReceipt,
                'result_code' => 0,
                'result_description' => 'Payment successful',
                'completed_at' => now(),
                'callback_data' => json_encode($paymentData),
            ]);

            // Log audit
            CardAuditLog::log(
                $card->id,
                'funding_completed',
                "Card funded successfully: KES {$amount} via M-Pesa. Receipt: {$mpesaReceipt}",
                null,
                $card->card_number,
                [
                    'amount' => $amount,
                    'mpesa_receipt' => $mpesaReceipt,
                    'transaction_id' => $transaction->id,
                    'card_transaction_id' => $cardTransaction->id,
                    'new_balance' => $card->balance,
                ]
            );

            DB::commit();

            // Send notifications
            $this->sendFundingNotifications($student, $card, $amount, $mpesaReceipt, $transaction->request_data['type_label'] ?? 'Funding');

            Log::info('Card funding completed successfully', [
                'student_id' => $student->id,
                'card_id' => $card->id,
                'amount' => $amount,
                'receipt' => $mpesaReceipt,
                'new_balance' => $card->balance,
            ]);

            return ['success' => true, 'message' => 'Funding completed successfully'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card funding completion failed: ' . $e->getMessage(), [
                'checkout_request_id' => $checkoutRequestId,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Failed to complete funding: ' . $e->getMessage()];
        }
    }

    /**
     * Check funding status
     */
    public function checkFundingStatus($checkoutRequestId)
    {
        $transaction = HighSchoolKcbTransaction::where('checkout_request_id', $checkoutRequestId)
            ->where('transaction_type', 'card_funding')
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        return [
            'success' => true,
            'status' => $transaction->status,
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'amount' => $transaction->amount,
            'invoice_number' => $transaction->invoice_number,
            'message' => $this->getStatusMessage($transaction->status, $transaction->result_description)
        ];
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
            case 'processing':
                return 'Waiting for payment confirmation...';
            default:
                return 'Checking payment status...';
        }
    }

    /**
     * Send funding notifications
     */
    private function sendFundingNotifications($student, $card, $amount, $receipt, $type)
    {
        try {
            // Send confirmation to parent
            if ($student->parent_phone) {
                $this->smsService->sendFundingConfirmation($student, $amount, $type, $receipt, $card->balance);
            }

            // Check low balance alert
            if ($card->balance > 0 && $card->balance < $card->low_balance_threshold) {
                $this->cardService->triggerLowBalanceAlert($card);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send funding notifications: ' . $e->getMessage(), [
                'student_id' => $student->id
            ]);
        }
    }

    /**
     * Get funding summary for a student
     */
    public function getFundingSummary(HighSchoolStudent $student)
    {
        $card = $student->cardAccount;

        $totalFunded = HighSchoolKcbTransaction::where('high_school_student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingCount = HighSchoolKcbTransaction::where('high_school_student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->whereIn('status', ['initiated', 'processing'])
            ->count();

        $failedCount = HighSchoolKcbTransaction::where('high_school_student_id', $student->id)
            ->where('transaction_type', 'card_funding')
            ->where('status', 'failed')
            ->count();

        return [
            'total_funded' => $totalFunded,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'current_balance' => $card ? $card->balance : 0,
        ];
    }
}