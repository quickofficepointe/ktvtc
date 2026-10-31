<?php

namespace App\Services\Cards;

use App\Models\CardAccount;
use App\Models\CardAuditLog;
use App\Models\CardTransaction;
use Illuminate\Support\Facades\DB;

/**
 * The only place a high-school card's money moves.
 *
 * Each card has two wallets:
 *   transport: topped up by parents (T), paid out as cash fare by the accountant
 *   canteen:   topped up by parents (C), spent at the cafeteria / pre-orders
 * card_accounts.balance is always transport + canteen.
 *
 * Every call locks the card row, so two simultaneous scans can't both spend
 * the same shillings.
 */
class CardWallet
{
    public const TRANSPORT = 'transport';
    public const CANTEEN   = 'canteen';

    public static function walletFor(?string $fundingType): string
    {
        return strtoupper((string) $fundingType) === 'T' ? self::TRANSPORT : self::CANTEEN;
    }

    /**
     * Add money to one wallet.
     *
     * @param array $tx extra CardTransaction fields (description, reference, mpesa_receipt, metadata...)
     */
    public function credit(CardAccount $card, string $wallet, float $amount, string $type, array $tx = [], ?int $userId = null): CardTransaction
    {
        return $this->move($card, $wallet, round($amount, 2), $type, $tx, $userId, allowNegative: true);
    }

    /**
     * Take money from one wallet.
     *
     * @throws InsufficientWalletBalance unless $allowNegative
     */
    public function debit(CardAccount $card, string $wallet, float $amount, string $type, array $tx = [], ?int $userId = null, bool $allowNegative = false): CardTransaction
    {
        return $this->move($card, $wallet, -round($amount, 2), $type, $tx, $userId, $allowNegative);
    }

    /**
     * Move money between a card's own wallets (e.g. a parent paid canteen money as transport).
     */
    public function transfer(CardAccount $card, string $from, string $to, float $amount, string $reason, int $userId): array
    {
        return DB::transaction(function () use ($card, $from, $to, $amount, $reason, $userId) {
            $ref = CardTransaction::generateReference('TRF');
            $out = $this->debit($card, $from, $amount, 'transfer', [
                'reference'   => $ref . '-OUT',
                'description' => "Moved to {$to}: {$reason}",
            ], $userId);
            $in = $this->credit($card, $to, $amount, 'transfer', [
                'reference'   => $ref . '-IN',
                'description' => "Moved from {$from}: {$reason}",
            ], $userId);
            return [$out, $in];
        });
    }

    public function balanceOf(CardAccount $card, string $wallet): float
    {
        $this->ensureSplit($card);
        return (float) $card->{$wallet . '_balance'};
    }

    private function move(CardAccount $original, string $wallet, float $delta, string $type, array $tx, ?int $userId, bool $allowNegative): CardTransaction
    {
        if (!in_array($wallet, [self::TRANSPORT, self::CANTEEN], true)) {
            throw new \InvalidArgumentException("Unknown wallet: {$wallet}");
        }

        return DB::transaction(function () use ($original, $wallet, $delta, $type, $tx, $userId, $allowNegative) {
            $card = CardAccount::whereKey($original->id)->lockForUpdate()->firstOrFail();
            $this->ensureSplit($card);

            $column = $wallet . '_balance';
            $before = (float) $card->{$column};
            $after  = round($before + $delta, 2);

            if ($delta < 0 && $after < 0 && !$allowNegative) {
                throw new InsufficientWalletBalance($wallet, $before, abs($delta));
            }

            $card->{$column} = $after;
            $card->balance   = round((float) $card->transport_balance + (float) $card->canteen_balance, 2);

            if ($type === 'funding') {
                $card->total_funded   = (float) $card->total_funded + $delta;
                $card->last_funded_at = now();
            } elseif ($type === 'purchase') {
                $card->total_spent         = (float) $card->total_spent - $delta;
                $card->today_spent         = (float) $card->today_spent - $delta;
                $card->today_transactions  = (int) $card->today_transactions + 1;
                $card->today_first_used_at ??= now();
                $card->last_used_at        = now();
            } elseif ($type === 'refund') {
                $card->total_spent = max(0, (float) $card->total_spent - $delta);
            }
            $card->save();

            $transaction = CardTransaction::create(array_merge([
                'reference'    => CardTransaction::generateReference(strtoupper(substr($type, 0, 3))),
                'status'       => 'completed',
                'processed_at' => now(),
                'processed_by' => $userId,
            ], $tx, [
                'card_account_id'  => $card->id,
                'transaction_type' => $type,
                'wallet'           => $wallet,
                'amount'           => abs($delta),
                'balance_before'   => $before,
                'balance_after'    => $after,
            ]));

            CardAuditLog::log(
                $card->id,
                $type . '_' . $wallet,
                ($tx['description'] ?? ucfirst($type)) . ' (' . ($delta >= 0 ? '+' : '-') . 'KES ' . number_format(abs($delta), 2) . ")",
                $before,
                $after,
                ['wallet' => $wallet, 'card_transaction_id' => $transaction->id, 'by' => $userId]
            );

            // Hand the fresh numbers back to the caller's model
            $this->copyBalances($card, $original);

            return $transaction;
        });
    }

    /**
     * Cards from before wallets existed hold one combined balance. Split it
     * from history the first time the card is touched (also done in bulk by
     * `php artisan cards:split-wallets`). Caller must hold the row lock.
     */
    public function ensureSplit(CardAccount $card): void
    {
        if ($card->wallets_split_at) {
            return;
        }

        [$transport, $canteen] = WalletSplitter::splitFor($card);

        $card->transport_balance = $transport;
        $card->canteen_balance   = $canteen;
        $card->wallets_split_at  = now();
        $card->save();
    }

    private function copyBalances(CardAccount $fresh, CardAccount $original): void
    {
        foreach (['balance', 'transport_balance', 'canteen_balance', 'wallets_split_at', 'total_funded', 'total_spent'] as $field) {
            $original->{$field} = $fresh->{$field};
        }
        $original->syncOriginal();
    }
}
