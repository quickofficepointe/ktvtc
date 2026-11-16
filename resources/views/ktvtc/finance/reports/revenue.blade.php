@extends('ktvtc.finance.layouts.app')

@section('title', 'Revenue Report')
@section('subtitle', 'Money received and cafeteria sales')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.reports.revenue', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}"
       class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm">
        <i class="fas fa-file-csv mr-2"></i> Download CSV
    </a>
</div>
@endsection

@section('content')
@php
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $single = $startDate === $endDate;
    $periodLabel = $single ? $from->format('j M Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y');
    $colors = ['#b91c1c', '#2563eb', '#16a34a', '#d97706', '#7c3aed', '#0891b2', '#db2777', '#65a30d', '#64748b', '#f59e0b'];
    $show = fn ($c) => $c->filter(fn ($s) => $s['total'] > 0 || $s['count'] > 0);
@endphp

<div class="space-y-6">
    {{-- Dates: both the From and To days are included --}}
    <form method="GET" action="{{ route('finance.reports.revenue') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">From</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">To</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold text-sm">Apply</button>
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach([
                'Today' => [today(), today()],
                'Yesterday' => [today()->subDay(), today()->subDay()],
                'This week' => [today()->startOfWeek(), today()],
                'This month' => [today()->startOfMonth(), today()],
                'Last month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
            ] as $label => [$a, $b])
                <a href="{{ route('finance.reports.revenue', ['start_date' => $a->toDateString(), 'end_date' => $b->toDateString()]) }}"
                   class="px-3 py-2 rounded-lg border {{ $startDate === $a->toDateString() && $endDate === $b->toDateString() ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50' }}">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-dash.kpi label="Money received · {{ $periodLabel }}" :value="$kes($moneyTotal)" icon="fa-coins" tone="brand" hint="Fees, paid sales, card top-ups and other fees" />
        <x-dash.kpi label="Cafeteria sales · {{ $periodLabel }}" :value="$kes($cafeteriaTotal)" icon="fa-utensils" tone="green" hint="Till, online and pre-orders, including card purchases" />
        <x-dash.kpi label="Paid with student cards" :value="$kes($cardSpend)" icon="fa-id-card" tone="blue" hint="Spent from card balances (came in earlier as top-ups)" />
    </div>

    @if(!$single)
        <x-dash.panel title="Day by day" subtitle="Money received and cafeteria sales each day">
            <x-dash.chart type="bar" :labels="$daily->pluck('date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('j M'))->values()" :datasets="[
                ['label' => 'Money received', 'data' => $daily->pluck('money')->values(), 'color' => '#b91c1c'],
                ['label' => 'Cafeteria sales', 'data' => $daily->pluck('cafeteria')->values(), 'color' => '#16a34a'],
            ]" />
        </x-dash.panel>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-dash.panel title="Money received" subtitle="Where the money came from">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Source</th><th class="py-2 text-right">Count</th><th class="py-2 text-right">Amount</th></tr></thead>
                <tbody>
                    @forelse($show($money) as $s)
                        <tr class="border-b"><td class="py-2">{{ $s['label'] }}</td><td class="py-2 text-right text-gray-500">{{ number_format($s['count']) }}</td><td class="py-2 text-right font-medium">{{ $kes($s['total']) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No money received in this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="font-bold"><td class="py-2">Total</td><td></td><td class="py-2 text-right">{{ $kes($moneyTotal) }}</td></tr></tfoot>
            </table>
        </x-dash.panel>

        <x-dash.panel title="Cafeteria sales by channel" subtitle="Paid sales only; prompts the customer did not complete are left out">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Channel</th><th class="py-2 text-right">Sales</th><th class="py-2 text-right">Amount</th></tr></thead>
                <tbody>
                    @forelse($show($cafeteria) as $s)
                        <tr class="border-b"><td class="py-2">{{ $s['label'] }}</td><td class="py-2 text-right text-gray-500">{{ number_format($s['count']) }}</td><td class="py-2 text-right font-medium">{{ $kes($s['total']) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No cafeteria sales in this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="font-bold"><td class="py-2">Total</td><td></td><td class="py-2 text-right">{{ $kes($cafeteriaTotal) }}</td></tr></tfoot>
            </table>
            @if($show($cafeteria)->isNotEmpty())
                <div class="mt-4">
                    <x-dash.chart type="doughnut" :height="200" :labels="$show($cafeteria)->pluck('label')->values()" :datasets="[[
                        'label' => 'Sales', 'data' => $show($cafeteria)->pluck('total')->values(), 'colors' => $colors,
                    ]]" />
                </div>
            @endif
        </x-dash.panel>
    </div>

    <x-dash.panel title="Daily breakdown">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-3">Date</th>
                        @foreach($show($cafeteria) as $s)<th class="py-2 pr-3 text-right whitespace-nowrap">{{ $s['label'] }}</th>@endforeach
                        <th class="py-2 pr-3 text-right">Cafeteria total</th>
                        <th class="py-2 text-right">Money received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daily->reverse() as $day)
                        @continue($day['money'] == 0 && $day['cafeteria'] == 0)
                        <tr class="border-b">
                            <td class="py-2 pr-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($day['date'])->format('D j M Y') }}</td>
                            @foreach($show($cafeteria) as $s)<td class="py-2 pr-3 text-right">{{ $s['days'][$day['date']] ? number_format($s['days'][$day['date']], 2) : '–' }}</td>@endforeach
                            <td class="py-2 pr-3 text-right font-medium">{{ number_format($day['cafeteria'], 2) }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format($day['money'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-3">Card purchases are part of cafeteria sales but not of money received: parents paid that money in earlier, as card top-ups.</p>
    </x-dash.panel>
</div>
@endsection
