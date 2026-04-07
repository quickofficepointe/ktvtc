@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Product Categories')
@section('page-title', 'Product Categories')
@section('page-description', 'Manage product categories for inventory')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Categories</span>
    </div>
</li>
@endsection

@section('page-actions')
<button class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg flex items-center transition duration-200" onclick="showCreateModal()">
    <i class="fas fa-plus mr-2"></i> New Category
</button>
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
        <button onclick="toggleTreeView()" class="text-primary hover:text-primary-dark text-sm">
            <i class="fas fa-compress-alt mr-1"></i> Collapse All
        </button>
    </div>
    <div id="categoriesTree" class="space-y-2">
        @if($treeCategories->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-2 text-gray-300"></i>
                <p>No categories found</p>
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

                    <!-- Level 2 -->
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

                                    <!-- Level 3 -->
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
                            <p>No categories found</p>
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
    /* Custom toggle switch styles */
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

    /* Modal animation */
    .modal-content {
        transition: all 0.3s ease-out;
    }
    .modal-content.scale-95 {
        transform: scale(0.95);
    }
    .modal-content.opacity-0 {
        opacity: 0;
    }

    /* Tree view transitions */
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
const API_BASE = '{{ url("/cafeteria") }}';

$(document).ready(function() {
    // Initialize event listeners
    $('#searchInput').on('keyup', debounce(loadCategories, 500));
    $('#sectionFilter, #parentFilter, #statusFilter').on('change', loadCategories);

    // Initialize tree view toggle buttons
    initializeTreeView();

    // Initialize modal animations
    initializeModals();
});

let currentPage = 1;
let searchQuery = '';
let sectionFilter = '';
let parentFilter = '';
let statusFilter = '';

// Tree View Functions
function initializeTreeView() {
    $('.toggle-children').each(function() {
        const $btn = $(this);
        const parentId = $btn.data('id');
        const $children = $(`.children-container[data-parent="${parentId}"]`);

        if ($children.length) {
            $btn.off('click').on('click', function(e) {
                e.stopPropagation();
                $children.toggleClass('collapsed');
                $btn.toggleClass('collapsed');
            });
        }
    });
}

let treeCollapsed = false;
function toggleTreeView() {
    treeCollapsed = !treeCollapsed;
    $('.children-container').toggleClass('collapsed', treeCollapsed);
    $('.toggle-children').toggleClass('collapsed', treeCollapsed);

    const $btn = $('#toggleTreeBtn');
    if (treeCollapsed) {
        $btn.html('<i class="fas fa-expand-alt mr-1"></i> Expand All');
    } else {
        $btn.html('<i class="fas fa-compress-alt mr-1"></i> Collapse All');
    }
}

function resetFilters() {
    $('#searchInput').val('');
    $('#sectionFilter').val('');
    $('#parentFilter').val('');
    $('#statusFilter').val('');
    loadCategories(1);
}

// Modal Functions
function initializeModals() {
    // Close modal when clicking overlay
    $('.modal-overlay').on('click', function() {
        closeCategoryModal();
        closeDetailsModal();
    });

    // Prevent modal content click from closing
    $('.modal-content').on('click', function(e) {
        e.stopPropagation();
    });

    // Handle ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCategoryModal();
            closeDetailsModal();
        }
    });
}

