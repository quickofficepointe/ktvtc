@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Dashboard')

@section('content')
<x-dash.header title="Admissions & academics dashboard" subtitle="{{ auth()->user()->role == 2 ? 'All campuses' : (auth()->user()->campus->name ?? 'Your campus') }}">
    <x-slot name="actions">
        <x-dash.action :href="route('admin.applications.index')" icon="fa-file-signature">Applications</x-dash.action>
        <x-dash.action :href="route('admin.students.index')" icon="fa-user-graduate">Students</x-dash.action>
        <x-dash.action :href="route('admin.enrollments.index')" icon="fa-plus" primary>Enroll a student</x-dash.action>
    </x-slot>
</x-dash.header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Active students" :value="number_format($activeStudents)" icon="fa-user-graduate" :hint="number_format($allStudents) . ' student records in total'" :href="route('admin.students.index')" />
    <x-dash.kpi label="Open enrollments" :value="number_format($openEnrollments)" icon="fa-book-open" tone="blue" :hint="number_format($newThisMonth) . ' new this month'" :href="route('admin.enrollments.index')" />
    <x-dash.kpi label="Applications to review" :value="number_format($pendingApplications)" icon="fa-file-signature" tone="amber"
        :hint="$acceptedNotAdmitted ? number_format($acceptedNotAdmitted) . ' accepted, waiting to be admitted' : 'Accepted ones are all admitted'" :href="route('admin.applications.index')" />
    <x-dash.kpi label="Results to publish" :value="number_format($resultsToPublish)" icon="fa-award" :tone="$resultsToPublish ? 'red' : 'green'"
        hint="Submitted by trainers, waiting for you" :href="route('academics.review')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Enrollments and applications, last 12 months" class="xl:col-span-2">
        <x-dash.chart type="bar" :money="false" :labels="$enrollTrend['labels']" :datasets="[
            ['label' => 'New enrollments', 'data' => $enrollTrend['data'], 'color' => '#b91c1c'],
            ['label' => 'Online applications', 'data' => $applyTrend['data'], 'color' => '#f59e0b'],
        ]" />
    </x-dash.panel>
    <x-dash.panel title="Needs attention">
        <ul class="space-y-3 text-sm">
            <li class="flex justify-between gap-3"><a href="{{ route('admin.students.duplicates') }}" class="text-gray-700 hover:text-red-700">Possible duplicate students</a><span class="text-gray-400">Review &rarr;</span></li>
            <li class="flex justify-between gap-3"><a href="{{ route('admin.students.index', ['portal' => 'never']) }}" class="text-gray-700 hover:text-red-700">Enrolled, never signed in to the portal</a><span class="font-semibold tabular-nums {{ $neverSignedIn ? 'text-amber-700' : '' }}">{{ number_format($neverSignedIn) }}</span></li>
            <li class="flex justify-between gap-3"><span class="text-gray-700">Students with no enrollment</span><span class="font-semibold tabular-nums">{{ number_format($withoutEnrollment) }}</span></li>
            <li class="flex justify-between gap-3"><span class="text-gray-700">Course units without a trainer</span><span class="font-semibold tabular-nums {{ $unitsWithoutTrainer ? 'text-red-700' : '' }}">{{ number_format($unitsWithoutTrainer) }}</span></li>
            <li class="flex justify-between gap-3"><span class="text-gray-700">Students in a term right now</span><span class="font-semibold tabular-nums">{{ number_format($termsRunning) }}</span></li>
        </ul>
        <h3 class="text-xs font-semibold uppercase text-gray-500 mt-6 mb-3">Open enrollments by campus</h3>
        <x-dash.bars :rows="$byCampus" :money="false" tone="bg-blue-600" />
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-dash.panel title="Largest courses" subtitle="Open enrollments">
        <x-dash.bars :rows="$byCourse" :money="false" />
    </x-dash.panel>
    <x-dash.panel title="Latest applications" :href="route('admin.applications.index')">
        <ul class="divide-y text-sm">
            @forelse($recentApplications as $a)
                <li class="py-2 flex justify-between gap-3">
                    <span><span class="font-medium text-gray-900">{{ $a->first_name }} {{ $a->last_name }}</span><span class="block text-xs text-gray-500">{{ $a->course->name ?? 'Course' }} · {{ $a->created_at?->format('d M') }}</span></span>
                    <span class="text-xs px-2 py-0.5 rounded-full self-start {{ ['pending' => 'bg-amber-100 text-amber-800', 'accepted' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-700'][$a->status] ?? 'bg-gray-100 text-gray-700' }}">{{ str_replace('_', ' ', $a->status) }}</span>
                </li>
            @empty
                <li class="py-2 text-gray-500">No applications yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
    <x-dash.panel title="Latest enrollments" :href="route('admin.enrollments.index')">
        <ul class="divide-y text-sm">
            @forelse($recentEnrollments as $e)
                <li class="py-2">
                    <a href="{{ route('admin.enrollments.show', $e) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $e->student ? trim($e->student->first_name . ' ' . $e->student->last_name) : $e->student_name }}</a>
                    <span class="block text-xs text-gray-500">{{ $e->student_number }} · {{ $e->course_name }} · {{ $e->created_at?->format('d M') }}</span>
                </li>
            @empty
                <li class="py-2 text-gray-500">No enrollments yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
</div>
@endsection
