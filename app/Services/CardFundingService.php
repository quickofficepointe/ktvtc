<?php
// app/Services/CardFundingService.php

namespace App\Services;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Models\HighSchoolKcbTransaction;
use App\Models\WithdrawalFeeTier;
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

            // 4. Transport top-ups carry the Safaricom withdrawal charge on top, so the
            //    wallet can pay for cash fare withdrawals later (500 -> prompt 528).
            $withdrawalFee = $fundingType === 'T' ? WithdrawalFeeTier::feeFor((float) $amount) : 0.0;
            $promptAmount  = round((float) $amount + $withdrawalFee, 2);

            // 5. Generate invoice number
            $typePrefix = $fundingType === 'T' ? 'T' : 'C';
            $invoiceNumber = '8118445#' . $typePrefix . $student->admission_number;
            $typeLabel = $fundingType === 'T' ? 'Transport' : 'Canteen';
            $description = "Card Funding - {$typeLabel} - {$student->admission_number}";

            // 5. Build callback URL - ✅ FIXED: Use card funding endpoint (NOT sales)
            $callbackUrl = rtrim(config('app.url'), '/') . '/api/kcb/card/funding/callback';

            // 6. Initiate STK Push
            $stkResponse = $this->kcbSalesService->initiateStkPush(
                $formattedPhone,
                $promptAmount,
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
                'amount' => $promptAmount,
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
                    'amount' => $promptAmount,
                    'base_amount' => (float) $amount,
                    'withdrawal_fee' => $withdrawalFee,
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
                'amount' => $promptAmount,
                'base_amount' => (float) $amount,
                'withdrawal_fee' => $withdrawalFee,
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
                return 'Payment received. The card balance updates as soon as the bank confirms.';
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