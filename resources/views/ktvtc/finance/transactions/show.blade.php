@extends('ktvtc.finance.layouts.app')

@section('title', 'Transaction Details')
@section('subtitle', 'View transaction details and information')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.transactions.index') }}" class="text-gray-600 hover:text-primary">Transactions</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Transaction Details</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    @if($transaction->status === 'completed')
        {{-- ✅ FIXED: Use 'print' instead of 'receipt' --}}
        <a href="{{ route('finance.transactions.print', $transaction) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </a>
    @endif
    @if($transaction->status === 'pending')
        <form method="POST" action="{{ route('finance.transactions.verify', $transaction) }}" class="inline">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
                <i class="fas fa-check-circle mr-2"></i> Verify
            </button>
        </form>
    @endif
    @if($transaction->status === 'completed')
        <button onclick="openReverseModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
            <i class="fas fa-undo mr-2"></i> Reverse
        </button>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Transaction Information -->
    <div class="lg:col-span-2 finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Transaction Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Transaction Number</p>
                <p class="font-semibold text-primary">{{ $transaction->transaction_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Amount</p>
                <p class="text-2xl font-bold text-gray-800">KES {{ number_format($transaction->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Payment Method</p>
                <p><span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $transaction->payment_method }}</span></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
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
            </div>
            <div>
                <p class="text-xs text-gray-500">Currency</p>
                <p class="font-medium">{{ $transaction->currency ?? 'KES' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Created At</p>
                <p class="font-medium">{{ $transaction->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            @if($transaction->completed_at)
            <div>
                <p class="text-xs text-gray-500">Completed At</p>
                <p class="font-medium">{{ $transaction->completed_at->format('d M Y, H:i:s') }}</p>
            </div>
            @endif
            @if($transaction->mpesa_receipt)
            <div>
                <p class="text-xs text-gray-500">M-Pesa Receipt</p>
                <p class="font-medium">{{ $transaction->mpesa_receipt }}</p>
            </div>
            @endif
            @if($transaction->phone_number)
            <div>
                <p class="text-xs text-gray-500">Phone Number</p>
                <p class="font-medium">{{ $transaction->phone_number }}</p>
            </div>
            @endif
        </div>
        @if($transaction->notes)
            <div class="mt-4 pt-4 border-t">
                <p class="text-xs text-gray-500">Notes</p>
                <p class="text-gray-700">{{ $transaction->notes }}</p>
            </div>
        @endif
        @if($transaction->status === 'reversed' && $transaction->reversal_reason)
            <div class="mt-4 pt-4 border-t border-red-200 bg-red-50 p-3 rounded-lg">
                <p class="text-xs text-red-600 font-semibold">Reversal Reason</p>
                <p class="text-red-700">{{ $transaction->reversal_reason }}</p>
            </div>
        @endif
    </div>

    <!-- Sale Information -->
    <div class="space-y-6">
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Sale Information</h3>
            @if($transaction->sale)
                <div class="space-y-2">
                    <p class="text-sm"><span class="text-gray-500">Invoice:</span> <span class="font-medium">{{ $transaction->sale->invoice_number ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Customer:</span> <span class="font-medium">{{ $transaction->sale->customer_name ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Phone:</span> <span class="font-medium">{{ $transaction->sale->customer_phone ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Shop:</span> <span class="font-medium">{{ $transaction->sale->shop->shop_name ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Sale Type:</span> <span class="font-medium">{{ $transaction->sale->sale_type ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Total Items:</span> <span class="font-medium">{{ $transaction->sale->total_items ?? 0 }}</span></p>
                </div>
                <div class="mt-3 pt-3 border-t">
                    <a href="#" class="text-primary hover:underline text-sm">
                        <i class="fas fa-eye mr-1"></i> View Sale Details
                    </a>
                </div>
            @else
                <p class="text-gray-500 text-sm">No sale associated with this transaction</p>
            @endif
        </div>

        <!-- Reconciliation Info -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Reconciliation Status</h3>
            <div class="space-y-2">
                <p class="text-sm">
                    <span class="text-gray-500">Reconciled:</span>
                    @if($transaction->is_reconciled ?? false)
                        <span class="text-green-600 font-medium"><i class="fas fa-check-circle mr-1"></i> Yes</span>
                    @else
                        <span class="text-orange-600 font-medium"><i class="fas fa-clock mr-1"></i> Pending</span>
                    @endif
                </p>
                @if($transaction->is_reconciled ?? false)
                    <p class="text-sm"><span class="text-gray-500">Reconciled By:</span> <span class="font-medium">{{ $transaction->reconciled_by ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Reconciled At:</span> <span class="font-medium">{{ $transaction->reconciled_at ? $transaction->reconciled_at->format('d M Y, H:i') : 'N/A' }}</span></p>
                    @if($transaction->reconciliation_notes)
                        <p class="text-sm"><span class="text-gray-500">Notes:</span> <span class="font-medium">{{ $transaction->reconciliation_notes }}</span></p>
                    @endif
                @endif
            </div>
            @if(!($transaction->is_reconciled ?? false) && $transaction->status === 'completed')
                <div class="mt-3 pt-3 border-t">
                    <button onclick="reconcileTransaction({{ $transaction->id }})" class="text-primary hover:underline text-sm">
                        <i class="fas fa-check-double mr-1"></i> Mark as Reconciled
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Transaction</h3>
            <button onclick="closeReverseModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('finance.transactions.reverse', $transaction) }}">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Reason for Reversal *</label>
                <textarea name="reason" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required></textarea>
                <p class="text-xs text-gray-500 mt-1">This action will reverse the transaction and update related records.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReverseModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                    <i class="fas fa-undo mr-2"></i> Reverse Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reconcile Modal -->
<div id="reconcileModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reconcile Transaction</h3>
            <button onclick="closeModal('reconcileModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reconcileForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Reconciled By *</label>
                <input type="text" name="reconciled_by" value="{{ Auth::user()->name }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea name="notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reconcileModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark font-semibold">
                    <i class="fas fa-check-double mr-2"></i> Reconcile
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openReverseModal() {
        document.getElementById('reverseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReverseModal() {
        document.getElementById('reverseModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function reconcileTransaction(id) {
        const modal = document.getElementById('reconcileModal');
        const form = document.getElementById('reconcileForm');
        form.action = `/finance/transactions/${id}/reconcile`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReverseModal();
            closeModal('reconcileModal');
        }
    });
</script>
@endpush
@endsection
