@extends('ktvtc.finance.layouts.app')

@section('title', 'Monthly Report')
@section('subtitle', 'Monthly transaction summary')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Monthly Report</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.card-reports.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Month Selector -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Month</label>
                <select name="month" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (request('month', now()->month) == $m) ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Year</label>
                <select name="year" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ (request('year', now()->year) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm">
                    <i class="fas fa-search mr-2"></i> View
                </button>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Spent</p>
            <p class="text-xl font-bold text-green-600">KES {{ number_format($totalSpent ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Transactions</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalTransactions ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Average Daily</p>
            <p class="text-xl font-bold text-purple-600">KES {{ number_format($averageDaily ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Daily Trend</h3>
        <canvas id="dailyTrendChart" height="200"></canvas>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Date</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Amount Spent</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Transactions</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Active Cards</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dailyTotals ?? [] as $day)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                            <td class="px-3 py-2 text-right font-medium">KES {{ number_format($day->total_spent, 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ $day->transaction_count }}</td>
                            <td class="px-3 py-2 text-right">{{ $day->active_cards }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-gray-500">No data for this month</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td class="px-3 py-2 font-bold">Total</td>
                        <td class="px-3 py-2 text-right font-bold">KES {{ number_format($totalSpent ?? 0, 2) }}</td>
                        <td class="px-3 py-2 text-right font-bold">{{ number_format($totalTransactions ?? 0) }}</td>
                        <td class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dailyTrendChart');
        if (ctx) {
            const labels = {!! json_encode($dailyTotals->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d M');
            }) ?? []) !!};
            const data = {!! json_encode($dailyTotals->pluck('total_spent') ?? []) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Spending (KES)',
                        data: data,
                        backgroundColor: 'rgba(185, 28, 28, 0.7)',
                        borderColor: 'rgba(185, 28, 28, 1)',
                        borderWidth: 1,
                        borderRadius: 4
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
