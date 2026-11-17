@extends('reports.pdf.layout')

@php
    use App\Support\SvgChart;
    use App\Services\Reports\CardsReport;
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $t = $wallets['transport'];
    $c = $wallets['canteen'];
    $show = fn ($w) => $part === 'full' || $part === $w;
    $dayLabels = $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('j M'))->all();
@endphp

@section('body')

@if($part !== 'balances')
    <table class="kpis">
        <tr>
            <td><div class="kpi-label">Transport top-ups</div><div class="kpi-value">{{ $kes($t['topups']) }}</div><div class="kpi-hint">{{ $t['topupCount'] }} top-ups</div></td>
            <td><div class="kpi-label">Fares paid out</div><div class="kpi-value">{{ $kes($fares['amount']) }}</div><div class="kpi-hint">{{ $fares['count'] }} fares · fees {{ $kes($fares['fees']) }}</div></td>
            <td><div class="kpi-label">Canteen top-ups</div><div class="kpi-value">{{ $kes($c['topups']) }}</div><div class="kpi-hint">{{ $c['topupCount'] }} top-ups</div></td>
            <td><div class="kpi-label">Canteen spending</div><div class="kpi-value">{{ $kes($c['spent']) }}</div><div class="kpi-hint">{{ $c['spentCount'] }} purchases</div></td>
        </tr>
    </table>
    <table class="kpis" style="margin-top: 6px;">
        <tr>
            <td><div class="kpi-label">Held on cards now · transport</div><div class="kpi-value">{{ $kes($t['held']) }}</div><div class="kpi-hint">{{ $t['cardsWithMoney'] }} cards with money</div></td>
            <td><div class="kpi-label">Held on cards now · canteen</div><div class="kpi-value">{{ $kes($c['held']) }}</div><div class="kpi-hint">{{ $c['cardsWithMoney'] }} cards with money</div></td>
            <td><div class="kpi-label">Cards</div><div class="kpi-value">{{ number_format($cardCount) }}</div><div class="kpi-hint">{{ $activeCards }} used in this period · {{ $blocked }} blocked</div></td>
            <td><div class="kpi-label">Low canteen balance</div><div class="kpi-value">{{ $low->count() }}</div><div class="kpi-hint">Below their low-balance level</div></td>
        </tr>
    </table>
    <p class="note">Money on the cards belongs to the students: the college holds it until it is spent or paid out.</p>

    @foreach(['transport', 'canteen'] as $key)
        @continue(!$show($key))
        @php $w = $wallets[$key]; @endphp
        <div class="{{ $loop->first ? '' : 'page-break' }}">
        <h2>{{ $w['label'] }} wallet</h2>
        <table width="100%"><tr>
            <td width="46%" style="vertical-align: top; padding-right: 10px;">
                <table class="grid">
                    <thead><tr><th>Movement</th><th class="r">Count</th><th class="r">In</th><th class="r">Out</th></tr></thead>
                    <tbody>
                        <tr><td class="bold">Opening balance {{ $from->format('j M') }}</td><td></td><td class="r bold" colspan="2">{{ $w['opening'] !== null ? $n($w['opening']) : 'n/a' }}</td></tr>
                        @foreach($w['lines'] as $l)
                            <tr><td>{{ $l['label'] }}</td><td class="r muted">{{ $l['count'] }}</td><td class="r">{{ $l['in'] ? $n($l['in']) : '' }}</td><td class="r">{{ $l['out'] ? $n($l['out']) : '' }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td>Total in / out</td><td></td><td class="r">{{ $n($w['in']) }}</td><td class="r">{{ $n($w['out']) }}</td></tr>
                        <tr><td>Closing balance {{ $to->format('j M') }}</td><td></td><td class="r" colspan="2">{{ $w['closing'] !== null ? $n($w['closing']) : 'n/a' }}</td></tr>
                    </tfoot>
                </table>
                @if($w['opening'] === null)
                    <p class="note">Cards were split into transport and canteen wallets on {{ $splitAt?->format('j M Y') }}; per-wallet balances before that date are not available.</p>
                @endif
            </td>
            <td style="vertical-align: top;">
                @if(!$single)
                    <h3 style="margin-top: 0;">{{ $key === 'transport' ? 'Top-ups and fares paid each day (KES)' : 'Top-ups and spending each day (KES)' }}</h3>
                    <img class="chart" src="{{ SvgChart::bars($dayLabels, [
                        ['label' => 'Top-ups', 'color' => '#16a34a', 'values' => $w['daily']->pluck('in')->all()],
                        ['label' => $key === 'transport' ? 'Fares paid' : 'Spent', 'color' => '#b91c1c', 'values' => $w['daily']->pluck('out')->all()],
                    ], false, 420, 210) }}">
                @endif
            </td>
        </tr></table>

        @if($key === 'transport')
            <h3>Fares handed out</h3>
            <table class="grid">
                <thead><tr><th>Date</th><th class="r">Fares</th><th class="r">Amount</th><th class="r">Withdrawal fees</th><th class="r">Total taken from cards</th></tr></thead>
                <tbody>
                @forelse($fares['byDay'] as $f)
                    <tr><td>{{ \Carbon\Carbon::parse($f->d)->format('D j M Y') }}</td><td class="r">{{ $f->n }}</td><td class="r">{{ $n($f->amount) }}</td><td class="r">{{ $n($f->fees) }}</td><td class="r">{{ $n($f->amount + $f->fees) }}</td></tr>
                @empty
                    <tr><td colspan="5" class="c muted">No fares handed out in this period.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr><td>Total</td><td class="r">{{ $fares['count'] }}</td><td class="r">{{ $n($fares['amount']) }}</td><td class="r">{{ $n($fares['fees']) }}</td><td class="r">{{ $n($fares['total']) }}</td></tr></tfoot>
            </table>
            @if($fares['reversedCount'])<p class="note">{{ $fares['reversedCount'] }} fare records ({{ $kes($fares['reversedTotal']) }}) were reversed in this period and their money returned to the cards.</p>@endif
        @else
            <table width="100%"><tr>
                <td width="50%" style="vertical-align: top; padding-right: 8px;">
                    <h3>Where canteen money was spent</h3>
                    <table class="grid">
                        <thead><tr><th>Where</th><th class="r">Purchases</th><th class="r">KES</th></tr></thead>
                        <tbody>@forelse($canteenWhere as $x)<tr><td>{{ $x['label'] }}</td><td class="r">{{ $x['count'] }}</td><td class="r">{{ $n($x['total']) }}</td></tr>@empty<tr><td colspan="3" class="c muted">No spending.</td></tr>@endforelse</tbody>
                    </table>
                </td>
                <td width="50%" style="vertical-align: top;">
                    <h3>How parents topped up</h3>
                    <table class="grid">
                        <thead><tr><th>Way</th><th class="r">Transport</th><th class="r">Canteen</th></tr></thead>
                        <tbody>@forelse($topupSources as $x)<tr><td>{{ $x['label'] }}</td><td class="r">{{ $n($x['transport']) }}</td><td class="r">{{ $n($x['canteen']) }}</td></tr>@empty<tr><td colspan="3" class="c muted">No top-ups.</td></tr>@endforelse</tbody>
                    </table>
                </td>
            </tr></table>
        @endif
        </div>
    @endforeach

    @if($part === 'full')
        <div class="keep">
            <h2>By class</h2>
            <table class="grid">
                <thead><tr><th>Class</th><th class="r">Cards</th><th class="r">Top-ups</th><th class="r">Canteen spent</th><th class="r">Fares paid</th><th class="r">Transport held</th><th class="r">Canteen held</th></tr></thead>
                <tbody>@foreach($byClass as $x)<tr><td>{{ $x['class'] }}</td><td class="r">{{ $x['cards'] }}</td><td class="r">{{ $n($x['topups']) }}</td><td class="r">{{ $n($x['canteen']) }}</td><td class="r">{{ $n($x['fares']) }}</td><td class="r">{{ $n($x['transport_held']) }}</td><td class="r">{{ $n($x['canteen_held']) }}</td></tr>@endforeach</tbody>
                <tfoot><tr><td>Total</td><td class="r">{{ $byClass->sum('cards') }}</td><td class="r">{{ $n($byClass->sum('topups')) }}</td><td class="r">{{ $n($byClass->sum('canteen')) }}</td><td class="r">{{ $n($byClass->sum('fares')) }}</td><td class="r">{{ $n($byClass->sum('transport_held')) }}</td><td class="r">{{ $n($byClass->sum('canteen_held')) }}</td></tr></tfoot>
            </table>
        </div>
        <div class="keep">
            <h2>Last 6 months</h2>
            <img class="chart" src="{{ SvgChart::bars($months->pluck('label')->all(), [
                ['label' => 'Transport top-ups', 'color' => '#2563eb', 'values' => $months->pluck('transport_in')->all()],
                ['label' => 'Fares paid', 'color' => '#93c5fd', 'values' => $months->pluck('fares')->all()],
                ['label' => 'Canteen top-ups', 'color' => '#16a34a', 'values' => $months->pluck('canteen_in')->all()],
                ['label' => 'Canteen spent', 'color' => '#86efac', 'values' => $months->pluck('canteen_spent')->all()],
            ], false, 720, 210) }}">
        </div>
    @endif
