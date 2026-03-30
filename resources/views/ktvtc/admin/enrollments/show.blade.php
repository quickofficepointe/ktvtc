@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollment Details')
@section('subtitle', $enrollment->enrollment_number ?? 'Enrollment Details')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $enrollment->enrollment_number ?? 'Details' }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
       class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Enrollment</span>
    </a>
    <a href="{{ route('admin.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-primary/10 to-transparent px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-xl bg-primary-light flex items-center justify-center">
                    <i class="fas fa-book-open text-primary text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $enrollment->enrollment_number ?? 'N/A' }}</h2>
                        @if($enrollment->legacy_code)
                            <span class="px-3 py-1 bg-gray-100 rounded-lg text-xs font-mono text-gray-600">
                                Legacy: {{ $enrollment->legacy_code }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @php
                            $statusColors = [
                                'active' => 'green',
                                'completed' => 'purple',
                                'dropped' => 'red',
                                'suspended' => 'yellow',
                                'pending' => 'gray',
                            ];
                            $color = $statusColors[$enrollment->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst($enrollment->status) }}
                        </span>
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $enrollment->intake_month }} {{ $enrollment->intake_year }}
                        </span>
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-university mr-1"></i>
                            {{ $enrollment->campus_name ?? ($enrollment->campus->name ?? 'N/A') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                @if($enrollment->status != 'active')
                    <form action="{{ route('admin.enrollments.activate', $enrollment) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Activate this enrollment?')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm flex items-center">
                            <i class="fas fa-play-circle mr-2"></i>
                            Activate
                        </button>
                    </form>
                @endif
                @if($enrollment->status != 'completed')
                    <form action="{{ route('admin.enrollments.complete', $enrollment) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Mark as completed?')"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Complete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50">
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-2">
                    <i class="fas fa-user-graduate text-blue-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Student</p>
                    <p class="text-xs font-semibold text-gray-800">{{ $enrollment->student_name ?? ($enrollment->student->full_name ?? 'N/A') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mr-2">
                    <i class="fas fa-book text-green-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Course</p>
                    <p class="text-xs font-semibold text-gray-800">{{ $enrollment->course_name ?? ($enrollment->course->name ?? 'N/A') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mr-2">
                    <i class="fas fa-money-bill-wave text-purple-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Fees</p>
                    <p class="text-xs font-semibold text-gray-800">KES {{ number_format($enrollment->total_fees, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center mr-2">
                    <i class="fas fa-clock text-amber-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Balance</p>
                    <p class="text-xs font-semibold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        KES {{ number_format($enrollment->balance, 2) }}
                    </p>
                </div>
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
            <button onclick="switchTab('payments')" id="tab-payments-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-credit-card mr-2"></i>
                Payment History
                <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $payments->count() ?? 0 }}</span>
            </button>
            <button onclick="switchTab('exams')" id="tab-exams-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap">
                <i class="fas fa-file-alt mr-2"></i>
                Exam Registrations
                <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $enrollment->exam_registrations_count ?? 0 }}</span>
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
                            <dd class="text-sm font-mono font-medium text-gray-900">{{ $enrollment->enrollment_number ?? 'N/A' }}</dd>
                        </div>
                        @if($enrollment->legacy_code)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Legacy Code</dt>
                            <dd class="text-sm font-mono text-gray-900">{{ $enrollment->legacy_code }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Student</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.students.show', $enrollment->student_id) }}" class="text-primary hover:underline">
                                    {{ $enrollment->student_name ?? ($enrollment->student->full_name ?? 'N/A') }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Course</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->course_name ?? ($enrollment->course->name ?? 'N/A') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Campus</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->campus_name ?? ($enrollment->campus->name ?? 'N/A') }}</dd>
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
                                {{ $enrollment->intake_month }} {{ $enrollment->intake_year }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Study Mode</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $enrollment->study_mode ?? 'N/A')) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Student Type</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($enrollment->student_type ?? 'N/A') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Sponsorship</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($enrollment->sponsorship_type ?? 'N/A') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Enrollment Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('F d, Y') : 'N/A' }}</dd>
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
                                {{ $enrollment->duration_months ? $enrollment->duration_months . ' months' : 'Not specified' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Start Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->start_date ? $enrollment->start_date->format('F d, Y') : 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Expected End Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->expected_end_date ? $enrollment->expected_end_date->format('F d, Y') : 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Actual End Date</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->actual_end_date ? $enrollment->actual_end_date->format('F d, Y') : 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Financial Summary -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Financial Summary
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Total Fees</dt>
                            <dd class="text-sm font-bold text-gray-900">KES {{ number_format($enrollment->total_fees, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Amount Paid</dt>
                            <dd class="text-sm font-medium text-green-600">KES {{ number_format($enrollment->amount_paid, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Balance</dt>
                            <dd class="text-sm font-bold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($enrollment->balance, 2) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Payment Progress</dt>
                            <dd class="text-sm text-gray-900">{{ $enrollment->payment_progress }}%</dd>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary rounded-full h-2" style="width: {{ $enrollment->payment_progress }}%"></div>
                        </div>
                    </dl>
                    @if($enrollment->balance > 0)
                    <div class="mt-4">
                        <a href="{{ route('admin.fee-payments.create', ['enrollment_id' => $enrollment->id]) }}"
                           class="w-full px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm flex items-center justify-center">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Record Payment
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- External Exam -->
            <div class="lg:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    External Examination
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Exam Required:</span>
                                @if($enrollment->requires_external_exam)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Yes
                                    </span>
                                    @if($enrollment->exam_body)
                                        <span class="ml-2 text-sm text-gray-900">({{ $enrollment->exam_body }})</span>
                                    @endif
                                @else
                                    <span class="ml-2 inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times mr-1"></i>
                                        No
                                    </span>
                                @endif
                            </p>
                        </div>
                        @if($enrollment->requires_external_exam && $enrollment->exam_registrations_count == 0)
                        <a href="{{ route('admin.exam-registrations.create', ['enrollment_id' => $enrollment->id]) }}"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                            <i class="fas fa-file-alt mr-2"></i>
                            Register for Exam
                        </a>
                        @endif
                    </div>
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

    <!-- Tab: Payment History -->
    <div id="tab-payments" class="tab-pane hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-medium text-gray-800 flex items-center">
                <i class="fas fa-credit-card text-primary mr-2"></i>
                Payment History
            </h4>
            @if($enrollment->balance > 0)
            <a href="{{ route('admin.fee-payments.create', ['enrollment_id' => $enrollment->id]) }}"
               class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Record Payment
            </a>
            @endif
        </div>

        @if($payments && $payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-green-600">KES {{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                    @if($payment->payment_method == 'cash') bg-yellow-100 text-yellow-800
                                    @elseif($payment->payment_method == 'mpesa') bg-green-100 text-green-800
                                    @elseif($payment->payment_method == 'bank') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($payment->payment_method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->transaction_code ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($payment->status == 'completed')
                                    @if($payment->is_verified)
                                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.fee-payments.show', $payment) }}"
                                   class="text-primary hover:text-primary-dark text-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-credit-card text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">No payments recorded for this enrollment</p>
                @if($enrollment->balance > 0)
                <a href="{{ route('admin.fee-payments.create', ['enrollment_id' => $enrollment->id]) }}"
                   class="mt-4 inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Record First Payment
                </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Tab: Exam Registrations -->
    <div id="tab-exams" class="tab-pane hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-medium text-gray-800 flex items-center">
                <i class="fas fa-file-alt text-primary mr-2"></i>
                Exam Registrations
            </h4>
            @if($enrollment->requires_external_exam)
            <a href="{{ route('admin.exam-registrations.create', ['enrollment_id' => $enrollment->id]) }}"
               class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                New Exam Registration
            </a>
            @endif
        </div>

        @if($enrollment->examRegistrations && $enrollment->examRegistrations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam Body</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($enrollment->examRegistrations as $exam)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ $exam->exam_body }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $exam->registration_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $exam->exam_date ? $exam->exam_date->format('d/m/Y') : 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'yellow',
                                        'registered' => 'green',
                                        'completed' => 'purple',
                                        'failed' => 'red',
                                    ];
                                    $color = $statusColors[$exam->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($exam->result)
                                    <span class="text-sm font-medium {{ $exam->result == 'Pass' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $exam->result }}
                                        @if($exam->grade)
                                            ({{ $exam->grade }})
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($exam->certificate_number)
                                    <span class="text-xs text-green-600">
                                        <i class="fas fa-certificate mr-1"></i>
                                        Issued
                                    </span>
                                @else
                                    <span class="text-gray-400">Not issued</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.exam-registrations.show', $exam) }}"
                                   class="text-primary hover:text-primary-dark text-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-file-alt text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">No exam registrations found for this enrollment</p>
                @if($enrollment->requires_external_exam)
                <a href="{{ route('admin.exam-registrations.create', ['enrollment_id' => $enrollment->id]) }}"
                   class="mt-4 inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Register for Exam
                </a>
                @endif
            </div>
        @endif
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
    <a href="{{ route('admin.fee-payments.create', ['enrollment_id' => $enrollment->id]) }}"
       class="block p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors">
        <i class="fas fa-credit-card text-green-600 text-xl mb-2"></i>
        <p class="text-sm font-medium text-gray-900">Record Payment</p>
        <p class="text-xs text-gray-600 mt-1">Add a new payment for this enrollment</p>
    </a>

    @if($enrollment->requires_external_exam)
    <a href="{{ route('admin.exam-registrations.create', ['enrollment_id' => $enrollment->id]) }}"
       class="block p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
        <i class="fas fa-file-alt text-blue-600 text-xl mb-2"></i>
        <p class="text-sm font-medium text-gray-900">Exam Registration</p>
        <p class="text-xs text-gray-600 mt-1">Register student for external exam</p>
    </a>
    @endif

    @if($enrollment->status != 'completed')
    <form action="{{ route('admin.enrollments.complete', $enrollment) }}" method="POST">
        @csrf
        <button type="submit" onclick="return confirm('Mark this enrollment as completed?')"
                class="w-full p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-left">
            <i class="fas fa-check-circle text-purple-600 text-xl mb-2"></i>
            <p class="text-sm font-medium text-gray-900">Mark Completed</p>
            <p class="text-xs text-gray-600 mt-1">Complete this enrollment</p>
        </button>
    </form>
    @endif

    <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
       class="block p-4 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition-colors">
        <i class="fas fa-edit text-amber-600 text-xl mb-2"></i>
        <p class="text-sm font-medium text-gray-900">Edit Enrollment</p>
        <p class="text-xs text-gray-600 mt-1">Update enrollment details</p>
    </a>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById(`tab-${tabId}`).classList.remove('hidden');
        const activeBtn = document.getElementById(`tab-${tabId}-btn`);
        activeBtn.classList.add('active', 'border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }

    // Check URL hash
    const hash = window.location.hash.substring(1);
    if (hash && ['details', 'payments', 'exams', 'timeline'].includes(hash)) {
        switchTab(hash);
    }
</script>

<style>
    .tab-btn {
        transition: all 0.2s ease;
    }
    .tab-btn.active {
        border-bottom-width: 2px;
    }
</style>
@endsection
