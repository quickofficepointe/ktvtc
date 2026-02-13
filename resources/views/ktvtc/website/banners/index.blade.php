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
            <h1 class="text-3xl font-bold text-gray-900">Banners</h1>
            <p class="text-gray-600 mt-2">Manage website banners and hero sections</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Banner
        </button>
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
                    <p class="text-sm font-medium text-gray-600">Total Banners</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $banners->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Banners</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $banners->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Highest Order</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $banners->max('order') ?? 0 }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Latest Banner</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $banners->first() ? $banners->first()->created_at->format('M d') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Banners Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Banners</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Banner Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Desktop Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mobile Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Button</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($banners as $banner)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $banner->title ?? 'Untitled Banner' }}</h3>
                                    @if($banner->button_link)
                                        <p class="text-sm text-gray-500 mt-1 truncate">{{ $banner->button_link }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $banner->desktop_image) }}"
                                     alt="Desktop Banner"
                                     class="w-24 h-16 object-cover rounded-lg shadow-sm">
                            </td>
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $banner->mobile_image) }}"
                                     alt="Mobile Banner"
                                     class="w-16 h-16 object-cover rounded-lg shadow-sm">
                            </td>
                            <td class="px-6 py-4">
                                @if($banner->button_text)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $banner->button_text }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $banner->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold text-sm">
                                    {{ $banner->order }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $banner->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete('{{ route('banners.destroy', $banner->id) }}')"
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No banners found</h3>
                                    <p class="text-gray-600 mb-4">Get started by creating your first banner.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Banner
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

<!-- Create Banner Modal -->
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
                            <h3 class="text-xl font-bold text-white">Create New Banner</h3>
                            <p class="text-red-100 text-sm">Add a new banner to your website</p>
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
                <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   placeholder="Banner title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                            <input type="number" name="order" value="{{ old('order', 0) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                            <input type="text" name="button_text" value="{{ old('button_text') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   placeholder="Learn More">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                            <input type="url" name="button_link" value="{{ old('button_link') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   placeholder="https://example.com">
                        </div>

                        <!-- Image Uploads -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Desktop Image *</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="createDesktopPreview" class="mx-auto h-20 w-32 object-cover rounded-lg mb-2 hidden">
                                    <svg id="createDesktopPlaceholder" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="create_desktop_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                            <span>Upload desktop image</span>
                                            <input id="create_desktop_image" name="desktop_image" type="file" class="sr-only" accept="image/*" required onchange="previewCreateImage(this, 'createDesktopPreview', 'createDesktopPlaceholder')">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Image *</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="createMobilePreview" class="mx-auto h-20 w-20 object-cover rounded-lg mb-2 hidden">
                                    <svg id="createMobilePlaceholder" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="create_mobile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                            <span>Upload mobile image</span>
                                            <input id="create_mobile_image" name="mobile_image" type="file" class="sr-only" accept="image/*" required onchange="previewCreateImage(this, 'createMobilePreview', 'createMobilePlaceholder')">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Banner Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this banner</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Banner</span>
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
                            Create Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Banner</h3>
                            <p class="text-red-100 text-sm">Update banner information</p>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="editTitle" name="title"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                            <input type="number" id="editOrder" name="order"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                            <input type="text" id="editButtonText" name="button_text"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                            <input type="url" id="editButtonLink" name="button_link"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <!-- Image Uploads -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Desktop Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="editDesktopPreview" class="mx-auto h-20 w-32 object-cover rounded-lg mb-2">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="edit_desktop_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                            <span>Change desktop image</span>
                                            <input id="edit_desktop_image" name="desktop_image" type="file" class="sr-only" accept="image/*" onchange="previewEditImage(this, 'editDesktopPreview')">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Leave empty to keep current</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="editMobilePreview" class="mx-auto h-20 w-20 object-cover rounded-lg mb-2">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="edit_mobile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                            <span>Change mobile image</span>
                                            <input id="edit_mobile_image" name="mobile_image" type="file" class="sr-only" accept="image/*" onchange="previewEditImage(this, 'editMobilePreview')">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Leave empty to keep current</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Banner Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this banner</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Banner</span>
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
                            Update Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Add this function - it's missing from your code
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// You already have this one:
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = 'auto';

    // Reset create form previews
    document.getElementById('createDesktopPreview').classList.add('hidden');
    document.getElementById('createMobilePreview').classList.add('hidden');
    document.getElementById('createDesktopPlaceholder').classList.remove('hidden');
    document.getElementById('createMobilePlaceholder').classList.remove('hidden');
    document.getElementById('create_desktop_image').value = '';
    document.getElementById('create_mobile_image').value = '';
}
    // Image Preview Functions
    function previewCreateImage(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const file = input.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }

            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
            if (placeholder) {
                placeholder.classList.remove('hidden');
            }
        }
    }

    function previewEditImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
            }

            reader.readAsDataURL(file);
        }
    }

    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Reset create form previews
        document.getElementById('createDesktopPreview').classList.add('hidden');
        document.getElementById('createMobilePreview').classList.add('hidden');
        document.getElementById('createDesktopPlaceholder').classList.remove('hidden');
        document.getElementById('createMobilePlaceholder').classList.remove('hidden');
        document.getElementById('create_desktop_image').value = '';
        document.getElementById('create_mobile_image').value = '';
    }

    // Edit Modal Functions
   // Edit Modal Functions
