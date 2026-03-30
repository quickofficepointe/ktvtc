@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Shops')
@section('page-title', 'Shops Management')
@section('page-description', 'Manage all shops within business sections')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Shops</span>
    </div>
</li>
@endsection

@section('page-actions')
<button class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center" onclick="showCreateModal()">
    <i class="fas fa-plus mr-2"></i> New Shop
</button>
@endsection

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between">
    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search shops..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64 focus:ring-primary focus:border-primary">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="sectionFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary w-48">
            <option value="">All Sections</option>
            @foreach($businessSections as $section)
                <option value="{{ $section->id }}" {{ request('business_section_id') == $section->id ? 'selected' : '' }}>
                    {{ $section->section_name }}
                </option>
            @endforeach
        </select>
        <select id="branchFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary w-40">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch }}" {{ request('branch') == $branch ? 'selected' : '' }}>
                    {{ $branch }}
                </option>
            @endforeach
        </select>
        <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary">
            <option value="">All Status</option>
            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>

<!-- Shops Table -->
<div class="table-container">
    <table class="min-w-full divide-y divide-gray-200" id="shopsTable">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($shops as $shop)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-primary">{{ $shop->shop_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $shop->shop_name }}</div>
                        <div class="text-xs text-gray-500">{{ $shop->branch ?: 'Main' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $shop->businessSection->section_name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $shop->location ?: 'N/A' }}</div>
                        @if($shop->building || $shop->floor || $shop->room_number)
                            <div class="text-xs text-gray-500">
                                {{ implode(', ', array_filter([$shop->building, $shop->floor, $shop->room_number])) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $shop->contact_phone ?: 'N/A' }}</div>
                        @if($shop->contact_email)
                            <div class="text-xs text-gray-500">{{ $shop->contact_email }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $shop->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $shop->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showEditModal({{ $shop->id }})"
                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal({{ $shop->id }})"
                                    class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="toggleStatus({{ $shop->id }}, {{ $shop->is_active }})"
                                    class="{{ $shop->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                    title="{{ $shop->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $shop->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                            <button onclick="deleteShop({{ $shop->id }})"
                                    class="text-red-600 hover:text-red-900" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-store-alt text-4xl mb-2 text-gray-300"></i>
                        <p>No shops found</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $shops->links() }}
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="shopModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Create New Shop</h3>
                <button class="close-modal text-white text-2xl" onclick="closeShopModal()">&times;</button>
            </div>
            <form id="shopModalForm">
                @csrf
                <input type="hidden" id="shopId" name="id">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="shopCode" class="block text-sm font-medium text-gray-700 mb-2">Shop Code *</label>
                            <input type="text" id="shopCode" name="shop_code" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="shopName" class="block text-sm font-medium text-gray-700 mb-2">Shop Name *</label>
                            <input type="text" id="shopName" name="shop_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="businessSectionId" class="block text-sm font-medium text-gray-700 mb-2">Business Section *</label>
                            <select id="businessSectionId" name="business_section_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">Select Section</option>
                                @foreach($businessSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" id="location" name="location"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <input type="text" id="branch" name="branch"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                   placeholder="e.g., Ngong, Town">
                        </div>
                        <div>
                            <label for="contactPhone" class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                            <input type="tel" id="contactPhone" name="contact_phone"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label for="contactEmail" class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                            <input type="email" id="contactEmail" name="contact_email"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="building" class="block text-sm font-medium text-gray-700 mb-2">Building</label>
                            <input type="text" id="building" name="building"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="floor" class="block text-sm font-medium text-gray-700 mb-2">Floor</label>
                            <input type="text" id="floor" name="floor"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="roomNumber" class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                            <input type="text" id="roomNumber" name="room_number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="isActive" name="is_active" value="1"
                                       class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <label for="isActive" class="ml-2 text-sm text-gray-700">Active Shop</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeShopModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">
                        Save Shop
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
                <h3 class="text-xl font-bold">Shop Details</h3>
                <button class="close-modal text-white text-2xl" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="p-6">
                <div class="space-y-4" id="detailsContent">
                    <!-- Details will be loaded via AJAX -->
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
    // Load shops via AJAX (for filtering)
    function loadShops(page = 1) {
        const params = {
            page: page,
            per_page: 15
        };

        if ($('#searchInput').val()) params.search = $('#searchInput').val();
        if ($('#sectionFilter').val()) params.business_section_id = $('#sectionFilter').val();
        if ($('#branchFilter').val()) params.branch = $('#branchFilter').val();
        if ($('#statusFilter').val() !== '') params.is_active = $('#statusFilter').val();

        $.ajax({
            url: '{{ route("cafeteria.api.shops.index") }}',
            method: 'GET',
            data: params,
            success: function(response) {
                renderShopsTable(response.data);
                renderPagination(response);
            },
            error: function(xhr) {
                toastr.error('Error loading shops');
            }
        });
    }

    // Render shops table via AJAX
    function renderShopsTable(shops) {
        const tbody = $('#shopsTable tbody');
        tbody.empty();

        if (shops.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-store-alt text-4xl mb-2 text-gray-300"></i>
                        <p>No shops found</p>
                    </td>
                </tr>
            `);
            return;
        }

        shops.forEach(function(shop) {
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-primary">${shop.shop_code}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${shop.shop_name}</div>
                        <div class="text-xs text-gray-500">${shop.branch || 'Main'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${shop.business_section?.section_name || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">${shop.location || 'N/A'}</div>
                        ${shop.building || shop.floor || shop.room_number ? `
                            <div class="text-xs text-gray-500">
                                ${[shop.building, shop.floor, shop.room_number].filter(Boolean).join(', ')}
                            </div>
                        ` : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${shop.contact_phone || 'N/A'}</div>
                        ${shop.contact_email ? `
                            <div class="text-xs text-gray-500">${shop.contact_email}</div>
                        ` : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${shop.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${shop.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showEditModal(${shop.id})"
                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal(${shop.id})"
                                    class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="toggleStatus(${shop.id}, ${shop.is_active})"
                                    class="${shop.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}"
                                    title="${shop.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="fas ${shop.is_active ? 'fa-ban' : 'fa-check'}"></i>
                            </button>
                            <button onclick="deleteShop(${shop.id})"
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

    // Render pagination via AJAX
    function renderPagination(response) {
        const container = $('#paginationContainer');
        container.html(response.links);
    }

    // Search functionality
    $('#searchInput').on('keyup', debounce(function() {
        loadShops(1);
    }, 500));

    // Filter functionality
    $('#sectionFilter, #branchFilter, #statusFilter').on('change', function() {
        loadShops(1);
    });

    // Debounce function
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
        $('#modalTitle').text('Create New Shop');
        $('#shopModalForm').attr('action', '{{ route("cafeteria.shops.store") }}');
        $('#shopModalForm').attr('method', 'POST');
        $('#shopId').val('');

        // Reset form
        $('#shopCode').val('');
        $('#shopName').val('');
        $('#businessSectionId').val('');
        $('#location').val('');
        $('#branch').val('');
        $('#contactPhone').val('');
        $('#contactEmail').val('');
        $('#building').val('');
        $('#floor').val('');
        $('#roomNumber').val('');
        $('#isActive').prop('checked', true);

        openShopModal();
    }

    function showEditModal(id) {
        $.ajax({
            url: `/cafeteria/shops/${id}`,
            method: 'GET',
            success: function(shop) {
                $('#modalTitle').text('Edit Shop');
                $('#shopModalForm').attr('action', `/cafeteria/shops/${id}`);
                $('#shopModalForm').attr('method', 'PUT');
                $('#shopId').val(id);

                // Fill form
                $('#shopCode').val(shop.shop_code);
                $('#shopName').val(shop.shop_name);
                $('#businessSectionId').val(shop.business_section_id);
                $('#location').val(shop.location);
                $('#branch').val(shop.branch);
                $('#contactPhone').val(shop.contact_phone);
                $('#contactEmail').val(shop.contact_email);
                $('#building').val(shop.building);
                $('#floor').val(shop.floor);
                $('#roomNumber').val(shop.room_number);
                $('#isActive').prop('checked', shop.is_active == 1 || shop.is_active === true);

                openShopModal();
            },
            error: function() {
                toastr.error('Error loading shop details');
            }
        });
    }

    function showDetailsModal(id) {
        $.ajax({
            url: `/cafeteria/shops/${id}`,
            method: 'GET',
            success: function(shop) {
                const detailsHtml = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Code</p>
                            <p class="font-medium">${shop.shop_code}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium">${shop.shop_name}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Section</p>
                            <p class="font-medium">${shop.business_section?.section_name || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Branch</p>
                            <p class="font-medium">${shop.branch || 'Main'}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Location</p>
                        <p class="font-medium">${shop.location || 'N/A'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Contact Info</p>
                        <div class="font-medium">
                            ${shop.contact_phone ? `<div class="mb-1">Phone: ${shop.contact_phone}</div>` : ''}
                            ${shop.contact_email ? `<div>Email: ${shop.contact_email}</div>` : ''}
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Physical Address</p>
                        <div class="font-medium">
                            ${shop.building ? `<div class="mb-1">Building: ${shop.building}</div>` : ''}
                            ${shop.floor ? `<div class="mb-1">Floor: ${shop.floor}</div>` : ''}
                            ${shop.room_number ? `<div>Room: ${shop.room_number}</div>` : ''}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${shop.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${shop.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Opening Hours</p>
                            <p class="font-medium">${shop.opening_hours || 'Not set'}</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">Audit Trail</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Created By</p>
                                <p class="font-medium">${shop.creator ? shop.creator.name : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Created At</p>
                                <p class="font-medium">${new Date(shop.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Last Updated</p>
                                <p class="font-medium">${new Date(shop.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                `;

                $('#detailsContent').html(detailsHtml);
                openDetailsModal();
            },
            error: function() {
                toastr.error('Error loading shop details');
            }
        });
    }

    function openShopModal() {
        $('#shopModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeShopModal() {
        $('#shopModal').addClass('hidden');
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
    $('#shopModalForm').on('submit', function(e) {
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
                toastr.success('Shop saved successfully');
                closeShopModal();
                loadShops(1); // Reload to show new/updated shop
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        toastr.error(errors[field][0]);
                    }
                } else {
                    toastr.error('Error saving shop');
                }
            }
        });
    });

    // Toggle shop status
    function toggleStatus(id, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        if (confirm(`Are you sure you want to ${action} this shop?`)) {
            $.ajax({
                url: `/cafeteria/shops/${id}`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: !currentStatus
                },
                success: function() {
                    toastr.success(`Shop ${action}d successfully`);
                    loadShops(1);
                },
                error: function() {
                    toastr.error('Error updating shop');
                }
            });
        }
    }

    // Delete shop
    function deleteShop(id) {
        if (confirm('Are you sure you want to delete this shop? This action cannot be undone.')) {
            $.ajax({
                url: `/cafeteria/shops/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    toastr.success('Shop deleted successfully');
                    loadShops(1);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Error deleting shop');
                }
            });
        }
    }
</script>
@endsection
