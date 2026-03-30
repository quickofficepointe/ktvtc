@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Payment Details')
@section('subtitle', 'View payment transaction details')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Finance</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Payments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $payment->receipt_number }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.fee-payments.receipt', $payment) }}" target="_blank"
       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span>Print Receipt</span>
    </a>
    <a href="{{ route('admin.fee-payments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Payments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-primary/10 to-transparent px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-xl bg-primary-light flex items-center justify-center">
                    <i class="fas fa-receipt text-primary text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $payment->receipt_number }}</h2>
                        @php
                            $statusColors = [
                                'completed' => 'green',
                                'pending' => 'yellow',
                                'failed' => 'red',
                                'reversed' => 'orange',
                            ];
                            $color = $statusColors[$payment->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst($payment->status) }}
                        </span>
                        @if($payment->is_verified)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Verified
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-600 mt-1">Transaction ID: {{ $payment->transaction_code ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                @if($payment->status == 'completed' && !$payment->is_verified)
                    <button onclick="verifyPayment()"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        Verify Payment
                    </button>
                @endif
                @if($payment->status == 'completed')
                    <button onclick="reversePayment()"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm">
                        <i class="fas fa-undo-alt mr-2"></i>
                        Reverse Payment
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Payment Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Payment Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-credit-card text-primary mr-2"></i>
                    Payment Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Amount</p>
                        <p class="text-2xl font-bold text-green-600">KES {{ number_format($payment->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($payment->payment_method == 'cash') bg-yellow-100 text-yellow-800
                            @elseif($payment->payment_method == 'mpesa') bg-green-100 text-green-800
                            @elseif($payment->payment_method == 'bank') bg-blue-100 text-blue-800
                            @elseif($payment->payment_method == 'kcb') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ strtoupper($payment->payment_method) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Payment Date</p>
                        <p class="text-sm font-medium">{{ $payment->payment_date->format('l, F j, Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Receipt Number</p>
                        <p class="text-sm font-mono font-medium">{{ $payment->receipt_number }}</p>
                    </div>
                    @if($payment->transaction_code)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Transaction Code</p>
                        <p class="text-sm font-mono">{{ $payment->transaction_code }}</p>
                    </div>
                    @endif
                    @if($payment->payment_for_month)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Payment For</p>
                        <p class="text-sm">{{ $payment->payment_for_month }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Student Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Student Information
                </h3>
            </div>
            <div class="p-6">
                <div class="flex items-start">
                    <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-primary">
                            {{ substr($payment->student->full_name ?? 'S', 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800">
                            <a href="{{ route('admin.students.show', $payment->student_id) }}" class="hover:text-primary">
                                {{ $payment->student->full_name ?? 'N/A' }}
                            </a>
                        </h4>
                        <p class="text-sm text-gray-600">{{ $payment->student->student_number ?? 'No ID' }}</p>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm">{{ $payment->student->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="text-sm">{{ $payment->student->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Information -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-book-open text-primary mr-2"></i>
                    Enrollment Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Course</p>
                        <p class="text-sm font-medium">{{ $payment->enrollment->course_name ?? ($payment->enrollment->course->name ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Intake</p>
                        <p class="text-sm">{{ $payment->enrollment->intake_month ?? '' }} {{ $payment->enrollment->intake_year ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Total Fees</p>
                        <p class="text-sm">KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Balance After Payment</p>
                        <p class="text-sm font-bold {{ ($payment->enrollment->balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                            KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.enrollments.show', $payment->enrollment_id) }}"
                       class="text-primary hover:text-primary-dark text-sm">
                        View Enrollment Details <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($payment->notes)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Notes
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-700">{{ $payment->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Payer & Status Info -->
    <div class="space-y-6">
        <!-- Payer Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user text-primary mr-2"></i>
                    Payer Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Name</p>
                        <p class="text-sm font-medium">{{ $payment->payer_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Phone</p>
                        <p class="text-sm">{{ $payment->payer_phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Type</p>
                        <p class="text-sm">{{ ucfirst($payment->payer_type ?? 'student') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Timeline Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Status Timeline
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-plus text-green-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Payment Recorded</p>
                            <p class="text-xs text-gray-500">{{ $payment->created_at->format('M j, Y \a\t h:i A') }}</p>
                            @if($payment->recorded_by)
                                <p class="text-xs text-gray-500">by {{ $payment->recorder->name ?? 'System' }}</p>
                            @endif
                        </div>
                    </div>

                    @if($payment->verified_at)
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check-circle text-blue-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Payment Verified</p>
                            <p class="text-xs text-gray-500">{{ $payment->verified_at->format('M j, Y \a\t h:i A') }}</p>
                            @if($payment->verifier)
                                <p class="text-xs text-gray-500">by {{ $payment->verifier->name }}</p>
                            @endif
                            @if($payment->verification_notes)
                                <p class="text-xs text-gray-600 mt-1">Note: {{ $payment->verification_notes }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($payment->receipt_sent_at)
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-envelope text-purple-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Receipt Sent</p>
                            <p class="text-xs text-gray-500">{{ $payment->receipt_sent_at->format('M j, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($payment->status == 'reversed')
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-undo-alt text-red-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Payment Reversed</p>
                            <p class="text-xs text-gray-500">{{ $payment->reversed_at->format('M j, Y \a\t h:i A') }}</p>
                            @if($payment->reversal_reason)
                                <p class="text-xs text-gray-600 mt-1">Reason: {{ $payment->reversal_reason }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($payment->updated_at != $payment->created_at && !$payment->verified_at && $payment->status != 'reversed')
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-pen text-amber-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Last Updated</p>
                            <p class="text-xs text-gray-500">{{ $payment->updated_at->format('M j, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Import Info Card -->
        @if($payment->import_source)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-import text-primary mr-2"></i>
                    Import Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-500">Source</p>
                        <p class="text-sm">{{ ucfirst($payment->import_source) }}</p>
                    </div>
                    @if($payment->original_data)
                    <div>
                        <p class="text-xs text-gray-500">Original Data</p>
                        <pre class="text-xs bg-gray-50 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($payment->original_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('verifyModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Verify Payment</h3>
                    <button onclick="closeModal('verifyModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="verifyForm" method="POST" action="{{ route('admin.fee-payments.verify', $payment) }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                Verify payment: <strong>{{ $payment->receipt_number }}</strong> -
                                KES {{ number_format($payment->amount, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes (Optional)</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('verifyModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitVerify()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Verify Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('reverseModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Reverse Payment</h3>
                    <button onclick="closeModal('reverseModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="reverseForm" method="POST" action="{{ route('admin.fee-payments.reverse', $payment) }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-sm text-orange-700">
                                Reverse payment: <strong>{{ $payment->receipt_number }}</strong> -
                                KES {{ number_format($payment->amount, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Reason for Reversal</label>
                            <textarea name="reason" required rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('reverseModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitReverse()" class="px-4 py-2 bg-orange-600 text-white rounded-lg">Reverse Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function verifyPayment() {
        openModal('verifyModal');
    }

    function reversePayment() {
        openModal('reverseModal');
    }

    function submitVerify() {
        document.getElementById('verifyForm').submit();
    }

    function submitReverse() {
        document.getElementById('reverseForm').submit();
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
            closeModal('reverseModal');
        }
    });
</script>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .hidden {
        display: none !important;
    }
</style>
@endsection
