<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\BusinessSection;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'businessSection',
            'shop',
            'items',
            'items.product',
            'approver',
            'creator',
            'updater'
        ]);

        // Filters
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

        if ($request->has('from_date')) {
            $query->where('order_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('order_date', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('supplier_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $purchaseOrders = $query->paginate($request->get('per_page', 15));

        // Get counts for status tabs
        $totalOrders = PurchaseOrder::count();
        $draftCount = PurchaseOrder::where('status', 'draft')->count();
        $pendingCount = PurchaseOrder::where('status', 'pending_approval')->count();
        $approvedCount = PurchaseOrder::where('status', 'approved')->count();
        $orderedCount = PurchaseOrder::where('status', 'ordered')->count();

        // Get filter data - FIXED: Using correct column names
        $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();

        // FIXED: Using section_name instead of name
        $businessSections = BusinessSection::where('is_active', true)->orderBy('section_name')->get();

        // Shops - ensure it exists or use empty collection
        $shops = collect();
        if (class_exists(Shop::class)) {
            $shops = Shop::where('is_active', true)->orderBy('shop_name')->get();
        }

        // Status colors
        $statusColors = [
            'draft' => 'bg-gray-100 text-gray-800',
            'pending_approval' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'ordered' => 'bg-blue-100 text-blue-800',
            'partial' => 'bg-indigo-100 text-indigo-800',
            'received' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'closed' => 'bg-gray-800 text-white'
        ];

        // Return view for web requests
        if (!$request->ajax()) {
            return view('ktvtc.cafeteria.purchase.index', [
                'purchaseOrders' => $purchaseOrders,
                'totalOrders' => $totalOrders,
                'draftCount' => $draftCount,
                'pendingCount' => $pendingCount,
                'approvedCount' => $approvedCount,
                'orderedCount' => $orderedCount,
                'suppliers' => $suppliers,
                'businessSections' => $businessSections,
                'shops' => $shops,
                'statusColors' => $statusColors,
                'filters' => $request->all()
            ]);
        }

        // Return JSON for AJAX requests
        return response()->json([
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'business_section_id' => 'required|exists:business_sections,id',
            'shop_id' => 'required|exists:shops,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string',
            'delivery_method' => 'nullable|in:pickup,supplier_delivery,courier',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
            'items.*.specifications' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Generate PO number
            $poNumber = $this->generatePONumber();

            $data = $request->except('items');
            $data['po_number'] = $poNumber;
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();
            $data['total_items'] = 0;
            $data['total_quantity'] = 0;
            $data['subtotal'] = 0;
            $data['tax_amount'] = 0;
            $data['total_amount'] = 0;

            $purchaseOrder = PurchaseOrder::create($data);

            // Process items
            $totalItems = 0;
            $totalQuantity = 0;
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $totalPrice = $itemData['quantity_ordered'] * $itemData['unit_price'];
                $itemTax = $totalPrice * ($itemData['tax_rate'] ?? 0) / 100;

                $purchaseOrder->items()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'unit' => $product->unit,
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'notes' => $itemData['notes'] ?? null,
                    'specifications' => $itemData['specifications'] ?? null
                ]);

                $totalItems++;
                $totalQuantity += $itemData['quantity_ordered'];
                $subtotal += $totalPrice;
                $taxAmount += $itemTax;
            }

            // Update totals
            $purchaseOrder->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'purchaseOrder' => $purchaseOrder->load(['supplier', 'businessSection', 'shop', 'items', 'items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePONumber()
    {
        $prefix = 'PO-';
        $year = date('Y');
        $month = date('m');

        $lastPO = PurchaseOrder::where('po_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            $lastNumber = intval(substr($lastPO->po_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . '-' . $newNumber;
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'businessSection',
            'shop',
            'items',
            'items.product',
            'goodsReceivedNotes',
            'goodsReceivedNotes.items',
            'approver',
            'creator',
            'updater'
        ])->findOrFail($id);

        return response()->json($purchaseOrder);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        // Only allow updates on draft status
        if ($purchaseOrder->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase orders can be modified'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'shop_id' => 'sometimes|exists:shops,id',
            'order_date' => 'sometimes|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string',
            'delivery_method' => 'nullable|in:pickup,supplier_delivery,courier',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $purchaseOrder->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order updated successfully',
            'purchaseOrder' => $purchaseOrder->fresh()->load(['supplier', 'businessSection', 'shop', 'items', 'items.product'])
        ]);
    }

    public function updateItems(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        // Only allow updates on draft status
        if ($purchaseOrder->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase orders can be modified'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
            'items.*.specifications' => 'nullable|string'
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
            $purchaseOrder->items()->delete();

            // Process new items
            $totalItems = 0;
            $totalQuantity = 0;
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $totalPrice = $itemData['quantity_ordered'] * $itemData['unit_price'];
                $itemTax = $totalPrice * ($itemData['tax_rate'] ?? 0) / 100;

                $purchaseOrder->items()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'unit' => $product->unit,
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'notes' => $itemData['notes'] ?? null,
                    'specifications' => $itemData['specifications'] ?? null
                ]);

                $totalItems++;
                $totalQuantity += $itemData['quantity_ordered'];
                $subtotal += $totalPrice;
                $taxAmount += $itemTax;
            }

            // Update totals
            $purchaseOrder->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order items updated successfully',
                'purchaseOrder' => $purchaseOrder->fresh()->load(['items', 'items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending_approval,approved,ordered,cancelled',
            'approval_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate status transitions
        $validTransitions = [
            'draft' => ['pending_approval', 'cancelled'],
            'pending_approval' => ['approved', 'cancelled'],
            'approved' => ['ordered', 'cancelled'],
            'ordered' => ['partial', 'received', 'cancelled'],
            'partial' => ['received', 'cancelled'],
            'received' => ['closed'],
            'cancelled' => [],
            'closed' => []
        ];

        if (!in_array($request->status, $validTransitions[$purchaseOrder->status] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition from ' . $purchaseOrder->status . ' to ' . $request->status
            ], 422);
        }

        $data = ['status' => $request->status];

        if ($request->status == 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
            $data['approval_notes'] = $request->approval_notes;
        }

        $data['updated_by'] = auth()->id();

        $purchaseOrder->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order status updated successfully',
            'purchaseOrder' => $purchaseOrder->fresh()
        ]);
    }

    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        // Only allow deletion of draft purchase orders
        if ($purchaseOrder->status != 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase orders can be deleted'
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase order deleted successfully'
        ]);
    }

    // Additional methods for getting data
    public function getProducts()
    {
        $products = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get(['id', 'product_code', 'product_name', 'unit', 'current_stock', 'reorder_level']);

        return response()->json($products);
    }

    public function pendingApproval(Request $request)
    {
        $purchaseOrders = PurchaseOrder::where('status', 'pending_approval')
            ->with(['supplier', 'businessSection', 'shop'])
            ->paginate($request->get('per_page', 15));

        return response()->json($purchaseOrders);
    }

    public function overdue(Request $request)
    {
        $purchaseOrders = PurchaseOrder::where('status', 'ordered')
            ->where('expected_delivery_date', '<', now())
            ->with(['supplier', 'businessSection', 'shop'])
            ->paginate($request->get('per_page', 15));

        return response()->json($purchaseOrders);
    }
}
