@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Management')
@section('subtitle', 'Manage and track all students')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Students</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">All Students</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.students.import.view') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-upload"></i>
        <span>Import Students</span>
    </a>
    <a href="{{ route('admin.students.export') }}?format=xlsx"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
    <a href="{{ route('admin.students.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Student</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Students</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-user-graduate text-success mr-1"></i>
                <span>{{ number_format($activeStudents ?? 0) }} active</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Students</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-user-check text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-percentage text-success mr-1"></i>
                <span>{{ $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0 }}% of total</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Graduated</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($graduatedStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-purple-600 mr-1"></i>
                <span>Completed programs</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Historical</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($historicalStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center">
                <i class="fas fa-archive text-gray-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-gray-600 mr-1"></i>
                <span>Imported from Excel</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Needs Cleanup</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($requiresCleanup ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-broom text-amber-600 mr-1"></i>
                <span>Requires data review</span>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
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

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Gender Distribution</h3>
        </div>
        <div class="h-64">
            <canvas id="genderChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Student Categories</h3>
        </div>
        <div class="h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter students by status, campus, and registration date</p>
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
        <form id="filterForm" action="{{ route('admin.students.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                        <option value="dropped" {{ request('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="alumnus" {{ request('status') == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                        <option value="prospective" {{ request('status') == 'prospective' ? 'selected' : '' }}>Prospective</option>
                        <option value="historical" {{ request('status') == 'historical' ? 'selected' : '' }}>Historical</option>
                    </select>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Genders</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="student_category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Categories</option>
                        <option value="regular" {{ request('student_category') == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="alumnus" {{ request('student_category') == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                        <option value="staff_child" {{ request('student_category') == 'staff_child' ? 'selected' : '' }}>Staff Child</option>
                        <option value="sponsored" {{ request('student_category') == 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                        <option value="scholarship" {{ request('student_category') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration From</label>
                    <input type="date" name="registration_date_from" value="{{ request('registration_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration To</label>
                    <input type="date" name="registration_date_to" value="{{ request('registration_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cleanup Status</label>
                    <select name="requires_cleanup" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('requires_cleanup') == 'yes' ? 'selected' : '' }}>Needs Cleanup</option>
                        <option value="no" {{ request('requires_cleanup') == 'no' ? 'selected' : '' }}>Clean</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, ID, Student #..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.students.index') }}"
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

<!-- Bulk Actions Bar -->
<div id="bulkActionsBar" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <i class="fas fa-check-square text-blue-600"></i>
            <span class="text-sm font-medium text-blue-800">
                <span id="selectedCount">0</span> student(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2 flex-wrap gap-2">
            <button onclick="bulkActivate()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Activate</span>
            </button>
            <button onclick="bulkSuspend()"
                    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-pause-circle"></i>
                <span>Suspend</span>
            </button>
            <button onclick="bulkArchive()"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-archive"></i>
                <span>Archive</span>
            </button>
            <button onclick="bulkSyncStudentNumbers()"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-sync-alt"></i>
                <span>Sync Numbers</span>
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

<!-- Students Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">All Students</h3>
                <p class="text-sm text-gray-600 mt-1">Click on any student to view full details</p>
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
        <table class="w-full" id="studentsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewStudent('{{ $student->id }}')">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               class="student-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-id-card text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $student->student_number ?? 'N/A' }}
                                </span>
                                @if($student->legacy_student_code)
                                <span class="text-xs text-gray-500 block">
                                    Legacy: {{ $student->legacy_student_code }}
                                </span>
                                @endif
                                @if($student->requires_cleanup)
                                <span class="text-xs text-amber-600 flex items-center mt-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Needs cleanup
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ $student->first_name }} {{ $student->last_name }}
                                @if($student->middle_name)
                                <span class="text-xs text-gray-500">{{ $student->middle_name }}</span>
                                @endif
                            </p>
                            <div class="flex items-center mt-1">
                                @if($student->gender)
                                <span class="text-xs px-2 py-1 rounded
                                    {{ $student->gender == 'male' ? 'bg-blue-100 text-blue-800' :
                                       ($student->gender == 'female' ? 'bg-pink-100 text-pink-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ ucfirst($student->gender) }}
                                </span>
                                @endif
                                @if($student->id_number)
                                <span class="text-xs text-gray-500 ml-2">ID: {{ $student->id_number }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            @if($student->email)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope text-xs mr-1 w-4"></i>
                                <span class="text-xs">{{ Str::limit($student->email, 25) }}</span>
                            </div>
                            @endif
                            @if($student->phone)
                            <div class="flex items-center text-gray-600 mt-1">
                                <i class="fas fa-phone-alt text-xs mr-1 w-4"></i>
                                <span class="text-xs">{{ $student->phone }}</span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $student->campus->name ?? 'Not Assigned' }}</span>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $categoryColors = [
                                'regular' => 'bg-green-100 text-green-800',
                                'alumnus' => 'bg-purple-100 text-purple-800',
                                'staff_child' => 'bg-blue-100 text-blue-800',
                                'sponsored' => 'bg-amber-100 text-amber-800',
                                'scholarship' => 'bg-indigo-100 text-indigo-800',
                            ];
                            $categoryColor = $categoryColors[$student->student_category] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryColor }}">
                            {{ ucfirst(str_replace('_', ' ', $student->student_category)) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-gray-100 text-gray-800',
                                'graduated' => 'bg-purple-100 text-purple-800',
                                'dropped' => 'bg-red-100 text-red-800',
                                'suspended' => 'bg-yellow-100 text-yellow-800',
                                'alumnus' => 'bg-blue-100 text-blue-800',
                                'prospective' => 'bg-amber-100 text-amber-800',
                                'historical' => 'bg-gray-100 text-gray-600',
                            ];
                            $statusColor = $statusColors[$student->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-600">
                            {{ $student->registration_date ? \Carbon\Carbon::parse($student->registration_date)->format('M j, Y') : 'N/A' }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $student->registration_type === 'excel_import' ? 'Excel Import' :
                               ($student->registration_type === 'online_application' ? 'Online' : 'Manual Entry') }}
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('admin.students.show', $student) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.students.edit', $student) }}"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Student">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $student->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $student->id }}"
                                     class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        <!-- Status Actions -->
                                        @if($student->status !== 'active')
                                        <button onclick="updateStatus('{{ $student->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate Student
                                        </button>
                                        @endif

                                        @if($student->status !== 'suspended')
                                        <button onclick="updateStatus('{{ $student->id }}', 'suspend')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Suspend Student
                                        </button>
                                        @endif

                                        <button onclick="archiveStudent('{{ $student->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-archive mr-2"></i>
                                            Archive Student
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <!-- NEW: Student Number Fix Actions -->
                                        <button onclick="openFixStudentNumberModal('{{ $student->id }}', '{{ $student->student_number }}', '{{ $student->first_name }} {{ $student->last_name }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pencil-alt mr-2"></i>
                                            Fix Student Number
                                        </button>

                                        <button onclick="syncStudentPassword('{{ $student->id }}', '{{ $student->student_number }}', '{{ $student->first_name }} {{ $student->last_name }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-key mr-2"></i>
                                            Sync Password
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <a href="{{ route('admin.students.details', $student) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            View Full Details
                                        </a>

                                        <button onclick="generateReport('{{ $student->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-teal-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            Generate Report
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteStudent('{{ $student->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete Student
                                        </button>
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
                            <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No students found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by adding your first student</p>
                            <a href="{{ route('admin.students.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Student
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $students->firstItem() ?? 0 }}</span> to
                <span class="font-medium">{{ $students->lastItem() ?? 0 }}</span> of
                <span class="font-medium">{{ number_format($students->total() ?? 0) }}</span> students
            </div>
            <div class="flex items-center space-x-2">
                @if($students->onFirstPage() ?? true)
                <button disabled class="px-3 py-1 border border-gray-300 rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                @else
                <a href="{{ $students->previousPageUrl() }}"
                   class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
                @endif

                <span class="text-sm text-gray-600">
                    Page {{ $students->currentPage() ?? 1 }} of {{ $students->lastPage() ?? 1 }}
                </span>

                @if($students->hasMorePages() ?? false)
                <a href="{{ $students->nextPageUrl() }}"
                   class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
                @else
                <button disabled class="px-3 py-1 border border-gray-300 rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('statusModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800" id="statusModalTitle">Update Student Status</h3>
                    <button onclick="closeModal('statusModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div id="statusModalIcon" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <i id="statusModalIconIcon" class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="statusModalMessage">
                        Are you sure you want to update this student's status?
                    </p>
                </div>
                <form id="statusForm" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" id="statusStudentId">
                    <input type="hidden" name="action" id="statusAction">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="3" id="statusNotes"
                                      placeholder="Add any notes about this status change..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('statusModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitStatusForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <span id="statusModalActionBtn">Confirm</span>
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Student</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this student? This action cannot be undone.
                    </p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="student_id" id="deleteStudentId">
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

<!-- ==================== NEW: FIX STUDENT NUMBER MODAL ==================== -->
<div id="fixStudentNumberModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('fixStudentNumberModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Fix Student Number</h3>
                    <button onclick="closeModal('fixStudentNumberModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
                        <i class="fas fa-id-card text-purple-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="fixStudentNumberMessage">
                        Update student number and automatically sync password.
                    </p>
                </div>
                <form id="fixStudentNumberForm" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" id="fixStudentNumberStudentId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Student Name</label>
                            <p class="text-gray-900 font-medium" id="fixStudentNumberName">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Student Number</label>
                            <p class="text-gray-600" id="fixStudentNumberCurrent">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Student Number <span class="text-red-500">*</span></label>
                            <input type="text" name="student_number" id="fixStudentNumberNew"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Enter new student number" required>
                            <p class="text-xs text-gray-500 mt-1">Password will be automatically updated to this student number (uppercase).</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm text-blue-700 font-medium">What happens next?</p>
                                    <ul class="text-xs text-blue-600 mt-1 list-disc pl-4 space-y-1">
                                        <li>Student number will be updated</li>
                                        <li>Password will be changed to the new student number (uppercase)</li>
                                        <li>SMS notification will be sent to the student</li>
                                        <li>Student will be prompted to change password on next login</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('fixStudentNumberModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitFixStudentNumber()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Update & Sync Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== NEW: SYNC PASSWORD MODAL ==================== -->
<div id="syncPasswordModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('syncPasswordModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Sync Password</h3>
                    <button onclick="closeModal('syncPasswordModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <i class="fas fa-key text-indigo-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="syncPasswordMessage">
                        Sync student password with their student number.
                    </p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <p class="text-gray-900 font-medium" id="syncPasswordName">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student Number</label>
                        <p class="text-gray-600 font-mono" id="syncPasswordNumber">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <p class="text-gray-600 font-mono bg-gray-100 p-2 rounded-lg" id="syncPasswordNew">-</p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                            <div>
                                <p class="text-sm text-green-700 font-medium">What happens next?</p>
                                <ul class="text-xs text-green-600 mt-1 list-disc pl-4 space-y-1">
                                    <li>Password will be changed to: <span id="syncPasswordNewDisplay" class="font-bold"></span></li>
                                    <li>SMS notification will be sent to the student</li>
                                    <li>Student will be prompted to change password on next login</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('syncPasswordModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitSyncPassword()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Sync Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Modals -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">...</div>
<div id="bulkActivateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">...</div>
<div id="bulkSuspendModal" class="hidden fixed inset-0 z-50 overflow-y-auto">...</div>
<div id="bulkArchiveModal" class="hidden fixed inset-0 z-50 overflow-y-auto">...</div>

<!-- ==================== NEW: BULK SYNC NUMBERS MODAL ==================== -->
<div id="bulkSyncModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkSyncModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Sync Student Numbers</h3>
                    <button onclick="closeModal('bulkSyncModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
                        <i class="fas fa-sync-alt text-purple-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkSyncMessage">
                        Generate new student numbers for <span id="bulkSyncCount"></span> selected students and sync passwords.
                    </p>
                </div>
                <form id="bulkSyncForm" method="POST" action="{{ route('admin.students.bulk-sync-numbers') }}">
                    @csrf
                    <div id="bulkSyncInputs"></div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number Prefix</label>
                            <input type="text" name="prefix" value="STU"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="e.g., STU">
                            <p class="text-xs text-gray-500 mt-1">Format: {PREFIX}/{YEAR}/{MONTH}/{0001}</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                <p class="text-sm text-blue-700">
                                    All selected students will get new student numbers and their passwords will be synced. SMS notifications will be sent.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkSyncModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkSync()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Sync All
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ CSRF TOKEN ============
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
                        backgroundColor: ['#10B981', '#6B7280', '#8B5CF6', '#EF4444', '#F59E0B', '#3B82F6', '#F59E0B', '#9CA3AF'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 15 }
                        }
                    }
                }
            });
        }

        // Gender Chart
        const genderCtx = document.getElementById('genderChart')?.getContext('2d');
        if (genderCtx) {
            new Chart(genderCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($genderBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($genderBreakdown ?? [])) !!},
                        backgroundColor: ['#3B82F6', '#EC4899', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 15 }
                        }
                    }
                }
            });
        }

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($categoryBreakdown ?? [])) !!},
                    datasets: [{
                        label: 'Number of Students',
                        data: {!! json_encode(array_values($categoryBreakdown ?? [])) !!},
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { display: true, color: '#E5E7EB' } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    // ============ TABLE FUNCTIONS ============
    function viewStudent(studentId) {
        window.location.href = `/admin/students/${studentId}`;
    }

    function refreshTable() { location.reload(); }

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
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.student-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').classList.toggle('hidden', count === 0);
    }

    function toggleBulkActions() {
        const bar = document.getElementById('bulkActionsBar');
        bar.classList.toggle('hidden');
        if (bar.classList.contains('hidden')) {
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedStudentIds() {
        return Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
    }

    // ============ BULK ACTIONS ============
    function bulkActivate() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) { alert('Please select at least one student'); return; }
        document.getElementById('bulkActivateCount').textContent = ids.length;
        const inputs = document.getElementById('bulkActivateInputs');
        inputs.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
        openModal('bulkActivateModal');
    }

    function submitBulkActivate() { document.getElementById('bulkActivateForm').submit(); }

    function bulkSuspend() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) { alert('Please select at least one student'); return; }
        document.getElementById('bulkSuspendCount').textContent = ids.length;
        const inputs = document.getElementById('bulkSuspendInputs');
        inputs.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
        openModal('bulkSuspendModal');
    }

    function submitBulkSuspend() { document.getElementById('bulkSuspendForm').submit(); }

    function bulkArchive() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) { alert('Please select at least one student'); return; }
        document.getElementById('bulkArchiveCount').textContent = ids.length;
        const inputs = document.getElementById('bulkArchiveInputs');
        inputs.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
        openModal('bulkArchiveModal');
    }

    function submitBulkArchive() { document.getElementById('bulkArchiveForm').submit(); }

    function bulkDelete() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) { alert('Please select at least one student'); return; }
        document.getElementById('bulkDeleteCount').textContent = ids.length;
        const inputs = document.getElementById('bulkDeleteInputs');
        inputs.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
        openModal('bulkDeleteModal');
    }

    function submitBulkDelete() { document.getElementById('bulkDeleteForm').submit(); }

    // ============ NEW: BULK SYNC STUDENT NUMBERS ============
    function bulkSyncStudentNumbers() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) { alert('Please select at least one student'); return; }

        if (!confirm(`Generate new student numbers for ${ids.length} student(s) and sync passwords?`)) return;

        document.getElementById('bulkSyncCount').textContent = ids.length;
        const inputs = document.getElementById('bulkSyncInputs');
        inputs.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
        openModal('bulkSyncModal');
    }

    function submitBulkSync() {
        document.getElementById('bulkSyncForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(studentId) {
        const menu = document.getElementById(`actionMenu-${studentId}`);
        document.querySelectorAll('[id^="actionMenu-"]').forEach(m => {
            if (m.id !== `actionMenu-${studentId}`) m.classList.add('hidden');
        });
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // ============ SINGLE STUDENT ACTIONS ============
    function updateStatus(studentId, action) {
        document.getElementById('statusStudentId').value = studentId;
        document.getElementById('statusAction').value = action;

        const configs = {
            activate: { title: 'Activate Student', message: 'Are you sure you want to activate this student?', icon: 'fa-check-circle', bg: 'bg-green-100 text-green-600', btn: 'Activate' },
            suspend: { title: 'Suspend Student', message: 'Are you sure you want to suspend this student?', icon: 'fa-pause-circle', bg: 'bg-yellow-100 text-yellow-600', btn: 'Suspend' }
        };

        const config = configs[action];
        document.getElementById('statusModalTitle').textContent = config.title;
        document.getElementById('statusModalMessage').textContent = config.message;
        document.getElementById('statusModalIconIcon').className = `fas ${config.icon} text-xl`;
        document.getElementById('statusModalIcon').className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${config.bg} mb-4`;
        document.getElementById('statusModalActionBtn').textContent = config.btn;

        document.getElementById('statusForm').action = `/admin/students/${studentId}/${action}`;
        openModal('statusModal');
    }

    function submitStatusForm() { document.getElementById('statusForm').submit(); }

    function archiveStudent(studentId) {
        if (confirm('Are you sure you want to archive this student?')) {
            window.location.href = `/admin/students/${studentId}/archive`;
        }
    }

    function deleteStudent(studentId) {
        document.getElementById('deleteStudentId').value = studentId;
        document.getElementById('deleteForm').action = `/admin/students/${studentId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() { document.getElementById('deleteForm').submit(); }

    function generateReport(studentId) {
        window.location.href = `/admin/students/${studentId}/report`;
    }

    // ============ NEW: FIX STUDENT NUMBER ============
    function openFixStudentNumberModal(studentId, currentNumber, studentName) {
        document.getElementById('fixStudentNumberStudentId').value = studentId;
        document.getElementById('fixStudentNumberName').textContent = studentName;
        document.getElementById('fixStudentNumberCurrent').textContent = currentNumber || 'No student number';
        document.getElementById('fixStudentNumberNew').value = '';
        document.getElementById('fixStudentNumberForm').action = `/admin/students/${studentId}/fix-student-number`;
        openModal('fixStudentNumberModal');

        // Focus the input after modal opens
        setTimeout(() => {
            document.getElementById('fixStudentNumberNew').focus();
        }, 300);
    }

    function submitFixStudentNumber() {
        const newNumber = document.getElementById('fixStudentNumberNew').value.trim();
        if (!newNumber) {
            showToast('Please enter a new student number', 'error');
            return;
        }

        if (newNumber === document.getElementById('fixStudentNumberCurrent').textContent) {
            if (!confirm('New number is the same as current. Continue anyway?')) return;
        }

        if (confirm(`Update student number to "${newNumber}" and sync password? SMS will be sent.`)) {
            document.getElementById('fixStudentNumberForm').submit();
        }
    }

    // ============ NEW: SYNC PASSWORD ============
    function syncStudentPassword(studentId, studentNumber, studentName) {
        if (!studentNumber) {
            showToast('Student has no student number to sync.', 'error');
            return;
        }

        if (!confirm(`Sync password for ${studentName} to "${studentNumber}"? SMS will be sent.`)) return;

        // Get the uppercase password
        const newPassword = studentNumber.toUpperCase();

        document.getElementById('syncPasswordName').textContent = studentName;
        document.getElementById('syncPasswordNumber').textContent = studentNumber;
        document.getElementById('syncPasswordNew').textContent = newPassword;
        document.getElementById('syncPasswordNewDisplay').textContent = newPassword;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/students/${studentId}/sync-password`;
        form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
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

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        document.getElementById('chartMenu').classList.toggle('hidden');
    }

    function exportChart(chartType) {
        const canvas = document.getElementById(`${chartType}Chart`);
        if (canvas) {
            const link = document.createElement('a');
            link.download = `${chartType}-chart.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
        toggleChartMenu();
    }

    // ============ TOAST NOTIFICATIONS ============
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            info: 'bg-blue-50 border-blue-200 text-blue-800'
        };
        const icons = {
            success: 'fa-check-circle text-green-600',
            error: 'fa-exclamation-circle text-red-600',
            warning: 'fa-exclamation-triangle text-yellow-600',
            info: 'fa-info-circle text-blue-600'
        };

        toast.className = `flex items-center p-4 rounded-lg shadow-lg border ${colors[type] || colors.info}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info} mr-3"></i>
            <span class="text-sm font-medium">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);
        setTimeout(() => { if (toast.parentElement) toast.remove(); }, 5000);
    }

    // Close modals on Escape key
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
@endsection
