@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')
@section('page-description', 'Detailed revenue analysis by payment method, category, and time period')

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
        Financial
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Revenue Analysis
    </span>
</li>
@endsection

@section('styles')
<style>
    .revenue-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .revenue-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.reports.financial.revenue') }}" class="space-y-4">
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

    <!-- Revenue Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="revenue-card bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($totalRevenue ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ number_format($dailyRevenue->sum('transactions') ?? 0) }} transactions
            </div>
        </div>

        <!-- M-Pesa Revenue -->
        <div class="revenue-card bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">M-Pesa Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($dailyRevenue->sum('mpesa') ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fab fa-mpesa text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ number_format(($totalRevenue ?? 0) > 0 ? (($dailyRevenue->sum('mpesa') ?? 0) / ($totalRevenue ?? 1)) * 100 : 0, 1) }}% of total
            </div>
        </div>

        <!-- Cash Revenue -->
        <div class="revenue-card bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Cash Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($dailyRevenue->sum('cash') ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ number_format(($totalRevenue ?? 0) > 0 ? (($dailyRevenue->sum('cash') ?? 0) / ($totalRevenue ?? 1)) * 100 : 0, 1) }}% of total
            </div>
        </div>

        <!-- Average Daily Revenue -->
        <div class="revenue-card bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Average Daily Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($averageDailyRevenue ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Over {{ $dailyRevenue->count() ?? 0 }} days
            </div>
        </div>
    </div>

    <!-- Revenue Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Daily Revenue Trend</h3>
            <div class="h-80">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Method Breakdown</h3>
            <div class="h-64">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-gray-500">M-Pesa</p>
                    <p class="text-lg font-bold text-green-600">KES {{ number_format($dailyRevenue->sum('mpesa') ?? 0, 2) }}</p>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                    <p class="text-xs text-gray-500">Cash</p>
                    <p class="text-lg font-bold text-yellow-600">KES {{ number_format($dailyRevenue->sum('cash') ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Revenue Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-tags text-primary mr-2"></i> Revenue by Category
            </h3>
            <p class="text-sm text-gray-600">Revenue breakdown by product category</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average per Transaction</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categoryRevenue ?? [] as $category)
                    @php
                        $percentage = ($totalRevenue ?? 0) > 0 ? ($category->revenue / ($totalRevenue ?? 1)) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-folder text-primary text-sm"></i>
                                </div>
                                <div class="font-medium text-gray-900">{{ $category->category_name ?? 'Uncategorized' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($category->transactions ?? 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                            KES {{ number_format($category->revenue ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-24">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            KES {{ number_format(($category->transactions ?? 0) > 0 ? ($category->revenue ?? 0) / ($category->transactions ?? 1) : 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No category revenue data available
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($categoryRevenue) && $categoryRevenue->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4">{{ number_format($categoryRevenue->sum('transactions'), 0) }}</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($categoryRevenue->sum('revenue'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Daily Revenue Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i> Daily Revenue Breakdown
                </h3>
                <p class="text-sm text-gray-600">Detailed daily revenue by payment method</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $dailyRevenue->count() ?? 0 }} days in range
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">M-Pesa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Order</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailyRevenue ?? [] as $day)
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
                            {{ number_format($day->transactions ?? 0) }}
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
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-chart-line text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-600">No revenue data found for selected period</p>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your date range or shop filter</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($dailyRevenue) && $dailyRevenue->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="2" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4">{{ number_format($dailyRevenue->sum('transactions'), 0) }}</td>
                        <td class="px-6 py-4 text-green-600">KES {{ number_format($dailyRevenue->sum('mpesa'), 2) }}</td>
                        <td class="px-6 py-4 text-yellow-600">KES {{ number_format($dailyRevenue->sum('cash'), 2) }}</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($dailyRevenue->sum('total'), 2) }}</td>
                        <td class="px-6 py-4">KES {{ number_format(($dailyRevenue->sum('transactions') ?? 0) > 0 ? ($dailyRevenue->sum('total') ?? 0) / ($dailyRevenue->sum('transactions') ?? 1) : 0, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function exportReport() {
        const startDate = '{{ request("start_date", $startDate ?? now()->startOfMonth()->format("Y-m-d")) }}';
        const endDate = '{{ request("end_date", $endDate ?? now()->format("Y-m-d")) }}';
        const shopId = '{{ request("shop_id") }}';

        let url = '{{ route("cafeteria.reports.financial.revenue") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;

        window.open(url, '_blank');
    }

    // Daily Revenue Chart
    $(document).ready(function() {
        const dailyData = @json($dailyRevenue ?? []);

        if (dailyData.length > 0) {
            const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
            const labels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            });
            const mpesaData = dailyData.map(item => item.mpesa);
            const cashData = dailyData.map(item => item.cash);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'M-Pesa',
                            data: mpesaData,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Cash',
                            data: cashData,
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': KES ' + context.raw.toLocaleString();
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
        const paymentData = {
            mpesa: {{ $dailyRevenue->sum('mpesa') ?? 0 }},
            cash: {{ $dailyRevenue->sum('cash') ?? 0 }}
        };

        if (paymentData.mpesa > 0 || paymentData.cash > 0) {
            const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');

            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['M-Pesa', 'Cash'],
                    datasets: [{
                        data: [paymentData.mpesa, paymentData.cash],
                        backgroundColor: ['#10B981', '#F59E0B'],
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
                                    const total = paymentData.mpesa + paymentData.cash;
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: KES ${context.raw.toLocaleString()} (${percentage}%)`;
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
