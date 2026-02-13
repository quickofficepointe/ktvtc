@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollment Details')
@section('subtitle', $enrollment->enrollment_number)

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $enrollment->enrollment_number }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.enrollments.edit', $enrollment) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Enrollment</span>
    </a>
    <a href="{{ route('admin.tvet.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="relative h-32 bg-gradient-to-r from-primary/20 to-primary/5">
        <div class="absolute -bottom-10 left-6 flex items-end space-x-6">
            <div class="w-24 h-24 rounded-xl bg-white shadow-lg flex items-center justify-center">
                <i class="fas fa-file-invoice text-4xl text-primary"></i>
            </div>
            <div class="mb-2">
                <h1 class="text-2xl font-bold text-gray-800">{{ $enrollment->enrollment_number }}</h1>
                <div class="flex items-center mt-2 space-x-3">
                    @if($enrollment->legacy_enrollment_code)
                        <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono text-gray-700">
                            Legacy: {{ $enrollment->legacy_enrollment_code }}
                        </span>
                    @endif
                    <span class="px-3 py-1 bg-blue-100 rounded-lg text-sm text-blue-700">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $enrollment->intake_period }} {{ $enrollment->intake_year }}
                    </span>
                    <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                        <i class="fas fa-book mr-1"></i>
                        {{ $enrollment->course->code ?? 'N/A' }}
                    </span>
                    @php
                        $statusColors = [
                            'registered' => 'blue',
                            'in_progress' => 'green',
                            'completed' => 'purple',
                            'dropped' => 'red',
                            'discontinued' => 'red',
                            'suspended' => 'yellow',
                            'deferred' => 'orange',
                            'transferred' => 'gray',
                        ];
                        $color = $statusColors[$enrollment->status] ?? 'gray';
                        $statusText = ucfirst(str_replace('_', ' ', $enrollment->status));
                    @endphp
                    <span class="px-3 py-1 bg-{{ $color }}-100 rounded-lg text-sm text-{{ $color }}-700">
                        <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                        {{ $statusText }}
                    </span>
                    @if($enrollment->is_active)
                        <span class="px-3 py-1 bg-green-100 rounded-lg text-sm text-green-700">
                            <i class="fas fa-check-circle mr-1"></i>
                            Active
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                            <i class="fas fa-minus-circle mr-1"></i>
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Student</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $enrollment->student->full_name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $enrollment->student->student_number ?? 'No ID' }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.tvet.students.show', $enrollment->student_id) }}"
               class="text-xs text-primary hover:text-primary-dark flex items-center">
                <i class="fas fa-external-link-alt mr-1"></i>
                View Student Profile
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Course</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $enrollment->course->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">Code: {{ $enrollment->course->code ?? 'N/A' }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-book text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-600">
                <i class="fas fa-tag mr-1"></i>
                {{ $enrollment->student_type }} | {{ $enrollment->sponsorship_type }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Financial Summary</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">KES {{ number_format($enrollment->total_course_fee ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Paid: KES {{ number_format($enrollment->amount_paid ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-600">Balance:</span>
                <span class="text-sm font-bold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    KES {{ number_format($enrollment->balance, 2) }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Progress</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $enrollment->completion_percentage ?? 0 }}%</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $enrollment->number_of_terms ?? 0 }} term(s) | {{ $enrollment->expected_duration_months ?? 0 }} months
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-primary rounded-full h-2"
                     style="width: {{ $enrollment->completion_percentage ?? 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px overflow-x-auto">
            <button onclick="switchTab('details')" id="tab-details-btn"
                    class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap">
                <i class="fas fa-info-circle mr-2"></i>
                Enrollment Details
            </button>
            <button onclick="switchTab('fees')" id="tab-fees-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-money-bill-wave mr-2"></i>
                Fee Items
                <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $enrollment->feeItems->count() ?? 0 }}</span>
            </button>
            <button onclick="switchTab('payments')" id="tab-payments-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-credit-card mr-2"></i>
                Payments
                <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs">0</span>
            </button>
            <button onclick="switchTab('exam')" id="tab-exam-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-file-alt mr-2"></i>
                Examination
            </button>
            <button onclick="switchTab('certificate')" id="tab-certificate-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-certificate mr-2"></i>
                Certificate
            </button>
            <button onclick="switchTab('timeline')" id="tab-timeline-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-history mr-2"></i>
                Timeline
            </button>
        </nav>
    </div>

    <!-- Tab: Enrollment Details -->
    <div id="tab-details" class="tab-pane p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Basic Information
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Enrollment Number</dt>
                            <dd class="text-sm font-mono font-medium text-gray-900">{{ $enrollment->enrollment_number }}</dd>
                        </div>
                        @if($enrollment->legacy_enrollment_code)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Legacy Code</dt>
                            <dd class="text-sm font-mono text-gray-900">{{ $enrollment->legacy_enrollment_code }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Student</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $enrollment->student->full_name ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Course</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->course->name ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Campus</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->campus->name ?? 'Not Assigned' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Department</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->department->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Intake & Mode -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Intake & Study Mode
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Intake</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $enrollment->intake_period }} {{ $enrollment->intake_year }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Study Mode</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $enrollment->study_mode)) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Student Type</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($enrollment->student_type) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Sponsorship</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($enrollment->sponsorship_type) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Enrollment Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->enrollment_date->format('F d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Duration & Dates -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Duration & Dates
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Duration</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if($enrollment->expected_duration_months)
                                    {{ $enrollment->expected_duration_months }} months
                                @else
                                    Not specified
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Number of Terms</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->number_of_terms ?? 'Not specified' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Start Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->start_date?->format('F d, Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Expected End Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->expected_end_date?->format('F d, Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Actual End Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->actual_end_date?->format('F d, Y') ?? 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Status & Progress -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-flag text-primary mr-2"></i>
                    Status & Progress
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Status</dt>
                            <dd>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                                    {{ $statusText }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Active</dt>
                            <dd>
                                @if($enrollment->is_active)
                                    <span class="text-green-600">Yes</span>
                                @else
                                    <span class="text-red-600">No</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Completion</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $enrollment->completion_percentage ?? 0 }}%</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Fee Structure</dt>
                            <dd class="text-sm text-gray-900">
                                @if($enrollment->fee_structure_type)
                                    {{ ucfirst(str_replace('_', ' ', $enrollment->fee_structure_type)) }}
                                @else
                                    Not specified
                                @endif
                            </dd>
                        </div>
                        @if($enrollment->feeTemplate)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Fee Template</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->feeTemplate->name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Remarks -->
            @if($enrollment->remarks)
            <div class="lg:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Remarks
                </h4>
                <div class="bg-amber-50 rounded-lg p-6 border border-amber-100">
                    <p class="text-amber-800">{{ $enrollment->remarks }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Tab: Fee Items -->
    <div id="tab-fees" class="tab-pane hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-medium text-gray-800 flex items-center">
                <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                Fee Items
            </h4>
            <button onclick="openAddFeeItemModal()"
                    class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm transition-colors flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Fee Item
            </button>
        </div>

        @if($enrollment->feeItems && $enrollment->feeItems->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($enrollment->feeItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                                @if(!$item->is_required)
                                    <span class="text-xs text-gray-500">Optional</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $item->feeCategory->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">KES {{ number_format($item->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm font-medium">KES {{ number_format($item->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm">KES {{ number_format($item->amount_paid, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-medium {{ $item->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($item->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $item->term_label }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'paid' => 'green',
                                        'partially_paid' => 'yellow',
                                        'pending' => 'blue',
                                        'waived' => 'gray',
                                        'cancelled' => 'red',
                                    ];
                                    $statusColor = $statusColors[$item->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @if($item->balance > 0)
                                    <button onclick="recordPayment('{{ $item->id }}')"
                                            class="text-green-600 hover:text-green-800 text-sm">
                                        Record Payment
                                    </button>
                                    @endif
                                    @if($item->balance == $item->total_amount && $item->status == 'pending')
                                    <button onclick="waiveFeeItem('{{ $item->id }}')"
                                            class="text-gray-600 hover:text-gray-800 text-sm ml-2">
                                        Waive
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right font-medium text-gray-700">Totals:</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">KES {{ number_format($enrollment->total_course_fee, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">KES {{ number_format($enrollment->amount_paid, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-bold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($enrollment->balance, 2) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-money-bill-wave text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">No fee items have been added to this enrollment</p>
                <button onclick="openAddFeeItemModal()"
                        class="mt-4 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm inline-flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Fee Item
                </button>
            </div>
        @endif
    </div>

    <!-- Tab: Payments -->
    <div id="tab-payments" class="tab-pane hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-medium text-gray-800 flex items-center">
                <i class="fas fa-credit-card text-primary mr-2"></i>
                Payment History
            </h4>
            <button onclick="recordPayment()"
                    class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm transition-colors flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Record Payment
            </button>
        </div>

        <div class="text-center py-8">
            <i class="fas fa-credit-card text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">No payment records found</p>
            <p class="text-gray-400 text-sm mt-1">Payments module coming soon</p>
        </div>
    </div>

    <!-- Tab: Examination -->
    <div id="tab-exam" class="tab-pane hidden p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Exam Registration
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    @if($enrollment->requires_external_exam)
                        <dl class="grid grid-cols-1 gap-4">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Exam Body</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ strtoupper($enrollment->external_exam_body ?? 'Not specified') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Registration Number</dt>
                                <dd class="text-sm font-mono text-gray-900">
                                    {{ $enrollment->exam_registration_number ?? 'Not registered' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Registration Date</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $enrollment->exam_registration_date?->format('F d, Y') ?? 'Not registered' }}
                                </dd>
                            </div>
                        </dl>
                        @if(!$enrollment->exam_registration_number)
                            <div class="mt-6">
                                <button onclick="openExamRegistrationModal()"
                                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                                    Register for Exam
                                </button>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-500 text-center py-4">This enrollment does not require external examination</p>
                    @endif
                </div>
            </div>

            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-star text-primary mr-2"></i>
                    Results & Grades
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Final Grade</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $enrollment->final_grade ?? 'Not available' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Class Award</dt>
                            <dd class="text-sm text-gray-900">
                                {{ $enrollment->class_award ?? 'Not available' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Certificate -->
    <div id="tab-certificate" class="tab-pane hidden p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-certificate text-primary mr-2"></i>
                    Certificate Information
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    @if($enrollment->certificate_number)
                        <dl class="grid grid-cols-1 gap-4">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Certificate Number</dt>
                                <dd class="text-sm font-mono font-medium text-gray-900">{{ $enrollment->certificate_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Issue Date</dt>
                                <dd class="text-sm text-gray-900">{{ $enrollment->certificate_issue_date?->format('F d, Y') }}</dd>
                            </div>
                        </dl>
                        <div class="mt-6">
                            <button class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                                <i class="fas fa-download mr-2"></i>
                                Download Certificate
                            </button>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No certificate has been issued for this enrollment</p>
                        @if($enrollment->status == 'completed')
                            <div class="mt-4 text-center">
                                <button onclick="openCertificateModal()"
                                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                                    Issue Certificate
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Timeline -->
    <div id="tab-timeline" class="tab-pane hidden p-6">
        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
            <i class="fas fa-history text-primary mr-2"></i>
            Enrollment Timeline
        </h4>

        <div class="flow-root">
            <ul class="-mb-8">
                <li class="relative pb-8">
                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                    <div class="relative flex space-x-3">
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-plus text-green-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Enrollment Created</p>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{ $enrollment->created_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                            @if($enrollment->creator)
                                <p class="mt-1 text-xs text-gray-500">by {{ $enrollment->creator->name }}</p>
                            @endif
                        </div>
                    </div>
                </li>

                @if($enrollment->created_at != $enrollment->updated_at)
                <li class="relative pb-8">
                    <div class="relative flex space-x-3">
                        <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center">
                            <i class="fas fa-pen text-amber-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Last Updated</p>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{ $enrollment->updated_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                            @if($enrollment->updater)
                                <p class="mt-1 text-xs text-gray-500">by {{ $enrollment->updater->name }}</p>
                            @endif
                        </div>
                    </div>
                </li>
                @endif

                @if($enrollment->status == 'completed' && $enrollment->actual_end_date)
                <li class="relative">
                    <div class="relative flex space-x-3">
                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-purple-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Course Completed</p>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{ $enrollment->actual_end_date->format('F d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @if($enrollment->status != 'in_progress')
    <form action="{{ route('admin.tvet.enrollments.activate', $enrollment) }}" method="POST">
        @csrf
        <button type="submit"
                class="w-full p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
            <i class="fas fa-play-circle text-green-600 text-xl mb-2"></i>
            <p class="text-sm font-medium text-gray-900">Mark In Progress</p>
            <p class="text-xs text-gray-600 mt-1">Activate this enrollment</p>
        </button>
    </form>
    @endif

    @if($enrollment->status != 'completed' && $enrollment->status == 'in_progress')
    <form action="{{ route('admin.tvet.enrollments.complete', $enrollment) }}" method="POST">
        @csrf
        <button type="submit"
                class="w-full p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-left">
            <i class="fas fa-graduation-cap text-purple-600 text-xl mb-2"></i>
            <p class="text-sm font-medium text-gray-900">Mark Completed</p>
            <p class="text-xs text-gray-600 mt-1">Complete this enrollment</p>
        </button>
    </form>
    @endif

    @if($enrollment->status != 'suspended' && $enrollment->status == 'in_progress')
    <form action="{{ route('admin.tvet.enrollments.suspend', $enrollment) }}" method="POST">
        @csrf
        <button type="submit"
                class="w-full p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition-colors text-left"
                onclick="return confirm('Are you sure you want to suspend this enrollment?')">
            <i class="fas fa-pause-circle text-yellow-600 text-xl mb-2"></i>
            <p class="text-sm font-medium text-gray-900">Suspend</p>
            <p class="text-xs text-gray-600 mt-1">Temporarily suspend enrollment</p>
        </button>
    </form>
    @endif

    @if($enrollment->requires_external_exam && !$enrollment->exam_registration_number)
    <button onclick="openExamRegistrationModal()"
            class="w-full p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-left">
        <i class="fas fa-file-alt text-blue-600 text-xl mb-2"></i>
        <p class="text-sm font-medium text-gray-900">Register for Exam</p>
        <p class="text-xs text-gray-600 mt-1">Complete exam registration</p>
    </button>
    @endif
</div>

<!-- Add Fee Item Modal -->
<div id="addFeeItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('addFeeItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Add Fee Item</h3>
                    <button onclick="closeModal('addFeeItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.tvet.enrollments.fee-items.store', $enrollment) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Fee Category</label>
                            <select name="fee_category_id" id="modal_fee_category_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Category</option>
                                @foreach(App\Models\FeeCategory::where('is_active', true)->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Item Name</label>
                            <input type="text" name="item_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Amount (KES)</label>
                                <input type="number" name="amount" step="0.01" min="0" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input type="number" name="quantity" value="1" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Applicable Terms</label>
                            <select name="applicable_terms" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="all">All Terms</option>
                                <option value="1">Term 1 Only</option>
                                <option value="2">Term 2 Only</option>
                                <option value="3">Term 3 Only</option>
                                <option value="4">Term 4 Only</option>
                                <option value="1,2">Terms 1 & 2</option>
                                <option value="1,2,3">Terms 1-3</option>
                                <option value="1,2,3,4">Terms 1-4</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input type="date" name="due_date"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_required" value="1" checked
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label class="ml-2 text-sm text-gray-700">Required Fee</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('addFeeItemModal')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                            Add Fee Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Exam Registration Modal -->
<div id="examRegistrationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('examRegistrationModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Exam Registration</h3>
                    <button onclick="closeModal('examRegistrationModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.tvet.enrollments.register-exam', $enrollment) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Registration Number</label>
                            <input type="text" name="exam_registration_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Date</label>
                            <input type="date" name="exam_registration_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Body</label>
                            <select name="external_exam_body" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Exam Body</option>
                                <option value="nita">NITA</option>
                                <option value="cdacc">CDACC</option>
                                <option value="knec">KNEC</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('examRegistrationModal')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Certificate Modal -->
<div id="certificateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('certificateModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Issue Certificate</h3>
                    <button onclick="closeModal('certificateModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.tvet.enrollments.issue-certificate', $enrollment) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Certificate Number</label>
                            <input type="text" name="certificate_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Issue Date</label>
                            <input type="date" name="certificate_issue_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Final Grade</label>
                            <input type="text" name="final_grade"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Distinction">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Class Award</label>
                            <input type="text" name="class_award"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., First Class">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('certificateModal')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                            Issue Certificate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ TAB NAVIGATION ============
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById(`tab-${tabId}`).classList.remove('hidden');
        const activeBtn = document.getElementById(`tab-${tabId}-btn`);
        activeBtn.classList.add('active', 'border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }

    // ============ MODAL FUNCTIONS ============
    function openAddFeeItemModal() {
        openModal('addFeeItemModal');
    }

    function openExamRegistrationModal() {
        openModal('examRegistrationModal');
    }

    function openCertificateModal() {
        openModal('certificateModal');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ============ FEE ITEM ACTIONS ============
    function recordPayment(feeItemId) {
        // To be implemented with payments module
        alert('Payment recording coming soon');
    }

    function waiveFeeItem(feeItemId) {
        if (confirm('Are you sure you want to waive this fee item?')) {
            // To be implemented
            alert('Waive functionality coming soon');
        }
    }

    // ============ CHECK URL HASH ============
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.substring(1);
        if (hash && ['details', 'fees', 'payments', 'exam', 'certificate', 'timeline'].includes(hash)) {
            switchTab(hash);
        }
    });

    // Close modals when clicking escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>

<style>
    .tab-btn {
        transition: all 0.2s ease;
    }
    .tab-btn.active {
        border-bottom-width: 2px;
    }
    .tab-pane {
        transition: opacity 0.15s ease;
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    .hidden {
        display: none !important;
    }
    .required:after {
        content: " *";
        color: #EF4444;
    }
</style>
@endsection
