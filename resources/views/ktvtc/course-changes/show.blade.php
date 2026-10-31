@extends($layout)

@section('title', 'Course Change #' . $change->id)
@section('subtitle', ($change->student->full_name ?? '') . ' · ' . $from->course_name . ' → ' . $to->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-6 lg:col-span-1">
        <div class="bg-white rounded-xl border p-5 text-sm space-y-2">
            <h3 class="font-semibold text-gray-800">Request</h3>
            <p><span class="text-gray-500">Student:</span> {{ $change->student->full_name }} <span class="font-mono text-xs">{{ $change->student->student_number }}</span></p>
            <p><span class="text-gray-500">Phone:</span> {{ $change->student->phone ?? '—' }}</p>
            <p><span class="text-gray-500">From:</span> {{ $from->course_name }} <span class="font-mono text-xs">{{ $from->student_number }}</span></p>
            <p><span class="text-gray-500">To:</span> {{ $to->name }}</p>
            <p><span class="text-gray-500">Sent:</span> {{ $change->created_at->format('d M Y H:i') }}</p>
            <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Student's reason</p>{{ $change->reason }}</div>
            <p class="font-semibold pt-2">{{ $change->status_label }}</p>
            @include('ktvtc.course-changes.partials.steps')
        </div>

        <div class="bg-white rounded-xl border p-5 text-sm space-y-3">
            <h3 class="font-semibold text-gray-800">Decisions so far</h3>
            @if($change->academics_at)
                <div><p class="font-medium">Academics · {{ $change->academicsUser->name ?? '' }} · {{ $change->academics_at->format('d M Y') }}</p>
                    @if($change->start_term)<p>Starts in Term {{ $change->start_term }} of {{ $to->term_count ?? '?' }}</p>@endif
                    @if($change->academics_note)<p class="text-gray-600">{{ $change->academics_note }}</p>@endif</div>
            @endif
            @if($change->finance_at)
                <div><p class="font-medium">Finance · {{ $change->financeUser->name ?? '' }} · {{ $change->finance_at->format('d M Y') }}</p>
                    @if($change->amount_moved !== null)<p>KES {{ number_format($change->amount_moved, 2) }} moves to the new course; old balance {{ $change->clear_old_balance ? 'cleared' : 'kept' }}</p>@endif
                    @if($change->finance_note)<p class="text-gray-600">{{ $change->finance_note }}</p>@endif</div>
            @endif
            @if($change->admin_at)
                <div><p class="font-medium">Admissions · {{ $change->adminUser->name ?? '' }} · {{ $change->admin_at->format('d M Y') }}</p>
                    @if($change->admin_note)<p class="text-gray-600">{{ $change->admin_note }}</p>@endif</div>
            @endif
            @if($change->status === 'rejected')
                <p class="text-red-700">Not approved by {{ \App\Models\CourseChangeRequest::STAGE_NAMES[$change->rejected_at_stage] ?? '' }}: {{ $change->rejection_reason }}</p>
            @endif
            @if($change->newEnrollment)
                <p class="text-green-700">New registration number: <span class="font-mono font-semibold">{{ $change->newEnrollment->student_number }}</span></p>
            @endif
            @unless($change->academics_at || $change->finance_at || $change->admin_at || $change->status === 'rejected')
                <p class="text-gray-500">None yet.</p>
            @endunless
        </div>
    </div>

    <div class="space-y-6 lg:col-span-2">
        {{-- Money position on the old course, for finance and admissions --}}
        <div class="bg-white rounded-xl border p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Fees on the current course</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><p class="text-xs text-gray-500">Billed</p><p class="font-bold">KES {{ number_format($from->total_fees, 2) }}</p></div>
                <div><p class="text-xs text-gray-500">Paid</p><p class="font-bold text-green-700">KES {{ number_format($from->amount_paid, 2) }}</p></div>
                <div><p class="text-xs text-gray-500">Balance</p><p class="font-bold {{ $from->balance > 0 ? 'text-red-600' : 'text-green-700' }}">KES {{ number_format($from->balance, 2) }}</p></div>
            </div>
            @if(in_array((int) auth()->user()->role, [2, 7], true))
                <a href="{{ route('enrollment-terms.show', $from) }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block">See the terms and payments</a>
            @endif
        </div>

        @if($canAct)
            <form method="POST" action="{{ route('course-changes.approve', $change) }}" class="bg-white rounded-xl border-2 border-green-200 p-5 space-y-3 text-sm"
                  onsubmit="return confirm('Approve this step?')">
                @csrf
                @if($change->status === 'academics')
                    <h3 class="font-semibold text-gray-800">Academics: approve and choose the starting term</h3>
                    @if($to->hasTermSetup())
                        <label class="block">Starts in
                            <select name="start_term" class="no-select2 border rounded-lg px-3 py-2 ml-2" required>
                                @for($t = 1; $t <= $to->term_count; $t++)
                                    <option value="{{ $t }}">Term {{ $t }}</option>
                                @endfor
                            </select>
                            <span class="text-xs text-gray-500 ml-2">Earlier terms are recorded as exempt (no fees, marks or evaluations).</span>
                        </label>
                    @else
                        <input type="hidden" name="start_term" value="1">
                        <p class="text-orange-700">{{ $to->name }} has not been split into terms yet; the student would start at the beginning. Finance must set up its terms before admissions can approve.</p>
                    @endif
                @elseif($change->status === 'finance')
                    <h3 class="font-semibold text-gray-800">Finance: how much of what was paid moves to {{ $to->name }}?</h3>
                    <label class="block">Amount moved (KES)
                        <input type="number" name="amount_moved" min="0" max="{{ (float) $from->amount_paid }}" step="0.01" required value="{{ old('amount_moved', (float) $from->amount_paid) }}"
                               class="border rounded-lg px-3 py-2 ml-2 w-40">
                        <span class="text-xs text-gray-500 ml-2">up to KES {{ number_format($from->amount_paid, 2) }} paid on the old course</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="clear_old_balance" value="1" checked>
                        Clear anything still owed on {{ $from->course_name }} (it is closed after the change)
                    </label>
                    @if($startFee !== null)
                        <p class="text-gray-600">Term {{ $change->start_term }} of {{ $to->name }} will be billed KES {{ number_format($startFee, 2) }}.</p>
                    @elseif($to->hasTermSetup())
                        <p class="text-orange-700">No fee is set for Term {{ $change->start_term }} of {{ $to->name }}. Set it under Term Fees before admissions approve.</p>
                    @else
                        <p class="text-orange-700">{{ $to->name }} has not been split into terms. Set it up under Term Fees before admissions approve.</p>
                    @endif
                @else
                    <h3 class="font-semibold text-gray-800">Admissions: final approval</h3>
                    <p class="text-gray-600">Approving closes {{ $from->course_name }} ({{ $from->student_number }}) and enrolls {{ $change->student->full_name }} on {{ $to->name }}
                        from Term {{ $change->start_term ?? 1 }} with a new registration number{{ $from->student_number === $change->student->student_number ? ', which also becomes their login' : '' }}.
                        KES {{ number_format($change->amount_moved ?? 0, 2) }} moves to the new course. The student is texted the new number.</p>
                @endif
                <textarea name="note" rows="2" maxlength="1000" placeholder="Note (optional)" class="w-full border rounded-lg px-3 py-2">{{ old('note') }}</textarea>
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                    {{ $change->status === 'admin' ? 'Approve and enroll' : 'Approve and pass on' }}
                </button>
            </form>

            <form method="POST" action="{{ route('course-changes.reject', $change) }}" class="bg-white rounded-xl border p-5 space-y-2 text-sm"
                  onsubmit="return confirm('Turn this request down? The student will be texted the reason.')">
                @csrf
                <h3 class="font-semibold text-gray-800">Turn it down</h3>
                <input type="text" name="reason" required minlength="5" maxlength="500" placeholder="Reason (sent to the student)" class="w-full border rounded-lg px-3 py-2">
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Not approved</button>
            </form>
        @elseif($change->isPending())
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-900">
                {{ $change->status_label }}. Only {{ \App\Models\CourseChangeRequest::STAGE_NAMES[$change->status] }} can act on it now.
            </div>
        @endif
    </div>
</div>
@endsection
