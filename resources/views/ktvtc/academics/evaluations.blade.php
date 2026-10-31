@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Trainer Evaluations')
@section('subtitle', 'Anonymous student feedback per unit and trainer')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<div class="flex gap-2">
    <a href="{{ route('academics.evaluations.questions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-list mr-1"></i> Questions</a>
    <a href="{{ route('academics.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Academics</a>
</div>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="space-y-6">
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Trainer</label>
            <select name="trainer_id" class="border rounded-lg px-3 py-2">
                <option value="">All trainers</option>
                @foreach($trainers as $t)<option value="{{ $t->id }}" @selected(request('trainer_id') == $t->id)>{{ $t->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500 mb-1">From</label><input type="date" name="from" value="{{ request('from') }}" class="border rounded-lg px-3 py-2"></div>
        <div><label class="block text-xs text-gray-500 mb-1">To</label><input type="date" name="to" value="{{ request('to') }}" class="border rounded-lg px-3 py-2"></div>
        <button class="px-4 py-2 bg-primary text-white rounded-lg">Filter</button>
    </form>

    <p class="text-sm text-gray-500">Ratings are 1 (strongly disagree) to 5 (strongly agree). A unit's results appear once at least {{ $min }} students have answered, so nobody can be identified.</p>

    @forelse($rows as $row)
        <div class="bg-white rounded-xl border p-5">
            <div class="flex flex-wrap justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-800">{{ $row['trainer']->name ?? 'Unknown trainer' }}</p>
                    <p class="text-sm text-gray-500">{{ $row['unit']->label ?? '?' }} · {{ $row['unit']->course->name ?? '' }} · Term {{ $row['unit']->term_number ?? '' }}</p>
                </div>
                <div class="text-right">
                    @if($row['visible'])
                        <p class="text-2xl font-bold {{ $row['overall'] >= 4 ? 'text-green-600' : ($row['overall'] >= 3 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($row['overall'], 2) }} / 5</p>
                    @endif
                    <p class="text-xs text-gray-500">{{ $row['responses'] }} response(s)</p>
                </div>
            </div>
            @if($row['visible'])
                <div class="mt-3 space-y-1">
                    @foreach($row['questions'] as $q)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="flex-1">{{ $q['question'] }}</span>
                            <div class="w-40 bg-gray-100 rounded h-3"><div class="h-3 rounded {{ $q['average'] >= 4 ? 'bg-green-500' : ($q['average'] >= 3 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $q['average'] / 5 * 100 }}%"></div></div>
                            <span class="w-10 text-right font-semibold">{{ number_format($q['average'], 1) }}</span>
                        </div>
                    @endforeach
                </div>
                @if($row['comments']->isNotEmpty())
                    <div class="mt-3 border-t pt-3 space-y-1">
                        @foreach($row['comments'] as $comment)
                            <p class="text-sm text-gray-700 bg-gray-50 rounded px-3 py-2">“{{ $comment }}”</p>
                        @endforeach
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 mt-2">Waiting for at least {{ $min }} responses before showing results.</p>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl border p-6 text-sm text-gray-500">No evaluations yet.</div>
    @endforelse

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Who has finished (current terms)</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Course</th><th class="py-2">Term</th><th class="py-2 text-right">Students</th><th class="py-2 text-right">All evaluations done</th></tr></thead>
            <tbody>
                @forelse($completion as $c)
                    <tr class="border-b"><td class="py-2">{{ $c['course'] }}</td><td class="py-2">{{ $c['term'] }}</td><td class="py-2 text-right">{{ $c['students'] }}</td>
                        <td class="py-2 text-right">{{ $c['complete'] }} ({{ $c['students'] ? round($c['complete'] / $c['students'] * 100) : 0 }}%)</td></tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">No terms running.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
