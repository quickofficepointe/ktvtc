<?php

namespace App\Http\Controllers;

use App\Models\CafeteriaDailyProduction;
use App\Models\DailyProductionItem;
use App\Models\DailyRawMaterialUsage;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CafeteriaDailyProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CafeteriaDailyProduction::with(['shop', 'recorder', 'verifier'])
            ->orderBy('production_date', 'desc');

        // Filters
        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date')) {
            $query->where('production_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('production_date', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('shop', function($q2) use ($search) {
                    $q2->where('shop_name', 'like', "%{$search}%")
                       ->orWhere('shop_code', 'like', "%{$search}%");
                });
            });
        }

        $productions = $query->paginate($request->get('per_page', 20));

        $shops = Shop::where('is_active', true)
            ->orderBy('shop_name')
            ->get();

        // Get production items and raw materials for modals
        $productionItems = Product::where('is_production_item', true)
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();

        $rawMaterials = Product::where('product_type', 'raw_material')
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();

        // Check if today's production exists
        $today = Carbon::today();
        $todayProduction = CafeteriaDailyProduction::whereDate('production_date', $today)
            ->where('shop_id', auth()->user()->shop_id ?? null)
            ->first();

        // Get specific production for view/edit
        $production = null;
        if ($request->has('view') || $request->has('edit')) {
            $production = CafeteriaDailyProduction::with([
                'shop',
                'recorder',
                'verifier',
                'productionItems.product',
                'rawMaterialUsages.rawMaterial',
                'rawMaterialUsages.producedProduct'
            ])->find($request->id);
        }

        return view('ktvtc.cafeteria.daily-production.index', [
            'productions' => $productions,
            'shops' => $shops,
            'productionItems' => $productionItems,
            'rawMaterials' => $rawMaterials,
            'today' => $today->format('Y-m-d'),
            'todayProduction' => $todayProduction,
            'production' => $production,
            'viewMode' => $request->has('view') ? 'view' : ($request->has('edit') ? 'edit' : 'list'),
            'filters' => $request->only(['shop_id', 'status', 'start_date', 'end_date', 'search'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'production_date' => 'required|date',
            'shop_id' => 'required|exists:shops,id',
            'notes' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.planned_quantity' => 'required|numeric|min:0',
            'items.*.actual_quantity' => 'required|numeric|min:0',
            'items.*.unit_selling_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.raw_material_product_id' => 'required|exists:products,id',
            'raw_materials.*.quantity_used' => 'required|numeric|min:0.001',
            'raw_materials.*.unit_cost' => 'required|numeric|min:0',
            'raw_materials.*.notes' => 'nullable|string',
            'raw_materials.*.produced_product_id' => 'nullable|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if production already exists
        $existingProduction = CafeteriaDailyProduction::where('production_date', $request->production_date)
            ->where('shop_id', $request->shop_id)
            ->first();

        if ($existingProduction) {
            return response()->json([
                'message' => 'A production record already exists for this date and shop.',
                'redirect' => route('cafeteria.daily-productions.index', ['edit' => true, 'id' => $existingProduction->id])
            ], 409);
        }

        DB::beginTransaction();
        try {
            // Create daily production
            $production = CafeteriaDailyProduction::create([
                'production_date' => $request->production_date,
                'shop_id' => $request->shop_id,
                'recorded_by' => auth()->id(),
                'notes' => $request->notes,
                'challenges' => $request->challenges,
                'suggestions' => $request->suggestions,
                'status' => 'draft',
                'created_by' => auth()->id()
            ]);

            // Save production items
            foreach ($request->items as $itemData) {
                DailyProductionItem::create([
                    'daily_production_id' => $production->id,
                    'product_id' => $itemData['product_id'],
                    'planned_quantity' => $itemData['planned_quantity'],
                    'actual_quantity' => $itemData['actual_quantity'],
                    'quantity_sold' => 0,
                    'quantity_wasted' => 0,
                    'unit_selling_price' => $itemData['unit_selling_price'],
                    'notes' => $itemData['notes'] ?? null
                ]);
            }

            // Save raw material usage
            if ($request->has('raw_materials') && !empty($request->raw_materials)) {
                foreach ($request->raw_materials as $materialData) {
                    DailyRawMaterialUsage::create([
                        'daily_production_id' => $production->id,
                        'raw_material_product_id' => $materialData['raw_material_product_id'],
                        'produced_product_id' => $materialData['produced_product_id'] ?? null,
                        'quantity_used' => $materialData['quantity_used'],
                        'unit' => 'kg',
                        'unit_cost' => $materialData['unit_cost'],
                        'total_cost' => $materialData['quantity_used'] * $materialData['unit_cost'],
                        'notes' => $materialData['notes'] ?? null,
                        'recorded_by' => auth()->id()
                    ]);
                }
            }

            // Calculate initial summary
            $this->recalculateProductionSummary($production);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily production created successfully!',
                'production_id' => $production->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create daily production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $production = CafeteriaDailyProduction::findOrFail($id);

        if ($production->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a verified production record.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:daily_production_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.planned_quantity' => 'required|numeric|min:0',
            'items.*.actual_quantity' => 'required|numeric|min:0',
            'items.*.quantity_sold' => 'required|numeric|min:0',
            'items.*.quantity_wasted' => 'required|numeric|min:0',
            'items.*.unit_selling_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.id' => 'nullable|exists:daily_raw_material_usage,id',
            'raw_materials.*.raw_material_product_id' => 'required|exists:products,id',
            'raw_materials.*.quantity_used' => 'required|numeric|min:0.001',
            'raw_materials.*.unit_cost' => 'required|numeric|min:0',
            'raw_materials.*.notes' => 'nullable|string',
            'raw_materials.*.produced_product_id' => 'nullable|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Update production
            $production->update([
                'notes' => $request->notes,
                'challenges' => $request->challenges,
                'suggestions' => $request->suggestions,
                'updated_by' => auth()->id()
            ]);

            // Update items
            $existingItemIds = $production->productionItems->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($request->items as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    $item = DailyProductionItem::find($itemData['id']);
                    $item->update([
                        'planned_quantity' => $itemData['planned_quantity'],
                        'actual_quantity' => $itemData['actual_quantity'],
                        'quantity_sold' => $itemData['quantity_sold'],
                        'quantity_wasted' => $itemData['quantity_wasted'],
                        'unit_selling_price' => $itemData['unit_selling_price'],
                        'notes' => $itemData['notes'] ?? null
                    ]);
                    $updatedItemIds[] = $itemData['id'];
                } else {
                    $item = DailyProductionItem::create([
                        'daily_production_id' => $production->id,
                        'product_id' => $itemData['product_id'],
                        'planned_quantity' => $itemData['planned_quantity'],
                        'actual_quantity' => $itemData['actual_quantity'],
                        'quantity_sold' => $itemData['quantity_sold'],
                        'quantity_wasted' => $itemData['quantity_wasted'],
                        'unit_selling_price' => $itemData['unit_selling_price'],
                        'notes' => $itemData['notes'] ?? null
                    ]);
                    $updatedItemIds[] = $item->id;
                }
            }

            // Delete removed items
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                DailyProductionItem::whereIn('id', $itemsToDelete)->delete();
            }

            // Update raw materials
            $existingMaterialIds = $production->rawMaterialUsages->pluck('id')->toArray();
            $updatedMaterialIds = [];

            if ($request->has('raw_materials') && !empty($request->raw_materials)) {
                foreach ($request->raw_materials as $materialData) {
                    if (isset($materialData['id']) && in_array($materialData['id'], $existingMaterialIds)) {
                        $material = DailyRawMaterialUsage::find($materialData['id']);
                        $material->update([
                            'raw_material_product_id' => $materialData['raw_material_product_id'],
                            'produced_product_id' => $materialData['produced_product_id'] ?? null,
                            'quantity_used' => $materialData['quantity_used'],
                            'unit_cost' => $materialData['unit_cost'],
                            'total_cost' => $materialData['quantity_used'] * $materialData['unit_cost'],
                            'notes' => $materialData['notes'] ?? null
                        ]);
                        $updatedMaterialIds[] = $materialData['id'];
                    } else {
                        $material = DailyRawMaterialUsage::create([
                            'daily_production_id' => $production->id,
                            'raw_material_product_id' => $materialData['raw_material_product_id'],
                            'produced_product_id' => $materialData['produced_product_id'] ?? null,
                            'quantity_used' => $materialData['quantity_used'],
                            'unit' => 'kg',
                            'unit_cost' => $materialData['unit_cost'],
                            'total_cost' => $materialData['quantity_used'] * $materialData['unit_cost'],
                            'notes' => $materialData['notes'] ?? null,
                            'recorded_by' => auth()->id()
                        ]);
                        $updatedMaterialIds[] = $material->id;
                    }
                }
            }

            // Delete removed materials
            $materialsToDelete = array_diff($existingMaterialIds, $updatedMaterialIds);
            if (!empty($materialsToDelete)) {
                DailyRawMaterialUsage::whereIn('id', $materialsToDelete)->delete();
            }

            // Recalculate costs and summary
            $this->distributeRawMaterialCosts($production);
            $this->recalculateProductionSummary($production);

            // Update status if completed
            $allItemsHaveSales = $production->productionItems->every(function ($item) {
                return $item->quantity_sold > 0 || $item->quantity_wasted > 0;
            });

            if ($allItemsHaveSales && $production->status === 'draft') {
                $production->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily production updated successfully!',
                'production_id' => $production->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update daily production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $production = CafeteriaDailyProduction::findOrFail($id);

        if ($production->status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a verified production record.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $production->productionItems()->delete();
            $production->rawMaterialUsages()->delete();
            $production->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily production deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete daily production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a production record.
     */
    public function verify($id)
    {
        $production = CafeteriaDailyProduction::findOrFail($id);

        if ($production->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Production must be completed before verification.'
            ], 400);
        }

        $production->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Production verified successfully!'
        ]);
    }

    /**
     * Update sales for production items.
     */
    public function updateSales(Request $request, $id)
    {
        $production = CafeteriaDailyProduction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sales' => 'required|array',
            'sales.*.item_id' => 'required|exists:daily_production_items,id',
            'sales.*.quantity_sold' => 'required|numeric|min:0',
            'sales.*.quantity_wasted' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->sales as $saleData) {
                $item = DailyProductionItem::find($saleData['item_id']);

                $total = $saleData['quantity_sold'] + $saleData['quantity_wasted'];
                if ($total > $item->actual_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Total quantity exceeds actual production for item #{$item->id}"
                    ], 400);
                }

                $item->update([
                    'quantity_sold' => $saleData['quantity_sold'],
                    'quantity_wasted' => $saleData['quantity_wasted'],
                    'total_sales_value' => $item->unit_selling_price * $saleData['quantity_sold']
                ]);
            }

            $this->recalculateProductionSummary($production);

            // Mark as completed if all items have sales data
            $allItemsHaveSales = $production->productionItems->every(function ($item) {
                return $item->quantity_sold > 0 || $item->quantity_wasted > 0;
            });

            if ($allItemsHaveSales && $production->status === 'draft') {
                $production->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales data updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribute raw material costs to production items.
     */
    private function distributeRawMaterialCosts($production)
    {
        $totalRawMaterialCost = $production->rawMaterialUsages->sum('total_cost');
        $totalActualQuantity = $production->productionItems->sum('actual_quantity');

        if ($totalRawMaterialCost <= 0 || $totalActualQuantity <= 0) {
            return;
        }

        $costPerUnit = $totalRawMaterialCost / $totalActualQuantity;

        foreach ($production->productionItems as $item) {
            $itemCost = $costPerUnit * $item->actual_quantity;
            $item->update([
                'unit_production_cost' => $item->actual_quantity > 0 ? $itemCost / $item->actual_quantity : 0,
                'total_production_cost' => $itemCost
            ]);
        }
    }

    /**
     * Recalculate production summary.
     */
    private function recalculateProductionSummary($production)
    {
        $production->refresh();

        $summary = [
            'total_items_produced' => $production->productionItems->sum('actual_quantity'),
            'total_items_sold' => $production->productionItems->sum('quantity_sold'),
            'total_items_wasted' => $production->productionItems->sum('quantity_wasted'),
            'total_raw_material_cost' => $production->rawMaterialUsages->sum('total_cost'),
            'total_production_cost' => $production->productionItems->sum('total_production_cost'),
            'total_sales_value' => $production->productionItems->sum('total_sales_value')
        ];

        $production->update($summary);
    }

    /**
     * Get production statistics.
     */
    public function statistics(Request $request)
    {
        $query = CafeteriaDailyProduction::query();

        if ($request->has('start_date')) {
            $query->where('production_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('production_date', '<=', $request->end_date);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        $stats = $query->select([
            DB::raw('COUNT(*) as total_records'),
            DB::raw('SUM(total_items_produced) as total_items_produced'),
            DB::raw('SUM(total_items_sold) as total_items_sold'),
            DB::raw('SUM(total_items_wasted) as total_items_wasted'),
            DB::raw('SUM(total_raw_material_cost) as total_raw_material_cost'),
            DB::raw('SUM(total_production_cost) as total_production_cost'),
            DB::raw('SUM(total_sales_value) as total_sales_value'),
            DB::raw('AVG(total_sales_value - total_production_cost) as avg_profit'),
            DB::raw('CASE WHEN SUM(total_production_cost) > 0 THEN
                (SUM(total_sales_value) - SUM(total_production_cost)) / SUM(total_production_cost) * 100
                ELSE 0 END as avg_margin_percentage')
        ])->first();

        return response()->json($stats);
    }

    /**
     * Show API endpoint for getting a specific production.
     */
    public function show($id)
    {
        $production = CafeteriaDailyProduction::with([
            'shop',
            'recorder',
            'verifier',
            'productionItems.product',
            'rawMaterialUsages.rawMaterial',
            'rawMaterialUsages.producedProduct'
        ])->findOrFail($id);

        return response()->json($production);
    }
}
