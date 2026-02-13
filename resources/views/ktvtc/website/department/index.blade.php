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

    {{-- Error Message --}}
    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
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
            <h1 class="text-3xl font-bold text-gray-900">Department Management</h1>
            <p class="text-gray-600 mt-2">Manage academic departments and their details</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Department
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Departments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Departments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->sum(function($dept) { return $dept->courses->count(); }) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Departments Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Departments</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Courses</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($departments as $department)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    @if($department->cover_image)
                                        <img src="{{ $department->cover_image }}" alt="{{ $department->name }}"
                                             class="w-12 h-12 rounded-lg object-cover shadow-sm">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $department->name }}</h3>
                                        @if($department->description)
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ strip_tags($department->description) }}</p>
                                        @endif
                                        <div class="flex items-center mt-2 text-xs text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Created {{ $department->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-2xl font-bold text-primary">{{ $department->courses->count() }}</span>
                                    <p class="text-xs text-gray-500 mt-1">courses</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $department->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $department->id }}, '{{ addslashes($department->name) }}', `{!! addslashes($department->description) !!}`, '{{ addslashes($department->cover_image) }}', {{ $department->is_active }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete('{{ route('departments.destroy', $department) }}')"
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
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No departments found</h3>
                                    <p class="text-gray-600 mb-4">Get started by creating your first department.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Add New Department
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

<!-- Create Department Modal -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create New Department</h3>
                            <p class="text-red-100 text-sm">Add a new academic department</p>
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
                <form action="{{ route('departments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                               placeholder="Enter department name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                 class="summernote w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                 placeholder="Describe the department's purpose and focus">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <img id="createImagePreview" class="mx-auto h-32 w-full object-cover rounded-lg mb-2 hidden">
                                <svg id="createImagePlaceholder" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="create_cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Upload cover image</span>
                                        <input id="create_cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewCreateImage(this)">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Department Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this department</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Department</span>
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
                            Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-white">Edit Department</h3>
                    <p class="text-red-100 text-sm">Update department information</p>
                </div>
                <button onclick="closeEditModal()" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department Name *</label>
                        <input type="text" id="editName" name="name" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="editDescription" name="description" rows="4"
                                  class="summernote w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <img id="editImagePreview" class="mx-auto h-32 w-full object-cover rounded-lg mb-2" style="display: none;">
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="edit_cover_image" class="cursor-pointer text-primary font-medium hover:text-red-500">
                                        <span>Change cover image</span>
                                        <input id="edit_cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewEditImage(this)">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Department Status</h4>
                                <p class="text-sm text-gray-600">Set active/inactive</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-full"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(id, name, description, coverImage, isActive) {
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Set form action to correct route - FIXED: Use direct URL path
    document.getElementById('editForm').action = `/website/departments/${id}`;

    // Fill form fields
    document.getElementById('editName').value = name;

    // Handle Summernote initialization
    const descriptionField = $('#editDescription');
    if (typeof descriptionField.summernote === 'function') {
        if (!descriptionField.parent().hasClass('note-editor')) {
            descriptionField.summernote();
        }
        descriptionField.summernote('code', description);
    } else {
        descriptionField.val(description);
    }

    document.getElementById('editIsActive').checked = !!isActive;

    // Handle image preview
    const preview = document.getElementById('editImagePreview');
    if (coverImage && coverImage !== 'null' && coverImage !== '') {
        preview.src = coverImage;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
        preview.src = '';
    }
    document.getElementById('edit_cover_image').value = '';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';

    // Reset Summernote
    const descriptionField = $('#editDescription');
    if (typeof descriptionField.summernote === 'function') {
        descriptionField.summernote('reset');
    } else {
        descriptionField.val('');
    }
}

function previewEditImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('editImagePreview').src = e.target.result;
            document.getElementById('editImagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
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

    // Reset create form preview
    document.getElementById('createImagePreview').classList.add('hidden');
    document.getElementById('createImagePlaceholder').classList.remove('hidden');
    document.getElementById('create_cover_image').value = '';
}

// Delete Confirmation
function confirmDelete(url) {
    if(confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
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

// Image Preview Functions
function previewCreateImage(input) {
    const preview = document.getElementById('createImagePreview');
    const placeholder = document.getElementById('createImagePlaceholder');
    const file = input.files[0];

    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, JPG, PNG).');
            input.value = '';
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size should be less than 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }

        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
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
