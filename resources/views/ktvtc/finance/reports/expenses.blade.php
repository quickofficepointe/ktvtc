@extends('ktvtc.finance.layouts.app')

@section('title', 'Expenses Report')
@section('subtitle', 'View expense breakdown and trends')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>

    <a href="{{ route('finance.reports.export-financial', ['type' => 'expenses', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET" action="{{ route('finance.reports.expenses') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600 block mb-1">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600 block mb-1">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="md:col-span-4">
                <button type="submit" class="w-full md:w-auto px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold text-sm">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4">
            <p class="text-sm text-gray-500">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600 break-words">
                KES {{ number_format($purchaseExpenses ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-4">
            <p class="text-sm text-gray-500">Purchase Orders</p>
            <p class="text-2xl font-bold text-orange-600">
                {{ number_format(isset($monthlyExpenses) ? $monthlyExpenses->sum('count') : 0) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-4">
            <p class="text-sm text-gray-500">Average per PO</p>
            <p class="text-2xl font-bold text-purple-600 break-words">
                KES {{ number_format((isset($monthlyExpenses) && $monthlyExpenses->sum('count') > 0) ? ($purchaseExpenses ?? 0) / $monthlyExpenses->sum('count') : 0, 2) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-line text-primary mr-2"></i>
            Monthly Expense Trend
        </h3>

        <div class="w-full" style="height: 280px;">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-truck text-primary mr-2"></i>
                Top Suppliers
            </h3>

            <div class="space-y-4">
                @forelse($supplierExpenses ?? [] as $supplier)
                    @php
                        $percentage = ($purchaseExpenses ?? 0) > 0 ? (($supplier->total ?? 0) / $purchaseExpenses) * 100 : 0;
                    @endphp

                    <div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm">
                            <span class="font-medium text-gray-800">
                                {{ $supplier->supplier->supplier_name ?? 'N/A' }}
                            </span>
                            <span class="font-semibold text-gray-700">
                                KES {{ number_format($supplier->total ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden mt-2">
                            <div class="h-full rounded-full bg-orange-500 transition-all duration-1000"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-500">
                        <i class="fas fa-truck text-3xl text-gray-300 mb-2 block"></i>
                        <p>No supplier data available</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                Monthly Breakdown
            </h3>

            <div class="w-full overflow-x-auto max-h-96">
                <table class="min-w-[650px] w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="py-2 px-3 text-left font-semibold text-gray-600">Month</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Amount</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Orders</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($monthlyExpenses ?? [] as $month)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 whitespace-nowrap">{{ $month->month }}</td>
                                <td class="py-2 px-3 text-right font-medium">
                                    KES {{ number_format($month->amount ?? 0, 2) }}
                                </td>
                                <td class="py-2 px-3 text-right">
                                    {{ number_format($month->count ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(isset($monthlyExpenses) && $monthlyExpenses->count() > 0)
                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td class="py-2 px-3 font-bold">Total</td>
                            <td class="py-2 px-3 text-right font-bold">
                                KES {{ number_format($purchaseExpenses ?? 0, 2) }}
                            </td>
                            <td class="py-2 px-3 text-right font-bold">
                                {{ number_format($monthlyExpenses->sum('count') ?? 0) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-bar text-primary mr-2"></i>
            Summary Statistics
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Period</p>
                <p class="text-sm font-semibold text-gray-800">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-lg font-bold text-gray-800">
                    {{ number_format(isset($monthlyExpenses) ? $monthlyExpenses->sum('count') : 0) }}
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg Monthly</p>
                <p class="text-lg font-bold text-blue-600 break-words">
                    KES {{ number_format((isset($monthlyExpenses) && $monthlyExpenses->count() > 0) ? ($purchaseExpenses ?? 0) / $monthlyExpenses->count() : 0, 2) }}
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Max Monthly</p>
                <p class="text-lg font-bold text-purple-600 break-words">
                    KES {{ number_format(isset($monthlyExpenses) ? ($monthlyExpenses->max('amount') ?? 0) : 0, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('expenseChart');

        if (!ctx) {
            return;
        }

        const labels = {!! json_encode(isset($monthlyExpenses) ? $monthlyExpenses->pluck('month')->values() : []) !!};
        const data = {!! json_encode(isset($monthlyExpenses) ? $monthlyExpenses->pluck('amount')->values() : []) !!};

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
                            callback: function (value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        document.querySelectorAll('.h-1\\.5.rounded-full.bg-gray-200 .h-full').forEach(function (bar) {
            const width = bar.style.width;
            bar.style.width = '0%';

            setTimeout(function () {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush
