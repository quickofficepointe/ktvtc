@extends('ktvtc.finance.layouts.app')

@section('title', 'Pending Transactions')
@section('subtitle', 'View and process pending transactions')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.transactions.index') }}" class="text-gray-600 hover:text-primary">Transactions</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Pending</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="bulkVerify()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-check-double mr-2"></i> Bulk Verify
    </button>
    <button onclick="bulkProcess()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-play mr-2"></i> Bulk Process
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-sm text-gray-600">Pending Transactions</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($transactions->count()) }}</p>
        </div>
        <div class="finance-card p-4 bg-orange-50 border-orange-200">
            <p class="text-sm text-gray-600">Total Amount</p>
            <p class="text-2xl font-bold text-orange-600">KES {{ number_format($transactions->sum('amount'), 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-sm text-gray-600">Average</p>
            <p class="text-2xl font-bold text-purple-600">
                KES {{ number_format($transactions->count() > 0 ? $transactions->sum('amount') / $transactions->count() : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="finance-card p-3 bg-gray-50">
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
            <button onclick="bulkVerify()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-sm">
                <i class="fas fa-check-circle mr-2"></i> Verify Selected
            </button>
            <button onclick="bulkProcess()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                <i class="fas fa-play mr-2"></i> Process Selected
            </button>
            <span class="text-sm text-gray-500 ml-4">
                <span id="selectedCount">0</span> selected
            </span>
        </div>
    </div>

    <!-- Pending Transactions Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        </th>
                        <th class="text-left py-3 px-4">Transaction #</th>
                        <th class="text-left py-3 px-4">Invoice</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Method</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" class="row-checkbox" value="{{ $transaction->id }}">
                            </td>
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
                            <td class="py-3 px-4 text-sm">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="verifyTransaction({{ $transaction->id }})" class="text-green-600 hover:text-green-800" title="Verify">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button onclick="rejectTransaction({{ $transaction->id }})" class="text-red-600 hover:text-red-800" title="Reject">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-300 mb-2 block"></i>
                                No pending transactions
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

@push('scripts')
<script>
    let selectedIds = [];

    function toggleAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        selectedIds = Array.from(checkboxes).map(cb => cb.value);
        document.getElementById('selectedCount').textContent = selectedIds.length;
    }

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function verifyTransaction(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');
        form.action = `/finance/transactions/${id}/verify`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function rejectTransaction(id) {
        if (confirm('Are you sure you want to reject this transaction?')) {
            showLoading('Rejecting transaction...');
            $.ajax({
                url: `/finance/transactions/${id}/reject`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    toastr.success('Transaction rejected successfully');
                    location.reload();
                },
                error: function(xhr) {
                    hideLoading();
                    toastr.error('Failed to reject transaction');
                }
            });
        }
    }

    function bulkVerify() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            toastr.warning('Please select at least one transaction');
            return;
        }
        const ids = Array.from(checked).map(cb => cb.value);

        if (confirm(`Verify ${ids.length} transaction(s)?`)) {
            showLoading('Verifying transactions...');
            $.ajax({
                url: "{{ route('finance.transactions.bulk-verify') }}",
                method: 'POST',
                data: { ids: ids },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    toastr.success(response.message || 'Transactions verified successfully');
                    location.reload();
                },
                error: function(xhr) {
                    hideLoading();
                    toastr.error('Failed to verify transactions');
                }
            });
        }
    }

    function bulkProcess() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            toastr.warning('Please select at least one transaction');
            return;
        }
        const ids = Array.from(checked).map(cb => cb.value);

        if (confirm(`Process ${ids.length} transaction(s)?`)) {
            showLoading('Processing transactions...');
            $.ajax({
                url: "{{ route('finance.transactions.bulk-process') }}",
                method: 'POST',
                data: { ids: ids },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    toastr.success(response.message || 'Transactions processed successfully');
                    location.reload();
                },
                error: function(xhr) {
                    hideLoading();
                    toastr.error('Failed to process transactions');
                }
            });
        }
    }

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
        }
    });
</script>
@endpush
