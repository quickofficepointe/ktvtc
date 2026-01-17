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
                <h1 class="text-3xl font-bold text-gray-900">Exam Results Management</h1>
                <p class="text-gray-600 mt-2">Manage all student exam results and grading</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Result
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
                    <p class="text-sm font-medium text-gray-600">Total Results</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Graded</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results->where('status', 'graded')->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results->where('status', 'pending')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Absent</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results->where('is_absent', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Exam Results</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Student & Exam
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Performance
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Grading
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Attempt Details
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
                    @forelse($results as $result)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Student & Exam --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $result->student->full_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $result->student->student_code }}</p>
                                    </div>
                                    <div class="text-primary">
                                        <p class="text-sm font-medium">{{ $result->exam->exam_name }}</p>
                                        <p class="text-xs">{{ $result->exam->exam_type }} • {{ $result->exam->course->course_code }}</p>
                                    </div>
                                    @if($result->exam->subject)
                                        <p class="text-xs text-gray-500">{{ $result->exam->subject->subject_name }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Performance --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">
                                        {{ number_format($result->marks_obtained, 1) }}/{{ number_format($result->total_marks, 1) }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        {{ number_format($result->percentage, 1) }}%
                                    </div>
                                    @if($result->class_rank && $result->total_students)
                                        <div class="text-xs text-gray-500">
                                            Rank: {{ $result->class_rank }}/{{ $result->total_students }}
                                        </div>
                                    @endif
                                    @if($result->class_average)
                                        <div class="text-xs text-gray-500">
                                            Avg: {{ number_format($result->class_average, 1) }}%
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Grading --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    @if($result->grade)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $result->grade === 'A' ? 'bg-green-100 text-green-800' : ($result->grade === 'B' ? 'bg-blue-100 text-blue-800' : ($result->grade === 'C' ? 'bg-yellow-100 text-yellow-800' : ($result->grade === 'D' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                                            Grade: {{ $result->grade }}
                                        </span>
                                    @endif
                                    @if($result->grade_point)
                                        <div class="text-xs text-gray-600">
                                            GPA: {{ $result->grade_point }}
                                        </div>
                                    @endif
                                    @if($result->graded_at)
                                        <div class="text-xs text-gray-500">
                                            Graded: {{ $result->graded_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Attempt Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">
                                        Attempt #{{ $result->attempt_number }}
                                    </div>
                                    @if($result->attempt_date)
                                        <div class="text-xs text-gray-600">
                                            {{ $result->attempt_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                    @if($result->time_taken_minutes)
                                        <div class="text-xs text-gray-500">
                                            Time: {{ $result->time_taken_minutes }} mins
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($result->is_retake)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">Retake</span>
                                        @endif
                                        @if($result->is_supplementary)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-purple-100 text-purple-800">Supplementary</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $result->status === 'graded' ? 'bg-green-100 text-green-800' : ($result->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($result->status === 'absent' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($result->status) }}
                                    </span>
                                    @if($result->is_absent)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            Absent
                                        </span>
                                    @endif
                                    @if($result->remarks)
                                        <div class="text-xs text-gray-500 line-clamp-2">
                                            {{ $result->remarks }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $result->result_id }}',
                                        '{{ $result->exam_id }}',
                                        '{{ $result->student_id }}',
                                        '{{ $result->enrollment_id }}',
                                        '{{ $result->marks_obtained }}',
                                        '{{ $result->total_marks }}',
                                        '{{ $result->percentage }}',
                                        '{{ $result->grade }}',
                                        '{{ $result->grade_point }}',
                                        `{{ addslashes($result->remarks) }}`,
                                        '{{ $result->status }}',
                                        '{{ $result->attempt_number }}',
                                        '{{ $result->attempt_date ? $result->attempt_date->format('Y-m-d') : '' }}',
                                        '{{ $result->time_taken_minutes }}',
                                        `{{ $result->section_marks ? json_encode($result->section_marks) : '[]' }}`,
                                        `{{ $result->question_wise_marks ? json_encode($result->question_wise_marks) : '[]' }}`,
                                        `{{ addslashes($result->grading_notes) }}`,
                                        '{{ $result->is_absent }}',
                                        '{{ $result->is_retake }}',
                                        '{{ $result->is_supplementary }}',
                                        `{{ addslashes($result->absent_reason) }}`,
                                        '{{ $result->class_rank }}',
                                        '{{ $result->total_students }}',
                                        '{{ $result->class_average }}'
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('exam-results.destroy', $result->result_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
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
                                    <p class="text-lg font-medium text-gray-500 mb-2">No exam results found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first exam result.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Result Modal -->
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
                            <h3 class="text-xl font-bold text-white">Create New Exam Result</h3>
                            <p class="text-red-100 text-sm">Record student exam performance</p>
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
                <form action="{{ route('exam-results.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Student & Exam Selection -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Student & Exam
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam *</label>
                                <select name="exam_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Exam</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->exam_id }}" {{ old('exam_id') == $exam->exam_id ? 'selected' : '' }}>
                                            {{ $exam->exam_name }} ({{ $exam->exam_type }} - {{ $exam->course->course_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                <select name="student_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                                            {{ $student->full_name }} ({{ $student->student_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment *</label>
                                <select name="enrollment_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Enrollment</option>
                                    @foreach($enrollments as $enrollment)
                                        <option value="{{ $enrollment->enrollment_id }}" {{ old('enrollment_id') == $enrollment->enrollment_id ? 'selected' : '' }}>
                                            {{ $enrollment->student->full_name }} - {{ $enrollment->course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attempt Number *</label>
                                <input type="number" name="attempt_number" value="{{ old('attempt_number', 1) }}" min="1" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Marks & Performance -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Marks & Performance
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marks Obtained *</label>
                                <input type="number" name="marks_obtained" value="{{ old('marks_obtained') }}" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 75.50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                                <input type="number" name="total_marks" value="{{ old('total_marks') }}" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 100.00">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Percentage *</label>
                                <input type="number" name="percentage" value="{{ old('percentage') }}" step="0.01" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 75.50">
                            </div>
                        </div>
                    </div>

                    <!-- Grading Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            Grading Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                                <select name="grade"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Grade</option>
                                    <option value="A" {{ old('grade') == 'A' ? 'selected' : '' }}>A (Excellent)</option>
                                    <option value="B" {{ old('grade') == 'B' ? 'selected' : '' }}>B (Very Good)</option>
                                    <option value="C" {{ old('grade') == 'C' ? 'selected' : '' }}>C (Good)</option>
                                    <option value="D" {{ old('grade') == 'D' ? 'selected' : '' }}>D (Satisfactory)</option>
                                    <option value="F" {{ old('grade') == 'F' ? 'selected' : '' }}>F (Fail)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade Point</label>
                                <input type="text" name="grade_point" value="{{ old('grade_point') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 4.0, 3.5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="graded" {{ old('status') == 'graded' ? 'selected' : '' }}>Graded</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="cheated" {{ old('status') == 'cheated' ? 'selected' : '' }}>Cheated</option>
                                    <option value="special_case" {{ old('status') == 'special_case' ? 'selected' : '' }}>Special Case</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Attempt Details -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Attempt Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attempt Date</label>
                                <input type="date" name="attempt_date" value="{{ old('attempt_date') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time Taken (minutes)</label>
                                <input type="number" name="time_taken_minutes" value="{{ old('time_taken_minutes') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 120">
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_retake" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_retake') ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Retake Exam</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_supplementary" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_supplementary') ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Supplementary Exam</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Class Statistics -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Class Statistics
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Rank</label>
                                <input type="number" name="class_rank" value="{{ old('class_rank') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Students</label>
                                <input type="number" name="total_students" value="{{ old('total_students') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Average (%)</label>
                                <input type="number" name="class_average" value="{{ old('class_average') }}" step="0.01" min="0" max="100"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 65.50">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Additional Information
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea name="remarks" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Any additional remarks">{{ old('remarks') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grading Notes</label>
                                <textarea name="grading_notes" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Notes from the grader">{{ old('grading_notes') }}</textarea>
                            </div>

                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_absent" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_absent') ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Student was absent</span>
                                </label>
                            </div>

                            <div id="absentReasonSection" class="{{ old('is_absent') ? 'block' : 'hidden' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Absent Reason</label>
                                <textarea name="absent_reason" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Reason for absence">{{ old('absent_reason') }}</textarea>
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
                            Create Result
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Result Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Exam Result</h3>
                            <p class="text-red-100 text-sm">Update student exam performance</p>
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

                    <!-- Student & Exam Selection -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Student & Exam
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="editResultId" name="result_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam *</label>
                                <select id="editExamId" name="exam_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Exam</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->exam_id }}">{{ $exam->exam_name }} ({{ $exam->exam_type }} - {{ $exam->course->course_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                <select id="editStudentId" name="student_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}">{{ $student->full_name }} ({{ $student->student_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment *</label>
                                <select id="editEnrollmentId" name="enrollment_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Enrollment</option>
                                    @foreach($enrollments as $enrollment)
                                        <option value="{{ $enrollment->enrollment_id }}">{{ $enrollment->student->full_name }} - {{ $enrollment->course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attempt Number *</label>
                                <input type="number" id="editAttemptNumber" name="attempt_number" min="1" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Marks & Performance -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Marks & Performance
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marks Obtained *</label>
                                <input type="number" id="editMarksObtained" name="marks_obtained" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                                <input type="number" id="editTotalMarks" name="total_marks" step="0.01" min="0" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Percentage *</label>
                                <input type="number" id="editPercentage" name="percentage" step="0.01" min="0" max="100" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Grading Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            Grading Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                                <select id="editGrade" name="grade"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Grade</option>
                                    <option value="A">A (Excellent)</option>
                                    <option value="B">B (Very Good)</option>
                                    <option value="C">C (Good)</option>
                                    <option value="D">D (Satisfactory)</option>
                                    <option value="F">F (Fail)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade Point</label>
                                <input type="text" id="editGradePoint" name="grade_point"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select id="editStatus" name="status" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="pending">Pending</option>
                                    <option value="graded">Graded</option>
                                    <option value="absent">Absent</option>
                                    <option value="cheated">Cheated</option>
                                    <option value="special_case">Special Case</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Attempt Details -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Attempt Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attempt Date</label>
                                <input type="date" id="editAttemptDate" name="attempt_date"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time Taken (minutes)</label>
                                <input type="number" id="editTimeTakenMinutes" name="time_taken_minutes" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editIsRetake" name="is_retake" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Retake Exam</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editIsSupplementary" name="is_supplementary" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Supplementary Exam</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Class Statistics -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Class Statistics
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Rank</label>
                                <input type="number" id="editClassRank" name="class_rank" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Students</label>
                                <input type="number" id="editTotalStudents" name="total_students" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Average (%)</label>
                                <input type="number" id="editClassAverage" name="class_average" step="0.01" min="0" max="100"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Additional Information
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                                <textarea id="editRemarks" name="remarks" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grading Notes</label>
                                <textarea id="editGradingNotes" name="grading_notes" rows="2"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editIsAbsent" name="is_absent" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Student was absent</span>
                                </label>
                            </div>

                            <div id="editAbsentReasonSection" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Absent Reason</label>
                                <textarea id="editAbsentReason" name="absent_reason" rows="2"
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
                            Update Result
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
                        <h3 class="text-lg font-semibold text-gray-900">Delete Exam Result</h3>
                        <p class="text-gray-600 text-sm">This action cannot be undone.</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">Are you sure you want to delete this exam result? All associated data will be permanently removed.</p>

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
                            Delete Result
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
    resultId, examId, studentId, enrollmentId, marksObtained, totalMarks, percentage,
    grade, gradePoint, remarks, status, attemptNumber, attemptDate, timeTakenMinutes,
    sectionMarks, questionWiseMarks, gradingNotes, isAbsent, isRetake, isSupplementary,
    absentReason, classRank, totalStudents, classAverage
) {
    // Set form action
    document.getElementById('editForm').action = `/exam-results/${resultId}`;

    // Populate form fields
    document.getElementById('editResultId').value = resultId;
    document.getElementById('editExamId').value = examId;
    document.getElementById('editStudentId').value = studentId;
    document.getElementById('editEnrollmentId').value = enrollmentId;
    document.getElementById('editMarksObtained').value = marksObtained || '';
    document.getElementById('editTotalMarks').value = totalMarks || '';
    document.getElementById('editPercentage').value = percentage || '';
    document.getElementById('editGrade').value = grade || '';
    document.getElementById('editGradePoint').value = gradePoint || '';
    document.getElementById('editRemarks').value = remarks || '';
    document.getElementById('editStatus').value = status;
    document.getElementById('editAttemptNumber').value = attemptNumber;
    document.getElementById('editAttemptDate').value = attemptDate || '';
    document.getElementById('editTimeTakenMinutes').value = timeTakenMinutes || '';
    document.getElementById('editGradingNotes').value = gradingNotes || '';
    document.getElementById('editIsAbsent').checked = isAbsent === '1';
    document.getElementById('editIsRetake').checked = isRetake === '1';
    document.getElementById('editIsSupplementary').checked = isSupplementary === '1';
    document.getElementById('editAbsentReason').value = absentReason || '';
    document.getElementById('editClassRank').value = classRank || '';
    document.getElementById('editTotalStudents').value = totalStudents || '';
    document.getElementById('editClassAverage').value = classAverage || '';

    // Toggle absent reason section
    toggleAbsentReasonSection();

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

// Toggle absent reason section
function toggleAbsentReasonSection() {
    const isAbsent = document.getElementById('editIsAbsent').checked;
    document.getElementById('editAbsentReasonSection').classList.toggle('hidden', !isAbsent);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Create modal absent toggle
    const createAbsentCheckbox = document.querySelector('input[name="is_absent"]');
    const createAbsentSection = document.getElementById('absentReasonSection');

    if (createAbsentCheckbox) {
        createAbsentCheckbox.addEventListener('change', function() {
            createAbsentSection.classList.toggle('hidden', !this.checked);
        });
    }

    // Edit modal absent toggle
    const editAbsentCheckbox = document.getElementById('editIsAbsent');
    if (editAbsentCheckbox) {
        editAbsentCheckbox.addEventListener('change', toggleAbsentReasonSection);
    }
});

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
