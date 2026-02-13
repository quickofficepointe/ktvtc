<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Shop;
use App\Models\BusinessSection;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\StockAdjustment;
use App\Models\StockAlert;
use App\Models\GoodsReceivedNote;
use App\Models\DirectPurchase;
use App\Models\User;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CafeteriaController extends Controller
{
    /**
     * Enhanced dashboard with comprehensive analytics
     */
    public function dashboard()
    {
        Log::info('=== ENHANCED CAFETERIA DASHBOARD ===');

        $user = auth()->user();
        $shopId = $user->shop_id ?? 1;
        $today = now()->format('Y-m-d');
        $currentMonth = now()->format('Y-m');

        // Today's Overview
        $todayStats = $this->getTodayStats($shopId, $today);

        // Week Overview
        $weekStats = $this->getWeekStats($shopId);

        // Month Overview
        $monthStats = $this->getMonthStats($shopId, $currentMonth);

        // Year Overview
        $yearStats = $this->getYearStats($shopId, now()->year);

        // Product Performance
        $topProducts = $this->getTopProducts($shopId, 10);
        $worstProducts = $this->getWorstProducts($shopId, 10);

        // Category Performance
        $categoryPerformance = $this->getCategoryPerformance($shopId);

        // Customer Analysis
        $topCustomers = $this->getTopCustomers($shopId, 10);
        $customerFrequency = $this->getCustomerFrequency($shopId);

        // Inventory Status
        $inventoryStats = $this->getInventoryStats($shopId);

        // Sales Trends
        $hourlyTrend = $this->getHourlySalesTrend($shopId, $today);
        $dailyTrend = $this->getDailySalesTrend($shopId, 30);
        $monthlyTrend = $this->getMonthlySalesTrend($shopId, 12);

        // Payment Methods
        $paymentDistribution = $this->getPaymentDistribution($shopId, 30);

        // Staff Performance
        $staffPerformance = $this->getStaffPerformance($shopId, 30);

        // Recent Activity
        $recentSales = $this->getRecentSales($shopId, 10);
        $recentPurchases = $this->getRecentPurchases($shopId, 10);
        $recentAlerts = $this->getRecentStockAlerts($shopId, 10);

        // Performance Metrics
        $performanceMetrics = $this->calculatePerformanceMetrics($shopId);

        Log::info('Dashboard analytics loaded', [
            'shop_id' => $shopId,
            'today_sales' => $todayStats['total_sales']
        ]);

        return view('ktvtc.cafeteria.dashboard', compact(
            'todayStats',
            'weekStats',
            'monthStats',
            'yearStats',
            'topProducts',
            'worstProducts',
            'categoryPerformance',
            'topCustomers',
            'customerFrequency',
            'inventoryStats',
            'hourlyTrend',
            'dailyTrend',
            'monthlyTrend',
            'paymentDistribution',
            'staffPerformance',
            'recentSales',
            'recentPurchases',
            'recentAlerts',
            'performanceMetrics'
        ));
    }

    /**
     * Get today's detailed statistics
     */
    private function getTodayStats($shopId, $date)
    {
        // Sales data
        $sales = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->where('payment_status', '!=', 'cancelled')
            ->select(
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_items) as total_items'),
                DB::raw('AVG(total_amount) as average_sale')
            )->first();

        // Peak hour analysis
        $peakHour = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->where('payment_status', '!=', 'cancelled')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('total_sales', 'desc')
            ->first();

        // Product categories breakdown
        $categoryBreakdown = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.final_price) as total_revenue')
            )
            ->where('sales.shop_id', $shopId)
            ->whereDate('sales.sale_date', $date)
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('product_categories.category_name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Customer count
        $uniqueCustomers = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->where('payment_status', '!=', 'cancelled')
            ->distinct('customer_phone')
            ->count('customer_phone');

        // Payment method breakdown
        $paymentMethods = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->where('payment_status', '!=', 'cancelled')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('payment_method')
            ->get();

        return [
            'date' => $date,
            'total_sales' => $sales->total_sales ?? 0,
            'transaction_count' => $sales->transaction_count ?? 0,
            'total_items' => $sales->total_items ?? 0,
            'average_sale' => $sales->average_sale ?? 0,
            'unique_customers' => $uniqueCustomers,
            'peak_hour' => $peakHour ? [
                'hour' => $peakHour->hour,
                'transactions' => $peakHour->transaction_count,
                'sales' => $peakHour->total_sales
            ] : null,
            'category_breakdown' => $categoryBreakdown,
            'payment_methods' => $paymentMethods
        ];
    }

    /**
     * Get week statistics
     */
    private function getWeekStats($shopId)
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        // Daily breakdown
        $dailyBreakdown = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $sales = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $current)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $transactions = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $current)
                ->where('payment_status', '!=', 'cancelled')
                ->count();

            $dailyBreakdown[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('D'),
                'sales' => $sales,
                'transactions' => $transactions,
                'average' => $transactions > 0 ? $sales / $transactions : 0
            ];

            $current->addDay();
        }

        // Week totals
        $totalSales = array_sum(array_column($dailyBreakdown, 'sales'));
        $totalTransactions = array_sum(array_column($dailyBreakdown, 'transactions'));
        $averageDailySales = count($dailyBreakdown) > 0 ? $totalSales / count($dailyBreakdown) : 0;

        // Comparison with last week
        $lastWeekStart = $startDate->copy()->subWeek();
        $lastWeekEnd = $endDate->copy()->subWeek();

        $lastWeekSales = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$lastWeekStart, $lastWeekEnd])
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $growthRate = $lastWeekSales > 0
            ? (($totalSales - $lastWeekSales) / $lastWeekSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        return [
            'period' => $startDate->format('M d') . ' - ' . $endDate->format('M d'),
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_daily_sales' => $averageDailySales,
            'daily_breakdown' => $dailyBreakdown,
            'last_week_sales' => $lastWeekSales,
            'growth_rate' => $growthRate
        ];
    }

    /**
     * Get month statistics
     */
    private function getMonthStats($shopId, $month)
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // Daily breakdown
        $dailyBreakdown = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $sales = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $current)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $dailyBreakdown[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('D'),
                'sales' => $sales
            ];

            $current->addDay();
        }

        // Month totals
        $totalSales = array_sum(array_column($dailyBreakdown, 'sales'));
        $totalTransactions = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->count();

        $averageDailySales = count($dailyBreakdown) > 0 ? $totalSales / count($dailyBreakdown) : 0;

        // Top products for the month
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_code',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.final_price) as total_revenue')
            )
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.product_name', 'products.product_code')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Category breakdown
        $categoryBreakdown = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.category_name',
                DB::raw('SUM(sale_items.final_price) as total_revenue'),
                DB::raw('ROUND(SUM(sale_items.final_price) * 100.0 / SUM(SUM(sale_items.final_price)) OVER(), 2) as percentage')
            )
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('product_categories.category_name')
            ->get();

        return [
            'month' => $startDate->format('F Y'),
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_daily_sales' => $averageDailySales,
            'daily_breakdown' => $dailyBreakdown,
            'top_products' => $topProducts,
            'category_breakdown' => $categoryBreakdown
        ];
    }

    /**
     * Get year statistics
     */
    private function getYearStats($shopId, $year)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = Carbon::create($year, 12, 31)->endOfYear();

        // Monthly breakdown
        $monthlyBreakdown = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

            $sales = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $transactions = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->where('payment_status', '!=', 'cancelled')
                ->count();

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M'),
                'month_full' => $monthStart->format('F'),
                'sales' => $sales,
                'transactions' => $transactions,
                'average' => $transactions > 0 ? $sales / $transactions : 0
            ];
        }

        // Year totals
        $totalSales = array_sum(array_column($monthlyBreakdown, 'sales'));
        $totalTransactions = array_sum(array_column($monthlyBreakdown, 'transactions'));
        $averageMonthlySales = count($monthlyBreakdown) > 0 ? $totalSales / count($monthlyBreakdown) : 0;

        // Best month
        $bestMonth = collect($monthlyBreakdown)->sortByDesc('sales')->first();
        $worstMonth = collect($monthlyBreakdown)->sortBy('sales')->first();

        // Comparison with last year
        $lastYearStart = $startDate->copy()->subYear();
        $lastYearEnd = $endDate->copy()->subYear();

        $lastYearSales = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$lastYearStart, $lastYearEnd])
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $growthRate = $lastYearSales > 0
            ? (($totalSales - $lastYearSales) / $lastYearSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        return [
            'year' => $year,
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_monthly_sales' => $averageMonthlySales,
            'monthly_breakdown' => $monthlyBreakdown,
            'best_month' => $bestMonth,
            'worst_month' => $worstMonth,
            'last_year_sales' => $lastYearSales,
            'growth_rate' => $growthRate
        ];
    }

    /**
     * Get top performing products
     */
    private function getTopProducts($shopId, $limit = 10)
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_code',
                'product_categories.category_name',
                'products.selling_price',
                'products.cost_price',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.final_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.final_price) - SUM(sale_items.quantity * products.cost_price) as total_profit'),
                DB::raw('ROUND((SUM(sale_items.final_price) - SUM(sale_items.quantity * products.cost_price)) * 100.0 / SUM(sale_items.final_price), 2) as profit_margin')
            )
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.product_name', 'products.product_code', 'product_categories.category_name', 'products.selling_price', 'products.cost_price')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get worst performing products
     */
    private function getWorstProducts($shopId, $limit = 10)
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_code',
                'product_categories.category_name',
                'products.selling_price',
                'products.cost_price',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_quantity'),
                DB::raw('COALESCE(SUM(sale_items.final_price), 0) as total_revenue')
            )
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.product_name', 'products.product_code', 'product_categories.category_name', 'products.selling_price', 'products.cost_price')
            ->orderBy('total_revenue', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get category performance analysis
     */
    private function getCategoryPerformance($shopId)
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.id',
                'product_categories.category_name',
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.final_price) as total_revenue'),
                DB::raw('ROUND(AVG(sale_items.final_price / sale_items.quantity), 2) as average_price'),
                DB::raw('ROUND(SUM(sale_items.final_price) * 100.0 / SUM(SUM(sale_items.final_price)) OVER(), 2) as revenue_percentage')
            )
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->groupBy('product_categories.id', 'product_categories.category_name')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get top customers
     */
    private function getTopCustomers($shopId, $limit = 10)
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        return Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('customer_phone')
            ->select(
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('ROUND(AVG(total_amount), 2) as average_spent'),
                DB::raw('MAX(sale_date) as last_visit')
            )
            ->groupBy('customer_phone', 'customer_name')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get customer frequency analysis
     */
    private function getCustomerFrequency($shopId)
    {
        $startDate = now()->subDays(90);
        $endDate = now();

        $customerFrequency = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('customer_phone')
            ->select(
                'customer_phone',
                DB::raw('COUNT(*) as visit_count')
            )
            ->groupBy('customer_phone')
            ->get();

        return [
            'total_customers' => $customerFrequency->count(),
            'one_time_customers' => $customerFrequency->where('visit_count', 1)->count(),
            'repeat_customers' => $customerFrequency->where('visit_count', '>', 1)->count(),
            'frequent_customers' => $customerFrequency->where('visit_count', '>=', 5)->count(),
            'average_visits' => $customerFrequency->avg('visit_count') ?? 0,
            'customer_retention_rate' => $customerFrequency->count() > 0
                ? ($customerFrequency->where('visit_count', '>', 1)->count() / $customerFrequency->count()) * 100
                : 0
        ];
    }

    /**
     * Get inventory statistics
     */
    private function getInventoryStats($shopId)
    {
        // Stock status
        $products = Product::where('shop_id', $shopId)
            ->where('track_inventory', true)
            ->get();

        $totalProducts = $products->count();
        $totalStockValue = $products->sum(function($product) {
            return $product->current_stock * $product->cost_price;
        });

        // Stock categorization
        $lowStock = $products->filter(function($product) {
            return $product->current_stock > 0 &&
                   $product->current_stock <= $product->min_stock_level;
        });

        $outOfStock = $products->filter(function($product) {
            return $product->current_stock <= 0;
        });

        $overStock = $products->filter(function($product) {
            return $product->current_stock > ($product->reorder_level * 2);
        });

        // Turnover analysis
        $turnoverData = [];
        foreach ($products as $product) {
            $monthlySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.product_id', $product->id)
                ->where('sales.shop_id', $shopId)
                ->whereBetween('sales.sale_date', [now()->subMonth(), now()])
                ->sum('sale_items.quantity');

            $turnoverRate = $product->current_stock > 0 ? $monthlySales / $product->current_stock : 0;
            $daysCoverage = $turnoverRate > 0 ? 30 / $turnoverRate : 999;

            $turnoverData[] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'current_stock' => $product->current_stock,
                'monthly_sales' => $monthlySales,
                'turnover_rate' => round($turnoverRate, 2),
                'days_coverage' => round($daysCoverage, 1)
            ];
        }

        // Slow moving items (turnover rate < 0.5)
        $slowMoving = collect($turnoverData)->where('turnover_rate', '<', 0.5);
        $fastMoving = collect($turnoverData)->where('turnover_rate', '>=', 1);

        return [
            'total_products' => $totalProducts,
            'total_stock_value' => $totalStockValue,
            'low_stock_items' => $lowStock->count(),
            'out_of_stock_items' => $outOfStock->count(),
            'over_stock_items' => $overStock->count(),
            'stock_status' => [
                'low_stock_percentage' => $totalProducts > 0 ? ($lowStock->count() / $totalProducts) * 100 : 0,
                'out_of_stock_percentage' => $totalProducts > 0 ? ($outOfStock->count() / $totalProducts) * 100 : 0,
                'healthy_stock_percentage' => $totalProducts > 0 ?
                    (($totalProducts - $lowStock->count() - $outOfStock->count()) / $totalProducts) * 100 : 0
            ],
            'turnover_analysis' => [
                'slow_moving_items' => $slowMoving->count(),
                'fast_moving_items' => $fastMoving->count(),
                'average_turnover_rate' => collect($turnoverData)->avg('turnover_rate') ?? 0,
                'average_days_coverage' => collect($turnoverData)->avg('days_coverage') ?? 0
            ]
        ];
    }

    /**
     * Get hourly sales trend for today
     */
    private function getHourlySalesTrend($shopId, $date)
    {
        $hourlyData = [];

        for ($hour = 6; $hour <= 22; $hour++) { // 6 AM to 10 PM
            $startTime = Carbon::parse($date)->setHour($hour)->setMinute(0)->setSecond(0);
            $endTime = Carbon::parse($date)->setHour($hour)->setMinute(59)->setSecond(59);

            $sales = Sale::where('shop_id', $shopId)
                ->whereBetween('created_at', [$startTime, $endTime])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $transactions = Sale::where('shop_id', $shopId)
                ->whereBetween('created_at', [$startTime, $endTime])
                ->where('payment_status', '!=', 'cancelled')
                ->count();

            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $hour),
                'hour_label' => $hour < 12 ? $hour . ' AM' : ($hour == 12 ? '12 PM' : ($hour - 12) . ' PM'),
                'sales' => $sales,
                'transactions' => $transactions,
                'average' => $transactions > 0 ? $sales / $transactions : 0
            ];
        }

        // Peak hours
        $peakHour = collect($hourlyData)->sortByDesc('sales')->first();
        $quietHour = collect($hourlyData)->sortBy('sales')->first();

        return [
            'hourly_data' => $hourlyData,
            'peak_hour' => $peakHour,
            'quiet_hour' => $quietHour,
            'total_sales' => collect($hourlyData)->sum('sales'),
            'total_transactions' => collect($hourlyData)->sum('transactions')
        ];
    }

    /**
     * Get daily sales trend for last N days
     */
    private function getDailySalesTrend($shopId, $days = 30)
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $dailyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $sales = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $current)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $transactions = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $current)
                ->where('payment_status', '!=', 'cancelled')
                ->count();

            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('D'),
                'sales' => $sales,
                'transactions' => $transactions,
                'average' => $transactions > 0 ? $sales / $transactions : 0
            ];

            $current->addDay();
        }

        // Calculate moving averages
        $salesData = array_column($dailyData, 'sales');
        $movingAverage = $this->calculateMovingAverage($salesData, 7); // 7-day moving average

        // Add moving average to data
        foreach ($dailyData as $index => &$day) {
            $day['moving_average'] = $movingAverage[$index] ?? null;
        }

        // Best and worst days
        $bestDay = collect($dailyData)->sortByDesc('sales')->first();
        $worstDay = collect($dailyData)->sortBy('sales')->first();

        // Day of week analysis
        $dayOfWeekAnalysis = collect($dailyData)->groupBy('day')->map(function($group) {
            return [
                'total_sales' => $group->sum('sales'),
                'average_sales' => $group->avg('sales'),
                'transaction_count' => $group->sum('transactions'),
                'average_transaction' => $group->sum('transactions') > 0 ? $group->sum('sales') / $group->sum('transactions') : 0
            ];
        });

        return [
            'period' => $startDate->format('M d') . ' - ' . $endDate->format('M d'),
            'daily_data' => $dailyData,
            'moving_average' => $movingAverage,
            'best_day' => $bestDay,
            'worst_day' => $worstDay,
            'day_of_week_analysis' => $dayOfWeekAnalysis,
            'total_sales' => collect($dailyData)->sum('sales'),
            'average_daily_sales' => collect($dailyData)->avg('sales'),
            'growth_rate' => $this->calculateGrowthRate($salesData)
        ];
    }

    /**
     * Get monthly sales trend for last N months
     */
    private function getMonthlySalesTrend($shopId, $months = 12)
    {
        $startDate = now()->subMonths($months - 1)->startOfMonth();
        $endDate = now()->endOfMonth();

        $monthlyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $sales = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount');

            $transactions = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->where('payment_status', '!=', 'cancelled')
                ->count();

            $customers = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->where('payment_status', '!=', 'cancelled')
                ->distinct('customer_phone')
                ->count('customer_phone');

            $monthlyData[] = [
                'month' => $current->format('Y-m'),
                'month_label' => $current->format('M Y'),
                'sales' => $sales,
                'transactions' => $transactions,
                'customers' => $customers,
                'average_sale' => $transactions > 0 ? $sales / $transactions : 0,
                'frequency' => $customers > 0 ? $transactions / $customers : 0
            ];

            $current->addMonth();
        }

        // Calculate month-over-month growth
        $salesData = array_column($monthlyData, 'sales');
        $growthRates = [];

        for ($i = 1; $i < count($salesData); $i++) {
            if ($salesData[$i - 1] > 0) {
                $growthRates[] = (($salesData[$i] - $salesData[$i - 1]) / $salesData[$i - 1]) * 100;
            } else {
                $growthRates[] = $salesData[$i] > 0 ? 100 : 0;
            }
        }

        // Add growth rates to data
        foreach ($monthlyData as $index => &$month) {
            $month['growth_rate'] = $growthRates[$index - 1] ?? null;
        }

        // Best and worst months
        $bestMonth = collect($monthlyData)->sortByDesc('sales')->first();
        $worstMonth = collect($monthlyData)->sortBy('sales')->first();

        // Seasonal pattern (if we have enough data)
        $seasonalPattern = $this->analyzeSeasonalPattern($monthlyData);

        return [
            'period' => $startDate->format('M Y') . ' - ' . $endDate->format('M Y'),
            'monthly_data' => $monthlyData,
            'best_month' => $bestMonth,
            'worst_month' => $worstMonth,
            'seasonal_pattern' => $seasonalPattern,
            'average_monthly_sales' => collect($monthlyData)->avg('sales'),
            'total_sales' => collect($monthlyData)->sum('sales'),
            'average_growth_rate' => collect($growthRates)->avg() ?? 0
        ];
    }

    /**
     * Get payment method distribution
     */
    private function getPaymentDistribution($shopId, $days = 30)
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $distribution = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('ROUND(AVG(total_amount), 2) as average_amount')
            )
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $totalAmount = $distribution->sum('total_amount');

        // Add percentages
        $distribution->each(function($method) use ($totalAmount) {
            $method->percentage = $totalAmount > 0 ? round(($method->total_amount / $totalAmount) * 100, 2) : 0;
        });

        return [
            'distribution' => $distribution,
            'total_amount' => $totalAmount,
            'total_transactions' => $distribution->sum('transaction_count'),
            'primary_method' => $distribution->first(),
            'trend' => $this->getPaymentMethodTrend($shopId, $distribution->pluck('payment_method')->toArray())
        ];
    }

    /**
     * Get staff performance metrics
     */
    private function getStaffPerformance($shopId, $days = 30)
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $performance = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('cashier_id')
            ->with('cashier')
            ->select(
                'cashier_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(total_items) as total_items'),
                DB::raw('ROUND(AVG(total_amount), 2) as average_sale'),
                DB::raw('ROUND(SUM(total_amount) / COUNT(*), 2) as revenue_per_transaction'),
                DB::raw('MAX(sale_date) as last_sale')
            )
            ->groupBy('cashier_id')
            ->orderBy('total_sales', 'desc')
            ->get();

        // Add efficiency metrics
        $performance->each(function($staff) use ($days) {
            $workingDays = $days; // Simplified - in reality should check staff schedule
            $staff->sales_per_day = $workingDays > 0 ? $staff->total_sales / $workingDays : 0;
            $staff->transactions_per_day = $workingDays > 0 ? $staff->transaction_count / $workingDays : 0;
            $staff->items_per_transaction = $staff->transaction_count > 0 ? $staff->total_items / $staff->transaction_count : 0;
        });

        return [
            'staff_performance' => $performance,
            'top_performer' => $performance->first(),
            'average_performance' => [
                'average_sales' => $performance->avg('total_sales'),
                'average_transactions' => $performance->avg('transaction_count'),
                'average_sale_amount' => $performance->avg('average_sale')
            ]
        ];
    }

    /**
     * Get recent sales for dashboard
     */
    private function getRecentSales($shopId, $limit = 10)
    {
        return Sale::with(['items', 'customer', 'paymentTransactions'])
            ->where('shop_id', $shopId)
            ->where('payment_status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'payment_status' => $sale->payment_status,
                    'order_status' => $sale->order_status,
                    'created_at' => $sale->created_at->format('H:i'),
                    'time_ago' => $sale->created_at->diffForHumans(),
                    'items_count' => $sale->total_items,
                    'items_preview' => $sale->items->take(2)->pluck('product_name')->implode(', ')
                ];
            });
    }

    /**
     * Get recent purchases for dashboard
     */
    private function getRecentPurchases($shopId, $limit = 10)
    {
        return PurchaseOrder::with(['supplier', 'items'])
            ->where('shop_id', $shopId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($purchase) {
                return [
                    'id' => $purchase->id,
                    'po_number' => $purchase->po_number,
                    'supplier_name' => $purchase->supplier->name ?? 'N/A',
                    'total_amount' => $purchase->total_amount,
                    'status' => $purchase->status,
                    'items_count' => $purchase->total_items,
                    'created_at' => $purchase->created_at->format('M d, H:i'),
                    'items_preview' => $purchase->items->take(2)->pluck('product_name')->implode(', ')
                ];
            });
    }

    /**
     * Get recent stock alerts
     */
    /**
 * Get recent stock alerts
 */
