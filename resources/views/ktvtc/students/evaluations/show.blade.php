@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Evaluate Trainers')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))
            <div class="rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 text-{{ $color }}-800 px-4 py-3 text-sm">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800">Evaluate your trainers · Term {{ $registration->term_number }}</h2>
        <p class="text-sm text-gray-600 mt-1">{{ $registration->course->name ?? '' }}</p>
        <p class="text-sm text-gray-500 mt-3">
            <i class="fas fa-user-secret mr-1"></i>
            Your answers are anonymous: trainers and staff only see combined results from several students, never your name.
            You need to evaluate every trainer to unlock this term's exam card.
        </p>
        <a href="{{ route('student.enrollment.index') }}" class="inline-block mt-3 text-sm text-primary hover:underline">← Back to My Terms</a>
    </div>

    @forelse($units as $unit)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start gap-3">
                <div>
                    <p class="font-semibold text-gray-800">{{ $unit->label }}</p>
                    <p class="text-sm text-gray-500">Trainer: {{ $unit->trainer->name ?? '—' }}</p>
                </div>
                @if($unit->evaluated)
                    <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full"><i class="fas fa-check mr-1"></i>Done</span>
                @endif
            </div>

            @unless($unit->evaluated)
                <form method="POST" action="{{ route('student.evaluations.store', [$registration, $unit]) }}" class="mt-4 space-y-4">
                    @csrf
                    @foreach($questions as $q)
                        <div>
                            <p class="text-sm text-gray-700 mb-1">{{ $q->text }} @if($q->is_required)<span class="text-red-500">*</span>@endif</p>
                            @if($q->type === 'rating')
                                <div class="flex flex-wrap gap-2">
                                    @foreach([1 => 'Strongly disagree', 2 => 'Disagree', 3 => 'Neutral', 4 => 'Agree', 5 => 'Strongly agree'] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $value }}" class="peer sr-only"
                                                   @checked(session('open_unit') == $unit->id && old("answers.{$q->id}") == $value) @required($q->is_required)>
                                            <span class="block border rounded-lg px-3 py-1.5 text-xs peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary">
                                                {{ $value }} · {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <textarea name="answers[{{ $q->id }}]" rows="3" maxlength="1000" class="w-full border rounded-lg px-3 py-2 text-sm"
                                          @required($q->is_required)>{{ session('open_unit') == $unit->id ? old("answers.{$q->id}") : '' }}</textarea>
                            @endif
                        </div>
                    @endforeach
                    <button class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-semibold">Submit evaluation</button>
                </form>
            @endunless
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-sm text-gray-600">
            There are no trainers to evaluate for this term.
        </div>
    @endforelse
</div>
@endsection