function openEditModal(bannerId) {
    // Show loading state
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Show loading indicator
    const form = modal.querySelector('form');
    const originalContent = form.innerHTML;
    form.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            <span class="ml-3 text-gray-600">Loading banner data...</span>
        </div>
    `;

    // Fetch banner data
   fetch(`/website/banners/${bannerId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Restore form content
            form.innerHTML = originalContent;

            // Set form action
            form.action = `/website/banners/${bannerId}`;

            // Fill form fields
            document.getElementById('editTitle').value = data.title || '';
            document.getElementById('editOrder').value = data.order;
            document.getElementById('editButtonText').value = data.button_text || '';
            document.getElementById('editButtonLink').value = data.button_link || '';

            // Set checkbox state
            const isActiveCheckbox = document.getElementById('editIsActive');
            if (isActiveCheckbox) {
                isActiveCheckbox.checked = data.is_active;
            }

            // Set image previews
            const desktopPreview = document.getElementById('editDesktopPreview');
            const mobilePreview = document.getElementById('editMobilePreview');

            if (data.desktop_image) {
                desktopPreview.src = `/storage/${data.desktop_image}?t=${new Date().getTime()}`;
                desktopPreview.onerror = function() {
                    this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjYwIiB2aWV3Qm94PSIwIDAgMTAwIDYwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iNjAiIGZpbGw9IiNFNUU1RTUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEwIiBmaWxsPSIjOTk5Ij5EZXNrdG9wIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                };
            }

            if (data.mobile_image) {
                mobilePreview.src = `/storage/${data.mobile_image}?t=${new Date().getTime()}`;
                mobilePreview.onerror = function() {
                    this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNFNUU1RTUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEwIiBmaWxsPSIjOTk5Ij5Nb2JpbGUgSW1hZ2U8L3RleHQ+PC9zdmc+';
                };
            }

            // Reset file inputs
            const desktopInput = document.getElementById('edit_desktop_image');
            const mobileInput = document.getElementById('edit_mobile_image');
            if (desktopInput) desktopInput.value = '';
            if (mobileInput) mobileInput.value = '';
        })
        .catch(error => {
            console.error('Error loading banner:', error);

            // Show error message
            form.innerHTML = `
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Banner</h3>
                    <p class="text-gray-600 mb-4">Could not load banner data. Please try again.</p>
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">
                        Close
                    </button>
                </div>
            `;
        });
}

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this banner?')) {
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
    .transform {
        transition: transform 0.2s ease-in-out;
    }
</style>
@endsection
