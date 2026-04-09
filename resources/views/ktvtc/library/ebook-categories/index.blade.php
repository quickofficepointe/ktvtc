@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">E-Book Categories</h1>
            <p class="text-gray-600 mt-2">Manage and organize digital book collections</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Category
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Categories</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeCategories }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total E-Books</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalEBooks }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Books/Category</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalCategories > 0 ? round($totalEBooks / $totalCategories, 1) : 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white"
                                 style="background-color: {{ $category->color }}">
                                @if($category->icon)
                                    <i class="fas fa-{{ $category->icon }} text-xl"></i>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $category->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $category->e_books_count }} eBooks</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($category->description)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $category->description }}</p>
                    @endif

                    <div class="flex justify-end space-x-2 pt-4 border-t border-amber-100">
                        <button onclick="openEditModal({{ $category->id }})"
                            class="px-3 py-1.5 text-sm border border-amber-300 text-amber-700 hover:bg-amber-50 rounded-lg transition-colors">
                            Edit
                        </button>
                        <button onclick="toggleStatus({{ $category->id }})"
                            class="px-3 py-1.5 text-sm border {{ $category->is_active ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-green-300 text-green-700 hover:bg-green-50' }} rounded-lg transition-colors">
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
                        <p class="text-gray-600 mb-4">Start organizing your digital library by creating categories.</p>
                        <button onclick="openCreateModal()"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Create First Category
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif" id="modalTitle">New Category</h3>
                            <p class="text-amber-100 text-sm" id="modalSubtitle">Create a new e-book category</p>
                        </div>
                    </div>
                    <button onclick="closeModal()" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="categoryForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <input type="hidden" name="category_id" id="categoryId">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                            <input type="text" name="name" id="categoryName" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="categoryDescription" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Icon (FontAwesome)</label>
                                <input type="text" name="icon" id="categoryIcon" placeholder="book, tag, folder, etc."
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">e.g., book, folder, tag, star</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <input type="color" name="color" id="categoryColor" value="#3b82f6"
                                       class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" name="sort_order" id="categorySortOrder" value="0"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="categoryIsActive" value="1" class="mr-2">
                            <label class="text-sm font-medium text-gray-700">Active</label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeModal()"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700">
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'New Category';
        document.getElementById('modalSubtitle').textContent = 'Create a new e-book category';
        document.getElementById('methodField').value = 'POST';
        document.getElementById('categoryForm').action = '{{ route("library.ebook-categories.store") }}';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryIcon').value = '';
        document.getElementById('categoryColor').value = '#3b82f6';
        document.getElementById('categorySortOrder').value = '0';
        document.getElementById('categoryIsActive').checked = true;
        document.getElementById('categoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openEditModal(id) {
        fetch(`/library/ebook-categories/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').textContent = 'Edit Category';
                document.getElementById('modalSubtitle').textContent = 'Update category information';
                document.getElementById('methodField').value = 'PUT';
                document.getElementById('categoryForm').action = `/library/ebook-categories/${id}`;
                document.getElementById('categoryId').value = id;
                document.getElementById('categoryName').value = data.name;
                document.getElementById('categoryDescription').value = data.description || '';
                document.getElementById('categoryIcon').value = data.icon || '';
                document.getElementById('categoryColor').value = data.color || '#3b82f6';
                document.getElementById('categorySortOrder').value = data.sort_order || 0;
                document.getElementById('categoryIsActive').checked = data.is_active === 1;
                document.getElementById('categoryModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
    }

    function toggleStatus(id) {
        if(confirm('Are you sure you want to change this category\'s status?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/library/ebook-categories/${id}/toggle-status`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endsection
