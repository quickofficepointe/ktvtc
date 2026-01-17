{{-- resources/views/payments/index.blade.php --}}
@extends('ktvtc.cafeteria.layout.cafeterialayout')


@section('title', 'Payment Transactions')
@section('page-title', 'Payment Management')
@section('page-description', 'View and manage all payment transactions')

@section('content')
<div class="space-y-6">
    <!-- Today's Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Today's Total</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($todayTotal, 2) }}</p>
                </div>
            </div>
        </div>
        <!-- More stats... -->
    </div>

    <!-- Transaction Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-4">
            <select id="paymentMethodFilter" class="border border-gray-300 rounded-lg p-2">
                <option value="">All Methods</option>
                <option value="mpesa">M-Pesa</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>

            <select id="statusFilter" class="border border-gray-300 rounded-lg p-2">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="reversed">Reversed</option>
            </select>

            <input type="date" id="dateFilter" class="border border-gray-300 rounded-lg p-2"
                   value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $transaction->transaction_number }}</td>
                        <td class="px-6 py-4">
                            @if($transaction->sale)
                            <a href="{{ route('sales.show', $transaction->sale_id) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $transaction->sale->invoice_number }}
                            </a>
                            @else
                            <span class="text-gray-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($transaction->payment_method == 'mpesa')
                                <i class="fab fa-mpesa text-green-600 mr-2"></i>
                                <span>M-Pesa</span>
                                @elseif($transaction->payment_method == 'cash')
                                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                                <span>Cash</span>
                                @elseif($transaction->payment_method == 'card')
                                <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                                <span>Card</span>
                                @else
                                <i class="fas fa-university text-purple-600 mr-2"></i>
                                <span>Bank Transfer</span>
                                @endif
                            </div>
                            @if($transaction->mpesa_receipt)
                            <div class="text-xs text-gray-500 mt-1">{{ $transaction->mpesa_receipt }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">KES {{ number_format($transaction->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($transaction->status == 'completed')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Completed
                            </span>
                            @elseif($transaction->status == 'pending')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @elseif($transaction->status == 'failed')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Failed
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4">
                            @if($transaction->status == 'pending')
                            <button onclick="markAsCompleted({{ $transaction->id }})"
                                    class="text-green-600 hover:text-green-900 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            @endif
                            <button onclick="viewReceipt({{ $transaction->id }})"
                                    class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-receipt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
