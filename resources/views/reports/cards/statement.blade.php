@extends('reports.pdf.layout')

@php
    $n = fn ($v) => number_format((float) $v, 2);
    $c = $card;
@endphp

@section('body')
    <table width="100%" class="keep">
        <tr>
            <td width="55%" style="vertical-align: top;">
                <table class="grid">
                    <tbody>
                        <tr><td class="muted" width="36%">Student</td><td class="bold">{{ $c->name ?: '—' }}</td></tr>
                        <tr><td class="muted">Admission number</td><td>{{ $c->admission ?: '—' }}</td></tr>
                        <tr><td class="muted">Class</td><td>{{ trim(($c->class ?? '') . ' ' . ($c->stream ?? '')) ?: '—' }}</td></tr>
                        <tr><td class="muted">Card number</td><td>{{ $c->card_number ?: '—' }}</td></tr>
                        <tr><td class="muted">Parent / guardian</td><td>{{ trim(($c->parent_name ?? '') . ' ' . ($c->parent_phone ?? '')) ?: '—' }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td width="45%" style="vertical-align: top; padding-left: 12px;">
                <table class="kpis" style="margin: 0;"><tr>
                    <td style="width: 50%;"><div class="kpi-label">Transport now</div><div class="kpi-value" style="font-size: 13px;">KES {{ $n($c->transport_balance) }}</div></td>
                    <td style="width: 50%;"><div class="kpi-label">Canteen now</div><div class="kpi-value" style="font-size: 13px;">KES {{ $n($c->canteen_balance) }}</div></td>
                </tr></table>
                <p class="note">Top up via M-Pesa to till {{ config('kcb.tills.cafeteria', '8118445') }} with the admission number as the account (add T for transport, e.g. T{{ $c->admission }}).</p>
            </td>
        </tr>
    </table>

    @foreach($wallets as $w)
        <h2>{{ $w['label'] }} wallet</h2>
        <table class="grid">
            <thead><tr><th width="80">Date</th><th>What</th><th>Details</th><th class="r">In</th><th class="r">Out</th><th class="r">Balance</th></tr></thead>
            <tbody>
                @if($w['opening'] !== null)
                    <tr><td>{{ $from->format('j M Y') }}</td><td class="bold">Opening balance</td><td></td><td></td><td></td><td class="r bold">{{ $n($w['opening']) }}</td></tr>
                @endif
                @forelse($w['lines'] as $l)
                    <tr>
                        <td style="white-space: nowrap;">{{ $l['at']->format('j M, H:i') }}</td>
                        <td>{{ $l['what'] }}</td>
                        <td class="muted">{{ \Illuminate\Support\Str::limit((string) $l['detail'], 60) }}</td>
                        <td class="r">{{ $l['in'] ? $n($l['in']) : '' }}</td>
                        <td class="r">{{ $l['out'] ? $n($l['out']) : '' }}</td>
                        <td class="r">{{ $l['balance'] !== null ? $n($l['balance']) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="c muted">No {{ strtolower($w['label']) }} transactions in this period.</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr><td colspan="3">Totals · closing balance {{ $to->format('j M Y') }}</td><td class="r">{{ $n($w['in']) }}</td><td class="r">{{ $n($w['out']) }}</td><td class="r">{{ $n($w['closing']) }}</td></tr></tfoot>
        </table>
    @endforeach
    @unless($known)
        <p class="note">This card's money was split into transport and canteen on {{ $splitAt?->format('j M Y') }}; running balances are shown from that date on.</p>
    @endunless

    @if($fares->isNotEmpty())
        <h2>Fares handed out</h2>
        <table class="grid">
            <thead><tr><th>Date</th><th>How</th><th class="c">Status</th><th class="r">Fare</th><th class="r">Fee</th><th class="r">Total</th></tr></thead>
            <tbody>
            @foreach($fares as $f)
                <tr><td>{{ \Carbon\Carbon::parse($f->given_on)->format('j M Y') }}</td><td>{{ ucfirst($f->method) }}</td><td class="c">@if($f->status !== 'given')<span class="tag tag-red">{{ ucfirst($f->status) }}</span>@endif</td><td class="r">{{ $n($f->amount) }}</td><td class="r">{{ $n($f->withdrawal_fee) }}</td><td class="r">{{ $n($f->total_deducted) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
