@extends('ktvtc.website.layout.websitelayout')

@section('title', 'Dashboard')

@section('content')
<x-dash.header title="Website dashboard" subtitle="Content, enquiries and applications">
    <x-slot name="actions">
        <x-dash.action :href="route('website.events.index')" icon="fa-calendar">Events</x-dash.action>
        <x-dash.action :href="route('website.messages.index')" icon="fa-envelope">Messages</x-dash.action>
        <x-dash.action :href="route('website.blogs.index')" icon="fa-pen-nib" primary>Write a post</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Applications this month" :value="number_format($applicationsMonth)" icon="fa-file-signature" :hint="number_format($pending) . ' waiting for admissions'" />
    <x-dash.kpi label="Unread messages" :value="number_format($unreadMessages)" icon="fa-envelope" :tone="$unreadMessages ? 'amber' : 'green'" hint="From the contact form" :href="route('website.messages.index')" />
    <x-dash.kpi label="Published posts" :value="number_format($blogs)" icon="fa-newspaper" tone="blue" :href="route('website.blogs.index')" />
    <x-dash.kpi label="Upcoming events" :value="number_format($upcomingEvents)" icon="fa-calendar" tone="green" :href="route('website.events.index')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Online applications, last 12 months" class="xl:col-span-2">
        <x-dash.chart type="bar" :money="false" :labels="$applyTrend['labels']" :datasets="[['label' => 'Applications', 'data' => $applyTrend['data'], 'color' => '#b91c1c']]" />
    </x-dash.panel>
    <x-dash.panel title="Most applied-for courses" subtitle="Last 6 months">
        <x-dash.bars :rows="$byCourse" :money="false" empty="No applications in the last 6 months." />
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-dash.panel title="Latest applications">
        <ul class="divide-y text-sm">
            @forelse($recentApplications as $a)
                <li class="py-2"><span class="font-medium text-gray-900">{{ $a->first_name }} {{ $a->last_name }}</span>
                    <span class="block text-xs text-gray-500">{{ $a->course->name ?? 'Course' }} · {{ $a->created_at?->format('d M') }} · {{ str_replace('_', ' ', $a->status) }}</span></li>
            @empty
                <li class="py-2 text-gray-500">No applications yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
    <x-dash.panel title="Latest messages" :href="route('website.messages.index')">
        <ul class="divide-y text-sm">
            @forelse($recentMessages as $m)
                <li class="py-2"><span class="font-medium text-gray-900">{{ $m->name }}</span> <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($m->created_at)->format('d M') }}</span>
                    <span class="block text-gray-600">{{ \Illuminate\Support\Str::limit($m->message, 90) }}</span></li>
            @empty
                <li class="py-2 text-gray-500">No messages yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
    <x-dash.panel title="Coming up" :href="route('website.events.index')">
        <ul class="divide-y text-sm">
            @forelse($events as $ev)
                <li class="py-2"><span class="font-medium text-gray-900">{{ $ev->title }}</span>
                    <span class="block text-xs text-gray-500">{{ \Carbon\Carbon::parse($ev->event_start_date)->format('d M Y') }} · {{ $ev->location }}</span></li>
            @empty
                <li class="py-2 text-gray-500">No upcoming events. Add one so it shows on the website.</li>
            @endforelse
        </ul>
    </x-dash.panel>
</div>
@endsection
