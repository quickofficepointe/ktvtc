@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollments')
@section('subtitle', 'Manage student course enrollments')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">All Enrollments</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.enrollments.export') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
    <a href="{{ route('admin.enrollments.create') }}"
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
                <i class="fas fa-book-open text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($activeEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-play-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($completedEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
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
                <i class="fas fa-clock text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Exam Required</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($requiresExamRegistration ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-file-alt text-blue-600 text-xl"></i>
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
                <p class="text-sm text-gray-600 mt-1">Filter enrollments by status, intake, and more</p>
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
        <form id="filterForm" action="{{ route('admin.enrollments.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="dropped" {{ request('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
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

                <!-- Intake Month -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intake Month</label>
                    <select name="intake_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Months</option>
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                            <option value="{{ $month }}" {{ request('intake_month') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Intake Year -->
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

                <!-- Payment Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>

                <!-- Exam Required -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Required</label>
                    <select name="exam_required" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('exam_required') == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('exam_required') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by student name, ID, or course..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.enrollments.index') }}"
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
                <i class="fas fa-play-circle"></i>
                <span>Activate</span>
            </button>
            <button onclick="bulkComplete()"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
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
                <p class="text-sm text-gray-600 mt-1">Click on any enrollment to view details</p>
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
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                            <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-primary">
                                    {{ substr($enrollment->student_name ?? $enrollment->student->first_name ?? 'S', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $enrollment->student_name ?? ($enrollment->student->full_name ?? 'N/A') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $enrollment->student_number ?? ($enrollment->student->student_number ?? 'No ID') }}</p>
                                @if($enrollment->legacy_code)
                                    <span class="text-xs text-gray-400">Legacy: {{ $enrollment->legacy_code }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm text-gray-900">{{ $enrollment->course_name ?? ($enrollment->course->name ?? 'N/A') }}</p>
                        <p class="text-xs text-gray-500">{{ $enrollment->course_code ?? ($enrollment->course->code ?? '') }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            <span class="text-gray-900">{{ $enrollment->intake_month }} {{ $enrollment->intake_year }}</span>
                            <span class="text-xs text-gray-500 block">{{ ucfirst(str_replace('_', ' ', $enrollment->study_mode ?? '')) }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $enrollment->campus_name ?? ($enrollment->campus->name ?? 'N/A') }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-bold text-gray-900">KES {{ number_format($enrollment->total_fees, 2) }}</p>
                            <div class="flex items-center mt-1">
                                <div class="w-16 bg-gray-200 rounded-full h-1.5 mr-2">
                                    <div class="bg-primary rounded-full h-1.5" style="width: {{ $enrollment->payment_progress }}%"></div>
                                </div>
                                <span class="text-xs {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Bal: KES {{ number_format($enrollment->balance, 2) }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
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
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($enrollment->requires_external_exam)
                            @if($enrollment->exam_registrations_count > 0)
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Registered
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Pending
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-times mr-1"></i>
                                No Exam
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               title="Edit Enrollment">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $enrollment->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $enrollment->id }}"
                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($enrollment->status != 'active')
                                            <button onclick="updateStatus('{{ $enrollment->id }}', 'activate')"
                                                    class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                                <i class="fas fa-play-circle mr-2"></i>
                                                Activate
                                            </button>
                                        @endif
                                        @if($enrollment->status != 'completed')
                                            <button onclick="updateStatus('{{ $enrollment->id }}', 'complete')"
                                                    class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Mark Completed
                                            </button>
                                        @endif
                                        @if($enrollment->requires_external_exam && $enrollment->exam_registrations_count == 0)
                                            <a href="{{ route('admin.exam-registrations.create', ['enrollment_id' => $enrollment->id]) }}"
                                               class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                                <i class="fas fa-file-alt mr-2"></i>
                                                Register Exam
                                            </a>
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
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-book-open text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No enrollments found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first enrollment</p>
                            <a href="{{ route('admin.enrollments.create') }}"
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
    @if($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator)
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

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkDeleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.enrollments.bulk.delete') }}">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkDeleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitBulkDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal (Single) -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
                    <p class="text-center text-gray-600">
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
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitDeleteForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ TABLE FUNCTIONS ============
    function viewEnrollment(id) {
        window.location.href = `/admin/enrollments/${id}`;
    }

    function refreshTable() {
        location.reload();
    }

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;

        const bulkBar = document.getElementById('bulkActionsBar');
        if (count > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    function toggleBulkActions() {
        const bulkBar = document.getElementById('bulkActionsBar');
        bulkBar.classList.toggle('hidden');

        if (bulkBar.classList.contains('hidden')) {
            document.querySelectorAll('.enrollment-checkbox').forEach(cb => cb.checked = false);
            document.querySelector('th input[type="checkbox"]').checked = false;
        }
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.enrollment-checkbox:checked')).map(cb => cb.value);
    }

    function bulkActivate() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one enrollment');
            return;
        }

        if (confirm(`Activate ${ids.length} enrollment(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.enrollments.bulk.activate") }}';
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
    }

    function bulkComplete() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('Please select at least one enrollment');
            return;
        }

        if (confirm(`Mark ${ids.length} enrollment(s) as completed?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.enrollments.bulk.complete") }}';
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
    }

    function bulkDelete() {
        const ids = getSelectedIds();
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
    function toggleActionMenu(id) {
        document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        const menu = document.getElementById(`actionMenu-${id}`);
        if (menu) menu.classList.toggle('hidden');
    }

    // Close action menus when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // ============ SINGLE ACTIONS ============
    function updateStatus(id, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/enrollments/${id}/activate`
            : `/admin/enrollments/${id}/complete`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteEnrollment(id) {
        document.getElementById('deleteForm').action = `/admin/enrollments/${id}`;
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

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('bulkDeleteModal');
            closeModal('deleteModal');
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
    #enrollmentsTable {
        min-width: 1400px;
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
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
