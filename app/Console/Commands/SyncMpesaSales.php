<?php

namespace App\Console\Commands;

use App\Models\KcbBuniTransaction;
use App\Models\KcbIpn;
use App\Services\KcbSalesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings cafeteria and pre-order sales in line with their M-Pesa prompts.
 *
 * M-Pesa's replies used to look for the sale through a column that does not exist, so a
 * successful prompt left its sale "pending" (and finance reports left it out), and a
 * pre-order paid by M-Pesa never became "paid". This marks:
 *   - sales whose prompt succeeded, or whose payment KCB confirmed, as paid;
 *   - sales whose last prompt failed, as failed.
 * No SMS is sent and stock is not changed for these old sales. Safe to run more than once.
 *
 *   php artisan sales:sync-mpesa --dry-run
 *   php artisan sales:sync-mpesa
 */
class SyncMpesaSales extends Command
{
    protected $signature = 'sales:sync-mpesa {--dry-run : Show what would change without saving}';

    protected $description = 'Mark cafeteria and pre-order sales paid or failed from their M-Pesa prompt results';

    public function handle(KcbSalesService $sales): int
    {
        $dry = (bool) $this->option('dry-run');
        $paid = ['n' => 0, 'kes' => 0.0];
        $failed = ['n' => 0, 'kes' => 0.0];
        $noSale = 0;

        // Prompts KCB's bank notification confirmed (counts as paid even if M-Pesa's reply was lost)
        $bankConfirmed = KcbIpn::where('status', KcbIpn::STATUS_LINKED)->where('matched_record_type', KcbBuniTransaction::class)
            ->pluck('transaction_id', 'matched_record_id');

        KcbBuniTransaction::where('transaction_type', 'sale_payment')->orderBy('id')->chunkById(200, function ($prompts) use ($sales, $dry, $bankConfirmed, &$paid, &$failed, &$noSale) {
            foreach ($prompts as $prompt) {
                $sale = $sales->saleForPrompt($prompt);
                $receipt = $prompt->mpesa_receipt_number ?: ($bankConfirmed[$prompt->id] ?? null);
                $succeeded = $prompt->status === 'completed' || isset($bankConfirmed[$prompt->id]);

                if (!$sale) {
                    $noSale += $succeeded ? 1 : 0;
                    continue;
                }

                if ($succeeded && $sale->payment_status !== 'paid') {
                    $paid['n']++; $paid['kes'] += (float) $sale->total_amount;
                    $this->line(sprintf('  paid    sale #%d  %s  KES %s  receipt %s', $sale->id, $sale->invoice_number, number_format((float) $sale->total_amount, 2), $receipt ?? '-'));
                    if (!$dry) {
                        DB::transaction(fn () => $sales->confirmSalePaid($sale, $prompt->checkout_request_id, $receipt, $prompt->transaction_date ?? $prompt->updated_at, false));
                    }
                } elseif ($prompt->status === 'failed' && $sale->payment_status === 'pending' && $sale->checkout_request_id === $prompt->checkout_request_id) {
                    $failed['n']++; $failed['kes'] += (float) $sale->total_amount;
                    if (!$dry) {
                        DB::transaction(fn () => $sales->markSaleFailed($sale, $prompt->checkout_request_id));
                    }
                }
            }
        });

        $this->newLine();
        $verb = $dry ? 'would be' : 'were';
        $this->info(sprintf('%d sales %s marked paid (KES %s): M-Pesa took the money but the sale was left pending.', $paid['n'], $verb, number_format($paid['kes'], 2)));
        $this->info(sprintf('%d sales %s marked failed (KES %s): the customer did not complete the M-Pesa prompt.', $failed['n'], $verb, number_format($failed['kes'], 2)));
        if ($noSale) {
            $this->warn("{$noSale} successful prompts have no matching sale; they are listed in the prompts table for finance to check.");
        }

        return self::SUCCESS;
    }
}
