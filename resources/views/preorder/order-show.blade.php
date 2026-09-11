@extends('layouts.app')

@section('title', 'Order ' . $order->order_number . ' — KTVTC Pre-Order')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4">

        <a href="{{ route('pre-order.orders') }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-red-700 mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Orders
        </a>

        {{-- Status card --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <p class="text-xs text-gray-500">Order Number</p>
                    <p class="text-xl font-bold text-gray-800">{{ $order->order_number }}</p>
                </div>
                <span class="text-sm px-3 py-1 rounded-full
                    @if($order->status === 'completed') bg-gray-200 text-gray-700
                    @elseif($order->status === 'ready') bg-purple-100 text-purple-700
                    @elseif($order->status === 'accepted') bg-indigo-100 text-indigo-700
                    @elseif($order->status === 'paid') bg-green-100 text-green-700
                    @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                    @elseif(in_array($order->status, ['cancelled','rejected','failed'])) bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ $order->status_label }}
                </span>
            </div>

            <p class="text-sm text-gray-500">
                Placed on {{ $order->created_at->format('d M Y, H:i') }}
            </p>

            {{-- Status timeline --}}
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle {{ $order->paid_at ? 'text-green-600' : 'text-gray-300' }}"></i>
                    <span class="{{ $order->paid_at ? 'text-gray-800' : 'text-gray-400' }}">
                        Payment confirmed
                        @if($order->paid_at) — {{ $order->paid_at->format('H:i') }} @endif
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle {{ $order->accepted_at ? 'text-green-600' : 'text-gray-300' }}"></i>
                    <span class="{{ $order->accepted_at ? 'text-gray-800' : 'text-gray-400' }}">
                        Accepted by cafeteria
                        @if($order->accepted_at) — {{ $order->accepted_at->format('H:i') }} @endif
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle {{ $order->ready_at ? 'text-green-600' : 'text-gray-300' }}"></i>
                    <span class="{{ $order->ready_at ? 'text-gray-800' : 'text-gray-400' }}">
                        Ready for pickup
                        @if($order->ready_at) — {{ $order->ready_at->format('H:i') }} @endif
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle {{ $order->completed_at ? 'text-green-600' : 'text-gray-300' }}"></i>
                    <span class="{{ $order->completed_at ? 'text-gray-800' : 'text-gray-400' }}">
                        Picked up
                        @if($order->completed_at) — {{ $order->completed_at->format('H:i') }} @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="font-bold text-gray-800 mb-4">Items</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                        <div>
                            <p class="font-medium text-gray-800">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $item->quantity }} × KES {{ number_format($item->unit_price, 2) }}
                            </p>
                        </div>
                        <p class="font-bold text-gray-800">KES {{ number_format($item->total_price, 2) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t flex items-center justify-between">
                <span class="font-bold text-gray-800">Total</span>
                <span class="text-xl font-bold text-red-700">KES {{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Payment info --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="font-bold text-gray-800 mb-3">Payment</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Method</p>
                    <p class="font-medium">{{ strtoupper($order->payment_method ?? '—') }}</p>
                </div>
                @if($order->mpesa_receipt)
                    <div>
                        <p class="text-xs text-gray-500">M-Pesa Receipt</p>
                        <p class="font-mono text-xs">{{ $order->mpesa_receipt }}</p>
                    </div>
                @endif
                @if($order->card_transaction_id)
                    <div>
                        <p class="text-xs text-gray-500">Card Transaction</p>
                        <p class="font-mono text-xs">#{{ $order->card_transaction_id }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Cancel --}}
        @if($order->canBeCancelled())
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <form method="POST" action="{{ route('pre-order.orders.cancel', $order->id) }}"
                      onsubmit="return confirm('Cancel this order?');">
                    @csrf
                    <button type="submit"
                            class="w-full border border-red-600 text-red-700 hover:bg-red-600 hover:text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-times-circle mr-2"></i> Cancel Order
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-2">
                        Any card payment will be refunded automatically.
                    </p>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection