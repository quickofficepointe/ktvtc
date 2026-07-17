@extends('ktvtc.finance.layouts.app')

@section('title', 'Revenue Report')
@section('subtitle', 'View revenue breakdown and trends')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>

    <a href="{{ route('finance.reports.export-financial', ['type' => 'revenue', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET" action="{{ route('finance.reports.revenue') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600 block mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600 block mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
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
        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600 break-words">
                KES {{ number_format($totalRevenue ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
            <p class="text-sm text-gray-500">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-600">
                {{ number_format(isset($dailyRevenue) ? $dailyRevenue->sum('transactions') : 0) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-4">
            <p class="text-sm text-gray-500">Average Daily Revenue</p>
            <p class="text-2xl font-bold text-purple-600 break-words">
                KES {{ number_format((isset($dailyRevenue) && $dailyRevenue->count() > 0) ? ($totalRevenue ?? 0) / $dailyRevenue->count() : 0, 2) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-line text-primary mr-2"></i>
            Daily Revenue Trend
        </h3>

        <div class="w-full" style="height: 280px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-credit-card text-primary mr-2"></i>
                Revenue by Payment Method
            </h3>

            <div class="space-y-4">
                @forelse($methodRevenue ?? [] as $method)
                    @php
                        $percentage = ($totalRevenue ?? 0) > 0 ? (($method->total ?? 0) / $totalRevenue) * 100 : 0;
                    @endphp

                    <div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm">
                            <span class="font-medium uppercase text-gray-800">
                                {{ $method->payment_method ?? 'N/A' }}
                            </span>

                            <span class="font-semibold text-gray-700">
                                KES {{ number_format($method->total ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden mt-2">
                            <div class="h-full rounded-full bg-primary transition-all duration-1000"
                                 style="width: {{ $percentage }}%"></div>
                        </div>

                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>{{ number_format($method->count ?? 0) }} transactions</span>
                            <span>{{ number_format($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-500">
                        <i class="fas fa-credit-card text-3xl text-gray-300 mb-2 block"></i>
                        <p>No revenue data available</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-day text-primary mr-2"></i>
                Daily Breakdown
            </h3>

            <div class="w-full overflow-x-auto max-h-96">
                <table class="min-w-[650px] w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="py-2 px-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Revenue</th>
                            <th class="py-2 px-3 text-right font-semibold text-gray-600">Transactions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($dailyRevenue ?? [] as $day)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                                </td>
                                <td class="py-2 px-3 text-right font-medium">
                                    KES {{ number_format($day->total ?? 0, 2) }}
                                </td>
                                <td class="py-2 px-3 text-right">
                                    {{ number_format($day->transactions ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(isset($dailyRevenue) && $dailyRevenue->count() > 0)
                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td class="py-2 px-3 font-bold">Total</td>
                            <td class="py-2 px-3 text-right font-bold">
                                KES {{ number_format($totalRevenue ?? 0, 2) }}
                            </td>
                            <td class="py-2 px-3 text-right font-bold">
                                {{ number_format($dailyRevenue->sum('transactions') ?? 0) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ✅ FIX: Use both DOMContentLoaded and window.onload for safety
    function initRevenueChart() {
        const ctx = document.getElementById('revenueChart');

        if (!ctx) {
            console.warn('Revenue chart canvas not found');
            return;
        }

        const labels = {!! json_encode(isset($dailyRevenue) ? $dailyRevenue->pluck('date')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d M');
        })->values() : []) !!};

        const data = {!! json_encode(isset($dailyRevenue) ? $dailyRevenue->pluck('total')->values() : []) !!};

        if (labels.length === 0 || data.length === 0) {
            console.warn('No data for revenue chart');
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (KES)',
                    data: data,
                    borderColor: 'rgba(185, 28, 28, 1)',
                    backgroundColor: 'rgba(185, 28, 28, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(185, 28, 28, 1)',
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
                            callback: function (value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Run on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initRevenueChart();

        // Animate progress bars
        document.querySelectorAll('.h-1\\.5.rounded-full.bg-gray-200 .h-full').forEach(function (bar) {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function () {
                bar.style.width = width;
            }, 100);
        });
    });

    // Also run on window load for safety
    window.addEventListener('load', function() {
        // Re-initialize if chart wasn't rendered
        if (typeof Chart !== 'undefined') {
            // Chart already initialized, skip
        }
    });
</script>
@endpush
