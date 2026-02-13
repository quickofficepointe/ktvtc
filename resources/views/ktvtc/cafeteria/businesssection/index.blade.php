@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Business Sections')
@section('page-title', 'Business Sections')
@section('page-description', 'Manage different business sections in the cafeteria')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Business Sections</span>
    </div>
</li>
@endsection

@section('page-actions')
<button class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center" onclick="showCreateModal()">
    <i class="fas fa-plus mr-2"></i> New Section
</button>
@endsection

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between">
    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search sections..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64 focus:ring-primary focus:border-primary">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        <select id="typeFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary">
            <option value="">All Types</option>
            <option value="cafeteria">Cafeteria</option>
            <option value="restaurant">Restaurant</option>
            <option value="bakery">Bakery</option>
            <option value="snack_bar">Snack Bar</option>
        </select>
    </div>
</div>

<!-- Business Sections Table -->
<div class="table-container">
    <table class="min-w-full divide-y divide-gray-200" id="sectionsTable">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="sectionsTableBody">
            <!-- Data will be loaded via AJAX -->
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6" id="paginationContainer">
    <!-- Pagination will be loaded via AJAX -->
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="businessSectionModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Create New Business Section</h3>
                <button class="close-modal text-white text-2xl" onclick="closeModal()">&times;</button>
            </div>
            <form id="modalForm">
                @csrf
                <input type="hidden" id="sectionId" name="id">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="sectionCode" class="block text-sm font-medium text-gray-700 mb-2">Section Code *</label>
                            <input type="text" id="sectionCode" name="section_code" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="sectionName" class="block text-sm font-medium text-gray-700 mb-2">Section Name *</label>
                            <input type="text" id="sectionName" name="section_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        <div>
                            <label for="sectionType" class="block text-sm font-medium text-gray-700 mb-2">Section Type *</label>
                            <select id="sectionType" name="section_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">Select Type</option>
                                <option value="cafeteria">Cafeteria</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="bakery">Bakery</option>
                                <option value="snack_bar">Snack Bar</option>
                                <option value="food_court">Food Court</option>
                                <option value="coffee_shop">Coffee Shop</option>
                            </select>
                        </div>
                        <div>
                            <label for="managerId" class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                            <select id="managerId" name="manager_user_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">Select Manager</option>
                                @foreach($cafeteriaUsers ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center">
                             <input type="checkbox" id="isActive" name="is_active" value="1"
           class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
    <label for="isActive" class="ml-2 text-sm text-gray-700">Active Section</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="detailsModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Section Details</h3>
                <button class="close-modal text-white text-2xl" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Code</p>
                            <p class="font-medium" id="detailsSectionCode"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium" id="detailsSectionName"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="font-medium" id="detailsDescription"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Type</p>
                            <p class="font-medium" id="detailsSectionType"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium" id="detailsStatus"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Manager</p>
                        <p class="font-medium" id="detailsManager"></p>
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">Audit Trail</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Created By</p>
                                <p class="font-medium" id="detailsCreatedBy"></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Created At</p>
                                <p class="font-medium" id="detailsCreatedAt"></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Last Updated</p>
                                <p class="font-medium" id="detailsUpdatedAt"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeDetailsModal()"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentPage = 1;
    let searchQuery = '';
    let statusFilter = '';
    let typeFilter = '';

    // Load sections on page load
    $(document).ready(function() {
        loadSections();
    });

    // Load sections via AJAX
    function loadSections(page = 1) {
        currentPage = page;
        const params = {
            page: page,
            per_page: 15
        };

        if (searchQuery) params.search = searchQuery;
        if (statusFilter !== '') params.is_active = statusFilter;
        if (typeFilter) params.section_type = typeFilter;

        $.ajax({
            url: '{{ route("cafeteria.api.business-sections.index") }}',
            method: 'GET',
            data: params,
            success: function(response) {
                renderSectionsTable(response.data);
                renderPagination(response);
            },
            error: function(xhr) {
                toastr.error('Error loading sections');
            }
        });
    }

    // Render sections table
    function renderSectionsTable(sections) {
        const tbody = $('#sectionsTableBody');
        tbody.empty();

        if (sections.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>No business sections found</p>
                    </td>
                </tr>
            `);
            return;
        }

        sections.forEach(function(section) {
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-primary">${section.section_code}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${section.section_name}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs">${section.description || 'No description'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full ${getTypeColor(section.section_type)}">
                            ${getTypeDisplayName(section.section_type)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${section.manager ? `
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center">
                                    <i class="fas fa-user-tie text-primary text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">${section.manager.name}</div>
                                </div>
                            </div>
                        ` : '<span class="text-sm text-gray-500">No manager</span>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${section.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${section.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showEditModal(${section.id})"
                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal(${section.id})"
                                    class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="toggleStatus(${section.id}, ${section.is_active})"
                                    class="${section.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}"
                                    title="${section.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="fas ${section.is_active ? 'fa-ban' : 'fa-check'}"></i>
                            </button>
                            <button onclick="deleteSection(${section.id})"
                                    class="text-red-600 hover:text-red-900" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Render pagination
    function renderPagination(response) {
        const container = $('#paginationContainer');
        container.empty();

        if (response.links.length <= 3) return;

        let pagination = `
            <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between sm:hidden">
                    ${response.prev_page_url ? `
                        <button onclick="loadSections(${currentPage - 1})" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Previous
                        </button>
                    ` : ''}
                    ${response.next_page_url ? `
                        <button onclick="loadSections(${currentPage + 1})" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Next
                        </button>
                    ` : ''}
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">${response.from}</span> to <span class="font-medium">${response.to}</span> of <span class="font-medium">${response.total}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        `;

        response.links.forEach(function(link, index) {
            if (link.label.includes('Previous')) {
                pagination += `
                    <button onclick="${link.url ? `loadSections(${currentPage - 1})` : ''}"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left h-5 w-5"></i>
                    </button>
                `;
            } else if (link.label.includes('Next')) {
                pagination += `
                    <button onclick="${link.url ? `loadSections(${currentPage + 1})` : ''}"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right h-5 w-5"></i>
                    </button>
                `;
            } else {
                const isActive = link.active;
                const pageNumber = link.label;
                pagination += `
                    <button onclick="loadSections(${pageNumber})"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ${isActive ? 'z-10 bg-primary text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'}">
                        ${pageNumber}
                    </button>
                `;
            }
        });

        pagination += `
                        </nav>
                    </div>
                </div>
            </div>
        `;

        container.html(pagination);
    }

    // Search functionality
    $('#searchInput').on('keyup', debounce(function() {
        searchQuery = $(this).val();
        loadSections(1);
    }, 500));

    // Filter functionality
    $('#statusFilter, #typeFilter').on('change', function() {
        statusFilter = $('#statusFilter').val();
        typeFilter = $('#typeFilter').val();
        loadSections(1);
    });

    // Helper function for type colors
    function getTypeColor(type) {
        const colors = {
            'cafeteria': 'bg-red-100 text-red-800',
            'restaurant': 'bg-blue-100 text-blue-800',
            'bakery': 'bg-yellow-100 text-yellow-800',
            'snack_bar': 'bg-green-100 text-green-800',
            'food_court': 'bg-purple-100 text-purple-800',
            'coffee_shop': 'bg-indigo-100 text-indigo-800'
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    }

    // Helper function for type display names
    function getTypeDisplayName(type) {
        const names = {
            'cafeteria': 'Cafeteria',
            'restaurant': 'Restaurant',
            'bakery': 'Bakery',
            'snack_bar': 'Snack Bar',
            'food_court': 'Food Court',
            'coffee_shop': 'Coffee Shop'
        };
        return names[type] || type.replace('_', ' ').toUpperCase();
    }

    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Modal functions
    function showCreateModal() {
        $('#modalTitle').text('Create New Business Section');
        $('#modalForm').attr('action', '{{ route("cafeteria.business-sections.store") }}');
        $('#modalForm').attr('method', 'POST');
        $('#sectionId').val('');

        // Reset form
        $('#sectionCode').val('');
        $('#sectionName').val('');
        $('#description').val('');
        $('#sectionType').val('');
        $('#managerId').val('');
        $('#isActive').prop('checked', true);

        openModal();
    }

    function showEditModal(id) {
        $.ajax({
            url: `/cafeteria/business-sections/${id}`,
            method: 'GET',
            success: function(section) {
                $('#modalTitle').text('Edit Business Section');
                $('#modalForm').attr('action', `/cafeteria/business-sections/${id}`);
                $('#modalForm').attr('method', 'PUT');
                $('#sectionId').val(id);

                // Fill form
                $('#sectionCode').val(section.section_code);
                $('#sectionName').val(section.section_name);
                $('#description').val(section.description);
                $('#sectionType').val(section.section_type);
                $('#managerId').val(section.manager_user_id);
                $('#isActive').prop('checked', section.is_active);

                openModal();
            },
            error: function() {
                toastr.error('Error loading section details');
            }
        });
    }

    function showDetailsModal(id) {
        $.ajax({
            url: `/cafeteria/business-sections/${id}`,
            method: 'GET',
            success: function(section) {
                $('#detailsSectionCode').text(section.section_code);
                $('#detailsSectionName').text(section.section_name);
                $('#detailsDescription').text(section.description || 'No description');
                $('#detailsSectionType').text(getTypeDisplayName(section.section_type));
                $('#detailsStatus').html(section.is_active ?
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>' :
                    '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>');
                $('#detailsManager').text(section.manager ? section.manager.name : 'No manager');
                $('#detailsCreatedBy').text(section.creator ? section.creator.name : 'N/A');
                $('#detailsCreatedAt').text(new Date(section.created_at).toLocaleString());
                $('#detailsUpdatedAt').text(new Date(section.updated_at).toLocaleString());

                openDetailsModal();
            },
            error: function() {
                toastr.error('Error loading section details');
            }
        });
    }

    function openModal() {
        $('#businessSectionModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        $('#businessSectionModal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openDetailsModal() {
        $('#detailsModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDetailsModal() {
        $('#detailsModal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Handle form submission
    $('#modalForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method');
        const formData = form.serialize();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                toastr.success('Business section saved successfully');
                closeModal();
                loadSections(currentPage);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        toastr.error(errors[field][0]);
                    }
                } else {
                    toastr.error('Error saving business section');
                }
            }
        });
    });

    // Toggle section status
    function toggleStatus(id, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        if (confirm(`Are you sure you want to ${action} this section?`)) {
            $.ajax({
                url: `/cafeteria/business-sections/${id}`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: !currentStatus
                },
                success: function() {
                    toastr.success(`Section ${action}d successfully`);
                    loadSections(currentPage);
                },
                error: function() {
                    toastr.error('Error updating section');
                }
            });
        }
    }

    // Delete section
    function deleteSection(id) {
        if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
            $.ajax({
                url: `/cafeteria/business-sections/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    toastr.success('Section deleted successfully');
                    loadSections(currentPage);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Error deleting section');
                }
            });
        }
    }
</script>
@endsection
