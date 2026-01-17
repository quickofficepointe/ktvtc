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
                <h1 class="text-3xl font-bold text-gray-900">Certificate Templates</h1>
                <p class="text-gray-600 mt-2">Manage certificate templates and configurations</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Template
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Templates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Templates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Course Completion</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->where('template_type', 'course_completion')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Auto-Generate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->where('auto_generate', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Templates Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Certificate Templates</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Template Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Associated Entities
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Dynamic Fields
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Security & Features
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
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Template Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $template->template_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $template->template_code }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst(str_replace('_', ' ', $template->template_type)) }}
                                        </span>
                                    </div>
                                    @if($template->description)
                                        <p class="text-xs text-gray-500 line-clamp-2">{{ $template->description }}</p>
                                    @endif
                                    @if($template->template_file)
                                        <div class="text-xs text-primary">
                                            <a href="{{ Storage::url('certificates/' . $template->template_file) }}" target="_blank" class="flex items-center hover:underline">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                View Template
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Associated Entities --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    @if($template->course)
                                        <div class="text-primary">
                                            <p class="text-sm font-medium">{{ $template->course->course_name }}</p>
                                            <p class="text-xs">{{ $template->course->course_code }}</p>
                                        </div>
                                    @endif
                                    @if($template->mobileSchool)
                                        <div>
                                            <p class="text-sm text-gray-900">{{ $template->mobileSchool->name }}</p>
                                            <p class="text-xs text-gray-600">Mobile School</p>
                                        </div>
                                    @endif
                                    @if(!$template->course && !$template->mobileSchool)
                                        <span class="text-xs text-gray-500">Global Template</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Dynamic Fields --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    @if($template->dynamic_fields)
                                        @php
                                            $fields = json_decode($template->dynamic_fields, true);
                                            $fieldCount = count($fields);
                                        @endphp
                                        <div class="text-sm text-gray-900">{{ $fieldCount }} field(s)</div>
                                        <div class="text-xs text-gray-500 space-y-1">
                                            @foreach(array_slice($fields, 0, 3) as $field)
                                                <div class="flex items-center justify-between">
                                                    <span>{{ $field['field_name'] }}</span>
                                                    <span class="text-gray-400">{{ $field['font_size'] }}px</span>
                                                </div>
                                            @endforeach
                                            @if($fieldCount > 3)
                                                <div class="text-primary">+{{ $fieldCount - 3 }} more</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">No fields configured</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Security & Features --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    @if($template->watermark_text)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                            Watermark
                                        </span>
                                    @endif
                                    @if($template->has_qr_code)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            QR Code
                                        </span>
                                    @endif
                                    @if($template->background_image)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                            Background
                                        </span>
                                    @endif
                                    @if($template->requires_approval)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                            Requires Approval
                                        </span>
                                    @endif
                                    @if($template->validity_months)
                                        <div class="text-xs text-gray-500">
                                            Valid: {{ $template->validity_months }} months
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($template->auto_generate)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            Auto-Generate
                                        </span>
                                    @endif
                                    @if($template->certificates_count > 0)
                                        <div class="text-xs text-gray-500">
                                            {{ $template->certificates_count }} certificates
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(
                                        '{{ $template->template_id }}',
                                        '{{ addslashes($template->template_name) }}',
                                        '{{ $template->template_code }}',
                                        `{{ addslashes($template->description) }}`,
                                        '{{ $template->template_type }}',
                                        '{{ $template->course_id }}',
                                        '{{ $template->mobile_school_id }}',
                                        `{{ $template->dynamic_fields }}`,
                                        `{{ $template->layout_config }}`,
                                        `{{ $template->styling }}`,
                                        '{{ $template->watermark_text }}',
                                        '{{ $template->has_qr_code }}',
                                        '{{ $template->qr_code_position }}',
                                        `{{ addslashes($template->signature_line1) }}`,
                                        `{{ addslashes($template->signature_line2) }}`,
                                        '{{ $template->validity_months }}',
                                        '{{ $template->is_active }}',
                                        '{{ $template->auto_generate }}',
                                        '{{ $template->requires_approval }}'
                                    )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                    <button onclick="confirmDelete('{{ route('certificate-templates.destroy', $template->template_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
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
                                    <p class="text-lg font-medium text-gray-500 mb-2">No certificate templates found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first template.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all">
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
                            <h3 class="text-xl font-bold text-white">Create Certificate Template</h3>
                            <p class="text-red-100 text-sm">Design and configure certificate templates</p>
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
                <form action="{{ route('certificate-templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                                <input type="text" name="template_name" value="{{ old('template_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Course Completion Certificate">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Code *</label>
                                <input type="text" name="template_code" value="{{ old('template_code') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., CERT_COURSE_COMPLETION">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Type *</label>
                                <select name="template_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Type</option>
                                    <option value="course_completion" {{ old('template_type') == 'course_completion' ? 'selected' : '' }}>Course Completion</option>
                                    <option value="achievement" {{ old('template_type') == 'achievement' ? 'selected' : '' }}>Achievement</option>
                                    <option value="participation" {{ old('template_type') == 'participation' ? 'selected' : '' }}>Participation</option>
                                    <option value="excellence" {{ old('template_type') == 'excellence' ? 'selected' : '' }}>Excellence</option>
                                    <option value="custom" {{ old('template_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template File (PDF) *</label>
                                <input type="file" name="template_file" accept=".pdf" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Upload PDF template file (max: 10MB)</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Template description and usage notes">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Entities -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Associated Entities
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                                <select name="course_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course (Optional)</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}" {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                            {{ $course->course_name }} ({{ $course->course_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile School</label>
                                <select name="mobile_school_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Mobile School (Optional)</option>
                                    @foreach($mobileSchools as $school)
                                        <option value="{{ $school->id }}" {{ old('mobile_school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fields Configuration -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                </svg>
                                Dynamic Fields Configuration
                            </div>
                            <button type="button" onclick="addDynamicField()" class="text-sm bg-primary text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors">
                                Add Field
                            </button>
                        </h4>
                        <div id="dynamicFieldsContainer" class="space-y-4">
                            <!-- Dynamic fields will be added here -->
                            <div class="text-center text-gray-500 py-4" id="noFieldsMessage">
                                No fields added yet. Click "Add Field" to start.
                            </div>
                        </div>
                    </div>

                    <!-- Security & Features -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security & Features
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Text</label>
                                <input type="text" name="watermark_text" value="{{ old('watermark_text') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., COPYRIGHT KTVTC">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Background Image</label>
                                <input type="file" name="background_image" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Optional background image (max: 5MB)</p>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="has_qr_code" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('has_qr_code') ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Include QR Code</span>
                                </label>

                                <div id="qrCodePositionSection" class="{{ old('has_qr_code') ? 'block' : 'hidden' }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Position</label>
                                    <select name="qr_code_position"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                        <option value="">Select Position</option>
                                        <option value="bottom_right" {{ old('qr_code_position') == 'bottom_right' ? 'selected' : '' }}>Bottom Right</option>
                                        <option value="bottom_left" {{ old('qr_code_position') == 'bottom_left' ? 'selected' : '' }}>Bottom Left</option>
                                        <option value="top_right" {{ old('qr_code_position') == 'top_right' ? 'selected' : '' }}>Top Right</option>
                                        <option value="top_left" {{ old('qr_code_position') == 'top_left' ? 'selected' : '' }}>Top Left</option>
                                        <option value="center" {{ old('qr_code_position') == 'center' ? 'selected' : '' }}>Center</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Validity Period (Months)</label>
                                <input type="number" name="validity_months" value="{{ old('validity_months') }}" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., 12 (leave empty for no expiry)">
                            </div>
                        </div>
                    </div>

                    <!-- Signature Configuration -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Signature Configuration
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Line 1</label>
                                <input type="text" name="signature_line1" value="{{ old('signature_line1') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Principal Name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Image 1</label>
                                <input type="file" name="signature_image1" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Optional signature image (max: 2MB)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Line 2</label>
                                <input type="text" name="signature_line2" value="{{ old('signature_line2') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., Coordinator Name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Image 2</label>
                                <input type="file" name="signature_image2" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Optional signature image (max: 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Template Settings -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Template Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Active Template</span>
                                        <p class="text-sm text-gray-500">Enable this template</p>
                                    </div>
                                    <input type="checkbox" name="is_active" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                </label>

                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Auto-Generate</span>
                                        <p class="text-sm text-gray-500">Auto-generate on course completion</p>
                                    </div>
                                    <input type="checkbox" name="auto_generate" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('auto_generate') ? 'checked' : '' }}>
                                </label>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Requires Approval</span>
                                        <p class="text-sm text-gray-500">Certificate requires approval</p>
                                    </div>
                                    <input type="checkbox" name="requires_approval" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('requires_approval') ? 'checked' : '' }}>
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
                            Create Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Template Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all">
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
                            <h3 class="text-xl font-bold text-white">Edit Certificate Template</h3>
                            <p class="text-red-100 text-sm">Update template configuration</p>
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
                <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="editTemplateId" name="template_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                                <input type="text" id="editTemplateName" name="template_name" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Code *</label>
                                <input type="text" id="editTemplateCode" name="template_code" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Type *</label>
                                <select id="editTemplateType" name="template_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Type</option>
                                    <option value="course_completion">Course Completion</option>
                                    <option value="achievement">Achievement</option>
                                    <option value="participation">Participation</option>
                                    <option value="excellence">Excellence</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template File (PDF)</label>
                                <input type="file" name="template_file" accept=".pdf"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Upload new PDF template file (max: 10MB)</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="editDescription" name="description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Entities -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Associated Entities
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                                <select id="editCourseId" name="course_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course (Optional)</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}">{{ $course->course_name }} ({{ $course->course_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile School</label>
                                <select id="editMobileSchoolId" name="mobile_school_id"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Mobile School (Optional)</option>
                                    @foreach($mobileSchools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fields Configuration -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                </svg>
                                Dynamic Fields Configuration
                            </div>
                            <button type="button" onclick="addDynamicFieldEdit()" class="text-sm bg-primary text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors">
                                Add Field
                            </button>
                        </h4>
                        <div id="editDynamicFieldsContainer" class="space-y-4">
                            <!-- Dynamic fields will be added here -->
                        </div>
                    </div>

                    <!-- Security & Features -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security & Features
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Text</label>
                                <input type="text" id="editWatermarkText" name="watermark_text"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Background Image</label>
                                <input type="file" name="background_image" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Upload new background image (max: 5MB)</p>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editHasQrCode" name="has_qr_code" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Include QR Code</span>
                                </label>

                                <div id="editQrCodePositionSection" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Position</label>
                                    <select id="editQrCodePosition" name="qr_code_position"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                        <option value="">Select Position</option>
                                        <option value="bottom_right">Bottom Right</option>
                                        <option value="bottom_left">Bottom Left</option>
                                        <option value="top_right">Top Right</option>
                                        <option value="top_left">Top Left</option>
                                        <option value="center">Center</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Validity Period (Months)</label>
                                <input type="number" id="editValidityMonths" name="validity_months" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Signature Configuration -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Signature Configuration
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Line 1</label>
                                <input type="text" id="editSignatureLine1" name="signature_line1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Image 1</label>
                                <input type="file" name="signature_image1" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Upload new signature image (max: 2MB)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Line 2</label>
                                <input type="text" id="editSignatureLine2" name="signature_line2"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Image 2</label>
                                <input type="file" name="signature_image2" accept="image/*"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Upload new signature image (max: 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Template Settings -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Template Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Active Template</span>
                                        <p class="text-sm text-gray-500">Enable this template</p>
                                    </div>
                                    <input type="checkbox" id="editIsActive" name="is_active" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                </label>

                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Auto-Generate</span>
                                        <p class="text-sm text-gray-500">Auto-generate on course completion</p>
                                    </div>
                                    <input type="checkbox" id="editAutoGenerate" name="auto_generate" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                </label>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Requires Approval</span>
                                        <p class="text-sm text-gray-500">Certificate requires approval</p>
                                    </div>
                                    <input type="checkbox" id="editRequiresApproval" name="requires_approval" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
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
                            Update Template
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
                        <h3 class="text-lg font-semibold text-gray-900">Delete Certificate Template</h3>
                        <p class="text-gray-600 text-sm">This action cannot be undone.</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">Are you sure you want to delete this certificate template? All associated files and configurations will be permanently removed.</p>

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
                            Delete Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dynamic field counter
let fieldCounter = 0;
let editFieldCounter = 0;

// Common field configurations
const commonFields = [
    { name: 'student_name', label: 'Student Name' },
    { name: 'course_name', label: 'Course Name' },
    { name: 'completion_date', label: 'Completion Date' },
    { name: 'certificate_id', label: 'Certificate ID' },
    { name: 'issue_date', label: 'Issue Date' },
    { name: 'grade', label: 'Grade' },
    { name: 'duration', label: 'Course Duration' },
    { name: 'institution_name', label: 'Institution Name' },
    { name: 'principal_name', label: 'Principal Name' },
    { name: 'coordinator_name', label: 'Coordinator Name' }
];

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
    templateId, templateName, templateCode, description, templateType, courseId,
    mobileSchoolId, dynamicFields, layoutConfig, styling, watermarkText, hasQrCode,
    qrCodePosition, signatureLine1, signatureLine2, validityMonths, isActive,
    autoGenerate, requiresApproval
) {
    // Set form action
    document.getElementById('editForm').action = `/certificate-templates/${templateId}`;

    // Populate form fields
    document.getElementById('editTemplateId').value = templateId;
    document.getElementById('editTemplateName').value = templateName || '';
    document.getElementById('editTemplateCode').value = templateCode || '';
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editTemplateType').value = templateType;
    document.getElementById('editCourseId').value = courseId || '';
    document.getElementById('editMobileSchoolId').value = mobileSchoolId || '';
    document.getElementById('editWatermarkText').value = watermarkText || '';
    document.getElementById('editHasQrCode').checked = hasQrCode === '1';
    document.getElementById('editQrCodePosition').value = qrCodePosition || '';
    document.getElementById('editSignatureLine1').value = signatureLine1 || '';
    document.getElementById('editSignatureLine2').value = signatureLine2 || '';
    document.getElementById('editValidityMonths').value = validityMonths || '';
    document.getElementById('editIsActive').checked = isActive === '1';
    document.getElementById('editAutoGenerate').checked = autoGenerate === '1';
    document.getElementById('editRequiresApproval').checked = requiresApproval === '1';

    // Toggle QR code section
    toggleQrCodeSectionEdit();

    // Load dynamic fields
    loadDynamicFieldsEdit(dynamicFields);

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

// Dynamic Field Functions
function addDynamicField() {
    fieldCounter++;
    const container = document.getElementById('dynamicFieldsContainer');
    const noFieldsMessage = document.getElementById('noFieldsMessage');

    if (noFieldsMessage) {
        noFieldsMessage.style.display = 'none';
    }

    const fieldHtml = `
        <div class="border border-gray-200 rounded-lg p-4 bg-white" id="field-${fieldCounter}">
            <div class="flex justify-between items-center mb-3">
                <h5 class="font-medium text-gray-900">Field ${fieldCounter}</h5>
                <button type="button" onclick="removeDynamicField(${fieldCounter})" class="text-red-600 hover:text-red-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                    <select name="dynamic_fields[${fieldCounter}][field_name]" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Field</option>
                        ${commonFields.map(field => `<option value="${field.name}">${field.label}</option>`).join('')}
                        <option value="custom">Custom Field</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">X Position</label>
                    <input type="number" name="dynamic_fields[${fieldCounter}][x_position]" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., 100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Y Position</label>
                    <input type="number" name="dynamic_fields[${fieldCounter}][y_position]" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., 200">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                        <input type="number" name="dynamic_fields[${fieldCounter}][font_size]" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 16" value="12">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                        <select name="dynamic_fields[${fieldCounter}][font_family]" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Helvetica">Helvetica</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
}

function addDynamicFieldEdit() {
    editFieldCounter++;
    const container = document.getElementById('editDynamicFieldsContainer');

    const fieldHtml = `
        <div class="border border-gray-200 rounded-lg p-4 bg-white" id="edit-field-${editFieldCounter}">
            <div class="flex justify-between items-center mb-3">
                <h5 class="font-medium text-gray-900">Field ${editFieldCounter}</h5>
                <button type="button" onclick="removeDynamicFieldEdit(${editFieldCounter})" class="text-red-600 hover:text-red-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                    <select name="dynamic_fields[${editFieldCounter}][field_name]" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Field</option>
                        ${commonFields.map(field => `<option value="${field.name}">${field.label}</option>`).join('')}
                        <option value="custom">Custom Field</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">X Position</label>
                    <input type="number" name="dynamic_fields[${editFieldCounter}][x_position]" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., 100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Y Position</label>
                    <input type="number" name="dynamic_fields[${editFieldCounter}][y_position]" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., 200">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                        <input type="number" name="dynamic_fields[${editFieldCounter}][font_size]" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 16" value="12">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                        <select name="dynamic_fields[${editFieldCounter}][font_family]" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Helvetica">Helvetica</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
}

function removeDynamicField(fieldId) {
    const fieldElement = document.getElementById(`field-${fieldId}`);
    if (fieldElement) {
        fieldElement.remove();
    }

    // Show no fields message if no fields left
    const container = document.getElementById('dynamicFieldsContainer');
    if (container.children.length === 1) { // Only noFieldsMessage left
        document.getElementById('noFieldsMessage').style.display = 'block';
    }
}

function removeDynamicFieldEdit(fieldId) {
    const fieldElement = document.getElementById(`edit-field-${fieldId}`);
    if (fieldElement) {
        fieldElement.remove();
    }
}

function loadDynamicFieldsEdit(dynamicFieldsJson) {
    const container = document.getElementById('editDynamicFieldsContainer');
    container.innerHTML = '';

    if (dynamicFieldsJson) {
        try {
            const fields = JSON.parse(dynamicFieldsJson);
            fields.forEach((field, index) => {
                editFieldCounter++;
                const fieldHtml = `
                    <div class="border border-gray-200 rounded-lg p-4 bg-white" id="edit-field-${editFieldCounter}">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="font-medium text-gray-900">Field ${index + 1}</h5>
                            <button type="button" onclick="removeDynamicFieldEdit(${editFieldCounter})" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                                <select name="dynamic_fields[${editFieldCounter}][field_name]" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Field</option>
                                    ${commonFields.map(f => `<option value="${f.name}" ${field.field_name === f.name ? 'selected' : ''}>${f.label}</option>`).join('')}
                                    <option value="custom" ${field.field_name === 'custom' ? 'selected' : ''}>Custom Field</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">X Position</label>
                                <input type="number" name="dynamic_fields[${editFieldCounter}][x_position]" value="${field.x_position}" required
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Y Position</label>
                                <input type="number" name="dynamic_fields[${editFieldCounter}][y_position]" value="${field.y_position}" required
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                                    <input type="number" name="dynamic_fields[${editFieldCounter}][font_size]" value="${field.font_size}" required
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                                    <select name="dynamic_fields[${editFieldCounter}][font_family]" required
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="Arial" ${field.font_family === 'Arial' ? 'selected' : ''}>Arial</option>
                                        <option value="Times New Roman" ${field.font_family === 'Times New Roman' ? 'selected' : ''}>Times New Roman</option>
                                        <option value="Helvetica" ${field.font_family === 'Helvetica' ? 'selected' : ''}>Helvetica</option>
                                        <option value="Courier New" ${field.font_family === 'Courier New' ? 'selected' : ''}>Courier New</option>
                                        <option value="Verdana" ${field.font_family === 'Verdana' ? 'selected' : ''}>Verdana</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', fieldHtml);
            });
        } catch (e) {
            console.error('Error parsing dynamic fields:', e);
        }
    }
}

// Toggle QR code section
function toggleQrCodeSectionEdit() {
    const hasQrCode = document.getElementById('editHasQrCode').checked;
    document.getElementById('editQrCodePositionSection').classList.toggle('hidden', !hasQrCode);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Create modal QR code toggle
    const createQrCheckbox = document.querySelector('input[name="has_qr_code"]');
    const createQrSection = document.getElementById('qrCodePositionSection');

    if (createQrCheckbox) {
        createQrCheckbox.addEventListener('change', function() {
            createQrSection.classList.toggle('hidden', !this.checked);
        });
    }

    // Edit modal QR code toggle
    const editQrCheckbox = document.getElementById('editHasQrCode');
    if (editQrCheckbox) {
        editQrCheckbox.addEventListener('change', toggleQrCodeSectionEdit);
    }

    // Add initial field to create modal
    addDynamicField();
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
