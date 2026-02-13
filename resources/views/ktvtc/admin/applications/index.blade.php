@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Course Applications')
@section('subtitle', 'Manage and review all course applications')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Applications</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Course Applications</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportToExcel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Excel</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Applications</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalApplications ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-alt text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-arrow-up text-success mr-1"></i>
                <span>+8% from last month</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Review</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $pendingApplications ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                <i class="fas fa-clock text-warning text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-hourglass-half text-warning mr-1"></i>
                <span>Awaiting review</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Accepted</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $acceptedApplications ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-user-check text-success mr-1"></i>
                <span>Ready for admission</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Waiting List</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $waitingListApplications ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-list-ol text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-users text-purple-600 mr-1"></i>
                <span>Pending admission slots</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
        <p class="text-sm text-gray-600 mt-1">Filter applications by status, course, and date</p>
    </div>
    <div class="p-6">
        <form id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="waiting_list">Waiting List</option>
                    </select>
                </div>

                <!-- Course Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Campus Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" name="date_from" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" name="date_to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <button type="reset" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">All Applications</h3>
                <p class="text-sm text-gray-600 mt-1">Click on any application to view details</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search applications..."
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
        <table class="w-full" id="applicationsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App #</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applications as $application)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewApplication('{{ $application->id }}')">
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-file-alt text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $application->application_number }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $application->first_name }} {{ $application->last_name }}</p>
                            <div class="flex items-center mt-1">
                                <span class="text-xs px-2 py-1 rounded {{ $application->id_type === 'birth_certificate' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $application->id_type === 'birth_certificate' ? 'Birth Cert' : 'National ID' }}
                                </span>
                                <span class="text-xs text-gray-500 ml-2">{{ $application->id_number }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">{{ $application->course->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $application->study_mode_label }}</div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $application->campus->name ?? 'Not Specified' }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">{{ $application->intake_period }}</div>
                        <div class="text-xs text-gray-500">{{ $application->education_level }}</div>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'under_review' => 'bg-blue-100 text-blue-800',
                                'accepted' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'waiting_list' => 'bg-purple-100 text-purple-800'
                            ];
                            $color = $statusColors[$application->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $color }}">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            {{ $application->status_label }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-600">
                            {{ $application->submitted_at->format('M j, Y') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $application->submitted_at->format('g:i A') }}
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewApplication('{{ $application->id }}')"
                                    class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $application->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $application->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        <button onclick="updateStatus('{{ $application->id }}', 'accepted')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check mr-2"></i>
                                            Accept Application
                                        </button>
                                        <button onclick="updateStatus('{{ $application->id }}', 'under_review')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-search mr-2"></i>
                                            Mark as Review
                                        </button>
                                        <button onclick="updateStatus('{{ $application->id }}', 'waiting_list')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-list-ol mr-2"></i>
                                            Move to Waiting List
                                        </button>
                                        <button onclick="updateStatus('{{ $application->id }}', 'rejected')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-times mr-2"></i>
                                            Reject Application
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No applications found</p>
                            <p class="text-gray-400 text-sm mt-1">Start receiving applications by promoting your courses</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    @if($applications instanceof \Illuminate\Pagination\LengthAwarePaginator && $applications->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $applications->firstItem() }}</span> to
                <span class="font-medium">{{ $applications->lastItem() }}</span> of
                <span class="font-medium">{{ $applications->total() }}</span> applications
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $applications->currentPage() == 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}
                </span>
                <button onclick="nextPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $applications->currentPage() == $applications->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- View Application Modal -->
<div id="viewApplicationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <!-- Modal content will be loaded via AJAX -->
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('updateStatusModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Update Application Status</h3>
                    <button onclick="closeModal('updateStatusModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-600 mb-4" id="statusModalMessage">Are you sure you want to update this application status?</p>
                <form id="statusForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="pending">Pending</option>
                                <option value="under_review">Under Review</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="waiting_list">Waiting List</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Add any notes about this status change..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('updateStatusModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitStatusForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Filter form submission
    document.getElementById('filterForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '{{ route('admin.applications.index') }}?' + params.toString();
    });

    // Reset filter form
    document.getElementById('filterForm')?.addEventListener('reset', function() {
        window.location.href = '{{ route('admin.applications.index') }}';
    });

    // View application details
    function viewApplication(applicationId) {
        fetch(`/admin/applications/${applicationId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewApplicationModal').innerHTML = html;
                openModal('viewApplicationModal', '6xl');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load application details', 'error');
            });
    }

    // Update application status
    function updateStatus(applicationId, status) {
        const statusLabels = {
            'pending': 'Pending',
            'under_review': 'Under Review',
            'accepted': 'Accepted',
            'rejected': 'Rejected',
            'waiting_list': 'Waiting List'
        };

        const form = document.getElementById('statusForm');
        form.action = `/admin/applications/${applicationId}/status`;
        form.querySelector('[name="status"]').value = status;
        document.getElementById('statusModalMessage').textContent =
            `Are you sure you want to update this application status to "${statusLabels[status]}"?`;
        openModal('updateStatusModal', 'md');
    }

    function submitStatusForm() {
        const form = document.getElementById('statusForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Updating...
            `;
            submitBtn.disabled = true;
        }
        form.submit();
    }

    function toggleActionMenu(applicationId) {
        const menu = document.getElementById(`actionMenu-${applicationId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${applicationId}`) {
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

    function exportToExcel() {
        showToast('Excel export functionality will be implemented soon', 'info');
    }

    function refreshTable() {
        location.reload();
    }

    function prevPage() {
        @if($applications instanceof \Illuminate\Pagination\LengthAwarePaginator && $applications->currentPage() > 1)
            window.location.href = '{{ $applications->previousPageUrl() }}';
        @endif
    }

    function nextPage() {
        @if($applications instanceof \Illuminate\Pagination\LengthAwarePaginator && $applications->hasMorePages())
            window.location.href = '{{ $applications->nextPageUrl() }}';
        @endif
    }
</script>

<style>
    #applicationsTable {
        min-width: 1000px;
    }
    
    @media (max-width: 768px) {
        #applicationsTable {
            min-width: 100%;
        }
    }

    tr[onclick]:hover {
        cursor: pointer;
        background-color: #F9FAFB;
    }
</style>
@endsection
