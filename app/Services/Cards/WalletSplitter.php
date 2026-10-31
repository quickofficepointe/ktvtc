<?php

namespace App\Services\Cards;

use App\Models\CardAccount;
use App\Models\CardTransaction;

/**
 * Splits an old combined card balance into transport and canteen, from history:
 *
 *   transport = completed transport (T) top-ups
 *   canteen   = whatever else is on the card
 *   if canteen spending ate into transport money, transport is reduced by that much.
 *
 * Equivalent to: transport = min(T top-ups, current balance); canteen = balance - transport.
 * The two always add up to the current balance. Past cash fare is then recorded by
 * the accountant (backdated) to bring transport down to what is really left.
 */
class WalletSplitter
{
    /** @return array{0: float, 1: float} [transport, canteen] */
    public static function splitFor(CardAccount $card): array
    {
        $balance = round((float) $card->balance, 2);

        $transportFunded = 0.0;
        CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'funding')
            ->where('status', 'completed')
            ->select(['id', 'amount', 'metadata'])
            ->chunkById(500, function ($rows) use (&$transportFunded) {
                foreach ($rows as $row) {
                    if (self::fundingType($row) === 'T') {
                        $transportFunded += (float) $row->amount;
                    }
                }
            });

        $transport = round(max(0, min($transportFunded, $balance)), 2);
        $canteen   = round($balance - $transport, 2);

        return [$transport, $canteen];
    }

    /**
     * Older rows stored metadata already json_encoded before the array cast,
     * so it can come back as a JSON string.
     */
    private static function fundingType(CardTransaction $row): ?string
    {
        $meta = $row->metadata;
        for ($i = 0; $i < 2 && is_string($meta); $i++) {
            $meta = json_decode($meta, true);
        }
        return is_array($meta) ? strtoupper((string) ($meta['funding_type'] ?? '')) ?: null : null;
    }
}
