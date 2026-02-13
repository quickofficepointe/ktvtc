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
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Course Management</h1>
                <p class="text-gray-600 mt-2">Manage all courses and their details</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add New Course
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $courses->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $courses->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Departments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $departments->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Delivery Modes</p>
                    <p class="text-2xl font-bold text-gray-900">3</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Courses Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Courses</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Course Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Duration & Level
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Department
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Delivery
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse($courses as $course)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Course Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start space-x-4">
                                    @if($course->cover_image_url)
                                        <img src="{{ $course->cover_image_url }}"
                                             alt="{{ $course->name }}"
                                             class="w-12 h-12 rounded-lg object-cover shadow-sm flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 6.253v13m0-13C10.8 5.5 9.2 5 7.5 5S4.2 5.5 3 6.3v13C4.2 18.5 5.8 18 7.5 18s3.3.5 4.5 1.3m0-13C13.2 5.5 14.8 5 16.5 5c1.7 0 3.3.5 4.5 1.3v13c-1.2-.8-2.8-1.3-4.5-1.3s-3.3.5-4.5 1.3"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $course->name }}</h3>
                                        <p class="text-xs text-gray-600 line-clamp-2">{{ strip_tags($course->description) }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Duration & Level --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-900">{{ $course->duration ?? 'Not set' }}</div>
                                <div class="text-xs text-gray-600">{{ $course->level ?? 'N/A' }}</div>
                            </td>

                            {{-- Department --}}
                            <td class="px-6 py-4 align-top">
                                <span class="text-sm font-medium text-gray-900 truncate max-w-xs block">
                                    {{ $course->department->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Delivery --}}
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                    {{ ucfirst($course->delivery_mode ?? 'N/A') }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $course->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $course->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $course->id }}',
                                        '{{ $course->department_id }}',
                                        '{{ addslashes($course->name) }}',
                                        '{{ addslashes($course->code) }}',
                                        '{{ addslashes($course->duration) }}',
                                        '{{ addslashes($course->total_hours) }}',
                                        '{{ addslashes($course->schedule) }}',
                                        `{{ addslashes($course->description) }}`,
                                        `{{ addslashes($course->requirements) }}`,
                                        `{{ addslashes($course->fees_breakdown) }}`,
                                        '{{ $course->delivery_mode }}',
                                        `{{ addslashes($course->what_you_will_learn) }}`,
                                        '{{ $course->cover_image_url }}',
                                        '{{ $course->level }}',
                                        '{{ $course->featured }}',
                                        '{{ $course->sort_order }}',
                                        '{{ $course->is_active }}'
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('courses.destroy', $course->id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No courses available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Summernote CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<!-- Create Course Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create New Course</h3>
                            <p class="text-red-100 text-sm">Add a new course to the system</p>
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
                <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                                <select name="department_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Enter course name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Code</label>
                                <input type="text" name="code" value="{{ old('code') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., CSC101">
                            </div>

                           <!-- In the create modal -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Modes *</label>
    <div class="space-y-2">
        <div class="flex items-center">
            <input type="checkbox" id="onsite" name="delivery_modes[]" value="onsite"
                   {{ old('delivery_modes') && in_array('onsite', old('delivery_modes')) ? 'checked' : '' }}
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="onsite" class="ml-2 text-sm text-gray-700">Onsite (Physical)</label>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="virtual" name="delivery_modes[]" value="virtual"
                   {{ old('delivery_modes') && in_array('virtual', old('delivery_modes')) ? 'checked' : '' }}
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="virtual" class="ml-2 text-sm text-gray-700">Virtual (Online)</label>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="hybrid" name="delivery_modes[]" value="hybrid"
                   {{ old('delivery_modes') && in_array('hybrid', old('delivery_modes')) ? 'checked' : '' }}
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="hybrid" class="ml-2 text-sm text-gray-700">Hybrid (Mixed)</label>
        </div>
    </div>
    @error('delivery_modes')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration *</label>
                                <input type="text" name="duration" value="{{ old('duration') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 3 months, 6 weeks">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Hours</label>
                                <input type="text" name="total_hours" value="{{ old('total_hours') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 120 hours">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
                                <select name="level" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Level</option>
                                    <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                                <input type="text" name="schedule" value="{{ old('schedule') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Mon-Wed-Fri, 6-8 PM">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="0">
                            </div>

                            <div class="md:col-span-2">
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
                                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Details -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Course Details
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 summernote focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Describe the course content and objectives">{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                                <textarea name="requirements" rows="3"
                                          class="w-full border border-gray-300 summernote rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="List the entry requirements for this course">{{ old('requirements') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">What You Will Learn</label>
                                <textarea name="what_you_will_learn" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 summernote py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Key learning outcomes and skills gained">{{ old('what_you_will_learn') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Fees Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold  text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Fees Information
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fees Breakdown</label>
                            <textarea name="fees_breakdown" rows="5"
                                      class="w-full border border-gray-300 summernote rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Format: Fee Type: Amount
Example:
Tuition Fee: 50,000
Registration: 2,000
Examination: 1,500">{{ old('fees_breakdown') }}</textarea>
                            <p class="text-sm text-gray-500 mt-2">Enter each fee type and amount on a new line using the format: "Fee Type: Amount"</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Course Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this course</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Course</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Featured Course</h4>
                                <p class="text-sm text-gray-600">Highlight this course as featured</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="featured" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Featured</span>
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
                            Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
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
                            <h3 class="text-xl font-bold text-white">Edit Course</h3>
                            <p class="text-red-100 text-sm">Update course information</p>
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
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                                <select id="editDepartmentId" name="department_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name *</label>
                                <input type="text" id="editName" name="name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Code</label>
                                <input type="text" id="editCode" name="code"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                          <!-- In the edit modal -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Modes *</label>
    <div class="space-y-2">
        <div class="flex items-center">
            <input type="checkbox" id="editOnsite" name="delivery_modes[]" value="onsite"
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="editOnsite" class="ml-2 text-sm text-gray-700">Onsite (Physical)</label>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="editVirtual" name="delivery_modes[]" value="virtual"
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="editVirtual" class="ml-2 text-sm text-gray-700">Virtual (Online)</label>
        </div>
        <div class="flex items-center">
            <input type="checkbox" id="editHybrid" name="delivery_modes[]" value="hybrid"
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="editHybrid" class="ml-2 text-sm text-gray-700">Hybrid (Mixed)</label>
        </div>
    </div>
</div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration *</label>
                                <input type="text" id="editDuration" name="duration" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Hours</label>
                                <input type="text" id="editTotalHours" name="total_hours"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
                                <select id="editLevel" name="level" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Level</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                                <input type="text" id="editSchedule" name="schedule"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" id="editSortOrder" name="sort_order" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                    <div class="space-y-1 text-center">
                                        <img id="editImagePreview" class="mx-auto h-32 w-full object-cover rounded-lg mb-2">
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="edit_cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                <span>Change cover image</span>
                                                <input id="edit_cover_image" name="cover_image" type="file" class="sr-only" accept="image/*" onchange="previewEditImage(this)">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB. Leave empty to keep current.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Details -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Course Details
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="editDescription" name="description" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 summernote focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                                <textarea id="editRequirements" name="requirements" rows="3"
                                          class="w-full border border-gray-300 summernote rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">What You Will Learn</label>
                                <textarea id="editWhatYouWillLearn" name="what_you_will_learn" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 summernote py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Fees Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Fees Information
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fees Breakdown</label>
                            <textarea id="editFeesBreakdown" name="fees_breakdown" rows="5"
                                      class="w-full border border-gray-300 summernote rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            <p class="text-sm text-gray-500 mt-2">Enter each fee type and amount on a new line using the format: "Fee Type: Amount"</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Course Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this course</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Course</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Featured Course</h4>
                                <p class="text-sm text-gray-600">Highlight this course as featured</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editFeatured" name="featured" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Featured</span>
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
                            Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Summernote when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeSummernote();
    });

    function initializeSummernote() {
        // Initialize all summernote textareas
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onInit: function() {
                    // console.log('Summernote initialized');
                }
            }
        });
    }

    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Re-initialize summernote for create modal
        setTimeout(() => {
            $('#createModal .summernote').summernote('destroy');
            $('#createModal .summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }, 100);
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Reset create form preview
        const preview = document.getElementById('createImagePreview');
        const placeholder = document.getElementById('createImagePlaceholder');
        if (preview && placeholder) {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
        const fileInput = document.getElementById('create_cover_image');
        if (fileInput) fileInput.value = '';

        // Reset summernote content
        $('#createModal .summernote').summernote('reset');
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
            if (file.size > 10 * 1024 * 1024) {
                alert('Image size should be less than 10MB.');
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

    function previewEditImage(input) {
        const preview = document.getElementById('editImagePreview');
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
            if (file.size > 10 * 1024 * 1024) {
                alert('Image size should be less than 10MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
            }

            reader.readAsDataURL(file);
        }
    }

    // Edit Modal Functions
    function openEditModal(id, departmentId, name, code, duration, totalHours, schedule, description, requirements, feesBreakdown, deliveryMode, whatYouWillLearn, coverImage, level, featured, sortOrder, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set form action - use the route without parameters
        const editForm = document.getElementById('editForm');
        editForm.action = '{{ route("courses.update") }}';
        editForm.enctype = 'multipart/form-data';

        console.log('Update URL:', editForm.action);

        // Create or update hidden course_id field
        let idInput = document.getElementById('editCourseId');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'course_id';
            idInput.id = 'editCourseId';
            editForm.appendChild(idInput);
        }
        idInput.value = id;
 let modes = [];
    if (deliveryMode) {
        modes = deliveryMode.split(',').map(mode => mode.trim());
    }

    // Set checkboxes based on modes array
    document.getElementById('editOnsite').checked = modes.includes('onsite');
    document.getElementById('editVirtual').checked = modes.includes('virtual');
    document.getElementById('editHybrid').checked = modes.includes('hybrid');
        // Fill form fields
        document.getElementById('editDepartmentId').value = departmentId;
        document.getElementById('editName').value = name;
        document.getElementById('editCode').value = code || '';
        document.getElementById('editDuration').value = duration || '';
        document.getElementById('editTotalHours').value = totalHours || '';
        document.getElementById('editSchedule').value = schedule || '';
        document.getElementById('editLevel').value = level || 'beginner';
        document.getElementById('editSortOrder').value = sortOrder || 0;

        document.getElementById('editIsActive').checked = isActive === '1';
        document.getElementById('editFeatured').checked = featured === '1';

        // Set image preview
        const preview = document.getElementById('editImagePreview');
        if (coverImage && coverImage !== 'null' && coverImage !== '') {
            if (coverImage.startsWith('courses/cover-images/')) {
                preview.src = '/storage/' + coverImage;
            } else {
                preview.src = coverImage;
            }
        } else {
            preview.src = '/images/placeholder-course.jpg';
        }

        // Reset file input
        document.getElementById('edit_cover_image').value = '';

        // Initialize summernote
        setTimeout(() => {
            $('#editModal .summernote').summernote('destroy');

            if (description) {
                $('#editModal textarea[name="description"]').val(description);
            }
            if (requirements) {
                $('#editModal textarea[name="requirements"]').val(requirements);
            }
            if (whatYouWillLearn) {
                $('#editModal textarea[name="what_you_will_learn"]').val(whatYouWillLearn);
            }

            document.getElementById('editFeesBreakdown').value = feesBreakdown || '';

            $('#editModal .summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }, 100);
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitDeleteForm(url);
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this course?')) {
                submitDeleteForm(url);
            }
        }
    }

    function submitDeleteForm(url) {
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

    /* Summernote customization */
    .note-editor.note-frame {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }

    .note-editor.note-frame .note-toolbar {
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .note-editor.note-frame .note-statusbar {
        background-color: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    /* Ensure table cells have proper spacing */
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        padding: 0.75rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Make sure actions column doesn't wrap */
    td:last-child {
        white-space: nowrap;
    }
</style>
@endsection
