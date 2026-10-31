@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Admit Applicant')
@section('subtitle', $application->first_name . ' ' . $application->last_name . ' · ' . $application->application_number)

@section('header-actions')
<a href="{{ route('admin.applications.show', $application->id) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Application</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.applications.admit', $application) }}" class="space-y-6">
    @csrf

    @if($application->status !== 'accepted')
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">
            This application is "{{ str_replace('_', ' ', $application->status) }}". Mark it Accepted before admitting.
        </div>
    @endif

    @php $feePaid = $application->payments()->where('status', 'completed')->first(); @endphp
    @if($feePaid)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-900">
            <i class="fas fa-check-circle mr-1"></i> Application fee paid: KES {{ number_format($feePaid->amount, 2) }}{{ $feePaid->mpesa_receipt_number ? ', M-Pesa ' . $feePaid->mpesa_receipt_number : '' }}.
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">
            <i class="fas fa-exclamation-triangle mr-1"></i> No application fee payment is recorded for this applicant. Check it was paid (e.g. in cash) before admitting.
        </div>
    @endif

    <div class="bg-white rounded-xl border p-5 space-y-3">
        <h3 class="font-semibold text-gray-800">1. Who is this?</h3>
        <p class="text-sm text-gray-500">
            {{ $application->first_name }} {{ $application->last_name }} · {{ $application->phone }} · {{ $application->email ?? 'no email' }}
            · ID {{ $application->id_number ?? '—' }}
        </p>
        @foreach($matches as $m)
            <label class="flex items-start gap-2 border rounded-lg p-3 text-sm cursor-pointer hover:bg-gray-50">
                <input type="radio" name="student_choice" value="{{ $m['student']->id }}" @checked(old('student_choice') == $m['student']->id || ($loop->first && !old('student_choice'))) class="mt-1">
                <span>
                    <strong>Existing student:</strong> {{ $m['student']->full_name }} ({{ $m['student']->student_number }})
                    <span class="text-xs text-orange-700 block">{{ $m['why'] }}. Choose this if it's the same person; they'll get a new number for this course.</span>
                </span>
            </label>
        @endforeach
        <label class="flex items-start gap-2 border rounded-lg p-3 text-sm cursor-pointer hover:bg-gray-50">
            <input type="radio" name="student_choice" value="new" @checked(old('student_choice') === 'new' || ($matches->isEmpty() && !old('student_choice'))) class="mt-1">
            <span><strong>New student</strong>: create a student record from the application.</span>
        </label>
    </div>

    <div class="bg-white rounded-xl border p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <h3 class="font-semibold text-gray-800 md:col-span-2">2. Enrollment</h3>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Course</label>
            <select name="course_id" required class="w-full border rounded-lg px-3 py-2">
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" @selected(old('course_id', $application->course_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Campus</label>
            <select name="campus_id" class="w-full border rounded-lg px-3 py-2">
                @foreach($campuses as $c)
                    <option value="{{ $c->id }}" @selected(old('campus_id', $application->campus_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Intake / start date (applied for: {{ $application->intake_period }})</label>
            <input type="date" name="enrollment_date" required value="{{ old('enrollment_date', $intake->toDateString()) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Study mode</label>
            <select name="study_mode" class="w-full border rounded-lg px-3 py-2">
                @foreach(['full_time' => 'Full time', 'part_time' => 'Part time', 'evening' => 'Evening', 'weekend' => 'Weekend', 'online' => 'Online'] as $k => $label)
                    <option value="{{ $k }}" @selected(old('study_mode', $application->study_mode) === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sponsorship</label>
            <select name="sponsorship_type" class="w-full border rounded-lg px-3 py-2">
                @foreach(['self' => 'Self', 'sponsored' => 'Sponsored', 'government' => 'Government', 'scholarship' => 'Scholarship', 'company' => 'Company'] as $k => $label)
                    <option value="{{ $k }}" @selected(old('sponsorship_type', 'self') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Whole-course fee (KES)</label>
            <input type="number" name="total_fees" min="0" step="0.01" required value="{{ old('total_fees', 0) }}" class="w-full border rounded-lg px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Leave at 0 to bill per term from Finance's term fees (register Term 1 after admitting).</p>
        </div>
    </div>

    <button class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold" @disabled($application->status !== 'accepted')>
        <i class="fas fa-user-check mr-1"></i> Admit
    </button>
</form>
@endsection
