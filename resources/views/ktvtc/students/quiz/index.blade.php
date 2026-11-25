@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Portal Quiz')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800">Portal Quiz</h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ count($questions) }} quick questions on paying your fees, evaluating your trainers and using your dashboard.
            You see your score and the right answers straight away, and you can take it again as often as you like.
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('student.quiz.store') }}" class="space-y-4" id="quizForm">
        @csrf
        @foreach($questions as $i => $q)
            <fieldset class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <legend class="sr-only">Question {{ $i + 1 }}</legend>
                <p class="font-semibold text-gray-800 mb-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-700 text-white text-sm mr-2">{{ $i + 1 }}</span>{{ $q['q'] }}
                </p>
                <div class="space-y-2">
                    @foreach($q['options'] as $o => $option)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                            <input type="radio" name="answers[{{ $i }}]" value="{{ $o }}" required class="mt-1 accent-red-700"
                                   @checked((string) old("answers.$i") === (string) $o)>
                            <span class="text-sm text-gray-700 break-words min-w-0">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-wrap items-center justify-between gap-3">
            <span class="text-sm text-gray-500" id="quizProgress">Answer all {{ count($questions) }} questions.</span>
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-check mr-2"></i>Submit answers
            </button>
        </div>
    </form>

    @include('ktvtc.students.quiz._history')
</div>

<script>
    (function () {
        const form = document.getElementById('quizForm'), out = document.getElementById('quizProgress'), total = {{ count($questions) }};
        form.addEventListener('change', () => {
            const done = new Set([...form.querySelectorAll('input[type=radio]:checked')].map(r => r.name)).size;
            out.textContent = done + ' of ' + total + ' answered';
        });
    })();
</script>
@endsection
