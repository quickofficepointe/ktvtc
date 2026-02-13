@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Management')
@section('subtitle', 'Manage and track all TVET/CDACC students')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Students</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.students.import.view') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-upload"></i>
        <span>Import Students</span>
    </a>
    <a href="{{ route('admin.tvet.students.export') }}?format=xlsx"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
    <a href="{{ route('admin.tvet.students.create') }}"
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

    <!-- Gender Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Gender Distribution</h3>
        </div>
        <div class="h-64">
            <canvas id="genderChart"></canvas>
        </div>
    </div>

    <!-- Category Distribution Chart -->
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
        <form id="filterForm" action="{{ route('admin.tvet.students.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status Filter -->
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

                <!-- Campus Filter -->
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

                <!-- Gender Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Genders</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Category Filter -->
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

                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration From</label>
                    <input type="date" name="registration_date_from" value="{{ request('registration_date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration To</label>
                    <input type="date" name="registration_date_to" value="{{ request('registration_date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Cleanup Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cleanup Status</label>
                    <select name="requires_cleanup" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('requires_cleanup') == 'yes' ? 'selected' : '' }}>Needs Cleanup</option>
                        <option value="no" {{ request('requires_cleanup') == 'no' ? 'selected' : '' }}>Clean</option>
                    </select>
                </div>

                <!-- Search -->
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

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.students.index') }}"
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
                <span id="selectedCount">0</span> student(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
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
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.tvet.students.show', $student) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tvet.students.edit', $student) }}"
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
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
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

                                        <a href="{{ route('admin.tvet.students.details', $student) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            View Full Details
                                        </a>

                                        <button onclick="generateReport('{{ $student->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
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
                            <a href="{{ route('admin.tvet.students.create') }}"
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

    <!-- Table Footer -->
    @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $students->firstItem() }}</span> to
                <span class="font-medium">{{ $students->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($students->total()) }}</span> students
            </div>
            <div class="flex items-center space-x-2">
                @if($students->onFirstPage())
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
                    Page {{ $students->currentPage() }} of {{ $students->lastPage() }}
                </span>

                @if($students->hasMorePages())
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
    @endif
</div>

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

<!-- Bulk Action Modals -->
<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkDeleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Delete Students</h3>
                    <button onclick="closeModal('bulkDeleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkDeleteModalMessage">
                        Are you sure you want to delete <span id="bulkDeleteCount"></span> student(s)? This action cannot be undone.
                    </p>
                </div>
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.tvet.students.bulk.delete') }}">
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

<!-- Bulk Activate Modal -->
<div id="bulkActivateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkActivateModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Activate Students</h3>
                    <button onclick="closeModal('bulkActivateModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkActivateModalMessage">
                        Are you sure you want to activate <span id="bulkActivateCount"></span> student(s)?
                    </p>
                </div>
                <form id="bulkActivateForm" method="POST" action="{{ route('admin.tvet.students.bulk.activate') }}">
                    @csrf
                    <div id="bulkActivateInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkActivateModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkActivate()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Activate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Suspend Modal -->
