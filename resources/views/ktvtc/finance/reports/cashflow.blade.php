@extends('ktvtc.finance.layouts.app')

@section('title', 'Cash Flow Report')
@section('subtitle', 'View cash flow statement')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>

    <a href="{{ route('finance.reports.export-financial', ['type' => 'cashflow', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET" action="{{ route('finance.reports.cashflow') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
            <p class="text-sm text-gray-500">Cash Inflows</p>
            <p class="text-2xl font-bold text-green-600 break-words">KES {{ number_format($cashInflows ?? 0, 2) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4">
            <p class="text-sm text-gray-500">Cash Outflows</p>
            <p class="text-2xl font-bold text-red-600 break-words">KES {{ number_format($cashOutflows ?? 0, 2) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
            <p class="text-sm text-gray-500">Net Cash Flow</p>
            <p class="text-2xl font-bold break-words {{ ($netCashFlow ?? 0) >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                {{ ($netCashFlow ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netCashFlow ?? 0), 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Cash Position</p>
            <p class="text-2xl font-bold {{ ($netCashFlow ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ ($netCashFlow ?? 0) >= 0 ? 'Positive' : 'Negative' }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-area text-primary mr-2"></i>
            Daily Cash Flow Trend
        </h3>

        <div class="w-full" style="height: 280px;">
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                <i class="fas fa-file-invoice text-primary mr-2"></i>
                Cash Flow Statement
            </h3>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <span class="text-gray-700">Cash Inflows (Collections)</span>
                    <span class="font-semibold text-green-600">KES {{ number_format($cashInflows ?? 0, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <span class="text-gray-700">Cash Outflows (Purchases)</span>
                    <span class="font-semibold text-red-600">KES {{ number_format($cashOutflows ?? 0, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-1 pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Net Cash Flow</span>
                    <span class="text-xl font-bold {{ ($netCashFlow ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ ($netCashFlow ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netCashFlow ?? 0), 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-list text-primary mr-2"></i>
                Daily Breakdown
            </h3>

            <div class="w-full overflow-x-auto max-h-96">
                <table class="min-w-[750px] w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="py-2 px-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Inflow</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Outflow</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Net</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($dailyCashFlow ?? [] as $day)
                            @php
                                $net = ($day->inflow ?? 0) - ($day->outflow ?? 0);
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                <td class="py-2 px-3 text-right text-green-600 font-medium">KES {{ number_format($day->inflow ?? 0, 2) }}</td>
                                <td class="py-2 px-3 text-right text-red-600 font-medium">KES {{ number_format($day->outflow ?? 0, 2) }}</td>
                                <td class="py-2 px-3 text-right font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    {{ $net >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($net), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(isset($dailyCashFlow) && $dailyCashFlow->count() > 0)
                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td class="py-2 px-3 font-bold">Total</td>
                            <td class="py-2 px-3 text-right font-bold text-green-600">KES {{ number_format($dailyCashFlow->sum('inflow') ?? 0, 2) }}</td>
                            <td class="py-2 px-3 text-right font-bold text-red-600">KES {{ number_format($dailyCashFlow->sum('outflow') ?? 0, 2) }}</td>
                            <td class="py-2 px-3 text-right font-bold text-blue-600">
                                KES {{ number_format(($dailyCashFlow->sum('inflow') ?? 0) - ($dailyCashFlow->sum('outflow') ?? 0), 2) }}
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

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
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
                <p class="text-sm text-gray-500">Days</p>
                <p class="text-lg font-bold text-gray-800">
                    {{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }}
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg Daily Inflow</p>
                <p class="text-lg font-bold text-green-600 break-words">
                    KES {{ number_format((isset($dailyCashFlow) && $dailyCashFlow->count() > 0) ? ($cashInflows ?? 0) / $dailyCashFlow->count() : 0, 2) }}
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Avg Daily Outflow</p>
                <p class="text-lg font-bold text-red-600 break-words">
                    KES {{ number_format((isset($dailyCashFlow) && $dailyCashFlow->count() > 0) ? ($cashOutflows ?? 0) / $dailyCashFlow->count() : 0, 2) }}
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
        const ctx = document.getElementById('cashflowChart');

        if (!ctx) {
            return;
        }

        const labels = {!! json_encode(isset($dailyCashFlow) ? $dailyCashFlow->pluck('date')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d M');
        })->values() : []) !!};

        const inflow = {!! json_encode(isset($dailyCashFlow) ? $dailyCashFlow->pluck('inflow')->values() : []) !!};
        const outflow = {!! json_encode(isset($dailyCashFlow) ? $dailyCashFlow->pluck('outflow')->values() : []) !!};

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
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 16
                        }
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
    });
</script>
@endpush