function openModal($modal) {
    $modal.removeClass('hidden');
    setTimeout(() => {
        $modal.find('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
    }, 10);
    document.body.classList.add('overflow-hidden');
}

function closeModal($modal) {
    $modal.find('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
    setTimeout(() => {
        $modal.addClass('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 300);
}

function openCategoryModal() { openModal($('#categoryModal')); }
function closeCategoryModal() {
    closeModal($('#categoryModal'));
    $('#categoryModalForm')[0].reset();
    $('#categoryId').val('');
}

function openDetailsModal() { openModal($('#detailsModal')); }
function closeDetailsModal() { closeModal($('#detailsModal')); }

function showLoading(show) {
    if (show) {
        $('#loadingOverlay').removeClass('hidden').addClass('flex');
    } else {
        $('#loadingOverlay').addClass('hidden').removeClass('flex');
    }
}

// Category CRUD Functions
function showCreateModal(parentId = null) {
    $('#modalTitle').text(parentId ? 'Create Sub-category' : 'Create New Category');
    $('#categoryModalForm').attr('action', API_BASE + '/categories');
    $('#categoryModalForm').find('input[name="_method"]').remove();
    $('#categoryId').val('');
    $('#categoryCode, #categoryName, #businessSectionId, #description').val('');
    $('#parentCategoryId').val(parentId || '');
    $('#sortOrder').val(0);
    $('#isActive').prop('checked', true);

    // Disable business section if parent is selected (inherit from parent)
    if (parentId) {
        $('#businessSectionId').prop('disabled', true);
        // Fetch parent section
        $.ajax({
            url: API_BASE + '/categories/' + parentId,
            method: 'GET',
            success: function(parent) {
                $('#businessSectionId').val(parent.business_section_id);
            }
        });
    } else {
        $('#businessSectionId').prop('disabled', false);
    }

    openCategoryModal();
}

function showAddSubCategoryModal(parentId) { showCreateModal(parentId); }

function showEditModal(id) {
    showLoading(true);
    $.ajax({
        url: API_BASE + '/categories/' + id,
        method: 'GET',
        success: function(cat) {
            $('#modalTitle').text('Edit Category');
            $('#categoryModalForm').attr('action', API_BASE + '/categories/' + id);
            $('#categoryModalForm').find('input[name="_method"]').remove();
            $('#categoryModalForm').append('<input type="hidden" name="_method" value="PUT">');
            $('#categoryId').val(cat.id);
            $('#categoryCode').val(cat.category_code);
            $('#categoryName').val(cat.category_name);
            $('#businessSectionId').val(cat.business_section_id).prop('disabled', false);
            $('#description').val(cat.description || '');
            $('#parentCategoryId').val(cat.parent_category_id || '');
            $('#sortOrder').val(cat.sort_order || 0);
            $('#isActive').prop('checked', cat.is_active == 1 || cat.is_active === true);
            openCategoryModal();
        },
        error: function(xhr) {
            console.error(xhr);
            toastr.error('Error loading category details');
        },
        complete: function() {
            showLoading(false);
        }
    });
}

function showDetailsModal(id) {
    showLoading(true);
    $.ajax({
        url: API_BASE + '/categories/' + id,
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

                    <div class="border-t border-gray-200 pt-4">
                        <h5 class="text-sm font-semibold text-gray-700 mb-3">Audit Information</h5>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Created By</p>
                                <p class="font-medium text-gray-800">${escapeHtml(cat.creator?.name || 'System')}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Created At</p>
                                <p class="font-medium text-gray-800">${new Date(cat.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Last Updated By</p>
                                <p class="font-medium text-gray-800">${escapeHtml(cat.updater?.name || 'N/A')}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Last Updated</p>
                                <p class="font-medium text-gray-800">${new Date(cat.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            openDetailsModal();
        },
        error: function(xhr) {
            console.error(xhr);
            toastr.error('Error loading category details');
        },
        complete: function() {
            showLoading(false);
        }
    });
}

// Form Submission
$('#categoryModalForm').on('submit', function(e) {
    e.preventDefault();
    const $btn = $(this).find('button[type="submit"]');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
    showLoading(true);

    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            toastr.success('Category saved successfully');
            closeCategoryModal();
            setTimeout(() => location.reload(), 500);
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
        },
        complete: function() {
            showLoading(false);
        }
    });
});

function toggleCategoryStatus(id, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    Swal.fire({
        title: `Are you sure?`,
        text: `You want to ${action} this category. ${!currentStatus ? 'This will make it visible in the system.' : 'This will hide it from active views.'}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentStatus ? '#eab308' : '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${action} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading(true);
            $.ajax({
                url: API_BASE + '/categories/' + id,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: currentStatus ? 0 : 1
                },
                success: function(response) {
                    toastr.success(`Category ${action}d successfully`);
                    setTimeout(() => location.reload(), 500);
                },
                error: function(xhr) {
                    console.error(xhr);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        toastr.error(errors[0]);
                    } else {
                        toastr.error('Error updating category status');
                    }
                },
                complete: function() {
                    showLoading(false);
                }
            });
        }
    });
}

function deleteCategory(id) {
    Swal.fire({
        title: 'Delete Category?',
        text: "This action cannot be undone. The category will be permanently deleted if it has no products or sub-categories.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading(true);
            $.ajax({
                url: API_BASE + '/categories/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    toastr.success('Category deleted successfully');
                    setTimeout(() => location.reload(), 500);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Error deleting category');
                },
                complete: function() {
                    showLoading(false);
                }
            });
        }
    });
}

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

// Load Categories with AJAX
function loadCategories(page = 1) {
    currentPage = page;
    searchQuery = $('#searchInput').val();
    sectionFilter = $('#sectionFilter').val();
    parentFilter = $('#parentFilter').val();
    statusFilter = $('#statusFilter').val();

    showLoading(true);
    $.ajax({
        url: API_BASE + '/api/categories',
        method: 'GET',
        data: {
            page: page,
            per_page: 15,
            search: searchQuery,
            business_section_id: sectionFilter,
            parent_category_id: parentFilter,
            is_active: statusFilter
        },
        success: function(response) {
            if (response.data) renderCategoriesTable(response.data);
            if (response.links) renderPagination(response);
        },
        error: function(xhr) {
            console.error(xhr);
            toastr.error('Error loading categories');
        },
        complete: function() {
            showLoading(false);
        }
    });
}

function renderCategoriesTable(categories) {
    const tbody = $('#categoriesTable tbody');
    tbody.empty();
    if (!categories.length) {
        tbody.html('<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No categories found</td></tr>');
        return;
    }
    categories.forEach(cat => {
        tbody.append(`
            <tr class="hover:bg-gray-50 transition duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-primary">${escapeHtml(cat.category_code)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(cat.category_name)}</div>
                    <div class="text-xs text-gray-500 truncate max-w-xs">${escapeHtml(cat.description || 'No description')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${escapeHtml(cat.business_section?.section_name || 'N/A')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${escapeHtml(cat.parent?.category_name || 'Root')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${cat.sort_order}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${cat.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${cat.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-3">
                        <button onclick="showEditModal(${cat.id})" class="text-blue-600 hover:text-blue-900 transition duration-200" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showDetailsModal(${cat.id})" class="text-primary hover:text-primary-dark transition duration-200" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="showAddSubCategoryModal(${cat.id})" class="text-green-600 hover:text-green-900 transition duration-200" title="Add Sub-category">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                        <button onclick="toggleCategoryStatus(${cat.id}, ${cat.is_active})" class="${cat.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'} transition duration-200" title="${cat.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="fas ${cat.is_active ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button onclick="deleteCategory(${cat.id})" class="text-red-600 hover:text-red-900 transition duration-200" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

function renderPagination(response) {
    const container = $('#paginationContainer');
    if (!container.length) return;
    container.html(`
        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
                ${response.prev_page_url ? `<button onclick="loadCategories(${currentPage - 1})" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</button>` : ''}
                ${response.next_page_url ? `<button onclick="loadCategories(${currentPage + 1})" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</button>` : ''}
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div><p class="text-sm text-gray-700">Showing <span class="font-medium">${response.from}</span> to <span class="font-medium">${response.to}</span> of <span class="font-medium">${response.total}</span> results</p></div>
                <div><nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    ${response.links.map((link, i) => {
                        if (link.label.includes('Previous')) {
                            return `<button onclick="${link.url ? `loadCategories(${currentPage - 1})` : ''}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}"><i class="fas fa-chevron-left h-5 w-5"></i></button>`;
                        } else if (link.label.includes('Next')) {
                            return `<button onclick="${link.url ? `loadCategories(${currentPage + 1})` : ''}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}"><i class="fas fa-chevron-right h-5 w-5"></i></button>`;
                        } else {
                            return `<button onclick="loadCategories(${link.label})" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ${link.active ? 'z-10 bg-primary text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'}">${link.label}</button>`;
                        }
                    }).join('')}
                </nav></div>
            </div>
        </div>
    `);
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}
</script>

<!-- SweetAlert2 for better confirm dialogs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
