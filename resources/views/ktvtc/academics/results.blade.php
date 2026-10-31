@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Marks & grades')
@section('subtitle', 'Every mark entered by trainers, with how the total and grade were worked out')

@section('content')
@include('ktvtc.academics.partials.readonly')

<x-dash.panel title="Filter">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="course_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">All courses</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @if($units->isNotEmpty())
            <select name="unit_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">All units</option>
                @foreach($units as $u)<option value="{{ $u->id }}" @selected(request('unit_id') == $u->id)>Term {{ $u->term_number }} · {{ $u->code ? $u->code . ' ' : '' }}{{ $u->title }}</option>@endforeach
            </select>
        @endif
        <select name="term" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">All terms</option>
            @for($t = 1; $t <= 8; $t++)<option value="{{ $t }}" @selected(request('term') == $t)>Term {{ $t }}</option>@endfor
        </select>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Any status</option>
            @foreach(['draft' => 'Draft (trainer still working)', 'submitted' => 'Submitted, waiting for publishing', 'published' => 'Published to students'] as $k => $l)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Student name or number" class="border border-gray-300 rounded-lg px-3 py-2">
        <button class="px-4 py-2 rounded-lg bg-gray-800 text-white">Show</button>
    </form>
</x-dash.panel>

<div class="mt-6">
<x-dash.panel title="Marks" :subtitle="number_format($results->total()) . ' result(s)'">
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-gray-500 border-b">
                    <th class="py-2 px-1">Student</th><th class="py-2 px-1">Unit</th><th class="py-2 px-1">Trainer</th>
                    <th class="py-2 px-1">How the total is worked out</th><th class="py-2 px-1">Grade</th><th class="py-2 px-1">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $r)
                    @php
                        $scale = $scales[$r->unit?->course?->id] ?? null;
                        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
                    @endphp
                    <tr class="border-b last:border-0 align-top">
                        <td class="py-2 px-1">{{ trim(($r->student->first_name ?? '') . ' ' . ($r->student->last_name ?? '')) }}<span class="block text-xs text-gray-500 font-mono">{{ $r->student->student_number ?? '' }}</span></td>
                        <td class="py-2 px-1">{{ $r->unit->title ?? '—' }}
                            <span class="block text-xs text-gray-500">{{ $r->unit->course->name ?? '' }} · Term {{ $r->unit->term_number ?? '' }}@if($r->attempt > 1) · re-sit {{ $r->attempt - 1 }}@endif</span></td>
                        <td class="py-2 px-1 text-gray-600">{{ $r->trainer->name ?? '—' }}</td>
                        <td class="py-2 px-1 font-mono text-xs text-gray-700">
                            @if($scale && !is_null($r->score_a) && !is_null($r->score_b))
                                {{ $scale->component_a }} {{ $fmt($r->score_a) }} × {{ $scale->weight_a }}% + {{ $scale->component_b }} {{ $fmt($r->score_b) }} × {{ $scale->weight_b }}%
                                = <b class="text-gray-900">{{ $fmt($r->total) }}</b>
                            @elseif(!is_null($r->score_a) || !is_null($r->score_b))
                                <span class="text-amber-700">Incomplete: {{ $scale->component_a ?? 'A' }} {{ is_null($r->score_a) ? '—' : $fmt($r->score_a) }}, {{ $scale->component_b ?? 'B' }} {{ is_null($r->score_b) ? '—' : $fmt($r->score_b) }}</span>
                            @else
                                <span class="text-gray-400">No marks yet</span>
                            @endif
                            @if($r->remarks)<span class="block font-sans text-gray-500">“{{ $r->remarks }}”</span>@endif
                        </td>
                        <td class="py-2 px-1 font-semibold {{ $r->passed === null ? 'text-gray-400' : ($r->passed ? 'text-green-700' : 'text-red-700') }}">{{ $r->grade ?? '—' }}</td>
                        <td class="py-2 px-1">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ ['draft' => 'bg-gray-100 text-gray-700', 'submitted' => 'bg-amber-100 text-amber-800', 'published' => 'bg-green-100 text-green-800'][$r->status] ?? 'bg-gray-100' }}">{{ $r->status }}</span>
                            @if($r->status === 'submitted')<a href="{{ route('academics.review.unit', $r->course_unit_id) }}" class="block text-xs text-red-700 hover:underline mt-1">Review &rarr;</a>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-500">No marks match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($scales)
        <p class="text-xs text-gray-500 mt-4">Grade bands come from <a href="{{ route('academics.scales') }}" class="underline">Grading scales</a>: each track sets its two components, their weights and the cut-offs.</p>
    @endif
    <div class="mt-4">{{ $results->links() }}</div>
</x-dash.panel>
</div>
@endsection
