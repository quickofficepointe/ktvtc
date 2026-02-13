@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Enhanced Dashboard')

@section('breadcrumbs')
    <li class="inline-flex items-center">
        <span class="text-sm font-medium text-gray-500">Analytics Dashboard</span>
    </li>
@endsection

@section('page-actions')
    <button onclick="refreshDashboard()" class="bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-200">
        <i class="fas fa-sync-alt mr-2"></i> Refresh
    </button>
    <button onclick="exportDashboardData()" class="bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-200">
        <i class="fas fa-download mr-2"></i> Export
    </button>
@endsection

@section('styles')
    <!-- ApexCharts for advanced charts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">
    <!-- Flatpickr for date range -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .apexcharts-tooltip {
            border-radius: 10px !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
        }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .trend-up {
            color: #10B981;
            animation: pulse-green 2s infinite;
        }
        .trend-down {
            color: #EF4444;
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes pulse-red {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .metric-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .progress-bar-container {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #E5E7EB;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        .heatmap-day {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }
        .category-chip {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        .sparkline-container {
            min-width: 100px;
        }
        .table-row-hover:hover {
            background-color: rgba(230, 57, 70, 0.05);
        }
        .dark-mode .card {
            background-color: #1F2937;
            color: #F9FAFB;
        }
        .quick-stat {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #E5E7EB;
            transition: all 0.3s ease;
        }
        .quick-stat:hover {
            border-color: #E63946;
            transform: translateY(-2px);
        }
        .activity-timeline {
            border-left: 2px solid #E5E7EB;
            margin-left: 16px;
            padding-left: 24px;
        }
        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 4px;
        }
    </style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Header with Date Range -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Comprehensive Analytics Dashboard</h2>
            <p class="text-gray-600">Real-time insights and performance metrics</p>
        </div>
        <div class="mt-4 md:mt-0">
            <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <input type="text" id="dateRangePicker" class="bg-white border border-gray-300 rounded-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Select date range">
                    <i class="fas fa-calendar-alt absolute right-3 top-3 text-gray-400"></i>
                </div>
                <select id="timePeriodSelect" class="bg-white border border-gray-300 rounded-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week" selected>This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="hidden">
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            <span class="ml-3 text-gray-600">Loading dashboard data...</span>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div id="dashboardContent">

        <!-- =================== ROW 1: KEY METRICS =================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Today's Sales -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Today's Sales</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($todayStats['total_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="stat-card-icon bg-red-50 text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <span class="text-gray-600">{{ $todayStats['transaction_count'] ?? 0 }} transactions</span>
                        <span class="mx-2">•</span>
                        <span class="text-gray-600">Avg: KES {{ number_format($todayStats['average_sale'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center">
                        @if($todayStats['peak_hour'])
                        <span class="text-xs bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-full">
                            Peak: {{ $todayStats['peak_hour']['hour'] ?? '--' }}:00
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Week Performance -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Week Performance</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($weekStats['total_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="stat-card-icon bg-green-50 text-green-600">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <span class="{{ $weekStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas fa-arrow-{{ $weekStats['growth_rate'] >= 0 ? 'up' : 'down' }} mr-1"></i>
                            {{ number_format(abs($weekStats['growth_rate'] ?? 0), 1) }}%
                        </span>
                        <span class="text-gray-600 ml-2">vs last week</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ $weekStats['total_transactions'] ?? 0 }} trans
                    </div>
                </div>
            </div>

            <!-- Month Performance -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Month Performance</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($monthStats['total_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="stat-card-icon bg-blue-50 text-blue-600">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <span class="text-gray-600">Daily avg: KES {{ number_format($monthStats['average_daily_sales'] ?? 0, 2) }}</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ $monthStats['total_transactions'] ?? 0 }} trans
                    </div>
                </div>
            </div>

            <!-- Year Performance -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Year Performance</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($yearStats['total_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="stat-card-icon bg-purple-50 text-purple-600">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <span class="{{ $yearStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas fa-arrow-{{ $yearStats['growth_rate'] >= 0 ? 'up' : 'down' }} mr-1"></i>
                            {{ number_format(abs($yearStats['growth_rate'] ?? 0), 1) }}%
                        </span>
                        <span class="text-gray-600 ml-2">vs last year</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ $yearStats['total_transactions'] ?? 0 }} trans
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== ROW 2: SALES CHARTS =================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Daily Sales Trend -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Daily Sales Trend (30 Days)</h4>
                        <p class="text-sm text-gray-600">Revenue and transaction trends</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-green-600 font-medium">+{{ number_format($dailyTrend['growth_rate'] ?? 0, 1) }}% growth</span>
                    </div>
                </div>
                <div id="dailySalesChart" style="min-height: 300px;"></div>
            </div>

            <!-- Hourly Sales Pattern -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Hourly Sales Pattern (Today)</h4>
                        <p class="text-sm text-gray-600">Peak hours and transaction volume</p>
                    </div>
                    <div class="text-sm">
                        @if($hourlyTrend['peak_hour'])
                        <span class="bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-full text-xs font-medium">
                            Peak: {{ $hourlyTrend['peak_hour']['hour_label'] ?? '--' }}
                        </span>
                        @endif
                    </div>
                </div>
                <div id="hourlySalesChart" style="min-height: 300px;"></div>
            </div>
        </div>

        <!-- =================== ROW 3: PRODUCT PERFORMANCE =================== -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="flex justify-between items-center p-5">
                        <div>
                            <h4 class="font-semibold text-gray-800">Product Performance Analysis</h4>
                            <p class="text-sm text-gray-600">Top and worst performing products</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="toggleProductView('top')" class="px-4 py-2 text-sm font-medium rounded-lg bg-primary text-white">
                                Top Products
                            </button>
                            <button onclick="toggleProductView('worst')" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
                                Worst Products
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div id="topProductsTable">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit Margin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topProducts as $product)
                                <tr class="table-row-hover">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-red-50 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-utensils text-primary"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->product_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->product_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="category-chip bg-{{ $product->category_name ? 'blue' : 'gray' }}-100 text-{{ $product->category_name ? 'blue' : 'gray' }}-800">
                                            {{ $product->category_name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $product->total_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">KES {{ number_format($product->total_revenue, 2) }}</div>
                                        <div class="text-xs text-gray-500">Cost: KES {{ number_format($product->total_cost, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ max(0, min(100, $product->profit_margin)) }}%"></div>
                                            </div>
                                            <span class="ml-2 text-sm text-gray-900">{{ number_format($product->profit_margin, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-trend-up mr-1"></i> Excellent
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-utensils text-4xl text-gray-300 mb-3"></i>
                                        <p>No product sales data available</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Worst Products Table (Hidden by default) -->
                <div id="worstProductsTable" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($worstProducts as $product)
                                <tr class="table-row-hover">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-50 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-gray-400"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->product_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->product_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="category-chip bg-gray-100 text-gray-800">
                                            {{ $product->category_name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $product->total_quantity ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">KES {{ number_format($product->total_revenue, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Needs Review
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-3"></i>
                                        <p>No underperforming products</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== ROW 4: INVENTORY & CUSTOMER ANALYTICS =================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Inventory Status -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Inventory Status</h4>
                        <p class="text-sm text-gray-600">Stock levels and turnover</p>
                    </div>
                    <div class="text-sm text-primary font-medium">
                        <span>KES {{ number_format($inventoryStats['total_stock_value'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Stock Levels -->
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Low Stock Items</span>
                            <span class="font-medium">{{ $inventoryStats['low_stock_items'] ?? 0 }}</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill bg-yellow-500" style="width: {{ $inventoryStats['stock_status']['low_stock_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Out of Stock</span>
                            <span class="font-medium">{{ $inventoryStats['out_of_stock_items'] ?? 0 }}</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill bg-red-500" style="width: {{ $inventoryStats['stock_status']['out_of_stock_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Healthy Stock</span>
                            <span class="font-medium">{{ $inventoryStats['total_products'] - $inventoryStats['low_stock_items'] - $inventoryStats['out_of_stock_items'] }}</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill bg-green-500" style="width: {{ $inventoryStats['stock_status']['healthy_stock_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Turnover Metrics -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-800">{{ $inventoryStats['turnover_analysis']['slow_moving_items'] ?? 0 }}</div>
                            <div class="text-xs text-gray-600">Slow Moving</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-800">{{ $inventoryStats['turnover_analysis']['fast_moving_items'] ?? 0 }}</div>
                            <div class="text-xs text-gray-600">Fast Moving</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-800">{{ number_format($inventoryStats['turnover_analysis']['average_turnover_rate'] ?? 0, 1) }}</div>
                            <div class="text-xs text-gray-600">Avg Turnover</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-800">{{ number_format($inventoryStats['turnover_analysis']['average_days_coverage'] ?? 0, 0) }}</div>
                            <div class="text-xs text-gray-600">Days Coverage</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Analysis -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Customer Analysis</h4>
                        <p class="text-sm text-gray-600">Customer behavior and loyalty</p>
                    </div>
                    <div class="text-sm">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                            {{ number_format($customerFrequency['customer_retention_rate'] ?? 0, 1) }}% Retention
                        </span>
                    </div>
                </div>

                <!-- Customer Segments -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-users text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-800">Total Customers</div>
                                <div class="text-xs text-gray-600">Last 90 days</div>
                            </div>
                        </div>
                        <div class="text-lg font-bold text-gray-800">{{ $customerFrequency['total_customers'] ?? 0 }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-700">{{ $customerFrequency['repeat_customers'] ?? 0 }}</div>
                            <div class="text-xs text-green-600 font-medium">Repeat Customers</div>
                        </div>
                        <div class="p-3 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-700">{{ $customerFrequency['one_time_customers'] ?? 0 }}</div>
                            <div class="text-xs text-yellow-600 font-medium">One-time Customers</div>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-700">{{ $customerFrequency['frequent_customers'] ?? 0 }}</div>
                            <div class="text-xs text-purple-600 font-medium">Frequent Customers</div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-700">{{ number_format($customerFrequency['average_visits'] ?? 0, 1) }}</div>
                            <div class="text-xs text-gray-600 font-medium">Avg Visits/Customer</div>
                        </div>
                    </div>
                </div>

                <!-- Top Customers List -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h5 class="text-sm font-medium text-gray-800 mb-3">Top Customers</h5>
                    <div class="space-y-3">
                        @forelse($topCustomers->take(3) as $customer)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center">
                                    <i class="fas fa-user text-primary text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->customer_name ?? 'Anonymous' }}</div>
                                    <div class="text-xs text-gray-500">{{ $customer->customer_phone ?? 'No phone' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">KES {{ number_format($customer->total_spent, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->visit_count }} visits</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-gray-500 text-sm">
                            No customer data available
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Payment Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Payment Distribution</h4>
                        <p class="text-sm text-gray-600">Payment method preferences</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-primary font-medium">
                            KES {{ number_format($paymentDistribution['total_amount'] ?? 0, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Payment Methods Chart -->
                <div id="paymentDistributionChart" style="min-height: 200px;"></div>

                <!-- Payment Methods List -->
                <div class="mt-6 space-y-3">
                    @foreach($paymentDistribution['distribution'] as $method)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @switch($method->payment_method)
                                @case('cash')
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-money-bill text-green-600"></i>
                                    </div>
                                    @break
                                @case('card')
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-credit-card text-blue-600"></i>
                                    </div>
                                    @break
                                @case('mpesa')
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-mobile-alt text-green-600"></i>
                                    </div>
                                    @break
                                @default
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-wallet text-gray-600"></i>
                                    </div>
                            @endswitch
                            <span class="text-sm font-medium text-gray-800 capitalize">{{ $method->payment_method }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($method->percentage, 1) }}%</div>
                            <div class="text-xs text-gray-500">KES {{ number_format($method->total_amount, 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- =================== ROW 5: PERFORMANCE METRICS =================== -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="font-semibold text-gray-800">Performance Metrics</h4>
                        <p class="text-sm text-gray-600">Key performance indicators (KPIs)</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-primary font-medium">Last 30 Days</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Financial Metrics -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center mr-3">
                                <i class="fas fa-chart-pie text-primary"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-800">Financial</div>
                                <div class="text-xs text-gray-600">Revenue & Profit</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Gross Margin</span>
                                <span class="font-medium text-green-600">{{ $performanceMetrics['financial']['gross_margin'] ?? 0 }}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Avg Transaction</span>
                                <span class="font-medium text-gray-800">KES {{ number_format($performanceMetrics['financial']['average_transaction_value'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Items/Transaction</span>
                                <span class="font-medium text-gray-800">{{ number_format($performanceMetrics['financial']['items_per_transaction'] ?? 0, 1) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Metrics -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mr-3">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-800">Customer</div>
                                <div class="text-xs text-gray-600">Retention & Value</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Return Rate</span>
                                <span class="font-medium text-green-600">{{ $performanceMetrics['customer']['return_customer_rate'] ?? 0 }}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Avg Customer Value</span>
                                <span class="font-medium text-gray-800">KES {{ number_format($performanceMetrics['customer']['average_customer_value'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Customer Lifetime</span>
                                <span class="font-medium text-gray-800">KES {{ number_format($performanceMetrics['customer']['customer_lifetime_value'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Metrics -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center mr-3">
                                <i class="fas fa-boxes text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-800">Inventory</div>
                                <div class="text-xs text-gray-600">Turnover & Efficiency</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Turnover Rate</span>
                                <span class="font-medium text-gray-800">{{ number_format($performanceMetrics['inventory']['turnover_rate'] ?? 0, 1) }}x</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Days Inventory</span>
                                <span class="font-medium text-gray-800">{{ number_format($performanceMetrics['inventory']['days_inventory'] ?? 0, 0) }} days</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock Value</span>
                                <span class="font-medium text-gray-800">KES {{ number_format($performanceMetrics['inventory']['stock_value'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Operations Metrics -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center mr-3">
                                <i class="fas fa-cogs text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-800">Operations</div>
                                <div class="text-xs text-gray-600">Efficiency & Growth</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">MoM Growth</span>
                                <span class="font-medium {{ ($performanceMetrics['growth_metrics']['month_over_month_growth'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($performanceMetrics['growth_metrics']['month_over_month_growth'] ?? 0, 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Transactions/Day</span>
                                <span class="font-medium text-gray-800">{{ number_format($performanceMetrics['operations']['transactions_per_day'] ?? 0, 1) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Efficiency Score</span>
                                <span class="font-medium text-gray-800">{{ number_format($performanceMetrics['operations']['efficiency_score'] ?? 0, 1) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== ROW 6: RECENT ACTIVITY & ALERTS =================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Recent Activity</h4>
                        <p class="text-sm text-gray-600">Latest transactions and updates</p>
                    </div>
                    <div class="text-sm">
                        <button onclick="loadMoreActivity()" class="text-primary hover:text-primary-dark font-medium">
                            View All
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($recentSales as $sale)
                    <div class="flex items-start">
                        <div class="relative">
                            <div class="activity-dot bg-green-500"></div>
                        </div>
                        <div class="ml-6">
                            <div class="flex justify-between">
                                <h5 class="text-sm font-medium text-gray-900">Sale #{{ $sale['invoice_number'] }}</h5>
                                <span class="text-xs text-gray-500">{{ $sale['time_ago'] }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $sale['customer_name'] }} • {{ $sale['items_count'] }} items • KES {{ number_format($sale['total_amount'], 2) }}
                            </p>
                            <div class="flex items-center mt-2">
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800 mr-2">
                                    {{ ucfirst($sale['payment_method']) }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $sale['payment_status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($sale['payment_status']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-3xl text-gray-300 mb-3"></i>
                        <p>No recent activity</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Stock Alerts & Recent Purchases -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Alerts & Recent Purchases</h4>
                        <p class="text-sm text-gray-600">Stock alerts and recent purchases</p>
                    </div>
                    <div class="text-sm">
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                            {{ $recentAlerts['low_stock_products']->count() }} Alerts
                        </span>
                    </div>
                </div>

                <!-- Stock Alerts -->
                <div class="mb-6">
                    <h5 class="text-sm font-medium text-gray-800 mb-3">Stock Alerts</h5>
                    <div class="space-y-3">
                        @forelse($recentAlerts['low_stock_products']->take(3) as $product)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</div>
                                    <div class="text-xs text-gray-600">
                                        Stock: {{ $product['current_stock'] }} / Min: {{ $product['min_stock_level'] }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-red-600">{{ $product['percentage'] }}%</div>
                                <div class="text-xs text-gray-600">Reorder Level: {{ $product['reorder_level'] }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-gray-500 text-sm">
                            <i class="fas fa-check-circle text-green-400 text-lg mr-2"></i>
                            All stock levels are healthy
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Purchases -->
                <div>
                    <h5 class="text-sm font-medium text-gray-800 mb-3">Recent Purchases</h5>
                    <div class="space-y-3">
                        @forelse($recentPurchases as $purchase)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-shopping-basket text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $purchase['po_number'] }}</div>
                                    <div class="text-xs text-gray-600">{{ $purchase['supplier_name'] }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">KES {{ number_format($purchase['total_amount'], 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $purchase['created_at'] }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-gray-500 text-sm">
                            No recent purchases
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== ROW 7: CATEGORY PERFORMANCE =================== -->
        <div class="mt-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h4 class="font-semibold text-gray-800">Category Performance</h4>
                        <p class="text-sm text-gray-600">Revenue distribution by category</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-primary font-medium">{{ $categoryPerformance->count() }} Categories</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($categoryPerformance as $category)
                            <tr class="table-row-hover">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9 rounded-lg {{ $category->category_name == 'Food' ? 'bg-red-50 text-primary' : ($category->category_name == 'Beverage' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600') }} flex items-center justify-center">
                                            @if($category->category_name == 'Food')
                                            <i class="fas fa-utensils"></i>
                                            @elseif($category->category_name == 'Beverage')
                                            <i class="fas fa-coffee"></i>
                                            @else
                                            <i class="fas fa-cookie-bite"></i>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $category->category_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $category->transaction_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $category->total_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">KES {{ number_format($category->total_revenue, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">KES {{ number_format($category->average_price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-primary h-2.5 rounded-full" style="width: {{ $category->revenue_percentage }}%"></div>
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">{{ $category->revenue_percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-tags text-3xl text-gray-300 mb-3"></i>
                                    <p>No category data available</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats Footer -->
    <div class="mt-6 bg-white rounded-xl shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-lg font-bold text-gray-800">{{ $todayStats['transaction_count'] ?? 0 }}</div>
                <div class="text-xs text-gray-600">Today's Transactions</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-800">{{ $todayStats['unique_customers'] ?? 0 }}</div>
                <div class="text-xs text-gray-600">Today's Customers</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-800">{{ number_format($todayStats['average_sale'] ?? 0, 2) }}</div>
                <div class="text-xs text-gray-600">Avg Sale Value</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-800">{{ number_format(($todayStats['total_items'] ?? 0) / max($todayStats['transaction_count'] ?? 1, 1), 1) }}</div>
                <div class="text-xs text-gray-600">Items per Sale</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script>
// Initialize date range picker
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range picker
    flatpickr("#dateRangePicker", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [moment().subtract(30, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')]
    });

    // Initialize charts
    initializeCharts();

    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboardData, 300000);
});

// Chart initialization
function initializeCharts() {
    // Daily Sales Chart
    if (document.getElementById('dailySalesChart')) {
        const dailyData = @json($dailyTrend['daily_data'] ?? []);
        const dates = dailyData.map(d => d.date);
        const sales = dailyData.map(d => d.sales);
        const movingAvg = dailyData.map(d => d.moving_average);

        const options = {
            series: [{
                name: 'Daily Sales',
                data: sales
            }, {
                name: '7-Day Moving Avg',
                data: movingAvg
            }],
            chart: {
                height: 300,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            colors: ['#E63946', '#1D3557'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: dates,
                labels: {
                    rotate: -45,
                    formatter: function(value) {
                        return moment(value).format('MMM D');
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return 'KES ' + (value / 1000).toFixed(0) + 'K';
                    }
                }
            },
            tooltip: {
                x: {
                    formatter: function(value) {
                        return moment(value).format('MMM D, YYYY');
                    }
                },
                y: {
                    formatter: function(value) {
                        return 'KES ' + value.toFixed(2);
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        const chart = new ApexCharts(document.querySelector("#dailySalesChart"), options);
        chart.render();
    }

    // Hourly Sales Chart
    if (document.getElementById('hourlySalesChart')) {
        const hourlyData = @json($hourlyTrend['hourly_data'] ?? []);
        const hours = hourlyData.map(h => h.hour_label);
        const hourlySales = hourlyData.map(h => h.sales);

        const options = {
            series: [{
                name: 'Sales',
                data: hourlySales
            }],
            chart: {
                height: 300,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            colors: ['#E63946'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%',
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: hours
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return 'KES ' + value.toFixed(0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return 'KES ' + value.toFixed(2);
                    }
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#hourlySalesChart"), options);
        chart.render();
    }

    // Payment Distribution Chart
    if (document.getElementById('paymentDistributionChart')) {
        const paymentData = @json($paymentDistribution['distribution'] ?? []);
        const labels = paymentData.map(p => p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1));
        const percentages = paymentData.map(p => p.percentage);

        const options = {
            series: percentages,
            chart: {
                height: 200,
                type: 'donut',
                toolbar: {
                    show: false
                }
            },
            colors: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444'],
            labels: labels,
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    return 'KES ' + (@json($paymentDistribution['total_amount'] ?? 0)).toFixed(2);
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: false
            }
        };

        const chart = new ApexCharts(document.querySelector("#paymentDistributionChart"), options);
        chart.render();
    }
}

// Toggle between top and worst products
function toggleProductView(view) {
    const topBtn = document.querySelector('[onclick="toggleProductView(\'top\')"]');
    const worstBtn = document.querySelector('[onclick="toggleProductView(\'worst\')"]');
    const topTable = document.getElementById('topProductsTable');
    const worstTable = document.getElementById('worstProductsTable');

    if (view === 'top') {
        topBtn.classList.remove('bg-gray-100', 'text-gray-700');
        topBtn.classList.add('bg-primary', 'text-white');
        worstBtn.classList.remove('bg-primary', 'text-white');
        worstBtn.classList.add('bg-gray-100', 'text-gray-700');
        topTable.classList.remove('hidden');
        worstTable.classList.add('hidden');
    } else {
        worstBtn.classList.remove('bg-gray-100', 'text-gray-700');
        worstBtn.classList.add('bg-primary', 'text-white');
        topBtn.classList.remove('bg-primary', 'text-white');
        topBtn.classList.add('bg-gray-100', 'text-gray-700');
        worstTable.classList.remove('hidden');
        topTable.classList.add('hidden');
    }
}

// Refresh dashboard
function refreshDashboard() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('dashboardContent').classList.add('opacity-50');

    $.ajax({
        url: '{{ route("cafeteria.dashboard") }}',
        method: 'GET',
        success: function(response) {
            // Reload the page to get fresh data
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Failed to refresh dashboard');
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('dashboardContent').classList.remove('opacity-50');
        }
    });
}

// Export dashboard data
function exportDashboardData() {
    const date = new Date().toISOString().split('T')[0];
    const filename = `cafeteria-dashboard-${date}.pdf`;

    toastr.info('Preparing export...');

    // In a real implementation, this would call a PDF generation endpoint
    // For now, show a toast message
    setTimeout(() => {
        toastr.success('Export prepared! Download will start shortly.');

        // Simulate download (replace with actual download logic)
        const link = document.createElement('a');
        link.href = '#';
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }, 1500);
}

// Load more activity
function loadMoreActivity() {
    window.location.href = '{{ route("cafeteria.sales.index") }}';
}

// Auto-refresh dashboard data
function refreshDashboardData() {
    // Only refresh if user is active on the page
    if (!document.hidden) {
        $.ajax({
            url: '/cafeteria/stats',
            method: 'GET',
            success: function(data) {
                // Update quick stats
                if (data.today_sales) {
                    const salesEl = document.querySelector('.stat-card:nth-child(1) h3');
                    if (salesEl) {
                        salesEl.textContent = 'KES ' + parseFloat(data.today_sales).toFixed(2);
                    }
                }

                // Show notification for new activity
                toastr.info('Dashboard stats updated');
            }
        });
    }
}

// Time period selector
document.getElementById('timePeriodSelect').addEventListener('change', function(e) {
    const period = e.target.value;
    const datePicker = document.getElementById('dateRangePicker');

    if (period === 'custom') {
        datePicker.style.display = 'block';
        return;
    }

    datePicker.style.display = 'none';

    let startDate, endDate;
    const today = moment();

    switch(period) {
        case 'today':
            startDate = today.format('YYYY-MM-DD');
            endDate = today.format('YYYY-MM-DD');
            break;
        case 'yesterday':
            startDate = today.subtract(1, 'days').format('YYYY-MM-DD');
            endDate = startDate;
            break;
        case 'week':
            startDate = today.startOf('week').format('YYYY-MM-DD');
            endDate = today.endOf('week').format('YYYY-MM-DD');
            break;
        case 'month':
            startDate = today.startOf('month').format('YYYY-MM-DD');
            endDate = today.endOf('month').format('YYYY-MM-DD');
            break;
        case 'quarter':
            startDate = today.startOf('quarter').format('YYYY-MM-DD');
            endDate = today.endOf('quarter').format('YYYY-MM-DD');
            break;
        case 'year':
            startDate = today.startOf('year').format('YYYY-MM-DD');
            endDate = today.endOf('year').format('YYYY-MM-DD');
            break;
    }

    // Apply filter (this would normally trigger an AJAX request)
    toastr.info(`Filtering data for ${period} period`);

    // In a real implementation, you would:
    // 1. Show loading state
    // 2. Make AJAX request with date range
    // 3. Update charts and data
});
</script>
@endsection
