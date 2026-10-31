<?php

namespace App\Console\Commands;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Services\Cards\CardWallet;
use App\Services\Cards\WalletSplitter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off after deploying wallets: splits every card's old combined balance
 * into transport and canteen (see WalletSplitter for the rule). Safe to re-run;
 * cards already split are skipped.
 */
class SplitCardWallets extends Command
{
    protected $signature = 'cards:split-wallets {--dry-run : Show the result without saving}';

    protected $description = 'Split old combined card balances into transport and canteen wallets';

    public function handle(CardWallet $wallet): int
    {
        $this->labelOldTransactions();

        $cards = CardAccount::whereNull('wallets_split_at')->get();
        $totals = ['transport' => 0.0, 'canteen' => 0.0, 'cards' => 0, 'ate_transport' => 0];

        foreach ($cards as $card) {
            [$transport, $canteen] = WalletSplitter::splitFor($card);
            $totals['transport'] += $transport;
            $totals['canteen'] += $canteen;
            $totals['cards']++;

            if (!$this->option('dry-run')) {
                DB::transaction(function () use ($card, $wallet) {
                    $locked = CardAccount::whereKey($card->id)->lockForUpdate()->first();
                    $wallet->ensureSplit($locked);
                });
            }
        }

        $this->table(['Cards', 'Transport total', 'Canteen total', 'Combined'], [[
            $totals['cards'],
            number_format($totals['transport'], 2),
            number_format($totals['canteen'], 2),
            number_format($totals['transport'] + $totals['canteen'], 2),
        ]]);

        $this->info($this->option('dry-run') ? 'Dry run: nothing saved.' : 'Wallets split.');
        $this->line('Next: record past cash fare in Finance > Transport Fare (use past dates) so transport balances show what is really left.');

        return self::SUCCESS;
    }

    /**
     * Transactions from before the split have no wallet, so screens that split money by
     * wallet showed old transport top-ups as zero. Top-ups say which wallet in their
     * metadata (funding_type T = transport, C = canteen, from the paybill account
     * 8118445#T... or #C...); purchases were always canteen. Only empty rows are touched.
     */
    private function labelOldTransactions(): void
    {
        $counts = ['transport' => 0, 'canteen' => 0];

        CardTransaction::whereNull('wallet')->where('transaction_type', 'funding')
            ->select(['id', 'metadata'])->chunkById(500, function ($rows) use (&$counts) {
                foreach ($rows as $row) {
                    $meta = $row->metadata;
                    for ($i = 0; $i < 2 && is_string($meta); $i++) {
                        $meta = json_decode($meta, true);
                    }
                    $type = is_array($meta) ? strtoupper((string) ($meta['funding_type'] ?? '')) : '';
                    $wallet = CardWallet::walletFor($type === 'T' ? 'T' : 'C');
                    $counts[$wallet]++;
                    if (!$this->option('dry-run')) {
                        CardTransaction::whereKey($row->id)->update(['wallet' => $wallet]);
                    }
                }
            });

        $purchases = CardTransaction::whereNull('wallet')->whereIn('transaction_type', ['purchase', 'refund', 'reversal']);
        $counts['canteen'] += $n = (clone $purchases)->count();
        if (!$this->option('dry-run') && $n) {
            $purchases->update(['wallet' => CardWallet::CANTEEN]);
        }

        $this->info(($this->option('dry-run') ? 'Would label' : 'Labelled') . " old transactions: {$counts['transport']} transport top-ups, {$counts['canteen']} canteen top-ups and purchases.");
    }
}
