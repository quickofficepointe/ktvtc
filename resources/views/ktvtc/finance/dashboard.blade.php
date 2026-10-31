@extends('ktvtc.finance.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Finance overview')

@section('content')
@php
    $kes = fn ($v) => 'KES ' . number_format((float) $v);
    $streamColors = ['#b91c1c', '#2563eb', '#16a34a', '#d97706', '#7c3aed', '#64748b'];
    $streams = collect($received['streams']);
    $stale = fn ($at) => !$at || $at->lt(now()->subDays(3));
@endphp

<x-dash.header title="Finance dashboard" :subtitle="$periodLabel . ($campusId ? ' · ' . ($campuses->firstWhere('id', $campusId)->name ?? '') : ' · all campuses')">
    <x-slot name="actions">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="period" onchange="this.form.submit()" class="no-select2 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach(\App\Services\Dashboards\FinanceDashboard::PERIODS as $key => $label)
                    <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="campus_id" onchange="this.form.submit()" class="no-select2 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All campuses</option>
                @foreach($campuses as $c)<option value="{{ $c->id }}" @selected($campusId === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </form>
    </x-slot>
</x-dash.header>

@if($stale($kcb['lastFee']) || $kcb['waitingCount'] > 0)
    <div class="mb-6 grid gap-3 {{ $stale($kcb['lastFee']) && $kcb['waitingCount'] > 0 ? 'md:grid-cols-2' : '' }}">
        @if($stale($kcb['lastFee']))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold"><i class="fas fa-triangle-exclamation mr-1"></i> No school-fee payment has arrived from KCB {{ $kcb['lastFee'] ? 'since ' . $kcb['lastFee']->format('d M Y H:i') : 'yet' }}.</p>
                <p>If parents are paying, check the IPN link with KCB: payments only reach student accounts through it.</p>
            </div>
        @endif
        @if($kcb['waitingCount'] > 0)
            <a href="{{ route('finance.kcb-payments.index') }}" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 hover:bg-amber-100">
                <p class="font-semibold"><i class="fas fa-inbox mr-1"></i> {{ number_format($kcb['waitingCount']) }} KCB payment(s) worth {{ $kes($kcb['waitingAmount']) }} are waiting for you to assign.</p>
                <p>They came in without a recognisable account number. Open the queue &rarr;</p>
            </a>
        @endif
    </div>
@endif

{{-- Headline figures --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Received · {{ strtolower($periodLabel) }}" :value="$kes($received['total'])" icon="fa-coins" tone="brand"
        hint="All streams: fees, cards, cafeteria, applications, fines" />
    <x-dash.kpi label="School fees · {{ strtolower($periodLabel) }}" :value="$kes($received['streams']['School fees'])" icon="fa-graduation-cap" tone="green"
        :hint="number_format($received['feeCount']) . ' payments · today ' . $kes($received['feeToday'])" :href="route('finance.kcb-payments.index', ['status' => 'all'])" />
    <x-dash.kpi label="Fees outstanding" :value="$kes($fees['outstanding'])" icon="fa-hourglass-half" tone="amber"
        :hint="number_format($fees['owing']) . ' of ' . number_format($fees['enrollments']) . ' enrollments owe'" />
    <x-dash.kpi label="Collection rate" :value="$fees['rate'] . '%'" icon="fa-chart-pie" tone="blue" :progress="$fees['rate']"
        :hint="$kes($fees['paid']) . ' paid of ' . $kes($fees['billed']) . ' billed'" />
</div>

{{-- School fees --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="School fees received, last 12 months" subtitle="Automatic = paid to the KCB till and matched to the student; Recorded = entered by staff" class="xl:col-span-2">
        <x-dash.chart type="bar" :stacked="true" :labels="$trend['labels']" :datasets="[
            ['label' => 'Automatic (KCB)', 'data' => $trend['auto'], 'color' => '#b91c1c'],
            ['label' => 'Recorded by staff', 'data' => $trend['manual'], 'color' => '#f59e0b'],
        ]" />
    </x-dash.panel>
    <x-dash.panel title="Where the money came from" :subtitle="$periodLabel">
        @if($received['total'] > 0)
            <x-dash.chart type="doughnut" :height="200" :labels="$streams->keys()->values()" :datasets="[[
                'label' => 'Received', 'data' => $streams->values(), 'colors' => $streamColors,
            ]]" />
        @endif
        <div class="mt-4 space-y-1.5 text-sm">
            @foreach($streams as $name => $amount)
                <p class="flex justify-between"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $streamColors[$loop->index] }}"></span>{{ $name }}</span><span class="tabular-nums font-medium">{{ $kes($amount) }}</span></p>
            @endforeach
        </div>
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Billed on open enrollments" :value="$kes($fees['billed'])" tone="gray" :hint="number_format($fees['enrollments']) . ' enrollments'" />
    <x-dash.kpi label="Fully paid" :value="number_format($fees['cleared'])" tone="green" hint="Enrollments with nothing left to pay" />
    <x-dash.kpi label="Nothing paid yet" :value="number_format($fees['nothingPaid'])" tone="red" hint="Billed but no payment recorded" />
    <x-dash.kpi label="Overpaid (credit)" :value="$kes($fees['credit'])" tone="blue" hint="Paid above the fee; carried as credit" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Fees received by course" :subtitle="$periodLabel">
        <x-dash.bars :rows="$byCourse['received']" tone="bg-green-600" empty="No school-fee payments in this period." />
    </x-dash.panel>
    <x-dash.panel title="Outstanding by course" subtitle="Courses with the most unpaid fees">
        <x-dash.bars :rows="$byCourse['outstanding']" tone="bg-amber-500" empty="No outstanding balances." />
    </x-dash.panel>
    <x-dash.panel title="How old the balances are" subtitle="Outstanding fees by when the student enrolled">
        <x-dash.bars :rows="$aging" tone="bg-red-600" />
        <p class="text-xs text-gray-500 mt-3">Old balances may include amounts typed in wrongly before the new system; check them against receipts before chasing.</p>
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Largest balances" subtitle="Students to follow up first" class="xl:col-span-2" :href="route('finance.fee-reminders.index')" link-text="Send reminders">
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Student</th><th class="py-2 px-1">Course</th><th class="py-2 px-1 text-right">Paid</th><th class="py-2 px-1 text-right">Balance</th><th class="py-2 px-1">Last paid</th></tr></thead>
                <tbody>
                    @forelse($topBalances as $row)
                        <tr class="border-b last:border-0">
                            <td class="py-2 px-1"><a href="{{ route('enrollment-terms.show', $row['enrollment']) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $row['name'] }}</a>
                                <span class="block text-xs text-gray-500">{{ $row['enrollment']->student_number }} {{ $row['phone'] ? '· ' . $row['phone'] : '' }}</span></td>
                            <td class="py-2 px-1 text-gray-600">{{ $row['enrollment']->course_name }}</td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ number_format((float) $row['enrollment']->amount_paid) }}</td>
                            <td class="py-2 px-1 text-right tabular-nums font-semibold text-red-700">{{ number_format($row['balance']) }}</td>
                            <td class="py-2 px-1 text-gray-600 whitespace-nowrap">{{ $row['lastPaid'] ? \Carbon\Carbon::parse($row['lastPaid'])->format('d M Y') : 'No receipt' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-500">No outstanding balances.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dash.panel>
    <div class="space-y-6">
        <x-dash.panel title="How fees were paid" :subtitle="$periodLabel">
            <x-dash.bars :rows="$received['byChannel']" tone="bg-blue-600" empty="No payments in this period." />
        </x-dash.panel>
        <x-dash.panel title="Fees by campus" subtitle="Open enrollments">
            @foreach($byCampus as $c)
                <div class="mb-3 last:mb-0 text-sm">
                    <p class="flex justify-between"><span class="font-medium">{{ $c['name'] }}</span><span class="text-gray-500">{{ $c['rate'] }}% collected</span></p>
                    <div class="mt-1 h-2 rounded-full bg-amber-100 overflow-hidden"><div class="h-full bg-green-600" style="width: {{ $c['rate'] }}%"></div></div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ number_format($c['n']) }} enrollments · outstanding {{ $kes($c['outstanding']) }}</p>
                </div>
            @endforeach
        </x-dash.panel>
    </div>
