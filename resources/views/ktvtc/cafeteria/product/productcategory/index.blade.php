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
<button class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center" onclick="showCreateModal()">
    <i class="fas fa-plus mr-2"></i> New Category
</button>
@endsection

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between">
    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search categories..."
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
        <select id="parentFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary focus:border-primary w-48">
            <option value="">All Categories</option>
            @foreach($rootCategories as $category)
                <option value="{{ $category->id }}" {{ request('parent_category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->category_name }}
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

<!-- Categories Tree View -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Category Structure</h3>
    <div id="categoriesTree" class="space-y-2">
        @if($treeCategories->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-2 text-gray-300"></i>
                <p>No categories found</p>
            </div>
        @else
            @foreach($treeCategories as $category)
                @php $hasChildren = $category->children && $category->children->isNotEmpty(); @endphp
                <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <div class="flex-1 flex items-center">
                        <div class="mr-3 {{ $category->is_active ? 'text-primary' : 'text-gray-400' }}">
                            <i class="fas fa-folder{{ $hasChildren ? '-open' : '' }}"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">{{ $category->category_name }}</div>
                            <div class="text-xs text-gray-500">{{ $category->category_code }} • {{ $category->businessSection->section_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="showEditModal({{ $category->id }})"
                                class="text-blue-600 hover:text-blue-900 text-sm">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showAddSubCategoryModal({{ $category->id }})"
                                class="text-green-600 hover:text-green-900 text-sm">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button onclick="toggleCategoryStatus({{ $category->id }}, {{ $category->is_active }})"
                                class="{{ $category->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} text-sm">
                            <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                        </button>
                    </div>
                </div>

                @if($hasChildren)
                    <div class="space-y-2 ml-6">
                        @foreach($category->children as $child)
                            @php $hasChildChildren = $child->children && $child->children->isNotEmpty(); @endphp
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 ml-6">
                                <div class="flex-1 flex items-center">
                                    <div class="mr-3 {{ $child->is_active ? 'text-primary' : 'text-gray-400' }}">
                                        <i class="fas fa-folder{{ $hasChildChildren ? '-open' : '' }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $child->category_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $child->category_code }} • {{ $child->businessSection->section_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="showEditModal({{ $child->id }})"
                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="showAddSubCategoryModal({{ $child->id }})"
                                            class="text-green-600 hover:text-green-900 text-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button onclick="toggleCategoryStatus({{ $child->id }}, {{ $child->is_active }})"
                                            class="{{ $child->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} text-sm">
                                        <i class="fas {{ $child->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </div>
                            </div>

                            @if($hasChildChildren)
                                <div class="space-y-2 ml-12">
                                    @foreach($child->children as $grandChild)
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 ml-12">
                                            <div class="flex-1 flex items-center">
                                                <div class="mr-3 {{ $grandChild->is_active ? 'text-primary' : 'text-gray-400' }}">
                                                    <i class="fas fa-folder"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-800">{{ $grandChild->category_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $grandChild->category_code }} • {{ $grandChild->businessSection->section_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="showEditModal({{ $grandChild->id }})"
                                                        class="text-blue-600 hover:text-blue-900 text-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="toggleCategoryStatus({{ $grandChild->id }}, {{ $grandChild->is_active }})"
                                                        class="{{ $grandChild->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} text-sm">
                                                    <i class="fas {{ $grandChild->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>

<!-- Categories Table -->
<div class="table-container">
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
                <tr class="hover:bg-gray-50">
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
                        <div class="flex space-x-2">
                            <button onclick="showEditModal({{ $category->id }})"
                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal({{ $category->id }})"
                                    class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="showAddSubCategoryModal({{ $category->id }})"
                                    class="text-green-600 hover:text-green-900" title="Add Sub-category">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button onclick="toggleCategoryStatus({{ $category->id }}, {{ $category->is_active }})"
                                    class="{{ $category->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                    title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                            <button onclick="deleteCategory({{ $category->id }})"
                                    class="text-red-600 hover:text-red-900" title="Delete">
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

<!-- Pagination -->
<div class="mt-6">
    {{ $categories->links() }}
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="categoryModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Create New Category</h3>
                <button class="close-modal text-white text-2xl" onclick="closeCategoryModal()">&times;</button>
            </div>
            <form id="categoryModalForm">
                @csrf
                <input type="hidden" id="categoryId" name="id">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="categoryCode" class="block text-sm font-medium text-gray-700 mb-2">Category Code *</label>
                            <input type="text" id="categoryCode" name="category_code" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                            <input type="text" id="categoryName" name="category_name" required
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
                            <label for="parentCategoryId" class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                            <select id="parentCategoryId" name="parent_category_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">None (Root Category)</option>
                                @foreach($rootCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        <div>
                            <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="sortOrder" name="sort_order" value="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="isActive" name="is_active" value="1"
                                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary" checked>
                            <label for="isActive" class="ml-2 text-sm text-gray-700">Active Category</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeCategoryModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">
                        Save Category
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
                <h3 class="text-xl font-bold">Category Details</h3>
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
    let currentPage = 1;
    let searchQuery = '';
    let sectionFilter = '';
    let parentFilter = '';
    let statusFilter = '';

    // Load categories on page load (for AJAX filtering)
    $(document).ready(function() {
        // Search functionality
        $('#searchInput').on('keyup', debounce(function() {
            searchQuery = $(this).val();
            loadCategories(1);
        }, 500));

        // Filter functionality
        $('#sectionFilter, #parentFilter, #statusFilter').on('change', function() {
            sectionFilter = $('#sectionFilter').val();
            parentFilter = $('#parentFilter').val();
            statusFilter = $('#statusFilter').val();
            loadCategories(1);
        });
    });

    // Load categories via AJAX
    function loadCategories(page = 1) {
        currentPage = page;
        const params = {
            page: page,
            per_page: 15
        };

        if (searchQuery) params.search = searchQuery;
        if (sectionFilter) params.business_section_id = sectionFilter;
        if (parentFilter) params.parent_category_id = parentFilter;
        if (statusFilter !== '') params.is_active = statusFilter;

        $.ajax({
            url: '{{ route("cafeteria.api.categories.index") }}',
            method: 'GET',
            data: params,
            success: function(response) {
                renderCategoriesTable(response.data);
                renderPagination(response);
            },
            error: function(xhr) {
                toastr.error('Error loading categories');
            }
        });
    }

    // Load categories tree via AJAX
    function loadCategoriesTree() {
        $.ajax({
            url: '{{ route("cafeteria.api.categories.index") }}',
            method: 'GET',
            data: { tree: true },
            success: function(categories) {
                renderCategoriesTree(categories);
            },
            error: function() {
                toastr.error('Error loading category tree');
            }
        });
    }

    // Render categories table via AJAX
    function renderCategoriesTable(categories) {
        const tbody = $('#categoriesTable tbody');
        tbody.empty();

        if (categories.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-tags text-4xl mb-2 text-gray-300"></i>
                        <p>No categories found</p>
                    </td>
                </tr>
            `);
            return;
        }

        categories.forEach(function(category) {
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-primary">${category.category_code}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${category.category_name}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs">${category.description || 'No description'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${category.business_section?.section_name || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${category.parent?.category_name || 'Root'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${category.sort_order}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${category.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showEditModal(${category.id})"
                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showDetailsModal(${category.id})"
                                    class="text-primary hover:text-red-700" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="showAddSubCategoryModal(${category.id})"
                                    class="text-green-600 hover:text-green-900" title="Add Sub-category">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button onclick="toggleCategoryStatus(${category.id}, ${category.is_active})"
                                    class="${category.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}"
                                    title="${category.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="fas ${category.is_active ? 'fa-ban' : 'fa-check'}"></i>
                            </button>
                            <button onclick="deleteCategory(${category.id})"
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

    // Render categories tree via AJAX
    function renderCategoriesTree(categories) {
        const container = $('#categoriesTree');
        container.empty();

        if (categories.length === 0) {
            container.html(`
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-folder-open text-4xl mb-2 text-gray-300"></i>
                    <p>No categories found</p>
                </div>
            `);
            return;
        }

        // Simple tree rendering for 3 levels max
        categories.forEach(function(category) {
            const hasChildren = category.children && category.children.length > 0;

            let html = `
                <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <div class="flex-1 flex items-center">
                        <div class="mr-3 ${category.is_active ? 'text-primary' : 'text-gray-400'}">
                            <i class="fas fa-folder${hasChildren ? '-open' : ''}"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">${category.category_name}</div>
                            <div class="text-xs text-gray-500">${category.category_code} • ${category.business_section?.section_name || 'N/A'}</div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="showEditModal(${category.id})"
                                class="text-blue-600 hover:text-blue-900 text-sm">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showAddSubCategoryModal(${category.id})"
                                class="text-green-600 hover:text-green-900 text-sm">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button onclick="toggleCategoryStatus(${category.id}, ${category.is_active})"
                                class="${category.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'} text-sm">
                            <i class="fas ${category.is_active ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                    </div>
                </div>
            `;

            if (hasChildren) {
                html += `<div class="space-y-2 ml-6">`;
                category.children.forEach(function(child) {
                    const hasChildChildren = child.children && child.children.length > 0;

                    html += `
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <div class="flex-1 flex items-center">
                                <div class="mr-3 ${child.is_active ? 'text-primary' : 'text-gray-400'}">
                                    <i class="fas fa-folder${hasChildChildren ? '-open' : ''}"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">${child.category_name}</div>
                                    <div class="text-xs text-gray-500">${child.category_code} • ${child.business_section?.section_name || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="showEditModal(${child.id})"
                                        class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="showAddSubCategoryModal(${child.id})"
                                        class="text-green-600 hover:text-green-900 text-sm">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button onclick="toggleCategoryStatus(${child.id}, ${child.is_active})"
                                        class="${child.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'} text-sm">
                                    <i class="fas ${child.is_active ? 'fa-ban' : 'fa-check'}"></i>
                                </button>
                            </div>
                        </div>
                    `;

                    if (hasChildChildren) {
                        html += `<div class="space-y-2 ml-6">`;
                        child.children.forEach(function(grandChild) {
                            html += `
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                    <div class="flex-1 flex items-center">
                                        <div class="mr-3 ${grandChild.is_active ? 'text-primary' : 'text-gray-400'}">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800">${grandChild.category_name}</div>
                                            <div class="text-xs text-gray-500">${grandChild.category_code} • ${grandChild.business_section?.section_name || 'N/A'}</div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="showEditModal(${grandChild.id})"
                                                class="text-blue-600 hover:text-blue-900 text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleCategoryStatus(${grandChild.id}, ${grandChild.is_active})"
                                                class="${grandChild.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'} text-sm">
                                            <i class="fas ${grandChild.is_active ? 'fa-ban' : 'fa-check'}"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        html += `</div>`;
                    }
                });
                html += `</div>`;
            }

            container.append(html);
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
                        <button onclick="loadCategories(${currentPage - 1})" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Previous
                        </button>
                    ` : ''}
                    ${response.next_page_url ? `
                        <button onclick="loadCategories(${currentPage + 1})" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                    <button onclick="${link.url ? `loadCategories(${currentPage - 1})` : ''}"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left h-5 w-5"></i>
                    </button>
                `;
            } else if (link.label.includes('Next')) {
                pagination += `
                    <button onclick="${link.url ? `loadCategories(${currentPage + 1})` : ''}"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right h-5 w-5"></i>
                    </button>
                `;
            } else {
                const isActive = link.active;
                const pageNumber = link.label;
                pagination += `
                    <button onclick="loadCategories(${pageNumber})"
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
    function showCreateModal(parentId = null) {
        $('#modalTitle').text(parentId ? 'Create Sub-category' : 'Create New Category');
        $('#categoryModalForm').attr('action', '{{ route("cafeteria.categories.store") }}');
        $('#categoryModalForm').attr('method', 'POST');
        $('#categoryId').val('');

        // Reset form
        $('#categoryCode').val('');
        $('#categoryName').val('');
        $('#businessSectionId').val('');
        $('#description').val('');
        $('#parentCategoryId').val(parentId || '');
        $('#sortOrder').val(0);
        $('#isActive').prop('checked', true);

        openCategoryModal();
    }

    function showAddSubCategoryModal(parentId) {
        showCreateModal(parentId);
    }

    function showEditModal(id) {
        $.ajax({
            url: `/cafeteria/categories/${id}`,
            method: 'GET',
            success: function(category) {
                $('#modalTitle').text('Edit Category');
                $('#categoryModalForm').attr('action', `/cafeteria/categories/${id}`);
                $('#categoryModalForm').attr('method', 'PUT');
                $('#categoryId').val(id);

                // Fill form
                $('#categoryCode').val(category.category_code);
                $('#categoryName').val(category.category_name);
                $('#businessSectionId').val(category.business_section_id);
                $('#description').val(category.description);
                $('#parentCategoryId').val(category.parent_category_id || '');
                $('#sortOrder').val(category.sort_order);
                $('#isActive').prop('checked', category.is_active == 1 || category.is_active === true);

                openCategoryModal();
            },
            error: function() {
                toastr.error('Error loading category details');
            }
        });
    }

    function showDetailsModal(id) {
        $.ajax({
            url: `/cafeteria/categories/${id}`,
            method: 'GET',
            success: function(category) {
                const detailsHtml = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Code</p>
                            <p class="font-medium">${category.category_code}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium">${category.category_name}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="font-medium">${category.description || 'No description'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Section</p>
                            <p class="font-medium">${category.business_section?.section_name || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Parent</p>
                            <p class="font-medium">${category.parent?.category_name || 'Root'}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Sort Order</p>
                            <p class="font-medium">${category.sort_order}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${category.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">Audit Trail</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Created By</p>
                                <p class="font-medium">${category.creator ? category.creator.name : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Created At</p>
                                <p class="font-medium">${new Date(category.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Last Updated</p>
                                <p class="font-medium">${new Date(category.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                `;

                $('#detailsContent').html(detailsHtml);
                openDetailsModal();
            },
            error: function() {
                toastr.error('Error loading category details');
            }
        });
    }

    function openCategoryModal() {
        $('#categoryModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeCategoryModal() {
        $('#categoryModal').addClass('hidden');
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
    $('#categoryModalForm').on('submit', function(e) {
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
                toastr.success('Category saved successfully');
                closeCategoryModal();
                loadCategories(1);
                loadCategoriesTree();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        toastr.error(errors[field][0]);
                    }
                } else {
                    toastr.error('Error saving category');
                }
            }
        });
    });

    // Toggle category status
    function toggleCategoryStatus(id, currentStatus) {
        const action = currentStatus ? 'deactivate' : 'activate';
        if (confirm(`Are you sure you want to ${action} this category?`)) {
            $.ajax({
                url: `/cafeteria/categories/${id}`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: !currentStatus
                },
                success: function() {
                    toastr.success(`Category ${action}d successfully`);
                    loadCategories(1);
                    loadCategoriesTree();
                },
                error: function() {
                    toastr.error('Error updating category');
                }
            });
        }
    }

    // Delete category
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category? This will fail if it has products or sub-categories.')) {
            $.ajax({
                url: `/cafeteria/categories/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    toastr.success('Category deleted successfully');
                    loadCategories(1);
                    loadCategoriesTree();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Error deleting category');
                }
            });
        }
    }

    // Auto-generate category code from name
    $('#categoryName').on('blur', function() {
        const codeInput = $('#categoryCode');
        if (!codeInput.val() && $(this).val()) {
            const code = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 20);
            codeInput.val(code.toUpperCase());
        }
    });
</script>
@endsection