private function getRecentStockAlerts($shopId, $limit = 10)
{
    // Use is_resolved = false to get active alerts (not resolved)
    $alerts = StockAlert::with(['product'])
        ->whereHas('product', function($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
        ->where('is_resolved', false) // Use is_resolved instead of status
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();

    $products = Product::where('shop_id', $shopId)
        ->where('track_inventory', true)
        ->where('current_stock', '<=', DB::raw('min_stock_level'))
        ->where('current_stock', '>', 0)
        ->orderBy('current_stock', 'asc')
        ->limit($limit)
        ->get();

    return [
        'alerts' => $alerts,
        'low_stock_products' => $products->map(function($product) {
            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'current_stock' => $product->current_stock,
                'min_stock_level' => $product->min_stock_level,
                'reorder_level' => $product->reorder_level,
                'percentage' => $product->min_stock_level > 0 ?
                    round(($product->current_stock / $product->min_stock_level) * 100, 2) : 0,
                'alert_type' => 'low_stock' // This matches your migration enum
            ];
        })
    ];
}
    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics($shopId)
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        // Sales data
        $salesData = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('AVG(total_amount) as average_transaction_value'),
                DB::raw('SUM(total_items) as total_items_sold')
            )->first();

        // Cost of goods sold
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.shop_id', $shopId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', '!=', 'cancelled')
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Gross profit
        $grossProfit = ($salesData->total_revenue ?? 0) - $cogs;
        $grossMargin = ($salesData->total_revenue ?? 0) > 0 ? ($grossProfit / $salesData->total_revenue) * 100 : 0;

        // Customer metrics
        $customerData = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('customer_phone')
            ->select(
                DB::raw('COUNT(DISTINCT customer_phone) as unique_customers'),
                DB::raw('AVG(total_amount) as average_customer_value')
            )->first();

        // Return customer rate
        $allCustomers = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate->copy()->subDays(60), $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('customer_phone')
            ->distinct('customer_phone')
            ->pluck('customer_phone');

        $returnCustomers = 0;
        foreach ($allCustomers as $customerPhone) {
            $visits = Sale::where('shop_id', $shopId)
                ->where('customer_phone', $customerPhone)
                ->where('payment_status', '!=', 'cancelled')
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->count();
            if ($visits > 1) $returnCustomers++;
        }

        $returnRate = ($customerData->unique_customers ?? 0) > 0 ?
            ($returnCustomers / $customerData->unique_customers) * 100 : 0;

        // Inventory metrics
        $inventoryValue = Product::where('shop_id', $shopId)
            ->where('track_inventory', true)
            ->sum(DB::raw('current_stock * cost_price'));

        $inventoryTurnover = $inventoryValue > 0 ? $cogs / $inventoryValue : 0;
        $inventoryDays = $inventoryTurnover > 0 ? 30 / $inventoryTurnover : 0;

        // Staff efficiency
        $staffEfficiency = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->whereNotNull('cashier_id')
            ->select(
                'cashier_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy('cashier_id')
            ->orderBy('total_sales', 'desc')
            ->get();

        $averageStaffSales = $staffEfficiency->avg('total_sales') ?? 0;

        return [
            'financial' => [
                'revenue' => $salesData->total_revenue ?? 0,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'gross_margin' => round($grossMargin, 2),
                'average_transaction_value' => $salesData->average_transaction_value ?? 0,
                'items_per_transaction' => ($salesData->transaction_count ?? 0) > 0 ?
                    ($salesData->total_items_sold ?? 0) / ($salesData->transaction_count ?? 1) : 0
            ],
            'customer' => [
                'unique_customers' => $customerData->unique_customers ?? 0,
                'average_customer_value' => $customerData->average_customer_value ?? 0,
                'return_customer_rate' => round($returnRate, 2),
                'customer_acquisition_cost' => 0, // Would need marketing data
                'customer_lifetime_value' => round(($customerData->average_customer_value ?? 0) * 3, 2) // Simplified
            ],
            'inventory' => [
                'turnover_rate' => round($inventoryTurnover, 2),
                'days_inventory' => round($inventoryDays, 1),
                'stock_value' => $inventoryValue,
                'stockout_rate' => 0, // Would need more detailed tracking
                'carrying_cost_percentage' => 0 // Would need storage cost data
            ],
            'operations' => [
                'transactions_per_day' => 30 > 0 ? ($salesData->transaction_count ?? 0) / 30 : 0,
                'sales_per_staff' => $averageStaffSales,
                'efficiency_score' => round(($salesData->average_transaction_value ?? 0) * ($salesData->transaction_count ?? 0) / max($staffEfficiency->count(), 1), 2),
                'peak_hour_efficiency' => 0 // Would need hour-by-hour analysis
            ],
            'growth_metrics' => [
                'month_over_month_growth' => $this->calculateMOMGrowth($shopId),
                'customer_growth_rate' => $this->calculateCustomerGrowth($shopId),
                'revenue_growth_rate' => $this->calculateRevenueGrowth($shopId),
                'market_share_growth' => 0 // Would need market data
            ]
        ];
    }

    /**
     * Helper: Calculate moving average
     */
    private function calculateMovingAverage($data, $window)
    {
        $movingAverage = [];
        $count = count($data);

        for ($i = 0; $i < $count; $i++) {
            $start = max(0, $i - $window + 1);
            $end = $i + 1;
            $slice = array_slice($data, $start, $end - $start);
            $movingAverage[] = count($slice) > 0 ? array_sum($slice) / count($slice) : null;
        }

        return $movingAverage;
    }

    /**
     * Helper: Calculate growth rate
     */
    private function calculateGrowthRate($data)
    {
        if (count($data) < 2) return 0;

        $first = $data[0];
        $last = $data[count($data) - 1];

        if ($first > 0) {
            return (($last - $first) / $first) * 100;
        }

        return $last > 0 ? 100 : 0;
    }

    /**
     * Helper: Analyze seasonal pattern
     */
    private function analyzeSeasonalPattern($monthlyData)
    {
        if (count($monthlyData) < 12) return null;

        $monthlySales = [];
        foreach ($monthlyData as $data) {
            $month = (int) substr($data['month'], 5, 2);
            $monthlySales[$month] = ($monthlySales[$month] ?? 0) + $data['sales'];
        }

        ksort($monthlySales);

        $average = array_sum($monthlySales) / count($monthlySales);
        $seasonality = [];

        foreach ($monthlySales as $month => $sales) {
            $seasonality[$month] = $average > 0 ? ($sales / $average) * 100 : 0;
        }

        return [
            'peak_month' => array_search(max($monthlySales), $monthlySales),
            'low_month' => array_search(min($monthlySales), $monthlySales),
            'seasonality_index' => $seasonality
        ];
    }

    /**
     * Helper: Get payment method trend
     */
    private function getPaymentMethodTrend($shopId, $methods)
    {
        $trend = [];
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->endOfMonth();

        foreach ($methods as $method) {
            $monthlyData = [];
            $current = $startDate->copy();

            while ($current <= $endDate) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                $sales = Sale::where('shop_id', $shopId)
                    ->where('payment_method', $method)
                    ->whereBetween('sale_date', [$monthStart, $monthEnd])
                    ->where('payment_status', '!=', 'cancelled')
                    ->sum('total_amount');

                $monthlyData[] = [
                    'month' => $current->format('M Y'),
                    'sales' => $sales
                ];

                $current->addMonth();
            }

            $trend[$method] = $monthlyData;
        }

        return $trend;
    }

    /**
     * Helper: Calculate month-over-month growth
     */
    private function calculateMOMGrowth($shopId)
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $currentSales = Sale::where('shop_id', $shopId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $previousSales = Sale::where('shop_id', $shopId)
            ->whereMonth('sale_date', $previousMonth->month)
            ->whereYear('sale_date', $previousMonth->year)
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        if ($previousSales > 0) {
            return (($currentSales - $previousSales) / $previousSales) * 100;
        }

        return $currentSales > 0 ? 100 : 0;
    }

    /**
     * Helper: Calculate customer growth
     */
    private function calculateCustomerGrowth($shopId)
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $currentCustomers = Sale::where('shop_id', $shopId)
            ->whereMonth('sale_date', $currentMonth->month)
            ->whereYear('sale_date', $currentMonth->year)
            ->where('payment_status', '!=', 'cancelled')
            ->distinct('customer_phone')
            ->count('customer_phone');

        $previousCustomers = Sale::where('shop_id', $shopId)
            ->whereMonth('sale_date', $previousMonth->month)
            ->whereYear('sale_date', $previousMonth->year)
            ->where('payment_status', '!=', 'cancelled')
            ->distinct('customer_phone')
            ->count('customer_phone');

        if ($previousCustomers > 0) {
            return (($currentCustomers - $previousCustomers) / $previousCustomers) * 100;
        }

        return $currentCustomers > 0 ? 100 : 0;
    }

    /**
     * Helper: Calculate revenue growth
     */
    private function calculateRevenueGrowth($shopId)
    {
        $startDate = now()->subDays(60);
        $endDate = now();

        $recentPeriod = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [now()->subDays(30), $endDate])
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        $previousPeriod = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [now()->subDays(60), now()->subDays(30)])
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_amount');

        if ($previousPeriod > 0) {
            return (($recentPeriod - $previousPeriod) / $previousPeriod) * 100;
        }

        return $recentPeriod > 0 ? 100 : 0;
    }

    /**
     * Get stats API endpoint for sidebar
     */
    public function getStats()
    {
        try {
            Log::info('Cafeteria stats API called');

            $shopId = auth()->user()->shop_id ?? 1;
            $today = now()->format('Y-m-d');

            // Today's sales
            $todaySales = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;

            $transactionCount = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('payment_status', '!=', 'cancelled')
                ->count() ?? 0;

            $pendingOrders = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('order_status', 'pending')
                ->count() ?? 0;

            // Low stock items
            $lowStockItems = Product::where('shop_id', $shopId)
                ->where('track_inventory', true)
                ->where('current_stock', '<=', DB::raw('min_stock_level'))
                ->where('current_stock', '>', 0)
                ->count();

            // Out of stock items
            $outOfStockItems = Product::where('shop_id', $shopId)
                ->where('track_inventory', true)
                ->where('current_stock', '<=', 0)
                ->count();

            // Pending payments
            $pendingPayments = Sale::where('shop_id', $shopId)
                ->where('payment_status', 'pending')
                ->count();

            // Average sale
            $averageSale = $transactionCount > 0 ? $todaySales / $transactionCount : 0;

            // This week's sales
            $weekSales = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;

            // This month's sales
            $monthSales = Sale::where('shop_id', $shopId)
                ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;

            return response()->json([
                'today_sales' => (float) $todaySales,
                'transaction_count' => (int) $transactionCount,
                'average_sale' => (float) $averageSale,
                'pending_orders' => (int) $pendingOrders,
                'today_orders' => (int) $transactionCount,
                'low_stock_items' => (int) $lowStockItems,
                'out_of_stock_items' => (int) $outOfStockItems,
                'pending_payments' => (int) $pendingPayments,
                'week_sales' => (float) $weekSales,
                'month_sales' => (float) $monthSales,
                'updated_at' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching stats: ' . $e->getMessage());
            return response()->json([
                'today_sales' => 0,
                'transaction_count' => 0,
                'average_sale' => 0,
                'pending_orders' => 0,
                'today_orders' => 0,
                'low_stock_items' => 0,
                'out_of_stock_items' => 0,
                'pending_payments' => 0,
                'week_sales' => 0,
                'month_sales' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get recent activity for dashboard
     */
    public function recentActivity()
    {
        try {
            $shopId = auth()->user()->shop_id ?? 1;
            $limit = 20;

            // Recent sales
            $recentSales = Sale::where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($sale) {
                    return [
                        'type' => 'sale',
                        'id' => $sale->id,
                        'title' => 'New Sale: ' . $sale->invoice_number,
                        'description' => $sale->customer_name . ' - KES ' . number_format($sale->total_amount, 2),
                        'time' => $sale->created_at->diffForHumans(),
                        'icon' => 'fa-shopping-cart',
                        'color' => 'text-green-500'
                    ];
                });

            // Recent purchases
            $recentPurchases = PurchaseOrder::where('shop_id', $shopId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($purchase) {
                    return [
                        'type' => 'purchase',
                        'id' => $purchase->id,
                        'title' => 'Purchase Order: ' . $purchase->po_number,
                        'description' => 'Supplier: ' . ($purchase->supplier->name ?? 'N/A') . ' - KES ' . number_format($purchase->total_amount, 2),
                        'time' => $purchase->created_at->diffForHumans(),
                        'icon' => 'fa-shopping-basket',
                        'color' => 'text-blue-500'
                    ];
                });

            // Recent stock adjustments
            $recentAdjustments = StockAdjustment::whereHas('product', function($query) use ($shopId) {
                    $query->where('shop_id', $shopId);
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($adjustment) {
                    return [
                        'type' => 'adjustment',
                        'id' => $adjustment->id,
                        'title' => 'Stock Adjustment',
                        'description' => $adjustment->product->product_name . ' - ' . $adjustment->quantity . ' units',
                        'time' => $adjustment->created_at->diffForHumans(),
                        'icon' => 'fa-exchange-alt',
                        'color' => 'text-yellow-500'
                    ];
                });

            // Combine and sort by time
            $allActivity = collect([])
                ->merge($recentSales)
                ->merge($recentPurchases)
                ->merge($recentAdjustments)
                ->sortByDesc('time')
                ->take($limit);

            return response()->json([
                'activity' => $allActivity->values(),
                'total' => $allActivity->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Recent activity error: ' . $e->getMessage());
            return response()->json([
                'activity' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
