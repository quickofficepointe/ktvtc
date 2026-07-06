@extends('ktvtc.finance.layouts.app')

@section('title', 'Balance Sheet')
@section('subtitle', 'View financial position statement')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.reports.balance-sheet') }}" class="text-gray-600 hover:text-primary">Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Balance Sheet</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.export', ['type' => 'balance-sheet', 'date' => $asAt->format('Y-m-d')]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.reports.balance-sheet') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">As At Date</label>
                <input type="date" name="date" value="{{ $asAt->format('Y-m-d') }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Report Header -->
    <div class="finance-card p-4 text-center bg-gray-50">
        <p class="text-lg font-bold text-gray-800">Balance Sheet</p>
        <p class="text-sm text-gray-500">As At {{ $asAt->format('l, F j, Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assets -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-blue-600 text-lg mb-4 border-b pb-2">Assets</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Total Fees Collected</span>
                    <span class="font-semibold text-blue-600">KES {{ number_format($totalFeesCollected ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total Assets</span>
                    <span class="text-xl font-bold text-blue-600">KES {{ number_format($totalFeesCollected ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Liabilities -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-red-600 text-lg mb-4 border-b pb-2">Liabilities</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Outstanding Balance</span>
                    <span class="font-semibold text-red-600">KES {{ number_format($outstandingBalance ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total Liabilities</span>
                    <span class="text-xl font-bold text-red-600">KES {{ number_format($outstandingBalance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Equity -->
    <div class="finance-card p-6 bg-green-50 border-green-200">
        <h3 class="font-bold text-gray-800 text-lg mb-4 border-b border-green-200 pb-2">Equity</h3>
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-700">Net Position (Assets - Liabilities)</span>
                <p class="text-sm text-gray-500">Total collected fees minus outstanding balance</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-green-600">KES {{ number_format($equity ?? 0, 2) }}</p>
                <p class="text-sm text-green-600">{{ ($equity ?? 0) >= 0 ? 'Positive Equity' : 'Negative Equity' }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Balance Sheet Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 text-left font-bold text-gray-700">Account</th>
                        <th class="py-2 px-4 text-right font-bold text-gray-700">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-2 px-4 font-medium text-blue-600">ASSETS</td>
                        <td class="py-2 px-4 text-right"></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4 pl-8 text-gray-700">Total Fees Collected</td>
                        <td class="py-2 px-4 text-right font-medium">{{ number_format($totalFeesCollected ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b bg-gray-50">
                        <td class="py-2 px-4 pl-8 font-bold text-gray-800">Total Assets</td>
                        <td class="py-2 px-4 text-right font-bold text-blue-600">{{ number_format($totalFeesCollected ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4 font-medium text-red-600">LIABILITIES</td>
                        <td class="py-2 px-4 text-right"></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4 pl-8 text-gray-700">Outstanding Balance</td>
                        <td class="py-2 px-4 text-right font-medium text-red-600">{{ number_format($outstandingBalance ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b bg-gray-50">
                        <td class="py-2 px-4 pl-8 font-bold text-gray-800">Total Liabilities</td>
                        <td class="py-2 px-4 text-right font-bold text-red-600">{{ number_format($outstandingBalance ?? 0, 2) }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4 font-medium text-green-600">EQUITY</td>
                        <td class="py-2 px-4 text-right"></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4 pl-8 text-gray-700">Net Position</td>
                        <td class="py-2 px-4 text-right font-medium text-green-600">{{ number_format($equity ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="py-3 px-4 pl-8 font-bold text-gray-800">Total Equity</td>
                        <td class="py-3 px-4 text-right font-bold text-green-600">{{ number_format($equity ?? 0, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td class="py-3 px-4 font-bold text-gray-800">Total Liabilities + Equity</td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800">
                            {{ number_format(($outstandingBalance ?? 0) + ($equity ?? 0), 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm text-gray-500">Should equal Total Assets</td>
                        <td class="py-2 px-4 text-right text-sm text-gray-500">{{ number_format($totalFeesCollected ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endpush