@endif

@if($part === 'full' || $part === 'balances')
    @if($part === 'full')<div class="page-break"></div>@endif
    <h2>Card balances <span class="muted" style="font-weight: normal; font-size: 9px;">as at {{ $generatedAt->format('j M Y, H:i') }}</span></h2>
    <table class="grid">
        <thead><tr><th>Adm. no.</th><th>Student</th><th>Class</th><th>Parent phone</th><th class="r">Transport</th><th class="r">Canteen</th><th>Last used</th><th>Last top-up</th></tr></thead>
        <tbody>
        @foreach($cards as $x)
            <tr>
                <td style="white-space: nowrap;">{{ $x->admission ?: '—' }}</td>
                <td>{{ $x->name ?: '—' }}@if($x->is_blocked) <span class="tag tag-red">Blocked</span>@endif</td>
                <td class="muted">{{ $x->class ?: '—' }}</td>
                <td class="muted" style="white-space: nowrap;">{{ $x->parent_phone ?: '—' }}</td>
                <td class="r">{{ $n($x->transport_balance) }}</td>
                <td class="r {{ $x->canteen_balance < $x->low_at ? 'down bold' : '' }}">{{ $n($x->canteen_balance) }}</td>
                <td class="muted" style="white-space: nowrap;">{{ $x->last_used_at ? \Carbon\Carbon::parse($x->last_used_at)->format('j M Y') : 'Never' }}</td>
                <td class="muted" style="white-space: nowrap;">{{ $x->last_funded_at ? \Carbon\Carbon::parse($x->last_funded_at)->format('j M Y') : 'Never' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="4">Total ({{ $cards->count() }} cards)</td><td class="r">{{ $n($cards->sum('transport_balance')) }}</td><td class="r">{{ $n($cards->sum('canteen_balance')) }}</td><td colspan="2"></td></tr></tfoot>
    </table>
    <p class="note">Canteen balances in red are below the card's low-balance level ({{ CardsReport::LOW_BALANCE }} unless set on the card).</p>
@endif

@endsection
