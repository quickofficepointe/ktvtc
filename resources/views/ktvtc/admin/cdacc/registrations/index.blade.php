@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'CDACC Registrations Management')
@section('subtitle', 'Manage TVET/CDACC student registrations')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET/CDACC</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">CDACC Registrations</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createRegistrationModal', 'xl')"
            class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>New Registration</span>
    </button>
    <button onclick="openModal('bulkActionsModal', 'lg')"
            class="px-4 py-2 bg-info hover:bg-blue-700 text-white rounded-lg font-medium flex items-center space-x-2">
        <i class="fas fa-tasks"></i>
        <span>Bulk Actions</span>
    </button>
    <button onclick="exportToExcel()"
            class="px-4 py-2 bg-success hover:bg-green-700 text-white rounded-lg font-medium flex items-center space-x-2">
        <i class="fas fa-file-export"></i>
        <span>Export</span>
    </button>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalCount ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Certified</p>
                <p class="text-2xl font-bold text-gray-800">{{ $certifiedCount ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Pending Sync</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pendingSyncCount ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Success Rate</p>
                <p class="text-2xl font-bold text-gray-800">{{ $successRate ?? 0 }}%</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" placeholder="Search by name, ID, reg number..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="cdacc_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="registered">Registered</option>
                <option value="active">Active</option>
                <option value="under_assessment">Under Assessment</option>
                <option value="completed">Completed</option>
                <option value="certified">Certified</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
            <select name="program_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Programs</option>
                @foreach($programs ?? [] as $program)
                <option value="{{ $program->code }}">{{ $program->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end space-x-2">
            <button type="button" onclick="applyFilters()"
                    class="flex-1 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                <i class="fas fa-filter mr-2"></i>
                Filter
            </button>
            <button type="button" onclick="resetFilters()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                Clear
            </button>
        </div>
    </form>
</div>

<!-- Registrations Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300" onchange="selectAllRows()">
                    </th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Program Details</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Registration Info</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="registrationsTableBody">
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="py-12 text-center">
        <div class="inline-block">
            <i class="fas fa-spinner fa-spin text-3xl text-primary mb-3"></i>
            <p class="text-gray-600">Loading registrations...</p>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden py-12 text-center">
        <i class="fas fa-user-graduate text-4xl text-gray-400 mb-3"></i>
        <p class="text-lg text-gray-500 mb-2">No CDACC registrations found</p>
        <button onclick="openModal('createRegistrationModal', 'xl')"
                class="text-primary hover:text-primary-dark font-medium">
            Create your first registration
        </button>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="hidden px-6 py-4 border-t border-gray-200">
        <!-- Pagination will be loaded here -->
    </div>
</div>

<!-- Bottom Actions -->
<div class="mt-4 flex justify-between items-center">
    <div id="selectionCount" class="text-sm text-gray-600 hidden">
        <span id="selectedCount">0</span> registration(s) selected
    </div>
    <div class="flex space-x-2">
        <select id="bulkActionSelect" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Bulk Actions</option>
            <option value="submit">Submit to CDACC</option>
            <option value="approve">Approve</option>
            <option value="sync">Sync Status</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button onclick="applyBulkAction()"
                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
            Apply
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Global variables
let currentPage = 1;
let selectedRegistrations = new Set();
let allSelected = false;

// Load registrations on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRegistrations();
});

// Function to load registrations via AJAX
function loadRegistrations(page = 1) {
    currentPage = page;

    // Show loading state
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('paginationContainer').classList.add('hidden');
    document.getElementById('registrationsTableBody').innerHTML = '';

    // Get filter values
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams();

    for (let [key, value] of formData) {
        if (value) params.append(key, value);
    }
    params.append('page', page);

    // Make AJAX request
    fetch(`{{ route('admin.cdacc.registrations.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        renderRegistrations(data.data);
        renderPagination(data);
        updateSelectionCount();
    })
    .catch(error => {
        console.error('Error loading registrations:', error);
        showToast('Failed to load registrations', 'error');
    })
    .finally(() => {
        document.getElementById('loadingState').classList.add('hidden');
    });
}

// Function to render registrations
function renderRegistrations(registrations) {
    const tbody = document.getElementById('registrationsTableBody');

    if (registrations.length === 0) {
        document.getElementById('emptyState').classList.remove('hidden');
        return;
    }

    let html = '';

    registrations.forEach(registration => {
        const statusClass = getStatusClass(registration.cdacc_status);
        const certStatusClass = getCertStatusClass(registration.certification_status);
        const isChecked = allSelected || selectedRegistrations.has(registration.id.toString());

        html += `
            <tr class="hover:bg-gray-50" id="row-${registration.id}">
                <td class="py-3 px-4">
                    <input type="checkbox"
                           class="row-checkbox rounded border-gray-300"
                           value="${registration.id}"
                           ${isChecked ? 'checked' : ''}
                           onchange="toggleSelection(${registration.id})">
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${registration.student_name || 'N/A'}</div>
                            <div class="text-sm text-gray-600">${registration.student_email || ''}</div>
                            <div class="text-xs text-gray-500">ID: ${registration.cdacc_learner_id || 'Pending'}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <div class="text-sm text-gray-900">${registration.cdacc_program_name}</div>
                    <div class="text-sm text-gray-600">${registration.cdacc_qualification_title}</div>
                    <div class="text-xs text-gray-500">Code: ${registration.cdacc_program_code}</div>
                </td>
                <td class="py-3 px-4">
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">${registration.cdacc_registration_number || 'Not Assigned'}</div>
                        <div class="text-gray-600">Date: ${formatDate(registration.cdacc_registration_date)}</div>
                        <div class="text-gray-600">Center: ${registration.cdacc_center_name}</div>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <div class="space-y-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            ${formatStatus(registration.cdacc_status)}
                        </span>
                        ${registration.certification_status !== 'not_applicable' ? `
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${certStatusClass}">
                                    ${formatStatus(registration.certification_status)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td class="py-3 px-4">
                    <div class="flex space-x-2">
                        <button onclick="viewRegistration(${registration.id})"
                                class="p-1 text-blue-600 hover:text-blue-800"
                                title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editRegistration(${registration.id})"
                                class="p-1 text-green-600 hover:text-green-800"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${registration.cdacc_status === 'pending' ? `
                            <button onclick="deleteRegistration(${registration.id})"
                                    class="p-1 text-red-600 hover:text-red-800"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                        <button onclick="showQuickActions(${registration.id})"
                                class="p-1 text-purple-600 hover:text-purple-800"
                                title="More Actions">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// Function to render pagination
function renderPagination(data) {
    const container = document.getElementById('paginationContainer');

    if (data.last_page <= 1) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');

    let html = `
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing ${data.from} to ${data.to} of ${data.total} entries
            </div>
            <div class="flex space-x-2">
    `;

    // Previous button
    html += `
        <button onclick="loadRegistrations(${currentPage - 1})"
                ${currentPage === 1 ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded ${currentPage === 1 ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50 text-gray-700'}">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;

    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <button onclick="loadRegistrations(${i})"
                        class="px-3 py-1 border rounded ${currentPage === i ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-50 text-gray-700'}">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<span class="px-3 py-1 text-gray-500">...</span>`;
        }
    }

    // Next button
    html += `
        <button onclick="loadRegistrations(${currentPage + 1})"
                ${currentPage === data.last_page ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded ${currentPage === data.last_page ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50 text-gray-700'}">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;

    html += `</div></div>`;
    container.innerHTML = html;
}

// Helper functions
function getStatusClass(status) {
    const classes = {
        'certified': 'bg-green-100 text-green-800',
        'approved': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'submitted': 'bg-purple-100 text-purple-800',
        'active': 'bg-green-100 text-green-800',
        'under_assessment': 'bg-indigo-100 text-indigo-800',
        'default': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || classes.default;
}

function getCertStatusClass(status) {
    const classes = {
        'awarded': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'eligible': 'bg-blue-100 text-blue-800',
        'default': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || classes.default;
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Selection functions
function selectAllRows() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');

    allSelected = selectAll.checked;

    checkboxes.forEach(checkbox => {
        checkbox.checked = allSelected;
        const id = checkbox.value;

        if (allSelected) {
            selectedRegistrations.add(id);
        } else {
            selectedRegistrations.delete(id);
        }
    });

    updateSelectionCount();
}

function toggleSelection(id) {
    const checkbox = document.querySelector(`.row-checkbox[value="${id}"]`);

    if (checkbox.checked) {
        selectedRegistrations.add(id.toString());
    } else {
        selectedRegistrations.delete(id.toString());
        document.getElementById('selectAll').checked = false;
        allSelected = false;
    }

    updateSelectionCount();
}

function updateSelectionCount() {
    const count = selectedRegistrations.size;
    const countElement = document.getElementById('selectedCount');
    const container = document.getElementById('selectionCount');

    countElement.textContent = count;

    if (count > 0) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Filter functions
function applyFilters() {
    loadRegistrations(1);
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    loadRegistrations(1);
}

// Bulk actions
function applyBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;

    if (!action) {
        showToast('Please select an action', 'warning');
        return;
    }

    if (selectedRegistrations.size === 0) {
        showToast('Please select at least one registration', 'warning');
        return;
    }

    const ids = Array.from(selectedRegistrations);

    switch(action) {
        case 'submit':
            openBulkSubmitModal(ids);
            break;
        case 'approve':
            openBulkApproveModal(ids);
            break;
        case 'sync':
            openBulkSyncModal(ids);
            break;
        case 'delete':
            openBulkDeleteModal(ids);
            break;
    }
}

// Action functions
function viewRegistration(id) {
    // Load registration data via AJAX
    fetch(`/admin/cdacc/registrations/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        openViewModal(data);
    })
    .catch(error => {
        console.error('Error loading registration:', error);
        showToast('Failed to load registration details', 'error');
    });
}

function editRegistration(id) {
    // Load registration data for editing
    fetch(`/admin/cdacc/registrations/${id}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        openEditModal(data);
    })
    .catch(error => {
        console.error('Error loading registration:', error);
        showToast('Failed to load registration for editing', 'error');
    });
}

function deleteRegistration(id) {
    if (confirm('Are you sure you want to delete this registration?')) {
        fetch(`/admin/cdacc/registrations/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Registration deleted successfully', 'success');
                // Remove row from table
                document.getElementById(`row-${id}`)?.remove();
                selectedRegistrations.delete(id.toString());
                updateSelectionCount();
            } else {
                showToast(data.message || 'Failed to delete registration', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting registration:', error);
            showToast('Failed to delete registration', 'error');
        });
    }
}

