@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Registrations Management')
@section('subtitle', 'Manage student course registrations')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Admin</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Registrations</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="showCreateModal()" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>New Registration</span>
    </button>
    <button onclick="showBulkActionsModal()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-tasks"></i>
        <span>Bulk Actions</span>
    </button>
    <button onclick="showExportModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalRegistrations }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Active Registrations</p>
                <p class="text-2xl font-bold text-green-600">{{ $activeRegistrations }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pending Registrations</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $pendingRegistrations }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Balance</p>
                <p class="text-2xl font-bold text-red-600">KES {{ number_format($totalBalance, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6">
        <form id="filterForm" method="GET" action="{{ route('admin.registrations.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->admission_number ?? 'N/A' }})
                            </option>
                        @endforeach
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <select name="academic_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intake Month</label>
                    <select name="intake_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Months</option>
                        @foreach($intakeMonths as $month)
                            <option value="{{ $month }}" {{ request('intake_month') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode</label>
                    <select name="study_mode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Modes</option>
                        @foreach($studyModes as $mode)
                            <option value="{{ $mode }}" {{ request('study_mode') == $mode ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $mode)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <div class="grid grid-cols-2 gap-2 w-full">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Clear Filters
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Registrations Table -->
<div class="bg-white rounded-xl border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Registrations</h3>
            <div class="flex items-center space-x-2">
                <div class="text-sm text-gray-600">
                    Showing {{ $registrations->firstItem() }} - {{ $registrations->lastItem() }} of {{ $registrations->total() }}
                </div>
                <select id="perPage" class="px-3 py-1 border border-gray-300 rounded-lg text-sm" onchange="updatePerPage(this.value)">
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 per page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if($registrations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Reg No</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($registrations as $registration)
                        <tr class="hover:bg-gray-50" id="row-{{ $registration->id }}">
                            <td class="py-3 px-4">
                                <input type="checkbox" class="row-checkbox" value="{{ $registration->id }}" onchange="updateSelectAll()">
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $registration->registration_number }}</div>
                                <div class="text-sm text-gray-600">{{ $registration->student_number }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $registration->student->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ $registration->student->email ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-gray-900">{{ $registration->course->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ $registration->academic_year }} - {{ $registration->intake_month }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-gray-900">{{ $registration->campus->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ ucfirst($registration->study_mode) }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'provisional' => 'bg-blue-100 text-blue-800',
                                        'registered' => 'bg-green-100 text-green-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'behind_payment' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'suspended' => 'bg-orange-100 text-orange-800',
                                        'withdrawn' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$registration->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                </span>
                                @if($registration->cdacc_status != 'pending')
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                        CDACC: {{ ucfirst($registration->cdacc_status) }}
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $registration->completion_percentage }}%"></div>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-600">{{ round($registration->completion_percentage) }}%</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Month {{ $registration->current_month }} of {{ $registration->total_course_months }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium {{ $registration->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    KES {{ number_format($registration->balance, 2) }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Paid: KES {{ number_format($registration->amount_paid, 2) }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRegistration({{ $registration->id }})"
                                            class="p-1 text-blue-600 hover:text-blue-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editRegistration({{ $registration->id }})"
                                            class="p-1 text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="recordPayment({{ $registration->id }})"
                                            class="p-1 text-green-600 hover:text-green-800" title="Record Payment">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                    <div class="relative group">
                                        <button class="p-1 text-gray-600 hover:text-gray-800">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10 hidden group-hover:block">
                                            <div class="py-1">
                                                @if($registration->status == 'pending')
                                                <button onclick="approveRegistration({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50">
                                                    <i class="fas fa-check mr-2"></i> Approve
                                                </button>
                                                @endif
                                                @if($registration->status == 'registered' || $registration->status == 'provisional')
                                                <button onclick="activateRegistration({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">
                                                    <i class="fas fa-play mr-2"></i> Activate
                                                </button>
                                                @endif
                                                @if($registration->current_month < $registration->total_course_months && $registration->can_proceed_to_next_month)
                                                <button onclick="advanceMonth({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-purple-50">
                                                    <i class="fas fa-forward mr-2"></i> Advance Month
                                                </button>
                                                @endif
                                                @if($registration->current_month == $registration->total_course_months && $registration->balance == 0)
                                                <button onclick="completeRegistration({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                                    <i class="fas fa-graduation-cap mr-2"></i> Complete
                                                </button>
                                                @endif
                                                <button onclick="generateAdmissionLetter({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                                    <i class="fas fa-file-pdf mr-2"></i> Admission Letter
                                                </button>
                                                <button onclick="generateFeeStructure({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                                    <i class="fas fa-file-invoice-dollar mr-2"></i> Fee Structure
                                                </button>
                                                <button onclick="deleteRegistration({{ $registration->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-2"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $registrations->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Registrations Found</h3>
                <p class="text-gray-600 mb-6">Get started by creating a new registration.</p>
                <button onclick="showCreateModal()" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Create Registration
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="registrationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('registrationModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Create New Registration</h3>
                    <button onclick="closeModal('registrationModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="registrationForm" method="POST">
                    @csrf
                    <div id="formMethod" style="display: none;"></div>

                    <div class="space-y-6">
                        <!-- Student Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                <select name="student_id" id="student_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        onchange="loadStudentApplications(this.value)">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->admission_number ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Application (Optional)</label>
                                <select name="application_id" id="application_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">No Application</option>
                                </select>
                            </div>
                        </div>

                        <!-- Course and Campus -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Campus *</label>
                                <select name="campus_id" id="campus_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        onchange="loadFeeStructure()">
                                    <option value="">Select Campus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select name="course_id" id="course_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        onchange="loadFeeStructure()">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Academic Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                                <select name="academic_year" id="academic_year" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Intake Month *</label>
                                <select name="intake_month" id="intake_month" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Month</option>
                                    @foreach($intakeMonths as $month)
                                        <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode *</label>
                                <select name="study_mode" id="study_mode" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @foreach($studyModes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Course Months *</label>
                                <input type="number" name="total_course_months" id="total_course_months" required min="1" max="48"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Completion</label>
                                <input type="date" name="expected_completion_date" id="expected_completion_date" readonly
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                        </div>

                        <!-- Fee Details -->
                        <div id="feeStructureSection" class="hidden">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-700 mb-3">Fee Structure</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Registration Fee</label>
                                        <input type="number" name="registration_fee" id="registration_fee" step="0.01" min="0"
                                               class="w-full px-3 py-1 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Monthly Tuition</label>
                                        <input type="number" name="tuition_per_month" id="tuition_per_month" step="0.01" min="0"
                                               class="w-full px-3 py-1 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Caution Money</label>
                                        <input type="number" name="caution_money" id="caution_money" step="0.01" min="0"
                                               class="w-full px-3 py-1 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">CDACC Registration</label>
                                        <input type="number" name="cdacc_registration_fee" id="cdacc_registration_fee" step="0.01" min="0"
                                               class="w-full px-3 py-1 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">CDACC Exam Fee</label>
                                        <input type="number" name="cdacc_examination_fee" id="cdacc_examination_fee" step="0.01" min="0"
                                               class="w-full px-3 py-1 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Total Course Fee</label>
                                        <input type="number" name="total_course_fee" id="total_course_fee" step="0.01" min="0" readonly
                                               class="w-full px-3 py-1 border border-gray-300 rounded bg-gray-50">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Plan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Plan *</label>
                                <select name="payment_plan" id="payment_plan" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @foreach($paymentPlans as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Due Day</label>
                                <input type="number" name="monthly_due_day" id="monthly_due_day" min="1" max="31" value="5"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Official Email (Optional)</label>
                            <input type="email" name="official_email" id="official_email"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('registrationModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitRegistrationForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Save Registration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="viewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('viewModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Registration Details</h3>
                    <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="viewContent" class="space-y-6">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeModal('viewModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Close
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
                <form id="paymentForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                            <input type="number" name="amount" required step="0.01" min="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                            <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                            <select name="payment_method" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number (Optional)</label>
                            <input type="text" name="reference_number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                            <textarea name="description" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('paymentModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitPaymentForm()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkActionsModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Actions</h3>
                    <button onclick="closeModal('bulkActionsModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Action</label>
                        <select id="bulkAction" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select Action</option>
                            <option value="approve">Approve Registrations</option>
                            <option value="activate">Activate Registrations</option>
                            <option value="complete">Complete Registrations</option>
                            <option value="archive">Archive Registrations</option>
                            <option value="delete">Delete Registrations</option>
                        </select>
                    </div>
                    <div id="rejectReasonSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                        <textarea id="rejectReason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                            <span class="text-sm text-yellow-700" id="selectedCount">0 registrations selected</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkActionsModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="processBulkAction()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                    Process Action
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('exportModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Export Registrations</h3>
                    <button onclick="closeModal('exportModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="exportForm" method="POST" action="{{ route('admin.registrations.export') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <select name="format" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p>The export will include all registrations matching your current filters.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('exportModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="document.getElementById('exportForm').submit()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    Export
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Registration</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-700">Are you sure you want to delete this registration? This action cannot be undone.</p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Delete Registration
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRegistrationId = null;
    let selectedIds = [];

    // Modal functions
    function openModal(modalId, size = 'lg') {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Add size class
        const modalContent = modal.querySelector('.modal-content');
        modalContent.className = modalContent.className.replace(/sm:max-w-\w+/, `sm:max-w-${size}`);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Selection functions
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateSelectedIds();
    }

    function updateSelectAll() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const selectAll = document.getElementById('selectAll');

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;

        updateSelectedIds();
    }

    function updateSelectedIds() {
        selectedIds = [];
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });

        document.getElementById('selectedCount').textContent = `${selectedIds.length} registrations selected`;
    }

    // Form functions
    function showCreateModal() {
        document.getElementById('modalTitle').textContent = 'Create New Registration';
        document.getElementById('registrationForm').reset();
        document.getElementById('formMethod').innerHTML = '';
        document.getElementById('feeStructureSection').classList.add('hidden');
        openModal('registrationModal', '4xl');
    }

    async function editRegistration(id) {
        try {
            const response = await fetch(`/admin/registrations/${id}/edit`, {
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                const reg = data.data.registration;

                document.getElementById('modalTitle').textContent = 'Edit Registration';
                document.getElementById('formMethod').innerHTML = `
                    <input type="hidden" name="_method" value="PUT">
                `;

                // Populate form fields
                document.getElementById('student_id').value = reg.student_id;
                document.getElementById('application_id').value = reg.application_id;
                document.getElementById('campus_id').value = reg.campus_id;
                document.getElementById('course_id').value = reg.course_id;
                document.getElementById('academic_year').value = reg.academic_year;
                document.getElementById('intake_month').value = reg.intake_month;
                document.getElementById('study_mode').value = reg.study_mode;
                document.getElementById('start_date').value = reg.start_date.split('T')[0];
                document.getElementById('total_course_months').value = reg.total_course_months;
                document.getElementById('expected_completion_date').value = reg.expected_completion_date.split('T')[0];
                document.getElementById('registration_fee').value = reg.registration_fee;
                document.getElementById('tuition_per_month').value = reg.tuition_per_month;
                document.getElementById('caution_money').value = reg.caution_money;
                document.getElementById('cdacc_registration_fee').value = reg.cdacc_registration_fee;
                document.getElementById('cdacc_examination_fee').value = reg.cdacc_examination_fee;
                document.getElementById('total_course_fee').value = reg.total_course_fee;
                document.getElementById('payment_plan').value = reg.payment_plan;
                document.getElementById('monthly_due_day').value = reg.monthly_due_day;
                document.getElementById('official_email').value = reg.official_email || '';
                document.getElementById('notes').value = reg.notes || '';

                document.getElementById('feeStructureSection').classList.remove('hidden');

                currentRegistrationId = id;
                openModal('registrationModal', '4xl');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load registration data');
        }
    }

    async function submitRegistrationForm() {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        const url = currentRegistrationId
            ? `/admin/registrations/${currentRegistrationId}`
            : '/admin/registrations';
        const method = currentRegistrationId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                closeModal('registrationModal');
                window.location.reload();
            } else {
                alert(data.message);
                if (data.errors) {
                    console.error(data.errors);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to save registration');
        }
    }

    // View registration details
    async function viewRegistration(id) {
        try {
            const response = await fetch(`/admin/registrations/${id}`, {
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                const reg = data.data.registration;
                const progress = data.data.progress;
                const monthlyPayments = data.data.monthly_payments;
                const requirements = data.data.requirements;
                const documents = data.data.documents;

                let html = `
                    <div class="space-y-6">
                        <!-- Header -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-lg">${reg.registration_number}</h4>
                                    <p class="text-gray-600">${reg.student_number}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-${getStatusColor(reg.status)}">
                                        ${formatStatus(reg.status)}
                                    </span>
                                    <p class="text-sm text-gray-600 mt-1">${reg.academic_year} - ${reg.intake_month}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Student Info -->
                        <div>
                            <h5 class="font-medium text-gray-700 mb-2">Student Information</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Name</p>
                                    <p class="font-medium">${reg.student?.name || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-medium">${reg.official_email || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Course</p>
                                    <p class="font-medium">${reg.course?.name || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Campus</p>
                                    <p class="font-medium">${reg.campus?.name || 'N/A'}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div>
                            <h5 class="font-medium text-gray-700 mb-2">Course Progress</h5>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm text-gray-600">Progress</span>
                                    <span class="text-sm font-medium">${progress.completion_percentage}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: ${progress.completion_percentage}%"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Current Month</p>
                                        <p class="font-medium">${progress.current_month} of ${progress.total_months}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Months Remaining</p>
                                        <p class="font-medium">${progress.months_remaining}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Start Date</p>
                                        <p class="font-medium">${formatDate(reg.start_date)}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Expected Completion</p>
                                        <p class="font-medium">${formatDate(reg.expected_completion_date)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div>
                            <h5 class="font-medium text-gray-700 mb-2">Financial Summary</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Total Course Fee</p>
                                    <p class="text-xl font-bold text-green-700">KES ${formatCurrency(reg.total_course_fee)}</p>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Amount Paid</p>
                                    <p class="text-xl font-bold text-blue-700">KES ${formatCurrency(reg.amount_paid)}</p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Balance Due</p>
                                    <p class="text-xl font-bold text-red-700">KES ${formatCurrency(reg.balance)}</p>
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
                                Payment Status: <span class="font-medium">${progress.payment_status}</span>
                            </div>
                        </div>

                        <!-- Monthly Payments -->
                        <div>
                            <h5 class="font-medium text-gray-700 mb-2">Monthly Payment Schedule</h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-2 px-3 text-left">Month</th>
                                            <th class="py-2 px-3 text-left">Due Date</th>
                                            <th class="py-2 px-3 text-left">Amount</th>
                                            <th class="py-2 px-3 text-left">Status</th>
                                            <th class="py-2 px-3 text-left">Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                `;

                for (let month = 1; month <= reg.total_course_months; month++) {
                    const payment = monthlyPayments[`month_${month}`] || {};
                    html += `
                        <tr>
                            <td class="py-2 px-3">Month ${month}</td>
                            <td class="py-2 px-3">${payment.due_date ? formatDate(payment.due_date) : 'N/A'}</td>
                            <td class="py-2 px-3">KES ${formatCurrency(payment.amount || 0)}</td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-1 rounded text-xs ${getPaymentStatusColor(payment.status)}">
                                    ${payment.status ? payment.status.charAt(0).toUpperCase() + payment.status.slice(1) : 'Pending'}
                                </span>
                            </td>
                            <td class="py-2 px-3">${payment.paid_date ? formatDate(payment.paid_date) : '-'}</td>
                        </tr>
                    `;
                }

                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('viewContent').innerHTML = html;
                openModal('viewModal', '6xl');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load registration details');
        }
    }

    // Payment functions
    function recordPayment(id) {
        currentRegistrationId = id;
        document.getElementById('paymentForm').reset();
        openModal('paymentModal');
    }

    async function submitPaymentForm() {
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);

        try {
            const response = await fetch(`/admin/registrations/${currentRegistrationId}/record-payment`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                closeModal('paymentModal');
                window.location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to record payment');
        }
    }

    // Action functions
    async function approveRegistration(id) {
        if (confirm('Approve this registration?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to approve registration');
            }
        }
    }

    async function activateRegistration(id) {
        if (confirm('Activate this registration?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/activate`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to activate registration');
            }
        }
    }

    async function advanceMonth(id) {
        if (confirm('Advance to next month?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/advance-month`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to advance month');
            }
        }
    }

    async function completeRegistration(id) {
        if (confirm('Mark registration as completed?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to complete registration');
            }
        }
    }

    function deleteRegistration(id) {
        currentRegistrationId = id;
        openModal('deleteModal');
    }

    async function confirmDelete() {
        try {
            const response = await fetch(`/admin/registrations/${currentRegistrationId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                closeModal('deleteModal');
                document.getElementById(`row-${currentRegistrationId}`).remove();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to delete registration');
        }
    }

    // Bulk actions
    function showBulkActionsModal() {
        if (selectedIds.length === 0) {
            alert('Please select at least one registration');
            return;
        }

        openModal('bulkActionsModal');
    }

    async function processBulkAction() {
        const action = document.getElementById('bulkAction').value;

        if (!action) {
            alert('Please select an action');
            return;
        }

        const data = {
            action: action,
            ids: selectedIds
        };

        if (action === 'reject') {
            data.reason = document.getElementById('rejectReason').value;
            if (!data.reason.trim()) {
                alert('Please provide a rejection reason');
                return;
            }
        }

        try {
            const response = await fetch('/admin/registrations/bulk-actions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert(`${result.processed} registrations processed successfully.${result.failed > 0 ? ` ${result.failed} failed.` : ''}`);
                closeModal('bulkActionsModal');
                window.location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to process bulk action');
        }
    }

    // Export
    function showExportModal() {
        openModal('exportModal');
    }

    // AJAX functions
    async function loadStudentApplications(studentId) {
        if (!studentId) return;

        try {
            const response = await fetch(`/admin/registrations/get-applications?student_id=${studentId}`);
            const data = await response.json();

            const select = document.getElementById('application_id');
            select.innerHTML = '<option value="">No Application</option>';

            if (data.success && data.data.length > 0) {
                data.data.forEach(app => {
                    const option = document.createElement('option');
                    option.value = app.id;
                    option.textContent = `${app.course?.name || 'N/A'} - ${app.campus?.name || 'N/A'}`;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading applications:', error);
        }
    }

    async function loadFeeStructure() {
        const courseId = document.getElementById('course_id').value;
        const campusId = document.getElementById('campus_id').value;

        if (!courseId || !campusId) return;

        try {
            const response = await fetch(`/admin/registrations/get-fee-structure?course_id=${courseId}&campus_id=${campusId}`);
            const data = await response.json();

            const section = document.getElementById('feeStructureSection');

            if (data.success) {
                const fee = data.data;
                document.getElementById('registration_fee').value = fee.registration_fee || 0;
                document.getElementById('tuition_per_month').value = fee.tuition_per_month || 0;
                document.getElementById('caution_money').value = fee.caution_money || 0;
                document.getElementById('cdacc_registration_fee').value = fee.cdacc_registration_fee || 0;
                document.getElementById('cdacc_examination_fee').value = fee.cdacc_examination_fee || 0;

                calculateTotalFee();
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error loading fee structure:', error);
            document.getElementById('feeStructureSection').classList.add('hidden');
        }
    }

    function calculateTotalFee() {
        const months = parseInt(document.getElementById('total_course_months').value) || 0;
        const tuition = parseFloat(document.getElementById('tuition_per_month').value) || 0;
        const registration = parseFloat(document.getElementById('registration_fee').value) || 0;
        const caution = parseFloat(document.getElementById('caution_money').value) || 0;
        const cdaccReg = parseFloat(document.getElementById('cdacc_registration_fee').value) || 0;
        const cdaccExam = parseFloat(document.getElementById('cdacc_examination_fee').value) || 0;

        const total = (tuition * months) + registration + caution + cdaccReg + cdaccExam;
        document.getElementById('total_course_fee').value = total.toFixed(2);

        // Calculate expected completion date
        const startDate = document.getElementById('start_date').value;
        if (startDate && months > 0) {
            const date = new Date(startDate);
            date.setMonth(date.getMonth() + months);
            document.getElementById('expected_completion_date').value = date.toISOString().split('T')[0];
        }
    }

    // Event listeners
    document.getElementById('total_course_months').addEventListener('input', calculateTotalFee);
    document.getElementById('tuition_per_month').addEventListener('input', calculateTotalFee);
    document.getElementById('registration_fee').addEventListener('input', calculateTotalFee);
    document.getElementById('caution_money').addEventListener('input', calculateTotalFee);
    document.getElementById('cdacc_registration_fee').addEventListener('input', calculateTotalFee);
    document.getElementById('cdacc_examination_fee').addEventListener('input', calculateTotalFee);
    document.getElementById('start_date').addEventListener('change', calculateTotalFee);

    document.getElementById('bulkAction').addEventListener('change', function() {
        document.getElementById('rejectReasonSection').classList.toggle('hidden', this.value !== 'reject');
    });

    // Utility functions
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatCurrency(amount) {
        return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'yellow-100 text-yellow-800',
            'provisional': 'blue-100 text-blue-800',
            'registered': 'green-100 text-green-800',
            'active': 'green-100 text-green-800',
            'behind_payment': 'red-100 text-red-800',
            'completed': 'purple-100 text-purple-800',
            'suspended': 'orange-100 text-orange-800',
            'withdrawn': 'gray-100 text-gray-800'
        };
        return colors[status] || 'gray-100 text-gray-800';
    }

    function getPaymentStatusColor(status) {
        const colors = {
            'paid': 'bg-green-100 text-green-800',
            'partial': 'bg-blue-100 text-blue-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'overdue': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function updatePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    }

    // Document generation
    async function generateAdmissionLetter(id) {
        if (confirm('Generate admission letter?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/generate-admission-letter`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Admission letter generated successfully!');
                    if (data.data.download_url) {
                        window.open(data.data.download_url, '_blank');
                    }
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to generate admission letter');
            }
        }
    }

    async function generateFeeStructure(id) {
        if (confirm('Generate fee structure?')) {
            try {
                const response = await fetch(`/admin/registrations/${id}/generate-fee-structure`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Fee structure generated successfully!');
                    if (data.data.download_url) {
                        window.open(data.data.download_url, '_blank');
                    }
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to generate fee structure');
            }
        }
    }
</script>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    .group:hover .group-hover\:block {
        display: block;
    }
</style>
@endsection
