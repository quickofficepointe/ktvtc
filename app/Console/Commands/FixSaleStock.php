<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Services\KcbSalesService;
use App\Services\StockService;
use Illuminate\Console\Command;

/**
 * Till M-Pesa sales used to take stock when the prompt was sent. This gives back:
 *  - stock taken by prompts that were never paid (failed, cancelled, or pending for over an hour);
 *  - stock taken twice for paid sales (once at the prompt, again when the payment was confirmed).
 */
class FixSaleStock extends Command
{
    protected $signature = 'sales:fix-stock {--dry-run : Only list what would change}';

    protected $description = 'Give back stock taken by unpaid M-Pesa prompts, and stock taken twice for paid sales';

    public function handle(KcbSalesService $sales, StockService $stock): int
    {
        $dry = (bool) $this->option('dry-run');
        $unpaid = $double = 0;

        // Sales that took stock at the prompt ("Sale: <invoice>")
        $invoices = InventoryMovement::where('movement_type', 'sale')->where('reason', 'like', 'Sale: %')
            ->pluck('reason')->map(fn ($r) => substr($r, 6))->unique();

        Sale::whereIn('invoice_number', $invoices)->where('payment_method', '!=', 'cash')->orderBy('id')
            ->chunk(200, function ($chunk) use ($sales, $stock, $dry, &$unpaid, &$double) {
                foreach ($chunk as $sale) {
                    $unpaidSale = in_array($sale->payment_status, ['failed', 'cancelled'], true)
                        || ($sale->payment_status === 'pending' && $sale->created_at->lt(now()->subHour()));

                    if ($unpaidSale && $sales->stockTakenFor($sale)) {
                        $this->line(sprintf('%s  %-8s KES %8s  give back unpaid stock', $sale->invoice_number, $sale->payment_status, number_format($sale->total_amount, 2)));
                        $unpaid++;
                        if (!$dry) {
                            $sales->restoreStockForUnpaidSale($sale, 'never paid (cleanup)');
                        }
                        continue;
                    }

                    if ($sale->payment_status !== 'paid') {
                        continue;
                    }
                    // Paid: the prompt took stock and the confirmation took it again
                    $again = InventoryMovement::where('movement_type', 'sale')->where('reference_type', 'sale')->where('reference_id', $sale->id)
                        ->selectRaw('product_id, SUM(quantity) qty')->groupBy('product_id')->pluck('qty', 'product_id');
                    $fixed = InventoryMovement::where('movement_type', 'return_in')->where('reason', 'Taken twice: ' . $sale->invoice_number)->exists();
                    if ($again->isEmpty() || $fixed) {
                        continue;
                    }
                    $this->line(sprintf('%s  paid     KES %8s  give back stock taken twice', $sale->invoice_number, number_format($sale->total_amount, 2)));
                    $double++;
                    if (!$dry) {
                        foreach ($again as $productId => $qty) {
                            $stock->updateStock($productId, $sale->shop_id, (float) $qty, 'return_in',
                                'Taken twice: ' . $sale->invoice_number, 'Stock was taken at the prompt and again on payment', null);
                        }
                    }
                }
            });

        $this->info(($dry ? 'Would fix' : 'Fixed') . ": {$unpaid} unpaid sales, {$double} paid sales taken twice.");
        return self::SUCCESS;
    }
}
