@extends('reports.pdf.layout')

@php
    use App\Support\SvgChart;
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $showCollections = in_array($part, ['full', 'collections'], true);
    $showBalances = in_array($part, ['full', 'balances'], true);
    $showDefaulters = in_array($part, ['full', 'defaulters'], true);
    $b = $balances;
    $palette = ['#b91c1c', '#2563eb', '#16a34a', '#d97706', '#7c3aed', '#0891b2'];
@endphp

@section('body')

{{-- ============================== COLLECTIONS ============================== --}}
@if($showCollections)
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-label">Fees collected</div>
                <div class="kpi-value">{{ $kes($total) }}</div>
                <div class="kpi-hint">
                    @if($change !== null)
                        <span class="{{ $change >= 0 ? 'up' : 'down' }}">{{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%</span> vs {{ $prevFrom->format('j M') }} – {{ $prevTo->format('j M') }}
                    @else
                        Nothing collected in the previous period
                    @endif
                </div>
            </td>
            <td><div class="kpi-label">Payments</div><div class="kpi-value">{{ number_format($count) }}</div><div class="kpi-hint">From {{ number_format($students) }} students</div></td>
            <td><div class="kpi-label">Confirmed balances owed</div><div class="kpi-value">{{ $kes($b['confirmedTotal']) }}</div><div class="kpi-hint">{{ $b['confirmed']->count() }} students · as at today</div></td>
            <td><div class="kpi-label">Not yet on a student</div><div class="kpi-value">{{ $kes($unmatched['total']) }}</div><div class="kpi-hint">{{ $unmatched['count'] }} payments to assign</div></td>
        </tr>
    </table>

    @if(!$single)
        <h2>Collected each day (KES)</h2>
        <img class="chart" src="{{ SvgChart::bars($byDay->map(fn ($d) => \Carbon\Carbon::parse($d['date'])->format('j M'))->all(), [['label' => 'Collected', 'color' => '#b91c1c', 'values' => $byDay->pluck('total')->all()]], true, 720, 190) }}">
    @endif

    <table width="100%" style="margin-top: 6px;"><tr>
        <td width="50%" style="vertical-align: top; padding-right: 8px;">
            <h2>How it was paid</h2>
            <table class="grid">
                <thead><tr><th>Method</th><th class="r">Payments</th><th class="r">KES</th><th class="r">Share</th></tr></thead>
                <tbody>@forelse($byMethod as $i => $m)<tr><td><span class="swatch" style="background: {{ $palette[$i % 6] }}"></span>{{ $m['label'] }}</td><td class="r">{{ $m['count'] }}</td><td class="r">{{ $n($m['total']) }}</td><td class="r muted">{{ $m['share'] }}%</td></tr>@empty<tr><td colspan="4" class="c muted">No payments.</td></tr>@endforelse</tbody>
            </table>
        </td>
        <td width="50%" style="vertical-align: top;">
            <h2>By campus</h2>
            <table class="grid">
                <thead><tr><th>Campus</th><th class="r">Payments</th><th class="r">KES</th><th class="r">Share</th></tr></thead>
                <tbody>@forelse($byCampus as $m)<tr><td>{{ $m['label'] }}</td><td class="r">{{ $m['count'] }}</td><td class="r">{{ $n($m['total']) }}</td><td class="r muted">{{ $m['share'] }}%</td></tr>@empty<tr><td colspan="4" class="c muted">No payments.</td></tr>@endforelse</tbody>
            </table>
        </td>
    </tr></table>

    <table width="100%" class="keep"><tr>
        <td width="58%" style="vertical-align: top; padding-right: 8px;">
            <h2>By course (top 15)</h2>
            <table class="grid">
                <thead><tr><th>Course</th><th class="r">Payments</th><th class="r">KES</th></tr></thead>
                <tbody>@forelse($byCourse as $m)<tr><td>{{ $m['label'] }}</td><td class="r">{{ $m['count'] }}</td><td class="r">{{ $n($m['total']) }}</td></tr>@empty<tr><td colspan="3" class="c muted">No payments.</td></tr>@endforelse</tbody>
            </table>
        </td>
        <td width="42%" style="vertical-align: top;">
            <h2>Who recorded it</h2>
            <table class="grid">
                <thead><tr><th>Recorded by</th><th class="r">Payments</th><th class="r">KES</th></tr></thead>
                <tbody>@foreach($byRecorder as $m)<tr><td>{{ $m['label'] }}</td><td class="r">{{ $m['count'] }}</td><td class="r">{{ $n($m['total']) }}</td></tr>@endforeach</tbody>
            </table>
            @if($reversed['count'] || $adjustments->isNotEmpty())
                <h3>Also in this period</h3>
                <table class="grid">
                    <tbody>
                        @if($reversed['count'])<tr><td>Payments reversed</td><td class="r">{{ $reversed['count'] }}</td><td class="r">{{ $n($reversed['total']) }}</td></tr>@endif
                        @foreach($adjustments as $a)
                            <tr><td>{{ $a['label'] }}</td><td class="r">{{ $a['count'] }}</td><td class="r">{{ $n($a['fees'] ?: $a['paid']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </td>
    </tr></table>

    <div class="keep">
        <h2>Terms</h2>
        <table class="grid">
            <thead><tr><th>Term</th><th>Dates</th><th class="r">Collected in term</th><th class="r">Students billed*</th><th class="r">Billed*</th><th class="r">Paid*</th><th class="r">Still owed*</th><th class="r">Collected %*</th></tr></thead>
            <tbody>
            @foreach($terms as $t)
                <tr>
                    <td class="{{ $t['current'] ? 'bold' : '' }}">{{ $t['name'] }}{{ $t['current'] ? ' (current)' : '' }}</td>
                    <td class="muted">{{ $t['from']->format('j M') }} – {{ $t['to']->format('j M Y') }}</td>
                    <td class="r bold">{{ $n($t['collected']) }}</td>
                    <td class="r">{{ $t['registered'] }}</td>
                    <td class="r">{{ $n($t['billed']) }}</td>
                    <td class="r">{{ $n($t['paid']) }}</td>
                    <td class="r">{{ $n($t['balance']) }}</td>
                    <td class="r">{{ $t['rate'] !== null ? $t['rate'] . '%' : '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="note">* Confirmed balances only: terms billed by the new system, and old balances accounts has confirmed. "Collected in term" is all fee money received between the term's dates.</p>
    </div>

    <div class="keep">
        <h2>Last 12 months (KES collected per month)</h2>
        <img class="chart" src="{{ SvgChart::bars($months->pluck('label')->all(), [['label' => 'Collected', 'color' => '#2563eb', 'values' => $months->pluck('total')->all()]], true, 720, 180) }}">
    </div>

    @if($unmatched['count'])
        <div class="keep">
            <h2>Payments not yet on a student ({{ $unmatched['count'] }} · {{ $kes($unmatched['total']) }})</h2>
            <table class="grid">
                <thead><tr><th>Date</th><th>Receipt</th><th>Paid by</th><th>Account typed</th><th>Why</th><th class="r">KES</th></tr></thead>
                <tbody>
                @foreach($unmatched['rows'] as $u)
                    <tr><td style="white-space: nowrap;">{{ $u->transaction_date ? \Carbon\Carbon::parse($u->transaction_date)->format('j M Y') : '—' }}</td><td>{{ $u->transaction_id }}</td><td>{{ trim($u->first_name . ' ' . $u->last_name) ?: '—' }}</td><td>{{ $u->account_reference ?: '(none)' }}</td><td class="muted">{{ \Illuminate\Support\Str::limit($u->status_reason, 60) }}</td><td class="r">{{ $n($u->transaction_amount) }}</td></tr>
                @endforeach
                </tbody>
            </table>
            @if($unmatched['count'] > $unmatched['rows']->count())<p class="note">Showing the oldest {{ $unmatched['rows']->count() }}. Assign them in Finance → KCB Payments.</p>@endif
        </div>
    @endif

    <div class="page-break"></div>
    <h2>Payments received ({{ number_format($count) }})</h2>
    @if($count <= 600)
        <table class="grid">
            <thead><tr><th>Date</th><th>Student</th><th>Name</th><th>Course</th><th>Method</th><th>Receipt / code</th><th class="r">KES</th></tr></thead>
            <tbody>
            @foreach($payments as $p)
                <tr>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($p->payment_date)->format('j M') }}</td>
                    <td>{{ $p->student_number ?: '—' }}</td>
                    <td>{{ $p->student_name ?: ($p->payer_name ?: '—') }}</td>
                    <td class="muted">{{ \Illuminate\Support\Str::limit($p->course_name, 32) }}</td>
                    <td class="muted">{{ $p->payment_method === 'kcb' ? 'KCB' : $p->method_label }}</td>
                    <td class="muted">{{ $p->transaction_code ?: ($p->receipt_number ?: '—') }}</td>
                    <td class="r">{{ $n($p->amount) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="6">Total</td><td class="r">{{ $n($total) }}</td></tr></tfoot>
        </table>
    @else
        <p class="note">{{ number_format($count) }} payments are too many to list here; choose a shorter period to print the full list.</p>
    @endif
@endif

{{-- ============================== BALANCES ============================== --}}
@if($showBalances)
    @if($showCollections)<div class="page-break"></div>@endif
    <h2>Fee balances <span class="muted" style="font-weight: normal; font-size: 9px;">as at {{ $generatedAt->format('j M Y, H:i') }}</span></h2>
    <table class="kpis" style="margin-bottom: 8px;">
        <tr>
            <td><div class="kpi-label">Confirmed balances owed</div><div class="kpi-value">{{ $kes($b['confirmedTotal']) }}</div><div class="kpi-hint">{{ $b['confirmed']->count() }} students owe</div></td>
            <td><div class="kpi-label">Paid ahead (credit)</div><div class="kpi-value">{{ $kes($b['creditTotal']) }}</div><div class="kpi-hint">{{ $b['creditCount'] }} students</div></td>
            <td><div class="kpi-label">Old balances to confirm</div><div class="kpi-value">{{ $kes($b['unconfirmedTotal']) }}</div><div class="kpi-hint">{{ number_format($b['unconfirmedCount']) }} students, not counted</div></td>
            <td><div class="kpi-label">Open enrollments</div><div class="kpi-value">{{ number_format($b['openCount']) }}</div><div class="kpi-hint">Active, pending, suspended, deferred</div></td>
        </tr>
    </table>

    <table width="100%" class="keep"><tr>
        <td width="45%" style="vertical-align: top; padding-right: 8px;">
            <h3>How long confirmed balances have been owed</h3>
            <table class="grid">
                <thead><tr><th>Owed for</th><th class="r">Students</th><th class="r">KES</th></tr></thead>
                <tbody>@foreach($b['ageing'] as $a)<tr><td>{{ $a['bucket'] }}</td><td class="r">{{ $a['count'] }}</td><td class="r">{{ $n($a['total']) }}</td></tr>@endforeach</tbody>
                <tfoot><tr><td>Total</td><td class="r">{{ $b['confirmed']->count() }}</td><td class="r">{{ $n($b['confirmedTotal']) }}</td></tr></tfoot>
            </table>
            <p class="note">Counted from the start of the oldest unpaid term (or from when accounts confirmed the balance).</p>
        </td>
        <td width="55%" style="vertical-align: top;">
            <img class="chart" src="{{ SvgChart::hbars($b['ageing']->pluck('bucket')->all(), $b['ageing']->pluck('total')->all(), '#d97706', 400) }}">
        </td>
    </tr></table>

    <h2>Collection by course (confirmed balances)</h2>
    <table class="grid">
        <thead><tr><th>Course</th><th class="r">Students</th><th class="r">Fees billed</th><th class="r">Paid</th><th class="r">Owed</th><th class="r">Owing</th><th class="r">Collected</th></tr></thead>
        <tbody>
        @forelse($b['byCourse'] as $c)
            <tr><td>{{ $c['course'] }}</td><td class="r">{{ $c['students'] }}</td><td class="r">{{ $n($c['fees']) }}</td><td class="r">{{ $n($c['paid']) }}</td><td class="r bold">{{ $n($c['owed']) }}</td><td class="r">{{ $c['owing'] }}</td><td class="r">{{ $c['rate'] !== null ? $c['rate'] . '%' : '—' }}</td></tr>
        @empty
            <tr><td colspan="7" class="c muted">No confirmed balances yet.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><td>Total</td><td class="r">{{ $b['byCourse']->sum('students') }}</td><td class="r">{{ $n($b['byCourse']->sum('fees')) }}</td><td class="r">{{ $n($b['byCourse']->sum('paid')) }}</td><td class="r">{{ $n($b['byCourse']->sum('owed')) }}</td><td class="r">{{ $b['byCourse']->sum('owing') }}</td><td></td></tr></tfoot>
    </table>
    <p class="note">{{ number_format($b['unconfirmedCount']) }} older whole-course balances ({{ $kes($b['unconfirmedTotal']) }}) came from the old records and have not been checked by accounts, so they are left out of these figures. Confirm them in Finance → Fee Reminders.</p>
@endif

{{-- ============================== DEFAULTERS ============================== --}}
@if($showDefaulters)
    @if($showCollections || $showBalances)<div class="page-break"></div>@endif
    <h2>Students owing fees ({{ $b['confirmed']->count() }} · {{ $kes($b['confirmedTotal']) }}) <span class="muted" style="font-weight: normal; font-size: 9px;">as at {{ $generatedAt->format('j M Y') }}</span></h2>
    <table class="grid">
        <thead><tr><th>#</th><th>Student</th><th>Name</th><th>Course</th><th>Phone</th><th class="r">Fees</th><th class="r">Paid</th><th class="r">Balance</th><th class="r">Owed for</th><th>Last paid</th></tr></thead>
        <tbody>
        @forelse($b['confirmed'] as $i => $d)
            <tr>
                <td class="muted">{{ $i + 1 }}</td>
                <td style="white-space: nowrap;">{{ $d->student_number ?: '—' }}</td>
                <td>{{ $d->name }}</td>
                <td class="muted">{{ \Illuminate\Support\Str::limit($d->course, 28) }}</td>
                <td class="muted" style="white-space: nowrap;">{{ $d->phone ?: '—' }}</td>
                <td class="r">{{ $n($d->fees) }}</td>
                <td class="r">{{ $n($d->paid) }}</td>
                <td class="r bold">{{ $n($d->balance) }}</td>
                <td class="r">
                    @if($d->age !== null)
                        <span class="tag {{ $d->age > 60 ? 'tag-red' : ($d->age > 30 ? 'tag-amber' : 'tag-green') }}">{{ $d->age }} days</span>
                    @else — @endif
                </td>
                <td class="muted" style="white-space: nowrap;">{{ $d->last_paid ? \Carbon\Carbon::parse($d->last_paid)->format('j M Y') : 'Never' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="c muted">No student owes a confirmed balance.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><td colspan="7">Total owed</td><td class="r">{{ $n($b['confirmedTotal']) }}</td><td colspan="2"></td></tr></tfoot>
    </table>
    <p class="note">Confirmed balances only. Students whose old balance accounts has not yet confirmed are not listed.</p>
@endif

@endsection
