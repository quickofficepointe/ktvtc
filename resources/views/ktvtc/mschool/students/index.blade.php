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
                <h1 class="text-3xl font-bold text-gray-900">Student Management</h1>
                <p class="text-gray-600 mt-2">Manage all students and their details</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add New Student
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Students</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $students->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Students</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $students->where('is_active', true)->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Total Enrollments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $students->sum(function($student) { return $student->enrollments->count(); }) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Mobile Schools</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $mobileSchools->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Students</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Student Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Contact Info
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            School & Code
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Guardian Info
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
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Student Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start space-x-4">
                                    @if($student->profile_image)
                                        <img src="{{ Storage::url($student->profile_image) }}"
                                             alt="{{ $student->first_name }} {{ $student->last_name }}"
                                             class="w-12 h-12 rounded-full object-cover shadow-sm flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-red-400 flex items-center justify-center shadow-sm flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </h3>
                                        <p class="text-xs text-gray-600">
                                            @if($student->date_of_birth)
                                                {{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years old
                                            @else
                                                Age not specified
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 capitalize">{{ $student->gender ?? 'Not specified' }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $student->enrollments->count() }} enrollments
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Info --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    @if($student->email)
                                        <div class="text-sm text-gray-900 truncate">{{ $student->email }}</div>
                                    @endif
                                    @if($student->phone)
                                        <div class="text-xs text-gray-600">{{ $student->phone }}</div>
                                    @endif
                                    @if($student->address)
                                        <div class="text-xs text-gray-500 line-clamp-2">{{ $student->address }}</div>
                                    @endif
                                </div>
                            </td>

                            {{-- School & Code --}}
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-900 font-mono">{{ $student->student_code ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-600">{{ $student->mobileSchool->name ?? 'No school' }}</div>
                                @if($student->enrollment_date)
                                    <div class="text-xs text-gray-500">
                                        Enrolled: {{ \Carbon\Carbon::parse($student->enrollment_date)->format('M d, Y') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Guardian Info --}}
                            <td class="px-6 py-4 align-top">
                                @if($student->guardian_name)
                                    <div class="text-sm text-gray-900">{{ $student->guardian_name }}</div>
                                    @if($student->guardian_phone)
                                        <div class="text-xs text-gray-600">{{ $student->guardian_phone }}</div>
                                    @endif
                                    @if($student->guardian_email)
                                        <div class="text-xs text-gray-500 truncate">{{ $student->guardian_email }}</div>
                                    @endif
                                @else
                                    <div class="text-xs text-gray-500">No guardian info</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($student->enrollment_date)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($student->enrollment_date)->diffForHumans() }}
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $student->student_id }}',
                                        '{{ addslashes($student->first_name) }}',
                                        '{{ addslashes($student->last_name) }}',
                                        '{{ $student->email }}',
                                        '{{ $student->phone }}',
                                        '{{ $student->date_of_birth }}',
                                        '{{ $student->gender }}',
                                        `{{ addslashes($student->address) }}`,
                                        '{{ $student->mobile_school_id }}',
                                        '{{ $student->student_code }}',
                                        '{{ $student->enrollment_date }}',
                                        '{{ $student->is_active }}',
                                        '{{ $student->profile_image }}',
                                        '{{ addslashes($student->guardian_name) }}',
                                        '{{ $student->guardian_phone }}',
                                        '{{ $student->guardian_email }}',
                                        `{{ addslashes($student->guardian_address) }}`
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('students.destroy', $student->student_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500 mb-2">No students found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first student.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Student Modal -->
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
                            <h3 class="text-xl font-bold text-white">Create New Student</h3>
                            <p class="text-red-100 text-sm">Add a new student to the system</p>
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
                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Personal Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Personal Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Enter first name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Enter last name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="student@example.com">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="+254 712 345 678">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select name="gender"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Enter full address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- School Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            School Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile School</label>
                                <select name="mobile_school_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Mobile School</option>
                                    @foreach($mobileSchools as $school)
                                        <option value="{{ $school->id }}" {{ old('mobile_school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Code</label>
                                <input type="text" name="student_code" value="{{ old('student_code') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Leave empty to auto-generate">
                                <p class="text-sm text-gray-500 mt-1">Auto-generated if left empty</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Date</label>
                                <input type="date" name="enrollment_date" value="{{ old('enrollment_date') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                    <div class="space-y-1 text-center">
                                        <img id="createImagePreview" class="mx-auto h-32 w-32 object-cover rounded-full mb-2 hidden">
                                        <svg id="createImagePlaceholder" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="create_profile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                <span>Upload profile image</span>
                                                <input id="create_profile_image" name="profile_image" type="file" class="sr-only" accept="image/*" onchange="previewCreateImage(this)">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Guardian Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Name</label>
                                <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Enter guardian name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Phone</label>
                                <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="+254 712 345 678">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Email</label>
                                <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="guardian@example.com">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Address</label>
                                <textarea name="guardian_address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Enter guardian address">{{ old('guardian_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Student Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this student</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Student</span>
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
                            Create Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
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
                            <h3 class="text-xl font-bold text-white">Edit Student</h3>
                            <p class="text-red-100 text-sm">Update student information</p>
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

                    <!-- Personal Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Personal Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="editStudentId" name="student_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" id="editFirstName" name="first_name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                <input type="text" id="editLastName" name="last_name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="editEmail" name="email"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="text" id="editPhone" name="phone"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input type="date" id="editDateOfBirth" name="date_of_birth"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select id="editGender" name="gender"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea id="editAddress" name="address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- School Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            School Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile School</label>
                                <select id="editMobileSchoolId" name="mobile_school_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Mobile School</option>
                                    @foreach($mobileSchools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Code</label>
                                <input type="text" id="editStudentCode" name="student_code"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Date</label>
                                <input type="date" id="editEnrollmentDate" name="enrollment_date"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                    <div class="space-y-1 text-center">
                                        <img id="editImagePreview" class="mx-auto h-32 w-32 object-cover rounded-full mb-2">
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="edit_profile_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                <span>Change profile image</span>
                                                <input id="edit_profile_image" name="profile_image" type="file" class="sr-only" accept="image/*" onchange="previewEditImage(this)">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB. Leave empty to keep current.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Guardian Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Name</label>
                                <input type="text" id="editGuardianName" name="guardian_name"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Phone</label>
                                <input type="text" id="editGuardianPhone" name="guardian_phone"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Email</label>
                                <input type="email" id="editGuardianEmail" name="guardian_email"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guardian Address</label>
                                <textarea id="editGuardianAddress" name="guardian_address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Student Status</h4>
                                <p class="text-sm text-gray-600">Set the active status of this student</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Student</span>
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
                            Update Student
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

        // Reset create form preview
        const preview = document.getElementById('createImagePreview');
        const placeholder = document.getElementById('createImagePlaceholder');
        if (preview && placeholder) {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
        const fileInput = document.getElementById('create_profile_image');
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
    function openEditModal(id, firstName, lastName, email, phone, dateOfBirth, gender, address, mobileSchoolId, studentCode, enrollmentDate, isActive, profileImage, guardianName, guardianPhone, guardianEmail, guardianAddress) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set form action
        const editForm = document.getElementById('editForm');
        editForm.action = '{{ route("students.update", ":id") }}'.replace(':id', id);

        // Fill form fields
        document.getElementById('editStudentId').value = id;
        document.getElementById('editFirstName').value = firstName;
        document.getElementById('editLastName').value = lastName;
        document.getElementById('editEmail').value = email || '';
        document.getElementById('editPhone').value = phone || '';
        document.getElementById('editDateOfBirth').value = dateOfBirth || '';
        document.getElementById('editGender').value = gender || '';
        document.getElementById('editAddress').value = address || '';
        document.getElementById('editMobileSchoolId').value = mobileSchoolId || '';
        document.getElementById('editStudentCode').value = studentCode || '';
        document.getElementById('editEnrollmentDate').value = enrollmentDate || '';
        document.getElementById('editIsActive').checked = isActive === '1';
        document.getElementById('editGuardianName').value = guardianName || '';
        document.getElementById('editGuardianPhone').value = guardianPhone || '';
        document.getElementById('editGuardianEmail').value = guardianEmail || '';
        document.getElementById('editGuardianAddress').value = guardianAddress || '';

        // Set image preview
        const preview = document.getElementById('editImagePreview');
        if (profileImage && profileImage !== 'null' && profileImage !== '') {
            preview.src = '/storage/' + profileImage;
        } else {
            preview.src = '/images/placeholder-student.jpg';
        }

        // Reset file input
        document.getElementById('edit_profile_image').value = '';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this student?')) {
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
