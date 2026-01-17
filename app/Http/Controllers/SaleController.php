<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PaymentTransaction;
use App\Models\BusinessSection;
use App\Models\Shop;
use App\Models\Product;
use App\Services\KcbSalesService;
use App\Services\StockService; // ADD THIS
use Illuminate\Support\Facades\Log;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\InventoryStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Show the POS interface
     */
    public function pos(Request $request)
    {
        Log::info('=== POS PAGE LOADED ===');
        Log::info('User ID: ' . auth()->id());
        Log::info('User Name: ' . auth()->user()->name);
        Log::info('User Shop ID: ' . (auth()->user()->shop_id ?? 'null'));
        Log::info('User Business Section ID: ' . (auth()->user()->business_section_id ?? 'null'));

        // Get categories for filter
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        Log::info('Categories count: ' . $categories->count());

        // Get today's stats
        $todayStats = $this->getTodayStats();

        // Get user's default shop and business section
        $user = auth()->user();
        $defaultShop = $user->shop_id ? Shop::find($user->shop_id) : Shop::where('is_active', true)->first();
        $defaultBusinessSection = $user->business_section_id ? BusinessSection::find($user->business_section_id) : BusinessSection::where('is_active', true)->first();

        Log::info('Default Shop: ' . ($defaultShop ? $defaultShop->shop_name : 'null'));
        Log::info('Default Business Section: ' . ($defaultBusinessSection ? $defaultBusinessSection->name : 'null'));

        Log::info('=== END POS PAGE LOAD ===');

        return view('ktvtc.cafeteria.shop.pos', compact('categories', 'todayStats', 'defaultShop', 'defaultBusinessSection'));
    }

    /**
     * Get products for POS (AJAX)
     */
    public function posProducts(Request $request)
    {
        Log::info('=== POS PRODUCTS API CALLED ===');
        Log::info('Request Parameters:', $request->all());
        Log::info('User ID: ' . auth()->id());
        Log::info('User Shop ID: ' . (auth()->user()->shop_id ?? 'null'));
        Log::info('User Business Section ID: ' . (auth()->user()->business_section_id ?? 'null'));

        try {
            // First, let's debug the basic product data
            $totalProducts = Product::count();
            $activeProducts = Product::where('is_active', true)->count();

            Log::info("Database Stats - Total Products: {$totalProducts}, Active Products: {$activeProducts}");

            // Get first 5 active products for debugging
            $sampleProducts = Product::where('is_active', true)
                ->take(5)
                ->get(['id', 'product_name', 'product_code', 'shop_id', 'category_id', 'is_active', 'track_inventory', 'current_stock']);

            Log::info("Sample Products (first 5 active):");
            foreach ($sampleProducts as $product) {
                Log::info("  - ID: {$product->id}, Name: {$product->product_name}, Shop ID: {$product->shop_id}, Category ID: {$product->category_id}, Active: {$product->is_active}");
            }

            // Get the shop ID we'll use
            $shopId = $request->shop_id ?? auth()->user()->shop_id ?? 1;
            Log::info("Using Shop ID: {$shopId}");

            // Check if shop exists
            $shop = Shop::find($shopId);
            Log::info("Shop found: " . ($shop ? $shop->shop_name : 'No shop found with ID ' . $shopId));

            // Count products for this shop
            $shopProductCount = Product::where('is_active', true)
                ->where('shop_id', $shopId)
                ->count();
            Log::info("Active products in shop {$shopId}: {$shopProductCount}");

            // Build the query
            Log::info("Building query...");
            $query = Product::with(['category'])
                ->where('is_active', true);

            // Filter by shop if we have a shop ID
            if ($shopId) {
                $query->where('shop_id', $shopId);
                Log::info("Added shop filter: shop_id = {$shopId}");
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                Log::info("Adding search filter: {$search}");
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            // Category filter
            if ($request->has('category_id') && $request->category_id) {
                Log::info("Adding category filter: category_id = {$request->category_id}");
                $query->where('category_id', $request->category_id);
            }

            // In stock only - UPDATED: Always check products.current_stock
            if ($request->boolean('in_stock_only')) {
                Log::info("Adding in-stock filter (checking products.current_stock)");
                $query->where(function($q) {
                    $q->where('track_inventory', false)
                      ->orWhere('current_stock', '>', 0);
                });
            }

            // Product type filter
            if ($request->has('type') && $request->type) {
                Log::info("Adding product type filter: type = {$request->type}");
                $query->where('product_type', $request->type);
            }

            // Debug the final query
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info("Final SQL Query: {$sql}");
            Log::info("Query Bindings: " . json_encode($bindings));

            // Get count before pagination
            $totalResults = $query->count();
            Log::info("Total results found: {$totalResults}");

            if ($totalResults > 0) {
                // Get sample of what we found
                $sampleResults = $query->take(3)->get(['id', 'product_name', 'product_code', 'selling_price', 'current_stock']);
                Log::info("Sample results (first 3):");
                foreach ($sampleResults as $product) {
                    Log::info("  - {$product->product_name} (KES {$product->selling_price}), Stock: {$product->current_stock}");
                }
            } else {
                Log::warning("No products found with current filters!");

                // Let's check what's wrong by removing filters one by one
                Log::info("Debugging query by removing filters:");

                // Check without shop filter
                $noShopFilter = Product::where('is_active', true)->count();
                Log::info("  - Without shop filter: {$noShopFilter} products");

                // Check with just active filter
                $justActive = Product::where('is_active', true)->count();
                Log::info("  - Just active filter: {$justActive} products");
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            Log::info("Paginating with {$perPage} items per page");

            $products = $query->paginate($perPage);

            Log::info("Pagination results:");
            Log::info("  - Total items: {$products->total()}");
            Log::info("  - Items on this page: {$products->count()}");
            Log::info("  - Last page: {$products->lastPage()}");

            if ($products->count() > 0) {
                Log::info("First product in results:");
                $firstProduct = $products->first();
                Log::info("  - ID: {$firstProduct->id}");
                Log::info("  - Name: {$firstProduct->product_name}");
                Log::info("  - Code: {$firstProduct->product_code}");
                Log::info("  - Price: {$firstProduct->selling_price}");
                Log::info("  - Stock: {$firstProduct->current_stock}");
                Log::info("  - Shop ID: {$firstProduct->shop_id}");
                Log::info("  - Category: " . ($firstProduct->category ? $firstProduct->category->category_name : 'None'));
            }

            Log::info('=== END POS PRODUCTS API ===');

            return response()->json($products);

        } catch (\Exception $e) {
            Log::error('ERROR in posProducts: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'debug_info' => [
                    'user_id' => auth()->id(),
                    'shop_id' => $request->shop_id ?? auth()->user()->shop_id ?? 'null',
                    'timestamp' => now()->toDateTimeString()
                ]
            ], 500);
        }
    }

    /**
     * Debug endpoint for checking database
     */
    public function debugProducts(Request $request)
    {
        Log::info('=== DEBUG PRODUCTS ENDPOINT ===');

        $results = [
            'database_connection' => 'OK',
            'timestamp' => now()->toDateTimeString(),
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'shop_id' => auth()->user()->shop_id,
                'business_section_id' => auth()->user()->business_section_id
            ]
        ];

        try {
            // Check products table
            $results['products'] = [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'with_shop_null' => Product::whereNull('shop_id')->where('is_active', true)->count(),
                'with_shop_1' => Product::where('shop_id', 1)->where('is_active', true)->count(),
                'with_user_shop' => Product::where('shop_id', auth()->user()->shop_id)->where('is_active', true)->count()
            ];

            // Get sample products
            $results['sample_products'] = Product::where('is_active', true)
                ->take(10)
                ->get(['id', 'product_name', 'product_code', 'shop_id', 'category_id', 'selling_price', 'current_stock', 'is_active'])
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'shop_id' => $product->shop_id,
                        'category_id' => $product->category_id,
                        'price' => $product->selling_price,
                        'stock' => $product->current_stock,
                        'active' => $product->is_active
                    ];
                });

            // Check shops
            $results['shops'] = Shop::where('is_active', true)
                ->get(['id', 'shop_name', 'business_section_id', 'is_active'])
                ->map(function($shop) {
                    return [
                        'id' => $shop->id,
                        'name' => $shop->shop_name,
                        'business_section_id' => $shop->business_section_id,
                        'active' => $shop->is_active
                    ];
                });

            // Check categories
            $results['categories'] = [
                'total' => ProductCategory::count(),
                'active' => ProductCategory::where('is_active', true)->count(),
                'list' => ProductCategory::where('is_active', true)
                    ->take(10)
                    ->get(['id', 'category_name', 'business_section_id'])
            ];

            // Test the actual POS query
            $shopId = auth()->user()->shop_id ?? 1;
            $testQuery = Product::with(['category'])
                ->where('is_active', true)
                ->where('shop_id', $shopId);

            $results['test_query'] = [
                'shop_id_used' => $shopId,
                'sql' => $testQuery->toSql(),
                'bindings' => $testQuery->getBindings(),
                'count' => $testQuery->count(),
                'sample' => $testQuery->take(3)->get(['id', 'product_name', 'shop_id'])->toArray()
            ];

            Log::info('Debug results:', $results);

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $results['database_connection'] = 'FAILED';
            Log::error('Debug endpoint error: ' . $e->getMessage());
        }

        Log::info('=== END DEBUG PRODUCTS ===');

        return response()->json($results);
    }

    /**
     * Simple test endpoint
     */
    public function testProducts(Request $request)
    {
        Log::info('=== TEST PRODUCTS ENDPOINT ===');

        // Simple query without complex filters
        $products = Product::where('is_active', true)
            ->select('id', 'product_name', 'product_code', 'selling_price', 'image', 'current_stock', 'shop_id')
            ->orderBy('product_name')
            ->take(50)
            ->get();

        Log::info("Found {$products->count()} active products");

        $result = [
            'success' => true,
            'message' => 'Test endpoint working',
            'total' => $products->count(),
            'data' => $products,
            'debug' => [
                'shop_id_used' => 'none (all shops)',
                'user_shop_id' => auth()->user()->shop_id ?? 'null',
                'timestamp' => now()->toDateTimeString()
            ]
        ];

        Log::info('=== END TEST PRODUCTS ===');

        return response()->json($result);
    }

    /**
     * Search customers for POS (AJAX)
     */
    public function searchCustomers(Request $request)
    {
        try {
            $phone = $request->input('phone');
            $type = $request->input('type');

            // Clean the phone parameter
            $cleanPhone = $phone;
            if (is_string($phone)) {
                if (strpos($phone, 'HTTP/1.1') !== false) {
                    preg_match('/phone=(\d+)/', $phone, $matches);
                    $cleanPhone = $matches[1] ?? '';
                } else {
                    $cleanPhone = preg_replace('/\D/', '', $phone);
                }
            }

            Log::info('Search customers called', [
                'original_phone' => $phone,
                'clean_phone' => $cleanPhone,
                'type' => $type
            ]);

            $customers = [];

            // Search by phone in users table
            if ($cleanPhone && strlen($cleanPhone) >= 10) {
                $users = User::where('phone_number', 'like', '%' . $cleanPhone . '%')
                    ->limit(10)
                    ->get()
                    ->map(function($user) {
                        return [
                            'name' => $user->name,
                            'phone' => $user->phone_number,
                            'email' => $user->email,
                            'type' => $this->getCustomerTypeFromRole($user->role)
                        ];
                    });

                $customers = array_merge($customers, $users->toArray());
            }

            // If no users found, return walk-in customer
            if (empty($customers) && $cleanPhone && strlen($cleanPhone) >= 10) {
                $customers[] = [
                    'name' => 'Walk-in Customer',
                    'phone' => $cleanPhone,
                    'email' => null,
                    'type' => 'walk_in'
                ];
            }

            Log::info('Found customers: ' . count($customers));

            return response()->json([
                'customers' => $customers,
                'count' => count($customers)
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'customers' => [],
                'count' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method to get customer type from role
     */
    private function getCustomerTypeFromRole($role)
    {
        $map = [
            1 => 'main_school',
            2 => 'admin',
            3 => 'scholarship',
            4 => 'library',
            5 => 'student',
            6 => 'cafeteria',
            7 => 'finance',
            8 => 'trainers',
            9 => 'website',
        ];

        return $map[$role] ?? 'unknown';
    }

    /**
     * Get today's sales statistics (AJAX)
     */
    public function todayStats()
    {
        Log::info('Today stats called');

        $today = now()->format('Y-m-d');
        $shopId = auth()->user()->shop_id ?? 1;

        Log::info("Getting stats for shop {$shopId} on {$today}");

        $totalSales = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $transactionCount = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('payment_status', '!=', 'cancelled')
            ->count();

        $pendingOrders = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('order_status', 'pending')
            ->count();

        $averageSale = $transactionCount > 0 ? $totalSales / $transactionCount : 0;

        $stats = [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'average_sale' => $averageSale,
            'pending_orders' => $pendingOrders
        ];

        Log::info("Today's stats:", $stats);

        return response()->json($stats);
    }

    /**
     * Get recent sales for POS (AJAX)
     */
    public function recentSales()
    {
        Log::info('Recent sales called');

        $sales = Sale::with(['customer', 'items'])
            ->where('shop_id', auth()->user()->shop_id ?? 1)
            ->where('sale_type', 'pos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'total_amount' => $sale->total_amount,
                    'payment_status' => $sale->payment_status,
                    'created_at' => $sale->created_at,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone
                ];
            });

        Log::info("Found {$sales->count()} recent sales");

        return response()->json($sales);
    }

    /**
     * Save sale as draft (AJAX)
     */
    public function saveDraft(Request $request)
    {
        Log::info('Save draft called', $request->all());

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'customer_type' => 'nullable|string',
            'customer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::error('Draft validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate draft ID
        $draftId = 'DRAFT-' . date('Ymd-His') . '-' . strtoupper(uniqid());

        Log::info("Draft saved: {$draftId} with " . count($request->items) . " items");

        return response()->json([
            'message' => 'Draft saved successfully',
            'draft_id' => $draftId,
            'items_count' => count($request->items)
        ]);
    }

    /**
     * Process quick sale (for predefined products) (AJAX)
     */
    public function quickSale(Request $request)
    {
        Log::info('Quick sale called', $request->all());

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'payment_method' => 'required|in:mpesa,cash'
        ]);

        if ($validator->fails()) {
            Log::error('Quick sale validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $product = Product::find($request->product_id);
            Log::info("Quick sale for product: {$product->product_name} (ID: {$product->id})");

            // Check stock - UPDATED: Check product.current_stock directly
            if ($product->track_inventory) {
                if ($product->current_stock < $request->quantity) {
                    $message = "Insufficient stock for product: {$product->product_name}. Available: {$product->current_stock}";
                    Log::error($message);
                    throw new \Exception($message);
                }
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumberWithShop(auth()->user()->shop_id ?? 1);
            Log::info("Generated invoice: {$invoiceNumber}");

            // Create sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'business_section_id' => auth()->user()->business_section_id ?? 1,
                'shop_id' => auth()->user()->shop_id ?? 1,
                'sale_type' => 'pos',
                'channel' => 'cafeteria',
                'sale_date' => now(),
                'cashier_id' => auth()->id(),
                'created_by' => auth()->id(),
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'total_items' => 1,
                'subtotal' => $product->selling_price * $request->quantity,
                'total_amount' => $product->selling_price * $request->quantity,
                'tax_amount' => ($product->selling_price * $request->quantity) * 0.16 // 16% VAT
            ]);

            Log::info("Sale created with ID: {$sale->id}");

            // Create sale item
            $sale->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'description' => $product->description,
                'unit' => $product->unit,
                'unit_price' => $product->selling_price,
                'quantity' => $request->quantity,
                'total_price' => $product->selling_price * $request->quantity,
                'final_price' => $product->selling_price * $request->quantity
            ]);

            Log::info("Sale item created");

            // Create payment transaction
            if ($request->payment_method === 'mpesa') {
                $transactionNumber = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());
                Log::info("Creating M-Pesa transaction: {$transactionNumber}");

                $sale->paymentTransactions()->create([
                    'transaction_number' => $transactionNumber,
                    'payment_method' => 'mpesa',
                    'amount' => $product->selling_price * $request->quantity,
                    'currency' => 'KES',
                    'mpesa_receipt' => $request->mpesa_receipt,
                    'phone_number' => $request->phone_number,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'recorded_by' => auth()->id()
                ]);
            }

            // Update inventory - UPDATED: Use StockService
            if ($product->track_inventory) {
                Log::info("Updating inventory for product {$product->id}");

                $stockService = app(StockService::class);
                $result = $stockService->updateStock(
                    $product->id,
                    auth()->user()->shop_id ?? 1,
                    $request->quantity,
                    'sale',
                    'Quick Sale: ' . $invoiceNumber,
                    'POS Quick Sale'
                );

                if (!$result['success']) {
                    throw new \Exception("Failed to update stock: " . $result['message']);
                }
            }

            DB::commit();
            Log::info("Quick sale completed successfully");

            return response()->json([
                'message' => 'Quick sale completed successfully',
                'sale' => $sale->load(['items', 'paymentTransactions']),
                'receipt' => [
                    'invoice_number' => $sale->invoice_number,
                    'total' => $sale->total_amount,
                    'payment_method' => $sale->payment_method
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick sale failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process quick sale: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Initiate M-Pesa payment (AJAX)
     */
    public function initiateMpesa(Request $request)
    {
        try {
            Log::info('=== INITIATE M-PESA PAYMENT ===', $request->all());

            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'phone' => 'required|string|min:10|max:12',
                'items' => 'required|array|min:1',
                'customer_name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                Log::error('M-Pesa validation failed', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get user and shop info
            $user = auth()->user();
            $shopId = $user->shop_id ?? 1; // Default to shop 1 if not set
            $businessSectionId = $user->business_section_id ?? 1; // Default to 1 if not set
            $shop = Shop::find($shopId);

            Log::info('User and shop info', [
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'business_section_id' => $businessSectionId,
                'shop_name' => $shop ? $shop->shop_name : 'Not found'
            ]);

            // Clean phone number
            $cleanPhone = preg_replace('/\D/', '', $request->phone);

            // Generate invoice number using the updated method with shopId parameter
            $invoiceNumber = $this->generateInvoiceNumberWithShop($shopId);
            Log::info('Generated invoice number', ['invoice_number' => $invoiceNumber]);

            // Calculate totals
            $totalItems = 0;
            $subtotal = 0;
            $itemsData = [];

            // Initialize StockService
            $stockService = app(StockService::class);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: " . $item['product_id']);
                }

                // Check stock if product tracks inventory - UPDATED: Use StockService
                if ($product->track_inventory) {
                    $stockCheck = $stockService->checkStock(
                        $product->id,
                        $shopId,
                        $item['quantity']
                    );

                    if (!$stockCheck['available']) {
                        throw new \Exception("Insufficient stock for product: {$product->product_name}. Available: {$stockCheck['stock']}");
                    }
                }

                $itemTotal = $product->selling_price * $item['quantity'];
                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'description' => $product->description,
                    'unit' => $product->unit,
                    'unit_price' => $product->selling_price,
                    'quantity' => $item['quantity'],
                    'total_price' => $itemTotal,
                    'final_price' => $itemTotal,
                    'is_production_item' => false, // Assuming gift shop items are not production items
                ];

                $totalItems++;
                $subtotal += $itemTotal;
            }

           // $taxAmount = $subtotal * 0.16; // 16% VAT
               $taxAmount = 0; // ADD THIS LINE - NO TAX
        $totalAmount = $subtotal + $taxAmount; // This is just subtotal + 0

        Log::info('Calculated totals', [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount, // Will be 0
            'total_amount' => $totalAmount, // Same as subtotal
            'total_items' => $totalItems
            ]);

            // Validate amount matches calculated total (allow small difference)
            if (abs($request->amount - $totalAmount) > 1) { // Changed to 1 KSH tolerance
                Log::warning('Amount mismatch', [
                    'calculated' => $totalAmount,
                    'sent' => $request->amount,
                    'difference' => abs($request->amount - $totalAmount)
                ]);
                // Continue anyway, as amount might be rounded
                $totalAmount = $request->amount; // Use the sent amount
            }

            // Create sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'business_section_id' => $businessSectionId,
                'shop_id' => $shopId,
                'sale_type' => 'pos',
                'channel' => 'gift_shop',
                'customer_name' => $request->customer_name ?? 'Walk-in Customer',
                'customer_phone' => $cleanPhone,
                'customer_type' => 'visitor',
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => 'mpesa',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'cashier_id' => $user->id,
                'created_by' => $user->id,
                'sale_date' => now(),
            ]);

            Log::info('Sale created', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number
            ]);

            // Create sale items and update inventory
            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);

                // Update inventory immediately for gift shop items - UPDATED: Use StockService
                if (!$itemData['is_production_item']) {
                    $product = Product::find($itemData['product_id']);
                    if ($product && $product->track_inventory) {
                        $result = $stockService->updateStock(
                            $product->id,
                            $shopId,
                            $itemData['quantity'],
                            'sale',
                            'Sale: ' . $sale->invoice_number,
                            'POS Sale'
                        );

                        if (!$result['success']) {
                            throw new \Exception("Failed to update stock for {$product->product_name}: " . $result['message']);
                        }
                    }
                }
            }

            Log::info('Sale items created and inventory updated', ['items_count' => count($itemsData)]);

            // Initialize KCB Sales Service
            $kcbSalesService = app(KcbSalesService::class);

            // Initiate KCB payment
            $paymentResult = $kcbSalesService->initiateSalePayment($sale, $cleanPhone, $totalAmount);

            if (isset($paymentResult['error'])) {
                throw new \Exception($paymentResult['error']);
            }

            DB::commit();

            Log::info('M-Pesa payment initiated successfully', [
                'sale_id' => $sale->id,
                'checkout_request_id' => $paymentResult['checkout_request_id'] ?? null,
                'merchant_request_id' => $paymentResult['merchant_request_id'] ?? null,
                'kcb_invoice' => $paymentResult['kcb_invoice_number'] ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment request sent successfully',
                'data' => [
                    'sale_id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'checkout_request_id' => $paymentResult['checkout_request_id'] ?? null,
                    'merchant_request_id' => $paymentResult['merchant_request_id'] ?? null,
                    'kcb_invoice_number' => $paymentResult['kcb_invoice_number'] ?? null,
                    'message' => 'Please check your phone to complete payment'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa initiation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate M-Pesa payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check M-Pesa payment status (AJAX) - UPDATED to use KCB service
     */
    public function checkMpesaStatus(Request $request)
    {
        Log::info('Check M-Pesa status called', $request->all());

        $validator = Validator::make($request->all(), [
            'checkout_request_id' => 'required|string',
            'sale_id' => 'nullable|exists:sales,id'
        ]);

        if ($validator->fails()) {
            Log::error('M-Pesa status check validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Use KCB service to check real status
            $kcbSalesService = app(KcbSalesService::class);
            $statusResult = $kcbSalesService->checkSalePaymentStatus($request->checkout_request_id);

            if (isset($statusResult['success']) && $statusResult['success']) {
                // If we have a sale_id, also get sale details
                $saleData = [];
                if ($request->sale_id) {
                    $sale = Sale::find($request->sale_id);
                    if ($sale) {
                        $saleData = [
                            'invoice_number' => $sale->invoice_number,
                            'total_amount' => $sale->total_amount,
                            'payment_status' => $sale->payment_status,
                            'mpesa_receipt' => $sale->mpesa_receipt
                        ];
                    }
                }

                return response()->json([
                    'status' => $statusResult['status'],
                    'result_code' => $statusResult['result_code'],
                    'result_description' => $statusResult['result_description'],
                    'mpesa_receipt_number' => $statusResult['mpesa_receipt_number'],
                    'kcb_invoice_number' => $statusResult['kcb_invoice_number'],
                    'sale_data' => $saleData,
                    'message' => $statusResult['status'] === 'completed'
                        ? 'Payment confirmed successfully!'
                        : 'Waiting for payment confirmation...'
                ]);
            } else {
                // If KCB check fails, return pending status
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Checking payment status...'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Check M-Pesa status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'pending',
                'message' => 'Unable to check status. Please wait...'
            ]);
        }
    }

    /**
     * Main sale creation endpoint (AJAX)
     */
    public function store(Request $request)
    {
        Log::info('=== CREATE SALE ===');
        Log::info('Request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'business_section_id' => 'required|exists:business_sections,id',
            'shop_id' => 'required|exists:shops,id',
            'sale_type' => 'required|in:pos,online,mobile,preorder,delivery',
            'channel' => 'required|in:cafeteria,gift_shop,student_store,website,mobile_app',
            'customer_id' => 'nullable|exists:users,id',
            'customer_type' => 'nullable|in:student,staff,visitor,online_customer',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.is_production_item' => 'boolean',
            'items.*.customizations' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:mpesa,cash,card,bank_transfer,credit,wallet,multiple',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'order_status' => 'nullable|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,picked_up,cancelled,on_hold',
            'delivery_address' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'delivery_time' => 'nullable|date',
            'customer_notes' => 'nullable|string',
            'internal_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::error('Sale validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumberWithShop($request->shop_id);
            Log::info("Generated invoice number: {$invoiceNumber}");

            $data = $request->except('items');
            $data['invoice_number'] = $invoiceNumber;
            $data['sale_date'] = now();
            $data['created_by'] = auth()->id();

            // For POS sales, set cashier to current user
            if ($request->sale_type == 'pos') {
                $data['cashier_id'] = auth()->id();
            }

            // Set default statuses
            $data['payment_status'] = $request->payment_method == 'credit' ? 'pending' : 'paid';
            $data['order_status'] = $request->order_status ?? 'pending';

            if ($request->sale_type == 'pos') {
                $data['order_status'] = 'completed';
            }

            Log::info("Creating sale with data:", $data);

            // Calculate totals
            $totalItems = 0;
            $subtotal = 0;
             $discountAmount = $request->discount_amount ?? 0;
    $taxAmount = $request->tax_amount ?? 0; // This will be 0 if not provided
    $deliveryFee = $request->delivery_fee ?? 0;
    $serviceCharge = $request->service_charge ?? 0;

            Log::info("Processing " . count($request->items) . " items");

            // Initialize StockService
            $stockService = app(StockService::class);

            // Validate stock availability for non-production items - UPDATED: Use StockService
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                Log::info("Checking stock for product: {$product->product_name}");

                if (!$item['is_production_item'] && $product->track_inventory) {
                    $stockCheck = $stockService->checkStock(
                        $product->id,
                        $request->shop_id,
                        $item['quantity']
                    );

                    if (!$stockCheck['available']) {
                        $message = "Insufficient stock for product: {$product->product_name}. Available: {$stockCheck['stock']}";
                        Log::error($message);
                        throw new \Exception($message);
                    }
                }
            }

            $sale = Sale::create($data);
            Log::info("Sale created with ID: {$sale->id}");

            // Process items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $itemTotalPrice = $itemData['quantity'] * $itemData['unit_price'];
                $itemDiscount = $itemData['discount_amount'] ?? ($itemTotalPrice * ($itemData['discount_percentage'] ?? 0) / 100);
                $itemFinalPrice = $itemTotalPrice - $itemDiscount;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'description' => $product->description,
                    'unit' => $product->unit,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'total_price' => $itemTotalPrice,
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'discount_amount' => $itemDiscount,
                    'final_price' => $itemFinalPrice,
                    'is_production_item' => $itemData['is_production_item'] ?? false,
                    'customizations' => $itemData['customizations'] ?? null,
                    'notes' => $itemData['notes'] ?? null
                ]);

                $totalItems++;
                $subtotal += $itemFinalPrice;

                Log::info("Added item: {$product->product_name} x{$itemData['quantity']} = KES {$itemFinalPrice}");

                // Update inventory for non-production items - UPDATED: Use StockService
                if (!$itemData['is_production_item'] && $product->track_inventory) {
                    Log::info("Updating inventory for {$product->product_name}");

                    $result = $stockService->updateStock(
                        $product->id,
                        $request->shop_id,
                        $itemData['quantity'],
                        'sale',
                        'Sale: ' . $invoiceNumber,
                        'Regular Sale'
                    );

                    if (!$result['success']) {
                        throw new \Exception("Failed to update stock for {$product->product_name}: " . $result['message']);
                    }
                }
            }

            // Calculate final totals
            $totalAmount = $subtotal + $taxAmount + $deliveryFee + $serviceCharge - $discountAmount;

            Log::info("Final totals - Subtotal: {$subtotal}, Tax: {$taxAmount}, Discount: {$discountAmount}, Total: {$totalAmount}");

            $sale->update([
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount
            ]);

            // Create payment transaction if paid
            if ($request->payment_method && $request->payment_method != 'credit') {
                $transactionNumber = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());
                Log::info("Creating payment transaction: {$transactionNumber}");

                $paymentData = [
                    'transaction_number' => $transactionNumber,
                    'payment_method' => $request->payment_method,
                    'amount' => $totalAmount,
                    'currency' => 'KES',
                    'status' => 'completed',
                    'completed_at' => now(),
                    'recorded_by' => auth()->id()
                ];

                // Add M-Pesa specific data
                if ($request->payment_method === 'mpesa') {
                    $paymentData['mpesa_receipt'] = $request->mpesa_receipt;
                    $paymentData['phone_number'] = $request->customer_phone;
                    $paymentData['transaction_id'] = $request->transaction_id;
                }

                $sale->paymentTransactions()->create($paymentData);
            }

            // Create initial status history
            $sale->statusHistory()->create([
                'status' => $sale->order_status,
                'changed_by' => auth()->id()
            ]);

            DB::commit();
            Log::info("Sale completed successfully: {$invoiceNumber}");

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale' => $sale->load([
                    'businessSection',
                    'shop',
                    'items',
                    'items.product',
                    'paymentTransactions'
                ]),
                'receipt' => [
                    'invoice_number' => $sale->invoice_number,
                    'total' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to create sale: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print receipt (AJAX)
     */
    public function printReceipt($id)
    {
        Log::info("Print receipt called for sale ID: {$id}");

        $sale = Sale::with(['items', 'businessSection', 'shop'])
            ->findOrFail($id);

        Log::info("Printing receipt for invoice: {$sale->invoice_number}");

        return response()->json([
            'sale' => $sale,
            'print_data' => [
                'invoice_number' => $sale->invoice_number,
                'date' => $sale->sale_date,
                'customer' => $sale->customer_name,
                'phone' => $sale->customer_phone,
                'items' => $sale->items->map(function($item) {
                    return [
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price,
                        'total' => $item->final_price
                    ];
                }),
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax_amount,
                'total' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'cashier' => $sale->cashier->name ?? auth()->user()->name
            ]
        ]);
    }

    /**
     * Email receipt to customer (AJAX)
     */
    public function emailReceipt($id)
    {
        Log::info("Email receipt called for sale ID: {$id}");

        $sale = Sale::with(['items'])->findOrFail($id);

        if (!$sale->customer_email) {
            Log::warning("No customer email for sale {$id}");
            return response()->json(['success' => false, 'error' => 'Customer email not found'], 400);
        }

        Log::info("Sending receipt to: {$sale->customer_email}");

        // In a real implementation, you would send an email here
        // For now, we'll just return success

        return response()->json([
            'success' => true,
            'message' => 'Receipt sent to customer email',
            'email' => $sale->customer_email
        ]);
    }

    /**
     * Get today's stats for the view
     */
    private function getTodayStats()
    {
        $today = now()->format('Y-m-d');
        $shopId = auth()->user()->shop_id ?? 1;

        Log::info("Getting today's stats for shop {$shopId}");

        $totalSales = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $transactionCount = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('payment_status', '!=', 'cancelled')
            ->count();

        $pendingOrders = Sale::whereDate('sale_date', $today)
            ->where('shop_id', $shopId)
            ->where('order_status', 'pending')
            ->count();

        $averageSale = $transactionCount > 0 ? $totalSales / $transactionCount : 0;

        return [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'average_sale' => $averageSale,
            'pending_orders' => $pendingOrders
        ];
    }

    /**
     * Generate invoice number with shop prefix
     */
    private function generateInvoiceNumberWithShop($shopId)
    {
        $shop = Shop::find($shopId);
        $shopCode = $shop ? $shop->shop_code : 'SHOP';
        $date = date('Ymd');

        // Get today's sequence for this shop
        $todayCount = Sale::whereDate('created_at', today())
            ->where('shop_id', $shopId)
            ->count();

        $sequence = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        // Format: SHOPCODE-YYYYMMDD-0001
        return $shopCode . '-' . $date . '-' . $sequence;
    }

    /**
     * Generate invoice number (old method - keep for backward compatibility)
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = Sale::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        $invoiceNumber = $prefix . $year . $month . '-' . $newNumber;
        Log::info("Generated invoice number: {$invoiceNumber}");

        return $invoiceNumber;
    }

    /**
     * Update inventory after sale - DEPRECATED: Now using StockService
     * Keep for backward compatibility but mark as deprecated
     */
    private function updateInventoryFromSale($product, $shopId, $quantity, $sale)
    {
        Log::info("DEPRECATED: updateInventoryFromSale called. Use StockService instead.");

        // For backward compatibility, use StockService
        $stockService = app(StockService::class);

        $result = $stockService->updateStock(
            $product->id,
            $shopId,
            $quantity,
            'sale',
            'Sale: ' . $sale->invoice_number,
            'Legacy Sale Update'
        );

        if (!$result['success']) {
            throw new \Exception("Failed to update inventory: " . $result['message']);
        }
    }

    /**
     * API endpoints for the POS JavaScript
     */
    public function apiPosProducts(Request $request)
    {
        return $this->posProducts($request);
    }

    public function apiDebugProducts(Request $request)
    {
        return $this->debugProducts($request);
    }

    public function apiTestProducts(Request $request)
    {
        return $this->testProducts($request);
    }

    public function apiSearchCustomers(Request $request)
    {
        return $this->searchCustomers($request);
    }

    public function apiTodayStats()
    {
        return $this->todayStats();
    }

    public function apiRecentSales()
    {
        return $this->recentSales();
    }

    public function apiSaveDraft(Request $request)
    {
        return $this->saveDraft($request);
    }
}
