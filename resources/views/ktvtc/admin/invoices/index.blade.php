@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Invoices')
@section('subtitle', 'Manage student invoices and billing')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Invoices</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openBulkGenerateModal()"
       class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-layer-group"></i>
        <span>Bulk Generate</span>
    </button>
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Create Invoice</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Invoices</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalInvoices ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-invoice text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Amount</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($totalAmount ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-coins text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Paid</p>
                <p class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format($totalPaid ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Outstanding</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">KES {{ number_format($totalBalance ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-500">
            <span class="text-red-600">{{ $overdueCount ?? 0 }} overdue</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Collection Rate</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Draft</span>
        <p class="text-lg font-bold text-gray-800">{{ $draftCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Sent</span>
        <p class="text-lg font-bold text-blue-600">{{ $sentCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Partial</span>
        <p class="text-lg font-bold text-amber-600">{{ $partialCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Paid</span>
        <p class="text-lg font-bold text-green-600">{{ $paidCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Overdue</span>
        <p class="text-lg font-bold text-red-600">{{ $overdueCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Cancelled</span>
        <p class="text-lg font-bold text-gray-600">{{ $cancelledCount ?? 0 }}</p>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter invoices by status, student, date range</p>
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
        <form id="filterForm" action="{{ route('admin.tvet.invoices.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

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

                <!-- Invoice Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Date From</label>
                    <input type="date" name="invoice_date_from" value="{{ request('invoice_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Invoice Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Date To</label>
                    <input type="date" name="invoice_date_to" value="{{ request('invoice_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Due Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date From</label>
                    <input type="date" name="due_date_from" value="{{ request('due_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Due Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date To</label>
                    <input type="date" name="due_date_to" value="{{ request('due_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Invoice #, Student name, Description..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.invoices.index') }}"
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
                <span id="selectedCount">0</span> invoice(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkSend()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-envelope"></i>
                <span>Send</span>
            </button>
            <button onclick="bulkPrint()"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>
            <button onclick="bulkMarkPaid()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Mark Paid</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">All Invoices</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $invoices->total() }} invoices found</p>
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
        <table class="w-full" id="invoicesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewInvoice({{ $invoice->id }})">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}"
                               class="invoice-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $invoice->invoice_number }}</span>
                                <p class="text-xs text-gray-500">{{ Str::limit($invoice->description, 30) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-gray-600">
                                    {{ substr($invoice->student->first_name ?? 'S', 0, 1) }}{{ substr($invoice->student->last_name ?? 'T', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $invoice->student->full_name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $invoice->student->student_number ?? 'No ID' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $invoice->enrollment->course->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->enrollment->course->code ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm {{ $invoice->due_date->isPast() && $invoice->status != 'paid' ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->due_date->isPast() && $invoice->status != 'paid')
                                <span class="ml-1 text-xs">(Overdue)</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-bold text-gray-900">KES {{ number_format($invoice->total_amount, 2) }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-medium text-green-600">KES {{ number_format($invoice->amount_paid, 2) }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-bold {{ $invoice->balance > 0 ? 'text-amber-600' : 'text-green-600' }}">
                            KES {{ number_format($invoice->balance, 2) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'draft' => 'gray',
                                'sent' => 'blue',
                                'partial' => 'yellow',
                                'paid' => 'green',
                                'overdue' => 'red',
                                'cancelled' => 'gray',
                            ];
                            $color = $statusColors[$invoice->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewInvoice({{ $invoice->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(!in_array($invoice->status, ['paid', 'cancelled']))
                            <button onclick="editInvoice({{ $invoice->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Invoice">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $invoice->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $invoice->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($invoice->status == 'draft')
                                        <button onclick="markAsSent('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Mark as Sent
                                        </button>
                                        @endif

                                        @if($invoice->status != 'paid')
                                        <button onclick="recordPayment({{ $invoice->id }})"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-money-bill-wave mr-2"></i>
                                            Record Payment
                                        </button>
                                        @endif

                                        <button onclick="sendInvoice('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-envelope mr-2"></i>
                                            Send Email
                                        </button>

                                        <button onclick="printInvoice('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-print mr-2"></i>
                                            Print Invoice
                                        </button>

                                        <button onclick="downloadPdf('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-download mr-2"></i>
                                            Download PDF
                                        </button>

                                        @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                        <hr class="my-1 border-gray-200">
                                        <button onclick="cancelInvoice('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-ban mr-2"></i>
                                            Cancel Invoice
                                        </button>
                                        @endif

                                        @if($invoice->status == 'cancelled' || $invoice->status == 'draft')
                                        <button onclick="deleteInvoice('{{ $invoice->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete
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
                    <td colspan="11" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-file-invoice text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No invoices found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first invoice</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Create Invoice
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator && $invoices->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $invoices->firstItem() }}</span> to
                <span class="font-medium">{{ $invoices->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($invoices->total()) }}</span> invoices
            </div>
            <div class="flex items-center space-x-2">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Create Invoice Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Invoice</h3>
                        <p class="text-sm text-gray-600">Generate a new invoice for a student</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.invoices.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Header Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Student</label>
                                <select name="student_id" id="student_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->full_name }} ({{ $student->student_number ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Enrollment -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Enrollment</label>
                                <select name="enrollment_id" id="enrollment_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Enrollment</option>
                                    @foreach($enrollments as $enrollment)
                                        <option value="{{ $enrollment->id }}" data-student="{{ $enrollment->student_id }}">
                                            {{ $enrollment->student->full_name ?? 'N/A' }} - {{ $enrollment->course->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Invoice Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Description</label>
                                <input type="text" name="description" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., Term 1 Fees - Diploma in IT">
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Due Date</label>
                                <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <input type="text" name="notes"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Optional notes">
                            </div>
                        </div>

                        <!-- Fee Items Section -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-medium text-gray-800 flex items-center">
                                    <i class="fas fa-list text-primary mr-2"></i>
                                    Invoice Items
                                </h4>
                                <button type="button" onclick="addInvoiceItem()"
                                        class="px-3 py-1 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Add Item
                                </button>
                            </div>

                            <div id="invoice-items-container">
                                <div class="grid grid-cols-12 gap-3 mb-2 text-xs font-medium text-gray-500">
                                    <div class="col-span-5">Description</div>
                                    <div class="col-span-2">Quantity</div>
                                    <div class="col-span-2">Unit Price</div>
                                    <div class="col-span-2">Total</div>
                                    <div class="col-span-1"></div>
                                </div>

                                <div id="invoice-items-list">
                                    <!-- Items will be added here dynamically -->
                                </div>

                                <div id="no-items-message" class="text-center py-4 text-gray-500 text-sm">
                                    No items added yet. Click "Add Item" to start.
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <div class="flex justify-end">
                                    <div class="w-64 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium" id="subtotal">KES 0.00</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Discount:</span>
                                            <span class="font-medium">
                                                <input type="number" name="discount" id="discount" value="0" min="0" step="0.01"
                                                       class="w-24 px-2 py-1 text-right border border-gray-300 rounded text-sm"
                                                       onchange="calculateTotals()">
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Tax:</span>
                                            <span class="font-medium">
                                                <input type="number" name="tax" id="tax" value="0" min="0" step="0.01"
                                                       class="w-24 px-2 py-1 text-right border border-gray-300 rounded text-sm"
                                                       onchange="calculateTotals()">
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-base font-bold pt-2 border-t">
                                            <span class="text-gray-800">Total:</span>
                                            <span class="text-primary" id="total">KES 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="discount_reason" id="discount_reason">
                            <input type="hidden" name="items_data" id="items_data">
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
                    Create Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Generate Modal -->
<div id="bulkGenerateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkGenerateModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Generate Invoices</h3>
                    <button onclick="closeModal('bulkGenerateModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="bulkGenerateForm" method="POST" action="{{ route('admin.tvet.invoices.bulk.generate') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Select Enrollments</label>
                            <select name="enrollment_ids[]" multiple size="5" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                @foreach($enrollments as $enrollment)
                                    <option value="{{ $enrollment->id }}">
                                        {{ $enrollment->student->full_name ?? 'N/A' }} - {{ $enrollment->course->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Description</label>
                            <input type="text" name="description" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Term 1 Fees">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Due Date</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkGenerateModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkGenerate()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-layer-group mr-2"></i>
                    Generate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('paymentModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Record Payment</h3>
                    <button onclick="closeModal('paymentModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="paymentForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                <input type="number" name="amount" id="payment_amount" step="0.01" min="0.01" required
                                       class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Payment Method</label>
                            <select name="payment_method" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="kcb_stk_push">KCB STK Push</option>
                                <option value="paybill">Paybill</option>
                                <option value="helb">HELB</option>
                                <option value="sponsor">Sponsor</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Payment Date</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference/Transaction ID</label>
                            <input type="text" name="reference"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., MPAESA123">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Payment notes..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('paymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitPaymentForm()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Record Payment
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Invoice</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this invoice?
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

<!-- Bulk Send Modal -->
<div id="bulkSendModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkSendModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Send Invoices</h3>
                    <button onclick="closeModal('bulkSendModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-envelope text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkSendModalMessage">
                        Send <span id="bulkSendCount"></span> invoice(s) to students?
                    </p>
                </div>
                <form id="bulkSendForm" method="POST" action="{{ route('admin.tvet.invoices.bulk.send') }}">
                    @csrf
                    <div id="bulkSendInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkSendModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkSend()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Mark Paid Modal -->
<div id="bulkMarkPaidModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkMarkPaidModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Mark as Paid</h3>
                    <button onclick="closeModal('bulkMarkPaidModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkMarkPaidModalMessage">
                        Mark <span id="bulkMarkPaidCount"></span> invoice(s) as paid?
                    </p>
                </div>
                <form id="bulkMarkPaidForm" method="POST" action="{{ route('admin.tvet.invoices.bulk.mark-paid') }}">
                    @csrf
                    <div id="bulkMarkPaidInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkMarkPaidModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkMarkPaid()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Mark Paid
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickSearch();
        initializeStudentEnrollmentSync();
        initializeInvoiceItems();
    });

    // ============ TABLE FUNCTIONS ============
    function viewInvoice(invoiceId) {
        window.location.href = `/admin/tvet/invoices/${invoiceId}`;
    }

    function editInvoice(invoiceId) {
        window.location.href = `/admin/tvet/invoices/${invoiceId}/edit`;
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

    // ============ STUDENT-ENROLLMENT SYNC ============
    function initializeStudentEnrollmentSync() {
        const studentSelect = document.getElementById('student_id');
        const enrollmentSelect = document.getElementById('enrollment_id');

        if (studentSelect && enrollmentSelect) {
            studentSelect.addEventListener('change', function() {
                const studentId = this.value;
                Array.from(enrollmentSelect.options).forEach(option => {
                    if (option.value === '') return;
                    if (option.dataset.student == studentId || !studentId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
                enrollmentSelect.value = '';
            });
        }
    }

    // ============ INVOICE ITEMS MANAGEMENT ============
    let itemCounter = 0;
    let items = [];

    function initializeInvoiceItems() {
        // Add first item by default
        addInvoiceItem();
    }

    function addInvoiceItem() {
        const container = document.getElementById('invoice-items-list');
        const noItemsMsg = document.getElementById('no-items-message');

        if (noItemsMsg) noItemsMsg.style.display = 'none';

        const itemId = 'item-' + itemCounter++;
        const itemDiv = document.createElement('div');
        itemDiv.id = itemId;
        itemDiv.className = 'grid grid-cols-12 gap-3 mb-3 items-center';

        itemDiv.innerHTML = `
            <div class="col-span-5">
                <input type="text" class="item-desc w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Description">
            </div>
            <div class="col-span-2">
                <input type="number" class="item-qty w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="1" min="1" onchange="calculateItemTotal(this)">
            </div>
            <div class="col-span-2">
                <input type="number" class="item-price w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="0" min="0" step="0.01" onchange="calculateItemTotal(this)">
            </div>
            <div class="col-span-2">
                <span class="item-total block px-3 py-2 text-sm font-medium">0.00</span>
            </div>
            <div class="col-span-1">
                <button type="button" onclick="removeInvoiceItem('${itemId}')" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        container.appendChild(itemDiv);
        items.push(itemId);
    }

    function removeInvoiceItem(itemId) {
        const item = document.getElementById(itemId);
        if (item) {
            item.remove();
            items = items.filter(id => id !== itemId);

            if (items.length === 0) {
                document.getElementById('no-items-message').style.display = 'block';
            }

            calculateTotals();
        }
    }

    function calculateItemTotal(element) {
        const itemDiv = element.closest('[id^="item-"]');
        const qty = parseFloat(itemDiv.querySelector('.item-qty').value) || 0;
        const price = parseFloat(itemDiv.querySelector('.item-price').value) || 0;
        const totalSpan = itemDiv.querySelector('.item-total');

        const total = qty * price;
        totalSpan.textContent = total.toFixed(2);

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;

        document.querySelectorAll('[id^="item-"]').forEach(itemDiv => {
            const qty = parseFloat(itemDiv.querySelector('.item-qty').value) || 0;
            const price = parseFloat(itemDiv.querySelector('.item-price').value) || 0;
            subtotal += qty * price;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const total = subtotal - discount + tax;

        document.getElementById('subtotal').textContent = 'KES ' + subtotal.toFixed(2);
        document.getElementById('total').textContent = 'KES ' + total.toFixed(2);
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
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
            const checkboxes = document.querySelectorAll('.invoice-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedInvoiceIds() {
        const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkSend() {
        const ids = getSelectedInvoiceIds();
        if (ids.length === 0) {
            alert('Please select at least one invoice');
            return;
        }

        document.getElementById('bulkSendCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkSendInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkSendModal');
    }

    function submitBulkSend() {
        document.getElementById('bulkSendForm').submit();
    }

    function bulkPrint() {
        const ids = getSelectedInvoiceIds();
        if (ids.length === 0) {
            alert('Please select at least one invoice');
            return;
        }

        // Open print page with selected IDs
        window.open(`/admin/tvet/invoices/print?ids=${ids.join(',')}`, '_blank');
    }

    function bulkMarkPaid() {
        const ids = getSelectedInvoiceIds();
        if (ids.length === 0) {
            alert('Please select at least one invoice');
            return;
        }

        document.getElementById('bulkMarkPaidCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkMarkPaidInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkMarkPaidModal');
    }

    function submitBulkMarkPaid() {
        document.getElementById('bulkMarkPaidForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(invoiceId) {
        const menu = document.getElementById(`actionMenu-${invoiceId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${invoiceId}`) {
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

    // ============ INVOICE ACTIONS ============
    function markAsSent(invoiceId) {
        if (confirm('Mark this invoice as sent?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/invoices/${invoiceId}/mark-as-sent`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function recordPayment(invoiceId) {
        document.getElementById('paymentForm').action = `/admin/tvet/invoices/${invoiceId}/mark-as-paid`;
        openModal('paymentModal');
    }

    function submitPaymentForm() {
        document.getElementById('paymentForm').submit();
    }

    function sendInvoice(invoiceId) {
        if (confirm('Send this invoice to the student\'s email?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/invoices/${invoiceId}/send`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function printInvoice(invoiceId) {
        window.open(`/admin/tvet/invoices/${invoiceId}/print`, '_blank');
    }

    function downloadPdf(invoiceId) {
        window.open(`/admin/tvet/invoices/${invoiceId}/download-pdf`, '_blank');
    }

    function cancelInvoice(invoiceId) {
        if (confirm('Are you sure you want to cancel this invoice?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/invoices/${invoiceId}/cancel`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteInvoice(invoiceId) {
        document.getElementById('deleteForm').action = `/admin/tvet/invoices/${invoiceId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    // ============ CREATE FORM SUBMISSION ============
    function submitCreateForm() {
        // Prepare items data
        const items = [];
        document.querySelectorAll('[id^="item-"]').forEach(itemDiv => {
            const description = itemDiv.querySelector('.item-desc').value;
            const quantity = itemDiv.querySelector('.item-qty').value;
            const unitPrice = itemDiv.querySelector('.item-price').value;

            if (description && quantity && unitPrice) {
                items.push({
                    description,
                    quantity,
                    unit_price: unitPrice
                });
            }
        });

        if (items.length === 0) {
            alert('Please add at least one item');
            return;
        }

        document.getElementById('items_data').value = JSON.stringify(items);
        document.getElementById('createForm').submit();
    }

    // ============ BULK GENERATE ============
    function openBulkGenerateModal() {
        openModal('bulkGenerateModal');
    }

    function submitBulkGenerate() {
        document.getElementById('bulkGenerateForm').submit();
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

    // Close modals when clicking escape key
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
    #invoicesTable {
        min-width: 1600px;
    }

    @media (max-width: 768px) {
        #invoicesTable {
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

    /* Multi-select styling */
    select[multiple] {
        min-height: 120px;
    }
</style>
@endsection
