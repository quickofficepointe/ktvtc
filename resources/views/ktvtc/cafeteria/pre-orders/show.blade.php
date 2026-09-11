@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Order ' . $order->order_number)
@section('subtitle', $order->account?->display_name ?? 'Order Detail')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <a href="{{ route('cafeteria.pre-orders.index') }}"
       class="inline-flex items-center text-sm text-gray-600 hover:text-red-700">
        <i class="fas fa-arrow-left mr-2"></i> Back to Pre-Orders
    </a>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs text-gray-500">Order Number</p>
                <p class="text-2xl font-bold text-gray-800">{{ $order->order_number }}</p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $order->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block text-sm px-3 py-1 rounded-full
                    @if($order->status === 'completed') bg-gray-200 text-gray-700
                    @elseif($order->status === 'ready') bg-purple-100 text-purple-700
                    @elseif($order->status === 'accepted') bg-indigo-100 text-indigo-700
                    @elseif($order->status === 'paid') bg-green-100 text-green-700
                    @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                    @elseif(in_array($order->status, ['cancelled','rejected','failed'])) bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ $order->status_label }}
                </span>
                <p class="text-2xl font-bold text-red-700 mt-2">KES {{ number_format($order->total_amount, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Customer --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-3">Customer</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500">Name</p>
                <p class="font-medium">{{ $order->account?->display_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Type</p>
                <p class="font-medium">{{ $order->account?->type_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Identifier</p>
                <p class="font-mono text-xs">{{ $order->account?->identifier }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Phone</p>
                <p class="font-medium">{{ $order->account?->phone ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-3">Items ({{ $order->items->count() }})</h3>
        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 last:border-0">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->quantity }} × KES {{ number_format($item->unit_price, 2) }}</p>
                    </div>
                    <p class="font-bold">KES {{ number_format($item->total_price, 2) }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-4 pt-4 border-t flex justify-between">
            <span class="font-bold">Total</span>
            <span class="font-bold text-red-700">KES {{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    {{-- Payment --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-3">Payment</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
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
            @if($order->paid_at)
                <div>
                    <p class="text-xs text-gray-500">Paid At</p>
                    <p class="font-medium">{{ $order->paid_at->format('d M Y, H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="bg-white rounded-xl shadow-sm p-5 space-y-3">
        <h3 class="font-bold text-gray-800 mb-1">Actions</h3>

        @if($order->canBeAccepted())
            <form method="POST" action="{{ route('cafeteria.pre-orders.accept', $order->id) }}">
                @csrf
                <input type="hidden" name="staff_notes" value="">
                <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-check mr-2"></i> Accept Order
                </button>
            </form>
        @endif

        @if($order->status === 'accepted')
            <form method="POST" action="{{ route('cafeteria.pre-orders.ready', $order->id) }}">
                @csrf
                <input type="hidden" name="staff_notes" value="">
                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-utensils mr-2"></i> Mark Ready for Pickup
                </button>
            </form>
        @endif

        @if(in_array($order->status, ['ready', 'accepted']))
            <form method="POST" action="{{ route('cafeteria.pre-orders.pickup', $order->id) }}">
                @csrf
                <button type="submit"
                        class="w-full bg-gray-700 hover:bg-gray-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-hand-holding mr-2"></i> Mark Picked Up
                </button>
            </form>
        @endif

        @if(!in_array($order->status, ['completed', 'cancelled', 'rejected']))
            <form method="POST" action="{{ route('cafeteria.pre-orders.reject', $order->id) }}"
                  onsubmit="return confirm('Reject this order?');">
                @csrf
                <div class="space-y-2">
                    <textarea name="reason" required maxlength="500" rows="2"
                              placeholder="Reason for rejection (customer will be notified)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    <button type="submit"
                            class="w-full border border-red-600 text-red-700 hover:bg-red-600 hover:text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-times-circle mr-2"></i> Reject Order
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection