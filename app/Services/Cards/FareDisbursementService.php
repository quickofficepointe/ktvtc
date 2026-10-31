<?php

namespace App\Services\Cards;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\FareDisbursement;
use App\Models\WithdrawalFeeTier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Transport fare handed to a student in cash.
 *
 * The transport wallet is charged amount + the Safaricom withdrawal fee for
 * that amount, every time. A student who takes fare in bits (5 x 100) pays
 * five small fees, which is what it costs the school to withdraw it that way.
 */
class FareDisbursementService
{
    public function __construct(private CardWallet $wallet) {}

    public function quote(float $amount, bool $chargeFee = true): array
    {
        $fee = $chargeFee ? WithdrawalFeeTier::feeFor($amount) : 0.0;
        return ['amount' => round($amount, 2), 'fee' => $fee, 'total' => round($amount + $fee, 2)];
    }

    /**
     * @param bool $allowNegative only for recording fare already given in the past (reconciliation)
     * @throws InsufficientWalletBalance
     */
    public function give(
        CardAccount $card,
        float $amount,
        Carbon $givenOn,
        int $userId,
        ?string $notes = null,
        bool $chargeFee = true,
        bool $allowNegative = false,
        ?string $batchReference = null,
    ): FareDisbursement {
        $quote = $this->quote($amount, $chargeFee);
        $backdated = $givenOn->lt(today());

        return DB::transaction(function () use ($card, $quote, $givenOn, $userId, $notes, $allowNegative, $batchReference, $backdated) {
            $tx = $this->wallet->debit($card, CardWallet::TRANSPORT, $quote['total'], 'disbursement', [
                'reference'   => CardTransaction::generateReference('FARE'),
                'fee_amount'  => $quote['fee'],
                'description' => 'Transport fare (cash) KES ' . number_format($quote['amount'], 2)
                    . ($quote['fee'] > 0 ? ' + withdrawal fee KES ' . number_format($quote['fee'], 2) : '')
                    . ' given ' . $givenOn->format('d M Y'),
                'metadata'    => ['given_on' => $givenOn->toDateString(), 'batch' => $batchReference, 'notes' => $notes],
            ], $userId, $allowNegative);

            return FareDisbursement::create([
                'card_account_id'        => $card->id,
                'high_school_student_id' => $card->high_school_student_id,
                'card_transaction_id'    => $tx->id,
                'amount'                 => $quote['amount'],
                'withdrawal_fee'         => $quote['fee'],
                'total_deducted'         => $quote['total'],
                'given_on'               => $givenOn->toDateString(),
                'method'                 => 'cash',
                'batch_reference'        => $batchReference,
                'notes'                  => $notes,
                'is_backdated'           => $backdated,
                'status'                 => 'given',
                'recorded_by'            => $userId,
            ]);
        });
    }

    /**
     * Record fare for many students at once (e.g. a whole class on closing day).
     * All or nothing: if any student lacks the balance, nothing is saved.
     *
     * @param array<int, float> $amountsByCardId
     */
    public function giveBatch(array $amountsByCardId, Carbon $givenOn, int $userId, ?string $notes, bool $chargeFee, bool $allowNegative): array
    {
        $batch = 'FARE-' . $givenOn->format('Ymd') . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use ($amountsByCardId, $givenOn, $userId, $notes, $chargeFee, $allowNegative, $batch) {
            $done = [];
            foreach ($amountsByCardId as $cardId => $amount) {
                $done[] = $this->give(CardAccount::findOrFail($cardId), (float) $amount, $givenOn, $userId, $notes, $chargeFee, $allowNegative, $batch);
            }
            return $done;
        });
    }

    /**
     * Undo a disbursement recorded by mistake: the full amount (incl. fee) goes back to transport.
     */
    public function reverse(FareDisbursement $disbursement, string $reason, int $userId): FareDisbursement
    {
        return DB::transaction(function () use ($disbursement, $reason, $userId) {
            $d = FareDisbursement::whereKey($disbursement->id)->lockForUpdate()->firstOrFail();
            if ($d->status !== 'given') {
                throw new \DomainException('This fare record has already been reversed.');
            }

            $this->wallet->credit($d->cardAccount, CardWallet::TRANSPORT, (float) $d->total_deducted, 'reversal', [
                'reference'   => CardTransaction::generateReference('FREV'),
                'description' => "Fare record reversed: {$reason}",
                'metadata'    => ['fare_disbursement_id' => $d->id],
            ], $userId);

            $d->update([
                'status'          => 'reversed',
                'reversal_reason' => $reason,
                'reversed_by'     => $userId,
                'reversed_at'     => now(),
            ]);

            return $d;
        });
    }
}
