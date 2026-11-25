@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Quiz Scores')
@section('subtitle', 'How students did on the portal quiz, and who has watched the how-to video')

@section('breadcrumb')
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Quiz Scores</span>
    </div>
</li>
@endsection

@section('content')
@php
    $tone = fn ($p) => $p === null ? 'text-gray-400' : ($p >= 80 ? 'text-green-700' : ($p >= 50 ? 'text-amber-600' : 'text-red-700'));
    $bar = fn ($p) => $p >= 80 ? 'bg-green-500' : ($p >= 50 ? 'bg-amber-500' : 'bg-red-500');
    $pct = fn ($n) => $summary['students'] ? round($n * 100 / $summary['students']) : 0;
@endphp
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Took the quiz</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['taken']) }} <span class="text-sm font-normal text-gray-500">of {{ number_format($summary['students']) }}</span></p>
            <p class="text-xs text-gray-500">{{ $pct($summary['taken']) }}% of students with a portal login</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Average best score</p>
            <p class="text-2xl font-bold {{ $tone($summary['average'] !== null ? (int) $summary['average'] : null) }}">{{ $summary['average'] !== null ? round($summary['average']) . '%' : '-' }}</p>
            <p class="text-xs text-gray-500">Each student's best attempt</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Full marks</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($summary['perfect']) }}</p>
            <p class="text-xs text-gray-500">Students who scored 10/10</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Watched the how-to video</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($summary['video']) }}</p>
            <p class="text-xs text-gray-500">{{ $pct($summary['video']) }}% of students with a portal login</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-bold text-gray-800">Questions students get wrong</h3>
        <p class="text-sm text-gray-500 mb-4">
            @if($counted)
                Share of students who answered correctly on their latest attempt ({{ number_format($counted) }} {{ Str::plural('student', $counted) }}). Low ones are worth explaining in class.
            @else
                No one has taken the quiz yet.
            @endif
        </p>
        @if($counted)
            <ul class="space-y-2">
                @foreach($byQuestion->sortBy('percent') as $i => $row)
                    <li class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_10rem_3rem] items-center gap-x-3 gap-y-1 text-sm">
                        <span class="text-gray-700 min-w-0">{{ $i + 1 }}. {{ $row['q'] }}</span>
                        <span class="hidden sm:block h-2 bg-gray-100 rounded-full overflow-hidden"><span class="block h-full {{ $bar($row['percent']) }}" style="width: {{ $row['percent'] }}%"></span></span>
                        <b class="text-right {{ $tone($row['percent']) }}">{{ $row['percent'] }}%</b>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex gap-2">
                <a href="{{ route('admin.quiz-scores.index', ['tab' => 'taken', 'search' => $search ?: null]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold {{ $tab === 'taken' ? 'bg-red-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Took the quiz</a>
                <a href="{{ route('admin.quiz-scores.index', ['tab' => 'not-taken', 'search' => $search ?: null]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold {{ $tab === 'not-taken' ? 'bg-red-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Not taken yet</a>
            </div>
            <form method="GET" class="flex gap-2 w-full sm:w-auto">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Name, student number or phone"
                       class="flex-1 sm:w-72 min-w-0 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <button class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-3 font-semibold">Student</th>
                        <th class="py-2 pr-3 font-semibold">Phone</th>
                        @if($tab === 'taken')
                            <th class="py-2 pr-3 font-semibold text-right">Best</th>
                            <th class="py-2 pr-3 font-semibold text-right">Latest</th>
                            <th class="py-2 pr-3 font-semibold text-right">Attempts</th>
                            <th class="py-2 pr-3 font-semibold">Last taken</th>
                        @else
                            <th class="py-2 pr-3 font-semibold">Last signed in</th>
                        @endif
                        <th class="py-2 font-semibold">How-to video</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php $best = $r->total ? (int) round($r->best * 100 / $r->total) : null; @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-3"><span class="font-medium text-gray-800">{{ $r->name }}</span><span class="block text-xs text-gray-500">{{ $r->student_number }}</span></td>
                            <td class="py-2 pr-3 text-gray-600 whitespace-nowrap">{{ $r->phone }}</td>
                            @if($tab === 'taken')
                                <td class="py-2 pr-3 text-right font-bold {{ $tone($best) }}">{{ $r->best }}/{{ $r->total }}</td>
                                <td class="py-2 pr-3 text-right text-gray-700">{{ $latest[$r->id] ?? '-' }}/{{ $r->total }}</td>
                                <td class="py-2 pr-3 text-right text-gray-700">{{ $r->attempts }}</td>
                                <td class="py-2 pr-3 text-gray-600 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($r->last_at)->format('d M Y, H:i') }}</td>
                            @else
                                <td class="py-2 pr-3 text-gray-600 whitespace-nowrap">{{ $r->last_login_at ? \Illuminate\Support\Carbon::parse($r->last_login_at)->format('d M Y') : 'Never' }}</td>
                            @endif
                            <td class="py-2 whitespace-nowrap">
                                @if($r->welcome_video_watched_at)
                                    <span class="text-green-700"><i class="fas fa-check-circle mr-1"></i>{{ \Illuminate\Support\Carbon::parse($r->welcome_video_watched_at)->format('d M Y') }}</span>
                                @else
                                    <span class="text-gray-400">Not yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-500">{{ $tab === 'taken' ? 'No student has taken the quiz yet.' : 'Every student has taken the quiz.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</div>
@endsection
