@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Reports')
@section('subtitle', 'View card system reports and analytics')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="finance-card p-3">
            <p class="text-xs text-gray-500">Total Cards</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($totalCards ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Balance</p>
            <p class="text-xl font-bold text-green-600">KES {{ number_format($totalBalance ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Students</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Active Students</p>
            <p class="text-xl font-bold text-purple-600">{{ number_format($activeStudents ?? 0) }}</p>
        </div>
    </div>

    <!-- Today's Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="finance-card p-4">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Today's Activity</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600 text-sm">Total Spent</span>
                    <span class="font-bold text-lg text-primary">KES {{ number_format($todayTransactions ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600 text-sm">Transactions</span>
                    <span class="font-bold text-lg text-blue-600">{{ number_format($todayCount ?? 0) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t">
                <a href="{{ route('finance.card-reports.daily') }}" class="text-primary hover:underline text-sm">
                    <i class="fas fa-arrow-right mr-1"></i> View Daily Report
                </a>
            </div>
        </div>

        <div class="finance-card p-4">
            <h3 class="font-bold text-gray-800 text-sm mb-3">This Month's Activity</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600 text-sm">Total Spent</span>
                    <span class="font-bold text-lg text-primary">KES {{ number_format($monthTransactions ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600 text-sm">Transactions</span>
                    <span class="font-bold text-lg text-blue-600">{{ number_format($monthCount ?? 0) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t">
                <a href="{{ route('finance.card-reports.monthly') }}" class="text-primary hover:underline text-sm">
                    <i class="fas fa-arrow-right mr-1"></i> View Monthly Report
                </a>
            </div>
        </div>
    </div>

    <!-- Report Links -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <a href="{{ route('finance.card-reports.students') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-user-graduate text-primary text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Student Report</h4>
                <p class="text-xs text-gray-500">All students with card details</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>

        <a href="{{ route('finance.card-reports.balances') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-wallet text-primary text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Balance Report</h4>
                <p class="text-xs text-gray-500">All card balances</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>

        <a href="{{ route('finance.card-reports.low-balance') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Low Balance</h4>
                <p class="text-xs text-gray-500">Students with low balances</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>

        <a href="{{ route('finance.card-reports.inactive') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-clock text-gray-500 text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Inactive Cards</h4>
                <p class="text-xs text-gray-500">Cards not used recently</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>

        <a href="{{ route('finance.card-reports.daily') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-calendar-day text-primary text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Daily Report</h4>
                <p class="text-xs text-gray-500">Daily transaction summary</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>

        <a href="{{ route('finance.card-reports.monthly') }}" class="finance-card p-4 hover:shadow-lg transition flex items-center justify-between group">
            <div>
                <i class="fas fa-calendar-alt text-primary text-xl mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-sm">Monthly Report</h4>
                <p class="text-xs text-gray-500">Monthly transaction summary</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition"></i>
        </a>
    </div>
</div>
@endsection