<div id="bulkSuspendModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkSuspendModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Suspend Students</h3>
                    <button onclick="closeModal('bulkSuspendModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                        <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkSuspendModalMessage">
                        Are you sure you want to suspend <span id="bulkSuspendCount"></span> student(s)?
                    </p>
                </div>
                <form id="bulkSuspendForm" method="POST" action="{{ route('admin.tvet.students.bulk.suspend') }}">
                    @csrf
                    <div id="bulkSuspendInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkSuspendModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkSuspend()"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-pause-circle mr-2"></i>
                    Suspend
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Archive Modal -->
<div id="bulkArchiveModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkArchiveModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Archive Students</h3>
                    <button onclick="closeModal('bulkArchiveModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-archive text-gray-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkArchiveModalMessage">
                        Are you sure you want to archive <span id="bulkArchiveCount"></span> student(s)?
                    </p>
                </div>
                <form id="bulkArchiveForm" method="POST" action="{{ route('admin.tvet.students.bulk.archive') }}">
                    @csrf
                    <div id="bulkArchiveInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkArchiveModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkArchive()"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-archive mr-2"></i>
                    Archive
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
                            '#10B981', // active - green
                            '#6B7280', // inactive - gray
                            '#8B5CF6', // graduated - purple
                            '#EF4444', // dropped - red
                            '#F59E0B', // suspended - amber
                            '#3B82F6', // alumnus - blue
                            '#F59E0B', // prospective - amber
                            '#9CA3AF'  // historical - gray
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
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
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
    function viewStudent(studentId) {
        window.location.href = `/admin/tvet/students/${studentId}`;
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
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
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

        // Uncheck all checkboxes when hiding
        if (bulkActionsBar.classList.contains('hidden')) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const mainCheckbox = document.querySelector('th input[type="checkbox"]');
            if (mainCheckbox) mainCheckbox.checked = false;
        }
    }

    function getSelectedStudentIds() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    // ============ BULK ACTIONS ============
    function bulkActivate() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) {
            alert('Please select at least one student');
            return;
        }

        document.getElementById('bulkActivateCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkActivateInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkActivateModal');
    }

    function submitBulkActivate() {
        document.getElementById('bulkActivateForm').submit();
    }

    function bulkSuspend() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) {
            alert('Please select at least one student');
            return;
        }

        document.getElementById('bulkSuspendCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkSuspendInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkSuspendModal');
    }

    function submitBulkSuspend() {
        document.getElementById('bulkSuspendForm').submit();
    }

    function bulkArchive() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) {
            alert('Please select at least one student');
            return;
        }

        document.getElementById('bulkArchiveCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkArchiveInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkArchiveModal');
    }

    function submitBulkArchive() {
        document.getElementById('bulkArchiveForm').submit();
    }

    function bulkDelete() {
        const ids = getSelectedStudentIds();
        if (ids.length === 0) {
            alert('Please select at least one student');
            return;
        }

        document.getElementById('bulkDeleteCount').textContent = ids.length;

        const inputsDiv = document.getElementById('bulkDeleteInputs');
        inputsDiv.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            inputsDiv.appendChild(input);
        });

        openModal('bulkDeleteModal');
    }

    function submitBulkDelete() {
        document.getElementById('bulkDeleteForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(studentId) {
        const menu = document.getElementById(`actionMenu-${studentId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${studentId}`) {
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

    // ============ SINGLE STUDENT ACTIONS ============
    function updateStatus(studentId, action) {
        document.getElementById('statusStudentId').value = studentId;
        document.getElementById('statusAction').value = action;

        let title = '';
        let message = '';
        let icon = '';
        let iconBg = '';
        let actionText = '';

        switch(action) {
            case 'activate':
                title = 'Activate Student';
                message = 'Are you sure you want to activate this student?';
                icon = 'fa-check-circle';
                iconBg = 'bg-green-100 text-green-600';
                actionText = 'Activate';
                break;
            case 'suspend':
                title = 'Suspend Student';
                message = 'Are you sure you want to suspend this student?';
                icon = 'fa-pause-circle';
                iconBg = 'bg-yellow-100 text-yellow-600';
                actionText = 'Suspend';
                break;
        }

        document.getElementById('statusModalTitle').textContent = title;
        document.getElementById('statusModalMessage').textContent = message;
        document.getElementById('statusModalIconIcon').className = `fas ${icon} text-xl`;
        document.getElementById('statusModalIcon').className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${iconBg} mb-4`;
        document.getElementById('statusModalActionBtn').textContent = actionText;

        if (action === 'activate') {
            document.getElementById('statusForm').action = `/admin/tvet/students/${studentId}/activate`;
        } else if (action === 'suspend') {
            document.getElementById('statusForm').action = `/admin/tvet/students/${studentId}/suspend`;
        }

        openModal('statusModal');
    }

    function submitStatusForm() {
        document.getElementById('statusForm').submit();
    }

    function archiveStudent(studentId) {
        if (confirm('Are you sure you want to archive this student?')) {
            window.location.href = `/admin/tvet/students/${studentId}/archive`;
        }
    }

    function deleteStudent(studentId) {
        document.getElementById('deleteStudentId').value = studentId;
        document.getElementById('deleteForm').action = `/admin/tvet/students/${studentId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    function generateReport(studentId) {
        window.location.href = `/admin/tvet/students/${studentId}/report`;
    }

    // ============ MODAL FUNCTIONS ============
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Clear form inputs
        if (modalId === 'statusModal') {
            document.getElementById('statusNotes').value = '';
        }
    }

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        const menu = document.getElementById('chartMenu');
        menu.classList.toggle('hidden');
    }

    function exportChart(chartType) {
        let canvas;
        switch(chartType) {
            case 'status':
                canvas = document.getElementById('statusChart');
                break;
            case 'gender':
                canvas = document.getElementById('genderChart');
                break;
            case 'category':
                canvas = document.getElementById('categoryChart');
                break;
        }

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
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(toastContainer);
        }

        // Create toast
        const toast = document.createElement('div');
        toast.className = `flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 ${
            type === 'success' ? 'bg-green-50 border border-green-200' :
            type === 'error' ? 'bg-red-50 border border-red-200' :
            'bg-blue-50 border border-blue-200'
        }`;

        const icon = type === 'success' ? 'fa-check-circle text-green-600' :
                     type === 'error' ? 'fa-exclamation-circle text-red-600' :
                     'fa-info-circle text-blue-600';

        toast.innerHTML = `
            <div class="flex items-start">
                <i class="fas ${icon} mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium ${
                        type === 'success' ? 'text-green-800' :
                        type === 'error' ? 'text-red-800' :
                        'text-blue-800'
                    }">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
            if (toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 5000);
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
    #studentsTable {
        min-width: 1400px;
    }

    @media (max-width: 768px) {
        #studentsTable {
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

    /* Custom scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Card hover effect */
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endsection
