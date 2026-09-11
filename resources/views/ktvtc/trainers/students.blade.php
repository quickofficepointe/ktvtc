@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'My Students')

@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">My Students</h1>
        <p class="text-sm text-gray-500">
            Enrolled students in your courses and their fee status.
        </p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Term</label>
            <select name="term_id" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All terms</option>
                @foreach ($terms as $t)
                    <option value="{{ $t->id }}" @selected($t->id == $termId)>
                        {{ $t->name }} — {{ $t->academic_year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Course</label>
            <select name="course_id" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All my courses</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" @selected(request('course_id') == $c->id)>
                        {{ $c->name }} ({{ $c->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Search</label>
            <input name="search" value="{{ request('search') }}"
                   placeholder="Name or student no."
                   class="w-full border-gray-300 rounded-md text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button class="bg-primary hover:bg-secondary text-white text-sm font-medium px-4 py-2 rounded-md">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('trainers.students.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="text-left px-4 py-3">Student No.</th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Course</th>
                    <th class="text-left px-4 py-3">Term</th>
                    <th class="text-right px-4 py-3">Total Fees</th>
                    <th class="text-right px-4 py-3">Paid</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-right px-4 py-3">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($enrollments as $e)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                            {{ $e->student_number }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $e->student_name }}</div>
                            @if ($e->student)
                                <div class="text-xs text-gray-500">{{ $e->student->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $e->course->name ?? $e->course_name }}
                            <div class="text-xs text-gray-500">{{ $e->course_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $e->term->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            {{ number_format($e->total_fees, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-green-700">
                            {{ number_format($e->amount_paid, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold
                            {{ $e->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($e->balance, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    @php $pct = $e->percentage_paid; @endphp
                                    <div class="h-2 rounded-full
                                        @if ($pct >= 100) bg-green-500
                                        @elseif ($pct >= 50) bg-amber-500
                                        @else bg-red-500 @endif"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            No students found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($enrollments->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>

@endsection