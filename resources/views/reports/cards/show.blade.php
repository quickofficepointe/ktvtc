@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Cards Report')
@section('subtitle', 'Transport and canteen wallets, kept apart')

@php
    use App\Services\Reports\CardsReport;
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $n = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
    $q = array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'class' => $class]);
    $periodLabel = $single ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y');
    $t = $wallets['transport'];
    $c = $wallets['canteen'];
    $dayLabels = $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('j M'));
@endphp

@section('header-actions')
<a href="{{ route('finance.reports.cards.pdf', $q + ['part' => 'full']) }}" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm shadow-sm">
    <i class="fas fa-file-pdf mr-2"></i> Full report (PDF)
</a>
@endsection

@section('content')
<div class="space-y-6">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">From</label><input type="date" name="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">To</label><input type="date" name="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-semibold text-gray-600 mb-1">Class</label>
                <select name="class" class="border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="">All classes</option>@foreach($classes as $cl)<option value="{{ $cl }}" @selected($class === $cl)>{{ $cl }}</option>@endforeach</select></div>
            <button class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm">Show</button>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach([
                'Today' => [today(), today()], 'Yesterday' => [today()->subDay(), today()->subDay()], 'This week' => [today()->startOfWeek(), today()],
                'This month' => [today()->startOfMonth(), today()], 'Last month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
                'Since wallets split' => [$splitAt ? $splitAt->copy()->addDay()->startOfDay() : today()->startOfMonth(), today()],
            ] as $label => [$a, $z])
                <a href="{{ route('finance.reports.cards', array_filter(['start_date' => $a->toDateString(), 'end_date' => $z->toDateString(), 'class' => $class])) }}"
                   class="px-3 py-2 rounded-lg border {{ $startDate === $a->toDateString() && $endDate === $z->toDateString() ? 'bg-red-700 text-white border-red-700' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300' }}">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    <div class="sticky top-0 z-20 bg-gray-50/95 backdrop-blur py-2 -my-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <nav class="flex flex-wrap gap-1 text-sm">
                @foreach(['overview' => 'Overview', 'transport' => 'Transport', 'canteen' => 'Canteen', 'classes' => 'By class', 'students' => 'Students', 'watch' => 'Low & inactive'] as $id => $label)
                    <a href="#{{ $id }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:border-red-300 hover:text-red-700">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex flex-wrap gap-1 text-sm">
                <span class="text-gray-500 self-center mr-1 hidden sm:inline">PDF:</span>
                @foreach(['transport' => 'Transport', 'canteen' => 'Canteen', 'balances' => 'Balances list'] as $part => $label)
                    <a href="{{ route('finance.reports.cards.pdf', $q + ['part' => $part]) }}" class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-700 hover:bg-red-50 hover:text-red-700"><i class="fas fa-download mr-1 text-xs"></i>{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ OVERVIEW ============ --}}
    <div id="overview" class="scroll-mt-16 space-y-4">
        <p class="text-sm text-gray-500"><i class="far fa-calendar mr-1"></i> {{ $periodLabel }}@if($class) · {{ $class }}@endif</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Transport top-ups" :value="$kes($t['topups'])" icon="fa-bus" tone="blue" :hint="$t['topupCount'] . ' top-ups'" />
            <x-dash.kpi label="Fares paid out" :value="$kes($fares['amount'])" icon="fa-hand-holding-dollar" tone="brand" :hint="$fares['count'] . ' fares · fees ' . $kes($fares['fees'])" />
            <x-dash.kpi label="Canteen top-ups" :value="$kes($c['topups'])" icon="fa-utensils" tone="green" :hint="$c['topupCount'] . ' top-ups'" />
            <x-dash.kpi label="Canteen spending" :value="$kes($c['spent'])" icon="fa-cart-shopping" tone="amber" :hint="$c['spentCount'] . ' purchases'" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-dash.kpi label="Held on cards · transport" :value="$kes($t['held'])" icon="fa-wallet" tone="gray" :hint="$t['cardsWithMoney'] . ' cards with money · right now'" />
            <x-dash.kpi label="Held on cards · canteen" :value="$kes($c['held'])" icon="fa-wallet" tone="gray" :hint="$c['cardsWithMoney'] . ' cards with money · right now'" />
            <x-dash.kpi label="Cards used in period" :value="$activeCards . ' / ' . $cardCount" icon="fa-id-card" tone="blue" :hint="$blocked . ' blocked'" />
            <x-dash.kpi label="Low canteen balance" :value="$low->count()" icon="fa-battery-quarter" tone="red" hint="Below their low-balance level" :href="'#watch'" />
        </div>
    </div>

    {{-- ============ WALLETS ============ --}}
    @foreach(['transport' => ['fa-bus', 'Fares paid'], 'canteen' => ['fa-utensils', 'Spent']] as $key => [$icon, $outLabel])
        @php $w = $wallets[$key]; @endphp
        <div id="{{ $key }}" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-5 gap-6">
            <x-dash.panel :title="$w['label'] . ' wallet'" subtitle="Opening + money in − money out = closing" class="xl:col-span-2">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Movement</th><th class="py-2 pl-4 text-right">Count</th><th class="py-2 pl-4 text-right">In</th><th class="py-2 pl-4 text-right">Out</th></tr></thead>
                    <tbody>
                        <tr class="border-b border-gray-100 font-semibold"><td class="py-2">Opening · {{ $from->format('j M') }}</td><td></td><td colspan="2" class="py-2 text-right">{{ $w['opening'] !== null ? $kes($w['opening']) : 'n/a' }}</td></tr>
                        @forelse($w['lines'] as $l)
                            <tr class="border-b border-gray-100"><td class="py-2">{{ $l['label'] }}</td><td class="py-2 pl-4 text-right text-gray-500">{{ $l['count'] }}</td><td class="py-2 pl-4 text-right text-green-700">{{ $l['in'] ? $n($l['in']) : '' }}</td><td class="py-2 pl-4 text-right text-red-700">{{ $l['out'] ? $n($l['out']) : '' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">No movement in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold"><td class="py-2">Total</td><td></td><td class="py-2 pl-4 text-right text-green-700">{{ $n($w['in']) }}</td><td class="py-2 pl-4 text-right text-red-700">{{ $n($w['out']) }}</td></tr>
                        <tr class="font-bold border-t"><td class="py-2">Closing · {{ $to->format('j M') }}</td><td></td><td colspan="2" class="py-2 text-right">{{ $w['closing'] !== null ? $kes($w['closing']) : 'n/a' }}</td></tr>
                    </tfoot>
                </table>
                @if($w['opening'] === null)
                    <p class="text-xs text-gray-500 mt-2">Cards were split into transport and canteen on {{ $splitAt?->format('j M Y') }}; per-wallet balances before then are not available. Use "Since wallets split".</p>
                @endif
            </x-dash.panel>
            <x-dash.panel :title="$single ? $w['label'] . ' today' : $w['label'] . ' each day'" :subtitle="'Top-ups vs ' . strtolower($outLabel)" class="xl:col-span-3">
                <x-dash.chart type="bar" :height="300" :labels="$dayLabels" :datasets="[
                    ['label' => 'Top-ups', 'data' => $w['daily']->pluck('in'), 'color' => '#16a34a'],
                    ['label' => $outLabel, 'data' => $w['daily']->pluck('out'), 'color' => '#b91c1c'],
                ]" />
            </x-dash.panel>
        </div>
        @if($key === 'canteen')
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <x-dash.panel title="Where canteen money was spent">
                    <table class="w-full text-sm">@forelse($canteenWhere as $x)<tr class="border-b border-gray-100"><td class="py-2">{{ $x['label'] }}</td><td class="py-2 text-right text-gray-500">{{ $x['count'] }}</td><td class="py-2 text-right font-medium">{{ $n($x['total']) }}</td></tr>@empty<tr><td class="py-4 text-center text-gray-500">No spending.</td></tr>@endforelse</table>
                </x-dash.panel>
                <x-dash.panel title="How parents topped up">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Way</th><th class="py-2 text-right">Transport</th><th class="py-2 text-right">Canteen</th></tr></thead>
                        @forelse($topupSources as $x)<tr class="border-b border-gray-100"><td class="py-2">{{ $x['label'] }}</td><td class="py-2 text-right">{{ $n($x['transport']) }}</td><td class="py-2 text-right">{{ $n($x['canteen']) }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-gray-500">No top-ups.</td></tr>@endforelse
                    </table>
                </x-dash.panel>
            </div>
        @else
            <x-dash.panel title="Fares handed out" :subtitle="$fares['count'] . ' fares to ' . $fares['students'] . ' students · withdrawal fees ' . $kes($fares['fees'])">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Date</th><th class="py-2 text-right">Fares</th><th class="py-2 text-right">Amount</th><th class="py-2 text-right">Withdrawal fees</th><th class="py-2 text-right">Taken from cards</th></tr></thead>
                        <tbody>
                        @forelse($fares['byDay'] as $f)
                            <tr class="border-b border-gray-100"><td class="py-2">{{ \Carbon\Carbon::parse($f->d)->format('D j M Y') }}</td><td class="py-2 text-right">{{ $f->n }}</td><td class="py-2 text-right">{{ $n($f->amount) }}</td><td class="py-2 text-right">{{ $n($f->fees) }}</td><td class="py-2 text-right font-medium">{{ $n($f->amount + $f->fees) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-500">No fares handed out in this period.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($fares['reversedCount'])<p class="text-xs text-gray-500 mt-2">{{ $fares['reversedCount'] }} fare records ({{ $kes($fares['reversedTotal']) }}) were reversed and the money returned to the cards.</p>@endif
            </x-dash.panel>
        @endif
    @endforeach

    {{-- ============ BY CLASS + TREND ============ --}}
    <div id="classes" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-5 gap-6">
        <x-dash.panel title="By class" subtitle="Money in and out in the period; balances right now" class="xl:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Class</th><th class="py-2 pr-3 text-right">Cards</th><th class="py-2 pr-3 text-right">Top-ups</th><th class="py-2 pr-3 text-right">Canteen spent</th><th class="py-2 pr-3 text-right">Fares</th><th class="py-2 pr-3 text-right">Transport held</th><th class="py-2 text-right">Canteen held</th></tr></thead>
                    <tbody>
                    @foreach($byClass as $x)
                        <tr class="border-b border-gray-100"><td class="py-2 pr-3 font-medium"><a class="hover:text-red-700" href="{{ route('finance.reports.cards', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'class' => $x['class'] === 'No class' ? null : $x['class']])) }}">{{ $x['class'] }}</a></td><td class="py-2 pr-3 text-right text-gray-500">{{ $x['cards'] }}</td><td class="py-2 pr-3 text-right">{{ $n($x['topups']) }}</td><td class="py-2 pr-3 text-right">{{ $n($x['canteen']) }}</td><td class="py-2 pr-3 text-right">{{ $n($x['fares']) }}</td><td class="py-2 pr-3 text-right">{{ $n($x['transport_held']) }}</td><td class="py-2 text-right">{{ $n($x['canteen_held']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-dash.panel>
        <x-dash.panel title="Last 6 months" subtitle="Top-ups and spending per wallet" class="xl:col-span-2">
            <x-dash.chart type="bar" :height="300" :labels="$months->pluck('label')" :datasets="[
                ['label' => 'Transport top-ups', 'data' => $months->pluck('transport_in'), 'color' => '#2563eb'],
                ['label' => 'Fares paid', 'data' => $months->pluck('fares'), 'color' => '#93c5fd'],
                ['label' => 'Canteen top-ups', 'data' => $months->pluck('canteen_in'), 'color' => '#16a34a'],
                ['label' => 'Canteen spent', 'data' => $months->pluck('canteen_spent'), 'color' => '#86efac'],
            ]" />
        </x-dash.panel>
    </div>

    {{-- ============ STUDENTS ============ --}}
    <div id="students" class="scroll-mt-16">
        <x-dash.panel :title="'Students (' . $cards->count() . ')'" subtitle="Balances right now · click a name for the card statement (PDF, chosen period)"
            :href="route('finance.reports.cards.pdf', $q + ['part' => 'balances'])" link-text="Download balances (PDF)">
            <div class="mb-3"><input type="search" placeholder="Search name, admission, class, phone…" oninput="filterRows(this, 'cards-table')" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-80"></div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-sm" id="cards-table">
                    <thead class="sticky top-0 bg-white"><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Adm. no.</th><th class="py-2 pr-3">Student</th><th class="py-2 pr-3">Class</th><th class="py-2 pr-3">Parent phone</th><th class="py-2 pr-3 text-right">Transport</th><th class="py-2 pr-3 text-right">Canteen</th><th class="py-2 pr-3">Last used</th><th class="py-2">Last top-up</th></tr></thead>
                    <tbody>
                    @foreach($cards as $x)
                        <tr class="border-b border-gray-100 hover:bg-gray-50" data-search="{{ strtolower($x->admission . ' ' . $x->name . ' ' . $x->class . ' ' . $x->parent_phone) }}">
                            <td class="py-2 pr-3 whitespace-nowrap">{{ $x->admission ?: '—' }}</td>
                            <td class="py-2 pr-3"><a class="text-red-700 hover:underline font-medium" href="{{ route('finance.reports.cards.statement', ['card' => $x->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}">{{ $x->name ?: '—' }}</a>@if($x->is_blocked) <span class="ml-1 px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-800">Blocked</span>@endif</td>
                            <td class="py-2 pr-3 text-gray-500">{{ $x->class ?: '—' }}</td>
                            <td class="py-2 pr-3 text-gray-500 whitespace-nowrap">{{ $x->parent_phone ?: '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ $n($x->transport_balance) }}</td>
                            <td class="py-2 pr-3 text-right {{ $x->canteen_balance < $x->low_at ? 'text-red-700 font-semibold' : '' }}">{{ $n($x->canteen_balance) }}</td>
                            <td class="py-2 pr-3 text-gray-500 whitespace-nowrap">{{ $x->last_used_at ? \Carbon\Carbon::parse($x->last_used_at)->format('j M Y') : 'Never' }}</td>
                            <td class="py-2 text-gray-500 whitespace-nowrap">{{ $x->last_funded_at ? \Carbon\Carbon::parse($x->last_funded_at)->format('j M Y') : 'Never' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr class="font-bold border-t"><td colspan="4" class="py-2">Total</td><td class="py-2 pr-3 text-right">{{ $n($cards->sum('transport_balance')) }}</td><td class="py-2 pr-3 text-right">{{ $n($cards->sum('canteen_balance')) }}</td><td colspan="2"></td></tr></tfoot>
                </table>
            </div>
        </x-dash.panel>
    </div>

    {{-- ============ LOW & INACTIVE ============ --}}
    <div id="watch" class="scroll-mt-16 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <x-dash.panel :title="'Low canteen balance (' . $low->count() . ')'" :subtitle="'Below the card\'s low-balance level (' . CardsReport::LOW_BALANCE . ' unless set) — parents may need a reminder'">
            <div class="max-h-[380px] overflow-y-auto">
                <table class="w-full text-sm">
                    @forelse($low->take(200) as $x)
                        <tr class="border-b border-gray-100"><td class="py-1.5">{{ $x->name }} <span class="text-gray-400 text-xs">{{ $x->class }}</span></td><td class="py-1.5 text-gray-500 whitespace-nowrap">{{ $x->parent_phone ?: '—' }}</td><td class="py-1.5 text-right font-semibold text-red-700">{{ $n($x->canteen_balance) }}</td></tr>
                    @empty
                        <tr><td class="py-4 text-center text-gray-500">No card is low.</td></tr>
                    @endforelse
                </table>
            </div>
        </x-dash.panel>
        <x-dash.panel :title="'Not used for ' . CardsReport::INACTIVE_DAYS . ' days (' . $inactive->count() . ')'" subtitle="Most money held first — lost card, left school, or saving?">
            <div class="max-h-[380px] overflow-y-auto">
                <table class="w-full text-sm">
                    @forelse($inactive->take(200) as $x)
                        <tr class="border-b border-gray-100"><td class="py-1.5">{{ $x->name }} <span class="text-gray-400 text-xs">{{ $x->class }}</span></td><td class="py-1.5 text-gray-500 whitespace-nowrap">{{ $x->last_used_at ? 'Last ' . \Carbon\Carbon::parse($x->last_used_at)->format('j M') : 'Never used' }}</td><td class="py-1.5 text-right font-semibold">{{ $n($x->transport_balance + $x->canteen_balance) }}</td></tr>
                    @empty
                        <tr><td class="py-4 text-center text-gray-500">Every card was used recently.</td></tr>
                    @endforelse
                </table>
            </div>
        </x-dash.panel>
    </div>
</div>

<script>
    function filterRows(input, tableId) {
        const q = input.value.trim().toLowerCase();
        document.querySelectorAll('#' + tableId + ' tbody tr[data-search]').forEach(tr => { tr.style.display = q && !tr.dataset.search.includes(q) ? 'none' : ''; });
    }
</script>
@endsection
