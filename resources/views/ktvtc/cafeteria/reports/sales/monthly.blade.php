@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Monthly Sales Report')
@section('page-title', 'Monthly Sales Report')
@section('page-description', 'Comprehensive monthly sales analysis with trends and comparisons')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Reports
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Sales
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Monthly Report
    </span>
</li>
@endsection

@section('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.reports.sales.monthly') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date"
                           name="start_date"
                           value="{{ request('start_date', $startDate ?? now()->startOfMonth()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date"
                           name="end_date"
                           value="{{ request('end_date', $endDate ?? now()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Shop Selection -->
                @if(isset($shops) && $shops->count() > 1)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
                    <select name="shop_id"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition flex items-center">
                        <i class="fas fa-chart-line mr-2"></i> Generate Report
                    </button>
                    <button type="button"
                            onclick="exportReport()"
                            class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i> Export
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="stat-card bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($totalSales ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ number_format($totalTransactions ?? 0) }} transactions
            </div>
        </div>

        <!-- Average Daily Sales -->
        <div class="stat-card bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Average Daily Sales</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($averageDailySales ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Over {{ $daysCount ?? 0 }} days
            </div>
        </div>

        <!-- Best Day -->
        <div class="stat-card bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Best Day</p>
                    <p class="text-xl font-bold mt-1">{{ $bestDay ?? 'N/A' }}</p>
                    <p class="text-sm mt-1">KES {{ number_format($bestDayAmount ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Growth Rate -->
        <div class="stat-card bg-gradient-to-r from-purple-500 to-indigo-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Growth Rate</p>
                    <p class="text-2xl font-bold mt-1">
                        @php
                            $growthRate = $growthRate ?? 0;
                        @endphp
                        <span class="{{ $growthRate >= 0 ? 'text-green-300' : 'text-red-300' }}">
                            {{ $growthRate >= 0 ? '+' : '' }}{{ number_format($growthRate, 1) }}%
                        </span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                vs previous month
            </div>
        </div>
    </div>

    <!-- Daily Sales Trend Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Daily Sales Trend</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Total: KES {{ number_format($dailySales->sum('total') ?? 0, 2) }}</span>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Methods Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Method Breakdown</h3>
            <div class="chart-container" style="height: 250px;">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="mt-4 space-y-2" id="paymentMethodLegend"></div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top Selling Products</h3>
            <div class="overflow-y-auto max-h-80">
                <div class="space-y-3">
                    @forelse($topProducts ?? [] as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                <i class="fas fa-box text-primary text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $product->product_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $product->quantity ?? 0 }} units sold</div>
                            </div>
                        </div>
                        <div class="font-bold text-primary">KES {{ number_format($product->revenue ?? 0, 2) }}</div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box text-3xl mb-2 text-gray-300"></i>
                        <p>No product data available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Sales Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Hourly Sales Distribution</h3>
        <div class="chart-container">
            <canvas id="hourlySalesChart"></canvas>
        </div>
    </div>

    <!-- Daily Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i> Daily Breakdown
                </h3>
                <p class="text-sm text-gray-600">Detailed daily sales performance</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $dailySales->count() ?? 0 }} days in range
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">M-Pesa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Order</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailySales ?? [] as $day)
                    @php
                        $date = Carbon\Carbon::parse($day->date);
                        $dayName = $date->format('l');
                        $isWeekend = in_array($dayName, ['Saturday', 'Sunday']);
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $isWeekend ? 'bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isWeekend ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $dayName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium">{{ number_format($day->transactions ?? 0) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($day->items_sold ?? 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-green-600">KES {{ number_format($day->mpesa ?? 0, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-yellow-600">KES {{ number_format($day->cash ?? 0, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                            KES {{ number_format($day->total ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            KES {{ number_format(($day->transactions ?? 0) > 0 ? ($day->total ?? 0) / ($day->transactions ?? 1) : 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-chart-line text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-600">No sales data found for selected period</p>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your date range or shop filter</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($dailySales) && $dailySales->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="2" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4">{{ number_format($dailySales->sum('transactions'), 0) }}</td>
                        <td class="px-6 py-4">{{ number_format($dailySales->sum('items_sold'), 0) }}</td>
                        <td class="px-6 py-4 text-green-600">KES {{ number_format($dailySales->sum('mpesa'), 2) }}</td>
                        <td class="px-6 py-4 text-yellow-600">KES {{ number_format($dailySales->sum('cash'), 2) }}</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($dailySales->sum('total'), 2) }}</td>
                        <td class="px-6 py-4">KES {{ number_format(($dailySales->sum('transactions') ?? 0) > 0 ? ($dailySales->sum('total') ?? 0) / ($dailySales->sum('transactions') ?? 1) : 0, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Sales Transactions List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-receipt text-primary mr-2"></i> Sales Transactions
                </h3>
                <p class="text-sm text-gray-600">Detailed list of all sales in the selected period</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $sales->total() ?? 0 }} transactions found
            </div>
        </div>

        @if(isset($sales) && $sales->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-primary">{{ $sale->invoice_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>{{ $sale->sale_date->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $sale->customer_name ?? 'Walk-in Customer' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->customer_phone ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($sale->total_items) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $sale->payment_method === 'mpesa' ? 'bg-green-100 text-green-800' :
                                   ($sale->payment_method === 'cash' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($sale->payment_method ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                   ($sale->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   ($sale->payment_status === 'failed' ? 'bg-red-100 text-red-800' :
                                   'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($sale->payment_status ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                            KES {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $sale->cashier->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="6" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($sales->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
        @else
        <div class="p-8 text-center">
            <i class="fas fa-receipt text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-600">No sales transactions found for selected period</p>
            <p class="text-sm text-gray-500 mt-1">Try adjusting your date range or shop filter</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Export Report
    function exportReport() {
        const startDate = '{{ request("start_date", $startDate ?? now()->startOfMonth()->format("Y-m-d")) }}';
        const endDate = '{{ request("end_date", $endDate ?? now()->format("Y-m-d")) }}';
        const shopId = '{{ request("shop_id") }}';

        let url = '{{ route("cafeteria.reports.sales.monthly") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;

        window.open(url, '_blank');
    }

    // Initialize Charts
    $(document).ready(function() {
        // Daily Sales Chart
        const dailyData = @json($dailySales ?? []);

        if (dailyData.length > 0) {
            const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
            const dailyLabels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            });
            const dailyRevenue = dailyData.map(item => item.total);

            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: dailyRevenue,
                        borderColor: '#E63946',
                        backgroundColor: 'rgba(230, 57, 70, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#E63946',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
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

        // Payment Method Chart
        const paymentData = @json($paymentMethodData ?? []);

        if (Object.keys(paymentData).length > 0) {
            const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
            const paymentLabels = Object.keys(paymentData);
            const paymentValues = Object.values(paymentData);
            const paymentColors = ['#25A25A', '#F59E0B', '#3B82F6', '#8B5CF6', '#EC4899'];

            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: paymentLabels.map(l => l.toUpperCase()),
                    datasets: [{
                        data: paymentValues,
                        backgroundColor: paymentColors.slice(0, paymentLabels.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = paymentValues.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: KES ${context.raw.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Hourly Sales Chart
        const hourlyData = @json($hourlySales ?? []);

        if (hourlyData.length > 0) {
            const hourlyCtx = document.getElementById('hourlySalesChart').getContext('2d');
            const hourlyLabels = hourlyData.map(item => item.hour);
            const hourlyValues = hourlyData.map(item => item.amount);

            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: hourlyLabels,
                    datasets: [{
                        label: 'Sales Amount',
                        data: hourlyValues,
                        backgroundColor: '#E63946',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
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
    });
</script>
@endsection
