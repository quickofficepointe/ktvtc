<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Shop;
use App\Models\PaymentTransaction;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ReportController extends Controller
{
    /**
     * Reports Dashboard - Main landing page for all reports
     */
    public function dashboard(Request $request)
    {
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        // Current month data
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Monthly revenue
        $monthlyRevenue = Sale::whereBetween('sale_date', [$currentMonthStart, $currentMonthEnd])
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        $lastMonthRevenue = Sale::whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd])
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        $revenueTrend = $lastMonthRevenue > 0 ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        // Monthly transactions
        $monthlyTransactions = Sale::whereBetween('sale_date', [$currentMonthStart, $currentMonthEnd])
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->count();

        $avgDailySales = $monthlyTransactions / now()->daysInMonth;

        // Monthly items sold
        $monthlyItemsSold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$currentMonthStart, $currentMonthEnd])
            ->where('sales.payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId);
            })
            ->sum('sale_items.quantity');

        // Profit margin
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$currentMonthStart, $currentMonthEnd])
            ->where('sales.payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId);
            })
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        $profitMargin = $monthlyRevenue > 0 ? (($monthlyRevenue - $cogs) / $monthlyRevenue) * 100 : 0;

        // Today's data
        $todaySales = Sale::whereDate('sale_date', today())
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        $todayTransactions = Sale::whereDate('sale_date', today())
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->count();

        // Month to date sales
        $monthToDateSales = Sale::whereBetween('sale_date', [now()->startOfMonth(), now()])
            ->where('payment_status', 'paid')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        // Monthly purchases
        $monthlyPurchases = PurchaseOrder::whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        $purchaseOrdersCount = PurchaseOrder::whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->count();

        // Top supplier - FIXED to use 'supplier_name'
        $topSupplier = Supplier::withCount(['purchaseOrders' => function($q) use ($currentMonthStart, $currentMonthEnd, $shopId) {
            $q->whereBetween('order_date', [$currentMonthStart, $currentMonthEnd]);
            if ($shopId) $q->where('shop_id', $shopId);
        }])
        ->orderBy('purchase_orders_count', 'desc')
        ->first();

        $topSupplierName = $topSupplier ? $topSupplier->supplier_name : 'N/A';

        $activeSuppliers = Supplier::whereHas('purchaseOrders', function($q) use ($currentMonthStart, $currentMonthEnd) {
            $q->whereBetween('order_date', [$currentMonthStart, $currentMonthEnd]);
        })->count();

        // Stock metrics
        $lowStockItems = InventoryStock::whereHas('product', function($q) {
            $q->whereColumn('inventory_stocks.current_stock', '<=', 'products.reorder_level')
              ->where('inventory_stocks.current_stock', '>', 0);
        })->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })->count();

        $outOfStockItems = InventoryStock::where('current_stock', '<=', 0)
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->count();

        $totalStockValue = InventoryStock::select(DB::raw('SUM(current_stock * average_unit_cost) as total'))
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->value('total') ?? 0;

        // Revenue breakdown by payment method
        $mpesaRevenue = PaymentTransaction::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->where('payment_method', 'mpesa')
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->whereHas('sale', function($sq) use ($shopId) {
                    $sq->where('shop_id', $shopId);
                });
            })
            ->sum('amount');

        $cashRevenue = PaymentTransaction::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->where('payment_method', 'cash')
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->whereHas('sale', function($sq) use ($shopId) {
                    $sq->where('shop_id', $shopId);
                });
            })
            ->sum('amount');

        // Net profit (simplified)
        $netProfit = $monthlyRevenue - $cogs - $monthlyPurchases;
        $netMargin = $monthlyRevenue > 0 ? ($netProfit / $monthlyRevenue) * 100 : 0;

        $totalExpenses = $monthlyPurchases;
        $expenseRatio = $monthlyRevenue > 0 ? ($totalExpenses / $monthlyRevenue) * 100 : 0;

        // Shops for filter
        $shops = Shop::where('is_active', true)->get();

        return view('ktvtc.cafeteria.reports.dashboard', compact(
            'monthlyRevenue',
            'revenueTrend',
            'monthlyTransactions',
            'avgDailySales',
            'monthlyItemsSold',
            'profitMargin',
            'todaySales',
            'todayTransactions',
            'monthToDateSales',
            'monthlyPurchases',
            'purchaseOrdersCount',
            'topSupplierName',
            'activeSuppliers',
            'lowStockItems',
            'outOfStockItems',
            'totalStockValue',
            'mpesaRevenue',
            'cashRevenue',
            'netProfit',
            'netMargin',
            'totalExpenses',
            'expenseRatio',
            'shops',
            'shopId'
        ));
    }

    /**
     * Daily Sales Report
     */
    public function dailySalesReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        // Get shops for filter dropdown
        $shops = Shop::where('is_active', true)->get();

        // Get sales for the selected date
        $query = Sale::with(['items', 'cashier'])
            ->whereDate('sale_date', $date)
            ->where('payment_status', '!=', 'cancelled');

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalTransactions = $sales->count();
        $totalItems = $sales->sum('total_items');

        // Calculate sales change from previous day
        $previousDate = Carbon::parse($date)->subDay();
        $previousSales = Sale::whereDate('sale_date', $previousDate)
            ->where('payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        $salesChange = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;

        // Payment method breakdown
        $paymentBreakdown = $sales->groupBy('payment_method')->map(function($group) {
            return [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ];
        });

        return view('ktvtc.cafeteria.reports.sales.daily', compact(
            'date',
            'sales',
            'totalSales',
            'totalTransactions',
            'totalItems',
            'salesChange',
            'paymentBreakdown',
            'shops',
            'shopId'
        ));
    }

    /**
     * Monthly Sales Report
     */
   /**
 * Monthly Sales Report
 */