function showQuickActions(id) {
    // You can implement a dropdown menu for quick actions
    // For now, let's just open the view modal
    viewRegistration(id);
}

// Export function
function exportToExcel() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams();

    for (let [key, value] of formData) {
        if (value) params.append(key, value);
    }

    window.location.href = `/admin/cdacc/registrations/export?${params}`;
}

// Toast notification
function showToast(message, type = 'success') {
    // Use the existing toast system from the layout
    window.showToast(message, type);
}

// Function to open create modal
function openCreateModal() {
    fetch('/admin/cdacc/registrations/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('createRegistrationContent').innerHTML = html;
        openModal('createRegistrationModal', 'xl');
        initCreateForm();
    })
    .catch(error => {
        console.error('Error loading create form:', error);
        showToast('Failed to load create form', 'error');
    });
}

// Function to open view modal
function openViewModal(data) {
    let html = `
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">CDACC Registration Details</h3>
                    <p class="text-gray-600">${data.cdacc_registration_number || 'Pending Registration'}</p>
                </div>
                <div>
                    <button onclick="closeModal('viewRegistrationModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-800 mb-3">Student Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                <i class="fas fa-user text-gray-600"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold">${data.student_name}</h5>
                                <p class="text-sm text-gray-600">${data.student_email}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Student ID:</span>
                                <span class="font-medium">${data.student_id || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Registration:</span>
                                <span class="font-medium">${data.registration_number || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Course:</span>
                                <span class="font-medium">${data.course_name || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800 mb-3">Program Details</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Program Code:</span>
                                <span class="font-medium">${data.cdacc_program_code}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Program Name:</span>
                                <span class="font-medium">${data.cdacc_program_name}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Qualification:</span>
                                <span class="font-medium">${data.cdacc_qualification_title}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Level:</span>
                                <span class="font-medium capitalize">${data.cdacc_qualification_level}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <h4 class="font-medium text-gray-800 mb-3">Status Information</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">CDACC Status:</span>
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(data.cdacc_status)}">
                                ${formatStatus(data.cdacc_status)}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Certification Status:</span>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${getCertStatusClass(data.certification_status)}">
                                ${formatStatus(data.certification_status)}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Registration Date:</span>
                            <span class="ml-2 font-medium">${formatDate(data.cdacc_registration_date)}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Expiry Date:</span>
                            <span class="ml-2 font-medium">${formatDate(data.cdacc_registration_expiry)}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="editRegistration(${data.id})"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Registration
                </button>
                <button onclick="closeModal('viewRegistrationModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    `;

    document.getElementById('viewRegistrationContent').innerHTML = html;
    openModal('viewRegistrationModal', 'xl');
}

