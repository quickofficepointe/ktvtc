@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Fees')
@section('subtitle', 'Manage student fees, invoices, and payments')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Student Fees</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportToExcel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>
    <button onclick="openModal('generateFeesModal', '4xl')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-bolt"></i>
        <span>Generate Fees</span>
    </button>
    <a href="{{ route('admin.fees.student-fees.create') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>New Invoice</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Invoices</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['totalFees'] }}</p>

            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
             <span>KES {{ number_format($stats['totalAmount'], 2) }}</span>

            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Amount Paid</p>
              <p class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format($stats['totalPaid'], 2) }}</p>

            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-percentage text-green-600 mr-1"></i>
               <span>{{ $stats['totalAmount'] > 0 ? round(($stats['totalPaid'] / $stats['totalAmount']) * 100, 1) : 0 }}% collected</span>

            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Outstanding Balance</p>
               <p class="text-2xl font-bold text-red-600 mt-2">KES {{ number_format($stats['totalBalance'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-yellow-600 mr-1"></i>
               <span>{{ $stats['overdueFees'] }} overdue invoices</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Payments</p>
               <p class="text-2xl font-bold text-yellow-600 mt-2">{{ $stats['pendingFees'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-calendar text-yellow-600 mr-1"></i>
                <span>Awaiting payment</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
        <p class="text-sm text-gray-600 mt-1">Filter invoices by various criteria</p>
    </div>
    <div class="p-6">
        <form id="filterForm" method="GET" action="{{ route('admin.fees.student-fees.index') }}">
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

                <!-- Academic Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <select name="academic_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Years</option>
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Billing Month Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Billing Month</label>
                    <select name="billing_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Months</option>
                        @foreach($billingMonths as $month)
                            <option value="{{ $month }}" {{ request('billing_month') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        @foreach($paymentStatuses as $status)
                            <option value="{{ $status }}" {{ request('payment_status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fee Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fee Category</label>
                    <select name="fee_category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($feeCategories as $category)
                            <option value="{{ $category }}" {{ request('fee_category') == $category ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- CDACC Fees Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CDACC Fees</label>
                    <select name="is_cdacc_fee" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Fees</option>
                        <option value="1" {{ request('is_cdacc_fee') == '1' ? 'selected' : '' }}>CDACC Fees Only</option>
                        <option value="0" {{ request('is_cdacc_fee') == '0' ? 'selected' : '' }}>Non-CDACC Fees</option>
                    </select>
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
                    <option value="mark_paid">Mark as Paid</option>
                    <option value="apply_discount">Apply Discount</option>
                    <option value="delete">Delete</option>
                    <option value="generate_reminders">Generate Reminders</option>
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

<!-- Student Fees Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Student Invoices</h3>
                <p class="text-sm text-gray-600 mt-1">All student fees, invoices, and payment records</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search invoices..."
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
        <table class="w-full" id="studentFeesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Details</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Details</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($studentFees as $fee)
                <tr class="hover:bg-gray-50 transition-colors {{ $fee->payment_status == 'overdue' ? 'bg-red-50' : '' }}">
                    <td class="py-3 px-6">
                        <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $fee->id }}">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg {{ $fee->is_cdacc_fee ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900 block">{{ $fee->invoice_number }}</span>
                                <div class="text-xs text-gray-500">{{ $fee->invoice_date->format('M j, Y') }}</div>
                                @if($fee->is_cdacc_fee)
                                <span class="text-xs text-purple-600 font-medium">CDACC</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $fee->student->name ?? 'N/A' }}</p>
                            <div class="text-xs text-gray-600 mt-1">
                                {{ $fee->student->student_number ?? 'No ID' }} • {{ $fee->academic_year }}
                            </div>
                            @if($fee->registration)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $fee->registration->course->name ?? 'N/A' }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <p class="text-sm text-gray-800">{{ $fee->description }}</p>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $fee->fee_category == 'tuition' ? 'bg-blue-100 text-blue-800' :
                                       ($fee->fee_category == 'registration' ? 'bg-green-100 text-green-800' :
                                       ($fee->fee_category == 'examination' ? 'bg-purple-100 text-purple-800' :
                                       ($fee->fee_category == 'caution_money' ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-gray-100 text-gray-800'))) }}">
                                    {{ ucfirst(str_replace('_', ' ', $fee->fee_category)) }}
                                </span>
                                @if($fee->is_refundable)
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-undo-alt mr-1"></i>
                                    Refundable
                                </span>
                                @endif
                            </div>
                            @if($fee->billing_month)
                            <div class="text-xs text-gray-500">
                                {{ $fee->billing_month }}
                                @if($fee->month_number)
                                • Month {{ $fee->month_number }}/{{ $fee->total_installments }}
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <div class="text-sm font-medium text-gray-900">
                                KES {{ number_format($fee->total_amount, 2) }}
                            </div>
                            @if($fee->discount > 0)
                            <div class="text-xs text-green-600">
                                -KES {{ number_format($fee->discount, 2) }}
                                @if($fee->discount_reason)
                                <div class="text-xs text-gray-500">{{ $fee->discount_reason }}</div>
                                @endif
                            </div>
                            @endif
                            <div class="text-xs">
                                <span class="text-gray-600">Paid: </span>
                                <span class="font-medium {{ $fee->amount_paid > 0 ? 'text-green-600' : 'text-gray-600' }}">
                                    KES {{ number_format($fee->amount_paid, 2) }}
                                </span>
                            </div>
                            @if($fee->balance > 0)
                            <div class="text-xs text-red-600 font-medium">
                                Balance: KES {{ number_format($fee->balance, 2) }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-2">
                            @if($fee->payment_status == 'paid')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Paid
                            </span>
                            <div class="text-xs text-gray-500">
                                {{ $fee->paid_date ? $fee->paid_date->format('M j') : 'N/A' }}
                            </div>
                            @elseif($fee->payment_status == 'partial')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-percentage mr-1"></i>
                                Partial
                            </span>
                            <div class="text-xs text-gray-500">
                                {{ round($fee->payment_progress) }}% paid
                            </div>
                            @elseif($fee->payment_status == 'overdue')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Overdue
                            </span>
                            @if($fee->days_overdue > 0)
                            <div class="text-xs text-red-600">
                                {{ $fee->days_overdue }} days
                            </div>
                            @endif
                            @if($fee->late_fee_applied)
                            <div class="text-xs text-red-600">
                                +KES {{ number_format($fee->late_fee_amount, 2) }}
                            </div>
                            @endif
                            @elseif($fee->payment_status == 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>
                                Pending
                            </span>
                            @elseif($fee->payment_status == 'cancelled')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-ban mr-1"></i>
                                Cancelled
                            </span>
                            @elseif($fee->payment_status == 'waived')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-hand-holding-usd mr-1"></i>
                                Waived
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <div class="text-sm {{ $fee->due_date->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $fee->due_date->format('M j, Y') }}
                            </div>
                            @if($fee->due_date->isPast() && $fee->payment_status != 'paid')
                            <div class="text-xs text-red-600">
                                Due {{ $fee->due_date->diffForHumans() }}
                            </div>
                            @elseif($fee->due_date->diffInDays(now(), false) >= -7)
                            <div class="text-xs text-yellow-600">
                                Due in {{ $fee->due_date->diffForHumans() }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.student-fees.show', $fee) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.student-fees.edit', $fee) }}"
                               class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Invoice">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="recordPayment('{{ $fee->id }}')"
                                    class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $fee->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $fee->id }}" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($fee->payment_status != 'paid')
                                        <button onclick="markAsPaid('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-double mr-2"></i>
                                            Mark as Paid
                                        </button>
                                        @endif

                                        @if($fee->payment_status != 'paid' && $fee->payment_status != 'cancelled')
                                        <button onclick="applyDiscount('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-tag mr-2"></i>
                                            Apply Discount
                                        </button>
                                        @endif

                                        <button onclick="printInvoice('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-print mr-2"></i>
                                            Print Invoice
                                        </button>

                                        <button onclick="sendReminder('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-envelope mr-2"></i>
                                            Send Reminder
                                        </button>

                                        <div class="border-t border-gray-200 my-1"></div>

                                        @if($fee->payment_status != 'paid')
                                        <button onclick="cancelInvoice('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-ban mr-2"></i>
                                            Cancel Invoice
                                        </button>
                                        @endif

                                        <button onclick="deleteInvoice('{{ $fee->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete Invoice
                                        </button>
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
                            <i class="fas fa-file-invoice-dollar text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No student fees found</p>
                            <p class="text-gray-400 text-sm mt-1">Click "New Invoice" to create your first student fee</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    @if($studentFees->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $studentFees->firstItem() }}</span> to
                <span class="font-medium">{{ $studentFees->lastItem() }}</span> of
                <span class="font-medium">{{ $studentFees->total() }}</span> invoices
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $studentFees->currentPage() == 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ $studentFees->currentPage() }} of {{ $studentFees->lastPage() }}
                </span>
                <button onclick="nextPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $studentFees->currentPage() == $studentFees->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Generate Fees Modal -->
<div id="generateFeesModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('generateFeesModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Generate Fees from Fee Structure</h3>
                    <button onclick="closeModal('generateFeesModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="generateFeesForm" method="POST" action="{{ route('admin.fees.student-fees.generate') }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Student Selection -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Student Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                    <select name="student_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Student</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->name }} ({{ $student->student_number ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration *</label>
                                    <select name="registration_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Registration</option>
                                        <!-- Registration options will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fee Structure Selection -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Fee Structure</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fee Structure *</label>
                                    <select name="fee_structure_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Fee Structure</option>
                                        @foreach($feeStructures as $structure)
                                            <option value="{{ $structure->id }}">
                                                {{ $structure->course->name ?? 'N/A' }} - {{ $structure->campus->name ?? 'N/A' }}
                                                ({{ $structure->academic_year }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Plan *</label>
                                    <select name="payment_plan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Plan</option>
                                        <option value="monthly">Monthly Installments</option>
                                        <option value="quarterly">Quarterly Payments</option>
                                        <option value="semester">Semester Payments</option>
                                        <option value="full">Full Payment</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Details -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Academic Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                                    <input type="number" name="academic_year" required min="2000" max="2100" value="{{ date('Y') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Month *</label>
                                    <select name="start_month" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Month</option>
                                        @foreach($billingMonths as $month)
                                            <option value="{{ $month }}" {{ $month == date('F') ? 'selected' : '' }}>
                                                {{ $month }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Months</label>
                                    <input type="text" id="totalMonths" readonly
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Fee Preview</h4>
                            <div id="feePreview" class="space-y-3">
                                <p class="text-gray-600 text-sm">Select a fee structure and payment plan to see preview</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('generateFeesModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitForm('generateFeesForm', 'Generating Fees...')"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Generate Fees
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="recordPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('recordPaymentModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Record Payment</h3>
                    <button onclick="closeModal('recordPaymentModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="recordPaymentForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-700" id="paymentInvoiceInfo"></span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="text-gray-600">Balance: </span>
                                <span class="font-medium text-gray-800" id="paymentBalance"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount *</label>
                            <input type="number" name="payment_amount" required step="0.01" min="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                            <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                            <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                            <input type="text" name="reference_number" maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., MPESA confirmation code">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('recordPaymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitPaymentForm()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Apply Discount Modal -->
<div id="applyDiscountModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('applyDiscountModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Apply Discount</h3>
                    <button onclick="closeModal('applyDiscountModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="applyDiscountForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-700" id="discountInvoiceInfo"></span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="text-gray-600">Current Amount: </span>
                                <span class="font-medium text-gray-800" id="currentAmount"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Amount *</label>
                            <input type="number" name="discount_amount" required step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   id="discountAmount">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Reason *</label>
                            <input type="text" name="discount_reason" required maxlength="255"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Early payment discount, Scholarship, etc.">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('applyDiscountModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitDiscountForm()"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors">
                    Apply Discount
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
                <p class="text-gray-700" id="deleteMessage"></p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                <button onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Delete Invoice
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
    });

    // Action menu toggle
    function toggleActionMenu(feeId) {
        const menu = document.getElementById(`actionMenu-${feeId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${feeId}`) {
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

    // Record payment
    function recordPayment(feeId) {
        fetch(`/admin/student-fees/${feeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fee = data.data.student_fee;
                    document.getElementById('paymentInvoiceInfo').textContent = `Invoice: ${fee.invoice_number} - ${fee.description}`;
                    document.getElementById('paymentBalance').textContent = `KES ${parseFloat(fee.balance).toFixed(2)}`;

                    const form = document.getElementById('recordPaymentForm');
                    form.action = `/admin/student-fees/${feeId}/record-payment`;

                    // Set max payment amount
                    const paymentAmountInput = form.querySelector('input[name="payment_amount"]');
                    paymentAmountInput.max = fee.balance;
                    paymentAmountInput.placeholder = `Max: KES ${parseFloat(fee.balance).toFixed(2)}`;

                    openModal('recordPaymentModal', 'lg');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load fee details', 'error');
            });
    }

    // Apply discount
    function applyDiscount(feeId) {
        fetch(`/admin/student-fees/${feeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fee = data.data.student_fee;
                    document.getElementById('discountInvoiceInfo').textContent = `Invoice: ${fee.invoice_number} - ${fee.description}`;
                    document.getElementById('currentAmount').textContent = `KES ${parseFloat(fee.total_amount).toFixed(2)}`;

                    const form = document.getElementById('applyDiscountForm');
                    form.action = `/admin/student-fees/${feeId}/apply-discount`;

                    // Set max discount amount
                    const discountInput = document.getElementById('discountAmount');
                    discountInput.max = fee.amount;

                    openModal('applyDiscountModal', 'lg');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load fee details', 'error');
            });
    }

    // Mark as paid
    function markAsPaid(feeId) {
        if (confirm('Are you sure you want to mark this invoice as paid?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/student-fees/${feeId}/mark-paid`;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="_method" value="POST">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete invoice
    function deleteInvoice(feeId) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/student-fees/${feeId}`;
        document.getElementById('deleteMessage').textContent =
            'Are you sure you want to delete this invoice? This action cannot be undone.';
        openModal('deleteModal', 'md');
    }

    function confirmDelete() {
        document.getElementById('deleteForm').submit();
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
            showToast('Please select at least one invoice', 'error');
            return;
        }

        switch (action) {
            case 'mark_paid':
                if (confirm(`Mark ${selectedIds.length} invoice(s) as paid?`)) {
                    submitBulkAction(action, selectedIds);
                }
                break;

            case 'apply_discount':
                openBulkDiscountModal(selectedIds);
                break;

            case 'delete':
                if (confirm(`Delete ${selectedIds.length} invoice(s)? This cannot be undone.`)) {
                    submitBulkAction(action, selectedIds);
                }
                break;

            case 'generate_reminders':
                submitBulkAction(action, selectedIds);
                break;
        }
    }

    function submitBulkAction(action, ids) {
        fetch('/admin/student-fees/bulk-actions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                action: action,
                ids: ids,
                discount_amount: document.getElementById('bulkDiscountAmount')?.value || 0,
                discount_reason: document.getElementById('bulkDiscountReason')?.value || ''
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

    // Clear selection
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('bulkActionsBar').classList.add('hidden');
    }

    // Submit payment form
    function submitPaymentForm() {
        const form = document.getElementById('recordPaymentForm');
        const paymentAmount = parseFloat(form.querySelector('input[name="payment_amount"]').value);
        const balance = parseFloat(document.getElementById('paymentBalance').textContent.replace('KES ', ''));

        if (paymentAmount > balance) {
            showToast('Payment amount cannot exceed the balance', 'error');
            return;
        }

        form.submit();
    }

    // Submit discount form
    function submitDiscountForm() {
        const form = document.getElementById('applyDiscountForm');
        const discountAmount = parseFloat(form.querySelector('input[name="discount_amount"]').value);
        const currentAmount = parseFloat(document.getElementById('currentAmount').textContent.replace('KES ', ''));

        if (discountAmount > currentAmount) {
            showToast('Discount amount cannot exceed the invoice amount', 'error');
            return;
        }

        form.submit();
    }

    // Export to Excel
    function exportToExcel() {
        // Collect current filter values
        const filters = {
            student_id: document.querySelector('select[name="student_id"]')?.value,
            academic_year: document.querySelector('select[name="academic_year"]')?.value,
            billing_month: document.querySelector('select[name="billing_month"]')?.value,
            payment_status: document.querySelector('select[name="payment_status"]')?.value,
            fee_category: document.querySelector('select[name="fee_category"]')?.value,
            is_cdacc_fee: document.querySelector('select[name="is_cdacc_fee"]')?.value,
            date_from: document.querySelector('input[name="date_from"]')?.value,
            date_to: document.querySelector('input[name="date_to"]')?.value,
        };

        // Submit export form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.innerHTML = `
            @csrf
            <input type="hidden" name="format" value="excel">
            <input type="hidden" name="filters" value='${JSON.stringify(filters)}'>
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#studentFeesTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Pagination
    function prevPage() {
        @if($studentFees->currentPage() > 1)
            window.location.href = '{{ $studentFees->previousPageUrl() }}';
        @endif
    }

    function nextPage() {
        @if($studentFees->hasMorePages())
            window.location.href = '{{ $studentFees->nextPageUrl() }}';
        @endif
    }

    // Fee preview for generation
    document.querySelector('select[name="fee_structure_id"]')?.addEventListener('change', function() {
        const structureId = this.value;
        const paymentPlan = document.querySelector('select[name="payment_plan"]').value;

        if (structureId && paymentPlan) {
            fetch(`/admin/fee-structures/${structureId}/calculate-preview?plan=${paymentPlan}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateFeePreview(data.data);
                    }
                });
        }
    });

    document.querySelector('select[name="payment_plan"]')?.addEventListener('change', function() {
        const structureId = document.querySelector('select[name="fee_structure_id"]').value;
        const paymentPlan = this.value;

        if (structureId && paymentPlan) {
            fetch(`/admin/fee-structures/${structureId}/calculate-preview?plan=${paymentPlan}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateFeePreview(data.data);
                    }
                });
        }
    });

    function updateFeePreview(data) {
        const preview = document.getElementById('feePreview');
        preview.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 bg-white rounded-lg border">
                    <p class="text-sm text-gray-600">Total Course Fee</p>
                    <p class="text-lg font-bold text-primary">KES ${data.total_course_fee.toFixed(2)}</p>
                </div>
                <div class="p-3 bg-white rounded-lg border">
                    <p class="text-sm text-gray-600">Installments</p>
                    <p class="text-lg font-bold text-blue-600">${data.installments}</p>
                </div>
                <div class="p-3 bg-white rounded-lg border">
                    <p class="text-sm text-gray-600">Monthly Payment</p>
                    <p class="text-lg font-bold text-green-600">KES ${data.monthly_payment.toFixed(2)}</p>
                </div>
            </div>
            <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-700">
                    ${data.description}<br>
                    <small class="text-gray-500">Discount: ${(data.discount * 100).toFixed(0)}%</small>
                </p>
            </div>
        `;

        // Update total months
        document.getElementById('totalMonths').value = data.total_months || 'N/A';
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

    #studentFeesTable {
        min-width: 1400px;
    }

    @media (max-width: 768px) {
        #studentFeesTable {
            min-width: 100%;
        }
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
    }

    .progress-bar {
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        transition: width 0.3s ease;
    }
</style>
@endsection
