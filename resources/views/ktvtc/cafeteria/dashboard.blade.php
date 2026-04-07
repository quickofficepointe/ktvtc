@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Dashboard')
@section('subtitle', 'Cafeteria Management Dashboard')

@section('breadcrumb')
<li class="inline-flex items-center">
    <span class="text-sm font-medium text-gray-500">Dashboard</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="refreshDashboard()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center space-x-2">
        <i class="fas fa-sync-alt"></i>
        <span>Refresh</span>
    </button>
    <a href="{{ route('cafeteria.sales.pos') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Sale</span>
    </a>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Today's Sales -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($todayStats['total_sales'] ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-shopping-cart text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <i class="fas fa-receipt mr-1"></i>
            {{ $todayStats['transaction_count'] ?? 0 }} transactions
        </div>
    </div>

    <!-- Week Performance -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Week Performance</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($weekStats['total_sales'] ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm">
            @if(($weekStats['growth_rate'] ?? 0) >= 0)
                <span class="text-green-600"><i class="fas fa-arrow-up mr-1"></i> {{ number_format(abs($weekStats['growth_rate'] ?? 0), 1) }}% vs last week</span>
            @else
                <span class="text-red-600"><i class="fas fa-arrow-down mr-1"></i> {{ number_format(abs($weekStats['growth_rate'] ?? 0), 1) }}% vs last week</span>
            @endif
        </div>
    </div>

    <!-- Month Performance -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Month Performance</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($monthStats['total_sales'] ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <i class="fas fa-chart-bar mr-1"></i>
            Daily avg: KES {{ number_format($monthStats['average_daily_sales'] ?? 0, 2) }}
        </div>
    </div>

    <!-- Total Customers -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Customers</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($customerFrequency['total_customers'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <i class="fas fa-user-check mr-1"></i>
            {{ number_format($customerFrequency['repeat_customers'] ?? 0) }} repeat customers
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Daily Sales Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Sales Trend</h3>
        <div class="h-80">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Selling Products</h3>
        <div class="overflow-y-auto max-h-80">
            <div class="space-y-4">
                @forelse($topProducts->take(5) as $product)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $product->product_name }}</span>
                        <span class="text-sm text-gray-600">KES {{ number_format($product->total_revenue, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: {{ ($product->total_revenue / max($topProducts[0]->total_revenue ?? 1, 1)) * 100 }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $product->total_quantity }} units sold</p>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No product data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods & Category Performance -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Methods -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @foreach($paymentDistribution['distribution'] ?? [] as $method)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 capitalize">{{ $method->payment_method }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($method->percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $method->percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">KES {{ number_format($method->total_amount, 2) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Category Performance -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Performance</h3>
        <div class="space-y-4">
            @forelse($categoryPerformance as $category)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $category->category_name }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($category->revenue_percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $category->revenue_percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">KES {{ number_format($category->total_revenue, 2) }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No category data available</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
        <p class="text-sm text-gray-600 mt-1">Latest sales activity</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentSales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-sm font-mono">{{ $sale['invoice_number'] }}</td>
                    <td class="py-3 px-6 text-sm">{{ $sale['customer_name'] }}</td>
                    <td class="py-3 px-6 text-sm">{{ $sale['items_count'] }}</td>
                    <td class="py-3 px-6 text-sm font-medium text-green-600">KES {{ number_format($sale['total_amount'], 2) }}</td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($sale['payment_method'] == 'cash') bg-yellow-100 text-yellow-800
                            @elseif($sale['payment_method'] == 'mpesa') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($sale['payment_method']) }}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-sm text-gray-500">{{ $sale['time_ago'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">No recent transactions</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Low Stock Alerts -->
@if(($recentAlerts['low_stock_products'] ?? collect())->count() > 0)
<div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3"></i>
        <div class="flex-1">
            <p class="font-semibold text-yellow-800">Low Stock Alert</p>
            <p class="text-sm text-yellow-700">The following products are running low on stock:</p>
        </div>
        <a href="{{ route('cafeteria.products.index') }}" class="text-sm text-yellow-800 hover:underline">View Products</a>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($recentAlerts['low_stock_products']->take(5) as $product)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            {{ $product['product_name'] }} ({{ $product['current_stock'] }} left)
        </span>
        @endforeach
    </div>
</div>
@endif
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Sales Chart
    const dailyData = @json($dailyTrend['daily_data'] ?? []);

    // Check if we have data
    if (dailyData.length > 0) {
        const dates = dailyData.map(d => {
            // Handle different date formats
            if (d.date) {
                return moment(d.date).format('MMM D');
            }
            return '';
        });

        const sales = dailyData.map(d => d.sales || 0);

        const ctx = document.getElementById('dailySalesChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Sales (KES)',
                        data: sales,
                        borderColor: '#E63946',
                        backgroundColor: 'rgba(230, 57, 70, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#E63946',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    } else {
        // Show no data message
        const chartContainer = document.getElementById('dailySalesChart');
        if (chartContainer && chartContainer.parentElement) {
            chartContainer.parentElement.innerHTML += `
                <div class="flex items-center justify-center h-80 text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-2"></i>
                        <p>No sales data available for the selected period</p>
                    </div>
                </div>
            `;
        }
    }

    // Refresh function
    function refreshDashboard() {
        location.reload();
    }
</script>
@endsection
