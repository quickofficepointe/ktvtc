@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Pre-Orders')
@section('subtitle', 'Student and staff pre-orders')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    {{-- Status tabs --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [
                    'active'    => ['label' => 'Active',    'count' => $counts['active'],    'color' => 'indigo'],
                    'pending'   => ['label' => 'Pending',   'count' => $counts['pending'],   'color' => 'yellow'],
                    'completed' => ['label' => 'Completed', 'count' => $counts['completed'], 'color' => 'green'],
                    'cancelled' => ['label' => 'Cancelled', 'count' => $counts['cancelled'], 'color' => 'red'],
                    'all'       => ['label' => 'All',       'count' => null,                 'color' => 'gray'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ route('cafeteria.pre-orders.index', ['status' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                          {{ $status === $key
                             ? 'bg-red-700 text-white'
                             : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $tab['label'] }}
                    @if($tab['count'] !== null)
                        <span class="ml-1 text-xs {{ $status === $key ? 'text-white/80' : 'text-gray-500' }}">
                            ({{ $tab['count'] }})
                        </span>
                    @endif
                </a>
            @endforeach

            <form method="GET" class="ml-auto flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Order # or customer..."
                       class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none">
                <input type="date" name="date" value="{{ request('date') }}"
                       class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-1.5 rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Orders list --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @forelse($orders as $order)
            <div class="border-b border-gray-100 last:border-0 p-4 hover:bg-gray-50 transition">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="font-bold text-gray-800">{{ $order->order_number }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                @if($order->status === 'completed') bg-gray-200 text-gray-700
                                @elseif($order->status === 'ready') bg-purple-100 text-purple-700
                                @elseif($order->status === 'accepted') bg-indigo-100 text-indigo-700
                                @elseif($order->status === 'paid') bg-green-100 text-green-700
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                                @elseif(in_array($order->status, ['cancelled','rejected','failed'])) bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ $order->status_label }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                {{ strtoupper($order->payment_method ?? '—') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-user mr-1 text-gray-400"></i>
                            {{ $order->account?->display_name ?? 'Unknown' }}
                            <span class="text-xs text-gray-400">({{ $order->account?->type_label ?? '' }})</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $order->created_at->format('d M, H:i') }} &middot;
                            {{ $order->items->count() }} item(s)
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-lg font-bold text-red-700">KES {{ number_format($order->total_amount, 2) }}</p>
                        <a href="{{ route('cafeteria.pre-orders.show', $order->id) }}"
                           class="inline-block mt-1 text-xs bg-red-700 hover:bg-red-800 text-white px-3 py-1.5 rounded-lg transition">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No pre-orders found</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $orders->links() }}
    </div>
</div>
@endsection