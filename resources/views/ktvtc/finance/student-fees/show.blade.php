@extends('ktvtc.finance.layouts.app')

@section('title', 'Payment Details')
@section('subtitle', 'View payment details and information')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.student-fees.index') }}" class="text-gray-600 hover:text-primary">Student Fees</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Payment Details</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('finance.student-fees.receipt', $payment) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center shadow-md hover:shadow-lg transition-all duration-200">
        <i class="fas fa-print mr-2"></i> Print Receipt
    </a>
    @if(!$payment->is_verified && $payment->status === 'completed')
        <form method="POST" action="{{ route('finance.student-fees.verify', $payment) }}" class="inline">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-check-circle mr-2"></i> Verify
            </button>
        </form>
    @endif
    @if($payment->status === 'completed')
        <button onclick="openReverseModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center shadow-md hover:shadow-lg transition-all duration-200">
            <i class="fas fa-undo mr-2"></i> Reverse
        </button>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <i class="fas fa-receipt text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Payment #{{ $payment->receipt_number }}</h3>
                    <p class="text-sm text-gray-500">Recorded on {{ $payment->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
            <div>
                @if($payment->is_verified && $payment->status === 'completed')
                    <span class="status-badge status-verified text-base py-2 px-4"><i class="fas fa-check-circle mr-2"></i> Verified</span>
                @elseif($payment->status === 'completed' && !$payment->is_verified)
                    <span class="status-badge status-pending text-base py-2 px-4"><i class="fas fa-clock mr-2"></i> Pending Verification</span>
                @elseif($payment->status === 'reversed')
                    <span class="status-badge status-failed text-base py-2 px-4"><i class="fas fa-times-circle mr-2"></i> Reversed</span>
                @else
                    <span class="status-badge status-pending text-base py-2 px-4">{{ ucfirst($payment->status) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Information -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                <i class="fas fa-info-circle text-primary mr-2"></i>
                Payment Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt Number</p>
                    <p class="font-semibold text-primary text-lg">{{ $payment->receipt_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($payment->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</p>
                    <p><span class="text-xs uppercase px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 font-medium">{{ $payment->payment_method }}</span></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</p>
                    <p class="font-medium text-gray-800">{{ $payment->payment_date->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Code</p>
                    <p class="font-medium text-gray-800">{{ $payment->transaction_code ?? 'N/A' }}</p>
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
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</p>
                    <p class="text-gray-700 mt-1">{{ $payment->notes }}</p>
                </div>
            @endif
            @if($payment->status === 'reversed' && $payment->reversal_reason)
                <div class="mt-4 pt-4 border-t border-red-200 bg-red-50 p-4 rounded-lg">
                    <p class="text-xs font-medium text-red-600 uppercase tracking-wider">Reversal Reason</p>
                    <p class="text-red-700 mt-1">{{ $payment->reversal_reason }}</p>
                    <p class="text-xs text-red-500 mt-2">Reversed by: {{ $payment->reversedBy->name ?? 'N/A' }} on {{ $payment->reversed_at ? $payment->reversed_at->format('d M Y, H:i') : 'N/A' }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Student Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
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
                        <p class="font-medium text-gray-800">{{ $payment->student->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $payment->student->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('finance.students.financial', $payment->student) }}" class="text-primary hover:text-primary-dark hover:underline text-sm font-medium flex items-center">
                        <i class="fas fa-eye mr-1"></i> View Financial Details
                    </a>
                </div>
            </div>

            <!-- Enrollment Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-book-open text-primary mr-2"></i>
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
                        <p class="font-medium text-gray-800">KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Amount Paid</p>
                        <p class="font-medium text-green-600">KES {{ number_format($payment->enrollment->amount_paid ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Balance</p>
                        <p class="font-medium text-yellow-600">KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="status-badge status-{{ $payment->enrollment->status }}">{{ ucfirst($payment->enrollment->status ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Verification Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
                <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                    <i class="fas fa-shield-alt text-primary mr-2"></i>
                    Verification Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Verified</p>
                        @if($payment->is_verified)
                            <p class="font-medium text-green-600"><i class="fas fa-check-circle mr-1"></i> Yes</p>
                        @else
                            <p class="font-medium text-orange-600"><i class="fas fa-clock mr-1"></i> Pending</p>
                        @endif
                    </div>
                    @if($payment->is_verified)
                        <div>
                            <p class="text-xs text-gray-500">Verified By</p>
                            <p class="font-medium text-gray-800">{{ $payment->verifier->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Verified At</p>
                            <p class="font-medium text-gray-800">{{ $payment->verified_at ? $payment->verified_at->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Recorded By</p>
                        <p class="font-medium text-gray-800">{{ $payment->recorder->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Recorded At</p>
                        <p class="font-medium text-gray-800">{{ $payment->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('finance.student-fees.index') }}" class="text-primary hover:text-primary-dark hover:underline font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Payments
        </a>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl animate-slide-in">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Payment</h3>
            <button onclick="closeReverseModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('finance.student-fees.reverse', $payment) }}">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Reason for Reversal <span class="text-red-500">*</span></label>
                <textarea name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" rows="3" placeholder="Enter reason for reversal..." required></textarea>
                <p class="text-xs text-gray-500 mt-1">This action will reverse the payment and update the student's balance.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReverseModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold flex items-center gap-2">
                    <i class="fas fa-undo"></i> Reverse Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openReverseModal() {
        document.getElementById('reverseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReverseModal() {
        document.getElementById('reverseModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReverseModal();
        }
    });

    // Close on overlay click
    document.querySelector('#reverseModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeReverseModal();
        }
    });
</script>
@endpush
