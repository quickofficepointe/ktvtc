@extends('ktvtc.mschool.layout.mschoollayout')

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
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Subject Management</h1>
                <p class="text-gray-600 mt-2">Manage all subjects and their details</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add New Subject
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
                    <p class="text-sm font-medium text-gray-600">Total Subjects</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $subjects->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Subjects</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $subjects->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Core Subjects</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $subjects->where('is_core', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $courses->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Subjects Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Subjects</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Subject Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Code & Duration
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Course & Prerequisite
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Assessment
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
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Subject Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start space-x-4">
                                    @if($subject->cover_image)
                                        <img src="{{ Storage::url($subject->cover_image) }}"
                                             alt="{{ $subject->subject_name }}"
                                             class="w-12 h-12 rounded-lg object-cover shadow-sm flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $subject->subject_name }}</h3>
                                        <p class="text-xs text-gray-600 line-clamp-2">{{ $subject->description ?? 'No description' }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $subject->is_core ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $subject->is_core ? 'Core' : 'Elective' }}
                                            </span>
                                            @if($subject->price)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    KSh {{ number_format($subject->price, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Code & Duration --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-900 font-mono">{{ $subject->subject_code ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-600">{{ $subject->credit_hours }} credit hours</div>
                                @if($subject->duration_weeks)
                                    <div class="text-xs text-gray-500">{{ $subject->duration_weeks }} weeks</div>
                                @endif
                            </td>

                            {{-- Course & Prerequisite --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-900">{{ $subject->course->course_name ?? 'No course' }}</div>
                                @if($subject->prerequisite)
                                    <div class="text-xs text-gray-600">Prereq: {{ $subject->prerequisite->subject_name }}</div>
                                @else
                                    <div class="text-xs text-gray-500">No prerequisite</div>
                                @endif
                            </td>

                            {{-- Assessment --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Exam:</span>
                                        <span class="font-medium">{{ $subject->exam_weight }}%</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Assignment:</span>
                                        <span class="font-medium">{{ $subject->assignment_weight }}%</span>
                                    </div>
                                    @if($subject->syllabus_file)
                                        <div class="mt-1">
                                            <a href="{{ Storage::url($subject->syllabus_file) }}" target="_blank" class="text-xs text-primary hover:text-red-700 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Syllabus
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $subject->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">Order: {{ $subject->sort_order }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $subject->subject_id }}',
                                        '{{ addslashes($subject->subject_name) }}',
                                        '{{ $subject->subject_code }}',
                                        `{{ addslashes($subject->description) }}`,
                                        '{{ $subject->course_id }}',
                                        '{{ $subject->credit_hours }}',
                                        '{{ $subject->duration_weeks }}',
                                        '{{ $subject->price }}',
                                        '{{ $subject->sort_order }}',
                                        '{{ $subject->is_active }}',
                                        '{{ $subject->is_core }}',
                                        '{{ $subject->prerequisite_subject_id }}',
                                        '{{ $subject->exam_weight }}',
                                        '{{ $subject->assignment_weight }}',
                                        '{{ $subject->cover_image }}',
                                        '{{ $subject->syllabus_file }}'
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('subjects.destroy', $subject->subject_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500 mb-2">No subjects found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first subject.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Subject Modal -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create New Subject</h3>
                            <p class="text-red-100 text-sm">Add a new subject to the system</p>
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
                <form action="{{ route('subjects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject Name *</label>
                                <input type="text" name="subject_name" value="{{ old('subject_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Enter subject name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject Code</label>
                                <input type="text" name="subject_code" value="{{ old('subject_code') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., MATH101">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                                <select name="course_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}" {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                            {{ $course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisite Subject</label>
                                <select name="prerequisite_subject_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">No Prerequisite</option>
                                    @foreach($prerequisites as $prerequisite)
                                        <option value="{{ $prerequisite->subject_id }}" {{ old('prerequisite_subject_id') == $prerequisite->subject_id ? 'selected' : '' }}>
                                            {{ $prerequisite->subject_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Hours</label>
                                <input type="number" name="credit_hours" value="{{ old('credit_hours', 0) }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Weeks)</label>
                                <input type="number" name="duration_weeks" value="{{ old('duration_weeks') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 12">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (KSh)</label>
                                <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 5000.00">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Weights -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Assessment Weights
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Weight (%) *</label>
                                <input type="number" name="exam_weight" value="{{ old('exam_weight', 70) }}" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       onchange="updateWeights()">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Weight (%) *</label>
                                <input type="number" name="assignment_weight" value="{{ old('assignment_weight', 30) }}" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       onchange="updateWeights()">
                            </div>

                            <div class="md:col-span-2">
                                <div id="weightWarning" class="hidden p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <span class="text-yellow-800 text-sm">Exam and assignment weights must sum to 100%</span>
                                    </div>
                                </div>
                                <div id="weightSuccess" class="hidden p-3 rounded-lg bg-green-50 border border-green-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-green-800 text-sm">Weights sum to 100% - Perfect!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description & Files -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Description & Files
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Describe the subject content and objectives">{{ old('description') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                            <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Syllabus File</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="create_syllabus_file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Upload syllabus</span>
                                                    <input id="create_syllabus_file" name="syllabus_file" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 20MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Subject Status</h4>
                                    <p class="text-sm text-gray-600">Set the active status of this subject</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Active Subject</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Subject Type</h4>
                                    <p class="text-sm text-gray-600">Set as core or elective subject</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_core" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Core Subject</span>
                                </label>
                            </div>
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
                            Create Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Subject</h3>
                            <p class="text-red-100 text-sm">Update subject information</p>
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
                            <input type="hidden" id="editSubjectId" name="subject_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject Name *</label>
                                <input type="text" id="editSubjectName" name="subject_name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject Code</label>
                                <input type="text" id="editSubjectCode" name="subject_code"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                                <select id="editCourseId" name="course_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}">{{ $course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisite Subject</label>
                                <select id="editPrerequisiteSubjectId" name="prerequisite_subject_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">No Prerequisite</option>
                                    @foreach($prerequisites as $prerequisite)
                                        <option value="{{ $prerequisite->subject_id }}">{{ $prerequisite->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Hours</label>
                                <input type="number" id="editCreditHours" name="credit_hours" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Weeks)</label>
                                <input type="number" id="editDurationWeeks" name="duration_weeks" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (KSh)</label>
                                <input type="number" id="editPrice" name="price" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" id="editSortOrder" name="sort_order" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Weights -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Assessment Weights
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Weight (%) *</label>
                                <input type="number" id="editExamWeight" name="exam_weight" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       onchange="updateEditWeights()">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Weight (%) *</label>
                                <input type="number" id="editAssignmentWeight" name="assignment_weight" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       onchange="updateEditWeights()">
                            </div>

                            <div class="md:col-span-2">
                                <div id="editWeightWarning" class="hidden p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <span class="text-yellow-800 text-sm">Exam and assignment weights must sum to 100%</span>
                                    </div>
                                </div>
                                <div id="editWeightSuccess" class="hidden p-3 rounded-lg bg-green-50 border border-green-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-green-800 text-sm">Weights sum to 100% - Perfect!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description & Files -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Description & Files
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="editDescription" name="description" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
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

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Syllabus File</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                        <div class="space-y-1 text-center">
                                            <div id="editSyllabusInfo" class="text-xs text-gray-500 mb-2"></div>
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="edit_syllabus_file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Change syllabus</span>
                                                    <input id="edit_syllabus_file" name="syllabus_file" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 20MB. Leave empty to keep current.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Subject Status</h4>
                                    <p class="text-sm text-gray-600">Set the active status of this subject</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Active Subject</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Subject Type</h4>
                                    <p class="text-sm text-gray-600">Set as core or elective subject</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="editIsCore" name="is_core" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Core Subject</span>
                                </label>
                            </div>
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
                            Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Weight validation functions
    function updateWeights() {
        const examWeight = parseInt(document.querySelector('input[name="exam_weight"]').value) || 0;
        const assignmentWeight = parseInt(document.querySelector('input[name="assignment_weight"]').value) || 0;
        const total = examWeight + assignmentWeight;

        const warning = document.getElementById('weightWarning');
        const success = document.getElementById('weightSuccess');

        if (total !== 100) {
            warning.classList.remove('hidden');
            success.classList.add('hidden');
        } else {
            warning.classList.add('hidden');
            success.classList.remove('hidden');
        }
    }

    function updateEditWeights() {
        const examWeight = parseInt(document.getElementById('editExamWeight').value) || 0;
        const assignmentWeight = parseInt(document.getElementById('editAssignmentWeight').value) || 0;
        const total = examWeight + assignmentWeight;

        const warning = document.getElementById('editWeightWarning');
        const success = document.getElementById('editWeightSuccess');

        if (total !== 100) {
            warning.classList.remove('hidden');
            success.classList.add('hidden');
        } else {
            warning.classList.add('hidden');
            success.classList.remove('hidden');
        }
    }

    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateWeights(); // Initialize weight validation
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
    }

    // Image Preview Functions
    function previewCreateImage(input) {
        const preview = document.getElementById('createImagePreview');
        const placeholder = document.getElementById('createImagePlaceholder');
        const file = input.files[0];

        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, JPG, PNG, GIF).');
                input.value = '';
                return;
            }

            // Validate file size (10MB)
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
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, JPG, PNG, GIF).');
                input.value = '';
                return;
            }

            // Validate file size (10MB)
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
    function openEditModal(id, subjectName, subjectCode, description, courseId, creditHours, durationWeeks, price, sortOrder, isActive, isCore, prerequisiteSubjectId, examWeight, assignmentWeight, coverImage, syllabusFile) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set form action
        const editForm = document.getElementById('editForm');
       // Fixed line - use the correct route name 'course-subjects.update'
// Fixed line - properly pass the ID parameter
editForm.action = '{{ route("course-subjects.update", ["id" => ":id"]) }}'.replace(':id', id);

        // Fill form fields
        document.getElementById('editSubjectId').value = id;
        document.getElementById('editSubjectName').value = subjectName;
        document.getElementById('editSubjectCode').value = subjectCode || '';
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editCourseId').value = courseId || '';
        document.getElementById('editCreditHours').value = creditHours || '';
        document.getElementById('editDurationWeeks').value = durationWeeks || '';
        document.getElementById('editPrice').value = price || '';
        document.getElementById('editSortOrder').value = sortOrder || 0;
        document.getElementById('editPrerequisiteSubjectId').value = prerequisiteSubjectId || '';
        document.getElementById('editExamWeight').value = examWeight || 70;
        document.getElementById('editAssignmentWeight').value = assignmentWeight || 30;
        document.getElementById('editIsActive').checked = isActive === '1';
        document.getElementById('editIsCore').checked = isCore === '1';

        // Set image preview
        const preview = document.getElementById('editImagePreview');
        if (coverImage && coverImage !== 'null' && coverImage !== '') {
            preview.src = '/storage/' + coverImage;
        } else {
            preview.src = '/images/placeholder-subject.jpg';
        }

        // Set syllabus file info
        const syllabusInfo = document.getElementById('editSyllabusInfo');
        if (syllabusFile && syllabusFile !== 'null' && syllabusFile !== '') {
            syllabusInfo.innerHTML = `<a href="/storage/${syllabusFile}" target="_blank" class="text-primary hover:text-red-700">Current syllabus file</a>`;
        } else {
            syllabusInfo.innerHTML = 'No syllabus file uploaded';
        }

        // Reset file inputs
        document.getElementById('edit_cover_image').value = '';
        document.getElementById('edit_syllabus_file').value = '';

        // Initialize weight validation
        updateEditWeights();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this subject?')) {
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

    // Initialize weight validation on page load for create modal
    document.addEventListener('DOMContentLoaded', function() {
        updateWeights();
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
