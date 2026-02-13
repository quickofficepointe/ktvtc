@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Suppliers')
@section('page-title', 'Supplier Management')
@section('page-description', 'Manage suppliers and vendor information')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Procurement
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Suppliers
    </span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-truck text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Suppliers</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalSuppliers }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $activeSuppliers }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center mr-4">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Inactive</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $inactiveSuppliers }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row md:items-center gap-4 flex-1">
                <!-- Search -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="searchInput"
                           placeholder="Search suppliers..."
                           value="{{ request('search') }}"
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>

                <!-- Status Filter -->
                <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary min-w-[180px]">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <!-- Export Button -->
                <button onclick="exportSuppliers()" class="btn-secondary py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>

                <!-- Add Supplier Button -->
                <button onclick="openSupplierModal()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Supplier
                </button>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-semibold text-gray-800">All Suppliers</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} entries</span>
                    <select id="perPageSelect" class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('supplier_code')">
                            <div class="flex items-center">
                                Code
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('supplier_name')">
                            <div class="flex items-center">
                                Supplier Name
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('is_active')">
                            <div class="flex items-center">
                                Status
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="suppliersTableBody">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50" id="supplier-row-{{ $supplier->id }}">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                            {{ $supplier->supplier_code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $supplier->supplier_name }}</div>
                            @if($supplier->tax_number)
                            <div class="text-sm text-gray-500">Tax: {{ $supplier->tax_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $supplier->contact_person ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $supplier->phone ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $supplier->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($supplier->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-circle text-red-500 mr-1" style="font-size: 6px;"></i>
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <!-- View Details Button -->
                                <button onclick="viewSupplierDetails({{ $supplier->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Edit Button -->
                                <button onclick="editSupplier({{ $supplier->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Delete Button -->
                                <button onclick="confirmDeleteSupplier({{ $supplier->id }}, '{{ $supplier->supplier_name }}')"
                                        class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-truck text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-700">No suppliers found</p>
                                <p class="text-gray-500 mt-1">Start by adding your first supplier</p>
                                <button onclick="openSupplierModal()"
                                        class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Supplier
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($suppliers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $suppliers->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Supplier Modal (Create/Edit) -->
<div id="supplierModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Add New Supplier</h3>
                <button onclick="closeSupplierModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="supplierForm" class="p-6">
            @csrf
            <input type="hidden" id="supplier_id" name="id">

            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supplier_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Supplier Code *
                            </label>
                            <input type="text"
                                   id="supplier_code"
                                   name="supplier_code"
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                            <div id="supplier_code_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <div>
                            <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Supplier Name *
                            </label>
                            <input type="text"
                                   id="supplier_name"
                                   name="supplier_name"
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                            <div id="supplier_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Person
                            </label>
                            <input type="text"
                                   id="contact_person"
                                   name="contact_person"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>
                            <textarea id="address"
                                      name="address"
                                      rows="2"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tax_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Tax Number
                            </label>
                            <input type="text"
                                   id="tax_number"
                                   name="tax_number"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Terms
                            </label>
                            <input type="text"
                                   id="payment_terms"
                                   name="payment_terms"
                                   placeholder="e.g., Net 30, COD"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Bank Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Name
                            </label>
                            <input type="text"
                                   id="bank_name"
                                   name="bank_name"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Number
                            </label>
                            <input type="text"
                                   id="bank_account"
                                   name="bank_account"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="bank_branch" class="block text-sm font-medium text-gray-700 mb-2">
                                Branch
                            </label>
                            <input type="text"
                                   id="bank_branch"
                                   name="bank_branch"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>

                        <div class="flex flex-col justify-center">
                            <div class="flex items-center h-10 mb-4">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       class="h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded">
                                <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                                    Active Supplier
                                </label>
                            </div>
                            <p class="text-sm text-gray-500">Inactive suppliers won't appear in selection lists</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-end space-x-3">
                <button type="button"
                        onclick="closeSupplierModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="saveSupplier()"
                        id="saveSupplierBtn"
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                    Save Supplier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="viewModalTitle" class="text-xl font-bold text-gray-900">Supplier Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-6" id="supplierDetailsContent">
            <!-- Content will be loaded here -->
        </div>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-end">
                <button type="button"
                        onclick="closeViewModal()"
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 text-center">Delete Supplier</h3>
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">
                Are you sure you want to delete this supplier?
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="deleteSupplier()"
                        id="confirmDeleteBtn"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Delete Supplier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg border p-4 min-w-[300px] transform transition-all duration-300 translate-x-full">
        <div class="flex items-start">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="ml-3 w-0 flex-1">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
                <p id="toastDescription" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button onclick="hideToast()" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// State
let currentSupplierId = null;
let currentSortField = '{{ request("sort", "supplier_name") }}';
let currentSortDirection = '{{ request("direction", "asc") }}';

// DOM Elements
const supplierModal = document.getElementById('supplierModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const supplierForm = document.getElementById('supplierForm');
const toast = document.getElementById('toast');

// ======================
// FILTERS & SORTING
// ======================

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const perPage = document.getElementById('perPageSelect').value;
    const url = new URL(window.location.href);

    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');

    if (status && status !== 'all') url.searchParams.set('status', status);
    else url.searchParams.delete('status');

    if (perPage) url.searchParams.set('per_page', perPage);

    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function sortTable(field) {
    const url = new URL(window.location.href);
    let direction = 'asc';

    if (field === currentSortField) {
        direction = currentSortDirection === 'asc' ? 'desc' : 'asc';
    }

    url.searchParams.set('sort', field);
    url.searchParams.set('direction', direction);
    window.location.href = url.toString();
}

// Event Listeners for filters
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') applyFilters();
});

document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('perPageSelect').addEventListener('change', applyFilters);

// ======================
// SUPPLIER MODAL FUNCTIONS
// ======================

function openSupplierModal(id = null) {
    resetForm();
    clearErrors();

    if (id) {
        // Edit mode
        document.getElementById('modalTitle').textContent = 'Edit Supplier';
        document.getElementById('saveSupplierBtn').textContent = 'Update Supplier';
        currentSupplierId = id;

        // Load supplier data
        fetch(`/suppliers/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch supplier');
            return response.json();
        })
        .then(supplier => {
            populateForm(supplier);
            supplierModal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading supplier data', 'error');
        });
    } else {
        // Create mode
        document.getElementById('modalTitle').textContent = 'Add New Supplier';
        document.getElementById('saveSupplierBtn').textContent = 'Save Supplier';
        currentSupplierId = null;
        supplierModal.classList.remove('hidden');
    }
}

function closeSupplierModal() {
    supplierModal.classList.add('hidden');
    resetForm();
    clearErrors();
}

function populateForm(supplier) {
    document.getElementById('supplier_id').value = supplier.id;
    document.getElementById('supplier_code').value = supplier.supplier_code || '';
    document.getElementById('supplier_name').value = supplier.supplier_name || '';
    document.getElementById('contact_person').value = supplier.contact_person || '';
    document.getElementById('phone').value = supplier.phone || '';
    document.getElementById('email').value = supplier.email || '';
    document.getElementById('address').value = supplier.address || '';
    document.getElementById('tax_number').value = supplier.tax_number || '';
    document.getElementById('payment_terms').value = supplier.payment_terms || '';
    document.getElementById('bank_name').value = supplier.bank_name || '';
    document.getElementById('bank_account').value = supplier.bank_account || '';
    document.getElementById('bank_branch').value = supplier.bank_branch || '';
    document.getElementById('notes').value = supplier.notes || '';
    document.getElementById('is_active').checked = supplier.is_active || false;
}

function resetForm() {
    supplierForm.reset();
    document.getElementById('supplier_id').value = '';
    document.getElementById('is_active').checked = true;
}

function clearErrors() {
    const errorElements = document.querySelectorAll('[id$="_error"]');
    errorElements.forEach(element => {
        element.textContent = '';
        element.classList.add('hidden');
    });
}

function showErrors(errors) {
    clearErrors();

    for (const [field, messages] of Object.entries(errors)) {
        const errorElement = document.getElementById(`${field}_error`);
        if (errorElement) {
            errorElement.textContent = messages[0];
            errorElement.classList.remove('hidden');
        }
    }
}

function saveSupplier() {
    const formData = new FormData(supplierForm);
    const isEdit = !!currentSupplierId;
    const url = isEdit ? `/suppliers/${currentSupplierId}` : '/suppliers';
    const method = isEdit ? 'PUT' : 'POST';

    // Add _method for PUT requests
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeSupplierModal();

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showErrors(data.errors || { general: [data.message] });
            showToast('Please fix the errors in the form', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// ======================
// VIEW MODAL FUNCTIONS
// ======================

function viewSupplierDetails(id) {
    fetch(`/suppliers/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to fetch supplier');
        return response.json();
    })
    .then(supplier => {
        document.getElementById('viewModalTitle').textContent = supplier.supplier_name;

        const content = `
            <div class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Supplier Code</p>
                            <p class="font-medium text-gray-900">${supplier.supplier_code || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                                supplier.is_active
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'
                            }">
                                ${supplier.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="font-medium text-gray-900">${supplier.contact_person || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone Number</p>
                            <p class="font-medium text-gray-900">${supplier.phone || 'N/A'}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600">Email Address</p>
                            <p class="font-medium text-gray-900">${supplier.email || 'N/A'}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium text-gray-900 whitespace-pre-line">${supplier.address || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Info -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Tax Number</p>
                            <p class="font-medium text-gray-900">${supplier.tax_number || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payment Terms</p>
                            <p class="font-medium text-gray-900">${supplier.payment_terms || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                ${supplier.bank_name ? `
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Bank Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Bank Name</p>
                            <p class="font-medium text-gray-900">${supplier.bank_name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account Number</p>
                            <p class="font-medium text-gray-900">${supplier.bank_account}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Branch</p>
                            <p class="font-medium text-gray-900">${supplier.bank_branch}</p>
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Notes -->
                ${supplier.notes ? `
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Notes</h4>
                    <p class="text-gray-700 whitespace-pre-line">${supplier.notes}</p>
                </div>
                ` : ''}
            </div>
        `;

        document.getElementById('supplierDetailsContent').innerHTML = content;
        viewModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error loading supplier details', 'error');
    });
}

function closeViewModal() {
    viewModal.classList.add('hidden');
}

// ======================
// DELETE FUNCTIONS
// ======================

function confirmDeleteSupplier(id, name) {
    currentSupplierId = id;
    document.getElementById('deleteMessage').textContent =
        `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    deleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    currentSupplierId = null;
}

function deleteSupplier() {
    if (!currentSupplierId) return;

    fetch(`/suppliers/${currentSupplierId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeDeleteModal();

            // Remove row from table
            const row = document.getElementById(`supplier-row-${currentSupplierId}`);
            if (row) row.remove();

            // Reload after delay if table is empty
            setTimeout(() => {
                const rows = document.querySelectorAll('#suppliersTableBody tr');
                if (rows.length === 1 && rows[0].querySelector('td[colspan]')) {
                    window.location.reload();
                }
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete supplier', 'error');
            closeDeleteModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        closeDeleteModal();
    });
}

// ======================
// TOAST NOTIFICATION
// ======================

function showToast(message, type = 'success', description = '') {
    const toastMessage = document.getElementById('toastMessage');
    const toastDescription = document.getElementById('toastDescription');
    const toastIcon = document.getElementById('toastIcon');

    toastMessage.textContent = message;
    toastDescription.textContent = description;

    // Set icon based on type
    if (type === 'success') {
        toastIcon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    } else if (type === 'error') {
        toastIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    } else {
        toastIcon.innerHTML = '<i class="fas fa-info-circle text-blue-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    }

    // Show toast
    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.querySelector('.transform').classList.remove('translate-x-full');
    }, 10);

    // Auto hide after 5 seconds
    setTimeout(hideToast, 5000);
}

function hideToast() {
    toast.querySelector('.transform').classList.add('translate-x-full');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 300);
}

// ======================
// EXPORT FUNCTION
// ======================

function exportSuppliers() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    let url = '/suppliers/export';

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status && status !== 'all') params.append('status', status);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    window.open(url, '_blank');
    showToast('Export started. Please check your downloads.', 'success');
}

// ======================
// EVENT LISTENERS
// ======================

// Close modals on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSupplierModal();
        closeViewModal();
        closeDeleteModal();
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target === supplierModal) closeSupplierModal();
    if (e.target === viewModal) closeViewModal();
    if (e.target === deleteModal) closeDeleteModal();
});

// Table row hover effect
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#suppliersTableBody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('bg-gray-50');
        });
        row.addEventListener('mouseleave', function() {
            this.classList.remove('bg-gray-50');
        });
    });
});

// Quick actions from table
function editSupplier(id) {
    openSupplierModal(id);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Any initialization code here
});
</script>
@endsection
