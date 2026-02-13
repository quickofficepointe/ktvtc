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
                <h1 class="text-3xl font-bold text-gray-900">Exam Management</h1>
                <p class="text-gray-600 mt-2">Manage all exams, assignments, and assessments</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Exam
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Exams</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $exams->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Published</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $exams->where('is_published', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Exams</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $exams->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Main Exams</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $exams->where('exam_type', 'main_exam')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Exams Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Exams</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Exam Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Course & Subject
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Scheduling
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Grading
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
                    @forelse($exams as $exam)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Exam Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $exam->exam_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $exam->exam_code ?? 'No Code' }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst(str_replace('_', ' ', $exam->exam_type)) }}
                                        </span>
                                        @if($exam->exam_category)
                                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                {{ $exam->exam_category }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($exam->description)
                                        <p class="text-xs text-gray-500 line-clamp-2">{{ $exam->description }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Course & Subject --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div class="text-primary">
                                        <p class="text-sm font-medium">{{ $exam->course->course_name }}</p>
                                        <p class="text-xs">{{ $exam->course->course_code }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $exam->subject->subject_name }}</p>
                                        <p class="text-xs text-gray-600">{{ $exam->subject->subject_code }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Scheduling --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">{{ $exam->exam_date->format('M d, Y') }}</div>
                                    @if($exam->start_time && $exam->end_time)
                                        <div class="text-xs text-gray-600">
                                            {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                        </div>
                                    @endif
                                    @if($exam->duration_minutes)
                                        <div class="text-xs text-gray-500">
                                            Duration: {{ $exam->duration_minutes }} mins
                                        </div>
                                    @endif
                                    @if($exam->venue)
                                        <div class="text-xs text-gray-500">
                                            Venue: {{ $exam->venue }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Grading --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">
                                        Total: {{ number_format($exam->total_marks, 1) }} marks
                                    </div>
                                    @if($exam->passing_marks)
                                        <div class="text-xs text-gray-600">
                                            Pass: {{ number_format($exam->passing_marks, 1) }} marks
                                        </div>
                                    @endif
                                    @if($exam->weightage)
                                        <div class="text-xs text-gray-500">
                                            Weightage: {{ $exam->weightage }}%
                                        </div>
                                    @endif
                                    @if($exam->number_of_questions)
                                        <div class="text-xs text-gray-500">
                                            Questions: {{ $exam->number_of_questions }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $exam->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $exam->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $exam->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $exam->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($exam->allow_retake)
                                        <div class="text-xs text-green-600 font-medium">
                                            Retake Allowed
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $exam->exam_id }}',
                                        '{{ addslashes($exam->exam_name) }}',
                                        '{{ $exam->exam_code }}',
                                        `{{ addslashes($exam->description) }}`,
                                        '{{ $exam->subject_id }}',
                                        '{{ $exam->course_id }}',
                                        '{{ $exam->exam_type }}',
                                        '{{ $exam->exam_category }}',
                                        '{{ $exam->exam_date->format('Y-m-d') }}',
                                        '{{ $exam->start_time }}',
                                        '{{ $exam->end_time }}',
                                        '{{ $exam->duration_minutes }}',
                                        '{{ $exam->total_marks }}',
                                        '{{ $exam->passing_marks }}',
                                        '{{ $exam->weightage }}',
                                        '{{ $exam->number_of_questions }}',
                                        `{{ $exam->question_types ? json_encode($exam->question_types) : '[]' }}`,
                                        `{{ $exam->sections ? json_encode($exam->sections) : '[]' }}`,
                                        '{{ $exam->venue }}',
                                        `{{ addslashes($exam->instructions) }}`,
                                        `{{ addslashes($exam->materials_allowed) }}`,
                                        '{{ $exam->academic_year }}',
                                        '{{ $exam->semester }}',
                                        '{{ $exam->term }}',
                                        '{{ $exam->is_published }}',
                                        '{{ $exam->is_active }}',
                                        '{{ $exam->allow_retake }}',
                                        '{{ $exam->max_attempts }}'
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('exams.destroy', $exam->exam_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500 mb-2">No exams found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first exam.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Exam Modal -->
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
                            <h3 class="text-xl font-bold text-white">Create New Exam</h3>
                            <p class="text-red-100 text-sm">Set up a new examination or assessment</p>
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
                <form action="{{ route('exams.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Basic Exam Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Name *</label>
                                <input type="text" name="exam_name" value="{{ old('exam_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., CAT 1 - Mathematics">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Code</label>
                                <input type="text" name="exam_code" value="{{ old('exam_code') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Auto-generated if empty">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type *</label>
                                <select name="exam_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Type</option>
                                    <option value="cat1" {{ old('exam_type') == 'cat1' ? 'selected' : '' }}>CAT 1</option>
                                    <option value="cat2" {{ old('exam_type') == 'cat2' ? 'selected' : '' }}>CAT 2</option>
                                    <option value="cat3" {{ old('exam_type') == 'cat3' ? 'selected' : '' }}>CAT 3</option>
                                    <option value="main_exam" {{ old('exam_type') == 'main_exam' ? 'selected' : '' }}>Main Exam</option>
                                    <option value="assignment" {{ old('exam_type') == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                    <option value="practical" {{ old('exam_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                                    <option value="project" {{ old('exam_type') == 'project' ? 'selected' : '' }}>Project</option>
                                    <option value="quiz" {{ old('exam_type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="final" {{ old('exam_type') == 'final' ? 'selected' : '' }}>Final Exam</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Category</label>
                                <input type="text" name="exam_category" value="{{ old('exam_category') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Theory, Practical, Combined">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Brief description of the exam">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Course & Subject Selection -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Course & Subject
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select name="course_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}" {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                            {{ $course->course_name }} ({{ $course->course_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <select name="subject_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->subject_id }}" {{ old('subject_id') == $subject->subject_id ? 'selected' : '' }}>
                                            {{ $subject->subject_name }} ({{ $subject->subject_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Scheduling
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Date *</label>
                                <input type="date" name="exam_date" value="{{ old('exam_date', date('Y-m-d')) }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" name="end_time" value="{{ old('end_time') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 120">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                                <input type="text" name="venue" value="{{ old('venue') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Room 101, Main Hall">
                            </div>
                        </div>
                    </div>

                    <!-- Grading System -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Grading System
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                                <input type="number" name="total_marks" value="{{ old('total_marks') }}" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 100.00">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Passing Marks</label>
                                <input type="number" name="passing_marks" value="{{ old('passing_marks') }}" step="0.01" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 40.00">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weightage (%)</label>
                                <input type="number" name="weightage" value="{{ old('weightage', 0) }}" min="0" max="100"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 30">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Questions</label>
                                <input type="number" name="number_of_questions" value="{{ old('number_of_questions') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 50">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Question Types</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @php
                                        $questionTypes = ['multiple_choice', 'essay', 'practical', 'true_false', 'short_answer', 'matching', 'fill_blank', 'oral'];
                                    @endphp
                                    @foreach($questionTypes as $type)
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="question_types[]" value="{{ $type }}"
                                                   class="rounded border-gray-300 text-primary focus:ring-primary"
                                                   {{ in_array($type, old('question_types', [])) ? 'checked' : '' }}>
                                            <span class="text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Academic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                                <input type="text" name="academic_year" value="{{ old('academic_year', $academicYear) }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 2024/2025">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                                <input type="text" name="semester" value="{{ old('semester') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Semester 1">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                <input type="number" name="term" value="{{ old('term') }}" min="1" max="3"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 1">
                            </div>
                        </div>
                    </div>

                    <!-- Exam Settings -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Exam Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Publish Exam</span>
                                        <p class="text-sm text-gray-500">Make exam visible to students</p>
                                    </div>
                                    <input type="checkbox" name="is_published" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_published') ? 'checked' : '' }}>
                                </label>

                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Active Exam</span>
                                        <p class="text-sm text-gray-500">Enable this exam</p>
                                    </div>
                                    <input type="checkbox" name="is_active" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                </label>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Allow Retake</span>
                                        <p class="text-sm text-gray-500">Students can retake this exam</p>
                                    </div>
                                    <input type="checkbox" name="allow_retake" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('allow_retake') ? 'checked' : '' }}>
                                </label>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Attempts</label>
                                    <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions & Materials -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Instructions & Materials
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                                <textarea name="instructions" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Exam instructions for students">{{ old('instructions') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Materials Allowed</label>
                                <textarea name="materials_allowed" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="e.g., Calculator, Formula sheet, Dictionary">{{ old('materials_allowed') }}</textarea>
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
                            Create Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Exam Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Exam</h3>
                            <p class="text-red-100 text-sm">Update exam information</p>
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
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Exam Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="editExamId" name="exam_id">

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Name *</label>
                                <input type="text" id="editExamName" name="exam_name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Code</label>
                                <input type="text" id="editExamCode" name="exam_code"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type *</label>
                                <select id="editExamType" name="exam_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Type</option>
                                    <option value="cat1">CAT 1</option>
                                    <option value="cat2">CAT 2</option>
                                    <option value="cat3">CAT 3</option>
                                    <option value="main_exam">Main Exam</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="practical">Practical</option>
                                    <option value="project">Project</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="final">Final Exam</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Category</label>
                                <input type="text" id="editExamCategory" name="exam_category"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="editDescription" name="description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Course & Subject Selection -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Course & Subject
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select id="editCourseId" name="course_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}">{{ $course->course_name }} ({{ $course->course_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <select id="editSubjectId" name="subject_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->subject_id }}">{{ $subject->subject_name }} ({{ $subject->subject_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Scheduling
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Date *</label>
                                <input type="date" id="editExamDate" name="exam_date" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" id="editStartTime" name="start_time"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" id="editEndTime" name="end_time"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                                <input type="number" id="editDurationMinutes" name="duration_minutes" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                                <input type="text" id="editVenue" name="venue"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Grading System -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Grading System
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                                <input type="number" id="editTotalMarks" name="total_marks" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Passing Marks</label>
                                <input type="number" id="editPassingMarks" name="passing_marks" step="0.01" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weightage (%)</label>
                                <input type="number" id="editWeightage" name="weightage" min="0" max="100"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Questions</label>
                                <input type="number" id="editNumberOfQuestions" name="number_of_questions" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Question Types</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2" id="editQuestionTypesContainer">
                                    @php
                                        $questionTypes = ['multiple_choice', 'essay', 'practical', 'true_false', 'short_answer', 'matching', 'fill_blank', 'oral'];
                                    @endphp
                                    @foreach($questionTypes as $type)
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="question_types[]" value="{{ $type }}"
                                                   class="rounded border-gray-300 text-primary focus:ring-primary question-type-checkbox">
                                            <span class="text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Academic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                                <input type="text" id="editAcademicYear" name="academic_year" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                                <input type="text" id="editSemester" name="semester"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                                <input type="number" id="editTerm" name="term" min="1" max="3"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Exam Settings -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Exam Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Publish Exam</span>
                                        <p class="text-sm text-gray-500">Make exam visible to students</p>
                                    </div>
                                    <input type="checkbox" id="editIsPublished" name="is_published" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                </label>

                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Active Exam</span>
                                        <p class="text-sm text-gray-500">Enable this exam</p>
                                    </div>
                                    <input type="checkbox" id="editIsActive" name="is_active" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                </label>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Allow Retake</span>
                                        <p class="text-sm text-gray-500">Students can retake this exam</p>
                                    </div>
                                    <input type="checkbox" id="editAllowRetake" name="allow_retake" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                </label>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Attempts</label>
                                    <input type="number" id="editMaxAttempts" name="max_attempts" min="1"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions & Materials -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Instructions & Materials
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                                <textarea id="editInstructions" name="instructions" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Materials Allowed</label>
                                <textarea id="editMaterialsAllowed" name="materials_allowed" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
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
                            Update Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDeleteModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Delete Exam</h3>
                        <p class="text-gray-600 text-sm">This action cannot be undone.</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">Are you sure you want to delete this exam? All associated data will be permanently removed.</p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                            Delete Exam
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Functions
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openEditModal(
    examId, examName, examCode, description, subjectId, courseId, examType,
    examCategory, examDate, startTime, endTime, durationMinutes, totalMarks,
    passingMarks, weightage, numberOfQuestions, questionTypes, sections,
    venue, instructions, materialsAllowed, academicYear, semester, term,
    isPublished, isActive, allowRetake, maxAttempts
) {
    // Set form action
    document.getElementById('editForm').action = `/exams/${examId}`;

    // Populate form fields
    document.getElementById('editExamId').value = examId;
    document.getElementById('editExamName').value = examName || '';
    document.getElementById('editExamCode').value = examCode || '';
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editSubjectId').value = subjectId;
    document.getElementById('editCourseId').value = courseId;
    document.getElementById('editExamType').value = examType;
    document.getElementById('editExamCategory').value = examCategory || '';
    document.getElementById('editExamDate').value = examDate;
    document.getElementById('editStartTime').value = startTime || '';
    document.getElementById('editEndTime').value = endTime || '';
    document.getElementById('editDurationMinutes').value = durationMinutes || '';
    document.getElementById('editTotalMarks').value = totalMarks || '';
    document.getElementById('editPassingMarks').value = passingMarks || '';
    document.getElementById('editWeightage').value = weightage || '';
    document.getElementById('editNumberOfQuestions').value = numberOfQuestions || '';
    document.getElementById('editVenue').value = venue || '';
    document.getElementById('editInstructions').value = instructions || '';
    document.getElementById('editMaterialsAllowed').value = materialsAllowed || '';
    document.getElementById('editAcademicYear').value = academicYear || '';
    document.getElementById('editSemester').value = semester || '';
    document.getElementById('editTerm').value = term || '';
    document.getElementById('editIsPublished').checked = isPublished === '1';
    document.getElementById('editIsActive').checked = isActive === '1';
    document.getElementById('editAllowRetake').checked = allowRetake === '1';
    document.getElementById('editMaxAttempts').value = maxAttempts || '1';

    // Handle question types
    const questionTypesArray = JSON.parse(questionTypes || '[]');
    const checkboxes = document.querySelectorAll('.question-type-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = questionTypesArray.includes(checkbox.value);
    });

    document.getElementById('editModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function confirmDelete(deleteUrl) {
    document.getElementById('deleteForm').action = deleteUrl;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
        closeDeleteModal();
    }
});
</script>
@endsection
