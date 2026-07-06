@extends('ktvtc.finance.layouts.app')

@section('title', 'Expenses Report')
@section('subtitle', 'View expense breakdown and trends')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.reports.expenses') }}" class="text-gray-600 hover:text-primary">Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Expenses</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.export', ['type' => 'expenses', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.reports.expenses') }}" class="flex flex-wrap items-end gap-4">
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
        <div class="finance-card p-4 bg-red-50 border-red-200">
            <p class="text-sm text-gray-600">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">KES {{ number_format($purchaseExpenses ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-orange-50 border-orange-200">
            <p class="text-sm text-gray-600">Purchase Orders</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($monthlyExpenses->sum('count') ?? 0) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-sm text-gray-600">Average per PO</p>
            <p class="text-2xl font-bold text-purple-600">
                KES {{ number_format(($monthlyExpenses->sum('count') ?? 0) > 0 ? ($purchaseExpenses ?? 0) / $monthlyExpenses->sum('count') : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Monthly Expense Trend</h3>
        <canvas id="expenseChart" height="250"></canvas>
    </div>

    <!-- Supplier Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Top Suppliers</h3>
            <div class="space-y-3">
                @forelse($supplierExpenses ?? [] as $supplier)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium">{{ $supplier->supplier->supplier_name ?? 'N/A' }}</span>
                            <span>KES {{ number_format($supplier->total, 2) }}</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="fill bg-orange-500" style="width: {{ ($purchaseExpenses > 0) ? ($supplier->total / $purchaseExpenses) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No supplier data available</p>
                @endforelse
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Monthly Breakdown</h3>
            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Month</th>
                            <th class="pb-2 font-semibold text-right">Amount</th>
                            <th class="pb-2 font-semibold text-right">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyExpenses ?? [] as $month)
                            <tr class="border-b border-gray-100">
                                <td class="py-2">{{ $month->month }}</td>
                                <td class="py-2 text-right font-medium">KES {{ number_format($month->amount, 2) }}</td>
                                <td class="py-2 text-right">{{ $month->count }}</td>
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
                            <td class="py-2 text-right font-bold">KES {{ number_format($purchaseExpenses ?? 0, 2) }}</td>
                            <td class="py-2 text-right font-bold">{{ number_format($monthlyExpenses->sum('count') ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Expense Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Purchase Orders</p>
                <p class="text-2xl font-bold text-orange-600">{{ number_format($monthlyExpenses->sum('count') ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Total Amount</p>
                <p class="text-2xl font-bold text-red-600">KES {{ number_format($purchaseExpenses ?? 0, 2) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg per Month</p>
                <p class="text-2xl font-bold text-blue-600">
                    KES {{ number_format(($monthlyExpenses->count() ?? 0) > 0 ? ($purchaseExpenses ?? 0) / $monthlyExpenses->count() : 0, 2) }}
                </p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Max Monthly</p>
                <p class="text-2xl font-bold text-purple-600">
                    KES {{ number_format($monthlyExpenses->max('amount') ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('expenseChart');
        if (ctx) {
            const labels = {!! json_encode($monthlyExpenses->pluck('month') ?? []) !!};
            const data = {!! json_encode($monthlyExpenses->pluck('amount') ?? []) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Expenses (KES)',
                        data: data,
                        backgroundColor: 'rgba(234, 88, 12, 0.7)',
                        borderColor: 'rgba(234, 88, 12, 1)',
                        borderWidth: 2,
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
