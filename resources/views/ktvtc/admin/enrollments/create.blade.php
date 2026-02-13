@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'New Enrollment')
@section('subtitle', 'Enroll a student in a course')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">New</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">New Course Enrollment</h3>
        <p class="text-sm text-gray-600 mt-1">Enroll a student in a course and set up their fee structure</p>
    </div>

    <form action="{{ route('admin.tvet.enrollments.store') }}" method="POST" id="enrollmentForm">
        @csrf

        <div class="p-6">
            <!-- Student Selection Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Student Information
                </h4>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Select Student
                        </label>
                        <div class="relative">
                            <select name="student_id" id="student_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_id') border-red-500 @enderror"
                                    required>
                                <option value="">Search for a student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}
                                            data-student-number="{{ $student->student_number }}"
                                            data-email="{{ $student->email }}"
                                            data-phone="{{ $student->phone }}">
                                        {{ $student->full_name }} - {{ $student->student_number ?? 'No ID' }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                        @error('student_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Student Quick Info -->
                        <div id="studentQuickInfo" class="mt-3 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800" id="studentName"></p>
                                        <p class="text-xs text-blue-600" id="studentDetails"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Campus
                        </label>
                        @if(auth()->user()->role == 2)
                            <select name="campus_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id', auth()->user()->campus_id) == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                            <input type="text" value="{{ auth()->user()->campus->name ?? 'N/A' }}"
                                   class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                        @endif
                        @error('campus_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Course & Intake Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-book text-primary mr-2"></i>
                    Course & Intake
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Course Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Course
                        </label>
                        <select name="course_id" id="course_id"
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

                    <!-- Intake Period -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Intake Period
                        </label>
                        <select name="intake_period"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_period') border-red-500 @enderror"
                                required>
                            <option value="">Select Intake</option>
                            @foreach($intakePeriods as $period)
                                <option value="{{ $period }}" {{ old('intake_period') == $period ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                        @error('intake_period')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Intake Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Intake Year
                        </label>
                        <select name="intake_year"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_year') border-red-500 @enderror"
                                required>
                            <option value="">Select Year</option>
                            @for($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ old('intake_year', date('Y')) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        @error('intake_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Study Mode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Study Mode
                        </label>
                        <select name="study_mode"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('study_mode') border-red-500 @enderror"
                                required>
                            <option value="">Select Mode</option>
                            @foreach($studyModes as $mode)
                                <option value="{{ $mode }}" {{ old('study_mode', 'full_time') == $mode ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $mode)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('study_mode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Student Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Student Type
                        </label>
                        <select name="student_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_type') border-red-500 @enderror"
                                required>
                            <option value="">Select Type</option>
                            @foreach($studentTypes as $type)
                                <option value="{{ $type }}" {{ old('student_type', 'new') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sponsorship Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Sponsorship
                        </label>
                        <select name="sponsorship_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('sponsorship_type') border-red-500 @enderror"
                                required>
                            <option value="">Select Sponsorship</option>
                            @foreach($sponsorshipTypes as $type)
                                <option value="{{ $type }}" {{ old('sponsorship_type', 'self') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('sponsorship_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fee Template Selection Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-invoice text-primary mr-2"></i>
                    Fee Template
                </h4>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Fee Template (Optional)
                        </label>
                        <select name="use_template" id="fee_template_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">-- Manually enter fees --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Select a fee template to automatically populate fee items
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fee Structure Type
                        </label>
                        <select name="fee_structure_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Type</option>
                            @foreach($feeStructureTypes as $type)
                                <option value="{{ $type }}" {{ old('fee_structure_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Template Preview -->
                <div id="templatePreview" class="mt-4 hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-semibold text-blue-800">Template Preview</h5>
                            <span id="templateTotal" class="text-lg font-bold text-blue-800">KES 0.00</span>
                        </div>
                        <div id="templateItems" class="space-y-2 text-sm text-blue-700">
                            <!-- Template items will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollment Dates Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Enrollment Dates
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Enrollment Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Enrollment Date
                        </label>
                        <input type="date" name="enrollment_date"
                               value="{{ old('enrollment_date', date('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('enrollment_date') border-red-500 @enderror"
                               required>
                        @error('enrollment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expected Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Duration (Months)
                        </label>
                        <input type="number" name="expected_duration_months"
                               value="{{ old('expected_duration_months') }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 12">
                    </div>

                    <!-- Number of Terms -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Number of Terms
                        </label>
                        <input type="number" name="number_of_terms"
                               value="{{ old('number_of_terms') }}"
                               min="1"
                               max="4"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 4">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Expected Start Date
                        </label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Expected End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Expected End Date
                        </label>
                        <input type="date" name="expected_end_date"
                               value="{{ old('expected_end_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Manual Fee Entry Card -->
            <div id="manualFeeEntry" class="bg-gray-50 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-medium text-gray-800 flex items-center">
                        <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                        Fee Details
                    </h4>
                    <span class="text-sm text-gray-500">Enter total course fee if not using a template</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Total Course Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Total Course Fee (KES)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="total_course_fee" id="total_course_fee"
                                   step="0.01" min="0"
                                   value="{{ old('total_course_fee') }}"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Exam Requirements -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            External Examination
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="requires_external_exam" id="requires_external_exam"
                                       value="1" {{ old('requires_external_exam') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Requires external exam registration</span>
                            </label>
                        </div>
                    </div>

                    <!-- Exam Body (shown if exam required) -->
                    <div id="exam_body_container" class="lg:col-span-3 {{ !old('requires_external_exam') ? 'hidden' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Exam Body
                                </label>
                                <select name="external_exam_body"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Exam Body</option>
                                    <option value="nita" {{ old('external_exam_body') == 'nita' ? 'selected' : '' }}>NITA</option>
                                    <option value="cdacc" {{ old('external_exam_body') == 'cdacc' ? 'selected' : '' }}>CDACC</option>
                                    <option value="knec" {{ old('external_exam_body') == 'knec' ? 'selected' : '' }}>KNEC</option>
                                    <option value="other" {{ old('external_exam_body') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remarks Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Remarks
                </h4>

                <div>
                    <textarea name="remarks"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Any additional notes about this enrollment...">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-flag text-primary mr-2"></i>
                    Status
                </h4>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="radio" name="status" value="registered"
                               {{ old('status', 'registered') == 'registered' ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Registered</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="in_progress"
                               {{ old('status') == 'in_progress' ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">In Progress</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.tvet.enrollments.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Create Enrollment</span>
            </button>
        </div>
    </form>
</div>

<!-- Fee Templates JSON Data -->
<script>
    window.feeTemplates = @json($feeTemplates);
</script>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============ STUDENT QUICK INFO ============
        const studentSelect = document.getElementById('student_id');
        const studentQuickInfo = document.getElementById('studentQuickInfo');
        const studentName = document.getElementById('studentName');
        const studentDetails = document.getElementById('studentDetails');

        studentSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const name = selected.text.split(' - ')[0];
                const studentNumber = selected.dataset.studentNumber;
                const email = selected.dataset.email;
                const phone = selected.dataset.phone;

                studentName.textContent = name;
                studentDetails.textContent = `${studentNumber || 'No ID'} | ${email || 'No email'} | ${phone || 'No phone'}`;
                studentQuickInfo.classList.remove('hidden');
            } else {
                studentQuickInfo.classList.add('hidden');
            }
        });

        // Trigger change if there's a selected value
        if (studentSelect.value) {
            studentSelect.dispatchEvent(new Event('change'));
        }

        // ============ FEE TEMPLATE LOADER ============
        const courseSelect = document.getElementById('course_id');
        const feeTemplateSelect = document.getElementById('fee_template_id');
        const templatePreview = document.getElementById('templatePreview');
        const templateItems = document.getElementById('templateItems');
        const templateTotal = document.getElementById('templateTotal');
        const totalCourseFee = document.getElementById('total_course_fee');

        courseSelect.addEventListener('change', function() {
            const courseId = this.value;
            feeTemplateSelect.innerHTML = '<option value="">-- Manually enter fees --</option>';

            if (courseId && window.feeTemplates[courseId]) {
                const templates = window.feeTemplates[courseId];
                templates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = `${template.name} - KES ${template.total_amount.toLocaleString()}`;
                    feeTemplateSelect.appendChild(option);
                });
            }
        });

        feeTemplateSelect.addEventListener('change', function() {
            const templateId = this.value;

            if (templateId) {
                // Find the selected template
                let selectedTemplate = null;
                for (const courseId in window.feeTemplates) {
                    const template = window.feeTemplates[courseId].find(t => t.id == templateId);
                    if (template) {
                        selectedTemplate = template;
                        break;
                    }
                }

                if (selectedTemplate) {
                    // Display template preview
                    templateItems.innerHTML = '';
                    selectedTemplate.fee_items.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'flex justify-between items-center';
                        itemDiv.innerHTML = `
                            <span>${item.item_name} (${item.term_label})</span>
                            <span class="font-medium">KES ${item.total_amount.toLocaleString()}</span>
                        `;
                        templateItems.appendChild(itemDiv);
                    });

                    templateTotal.textContent = `KES ${selectedTemplate.total_amount.toLocaleString()}`;
                    templatePreview.classList.remove('hidden');

                    // Set total course fee
                    if (totalCourseFee) {
                        totalCourseFee.value = selectedTemplate.total_amount;
                    }
                }
            } else {
                templatePreview.classList.add('hidden');
            }
        });

        // Trigger change if there are preselected values
        if (courseSelect.value) {
            courseSelect.dispatchEvent(new Event('change'));
        }

        // ============ EXAM REQUIREMENT TOGGLE ============
        const examCheckbox = document.getElementById('requires_external_exam');
        const examBodyContainer = document.getElementById('exam_body_container');

        examCheckbox.addEventListener('change', function() {
            if (this.checked) {
                examBodyContainer.classList.remove('hidden');
            } else {
                examBodyContainer.classList.add('hidden');
            }
        });

        // ============ DATE VALIDATION ============
        const startDate = document.querySelector('input[name="start_date"]');
        const endDate = document.querySelector('input[name="expected_end_date"]');

        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                if (endDate.value && new Date(endDate.value) < new Date(this.value)) {
                    alert('End date cannot be before start date');
                    endDate.value = '';
                }
            });

            endDate.addEventListener('change', function() {
                if (startDate.value && new Date(this.value) < new Date(startDate.value)) {
                    alert('End date cannot be before start date');
                    this.value = '';
                }
            });
        }
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
