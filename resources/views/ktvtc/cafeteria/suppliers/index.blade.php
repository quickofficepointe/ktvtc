@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Suppliers')
@section('page-title', 'Supplier Management')
@section('page-description', 'Manage your product suppliers')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Suppliers</span>
    </div>
</li>
@endsection

@section('page-actions')
<div class="flex space-x-2">
    <button onclick="showCreateModal()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i> Add Supplier
    </button>
    <button onclick="exportSuppliers()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center">
        <i class="fas fa-download mr-2"></i> Export
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Suppliers</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalSuppliers ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-truck text-primary text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Suppliers</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($activeSuppliers ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Inactive Suppliers</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($inactiveSuppliers ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-ban text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" id="searchInput" placeholder="Search by name, code, contact, phone or email..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div class="w-48">
            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div>
            <button onclick="applyFilters()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-filter mr-2"></i> Apply
            </button>
            <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition ml-2">
                <i class="fas fa-undo mr-2"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Suppliers Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button onclick="sortBy('supplier_code')" class="hover:text-gray-700">Code <i class="fas fa-sort text-xs"></i></button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button onclick="sortBy('supplier_name')" class="hover:text-gray-700">Name <i class="fas fa-sort text-xs"></i></button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-primary">{{ $supplier->supplier_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $supplier->supplier_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $supplier->phone ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $supplier->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showEditModal({{ $supplier->id }})" class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal({{ $supplier->id }})" class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="toggleSupplierStatus({{ $supplier->id }}, {{ $supplier->is_active ? 'true' : 'false' }})"
                                    class="{{ $supplier->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                    title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $supplier->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                            <button onclick="deleteSupplier({{ $supplier->id }})" class="text-red-600 hover:text-red-900" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-truck text-4xl mb-2 text-gray-300"></i>
                        <p>No suppliers found</p>
                        <button onclick="showCreateModal()" class="mt-2 text-primary hover:underline">Add your first supplier</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $suppliers->links() }}
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="supplierModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0">
                <h3 class="text-xl font-bold" id="modalTitle">Add New Supplier</h3>
                <button class="close-modal text-white text-2xl" onclick="closeSupplierModal()">&times;</button>
            </div>
            <form id="supplierForm">
                @csrf
                <input type="hidden" id="supplierId" name="id">
                <div class="p-6 space-y-4">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Code *</label>
                            <input type="text" id="supplierCode" name="supplier_code" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">Unique identifier (auto-generated if empty)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
                            <input type="text" id="supplierName" name="supplier_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Contact Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                <input type="text" id="contactPerson" name="contact_person"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" id="phone" name="phone"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Number (PIN)</label>
                                <input type="text" id="taxNumber" name="tax_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea id="address" name="address" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"></textarea>
                        </div>
                    </div>

                    <!-- Payment & Banking Information -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Payment & Banking Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
                                <select id="paymentTerms" name="payment_terms"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                    <option value="">Select Payment Terms</option>
                                    <option value="Cash on Delivery">Cash on Delivery</option>
                                    <option value="Net 15">Net 15 Days</option>
                                    <option value="Net 30">Net 30 Days</option>
                                    <option value="Net 45">Net 45 Days</option>
                                    <option value="Net 60">Net 60 Days</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                <input type="text" id="bankName" name="bank_name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
                                <input type="text" id="bankAccount" name="bank_account"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Branch</label>
                                <input type="text" id="bankBranch" name="bank_branch"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Additional Information</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        <div class="mt-3 flex items-center">
                            <input type="checkbox" id="isActive" name="is_active" value="1" class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary" checked>
                            <label for="isActive" class="ml-2 text-sm text-gray-700">Active Supplier</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button type="button" onclick="closeSupplierModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="detailsModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Supplier Details</h3>
                <button class="close-modal text-white text-2xl" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="p-6">
                <div id="detailsContent" class="space-y-4">
                    <!-- Details loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer p-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const API_BASE = '{{ url("/cafeteria") }}';
let currentSort = 'supplier_name';
let currentDirection = 'asc';

$(document).ready(function() {
    // Auto-generate supplier code
    $('#supplierName').on('blur', function() {
        const codeInput = $('#supplierCode');
        if (!codeInput.val() && $(this).val()) {
            const code = $(this).val().substring(0, 10).toUpperCase().replace(/\s+/g, '_');
            codeInput.val(code);
        }
    });
});

function showCreateModal() {
    $('#modalTitle').text('Add New Supplier');
    $('#supplierForm').attr('action', API_BASE + '/suppliers');
    $('#supplierForm').find('input[name="_method"]').remove();
    $('#supplierId').val('');
    $('#supplierForm')[0].reset();
    $('#isActive').prop('checked', true);
    openSupplierModal();
}

function showEditModal(id) {
    $.ajax({
        url: API_BASE + '/suppliers/' + id,
        method: 'GET',
        success: function(supplier) {
            $('#modalTitle').text('Edit Supplier');
            $('#supplierForm').attr('action', API_BASE + '/suppliers/' + id);
            $('#supplierForm').find('input[name="_method"]').remove();
            $('#supplierForm').append('<input type="hidden" name="_method" value="PUT">');
            $('#supplierId').val(supplier.id);
            $('#supplierCode').val(supplier.supplier_code);
            $('#supplierName').val(supplier.supplier_name);
            $('#contactPerson').val(supplier.contact_person || '');
            $('#phone').val(supplier.phone || '');
            $('#email').val(supplier.email || '');
            $('#taxNumber').val(supplier.tax_number || '');
            $('#address').val(supplier.address || '');
            $('#paymentTerms').val(supplier.payment_terms || '');
            $('#bankName').val(supplier.bank_name || '');
            $('#bankAccount').val(supplier.bank_account || '');
            $('#bankBranch').val(supplier.bank_branch || '');
            $('#notes').val(supplier.notes || '');
            $('#isActive').prop('checked', supplier.is_active == 1 || supplier.is_active === true);
            openSupplierModal();
        },
        error: function() { toastr.error('Error loading supplier details'); }
    });
}

