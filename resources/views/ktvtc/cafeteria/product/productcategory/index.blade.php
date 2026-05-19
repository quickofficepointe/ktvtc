@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Product Categories')
@section('subtitle', 'Manage product categories for inventory')

@section('header-actions')
<button class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg flex items-center transition duration-200" onclick="showCreateModal()">
    <i class="fas fa-plus mr-2"></i> New Category
</button>
@endsection

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Categories</span>
    </div>
</li>
@endsection

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between">
    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search categories..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64 focus:ring-2 focus:ring-primary focus:border-primary">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="sectionFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-48">
            <option value="">All Sections</option>
            @foreach($businessSections as $section)
                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
            @endforeach
        </select>
        <select id="parentFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-48">
            <option value="">All Categories</option>
            @foreach($rootCategories as $category)
                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
            @endforeach
        </select>
        <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
            <i class="fas fa-undo-alt mr-1"></i> Reset
        </button>
    </div>
</div>

<!-- Categories Tree View -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">Category Structure</h3>
        <button id="toggleTreeBtn" onclick="toggleTreeView()" class="text-primary hover:text-primary-dark text-sm">
            <i class="fas fa-compress-alt mr-1"></i> Collapse All
        </button>
    </div>
    <div id="categoriesTree" class="space-y-2">
        @if($treeCategories->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-2 text-gray-300"></i>
                <p>No categories found. Click "New Category" to create one.</p>
            </div>
        @else
            @foreach($treeCategories as $category)
                <div class="tree-node" data-level="1">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                        <div class="flex-1 flex items-center">
                            <button class="toggle-children mr-2 text-gray-500 hover:text-gray-700 focus:outline-none" data-id="{{ $category->id }}">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="mr-3 {{ $category->is_active ? 'text-primary' : 'text-gray-400' }}">
                                <i class="fas fa-folder{{ $category->children->isNotEmpty() ? '-open' : '' }}"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ $category->category_name }}</div>
                                <div class="text-xs text-gray-500">{{ $category->category_code }} • {{ $category->businessSection->section_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="showEditModal({{ $category->id }})" class="text-blue-600 hover:text-blue-900 transition duration-200" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showAddSubCategoryModal({{ $category->id }})" class="text-green-600 hover:text-green-900 transition duration-200" title="Add Sub-category">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button onclick="toggleCategoryStatus({{ $category->id }}, {{ $category->is_active ? 'true' : 'false' }})" class="{{ $category->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} transition duration-200" title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        </div>
                    </div>

                    @if($category->children->isNotEmpty())
                        <div class="children-container ml-8 mt-2 space-y-2" data-parent="{{ $category->id }}">
                            @foreach($category->children as $child)
                                <div class="tree-node" data-level="2">
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <div class="flex-1 flex items-center">
                                            @if($child->children->isNotEmpty())
                                                <button class="toggle-children mr-2 text-gray-500 hover:text-gray-700 focus:outline-none" data-id="{{ $child->id }}">
                                                    <i class="fas fa-chevron-down text-xs"></i>
                                                </button>
                                            @else
                                                <div class="w-4 mr-2"></div>
                                            @endif
                                            <div class="mr-3 {{ $child->is_active ? 'text-primary' : 'text-gray-400' }}">
                                                <i class="fas fa-folder{{ $child->children->isNotEmpty() ? '-open' : '' }}"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800">{{ $child->category_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $child->category_code }} • {{ $child->businessSection->section_name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="showEditModal({{ $child->id }})" class="text-blue-600 hover:text-blue-900 transition duration-200" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="showAddSubCategoryModal({{ $child->id }})" class="text-green-600 hover:text-green-900 transition duration-200" title="Add Sub-category">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button onclick="toggleCategoryStatus({{ $child->id }}, {{ $child->is_active ? 'true' : 'false' }})" class="{{ $child->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} transition duration-200" title="{{ $child->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $child->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </div>
                                    </div>

                                    @if($child->children->isNotEmpty())
                                        <div class="children-container ml-8 mt-2 space-y-2" data-parent="{{ $child->id }}">
                                            @foreach($child->children as $grandChild)
                                                <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                                    <div class="flex-1 flex items-center">
                                                        <div class="w-4 mr-2"></div>
                                                        <div class="mr-3 {{ $grandChild->is_active ? 'text-primary' : 'text-gray-400' }}">
                                                            <i class="fas fa-folder"></i>
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-gray-800">{{ $grandChild->category_name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $grandChild->category_code }} • {{ $grandChild->businessSection->section_name ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button onclick="showEditModal({{ $grandChild->id }})" class="text-blue-600 hover:text-blue-900 transition duration-200" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button onclick="toggleCategoryStatus({{ $grandChild->id }}, {{ $grandChild->is_active ? 'true' : 'false' }})" class="{{ $grandChild->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} transition duration-200" title="{{ $grandChild->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas {{ $grandChild->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="categoriesTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-primary">{{ $category->category_code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $category->category_name }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $category->description ?: 'No description' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $category->businessSection->section_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $category->parent->category_name ?? 'Root' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $category->sort_order }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <button onclick="showEditModal({{ $category->id }})" class="text-blue-600 hover:text-blue-900 transition duration-200" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="showDetailsModal({{ $category->id }})" class="text-primary hover:text-primary-dark transition duration-200" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="showAddSubCategoryModal({{ $category->id }})" class="text-green-600 hover:text-green-900 transition duration-200" title="Add Sub-category">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                <button onclick="toggleCategoryStatus({{ $category->id }}, {{ $category->is_active ? 'true' : 'false' }})" class="{{ $category->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} transition duration-200" title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                <button onclick="deleteCategory({{ $category->id }})" class="text-red-600 hover:text-red-900 transition duration-200" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-tags text-4xl mb-2 text-gray-300"></i>
                            <p>No categories found. Click "New Category" to create one.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6" id="paginationContainer">
    {{ $categories->links() }}
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="categoryModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50 transition-opacity duration-300"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl transform transition-all duration-300 scale-95 opacity-0">
            <div class="modal-header bg-gradient-to-r from-primary to-primary-dark text-white p-5 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Create New Category</h3>
                <button class="close-modal text-white text-2xl hover:text-gray-200 transition duration-200" onclick="closeCategoryModal()">&times;</button>
            </div>
            <form id="categoryModalForm">
                @csrf
                <input type="hidden" id="categoryId" name="id">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Code <span class="text-red-500">*</span></label>
                            <input type="text" id="categoryCode" name="category_code" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200">
                            <p class="text-xs text-gray-500 mt-1">Unique identifier for this category</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name <span class="text-red-500">*</span></label>
                            <input type="text" id="categoryName" name="category_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Section <span class="text-red-500">*</span></label>
                            <select id="businessSectionId" name="business_section_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200">
                                <option value="">Select Section</option>
                                @foreach($businessSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                            <select id="parentCategoryId" name="parent_category_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200">
                                <option value="">None (Root Category)</option>
                                @foreach($rootCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="sortOrder" name="sort_order" value="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200">
                            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-medium text-gray-700">Active Category</label>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="isActive" name="is_active" value="1"
                                       class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition duration-200">
                                <label for="isActive" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition duration-200"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition duration-200">
                        <i class="fas fa-save mr-2"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="detailsModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50 transition-opacity duration-300"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl transform transition-all duration-300 scale-95 opacity-0">
            <div class="modal-header bg-gradient-to-r from-primary to-primary-dark text-white p-5 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">
                    <i class="fas fa-info-circle mr-2"></i> Category Details
                </h3>
                <button class="close-modal text-white text-2xl hover:text-gray-200 transition duration-200" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="p-6" id="detailsContent"></div>
            <div class="modal-footer p-5 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-times mr-2"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span class="text-gray-700">Processing...</span>
    </div>
</div>

<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #10B981;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #10B981;
    }
    .toggle-checkbox {
        right: 1rem;
        transition: all 0.2s;
    }
    .toggle-label {
        transition: background-color 0.2s;
    }
    .modal-content {
        transition: all 0.3s ease-out;
    }
    .modal-content.scale-95 {
        transform: scale(0.95);
    }
    .modal-content.opacity-0 {
        opacity: 0;
    }
    .children-container {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .children-container.collapsed {
        display: none;
    }
    .toggle-children .fa-chevron-down {
        transition: transform 0.2s;
    }
    .toggle-children.collapsed .fa-chevron-down {
        transform: rotate(-90deg);
    }
</style>
@endsection

@section('scripts')
<script>
// Make sure showCreateModal is globally accessible
window.showCreateModal = function(parentId = null) {
    console.log('showCreateModal called', parentId);

    $('#modalTitle').text(parentId ? 'Create Sub-category' : 'Create New Category');
    $('#categoryModalForm').attr('action', '{{ url("/cafeteria") }}/categories');
    $('#categoryModalForm').find('input[name="_method"]').remove();
    $('#categoryId').val('');
    $('#categoryCode').val('');
    $('#categoryName').val('');
    $('#description').val('');
    $('#parentCategoryId').val(parentId || '');
    $('#sortOrder').val(0);
    $('#isActive').prop('checked', true);
    $('#businessSectionId').val('').prop('disabled', false);

    if (parentId) {
        $('#businessSectionId').prop('disabled', true);
        $.ajax({
            url: '{{ url("/cafeteria") }}/categories/' + parentId,
            method: 'GET',
            success: function(parent) {
                $('#businessSectionId').val(parent.business_section_id);
            },
            error: function() {
                $('#businessSectionId').prop('disabled', false);
            }
        });
    }

    $('#categoryModal').removeClass('hidden');
    setTimeout(function() {
        $('#categoryModal .modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
    }, 10);
    document.body.classList.add('overflow-hidden');
};

window.showAddSubCategoryModal = function(parentId) {
    window.showCreateModal(parentId);
};

window.closeCategoryModal = function() {
    $('#categoryModal .modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
    setTimeout(function() {
        $('#categoryModal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');
        $('#categoryModalForm')[0].reset();
        $('#categoryId').val('');
        $('#businessSectionId').prop('disabled', false);
    }, 300);
};

// CSRF setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

$(document).ready(function() {
    // Auto-generate category code from name
    $('#categoryName').on('blur', function() {
        if (!$('#categoryCode').val() && $(this).val()) {
            let code = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 20);
            $('#categoryCode').val(code.toUpperCase());
        }
    });

    // Form submission
    $('#categoryModalForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Category saved successfully');
                window.closeCategoryModal();
                setTimeout(function() { location.reload(); }, 500);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        toastr.error(errors[field][0]);
                    }
                } else if (xhr.status === 409) {
                    toastr.error(xhr.responseJSON.message || 'Category code already exists');
                } else {
                    toastr.error('Error saving category');
                }
            }
        });
    });

    // Tree view toggles
    $('.toggle-children').each(function() {
        const $btn = $(this);
        const parentId = $btn.data('id');
        const $children = $(`.children-container[data-parent="${parentId}"]`);
        if ($children.length) {
            $btn.on('click', function(e) {
                e.stopPropagation();
                $children.toggleClass('collapsed');
                $btn.toggleClass('collapsed');
            });
        }
    });
});

window.toggleTreeView = function() {
    const treeCollapsed = !$('.children-container').first().hasClass('collapsed');
    $('.children-container').toggleClass('collapsed', !treeCollapsed);
    $('.toggle-children').toggleClass('collapsed', !treeCollapsed);
    const $btn = $('#toggleTreeBtn');
    if (treeCollapsed) {
        $btn.html('<i class="fas fa-expand-alt mr-1"></i> Expand All');
    } else {
        $btn.html('<i class="fas fa-compress-alt mr-1"></i> Collapse All');
    }
};

window.resetFilters = function() {
    $('#searchInput').val('');
    $('#sectionFilter').val('');
    $('#parentFilter').val('');
    $('#statusFilter').val('');
    location.reload();
};

window.showEditModal = function(id) {
    $.ajax({
        url: '{{ url("/cafeteria") }}/categories/' + id,
        method: 'GET',
        success: function(cat) {
            $('#modalTitle').text('Edit Category');
            $('#categoryModalForm').attr('action', '{{ url("/cafeteria") }}/categories/' + id);
            $('#categoryModalForm').find('input[name="_method"]').remove();
            $('#categoryModalForm').append('<input type="hidden" name="_method" value="PUT">');
            $('#categoryId').val(cat.id);
            $('#categoryCode').val(cat.category_code);
            $('#categoryName').val(cat.category_name);
            $('#businessSectionId').val(cat.business_section_id).prop('disabled', false);
            $('#description').val(cat.description || '');
            $('#parentCategoryId').val(cat.parent_category_id || '');
            $('#sortOrder').val(cat.sort_order || 0);
            $('#isActive').prop('checked', cat.is_active == 1);

            $('#categoryModal').removeClass('hidden');
            setTimeout(function() {
                $('#categoryModal .modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);
            document.body.classList.add('overflow-hidden');
        },
        error: function() {
            toastr.error('Error loading category details');
        }
    });
};

window.showDetailsModal = function(id) {
    $.ajax({
        url: '{{ url("/cafeteria") }}/categories/' + id,
        method: 'GET',
        success: function(cat) {
            $('#detailsContent').html(`
                <div class="space-y-4">
                    <div class="flex items-center space-x-4 pb-4 border-b border-gray-200">
                        <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary-dark rounded-full flex items-center justify-center">
                            <i class="fas fa-folder-open text-white text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-800">${escapeHtml(cat.category_name)}</h4>
                            <p class="text-sm text-gray-500">${escapeHtml(cat.category_code)}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Category Code</p>
                            <p class="font-mono text-sm font-medium text-gray-800">${escapeHtml(cat.category_code)}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${cat.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${cat.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Description</p>
                        <p class="text-sm text-gray-700">${escapeHtml(cat.description || 'No description provided')}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Business Section</p>
                            <p class="text-sm font-medium text-gray-800">${escapeHtml(cat.business_section?.section_name || 'N/A')}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Parent Category</p>
                            <p class="text-sm font-medium text-gray-800">${escapeHtml(cat.parent?.category_name || 'Root Category')}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Sort Order</p>
                            <p class="text-sm font-medium text-gray-800">${cat.sort_order || 0}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Sub-categories</p>
                            <p class="text-sm font-medium text-gray-800">${cat.children?.length || 0}</p>
                        </div>
                    </div>
                </div>
            `);
            $('#detailsModal').removeClass('hidden');
            setTimeout(function() {
                $('#detailsModal .modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);
            document.body.classList.add('overflow-hidden');
        },
        error: function() {
            toastr.error('Error loading category details');
        }
    });
};

window.closeDetailsModal = function() {
    $('#detailsModal .modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
    setTimeout(function() {
        $('#detailsModal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 300);
};

window.toggleCategoryStatus = function(id, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    Swal.fire({
        title: `Are you sure?`,
        text: `You want to ${action} this category.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentStatus ? '#eab308' : '#10b981',
        confirmButtonText: `Yes, ${action} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("/cafeteria") }}/categories/' + id + '/toggle-status',
                method: 'PATCH',
                data: { is_active: currentStatus ? 0 : 1 },
                success: function() {
                    toastr.success(`Category ${action}d successfully`);
                    setTimeout(function() { location.reload(); }, 500);
                },
                error: function() {
                    toastr.error('Error updating category status');
                }
            });
        }
    });
};

window.deleteCategory = function(id) {
    Swal.fire({
        title: 'Delete Category?',
        text: "This action cannot be undone.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("/cafeteria") }}/categories/' + id,
                method: 'DELETE',
                success: function() {
                    toastr.success('Category deleted successfully');
                    setTimeout(function() { location.reload(); }, 500);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Error deleting category');
                }
            });
        }
    });
};

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

// Close modals when clicking overlay
$(document).on('click', '.modal-overlay', function() {
    window.closeCategoryModal();
    window.closeDetailsModal();
});

// Close modals with ESC key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        window.closeCategoryModal();
        window.closeDetailsModal();
    }
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
