@extends('ktvtc.library.layout.librarylayout')

@section('title', 'Library dashboard')

@section('content')
<x-dash.header title="Library dashboard" subtitle="Lending, stock and fines">
    <x-slot name="actions">
        <x-dash.action :href="route('library.reservations.index')" icon="fa-hourglass-half">Waiting list</x-dash.action>
        <x-dash.action :href="route('library.books.index')" icon="fa-book">Books</x-dash.action>
        <x-dash.action :href="route('library.transactions.index')" icon="fa-right-left" primary>Lend / return</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Copies on the shelf" :value="number_format($onShelf)" icon="fa-book" :hint="number_format($copies) . ' copies · ' . number_format($titles) . ' titles'" :href="route('library.items.index')" />
    <x-dash.kpi label="On loan" :value="number_format($onLoan)" icon="fa-right-left" tone="blue" :hint="number_format($members) . ' active members'" :href="route('library.transactions.index', ['status' => 'borrowed'])" />
    <x-dash.kpi label="Overdue" :value="number_format($overdue)" icon="fa-clock" :tone="$overdue ? 'red' : 'green'" hint="Past their due date" :href="route('library.transactions.index', ['status' => 'overdue'])" />
    <x-dash.kpi label="Unpaid fines" :value="'KES ' . number_format($fines)" icon="fa-coins" tone="amber" :hint="'KES ' . number_format($finesPaidMonth) . ' collected this month'" :href="route('library.members.index')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Books lent and returned, last 30 days" class="xl:col-span-2">
        <x-dash.chart type="bar" :money="false" :labels="$loans['labels']" :datasets="[
            ['label' => 'Lent', 'data' => $loans['issued'], 'color' => '#b91c1c'],
            ['label' => 'Returned', 'data' => $loans['returned'], 'color' => '#16a34a'],
        ]" />
    </x-dash.panel>
    <x-dash.panel title="Waiting list & stock">
        <ul class="space-y-3 text-sm">
            <li class="flex justify-between"><a href="{{ route('library.reservations.index') }}" class="text-gray-700 hover:text-red-700">Copies held for collection</a><span class="font-semibold tabular-nums {{ $readyForPickup ? 'text-green-700' : '' }}">{{ $readyForPickup }}</span></li>
            <li class="flex justify-between"><span class="text-gray-700">Readers waiting for a copy</span><span class="font-semibold tabular-nums">{{ $waiting }}</span></li>
            <li class="flex justify-between"><a href="{{ route('library.items.index') }}" class="text-gray-700 hover:text-red-700">Copies missing (stock-take)</a><span class="font-semibold tabular-nums {{ $missing ? 'text-amber-700' : '' }}">{{ number_format($missing) }}</span></li>
        </ul>
        <h3 class="text-xs font-semibold uppercase text-gray-500 mt-6 mb-3">Most borrowed, last 90 days</h3>
        <x-dash.bars :rows="$popular" :money="false" empty="No loans in the last 90 days." />
    </x-dash.panel>
</div>

<x-dash.panel title="Overdue books" subtitle="Oldest first" :href="route('library.transactions.index', ['status' => 'overdue'])">
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Book</th><th class="py-2 px-1">Member</th><th class="py-2 px-1">Due</th><th class="py-2 px-1 text-right">Days late</th></tr></thead>
            <tbody>
                @forelse($overdueList as $loan)
                    <tr class="border-b last:border-0">
                        <td class="py-2 px-1"><a href="{{ route('library.transactions.show', $loan) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $loan->item->book->title ?? 'Book' }}</a><span class="block text-xs text-gray-500 font-mono">{{ $loan->item->barcode ?? '' }}</span></td>
                        <td class="py-2 px-1">@if($loan->member)<a href="{{ route('library.members.show', $loan->member) }}" class="hover:text-red-700">{{ $loan->member->full_name }}</a> <span class="text-xs text-gray-500">{{ $loan->member->phone }}</span>@endif</td>
                        <td class="py-2 px-1 whitespace-nowrap">{{ $loan->due_date?->format('d M Y') }}</td>
                        <td class="py-2 px-1 text-right font-semibold text-red-700 tabular-nums">{{ $loan->days_overdue }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">No overdue books.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dash.panel>
@endsection
