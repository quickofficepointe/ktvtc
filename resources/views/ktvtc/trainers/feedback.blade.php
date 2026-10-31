@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'My Feedback')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <h2 class="text-lg font-bold text-gray-800">Student feedback</h2>
        <p class="text-sm text-gray-500">Anonymous. Each unit's results appear once at least {{ $min }} students have responded. Ratings are 1 (strongly disagree) to 5 (strongly agree).</p>
    </div>

    @forelse($rows as $row)
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <div class="flex justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-800">{{ $row['unit']->label ?? '?' }}</p>
                    <p class="text-sm text-gray-500">{{ $row['unit']->course->name ?? '' }} · Term {{ $row['unit']->term_number ?? '' }}</p>
                </div>
                <div class="text-right">
                    @if($row['visible'])<p class="text-2xl font-bold text-primary">{{ number_format($row['overall'], 2) }} / 5</p>@endif
                    <p class="text-xs text-gray-500">{{ $row['responses'] }} response(s)</p>
                </div>
            </div>
            @if($row['visible'])
                <div class="mt-3 space-y-1">
                    @foreach($row['questions'] as $q)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="flex-1">{{ $q['question'] }}</span>
                            <span class="font-semibold">{{ number_format($q['average'], 1) }}</span>
                        </div>
                    @endforeach
                </div>
                @foreach($row['comments'] as $comment)
                    <p class="text-sm text-gray-700 bg-gray-50 rounded px-3 py-2 mt-2">“{{ $comment }}”</p>
                @endforeach
            @else
                <p class="text-sm text-gray-500 mt-2">Not enough responses yet.</p>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-sm border p-5 text-sm text-gray-500">No feedback yet.</div>
    @endforelse
</div>
@endsection
