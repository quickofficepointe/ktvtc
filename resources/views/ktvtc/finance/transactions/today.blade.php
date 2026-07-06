@extends('ktvtc.finance.layouts.app')

@section('title', "Today's Transactions")
@section('subtitle', 'View all transactions for today')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.transactions.index') }}" class="text-gray-600 hover:text-primary">Transactions</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Today</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-sm text-gray-600">Today's Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($transactions->count()) }}</p>
        </div>
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-sm text-gray-600">Total Amount</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($transactions->sum('amount'), 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-sm text-gray-600">Average</p>
            <p class="text-2xl font-bold text-purple-600">
                KES {{ number_format($transactions->count() > 0 ? $transactions->sum('amount') / $transactions->count() : 0, 2) }}
            </p>
        </div>
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($transactions->where('status', 'pending')->count()) }}</p>
        </div>
    </div>

    <!-- Today's Date -->
    <div class="finance-card p-4 text-center bg-gray-50">
        <p class="text-lg font-bold text-gray-800">{{ now()->format('l, F j, Y') }}</p>
        <p class="text-sm text-gray-500">{{ now()->format('H:i:s') }} - System Time</p>
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
                        <th class="text-left py-3 px-4">Time</th>
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
                                @else
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> {{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $transaction->created_at->format('H:i:s') }}</td>
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-calendar-day text-4xl text-gray-300 mb-2 block"></i>
                                No transactions recorded today
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="py-2 px-4 font-bold">Total</td>
                        <td class="py-2 px-4 text-right font-bold">KES {{ number_format($transactions->sum('amount'), 2) }}</td>
                        <td colspan="4" class="py-2 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Hourly Breakdown -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Hourly Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $hourlyData = [];
                foreach($transactions as $t) {
                    $hour = $t->created_at->format('H');
                    if (!isset($hourlyData[$hour])) {
                        $hourlyData[$hour] = ['count' => 0, 'amount' => 0];
                    }
                    $hourlyData[$hour]['count']++;
                    $hourlyData[$hour]['amount'] += $t->amount;
                }
                ksort($hourlyData);
            @endphp
            @forelse($hourlyData as $hour => $data)
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-sm font-semibold text-gray-700">{{ sprintf('%02d:00 - %02d:59', $hour, $hour) }}</p>
                    <p class="text-lg font-bold text-primary">{{ $data['count'] }} transactions</p>
                    <p class="text-xs text-gray-500">KES {{ number_format($data['amount'], 2) }}</p>
                </div>
            @empty
                <p class="text-gray-500 text-center col-span-4">No data available</p>
            @endforelse
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
    function verifyTransaction(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');
        form.action = `/finance/transactions/${id}/verify`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close modal on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
        }
    });
</script>
@endpush
