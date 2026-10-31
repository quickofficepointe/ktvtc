@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="px-4 py-6">
<x-dash.header title="Mobile school dashboard" :subtitle="$schools . ' active mobile schools'">
    <x-slot name="actions">
        <x-dash.action :href="route('mschool.students.index')" icon="fa-users">Students</x-dash.action>
        <x-dash.action :href="route('mschool.certificates.index')" icon="fa-certificate">Certificates</x-dash.action>
        <x-dash.action :href="route('mschool.attendances.index')" icon="fa-clipboard-check" primary>Take attendance</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Students" :value="number_format($students)" icon="fa-users" :hint="number_format($completed) . ' have completed a course'" :href="route('mschool.students.index')" />
    <x-dash.kpi label="Studying now" :value="number_format($active)" icon="fa-book-open" tone="blue" hint="Active and pending enrollments" :href="route('mschool.enrollments.index')" />
    <x-dash.kpi label="Attendance this month" :value="is_null($attendanceRate) ? '—' : $attendanceRate . '%'" icon="fa-clipboard-check"
        :tone="is_null($attendanceRate) ? 'gray' : ($attendanceRate >= 80 ? 'green' : 'amber')" :progress="$attendanceRate"
        :hint="$sessionsMonth . ' session' . ($sessionsMonth == 1 ? '' : 's') . ' recorded (late counts as present)'" :href="route('mschool.attendances.index')" />
    <x-dash.kpi label="Certificates issued" :value="number_format($certificates)" icon="fa-certificate" tone="green" :href="route('mschool.certificates.index')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="New enrollments, last 12 months" class="xl:col-span-2">
        <x-dash.chart type="bar" :money="false" :labels="$enrollTrend['labels']" :datasets="[['label' => 'Enrollments', 'data' => $enrollTrend['data'], 'color' => '#b91c1c']]" />
    </x-dash.panel>
    <x-dash.panel title="Enrollments by mobile school">
        <x-dash.bars :rows="$bySchool" :money="false" tone="bg-blue-600" />
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-dash.panel title="Enrollments by course">
        <x-dash.bars :rows="$byCourse" :money="false" />
    </x-dash.panel>
    <x-dash.panel title="Latest attendance sessions" :href="route('mschool.attendances.index')">
        <ul class="divide-y text-sm">
            @forelse($recentSessions as $s)
                @php $rate = $s->total_expected > 0 ? round(($s->total_present + $s->total_late) / $s->total_expected * 100) : null; @endphp
                <li class="py-2 flex justify-between gap-3">
                    <a href="{{ route('mschool.attendances.show', $s->attendance_id) }}" class="hover:text-red-700">
                        <span class="font-medium text-gray-900">{{ $s->session_name ?: 'Class session' }}</span>
                        <span class="block text-xs text-gray-500">{{ \Carbon\Carbon::parse($s->attendance_date)->format('d M Y') }} · {{ $s->course_name ?? 'No course' }}</span>
                    </a>
                    <span class="text-sm tabular-nums {{ is_null($rate) ? 'text-gray-400' : ($rate >= 80 ? 'text-green-700' : 'text-amber-700') }}">{{ is_null($rate) ? 'not marked' : $rate . '%' }}</span>
                </li>
            @empty
                <li class="py-2 text-gray-500">No sessions yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
</div>
</div>
@endsection
