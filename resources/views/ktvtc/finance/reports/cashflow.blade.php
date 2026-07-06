@extends('ktvtc.finance.layouts.app')

@section('title', 'Cash Flow Report')
@section('subtitle', 'View cash flow statement')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.reports.cashflow') }}" class="text-gray-600 hover:text-primary">Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Cash Flow</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.export', ['type' => 'cashflow', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.reports.cashflow') }}" class="flex flex-wrap items-end gap-4">
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-sm text-gray-600">Cash Inflows</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($cashInflows ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-red-50 border-red-200">
            <p class="text-sm text-gray-600">Cash Outflows</p>
            <p class="text-2xl font-bold text-red-600">KES {{ number_format($cashOutflows ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 {{ ($netCashFlow ?? 0) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-orange-50 border-orange-200' }}">
            <p class="text-sm text-gray-600">Net Cash Flow</p>
            <p class="text-2xl font-bold {{ ($netCashFlow ?? 0) >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                {{ ($netCashFlow ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netCashFlow ?? 0), 2) }}
            </p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-sm text-gray-600">Cash Position</p>
            <p class="text-2xl font-bold text-purple-600">
                {{ ($netCashFlow ?? 0) >= 0 ? 'Positive' : 'Negative' }}
            </p>
        </div>
    </div>

    <!-- Cash Flow Chart -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Daily Cash Flow Trend</h3>
        <canvas id="cashflowChart" height="250"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cash Flow Statement -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-lg mb-4 border-b pb-2">Cash Flow Statement</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Cash Inflows (Collections)</span>
                    <span class="font-semibold text-green-600">KES {{ number_format($cashInflows ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Cash Outflows (Purchases)</span>
                    <span class="font-semibold text-red-600">KES {{ number_format($cashOutflows ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Net Cash Flow</span>
                    <span class="text-xl font-bold {{ ($netCashFlow ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ ($netCashFlow ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netCashFlow ?? 0), 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Daily Breakdown -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daily Breakdown</h3>
            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Date</th>
                            <th class="pb-2 font-semibold text-right">Inflow</th>
                            <th class="pb-2 font-semibold text-right">Outflow</th>
                            <th class="pb-2 font-semibold text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyCashFlow ?? [] as $day)
                            @php
                                $net = $day->inflow - $day->outflow;
                            @endphp
                            <tr class="border-b border-gray-100">
                                <td class="py-2">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                <td class="py-2 text-right text-green-600 font-medium">KES {{ number_format($day->inflow, 2) }}</td>
                                <td class="py-2 text-right text-red-600 font-medium">KES {{ number_format($day->outflow, 2) }}</td>
                                <td class="py-2 text-right font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    {{ $net >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($net), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-gray-300">
                        <tr>
                            <td class="py-2 font-bold">Total</td>
                            <td class="py-2 text-right font-bold text-green-600">KES {{ number_format($dailyCashFlow->sum('inflow') ?? 0, 2) }}</td>
                            <td class="py-2 text-right font-bold text-red-600">KES {{ number_format($dailyCashFlow->sum('outflow') ?? 0, 2) }}</td>
                            <td class="py-2 text-right font-bold text-blue-600">
                                KES {{ number_format(($dailyCashFlow->sum('inflow') ?? 0) - ($dailyCashFlow->sum('outflow') ?? 0), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Summary Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Period</p>
                <p class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Days</p>
                <p class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg Daily Inflow</p>
                <p class="text-lg font-bold text-green-600">
                    KES {{ number_format(($dailyCashFlow->count() ?? 0) > 0 ? ($cashInflows ?? 0) / $dailyCashFlow->count() : 0, 2) }}
                </p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg Daily Outflow</p>
                <p class="text-lg font-bold text-red-600">
                    KES {{ number_format(($dailyCashFlow->count() ?? 0) > 0 ? ($cashOutflows ?? 0) / $dailyCashFlow->count() : 0, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('cashflowChart');
        if (ctx) {
            const labels = {!! json_encode($dailyCashFlow->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d M');
            }) ?? []) !!};
            const inflow = {!! json_encode($dailyCashFlow->pluck('inflow') ?? []) !!};
            const outflow = {!! json_encode($dailyCashFlow->pluck('outflow') ?? []) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Inflow',
                            data: inflow,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Outflow',
                            data: outflow,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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
