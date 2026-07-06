@extends('ktvtc.finance.layouts.app')

@section('title', 'M-Pesa Transactions')
@section('subtitle', 'View all M-Pesa payment transactions')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.transactions.index') }}" class="text-gray-600 hover:text-primary">Transactions</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">M-Pesa</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total M-Pesa Payments</p>
            <p class="text-2xl font-bold text-purple-600">KES {{ number_format($transactions->sum('amount') ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($transactions->count()) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Average Payment</p>
            <p class="text-2xl font-bold text-green-600">
                KES {{ number_format($transactions->count() > 0 ? $transactions->sum('amount') / $transactions->count() : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">Reference</th>
                        <th class="text-left py-3 px-4">Sale</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Receipt</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium text-primary">{{ $transaction->transaction_number }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm">{{ $transaction->sale->invoice_number ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium">{{ $transaction->sale->customer_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $transaction->phone_number ?? $transaction->sale->customer_phone ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold">KES {{ number_format($transaction->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="text-sm">{{ $transaction->mpesa_receipt ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($transaction->status === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @else
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> {{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($transaction->status === 'completed')
                                        <a href="{{ route('finance.transactions.receipt', $transaction) }}" class="text-gray-500 hover:text-gray-700" title="Receipt" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-mobile-alt text-4xl text-gray-300 mb-2 block"></i>
                                No M-Pesa transactions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endpush
