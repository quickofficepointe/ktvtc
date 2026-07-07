@extends('ktvtc.finance.layouts.app')

@section('title', 'Daily Report')
@section('subtitle', 'Daily transaction summary')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Daily Report</span>
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
    <!-- Date Selector -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date</label>
                <input type="date" name="date" value="{{ $date ?? now()->format('Y-m-d') }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
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
            <p class="text-xs text-gray-500">Transactions</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalTransactions ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Students</p>
            <p class="text-xl font-bold text-purple-600">{{ number_format($totalStudents ?? 0) }}</p>
        </div>
    </div>

    <!-- Daily Usage Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Amount Spent</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Transactions</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Last Transaction</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dailyUsage ?? [] as $usage)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ $usage->cardAccount->student_name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $usage->cardAccount->student_class ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-right font-bold">KES {{ number_format($usage->total_spent, 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ $usage->transaction_count }}</td>
                            <td class="px-3 py-2 text-sm">{{ $usage->last_transaction_at ? $usage->last_transaction_at->format('H:i') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-gray-500">No transactions on this date</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-3 py-2 font-bold">Total</td>
                        <td class="px-3 py-2 text-right font-bold">KES {{ number_format($totalSpent ?? 0, 2) }}</td>
                        <td class="px-3 py-2 text-right font-bold">{{ number_format($totalTransactions ?? 0) }}</td>
                        <td class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Top Spenders -->
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Top Spenders</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            @forelse($topSpenders ?? [] as $usage)
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500 truncate">{{ $usage->cardAccount->student_name ?? 'N/A' }}</p>
                    <p class="text-lg font-bold text-primary">KES {{ number_format($usage->total_spent, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $usage->transaction_count }} transactions</p>
                </div>
            @empty
                <p class="text-gray-500 text-sm col-span-5 text-center">No data available</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
