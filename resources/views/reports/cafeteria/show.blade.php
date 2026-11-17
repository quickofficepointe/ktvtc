@extends($layout)

@section('title', 'Cafeteria Report')
@section('subtitle', 'Sales, items, stock and what to buy')

@php
    use App\Services\Reports\CafeteriaReport;
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $q = ['start_date' => $startDate, 'end_date' => $endDate];
    $periodLabel = $single ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y');
    $openDays = $byDay->where('count', '>', 0);
    $peak = $hours->sortByDesc('total')->first();
@endphp

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <a href="{{ route($routePrefix . '.pdf', $q + ['part' => 'full']) }}" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-sm">
        <i class="fas fa-file-pdf mr-2"></i> Full report (PDF)
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Period --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">From</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">To</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm">Show</button>
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach([
                'Today' => [today(), today()],
                'Yesterday' => [today()->subDay(), today()->subDay()],
                'This week' => [today()->startOfWeek(), today()],
                'Last 7 days' => [today()->subDays(6), today()],
                'This month' => [today()->startOfMonth(), today()],
                'Last month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
            ] as $label => [$a, $b])
                <a href="{{ route($routePrefix, ['start_date' => $a->toDateString(), 'end_date' => $b->toDateString()]) }}"
                   class="px-3 py-2 rounded-lg border {{ $startDate === $a->toDateString() && $endDate === $b->toDateString() ? 'bg-red-700 text-white border-red-700' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300' }}">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    {{-- Section bar + downloads --}}
    <div class="sticky top-0 z-20 bg-gray-50/95 backdrop-blur py-2 -my-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <nav class="flex flex-wrap gap-1 text-sm">
                @foreach(['overview' => 'Overview', 'items' => 'Items sold', 'days' => 'Day by day', 'stock' => 'Stock', 'reorder' => 'What to buy', 'trends' => 'Trends'] as $id => $label)
                    <a href="#{{ $id }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:border-red-300 hover:text-red-700">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex flex-wrap gap-1 text-sm">
                <span class="text-gray-500 self-center mr-1 hidden sm:inline">PDF:</span>
                @foreach(['sales' => 'Sales', 'stock' => 'Stock', 'reorder' => 'Purchase list'] as $part => $label)
                    <a href="{{ route($routePrefix . '.pdf', $q + ['part' => $part]) }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:bg-red-50 hover:text-red-700"><i class="fas fa-download mr-1 text-xs"></i>{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ OVERVIEW ============ --}}
    <div id="overview" class="scroll-mt-16">
        <p class="text-sm text-gray-500 mb-3"><i class="far fa-calendar mr-1"></i> {{ $periodLabel }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Total sales" :value="$kes($total)" icon="fa-cash-register" tone="brand"
                :hint="$change !== null ? (($change >= 0 ? '▲ ' : '▼ ') . abs($change) . '% vs ' . $prevFrom->format('j M') . ($single ? '' : ' – ' . $prevTo->format('j M'))) : 'No sales in the previous period'" />
            <x-dash.kpi label="Transactions" :value="number_format($count)" icon="fa-receipt" tone="blue" :hint="'Average ' . $kes($average) . ' each'" />
            <x-dash.kpi label="Gross profit (costed items)" :value="$kes($profit)" icon="fa-chart-line" tone="green"
                :hint="$costedRevenue > 0 ? round($profit / $costedRevenue * 100, 1) . '% margin' : 'Add cost prices to products'" />
            @if($single)
                <x-dash.kpi label="Busiest hour" :value="$peak && $peak['total'] > 0 ? $peak['label'] : '—'" icon="fa-clock" tone="amber" :hint="$peak ? $kes($peak['total']) . ' · ' . $peak['count'] . ' sales' : null" />
            @else
                <x-dash.kpi label="Best day" :value="$bestDay && $bestDay['total'] > 0 ? \Carbon\Carbon::parse($bestDay['date'])->format('D j M') : '—'" icon="fa-trophy" tone="amber"
                    :hint="$bestDay ? $kes($bestDay['total']) . ' · average ' . $kes($openDays->count() ? $total / $openDays->count() : 0) . '/day' : null" />
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        <x-dash.panel title="Sales by channel" subtitle="Till, student cards, pre-orders and online" class="xl:col-span-2">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Channel</th><th class="py-2 text-right">Sales</th><th class="py-2 text-right">Amount</th><th class="py-2 text-right">Share</th></tr></thead>
                <tbody>
                @forelse($channels as $c)
                    <tr class="border-b border-gray-100">
                        <td class="py-2"><span class="inline-block w-2.5 h-2.5 rounded-sm mr-2" style="background: {{ $c['color'] }}"></span>{{ $c['label'] }}</td>
                        <td class="py-2 text-right text-gray-500">{{ number_format($c['count']) }}</td>
                        <td class="py-2 text-right font-medium">{{ $n($c['total']) }}</td>
                        <td class="py-2 text-right text-gray-500">{{ $total > 0 ? round($c['total'] / $total * 100) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-gray-500">No sales in this period.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr class="font-bold"><td class="py-2">Total</td><td class="py-2 text-right">{{ number_format($count) }}</td><td class="py-2 text-right">{{ $n($total) }}</td><td></td></tr></tfoot>
            </table>
            @if($channels->isNotEmpty())
                <div class="mt-4"><x-dash.chart type="doughnut" :height="200" :labels="$channels->pluck('label')" :datasets="[['label' => 'Sales', 'data' => $channels->pluck('total'), 'colors' => $channels->pluck('color')]]" /></div>
            @endif
        </x-dash.panel>

        <x-dash.panel :title="$single ? 'Sales through the day' : 'Sales each day by channel'" :subtitle="$single ? 'Amount sold each hour' : 'Stacked by how customers paid'" class="xl:col-span-3">
            @if($single)
                <x-dash.chart type="bar" :height="330" :labels="$hours->pluck('label')" :datasets="[['label' => 'Sales', 'data' => $hours->pluck('total'), 'color' => '#b91c1c']]" />
            @else
                <x-dash.chart type="bar" :stacked="true" :height="330" :labels="$days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('j M'))"
                    :datasets="$channels->map(fn ($c) => ['label' => $c['label'], 'data' => array_values($c['days']), 'color' => $c['color']])->values()" />
            @endif
        </x-dash.panel>
    </div>

    {{-- ============ ITEMS ============ --}}
    <div id="items" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <x-dash.panel title="Top 10 by sales" subtitle="KES earned per item">
            <x-dash.chart type="bar" :height="300" :labels="$topByRevenue->pluck('name')" :datasets="[['label' => 'Sales', 'data' => $topByRevenue->pluck('revenue'), 'color' => '#b91c1c']]" />
        </x-dash.panel>
        <x-dash.panel title="Top 10 by quantity" subtitle="Pieces / units sold">
            <x-dash.chart type="bar" :money="false" :height="300" :labels="$topByQty->pluck('name')" :datasets="[['label' => 'Quantity', 'data' => $topByQty->pluck('qty'), 'color' => '#2563eb']]" />
        </x-dash.panel>
    </div>

    <x-dash.panel title="Items sold" :subtitle="$items->count() . ' items · cost and profit use each product\'s cost price'">
        <div class="mb-3"><input type="search" placeholder="Search items…" oninput="filterRows(this, 'items-table')" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-72"></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="items-table">
                <thead><tr class="text-left text-gray-500 border-b">
                    <th class="py-2 pr-3">Item</th><th class="py-2 pr-3">Category</th><th class="py-2 pr-3 text-right">Qty</th><th class="py-2 pr-3 text-right">Avg price</th>
                    <th class="py-2 pr-3 text-right">Sales</th><th class="py-2 pr-3 text-right">Cost</th><th class="py-2 pr-3 text-right">Profit</th><th class="py-2 text-right">Margin</th>
                </tr></thead>
                <tbody>
                @forelse($items as $it)
                    <tr class="border-b border-gray-100 hover:bg-gray-50" data-search="{{ strtolower($it['name'] . ' ' . $it['category']) }}">
                        <td class="py-2 pr-3 font-medium text-gray-900">{{ $it['name'] }}</td>
                        <td class="py-2 pr-3 text-gray-500">{{ $it['category'] }}</td>
                        <td class="py-2 pr-3 text-right">{{ $n($it['qty']) }}</td>
                        <td class="py-2 pr-3 text-right text-gray-500">{{ $n($it['avg_price']) }}</td>
                        <td class="py-2 pr-3 text-right font-semibold">{{ $n($it['revenue']) }}</td>
                        <td class="py-2 pr-3 text-right text-gray-500">{{ $it['cost'] !== null ? $n($it['cost']) : '—' }}</td>
                        <td class="py-2 pr-3 text-right {{ ($it['profit'] ?? 0) < 0 ? 'text-red-600' : '' }}">{{ $it['profit'] !== null ? $n($it['profit']) : '—' }}</td>
                        <td class="py-2 text-right text-gray-500">{{ $it['margin'] !== null ? $it['margin'] . '%' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-gray-500">No items sold in this period.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr class="font-bold border-t"><td class="py-2">Total</td><td></td><td class="py-2 pr-3 text-right">{{ $n($items->sum('qty')) }}</td><td></td><td class="py-2 pr-3 text-right">{{ $n($itemsTotal) }}</td><td class="py-2 pr-3 text-right">{{ $n($cost) }}</td><td class="py-2 pr-3 text-right">{{ $n($profit) }}</td><td></td></tr></tfoot>
            </table>
        </div>
        @if($unitemised > 0.5)
            <p class="text-xs text-gray-500 mt-3">{{ $kes($unitemised) }} was recorded as an amount without items (e.g. a custom card amount): in the totals, not in this list.</p>
        @endif
    </x-dash.panel>

    <x-dash.panel title="Who sold" subtitle="By cashier or point of sale">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Staff / point of sale</th><th class="py-2 text-right">Sales</th><th class="py-2 text-right">Cash</th><th class="py-2 text-right">M-Pesa</th><th class="py-2 text-right">Student card</th><th class="py-2 text-right">Total</th></tr></thead>
                <tbody>
                @foreach($cashiers as $c)
                    <tr class="border-b border-gray-100"><td class="py-2">{{ $c['name'] }}</td><td class="py-2 text-right text-gray-500">{{ number_format($c['count']) }}</td><td class="py-2 text-right">{{ $n($c['cash']) }}</td><td class="py-2 text-right">{{ $n($c['mpesa']) }}</td><td class="py-2 text-right">{{ $n($c['card']) }}</td><td class="py-2 text-right font-semibold">{{ $n($c['total']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-dash.panel>

    {{-- ============ DAY BY DAY ============ --}}
    <div id="days" class="scroll-mt-16">
        <x-dash.panel title="Day by day" subtitle="Click a day to see the items sold">
            <div class="divide-y divide-gray-100">
                @forelse($byDay->where('count', '>', 0)->reverse() as $d)
                    <details class="group py-1">
                        <summary class="flex flex-wrap items-center gap-x-4 gap-y-1 py-2 cursor-pointer list-none">
                            <i class="fas fa-chevron-right text-xs text-gray-400 group-open:rotate-90 transition-transform"></i>
                            <span class="font-medium text-gray-900 w-28">{{ \Carbon\Carbon::parse($d['date'])->format('D j M') }}</span>
                            <span class="flex flex-wrap gap-1.5 text-xs flex-1">
                                @foreach($channels as $c)
                                    @if($d['channels'][$c['key']] > 0)
                                        <span class="px-2 py-0.5 rounded-full text-white" style="background: {{ $c['color'] }}">{{ $c['label'] }} {{ $n($d['channels'][$c['key']]) }}</span>
                                    @endif
                                @endforeach
                            </span>
                            <span class="text-gray-500 text-xs">{{ $d['count'] }} sales</span>
                            <span class="font-semibold w-28 text-right">{{ $kes($d['total']) }}</span>
                        </summary>
                        <div class="pl-8 pb-3">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($d['items'] as $it)
                                    <span class="px-2 py-1 rounded-md bg-gray-100 text-xs text-gray-700">{{ $it['name'] }} <b>{{ $n($it['qty']) }}</b> <span class="text-gray-400">· {{ $n($it['revenue']) }}</span></span>
                                @empty
                                    <span class="text-xs text-gray-500">No itemised sales this day.</span>
                                @endforelse
                            </div>
                        </div>
                    </details>
                @empty
                    <p class="py-6 text-center text-gray-500 text-sm">No sales in this period.</p>
                @endforelse
            </div>
        </x-dash.panel>
    </div>

    {{-- ============ STOCK ============ --}}
    <div id="stock" class="scroll-mt-16 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Stocked items" :value="$stock->count()" icon="fa-boxes-stacked" tone="gray" />
            <x-dash.kpi label="Stock value (at cost)" :value="$kes($stockValue)" icon="fa-warehouse" tone="blue" />
            <x-dash.kpi label="Out of stock" :value="$stock->where('status', 'Out of stock')->count()" icon="fa-circle-exclamation" tone="red" />
            <x-dash.kpi label="Need buying" :value="$reorder->count()" icon="fa-cart-shopping" tone="amber" :hint="'About ' . $kes($reorderCost)" />
        </div>
        <x-dash.panel title="Stock on hand" :subtitle="'Right now · sells/day is the average over the last ' . CafeteriaReport::SPEED_WINDOW . ' days'">
            <div class="flex flex-wrap gap-2 mb-3">
                <input type="search" placeholder="Search stock…" oninput="filterRows(this, 'stock-table')" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-72">
                @foreach(['' => 'All', 'Out of stock' => 'Out of stock', 'Low' => 'Low', 'OK' => 'OK'] as $val => $label)
                    <button type="button" onclick="filterStatus('{{ $val }}', this)" class="status-btn px-3 py-2 rounded-lg border text-sm {{ $val === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="overflow-x-auto max-h-[560px] overflow-y-auto">
                <table class="w-full text-sm" id="stock-table">
                    <thead class="sticky top-0 bg-white"><tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-3">Item</th><th class="py-2 pr-3">Category</th><th class="py-2 pr-3 text-right">In stock</th><th class="py-2 pr-3 text-right">Reorder at</th>
                        <th class="py-2 pr-3 text-right">Sells/day</th><th class="py-2 pr-3 text-right">Days left</th><th class="py-2 pr-3 text-right">Value</th><th class="py-2 text-center">Status</th>
                    </tr></thead>
                    <tbody>
                    @foreach($stock as $s)
                        <tr class="border-b border-gray-100 hover:bg-gray-50" data-search="{{ strtolower($s['name'] . ' ' . $s['category']) }}" data-status="{{ $s['status'] }}">
                            <td class="py-2 pr-3 font-medium text-gray-900">{{ $s['name'] }}</td>
                            <td class="py-2 pr-3 text-gray-500">{{ $s['category'] }}</td>
                            <td class="py-2 pr-3 text-right font-semibold">{{ $n($s['stock']) }} <span class="text-gray-400 font-normal">{{ $s['unit'] }}</span></td>
                            <td class="py-2 pr-3 text-right text-gray-500">{{ $s['reorder_level'] > 0 ? $n($s['reorder_level']) : '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ $s['per_day'] > 0 ? $n($s['per_day']) : '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ $s['days_left'] !== null ? $n($s['days_left']) : '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ $n($s['value']) }}</td>
                            <td class="py-2 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $s['status'] === 'OK' ? 'bg-green-100 text-green-800' : ($s['status'] === 'Low' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $s['status'] }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-dash.panel>
        @if($slow->isNotEmpty())
            <x-dash.panel title="Slow movers" subtitle="In stock but sold least in this period — money tied up">
                <div class="flex flex-wrap gap-2">
                    @foreach($slow as $s)
                        <span class="px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-sm">{{ $s['name'] }} · sold <b>{{ $n($s['sold']) }}</b> · {{ $n($s['stock']) }} {{ $s['unit'] }} left ({{ $kes($s['value']) }})</span>
                    @endforeach
                </div>
            </x-dash.panel>
        @endif
    </div>

    {{-- ============ WHAT TO BUY ============ --}}
    <div id="reorder" class="scroll-mt-16">
        <x-dash.panel title="What needs to be purchased"
            :subtitle="'Stock at/below its reorder level, or running out within ' . CafeteriaReport::DAYS_WARNING . ' days · suggested quantity covers about ' . CafeteriaReport::DAYS_COVER . ' days'"
            :href="route($routePrefix . '.pdf', $q + ['part' => 'reorder'])" link-text="Download purchase list (PDF)">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Item</th><th class="py-2 pr-3">Why</th><th class="py-2 pr-3 text-right">In stock</th><th class="py-2 pr-3 text-right">Sells/day</th><th class="py-2 pr-3 text-right">Buy</th><th class="py-2 pr-3 text-right">Unit cost</th><th class="py-2 text-right">Est. cost</th></tr></thead>
                    <tbody>
                    @forelse($reorder as $s)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium text-gray-900">{{ $s['name'] }}</td>
                            <td class="py-2 pr-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $s['status'] === 'Out of stock' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">{{ $s['why'] }}</span></td>
                            <td class="py-2 pr-3 text-right">{{ $n($s['stock']) }}</td>
                            <td class="py-2 pr-3 text-right">{{ $s['per_day'] > 0 ? $n($s['per_day']) : '—' }}</td>
                            <td class="py-2 pr-3 text-right font-bold">{{ $n($s['order_qty']) }} {{ $s['unit'] }}</td>
                            <td class="py-2 pr-3 text-right text-gray-500">{{ $s['cost_price'] > 0 ? $n($s['cost_price']) : '—' }}</td>
                            <td class="py-2 text-right">{{ $s['cost_price'] > 0 ? $n($s['order_cost']) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-gray-500">Nothing needs buying right now.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot><tr class="font-bold border-t"><td class="py-2" colspan="6">Estimated total</td><td class="py-2 text-right">{{ $kes($reorderCost) }}</td></tr></tfoot>
                </table>
            </div>
            @if($outIdle->isNotEmpty())
                <div class="mt-4 p-3 rounded-lg bg-gray-50 border border-gray-200">
                    <p class="text-sm font-semibold text-gray-700">Out of stock but not sold in the last {{ CafeteriaReport::SPEED_WINDOW }} days ({{ $outIdle->count() }}) — buy only if still needed</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $outIdle->pluck('name')->implode(' · ') }}</p>
                    <p class="text-xs text-gray-500 mt-2">Give an item a reorder level (Products → edit) and it will appear in the list above automatically when it runs low.</p>
                </div>
            @endif
        </x-dash.panel>
    </div>

    {{-- ============ TRENDS ============ --}}
    <div id="trends" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <x-dash.panel title="Last 12 weeks" subtitle="Total sales per week">
            <x-dash.chart type="line" :height="260" :labels="$weeks->pluck('label')" :datasets="[['label' => 'Sales', 'data' => $weeks->pluck('total'), 'color' => '#b91c1c']]" />
        </x-dash.panel>
        <x-dash.panel title="Last 6 months" subtitle="Total sales per month">
            <x-dash.chart type="bar" :height="260" :labels="$months->pluck('label')" :datasets="[['label' => 'Sales', 'data' => $months->pluck('total'), 'color' => '#2563eb']]" />
        </x-dash.panel>
        @if(!$single)
            <x-dash.panel title="Average by day of the week" subtitle="Days with sales in this period" class="xl:col-span-2">
                <x-dash.chart type="bar" :height="220" :labels="$weekdays->pluck('day')" :datasets="[['label' => 'Average sales', 'data' => $weekdays->pluck('average'), 'color' => '#16a34a']]" />
            </x-dash.panel>
        @endif
    </div>

    @if(collect($unpaid)->sum('count') > 0)
        <x-dash.panel title="Not counted as sales" subtitle="These took no money and no stock">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-red-50"><p class="text-gray-600">M-Pesa prompts not completed</p><p class="text-lg font-bold">{{ $unpaid['failed']['count'] }} · {{ $kes($unpaid['failed']['total']) }}</p></div>
                <div class="p-3 rounded-lg bg-amber-50"><p class="text-gray-600">Still waiting for payment</p><p class="text-lg font-bold">{{ $unpaid['pending']['count'] }} · {{ $kes($unpaid['pending']['total']) }}</p></div>
                <div class="p-3 rounded-lg bg-gray-50"><p class="text-gray-600">Cancelled</p><p class="text-lg font-bold">{{ $unpaid['cancelled']['count'] }} · {{ $kes($unpaid['cancelled']['total']) }}</p></div>
            </div>
        </x-dash.panel>
    @endif
</div>

<script>
    function filterRows(input, tableId) {
        const q = input.value.trim().toLowerCase();
        document.querySelectorAll('#' + tableId + ' tbody tr[data-search]').forEach(tr => {
            tr.dataset.hideSearch = q && !tr.dataset.search.includes(q) ? '1' : '';
            tr.style.display = tr.dataset.hideSearch || tr.dataset.hideStatus ? 'none' : '';
        });
    }
    function filterStatus(status, btn) {
        document.querySelectorAll('.status-btn').forEach(b => { b.classList.remove('bg-gray-900', 'text-white', 'border-gray-900'); b.classList.add('bg-white', 'text-gray-700', 'border-gray-300'); });
        btn.classList.add('bg-gray-900', 'text-white', 'border-gray-900'); btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
        document.querySelectorAll('#stock-table tbody tr[data-status]').forEach(tr => {
            tr.dataset.hideStatus = status && tr.dataset.status !== status ? '1' : '';
            tr.style.display = tr.dataset.hideSearch || tr.dataset.hideStatus ? 'none' : '';
        });
    }
</script>
@endsection
