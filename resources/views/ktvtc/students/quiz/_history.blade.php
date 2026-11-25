{{-- My scores: every attempt, newest first --}}
@if($attempts->isNotEmpty())
    @php $best = $attempts->max('score'); $total = $attempts->first()->total; @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-bold text-gray-800"><i class="fas fa-chart-line text-red-700 mr-2"></i>My scores</h3>
            <span class="text-sm text-gray-600">Best: <b class="text-green-700">{{ $best }}/{{ $total }}</b> · {{ $attempts->count() }} {{ Str::plural('attempt', $attempts->count()) }}</span>
        </div>
        <ul class="divide-y text-sm">
            @foreach($attempts as $a)
                <li class="py-2 flex items-center justify-between gap-3">
                    <span class="text-gray-600">{{ $a->created_at->format('d M Y, H:i') }}</span>
                    <span class="flex items-center gap-3">
                        <span class="w-28 h-2 bg-gray-100 rounded-full overflow-hidden hidden sm:block">
                            <span class="block h-full {{ $a->percent() >= 80 ? 'bg-green-500' : ($a->percent() >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $a->percent() }}%"></span>
                        </span>
                        <b class="{{ $a->percent() >= 80 ? 'text-green-700' : ($a->percent() >= 50 ? 'text-amber-700' : 'text-red-700') }}">{{ $a->score }}/{{ $a->total }}</b>
                        <a href="{{ route('student.quiz.show', $a) }}" class="text-red-700 hover:underline">Review</a>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
