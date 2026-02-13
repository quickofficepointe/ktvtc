@extends('ktvtc.admin.layout.adminlayout')

@section('title', $courseFeeTemplate->name)
@section('subtitle', 'Course fee template details and fee breakdown')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fees</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Course Fee Templates</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $courseFeeTemplate->code ?? 'Details' }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.course-fee-templates.edit', $courseFeeTemplate) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Template</span>
    </a>
    <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Templates</span>
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
                <h1 class="text-2xl font-bold text-gray-800">{{ $courseFeeTemplate->name }}</h1>
                <div class="flex items-center mt-2 space-x-3">
                    @if($courseFeeTemplate->code)
                        <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono text-gray-700">
                            {{ $courseFeeTemplate->code }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                        @if($courseFeeTemplate->exam_type == 'nita') bg-blue-100 text-blue-800
                        @elseif($courseFeeTemplate->exam_type == 'cdacc') bg-green-100 text-green-800
                        @elseif($courseFeeTemplate->exam_type == 'school_assessment') bg-amber-100 text-amber-800
                        @else bg-purple-100 text-purple-800
                        @endif">
                        <i class="fas
                            @if($courseFeeTemplate->exam_type == 'nita') fa-certificate
                            @elseif($courseFeeTemplate->exam_type == 'cdacc') fa-graduation-cap
                            @elseif($courseFeeTemplate->exam_type == 'school_assessment') fa-school
                            @else fa-mixed
                            @endif mr-1">
                        </i>
                        {{ $courseFeeTemplate->exam_type_label }}
                    </span>
                    @if($courseFeeTemplate->campus)
                        <span class="px-3 py-1 bg-blue-100 rounded-lg text-sm text-blue-700">
                            <i class="fas fa-building mr-1"></i> {{ $courseFeeTemplate->campus->name }}
                        </span>
                    @else
                        <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                            <i class="fas fa-globe mr-1"></i> Global Template
                        </span>
                    @endif
                    @php
                        $statusColor = $courseFeeTemplate->is_active ? 'green' : 'gray';
                        $statusText = $courseFeeTemplate->is_active ? 'Active' : 'Inactive';
                    @endphp
                    <span class="px-3 py-1 bg-{{ $statusColor }}-100 rounded-lg text-sm text-{{ $statusColor }}-700">
                        <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i> {{ $statusText }}
                    </span>
                    @if($courseFeeTemplate->is_default)
                        <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                            <i class="fas fa-star mr-1"></i> Default Template
                        </span>
                    @endif
                    @if($courseFeeTemplate->is_public)
                        <span class="px-3 py-1 bg-green-100 rounded-lg text-sm text-green-700">
                            <i class="fas fa-globe mr-1"></i> Public
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                            <i class="fas fa-lock mr-1"></i> Private
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
                <p class="text-sm font-medium text-gray-600">Total Course Fee</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($courseFeeTemplate->total_amount, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Tuition:</span>
                <span class="font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_tuition_fee, 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm mt-1">
                <span class="text-gray-600">Other Fees:</span>
                <span class="font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_other_fees, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Course</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $courseFeeTemplate->course->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">Code: {{ $courseFeeTemplate->course->code ?? 'N/A' }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-book text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Duration</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">
                    @if($courseFeeTemplate->duration_months)
                        {{ $courseFeeTemplate->duration_months }} months
                    @else
                        Not specified
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ $courseFeeTemplate->total_terms }} term(s)</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-xl"></i>
            </div>
        </div>
        @if($courseFeeTemplate->intake_periods)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-600">
                <i class="fas fa-calendar-alt mr-1"></i>
                Intakes: {{ is_array($courseFeeTemplate->intake_periods) ? implode(', ', $courseFeeTemplate->intake_periods) : $courseFeeTemplate->intake_periods }}
            </p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Fee Items</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $courseFeeTemplate->feeItems->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Categories: {{ $courseFeeTemplate->feeItems->groupBy('fee_category_id')->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-list-ul text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="#fee-items-section" class="text-xs text-primary hover:text-primary-dark flex items-center">
                <i class="fas fa-arrow-down mr-1"></i>
                View fee breakdown
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Fee Breakdown Chart -->
    <div class="lg:col-span-1 bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-pie text-primary mr-2"></i>
            Fee Distribution
        </h3>
        <div class="h-64">
            <canvas id="feeDistributionChart"></canvas>
        </div>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                <span class="inline-block w-3 h-3 bg-blue-600 rounded-full mr-1"></span> Tuition
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1 ml-3"></span> Other Fees
            </p>
        </div>
    </div>

    <!-- Fee Items Summary -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-bar text-primary mr-2"></i>
            Fee Summary by Term
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        @for($i = 1; $i <= $courseFeeTemplate->total_terms; $i++)
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Term {{ $i }}</th>
                        @endfor
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $termTotals = [];
                        $categoryTotals = [];

                        foreach($courseFeeTemplate->feeItems as $item) {
                            $terms = $item->applicable_terms == 'all'
                                ? range(1, $courseFeeTemplate->total_terms)
                                : explode(',', $item->applicable_terms);

                            foreach($terms as $term) {
                                if (!isset($termTotals[$term])) $termTotals[$term] = 0;
                                $termTotals[$term] += $item->total_amount;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900">Total per Term</td>
                        @for($i = 1; $i <= $courseFeeTemplate->total_terms; $i++)
                            <td class="px-4 py-2 text-right text-sm text-gray-900">
                                KES {{ number_format($termTotals[$i] ?? 0, 2) }}
                            </td>
                        @endfor
                        <td class="px-4 py-2 text-right text-sm font-bold text-primary">
                            KES {{ number_format($courseFeeTemplate->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Fee Items Section -->
<div id="fee-items-section" class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-list text-primary mr-2"></i>
                Fee Items Breakdown
            </h3>
            <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                {{ $courseFeeTemplate->feeItems->count() }} items
            </span>
        </div>
    </div>

    <div class="p-6">
        @if($courseFeeTemplate->feeItems->count() > 0)
            @php
                $groupedItems = $courseFeeTemplate->feeItems->groupBy(function($item) {
                    return $item->feeCategory->name ?? 'Other';
                });
            @endphp

            @foreach($groupedItems as $categoryName => $items)
                <div class="mb-6 last:mb-0">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-primary mr-2"></span>
                        {{ $categoryName }}
                        <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs text-gray-600">
                            {{ $items->count() }} item(s)
                        </span>
                    </h4>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terms</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($items as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">{{ $item->item_name }}</span>
                                                @if(!$item->is_required)
                                                    <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Optional</span>
                                                @endif
                                            </div>
                                            @if($item->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ $item->description }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            KES {{ number_format($item->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            KES {{ number_format($item->total_amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $item->term_label }}
                                            @if($item->due_day_offset > 0)
                                                <span class="text-xs text-gray-500 block">
                                                    Due {{ $item->due_day_offset }} days after term start
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col space-y-1">
                                                @if($item->is_refundable)
                                                    <span class="inline-flex items-center text-xs text-purple-600">
                                                        <i class="fas fa-undo-alt mr-1"></i> Refundable
                                                    </span>
                                                @endif
                                                @if($item->is_advance_payment)
                                                    <span class="inline-flex items-center text-xs text-amber-600">
                                                        <i class="fas fa-clock mr-1"></i> Advance Payment
                                                    </span>
                                                @endif
                                                @if(!$item->is_visible_to_student)
                                                    <span class="inline-flex items-center text-xs text-gray-600">
                                                        <i class="fas fa-eye-slash mr-1"></i> Hidden from Students
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <!-- Grand Total -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex justify-end">
                    <div class="w-80">
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-600">Tuition Fees:</span>
                            <span class="text-sm font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_tuition_fee, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-600">Other Fees:</span>
                            <span class="text-sm font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_other_fees, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-base font-bold border-t border-gray-200 mt-2 pt-2">
                            <span class="text-gray-800">Total Course Fee:</span>
                            <span class="text-primary">KES {{ number_format($courseFeeTemplate->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-file-invoice text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">No fee items in this template</p>
                <a href="{{ route('admin.tvet.course-fee-templates.edit', $courseFeeTemplate) }}"
                   class="mt-4 inline-flex items-center text-primary hover:text-primary-dark">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add fee items
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Additional Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Description & Notes -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-info-circle text-primary mr-2"></i>
                Description & Notes
            </h3>
        </div>
        <div class="p-6">
            @if($courseFeeTemplate->description)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                    <p class="text-gray-600">{{ $courseFeeTemplate->description }}</p>
                </div>
            @endif

            @if($courseFeeTemplate->notes)
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Internal Notes</h4>
                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                        <p class="text-amber-800 text-sm">{{ $courseFeeTemplate->notes }}</p>
                    </div>
                </div>
            @endif

            @if(!$courseFeeTemplate->description && !$courseFeeTemplate->notes)
                <p class="text-gray-500 text-center py-4">No description or notes provided</p>
            @endif
        </div>
    </div>

    <!-- Audit Trail -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-history text-primary mr-2"></i>
                Audit Trail
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-plus text-blue-600 text-xs"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-800">Created</p>
                        <p class="text-xs text-gray-500">{{ $courseFeeTemplate->created_at->format('F d, Y \a\t h:i A') }}</p>
                        @if($courseFeeTemplate->creator)
                            <p class="text-xs text-gray-500 mt-1">by {{ $courseFeeTemplate->creator->name }}</p>
                        @endif
                    </div>
                </div>

                @if($courseFeeTemplate->created_at != $courseFeeTemplate->updated_at)
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-pen text-amber-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Last Updated</p>
                            <p class="text-xs text-gray-500">{{ $courseFeeTemplate->updated_at->format('F d, Y \a\t h:i A') }}</p>
                            @if($courseFeeTemplate->updater)
                                <p class="text-xs text-gray-500 mt-1">by {{ $courseFeeTemplate->updater->name }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Total Fee Items</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $courseFeeTemplate->feeItems->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Enrollments Using Template</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $courseFeeTemplate->enrollments->count() ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Enrollments -->
    @if($courseFeeTemplate->enrollments->count() > 0)
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-users text-primary mr-2"></i>
                Enrollments Using This Template
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollment #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Intake</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Fee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($courseFeeTemplate->enrollments->take(5) as $enrollment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">
                                            {{ substr($enrollment->student->first_name ?? 'S', 0, 1) }}{{ substr($enrollment->student->last_name ?? 'T', 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $enrollment->student->full_name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $enrollment->student->student_number ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $enrollment->enrollment_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ $enrollment->intake_period }} {{ $enrollment->intake_year }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'registered' => 'blue',
                                        'in_progress' => 'green',
                                        'completed' => 'purple',
                                        'dropped' => 'red',
                                        'suspended' => 'yellow',
                                    ];
                                    $color = $statusColors[$enrollment->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">KES {{ number_format($enrollment->total_course_fee, 2) }}</td>
                            <td class="px-6 py-4 text-sm">KES {{ number_format($enrollment->amount_paid, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-medium {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($enrollment->balance, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
                                   class="text-primary hover:text-primary-dark text-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($courseFeeTemplate->enrollments->count() > 5)
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-center">
                <a href="{{ route('admin.tvet.enrollments.index', ['fee_template_id' => $courseFeeTemplate->id]) }}"
                   class="text-sm text-primary hover:text-primary-dark">
                    View all {{ $courseFeeTemplate->enrollments->count() }} enrollments
                </a>
            </div>
        @endif
    </div>
    @endif
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-bolt text-primary mr-2"></i>
            Quick Actions
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.tvet.course-fee-templates.edit', $courseFeeTemplate) }}#add-fee-item"
               class="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
                <i class="fas fa-plus-circle text-green-600 text-xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Add Fee Item</p>
                <p class="text-xs text-gray-600 mt-1">Add another fee to this template</p>
            </a>

            @if(!$courseFeeTemplate->is_default)
            <form action="{{ route('admin.tvet.course-fee-templates.set-default', $courseFeeTemplate) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit"
                        class="w-full p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-left">
                    <i class="fas fa-star text-purple-600 text-xl mb-2"></i>
                    <p class="text-sm font-medium text-gray-900">Set as Default</p>
                    <p class="text-xs text-gray-600 mt-1">Make this the default template</p>
                </button>
            </form>
            @endif

            @if($courseFeeTemplate->is_active)
            <form action="{{ route('admin.tvet.course-fee-templates.deactivate', $courseFeeTemplate) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit"
                        class="w-full p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition-colors text-left">
                    <i class="fas fa-pause-circle text-yellow-600 text-xl mb-2"></i>
                    <p class="text-sm font-medium text-gray-900">Deactivate</p>
                    <p class="text-xs text-gray-600 mt-1">Disable this template</p>
                </button>
            </form>
            @else
            <form action="{{ route('admin.tvet.course-fee-templates.activate', $courseFeeTemplate) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit"
                        class="w-full p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
                    <i class="fas fa-check-circle text-green-600 text-xl mb-2"></i>
                    <p class="text-sm font-medium text-gray-900">Activate</p>
                    <p class="text-xs text-gray-600 mt-1">Enable this template</p>
                </button>
            </form>
            @endif

            <button onclick="duplicateTemplate('{{ $courseFeeTemplate->id }}')"
                    class="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-left">
                <i class="fas fa-copy text-blue-600 text-xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Duplicate</p>
                <p class="text-xs text-gray-600 mt-1">Create a copy of this template</p>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    // ============ CHARTS ============
    function initializeCharts() {
        // Fee Distribution Chart
        const feeCtx = document.getElementById('feeDistributionChart')?.getContext('2d');
        if (feeCtx) {
            new Chart(feeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Tuition Fees', 'Other Fees'],
                    datasets: [{
                        data: [
                            {{ $courseFeeTemplate->total_tuition_fee }},
                            {{ $courseFeeTemplate->total_other_fees }}
                        ],
                        backgroundColor: ['#3B82F6', '#10B981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: KES ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ============ TEMPLATE ACTIONS ============
    function duplicateTemplate(templateId) {
        if (confirm('Are you sure you want to duplicate this template?')) {
            window.location.href = `/admin/tvet/course-fee-templates/${templateId}/duplicate`;
        }
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
</style>
@endsection
