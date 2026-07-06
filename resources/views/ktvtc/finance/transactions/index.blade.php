@extends('ktvtc.finance.layouts.app')

@section('title', 'All Transactions')
@section('subtitle', 'View and manage all financial transactions')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.transactions.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
    <a href="{{ route('finance.transactions.pending') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-clock mr-2"></i> Pending
        @if(isset($stats['pending_count']) && $stats['pending_count'] > 0)
            <span class="ml-2 bg-white text-yellow-600 px-2 py-0.5 rounded-full text-xs font-bold">{{ $stats['pending_count'] }}</span>
        @endif
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Amount</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_count'] ?? 0) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">M-Pesa</p>
            <p class="text-xl font-bold text-purple-600">KES {{ number_format($stats['mpesa_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($stats['mpesa_count'] ?? 0) }} transactions</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Cash</p>
            <p class="text-xl font-bold text-orange-600">KES {{ number_format($stats['cash_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($stats['cash_count'] ?? 0) }} transactions</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date Range</label>
                <select name="date_range" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="toggleCustomDates(this)">
                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div id="customDates" style="{{ request('date_range') == 'custom' ? 'display:block;' : 'display:none;' }}" class="md:col-span-2 grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Methods</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <div class="flex">
                    <input type="text" name="search" placeholder="Invoice, customer..." value="{{ request('search') }}" class="w-full px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">Transaction #</th>
                        <th class="text-left py-3 px-4">Invoice</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Method</th>
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
                                <span class="text-xs text-gray-500 block">{{ $transaction->sale->customer_phone ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold">KES {{ number_format($transaction->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $transaction->payment_method }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($transaction->status === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Failed</span>
                                @elseif($transaction->status === 'reversed')
                                    <span class="status-badge status-reversed"><i class="fas fa-undo mr-1"></i> Reversed</span>
                                @else
                                    <span class="status-badge status-pending">{{ ucfirst($transaction->status) }}</span>
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
                                    @if($transaction->status === 'pending')
                                        <button onclick="verifyTransaction({{ $transaction->id }})" class="text-green-600 hover:text-green-800" title="Verify">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    @if($transaction->status === 'completed')
                                        <button onclick="reverseTransaction({{ $transaction->id }})" class="text-red-600 hover:text-red-800" title="Reverse">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-2 block"></i>
                                No transactions found
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

    <!-- Summary Footer -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-xs text-gray-500">Completed</p>
            <p class="text-lg font-bold text-green-600">{{ number_format($stats['total_count'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-lg font-bold text-yellow-600">{{ number_format($stats['pending_count'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-xs text-gray-500">Failed</p>
            <p class="text-lg font-bold text-red-600">{{ number_format($stats['failed_count'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-xs text-gray-500">Average</p>
            <p class="text-lg font-bold text-blue-600">
                KES {{ number_format(($stats['total_count'] ?? 0) > 0 ? ($stats['total_amount'] ?? 0) / ($stats['total_count'] ?? 1) : 0, 2) }}
            </p>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Verify Transaction</h3>
            <button onclick="closeModal('verifyModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="verifyForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea name="notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('verifyModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Verify Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Transaction</h3>
            <button onclick="closeModal('reverseModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reverseForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Reason for Reversal *</label>
                <textarea name="reason" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reverseModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reverse Transaction</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleCustomDates(select) {
        const customDates = document.getElementById('customDates');
        if (select.value === 'custom') {
            customDates.style.display = 'grid';
        } else {
            customDates.style.display = 'none';
        }
    }

    function verifyTransaction(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');
        form.action = `/finance/transactions/${id}/verify`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function reverseTransaction(id) {
        const modal = document.getElementById('reverseModal');
        const form = document.getElementById('reverseForm');
        form.action = `/finance/transactions/${id}/reverse`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
            closeModal('reverseModal');
        }
    });
</script>
@endpush
