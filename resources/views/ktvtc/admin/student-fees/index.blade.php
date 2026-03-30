@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Fee Payments')
@section('subtitle', 'Manage and track all fee payments')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
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
    <button onclick="exportToExcel()"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>
    <button onclick="openModal('createModal')"
            class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Record Payment</span>
    </button>
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
                <i class="fas fa-money-check-alt text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                <span>KES {{ number_format($totalAmount ?? 0, 2) }}</span>
            </div>
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
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-green-600 mr-1"></i>
                <span>{{ now()->format('M j, Y') }}</span>
            </div>
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
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-amber-600 mr-1"></i>
                <span>Awaiting verification</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">KCB Payments</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($kcbPayments ?? 0) }}</p>
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

<!-- Daily Summary Chart -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daily Collection Summary</h3>
        <p class="text-sm text-gray-600 mt-1">Last 7 days payment collection</p>
    </div>
    <div class="p-6">
        <canvas id="dailyCollectionChart" style="height: 300px;"></canvas>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter payments by various criteria</p>
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
        <form id="filterForm" method="GET" action="{{ route('admin.tvet.fee-payments.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Student Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->student_number ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Enrollment Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment</label>
                    <select name="enrollment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Enrollments</option>
                        @foreach($enrollments as $enrollment)
                            <option value="{{ $enrollment->id }}" {{ request('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                                {{ $enrollment->student->full_name ?? 'N/A' }} - {{ $enrollment->course->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Invoice Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice</label>
                    <select name="invoice_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Invoices</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}" {{ request('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_number }} - {{ $invoice->student->full_name ?? 'N/A' }}
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification</label>
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
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.fee-payments.index') }}"
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
            <button onclick="bulkSendReceipts()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-envelope"></i>
                <span>Send Receipts</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Fee Payments Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Fee Payments</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $feePayments->total() }} payments found</p>
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
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="feePaymentsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt/Transaction</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($feePayments as $payment)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewPayment({{ $payment->id }})">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}"
                               class="payment-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg
                                {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100' :
                                   ($payment->payment_method == 'paybill' ? 'bg-blue-100' :
                                   ($payment->payment_method == 'bank_deposit' ? 'bg-green-100' :
                                   ($payment->payment_method == 'cash' ? 'bg-yellow-100' :
                                   'bg-gray-100'))) }}
                                flex items-center justify-center mr-3">
                                @if($payment->payment_method == 'kcb_stk_push')
                                <i class="fas fa-mobile-alt text-purple-600 text-sm"></i>
                                @elseif($payment->payment_method == 'paybill')
                                <i class="fas fa-wifi text-blue-600 text-sm"></i>
                                @elseif($payment->payment_method == 'bank_deposit')
                                <i class="fas fa-university text-green-600 text-sm"></i>
                                @elseif($payment->payment_method == 'cash')
                                <i class="fas fa-money-bill-wave text-yellow-600 text-sm"></i>
                                @else
                                <i class="fas fa-money-check-alt text-gray-600 text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900 block">{{ $payment->receipt_number }}</span>
                                <div class="text-xs text-gray-500">{{ $payment->transaction_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $payment->student->full_name ?? 'N/A' }}</p>
                            <div class="text-xs text-gray-600 mt-1">
                                {{ $payment->student->student_number ?? 'No ID' }}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($payment->invoice)
                            <div>
                                <span class="text-sm font-mono text-gray-900">{{ $payment->invoice->invoice_number }}</span>
                                <div class="text-xs text-gray-500">
                                    Balance: KES {{ number_format($payment->invoice->balance, 2) }}
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100 text-purple-800' :
                               ($payment->payment_method == 'paybill' ? 'bg-blue-100 text-blue-800' :
                               ($payment->payment_method == 'bank_deposit' ? 'bg-green-100 text-green-800' :
                               ($payment->payment_method == 'cash' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800'))) }}">
                            {{ $payment->payment_method_label }}
                        </span>
                        @if($payment->is_kcb_payment)
                            <span class="text-xs text-purple-600 block mt-1">STK Push</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <span class="text-lg font-bold text-gray-900">KES {{ number_format($payment->amount, 2) }}</span>
                            <div class="text-xs text-gray-500 mt-1">
                                Bal: {{ number_format($payment->balance_before, 0) }} → {{ number_format($payment->balance_after, 0) }}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-2">
                            @php
                                $statusColors = [
                                    'pending' => 'yellow',
                                    'completed' => 'green',
                                    'failed' => 'red',
                                    'reversed' => 'orange',
                                    'disputed' => 'purple',
                                ];
                                $color = $statusColors[$payment->status] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                                {{ ucfirst($payment->status) }}
                            </span>
                            @if($payment->is_verified)
                                <span class="inline-flex items-center text-xs text-green-600 ml-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Verified
                                </span>
                            @elseif($payment->status == 'completed')
                                <span class="inline-flex items-center text-xs text-amber-600 ml-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    Pending Verification
                                </span>
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
                            @if($payment->payer_name)
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                {{ $payment->payer_name }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewPayment({{ $payment->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="showReceipt({{ $payment->id }})"
                               class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Receipt">
                                <i class="fas fa-receipt"></i>
                            </button>
                            <button onclick="downloadReceipt({{ $payment->id }})"
                                    class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Download Receipt">
                                <i class="fas fa-download"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $payment->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $payment->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
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
                                                class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-undo-alt mr-2"></i>
                                            Reverse Payment
                                        </button>
                                        @endif

                                        @if(!$payment->receipt_sent_to_payer)
                                        <button onclick="sendReceipt('{{ $payment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-envelope mr-2"></i>
                                            Send Receipt
                                        </button>
                                        @endif

                                        <hr class="my-1 border-gray-200">

                                        <a href="{{ route('admin.tvet.invoices.show', $payment->invoice_id) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                                            View Invoice
                                        </a>

                                        <hr class="my-1 border-gray-200">

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
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-money-check-alt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payments found</p>
                            <p class="text-gray-400 text-sm mt-1">Click "Record Payment" to record your first payment</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Record Payment
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($feePayments instanceof \Illuminate\Pagination\LengthAwarePaginator && $feePayments->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $feePayments->firstItem() }}</span> to
                <span class="font-medium">{{ $feePayments->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($feePayments->total()) }}</span> payments
            </div>
            <div class="flex items-center space-x-2">
                {{ $feePayments->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Create Payment Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Record Payment</h3>
                        <p class="text-sm text-gray-600">Record a new fee payment</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.fee-payments.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Invoice Selection -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-file-invoice text-primary mr-2"></i>
                                    Invoice Details
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Select Invoice</label>
                                        <select name="invoice_id" id="invoice_id" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Choose invoice...</option>
                                            @foreach($invoices->where('balance', '>', 0) as $invoice)
                                                <option value="{{ $invoice->id }}"
                                                        data-student="{{ $invoice->student_id }}"
                                                        data-enrollment="{{ $invoice->enrollment_id }}"
                                                        data-balance="{{ $invoice->balance }}">
                                                    {{ $invoice->invoice_number }} - {{ $invoice->student->full_name ?? 'N/A' }}
                                                    (Bal: KES {{ number_format($invoice->balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Hidden fields that will be auto-filled -->
                                    <input type="hidden" name="student_id" id="student_id">
                                    <input type="hidden" name="enrollment_id" id="enrollment_id">

                                    <div id="invoicePreview" class="hidden bg-blue-50 p-4 rounded-lg">
                                        <p class="text-sm font-medium text-blue-800" id="previewInvoiceNumber"></p>
                                        <p class="text-xs text-blue-600" id="previewBalance"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Amount -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                                    Payment Amount
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Amount (KES)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="0.00">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500" id="maxAmount"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-credit-card text-primary mr-2"></i>
                                    Payment Method
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Method</label>
                                        <select name="payment_method" id="payment_method" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                onchange="togglePaymentFields()">
                                            @foreach($paymentMethods as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- KCB STK Push Fields -->
                                    <div id="kcb_fields" class="hidden space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Transaction Code</label>
                                            <input type="text" name="kcb_transaction_code"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="e.g., OI41M2J3K5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Phone Number</label>
                                            <input type="text" name="kcb_phone_number"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="0712345678">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                            <input type="text" name="kcb_account_number"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="Optional">
                                        </div>
                                    </div>

                                    <!-- Paybill Fields -->
                                    <div id="paybill_fields" class="hidden space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Paybill Number</label>
                                            <input type="text" name="paybill_number"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="e.g., 522522">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Account Number</label>
                                            <input type="text" name="paybill_account_number"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="Student number or invoice number">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Transaction Code</label>
                                            <input type="text" name="paybill_transaction_code"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="MPESA transaction code">
                                        </div>
                                    </div>

                                    <!-- Bank Deposit Fields -->
                                    <div id="bank_fields" class="hidden space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Bank Name</label>
                                            <input type="text" name="bank_name"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="e.g., KCB, Equity">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Branch</label>
                                            <input type="text" name="bank_branch"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="e.g., Moi Avenue">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Deposit Slip Number</label>
                                            <input type="text" name="deposit_slip_number"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   placeholder="Slip reference number">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Deposit Date</label>
                                            <input type="date" name="deposit_date" value="{{ date('Y-m-d') }}"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Payment Date & Time -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                    Payment Date & Time
                                </h4>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Date</label>
                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Time</label>
                                        <input type="time" name="payment_time" value="{{ date('H:i') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Payer Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user text-primary mr-2"></i>
                                    Payer Information
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Payer Name</label>
                                        <input type="text" name="payer_name" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="Full name">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Payer Type</label>
                                        <select name="payer_type" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="student">Student</option>
                                            <option value="parent">Parent/Guardian</option>
                                            <option value="sponsor">Sponsor</option>
                                            <option value="employer">Employer</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Phone Number</label>
                                        <input type="text" name="payer_phone" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="0712345678">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="payer_email"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="email@example.com">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                                        <input type="text" name="payer_id_number"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="National ID/Passport">
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                                    Additional Information
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <input type="text" name="description"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="Brief description (optional)">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                        <textarea name="notes" rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Any additional notes..."></textarea>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="generate_receipt" id="generate_receipt" value="1" checked
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="generate_receipt" class="ml-2 text-sm text-gray-700">
                                            Generate receipt automatically
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="submitCreateForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Verify Payment Modal -->
<div id="verifyModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('verifyModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
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
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-700" id="verifyInfo"></span>
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
                <button onclick="closeModal('verifyModal')"
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
<div id="reverseModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('reverseModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
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
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                                <span class="text-sm text-orange-700" id="reverseInfo"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Reason for Reversal</label>
                            <textarea name="reason" required rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Explain why this payment is being reversed..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('reverseModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitReverseForm()"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
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
                                    <span class="text-gray-500 text-xs block" id="payerEmail"></span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="sendSms" name="send_sms" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="sendSms" class="ml-2 text-sm text-gray-700">
                                    Send SMS Receipt
                                    <span class="text-gray-500 text-xs block" id="payerPhone"></span>
                                </label>
                            </div>
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
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
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
                    <p class="text-center text-gray-600" id="deleteMessage">
                        Are you sure you want to delete this payment?
                    </p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitDeleteForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
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
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkVerifyMessage">
                        Verify <span id="bulkVerifyCount"></span> selected payment(s)?
                    </p>
                </div>
                <form id="bulkVerifyForm" method="POST" action="{{ route('admin.tvet.fee-payments.bulk.verify') }}">
                    @csrf
                    <div id="bulkVerifyInputs"></div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes</label>
                        <textarea name="verification_notes" rows="2" maxlength="500"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkVerifyModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkVerify()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Verify All
                </button>
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
        initializeQuickSearch();
        initializeInvoiceSelect();
        loadDailyCollectionChart();
    });

    // ============ CHART ============
    function loadDailyCollectionChart() {
        const ctx = document.getElementById('dailyCollectionChart')?.getContext('2d');
        if (!ctx) return;

        fetch('{{ route("admin.tvet.fee-payments.stats") }}?days=7')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'KES ' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
    }

    // ============ TABLE FUNCTIONS ============
    function viewPayment(paymentId) {
        window.location.href = `/admin/tvet/fee-payments/${paymentId}`;
    }

    function refreshTable() {
        location.reload();
    }

    function initializeQuickSearch() {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchParams = new URLSearchParams(window.location.search);
                    searchParams.set('search', this.value);
                    window.location.href = `${window.location.pathname}?${searchParams.toString()}`;
                }
            });
        }
    }

    // ============ INVOICE SELECT ============
    function initializeInvoiceSelect() {
        const invoiceSelect = document.getElementById('invoice_id');
        const studentId = document.getElementById('student_id');
        const enrollmentId = document.getElementById('enrollment_id');
        const amountInput = document.getElementById('amount');
        const maxAmount = document.getElementById('maxAmount');
        const preview = document.getElementById('invoicePreview');
        const previewNumber = document.getElementById('previewInvoiceNumber');
        const previewBalance = document.getElementById('previewBalance');

        if (invoiceSelect) {
            invoiceSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (selected.value) {
                    const balance = selected.dataset.balance;
                    studentId.value = selected.dataset.student;
                    enrollmentId.value = selected.dataset.enrollment;
                    amountInput.max = balance;
                    amountInput.value = balance;
                    maxAmount.textContent = `Max: KES ${parseFloat(balance).toFixed(2)}`;

                    previewNumber.textContent = selected.text.split(' - ')[0];
                    previewBalance.textContent = `Balance: KES ${parseFloat(balance).toFixed(2)}`;
                    preview.classList.remove('hidden');
                } else {
                    studentId.value = '';
                    enrollmentId.value = '';
                    amountInput.max = '';
                    amountInput.value = '';
                    maxAmount.textContent = '';
                    preview.classList.add('hidden');
                }
            });
        }
    }

    // ============ PAYMENT METHOD FIELDS ============
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        const kcbFields = document.getElementById('kcb_fields');
        const paybillFields = document.getElementById('paybill_fields');
        const bankFields = document.getElementById('bank_fields');

        [kcbFields, paybillFields, bankFields].forEach(f => f?.classList.add('hidden'));

        if (method === 'kcb_stk_push') {
            kcbFields?.classList.remove('hidden');
        } else if (method === 'paybill') {
            paybillFields?.classList.remove('hidden');
        } else if (method === 'bank_deposit') {
            bankFields?.classList.remove('hidden');
        }
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.payment-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;

        const bulkActionsBar = document.getElementById('bulkActionsBar');
        if (count > 0) {
            bulkActionsBar.classList.remove('hidden');
        } else {
            bulkActionsBar.classList.add('hidden');
        }
    }

    function toggleBulkActions() {
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        bulkActionsBar.classList.toggle('hidden');

        if (bulkActionsBar.classList.contains('hidden')) {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedPaymentIds() {
        const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkVerify() {
        const ids = getSelectedPaymentIds();
        if (ids.length === 0) {
            alert('Please select at least one payment');
            return;
        }

        document.getElementById('bulkVerifyCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkVerifyInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkVerifyModal');
    }

    function submitBulkVerify() {
        document.getElementById('bulkVerifyForm').submit();
    }

    function bulkSendReceipts() {
        const ids = getSelectedPaymentIds();
        if (ids.length === 0) {
            alert('Please select at least one payment');
            return;
        }

        if (confirm(`Send receipts for ${ids.length} payment(s)?`)) {
            // Implement bulk send receipts
            alert('Bulk send receipts coming soon');
        }
    }

    // ============ ACTION MENU ============
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

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // ============ PAYMENT ACTIONS ============
    function showReceipt(paymentId) {
        window.open(`/admin/tvet/fee-payments/${paymentId}/receipt`, '_blank');
    }

    function downloadReceipt(paymentId) {
        window.open(`/admin/tvet/fee-payments/${paymentId}/download-receipt`, '_blank');
    }

    function verifyPayment(paymentId) {
        fetch(`/admin/tvet/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('verifyInfo').textContent =
                        `Verify payment: ${payment.receipt_number} - KES ${parseFloat(payment.amount).toFixed(2)}`;
                    document.getElementById('verifyForm').action = `/admin/tvet/fee-payments/${paymentId}/verify`;
                    openModal('verifyModal');
                }
            });
    }

    function submitVerifyForm() {
        document.getElementById('verifyForm').submit();
    }

    function reversePayment(paymentId) {
        fetch(`/admin/tvet/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('reverseInfo').textContent =
                        `Reverse payment: ${payment.receipt_number} - KES ${parseFloat(payment.amount).toFixed(2)}`;
                    document.getElementById('reverseForm').action = `/admin/tvet/fee-payments/${paymentId}/reverse`;
                    openModal('reverseModal');
                }
            });
    }

    function submitReverseForm() {
        document.getElementById('reverseForm').submit();
    }

    function sendReceipt(paymentId) {
        fetch(`/admin/tvet/fee-payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data.fee_payment;
                    document.getElementById('sendReceiptInfo').textContent =
                        `Send receipt for: ${payment.receipt_number}`;
                    document.getElementById('payerEmail').textContent = payment.payer_email || 'No email';
                    document.getElementById('payerPhone').textContent = payment.payer_phone;
                    document.getElementById('sendReceiptForm').action = `/admin/tvet/fee-payments/${paymentId}/send-receipt`;
                    openModal('sendReceiptModal');
                }
            });
    }

    function submitSendReceiptForm() {
        document.getElementById('sendReceiptForm').submit();
    }

    function deletePayment(paymentId) {
        document.getElementById('deleteForm').action = `/admin/tvet/fee-payments/${paymentId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    function submitCreateForm() {
        document.getElementById('createForm').submit();
    }

    // ============ EXPORT ============
    function exportToExcel() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.tvet.fee-payments.export") }}';
        form.innerHTML = `
            @csrf
            <input type="hidden" name="format" value="csv">
            ${Array.from(document.querySelectorAll('#filterForm select, #filterForm input')).map(el =>
                `<input type="hidden" name="${el.name}" value="${el.value || ''}">`
            ).join('')}
        `;
        document.body.appendChild(form);
        form.submit();
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
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>

<style>
    #feePaymentsTable {
        min-width: 1600px;
    }

    @media (max-width: 768px) {
        #feePaymentsTable {
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

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