function showDetailsModal(id) {
    $.ajax({
        url: API_BASE + '/suppliers/' + id,
        method: 'GET',
        success: function(supplier) {
            $('#detailsContent').html(`
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-sm text-gray-600">Code</p><p class="font-medium text-primary">${escapeHtml(supplier.supplier_code)}</p></div>
                    <div><p class="text-sm text-gray-600">Name</p><p class="font-medium">${escapeHtml(supplier.supplier_name)}</p></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-sm text-gray-600">Contact Person</p><p class="font-medium">${escapeHtml(supplier.contact_person || 'N/A')}</p></div>
                    <div><p class="text-sm text-gray-600">Phone</p><p class="font-medium">${escapeHtml(supplier.phone || 'N/A')}</p></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-sm text-gray-600">Email</p><p class="font-medium">${escapeHtml(supplier.email || 'N/A')}</p></div>
                    <div><p class="text-sm text-gray-600">Tax Number</p><p class="font-medium">${escapeHtml(supplier.tax_number || 'N/A')}</p></div>
                </div>
                <div><p class="text-sm text-gray-600">Address</p><p class="font-medium">${escapeHtml(supplier.address || 'N/A')}</p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-sm text-gray-600">Payment Terms</p><p class="font-medium">${escapeHtml(supplier.payment_terms || 'N/A')}</p></div>
                    <div><p class="text-sm text-gray-600">Status</p><p><span class="px-2 py-1 text-xs font-semibold rounded-full ${supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${supplier.is_active ? 'Active' : 'Inactive'}</span></p></div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-2">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Banking Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-sm text-gray-600">Bank Name</p><p class="font-medium">${escapeHtml(supplier.bank_name || 'N/A')}</p></div>
                        <div><p class="text-sm text-gray-600">Bank Account</p><p class="font-medium">${escapeHtml(supplier.bank_account || 'N/A')}</p></div>
                        <div><p class="text-sm text-gray-600">Bank Branch</p><p class="font-medium">${escapeHtml(supplier.bank_branch || 'N/A')}</p></div>
                    </div>
                </div>
                ${supplier.notes ? `<div><p class="text-sm text-gray-600">Notes</p><p class="font-medium">${escapeHtml(supplier.notes)}</p></div>` : ''}
                <div class="border-t border-gray-200 pt-4 mt-2">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Audit Trail</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><p class="text-gray-600">Created By</p><p class="font-medium">${escapeHtml(supplier.creator?.name || 'N/A')}</p></div>
                        <div><p class="text-gray-600">Created At</p><p class="font-medium">${new Date(supplier.created_at).toLocaleString()}</p></div>
                        ${supplier.updated_by ? `<div><p class="text-gray-600">Last Updated By</p><p class="font-medium">${escapeHtml(supplier.updater?.name || 'N/A')}</p></div>` : ''}
                        <div><p class="text-gray-600">Last Updated</p><p class="font-medium">${new Date(supplier.updated_at).toLocaleString()}</p></div>
                    </div>
                </div>
            `);
            openDetailsModal();
        },
        error: function() { toastr.error('Error loading supplier details'); }
    });
}

function openSupplierModal() { $('#supplierModal').removeClass('hidden'); document.body.classList.add('overflow-hidden'); }
function closeSupplierModal() { $('#supplierModal').addClass('hidden'); document.body.classList.remove('overflow-hidden'); }
function openDetailsModal() { $('#detailsModal').removeClass('hidden'); document.body.classList.add('overflow-hidden'); }
function closeDetailsModal() { $('#detailsModal').addClass('hidden'); document.body.classList.remove('overflow-hidden'); }

$('#supplierForm').on('submit', function(e) {
    e.preventDefault();
    const $btn = $(this).find('button[type="submit"]');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function() {
            toastr.success('Supplier saved successfully');
            closeSupplierModal();
            location.reload();
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalText);
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let field in errors) toastr.error(errors[field][0]);
            } else {
                toastr.error(xhr.responseJSON?.error || 'Error saving supplier');
            }
        }
    });
});

function applyFilters() {
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    location.href = API_BASE + '/suppliers?search=' + encodeURIComponent(search) + '&status=' + status;
}

function resetFilters() {
    location.href = API_BASE + '/suppliers';
}

function sortBy(field) {
    if (currentSort === field) {
        currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort = field;
        currentDirection = 'asc';
    }
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    location.href = API_BASE + '/suppliers?search=' + encodeURIComponent(search) + '&status=' + status + '&sort=' + currentSort + '&direction=' + currentDirection;
}

function toggleSupplierStatus(id, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} this supplier?`)) {
        $.ajax({
            url: API_BASE + '/suppliers/' + id,
            method: 'PUT',
            data: { _token: '{{ csrf_token() }}', is_active: !currentStatus },
            success: function() { toastr.success(`Supplier ${action}d`); location.reload(); },
            error: function() { toastr.error('Error updating supplier status'); }
        });
    }
}

function deleteSupplier(id) {
    if (confirm('Delete this supplier? This will fail if they have purchase orders or goods received notes.')) {
        $.ajax({
            url: API_BASE + '/suppliers/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function() { toastr.success('Supplier deleted'); location.reload(); },
            error: function(xhr) { toastr.error(xhr.responseJSON?.error || 'Error deleting supplier'); }
        });
    }
}

function exportSuppliers() {
    window.location.href = API_BASE + '/suppliers/export';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}
</script>
@endsection
