@extends('layouts.app')

@section('title', 'My Orders — KTVTC Pre-Order')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-receipt mr-2 text-red-600"></i>My Pre-Orders
            </h1>
            <a href="{{ route('pre-order.home') }}"
               class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-1"></i> New Order
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @forelse($orders as $order)
            <a href="{{ route('pre-order.orders.show', $order->id) }}"
               class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition mb-3">
                <div class="p-4 flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-bold text-gray-800">{{ $order->order_number }}</p>
                            @php
                                $statusColors = [
                                    'pending'    => 'bg-yellow-100 text-yellow-700',
                                    'processing' => 'bg-blue-100 text-blue-700',
                                    'paid'       => 'bg-green-100 text-green-700',
                                    'accepted'   => 'bg-indigo-100 text-indigo-700',
                                    'ready'      => 'bg-purple-100 text-purple-700',
                                    'completed'  => 'bg-gray-200 text-gray-700',
                                    'cancelled'  => 'bg-red-100 text-red-700',
                                    'rejected'   => 'bg-red-100 text-red-700',
                                    'failed'     => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y, H:i') }} &middot;
                            {{ $order->items->count() }} item(s) &middot;
                            {{ $order->shop?->shop_name ?? 'Cafeteria' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $order->items->take(3)->pluck('product_name')->implode(', ') }}
                            @if($order->items->count() > 3)
                                +{{ $order->items->count() - 3 }} more
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-700">KES {{ number_format($order->total_amount, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ strtoupper($order->payment_method ?? '—') }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-receipt text-5xl text-gray-300 mb-3"></i>
                <p class="text-lg font-medium text-gray-600">No orders yet</p>
                <p class="text-sm text-gray-400 mt-1 mb-4">Start by placing your first pre-order.</p>
                <a href="{{ route('pre-order.home') }}"
                   class="inline-block bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                    <i class="fas fa-utensils mr-1"></i> Browse Menu
                </a>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection