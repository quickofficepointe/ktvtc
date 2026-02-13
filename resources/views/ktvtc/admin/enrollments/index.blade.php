@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollments')
@section('subtitle', 'Manage student course enrollments')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Enrollments</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.enrollments.export') }}?format=xlsx"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
    <a href="{{ route('admin.tvet.enrollments.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Enrollment</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Enrollments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-success mr-1"></i>
                <span>{{ number_format($activeEnrollments ?? 0) }} active</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">In Progress</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-play-circle text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-percentage text-success mr-1"></i>
                <span>{{ $totalEnrollments > 0 ? round(($activeEnrollments / $totalEnrollments) * 100, 1) : 0 }}% of total</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($completedEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-purple-600 mr-1"></i>
                <span>Graduated/Completed</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Payment</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($pendingPayment ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-exclamation-triangle text-amber-600 mr-1"></i>
                <span>Outstanding balance</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Exam Registration</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($requiresExamRegistration ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-file-alt text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-blue-600 mr-1"></i>
                <span>Need exam registration</span>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Status Breakdown Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Status Distribution</h3>
            <div class="relative">
                <button onclick="toggleChartMenu()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="chartMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportChart('status')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            Download Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Fee Structure Breakdown Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Fee Structure</h3>
        </div>
        <div class="h-64">
            <canvas id="feeStructureChart"></canvas>
        </div>
    </div>

    <!-- Intake Breakdown Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ date('Y') }} Intakes</h3>
        </div>
        <div class="h-64">
            <canvas id="intakeChart"></canvas>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter enrollments by status, course, intake and more</p>
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
        <form id="filterForm" action="{{ route('admin.tvet.enrollments.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

                <!-- Course Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Campus Filter (Admin only) -->
                @if(auth()->user()->role == 2)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Intake Period Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intake Period</label>
                    <select name="intake_period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Periods</option>
                        @foreach($intakePeriods as $period)
                            <option value="{{ $period }}" {{ request('intake_period') == $period ? 'selected' : '' }}>
                                {{ $period }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Intake Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intake Year</label>
                    <select name="intake_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Years</option>
                        @foreach($intakeYears as $year)
                            <option value="{{ $year }}" {{ request('intake_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Exam Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type</label>
                    <select name="exam_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($examTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('exam_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Student Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student Type</label>
                    <select name="student_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($studentTypes as $type)
                            <option value="{{ $type }}" {{ request('student_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sponsorship Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sponsorship</label>
                    <select name="sponsorship_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        @foreach($sponsorshipTypes as $type)
                            <option value="{{ $type }}" {{ request('sponsorship_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Active Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Active</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_active') == 'yes' ? 'selected' : '' }}>Active</option>
                        <option value="no" {{ request('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Exam Registration Required -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Registration</label>
                    <select name="requires_external_exam" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('requires_external_exam') == 'yes' ? 'selected' : '' }}>Requires Registration</option>
                        <option value="no" {{ request('requires_external_exam') == 'no' ? 'selected' : '' }}>No Exam</option>
                    </select>
                </div>

                <!-- Enrollment Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment From</label>
                    <input type="date" name="enrollment_date_from" value="{{ request('enrollment_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Enrollment Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment To</label>
                    <input type="date" name="enrollment_date_to" value="{{ request('enrollment_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by enrollment #, student name, ID or course..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.enrollments.index') }}"
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
                <span id="selectedCount">0</span> enrollment(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkActivate()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Activate</span>
            </button>
            <button onclick="bulkComplete()"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-graduation-cap"></i>
                <span>Mark Completed</span>
            </button>
            <button onclick="bulkDelete()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Enrollments Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">All Enrollments</h3>
                <p class="text-sm text-gray-600 mt-1">Click on any enrollment to view full details</p>
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
        <table class="w-full" id="enrollmentsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($enrollments as $enrollment)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewEnrollment('{{ $enrollment->id }}')">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}"
                               class="enrollment-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900 font-mono">
                                    {{ $enrollment->enrollment_number }}
                                </span>
                                @if($enrollment->legacy_enrollment_code)
                                    <span class="text-xs text-gray-500 block">
                                        Legacy: {{ $enrollment->legacy_enrollment_code }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
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
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $enrollment->course->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $enrollment->course->code ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            <span class="text-gray-900">{{ $enrollment->intake_period }} {{ $enrollment->intake_year }}</span>
                            @if($enrollment->study_mode)
                                <span class="text-xs text-gray-500 block">{{ ucfirst(str_replace('_', ' ', $enrollment->study_mode)) }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $enrollment->campus->name ?? 'Not Assigned' }}</span>
                    </td>
                    <td class="py-3 px-6">
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
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">KES {{ number_format($enrollment->total_course_fee ?? 0, 2) }}</p>
                            @if($enrollment->balance > 0)
                                <p class="text-xs text-red-600">Balance: KES {{ number_format($enrollment->balance, 2) }}</p>
                            @else
                                <p class="text-xs text-green-600">Paid</p>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($enrollment->requires_external_exam)
                            @if($enrollment->exam_registration_number)
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Registered
                                </span>
                                <span class="text-xs text-gray-500 block mt-1">{{ $enrollment->exam_registration_number }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Pending
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-times mr-1"></i>
                                No Exam
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tvet.enrollments.edit', $enrollment) }}"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Enrollment">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $enrollment->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $enrollment->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($enrollment->status != 'in_progress')
                                        <button onclick="updateStatus('{{ $enrollment->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Mark In Progress
                                        </button>
                                        @endif

                                        @if($enrollment->status != 'completed')
                                        <button onclick="updateStatus('{{ $enrollment->id }}', 'complete')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Mark Completed
                                        </button>
                                        @endif

                                        @if($enrollment->status != 'suspended' && $enrollment->status == 'in_progress')
                                        <button onclick="updateStatus('{{ $enrollment->id }}', 'suspend')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Suspend
                                        </button>
                                        @endif

                                        @if($enrollment->requires_external_exam && !$enrollment->exam_registration_number)
                                        <button onclick="openExamRegistration('{{ $enrollment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            Register for Exam
                                        </button>
                                        @endif

                                        @if($enrollment->status == 'completed' && !$enrollment->certificate_number)
                                        <button onclick="openCertificateIssuance('{{ $enrollment->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-certificate mr-2"></i>
                                            Issue Certificate
                                        </button>
                                        @endif

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteEnrollment('{{ $enrollment->id }}')"
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
                    <td colspan="10" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-user-graduate text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No enrollments found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first enrollment</p>
                            <a href="{{ route('admin.tvet.enrollments.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                New Enrollment
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $enrollments->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $enrollments->firstItem() }}</span> to
                <span class="font-medium">{{ $enrollments->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($enrollments->total()) }}</span> enrollments
            </div>
            <div class="flex items-center space-x-2">
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
    @endif
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
                <form id="examRegistrationForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Registration Number</label>
                            <input type="text" name="exam_registration_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Date</label>
                            <input type="date" name="exam_registration_date" required
                                   value="{{ date('Y-m-d') }}"
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
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('examRegistrationModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitExamRegistration()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Register Exam
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Certificate Issuance Modal -->
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
                <form id="certificateForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Certificate Number</label>
                            <input type="text" name="certificate_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Issue Date</label>
                            <input type="date" name="certificate_issue_date" required
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Final Grade</label>
                            <input type="text" name="final_grade"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Distinction, Credit, Pass">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Class Award</label>
                            <input type="text" name="class_award"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., First Class, Second Class">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('certificateModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitCertificateIssuance()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-certificate mr-2"></i>
                    Issue Certificate
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Enrollment</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this enrollment? This action cannot be undone.
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

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkDeleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Delete Enrollments</h3>
                    <button onclick="closeModal('bulkDeleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkDeleteModalMessage">
                        Are you sure you want to delete <span id="bulkDeleteCount"></span> enrollment(s)? This action cannot be undone.
                    </p>
                </div>
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.tvet.enrollments.bulk.delete') }}">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkDeleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkDelete()"
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        initializeQuickSearch();
    });

    // ============ CHARTS ============
    function initializeCharts() {
        // Status Chart
        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($statusBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($statusBreakdown ?? [])) !!},
                        backgroundColor: [
                            '#3B82F6', // registered - blue
                            '#10B981', // in_progress - green
                            '#8B5CF6', // completed - purple
                            '#EF4444', // dropped - red
                            '#F59E0B', // suspended - amber
                            '#F59E0B', // deferred - amber
                            '#6B7280'  // others - gray
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Fee Structure Chart
        const feeCtx = document.getElementById('feeStructureChart')?.getContext('2d');
        if (feeCtx) {
            new Chart(feeCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($feeStructureBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($feeStructureBreakdown ?? [])) !!},
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Intake Chart
        const intakeCtx = document.getElementById('intakeChart')?.getContext('2d');
        if (intakeCtx) {
            new Chart(intakeCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($intakeBreakdown ?? [])) !!},
                    datasets: [{
                        label: 'Number of Enrollments',
                        data: {!! json_encode(array_values($intakeBreakdown ?? [])) !!},
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }

    // ============ TABLE FUNCTIONS ============
    function viewEnrollment(enrollmentId) {
        window.location.href = `/admin/tvet/enrollments/${enrollmentId}`;
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

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
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
            const checkboxes = document.querySelectorAll('.enrollment-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedEnrollmentIds() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    // ============ BULK ACTIONS ============
    function bulkActivate() {
        const ids = getSelectedEnrollmentIds();
        if (ids.length === 0) {
            alert('Please select at least one enrollment');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.tvet.enrollments.bulk.activate") }}';
        form.innerHTML = '@csrf';

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function bulkComplete() {
        const ids = getSelectedEnrollmentIds();
        if (ids.length === 0) {
            alert('Please select at least one enrollment');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.tvet.enrollments.bulk.complete") }}';
        form.innerHTML = '@csrf';

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function bulkDelete() {
        const ids = getSelectedEnrollmentIds();
        if (ids.length === 0) {
            alert('Please select at least one enrollment');
            return;
        }

        document.getElementById('bulkDeleteCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkDeleteInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkDeleteModal');
    }

    function submitBulkDelete() {
        document.getElementById('bulkDeleteForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(enrollmentId) {
        const menu = document.getElementById(`actionMenu-${enrollmentId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${enrollmentId}`) {
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

    // ============ SINGLE ENROLLMENT ACTIONS ============
    function updateStatus(enrollmentId, action) {
        let url = '';
        let method = 'POST';

        switch(action) {
            case 'activate':
                url = `/admin/tvet/enrollments/${enrollmentId}/activate`;
                break;
            case 'suspend':
                url = `/admin/tvet/enrollments/${enrollmentId}/suspend`;
                break;
            case 'complete':
                url = `/admin/tvet/enrollments/${enrollmentId}/complete`;
                break;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function openExamRegistration(enrollmentId) {
        document.getElementById('examRegistrationForm').action = `/admin/tvet/enrollments/${enrollmentId}/register-exam`;
        openModal('examRegistrationModal');
    }

    function submitExamRegistration() {
        document.getElementById('examRegistrationForm').submit();
    }

    function openCertificateIssuance(enrollmentId) {
        document.getElementById('certificateForm').action = `/admin/tvet/enrollments/${enrollmentId}/issue-certificate`;
        openModal('certificateModal');
    }

    function submitCertificateIssuance() {
        document.getElementById('certificateForm').submit();
    }

    function deleteEnrollment(enrollmentId) {
        document.getElementById('deleteForm').action = `/admin/tvet/enrollments/${enrollmentId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
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

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        const menu = document.getElementById('chartMenu');
        menu.classList.toggle('hidden');
    }

    function exportChart(chartType) {
        let canvas;
        if (chartType === 'status') {
            canvas = document.getElementById('statusChart');
        }

        if (canvas) {
            const link = document.createElement('a');
            link.download = `${chartType}-chart.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        toggleChartMenu();
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
    #enrollmentsTable {
        min-width: 1600px;
    }

    @media (max-width: 768px) {
        #enrollmentsTable {
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
</style>
@endsection
