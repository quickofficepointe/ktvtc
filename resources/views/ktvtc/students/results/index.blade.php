@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Results')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">My Results</h2>
            <p class="text-sm text-gray-500">Results appear here once the academic office publishes them.</p>
        </div>
        @if($courses->isNotEmpty())
            <a href="{{ route('student.results.transcript') }}" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fas fa-file-pdf mr-1"></i> Download transcript
            </a>
        @endif
    </div>

    @forelse($courses as $c)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="font-bold text-gray-800 text-lg">{{ $c['enrollment']->course_name }}
                @if($c['track'])<span class="text-xs bg-gray-100 px-2 py-0.5 rounded ml-1 font-normal">{{ $c['track'] }}</span>@endif
            </h3>
            @foreach($c['terms'] as $term => $results)
                <div>
                    <p class="font-semibold text-gray-700 mb-2">Term {{ $term }}</p>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-3">Unit</th>
                                <th class="py-2 pr-3 text-right">{{ $c['scale']->component_a ?? 'Score 1' }}</th>
                                <th class="py-2 pr-3 text-right">{{ $c['scale']->component_b ?? 'Score 2' }}</th>
                                <th class="py-2 pr-3 text-right">Total</th>
                                <th class="py-2">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $r)
                                <tr class="border-b">
                                    <td class="py-2 pr-3">{{ $r->unit->label }}
                                        @if($r->attempts_count > 1)<span class="text-xs text-gray-400">({{ $r->attempts_count }} attempts)</span>@endif
                                    </td>
                                    <td class="py-2 pr-3 text-right">{{ $r->score_a }}</td>
                                    <td class="py-2 pr-3 text-right">{{ $r->score_b }}</td>
                                    <td class="py-2 pr-3 text-right font-semibold">{{ $r->total }}</td>
                                    <td class="py-2 {{ $r->passed ? 'text-green-700' : 'text-red-600 font-semibold' }}">{{ $r->grade }}
                                        @unless($r->passed)<span class="text-xs block font-normal">You will be listed for a re-sit.</span>@endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-sm text-gray-600">No published results yet.</div>
    @endforelse
</div>
@endsection
