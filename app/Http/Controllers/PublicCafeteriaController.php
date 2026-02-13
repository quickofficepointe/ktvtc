<?php
// app/Http/Controllers/PublicCafeteriaController.php
namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Sale;
use App\Services\KcbSalesService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PublicCafeteriaController extends Controller
{
    public function index()
    {
        // Get cafeterias (shops of type 'cafeteria')
        $cafeterias = Shop::where('is_active', true)
                      ->orderBy('shop_name')
                      ->get();

        $departments = Department::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Get delivery/pickup locations
        $locations = DeliveryService::getLocations();

        return view('public.cafeteria.index', compact('cafeterias', 'locations', 'departments'));
    }

    public function getMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid shop selected'], 400);
        }

        $shopId = $request->shop_id;
        $shop = Shop::find($shopId);

        // Get all ACTIVE products for this cafeteria
        $products = Product::where('shop_id', $shopId)
                          ->where('is_active', true)
                          ->with('category')
                          ->orderBy('sort_order')
                          ->orderBy('product_name')
                          ->get();

        // Group by category for better display
        $categories = ProductCategory::whereHas('products', function($query) use ($shopId) {
            $query->where('shop_id', $shopId)
                  ->where('is_active', true);
        })
        ->with(['products' => function($query) use ($shopId) {
            $query->where('shop_id', $shopId)
                  ->where('is_active', true)
                  ->orderBy('sort_order')
                  ->orderBy('product_name');
        }])
        ->orderBy('sort_order')
        ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'categories' => $categories,
            'shop' => $shop
        ]);
    }

   public function placeOrder(Request $request)
{
    Log::info('Public order received', $request->all());

    $validator = Validator::make($request->all(), [
        'shop_id' => 'required|exists:shops,id',
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|min:10|max:12',
        'customer_email' => 'nullable|email',
        'order_type' => 'required|in:pickup,delivery',
        'location_id' => 'required|string',
        'location_details' => 'nullable|string|max:500',
        'pickup_time' => 'required|in:asap,30,45,60,custom',
        'custom_time' => 'nullable|date_format:H:i',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'special_instructions' => 'nullable|string|max:1000'
    ]);

    if ($validator->fails()) {
        Log::error('Order validation failed', $validator->errors()->toArray());
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        $shop = Shop::find($request->shop_id);
        $location = DeliveryService::getLocation($request->location_id);

        if (!$location) {
            return response()->json(['error' => 'Invalid location selected'], 400);
        }

        // 1. FIRST: Calculate total amount
        $totalAmount = 0;
        $formattedItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                throw new \Exception("Product not found: {$item['product_id']}");
            }

            if (!$product->is_active) {
                throw new \Exception("Product {$product->product_name} is not available");
            }

            $itemTotal = $product->selling_price * $item['quantity'];
            $totalAmount += $itemTotal;

            $formattedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'unit' => $product->unit ?? 'piece', // ADD THIS LINE - FIX THE ISSUE
                'unit_price' => $product->selling_price,
                'quantity' => $item['quantity'],
                'total_price' => $itemTotal,
                'final_price' => $itemTotal,
                'is_production_item' => $product->is_production_item ?? false
            ];
        }

        // Add any fees if needed (delivery fee, service charge, etc.)
        $deliveryFee = $request->order_type === 'delivery' ? 50 : 0;
        $serviceFee = 0;
        $totalAmount += $deliveryFee + $serviceFee;

        // 2. INITIATE STK PUSH FIRST
        $kcbSalesService = app(KcbSalesService::class);

        // Create a temporary sale record first (in pending status)
        $tempSale = Sale::create([
            'business_section_id' => $shop->business_section_id,
            'shop_id' => $shop->id,
            'sale_type' => 'online',
            'channel' => 'website',
            'invoice_number' => 'TEMP-' . date('YmdHis') . '-' . rand(1000, 9999),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_type' => 'online_customer',
            'payment_method' => 'mpesa',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'total_amount' => $totalAmount,
            'subtotal' => $totalAmount - $deliveryFee - $serviceFee,
            'delivery_fee' => $deliveryFee,
            'service_charge' => $serviceFee,
            'delivery_address' => $this->formatDeliveryAddress($request, $location),
            'delivery_instructions' => $this->formatDeliveryInstructions($request, $location),
            'internal_notes' => $this->formatOrderNotes($request),
            'sale_date' => now(),
            'created_by' => 1, // System user
            'recorded_by' => 1, // System user
        ]);

        // 3. INITIATE STK PUSH PAYMENT
        $paymentResult = $kcbSalesService->initiateSalePayment(
            $tempSale,
            $request->customer_phone,
            $totalAmount
        );

        if (isset($paymentResult['error'])) {
            // Delete temporary sale if payment initiation fails
            $tempSale->delete();
            throw new \Exception("Payment initiation failed: " . $paymentResult['error']);
        }

        // 4. Store the temporary sale with KCB details
        $tempSale->update([
            'checkout_request_id' => $paymentResult['checkout_request_id'],
            'merchant_request_id' => $paymentResult['merchant_request_id'],
            'kcb_invoice_number' => $paymentResult['kcb_invoice_number'],
            'kcb_response' => json_encode($paymentResult),
        ]);

        // 5. Save order items (but don't update inventory yet - wait for payment confirmation)
        foreach ($formattedItems as $itemData) {
            $tempSale->items()->create([
                'product_id' => $itemData['product_id'],
                'product_name' => $itemData['product_name'],
                'product_code' => $itemData['product_code'],
                'unit' => $itemData['unit'], // ADDED THIS FIELD - FIX
                'unit_price' => $itemData['unit_price'],
                'quantity' => $itemData['quantity'],
                'total_price' => $itemData['total_price'],
                'final_price' => $itemData['final_price'],
                'is_production_item' => $itemData['is_production_item'],
            ]);
        }

        Log::info('Online order payment initiated', [
            'temp_sale_id' => $tempSale->id,
            'checkout_request_id' => $paymentResult['checkout_request_id'],
            'kcb_invoice' => $paymentResult['kcb_invoice_number']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent to your phone. Please complete payment to confirm your order.',
            'order' => [
                'temporary_id' => $tempSale->id,
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'total_amount' => $totalAmount,
                'estimated_time' => $this->getEstimatedReadyTime($request),
                'pickup_location' => $this->getPickupLocationText($request, $location)
            ],
            'payment_info' => [
                'status' => 'pending',
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'message' => 'Check your phone to complete M-Pesa payment'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Online order processing failed: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Add this for more detailed error logging

        return response()->json([
            'success' => false,
            'error' => 'Failed to process order. Please try again.'
        ], 500);
    }
}

public function checkPaymentStatus(Request $request)
{
    $validator = Validator::make($request->all(), [
        'checkout_request_id' => 'required|string',
        'temp_sale_id' => 'required|exists:sales,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => 'Invalid request'], 400);
    }

    try {
        $kcbSalesService = app(KcbSalesService::class);
        $statusResult = $kcbSalesService->checkSalePaymentStatus($request->checkout_request_id);

        if ($statusResult['status'] === 'completed') {
            // Update the temporary sale to final status
            $sale = Sale::find($request->temp_sale_id);

            if ($sale) {
                // Generate final invoice number
                $finalInvoice = $this->generateInvoiceNumberWithShop($sale->shop_id);
                $sale->update([
                    'invoice_number' => $finalInvoice,
                    'payment_status' => 'paid',
                    'order_status' => 'confirmed',
                    'mpesa_receipt' => $statusResult['mpesa_receipt_number'],
                    'payment_confirmed_at' => now()
                ]);

                // Update inventory now that payment is confirmed
                $this->updateInventoryAfterPayment($sale);

                return response()->json([
                    'status' => 'completed',
                    'message' => 'Payment confirmed!',
                    'order' => [
                        'invoice_number' => $finalInvoice,
                        'estimated_time' => $this->getEstimatedReadyTimeForSale($sale),
                        'total_amount' => $sale->total_amount,
                        'payment_receipt' => $statusResult['mpesa_receipt_number']
                    ]
                ]);
            }
        }

        return response()->json($statusResult);

    } catch (\Exception $e) {
        Log::error('Payment status check error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Error checking payment status'
        ], 500);
    }
}
    private function formatDeliveryAddress($request, $location)
    {
        $address = $location['name'];

        if ($request->location_id === 'other_specified' && $request->location_details) {
            $address .= ': ' . $request->location_details;
        }

        return $address;
    }

    private function formatDeliveryInstructions($request, $location)
    {
        $instructions = [];

        if ($request->order_type === 'pickup') {
            $instructions[] = "PICKUP ORDER";
        } else {
            $instructions[] = "DELIVERY ORDER";
        }

        $instructions[] = "Location: " . $location['name'];
        $instructions[] = "Instructions: " . $location['instructions'];

        if ($request->special_instructions) {
            $instructions[] = "Customer Instructions: " . $request->special_instructions;
        }

        return implode(' | ', $instructions);
    }

    private function formatOrderNotes($request)
    {
        $notes = [];

        // Add pickup/delivery time
        $pickupTime = $this->getPickupTimeText($request);
        $notes[] = "Requested time: {$pickupTime}";

        // Add order type
        $notes[] = "Order type: " . ucfirst($request->order_type);

        // Add location
        $location = DeliveryService::getLocation($request->location_id);
        if ($location) {
            $notes[] = "Location: {$location['name']}";
        }

        return implode(' | ', $notes);
    }

    private function getPickupTimeText($request)
    {
        switch ($request->pickup_time) {
            case 'asap':
                return 'ASAP (20-30 minutes)';
            case '30':
                return '30 minutes from now';
            case '45':
                return '45 minutes from now';
            case '60':
                return '1 hour from now';
            case 'custom':
                return 'Custom time: ' . ($request->custom_time ?: 'Not specified');
            default:
                return 'ASAP';
        }
    }

    private function getEstimatedReadyTime($request)
    {
        $baseTime = now();

        switch ($request->pickup_time) {
            case 'asap':
                return $baseTime->addMinutes(25)->format('h:i A');
            case '30':
                return $baseTime->addMinutes(30)->format('h:i A');
            case '45':
                return $baseTime->addMinutes(45)->format('h:i A');
            case '60':
                return $baseTime->addHour()->format('h:i A');
            case 'custom':
                return $request->custom_time ? date('h:i A', strtotime($request->custom_time)) : 'ASAP';
            default:
                return $baseTime->addMinutes(25)->format('h:i A');
        }
    }

    private function getPickupLocationText($request, $location)
    {
        $text = $location['name'];

        if ($request->order_type === 'pickup') {
            $text .= " (Pickup)";
        } else {
            $text .= " (Delivery)";
        }

        return $text;
    }
}
