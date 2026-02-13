<?php

namespace App\Http\Controllers;

use App\Models\DirectPurchase;
use App\Models\Supplier;
use App\Models\BusinessSection;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DirectPurchaseController extends Controller
{
    public function index(Request $request)
{
    $query = DirectPurchase::with([
        'supplier',
        'businessSection',
        'shop',
        'purchaser',
        'receiver',
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

    if ($request->has('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }

    if ($request->has('payment_status')) {
        $query->where('payment_status', $request->payment_status);
    }

    if ($request->has('from_date')) {
        $query->where('purchase_date', '>=', $request->from_date);
    }

    if ($request->has('to_date')) {
        $query->where('purchase_date', '<=', $request->to_date);
    }

    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhere('supplier_name', 'like', "%{$search}%")
              ->orWhereHas('supplier', function($q) use ($search) {
                  $q->where('supplier_name', 'like', "%{$search}%");
              });
        });
    }

    // Sorting
    $sortBy = $request->get('sort_by', 'purchase_date');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    $directPurchases = $query->paginate($request->get('per_page', 15));

    // Get counts
    $totalPurchases = DirectPurchase::count();
    $cashPurchases = DirectPurchase::where('payment_method', 'cash')->count();
    $mpesaPurchases = DirectPurchase::where('payment_method', 'mpesa')->count();
    $pendingPayments = DirectPurchase::where('payment_status', 'pending')->count();
    $partialPayments = DirectPurchase::where('payment_status', 'partial')->count();

    // Get filter data
    $suppliers = Supplier::where('is_active', true)->orderBy('supplier_name')->get();
    $businessSections = BusinessSection::where('is_active', true)->orderBy('section_name')->get();
    $shops = Shop::where('is_active', true)->orderBy('shop_name')->get();
    $products = Product::where('is_active', true)->orderBy('product_name')->get();

    // Today's purchases LIST (for display)
    $todayPurchasesList = DirectPurchase::whereDate('purchase_date', today())
        ->with(['supplier', 'businessSection', 'shop'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    // Today's purchases COUNT (for stats)
    $todayPurchasesCount = $todayPurchasesList->count();

    // Status colors
    $paymentStatusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'partial' => 'bg-blue-100 text-blue-800',
        'paid' => 'bg-green-100 text-green-800',
        'overdue' => 'bg-red-100 text-red-800'
    ];

    $paymentMethodColors = [
        'cash' => 'bg-green-100 text-green-800',
        'mpesa' => 'bg-purple-100 text-purple-800',
        'bank_transfer' => 'bg-blue-100 text-blue-800',
        'credit' => 'bg-orange-100 text-orange-800'
    ];

    // Return view for web requests
    if (!$request->ajax()) {
        return view('ktvtc.cafeteria.purchase.direct', [
            'directPurchases' => $directPurchases,
            'todayPurchasesList' => $todayPurchasesList, // This is what you're using in Blade
            'todayPurchases' => $todayPurchasesCount, // This is for the stat card
            'totalPurchases' => $totalPurchases,
            'cashPurchases' => $cashPurchases,
            'mpesaPurchases' => $mpesaPurchases,
            'pendingPayments' => $pendingPayments,
            'partialPayments' => $partialPayments,
            'suppliers' => $suppliers,
            'businessSections' => $businessSections,
            'shops' => $shops,
            'products' => $products,
            'paymentStatusColors' => $paymentStatusColors,
            'paymentMethodColors' => $paymentMethodColors,
            'filters' => $request->all()
        ]);
    }

    // Return JSON for AJAX requests
    return response()->json([
        'directPurchases' => $directPurchases,
        'filters' => $request->all()
    ]);
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'business_section_id' => 'required|exists:business_sections,id',
            'shop_id' => 'required|exists:shops,id',
            'purchase_date' => 'required|date',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'delivery_details' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,mpesa,bank_transfer,credit',
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'payment_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:100',
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
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Calculate totals
            $totalItems = count($request->items);
            $totalQuantity = 0;
            $subtotal = 0;
            $taxAmount = 0;

            $items = [];
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];

                $items[] = [
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'quantity' => $itemData['quantity'],
                    'unit' => $product->unit,
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null
                ];

                $totalQuantity += $itemData['quantity'];
                $subtotal += $itemTotal;
            }

            $totalAmount = $subtotal + $taxAmount;

            $data = $request->except('items');
            $data['invoice_number'] = $invoiceNumber;
            $data['items'] = $items;
            $data['total_items'] = $totalItems;
            $data['total_quantity'] = $totalQuantity;
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['total_amount'] = $totalAmount;
            $data['purchased_by'] = auth()->id();
            $data['received_by'] = auth()->id();
            $data['created_by'] = auth()->id();

            // If supplier_id is not provided, use supplier_name
            if (!$request->supplier_id && $request->supplier_name) {
                $data['supplier_name'] = $request->supplier_name;
                $data['supplier_phone'] = $request->supplier_phone;
            }

            // Set payment date for paid purchases
            if ($request->payment_status == 'paid' && !$request->payment_date) {
                $data['payment_date'] = now();
            }

            $directPurchase = DirectPurchase::create($data);

            // Update inventory stock
            $this->updateInventoryFromDirectPurchase($directPurchase);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Direct purchase created successfully',
                'directPurchase' => $directPurchase->load(['supplier', 'businessSection', 'shop', 'purchaser', 'receiver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create direct purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'DP-';
        $year = date('Y');
        $month = date('m');

        $lastPurchase = DirectPurchase::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = intval(substr($lastPurchase->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . '-' . $newNumber;
    }

    private function updateInventoryFromDirectPurchase($directPurchase)
    {
        foreach ($directPurchase->items as $item) {
            // Create inventory movement
            $movementNumber = 'MOV-' . date('Ymd') . '-' . strtoupper(uniqid());

            DB::table('inventory_movements')->insert([
                'movement_number' => $movementNumber,
                'product_id' => $item['product_id'],
                'shop_id' => $directPurchase->shop_id,
                'movement_type' => 'direct_purchase',
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_cost' => $item['unit_price'],
                'total_cost' => $item['total_price'],
                'reference_number' => $directPurchase->invoice_number,
                'reference_type' => 'direct_purchase',
                'reference_id' => $directPurchase->id,
                'batch_number' => $item['batch_number'],
                'expiry_date' => $item['expiry_date'],
                'recorded_by' => auth()->id(),
                'movement_date' => now(),
                'notes' => 'Direct purchase: ' . $directPurchase->invoice_number,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update inventory stock
            $stock = DB::table('inventory_stocks')
                ->where('product_id', $item['product_id'])
                ->where('shop_id', $directPurchase->shop_id)
                ->first();

            if ($stock) {
                // Calculate new average cost using weighted average
                $totalQuantity = $stock->current_stock + $item['quantity'];
                $totalValue = ($stock->current_stock * $stock->average_unit_cost) + $item['total_price'];
                $averageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : $item['unit_price'];

                DB::table('inventory_stocks')
                    ->where('product_id', $item['product_id'])
                    ->where('shop_id', $directPurchase->shop_id)
                    ->update([
                        'current_stock' => $totalQuantity,
                        'available_stock' => $totalQuantity - $stock->reserved_stock,
                        'average_unit_cost' => $averageCost,
                        'stock_value' => $totalQuantity * $averageCost,
                        'last_movement_at' => now(),
                        'last_received_date' => now(),
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('inventory_stocks')->insert([
                    'product_id' => $item['product_id'],
                    'shop_id' => $directPurchase->shop_id,
                    'current_stock' => $item['quantity'],
                    'available_stock' => $item['quantity'],
                    'average_unit_cost' => $item['unit_price'],
                    'stock_value' => $item['total_price'],
                    'last_movement_at' => now(),
                    'last_received_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    public function show($id)
    {
        $directPurchase = DirectPurchase::with([
            'supplier',
            'businessSection',
            'shop',
            'purchaser',
            'receiver',
            'approver',
            'creator',
            'updater'
        ])->findOrFail($id);

        return response()->json($directPurchase);
    }

    public function update(Request $request, $id)
    {
        $directPurchase = DirectPurchase::findOrFail($id);

        // Only allow updates if not approved
        if ($directPurchase->approved_by) {
            return response()->json([
                'success' => false,
                'message' => 'Approved direct purchases cannot be modified'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'shop_id' => 'sometimes|exists:shops,id',
            'purchase_date' => 'sometimes|date',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'delivery_details' => 'nullable|string',
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

        $directPurchase->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Direct purchase updated successfully',
            'directPurchase' => $directPurchase->fresh()->load(['supplier', 'businessSection', 'shop'])
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $directPurchase = DirectPurchase::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'payment_method' => 'sometimes|in:cash,mpesa,bank_transfer,credit',
            'payment_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        // Set payment date for paid purchases
        if ($request->payment_status == 'paid' && !$request->payment_date) {
            $data['payment_date'] = now();
        }

        $directPurchase->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'directPurchase' => $directPurchase->fresh()
        ]);
    }

    public function approve(Request $request, $id)
    {
        $directPurchase = DirectPurchase::findOrFail($id);

        if ($directPurchase->approved_by) {
            return response()->json([
                'success' => false,
                'message' => 'Direct purchase already approved'
            ], 422);
        }

        $directPurchase->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Direct purchase approved successfully',
            'directPurchase' => $directPurchase->fresh()
        ]);
    }

    public function destroy($id)
    {
        $directPurchase = DirectPurchase::findOrFail($id);

        // Only allow deletion if not approved
        if ($directPurchase->approved_by) {
            return response()->json([
                'success' => false,
                'message' => 'Approved direct purchases cannot be deleted'
            ], 422);
        }

        $directPurchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Direct purchase deleted successfully'
        ]);
    }

    public function today(Request $request)
    {
        $purchases = DirectPurchase::whereDate('purchase_date', today())
            ->with(['supplier', 'businessSection', 'shop'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($purchases);
    }

    public function bySupplier($supplierId, Request $request)
    {
        $purchases = DirectPurchase::where('supplier_id', $supplierId)
            ->with(['businessSection', 'shop'])
            ->orderBy('purchase_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($purchases);
    }
}
