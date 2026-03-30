@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Registrations')
@section('subtitle', 'Manage student exam registrations across all examining bodies')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exams</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Exam Registrations</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.exam-registrations.export') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Registration</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalRegistrations ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-alt text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($statusBreakdown['pending'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Registered</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($statusBreakdown['registered'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Certified</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($statusBreakdown['certified'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-certificate text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">This Month</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($registrationsThisMonth ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<div class="bg-white rounded-xl border border-gray-200 mb-6 overflow-hidden">
    <div class="flex overflow-x-auto">
        <a href="{{ route('admin.tvet.exam-registrations.index') }}"
           class="px-6 py-3 text-sm font-medium {{ !request('status') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            All ({{ $totalRegistrations ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'pending']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'pending' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Pending ({{ $statusBreakdown['pending'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'submitted']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'submitted' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Submitted ({{ $statusBreakdown['submitted'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'registered']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'registered' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Registered ({{ $statusBreakdown['registered'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'active']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'active' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Active ({{ $statusBreakdown['active'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'completed']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'completed' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Completed ({{ $statusBreakdown['completed'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'results_published']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'results_published' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Results Published ({{ $statusBreakdown['results_published'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'certified']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'certified' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Certified ({{ $statusBreakdown['certified'] ?? 0 }})
        </a>
        <a href="{{ route('admin.tvet.exam-registrations.index', ['status' => 'failed']) }}"
           class="px-6 py-3 text-sm font-medium {{ request('status') == 'failed' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">
            Failed ({{ $statusBreakdown['failed'] ?? 0 }})
        </a>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter exam registrations by body, type, date and status</p>
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
        <form id="filterForm" action="{{ route('admin.tvet.exam-registrations.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Exam Body Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                    <select name="exam_body_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Bodies</option>
                        @foreach($examBodies as $body)
                            <option value="{{ $body->id }}" {{ request('exam_body_id') == $body->id ? 'selected' : '' }}>
                                {{ $body->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Exam Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type</label>
                    <select name="exam_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($examTypes as $type)
                            <option value="{{ $type->id }}" {{ request('exam_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="results_published" {{ request('status') == 'results_published' ? 'selected' : '' }}>Results Published</option>
                        <option value="certified" {{ request('status') == 'certified' ? 'selected' : '' }}>Certified</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="deferred" {{ request('status') == 'deferred' ? 'selected' : '' }}>Deferred</option>
                    </select>
                </div>

                <!-- Enrollment/Student Filter -->
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

                <!-- Registration Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reg Date From</label>
                    <input type="date" name="registration_date_from" value="{{ request('registration_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Registration Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reg Date To</label>
                    <input type="date" name="registration_date_to" value="{{ request('registration_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Exam Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Date From</label>
                    <input type="date" name="exam_date_from" value="{{ request('exam_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Exam Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Date To</label>
                    <input type="date" name="exam_date_to" value="{{ request('exam_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Registration #, Index #, Student name..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.exam-registrations.index') }}"
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
                <span id="selectedCount">0</span> registration(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkSubmit()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-paper-plane"></i>
                <span>Submit</span>
            </button>
            <button onclick="bulkVerify()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Verify</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Exam Registrations Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Exam Registrations</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $examRegistrations->total() }} registrations found</p>
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
        <table class="w-full" id="examRegistrationsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Body</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Type</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg Number</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($examRegistrations as $registration)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewRegistration({{ $registration->id }})">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}"
                               class="registration-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-gray-600">
                                    {{ substr($registration->enrollment->student->first_name ?? 'S', 0, 1) }}{{ substr($registration->enrollment->student->last_name ?? 'T', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $registration->enrollment->student->full_name ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $registration->enrollment->student->student_number ?? 'No ID' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $registration->enrollment->course->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $registration->enrollment->course->code ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $registration->examType->examBody->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-medium text-gray-900">{{ $registration->examType->name ?? 'N/A' }}</span>
                        @if($registration->examType->level)
                            <span class="text-xs text-gray-500 block">Level {{ $registration->examType->level }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($registration->registration_number)
                            <span class="text-sm font-mono text-gray-900">{{ $registration->registration_number }}</span>
                            @if($registration->index_number)
                                <span class="text-xs text-gray-500 block">Index: {{ $registration->index_number }}</span>
                            @endif
                        @else
                            <span class="text-gray-400">Not assigned</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">
                            {{ $registration->registration_date->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($registration->exam_date)
                            <div class="text-sm text-gray-900">
                                {{ $registration->exam_date->format('d/m/Y') }}
                            </div>
                            @if($registration->exam_date->isFuture())
                                <span class="text-xs text-green-600">Upcoming</span>
                            @elseif($registration->exam_date->isPast())
                                <span class="text-xs text-gray-500">Past</span>
                            @endif
                        @else
                            <span class="text-gray-400">Not set</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'submitted' => 'blue',
                                'registered' => 'green',
                                'active' => 'purple',
                                'completed' => 'indigo',
                                'results_published' => 'pink',
                                'certified' => 'emerald',
                                'failed' => 'red',
                                'deferred' => 'orange',
                            ];
                            $color = $statusColors[$registration->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                        </span>
                        @if($registration->verified_at)
                            <span class="text-xs text-green-600 block mt-1">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($registration->result)
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $registration->result }}</span>
                                @if($registration->grade)
                                    <span class="text-xs text-gray-500 block">Grade: {{ $registration->grade }}</span>
                                @endif
                                @if($registration->score)
                                    <span class="text-xs text-gray-500">Score: {{ $registration->score }}%</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">Pending</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($registration->certificate_number)
                            <div>
                                <span class="text-sm font-mono text-gray-900">{{ $registration->certificate_number }}</span>
                                @if($registration->certificate_issue_date)
                                    <span class="text-xs text-gray-500 block">Issued: {{ $registration->certificate_issue_date->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">Not issued</span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewRegistration({{ $registration->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editRegistration({{ $registration->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Registration">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $registration->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $registration->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($registration->status == 'pending')
                                        <button onclick="submitRegistration('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Submit to Exam Body
                                        </button>
                                        @endif

                                        @if(in_array($registration->status, ['submitted', 'registered']))
                                        <button onclick="verifyRegistration('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Verify
                                        </button>
                                        @endif

                                        @if($registration->status == 'registered' && !$registration->verified_at)
                                        <button onclick="markActive('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Mark Active
                                        </button>
                                        @endif

                                        <button onclick="openResultsModal('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-star mr-2"></i>
                                            Enter Results
                                        </button>

                                        <button onclick="openCertificateModal('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-certificate mr-2"></i>
                                            Issue Certificate
                                        </button>

                                        @if($registration->certificate_number)
                                        <button onclick="downloadCertificate('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-download mr-2"></i>
                                            Download Certificate
                                        </button>
                                        @endif

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="printSlip('{{ $registration->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-print mr-2"></i>
                                            Print Exam Slip
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteRegistration('{{ $registration->id }}')"
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
                    <td colspan="12" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-file-alt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No exam registrations found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first exam registration</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                New Registration
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($examRegistrations instanceof \Illuminate\Pagination\LengthAwarePaginator && $examRegistrations->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $examRegistrations->firstItem() }}</span> to
                <span class="font-medium">{{ $examRegistrations->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($examRegistrations->total()) }}</span> registrations
            </div>
            <div class="flex items-center space-x-2">
                {{ $examRegistrations->links() }}
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
                        <h3 class="text-lg font-semibold text-gray-800">Create Exam Registration</h3>
                        <p class="text-sm text-gray-600">Register a student for an examination</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.exam-registrations.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Student/Enrollment Selection -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                                    Student & Enrollment
                                </h4>

                                <div class="space-y-4">
                                    <!-- Enrollment Select -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Select Enrollment
                                        </label>
                                        <select name="enrollment_id" id="enrollment_id" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Choose enrollment...</option>
                                            @foreach($enrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}" data-student="{{ $enrollment->student->full_name }}" data-course="{{ $enrollment->course->name }}">
                                                    {{ $enrollment->student->full_name }} - {{ $enrollment->course->name }} ({{ $enrollment->intake_period }} {{ $enrollment->intake_year }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Student Info Preview -->
                                    <div id="studentPreview" class="hidden bg-blue-50 p-4 rounded-lg">
                                        <p class="text-sm font-medium text-blue-800" id="previewStudentName"></p>
                                        <p class="text-xs text-blue-600" id="previewCourseName"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Details -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-id-card text-primary mr-2"></i>
                                    Registration Details
                                </h4>

                                <div class="space-y-4">
                                    <!-- Registration Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Registration Number
                                        </label>
                                        <input type="text"
                                               name="registration_number"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                                               placeholder="e.g., CDACC/2025/00123">
                                        <p class="mt-1 text-xs text-gray-500">Leave empty for auto-generation</p>
                                    </div>

                                    <!-- Index Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Index Number
                                        </label>
                                        <input type="text"
                                               name="index_number"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., 2025-01-00123">
                                    </div>

                                    <!-- Registration Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Registration Date
                                        </label>
                                        <input type="date"
                                               name="registration_date"
                                               value="{{ date('Y-m-d') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               required>
                                    </div>

                                    <!-- Exam Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Exam Date
                                        </label>
                                        <input type="date"
                                               name="exam_date"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Exam Type Selection -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-layer-group text-primary mr-2"></i>
                                    Examination Details
                                </h4>

                                <div class="space-y-4">
                                    <!-- Exam Type -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Exam Type
                                        </label>
                                        <select name="exam_type_id" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Select exam type...</option>
                                            @foreach($examTypes as $type)
                                                <option value="{{ $type->id }}">
                                                    {{ $type->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Status
                                        </label>
                                        <select name="status" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="pending">Pending</option>
                                            <option value="submitted">Submitted</option>
                                            <option value="registered">Registered</option>
                                            <option value="active">Active</option>
                                            <option value="completed">Completed</option>
                                            <option value="results_published">Results Published</option>
                                            <option value="certified">Certified</option>
                                            <option value="failed">Failed</option>
                                            <option value="deferred">Deferred</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks & Metadata -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                                    Additional Information
                                </h4>

                                <div class="space-y-4">
                                    <!-- Remarks -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Remarks
                                        </label>
                                        <textarea name="remarks"
                                                  rows="4"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Any additional notes..."></textarea>
                                    </div>

                                    <!-- Metadata (hidden but can be added via JS if needed) -->
                                    <input type="hidden" name="metadata" value="{}">
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
                    Create Registration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Results Entry Modal -->
<div id="resultsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('resultsModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Enter Exam Results</h3>
                    <button onclick="closeModal('resultsModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="resultsForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Result</label>
                            <input type="text" name="result" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Pass, Fail, Distinction">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                            <input type="text" name="grade"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., A, B, C">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                            <input type="number" name="score" min="0" max="100" step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Result Date</label>
                            <input type="date" name="result_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('resultsModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitResultsForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Save Results
                </button>
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

                <form id="certificateForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Certificate Number</label>
                            <input type="text" name="certificate_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., CDACC/CERT/2025/00123">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Issue Date</label>
                            <input type="date" name="certificate_issue_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certificate File (PDF)</label>
                            <input type="file" name="certificate_file" accept=".pdf"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Upload scanned certificate (PDF only, max 2MB)</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('certificateModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitCertificateForm()"
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Exam Registration</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this exam registration?
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

<!-- Bulk Submit Modal -->
<div id="bulkSubmitModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkSubmitModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Submit Registrations</h3>
                    <button onclick="closeModal('bulkSubmitModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkSubmitModalMessage">
                        Submit <span id="bulkSubmitCount"></span> registration(s) to exam body?
                    </p>
                </div>
                <form id="bulkSubmitForm" method="POST" action="{{ route('admin.tvet.exam-registrations.bulk.submit') }}">
                    @csrf
                    <div id="bulkSubmitInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkSubmitModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkSubmit()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Submit
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
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Verify Registrations</h3>
                    <button onclick="closeModal('bulkVerifyModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkVerifyModalMessage">
                        Verify <span id="bulkVerifyCount"></span> registration(s)?
                    </p>
                </div>
                <form id="bulkVerifyForm" method="POST" action="{{ route('admin.tvet.exam-registrations.bulk.verify') }}">
                    @csrf
                    <div id="bulkVerifyInputs"></div>
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
                    Verify
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
        initializeEnrollmentSelect();
    });

    // ============ TABLE FUNCTIONS ============
    function viewRegistration(registrationId) {
        window.location.href = `/admin/tvet/exam-registrations/${registrationId}`;
    }

    function editRegistration(registrationId) {
        window.location.href = `/admin/tvet/exam-registrations/${registrationId}/edit`;
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

    // ============ ENROLLMENT SELECT PREVIEW ============
    function initializeEnrollmentSelect() {
        const enrollmentSelect = document.getElementById('enrollment_id');
        const preview = document.getElementById('studentPreview');
        const studentName = document.getElementById('previewStudentName');
        const courseName = document.getElementById('previewCourseName');

        if (enrollmentSelect) {
            enrollmentSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (selected.value) {
                    const student = selected.dataset.student;
                    const course = selected.dataset.course;
                    studentName.textContent = student;
                    courseName.textContent = course;
                    preview.classList.remove('hidden');
                } else {
                    preview.classList.add('hidden');
                }
            });
        }
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.registration-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
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
            const checkboxes = document.querySelectorAll('.registration-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedRegistrationIds() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkSubmit() {
        const ids = getSelectedRegistrationIds();
        if (ids.length === 0) {
            alert('Please select at least one registration');
            return;
        }

        document.getElementById('bulkSubmitCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkSubmitInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkSubmitModal');
    }

    function submitBulkSubmit() {
        document.getElementById('bulkSubmitForm').submit();
    }

    function bulkVerify() {
        const ids = getSelectedRegistrationIds();
        if (ids.length === 0) {
            alert('Please select at least one registration');
            return;
        }

        document.getElementById('bulkVerifyCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkVerifyInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkVerifyModal');
    }

    function submitBulkVerify() {
        document.getElementById('bulkVerifyForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(registrationId) {
        const menu = document.getElementById(`actionMenu-${registrationId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${registrationId}`) {
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

    // ============ REGISTRATION ACTIONS ============
    function submitRegistration(registrationId) {
        if (confirm('Submit this registration to the exam body?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/exam-registrations/${registrationId}/submit`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function verifyRegistration(registrationId) {
        if (confirm('Verify this registration?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/exam-registrations/${registrationId}/verify`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function markActive(registrationId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tvet/exam-registrations/${registrationId}/approve`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function openResultsModal(registrationId) {
        document.getElementById('resultsForm').action = `/admin/tvet/exam-registrations/${registrationId}/enter-results`;
        openModal('resultsModal');
    }

    function submitResultsForm() {
        document.getElementById('resultsForm').submit();
    }

    function openCertificateModal(registrationId) {
        document.getElementById('certificateForm').action = `/admin/tvet/exam-registrations/${registrationId}/issue-certificate`;
        openModal('certificateModal');
    }

    function submitCertificateForm() {
        document.getElementById('certificateForm').submit();
    }

    function downloadCertificate(registrationId) {
        window.location.href = `/admin/tvet/exam-registrations/${registrationId}/download-certificate`;
    }

    function printSlip(registrationId) {
        window.open(`/admin/tvet/exam-registrations/${registrationId}/print-slip`, '_blank');
    }

    function deleteRegistration(registrationId) {
        document.getElementById('deleteForm').action = `/admin/tvet/exam-registrations/${registrationId}`;
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
    #examRegistrationsTable {
        min-width: 1800px;
    }

    @media (max-width: 768px) {
        #examRegistrationsTable {
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
