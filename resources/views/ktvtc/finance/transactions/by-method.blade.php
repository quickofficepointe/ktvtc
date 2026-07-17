@extends('ktvtc.finance.layouts.app')

@section('title', ucfirst($method) . ' Transactions')
@section('subtitle', 'View all ' . ucfirst($method) . ' transactions')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.transactions.index') }}" class="text-gray-600 hover:text-primary">Transactions</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ ucfirst($method) }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <a href="{{ route('finance.transactions.export') }}?payment_method={{ $method }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>

    <a href="{{ route('finance.transactions.index') }}" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <!-- Method Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
            <p class="text-sm text-gray-500">Total {{ ucfirst($method) }}</p>
            <p class="text-2xl font-bold text-blue-600">
                KES {{ number_format($transactions->sum('amount') ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-green-600">
                {{ number_format($transactions->total() ?? 0) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
            <p class="text-sm text-gray-500">Average</p>
            <p class="text-2xl font-bold text-yellow-600 break-words">
                KES {{ number_format(($transactions->count() > 0) ? $transactions->sum('amount') / $transactions->count() : 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-4">
            <p class="text-sm text-gray-500">Method</p>
            <p class="text-2xl font-bold text-purple-600 uppercase">
                {{ $method }}
            </p>
        </div>
    </div>

    <!-- Method Icon -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex flex-col items-center justify-center">
            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center text-primary text-4xl">
                @if($method === 'mpesa')
                    <i class="fas fa-mobile-alt"></i>
                @elseif($method === 'cash')
                    <i class="fas fa-money-bill-wave"></i>
                @elseif($method === 'card')
                    <i class="fas fa-credit-card"></i>
                @elseif($method === 'bank')
                    <i class="fas fa-university"></i>
                @else
                    <i class="fas fa-wallet"></i>
                @endif
            </div>

            <h3 class="text-xl font-bold text-gray-800 mt-3 uppercase">{{ $method }}</h3>
            <p class="text-sm text-gray-500">{{ number_format($transactions->total() ?? 0) }} transactions</p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[1050px] w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Transaction #</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Invoice</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Customer</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Amount</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-medium text-primary">
                                    {{ $transaction->transaction_number }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="text-sm">
                                    {{ $transaction->sale->invoice_number ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-medium text-gray-900">
                                    {{ $transaction->sale->customer_name ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-gray-500 block">
                                    {{ $transaction->sale->customer_phone ?? '' }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right font-semibold text-gray-800">
                                KES {{ number_format($transaction->amount ?? 0, 2) }}
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

                            <td class="py-3 px-4 text-sm whitespace-nowrap">
                                {{ optional($transaction->created_at)->format('d M Y H:i') }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-3 whitespace-nowrap">
                                    <a href="{{ route('finance.transactions.show', $transaction) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($transaction->status === 'completed')
                                        <a href="{{ route('finance.transactions.receipt', $transaction) }}" class="text-gray-500 hover:text-gray-700" title="Receipt" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif

                                    @if($transaction->status === 'pending')
                                        <button type="button" onclick="verifyTransaction({{ $transaction->id }})" class="text-green-600 hover:text-green-800" title="Verify">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif

                                    @if($transaction->status === 'completed')
                                        <button type="button" onclick="reverseTransaction({{ $transaction->id }})" class="text-red-600 hover:text-red-800" title="Reverse">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        @if($method === 'mpesa')
                                            <i class="fas fa-mobile-alt text-3xl text-gray-300"></i>
                                        @elseif($method === 'cash')
                                            <i class="fas fa-money-bill-wave text-3xl text-gray-300"></i>
                                        @elseif($method === 'card')
                                            <i class="fas fa-credit-card text-3xl text-gray-300"></i>
                                        @else
                                            <i class="fas fa-wallet text-3xl text-gray-300"></i>
                                        @endif
                                    </div>
                                    <p class="font-medium text-gray-600">No {{ $method }} transactions found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($transactions->count() > 0)
                <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                    <tr>
                        <td colspan="3" class="py-2 px-4 font-bold">Total</td>
                        <td class="py-2 px-4 text-right font-bold">
                            KES {{ number_format($transactions->sum('amount'), 2) }}
                        </td>
                        <td colspan="3" class="py-2 px-4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if(isset($transactions))
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-green-100 text-center shadow-sm">
            <p class="text-xs text-gray-500">Completed</p>
            <p class="text-lg font-bold text-green-600">
                {{ number_format($transactions->where('status', 'completed')->count()) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-yellow-100 text-center shadow-sm">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-lg font-bold text-yellow-600">
                {{ number_format($transactions->where('status', 'pending')->count()) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-red-100 text-center shadow-sm">
            <p class="text-xs text-gray-500">Failed</p>
            <p class="text-lg font-bold text-red-600">
                {{ number_format($transactions->where('status', 'failed')->count()) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-blue-100 text-center shadow-sm">
            <p class="text-xs text-gray-500">Largest</p>
            <p class="text-lg font-bold text-blue-600 break-words">
                KES {{ number_format($transactions->max('amount') ?? 0, 2) }}
            </p>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('verifyModal')"></div>

        <div class="relative bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Verify Transaction</h3>
                <button type="button" onclick="closeModal('verifyModal')" class="text-gray-400 hover:text-gray-600">
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
                    <button type="button" onclick="closeModal('verifyModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Verify Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('reverseModal')"></div>

        <div class="relative bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reverse Transaction</h3>
                <button type="button" onclick="closeModal('reverseModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="reverseForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700">Reason for Reversal <span class="text-red-500">*</span></label>
                    <textarea name="reason" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('reverseModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Reverse Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function verifyTransaction(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');

        if (form) {
            form.action = `/finance/transactions/${id}/verify`;
        }

        if (modal) {
            modal.classList.remove('hidden');
        }

        document.body.style.overflow = 'hidden';
    }

    function reverseTransaction(id) {
        const modal = document.getElementById('reverseModal');
        const form = document.getElementById('reverseForm');

        if (form) {
            form.action = `/finance/transactions/${id}/reverse`;
        }

        if (modal) {
            modal.classList.remove('hidden');
        }

        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.add('hidden');
        }

        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
            closeModal('reverseModal');
        }
    });
</script>
@endpush
