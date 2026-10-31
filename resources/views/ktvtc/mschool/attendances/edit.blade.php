@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    @if(session('error'))<div class="border-l-4 rounded-lg px-4 py-3 text-sm bg-red-50 border-red-500 text-red-800 mb-4">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="border-l-4 rounded-lg px-4 py-3 text-sm bg-red-50 border-red-500 text-red-800 mb-4">{{ $errors->first() }}</div>@endif

    <a href="{{ route('mschool.attendances.show', $attendance->attendance_id) }}" class="text-sm text-blue-600 hover:underline">&larr; Back to the register</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-1 mb-4">Edit session</h1>

    <form method="POST" action="{{ route('mschool.attendances.update', $attendance->attendance_id) }}" class="bg-white rounded-xl shadow-sm border p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        @csrf
        @method('PUT')
        <div class="md:col-span-2">
            <label class="block text-gray-700 mb-1">Session name</label>
            <input name="session_name" value="{{ old('session_name', $attendance->session_name) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Date *</label>
            <input type="date" name="attendance_date" required value="{{ old('attendance_date', \Carbon\Carbon::parse($attendance->attendance_date)->toDateString()) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-gray-700 mb-1">Starts *</label>
                <input type="time" name="start_time" required value="{{ old('start_time', substr((string) $attendance->start_time, 0, 5)) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Ends *</label>
                <input type="time" name="end_time" required value="{{ old('end_time', substr((string) $attendance->end_time, 0, 5)) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Course</label>
            <select name="course_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">—</option>
                @foreach($courses as $course)
                    <option value="{{ $course->course_id }}" @selected(old('course_id', $attendance->course_id) == $course->course_id)>{{ $course->course_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Subject</label>
            <select name="subject_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">—</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->subject_id }}" @selected(old('subject_id', $attendance->subject_id) == $subject->subject_id)>{{ $subject->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Mobile school</label>
            <select name="mobile_school_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">All schools on this course</option>
                @foreach($mobileSchools as $school)
                    <option value="{{ $school->id }}" @selected(old('mobile_school_id', $attendance->mobile_school_id) == $school->id)>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-1">How attendance is taken *</label>
            <select name="recording_method" class="w-full border rounded-lg px-3 py-2">
                @foreach(['manual' => 'Manual', 'qr_code' => 'QR code', 'biometric' => 'Biometric', 'mobile_app' => 'Mobile app'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('recording_method', $attendance->recording_method) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Venue</label>
            <input name="venue" value="{{ old('venue', $attendance->venue) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 mb-1">Room</label>
            <input name="room" value="{{ old('room', $attendance->room) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-700 mb-1">Topic covered</label>
            <textarea name="topic_covered" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('topic_covered', $attendance->topic_covered) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-700 mb-1">Remarks</label>
            <textarea name="remarks" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('remarks', $attendance->remarks) }}</textarea>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-5">
            <label class="flex items-center gap-2"><input type="hidden" name="allow_late_marking" value="0"><input type="checkbox" name="allow_late_marking" value="1" @checked(old('allow_late_marking', $attendance->allow_late_marking))> Allow late marking</label>
            <label class="flex items-center gap-2">after <input type="number" name="late_threshold_minutes" min="1" value="{{ old('late_threshold_minutes', $attendance->late_threshold_minutes) }}" class="w-20 border rounded px-2 py-1"> minutes</label>
            <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $attendance->is_active))> Active</label>
        </div>
        <div class="md:col-span-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Save changes</button>
        </div>
    </form>
</div>
@endsection
