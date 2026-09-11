@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Enrollment')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- ═══════════ IDENTITY ═══════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center overflow-hidden">
                @if($student->passport_photo_path)
                    <img src="{{ Storage::disk('public')->url($student->passport_photo_path) }}"
                         class="w-full h-full object-cover" alt="">
                @else
                    <i class="fas fa-user-graduate text-primary text-2xl"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 mt-1">
                    <span><i class="fas fa-id-card mr-1"></i>{{ $student->student_number ?? '—' }}</span>
                    @if($currentEnrollment)
                        <span><i class="fas fa-book mr-1"></i>{{ $currentEnrollment->course_name }}</span>
                        @if($currentEnrollment->course_code)
                            <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">
                                {{ $currentEnrollment->course_code }}
                            </span>
                        @endif
                    @endif
                    @if($student->campus)
                        <span><i class="fas fa-building mr-1"></i>{{ $student->campus->name }}</span>
                    @endif
                </div>
            </div>
            <span class="text-xs px-3 py-1 rounded-full font-semibold
                {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($student->status) }}
            </span>
        </div>
    </div>

    {{-- ═══════════ CURRENT TERM + FEES ═══════════ --}}
    @if($currentTerm && $currentEnrollment)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                Current Term — {{ $currentTerm->name }}
            </h3>
            @if($currentTerm->is_current)
                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">
                    <i class="fas fa-star mr-1"></i>Current
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div>
                <p class="text-xs text-gray-500">Start Date</p>
                <p class="text-sm font-medium">{{ $currentTerm->start_date?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">End Date</p>
                <p class="text-sm font-medium">{{ $currentTerm->end_date?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Fee Due Date</p>
                <p class="text-sm font-medium">{{ $currentTerm->fee_due_date?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Days Remaining</p>
                @php
                    $daysLeft = $currentTerm->fee_due_date
                        ? (int) now()->diffInDays($currentTerm->fee_due_date, false)
                        : null;
                @endphp
                @if($daysLeft !== null)
                    <p class="text-sm font-medium
                        {{ $daysLeft <= 0 ? 'text-red-600' : ($daysLeft <= 14 ? 'text-orange-600' : 'text-green-600') }}">
                        {{ $daysLeft <= 0 ? 'Overdue' : $daysLeft . ' days' }}
                    </p>
                @else
                    <p class="text-sm text-gray-400">—</p>
                @endif
            </div>
        </div>

        {{-- ═══════════ EXAM DATES ═══════════ --}}
        @if($currentTerm->exam_start_date
            || $currentTerm->exam_end_date
            || $currentTerm->exam_registration_start_date
            || $currentTerm->exam_registration_end_date)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500">Exam Reg. Opens</p>
                    <p class="text-sm font-medium">
                        {{ $currentTerm->exam_registration_start_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Exam Reg. Closes</p>
                    <p class="text-sm font-medium
                        @if($currentTerm->exam_registration_end_date
                            && now()->diffInDays($currentTerm->exam_registration_end_date, false) <= 7
                            && now()->diffInDays($currentTerm->exam_registration_end_date, false) >= 0)
                            text-orange-600
                        @endif">
                        {{ $currentTerm->exam_registration_end_date?->format('d M Y') ?? '—' }}
                        @if($currentTerm->exam_registration_end_date
                            && now()->diffInDays($currentTerm->exam_registration_end_date, false) <= 7
                            && now()->diffInDays($currentTerm->exam_registration_end_date, false) >= 0)
                            <span class="text-xs font-normal">
                                ({{ (int) now()->diffInDays($currentTerm->exam_registration_end_date, false) }}d)
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Exams Begin</p>
                    <p class="text-sm font-medium">
                        {{ $currentTerm->exam_start_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Exams End</p>
                    <p class="text-sm font-medium">
                        {{ $currentTerm->exam_end_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Fee summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
                <p class="text-xs text-gray-500">Total Fees (this term)</p>
                <p class="text-xl font-bold text-gray-800 mt-1">
                    KES {{ number_format($currentEnrollment->total_fees, 2) }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Amount Paid</p>
                <p class="text-xl font-bold text-green-600 mt-1">
                    KES {{ number_format($currentEnrollment->amount_paid, 2) }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Balance</p>
                <p class="text-xl font-bold mt-1
                    {{ $currentEnrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    KES {{ number_format($currentEnrollment->balance, 2) }}
                </p>
            </div>
        </div>

        {{-- Progress with 50% threshold marker --}}
        <div class="mt-5">
            <div class="flex justify-between text-xs mb-1">
                <span class="font-medium text-gray-600">Payment Progress</span>
                <span class="font-bold">{{ $eligibility['percentage_paid'] }}%</span>
            </div>
            <div class="relative w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full
                    {{ $eligibility['percentage_paid'] >= 100 ? 'bg-green-500' :
                       ($eligibility['percentage_paid'] >= 50 ? 'bg-primary' : 'bg-red-500') }}"
                    style="width: {{ min(100, $eligibility['percentage_paid']) }}%"></div>
                <div class="absolute top-0 bottom-0 left-1/2 w-px bg-gray-700 opacity-60"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>0%</span>
                <span class="text-primary font-semibold">50% — enrollment threshold</span>
                <span>100%</span>
            </div>
        </div>

        {{-- ═══════════ EXAM CARD ═══════════ --}}
        @php
            $examCardUnlocked = (float) $currentEnrollment->balance <= 0;
        @endphp
        <div class="mt-6 pt-5 border-t border-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-id-badge text-primary mr-2"></i> Exam Card
                    </h4>
                    @if($examCardUnlocked)
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-check-circle text-green-600 mr-1"></i>
                            Term fees fully paid — your exam card is ready.
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pay the full term fee (<strong class="text-red-600">KES
                            {{ number_format($currentEnrollment->balance, 2) }}</strong> outstanding)
                            to unlock your exam card.
                        </p>
                    @endif
                </div>

                @if($examCardUnlocked)
                    <a href="{{ route('student.enrollment.exam-card', $currentEnrollment) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Download Exam Card
                    </a>
                @else
                    <button type="button" disabled
                            class="inline-flex items-center px-5 py-2.5 bg-gray-200 text-gray-500 font-semibold rounded-lg cursor-not-allowed">
                        <i class="fas fa-lock mr-2"></i>
                        Download Exam Card
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ NEXT TERM & ELIGIBILITY ═══════════ --}}
    @if($eligibility['eligible'])
        <div class="bg-gradient-to-r from-green-50 to-white border border-green-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-green-800">You can enroll for the next term</h3>
                    <p class="text-sm text-green-700 mt-1">{{ $eligibility['message'] }}</p>

                    @if($alreadyEnrolledNext)
                        <div class="mt-4 bg-white rounded-lg border border-green-200 p-4">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                You're already enrolled for {{ $nextTerm->name }}.
                            </p>
                        </div>
                    @elseif($nextTerm && $nextTermFee)
                        <div class="mt-4 bg-white rounded-lg border border-green-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Next Term</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $nextTerm->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $nextTerm->start_date?->format('d M') }}
                                        → {{ $nextTerm->end_date?->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Total Fee</p>
                                    <p class="text-xl font-bold text-primary">
                                        KES {{ number_format($nextTermFee->total_course_fee, 2) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Exam dates for next term --}}
                            @if($nextTerm->exam_start_date
                                || $nextTerm->exam_end_date
                                || $nextTerm->exam_registration_start_date
                                || $nextTerm->exam_registration_end_date)
                                <div class="mt-3 pt-3 border-t border-green-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <p class="text-gray-500">Exam Reg. Opens</p>
                                        <p class="font-medium text-gray-800">
                                            {{ $nextTerm->exam_registration_start_date?->format('d M Y') ?? '—' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Exam Reg. Closes</p>
                                        <p class="font-medium text-gray-800">
                                            {{ $nextTerm->exam_registration_end_date?->format('d M Y') ?? '—' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Exams Begin</p>
                                        <p class="font-medium text-gray-800">
                                            {{ $nextTerm->exam_start_date?->format('d M Y') ?? '—' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Exams End</p>
                                        <p class="font-medium text-gray-800">
                                            {{ $nextTerm->exam_end_date?->format('d M Y') ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($nextTerm->isInLateWindow() && $nextTerm->late_registration_fee > 0)
                                <p class="text-xs text-orange-600 mt-3">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Late registration window. A late fee of KES
                                    {{ number_format($nextTerm->late_registration_fee, 2) }} applies.
                                </p>
                            @endif

                            <form method="POST" action="{{ route('student.enrollment.store') }}"
                                  class="mt-4"
                                  onsubmit="return confirm('Confirm your enrollment for {{ $nextTerm->name }}?');">
                                @csrf
                                <button type="submit"
                                        class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition flex items-center justify-center">
                                    <i class="fas fa-arrow-right mr-2"></i>
                                    Enroll for {{ $nextTerm->name }}
                                </button>
                            </form>
                        </div>
                    @elseif(!$nextTerm)
                        <p class="text-sm text-green-700 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            You're eligible, but no term is currently open for enrollment.
                            Check back when registration opens.
                        </p>
                    @else
                        <p class="text-sm text-orange-700 mt-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Next term fees have not been set yet. Contact the finance office.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-gradient-to-r from-red-50 to-white border border-red-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lock text-red-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-red-800">Enrollment locked</h3>
                    <p class="text-sm text-red-700 mt-1">{{ $eligibility['message'] }}</p>

                    <div class="mt-4 bg-white rounded-lg border border-red-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Paid So Far</p>
                                <p class="text-lg font-bold text-gray-800">
                                    {{ $eligibility['percentage_paid'] }}%
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Required to Unlock</p>
                                <p class="text-lg font-bold text-primary">
                                    {{ $eligibility['threshold'] }}%
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Amount Short</p>
                                <p class="text-lg font-bold text-red-600">
                                    KES {{ number_format($eligibility['amount_required'], 2) }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('student.fees.index') }}"
                           class="mt-4 inline-block px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition">
                            <i class="fas fa-credit-card mr-2"></i> Pay Fees Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════ ENROLLMENT HISTORY ═══════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-history text-primary mr-2"></i> My Enrollment History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Term</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Course</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Total</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Paid</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Balance</th>
                        <th class="px-4 py-2 text-center font-medium text-gray-600">Status</th>
                        <th class="px-4 py-2 text-center font-medium text-gray-600">Exam Card</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($enrollments as $e)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                {{ $e->term->name ?? ($e->intake_month . ' ' . $e->intake_year) }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $e->course_name }}</p>
                                <p class="text-xs text-gray-500">{{ $e->course_code }}</p>
                            </td>
                            <td class="px-4 py-3 text-right">KES {{ number_format($e->total_fees, 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-600">KES {{ number_format($e->amount_paid, 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold
                                {{ $e->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($e->balance, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $badge = match($e->status) {
                                        'active'    => 'bg-green-100 text-green-700',
                                        'completed' => 'bg-blue-100 text-blue-700',
                                        'graduated' => 'bg-purple-100 text-purple-700',
                                        'suspended' => 'bg-yellow-100 text-yellow-700',
                                        'dropped'   => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full capitalize {{ $badge }}">
                                    {{ $e->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if((float) $e->balance <= 0)
                                    <a href="{{ route('student.enrollment.exam-card', $e) }}"
                                       class="inline-flex items-center text-xs font-medium text-green-700 hover:text-green-800">
                                        <i class="fas fa-file-pdf mr-1"></i> Download
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">
                                        <i class="fas fa-lock mr-1"></i> Locked
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                                No enrollments yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection