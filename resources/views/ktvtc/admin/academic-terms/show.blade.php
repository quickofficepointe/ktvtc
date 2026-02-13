@extends('ktvtc.admin.layout.adminlayout')

@section('title', $academicTerm->name)
@section('subtitle', 'Academic term details and enrollments')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Academic</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Terms</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $academicTerm->code }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.academic-terms.edit', $academicTerm) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Term</span>
    </a>
    <a href="{{ route('admin.tvet.academic-terms.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Terms</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="relative h-32 bg-gradient-to-r from-primary/20 to-primary/5">
        <div class="absolute -bottom-10 left-6 flex items-end space-x-6">
            <div class="w-24 h-24 rounded-xl bg-white shadow-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-4xl text-primary"></i>
            </div>
            <div class="mb-2">
                <h1 class="text-2xl font-bold text-gray-800">{{ $academicTerm->name }}</h1>
                <div class="flex items-center mt-2 space-x-3">
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono text-gray-700">
                        {{ $academicTerm->code }}
                    </span>
                    @if($academicTerm->short_code)
                        <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                            {{ $academicTerm->short_code }}
                        </span>
                    @endif
                    @if($academicTerm->campus)
                        <span class="px-3 py-1 bg-blue-100 rounded-lg text-sm text-blue-700">
                            <i class="fas fa-building mr-1"></i> {{ $academicTerm->campus->name }}
                        </span>
                    @else
                        <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                            <i class="fas fa-globe mr-1"></i> Global Term
                        </span>
                    @endif
                    @php
                        $statusColor = $academicTerm->is_active ? 'green' : 'gray';
                        $statusText = $academicTerm->is_active ? 'Active' : 'Inactive';
                    @endphp
                    <span class="px-3 py-1 bg-{{ $statusColor }}-100 rounded-lg text-sm text-{{ $statusColor }}-700">
                        <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i> {{ $statusText }}
                    </span>
                    @if($academicTerm->is_current)
                        <span class="px-3 py-1 bg-green-100 rounded-lg text-sm text-green-700">
                            <i class="fas fa-play-circle mr-1"></i> Current Term
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Term Period</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $academicTerm->start_date->format('M d, Y') }}</p>
                <p class="text-sm text-gray-500">to {{ $academicTerm->end_date->format('M d, Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-calendar-week text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-clock mr-2 text-gray-400"></i>
                <span>{{ $academicTerm->start_date->diffInDays($academicTerm->end_date) }} days</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Fee Due Date</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $academicTerm->fee_due_date->format('M d, Y') }}</p>
                @if($academicTerm->fee_due_date->isPast())
                    <p class="text-xs text-red-600 mt-1">Overdue</p>
                @else
                    <p class="text-xs text-green-600 mt-1">{{ $academicTerm->fee_due_date->diffForHumans() }}</p>
                @endif
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-exclamation-triangle mr-2 text-amber-500"></i>
                <span>Late fee: KES {{ number_format($academicTerm->late_payment_fee ?? 0, 2) }}</span>
                @if($academicTerm->late_payment_percentage > 0)
                    <span class="ml-2">({{ $academicTerm->late_payment_percentage }}%)</span>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Registration</p>
                @if($academicTerm->is_registration_open)
                    <p class="text-lg font-semibold text-green-600 mt-1">Open</p>
                @else
                    <p class="text-lg font-semibold text-gray-600 mt-1">Closed</p>
                @endif
                @if($academicTerm->registration_start_date && $academicTerm->registration_end_date)
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $academicTerm->registration_start_date->format('M d') }} - {{ $academicTerm->registration_end_date->format('M d, Y') }}
                    </p>
                @endif
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-door-open text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-clock mr-2 text-gray-400"></i>
                @if($academicTerm->allow_late_registration)
                    <span>Late registration allowed</span>
                    @if($academicTerm->late_registration_fee > 0)
                        <span class="ml-2 text-amber-600">(+KES {{ number_format($academicTerm->late_registration_fee, 2) }})</span>
                    @endif
                @else
                    <span>No late registration</span>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Enrollments</p>
                <p class="text-lg font-semibold text-gray-800 mt-1">{{ $enrollments->total() ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">This term</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>
                <span>Academic Year {{ $academicTerm->academic_year }}</span>
                @if($academicTerm->academic_year_name)
                    <span class="ml-2">({{ $academicTerm->academic_year_name }})</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('details')" id="tab-details-btn"
                    class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-primary text-primary">
                <i class="fas fa-info-circle mr-2"></i>
                Term Details
            </button>
            <button onclick="switchTab('enrollments')" id="tab-enrollments-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                <i class="fas fa-users mr-2"></i>
                Enrollments
                <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $enrollments->total() ?? 0 }}</span>
            </button>
            <button onclick="switchTab('exams')" id="tab-exams-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                <i class="fas fa-file-alt mr-2"></i>
                Examinations
            </button>
            <button onclick="switchTab('settings')" id="tab-settings-btn"
                    class="tab-btn px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                <i class="fas fa-cog mr-2"></i>
                Settings
            </button>
        </nav>
    </div>

    <!-- Tab: Term Details -->
    <div id="tab-details" class="tab-pane p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Term Information -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Term Information
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Term Name</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $academicTerm->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Term Code</dt>
                            <dd class="text-sm font-mono text-gray-900">{{ $academicTerm->code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Short Code</dt>
                            <dd class="text-sm text-gray-900">{{ $academicTerm->short_code ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Term Number</dt>
                            <dd class="text-sm text-gray-900">Term {{ $academicTerm->term_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Academic Year</dt>
                            <dd class="text-sm text-gray-900">
                                {{ $academicTerm->academic_year }}
                                @if($academicTerm->academic_year_name)
                                    <span class="text-gray-500">({{ $academicTerm->academic_year_name }})</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Campus</dt>
                            <dd class="text-sm text-gray-900">
                                @if($academicTerm->campus)
                                    {{ $academicTerm->campus->name }}
                                @else
                                    <span class="text-purple-600">Global (All Campuses)</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Date Information -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Date Information
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Start Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $academicTerm->start_date->format('F d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">End Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $academicTerm->end_date->format('F d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Fee Due Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $academicTerm->fee_due_date->format('F d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Registration Period</dt>
                            <dd class="text-sm text-gray-900">
                                @if($academicTerm->registration_start_date && $academicTerm->registration_end_date)
                                    {{ $academicTerm->registration_start_date->format('M d') }} - {{ $academicTerm->registration_end_date->format('M d, Y') }}
                                @else
                                    <span class="text-gray-500">Not set</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Late Registration</dt>
                            <dd class="text-sm text-gray-900">
                                @if($academicTerm->allow_late_registration)
                                    <span class="text-green-600">Allowed</span>
                                    @if($academicTerm->late_registration_start_date && $academicTerm->late_registration_end_date)
                                        <span class="block text-xs text-gray-500 mt-1">
                                            {{ $academicTerm->late_registration_start_date->format('M d') }} - {{ $academicTerm->late_registration_end_date->format('M d, Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-500">Not allowed</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Description -->
            @if($academicTerm->description)
            <div class="lg:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-align-left text-primary mr-2"></i>
                    Description
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <p class="text-gray-700">{{ $academicTerm->description }}</p>
                </div>
            </div>
            @endif

            <!-- Internal Notes -->
            @if($academicTerm->notes)
            <div class="lg:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Internal Notes
                </h4>
                <div class="bg-amber-50 rounded-lg p-6 border border-amber-100">
                    <p class="text-amber-800">{{ $academicTerm->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Tab: Enrollments -->
    <div id="tab-enrollments" class="tab-pane hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-md font-medium text-gray-800 flex items-center">
                <i class="fas fa-users text-primary mr-2"></i>
                Enrollments for {{ $academicTerm->name }}
            </h4>
            <a href="{{ route('admin.tvet.enrollments.create') }}?term_id={{ $academicTerm->id }}"
               class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm transition-colors flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                New Enrollment
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollment #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($enrollments as $enrollment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ substr($enrollment->student->first_name ?? 'S', 0, 1) }}{{ substr($enrollment->student->last_name ?? 'T', 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $enrollment->student->full_name ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $enrollment->student->student_number ?? 'No ID' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $enrollment->course->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $enrollment->course->code ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono">{{ $enrollment->enrollment_number }}</span>
                        </td>
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
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">KES {{ number_format($enrollment->total_course_fee ?? 0, 2) }}</p>
                            @if($enrollment->balance > 0)
                                <p class="text-xs text-red-600">Balance: KES {{ number_format($enrollment->balance, 2) }}</p>
                            @else
                                <p class="text-xs text-green-600">Paid</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
                               class="text-primary hover:text-primary-dark text-sm">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-gray-300 text-3xl mb-2"></i>
                            <p>No enrollments for this term</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $enrollments->hasPages())
        <div class="mt-6">
            {{ $enrollments->links() }}
        </div>
        @endif
    </div>

    <!-- Tab: Examinations -->
    <div id="tab-exams" class="tab-pane hidden p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-check text-primary mr-2"></i>
                    Exam Registration Period
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Registration Start</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $academicTerm->exam_registration_start_date?->format('F d, Y') ?? 'Not set' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Registration End</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $academicTerm->exam_registration_end_date?->format('F d, Y') ?? 'Not set' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-pencil-alt text-primary mr-2"></i>
                    Examination Period
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Exam Start Date</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $academicTerm->exam_start_date?->format('F d, Y') ?? 'Not set' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Exam End Date</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $academicTerm->exam_end_date?->format('F d, Y') ?? 'Not set' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                        <div>
                            <h5 class="text-sm font-medium text-blue-800">Exam Registration Information</h5>
                            <p class="text-xs text-blue-700 mt-1">
                                Students can register for exams during the registration period.
                                Late registrations may incur additional fees.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Settings -->
    <div id="tab-settings" class="tab-pane hidden p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Status Settings -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-flag text-primary mr-2"></i>
                    Status Settings
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-600">Active Status</dt>
                            <dd>
                                @if($academicTerm->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-minus-circle mr-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-600">Current Term</dt>
                            <dd>
                                @if($academicTerm->is_current)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-play-circle mr-1"></i>
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        No
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-600">Registration</dt>
                            <dd>
                                @if($academicTerm->is_registration_open)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-door-open mr-1"></i>
                                        Open
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-door-closed mr-1"></i>
                                        Closed
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-600">Fee Generation</dt>
                            <dd>
                                @if($academicTerm->is_fee_generation_locked)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        <i class="fas fa-lock mr-1"></i>
                                        Locked
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-unlock mr-1"></i>
                                        Unlocked
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Financial Settings -->
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill text-primary mr-2"></i>
                    Financial Settings
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Late Registration Fee</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                KES {{ number_format($academicTerm->late_registration_fee ?? 0, 2) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Late Payment Penalty</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if($academicTerm->late_payment_percentage > 0)
                                    {{ $academicTerm->late_payment_percentage }}% of balance
                                @elseif($academicTerm->late_payment_fee > 0)
                                    KES {{ number_format($academicTerm->late_payment_fee, 2) }}
                                @else
                                    <span class="text-gray-500">None</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="lg:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Audit Trail
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Created</p>
                            <p class="text-sm font-medium text-gray-900">{{ $academicTerm->created_at->format('F d, Y \a\t h:i A') }}</p>
                            @if($academicTerm->creator)
                                <p class="text-xs text-gray-500 mt-1">by {{ $academicTerm->creator->name }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900">{{ $academicTerm->updated_at->format('F d, Y \a\t h:i A') }}</p>
                            @if($academicTerm->updater)
                                <p class="text-xs text-gray-500 mt-1">by {{ $academicTerm->updater->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-bolt text-primary mr-2"></i>
            Quick Actions
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @if(!$academicTerm->is_current)
            <form action="{{ route('admin.tvet.academic-terms.set-current', $academicTerm) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
                    <i class="fas fa-play-circle text-green-600 text-xl mb-2"></i>
                    <p class="text-sm font-medium text-gray-900">Set as Current Term</p>
                    <p class="text-xs text-gray-600 mt-1">Make this the active term</p>
                </button>
            </form>
            @endif

            @if($academicTerm->is_active)
                @if(!$academicTerm->is_current)
                <form action="{{ route('admin.tvet.academic-terms.deactivate', $academicTerm) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition-colors text-left">
                        <i class="fas fa-pause-circle text-yellow-600 text-xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-900">Deactivate Term</p>
                        <p class="text-xs text-gray-600 mt-1">Temporarily disable</p>
                    </button>
                </form>
                @endif
            @else
                <form action="{{ route('admin.tvet.academic-terms.activate', $academicTerm) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
                        <i class="fas fa-check-circle text-green-600 text-xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-900">Activate Term</p>
                        <p class="text-xs text-gray-600 mt-1">Enable this term</p>
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.tvet.academic-terms.toggle-registration', $academicTerm) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-left">
                    <i class="fas fa-door-open text-blue-600 text-xl mb-2"></i>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $academicTerm->is_registration_open ? 'Close' : 'Open' }} Registration
                    </p>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $academicTerm->is_registration_open ? 'Stop' : 'Start' }} student registration
                    </p>
                </button>
            </form>

            <a href="{{ route('admin.tvet.enrollments.create') }}?term_id={{ $academicTerm->id }}"
               class="p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-left">
                <i class="fas fa-user-plus text-purple-600 text-xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Add Enrollment</p>
                <p class="text-xs text-gray-600 mt-1">Enroll student for this term</p>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ TAB NAVIGATION ============
    function switchTab(tabId) {
        // Hide all tab panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab pane
        document.getElementById(`tab-${tabId}`).classList.remove('hidden');

        // Activate selected tab button
        const activeBtn = document.getElementById(`tab-${tabId}-btn`);
        activeBtn.classList.add('active', 'border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }

    // Check URL hash for initial tab
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.substring(1);
        if (hash && ['details', 'enrollments', 'exams', 'settings'].includes(hash)) {
            switchTab(hash);
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
</style>
@endsection
