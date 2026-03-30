@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Payment Plans')
@section('subtitle', 'Manage payment plans for students')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Payment Plans</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createModal')"
            class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Payment Plan</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Plans</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalPlans ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Plans</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($activePlans ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-play-circle text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-500">
            {{ $approvedPlans ?? 0 }} approved
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Overdue Plans</p>
                <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($overduePlans ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Amount</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($totalAmount ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-coins text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-500">
            Paid: KES {{ number_format($totalPaid ?? 0, 2) }}
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

<!-- Plan Type Distribution -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Monthly</span>
        <p class="text-lg font-bold text-blue-600">{{ $monthlyPlans ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Quarterly</span>
        <p class="text-lg font-bold text-purple-600">{{ $quarterlyPlans ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Semester</span>
        <p class="text-lg font-bold text-amber-600">{{ $semesterPlans ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Annual</span>
        <p class="text-lg font-bold text-green-600">{{ $annualPlans ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Full Course</span>
        <p class="text-lg font-bold text-indigo-600">{{ $fullCoursePlans ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Custom</span>
        <p class="text-lg font-bold text-gray-600">{{ $customPlans ?? 0 }}</p>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter payment plans by student, status and type</p>
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
        <form id="filterForm" method="GET" action="{{ route('admin.tvet.payment-plans.index') }}">
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

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Plan Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plan Type</label>
                    <select name="plan_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($planTypes as $type)
                            <option value="{{ $type }}" {{ request('plan_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Approval Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Approved</label>
                    <select name="is_approved" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>Approved</option>
                        <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>Not Approved</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Plan code, name..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.payment-plans.index') }}"
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
                <span id="selectedCount">0</span> plan(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkApprove()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Approve</span>
            </button>
            <button onclick="bulkActivate()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-play-circle"></i>
                <span>Activate</span>
            </button>
            <button onclick="bulkGenerateInvoices()"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-file-invoice"></i>
                <span>Generate Invoices</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Payment Plans Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Payment Plans</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $paymentPlans->total() }} plans found</p>
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
        <table class="w-full" id="paymentPlansTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financials</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($paymentPlans as $plan)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewPlan({{ $plan->id }})">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="plan_ids[]" value="{{ $plan->id }}"
                               class="plan-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice-dollar text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $plan->plan_code }}</span>
                                <span class="text-xs text-gray-500 block">{{ $plan->plan_name }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $plan->student->full_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $plan->student->student_number ?? 'No ID' }}</p>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $plan->enrollment->course->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $plan->enrollment->course->code ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            @if($plan->plan_type == 'monthly') bg-blue-100 text-blue-800
                            @elseif($plan->plan_type == 'quarterly') bg-purple-100 text-purple-800
                            @elseif($plan->plan_type == 'semester') bg-amber-100 text-amber-800
                            @elseif($plan->plan_type == 'annual') bg-green-100 text-green-800
                            @elseif($plan->plan_type == 'full_course') bg-indigo-100 text-indigo-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $plan->plan_type)) }}
                        </span>
                        <span class="text-xs text-gray-500 block mt-1">{{ $plan->number_of_installments }} installments</span>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">KES {{ number_format($plan->total_amount, 2) }}</p>
                            @if($plan->discount_amount > 0)
                                <span class="text-xs text-green-600">Discount: KES {{ number_format($plan->discount_amount, 2) }}</span>
                            @endif
                            <div class="flex items-center mt-1">
                                <span class="text-xs font-medium {{ $plan->balance > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                    Balance: KES {{ number_format($plan->balance, 2) }}
                                </span>
                            </div>
                            <div class="w-16 h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-1 bg-primary rounded-full" style="width: {{ $plan->completion_percentage }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'draft' => 'gray',
                                'pending_approval' => 'yellow',
                                'approved' => 'blue',
                                'active' => 'green',
                                'completed' => 'purple',
                                'cancelled' => 'red',
                                'suspended' => 'orange',
                                'defaulted' => 'red',
                            ];
                            $color = $statusColors[$plan->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                        </span>
                        @if($plan->is_approved)
                            <span class="inline-flex items-center text-xs text-green-600 ml-1">
                                <i class="fas fa-check-circle mr-1"></i>
                                Approved
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="space-y-1">
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Start: {{ $plan->start_date->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-600">
                                <i class="fas fa-calendar-check mr-1"></i>
                                End: {{ $plan->end_date->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                First: {{ $plan->first_payment_date->format('d/m/Y') }}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewPlan({{ $plan->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(in_array($plan->status, ['draft', 'pending_approval']))
                            <button onclick="editPlan({{ $plan->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Plan">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $plan->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $plan->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($plan->status == 'draft')
                                        <button onclick="submitForApproval('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Submit for Approval
                                        </button>
                                        @endif

                                        @if($plan->status == 'pending_approval' && !$plan->is_approved)
                                        <button onclick="approvePlan('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Approve Plan
                                        </button>
                                        <button onclick="rejectPlan('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-times-circle mr-2"></i>
                                            Reject Plan
                                        </button>
                                        @endif

                                        @if($plan->status == 'approved' && $plan->is_approved)
                                        <button onclick="activatePlan('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Activate Plan
                                        </button>
                                        @endif

                                        @if($plan->status == 'active')
                                        <button onclick="generateInvoices('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-invoice mr-2"></i>
                                            Generate Invoices
                                        </button>
                                        <button onclick="viewInstallments('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-layer-group mr-2"></i>
                                            View Installments
                                        </button>
                                        @endif

                                        <hr class="my-1 border-gray-200">

                                        <a href="{{ route('admin.tvet.payment-plans.show', $plan) }}#agreement"
                                           class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Print Agreement
                                        </a>

                                        <hr class="my-1 border-gray-200">

                                        @if(in_array($plan->status, ['draft', 'pending_approval']))
                                        <button onclick="deletePlan('{{ $plan->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete Plan
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
                            <i class="fas fa-file-invoice-dollar text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payment plans found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating a new payment plan</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Create Payment Plan
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($paymentPlans instanceof \Illuminate\Pagination\LengthAwarePaginator && $paymentPlans->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $paymentPlans->firstItem() }}</span> to
                <span class="font-medium">{{ $paymentPlans->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($paymentPlans->total()) }}</span> plans
            </div>
            <div class="flex items-center space-x-2">
                {{ $paymentPlans->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Payment Plan</h3>
                        <p class="text-sm text-gray-600">Create a new payment plan for a student</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.payment-plans.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Student & Enrollment -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                                    Student & Enrollment
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Student</label>
                                        <select name="student_id" id="student_id" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                onchange="loadEnrollments()">
                                            <option value="">Select Student</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ $student->full_name }} ({{ $student->student_number ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Enrollment</label>
                                        <select name="enrollment_id" id="enrollment_id" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                onchange="loadEnrollmentDetails()">
                                            <option value="">Select Enrollment</option>
                                        </select>
                                    </div>

                                    <div id="enrollmentPreview" class="hidden bg-blue-50 p-4 rounded-lg">
                                        <p class="text-sm font-medium text-blue-800" id="previewCourse"></p>
                                        <p class="text-xs text-blue-600" id="previewTotalFee"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Plan Details -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-cog text-primary mr-2"></i>
                                    Plan Details
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Plan Name</label>
                                        <input type="text" name="plan_name" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Monthly Payment Plan">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Plan Type</label>
                                        <select name="plan_type" id="plan_type" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                onchange="updateInstallmentFrequency()">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="semester">Semester</option>
                                            <option value="annual">Annual</option>
                                            <option value="full_course">Full Course (One Payment)</option>
                                            <option value="custom">Custom Schedule</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Summary -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-calculator text-primary mr-2"></i>
                                    Financial Summary
                                </h4>

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Course Fee:</span>
                                        <span class="font-medium" id="displayCourseFee">KES 0.00</span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount Amount</label>
                                        <input type="number" name="discount_amount" id="discount_amount" value="0" min="0" step="0.01"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               onchange="calculateNetAmount()">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount Reason</label>
                                        <input type="text" name="discount_reason"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Early payment, Scholarship">
                                    </div>
                                    <div class="flex justify-between font-medium pt-2 border-t">
                                        <span class="text-sm text-gray-700">Net Amount:</span>
                                        <span class="text-primary" id="netAmount">KES 0.00</span>
                                    </div>
                                    <input type="hidden" name="total_amount" id="total_amount">
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Installment Configuration -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-layer-group text-primary mr-2"></i>
                                    Installment Configuration
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Number of Installments</label>
                                        <input type="number" name="number_of_installments" id="number_of_installments" value="1" min="1" max="60" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               onchange="calculateInstallments()">
                                    </div>

                                    <div id="frequency_container">
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Installment Frequency</label>
                                        <select name="installment_frequency" id="installment_frequency"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="semester">Every 6 Months</option>
                                            <option value="annual">Annual</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates Configuration -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                    Dates
                                </h4>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" value="{{ date('Y-m-d') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">End Date</label>
                                        <input type="date" name="end_date" id="end_date" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">First Payment Date</label>
                                        <input type="date" name="first_payment_date" id="first_payment_date" value="{{ date('Y-m-d') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-file-contract text-primary mr-2"></i>
                                    Terms & Conditions
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Late Fee Percentage (%)</label>
                                        <input type="number" name="late_fee_percentage" value="5.00" min="0" max="100" step="0.01"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                                        <input type="number" name="grace_period_days" value="7" min="0" max="30"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                                        <textarea name="terms_and_conditions" rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Enter terms and conditions...">Payment must be made by the due date. Late payments will incur a fee of the specified percentage.</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                        <textarea name="notes" rows="2"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Internal notes..."></textarea>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="auto_generate_invoices" id="auto_generate_invoices" value="1" checked
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="auto_generate_invoices" class="ml-2 text-sm text-gray-700">
                                            Auto-generate invoices before due date
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Installment Preview -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-6">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-list text-primary mr-2"></i>
                            Installment Schedule Preview
                        </h4>
                        <div id="installmentPreview" class="text-center text-gray-500">
                            Configure the plan to see installment schedule
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
                    Create Plan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('approvalModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Approve Payment Plan</h3>
                    <button onclick="closeModal('approvalModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="approvalForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-green-600 mr-2"></i>
                                <span class="text-sm text-green-700" id="approvalInfo"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Approval Notes</label>
                            <textarea name="approval_notes" rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('approvalModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitApprovalForm()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Approve Plan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('rejectModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Reject Payment Plan</h3>
                    <button onclick="closeModal('rejectModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-sm text-red-700" id="rejectInfo"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Rejection Reason</label>
                            <textarea name="rejection_reason" required rows="3" maxlength="500"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Explain why this plan is being rejected..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('rejectModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitRejectForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Reject Plan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div id="bulkApproveModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkApproveModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Approve Plans</h3>
                    <button onclick="closeModal('bulkApproveModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkApproveMessage">
                        Approve <span id="bulkApproveCount"></span> selected plan(s)?
                    </p>
                </div>
                <form id="bulkApproveForm" method="POST" action="{{ route('admin.tvet.payment-plans.bulk.approve') }}">
                    @csrf
                    <div id="bulkApproveInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkApproveModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkApprove()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Approve All
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Payment Plan</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteMessage">
                        Are you sure you want to delete this payment plan?
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
@endsection

@section('scripts')
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickSearch();
        updateInstallmentFrequency();
    });

    // ============ TABLE FUNCTIONS ============
    function viewPlan(planId) {
        window.location.href = `/admin/tvet/payment-plans/${planId}`;
    }

    function editPlan(planId) {
        window.location.href = `/admin/tvet/payment-plans/${planId}/edit`;
    }

    function viewInstallments(planId) {
        window.location.href = `/admin/tvet/payment-plans/${planId}#installments`;
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

    // ============ ENROLLMENT LOADING ============
    function loadEnrollments() {
        const studentId = document.getElementById('student_id').value;
        const enrollmentSelect = document.getElementById('enrollment_id');

        enrollmentSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`/admin/tvet/students/${studentId}/enrollments`)
            .then(response => response.json())
            .then(data => {
                enrollmentSelect.innerHTML = '<option value="">Select Enrollment</option>';
                if (data.enrollments && data.enrollments.length > 0) {
                    data.enrollments.forEach(enrollment => {
                        const option = document.createElement('option');
                        option.value = enrollment.id;
                        option.textContent = `${enrollment.course} (${enrollment.intake}) - KES ${enrollment.total_fee}`;
                        option.dataset.fee = enrollment.total_fee;
                        enrollmentSelect.appendChild(option);
                    });
                } else {
                    enrollmentSelect.innerHTML += '<option value="" disabled>No eligible enrollments found</option>';
                }
            });
    }

    function loadEnrollmentDetails() {
        const enrollmentSelect = document.getElementById('enrollment_id');
        const selected = enrollmentSelect.options[enrollmentSelect.selectedIndex];
        const preview = document.getElementById('enrollmentPreview');
        const courseEl = document.getElementById('previewCourse');
        const feeEl = document.getElementById('previewTotalFee');
        const displayFee = document.getElementById('displayCourseFee');
        const totalAmount = document.getElementById('total_amount');

        if (selected.value && selected.dataset.fee) {
            const fee = parseFloat(selected.dataset.fee);
            courseEl.textContent = selected.textContent.split(' - ')[0];
            feeEl.textContent = `Total Fee: KES ${fee.toFixed(2)}`;
            displayFee.textContent = `KES ${fee.toFixed(2)}`;
            totalAmount.value = fee;
            preview.classList.remove('hidden');
            calculateNetAmount();
        } else {
            preview.classList.add('hidden');
        }
    }

    // ============ INSTALLMENT CALCULATIONS ============
    function updateInstallmentFrequency() {
        const planType = document.getElementById('plan_type').value;
        const frequencyContainer = document.getElementById('frequency_container');
        const frequencySelect = document.getElementById('installment_frequency');

        if (planType === 'custom') {
            frequencyContainer.style.display = 'none';
        } else {
            frequencyContainer.style.display = 'block';
            if (planType === 'monthly') frequencySelect.value = 'monthly';
            else if (planType === 'quarterly') frequencySelect.value = 'quarterly';
            else if (planType === 'semester') frequencySelect.value = 'semester';
            else if (planType === 'annual') frequencySelect.value = 'annual';
            else if (planType === 'full_course') {
                document.getElementById('number_of_installments').value = 1;
            }
        }
    }

    function calculateNetAmount() {
        const totalFee = parseFloat(document.getElementById('total_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const netAmount = totalFee - discount;

        document.getElementById('netAmount').textContent = `KES ${netAmount.toFixed(2)}`;
    }

    function calculateInstallments() {
        const totalFee = parseFloat(document.getElementById('total_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const netAmount = totalFee - discount;
        const numInstallments = parseInt(document.getElementById('number_of_installments').value) || 1;
        const planType = document.getElementById('plan_type').value;
        const startDate = document.getElementById('first_payment_date').value;

        if (!startDate) {
            document.getElementById('installmentPreview').innerHTML = 'Please select a first payment date';
            return;
        }

        if (netAmount <= 0) {
            document.getElementById('installmentPreview').innerHTML = 'Please enter valid amounts';
            return;
        }

        const baseAmount = Math.floor((netAmount / numInstallments) * 100) / 100;
        const lastAmount = netAmount - (baseAmount * (numInstallments - 1));

        let installments = [];
        let dueDate = new Date(startDate);

        for (let i = 1; i <= numInstallments; i++) {
            const amount = i === numInstallments ? lastAmount : baseAmount;

            installments.push({
                number: i,
                amount: amount,
                dueDate: dueDate.toISOString().split('T')[0]
            });

            // Calculate next due date based on frequency
            if (planType === 'monthly') dueDate.setMonth(dueDate.getMonth() + 1);
            else if (planType === 'quarterly') dueDate.setMonth(dueDate.getMonth() + 3);
            else if (planType === 'semester') dueDate.setMonth(dueDate.getMonth() + 6);
            else if (planType === 'annual') dueDate.setFullYear(dueDate.getFullYear() + 1);
            else dueDate.setMonth(dueDate.getMonth() + 1);
        }

        let html = '<div class="space-y-2">';
        installments.forEach(inst => {
            html += `
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Installment ${inst.number}:</span>
                    <span class="font-medium">KES ${inst.amount.toFixed(2)}</span>
                    <span class="text-gray-500">Due: ${inst.dueDate}</span>
                </div>
            `;
        });
        html += '</div>';

        document.getElementById('installmentPreview').innerHTML = html;
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.plan-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.plan-checkbox:checked');
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
            const checkboxes = document.querySelectorAll('.plan-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedPlanIds() {
        const checkboxes = document.querySelectorAll('.plan-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkApprove() {
        const ids = getSelectedPlanIds();
        if (ids.length === 0) {
            alert('Please select at least one plan');
            return;
        }

        document.getElementById('bulkApproveCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkApproveInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkApproveModal');
    }

    function submitBulkApprove() {
        document.getElementById('bulkApproveForm').submit();
    }

    function bulkActivate() {
        const ids = getSelectedPlanIds();
        if (ids.length === 0) {
            alert('Please select at least one plan');
            return;
        }

        if (confirm(`Activate ${ids.length} selected plan(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.tvet.payment-plans.bulk.activate") }}';
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
    }

    function bulkGenerateInvoices() {
        const ids = getSelectedPlanIds();
        if (ids.length === 0) {
            alert('Please select at least one plan');
            return;
        }

        if (confirm(`Generate invoices for ${ids.length} selected plan(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.tvet.payment-plans.bulk.generate-invoices") }}';
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
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(planId) {
        const menu = document.getElementById(`actionMenu-${planId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${planId}`) {
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

    // ============ PLAN ACTIONS ============
    function submitForApproval(planId) {
        if (confirm('Submit this plan for approval?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/payment-plans/${planId}/submit-for-approval`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function approvePlan(planId) {
        fetch(`/admin/tvet/payment-plans/${planId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const plan = data.data.payment_plan;
                    document.getElementById('approvalInfo').textContent =
                        `Approve plan: ${plan.plan_code} - ${plan.plan_name}`;
                    document.getElementById('approvalForm').action = `/admin/tvet/payment-plans/${planId}/approve`;
                    openModal('approvalModal');
                }
            });
    }

    function submitApprovalForm() {
        document.getElementById('approvalForm').submit();
    }

    function rejectPlan(planId) {
        fetch(`/admin/tvet/payment-plans/${planId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const plan = data.data.payment_plan;
                    document.getElementById('rejectInfo').textContent =
                        `Reject plan: ${plan.plan_code} - ${plan.plan_name}`;
                    document.getElementById('rejectForm').action = `/admin/tvet/payment-plans/${planId}/reject`;
                    openModal('rejectModal');
                }
            });
    }

    function submitRejectForm() {
        document.getElementById('rejectForm').submit();
    }

    function activatePlan(planId) {
        if (confirm('Activate this payment plan?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/payment-plans/${planId}/activate`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function generateInvoices(planId) {
        if (confirm('Generate invoices for due installments?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/payment-plans/${planId}/generate-invoices`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deletePlan(planId) {
        document.getElementById('deleteForm').action = `/admin/tvet/payment-plans/${planId}`;
        document.getElementById('deleteMessage').textContent =
            'Are you sure you want to delete this payment plan? This action cannot be undone.';
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    function submitCreateForm() {
        document.getElementById('createForm').submit();
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
    #paymentPlansTable {
        min-width: 1600px;
    }

    @media (max-width: 768px) {
        #paymentPlansTable {
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
