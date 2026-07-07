@extends('ktvtc.finance.layouts.app')

@section('title', 'Payment Details')
@section('subtitle', 'View payment details and information')

@section('breadcrumb')
    <li><span class="mx-2">/</span></li>
    <li>
        <a href="{{ route('finance.student-fees.index') }}" class="hover:text-primary transition whitespace-nowrap">
            Student Fees
        </a>
    </li>
    <li><span class="mx-2">/</span></li>
    <li class="text-primary font-medium whitespace-nowrap">Payment Details</li>
@endsection

@section('header-actions')
    <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
        <a href="{{ route('finance.student-fees.receipt', $payment) }}"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-semibold transition w-full sm:w-auto">
            <i class="fas fa-print"></i>
            Print Receipt
        </a>

        @if(!$payment->is_verified && $payment->status === 'completed')
            <form method="POST" action="{{ route('finance.student-fees.verify', $payment) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition w-full sm:w-auto">
                    <i class="fas fa-check-circle"></i>
                    Verify
                </button>
            </form>
        @endif

        @if($payment->status === 'completed')
            <button type="button"
                    onclick="openReverseModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition w-full sm:w-auto">
                <i class="fas fa-undo"></i>
                Reverse
            </button>
        @endif
    </div>
@endsection

@section('content')
@php
    $paymentStatus = strtolower($payment->status ?? '');

    $paymentBadge = match(true) {
        $payment->is_verified && $paymentStatus === 'completed' => 'status-verified',
        $paymentStatus === 'reversed' => 'status-reversed',
        $paymentStatus === 'failed' => 'status-failed',
        default => 'status-pending',
    };

    $paymentLabel = match(true) {
        $payment->is_verified && $paymentStatus === 'completed' => 'Verified',
        $paymentStatus === 'completed' && !$payment->is_verified => 'Pending Verification',
        default => ucfirst($paymentStatus ?: 'N/A'),
    };

    $enrollmentStatus = strtolower($payment->enrollment->status ?? '');

    $enrollmentBadge = match($enrollmentStatus) {
        'active' => 'status-active',
        'completed' => 'status-success',
        'inactive' => 'status-inactive',
        default => 'status-warning',
    };
@endphp

