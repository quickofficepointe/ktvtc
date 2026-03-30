@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Registrations')
@section('subtitle', 'Manage student examination registrations')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Examinations</span>
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
    <a href="{{ route('admin.exam-registrations.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Registration</span>
    </a>
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
                <p class="text-sm font-medium text-gray-600">Registered</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($statusBreakdown['registered'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
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
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($statusBreakdown['completed'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pass Rate</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $successRate ?? 0 }}%</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-star text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">KNEC</p>
                <p class="text-lg font-bold text-gray-800">{{ $examBodyBreakdown['KNEC'] ?? 0 }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-university text-red-600 text-xs"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">CDACC</p>
                <p class="text-lg font-bold text-gray-800">{{ $examBodyBreakdown['CDACC'] ?? 0 }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-certificate text-blue-600 text-xs"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">NITA</p>
                <p class="text-lg font-bold text-gray-800">{{ $examBodyBreakdown['NITA'] ?? 0 }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-tools text-green-600 text-xs"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">TVETA</p>
                <p class="text-lg font-bold text-gray-800">{{ $examBodyBreakdown['TVETA'] ?? 0 }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-clipboard-check text-purple-600 text-xs"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter exam registrations by body, status, and date</p>
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
        <form id="filterForm" method="GET" action="{{ route('admin.exam-registrations.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Exam Body -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                    <select name="exam_body" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Bodies</option>
                        @foreach($examBodies as $body)
                            <option value="{{ $body }}" {{ request('exam_body') == $body ? 'selected' : '' }}>{{ $body }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                {{ ucfirst($statusOption) }}
                            </option>
                        @endforeach
                    </select>
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

                <!-- Student Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->student_number ?? 'No ID' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Enrollment Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment</label>
                    <select name="enrollment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Enrollments</option>
                        @foreach($enrollments as $enrollment)
                            <option value="{{ $enrollment->id }}" {{ request('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                                {{ $enrollment->course_name }} ({{ $enrollment->student_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Result -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Result</label>
                    <select name="result" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="Pass" {{ request('result') == 'Pass' ? 'selected' : '' }}>Pass</option>
                        <option value="Fail" {{ request('result') == 'Fail' ? 'selected' : '' }}>Fail</option>
                        <option value="Distinction" {{ request('result') == 'Distinction' ? 'selected' : '' }}>Distinction</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by student name, registration number, or index number..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.exam-registrations.index') }}"
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
                <span id="selectedCount">0</span> registration(s) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkGenerateSlips()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Generate Slips</span>
            </button>
            <button onclick="bulkUpdateStatus()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-sync-alt"></i>
                <span>Update Status</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Registrations Table -->
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
<a href="{{ route('admin.exam-registrations.reports.summary') }}"
                   class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                   title="Summary Report">
                    <i class="fas fa-chart-pie"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="registrationsTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Details</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($examRegistrations as $reg)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewRegistration('{{ $reg->id }}')">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}"
                               class="registration-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-primary">
                                    {{ substr($reg->enrollment->student_name ?? 'S', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $reg->enrollment->student_name ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $reg->enrollment->student_number ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                @if($reg->exam_body == 'KNEC') bg-red-100 text-red-800
                                @elseif($reg->exam_body == 'CDACC') bg-blue-100 text-blue-800
                                @elseif($reg->exam_body == 'NITA') bg-green-100 text-green-800
                                @elseif($reg->exam_body == 'TVETA') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $reg->exam_body ?? 'N/A' }}
                            </span>
                            <p class="text-xs text-gray-600 mt-1">{{ $reg->exam_type ?? 'N/A' }}</p>
                            @if($reg->exam_code)
                                <span class="text-xs text-gray-400">Code: {{ $reg->exam_code }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-mono font-medium text-gray-900">{{ $reg->registration_number ?? 'N/A' }}</span>
                        @if($reg->index_number)
                            <span class="text-xs text-gray-500 block">Index: {{ $reg->index_number }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">{{ $reg->exam_date ? $reg->exam_date->format('d/m/Y') : 'N/A' }}</div>
                        <div class="text-xs text-gray-500">Reg: {{ $reg->registration_date->format('d/m/Y') }}</div>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'registered' => 'green',
                                'active' => 'blue',
                                'completed' => 'purple',
                                'failed' => 'red',
                            ];
                            $color = $statusColors[$reg->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ ucfirst($reg->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($reg->result)
                            <span class="text-sm font-medium {{ in_array($reg->result, ['Pass', 'Distinction']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ $reg->result }}
                                @if($reg->grade)
                                    ({{ $reg->grade }})
                                @endif
                                @if($reg->score)
                                    <span class="text-xs text-gray-500 block">Score: {{ $reg->score }}%</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-400">Not available</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($reg->certificate_number)
                            <div class="text-sm text-green-600">
                                <i class="fas fa-certificate mr-1"></i>
                                Issued
                            </div>
                            <div class="text-xs text-gray-500">{{ $reg->certificate_number }}</div>
                        @else
                            <span class="text-gray-400">Not issued</span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.exam-registrations.show', $reg) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.exam-registrations.print-slip', $reg) }}" target="_blank"
                               class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors"
                               title="Print Exam Slip">
                                <i class="fas fa-print"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $reg->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $reg->id }}"
                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($reg->status == 'pending')
                                        <button onclick="markRegistered('{{ $reg->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Mark Registered
                                        </button>
                                        @endif
                                        @if(in_array($reg->status, ['registered', 'active']) && !$reg->result)
                                        <button onclick="enterResult('{{ $reg->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pen mr-2"></i>
                                            Enter Result
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.exam-registrations.edit', $reg) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit
                                        </a>
                                        <hr class="my-1 border-gray-200">
                                        <button onclick="deleteRegistration('{{ $reg->id }}')"
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
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-file-alt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No exam registrations found</p>
                            <p class="text-gray-400 text-sm mt-1">Register students for their first exam</p>
                            <a href="{{ route('admin.exam-registrations.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                New Registration
                            </a>
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

<!-- Result Entry Modal -->
<div id="resultModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('resultModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Enter Exam Result</h3>
                    <button onclick="closeModal('resultModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="resultForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Result</label>
                            <select name="result" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Select Result</option>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                                <option value="Distinction">Distinction</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                            <input type="text" name="grade" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., A, B+, Credit">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                            <input type="number" name="score" min="0" max="100" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., 75.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Result Date</label>
                            <input type="date" name="result_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number</label>
                            <input type="text" name="certificate_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="If issued">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('resultModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitResult()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Save Result</button>
            </div>
        </div>
    </div>
</div>

<!-- Mark Registered Modal -->
<div id="markRegisteredModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('markRegisteredModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Mark as Registered</h3>
                    <button onclick="closeModal('markRegisteredModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="markRegisteredForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Number</label>
                            <input type="text" name="registration_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., KNEC/2024/12345">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('markRegisteredModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitMarkRegistered()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Mark Registered</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Status Modal -->
<div id="bulkStatusModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkStatusModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Update Status</h3>
                    <button onclick="closeModal('bulkStatusModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
               <form id="bulkStatusForm" method="POST" action="{{ route('admin.exam-registrations.bulk.status') }}">
                    @csrf
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600" id="bulkStatusMessage"></p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">New Status</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="registered">Registered</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div id="bulkStatusInputs"></div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkStatusModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitBulkStatus()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Registration</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600">Are you sure you want to delete this registration?</p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ TABLE FUNCTIONS ============
    function viewRegistration(id) {
        window.location.href = `/admin/exam-registrations/${id}`;
    }

    function refreshTable() {
        location.reload();
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        document.querySelectorAll('.registration-checkbox').forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;

        const bulkBar = document.getElementById('bulkActionsBar');
        if (count > 0) bulkBar.classList.remove('hidden');
        else bulkBar.classList.add('hidden');
    }

    function toggleBulkActions() {
        const bulkBar = document.getElementById('bulkActionsBar');
        bulkBar.classList.toggle('hidden');
        if (bulkBar.classList.contains('hidden')) {
            document.querySelectorAll('.registration-checkbox').forEach(cb => cb.checked = false);
            document.querySelector('th input[type="checkbox"]').checked = false;
        }
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.registration-checkbox:checked')).map(cb => cb.value);
    }

    function bulkGenerateSlips() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one registration');
            return;
        }
        // Generate PDF with all selected slips
        window.open(`/admin/exam-registrations/bulk-slips?ids=${ids.join(',')}`, '_blank');
    }

    function bulkUpdateStatus() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one registration');
            return;
        }
        document.getElementById('bulkStatusMessage').textContent =
            `Update status for ${ids.length} selected registration(s)`;
        const inputs = document.getElementById('bulkStatusInputs');
        inputs.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            inputs.appendChild(input);
        });
        openModal('bulkStatusModal');
    }

    function submitBulkStatus() {
        document.getElementById('bulkStatusForm').submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(id) {
        document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        const menu = document.getElementById(`actionMenu-${id}`);
        if (menu) menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // ============ SINGLE ACTIONS ============
    function markRegistered(id) {
        document.getElementById('markRegisteredForm').action = `/admin/exam-registrations/${id}/mark-registered`;
        openModal('markRegisteredModal');
    }

    function submitMarkRegistered() {
        document.getElementById('markRegisteredForm').submit();
    }

    function enterResult(id) {
        document.getElementById('resultForm').action = `/admin/exam-registrations/${id}/enter-results`;
        openModal('resultModal');
    }

    function submitResult() {
        document.getElementById('resultForm').submit();
    }

    function deleteRegistration(id) {
        document.getElementById('deleteForm').action = `/admin/exam-registrations/${id}`;
        openModal('deleteModal');
    }

    function submitDelete() {
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

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['resultModal', 'markRegisteredModal', 'bulkStatusModal', 'deleteModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
            });
            document.body.style.overflow = 'auto';
        }
    });

    // Quick search
    document.getElementById('tableSearch')?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            const params = new URLSearchParams(window.location.search);
            params.set('search', this.value);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }
    });
</script>

<style>
    #registrationsTable {
        min-width: 1400px;
    }
    @media (max-width: 768px) {
        #registrationsTable {
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
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .required:after {
        content: " *";
        color: #EF4444;
    }
</style>
@endsection
