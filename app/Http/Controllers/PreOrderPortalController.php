<?php
// app/Http/Controllers/PreOrderPortalController.php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsurePreOrderAuthenticated;
use App\Models\CardTransaction;
use App\Models\PreOrder;
use App\Models\PreOrderAccount;
use App\Models\PreOrderItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\ProductCategory;
use App\Services\KcbSalesService;
use App\Services\PreOrderSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreOrderPortalController extends Controller
{
    protected PreOrderSmsService $sms;

    public function __construct(PreOrderSmsService $sms)
    {
        $this->sms = $sms;
    }

    // ============================================================
    // HOME / MENU
    // ============================================================

    /**
     * Home page — shows shops + categories + all products.
     */
    public function index(Request $request)
    {
        $account = $this->account($request);

        // Card balance for HS students
        $cardBalance = null;
        if ($account->isHighSchoolStudent()) {
            $card = $account->cardAccount();
            $cardBalance = $card ? (float) $card->balance : 0;
        }

        $shops = Shop::where('is_active', true)
            ->orderBy('shop_name')
            ->get();

        return view('preorder.home', compact('account', 'shops', 'cardBalance'));
    }

    /**
     * Menu endpoint — returns products for one shop (or all) with
     * optional category filter. Used by AJAX on the home page.
     */
    public function menu(Request $request)
    {
        $account = $this->account($request);

        $shopId = $request->get('shop_id');
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        $query = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with('category');

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('product_name')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_code' => $p->product_code,
                    'product_name' => $p->product_name,
                    'description' => $p->description,
                    'selling_price' => (float) $p->selling_price,
                    'unit' => $p->unit ?? 'piece',
                    'image' => $p->image ? asset('storage/' . $p->image) : null,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category->category_name ?? null,
                    'shop_id' => $p->shop_id,
                    'is_available' => !$p->track_inventory || $p->current_stock > 0,
                    'current_stock' => (float) $p->current_stock,
                ];
            });

        // Categories available in this shop
        $categoriesQuery = ProductCategory::whereHas('products', function ($q) use ($shopId) {
            $q->where('is_active', true)->where('is_featured', true);
            if ($shopId) {
                $q->where('shop_id', $shopId);
            }
        });
        $categories = $categoriesQuery->orderBy('category_name')
            ->get(['id', 'category_name']);

        return response()->json([
            'success' => true,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    // ============================================================
    // CART (session-backed)
    // ============================================================

    /**
     * Add a product to the cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || !$product->is_featured) {
            return response()->json(['error' => 'Product is not available'], 400);
        }

        if ($product->track_inventory && $product->current_stock <= 0) {
            return response()->json(['error' => 'Product is out of stock'], 400);
        }

        $cart = session()->get('preorder_cart', []);

        $pid = $product->id;
        if (isset($cart[$pid])) {
            $cart[$pid]['quantity'] += $request->quantity;
        } else {
            $cart[$pid] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'unit' => $product->unit ?? 'piece',
                'unit_price' => (float) $product->selling_price,
                'quantity' => $request->quantity,
                'image' => $product->image,
                'shop_id' => $product->shop_id,
            ];
        }

        // Cap at 50 per item
        $cart[$pid]['quantity'] = min($cart[$pid]['quantity'], 50);

        session()->put('preorder_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => "{$product->product_name} added to cart",
            'cart_count' => collect($cart)->sum('quantity'),
            'cart' => array_values($cart),
        ]);
    }

    /**
     * Update quantity of one cart item.
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:0|max:50',
        ]);

        $cart = session()->get('preorder_cart', []);
        $pid = $request->product_id;

        if (!isset($cart[$pid])) {
            return response()->json(['error' => 'Item not in cart'], 404);
        }

        if ($request->quantity == 0) {
            unset($cart[$pid]);
        } else {
            $cart[$pid]['quantity'] = $request->quantity;
        }

        session()->put('preorder_cart', $cart);

        return response()->json([
            'success' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'cart' => array_values($cart),
        ]);
    }

    /**
     * Remove a cart item.
     */
    public function removeFromCart(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);

        $cart = session()->get('preorder_cart', []);
        unset($cart[$request->product_id]);
        session()->put('preorder_cart', $cart);

        return response()->json([
            'success' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'cart' => array_values($cart),
        ]);
    }

    /**
     * Get the current cart (with totals).
     */
    public function viewCart(Request $request)
    {
        return response()->json($this->cartPayload($request));
    }

    /**
     * Empty the cart.
     */
    public function clearCart(Request $request)
    {
        session()->forget('preorder_cart');
        return response()->json(['success' => true, 'cart' => [], 'cart_count' => 0]);
    }

    // ============================================================
    // CHECKOUT
    // ============================================================

    /**
     * Place the order.
     *
     * HS students   → deduct from card balance, mark paid immediately
     * Staff/College → M-Pesa STK push, wait for callback
     */
    public function checkout(Request $request)
    {
        $account = $this->account($request);

        $cart = session()->get('preorder_cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Your cart is empty'], 400);
        }

        // Pick the shop from the first item (all items assumed same shop)
        $firstItem = reset($cart);
        $shop = Shop::find($firstItem['shop_id']);
        if (!$shop) {
            return response()->json(['error' => 'Shop not available'], 400);
        }

        // Recalculate the total from fresh product data
        $subtotal = 0;
        $items = [];
        foreach ($cart as $line) {
            $product = Product::find($line['product_id']);
            if (!$product || !$product->is_active) {
                return response()->json(['error' => "Product {$line['product_name']} is no longer available"], 400);
            }

            if ($product->track_inventory && $product->current_stock < $line['quantity']) {
                return response()->json([
                    'error' => "Insufficient stock for {$product->product_name}. Only {$product->current_stock} left."
                ], 400);
            }

            $lineTotal = (float) $product->selling_price * $line['quantity'];
            $subtotal += $lineTotal;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'unit' => $product->unit ?? 'piece',
                'unit_price' => (float) $product->selling_price,
                'quantity' => $line['quantity'],
                'total_price' => $lineTotal,
            ];
        }

        $totalAmount = $subtotal;

        // ── HIGH SCHOOL STUDENT → deduct from card ──
        if ($account->paysViaCard()) {
            return $this->checkoutViaCard($account, $shop, $items, $subtotal, $totalAmount);
        }

        // ── STAFF / COLLEGE → M-Pesa STK push ──
        return $this->checkoutViaMpesa($account, $shop, $items, $subtotal, $totalAmount);
    }

    /**
     * HS student checkout — deduct from cafeteria card.
     */
    protected function checkoutViaCard(PreOrderAccount $account, Shop $shop, array $items, float $subtotal, float $total)
    {
        $card = $account->cardAccount();
        if (!$card) {
            return response()->json(['error' => 'No cafeteria card on file. Contact the cafeteria.'], 400);
        }

        if (!$card->is_active || $card->is_blocked || $card->is_locked) {
            return response()->json(['error' => 'Your card is not usable right now. Contact the cafeteria.'], 400);
        }

        if ((float) $card->balance < $total) {
            return response()->json([
                'error' => 'Insufficient balance. Your card has KES '
                    . number_format($card->balance, 2)
                    . ' but this order needs KES ' . number_format($total, 2),
                'insufficient' => true,
                'card_balance' => (float) $card->balance,
                'required' => $total,
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Create the pre-order
            $order = PreOrder::create([
                'pre_order_account_id' => $account->id,
                'shop_id' => $shop->id,
                'payment_method' => 'card',
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'status' => 'paid',
                'paid_at' => now(),
                'customer_notes' => null,
            ]);

            // Insert items
            foreach ($items as $item) {
                PreOrderItem::create([
                    'pre_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Deduct from card
            $balanceBefore = (float) $card->balance;
            $card->balance = $balanceBefore - $total;
            $card->total_spent = (float) $card->total_spent + $total;
            $card->last_used_at = now();
            $card->save();

            // Card ledger entry
            $cardTxn = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $account->user_id,
                'transaction_type' => 'purchase',
                'amount' => $total,
                'balance_before' => $balanceBefore,
                'balance_after' => (float) $card->balance,
                'reference' => CardTransaction::generateReference('PRE'),
                'description' => 'Pre-order ' . $order->order_number,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'pre_order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'channel' => 'pre_order_portal',
                ]),
            ]);

            $order->update(['card_transaction_id' => $cardTxn->id]);

            DB::commit();

            session()->forget('preorder_cart');

            // Notify cafeteria
            $this->sms->sendNewOrderToCafeteria($order);

            Log::info('HS pre-order placed (card deduction)', [
                'order_number' => $order->order_number,
                'amount' => $total,
                'card_id' => $card->id,
                'new_balance' => (float) $card->balance,
            ]);

            return response()->json([
                'success' => true,
                'payment_method' => 'card',
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total_amount' => $total,
                'new_balance' => (float) $card->balance,
                'message' => 'Order placed! KES ' . number_format($total, 2)
                    . ' deducted from your card. New balance: KES ' . number_format($card->balance, 2),
                'redirect' => route('pre-order.orders.show', $order->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HS pre-order checkout failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to place order. Please try again.'], 500);
        }
    }

    /**
     * Staff / College checkout — M-Pesa STK push.
     */
    protected function checkoutViaMpesa(PreOrderAccount $account, Shop $shop, array $items, float $subtotal, float $total)
    {
        if (!$account->hasPhone()) {
            return response()->json([
                'error' => 'No phone number on file. Please add one before checking out.',
                'needs_phone' => true,
            ], 400);
        }

        $phone = $account->phone;
        $kcb = app(KcbSalesService::class);

        // Validate phone
        $formattedPhone = $kcb->validatePhoneNumber($phone);
        if (!$formattedPhone) {
            return response()->json(['error' => 'Your phone number on file is invalid. Update it and try again.'], 400);
        }

        DB::beginTransaction();

        try {
            // Create a Sale record (reuse existing sale pipeline)
            $invoice = '7722609-SALE-PRE-' . date('Ymd') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'business_section_id' => $shop->business_section_id,
                'shop_id' => $shop->id,
                'sale_type' => 'preorder',
                'channel' => 'preorder_portal',
                'invoice_number' => $invoice,
                'customer_name' => $account->display_name,
                'customer_phone' => $formattedPhone,
                'customer_email' => $account->isStaff() ? $account->identifier : null,
                'customer_type' => $account->user_type,
                'payment_method' => 'mpesa',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'sale_date' => now(),
                'created_by' => 1,
                'recorded_by' => 1,
            ]);

            // Create the pre-order
            $order = PreOrder::create([
                'pre_order_account_id' => $account->id,
                'shop_id' => $shop->id,
                'payment_method' => 'mpesa',
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'status' => 'processing',
                'kcb_invoice_number' => $invoice,
                'customer_notes' => null,
            ]);

            // Insert items into both the sale and pre_order
            foreach ($items as $item) {
                PreOrderItem::create([
                    'pre_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                    'final_price' => $item['total_price'],
                ]);
            }

            // Initiate STK push through the existing service
            $paymentResult = $kcb->initiateSalePayment($sale, $formattedPhone, $total);

            if (isset($paymentResult['error'])) {
                DB::rollBack();
                return response()->json(['error' => $paymentResult['error']], 400);
            }

            // Attach KCB refs to the pre-order
            $order->update([
                'checkout_request_id' => $paymentResult['checkout_request_id'] ?? null,
                'merchant_request_id' => $paymentResult['merchant_request_id'] ?? null,
            ]);

            DB::commit();

            session()->forget('preorder_cart');

            Log::info('Staff/College pre-order created, awaiting M-Pesa', [
                'order_number' => $order->order_number,
                'amount' => $total,
                'checkout_request_id' => $paymentResult['checkout_request_id'],
            ]);

            return response()->json([
                'success' => true,
                'payment_method' => 'mpesa',
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total_amount' => $total,
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'message' => 'Check your phone for the M-Pesa prompt.',
                'redirect' => route('pre-order.orders.show', $order->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff/College pre-order checkout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to place order. Please try again.'], 500);
        }
    }

    // ============================================================
    // STATUS POLLING
    // ============================================================

    /**
     * Called by the frontend every few seconds after a Staff/College
     * checkout to check whether the M-Pesa payment landed.
     */
    public function checkStatus(Request $request, string $checkoutId)
    {
        $order = PreOrder::where('checkout_request_id', $checkoutId)->first();

        if (!$order) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $order->status,
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'redirect' => in_array($order->status, ['paid', 'accepted', 'ready', 'completed'])
                ? route('pre-order.orders.show', $order->id)
                : null,
        ]);
    }

    // ============================================================
    // KCB CALLBACK
    // ============================================================

    /**
     * Receive the KCB STK callback, mark the pre-order paid if successful.
     */
    public function kcbCallback(Request $request)
    {
        Log::info('=== PRE-ORDER KCB CALLBACK ===', $request->all());

        try {
            $body = $request->all();
            $stk = $body['Body']['stkCallback'] ?? null;

            if (!$stk) {
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $checkoutId = $stk['CheckoutRequestID'] ?? null;
            $resultCode = $stk['ResultCode'] ?? null;

            if (!$checkoutId) {
                return response()->json(['error' => 'Missing CheckoutRequestID'], 400);
            }

            $order = PreOrder::where('checkout_request_id', $checkoutId)->first();
            if (!$order) {
                Log::warning('Pre-order callback: order not found', ['checkout_id' => $checkoutId]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($order->isPaid() || $order->isAccepted() || $order->isReady() || $order->isCompleted()) {
                return response()->json(['success' => true, 'message' => 'Already paid']);
            }

            if ($resultCode == 0) {
                // Success — extract M-Pesa receipt
                $items = $stk['CallbackMetadata']['Item'] ?? [];
                $receipt = null;
                foreach ($items as $item) {
                    if (($item['Name'] ?? null) === 'MpesaReceiptNumber') {
                        $receipt = $item['Value'] ?? null;
                    }
                }

                DB::beginTransaction();

                // Lock the card row is unnecessary here (no card involved for M-Pesa)
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'mpesa_receipt' => $receipt,
                ]);

                // Also mark the linked sale paid (so cafeteria reports include it)
                $sale = Sale::where('invoice_number', $order->kcb_invoice_number)->first();
                if ($sale) {
                    $sale->update([
                        'payment_status' => 'paid',
                        'order_status' => 'confirmed',
                        'mpesa_receipt' => $receipt,
                        'payment_confirmed_at' => now(),
                    ]);
                }

                DB::commit();

                // SMS the customer
                $this->sms->sendOrderPlacedNotification($order);

                // Notify cafeteria
                $this->sms->sendNewOrderToCafeteria($order);

                Log::info('Pre-order paid via M-Pesa', [
                    'order_number' => $order->order_number,
                    'receipt' => $receipt,
                ]);

                return response()->json(['success' => true]);

            } else {
                // Failed
                $resultDesc = $stk['ResultDesc'] ?? 'Payment failed';
                $order->update([
                    'status' => 'failed',
                    'cancellation_reason' => $resultDesc,
                ]);

                $this->sms->sendPaymentFailedNotification($order, $resultDesc);

                Log::warning('Pre-order payment failed', [
                    'order_number' => $order->order_number,
                    'result' => $resultDesc,
                ]);

                return response()->json(['success' => false, 'message' => $resultDesc]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pre-order KCB callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    // ============================================================
    // ORDERS
    // ============================================================

    public function myOrders(Request $request)
    {
        $account = $this->account($request);

        $orders = PreOrder::where('pre_order_account_id', $account->id)
            ->with(['items', 'shop'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('preorder.orders', compact('account', 'orders'));
    }

    public function orderDetail(Request $request, $id)
    {
        $account = $this->account($request);

        $order = PreOrder::where('pre_order_account_id', $account->id)
            ->with(['items', 'shop'])
            ->findOrFail($id);

        return view('preorder.order-show', compact('account', 'order'));
    }

    public function cancelOrder(Request $request, $id)
    {
        $account = $this->account($request);

        $order = PreOrder::where('pre_order_account_id', $account->id)->findOrFail($id);

        if (!$order->canBeCancelled()) {
            return response()->json(['error' => 'This order can no longer be cancelled. Contact the cafeteria.'], 400);
        }

        DB::beginTransaction();

        try {
            // If HS student and already paid via card → refund the card
            if ($order->payment_method === 'card' && $order->isPaid()) {
                $card = $account->cardAccount();
                if ($card) {
                    $refundAmount = (float) $order->total_amount;
                    $balanceBefore = (float) $card->balance;
                    $card->balance = $balanceBefore + $refundAmount;
                    $card->total_spent = max(0, (float) $card->total_spent - $refundAmount);
                    $card->save();

                    CardTransaction::create([
                        'card_account_id' => $card->id,
                        'high_school_student_id' => $account->user_id,
                        'transaction_type' => 'refund',
                        'amount' => $refundAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => (float) $card->balance,
                        'reference' => CardTransaction::generateReference('RFND'),
                        'description' => 'Refund for cancelled pre-order ' . $order->order_number,
                        'status' => 'completed',
                        'processed_at' => now(),
                    ]);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Cancelled by customer',
            ]);

            DB::commit();

            $this->sms->sendOrderCancelledNotification($order, 'Cancelled by customer');

            return response()->json(['success' => true, 'message' => 'Order cancelled.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to cancel order.'], 500);
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Get the authenticated pre-order account from the request.
     * (Set by EnsurePreOrderAuthenticated middleware.)
     */
    protected function account(Request $request): PreOrderAccount
    {
        return $request->attributes->get('preorder_account');
    }

    /**
     * Build the cart payload with totals for the frontend.
     */
    protected function cartPayload(Request $request): array
    {
        $cart = session()->get('preorder_cart', []);
        $items = array_values($cart);

        $subtotal = 0;
        foreach ($items as &$line) {
            $line['line_total'] = $line['unit_price'] * $line['quantity'];
            $subtotal += $line['line_total'];
        }
        unset($line);

        $account = $this->account($request);
        $cardBalance = null;
        if ($account->isHighSchoolStudent()) {
            $card = $account->cardAccount();
            $cardBalance = $card ? (float) $card->balance : 0;
        }

        return [
            'items' => $items,
            'count' => collect($items)->sum('quantity'),
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'payment_method' => $account->paysViaCard() ? 'card' : 'mpesa',
            'card_balance' => $cardBalance,
        ];
    }
}