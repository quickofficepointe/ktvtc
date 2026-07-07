@extends('ktvtc.finance.layouts.app')

@section('title', 'Funding Request Details')
@section('subtitle', 'View funding request details')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.funding.index') }}" class="text-gray-600 hover:text-primary">Funding</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Request #{{ $cardFundingRequest->id }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    @if($cardFundingRequest->status === 'pending' || $cardFundingRequest->status === 'processing')
        <button onclick="retryFunding()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-sync mr-2"></i> Retry
        </button>
        <button onclick="markFailed()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-times mr-2"></i> Mark Failed
        </button>
        <button onclick="markCompleted()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-check mr-2"></i> Mark Completed
        </button>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <!-- Request Details -->
    <div class="finance-card p-5">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Request Details</h3>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-500">Request ID</p>
                <p class="font-mono font-semibold">#{{ $cardFundingRequest->id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Amount</p>
                <p class="text-2xl font-bold text-primary">KES {{ number_format($cardFundingRequest->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                @if($cardFundingRequest->status === 'completed')
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded">Completed</span>
                @elseif($cardFundingRequest->status === 'pending' || $cardFundingRequest->status === 'processing')
                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded">{{ ucfirst($cardFundingRequest->status) }}</span>
                @elseif($cardFundingRequest->status === 'failed')
                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">Failed</span>
                @else
                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">{{ ucfirst($cardFundingRequest->status) }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-500">Created At</p>
                <p class="text-sm">{{ $cardFundingRequest->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            @if($cardFundingRequest->completed_at)
                <div>
                    <p class="text-xs text-gray-500">Completed At</p>
                    <p class="text-sm">{{ $cardFundingRequest->completed_at->format('d M Y, H:i:s') }}</p>
                </div>
            @endif
            @if($cardFundingRequest->failure_reason)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-600 font-semibold">Failure Reason</p>
                    <p class="text-red-700 text-sm">{{ $cardFundingRequest->failure_reason }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Student & Parent Info -->
    <div class="finance-card p-5">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Student & Parent Information</h3>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-500">Student Name</p>
                <p class="font-semibold">{{ $cardFundingRequest->student_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Admission Number</p>
                <p class="font-medium">{{ $cardFundingRequest->student_admission ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Parent/Guardian Name</p>
                <p class="font-semibold">{{ $cardFundingRequest->parent_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Parent/Guardian Phone</p>
                <p class="font-medium">{{ $cardFundingRequest->parent_phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Parent Response</p>
                <span class="text-xs px-2 py-0.5 rounded
                    @if($cardFundingRequest->parent_response === 'approved') bg-green-100 text-green-600
                    @elseif($cardFundingRequest->parent_response === 'declined') bg-red-100 text-red-600
                    @else bg-yellow-100 text-yellow-600 @endif">
                    {{ ucfirst($cardFundingRequest->parent_response) }}
                </span>
                @if($cardFundingRequest->parent_response_at)
                    <span class="text-xs text-gray-500 ml-2">{{ $cardFundingRequest->parent_response_at->format('d M Y, H:i') }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- KCB Transaction Details -->
    <div class="lg:col-span-2 finance-card p-5">
        <h3 class="font-bold text-gray-800 text-lg mb-4">KCB Transaction Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <p class="text-xs text-gray-500">Checkout Request ID</p>
                <p class="font-mono text-sm">{{ $cardFundingRequest->checkout_request_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">KCB Invoice Number</p>
                <p class="font-mono text-sm">{{ $cardFundingRequest->kcb_invoice_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">IPN Transaction ID</p>
                <p class="font-mono text-sm">{{ $cardFundingRequest->ipn_transaction_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">M-Pesa Receipt</p>
                <p class="font-mono text-sm">{{ $cardFundingRequest->mpesa_receipt ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Retry Count</p>
                <p class="font-medium">{{ $cardFundingRequest->retry_count }} / {{ $cardFundingRequest->max_retries }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">SMS Sent</p>
                <p class="font-medium">{{ $cardFundingRequest->sms_sent ? 'Yes' : 'No' }}</p>
                @if($cardFundingRequest->sms_sent_at)
                    <p class="text-xs text-gray-500">{{ $cardFundingRequest->sms_sent_at->format('d M Y, H:i') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function retryFunding() {
        if (confirm('Retry this funding request?')) {
            showLoading('Retrying...');
            fetch('{{ route("finance.funding.retry", $cardFundingRequest) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    toastr.success(data.message || 'Retry initiated');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message || 'Failed to retry');
                }
            })
            .catch(error => {
                hideLoading();
                toastr.error('An error occurred');
            });
        }
    }

    function markFailed() {
        const reason = prompt('Enter failure reason:');
        if (reason === null) return;
        if (!reason.trim()) {
            toastr.warning('Please enter a reason');
            return;
        }

        showLoading('Updating status...');
        fetch('{{ route("finance.funding.fail", $cardFundingRequest) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                toastr.success(data.message || 'Marked as failed');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to update');
            }
        })
        .catch(error => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }

    function markCompleted() {
        const receipt = prompt('Enter M-Pesa receipt number:');
        if (receipt === null) return;
        if (!receipt.trim()) {
            toastr.warning('Please enter a receipt number');
            return;
        }

        showLoading('Updating status...');
        fetch('{{ route("finance.funding.complete", $cardFundingRequest) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ mpesa_receipt: receipt })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                toastr.success(data.message || 'Marked as completed');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to update');
            }
        })
        .catch(error => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }
</script>
@endpush
