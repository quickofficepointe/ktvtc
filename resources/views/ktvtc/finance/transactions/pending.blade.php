@extends('ktvtc.finance.layouts.app')

@section('title', 'Pending Transactions')
@section('subtitle', 'View and manage pending transactions')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition hover:shadow-md">
            <p class="text-sm text-gray-500">Pending Transactions</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($transactions->total() ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition hover:shadow-md">
            <p class="text-sm text-gray-500">Total Pending Amount</p>
            <p class="text-2xl font-bold text-yellow-600">KES {{ number_format($transactions->sum('amount') ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition hover:shadow-md">
            <p class="text-sm text-gray-500">M-Pesa Pending</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($transactions->where('payment_method', 'mpesa')->count() ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition hover:shadow-md">
            <p class="text-sm text-gray-500">Cash Pending</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($transactions->where('payment_method', 'cash')->count() ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <form method="GET" action="{{ route('finance.transactions.pending') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="kcb" {{ request('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                    <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Search</label>
                <div class="flex">
                    <input type="text" name="search" placeholder="Reference, Customer..." value="{{ request('search') }}" class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-r-lg transition-colors duration-200">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-3 flex justify-end">
            <a href="{{ route('finance.transactions.pending') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                <i class="fas fa-times mr-1"></i> Clear Filters
            </a>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-800 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Pending Transactions
                </h3>
                <p class="text-xs text-gray-500 mt-1">Showing {{ $transactions->count() ?? 0 }} of {{ $transactions->total() ?? 0 }} pending transactions</p>
            </div>
            <div>
                <a href="{{ route('finance.transactions.index') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                    <i class="fas fa-list mr-1"></i> View All
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Transaction #</th>
                        <th class="pb-2 font-semibold">Customer</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold">Method</th>
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-2">
                                <span class="font-medium text-primary">{{ $transaction->transaction_number }}</span>
                            </td>
                            <td class="py-2">
                                <span class="font-medium text-gray-800">{{ $transaction->sale->customer_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $transaction->sale->customer_phone ?? '' }}</span>
                            </td>
                            <td class="py-2 text-right font-semibold text-gray-800">KES {{ number_format($transaction->amount, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs uppercase px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">{{ $transaction->payment_method }}</span>
                            </td>
                            <td class="py-2 text-gray-600">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-2 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="text-primary hover:text-primary-dark transition-colors" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="verifyTransaction({{ $transaction->id }})" class="text-green-600 hover:text-green-800 transition-colors" title="Verify">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button onclick="reverseTransaction({{ $transaction->id }})" class="text-red-600 hover:text-red-800 transition-colors" title="Reverse">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <i class="fas fa-check-circle text-3xl text-green-500"></i>
                                    </div>
                                    <p class="font-medium text-gray-600">No pending transactions</p>
                                    <p class="text-sm text-gray-400 mt-1">All transactions have been processed</p>
                                </div>
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

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Verify Transaction</h3>
            <button onclick="closeModal('verifyModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="verifyForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Notes (Optional)</label>
                <textarea name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" rows="3" placeholder="Add verification notes..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('verifyModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">Verify Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Transaction</h3>
            <button onclick="closeModal('reverseModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="reverseForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Reason for Reversal <span class="text-red-500">*</span></label>
                <textarea name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" rows="3" placeholder="Enter reason for reversal..." required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reverseModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">Reverse Transaction</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function verifyTransaction(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');
        form.action = '/finance/transactions/' + id + '/verify';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function reverseTransaction(id) {
        const modal = document.getElementById('reverseModal');
        const form = document.getElementById('reverseForm');
        form.action = '/finance/transactions/' + id + '/reverse';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
            closeModal('reverseModal');
        }
    });

    document.querySelectorAll('.bg-black\\/60').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endpush
