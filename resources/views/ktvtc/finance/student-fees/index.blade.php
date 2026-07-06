@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Fee Payments')
@section('subtitle', 'Manage and track all student fee payments')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.student-fees.create') }}" class="btn-primary px-4 py-2 text-white rounded-lg font-semibold flex items-center shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-plus mr-2"></i> Record Payment
    </a>
    <a href="{{ route('finance.student-fees.reports.outstanding') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition-all">
        <i class="fas fa-exclamation-triangle mr-2"></i> Outstanding
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Payments</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPayments ?? 0) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Amount</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalAmount ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Today's Collection</p>
            <p class="text-2xl font-bold text-blue-600">KES {{ number_format($todayAmount ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Pending Verification</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($pendingVerification ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.student-fees.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="kcb" {{ request('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                    <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Verified</label>
                <select name="is_verified" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All</option>
                    <option value="1" {{ request('is_verified') == '1' ? 'selected' : '' }}>Verified</option>
                    <option value="0" {{ request('is_verified') == '0' ? 'selected' : '' }}>Pending Verification</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <div class="flex">
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" class="w-full px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-primary-dark transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">Receipt #</th>
                        <th class="text-left py-3 px-4">Student</th>
                        <th class="text-left py-3 px-4">Course</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Method</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments ?? [] as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium text-primary">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium">{{ $payment->student->full_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $payment->student->student_number ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $payment->enrollment->course->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-right font-semibold">KES {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $payment->payment_method }}</span>
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">
                                @if($payment->is_verified && $payment->status === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @elseif($payment->status === 'completed' && !$payment->is_verified)
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif($payment->status === 'reversed')
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Reversed</span>
                                @else
                                    <span class="status-badge status-pending">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.student-fees.show', $payment) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.student-fees.receipt', $payment) }}" class="text-gray-500 hover:text-gray-700" title="Receipt" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if(!$payment->is_verified && $payment->status === 'completed')
                                        <button onclick="verifyPayment({{ $payment->id }})" class="text-green-600 hover:text-green-800" title="Verify">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    @if($payment->status === 'completed')
                                        <button onclick="reversePayment({{ $payment->id }})" class="text-red-600 hover:text-red-800" title="Reverse">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-credit-card text-4xl text-gray-300 mb-2 block"></i>
                                No payments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Verify Payment</h3>
            <button onclick="closeModal('verifyModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="verifyForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea name="notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('verifyModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Verify Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Reverse Modal -->
<div id="reverseModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Reverse Payment</h3>
            <button onclick="closeModal('reverseModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reverseForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Reason for Reversal *</label>
                <textarea name="reason" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reverseModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reverse Payment</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function verifyPayment(id) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');
        form.action = `/finance/student-fees/${id}/verify`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function reversePayment(id) {
        const modal = document.getElementById('reverseModal');
        const form = document.getElementById('reverseForm');
        form.action = `/finance/student-fees/${id}/reverse`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('verifyModal');
            closeModal('reverseModal');
        }
    });
</script>
@endpush
