<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Update stock for a product (single source of truth)
     */
   public function updateStock($productId, $shopId, $quantity, $type, $reason = null, $notes = null, $recordedBy = null)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);

            // Check if product tracks inventory
            if (!$product->track_inventory) {
                throw new \Exception("Product does not track inventory");
            }

            $oldStock = $product->current_stock;

            // Calculate new stock based on type
            switch ($type) {
                case 'adjustment_in':
                case 'transfer_in':
                case 'purchase':
                case 'return_in':
                case 'production_in':
                    $newStock = $oldStock + $quantity;
                    break;

                case 'adjustment_out':
                case 'transfer_out':
                case 'sale':
                case 'wastage':
                case 'damaged':
                case 'return_out':
                case 'production_usage':
                    $newStock = $oldStock - $quantity;
                    if ($newStock < 0) {
                        throw new \Exception("Insufficient stock. Available: {$oldStock}, Requested: {$quantity}");
                    }
                    break;

                default:
                    throw new \Exception("Invalid movement type: {$type}");
            }

            // Update the product (master stock)
            $product->current_stock = $newStock;
            $product->save();

            Log::info("Stock updated for product {$productId}: {$oldStock} -> {$newStock} ({$type})");

            // Create inventory movement
           // Create inventory movement
$movement = $this->createMovement($product, $shopId, $type, $quantity, $oldStock, $newStock, $reason, $notes, $recordedBy);

            // Sync to inventory_stocks (per-shop view) - UPDATED for generated columns
            $this->syncInventoryStock($product, $shopId, $newStock);

            DB::commit();

            return [
                'success' => true,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'movement' => $movement
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Stock update failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check stock availability for sale
     */
    public function checkStock($productId, $shopId, $quantity)
    {
        $product = Product::find($productId);

        if (!$product || !$product->track_inventory) {
            return ['available' => true, 'stock' => null];
        }

        $availableStock = $product->current_stock;

        return [
            'available' => $availableStock >= $quantity,
            'stock' => $availableStock,
            'required' => $quantity,
            'shortfall' => max(0, $quantity - $availableStock)
        ];
    }

    /**
     * Reserve stock for pending sale
     */
    public function reserveStock($productId, $shopId, $quantity, $referenceId, $referenceType = 'sale')
    {
        // Implementation for stock reservation
        $stock = InventoryStock::where('product_id', $productId)
            ->where('shop_id', $shopId)
            ->first();

        if ($stock) {
            $stock->reserved_stock += $quantity;
            // DO NOT set available_stock - it's generated from current_stock - reserved_stock
            $stock->save();

            return true;
        }

        return false;
    }

    /**
     * Release reserved stock
     */
   /**
 * Release reserved stock
 */
public function releaseStock($productId, $shopId, $quantity)
{
     $stock = InventoryStock::where('product_id', $productId)  // Remove the \App\Models\ prefix
        ->where('shop_id', $shopId)
        ->first();

    if ($stock) {
        $stock->reserved_stock = max(0, $stock->reserved_stock - $quantity);
        // DO NOT set available_stock - it's generated
        $stock->save();

        return true;
    }

    return false;
}

    /**
     * Create inventory movement record
     */
   private function createMovement($product, $shopId, $type, $quantity, $oldStock, $newStock, $reason, $notes, $recordedBy = null)
    {
        $movementNumber = 'MOV-' . date('Ymd') . '-' . strtoupper(uniqid());

        return InventoryMovement::create([
            'movement_number' => $movementNumber,
            'product_id' => $product->id,
            'shop_id' => $shopId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'unit' => $product->unit,
            'unit_cost' => $product->cost_price,
            'total_cost' => $quantity * $product->cost_price,
            'previous_stock' => $oldStock,
            'new_stock' => $newStock,
            'reference_type' => 'stock_adjustment',
            'reason' => $reason,
            'notes' => $notes,
    'recorded_by' => $recordedBy ?? auth()->id() ?? 1,
            'movement_date' => now(),
        ]);
    }

    /**
     * Sync product stock to inventory_stocks table - FIXED for generated columns
     * available_stock = current_stock - reserved_stock (GENERATED)
     * stock_value = current_stock * average_unit_cost (GENERATED)
     */
    private function syncInventoryStock($product, $shopId, $currentStock)
    {
        try {
            // Check if record exists
            $inventoryStock = InventoryStock::where('product_id', $product->id)
                ->where('shop_id', $shopId)
                ->first();

            if ($inventoryStock) {
                // Update existing record
                // ONLY update non-generated columns
                $inventoryStock->update([
                    'current_stock' => $currentStock,
                    'last_movement_at' => now(),
                    'last_sold_date' => now(),
                ]);
            } else {
                // Create new record - Use DB facade to avoid Eloquent issues with generated columns
                DB::table('inventory_stocks')->insert([
                    'product_id' => $product->id,
                    'shop_id' => $shopId,
                    'current_stock' => $currentStock,
                    'reserved_stock' => 0,
                    'average_unit_cost' => $product->cost_price,
                    'last_movement_at' => now(),
                    'last_sold_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    // DO NOT include available_stock or stock_value - they are generated
                ]);

                // Retrieve the created record
                $inventoryStock = InventoryStock::where('product_id', $product->id)
                    ->where('shop_id', $shopId)
                    ->first();
            }

            return $inventoryStock;

        } catch (\Exception $e) {
            Log::warning("Inventory stock sync warning: " . $e->getMessage());

            // Fallback: Raw SQL update
            DB::table('inventory_stocks')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'shop_id' => $shopId,
                ],
                [
                    'current_stock' => $currentStock,
                    'last_movement_at' => now(),
                    'last_sold_date' => now(),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );

            return InventoryStock::where('product_id', $product->id)
                ->where('shop_id', $shopId)
                ->first();
        }
    }

    /**
     * Alternative: Raw SQL method for generated columns
     */
    private function syncInventoryStockRaw($product, $shopId, $currentStock)
    {
        // Use raw SQL that handles generated columns properly
        $sql = "INSERT INTO inventory_stocks
                (product_id, shop_id, current_stock, reserved_stock, average_unit_cost,
                 last_movement_at, last_sold_date, created_at, updated_at)
                VALUES (?, ?, ?, 0, ?, NOW(), NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                current_stock = VALUES(current_stock),
                last_movement_at = VALUES(last_movement_at),
                last_sold_date = VALUES(last_sold_date),
                updated_at = VALUES(updated_at)";

        DB::statement($sql, [
            $product->id,
            $shopId,
            $currentStock,
            $product->cost_price
        ]);

        return InventoryStock::where('product_id', $product->id)
            ->where('shop_id', $shopId)
            ->first();
    }
}
