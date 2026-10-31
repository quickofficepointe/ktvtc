<?php

namespace App\Services\Payments;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\KcbIpn;
use App\Services\Cards\CardWallet;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Credits a high-school card from a received KCB payment.
 * T (transport) goes to the transport wallet; C or no letter goes to canteen.
 * The full amount paid is credited, including any withdrawal charge added to
 * the STK prompt: that charge is taken out later, each time fare is given.
 */
class CardFundingCreditor
{
    public function __construct(private CardWallet $wallet) {}

    /**
     * @param string|null $fundingType 'T' (transport), 'C' (canteen) or null
     */
    public function credit(CardAccount $card, KcbIpn $ipn, ?string $fundingType = null, ?int $userId = null): CardTransaction
    {
        $wallet    = CardWallet::walletFor($fundingType);
        $amount    = round((float) $ipn->transaction_amount, 2);
        $typeLabel = $wallet === CardWallet::TRANSPORT ? 'Transport' : 'Canteen';

        $transaction = $this->wallet->credit($card, $wallet, $amount, 'funding', [
            'reference'          => CardTransaction::generateReference('IPN'),
            'description'        => "M-Pesa till {$ipn->till_number} - {$typeLabel} top-up - Ref: {$ipn->transaction_id}",
            'mpesa_receipt'      => $ipn->transaction_id,
            'ipn_transaction_id' => $ipn->transaction_id,
            'metadata'           => [
                'kcb_ipn_id'   => $ipn->id,
                'funding_type' => $wallet === CardWallet::TRANSPORT ? 'T' : 'C',
                'type_label'   => $typeLabel,
                'account'      => $ipn->business_key,
                'payer_phone'  => $ipn->debit_msisdn,
                'assigned_by'  => $userId,
            ],
        ], $userId);

        DB::afterCommit(function () use ($card, $amount, $wallet, $ipn) {
            $student = $card->student;
            if (!$student || !$student->parent_phone) {
                return;
            }
            try {
                app(SmsService::class)->sendFundingConfirmation(
                    $student, $amount, $wallet === CardWallet::TRANSPORT ? 'T' : 'C', $ipn->transaction_id,
                    $card->fresh()->{$wallet . '_balance'}
                );
            } catch (\Throwable $e) {
                Log::warning('Card funding SMS failed', ['card_id' => $card->id, 'error' => $e->getMessage()]);
            }
        });

        return $transaction;
    }
}
