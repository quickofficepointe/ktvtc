@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'My Students')

@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">My Students</h1>
        <p class="text-sm text-gray-500">
            Students registered for a term in which you teach a unit, with how much of that term's fee is paid{{ $show === 'current' ? ': running now or ended in the last 30 days' : ', all intakes' }}.
        </p>
    </div>
    <div class="flex gap-2 text-sm">
        <a href="{{ request()->fullUrlWithQuery(['show' => null, 'page' => null]) }}" class="px-3 py-2 rounded-md border {{ $show === 'current' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700' }}">Current</a>
        <a href="{{ request()->fullUrlWithQuery(['show' => 'all', 'page' => null]) }}" class="px-3 py-2 rounded-md border {{ $show === 'all' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700' }}">All intakes</a>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @if($show === 'all')<input type="hidden" name="show" value="all">@endif
        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Course</label>
            <select name="course_id" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All my courses</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" @selected(request('course_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Search</label>
            <input name="search" value="{{ request('search') }}" placeholder="Name or student no." class="w-full border-gray-300 rounded-md text-sm">
        </div>
        <div class="flex items-end gap-2">
            <button class="bg-primary hover:bg-secondary text-white text-sm font-medium px-4 py-2 rounded-md"><i class="fas fa-filter mr-1"></i> Filter</button>
            <a href="{{ route('trainers.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="text-left px-4 py-3">Student</th>
                    <th class="text-left px-4 py-3">Course</th>
                    <th class="text-left px-4 py-3">Term</th>
                    <th class="text-left px-4 py-3">Your units</th>
                    <th class="text-left px-4 py-3">Phone</th>
                    <th class="text-right px-4 py-3">Term fee paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($registrations as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $r->student->full_name ?? trim(($r->student->first_name ?? '') . ' ' . ($r->student->last_name ?? '')) }}</div>
                            <div class="font-mono text-xs text-gray-500">{{ $r->student->student_number ?? $r->enrollment->student_number ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $r->course->name ?? $r->enrollment->course_name ?? '' }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            Term {{ $r->term_number }}
                            <div class="text-xs text-gray-500">{{ $r->starts_on?->format('j M') }} – {{ $r->ends_on?->format('j M Y') }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            @foreach ($unitsByTerm[$r->course_id . ':' . $r->term_number] ?? [] as $u)
                                <a href="{{ route('trainers.results.unit', $u) }}" class="block text-primary hover:underline">{{ trim($u->code . ' ' . $u->title) }}</a>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $r->student->phone ?? '' }}</td>
                        <td class="px-4 py-3">
                            @php $pct = $termPaid[$r->id] ?? 0; @endphp
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">{{ round($pct) }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            No students yet. Students appear here once they are registered for a term in which you teach a unit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($registrations->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50">{{ $registrations->links() }}</div>
    @endif
</div>

@endsection
