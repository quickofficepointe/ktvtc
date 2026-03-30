@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Fee Payments')
@section('subtitle', 'Manage and track all fee payments')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Finance</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Fee Payments</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.fee-payments.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Record Payment</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Payments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalPayments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-credit-card text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
            KES {{ number_format($totalAmount ?? 0, 2) }}
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Collection</p>
                <p class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format($todayAmount ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            <i class="fas fa-clock mr-1"></i>
            {{ now()->format('M j, Y') }}
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Verification</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($pendingVerification ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-hourglass-half text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            <i class="fas fa-check-circle text-amber-600 mr-1"></i>
            Awaiting verification
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">This Month</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">KES {{ number_format($monthlyAmount ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            <i class="fas fa-chart-line mr-1"></i>
            {{ $monthlyCount ?? 0 }} transactions
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Collection Rate</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $collectionRate ?? 0 }}%</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Daily Collection Chart -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daily Collection Summary</h3>
                <p class="text-sm text-gray-600 mt-1">Last 7 days payment collection</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.fee-payments.reports.daily') }}"
                   class="text-sm text-primary hover:underline">
                    <i class="fas fa-chart-line mr-1"></i>
                    View Daily Report
                </a>
                <a href="{{ route('admin.fee-payments.reports.monthly') }}"
                   class="text-sm text-primary hover:underline">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    View Monthly Report
                </a>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div id="dailyCollectionChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter payments by date, student, method and status</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="toggleBulkActions()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <i class="fas fa-check-square"></i>
                    <span>Bulk Actions</span>
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" method="GET" action="{{ route('admin.fee-payments.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="kcb" {{ request('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                        <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                    </select>
                </div>

                <!-- Verification -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification</label>
                    <select name="is_verified" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="1" {{ request('is_verified') == '1' ? 'selected' : '' }}>Verified</option>
                        <option value="0" {{ request('is_verified') == '0' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>

                <!-- Student/Course Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by receipt #, student name, or transaction code..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                    <select name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.fee-payments.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar (Hidden by default) -->
<div id="bulkActionsBar" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <i class="fas fa-check-square text-blue-600"></i>
            <span class="text-sm font-medium text-blue-800">
                <span id="selectedCount">0</span> payment(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkVerify()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Verify</span>
            </button>
            <button onclick="bulkExport()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-download"></i>
                <span>Export Selected</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Payment Transactions</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $payments->total() }} payments found</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" id="tableSearch" placeholder="Quick search..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="{{ route('admin.fee-payments.export') }}?{{ http_build_query(request()->all()) }}"
                   class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                   title="Export to Excel">
                    <i class="fas fa-file-excel"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="paymentsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewPayment('{{ $payment->id }}')">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}"
                               class="payment-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-2">
                                <i class="fas fa-receipt text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $payment->receipt_number }}</span>
                                @if($payment->transaction_code)
                                    <span class="text-xs text-gray-500 block">{{ $payment->transaction_code }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-gray-600">
                                    {{ substr($payment->student_name ?? ($payment->student->full_name ?? 'S'), 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $payment->student_name ?? ($payment->student->full_name ?? 'N/A') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $payment->student_number ?? ($payment->student->student_number ?? '') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $payment->course_name ?? ($payment->enrollment->course_name ?? 'N/A') }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-lg font-bold text-green-600">KES {{ number_format($payment->amount, 2) }}</span>
                        @if($payment->payment_for_month)
                            <span class="text-xs text-gray-500 block">For: {{ $payment->payment_for_month }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $methodColors = [
                                'cash' => 'yellow',
                                'mpesa' => 'green',
                                'bank' => 'blue',
                                'kcb' => 'purple',
                                'other' => 'gray',
                            ];
                            $color = $methodColors[$payment->payment_method] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ strtoupper($payment->payment_method) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">{{ $payment->payment_date->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'completed' => 'green',
                                'pending' => 'yellow',
                                'failed' => 'red',
                                'reversed' => 'orange',
                            ];
                            $color = $statusColors[$payment->status] ?? 'gray';
                        @endphp
                        <div class="space-y-1">
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($payment->status) }}
                            </span>
                            @if($payment->is_verified)
                                <span class="inline-flex items-center text-xs text-green-600 ml-1">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Verified
                                </span>
                            @elseif($payment->status == 'completed')
                                <span class="inline-flex items-center text-xs text-amber-600 ml-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Pending
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.fee-payments.show', $payment) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.fee-payments.receipt', $payment) }}" target="_blank"
                               class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                               title="Print Receipt">
                                <i class="fas fa-print"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $payment->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $payment->id }}"
                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($payment->status == 'completed' && !$payment->is_verified)
                                        <button onclick="verifyPayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Verify
                                        </button>
                                        @endif
                                        @if($payment->status == 'completed')
                                        <button onclick="reversePayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-undo-alt mr-2"></i>
                                            Reverse
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.fee-payments.receipt', $payment) }}" target="_blank"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-print mr-2"></i>
                                            Print Receipt
                                        </a>
                                        <hr class="my-1 border-gray-200">
                                        <button onclick="deletePayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-credit-card text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payments found</p>
                            <p class="text-gray-400 text-sm mt-1">Record your first payment to get started</p>
                            <a href="{{ route('admin.fee-payments.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Record Payment
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $payments->firstItem() }}</span> to
                <span class="font-medium">{{ $payments->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($payments->total()) }}</span> payments
            </div>
            <div class="flex items-center space-x-2">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
    @endif
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
                <form id="verifyForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700" id="verifyInfo"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('verifyModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitVerify()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Verify</button>
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
                <form id="reverseForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-sm text-orange-700" id="reverseInfo"></p>
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
                <button onclick="submitReverse()" class="px-4 py-2 bg-orange-600 text-white rounded-lg">Reverse</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Payment</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600">Are you sure you want to delete this payment?</p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Verify Modal -->
<div id="bulkVerifyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkVerifyModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Verify Payments</h3>
                    <button onclick="closeModal('bulkVerifyModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkVerifyMessage">
                        Verify <span id="bulkVerifyCount"></span> selected payment(s)?
                    </p>
                </div>
                <form id="bulkVerifyForm" method="POST" action="{{ route('admin.fee-payments.bulk.verify') }}">
                    @csrf
                    <div id="bulkVerifyInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkVerifyModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitBulkVerify()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Verify All</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        loadDailyCollectionChart();
        initializeQuickSearch();
    });

    // ============ CHART ============
    function loadDailyCollectionChart() {
        fetch('{{ route("admin.fee-payments.api.today-stats") }}?days=7')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ctx = document.getElementById('dailyCollectionChart')?.getContext('2d');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: Object.keys(data.data.daily_totals).map(date => {
                                    const d = new Date(date);
                                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                }),
                                datasets: [{
                                    label: 'Daily Collection (KES)',
                                    data: Object.values(data.data.daily_totals),
                                    borderColor: '#B91C1C',
                                    backgroundColor: 'rgba(185, 28, 28, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: value => 'KES ' + value.toLocaleString()
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            });
    }

    // ============ TABLE FUNCTIONS ============
    function viewPayment(id) {
        window.location.href = `/admin/fee-payments/${id}`;
    }

    function refreshTable() {
        location.reload();
    }

    function initializeQuickSearch() {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const params = new URLSearchParams(window.location.search);
                    params.set('search', this.value);
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                }
            });
        }
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        document.querySelectorAll('.payment-checkbox').forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;

        const bulkBar = document.getElementById('bulkActionsBar');
        if (count > 0) bulkBar.classList.remove('hidden');
        else bulkBar.classList.add('hidden');
    }

    function toggleBulkActions() {
        const bulkBar = document.getElementById('bulkActionsBar');
        bulkBar.classList.toggle('hidden');
        if (bulkBar.classList.contains('hidden')) {
            document.querySelectorAll('.payment-checkbox').forEach(cb => cb.checked = false);
            document.querySelector('th input[type="checkbox"]').checked = false;
        }
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.payment-checkbox:checked')).map(cb => cb.value);
    }

    function bulkVerify() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one payment');
            return;
        }
        document.getElementById('bulkVerifyCount').textContent = ids.length;
        const inputs = document.getElementById('bulkVerifyInputs');
        inputs.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            inputs.appendChild(input);
        });
        openModal('bulkVerifyModal');
    }

    function submitBulkVerify() {
        document.getElementById('bulkVerifyForm').submit();
    }

    function bulkExport() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one payment');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.fee-payments.export") }}';
        form.innerHTML = '@csrf';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(id) {
        document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        const menu = document.getElementById(`actionMenu-${id}`);
        if (menu) menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // ============ SINGLE ACTIONS ============
    function verifyPayment(id) {
        fetch(`/admin/fee-payments/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('verifyInfo').textContent =
                        `Verify payment: ${data.data.fee_payment.receipt_number} - KES ${data.data.fee_payment.amount}`;
                    document.getElementById('verifyForm').action = `/admin/fee-payments/${id}/verify`;
                    openModal('verifyModal');
                }
            });
    }

    function submitVerify() {
        document.getElementById('verifyForm').submit();
    }

    function reversePayment(id) {
        fetch(`/admin/fee-payments/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reverseInfo').textContent =
                        `Reverse payment: ${data.data.fee_payment.receipt_number} - KES ${data.data.fee_payment.amount}`;
                    document.getElementById('reverseForm').action = `/admin/fee-payments/${id}/reverse`;
                    openModal('reverseModal');
                }
            });
    }

    function submitReverse() {
        document.getElementById('reverseForm').submit();
    }

    function deletePayment(id) {
        document.getElementById('deleteForm').action = `/admin/fee-payments/${id}`;
        openModal('deleteModal');
    }

    function submitDelete() {
        document.getElementById('deleteForm').submit();
    }

    // ============ MODAL FUNCTIONS ============
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
            ['verifyModal', 'reverseModal', 'deleteModal', 'bulkVerifyModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>

<style>
    #paymentsTable {
        min-width: 1400px;
    }
    @media (max-width: 768px) {
        #paymentsTable {
            min-width: 100%;
        }
    }
    tr[onclick]:hover {
        cursor: pointer;
        background-color: #F9FAFB;
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
