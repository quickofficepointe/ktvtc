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
            <h1 class="text-3xl font-bold text-gray-900">Gallery Management</h1>
            <p class="text-gray-600 mt-2">Manage photo galleries and images</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openAddImagesModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Add Images
            </button>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Gallery
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Galleries</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $galleries->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Galleries</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $galleries->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Images</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $galleries->sum(fn($gallery) => $gallery->images->count()) }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Latest Gallery</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $galleries->first() ? $galleries->first()->created_at->format('M d') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Galleries Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Galleries</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gallery Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Images</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($galleries as $gallery)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    @if($gallery->cover_image)
                                        <img src="{{ asset('storage/'.$gallery->cover_image) }}" alt="{{ $gallery->title }}"
                                             class="w-16 h-16 rounded-lg object-cover shadow-sm">
                                    @else
                                        <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $gallery->title }}</h3>
                                        @if($gallery->description)
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $gallery->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-2xl font-bold text-primary">{{ $gallery->images->count() }}</span>
                                    <p class="text-xs text-gray-500 mt-1">images</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $gallery->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $gallery->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $gallery->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $gallery->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openViewModal({{ $gallery->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                    <button onclick="openEditModal({{ $gallery->id }}, '{{ addslashes($gallery->title) }}', `{!! addslashes($gallery->description) !!}`, {{ $gallery->is_active }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete('{{ route('galleries.destroy', $gallery->id) }}')"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No galleries found</h3>
                                    <p class="text-gray-600 mb-4">Get started by creating your first gallery.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Gallery
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

<!-- Create Gallery Modal -->
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
                            <h3 class="text-xl font-bold text-white">Create New Gallery</h3>
                            <p class="text-red-100 text-sm">Add a new photo gallery</p>
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
                <form action="{{ route('galleries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                               placeholder="Enter gallery title">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                  placeholder="Describe the gallery">{{ old('description') }}</textarea>
                    </div>

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="create_cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Upload cover image</span>
                                        <input id="create_cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewCoverImage(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                <div id="coverImagePreview" class="mt-2 hidden">
                                    <img id="previewImage" class="mx-auto h-32 rounded-lg shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Gallery Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this gallery</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Gallery</span>
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
                            Create Gallery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Gallery Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Gallery</h3>
                            <p class="text-red-100 text-sm">Update gallery information</p>
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
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" id="editTitle" name="title" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="editDescription" name="description" rows="4"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                    </div>

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image (Leave empty to keep current)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="edit_cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Upload new cover</span>
                                        <input id="edit_cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewEditCoverImage(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                <div id="editCoverImagePreview" class="mt-2 hidden">
                                    <img id="editPreviewImage" class="mx-auto h-32 rounded-lg shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Gallery Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this gallery</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Gallery</span>
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
                            Update Gallery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Gallery Modal -->
<div id="viewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeViewModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="viewTitle" class="text-xl font-bold text-white">Gallery Title</h3>
                            <p id="viewDescription" class="text-red-100 text-sm"></p>
                        </div>
                    </div>
                    <button onclick="closeViewModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="viewImages" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <!-- Images will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Images Modal - FIXED: Removed blue theme, added selected images count -->
<div id="addImagesModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeAddImagesModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header - FIXED: Changed from blue to primary red theme -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Add Images to Gallery</h3>
                            <p class="text-red-100 text-sm">Upload multiple images to a gallery</p>
                        </div>
                    </div>
                    <button onclick="closeAddImagesModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="addImagesForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Gallery Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Gallery *</label>
                        <select id="gallerySelect" name="gallery_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                onchange="updateFormAction(this.value)">
                            <option value="">Select a gallery</option>
                            @foreach($galleries as $gallery)
                                <option value="{{ $gallery->id }}">{{ $gallery->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Multiple File Upload - FIXED: Added selected images count display -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Images *</label>

                        <!-- Selected images count display -->
                        <div id="selectedImagesCount" class="hidden mb-3 p-3 bg-primary-50 border border-primary-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-primary-900">
                                        Selected <span id="selectedCount">0</span> image(s)
                                    </span>
                                </div>
                                <button type="button" onclick="clearSelectedImages()" class="text-sm text-red-600 hover:text-red-800">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="multiple_images" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Upload multiple images</span>
                                        <input id="multiple_images" name="images[]" type="file" multiple class="sr-only" accept="image/*" required onchange="updateSelectedImagesCount(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 4MB each</p>

                                <!-- Image preview container -->
                                <div id="imagePreviewContainer" class="mt-4 grid grid-cols-3 gap-2 hidden">
                                    <!-- Image previews will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer - FIXED: Changed from blue to primary red theme -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeAddImagesModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Upload Images
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
        document.getElementById('coverImagePreview').classList.add('hidden');
    }

    // Edit Modal Functions
    function openEditModal(id, title, description, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // FIXED: Added website prefix to route
        document.getElementById('editForm').action = `/website/galleries/${id}`;

        // Fill form fields
        document.getElementById('editTitle').value = title;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editIsActive').checked = isActive;
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('editCoverImagePreview').classList.add('hidden');
    }

    // View Modal Functions
    function openViewModal(galleryId) {
        const modal = document.getElementById('viewModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // FIXED: Added website prefix to route
        fetch(`/website/galleries/${galleryId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('viewTitle').textContent = data.title;
                document.getElementById('viewDescription').textContent = data.description || '';

                const imagesContainer = document.getElementById('viewImages');
                imagesContainer.innerHTML = '';

                if (data.images && data.images.length > 0) {
                    data.images.forEach(image => {
                        const imageElement = `
                            <div class="relative group">
                                <img src="/storage/${image.image_path}"
                                     alt="${image.caption || 'Gallery image'}"
                                     class="w-full h-32 object-cover rounded-lg shadow-sm cursor-pointer transition-transform group-hover:scale-105"
                                     onclick="openFullImage('/storage/${image.image_path}', '${image.caption || ''}')">
                                ${image.caption ? `
                                    <p class="text-xs text-gray-600 mt-1 truncate">${image.caption}</p>
                                ` : ''}
                                <button onclick="deleteImage(${galleryId}, ${image.id})"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                    ×
                                </button>
                            </div>
                        `;
                        imagesContainer.innerHTML += imageElement;
                    });
                } else {
                    imagesContainer.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-500 mt-4">No images in this gallery yet.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading gallery images');
            });
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Add Images Modal Functions
    function openAddImagesModal() {
        document.getElementById('addImagesModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAddImagesModal() {
        document.getElementById('addImagesModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        clearSelectedImages();
    }

    // NEW FUNCTION: Update selected images count and show previews
    function updateSelectedImagesCount(input) {
        const files = input.files;
        const countElement = document.getElementById('selectedCount');
        const countContainer = document.getElementById('selectedImagesCount');
        const previewContainer = document.getElementById('imagePreviewContainer');

        if (files.length > 0) {
            countElement.textContent = files.length;
            countContainer.classList.remove('hidden');
            previewContainer.classList.remove('hidden');

            // Clear previous previews
            previewContainer.innerHTML = '';

            // Create preview for each selected file (limit to 6 for display)
            const maxPreviews = Math.min(files.length, 6);
            for (let i = 0; i < maxPreviews; i++) {
                const file = files[i];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'relative';
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border border-gray-200">
                        <div class="absolute top-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded">${i + 1}</div>
                    `;
                    previewContainer.appendChild(preview);
                };

                reader.readAsDataURL(file);
            }

            // If more than 6 files, show indicator
            if (files.length > 6) {
                const extraCount = files.length - 6;
                const extraIndicator = document.createElement('div');
                extraIndicator.className = 'flex items-center justify-center bg-gray-100 rounded-lg border border-gray-200';
                extraIndicator.innerHTML = `
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-600">+${extraCount}</div>
                        <div class="text-xs text-gray-500">more</div>
                    </div>
                `;
                previewContainer.appendChild(extraIndicator);
            }
        } else {
            countContainer.classList.add('hidden');
            previewContainer.classList.add('hidden');
            previewContainer.innerHTML = '';
        }
    }

    // NEW FUNCTION: Clear selected images
    function clearSelectedImages() {
        const fileInput = document.getElementById('multiple_images');
        fileInput.value = '';

        document.getElementById('selectedImagesCount').classList.add('hidden');
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        document.getElementById('imagePreviewContainer').innerHTML = '';
        document.getElementById('selectedCount').textContent = '0';
    }

    // Update form action for add images - FIXED: Added website prefix
    function updateFormAction(galleryId) {
        const form = document.getElementById('addImagesForm');
        form.action = `/website/galleries/${galleryId}/images`;
    }

    // NEW FUNCTION: Preview cover image
    function previewCoverImage(input) {
        const preview = document.getElementById('previewImage');
        const previewContainer = document.getElementById('coverImagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            previewContainer.classList.add('hidden');
        }
    }

    // NEW FUNCTION: Preview edit cover image
    function previewEditCoverImage(input) {
        const preview = document.getElementById('editPreviewImage');
        const previewContainer = document.getElementById('editCoverImagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            previewContainer.classList.add('hidden');
        }
    }

    // Delete Confirmation - FIXED: Added website prefix
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this gallery? All images will also be deleted.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';

            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete single image - FIXED: Added website prefix
    function deleteImage(galleryId, imageId) {
        if(confirm('Are you sure you want to delete this image?')) {
            fetch(`/website/galleries/${galleryId}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error deleting image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting image');
            });
        }
    }

    // Full image viewer
    function openFullImage(src, caption) {
        const viewer = document.createElement('div');
        viewer.className = 'fixed inset-0 bg-black bg-opacity-90 z-60 flex items-center justify-center';
        viewer.innerHTML = `
            <span class="absolute top-5 right-5 text-white text-4xl cursor-pointer" onclick="this.parentElement.remove()">&times;</span>
            <div class="max-w-3xl mx-auto text-center">
                <img src="${src}" class="max-h-[80vh] mx-auto rounded shadow">
                ${caption ? `<p class="text-gray-200 mt-4">${caption}</p>` : ''}
            </div>
        `;
        document.body.appendChild(viewer);
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeViewModal();
            closeAddImagesModal();
        }
    });

    // Close modals when clicking on backdrop
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
            closeCreateModal();
            closeEditModal();
            closeViewModal();
            closeAddImagesModal();
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

    /* Primary color helper classes */
    .bg-primary-50 {
        background-color: rgba(185, 28, 28, 0.05);
    }
    .border-primary-200 {
        border-color: rgba(185, 28, 28, 0.2);
    }
    .text-primary-600 {
        color: #B91C1C;
    }
    .text-primary-900 {
        color: #7F1D1D;
    }
</style>
@endsection
