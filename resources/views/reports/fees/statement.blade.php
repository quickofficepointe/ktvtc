@extends('reports.pdf.layout')

@php
    $n = fn ($v) => number_format((float) $v, 2);
    $s = $student;
    $owed = $enrollments->sum(fn ($e) => max($e->balance_now, 0));
    $credit = $enrollments->sum(fn ($e) => max(-$e->balance_now, 0));
    $adjLabels = ['bursary' => 'Bursary / sponsorship', 'write_off' => 'Written off', 'charge' => 'Extra charge', 'refund' => 'Refund', 'transfer_in' => 'Transferred in', 'transfer_out' => 'Transferred out'];
@endphp

@section('body')
    <table width="100%" class="keep">
        <tr>
            <td width="60%" style="vertical-align: top;">
                <table class="grid">
                    <tbody>
                        <tr><td class="muted" width="34%">Student</td><td class="bold">{{ trim("{$s->first_name} {$s->middle_name} {$s->last_name}") }}</td></tr>
                        <tr><td class="muted">Student number</td><td>{{ $s->student_number ?: '—' }}</td></tr>
                        <tr><td class="muted">Campus</td><td>{{ $s->campus_name ?: '—' }}</td></tr>
                        <tr><td class="muted">Phone</td><td>{{ $s->phone ?: '—' }}</td></tr>
                        <tr><td class="muted">Parent / guardian</td><td>{{ trim(($s->next_of_kin_name ?? '') . ' ' . ($s->next_of_kin_phone ?? '')) ?: '—' }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td width="40%" style="vertical-align: top; padding-left: 12px;">
                <table class="kpis" style="margin: 0;"><tr><td style="width: 100%;">
                    <div class="kpi-label">{{ $owed > 0 ? 'Balance to pay' : ($credit > 0 ? 'Paid ahead (credit)' : 'Balance') }}</div>
                    <div class="kpi-value">KES {{ $n($owed > 0 ? $owed : $credit) }}</div>
                    <div class="kpi-hint">Pay via M-Pesa Paybill {{ config('kcb.paybill', '522533') }}, Account {{ config('kcb.tills.school_fees', '7664166') }}#{{ $s->student_number }}</div>
                </td></tr></table>
            </td>
        </tr>
    </table>

    @forelse($enrollments as $e)
        <h2>{{ $e->course_name ?: 'Course' }} <span class="muted" style="font-weight: normal; font-size: 9px;">{{ $e->student_number }} · {{ ucfirst($e->status) }} · {{ $e->billing_mode === 'term' ? 'billed per term' : 'whole-course fee' }}</span></h2>
        <table class="kpis" style="margin-bottom: 6px;"><tr>
            <td><div class="kpi-label">Fees</div><div class="kpi-value" style="font-size: 12px;">KES {{ $n($e->total_fees) }}</div></td>
            <td><div class="kpi-label">Paid</div><div class="kpi-value" style="font-size: 12px;">KES {{ $n($e->amount_paid) }}</div></td>
            <td><div class="kpi-label">{{ $e->balance_now < 0 ? 'Credit' : 'Balance' }}</div><div class="kpi-value" style="font-size: 12px;">KES {{ $n(abs($e->balance_now)) }}</div>
                @if(!$e->trusted && $e->balance_now > 0)<div class="kpi-hint">Old balance, being confirmed by accounts</div>@endif</td>
            <td><div class="kpi-label">Started</div><div class="kpi-value" style="font-size: 12px;">{{ $e->start_date ? \Carbon\Carbon::parse($e->start_date)->format('j M Y') : '—' }}</div></td>
        </tr></table>

        @if($e->terms->isNotEmpty())
            <h3>Terms</h3>
            <table class="grid">
                <thead><tr><th>Term</th><th>Dates</th><th class="r">Fee</th><th class="r">Paid</th><th class="r">Balance</th><th class="c">Status</th></tr></thead>
                <tbody>
                @foreach($e->terms as $t)
                    <tr>
                        <td>Term {{ $t['term'] }}</td>
                        <td class="muted">{{ $t['from'] ? \Carbon\Carbon::parse($t['from'])->format('j M Y') : '—' }} – {{ $t['to'] ? \Carbon\Carbon::parse($t['to'])->format('j M Y') : '—' }}</td>
                        <td class="r">{{ $n($t['fee']) }}</td><td class="r">{{ $n($t['paid']) }}</td><td class="r bold">{{ $n($t['balance']) }}</td>
                        <td class="c"><span class="tag {{ $t['balance'] <= 0 ? 'tag-green' : ($t['paid'] > 0 ? 'tag-amber' : 'tag-red') }}">{{ $t['balance'] <= 0 ? 'Paid' : ($t['paid'] > 0 ? 'Part paid' : 'Not paid') }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="note">Payments are applied to the oldest term first.</p>
        @endif

        <h3>Payments</h3>
        <table class="grid">
            <thead><tr><th>Date</th><th>Method</th><th>Receipt / code</th><th>Paid by</th><th class="c">Status</th><th class="r">KES</th></tr></thead>
            <tbody>
            @forelse($e->payments as $p)
                <tr>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($p->payment_date)->format('j M Y') }}</td>
                    <td>{{ strtoupper($p->payment_method) === 'KCB' ? 'KCB (M-Pesa)' : ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                    <td class="muted">{{ $p->transaction_code ?: ($p->receipt_number ?: '—') }}</td>
                    <td class="muted">{{ $p->payer_name ?: '—' }}</td>
                    <td class="c">@if($p->status !== 'completed')<span class="tag tag-red">{{ ucfirst($p->status) }}</span>@endif</td>
                    <td class="r {{ $p->status !== 'completed' ? 'muted' : '' }}" @if($p->status !== 'completed') style="text-decoration: line-through;" @endif>{{ $n($p->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="c muted">No payments recorded.</td></tr>
            @endforelse
            </tbody>
            <tfoot><tr><td colspan="5">Total paid</td><td class="r">{{ $n($e->payments->where('status', 'completed')->sum('amount')) }}</td></tr></tfoot>
        </table>
        @if(abs($e->payments->where('status', 'completed')->sum('amount') - $e->amount_paid) > 0.5)
            <p class="note">The paid figure above (KES {{ $n($e->amount_paid) }}) also includes payments carried over from the old records or adjustments.</p>
        @endif

        @if($e->adjustments->isNotEmpty())
            <h3>Adjustments</h3>
            <table class="grid">
                <thead><tr><th>Date</th><th>Type</th><th>Reason</th><th class="r">Fees change</th><th class="r">Paid change</th></tr></thead>
                <tbody>
                @foreach($e->adjustments as $a)
                    <tr><td>{{ \Carbon\Carbon::parse($a->created_at)->format('j M Y') }}</td><td>{{ $adjLabels[$a->type] ?? ucfirst($a->type) }}</td><td class="muted">{{ $a->reason ?: '—' }}</td><td class="r">{{ (float) $a->fees_change ? $n($a->fees_change) : '' }}</td><td class="r">{{ (float) $a->paid_change ? $n($a->paid_change) : '' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <p class="muted" style="margin-top: 20px;">This student has no enrollments.</p>
    @endforelse

    <table width="100%" style="margin-top: 30px;" class="keep"><tr>
        <td width="50%">Finance office: ______________________</td>
        <td width="50%">Stamp / date: ______________________</td>
    </tr></table>
@endsection
