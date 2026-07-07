@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure: ' . $course->name)
@section('subtitle', $course->code ?? 'Course Fee Details')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Structure</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $course->name }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
    <a href="{{ route('finance.fee-structure.edit', $course) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Fee Structure</span>
    </a>
    <a href="{{ route('finance.fee-structure.history', $course) }}"
       class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-history"></i>
        <span>View History</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Course Info Card -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $course->name }}</h2>
                    <div class="flex items-center space-x-3 mt-1">
                        <span class="text-sm text-gray-500">Code: {{ $course->code ?? 'N/A' }}</span>
                        <span class="text-sm text-gray-500">|</span>
                        <span class="text-sm text-gray-500">Department: {{ $course->department->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div>
                    @if($course->hasPendingFeeChanges())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                            <i class="fas fa-clock mr-2"></i> Pending Approval
                        </span>
                    @elseif($course->isFeeStructureApproved())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-2"></i> Approved
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-check mr-2"></i> Current
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Total Fees</p>
                    <p class="text-2xl font-bold text-primary">KES {{ number_format($totalFee, 2) }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Version</p>
                    <p class="text-xl font-bold text-gray-800">{{ $feeStructure['version'] }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Last Modified</p>
                    <p class="text-sm font-medium text-gray-800">{{ $feeStructure['last_modified_at'] ?? 'Never' }}</p>
                    <p class="text-xs text-gray-500">by {{ $feeStructure['last_modified_by'] ?? 'N/A' }}</p>
                </div>
            </div>

            @if($course->fee_modification_reason)
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Modification Reason:</strong> {{ $course->fee_modification_reason }}
                </p>
            </div>
            @endif

            @if($course->hasPendingFeeChanges())
            <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-800">
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Pending Approval:</strong> This fee structure is awaiting approval.
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="approveFee()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm flex items-center">
                            <i class="fas fa-check mr-2"></i> Approve
                        </button>
                        <button onclick="rejectFee()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm flex items-center">
                            <i class="fas fa-times mr-2"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Fee Breakdown -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-list-ul text-primary mr-2"></i>
                Fee Breakdown
            </h3>
        </div>
        <div class="p-6">
            @if(!empty($formattedBreakdown))
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Item</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($formattedBreakdown as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $item['label'] }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-primary text-right">
                                KES {{ number_format($item['amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $item['description'] ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 text-sm text-gray-900">TOTAL</td>
                            <td class="px-6 py-4 text-sm text-primary text-right">KES {{ number_format($totalFee, 2) }}</td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-circle text-gray-300 text-4xl mb-3 block"></i>
                    <p class="text-gray-500">No fee breakdown available for this course.</p>
                    <a href="{{ route('finance.fee-structure.edit', $course) }}"
                       class="mt-4 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Add Fee Structure
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('approveModal')"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Approve Fee Structure</h3>
                <button onclick="closeModal('approveModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('finance.fee-structure.approve', $course) }}" method="POST">
                @csrf
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to approve the fee structure changes for <strong>{{ $course->name }}</strong>?
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Approval Note (Optional)</label>
                    <textarea name="approval_note" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('approveModal')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('rejectModal')"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Reject Fee Structure</h3>
                <button onclick="closeModal('rejectModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('finance.fee-structure.reject', $course) }}" method="POST">
                @csrf
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to reject the fee structure changes for <strong>{{ $course->name }}</strong>?
                    This will restore the previous fee structure.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Please explain why you are rejecting these changes..."
                              required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('rejectModal')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function approveFee() {
        document.getElementById('approveModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function rejectFee() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
@endpush
