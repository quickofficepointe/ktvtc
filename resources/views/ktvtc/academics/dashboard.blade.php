@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Academics dashboard')

@section('content')
<x-dash.header title="Academics dashboard" subtitle="Trainers, marks and results">
    <x-slot name="actions">
        <x-dash.action :href="route('academics.trainers.index')" icon="fa-chalkboard-teacher">Trainers</x-dash.action>
        <x-dash.action :href="route('academics.results')" icon="fa-calculator">Marks &amp; grades</x-dash.action>
        <x-dash.action :href="route('academics.review')" icon="fa-check-double" primary>Results to publish</x-dash.action>
    </x-slot>
</x-dash.header>

@include('ktvtc.academics.partials.readonly')

@if($unitsWithoutTrainer || $coursesWithoutUnits)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <i class="fas fa-triangle-exclamation mr-1"></i>
        @if($coursesWithoutUnits){{ $coursesWithoutUnits }} course(s) with students have no units yet. @endif
        @if($unitsWithoutTrainer){{ $unitsWithoutTrainer }} unit(s) have no trainer, so nobody can enter their marks. @endif
        <a href="{{ route('academics.index') }}" class="underline">Set them up</a>
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Trainers" :value="number_format($trainers)" icon="fa-chalkboard-teacher" :hint="number_format($units) . ' active units'" :href="route('academics.trainers.index')" />
    <x-dash.kpi label="Marks entered this term" :value="$expected ? round($entered / $expected * 100) . '%' : '—'" icon="fa-pen" tone="blue"
        :progress="$expected ? $entered / $expected * 100 : null" :hint="number_format($entered) . ' of ' . number_format($expected) . ' student-unit marks'" :href="route('academics.results')" />
    <x-dash.kpi label="Results to publish" :value="number_format($toPublish)" icon="fa-check-double" :tone="$toPublish ? 'amber' : 'green'"
        :hint="number_format($drafts) . ' still in draft with trainers'" :href="route('academics.review')" />
    <x-dash.kpi label="Pass rate" :value="is_null($passRate) ? '—' : $passRate . '%'" icon="fa-award" :tone="is_null($passRate) ? 'gray' : ($passRate >= 70 ? 'green' : 'amber')"
        :hint="number_format($published) . ' published results'" :href="route('academics.results', ['status' => 'published'])" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Submitted by trainers, waiting for you" class="xl:col-span-2" :href="route('academics.review')">
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-gray-500 border-b"><th class="py-2 px-1">Unit</th><th class="py-2 px-1">Course</th><th class="py-2 px-1">Trainer</th><th class="py-2 px-1 text-right">Students</th><th class="py-2 px-1">Submitted</th></tr></thead>
                <tbody>
                    @forelse($awaiting as $u)
                        <tr class="border-b last:border-0">
                            <td class="py-2 px-1"><a href="{{ route('academics.review.unit', $u->id) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $u->title }}</a></td>
                            <td class="py-2 px-1 text-gray-600">{{ $u->course }} · Term {{ $u->term_number }}</td>
                            <td class="py-2 px-1 text-gray-600">{{ $u->trainer ?? '—' }}</td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ $u->n }}</td>
                            <td class="py-2 px-1 text-gray-500 whitespace-nowrap">{{ $u->submitted_at ? \Carbon\Carbon::parse($u->submitted_at)->diffForHumans() : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-500">Nothing waiting. Results appear here when trainers submit them.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dash.panel>
    <x-dash.panel title="Published results by grade">
        <x-dash.bars :rows="$byGrade" :money="false" tone="bg-green-600" empty="No published results yet." />
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-dash.panel title="Units per trainer" :href="route('academics.trainers.index')">
        <x-dash.bars :rows="$workload" :money="false" tone="bg-blue-600" empty="No trainer accounts yet. Add trainers first." />
    </x-dash.panel>
    <x-dash.panel title="Exam bodies (KNEC, CDACC, NITA)" :href="route('academics.exam-registrations.index')">
        <ul class="space-y-3 text-sm">
            <li class="flex justify-between"><a href="{{ route('academics.exam-registrations.index', ['status' => 'pending']) }}" class="text-gray-700 hover:text-red-700">Waiting to be registered</a><span class="font-semibold tabular-nums">{{ number_format($examPending) }}</span></li>
            <li class="flex justify-between"><a href="{{ route('academics.exam-registrations.index', ['status' => 'registered']) }}" class="text-gray-700 hover:text-red-700">Registered, results not in yet</a><span class="font-semibold tabular-nums">{{ number_format($examAwaitingResults) }}</span></li>
            <li class="flex justify-between"><a href="{{ route('academics.exam-registrations.reports.summary') }}" class="text-gray-700 hover:text-red-700">Exam results summary</a><span class="text-gray-400">&rarr;</span></li>
        </ul>
    </x-dash.panel>
    <x-dash.panel title="Trainer evaluations" :href="route('academics.evaluations')">
        <p class="text-3xl font-bold tabular-nums text-gray-900">{{ number_format($evaluations) }}</p>
        <p class="text-sm text-gray-500">student responses in the last 3 months (anonymous; results show from 3 responses per unit)</p>
    </x-dash.panel>
</div>
@endsection