/**
 * Monthly Sales Report
 */
public function monthlySalesReport(Request $request)
{
    $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->get('end_date', now()->format('Y-m-d'));
    $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

    $shops = Shop::where('is_active', true)->get();

    // Get sales with pagination
    $sales = Sale::with(['items', 'cashier'])
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->orderBy('sale_date', 'desc')
        ->paginate(50);

    // Calculate totals
    $totalSales = Sale::whereBetween('sale_date', [$startDate, $endDate])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->sum('total_amount');

    $totalTransactions = Sale::whereBetween('sale_date', [$startDate, $endDate])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->count();

    // Calculate daily sales data for chart
    $dailySales = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as transactions'),
            DB::raw('SUM(total_items) as items_sold'),
            DB::raw('SUM(CASE WHEN payment_method = "mpesa" THEN total_amount ELSE 0 END) as mpesa'),
            DB::raw('SUM(CASE WHEN payment_method = "cash" THEN total_amount ELSE 0 END) as cash')
        )
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    // Calculate average daily sales
    $daysCount = $dailySales->count();
    $averageDailySales = $daysCount > 0 ? $totalSales / $daysCount : 0;

    // Find best day
    $bestDayRecord = $dailySales->sortByDesc('total')->first();
    $bestDay = $bestDayRecord ? Carbon::parse($bestDayRecord->date)->format('M d, Y') : 'N/A';
    $bestDayAmount = $bestDayRecord ? $bestDayRecord->total : 0;

    // Calculate growth rate compared to previous month
    $previousMonthStart = Carbon::parse($startDate)->subMonth()->startOfMonth();
    $previousMonthEnd = Carbon::parse($startDate)->subMonth()->endOfMonth();

    $previousMonthSales = Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->sum('total_amount');

    $growthRate = $previousMonthSales > 0 ? (($totalSales - $previousMonthSales) / $previousMonthSales) * 100 : 0;

    // Payment method breakdown for chart
    $paymentMethodData = [
        'mpesa' => $dailySales->sum('mpesa'),
        'cash' => $dailySales->sum('cash'),
    ];

    // Top selling products
    $topProducts = DB::table('sale_items')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->where('sales.payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('sales.shop_id', $shopId);
        })
        ->select(
            'products.id',
            'products.product_name',
            'products.product_code',
            DB::raw('SUM(sale_items.quantity) as quantity'),
            DB::raw('SUM(sale_items.total_price) as revenue')
        )
        ->groupBy('products.id', 'products.product_name', 'products.product_code')
        ->orderBy('revenue', 'desc')
        ->limit(10)
        ->get();

    // Hourly sales distribution
    $hourlySales = Sale::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('SUM(total_amount) as amount'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->where('payment_status', '!=', 'cancelled')
        ->when($shopId, function($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })
        ->groupBy('hour')
        ->orderBy('hour')
        ->get()
        ->map(function($item) {
            return [
                'hour' => sprintf('%02d:00', $item->hour),
                'amount' => $item->amount,
                'count' => $item->count
            ];
        });

    return view('ktvtc.cafeteria.reports.sales.monthly', compact(
        'startDate',
        'endDate',
        'shopId',
        'shops',
        'sales',
        'totalSales',
        'totalTransactions',
        'dailySales',
        'averageDailySales',
        'daysCount',
        'bestDay',
        'bestDayAmount',
        'growthRate',
        'paymentMethodData',
        'topProducts',
        'hourlySales'
    ));
}

    /**
     * Custom Sales Report
     */
    public function customSalesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $categoryId = $request->get('category_id');
        $paymentMethod = $request->get('payment_method');

        $shops = Shop::where('is_active', true)->get();
        $categories = ProductCategory::where('is_active', true)->get();

        $sales = Sale::with(['items.product.category', 'cashier'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($paymentMethod, function($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            })
            ->when($categoryId, function($q) use ($categoryId) {
                $q->whereHas('items.product', function($sq) use ($categoryId) {
                    $sq->where('category_id', $categoryId);
                });
            })
            ->orderBy('sale_date', 'desc')
            ->paginate(50);

        return view('ktvtc.cafeteria.reports.sales.custom', compact(
            'startDate', 'endDate', 'shopId', 'categoryId', 'paymentMethod',
            'shops', 'categories', 'sales'
        ));
    }

    /**
     * Purchase Summary Report
     */
    public function purchaseSummaryReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $supplierId = $request->get('supplier_id');

        // Get shops and suppliers for filter dropdowns
        $shops = Shop::where('is_active', true)->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        // Build purchase order query
        $query = PurchaseOrder::with(['supplier', 'items'])
            ->whereBetween('order_date', [$startDate, $endDate]);

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->orderBy('order_date', 'desc')->get();

        // Calculate totals
        $totalPurchases = $purchases->sum('total_amount');
        $totalItems = $purchases->sum('total_quantity');
        $completedPurchases = $purchases->where('status', 'completed')->count();
        $pendingPurchases = $purchases->where('status', 'pending')->count();

        // Supplier breakdown - FIXED to use 'supplier_name'
        $supplierBreakdown = $purchases->groupBy('supplier_id')->map(function($group) {
            return [
                'name' => $group->first()->supplier->supplier_name ?? 'Unknown',
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ];
        })->sortByDesc('amount')->values();

        // Monthly trend for chart
        $monthlyTrend = PurchaseOrder::select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('order_date', [
                Carbon::parse($startDate)->startOfMonth(),
                Carbon::parse($endDate)->endOfMonth()
            ])
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($supplierId, function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('ktvtc.cafeteria.reports.purchase.summary', compact(
            'startDate',
            'endDate',
            'shopId',
            'supplierId',
            'shops',
            'suppliers',
            'purchases',
            'totalPurchases',
            'totalItems',
            'completedPurchases',
            'pendingPurchases',
            'supplierBreakdown',
            'monthlyTrend'
        ));
    }

    /**
     * Supplier Purchase Report
     */
    public function supplierPurchaseReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $supplierId = $request->get('supplier_id');

        $shops = Shop::where('is_active', true)->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        $purchases = PurchaseOrder::with(['supplier', 'items'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($supplierId, function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->orderBy('order_date', 'desc')
            ->paginate(50);

        $supplierTotals = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->select('supplier_id', DB::raw('SUM(total_amount) as total_amount'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->get();

        return view('ktvtc.cafeteria.reports.purchase.supplier', compact(
            'startDate', 'endDate', 'shopId', 'supplierId', 'shops', 'suppliers', 'purchases', 'supplierTotals'
        ));
    }

    /**
     * Inventory Stock Levels Report
     */
    public function inventoryStockLevelsReport(Request $request)
    {
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $stockStatus = $request->get('stock_status');

        // Get shops for filter dropdown
        $shops = Shop::where('is_active', true)->get();

        // Build inventory query
        $query = InventoryStock::with(['product', 'shop'])
            ->whereHas('product', function($q) {
                $q->where('track_inventory', true);
            });

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        // Apply stock status filter
        if ($stockStatus) {
            if ($stockStatus === 'low') {
                $query->whereHas('product', function($q) {
                    $q->whereColumn('inventory_stocks.current_stock', '<=', 'products.reorder_level')
                      ->where('inventory_stocks.current_stock', '>', 0);
                });
            } elseif ($stockStatus === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($stockStatus === 'normal') {
                $query->whereHas('product', function($q) {
                    $q->whereColumn('inventory_stocks.current_stock', '>', 'products.reorder_level');
                });
            }
        }

        $stocks = $query->paginate(50);

        // Calculate summary stats
        $totalValue = 0;
        $lowStock = collect([]);
        $outOfStock = collect([]);

        foreach ($stocks as $stock) {
            if ($stock->product) {
                $totalValue += $stock->current_stock * $stock->product->cost_price;

                if ($stock->current_stock <= 0) {
                    $outOfStock->push($stock);
                } elseif ($stock->current_stock <= ($stock->product->reorder_level ?? 0)) {
                    $lowStock->push($stock);
                }
            }
        }

        return view('ktvtc.cafeteria.reports.inventory.stock-levels', compact(
            'shopId',
            'stockStatus',
            'shops',
            'stocks',
            'totalValue',
            'lowStock',
            'outOfStock'
        ));
    }

    /**
     * Inventory Movement Report
     */
    public function inventoryMovementReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $movementType = $request->get('movement_type');

        $shops = Shop::where('is_active', true)->get();

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

        $movements = InventoryMovement::with(['product', 'shop', 'recorder'])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when($movementType, function($q) use ($movementType) {
                $q->where('movement_type', $movementType);
            })
            ->orderBy('movement_date', 'desc')
            ->paginate(50);

        $totalIn = $movements->whereIn('movement_type', ['purchase', 'transfer_in', 'adjustment_in', 'production_in', 'return_in'])->sum('quantity');
        $totalOut = $movements->whereIn('movement_type', ['sale', 'transfer_out', 'adjustment_out', 'production_usage', 'wastage', 'damaged', 'return_out'])->sum('quantity');

        return view('ktvtc.cafeteria.reports.inventory.movement', compact(
            'startDate', 'endDate', 'shopId', 'movementType', 'shops', 'movementTypes', 'movements', 'totalIn', 'totalOut'
        ));
    }

    /**
     * Inventory Turnover Report
     */
    public function inventoryTurnoverReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        $shops = Shop::where('is_active', true)->get();

        // Calculate COGS for the period
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId);
            })
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Calculate average inventory value
        $averageInventory = InventoryStock::select(DB::raw('AVG(current_stock * average_unit_cost) as avg_value'))
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->value('avg_value') ?? 0;

        $turnoverRatio = $averageInventory > 0 ? $cogs / $averageInventory : 0;

        // Product-level turnover
        $productTurnover = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId)
                  ->where('inventory_stocks.shop_id', $shopId);
            })
            ->select(
                'products.id',
                'products.product_name',
                'products.product_code',
                DB::raw('SUM(sale_items.quantity) as units_sold'),
                DB::raw('AVG(inventory_stocks.current_stock) as avg_stock'),
                DB::raw('SUM(sale_items.quantity) / NULLIF(AVG(inventory_stocks.current_stock), 0) as turnover_ratio')
            )
            ->groupBy('products.id', 'products.product_name', 'products.product_code')
            ->orderBy('turnover_ratio', 'desc')
            ->limit(20)
            ->get();

        return view('ktvtc.cafeteria.reports.inventory.turnover', compact(
            'startDate', 'endDate', 'shopId', 'shops', 'cogs', 'averageInventory', 'turnoverRatio', 'productTurnover'
        ));
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        // Get shops for filter dropdown
        $shops = Shop::where('is_active', true)->get();

        // Calculate revenue from sales
        $revenueQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled');

        if ($shopId) {
            $revenueQuery->where('shop_id', $shopId);
        }

        $revenue = $revenueQuery->sum('total_amount');

        // Calculate Cost of Goods Sold (COGS)
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId);
            })
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Calculate expenses from purchase orders
        $expensesQuery = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if ($shopId) {
            $expensesQuery->where('shop_id', $shopId);
        }

        $expenses = $expensesQuery->sum('total_amount');

        // Calculate profit metrics
        $grossProfit = $revenue - $cogs;
        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $netProfit = $grossProfit - $expenses;
        $netMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        // Daily trend for chart
        $dailyTrend = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('ktvtc.cafeteria.reports.financial.profit-loss', compact(
            'startDate',
            'endDate',
            'shopId',
            'shops',
            'revenue',
            'cogs',
            'expenses',
            'grossProfit',
            'grossMargin',
            'netProfit',
            'netMargin',
            'dailyTrend'
        ));
    }

    /**
     * Revenue Report
     */
    public function revenueReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        $shops = Shop::where('is_active', true)->get();

        // Daily revenue breakdown
        $dailyRevenue = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(CASE WHEN payment_method = "mpesa" THEN total_amount ELSE 0 END) as mpesa'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN total_amount ELSE 0 END) as cash'),
                DB::raw('COUNT(*) as transactions')
            )
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Category revenue
        $categoryRevenue = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('sales.shop_id', $shopId);
            })
            ->select(
                'product_categories.id',
                'product_categories.category_name',
                DB::raw('SUM(sale_items.quantity * sale_items.final_price) as revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as transactions')
            )
            ->groupBy('product_categories.id', 'product_categories.category_name')
            ->orderBy('revenue', 'desc')
            ->get();

        $totalRevenue = $dailyRevenue->sum('total');

        return view('ktvtc.cafeteria.reports.financial.revenue', compact(
            'startDate', 'endDate', 'shopId', 'shops', 'dailyRevenue', 'categoryRevenue', 'totalRevenue'
        ));
    }

    /**
     * Expenses Report
     */
    public function expensesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        $shops = Shop::where('is_active', true)->get();

        // Purchase order expenses
        $purchaseExpenses = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->sum('total_amount');

        // Supplier expense breakdown
        $supplierExpenses = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->select('supplier_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->orderBy('total', 'desc')
            ->get();

        // Monthly expense trend
        $monthlyExpenses = PurchaseOrder::select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->when($shopId, function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('ktvtc.cafeteria.reports.financial.expenses', compact(
            'startDate', 'endDate', 'shopId', 'shops', 'purchaseExpenses', 'supplierExpenses', 'monthlyExpenses'
        ));
    }

    /**
     * Quick Stats API for dashboard
     */
    public function quickStats(Request $request)
    {
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        $today = today();
        $currentMonthStart = now()->startOfMonth();

        $stats = [
            'today_sales' => Sale::whereDate('sale_date', $today)
                ->where('payment_status', 'paid')
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->sum('total_amount'),
            'today_transactions' => Sale::whereDate('sale_date', $today)
                ->where('payment_status', 'paid')
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->count(),
            'monthly_sales' => Sale::whereBetween('sale_date', [$currentMonthStart, now()])
                ->where('payment_status', 'paid')
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->sum('total_amount'),
            'low_stock_count' => InventoryStock::whereHas('product', function($q) {
                    $q->whereColumn('inventory_stocks.current_stock', '<=', 'products.reorder_level');
                })
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Schedule Report
     */
    public function scheduleReport(Request $request)
    {
        $validated = $request->validate([
            'reportType' => 'required|string',
            'frequency' => 'required|string',
            'email' => 'required|email',
            'format' => 'required|string'
        ]);

        // Store schedule in database or send confirmation
        // This is a placeholder - implement actual scheduling logic

        return response()->json(['success' => true, 'message' => 'Report scheduled successfully']);
    }

    /**
     * Export All Reports
     */
    public function exportAllReports(Request $request)
    {
        $format = $request->get('format', 'excel');

        // Generate zip file with all reports
        // This is a placeholder - implement actual export logic

        return response()->json(['message' => 'Export functionality coming soon']);
    }

    /**
     * Export Report to Excel
     */
    public function exportReport(Request $request, $type)
    {
        // Set export parameters
        $exportType = $request->get('export', 'excel');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id');

        if (!in_array($exportType, ['excel', 'pdf'])) {
            return redirect()->back()->with('error', 'Invalid export type');
        }

        // Generate export based on type
        switch ($type) {
            case 'daily-sales':
                return $this->exportDailySales($startDate, $endDate, $shopId, $exportType);

            case 'purchase-summary':
                return $this->exportPurchaseSummary($startDate, $endDate, $shopId, $exportType);

            case 'inventory-stock':
                return $this->exportInventoryStock($startDate, $endDate, $shopId, $exportType);

            case 'profit-loss':
                return $this->exportProfitLoss($startDate, $endDate, $shopId, $exportType);

            default:
                return redirect()->back()->with('error', 'Unknown report type');
        }
    }

    /**
     * Export Daily Sales Report
     */
    private function exportDailySales($date, $shopId, $format)
    {
        // Similar logic to dailySalesReport but formatted for export
        // In production, use Laravel Excel package

        return response()->download('path/to/exported/file.xlsx');
    }

    /**
     * Export Purchase Summary Report
     */
    private function exportPurchaseSummary($startDate, $endDate, $shopId, $format)
    {
        return response()->download('path/to/exported/file.xlsx');
    }

    /**
     * Export Inventory Stock Report
     */
    private function exportInventoryStock($startDate, $endDate, $shopId, $format)
    {
        return response()->download('path/to/exported/file.xlsx');
    }

    /**
     * Export Profit & Loss Report
     */
    private function exportProfitLoss($startDate, $endDate, $shopId, $format)
    {
        return response()->download('path/to/exported/file.xlsx');
    }
}
