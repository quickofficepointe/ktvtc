@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'ILO Dashboard')
@section('subtitle', 'Industrial attachment')

@section('content')
<x-dash.header title="Industrial liaison dashboard" subtitle="Companies, openings and students on attachment">
    <x-slot name="actions">
        <x-dash.action :href="route('ilo.applications.index')" icon="fa-inbox">Applications</x-dash.action>
        <x-dash.action :href="route('ilo.opportunities.index')" icon="fa-briefcase">Openings</x-dash.action>
        <x-dash.action :href="route('ilo.companies.create')" icon="fa-building" primary>Add a company</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Partner companies" :value="number_format($companies)" icon="fa-building" :href="route('ilo.companies.index')" />
    <x-dash.kpi label="Open attachment slots" :value="number_format($slotsLeft)" icon="fa-briefcase" tone="blue" :hint="number_format($openOpportunities) . ' openings accepting applications'" :href="route('ilo.opportunities.index')" />
    <x-dash.kpi label="Applications to review" :value="number_format($applicationsPending)" icon="fa-inbox" :tone="$applicationsPending ? 'amber' : 'green'" :href="route('ilo.applications.index')" />
    <x-dash.kpi label="On attachment now" :value="number_format($onAttachment)" icon="fa-user-tie" tone="green" :hint="$toReview ? number_format($toReview) . ' placements to review' : 'No placements waiting'" :href="route('ilo.attachments.index')" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-dash.panel title="Attachments by status">
        <x-dash.bars :rows="$byStatus" :money="false" tone="bg-blue-600" empty="No attachments yet." />
    </x-dash.panel>
    <x-dash.panel title="Placements by sector">
        <x-dash.bars :rows="$bySector" :money="false" empty="No placements yet." />
    </x-dash.panel>
    <x-dash.panel title="Latest applications" :href="route('ilo.applications.index')">
        <ul class="divide-y text-sm">
            @forelse($recentApplications as $a)
                <li class="py-2"><span class="font-medium text-gray-900">{{ $a->first_name }} {{ $a->last_name }}</span>
                    <span class="block text-xs text-gray-500">{{ $a->title ?? 'Opening' }} · {{ str_replace('_', ' ', $a->status) }} · {{ \Carbon\Carbon::parse($a->created_at)->format('d M') }}</span></li>
            @empty
                <li class="py-2 text-gray-500">No applications yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
</div>
@endsection
