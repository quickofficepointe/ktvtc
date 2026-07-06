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
<div class="flex space-x-3">
    <a href="{{ route('finance.student-fees.receipt', $payment) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print Receipt
    </a>
    @if(!$payment->is_verified && $payment->status === 'completed')
        <form method="POST" action="{{ route('finance.student-fees.verify', $payment) }}" class="inline">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
                <i class="fas fa-check-circle mr-2"></i> Verify
            </button>
        </form>
    @endif
    @if($payment->status === 'completed')
        <button onclick="openReverseModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
            <i class="fas fa-undo mr-2"></i> Reverse
        </button>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Payment Information -->
    <div class="lg:col-span-2 finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Payment Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Receipt Number</p>
                <p class="font-semibold text-primary">{{ $payment->receipt_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Amount</p>
                <p class="text-2xl font-bold text-gray-800">KES {{ number_format($payment->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Payment Method</p>
                <p><span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $payment->payment_method }}</span></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Payment Date</p>
                <p class="font-medium">{{ $payment->payment_date->format('d M Y, H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                @if($payment->is_verified && $payment->status === 'completed')
                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                @elseif($payment->status === 'completed' && !$payment->is_verified)
                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending Verification</span>
                @elseif($payment->status === 'reversed')
                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Reversed</span>
                @else
                    <span class="status-badge status-pending">{{ ucfirst($payment->status) }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-500">Transaction Code</p>
                <p class="font-medium">{{ $payment->transaction_code ?? 'N/A' }}</p>
            </div>
            @if($payment->payment_for_month)
            <div>
                <p class="text-xs text-gray-500">Payment For Month</p>
                <p class="font-medium">{{ $payment->payment_for_month }}</p>
            </div>
            @endif
            @if($payment->payer_name)
            <div>
                <p class="text-xs text-gray-500">Payer Name</p>
                <p class="font-medium">{{ $payment->payer_name }}</p>
            </div>
            @endif
            @if($payment->payer_phone)
            <div>
                <p class="text-xs text-gray-500">Payer Phone</p>
                <p class="font-medium">{{ $payment->payer_phone }}</p>
            </div>
            @endif
        </div>
        @if($payment->notes)
            <div class="mt-4 pt-4 border-t">
                <p class="text-xs text-gray-500">Notes</p>
                <p class="text-gray-700">{{ $payment->notes }}</p>
            </div>
        @endif
        @if($payment->status === 'reversed' && $payment->reversal_reason)
            <div class="mt-4 pt-4 border-t border-red-200 bg-red-50 p-3 rounded-lg">
                <p class="text-xs text-red-600 font-semibold">Reversal Reason</p>
                <p class="text-red-700">{{ $payment->reversal_reason }}</p>
                <p class="text-xs text-red-500 mt-1">Reversed by: {{ $payment->reversedBy->name ?? 'N/A' }} on {{ $payment->reversed_at ? $payment->reversed_at->format('d M Y, H:i') : 'N/A' }}</p>
            </div>
        @endif
    </div>

    <!-- Student & Enrollment Information -->
    <div class="space-y-6">
        <!-- Student Info -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Student Information</h3>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Name:</span> <span class="font-medium">{{ $payment->student->full_name ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Student Number:</span> <span class="font-medium">{{ $payment->student->student_number ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $payment->student->email ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Phone:</span> <span class="font-medium">{{ $payment->student->phone ?? 'N/A' }}</span></p>
            </div>
            <div class="mt-3 pt-3 border-t">
                <a href="{{ route('finance.students.financial', $payment->student) }}" class="text-primary hover:underline text-sm">
                    <i class="fas fa-eye mr-1"></i> View Financial Details
                </a>
            </div>
        </div>

        <!-- Enrollment Info -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Enrollment Information</h3>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Course:</span> <span class="font-medium">{{ $payment->enrollment->course->name ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Course Code:</span> <span class="font-medium">{{ $payment->enrollment->course->code ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Total Fees:</span> <span class="font-medium">KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Amount Paid:</span> <span class="font-medium text-green-600">KES {{ number_format($payment->enrollment->amount_paid ?? 0, 2) }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Balance:</span> <span class="font-medium text-yellow-600">KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Status:</span> <span class="status-badge status-{{ $payment->enrollment->status }}">{{ ucfirst($payment->enrollment->status ?? 'N/A') }}</span></p>
            </div>
        </div>

        <!-- Verification Info -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Verification Information</h3>
            <div class="space-y-2">
                <p class="text-sm"><span class="text-gray-500">Verified:</span>
                    @if($payment->is_verified)
                        <span class="text-green-600 font-medium"><i class="fas fa-check-circle mr-1"></i> Yes</span>
                    @else
                        <span class="text-orange-600 font-medium"><i class="fas fa-clock mr-1"></i> Pending</span>
                    @endif
                </p>
                @if($payment->is_verified)
                    <p class="text-sm"><span class="text-gray-500">Verified By:</span> <span class="font-medium">{{ $payment->verifier->name ?? 'N/A' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Verified At:</span> <span class="font-medium">{{ $payment->verified_at ? $payment->verified_at->format('d M Y, H:i') : 'N/A' }}</span></p>
                @endif
                <p class="text-sm"><span class="text-gray-500">Recorded By:</span> <span class="font-medium">{{ $payment->recorder->name ?? 'N/A' }}</span></p>
                <p class="text-sm"><span class="text-gray-500">Recorded At:</span> <span class="font-medium">{{ $payment->created_at->format('d M Y, H:i') }}</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Payment</h3>
            <button onclick="closeReverseModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('finance.student-fees.reverse', $payment) }}">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Reason for Reversal *</label>
                <textarea name="reason" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required></textarea>
                <p class="text-xs text-gray-500 mt-1">This action will reverse the payment and update the student's balance.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReverseModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                    <i class="fas fa-undo mr-2"></i> Reverse Payment
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
</script>
@endpush
