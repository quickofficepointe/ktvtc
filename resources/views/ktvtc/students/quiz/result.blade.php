@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Quiz Result')

@section('content')
@php
    $pct = $attempt->percent();
    $tone = $pct >= 80 ? 'green' : ($pct >= 50 ? 'amber' : 'red');
    $message = $pct === 100 ? 'Perfect! You know your portal inside out.'
        : ($pct >= 80 ? 'Well done! Check the ones you missed below.'
        : ($pct >= 50 ? 'Good start. Read the explanations below and try again.'
        : 'Read the explanations below, then take the quiz again.'));
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
        <p class="text-sm text-gray-500">Your score · {{ $attempt->created_at->format('d M Y, H:i') }}</p>
        <p class="text-5xl font-bold my-2 {{ $tone === 'green' ? 'text-green-700' : ($tone === 'amber' ? 'text-amber-600' : 'text-red-700') }}">
            {{ $attempt->score }}/{{ $attempt->total }}
        </p>
        <p class="text-gray-700">{{ $message }}</p>
        <div class="flex flex-wrap justify-center gap-3 mt-4">
            <a href="{{ route('student.quiz.index') }}" class="bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-lg font-semibold"><i class="fas fa-redo mr-2"></i>Take it again</a>
            <a href="{{ route('student.dashboard') }}" class="border border-gray-300 hover:bg-gray-50 px-5 py-2.5 rounded-lg font-semibold text-gray-700">Back to dashboard</a>
        </div>
    </div>

    @foreach($questions as $i => $q)
        @php $row = $result['rows'][$i]; @endphp
        <div class="bg-white rounded-xl shadow-sm border p-5 {{ $row['right'] ? 'border-green-200' : 'border-red-200' }}">
            <p class="font-semibold text-gray-800 mb-3 flex items-start gap-2">
                <i class="fas {{ $row['right'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} mt-1"></i>
                <span>{{ $i + 1 }}. {{ $q['q'] }}</span>
            </p>
            <ul class="space-y-1.5 text-sm">
                @foreach($q['options'] as $o => $option)
                    @php $isAnswer = $o === $q['answer']; $isChosen = $o === $row['chosen']; @endphp
                    <li class="px-3 py-2 rounded-lg break-words {{ $isAnswer ? 'bg-green-50 text-green-900 font-medium' : ($isChosen ? 'bg-red-50 text-red-900 line-through' : 'text-gray-500') }}">
                        {{ $option }}
                        @if($isAnswer) <span class="ml-1 text-xs">(correct answer)</span>@elseif($isChosen) <span class="ml-1 text-xs no-underline">(your answer)</span>@endif
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-sm text-gray-700 bg-gray-50 rounded-lg p-3"><i class="fas fa-lightbulb text-amber-500 mr-1"></i>{{ $q['why'] }}</p>
        </div>
    @endforeach

    @include('ktvtc.students.quiz._history')
</div>
@endsection
