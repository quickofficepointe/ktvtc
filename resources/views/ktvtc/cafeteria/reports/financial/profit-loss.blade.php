@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Profit & Loss Report')
@section('page-title', 'Profit & Loss Report')
@section('page-description', 'Financial performance analysis')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Reports
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Profit & Loss
    </span>
</li>
@endsection

@section('content')
<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('cafeteria.reports.financial.profit-loss') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ $startDate }}"
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                       max="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ $endDate }}"
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                       max="{{ now()->format('Y-m-d') }}">
            </div>

            <!-- Shop Selection -->
            @if($shops->count() > 1)
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
            <div class="flex items-end">
                <button type="submit"
                        class="bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition flex items-center">
                    <i class="fas fa-chart-line mr-2"></i> Generate Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Financial Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Revenue -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Total Revenue</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($revenue, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            From sales
        </div>
    </div>

    <!-- Gross Profit -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Gross Profit</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($grossProfit, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ $grossMargin > 0 ? number_format($grossMargin, 1) : 0 }}% margin
        </div>
    </div>

    <!-- Net Profit -->
    <div class="bg-gradient-to-r from-{{ $netProfit >= 0 ? 'primary to-red-600' : 'red-500 to-pink-500' }} rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Net Profit</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($netProfit, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-{{ $netProfit >= 0 ? 'chart-bar' : 'exclamation-triangle' }} text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ $netMargin > 0 ? number_format($netMargin, 1) : 0 }}% net margin
        </div>
    </div>
</div>

<!-- Cost Breakdown -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-6">Cost Breakdown</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Cost of Goods Sold -->
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h4 class="font-semibold text-gray-800">Cost of Goods Sold</h4>
                    <p class="text-sm text-gray-500">Direct costs of sold products</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-box text-red-600"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-red-600 mb-2">
                KES {{ number_format($cogs, 2) }}
            </div>
            <div class="text-sm text-gray-600">
                {{ $revenue > 0 ? round(($cogs / $revenue) * 100, 1) : 0 }}% of revenue
            </div>
        </div>

        <!-- Expenses -->
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h4 class="font-semibold text-gray-800">Operating Expenses</h4>
                    <p class="text-sm text-gray-500">Purchase orders & other expenses</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-orange-600"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-orange-600 mb-2">
                KES {{ number_format($expenses, 2) }}
            </div>
            <div class="text-sm text-gray-600">
                {{ $revenue > 0 ? round(($expenses / $revenue) * 100, 1) : 0 }}% of revenue
            </div>
        </div>

        <!-- Profitability -->
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h4 class="font-semibold text-gray-800">Profitability</h4>
                    <p class="text-sm text-gray-500">Financial performance metrics</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-percentage text-green-600"></i>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Gross Margin:</span>
                    <span class="font-semibold {{ $grossMargin >= 30 ? 'text-green-600' : ($grossMargin >= 20 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ number_format($grossMargin, 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Net Margin:</span>
                    <span class="font-semibold {{ $netMargin >= 15 ? 'text-green-600' : ($netMargin >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ number_format($netMargin, 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Profit per KES 100:</span>
                    <span class="font-semibold text-primary">
                        KES {{ number_format($netMargin, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Trend Chart -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Daily Revenue Trend</h3>
    <div class="h-64">
        <canvas id="revenueTrendChart"></canvas>
    </div>
</div>

<!-- Financial Ratios -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Financial Ratios & Analysis</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Revenue Growth</div>
            <div class="text-xl font-bold text-primary">
                {{ $revenue > 0 ? 'Positive' : 'No Growth' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Based on period sales</div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Cost Efficiency</div>
            <div class="text-xl font-bold {{ $grossMargin >= 30 ? 'text-green-600' : ($grossMargin >= 20 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $grossMargin >= 30 ? 'High' : ($grossMargin >= 20 ? 'Medium' : 'Low') }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Gross margin indicator</div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Net Profitability</div>
            <div class="text-xl font-bold {{ $netMargin >= 15 ? 'text-green-600' : ($netMargin >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $netMargin >= 15 ? 'Excellent' : ($netMargin >= 5 ? 'Good' : 'Needs Improvement') }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Overall profitability</div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Financial Health</div>
            <div class="text-xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $netProfit >= 0 ? 'Healthy' : 'At Risk' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Profit/Loss status</div>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="mt-6 text-center">
    <button onclick="exportReport()"
            class="bg-primary text-white font-medium py-3 px-6 rounded-lg hover:bg-red-700 transition inline-flex items-center">
        <i class="fas fa-file-export mr-2"></i> Export Report to Excel
    </button>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function exportReport() {
        const startDate = '{{ $startDate }}';
        const endDate = '{{ $endDate }}';
        const shopId = '{{ request("shop_id") }}';

        let url = '{{ route("cafeteria.reports.financial.profit-loss") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;

        window.open(url, '_blank');
    }

    // Revenue Trend Chart
    $(document).ready(function() {
        const ctx = document.getElementById('revenueTrendChart').getContext('2d');
        const dailyData = @json($dailyTrend);

        const labels = dailyData.map(item => {
            const date = new Date(item.date);
            return date.getDate() + '/' + (date.getMonth() + 1);
        });
        const data = dailyData.map(item => item.revenue);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Revenue',
                    data: data,
                    backgroundColor: 'rgba(230, 57, 70, 0.7)',
                    borderColor: '#E63946',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
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
    });
</script>
@endsection
