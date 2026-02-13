@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Create Fee Template')
@section('subtitle', 'Create a new course fee template')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fees</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Course Fee Templates</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Create</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Templates</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">New Course Fee Template</h3>
        <p class="text-sm text-gray-600 mt-1">Create a fee template for a specific course and exam type</p>
    </div>

    <form action="{{ route('admin.tvet.course-fee-templates.store') }}" method="POST" class="p-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Template Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Basic Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Template Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Template Name
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-red-500 @enderror"
                                   placeholder="e.g., SHEP NITA Package 2024"
                                   required
                                   onkeyup="generateCode()">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Template Code -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Template Code
                                <span class="text-xs text-gray-500 ml-2">(Auto-generated if empty)</span>
                            </label>
                            <div class="flex">
                                <input type="text"
                                       name="code"
                                       id="code"
                                       value="{{ old('code') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono @error('code') border-red-500 @enderror"
                                       placeholder="e.g., SHEP-NITA-2024">
                                <button type="button"
                                        onclick="generateCode()"
                                        class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Course Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Course
                            </label>
                            <select name="course_id"
                                    id="course_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('course_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }} ({{ $course->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Exam Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Exam Type
                            </label>
                            <select name="exam_type"
                                    id="exam_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('exam_type') border-red-500 @enderror"
                                    required>
                                <option value="">Select Exam Type</option>
                                @foreach($examTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('exam_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Duration & Structure Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-primary mr-2"></i>
                        Duration & Structure
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Terms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Number of Terms
                            </label>
                            <select name="total_terms"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('total_terms') border-red-500 @enderror"
                                    required>
                                <option value="">Select Terms</option>
                                @foreach($totalTerms as $terms)
                                    <option value="{{ $terms }}" {{ old('total_terms', 1) == $terms ? 'selected' : '' }}>
                                        {{ $terms }} Term{{ $terms > 1 ? 's' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('total_terms')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Duration Months -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Duration (Months)
                            </label>
                            <select name="duration_months"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Duration</option>
                                @foreach($durations as $months)
                                    <option value="{{ $months }}" {{ old('duration_months') == $months ? 'selected' : '' }}>
                                        {{ $months }} Month{{ $months > 1 ? 's' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('duration_months')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Intake Periods -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Applicable Intake Periods
                            </label>
                            <div class="flex flex-wrap gap-4">
                                @foreach($intakePeriods as $period)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox"
                                               name="intake_periods[]"
                                               value="{{ $period }}"
                                               {{ in_array($period, old('intake_periods', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700">{{ $period }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Leave empty if applicable to all intakes</p>
                            @error('intake_periods')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-align-left text-primary mr-2"></i>
                        Description & Notes
                    </h4>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Describe what this fee template covers...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Internal Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Internal Notes
                            </label>
                            <textarea name="notes"
                                      rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Internal notes (only visible to staff)">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings & Campus -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Campus Assignment Card -->
                @if(auth()->user()->role == 2)
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-building text-primary mr-2"></i>
                        Campus Assignment
                    </h4>

                    <div class="space-y-4">
                        <!-- Global or Campus-specific -->
                        <div>
                            <div class="flex items-center mb-3">
                                <input type="radio"
                                       name="campus_scope"
                                       id="scope_global"
                                       value="global"
                                       {{ !old('campus_id') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_global" class="ml-2 text-sm text-gray-700">
                                    Global Template (All Campuses)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio"
                                       name="campus_scope"
                                       id="scope_specific"
                                       value="specific"
                                       {{ old('campus_id') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_specific" class="ml-2 text-sm text-gray-700">
                                    Campus-Specific
                                </label>
                            </div>
                        </div>

                        <!-- Campus Select (hidden if global) -->
                        <div id="campus-select-container" class="{{ !old('campus_id') ? 'hidden' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Campus
                            </label>
                            <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Choose Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @else
                    <!-- For non-admin users, auto-assign their campus -->
                    <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                @endif

                <!-- Status Settings Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-flag text-primary mr-2"></i>
                        Status Settings
                    </h4>

                    <div class="space-y-4">
                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Active
                            </label>
                        </div>

                        <!-- Is Default -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_default"
                                   id="is_default"
                                   value="1"
                                   {{ old('is_default') ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_default" class="ml-2 text-sm text-gray-700">
                                Set as Default Template
                            </label>
                            <i class="fas fa-info-circle text-gray-400 ml-1 text-xs"
                               data-tooltip="This template will be auto-selected for new enrollments"></i>
                        </div>

                        <!-- Is Public -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_public"
                                   id="is_public"
                                   value="1"
                                   {{ old('is_public', true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_public" class="ml-2 text-sm text-gray-700">
                                Public (Visible to Students)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                    <h4 class="text-md font-medium text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-lightbulb text-blue-600 mr-2"></i>
                        Next Steps
                    </h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                            <span><strong>Step 1:</strong> Create the template with basic info</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                            <span><strong>Step 2:</strong> Add fee items (Tuition, Registration, Exam fees, etc.)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                            <span><strong>Step 3:</strong> Set term applicability for each fee</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                            <span><strong>Step 4:</strong> Activate and set as default</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Create Template & Continue</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // ============ CODE GENERATION ============
    function generateCode() {
        const name = document.getElementById('name').value;
        const courseSelect = document.getElementById('course_id');
        const examTypeSelect = document.getElementById('exam_type');

        let code = '';

        // Get course code
        if (courseSelect.selectedIndex > 0) {
            const courseText = courseSelect.options[courseSelect.selectedIndex].text;
            const courseCodeMatch = courseText.match(/\(([^)]+)\)/);
            if (courseCodeMatch) {
                code += courseCodeMatch[1];
            }
        }

        // Get exam type
        if (examTypeSelect.selectedIndex > 0) {
            const examType = examTypeSelect.value;
            if (code) code += '-';
            code += examType.toUpperCase();
        }

        // Add year
        if (code) {
            const year = new Date().getFullYear();
            code += `-${year}`;
            document.getElementById('code').value = code;
        }
    }

    // Auto-generate code when course or exam type changes
    document.getElementById('course_id')?.addEventListener('change', generateCode);
    document.getElementById('exam_type')?.addEventListener('change', generateCode);

    // ============ CAMPUS SCOPE ============
    function toggleCampusSelect() {
        const isSpecific = document.getElementById('scope_specific')?.checked;
        const container = document.getElementById('campus-select-container');

        if (container) {
            if (isSpecific) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                const campusSelect = document.querySelector('[name="campus_id"]');
                if (campusSelect) campusSelect.value = '';
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCampusSelect();
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
