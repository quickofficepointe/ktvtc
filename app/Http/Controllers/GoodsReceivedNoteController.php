<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\BusinessSection;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GoodsReceivedNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceivedNote::with([
            'purchaseOrder',
            'supplier',
            'businessSection',
            'shop',
            'items',
            'items.product',
            'qualityChecker',
            'receiver',
            'checker',
            'approver',
            'creator',
            'updater'
        ]);

        // Filters
        if ($request->has('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('quality_status')) {
            $query->where('quality_status', $request->quality_status);
        }

        if ($request->has('from_date')) {
            $query->where('delivery_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('delivery_date', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('delivery_note_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('supplier_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'delivery_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $grns = $query->paginate($request->get('per_page', 15));

        // Get counts for status
        $totalGrns = GoodsReceivedNote::count();
        $draftCount = GoodsReceivedNote::where('status', 'draft')->count();
        $pendingCount = GoodsReceivedNote::where('status', 'pending')->count();
        $completedCount = GoodsReceivedNote::where('status', 'completed')->count();

        // Quality status counts
        $pendingQualityCount = GoodsReceivedNote::where('quality_status', 'pending')->count();
        $passedQualityCount = GoodsReceivedNote::where('quality_status', 'passed')->count();
        $failedQualityCount = GoodsReceivedNote::where('quality_status', 'failed')->count();

        // Get filter data
        $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();
        $businessSections = BusinessSection::where('is_active', true)->orderBy('section_name')->get();
        $shops = Shop::where('is_active', true)->orderBy('shop_name')->get();
        $purchaseOrders = PurchaseOrder::where('status', 'ordered')
            ->orWhere('status', 'partial')
            ->orderBy('po_number')
            ->get();

        // Status colors
        $statusColors = [
            'draft' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800'
        ];

        $qualityStatusColors = [
            'pending' => 'bg-gray-100 text-gray-800',
            'passed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'partial' => 'bg-yellow-100 text-yellow-800'
        ];

        // Today's GRNs
        $todayGrns = GoodsReceivedNote::whereDate('delivery_date', today())
            ->with(['purchaseOrder', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Return view for web requests
        if (!$request->ajax()) {
            return view('ktvtc.cafeteria.grn.index', [
                'grns' => $grns,
                'todayGrns' => $todayGrns,
                'totalGrns' => $totalGrns,
                'draftCount' => $draftCount,
                'pendingCount' => $pendingCount,
                'completedCount' => $completedCount,
                'pendingQualityCount' => $pendingQualityCount,
                'passedQualityCount' => $passedQualityCount,
                'failedQualityCount' => $failedQualityCount,
                'suppliers' => $suppliers,
                'businessSections' => $businessSections,
                'shops' => $shops,
                'purchaseOrders' => $purchaseOrders,
                'statusColors' => $statusColors,
                'qualityStatusColors' => $qualityStatusColors,
                'filters' => $request->all()
            ]);
        }

        // Return JSON for AJAX requests
        return response()->json([
            'grns' => $grns,
            'filters' => $request->all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'business_section_id' => 'required|exists:business_sections,id',
            'shop_id' => 'required|exists:shops,id',
            'delivery_date' => 'required|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|numeric|min:0.001',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.condition' => 'nullable|in:good,damaged,expired,wrong_item',
            'items.*.quality_notes' => 'nullable|string',
            'items.*.storage_location' => 'nullable|string|max:255',
            'items.*.shelf_number' => 'nullable|string|max:50',
            'quality_notes' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Generate GRN number
            $grnNumber = $this->generateGRNNumber();

            $data = $request->except('items');
            $data['grn_number'] = $grnNumber;
            $data['status'] = 'draft';
            $data['quality_status'] = 'pending';
            $data['received_by'] = auth()->id();
            $data['received_date'] = now();
            $data['created_by'] = auth()->id();
            $data['total_items'] = 0;
            $data['total_quantity'] = 0;
            $data['total_value'] = 0;

            $grn = GoodsReceivedNote::create($data);

            // Process items
            $totalItems = 0;
            $totalQuantity = 0;
            $totalValue = 0;

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                $totalItemValue = $itemData['quantity_accepted'] * $itemData['unit_price'];

                $grn->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity_received' => $itemData['quantity_received'],
                    'quantity_accepted' => $itemData['quantity_accepted'],
                    'quantity_rejected' => $itemData['quantity_rejected'] ?? 0,
                    'unit' => $product->unit,
                    'unit_price' => $itemData['unit_price'],
                    'total_value' => $totalItemValue,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'condition' => $itemData['condition'] ?? 'good',
                    'quality_notes' => $itemData['quality_notes'] ?? null,
                    'storage_location' => $itemData['storage_location'] ?? null,
                    'shelf_number' => $itemData['shelf_number'] ?? null
                ]);

                $totalItems++;
                $totalQuantity += $itemData['quantity_accepted'];
                $totalValue += $totalItemValue;
            }

            // Update totals
            $grn->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'GRN created successfully',
                'grn' => $grn->load(['supplier', 'businessSection', 'shop', 'items', 'items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create GRN: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateGRNNumber()
    {
        $prefix = 'GRN-';
        $year = date('Y');
        $month = date('m');

        $lastGRN = GoodsReceivedNote::where('grn_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('grn_number', 'desc')
            ->first();

        if ($lastGRN) {
            $lastNumber = intval(substr($lastGRN->grn_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . '-' . $newNumber;
    }

    public function show($id)
    {
        $grn = GoodsReceivedNote::with([
            'purchaseOrder',
            'supplier',
            'businessSection',
            'shop',
            'items',
            'items.product',
            'qualityChecker',
            'receiver',
            'checker',
            'approver',
            'creator',
            'updater'
        ])->findOrFail($id);

        return response()->json($grn);
    }

    public function update(Request $request, $id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        // Only allow updates on draft status
        if ($grn->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft GRNs can be modified'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'shop_id' => 'sometimes|exists:shops,id',
            'delivery_date' => 'sometimes|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'quality_notes' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $grn->update($data);

        return response()->json([
            'success' => true,
            'message' => 'GRN updated successfully',
            'grn' => $grn->fresh()->load(['supplier', 'businessSection', 'shop', 'items', 'items.product'])
        ]);
    }

    public function updateItems(Request $request, $id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        // Only allow updates on draft status
        if ($grn->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft GRNs can be modified'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|numeric|min:0.001',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.condition' => 'nullable|in:good,damaged,expired,wrong_item',
            'items.*.quality_notes' => 'nullable|string',
            'items.*.storage_location' => 'nullable|string|max:255',
            'items.*.shelf_number' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Delete existing items
            $grn->items()->delete();

            // Process new items
            $totalItems = 0;
            $totalQuantity = 0;
            $totalValue = 0;

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                $totalItemValue = $itemData['quantity_accepted'] * $itemData['unit_price'];

                $grn->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity_received' => $itemData['quantity_received'],
                    'quantity_accepted' => $itemData['quantity_accepted'],
                    'quantity_rejected' => $itemData['quantity_rejected'] ?? 0,
                    'unit' => $product->unit,
                    'unit_price' => $itemData['unit_price'],
                    'total_value' => $totalItemValue,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'condition' => $itemData['condition'] ?? 'good',
                    'quality_notes' => $itemData['quality_notes'] ?? null,
                    'storage_location' => $itemData['storage_location'] ?? null,
                    'shelf_number' => $itemData['shelf_number'] ?? null
                ]);

                $totalItems++;
                $totalQuantity += $itemData['quantity_accepted'];
                $totalValue += $totalItemValue;
            }

            // Update totals
            $grn->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'GRN items updated successfully',
                'grn' => $grn->fresh()->load(['items', 'items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update GRN items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed,rejected',
            'quality_status' => 'nullable|in:pending,passed,failed,partial',
            'quality_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate status transitions
        $validTransitions = [
            'draft' => ['pending'],
            'pending' => ['completed', 'rejected'],
            'completed' => [],
            'rejected' => []
        ];

        if (!in_array($request->status, $validTransitions[$grn->status] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition from ' . $grn->status . ' to ' . $request->status
            ], 422);
        }

        $data = [
            'status' => $request->status,
            'updated_by' => auth()->id()
        ];

        if ($request->has('quality_status')) {
            $data['quality_status'] = $request->quality_status;
            if ($request->quality_status != 'pending') {
                $data['quality_checked_by'] = auth()->id();
                $data['quality_checked_at'] = now();
                $data['quality_notes'] = $request->quality_notes;
            }
        }

        if ($request->status == 'rejected') {
            $data['rejection_reason'] = $request->rejection_reason;
        }

        $grn->update($data);

        return response()->json([
            'success' => true,
            'message' => 'GRN status updated successfully',
            'grn' => $grn->fresh()
        ]);
    }

    public function qualityCheck(Request $request, $id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quality_status' => 'required|in:passed,failed,partial',
            'quality_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $grn->update([
            'quality_status' => $request->quality_status,
            'quality_checked_by' => auth()->id(),
            'quality_checked_at' => now(),
            'quality_notes' => $request->quality_notes,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quality check completed successfully',
            'grn' => $grn->fresh()
        ]);
    }

    public function approve(Request $request, $id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        if ($grn->status != 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed GRNs can be approved'
            ], 422);
        }

        $grn->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'GRN approved successfully',
            'grn' => $grn->fresh()
        ]);
    }

    public function destroy($id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        // Only allow deletion of draft GRNs
        if ($grn->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft GRNs can be deleted'
            ], 422);
        }

        $grn->delete();

        return response()->json([
            'success' => true,
            'message' => 'GRN deleted successfully'
        ]);
    }

    // Additional methods
    public function getProductsByPurchaseOrder($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::with(['items', 'items.product'])->findOrFail($purchaseOrderId);

        $products = [];
        foreach ($purchaseOrder->items as $item) {
            $products[] = [
                'id' => $item->product_id,
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'unit' => $item->unit,
                'quantity_ordered' => $item->quantity_ordered,
                'quantity_received' => $item->quantity_received,
                'unit_price' => $item->unit_price
            ];
        }

        return response()->json($products);
    }
}