// Function to open edit modal
function openEditModal(data) {
    let html = `
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Edit CDACC Registration</h3>
                <button onclick="closeModal('editRegistrationModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editRegistrationForm" onsubmit="updateRegistration(event, ${data.id})">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Program Code *</label>
                            <input type="text" name="cdacc_program_code" value="${data.cdacc_program_code}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Program Name *</label>
                            <input type="text" name="cdacc_program_name" value="${data.cdacc_program_name}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qualification Title *</label>
                            <input type="text" name="cdacc_qualification_title" value="${data.cdacc_qualification_title}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qualification Level *</label>
                            <select name="cdacc_qualification_level" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="artisan" ${data.cdacc_qualification_level === 'artisan' ? 'selected' : ''}>Artisan</option>
                                <option value="certificate" ${data.cdacc_qualification_level === 'certificate' ? 'selected' : ''}>Certificate</option>
                                <option value="diploma" ${data.cdacc_qualification_level === 'diploma' ? 'selected' : ''}>Diploma</option>
                                <option value="higher_diploma" ${data.cdacc_qualification_level === 'higher_diploma' ? 'selected' : ''}>Higher Diploma</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="cdacc_status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="pending" ${data.cdacc_status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="submitted" ${data.cdacc_status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                <option value="approved" ${data.cdacc_status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="registered" ${data.cdacc_status === 'registered' ? 'selected' : ''}>Registered</option>
                                <option value="active" ${data.cdacc_status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="under_assessment" ${data.cdacc_status === 'under_assessment' ? 'selected' : ''}>Under Assessment</option>
                                <option value="completed" ${data.cdacc_status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="certified" ${data.cdacc_status === 'certified' ? 'selected' : ''}>Certified</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certification Status *</label>
                            <select name="certification_status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="not_applicable" ${data.certification_status === 'not_applicable' ? 'selected' : ''}>Not Applicable</option>
                                <option value="pending" ${data.certification_status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="eligible" ${data.certification_status === 'eligible' ? 'selected' : ''}>Eligible</option>
                                <option value="awarded" ${data.certification_status === 'awarded' ? 'selected' : ''}>Awarded</option>
                                <option value="withheld" ${data.certification_status === 'withheld' ? 'selected' : ''}>Withheld</option>
                                <option value="revoked" ${data.certification_status === 'revoked' ? 'selected' : ''}>Revoked</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('editRegistrationModal')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                            <i class="fas fa-save mr-2"></i>
                            Update Registration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    `;

    document.getElementById('editRegistrationContent').innerHTML = html;
    openModal('editRegistrationModal', 'xl');
}

