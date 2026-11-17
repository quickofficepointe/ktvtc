@extends('reports.pdf.layout')

@php
    use App\Support\SvgChart;
    use App\Services\Reports\CafeteriaReport;
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $showSales = in_array($part, ['full', 'sales'], true);
    $showStock = in_array($part, ['full', 'stock'], true);
    $showReorder = in_array($part, ['full', 'reorder'], true);
    $openDays = $byDay->where('count', '>', 0);
@endphp

@section('body')

{{-- ============================== SALES ============================== --}}
@if($showSales)
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-label">Total sales</div>
                <div class="kpi-value">{{ $kes($total) }}</div>
                <div class="kpi-hint">
                    @if($change !== null)
                        <span class="{{ $change >= 0 ? 'up' : 'down' }}">{{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%</span> vs {{ $prevFrom->format('j M') }}{{ $single ? '' : ' – ' . $prevTo->format('j M') }}
                    @else
                        No sales in the previous period
                    @endif
                </div>
            </td>
            <td>
                <div class="kpi-label">Transactions</div>
                <div class="kpi-value">{{ number_format($count) }}</div>
                <div class="kpi-hint">Average {{ $kes($average) }} each</div>
            </td>
            <td>
                <div class="kpi-label">Gross profit (costed items)</div>
                <div class="kpi-value">{{ $kes($profit) }}</div>
                <div class="kpi-hint">{{ $costedRevenue > 0 ? round($profit / $costedRevenue * 100, 1) . '% margin on ' . $kes($costedRevenue) : 'Add cost prices to products' }}</div>
            </td>
            <td>
                @if($single)
                    <div class="kpi-label">Busiest hour</div>
                    @php $peak = $hours->sortByDesc('total')->first(); @endphp
                    <div class="kpi-value">{{ $peak && $peak['total'] > 0 ? $peak['label'] : '—' }}</div>
                    <div class="kpi-hint">{{ $peak ? $kes($peak['total']) . ' · ' . $peak['count'] . ' sales' : '' }}</div>
                @else
                    <div class="kpi-label">Best day</div>
                    <div class="kpi-value">{{ $bestDay && $bestDay['total'] > 0 ? \Carbon\Carbon::parse($bestDay['date'])->format('D j M') : '—' }}</div>
                    <div class="kpi-hint">{{ $bestDay ? $kes($bestDay['total']) . ' · daily average ' . $kes($openDays->count() ? $total / $openDays->count() : 0) : '' }}</div>
                @endif
            </td>
        </tr>
    </table>

    <h2>Sales by channel</h2>
    <table width="100%"><tr>
        <td width="44%" style="vertical-align: top; padding-right: 10px;">
            <table class="grid">
                <thead><tr><th>Channel</th><th class="r">Sales</th><th class="r">Amount</th><th class="r">Share</th></tr></thead>
                <tbody>
                @forelse($channels as $c)
                    <tr>
                        <td><span class="swatch" style="background: {{ $c['color'] }}"></span>{{ $c['label'] }}</td>
                        <td class="r">{{ number_format($c['count']) }}</td>
                        <td class="r">{{ $n($c['total']) }}</td>
                        <td class="r muted">{{ $total > 0 ? round($c['total'] / $total * 100) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="c muted">No sales in this period.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr><td>Total</td><td class="r">{{ number_format($count) }}</td><td class="r">{{ $n($total) }}</td><td></td></tr></tfoot>
            </table>
            <p class="note">Student card sales spend money parents loaded earlier as top-ups.</p>
        </td>
        <td style="vertical-align: top;">
            @if($single)
                <h3 style="margin-top: 0;">Sales through the day (KES)</h3>
                <img class="chart" src="{{ SvgChart::bars($hours->pluck('label')->all(), [['label' => 'Sales', 'color' => '#b91c1c', 'values' => $hours->pluck('total')->all()]], true, 440, 190) }}">
            @else
                <h3 style="margin-top: 0;">Sales each day by channel (KES)</h3>
                <img class="chart" src="{{ SvgChart::bars($days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('j M'))->all(), $channels->map(fn ($c) => ['label' => $c['label'], 'color' => $c['color'], 'values' => array_values($c['days'])])->all(), true, 440, 200) }}">
            @endif
        </td>
    </tr></table>

    <h2>Items sold</h2>
    <table class="grid">
        <thead><tr><th>#</th><th>Item</th><th>Category</th><th class="r">Qty</th><th class="r">Avg price</th><th class="r">Sales (KES)</th><th class="r">Cost</th><th class="r">Profit</th><th class="r">Margin</th></tr></thead>
        <tbody>
        @forelse($items as $i => $it)
            <tr>
                <td class="muted">{{ $i + 1 }}</td>
                <td>{{ $it['name'] }}</td>
                <td class="muted">{{ $it['category'] }}</td>
                <td class="r">{{ $n($it['qty']) }}</td>
                <td class="r">{{ $n($it['avg_price']) }}</td>
                <td class="r bold">{{ $n($it['revenue']) }}</td>
                <td class="r muted">{{ $it['cost'] !== null ? $n($it['cost']) : '—' }}</td>
                <td class="r">{{ $it['profit'] !== null ? $n($it['profit']) : '—' }}</td>
                <td class="r muted">{{ $it['margin'] !== null ? $it['margin'] . '%' : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="c muted">No items sold in this period.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr>
            <td></td><td>Total ({{ $items->count() }} items)</td><td></td><td class="r">{{ $n($items->sum('qty')) }}</td><td></td>
            <td class="r">{{ $n($itemsTotal) }}</td><td class="r">{{ $n($cost) }}</td><td class="r">{{ $n($profit) }}</td><td></td>
        </tr></tfoot>
    </table>
    @if($unitemised > 0.5)
        <p class="note">{{ $kes($unitemised) }} of sales were recorded as an amount without items (e.g. a custom card amount), so they are in the totals but not in this list.</p>
    @endif
    <p class="note">Cost and profit use each product's cost price; "—" means no cost price is set for that product.</p>

    @if($items->isNotEmpty())
        <table width="100%" class="keep" style="margin-top: 8px;"><tr>
            <td width="50%" style="vertical-align: top; padding-right: 8px;">
                <h3>Top 10 by sales (KES)</h3>
                <img class="chart" src="{{ SvgChart::hbars($topByRevenue->pluck('name')->all(), $topByRevenue->pluck('revenue')->all(), '#b91c1c', 360) }}">
            </td>
            <td width="50%" style="vertical-align: top;">
                <h3>Top 10 by quantity</h3>
                <img class="chart" src="{{ SvgChart::hbars($topByQty->pluck('name')->all(), $topByQty->pluck('qty')->all(), '#2563eb', 360) }}">
            </td>
        </tr></table>
    @endif

    <div class="keep">
        <h2>Who sold</h2>
        <table class="grid">
            <thead><tr><th>Staff / point of sale</th><th class="r">Sales</th><th class="r">Cash</th><th class="r">M-Pesa</th><th class="r">Student card</th><th class="r">Total (KES)</th></tr></thead>
            <tbody>
            @foreach($cashiers as $c)
                <tr><td>{{ $c['name'] }}</td><td class="r">{{ number_format($c['count']) }}</td><td class="r">{{ $n($c['cash']) }}</td><td class="r">{{ $n($c['mpesa']) }}</td><td class="r">{{ $n($c['card']) }}</td><td class="r bold">{{ $n($c['total']) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if(!$single)
        <h2>Day by day</h2>
        @php $chCols = $channels->pluck('label', 'key'); @endphp
        <table class="grid">
            <thead><tr><th>Date</th>@foreach($chCols as $label)<th class="r">{{ $label }}</th>@endforeach<th class="r">Sales</th><th class="r">Total (KES)</th></tr></thead>
            <tbody>
            @foreach($byDay as $d)
                @continue($d['count'] === 0)
                <tr>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($d['date'])->format('D j M') }}</td>
                    @foreach($chCols as $key => $label)<td class="r">{{ $d['channels'][$key] ? $n($d['channels'][$key]) : '–' }}</td>@endforeach
                    <td class="r muted">{{ $d['count'] }}</td>
                    <td class="r bold">{{ $n($d['total']) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><td>Total</td>@foreach($channels as $c)<td class="r">{{ $n($c['total']) }}</td>@endforeach<td class="r">{{ number_format($count) }}</td><td class="r">{{ $n($total) }}</td></tr></tfoot>
        </table>

        @if($days->count() <= 31)
            <h2>Items sold each day</h2>
            <table class="grid">
                <thead><tr><th width="70">Date</th><th>Items (quantity)</th><th class="r" width="70">Total (KES)</th></tr></thead>
                <tbody>
                @foreach($byDay as $d)
                    @continue($d['count'] === 0)
                    <tr>
                        <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($d['date'])->format('D j M') }}</td>
                        <td>{{ $d['items']->map(fn ($i) => $i['name'] . ' ' . $n($i['qty']))->implode(', ') ?: '—' }}</td>
                        <td class="r">{{ $n($d['total']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endif

    <div class="keep">
        <h2>Trends</h2>
        <table width="100%"><tr>
            <td width="50%" style="vertical-align: top; padding-right: 8px;">
                <h3>Last 12 weeks (KES per week)</h3>
                <img class="chart" src="{{ SvgChart::line($weeks->pluck('label')->all(), [['label' => 'Sales', 'color' => '#b91c1c', 'values' => $weeks->pluck('total')->all()]], 360, 180) }}">
            </td>
            <td width="50%" style="vertical-align: top;">
                <h3>Last 6 months (KES per month)</h3>
                <img class="chart" src="{{ SvgChart::bars($months->pluck('label')->all(), [['label' => 'Sales', 'color' => '#2563eb', 'values' => $months->pluck('total')->all()]], true, 360, 180) }}">
            </td>
        </tr></table>
        @if(!$single)
            <h3>Average sales by day of the week (days with sales)</h3>
            <img class="chart" src="{{ SvgChart::bars($weekdays->pluck('day')->all(), [['label' => 'Average', 'color' => '#16a34a', 'values' => $weekdays->pluck('average')->all()]], true, 720, 150) }}">
        @endif
    </div>

    @php $u = collect($unpaid); @endphp
    @if($u->sum('count') > 0 || $purchases['total'] > 0)
        <div class="keep">
            <h2>Also in this period</h2>
            <table class="grid">
                <tbody>
                    <tr><td>M-Pesa prompts the customer did not complete (failed)</td><td class="r">{{ $unpaid['failed']['count'] }}</td><td class="r">{{ $n($unpaid['failed']['total']) }}</td></tr>
                    <tr><td>Sales still waiting for payment (pending)</td><td class="r">{{ $unpaid['pending']['count'] }}</td><td class="r">{{ $n($unpaid['pending']['total']) }}</td></tr>
                    <tr><td>Cancelled sales</td><td class="r">{{ $unpaid['cancelled']['count'] }}</td><td class="r">{{ $n($unpaid['cancelled']['total']) }}</td></tr>
                    <tr><td>Stock bought (purchase orders and direct purchases)</td><td class="r">{{ $purchases['orders'] + $purchases['direct'] }}</td><td class="r">{{ $n($purchases['total']) }}</td></tr>
                </tbody>
            </table>
            <p class="note">Unpaid prompts are not counted as sales and did not take stock.</p>
        </div>
    @endif
@endif

{{-- ============================== STOCK ============================== --}}
@if($showStock)
    @if($showSales)<div class="page-break"></div>@endif
    <h2>Stock on hand <span class="muted" style="font-weight: normal; font-size: 9px;">as at {{ $generatedAt->format('j M Y, H:i') }}</span></h2>
    <table class="kpis" style="margin-bottom: 8px;">
        <tr>
            <td><div class="kpi-label">Stocked items</div><div class="kpi-value">{{ $stock->count() }}</div></td>
            <td><div class="kpi-label">Stock value (at cost)</div><div class="kpi-value">{{ $kes($stockValue) }}</div></td>
            <td><div class="kpi-label">Out of stock</div><div class="kpi-value">{{ $stock->where('status', 'Out of stock')->count() }}</div></td>
            <td><div class="kpi-label">Need buying</div><div class="kpi-value">{{ $reorder->count() }}</div><div class="kpi-hint">About {{ $kes($reorderCost) }}</div></td>
        </tr>
    </table>
    <table class="grid">
        <thead><tr><th>Item</th><th>Category</th><th class="r">In stock</th><th class="r">Reorder at</th><th class="r">Sells / day</th><th class="r">Days left</th><th class="r">Cost</th><th class="r">Value (KES)</th><th class="c">Status</th></tr></thead>
        <tbody>
        @foreach($stock as $s)
            <tr>
                <td>{{ $s['name'] }}</td>
                <td class="muted">{{ $s['category'] }}</td>
                <td class="r bold">{{ $n($s['stock']) }} <span class="muted">{{ $s['unit'] }}</span></td>
                <td class="r muted">{{ $s['reorder_level'] > 0 ? $n($s['reorder_level']) : '—' }}</td>
                <td class="r">{{ $s['per_day'] > 0 ? $n($s['per_day']) : '—' }}</td>
                <td class="r">{{ $s['days_left'] !== null ? $n($s['days_left']) : '—' }}</td>
                <td class="r muted">{{ $s['cost_price'] > 0 ? $n($s['cost_price']) : '—' }}</td>
                <td class="r">{{ $n($s['value']) }}</td>
                <td class="c"><span class="tag {{ $s['status'] === 'OK' ? 'tag-green' : ($s['status'] === 'Low' ? 'tag-amber' : 'tag-red') }}">{{ $s['status'] }}</span></td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="7">Total stock value</td><td class="r">{{ $n($stockValue) }}</td><td></td></tr></tfoot>
    </table>
    <p class="note">"Sells / day" is the average over the last {{ CafeteriaReport::SPEED_WINDOW }} days. Items made in the kitchen (production items) and items not tracked in stock are not listed.</p>

    @if($slow->isNotEmpty() && $showSales)
        <div class="keep">
            <h3>Slow movers in this period (in stock, sold least)</h3>
            <table class="grid">
                <thead><tr><th>Item</th><th class="r">Sold in period</th><th class="r">In stock</th><th class="r">Value tied up (KES)</th></tr></thead>
                <tbody>@foreach($slow as $s)<tr><td>{{ $s['name'] }}</td><td class="r">{{ $n($s['sold']) }}</td><td class="r">{{ $n($s['stock']) }} {{ $s['unit'] }}</td><td class="r">{{ $n($s['value']) }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    @endif
@endif

{{-- ============================== PURCHASE LIST ============================== --}}
@if($showReorder)
    @if($showSales || $showStock)<div class="page-break"></div>@endif
    <h2>What needs to be purchased <span class="muted" style="font-weight: normal; font-size: 9px;">as at {{ $generatedAt->format('j M Y, H:i') }}</span></h2>
    <p class="note" style="margin-bottom: 6px;">An item is listed when its stock is at or below its reorder level, or will run out within {{ CafeteriaReport::DAYS_WARNING }} days at the current sales rate. The suggested quantity covers about {{ CafeteriaReport::DAYS_COVER }} days of sales (or twice the reorder level).</p>
    <table class="grid">
        <thead><tr><th>#</th><th>Item</th><th>Why</th><th class="r">In stock</th><th class="r">Sells / day</th><th class="r">Buy</th><th class="r">Unit cost</th><th class="r">Est. cost (KES)</th><th class="c" width="50">Bought ✓</th></tr></thead>
        <tbody>
        @forelse($reorder as $i => $s)
            <tr>
                <td class="muted">{{ $i + 1 }}</td>
                <td class="bold">{{ $s['name'] }}</td>
                <td class="muted">{{ $s['why'] }}</td>
                <td class="r">{{ $n($s['stock']) }}</td>
                <td class="r">{{ $s['per_day'] > 0 ? $n($s['per_day']) : '—' }}</td>
                <td class="r bold">{{ $n($s['order_qty']) }} {{ $s['unit'] }}</td>
                <td class="r muted">{{ $s['cost_price'] > 0 ? $n($s['cost_price']) : '—' }}</td>
                <td class="r">{{ $s['cost_price'] > 0 ? $n($s['order_cost']) : '—' }}</td>
                <td></td>
            </tr>
        @empty
            <tr><td colspan="9" class="c muted">Nothing needs buying right now.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><td colspan="7">Estimated total</td><td class="r">{{ $n($reorderCost) }}</td><td></td></tr></tfoot>
    </table>
    @if($reorder->where('cost_price', '<=', 0)->count())
        <p class="note">{{ $reorder->where('cost_price', '<=', 0)->count() }} item(s) have no cost price, so they are not in the estimated total.</p>
    @endif

    @if($outIdle->isNotEmpty())
        <h3 style="margin-top: 14px;">Out of stock, but not sold in the last {{ CafeteriaReport::SPEED_WINDOW }} days ({{ $outIdle->count() }}) — buy only if still needed</h3>
        <p style="line-height: 1.5;">{{ $outIdle->pluck('name')->implode(' · ') }}</p>
        <p class="note">Set a reorder level on any of these that should always be in stock, and they will be listed above automatically.</p>
    @endif

    <table width="100%" style="margin-top: 34px;" class="keep"><tr>
        <td width="33%">Prepared by: ______________________</td>
        <td width="33%">Approved by: ______________________</td>
        <td width="33%">Date: ______________________</td>
    </tr></table>
@endif

@endsection
