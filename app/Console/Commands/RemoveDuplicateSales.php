<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use App\Models\PaymentTransaction;
use App\Models\Sale;
use App\Services\KcbSalesService;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The cafeteria till used to record every M-Pesa prompt payment twice: the prompt's own sale,
 * and a second sale the till created after the payment went through (often with the wrong items).
 * This keeps the prompt's sale (right items, amount M-Pesa charged) and removes the second one:
 * its stock is given back, its payment record marked reversed, and the sale soft-deleted.
 */
class RemoveDuplicateSales extends Command
{
    protected $signature = 'sales:remove-duplicates {--dry-run : Only list what would change}';

    protected $description = 'Remove second sales recorded for an M-Pesa payment that already has its own prompt sale';

    public function handle(KcbSalesService $sales, StockService $stock): int
    {
        $dry = (bool) $this->option('dry-run');

        $pairs = DB::table('sales as p')
            ->join('sales as d', function ($j) {
                $j->on('d.mpesa_receipt', '=', 'p.mpesa_receipt')->whereColumn('d.id', '<>', 'p.id')->whereNull('d.checkout_request_id');
            })
            ->whereNotNull('p.checkout_request_id')->where('p.payment_status', 'paid')
            ->whereNotNull('p.mpesa_receipt')->where('p.mpesa_receipt', '!=', '')
            ->whereNull('p.deleted_at')->whereNull('d.deleted_at')
            ->orderBy('d.id')
            ->get(['p.id as keep_id', 'p.invoice_number as keep_inv', 'p.total_amount as keep_amt', 'd.id as dup_id', 'd.invoice_number as dup_inv', 'd.total_amount as dup_amt', 'd.mpesa_receipt as receipt']);

        $amount = 0;
        foreach ($pairs as $r) {
            $this->line(sprintf('%s  keep %s (KES %s)  remove %s (KES %s)', $r->receipt, $r->keep_inv, $r->keep_amt, $r->dup_inv, $r->dup_amt));
            $amount += $r->dup_amt;
            if ($dry) {
                continue;
            }

            DB::transaction(function () use ($r, $sales, $stock) {
                $dup = Sale::find($r->dup_id);
                // Give back what the second sale took off stock
                $taken = InventoryMovement::where('movement_type', 'sale')->where('reason', 'Sale: ' . $dup->invoice_number)
                    ->selectRaw('product_id, SUM(quantity) qty')->groupBy('product_id')->pluck('qty', 'product_id');
                foreach ($taken as $productId => $qty) {
                    $stock->updateStock($productId, $dup->shop_id, (float) $qty, 'return_in',
                        'Duplicate sale: ' . $dup->invoice_number, 'Same M-Pesa payment as sale ' . $r->keep_inv, null);
                }
                PaymentTransaction::where('sale_id', $dup->id)->update(['status' => 'reversed']);
                $dup->update(['internal_notes' => trim(($dup->internal_notes ?? '') . "\nDuplicate of {$r->keep_inv} (same M-Pesa receipt {$r->receipt}); removed.")]);
                $dup->delete();

                // The kept sale must have its items off stock exactly once
                $sales->takeStockForSale(Sale::find($r->keep_id));
            });
        }

        $this->info(($dry ? 'Would remove' : 'Removed') . ' ' . count($pairs) . ' duplicate sales, KES ' . number_format($amount, 2) . '.');
        return self::SUCCESS;
    }
}
