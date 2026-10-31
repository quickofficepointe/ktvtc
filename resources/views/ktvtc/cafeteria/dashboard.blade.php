@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Dashboard')
@section('subtitle', 'Cafeteria overview')

@section('content')
<x-dash.header title="Cafeteria dashboard" subtitle="Sales, orders and stock">
    <x-slot name="actions">
        <x-dash.action :href="route('cafeteria.pre-orders.index')" icon="fa-receipt">Pre-orders</x-dash.action>
        <x-dash.action :href="route('cafeteria.products.index')" icon="fa-box">Products</x-dash.action>
        <x-dash.action :href="route('cafeteria.sales.pos')" icon="fa-cash-register" primary>Open the till</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Takings today" :value="'KES ' . number_format($todayPos + $todayCard)" icon="fa-cash-register"
        :hint="'Cash & M-Pesa ' . number_format($todayPos) . ' · cards ' . number_format($todayCard) . ' · ' . $todayCount . ' sales'" />
    <x-dash.kpi label="This month" :value="'KES ' . number_format($month)" icon="fa-calendar" tone="blue" hint="Paid sales and card purchases" />
    <x-dash.kpi label="Pre-orders to prepare" :value="number_format($preOrdersOpen)" icon="fa-receipt" :tone="$preOrdersOpen ? 'amber' : 'green'" hint="Paid, accepted or ready" :href="route('cafeteria.pre-orders.index')" />
    <x-dash.kpi label="Low on stock" :value="number_format($lowStockCount)" icon="fa-boxes-stacked" :tone="$lowStockCount ? 'red' : 'green'" hint="At or below the re-order level" :href="route('cafeteria.products.index')" />
</div>

@if($unpaidSales)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <i class="fas fa-circle-exclamation mr-1"></i> {{ $unpaidSales }} sale(s) in the last 7 days are still marked unpaid. Check them on the pending payments screen.
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Daily sales, last 30 days" class="xl:col-span-2">
        <x-dash.chart type="bar" :stacked="true" :labels="$daily['labels']" :datasets="[
            ['label' => 'Cash & M-Pesa', 'data' => $daily['pos'], 'color' => '#b91c1c'],
            ['label' => 'Student cards', 'data' => $daily['card'], 'color' => '#2563eb'],
        ]" />
    </x-dash.panel>
    <x-dash.panel title="Best sellers this month">
        <x-dash.bars :rows="$topProducts" empty="No paid sales this month yet." />
    </x-dash.panel>
</div>

<x-dash.panel title="Running low" subtitle="Order these soon" :href="route('cafeteria.products.index')">
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Product</th><th class="py-2 px-1 text-right">In stock</th><th class="py-2 px-1 text-right">Re-order at</th></tr></thead>
            <tbody>
                @forelse($lowStock as $p)
                    <tr class="border-b last:border-0">
                        <td class="py-2 px-1">{{ $p->product_name }}</td>
                        <td class="py-2 px-1 text-right tabular-nums font-semibold {{ $p->current_stock <= 0 ? 'text-red-700' : 'text-amber-700' }}">{{ rtrim(rtrim(number_format((float) $p->current_stock, 2), '0'), '.') }} {{ $p->unit }}</td>
                        <td class="py-2 px-1 text-right tabular-nums text-gray-600">{{ rtrim(rtrim(number_format((float) max($p->reorder_level, $p->min_stock_level), 2), '0'), '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-500">Everything is stocked.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dash.panel>
@endsection
