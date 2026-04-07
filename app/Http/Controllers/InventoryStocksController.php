<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\StockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryStocksController extends Controller
{
    /**
     * Main inventory dashboard
     */
    public function index(Request $request)
    {
        // Get stocks for display
        $stockQuery = InventoryStock::with(['product' => function($q) {
            $q->withTrashed(); // Include soft-deleted products
        }, 'shop'])
            ->when($request->shop_id, function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($request->product_id, function ($q, $productId) {
                $q->where('product_id', $productId);
            })
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('product', function ($q) use ($search) {
                        $q->withTrashed()
                          ->where('product_name', 'like', "%{$search}%")
                          ->orWhere('product_code', 'like', "%{$search}%");
                    });
                });
            });

        $stocks = $stockQuery->orderBy('current_stock', 'asc')->paginate(25);

        // Get recent movements
        $movementQuery = InventoryMovement::with(['product' => function($q) {
            $q->withTrashed(); // Include soft-deleted products
        }, 'shop', 'recorder'])
            ->when($request->movement_shop_id, function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($request->movement_type, function ($q, $type) {
                $q->where('movement_type', $type);
            })
            ->when($request->movement_start_date, function ($q, $startDate) {
                $q->whereDate('movement_date', '>=', $startDate);
            })
            ->when($request->movement_end_date, function ($q, $endDate) {
                $q->whereDate('movement_date', '<=', $endDate);
            });

        $movements = $movementQuery->latest('movement_date')->paginate(25);

        // Get data for dropdowns
        $shops = Shop::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();

        $movementTypes = [
            'purchase' => 'Purchase',
            'sale' => 'Sale',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'adjustment_in' => 'Adjustment In',
            'adjustment_out' => 'Adjustment Out',
            'production_in' => 'Production In',
            'production_usage' => 'Production Usage',
            'wastage' => 'Wastage',
            'damaged' => 'Damaged',
            'return_in' => 'Return In',
            'return_out' => 'Return Out',
        ];

        $adjustmentCategories = [
            'stock_take' => 'Stock Take',
            'damage' => 'Damage',
            'theft' => 'Theft',
            'expiry' => 'Expiry',
            'error' => 'Error',
            'other' => 'Other'
        ];

        return view('ktvtc.cafeteria.inventory.index', compact(
            'stocks',
            'movements',
            'shops',
            'products',
            'movementTypes',
            'adjustmentCategories'
        ));
    }

    /**
     * Store stock adjustment
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:stock_take,damage,theft,expiry,error,other',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.adjustment_direction' => 'required|in:in,out',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $stock = InventoryStock::firstOrCreate(
                    [
                        'shop_id' => $validated['shop_id'],
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'current_stock' => 0,
                        'reserved_stock' => 0,
                        'average_unit_cost' => $item['unit_cost'] ?? 0,
                    ]
                );

                $oldQuantity = $stock->current_stock;

                if ($item['adjustment_direction'] === 'in') {
                    $newQuantity = $oldQuantity + $item['quantity'];
                    $movementType = 'adjustment_in';
                } else {
                    $newQuantity = $oldQuantity - $item['quantity'];
                    $movementType = 'adjustment_out';
                }

                // Ensure stock doesn't go negative
                if ($newQuantity < 0) {
                    throw new \Exception("Stock cannot go negative for product ID {$item['product_id']}");
                }

                $unitCost = $item['unit_cost'] ?? $stock->average_unit_cost;

                // Generate movement number
                $movementNumber = 'MOV-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

                // Create movement
                InventoryMovement::create([
                    'movement_number' => $movementNumber,
                    'product_id' => $item['product_id'],
                    'shop_id' => $validated['shop_id'],
                    'movement_type' => $movementType,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $item['quantity'] * $unitCost,
                    'previous_stock' => $oldQuantity,
                    'new_stock' => $newQuantity,
                    'reference_number' => 'ADJ-' . date('YmdHis'),
                    'reference_type' => 'stock_adjustment',
                    'reason' => $validated['reason'],
                    'adjustment_category' => $validated['adjustment_type'],
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'recorded_by' => auth()->id(),
                    'movement_date' => $validated['adjustment_date'],
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id(),
                ]);

                // Update stock - REMOVED available_stock and stock_value as they are generated columns
                $stock->update([
                    'current_stock' => $newQuantity,
                    'last_movement_at' => now(),
                    'last_adjusted_date' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment completed successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store stock transfer
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'from_shop_id' => 'required|exists:shops,id',
            'to_shop_id' => 'required|exists:shops,id|different:from_shop_id',
            'transfer_date' => 'required|date',
            'transfer_reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                // Check source stock
                $sourceStock = InventoryStock::where('shop_id', $validated['from_shop_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$sourceStock || ($sourceStock->current_stock - $sourceStock->reserved_stock) < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product ID {$item['product_id']} in source shop");
                }

                $transferNumber = 'TRF-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

                // Update source (transfer out)
                $oldSourceStock = $sourceStock->current_stock;
                $newSourceStock = $oldSourceStock - $item['quantity'];

                // Create transfer out movement
                InventoryMovement::create([
                    'movement_number' => 'MOV-OUT-' . $transferNumber,
                    'product_id' => $item['product_id'],
                    'shop_id' => $validated['from_shop_id'],
                    'movement_type' => 'transfer_out',
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'] ?? $sourceStock->average_unit_cost,
                    'total_cost' => $item['quantity'] * ($item['unit_cost'] ?? $sourceStock->average_unit_cost),
                    'previous_stock' => $oldSourceStock,
                    'new_stock' => $newSourceStock,
                    'reference_number' => $transferNumber,
                    'reference_type' => 'stock_transfer',
                    'from_shop_id' => $validated['from_shop_id'],
                    'to_shop_id' => $validated['to_shop_id'],
                    'transfer_reason' => $validated['transfer_reason'],
                    'recorded_by' => auth()->id(),
                    'movement_date' => $validated['transfer_date'],
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id(),
                ]);

                // Update source stock - REMOVED available_stock (generated column)
                $sourceStock->update([
                    'current_stock' => $newSourceStock,
                    'last_movement_at' => now(),
                ]);

                // Update destination (transfer in)
                $destStock = InventoryStock::firstOrCreate(
                    [
                        'shop_id' => $validated['to_shop_id'],
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'current_stock' => 0,
                        'reserved_stock' => 0,
                        'average_unit_cost' => $item['unit_cost'] ?? $sourceStock->average_unit_cost,
                    ]
                );

                $oldDestStock = $destStock->current_stock;
                $newDestStock = $oldDestStock + $item['quantity'];

                // Create transfer in movement
                InventoryMovement::create([
                    'movement_number' => 'MOV-IN-' . $transferNumber,
                    'product_id' => $item['product_id'],
                    'shop_id' => $validated['to_shop_id'],
                    'movement_type' => 'transfer_in',
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'] ?? $sourceStock->average_unit_cost,
                    'total_cost' => $item['quantity'] * ($item['unit_cost'] ?? $sourceStock->average_unit_cost),
                    'previous_stock' => $oldDestStock,
                    'new_stock' => $newDestStock,
                    'reference_number' => $transferNumber,
                    'reference_type' => 'stock_transfer',
                    'from_shop_id' => $validated['from_shop_id'],
                    'to_shop_id' => $validated['to_shop_id'],
                    'transfer_reason' => $validated['transfer_reason'],
                    'recorded_by' => auth()->id(),
                    'movement_date' => $validated['transfer_date'],
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id(),
                ]);

                // Update destination stock - REMOVED available_stock (generated column)
                $destStock->update([
                    'current_stock' => $newDestStock,
                    'last_movement_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transfer completed successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete movement
     */
    public function deleteMovement(InventoryMovement $movement)
    {
        // Only allow deletion of recent movements (within 24 hours)
        if ($movement->created_at->diffInHours(now()) > 24) {
            return response()->json(['success' => false, 'message' => 'Cannot delete movements older than 24 hours.'], 403);
        }

        DB::beginTransaction();
        try {
            // Reverse the movement
            $stock = InventoryStock::where('shop_id', $movement->shop_id)
                ->where('product_id', $movement->product_id)
                ->first();

            if ($stock) {
                // Calculate new stock after reversal
                $movementTypesThatIncreaseStock = ['purchase', 'transfer_in', 'adjustment_in', 'production_in', 'return_in'];

                if (in_array($movement->movement_type, $movementTypesThatIncreaseStock)) {
                    $newStock = $stock->current_stock - $movement->quantity;
                } else {
                    $newStock = $stock->current_stock + $movement->quantity;
                }

                // Ensure stock doesn't go negative
                if ($newStock < 0) {
                    throw new \Exception("Cannot delete movement - would cause negative stock");
                }

                // Update stock - REMOVED available_stock (generated column)
                $stock->update([
                    'current_stock' => $newStock,
                ]);
            }

            $movement->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Movement deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get movements data for AJAX
     */
    public function getMovementsData(Request $request)
    {
        $query = InventoryMovement::with(['product' => function($q) {
            $q->withTrashed();
        }, 'shop', 'recorder'])
            ->when($request->shop_id, function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($request->product_id, function ($q, $productId) {
                $q->where('product_id', $productId);
            })
            ->when($request->movement_type, function ($q, $type) {
                $q->where('movement_type', $type);
            })
            ->when($request->start_date, function ($q, $startDate) {
                $q->whereDate('movement_date', '>=', $startDate);
            })
            ->when($request->end_date, function ($q, $endDate) {
                $q->whereDate('movement_date', '<=', $endDate);
            });

        $movements = $query->latest('movement_date')->paginate(25);

        return response()->json([
            'html' => view('cafeteria.inventory.partials.movements-table', compact('movements'))->render(),
            'pagination' => $movements->links()->toHtml()
        ]);
    }

    /**
     * Get stocks data for AJAX
     */
    public function getStocksData(Request $request)
    {
        $query = InventoryStock::with(['product' => function($q) {
            $q->withTrashed();
        }, 'shop'])
            ->when($request->shop_id, function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($request->product_id, function ($q, $productId) {
                $q->where('product_id', $productId);
            })
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('product', function ($q) use ($search) {
                        $q->withTrashed()
                          ->where('product_name', 'like', "%{$search}%")
                          ->orWhere('product_code', 'like', "%{$search}%");
                    });
                });
            });

        $stocks = $query->orderBy('current_stock', 'asc')->paginate(25);

        return response()->json([
            'html' => view('cafeteria.inventory.partials.stocks-table', compact('stocks'))->render(),
            'pagination' => $stocks->links()->toHtml()
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(Request $request)
    {
        $query = InventoryStock::with(['product' => function($q) {
            $q->withTrashed();
        }, 'shop'])
            ->whereRaw('current_stock - reserved_stock <= 0')
            ->orWhereHas('product', function($q) {
                $q->where('track_inventory', true)
                  ->whereColumn('current_stock', '<=', 'reorder_level');
            });

        if ($request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }

        $alerts = $query->get();

        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'alerts' => $alerts
        ]);
    }

    /**
     * Get stock statistics
     */
    public function getStockStats(Request $request)
    {
        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'total_stock_value' => InventoryStock::sum(DB::raw('current_stock * average_unit_cost')),
            'low_stock_count' => InventoryStock::whereRaw('current_stock - reserved_stock <= 0')->count(),
            'out_of_stock_count' => InventoryStock::where('current_stock', '<=', 0)->count(),
        ];

        return response()->json($stats);
    }
}
