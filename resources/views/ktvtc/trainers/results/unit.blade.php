@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', $unit->label)

@section('content')
@php
    $badge = ['draft' => 'bg-gray-100 text-gray-700', 'submitted' => 'bg-yellow-100 text-yellow-800', 'published' => 'bg-green-100 text-green-800'];
@endphp
<div class="bg-white rounded-lg shadow-sm border p-5 space-y-4">
    <div class="flex flex-wrap justify-between gap-2">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $unit->label }}</h2>
            <p class="text-sm text-gray-500">{{ $unit->course->name }} · Term {{ $unit->term_number }} · {{ $scale->name }}:
                {{ $scale->component_a }} {{ $scale->weight_a }}% + {{ $scale->component_b }} {{ $scale->weight_b }}%, each out of 100</p>
        </div>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('trainers.results.unit', [$unit, 'scope' => 'current']) }}" class="px-3 py-1.5 rounded border {{ $scope === 'current' ? 'bg-primary text-white' : '' }}">Current students</a>
            <a href="{{ route('trainers.results.unit', [$unit, 'scope' => 'all']) }}" class="px-3 py-1.5 rounded border {{ $scope === 'all' ? 'bg-primary text-white' : '' }}">All intakes</a>
            <a href="{{ route('trainers.results.index') }}" class="px-3 py-1.5 rounded border">Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('trainers.results.save', $unit) }}">
        @csrf
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 px-2">Student</th><th class="py-2 px-2">Term dates</th>
                        <th class="py-2 px-2 w-24">{{ $scale->component_a }}</th><th class="py-2 px-2 w-24">{{ $scale->component_b }}</th>
                        <th class="py-2 px-2 text-right">Total</th><th class="py-2 px-2">Grade</th><th class="py-2 px-2">Remarks</th><th class="py-2 px-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roster as $row)
                        @php
                            $reg = $row['registration']; $r = $row['result'];
                            $key = $reg->enrollment_id . ':' . $row['attempt'];
                            $locked = $r && !$r->isEditableByTrainer();
                        @endphp
                        <tr class="border-b mark-row">
                            <td class="py-2 px-2">
                                {{ $reg->student->full_name ?? '—' }}
                                <div class="text-xs text-gray-400">{{ $reg->student->student_number ?? '' }}</div>
                                @if($row['resit'])<span class="text-xs bg-orange-100 text-orange-700 px-1.5 rounded">Re-sit {{ $row['attempt'] - 1 }}</span>@endif
                            </td>
                            <td class="py-2 px-2 text-xs whitespace-nowrap">{{ $reg->starts_on->format('d M') }} – {{ $reg->ends_on->format('d M Y') }}</td>
                            <td class="py-2 px-2"><input type="number" step="0.01" min="0" max="100" name="marks[{{ $key }}][score_a]" value="{{ old("marks.$key.score_a", $r?->score_a) }}" class="score-a w-20 border rounded px-2 py-1" @disabled($locked)></td>
                            <td class="py-2 px-2"><input type="number" step="0.01" min="0" max="100" name="marks[{{ $key }}][score_b]" value="{{ old("marks.$key.score_b", $r?->score_b) }}" class="score-b w-20 border rounded px-2 py-1" @disabled($locked)></td>
                            <td class="py-2 px-2 text-right font-semibold total-cell">{{ $r?->total ?? '—' }}</td>
                            <td class="py-2 px-2 grade-cell">{{ $r?->grade ?? '—' }}</td>
                            <td class="py-2 px-2"><input type="text" maxlength="255" name="marks[{{ $key }}][remarks]" value="{{ old("marks.$key.remarks", $r?->remarks) }}" class="w-40 border rounded px-2 py-1" @disabled($locked)></td>
                            <td class="py-2 px-2">
                                @if($r)
                                    <span class="px-2 py-0.5 rounded text-xs {{ $badge[$r->status] }}">{{ ucfirst($r->status) }}</span>
                                    @if($r->returned_reason)<div class="text-xs text-orange-700">Returned: {{ $r->returned_reason }}</div>@endif
                                @else
                                    <span class="text-xs text-gray-400">Not entered</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-500">No students are due for this unit {{ $scope === 'current' ? 'right now' : '' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roster->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-4">
                <button class="px-4 py-2 bg-gray-700 text-white rounded font-semibold"><i class="fas fa-save mr-1"></i> Save draft</button>
                <button name="then" value="submit" class="px-4 py-2 bg-primary text-white rounded font-semibold"
                        onclick="return confirm('Submit complete results for publishing? You won\'t be able to change them unless the academic office returns them.')">
                    <i class="fas fa-paper-plane mr-1"></i> Save and submit
                </button>
            </div>
        @endif
    </form>
</div>

<script>
    const scale = @json(['wa' => $scale->weight_a, 'wb' => $scale->weight_b, 'bands' => collect($scale->bands)->sortByDesc('min')->values()]);
    document.querySelectorAll('.mark-row').forEach(row => {
        const a = row.querySelector('.score-a'), b = row.querySelector('.score-b');
        const update = () => {
            if (a.value === '' || b.value === '') return;
            const total = Math.round((a.value * scale.wa / 100 + b.value * scale.wb / 100) * 100) / 100;
            const band = scale.bands.find(x => total >= x.min);
            row.querySelector('.total-cell').textContent = total;
            const g = row.querySelector('.grade-cell');
            g.textContent = band ? band.label : '—';
            g.className = 'py-2 px-2 grade-cell ' + (band && !band.passed ? 'text-red-600 font-semibold' : '');
        };
        a.addEventListener('input', update);
        b.addEventListener('input', update);
    });
</script>
@endsection
