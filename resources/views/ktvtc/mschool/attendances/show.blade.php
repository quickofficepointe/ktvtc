@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="container mx-auto px-4 py-8 space-y-6">
    @foreach(['success' => 'bg-green-50 border-green-500 text-green-800', 'error' => 'bg-red-50 border-red-500 text-red-800'] as $key => $classes)
        @if(session($key))<div class="border-l-4 rounded-lg px-4 py-3 text-sm {{ $classes }}">{{ session($key) }}</div>@endif
    @endforeach
    @if($errors->any())<div class="border-l-4 rounded-lg px-4 py-3 text-sm bg-red-50 border-red-500 text-red-800">{{ $errors->first() }}</div>@endif

    <div class="bg-white rounded-xl shadow-sm border p-6 flex flex-wrap justify-between gap-4">
        <div>
            <a href="{{ route('mschool.attendances.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Attendance</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $attendance->session_name ?: 'Class session' }}</h1>
            <p class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l d M Y') }}
                · {{ substr((string) $attendance->start_time, 0, 5) }}–{{ substr((string) $attendance->end_time, 0, 5) }}
                · {{ $attendance->course->course_name ?? 'No course' }}
                @if($attendance->subject) · {{ $attendance->subject->subject_name }} @endif
                @if($attendance->mobileSchool) · {{ $attendance->mobileSchool->name }} @endif
                @if($attendance->venue) · {{ $attendance->venue }} @endif
            </p>
            @if($attendance->topic_covered)<p class="text-sm text-gray-700 mt-2">Topic: {{ $attendance->topic_covered }}</p>@endif
        </div>
        <div class="flex gap-5 text-center items-start">
            @foreach(['total_present' => ['Present', 'text-green-600'], 'total_late' => ['Late', 'text-yellow-600'], 'total_absent' => ['Absent', 'text-red-600'], 'total_leave' => ['Leave', 'text-gray-600']] as $field => [$label, $color])
                <div><p class="text-2xl font-bold {{ $color }}">{{ (int) $attendance->{$field} }}</p><p class="text-xs text-gray-500">{{ $label }}</p></div>
            @endforeach
            <div><p class="text-2xl font-bold">{{ $roster->count() }}</p><p class="text-xs text-gray-500">Expected</p></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="font-semibold text-gray-800">Register</h2>
            @if($attendance->is_locked)
                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700">Locked: unlock it from the attendance list to change marks</span>
            @elseif($roster->isNotEmpty())
                <button type="button" onclick="document.querySelectorAll('.status-select').forEach(s => s.value = 'present')" class="text-xs px-3 py-1.5 border rounded hover:bg-gray-50">Mark everyone present</button>
            @endif
        </div>

        @if($roster->isEmpty())
            <p class="text-sm text-gray-500">No students are expected: set a course (and mobile school) on this session, and make sure students are actively enrolled in it.</p>
        @else
            <form method="POST" action="{{ route('mschool.attendances.mark', $attendance->attendance_id) }}">
                @csrf
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Student</th><th class="py-2">Status</th><th class="py-2">Reason / note</th><th class="py-2">Marked</th></tr></thead>
                    <tbody>
                        @foreach($roster as $student)
                            @php $record = $records[$student->student_id] ?? null; @endphp
                            <tr class="border-b">
                                <td class="py-2">{{ $student->first_name }} {{ $student->last_name }} <span class="text-xs text-gray-400">{{ $student->student_code }}</span></td>
                                <td class="py-2">
                                    <select name="status[{{ $student->student_id }}]" class="status-select border rounded px-2 py-1" @disabled($attendance->is_locked)>
                                        @foreach(['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'leave' => 'On leave', 'excused' => 'Excused'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($record->status ?? 'present') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2"><input type="text" name="reason[{{ $student->student_id }}]" value="{{ $record->absence_reason ?? $record->late_reason ?? '' }}" class="border rounded px-2 py-1 w-full" @disabled($attendance->is_locked)></td>
                                <td class="py-2 text-xs text-gray-400">{{ $record ? $record->updated_at?->format('d M H:i') : 'not yet' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @unless($attendance->is_locked)
                    <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">Save register</button>
                @endunless
            </form>
        @endif
    </div>
</div>
@endsection
