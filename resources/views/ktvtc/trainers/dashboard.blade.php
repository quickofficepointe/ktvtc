@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'Dashboard')

@section('content')
<x-dash.header title="Trainer dashboard" :subtitle="$units->count() . ' unit' . ($units->count() == 1 ? '' : 's') . ' assigned to you'">
    <x-slot name="actions">
        <x-dash.action :href="route('trainers.students.index')" icon="fa-users">My students</x-dash.action>
        <x-dash.action :href="route('trainers.feedback')" icon="fa-star">Feedback</x-dash.action>
        <x-dash.action :href="route('trainers.results.index')" icon="fa-pen" primary>Enter marks</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Students" :value="number_format($students)" icon="fa-users" hint="In your units this term or last month" :href="route('trainers.students.index')" />
    <x-dash.kpi label="Marks in draft" :value="number_format($drafts)" icon="fa-pen" :tone="$drafts ? 'amber' : 'green'" hint="Saved but not submitted" :href="route('trainers.results.index')" />
    <x-dash.kpi label="Waiting for publishing" :value="number_format($submitted)" icon="fa-paper-plane" tone="blue" :hint="number_format($published) . ' already published'" />
    <x-dash.kpi label="Student rating" :value="is_null($rating) ? '—' : $rating . ' / 5'" icon="fa-star" :tone="is_null($rating) ? 'gray' : ($rating >= 4 ? 'green' : 'amber')"
        :hint="is_null($rating) ? 'Shown once 3 students have responded (' . $responses . ' so far)' : $responses . ' anonymous responses'" :href="route('trainers.feedback')" />
</div>

<x-dash.panel title="My units" :href="route('trainers.results.index')" link-text="Marks & results">
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Unit</th><th class="py-2 px-1">Course</th><th class="py-2 px-1">Term</th><th class="py-2 px-1 text-right">Draft</th><th class="py-2 px-1 text-right">Submitted</th><th class="py-2 px-1 text-right">Published</th><th></th></tr></thead>
            <tbody>
                @forelse($units as $u)
                    <tr class="border-b last:border-0">
                        <td class="py-2 px-1 font-medium text-gray-900">{{ $u->code ? $u->code . ' ' : '' }}{{ $u->title }}</td>
                        <td class="py-2 px-1 text-gray-600">{{ $u->course->name ?? '' }}</td>
                        <td class="py-2 px-1">Term {{ $u->term_number }}</td>
                        <td class="py-2 px-1 text-right tabular-nums {{ $u->draft ? 'text-amber-700 font-semibold' : 'text-gray-400' }}">{{ $u->draft }}</td>
                        <td class="py-2 px-1 text-right tabular-nums">{{ $u->submitted }}</td>
                        <td class="py-2 px-1 text-right tabular-nums text-green-700">{{ $u->published }}</td>
                        <td class="py-2 px-1 text-right"><a href="{{ route('trainers.results.unit', $u) }}" class="text-red-700 hover:underline">Enter marks</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-center text-gray-500">No units are assigned to you yet. The academic office assigns trainers to course units.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dash.panel>
@endsection
