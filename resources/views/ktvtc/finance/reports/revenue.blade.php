@extends('ktvtc.finance.layouts.app')

@section('title', 'Revenue Report')
@section('subtitle', 'View revenue breakdown and trends')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.reports.revenue') }}" class="text-gray-600 hover:text-primary">Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Revenue</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.export', ['type' => 'revenue', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.reports.revenue') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-sm text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalRevenue ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-sm text-gray-600">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($dailyRevenue->sum('transactions') ?? 0) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-sm text-gray-600">Average Daily Revenue</p>
            <p class="text-2xl font-bold text-purple-600">
                KES {{ number_format(($dailyRevenue->count() ?? 0) > 0 ? ($totalRevenue ?? 0) / $dailyRevenue->count() : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Daily Revenue Trend</h3>
        <canvas id="revenueChart" height="250"></canvas>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Revenue by Payment Method</h3>
            <div class="space-y-3">
                @forelse($methodRevenue ?? [] as $method)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium uppercase">{{ $method->payment_method }}</span>
                            <span>KES {{ number_format($method->total, 2) }}</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="fill bg-primary" style="width: {{ ($totalRevenue > 0) ? ($method->total / $totalRevenue) * 100 : 0 }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $method->count }} transactions</span>
                            <span>{{ ($totalRevenue > 0) ? number_format(($method->total / $totalRevenue) * 100, 1) : 0 }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No revenue data available</p>
                @endforelse
            </div>
        </div>

        <!-- Daily Breakdown Table -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daily Breakdown</h3>
            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Date</th>
                            <th class="pb-2 font-semibold text-right">Revenue</th>
                            <th class="pb-2 font-semibold text-right">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyRevenue ?? [] as $day)
                            <tr class="border-b border-gray-100">
                                <td class="py-2">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                <td class="py-2 text-right font-medium">KES {{ number_format($day->total, 2) }}</td>
                                <td class="py-2 text-right">{{ $day->transactions }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-gray-300">
                        <tr>
                            <td class="py-2 font-bold">Total</td>
                            <td class="py-2 text-right font-bold">KES {{ number_format($totalRevenue ?? 0, 2) }}</td>
                            <td class="py-2 text-right font-bold">{{ number_format($dailyRevenue->sum('transactions') ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            const labels = {!! json_encode($dailyRevenue->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d M');
            }) ?? []) !!};
            const data = {!! json_encode($dailyRevenue->pluck('total') ?? []) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (KES)',
                        data: data,
                        borderColor: 'rgba(5, 150, 105, 1)',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(5, 150, 105, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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
@endpush
