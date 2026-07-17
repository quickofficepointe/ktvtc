@extends('ktvtc.finance.layouts.app')

@section('title', 'Profit & Loss Report')
@section('subtitle', 'View financial profit and loss statement')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>

    <a href="{{ route('finance.reports.export-financial', ['type' => 'profit-loss', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET" action="{{ route('finance.reports.profit-loss') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
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

    <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-100 p-4 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <p class="text-lg font-bold text-gray-800">Profit & Loss Statement</p>
        <p class="text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 via-green-400 to-green-500"></div>

            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                <i class="fas fa-coins text-green-600 mr-2"></i>
                Revenue
            </h3>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                    <span class="text-gray-700">Student Fees</span>
                    <span class="font-semibold text-gray-800">KES {{ number_format($feeRevenue ?? 0, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                    <span class="text-gray-700">Cafeteria Sales</span>
                    <span class="font-semibold text-gray-800">KES {{ number_format($salesRevenue ?? 0, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total Revenue</span>
                    <span class="text-xl font-bold text-green-600 break-words">KES {{ number_format($totalRevenue ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-red-400 to-red-500"></div>

            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                <i class="fas fa-boxes text-red-600 mr-2"></i>
                Cost of Goods Sold
            </h3>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                    <span class="text-gray-700">COGS (Cafeteria)</span>
                    <span class="font-semibold text-red-600">KES {{ number_format($cogs ?? 0, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total COGS</span>
                    <span class="text-xl font-bold text-red-600 break-words">KES {{ number_format($cogs ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 via-green-400 to-green-500"></div>

        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Gross Profit</h3>
                <p class="text-sm text-gray-600">Revenue - COGS</p>
            </div>

            <div class="md:text-right">
                <p class="text-2xl sm:text-3xl font-bold text-green-600 break-words">
                    KES {{ number_format($grossProfit ?? 0, 2) }}
                </p>
                <p class="text-sm text-green-600">
                    Gross Margin: {{ number_format($grossMargin ?? 0, 1) }}%
                </p>
            </div>
        </div>

        <div class="mt-3 h-1.5 rounded-full bg-gray-200 overflow-hidden">
            <div class="h-full rounded-full bg-green-500 transition-all duration-1000"
                 style="width: {{ min(max($grossMargin ?? 0, 0), 100) }}%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-orange-400 to-orange-500"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-receipt text-orange-600 mr-2"></i>
            Operating Expenses
        </h3>

        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                <span class="text-gray-700">Purchase Orders</span>
                <span class="font-semibold text-orange-600">KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 pt-3 border-t-2 border-gray-300">
                <span class="font-bold text-gray-800">Total Expenses</span>
                <span class="text-xl font-bold text-orange-600 break-words">KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    @php
        $netTheme = ($netProfit ?? 0) >= 0 ? 'blue' : 'red';
    @endphp

    <div class="rounded-xl shadow-sm border p-4 sm:p-6 relative overflow-hidden {{ ($netProfit ?? 0) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200' }}">
        @if(($netProfit ?? 0) >= 0)
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-500"></div>
        @else
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-red-400 to-red-500"></div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">
                    Net {{ ($netProfit ?? 0) >= 0 ? 'Profit' : 'Loss' }}
                </h3>
                <p class="text-sm text-gray-600">Gross Profit - Expenses</p>
            </div>

            <div class="md:text-right">
                <p class="text-2xl sm:text-3xl font-bold break-words {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    {{ ($netProfit ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netProfit ?? 0), 2) }}
                </p>

                <p class="text-sm {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Net Margin: {{ number_format($netMargin ?? 0, 1) }}%
                </p>
            </div>
        </div>

        <div class="mt-4">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm text-gray-600">
                <span>Revenue: KES {{ number_format($totalRevenue ?? 0, 2) }}</span>
                <span>Expenses: KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-table text-primary mr-2"></i>
            Summary
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-[700px] w-full text-sm">
                <tbody>
                    <tr class="border-b">
                        <td class="py-3 px-3 font-medium text-gray-700">Total Revenue</td>
                        <td class="py-3 px-3 text-right font-semibold text-green-600">
                            KES {{ number_format($totalRevenue ?? 0, 2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="py-3 px-3 font-medium text-gray-700">Cost of Goods Sold</td>
                        <td class="py-3 px-3 text-right font-semibold text-red-600">
                            KES {{ number_format($cogs ?? 0, 2) }}
                        </td>
                    </tr>

                    <tr class="border-b bg-gray-50">
                        <td class="py-3 px-3 font-bold text-gray-800">Gross Profit</td>
                        <td class="py-3 px-3 text-right font-bold text-green-600">
                            KES {{ number_format($grossProfit ?? 0, 2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="py-3 px-3 font-medium text-gray-700">Operating Expenses</td>
                        <td class="py-3 px-3 text-right font-semibold text-orange-600">
                            KES {{ number_format($expenses ?? 0, 2) }}
                        </td>
                    </tr>

                    <tr class="{{ ($netProfit ?? 0) >= 0 ? 'bg-blue-50' : 'bg-red-50' }}">
                        <td class="py-3 px-3 font-bold text-gray-800">
                            Net {{ ($netProfit ?? 0) >= 0 ? 'Profit' : 'Loss' }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ ($netProfit ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netProfit ?? 0), 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="py-3 px-3 font-medium text-gray-700">Gross Margin</td>
                        <td class="py-3 px-3 text-right font-semibold">
                            {{ number_format($grossMargin ?? 0, 1) }}%
                        </td>
                    </tr>

                    <tr>
                        <td class="py-3 px-3 font-medium text-gray-700">Net Margin</td>
                        <td class="py-3 px-3 text-right font-semibold">
                            {{ number_format($netMargin ?? 0, 1) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.h-1\\.5.rounded-full.bg-gray-200 .h-full').forEach(function (bar) {
            const width = bar.style.width;
            bar.style.width = '0%';

            setTimeout(function () {
                bar.style.width = width;
            }, 200);
        });
    });
</script>
@endpush