<div class="max-w-7xl mx-auto">
    <div class="finance-card p-4 sm:p-6 mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                    <i class="fas fa-receipt text-xl sm:text-2xl"></i>
                </div>

                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800 break-words">
                        Payment #{{ $payment->receipt_number ?? 'N/A' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        Recorded on {{ optional($payment->created_at)->format('d M Y, H:i') ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div>
                <span class="status-badge {{ $paymentBadge }} text-sm sm:text-base py-2 px-4">
                    @if($payment->is_verified && $paymentStatus === 'completed')
                        <i class="fas fa-check-circle mr-2"></i>
                    @elseif($paymentStatus === 'reversed')
                        <i class="fas fa-undo mr-2"></i>
                    @else
                        <i class="fas fa-clock mr-2"></i>
                    @endif
                    {{ $paymentLabel }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 finance-card p-4 sm:p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                <i class="fas fa-info-circle w-5 text-center text-primary mr-2"></i>
                Payment Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt Number</p>
                    <p class="font-semibold text-primary text-lg break-words">
                        {{ $payment->receipt_number ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</p>
                    <p class="text-2xl font-bold text-gray-800">
                        KES {{ number_format($payment->amount ?? 0, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</p>
                    <span class="inline-flex text-xs uppercase px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 font-medium mt-1">
                        {{ strtoupper($payment->payment_method ?? 'N/A') }}
                    </span>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</p>
                    <p class="font-medium text-gray-800">
                        {{ optional($payment->payment_date)->format('d M Y, H:i') ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Code</p>
                    <p class="font-medium text-gray-800 break-words">
                        {{ $payment->transaction_code ?? 'N/A' }}
                    </p>
                </div>

                @if($payment->payment_for_month)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment For Month</p>
                        <p class="font-medium text-gray-800">{{ $payment->payment_for_month }}</p>
                    </div>
                @endif

                @if($payment->payer_name)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payer Name</p>
                        <p class="font-medium text-gray-800">{{ $payment->payer_name }}</p>
                    </div>
                @endif

                @if($payment->payer_phone)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payer Phone</p>
                        <p class="font-medium text-gray-800">{{ $payment->payer_phone }}</p>
                    </div>
                @endif

                @if($payment->payer_type)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payer Type</p>
                        <p class="font-medium text-gray-800">{{ ucfirst($payment->payer_type) }}</p>
                    </div>
                @endif
            </div>

            @if($payment->notes)
                <div class="mt-5 pt-5 border-t border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</p>
                    <p class="text-gray-700 mt-1">{{ $payment->notes }}</p>
                </div>
            @endif

            @if($paymentStatus === 'reversed' && $payment->reversal_reason)
                <div class="mt-5 bg-red-50 border border-red-200 p-4 rounded-lg">
                    <p class="text-xs font-medium text-red-600 uppercase tracking-wider">Reversal Reason</p>
                    <p class="text-red-700 mt-1">{{ $payment->reversal_reason }}</p>
                    <p class="text-xs text-red-500 mt-2">
                        Reversed by: {{ $payment->reversedBy->name ?? 'N/A' }}
                        on {{ optional($payment->reversed_at)->format('d M Y, H:i') ?? 'N/A' }}
                    </p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="finance-card p-4 sm:p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-user-graduate w-5 text-center text-primary mr-2"></i>
                    Student Information
                </h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="font-medium text-gray-800">{{ $payment->student->full_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Student Number</p>
                        <p class="font-medium text-gray-800">{{ $payment->student->student_number ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800 break-words">{{ $payment->student->email ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $payment->student->phone ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($payment->student)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('finance.students.financial', $payment->student) }}"
                           class="text-primary hover:text-primary-dark hover:underline text-sm font-medium inline-flex items-center">
                            <i class="fas fa-eye mr-1"></i>
                            View Financial Details
                        </a>
                    </div>
                @endif
            </div>

            <div class="finance-card p-4 sm:p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-book-open w-5 text-center text-primary mr-2"></i>
                    Enrollment Information
                </h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Course</p>
                        <p class="font-medium text-gray-800">{{ $payment->enrollment->course->name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Course Code</p>
                        <p class="font-medium text-gray-800">{{ $payment->enrollment->course->code ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Total Fees</p>
                        <p class="font-medium text-gray-800">
                            KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Amount Paid</p>
                        <p class="font-medium text-green-600">
                            KES {{ number_format($payment->enrollment->amount_paid ?? 0, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Balance</p>
                        <p class="font-medium text-yellow-600">
                            KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="status-badge {{ $enrollmentBadge }}">
                            {{ ucfirst($enrollmentStatus ?: 'N/A') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="finance-card p-4 sm:p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-shield-alt w-5 text-center text-primary mr-2"></i>
                    Verification Information
                </h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Verified</p>
                        @if($payment->is_verified)
                            <p class="font-medium text-green-600">
                                <i class="fas fa-check-circle mr-1"></i>
                                Yes
                            </p>
                        @else
                            <p class="font-medium text-orange-600">
                                <i class="fas fa-clock mr-1"></i>
                                Pending
                            </p>
                        @endif
                    </div>

                    @if($payment->is_verified)
                        <div>
                            <p class="text-xs text-gray-500">Verified By</p>
                            <p class="font-medium text-gray-800">{{ $payment->verifier->name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Verified At</p>
                            <p class="font-medium text-gray-800">
                                {{ optional($payment->verified_at)->format('d M Y, H:i') ?? 'N/A' }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs text-gray-500">Recorded By</p>
                        <p class="font-medium text-gray-800">{{ $payment->recorder->name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Recorded At</p>
                        <p class="font-medium text-gray-800">
                            {{ optional($payment->created_at)->format('d M Y, H:i') ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('finance.student-fees.index') }}"
           class="text-primary hover:text-primary-dark hover:underline font-medium inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Payments
        </a>
    </div>
</div>

<div id="reverseModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-xl p-5 sm:p-6 w-full max-w-md mx-auto shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Payment</h3>

            <button type="button" onclick="closeReverseModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form method="POST"
              action="{{ route('finance.student-fees.reverse', $payment) }}"
              onsubmit="return confirm('Are you sure you want to reverse this payment? This action cannot be undone.')">
            @csrf

            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">
                    Reason for Reversal <span class="text-red-500">*</span>
                </label>

                <textarea name="reason"
                          rows="3"
                          required
                          placeholder="Enter reason for reversal..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>

                <p class="text-xs text-gray-500 mt-1">
                    This action will reverse the payment and update the student's balance.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button type="button"
                        onclick="closeReverseModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold inline-flex items-center justify-center gap-2">
                    <i class="fas fa-undo"></i>
                    Reverse Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openReverseModal() {
        const modal = document.getElementById('reverseModal');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeReverseModal() {
        const modal = document.getElementById('reverseModal');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeReverseModal();
        }
    });

    document.getElementById('reverseModal')?.addEventListener('click', function (e) {
        if (e.target === this) {
            closeReverseModal();
        }
    });
</script>
@endpush