// Function to update registration
function updateRegistration(event, id) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    fetch(`/admin/cdacc/registrations/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Registration updated successfully', 'success');
            closeModal('editRegistrationModal');
            loadRegistrations(currentPage);
        } else {
            showToast(result.message || 'Failed to update registration', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating registration:', error);
        showToast('Failed to update registration', 'error');
    });
}

// Bulk action modals
function openBulkSubmitModal(ids) {
    document.getElementById('bulkSubmitCount').textContent = `${ids.length} registration(s) will be submitted to CDACC`;
    document.getElementById('bulkSubmitNotes').value = '';
    document.querySelector('#bulkSubmitModal button[onclick="confirmBulkSubmit()"]').dataset.ids = ids.join(',');
    openModal('bulkSubmitModal', 'lg');
}

function confirmBulkSubmit() {
    const button = document.querySelector('#bulkSubmitModal button[onclick="confirmBulkSubmit()"]');
    const ids = button.dataset.ids.split(',');
    const notes = document.getElementById('bulkSubmitNotes').value;

    fetch('/admin/cdacc/registrations/bulk/submit', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            registration_ids: ids,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast(`Submitted ${ids.length} registration(s) to CDACC`, 'success');
            closeModal('bulkSubmitModal');
            loadRegistrations(currentPage);
            selectedRegistrations.clear();
            updateSelectionCount();
        } else {
            showToast(result.message || 'Failed to submit registrations', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting registrations:', error);
        showToast('Failed to submit registrations', 'error');
    });
}

// Initialize create form
function initCreateForm() {
    // Add any create form initialization logic here
}

// Update the openModal calls
document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.querySelector('button[onclick*="createRegistrationModal"]');
    if (createBtn) {
        createBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openCreateModal();
        });
    }
});
</script>
@endsection
