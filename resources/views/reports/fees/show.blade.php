@extends('ktvtc.finance.layouts.app')

@section('title', 'School Fees Report')
@section('subtitle', 'Collections, balances by term, and who owes')

@php
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $filters = array_filter(['campus_id' => $campusId, 'course_id' => $courseId]);
    $q = ['start_date' => $startDate, 'end_date' => $endDate] + $filters;
    $periodLabel = $single ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y');
    $b = $balances;
    $currentTerm = $terms->firstWhere('current', true);
    $lastTerm = $currentTerm ? $terms->first(fn ($t) => $t['to']->lt($currentTerm['from'])) : null;
    $palette = ['#b91c1c', '#2563eb', '#16a34a', '#d97706', '#7c3aed', '#0891b2'];
@endphp

@section('header-actions')
<a href="{{ route('finance.reports.fees.pdf', $q + ['part' => 'full']) }}" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-sm">
    <i class="fas fa-file-pdf mr-2"></i> Full report (PDF)
</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">From</label><input type="date" name="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">To</label><input type="date" name="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">Campus</label>
                <select name="campus_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="">All campuses</option>@foreach($campuses as $id => $name)<option value="{{ $id }}" @selected($campusId == $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">Course</label>
                <select name="course_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm max-w-xs"><option value="">All courses</option>@foreach($courses as $id => $name)<option value="{{ $id }}" @selected($courseId == $id)>{{ $name }}</option>@endforeach</select></div>
            <button class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm">Show</button>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            @php
                $quick = [
                    'Today' => [today(), today()],
                    'Yesterday' => [today()->subDay(), today()->subDay()],
                    'This week' => [today()->startOfWeek(), today()],
                    'This month' => [today()->startOfMonth(), today()],
                    'Last month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
                ];
                if ($currentTerm) $quick['This term'] = [$currentTerm['from'], today()];
                if ($lastTerm) $quick['Last term'] = [$lastTerm['from'], $lastTerm['to']];
                $quick['This year'] = [today()->startOfYear(), today()];
            @endphp
            @foreach($quick as $label => [$a, $z])
                <a href="{{ route('finance.reports.fees', ['start_date' => $a->toDateString(), 'end_date' => $z->toDateString()] + $filters) }}"
                   class="px-3 py-2 rounded-lg border {{ $startDate === $a->toDateString() && $endDate === $z->toDateString() ? 'bg-red-700 text-white border-red-700' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300' }}">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    {{-- Section bar + downloads --}}
    <div class="sticky top-0 z-20 bg-gray-50/95 backdrop-blur py-2 -my-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <nav class="flex flex-wrap gap-1 text-sm">
                @foreach(['collections' => 'Collections', 'terms' => 'Terms', 'balances' => 'Balances', 'defaulters' => 'Who owes', 'statement' => 'Student statement', 'unmatched' => 'Unassigned'] as $id => $label)
                    <a href="#{{ $id }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:border-red-300 hover:text-red-700">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex flex-wrap gap-1 text-sm">
                <span class="text-gray-500 self-center mr-1 hidden sm:inline">PDF:</span>
                @foreach(['collections' => 'Collections', 'balances' => 'Balances', 'defaulters' => 'Defaulters'] as $part => $label)
                    <a href="{{ route('finance.reports.fees.pdf', $q + ['part' => $part]) }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:bg-red-50 hover:text-red-700"><i class="fas fa-download mr-1 text-xs"></i>{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ COLLECTIONS ============ --}}
    <div id="collections" class="scroll-mt-16">
        <p class="text-sm text-gray-500 mb-3"><i class="far fa-calendar mr-1"></i> {{ $periodLabel }}@if($campusName) · {{ $campusName }}@endif @if($courseName) · {{ $courseName }}@endif</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Fees collected" :value="$kes($total)" icon="fa-coins" tone="brand"
                :hint="$change !== null ? (($change >= 0 ? '▲ ' : '▼ ') . abs($change) . '% vs ' . $prevFrom->format('j M') . ' – ' . $prevTo->format('j M')) : 'Nothing collected in the previous period'" />
            <x-dash.kpi label="Payments" :value="number_format($count)" icon="fa-receipt" tone="blue" :hint="'From ' . number_format($students) . ' students'" />
            <x-dash.kpi label="Confirmed balances owed" :value="$kes($b['confirmedTotal'])" icon="fa-scale-unbalanced" tone="amber" :hint="$b['confirmed']->count() . ' students · today'" :href="'#defaulters'" />
            <x-dash.kpi label="Payments not on a student" :value="$kes($unmatched['total'])" icon="fa-circle-question" tone="red" :hint="$unmatched['count'] . ' to assign'" :href="'#unmatched'" />
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <x-dash.panel :title="$single ? 'Collected today' : 'Collected each day'" subtitle="Completed fee payments" class="xl:col-span-2">
            <x-dash.chart type="bar" :height="300" :labels="$byDay->map(fn ($d) => \Carbon\Carbon::parse($d['date'])->format('j M'))" :datasets="[['label' => 'Collected', 'data' => $byDay->pluck('total'), 'color' => '#b91c1c']]" />
        </x-dash.panel>
        <x-dash.panel title="How it was paid">
            @if($byMethod->isNotEmpty())
                <x-dash.chart type="doughnut" :height="200" :labels="$byMethod->pluck('label')" :datasets="[['label' => 'KES', 'data' => $byMethod->pluck('total'), 'colors' => $palette]]" />
            @endif
            <table class="w-full text-sm mt-3">
                @forelse($byMethod as $m)<tr class="border-b border-gray-100"><td class="py-1.5">{{ $m['label'] }}</td><td class="py-1.5 text-right text-gray-500">{{ $m['count'] }}</td><td class="py-1.5 text-right font-medium">{{ $n($m['total']) }}</td></tr>@empty<tr><td class="py-4 text-center text-gray-500">No payments.</td></tr>@endforelse
            </table>
        </x-dash.panel>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <x-dash.panel title="By course" subtitle="Top 15 courses by money received" class="xl:col-span-2">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Course</th><th class="py-2 text-right">Payments</th><th class="py-2 text-right">KES</th><th class="py-2 text-right w-40">Share</th></tr></thead>
                <tbody>
                @forelse($byCourse as $m)
                    <tr class="border-b border-gray-100"><td class="py-2">{{ $m['label'] }}</td><td class="py-2 text-right text-gray-500">{{ $m['count'] }}</td><td class="py-2 text-right font-medium">{{ $n($m['total']) }}</td>
                        <td class="py-2 pl-3"><div class="flex items-center gap-2"><div class="flex-1 h-2 bg-gray-100 rounded"><div class="h-2 bg-red-600 rounded" style="width: {{ $m['share'] }}%"></div></div><span class="text-xs text-gray-500 w-10 text-right">{{ $m['share'] }}%</span></div></td></tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-gray-500">No payments.</td></tr>
                @endforelse
                </tbody>
            </table>
        </x-dash.panel>
        <div class="space-y-6">
            <x-dash.panel title="By campus">
                <table class="w-full text-sm">@foreach($byCampus as $m)<tr class="border-b border-gray-100"><td class="py-1.5">{{ $m['label'] }}</td><td class="py-1.5 text-right font-medium">{{ $n($m['total']) }}</td><td class="py-1.5 text-right text-gray-500 w-12">{{ $m['share'] }}%</td></tr>@endforeach</table>
            </x-dash.panel>
            <x-dash.panel title="Who recorded it">
                <table class="w-full text-sm">@foreach($byRecorder as $m)<tr class="border-b border-gray-100"><td class="py-1.5">{{ $m['label'] }}</td><td class="py-1.5 text-right text-gray-500">{{ $m['count'] }}</td><td class="py-1.5 text-right font-medium">{{ $n($m['total']) }}</td></tr>@endforeach</table>
            </x-dash.panel>
            @if($reversed['count'] || $adjustments->isNotEmpty())
                <x-dash.panel title="Also in this period">
                    <table class="w-full text-sm">
                        @if($reversed['count'])<tr class="border-b border-gray-100"><td class="py-1.5">Payments reversed</td><td class="py-1.5 text-right text-gray-500">{{ $reversed['count'] }}</td><td class="py-1.5 text-right">{{ $n($reversed['total']) }}</td></tr>@endif
                        @foreach($adjustments as $a)<tr class="border-b border-gray-100"><td class="py-1.5">{{ $a['label'] }}</td><td class="py-1.5 text-right text-gray-500">{{ $a['count'] }}</td><td class="py-1.5 text-right">{{ $n($a['fees'] ?: $a['paid']) }}</td></tr>@endforeach
                    </table>
                </x-dash.panel>
            @endif
        </div>
    </div>

    <x-dash.panel :title="'Payments received (' . number_format($count) . ')'" subtitle="Every completed fee payment in the period">
        <details>
            <summary class="cursor-pointer text-sm text-red-700 font-medium">Show the list</summary>
            <div class="mt-3 mb-3"><input type="search" placeholder="Search name, number, course, receipt…" oninput="filterRows(this, 'pay-table')" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-80"></div>
            <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                <table class="w-full text-sm" id="pay-table">
                    <thead class="sticky top-0 bg-white"><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Student</th><th class="py-2 pr-3">Name</th><th class="py-2 pr-3">Course</th><th class="py-2 pr-3">Method</th><th class="py-2 pr-3">Receipt / code</th><th class="py-2 text-right">KES</th></tr></thead>
                    <tbody>
                    @foreach($payments->reverse() as $p)
                        <tr class="border-b border-gray-100" data-search="{{ strtolower($p->student_number . ' ' . $p->student_name . ' ' . $p->course_name . ' ' . $p->transaction_code . ' ' . $p->receipt_number . ' ' . $p->payer_name) }}">
                            <td class="py-1.5 pr-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($p->payment_date)->format('j M Y') }}</td>
                            <td class="py-1.5 pr-3 whitespace-nowrap">@if($p->student_id)<a class="text-red-700 hover:underline" href="{{ route('finance.reports.fees.statement', $p->student_id) }}" title="Fee statement (PDF)">{{ $p->student_number ?: '—' }}</a>@else — @endif</td>
                            <td class="py-1.5 pr-3">{{ $p->student_name ?: ($p->payer_name ?: '—') }}</td>
                            <td class="py-1.5 pr-3 text-gray-500">{{ $p->course_name }}</td>
                            <td class="py-1.5 pr-3 text-gray-500">{{ $p->method_label }}</td>
                            <td class="py-1.5 pr-3 text-gray-500">{{ $p->transaction_code ?: ($p->receipt_number ?: '—') }}</td>
                            <td class="py-1.5 text-right font-medium">{{ $n($p->amount) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </x-dash.panel>

    {{-- ============ TERMS ============ --}}
    <div id="terms" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-5 gap-6">
        <x-dash.panel title="Terms" subtitle="Money received in each term, and how much of each term's confirmed billing is paid" class="xl:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Term</th><th class="py-2 pr-3 text-right">Collected in term</th><th class="py-2 pr-3 text-right">Students billed</th><th class="py-2 pr-3 text-right">Billed</th><th class="py-2 pr-3 text-right">Still owed</th><th class="py-2 text-right w-36">Paid</th></tr></thead>
                    <tbody>
                    @foreach($terms as $t)
                        <tr class="border-b border-gray-100 {{ $t['current'] ? 'bg-red-50/50' : '' }}">
                            <td class="py-2 pr-3"><span class="font-medium">{{ $t['name'] }}</span>@if($t['current'])<span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-red-700 text-white">current</span>@endif<div class="text-xs text-gray-500">{{ $t['from']->format('j M') }} – {{ $t['to']->format('j M Y') }}</div></td>
                            <td class="py-2 pr-3 text-right font-semibold">{{ $n($t['collected']) }}</td>
                            <td class="py-2 pr-3 text-right">{{ $t['registered'] }}</td>
                            <td class="py-2 pr-3 text-right">{{ $n($t['billed']) }}</td>
                            <td class="py-2 pr-3 text-right {{ $t['balance'] > 0 ? 'text-red-700 font-medium' : '' }}">{{ $n($t['balance']) }}</td>
                            <td class="py-2 pl-3">@if($t['rate'] !== null)<div class="flex items-center gap-2"><div class="flex-1 h-2 bg-gray-100 rounded"><div class="h-2 rounded {{ $t['rate'] >= 80 ? 'bg-green-600' : ($t['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-600') }}" style="width: {{ $t['rate'] }}%"></div></div><span class="text-xs w-10 text-right">{{ $t['rate'] }}%</span></div>@else<span class="text-gray-400">—</span>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-3">Billed / owed / paid count confirmed balances only (per-term billing and balances accounts has confirmed).</p>
        </x-dash.panel>
        <x-dash.panel title="Last 12 months" subtitle="Fees collected per month" class="xl:col-span-2">
            <x-dash.chart type="bar" :height="300" :labels="$months->pluck('label')" :datasets="[['label' => 'Collected', 'data' => $months->pluck('total'), 'color' => '#2563eb']]" />
        </x-dash.panel>
    </div>

    {{-- ============ BALANCES ============ --}}
    <div id="balances" class="scroll-mt-16 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Confirmed balances owed" :value="$kes($b['confirmedTotal'])" icon="fa-scale-unbalanced" tone="amber" :hint="$b['confirmed']->count() . ' students owe'" />
            <x-dash.kpi label="Paid ahead (credit)" :value="$kes($b['creditTotal'])" icon="fa-piggy-bank" tone="green" :hint="$b['creditCount'] . ' students'" />
            <x-dash.kpi label="Old balances to confirm" :value="$kes($b['unconfirmedTotal'])" icon="fa-clipboard-question" tone="gray" :hint="number_format($b['unconfirmedCount']) . ' students · not counted'" :href="route('finance.fee-reminders.index')" />
            <x-dash.kpi label="Open enrollments" :value="number_format($b['openCount'])" icon="fa-user-graduate" tone="blue" hint="Active, pending, suspended, deferred" />
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <x-dash.panel title="How long it has been owed" subtitle="Confirmed balances, from the oldest unpaid term">
                <x-dash.chart type="doughnut" :height="200" :labels="$b['ageing']->pluck('bucket')" :datasets="[['label' => 'KES', 'data' => $b['ageing']->pluck('total'), 'colors' => ['#16a34a', '#d97706', '#ea580c', '#b91c1c', '#9ca3af']]]" />
                <table class="w-full text-sm mt-3">@foreach($b['ageing'] as $a)<tr class="border-b border-gray-100"><td class="py-1.5">{{ $a['bucket'] }}</td><td class="py-1.5 text-right text-gray-500">{{ $a['count'] }}</td><td class="py-1.5 text-right font-medium">{{ $n($a['total']) }}</td></tr>@endforeach</table>
            </x-dash.panel>
            <x-dash.panel title="Collection by course" subtitle="Confirmed balances: billed, paid and still owed" class="xl:col-span-2">
                <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white"><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Course</th><th class="py-2 pr-3 text-right">Students</th><th class="py-2 pr-3 text-right">Billed</th><th class="py-2 pr-3 text-right">Owed</th><th class="py-2 text-right w-36">Collected</th></tr></thead>
                        <tbody>
                        @forelse($b['byCourse'] as $c)
                            <tr class="border-b border-gray-100"><td class="py-2 pr-3">{{ $c['course'] }}</td><td class="py-2 pr-3 text-right text-gray-500">{{ $c['students'] }}</td><td class="py-2 pr-3 text-right">{{ $n($c['fees']) }}</td><td class="py-2 pr-3 text-right font-medium {{ $c['owed'] > 0 ? 'text-red-700' : '' }}">{{ $n($c['owed']) }}</td>
                                <td class="py-2 pl-3">@if($c['rate'] !== null)<div class="flex items-center gap-2"><div class="flex-1 h-2 bg-gray-100 rounded"><div class="h-2 rounded {{ $c['rate'] >= 80 ? 'bg-green-600' : ($c['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-600') }}" style="width: {{ $c['rate'] }}%"></div></div><span class="text-xs w-10 text-right">{{ $c['rate'] }}%</span></div>@endif</td></tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-gray-500">No confirmed balances yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-dash.panel>
        </div>
        @if($b['unconfirmedCount'])
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <i class="fas fa-circle-info mr-1"></i>
                <b>{{ number_format($b['unconfirmedCount']) }} old whole-course balances ({{ $kes($b['unconfirmedTotal']) }})</b> came from the old records and have not been checked by accounts, so they are not counted in these figures or reminded about.
                <a href="{{ route('finance.fee-reminders.index') }}" class="underline font-semibold">Confirm balances →</a>
            </div>
        @endif
    </div>

    {{-- ============ DEFAULTERS ============ --}}
    <div id="defaulters" class="scroll-mt-16">
        <x-dash.panel :title="'Who owes (' . $b['confirmed']->count() . ')'" subtitle="Confirmed balances, largest first · click a number for the student's fee statement"
            :href="route('finance.reports.fees.pdf', $q + ['part' => 'defaulters'])" link-text="Download list (PDF)">
            <div class="flex flex-wrap gap-2 mb-3">
                <input type="search" placeholder="Search name, number, course…" oninput="filterRows(this, 'owe-table')" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-72">
                @foreach(['' => 'All', '0–30 days' => '0–30 days', '31–60 days' => '31–60', '61–90 days' => '61–90', 'Over 90 days' => '90+'] as $val => $label)
                    <button type="button" onclick="filterBucket('{{ $val }}', this)" class="bucket-btn px-3 py-2 rounded-lg border text-sm {{ $val === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-sm" id="owe-table">
                    <thead class="sticky top-0 bg-white"><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Student</th><th class="py-2 pr-3">Name</th><th class="py-2 pr-3">Course</th><th class="py-2 pr-3">Phone</th><th class="py-2 pr-3 text-right">Fees</th><th class="py-2 pr-3 text-right">Paid</th><th class="py-2 pr-3 text-right">Balance</th><th class="py-2 pr-3 text-right">Owed for</th><th class="py-2">Last paid</th></tr></thead>
                    <tbody>
                    @forelse($b['confirmed'] as $d)
                        <tr class="border-b border-gray-100 hover:bg-gray-50" data-search="{{ strtolower($d->student_number . ' ' . $d->name . ' ' . $d->course . ' ' . $d->phone) }}" data-bucket="{{ $d->bucket }}">
                            <td class="py-2 pr-3 whitespace-nowrap"><a href="{{ route('finance.reports.fees.statement', $d->student_id) }}" class="text-red-700 hover:underline font-medium" title="Fee statement (PDF)">{{ $d->student_number ?: '—' }}</a></td>
                            <td class="py-2 pr-3">{{ $d->name }}</td>
                            <td class="py-2 pr-3 text-gray-500">{{ $d->course }}</td>
                            <td class="py-2 pr-3 text-gray-500 whitespace-nowrap">{{ $d->phone ?: '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ $n($d->fees) }}</td>
                            <td class="py-2 pr-3 text-right">{{ $n($d->paid) }}</td>
                            <td class="py-2 pr-3 text-right font-bold">{{ $n($d->balance) }}</td>
                            <td class="py-2 pr-3 text-right">@if($d->age !== null)<span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $d->age > 60 ? 'bg-red-100 text-red-800' : ($d->age > 30 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">{{ $d->age }} days</span>@else — @endif</td>
                            <td class="py-2 text-gray-500 whitespace-nowrap">{{ $d->last_paid ? \Carbon\Carbon::parse($d->last_paid)->format('j M Y') : 'Never' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-6 text-center text-gray-500">No student owes a confirmed balance.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot><tr class="font-bold border-t"><td colspan="6" class="py-2">Total</td><td class="py-2 pr-3 text-right">{{ $n($b['confirmedTotal']) }}</td><td colspan="2"></td></tr></tfoot>
                </table>
            </div>
        </x-dash.panel>
    </div>

    {{-- ============ STATEMENT ============ --}}
    <div id="statement" class="scroll-mt-16">
        <x-dash.panel title="Student fee statement" subtitle="Find a student and download their statement (PDF): every course, term, payment and adjustment">
            <form method="GET" action="{{ route('finance.reports.fees') }}#statement" class="flex flex-wrap gap-2">
                @foreach($q as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <input type="search" name="student" value="{{ $studentQuery }}" placeholder="Student number, name or phone" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-80">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">Find</button>
            </form>
            @if($studentQuery !== null && $studentQuery !== '')
                <div class="mt-3 divide-y divide-gray-100">
                    @forelse($search as $s)
                        <div class="flex items-center justify-between py-2 text-sm">
                            <span><b>{{ $s->student_number ?: '—' }}</b> · {{ trim("{$s->first_name} {$s->last_name}") }} <span class="text-gray-500">{{ $s->phone }}</span></span>
                            <span class="flex gap-2">
                                <a href="{{ route('finance.reports.fees.statement', ['student' => $s->id, 'inline' => 1]) }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50">View</a>
                                <a href="{{ route('finance.reports.fees.statement', $s->id) }}" class="px-3 py-1.5 rounded-lg bg-red-700 text-white hover:bg-red-800"><i class="fas fa-download mr-1"></i>PDF</a>
                            </span>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-gray-500">No student matches "{{ $studentQuery }}".</p>
                    @endforelse
                </div>
            @endif
        </x-dash.panel>
    </div>

    {{-- ============ UNMATCHED ============ --}}
    <div id="unmatched" class="scroll-mt-16">
        <x-dash.panel :title="'School fee payments not yet on a student (' . $unmatched['count'] . ')'" :subtitle="$kes($unmatched['total']) . ' received by KCB that could not be matched automatically'"
            :href="route('finance.kcb-payments.index')" link-text="Assign them">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Receipt</th><th class="py-2 pr-3">Paid by</th><th class="py-2 pr-3">Account typed</th><th class="py-2 pr-3">Why</th><th class="py-2 text-right">KES</th></tr></thead>
                    <tbody>
                    @forelse($unmatched['rows'] as $u)
                        <tr class="border-b border-gray-100"><td class="py-2 pr-3 whitespace-nowrap">{{ $u->transaction_date ? \Carbon\Carbon::parse($u->transaction_date)->format('j M Y') : '—' }}</td><td class="py-2 pr-3">{{ $u->transaction_id }}</td><td class="py-2 pr-3">{{ trim($u->first_name . ' ' . $u->last_name) ?: '—' }}</td><td class="py-2 pr-3">{{ $u->account_reference ?: '(none)' }}</td><td class="py-2 pr-3 text-gray-500">{{ \Illuminate\Support\Str::limit($u->status_reason, 70) }}</td><td class="py-2 text-right font-medium">{{ $n($u->transaction_amount) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-500">Every school fee payment is on a student.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-dash.panel>
    </div>
</div>

<script>
    function applyFilters(tableId) {
        document.querySelectorAll('#' + tableId + ' tbody tr[data-search]').forEach(tr => {
            tr.style.display = tr.dataset.hideSearch || tr.dataset.hideBucket ? 'none' : '';
        });
    }
    function filterRows(input, tableId) {
        const q = input.value.trim().toLowerCase();
        document.querySelectorAll('#' + tableId + ' tbody tr[data-search]').forEach(tr => { tr.dataset.hideSearch = q && !tr.dataset.search.includes(q) ? '1' : ''; });
        applyFilters(tableId);
    }
    function filterBucket(bucket, btn) {
        document.querySelectorAll('.bucket-btn').forEach(b => { b.classList.remove('bg-gray-900', 'text-white', 'border-gray-900'); b.classList.add('bg-white', 'text-gray-700', 'border-gray-300'); });
        btn.classList.add('bg-gray-900', 'text-white', 'border-gray-900'); btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
        document.querySelectorAll('#owe-table tbody tr[data-bucket]').forEach(tr => { tr.dataset.hideBucket = bucket && tr.dataset.bucket !== bucket ? '1' : ''; });
        applyFilters('owe-table');
    }
</script>
@endsection
