@extends('ktvtc.finance.layouts.app')

@section('title', 'Profit & Loss Report')
@section('subtitle', 'View financial profit and loss statement')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.reports.profit-loss') }}" class="text-gray-600 hover:text-primary">Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Profit & Loss</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.export', ['type' => 'profit-loss', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.reports.profit-loss') }}" class="flex flex-wrap items-end gap-4">
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

    <!-- Report Period -->
    <div class="finance-card p-4 text-center bg-gray-50">
        <p class="text-lg font-bold text-gray-800">Profit & Loss Statement</p>
        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Section -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-lg mb-4 border-b pb-2">Revenue</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Student Fees</span>
                    <span class="font-semibold text-gray-800">KES {{ number_format($feeRevenue ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Cafeteria Sales</span>
                    <span class="font-semibold text-gray-800">KES {{ number_format($salesRevenue ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total Revenue</span>
                    <span class="text-xl font-bold text-green-600">KES {{ number_format($totalRevenue ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Cost of Goods Sold -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-lg mb-4 border-b pb-2">Cost of Goods Sold</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">COGS (Cafeteria)</span>
                    <span class="font-semibold text-red-600">KES {{ number_format($cogs ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total COGS</span>
                    <span class="text-xl font-bold text-red-600">KES {{ number_format($cogs ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gross Profit -->
    <div class="finance-card p-6 bg-green-50 border-green-200">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Gross Profit</h3>
                <p class="text-sm text-gray-600">Revenue - COGS</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-green-600">KES {{ number_format($grossProfit ?? 0, 2) }}</p>
                <p class="text-sm text-green-600">Gross Margin: {{ number_format($grossMargin ?? 0, 1) }}%</p>
            </div>
        </div>
        <div class="mt-3 progress-bar">
            <div class="fill bg-green-500" style="width: {{ min($grossMargin ?? 0, 100) }}%"></div>
        </div>
    </div>

    <!-- Expenses -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4 border-b pb-2">Operating Expenses</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Purchase Orders</span>
                <span class="font-semibold text-orange-600">KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                <span class="font-bold text-gray-800">Total Expenses</span>
                <span class="text-xl font-bold text-orange-600">KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Net Profit -->
    <div class="finance-card p-6 {{ ($netProfit ?? 0) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200' }}">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Net {{ ($netProfit ?? 0) >= 0 ? 'Profit' : 'Loss' }}</h3>
                <p class="text-sm text-gray-600">Gross Profit - Expenses</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    {{ ($netProfit ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netProfit ?? 0), 2) }}
                </p>
                <p class="text-sm {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Net Margin: {{ number_format($netMargin ?? 0, 1) }}%
                </p>
            </div>
        </div>
        <div class="mt-3">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Revenue: KES {{ number_format($totalRevenue ?? 0, 2) }}</span>
                <span>Expenses: KES {{ number_format($expenses ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b">
                        <td class="py-2 font-medium text-gray-700">Total Revenue</td>
                        <td class="py-2 text-right font-semibold text-green-600">KES {{ number_format($totalRevenue ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 font-medium text-gray-700">Cost of Goods Sold</td>
                        <td class="py-2 text-right font-semibold text-red-600">KES {{ number_format($cogs ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b bg-gray-50">
                        <td class="py-2 font-bold text-gray-800">Gross Profit</td>
                        <td class="py-2 text-right font-bold text-green-600">KES {{ number_format($grossProfit ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 font-medium text-gray-700">Operating Expenses</td>
                        <td class="py-2 text-right font-semibold text-orange-600">KES {{ number_format($expenses ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-{{ ($netProfit ?? 0) >= 0 ? 'blue-50' : 'red-50' }}">
                        <td class="py-3 font-bold text-gray-800">Net {{ ($netProfit ?? 0) >= 0 ? 'Profit' : 'Loss' }}</td>
                        <td class="py-3 text-right font-bold text-{{ ($netProfit ?? 0) >= 0 ? 'blue' : 'red' }}-600">
                            {{ ($netProfit ?? 0) >= 0 ? 'KES ' : '-KES ' }}{{ number_format(abs($netProfit ?? 0), 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium text-gray-700">Gross Margin</td>
                        <td class="py-2 text-right font-semibold">{{ number_format($grossMargin ?? 0, 1) }}%</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium text-gray-700">Net Margin</td>
                        <td class="py-2 text-right font-semibold">{{ number_format($netMargin ?? 0, 1) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Animate progress bars on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.progress-bar .fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 200);
        });
    });
</script>
@endpush
