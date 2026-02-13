@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Blog Categories</h1>
            <p class="text-gray-600 mt-2">Manage blog post categories</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Category
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Categories</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $categories->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Categories</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $categories->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Inactive Categories</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $categories->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Latest Category</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $categories->first() ? $categories->first()->created_at->format('M d') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Categories</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Posts</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ $category->slug }}</p>
                                        @if($category->description)
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-2xl font-bold text-primary">{{ $category->posts_count ?? 0 }}</span>
                                    <p class="text-xs text-gray-500 mt-1">posts</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $category->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $category->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', `{!! addslashes($category->description) !!}`, {{ $category->is_active }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete('{{ route('blog-categories.destroy', $category->id) }}')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
                                    <p class="text-gray-600 mb-4">Get started by creating your first category.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Category
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create New Category</h3>
                            <p class="text-red-100 text-sm">Add a new blog category</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form action="{{ route('blog-categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                               placeholder="Enter category name">
                    </div>

                   <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
    <textarea name="description" rows="4"
              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
              placeholder="Describe the category">{{ old('description') }}</textarea>
</div>

<!-- Add Cover Image Upload Section -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
                <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                    <span>Upload an image</span>
                    <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'createPreview')">
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 3MB</p>
        </div>
    </div>

    <!-- Image Preview -->
    <div id="createPreview" class="mt-4 hidden">
        <div class="relative inline-block">
            <img id="createPreviewImage" class="h-32 w-32 object-cover rounded-lg shadow-md">
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                <span id="createFileSize">0 MB</span>
            </div>
        </div>
        <button type="button" onclick="removeImage('createPreview')" class="mt-2 text-sm text-red-600 hover:text-red-800">
            Remove image
        </button>
    </div>
</div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Category Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this category</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Category</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Category</h3>
                            <p class="text-red-100 text-sm">Update category information</p>
                        </div>
                    </div>
                    <button onclick="closeEditModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" enctype="multipart/form-data" >
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" id="editName" name="name" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>

                    <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
    <textarea id="editDescription" name="description" rows="4"
              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
</div>

<!-- Add Cover Image Upload Section -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>

    <!-- Current Image Preview -->
    <div id="currentImageContainer" class="mb-4 hidden">
        <p class="text-sm text-gray-600 mb-2">Current Image:</p>
        <div class="relative inline-block">
            <img id="currentImage" class="h-32 w-32 object-cover rounded-lg shadow-md">
            <div class="absolute top-2 right-2">
                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">Current</span>
            </div>
        </div>
    </div>

    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
                <label for="editCoverImage" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                    <span>Upload new image</span>
                    <input id="editCoverImage" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'editPreview')">
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 3MB</p>
        </div>
    </div>

    <!-- New Image Preview -->
    <div id="editPreview" class="mt-4 hidden">
        <p class="text-sm text-gray-600 mb-2">New Image:</p>
        <div class="relative inline-block">
            <img id="editPreviewImage" class="h-32 w-32 object-cover rounded-lg shadow-md">
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                <span id="editFileSize">0 MB</span>
            </div>
        </div>
        <button type="button" onclick="removeImage('editPreview')" class="mt-2 text-sm text-red-600 hover:text-red-800">
            Remove new image
        </button>
    </div>
</div>
                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Category Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this category</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Category</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Edit Modal Functions
    function openEditModal(id, name, description, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set form action
            document.getElementById('editForm').action = `/website/blog-categories/${id}`;

        // Fill form fields
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editIsActive').checked = isActive;
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
// Image preview functionality
function previewImage(input, previewType) {
    const file = input.files[0];
    if (file) {
        // Check file size (3MB = 3 * 1024 * 1024 bytes)
        const maxSize = 3 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 3MB');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewId = previewType + 'Image';
            const fileSizeId = previewType === 'createPreview' ? 'createFileSize' : 'editFileSize';

            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewType).classList.remove('hidden');

            // Display file size
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            document.getElementById(fileSizeId).textContent = fileSizeMB + ' MB';
        }
        reader.readAsDataURL(file);
    }
}

function removeImage(previewType) {
    const inputId = previewType === 'createPreview' ? 'cover_image' : 'editCoverImage';
    document.getElementById(inputId).value = '';
    document.getElementById(previewType).classList.add('hidden');
}


    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

    // Close modals when clicking on backdrop
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .transform {
        transition: transform 0.2s ease-in-out;
    }
</style>
@endsection
