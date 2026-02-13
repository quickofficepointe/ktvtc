@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Fee Payments')
@section('subtitle', 'Manage and track all fee payments')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Management</span>
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
    <button onclick="exportToExcel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>
    <a href="{{ route('admin.fees.payments.create') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Record Payment</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Payments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalPayments }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-money-check-alt text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                <span>KES {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Collection</p>
                <p class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format($todayAmount, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-green-600 mr-1"></i>
                <span>{{ date('M j, Y') }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Verification</p>
                <p class="text-2xl font-bold text-yellow-600 mt-2">{{ $pendingVerification }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-yellow-600 mr-1"></i>
                <span>Awaiting verification</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">KCB Payments</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">{{ $kcbPayments }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-mobile-alt text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-wifi text-purple-600 mr-1"></i>
                <span>STK Push transactions</span>
            </div>
        </div>
    </div>
</div>

<!-- Daily Summary Chart -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daily Collection Summary</h3>
        <p class="text-sm text-gray-600 mt-1">Last 7 days payment collection</p>
    </div>
    <div class="p-6">
        <div id="dailyCollectionChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
        <p class="text-sm text-gray-600 mt-1">Filter payments by various criteria</p>
    </div>
    <div class="p-6">
        <form id="filterForm" method="GET" action="{{ route('admin.fees.payments.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Student Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->student_number ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Registration Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration</label>
                    <select name="registration_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Registrations</option>
                        @foreach($registrations as $registration)
                            <option value="{{ $registration->id }}" {{ request('registration_id') == $registration->id ? 'selected' : '' }}>
                                {{ $registration->course->name ?? 'N/A' }} - {{ $registration->student->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" {{ request('payment_method') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Verification Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                    <select name="is_verified" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="1" {{ request('is_verified') == '1' ? 'selected' : '' }}>Verified</option>
                        <option value="0" {{ request('is_verified') == '0' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>

                <!-- Transaction ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID</label>
                    <input type="text" name="transaction_id" value="{{ request('transaction_id') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Search transaction ID...">
                </div>

                <!-- Receipt Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Number</label>
                    <input type="text" name="receipt_number" value="{{ request('receipt_number') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Search receipt number...">
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <button type="reset" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="bg-white rounded-xl border border-gray-200 mb-6 p-4 hidden" id="bulkActionsBar">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span id="selectedCount" class="text-sm font-medium text-gray-700">0 items selected</span>
            <div class="flex space-x-2">
                <select id="bulkAction" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                    <option value="">Choose action</option>
                    <option value="verify">Verify Payments</option>
                    <option value="send_receipts">Send Receipts</option>
                    <option value="export">Export Selected</option>
                </select>
                <button onclick="executeBulkAction()" class="px-3 py-1 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                    Apply
                </button>
            </div>
        </div>
        <button onclick="clearSelection()" class="text-sm text-gray-600 hover:text-gray-800">
            Clear Selection
        </button>
    </div>
</div>

<!-- Fee Payments Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Fee Payments</h3>
                <p class="text-sm text-gray-600 mt-1">All fee payment transactions and records</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search payments..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-64"
                           id="searchInput">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="feePaymentsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt/Transaction</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Details</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Details</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($feePayments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6">
                        <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $payment->id }}">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg
                                {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100 text-purple-600' :
                                   ($payment->payment_method == 'paybill' ? 'bg-blue-100 text-blue-600' :
                                   ($payment->payment_method == 'bank_deposit' ? 'bg-green-100 text-green-600' :
                                   ($payment->payment_method == 'cash' ? 'bg-yellow-100 text-yellow-600' :
                                   'bg-gray-100 text-gray-600'))) }}
                                flex items-center justify-center mr-3">
                                @if($payment->payment_method == 'kcb_stk_push')
                                <i class="fas fa-mobile-alt text-sm"></i>
                                @elseif($payment->payment_method == 'paybill')
                                <i class="fas fa-wifi text-sm"></i>
                                @elseif($payment->payment_method == 'bank_deposit')
                                <i class="fas fa-university text-sm"></i>
                                @elseif($payment->payment_method == 'cash')
                                <i class="fas fa-money-bill-wave text-sm"></i>
                                @else
                                <i class="fas fa-money-check-alt text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900 block">{{ $payment->receipt_number }}</span>
                                <div class="text-xs text-gray-500">{{ $payment->transaction_id }}</div>
                                @if($payment->is_kcb_payment)
                                <span class="text-xs text-purple-600 font-medium">KCB STK</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $payment->student->name ?? 'N/A' }}</p>
                            <div class="text-xs text-gray-600 mt-1">
                                {{ $payment->student->student_number ?? 'No ID' }}
                            </div>
                            @if($payment->registration)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $payment->registration->course->name ?? 'N/A' }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <p class="text-sm text-gray-800">{{ $payment->description ?? 'Fee Payment' }}</p>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100 text-purple-800' :
                                       ($payment->payment_method == 'paybill' ? 'bg-blue-100 text-blue-800' :
                                       ($payment->payment_method == 'bank_deposit' ? 'bg-green-100 text-green-800' :
                                       ($payment->payment_method == 'cash' ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-gray-100 text-gray-800'))) }}">
                                    {{ $payment->payment_method_label }}
                                </span>
                            </div>
                            @if($payment->payer_name)
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                {{ $payment->payer_name }} ({{ $payment->payer_type }})
                            </div>
                            @endif
                            @if($payment->payer_phone)
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-phone mr-1"></i>
                                {{ $payment->payer_phone }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <div class="text-lg font-bold text-gray-900">
                                KES {{ number_format($payment->amount, 2) }}
                            </div>
                            <div class="text-xs text-gray-600">
                                Balance: KES {{ number_format($payment->balance_before, 2) }} →
                                KES {{ number_format($payment->balance_after, 2) }}
                            </div>
                            @if($payment->studentFee)
                            <div class="text-xs text-gray-500">
                                Invoice: {{ $payment->studentFee->invoice_number }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-2">
                            @if($payment->status == 'completed')
                                @if($payment->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Verified
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Pending Verification
                                </span>
                                @endif
                            @elseif($payment->status == 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                Pending
                            </span>
                            @elseif($payment->status == 'failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                Failed
                            </span>
                            @elseif($payment->status == 'reversed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-undo-alt mr-1"></i>
                                Reversed
                            </span>
                            @elseif($payment->status == 'refunded')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-hand-holding-usd mr-1"></i>
                                Refunded
                            </span>
                            @endif

                            @if($payment->receipt_sent_to_payer)
                            <div class="text-xs text-green-600">
                                <i class="fas fa-receipt mr-1"></i>
                                Receipt Sent
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <div class="text-sm text-gray-900">
                                {{ $payment->payment_date->format('M j, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $payment->payment_time }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $payment->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.fee-payments.show', $payment) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.fee-payments.receipt', $payment) }}" target="_blank"
                               class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Receipt">
                                <i class="fas fa-receipt"></i>
                            </a>
                            <button onclick="downloadReceipt('{{ $payment->id }}')"
                                    class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Download Receipt">
                                <i class="fas fa-download"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $payment->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $payment->id }}" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($payment->status == 'completed' && !$payment->is_verified)
                                        <button onclick="verifyPayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Verify Payment
                                        </button>
                                        @endif

                                        @if($payment->status == 'completed' && $payment->is_verified)
                                        <button onclick="reversePayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-undo-alt mr-2"></i>
                                            Reverse Payment
                                        </button>
                                        @endif

                                        @if(!$payment->receipt_sent_to_payer)
                                        <button onclick="sendReceipt('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Send Receipt
                                        </button>
                                        @endif

                                        <div class="border-t border-gray-200 my-1"></div>

                                        <a href="{{ route('admin.fee-payments.show', $payment) }}#details"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            View Details
                                        </a>

                                        <a href="{{ route('admin.student-fees.show', $payment->student_fee_id) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                                            View Invoice
                                        </a>

                                        <div class="border-t border-gray-200 my-1"></div>

                                        @if(!$payment->is_verified || $payment->status == 'failed')
                                        <button onclick="deletePayment('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete Payment
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-money-check-alt text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payments found</p>
                            <p class="text-gray-400 text-sm mt-1">Click "Record Payment" to record your first payment</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    @if($feePayments->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $feePayments->firstItem() }}</span> to
                <span class="font-medium">{{ $feePayments->lastItem() }}</span> of
                <span class="font-medium">{{ $feePayments->total() }}</span> payments
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $feePayments->currentPage() == 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ $feePayments->currentPage() }} of {{ $feePayments->lastPage() }}
                </span>
                <button onclick="nextPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $feePayments->currentPage() == $feePayments->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Verify Payment Modal -->
<div id="verifyPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('verifyPaymentModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Verify Payment</h3>
                    <button onclick="closeModal('verifyPaymentModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="verifyPaymentForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-700" id="verifyPaymentInfo"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes</label>
                            <textarea name="verification_notes" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Optional notes about verification..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('verifyPaymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitVerifyForm()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Verify Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reverse Payment Modal -->
<div id="reversePaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('reversePaymentModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Reverse Payment</h3>
                    <button onclick="closeModal('reversePaymentModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="reversePaymentForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-sm text-red-700" id="reversePaymentInfo"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Reversal *</label>
                            <textarea name="reason" required rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Explain why this payment is being reversed..."></textarea>
                        </div>

                        <div class="text-sm text-gray-600">
                            <p><strong>Note:</strong> Reversing a payment will:</p>
                            <ul class="list-disc pl-5 mt-1">
                                <li>Update the payment status to "reversed"</li>
                                <li>Restore the invoice balance</li>
                                <li>Update the student's payment record</li>
                                <li>This action cannot be undone automatically</li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('reversePaymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitReverseForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Reverse Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Receipt Modal -->
<div id="sendReceiptModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('sendReceiptModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Send Receipt</h3>
                    <button onclick="closeModal('sendReceiptModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="sendReceiptForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <span class="text-sm text-blue-700" id="sendReceiptInfo"></span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="sendEmail" name="send_email" class="rounded border-gray-300 text-primary focus:ring-primary" checked>
                                <label for="sendEmail" class="ml-2 text-sm text-gray-700">
                                    Send Email Receipt
                                    <span class="text-gray-500 text-xs block">To: <span id="payerEmail"></span></span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="sendSms" name="send_sms" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="sendSms" class="ml-2 text-sm text-gray-700">
                                    Send SMS Receipt
                                    <span class="text-gray-500 text-xs block">To: <span id="payerPhone"></span></span>
                                </label>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600">
                            <p>The receipt will include:</p>
                            <ul class="list-disc pl-5 mt-1">
                                <li>Payment details and transaction ID</li>
                                <li>Student and course information</li>
                                <li>Payment method and amount</li>
                                <li>Balance information</li>
                                <li>Official receipt number</li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('sendReceiptModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitSendReceiptForm()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Send Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deletePaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deletePaymentModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                        <p class="text-sm text-gray-600 mt-1">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-700" id="deletePaymentMessage"></p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deletePaymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <form id="deletePaymentForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                <button onclick="confirmPaymentDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Delete Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Verify Modal -->
<div id="bulkVerifyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkVerifyModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Verify Payments</h3>
                    <button onclick="closeModal('bulkVerifyModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="bulkVerifyForm">
                    <div class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-700">
                                    Verify <span id="bulkVerifyCount">0</span> selected payment(s)
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes</label>
                            <textarea name="verification_notes" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Optional notes about verification..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkVerifyModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkVerify()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Verify Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Bulk selection
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionsBar = document.getElementById('bulkActionsBar');

        function updateBulkActions() {
            const selected = document.querySelectorAll('.row-checkbox:checked');
            const selectedCount = selected.length;

            if (selectedCount > 0) {
                bulkActionsBar.classList.remove('hidden');
                document.getElementById('selectedCount').textContent = `${selectedCount} items selected`;
                document.getElementById('bulkVerifyCount').textContent = selectedCount;
            } else {
                bulkActionsBar.classList.add('hidden');
            }
        }

        selectAll?.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        // Load daily collection chart
        loadDailyCollectionChart();
    });

    // Load daily collection chart
    function loadDailyCollectionChart() {
        fetch('/admin/fee-payments/statistics?start_date={{ now()->subDays(6)->format("Y-m-d") }}&end_date={{ now()->format("Y-m-d") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const dailyTotals = data.data.daily_totals;
                    const dates = Object.keys(dailyTotals).sort();
                    const amounts = dates.map(date => dailyTotals[date]);

                    // Create chart using Chart.js or any charting library
                    // This is a placeholder for chart implementation
                    console.log('Chart data:', { dates, amounts });
                }
            });
    }

    // Action menu toggle
    function toggleActionMenu(paymentId) {
        const menu = document.getElementById(`actionMenu-${paymentId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${paymentId}`) {
                m.classList.add('hidden');
            }
        });

        menu.classList.toggle('hidden');
    }

    // Close action menus when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // Verify payment
    function verifyPayment(paymentId) {
        fetch(`/admin/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('verifyPaymentInfo').textContent =
                        `Verify payment: ${payment.receipt_number} - KES ${parseFloat(payment.amount).toFixed(2)}`;

                    const form = document.getElementById('verifyPaymentForm');
                    form.action = `/admin/fee-payments/${paymentId}/verify`;

                    openModal('verifyPaymentModal', 'lg');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load payment details', 'error');
            });
    }

    // Reverse payment
    function reversePayment(paymentId) {
        fetch(`/admin/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('reversePaymentInfo').textContent =
                        `Reverse payment: ${payment.receipt_number} - KES ${parseFloat(payment.amount).toFixed(2)}`;

                    const form = document.getElementById('reversePaymentForm');
                    form.action = `/admin/fee-payments/${paymentId}/reverse`;

                    openModal('reversePaymentModal', 'lg');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load payment details', 'error');
            });
    }

    // Send receipt
    function sendReceipt(paymentId) {
        fetch(`/admin/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('sendReceiptInfo').textContent =
                        `Send receipt for payment: ${payment.receipt_number}`;
                    document.getElementById('payerEmail').textContent = payment.payer_email || 'No email';
                    document.getElementById('payerPhone').textContent = payment.payer_phone;

                    const form = document.getElementById('sendReceiptForm');
                    form.action = `/admin/fee-payments/${paymentId}/send-receipt`;

                    openModal('sendReceiptModal', 'lg');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load payment details', 'error');
            });
    }

    // Download receipt
    function downloadReceipt(paymentId) {
        window.open(`/admin/fee-payments/${paymentId}/download-receipt`, '_blank');
    }

    // Delete payment
    function deletePayment(paymentId) {
        const form = document.getElementById('deletePaymentForm');
        form.action = `/admin/fee-payments/${paymentId}`;
        document.getElementById('deletePaymentMessage').textContent =
            'Are you sure you want to delete this payment? This action cannot be undone.';
        openModal('deletePaymentModal', 'md');
    }

    function confirmPaymentDelete() {
        document.getElementById('deletePaymentForm').submit();
    }

    // Submit forms
    function submitVerifyForm() {
        document.getElementById('verifyPaymentForm').submit();
    }

    function submitReverseForm() {
        document.getElementById('reversePaymentForm').submit();
    }

    function submitSendReceiptForm() {
        document.getElementById('sendReceiptForm').submit();
    }

    // Bulk actions
    function executeBulkAction() {
        const action = document.getElementById('bulkAction').value;
        if (!action) {
            showToast('Please select an action', 'error');
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedIds.length === 0) {
            showToast('Please select at least one payment', 'error');
            return;
        }

        switch (action) {
            case 'verify':
                openBulkVerifyModal(selectedIds);
                break;

            case 'send_receipts':
                if (confirm(`Send receipts for ${selectedIds.length} payment(s)?`)) {
                    submitBulkAction(action, selectedIds);
                }
                break;

            case 'export':
                exportSelected(selectedIds);
                break;
        }
    }

    function openBulkVerifyModal(selectedIds) {
        document.getElementById('bulkVerifyCount').textContent = selectedIds.length;
        window.bulkVerifyIds = selectedIds;
        openModal('bulkVerifyModal', 'lg');
    }

    function submitBulkVerify() {
        const verificationNotes = document.querySelector('#bulkVerifyForm textarea[name="verification_notes"]').value;

        fetch('/admin/fee-payments/bulk-verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ids: window.bulkVerifyIds,
                verification_notes: verificationNotes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(error => {
                        showToast(error, 'warning');
                    });
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to bulk verify payments', 'error');
        });
    }

    function submitBulkAction(action, ids, additionalData = {}) {
        fetch('/admin/fee-payments/bulk-actions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                action: action,
                ids: ids,
                ...additionalData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(error => {
                        showToast(error, 'warning');
                    });
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to execute bulk action', 'error');
        });
    }

    function exportSelected(selectedIds) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.fees.payments.export") }}';
        form.innerHTML = `
            @csrf
            <input type="hidden" name="format" value="csv">
            <input type="hidden" name="ids" value='${JSON.stringify(selectedIds)}'>
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Clear selection
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('bulkActionsBar').classList.add('hidden');
    }

    // Export to Excel
    function exportToExcel() {
        // Collect current filter values
        const filters = {
            start_date: document.querySelector('input[name="date_from"]')?.value,
            end_date: document.querySelector('input[name="date_to"]')?.value,
            payment_method: document.querySelector('select[name="payment_method"]')?.value,
            status: document.querySelector('select[name="status"]')?.value,
        };

        // Submit export form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.fees.payments.export") }}';
        form.innerHTML = `
            @csrf
            <input type="hidden" name="format" value="excel">
            ${Object.entries(filters).map(([key, value]) =>
                `<input type="hidden" name="${key}" value="${value || ''}">`
            ).join('')}
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#feePaymentsTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Pagination
    function prevPage() {
        @if($feePayments->currentPage() > 1)
            window.location.href = '{{ $feePayments->previousPageUrl() }}';
        @endif
    }

    function nextPage() {
        @if($feePayments->hasMorePages())
            window.location.href = '{{ $feePayments->nextPageUrl() }}';
        @endif
    }
</script>

<style>
    .card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    #feePaymentsTable {
        min-width: 1400px;
    }

    @media (max-width: 768px) {
        #feePaymentsTable {
            min-width: 100%;
        }
    }

    .payment-method-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
