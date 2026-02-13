<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Daily Sales Report
     */
    public function dailySalesReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        // Get shops for filter dropdown
        $shops = Shop::whereIn('id', auth()->user()->accessibleShopIds())->get();

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
     * Purchase Summary Report
     */
    public function purchaseSummaryReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $supplierId = $request->get('supplier_id');

        // Get shops and suppliers for filter dropdowns
        $shops = Shop::whereIn('id', auth()->user()->accessibleShopIds())->get();
        $suppliers = Supplier::orderBy('name')->get();

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

        // Supplier breakdown
        $supplierBreakdown = $purchases->groupBy('supplier_id')->map(function($group) {
            return [
                'name' => $group->first()->supplier->name ?? 'Unknown',
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
     * Inventory Stock Levels Report
     */
    public function inventoryStockLevelsReport(Request $request)
    {
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);
        $stockStatus = $request->get('stock_status');

        // Get shops for filter dropdown
        $shops = Shop::whereIn('id', auth()->user()->accessibleShopIds())->get();

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
                    $q->whereColumn('inventory_stocks.current_stock', '<=', 'products.minimum_stock')
                      ->where('inventory_stocks.current_stock', '>', 0);
                });
            } elseif ($stockStatus === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($stockStatus === 'normal') {
                $query->whereHas('product', function($q) {
                    $q->whereColumn('inventory_stocks.current_stock', '>', 'products.minimum_stock');
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
                } elseif ($stock->current_stock <= ($stock->product->minimum_stock ?? 0)) {
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
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $shopId = $request->get('shop_id', auth()->user()->shop_id ?? 1);

        // Get shops for filter dropdown
        $shops = Shop::whereIn('id', auth()->user()->accessibleShopIds())->get();

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
     * Export Report to Excel
     */
    public function exportReport(Request $request, $type)
    {
        // Set export parameters
        $exportType = $request->get('export');
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
     * Helper function for accessible shop IDs
     */
    private function getAccessibleShopIds()
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return Shop::pluck('id')->toArray();
        }

        return [$user->shop_id ?? 1];
    }
}