</div>

{{-- Terms, KCB and cards --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Terms running now" :href="route('finance.fee-structure.index')" link-text="Fees per term">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Students in a term</dt><dd class="text-xl font-bold tabular-nums">{{ number_format($terms['current']) }}</dd></div>
            <div><dt class="text-gray-500">Billed for these terms</dt><dd class="text-xl font-bold tabular-nums">{{ $kes($terms['billed']) }}</dd></div>
            <div><dt class="text-gray-500">Exam cards downloaded</dt><dd class="text-xl font-bold tabular-nums">{{ number_format($terms['examCards']) }}</dd></div>
            <div><dt class="text-gray-500">Registered this month</dt><dd class="text-xl font-bold tabular-nums">{{ number_format($terms['thisMonth']) }}</dd><dd class="text-xs text-gray-500">{{ number_format($terms['self']) }} by the students themselves</dd></div>
        </dl>
    </x-dash.panel>

    <x-dash.panel title="KCB payment feed" :href="route('finance.kcb-payments.index')" link-text="Queue">
        <dl class="space-y-3 text-sm">
            @foreach(['School fees till' => $kcb['lastFee'], 'Cafeteria & cards till' => $kcb['lastCard']] as $name => $at)
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">{{ $name }}</dt>
                    <dd class="{{ $stale($at) ? 'text-red-700 font-semibold' : 'text-green-700' }}">
                        <i class="fas {{ $stale($at) ? 'fa-circle-exclamation' : 'fa-circle-check' }} mr-1"></i>{{ $at ? 'last ' . $at->diffForHumans() : 'never' }}
                    </dd>
                </div>
            @endforeach
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Credited automatically · {{ strtolower($periodLabel) }}</dt><dd class="tabular-nums">{{ number_format($kcb['creditedCount']) }} · {{ $kes($kcb['creditedAmount']) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Waiting for finance</dt><dd class="tabular-nums {{ $kcb['waitingCount'] ? 'text-amber-700 font-semibold' : '' }}">{{ number_format($kcb['waitingCount']) }} · {{ $kes($kcb['waitingAmount']) }}</dd></div>
        </dl>
    </x-dash.panel>

    <x-dash.panel title="High-school cards" :href="route('finance.transport.index')" link-text="Transport fare">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Transport wallets</dt><dd class="text-xl font-bold tabular-nums">{{ $kes($cards['transport']) }}</dd></div>
            <div><dt class="text-gray-500">Canteen wallets</dt><dd class="text-xl font-bold tabular-nums">{{ $kes($cards['canteen']) }}</dd></div>
            <div><dt class="text-gray-500">Top-ups · {{ strtolower($periodLabel) }}</dt><dd class="tabular-nums">Transport {{ $kes($cards['topupTransport']) }}<br>Canteen {{ $kes($cards['topupCanteen']) }}</dd></div>
            <div><dt class="text-gray-500">Fare given · {{ strtolower($periodLabel) }}</dt><dd class="tabular-nums">{{ $kes($cards['fareCash']) }} cash<br><span class="text-xs text-gray-500">+ {{ $kes($cards['fareFees']) }} withdrawal fees, {{ $cards['fareCount'] }} hand-outs</span></dd></div>
        </dl>
        <p class="text-xs text-gray-500 mt-3">{{ number_format($cards['cards']) }} cards · {{ $kes($cards['canteenSpent']) }} spent at the canteen {{ strtolower($periodLabel) }}</p>
    </x-dash.panel>
</div>

<x-dash.panel title="Latest school-fee payments" :href="route('finance.kcb-payments.index', ['status' => 'all'])">
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Date</th><th class="py-2 px-1">Student</th><th class="py-2 px-1">Course</th><th class="py-2 px-1">How</th><th class="py-2 px-1">Receipt</th><th class="py-2 px-1 text-right">Amount</th></tr></thead>
            <tbody>
                @forelse($recent as $p)
                    <tr class="border-b last:border-0">
                        <td class="py-2 px-1 whitespace-nowrap text-gray-600">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y') : '' }}</td>
                        <td class="py-2 px-1">{{ $p->student ? trim($p->student->first_name . ' ' . $p->student->last_name) : ($p->payer_name ?: '—') }}<span class="block text-xs text-gray-500">{{ $p->enrollment->student_number ?? $p->student->student_number ?? '' }}</span></td>
                        <td class="py-2 px-1 text-gray-600">{{ $p->enrollment->course_name ?? '—' }}</td>
                        <td class="py-2 px-1">{{ $p->payment_method === 'kcb' ? 'KCB till' : ucfirst((string) $p->payment_method) }}</td>
                        <td class="py-2 px-1 font-mono text-xs">{{ $p->transaction_code ?: $p->receipt_number }}</td>
                        <td class="py-2 px-1 text-right tabular-nums font-semibold">{{ number_format((float) $p->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-gray-500">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dash.panel>
@endsection
