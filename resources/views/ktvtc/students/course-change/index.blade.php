@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Change Course')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800">Change course</h2>
        <p class="text-sm text-gray-500 mt-1">
            Your request goes to <strong>Academics</strong> (who decide which term you start in), then <strong>Finance</strong>
            (who decide how much of what you have paid moves to the new course), then <strong>Admissions</strong> for final approval.
            Once approved you get a new registration number for the new course by SMS.
        </p>
    </div>

    @php $pending = $requests->first(fn ($r) => $r->isPending()); @endphp

    @if($pending)
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg px-4 py-3 text-sm text-blue-900">
            You have a request waiting: {{ $pending->fromEnrollment->course_name }} → {{ $pending->toCourse->name }} ({{ $pending->status_label }}).
        </div>
    @elseif($enrollments->isEmpty())
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg px-4 py-3 text-sm text-yellow-800">You are not on a course at the moment.</div>
    @else
        <form method="POST" action="{{ route('student.course-change.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4"
              onsubmit="return confirm('Send this course change request?')">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1">From</label>
                    <select name="from_enrollment_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        @foreach($enrollments as $e)
                            <option value="{{ $e->id }}" @selected(old('from_enrollment_id') == $e->id)>{{ $e->course_name }} ({{ $e->student_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1">To</label>
                    <select name="to_course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Choose the new course</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @selected(old('to_course_id') == $c->id)>{{ $c->name }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1">Why do you want to change?</label>
                <textarea name="reason" rows="4" minlength="10" maxlength="1000" required class="w-full border border-gray-300 rounded-lg px-3 py-2"
                          placeholder="Explain your reason">{{ old('reason') }}</textarea>
            </div>
            <button class="bg-red-700 hover:bg-red-800 text-white px-5 py-2 rounded-lg font-semibold">Send request</button>
        </form>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-3">My requests</h3>
        @forelse($requests as $r)
            <div class="border-b last:border-0 py-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <p class="font-medium text-gray-800">{{ $r->fromEnrollment->course_name }} → {{ $r->toCourse->name }}</p>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                        {{ $r->status === 'approved' ? 'bg-green-100 text-green-700' : ($r->status === 'rejected' ? 'bg-red-100 text-red-700' : ($r->isPending() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $r->status_label }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">Sent {{ $r->created_at->format('d M Y') }}</p>
                @include('ktvtc.course-changes.partials.steps', ['change' => $r])
                @if($r->status === 'approved' && $r->newEnrollment)
                    <p class="mt-2 text-green-700">New registration number: <span class="font-mono font-semibold">{{ $r->newEnrollment->student_number }}</span>{{ $r->start_term > 1 ? ", starting in Term {$r->start_term}" : '' }}.</p>
                @elseif($r->status === 'rejected')
                    <p class="mt-2 text-red-700">Reason: {{ $r->rejection_reason }}</p>
                @endif
                @if($r->isPending())
                    <form method="POST" action="{{ route('student.course-change.cancel', $r) }}" class="mt-2" onsubmit="return confirm('Withdraw this request?')">
                        @csrf
                        <button class="text-xs text-red-600 hover:underline">Withdraw request</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">You have not asked to change course.</p>
        @endforelse
    </div>
</div>
@endsection
