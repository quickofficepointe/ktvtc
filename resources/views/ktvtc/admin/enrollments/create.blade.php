@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'New Enrollment')
@section('subtitle', 'Enroll a student in a course')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">New Enrollment</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">New Course Enrollment</h3>
                <p class="text-sm text-gray-600 mt-1">Enroll a student in a course and set their fee structure</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fields marked with <span class="text-red-500">*</span> are required
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.enrollments.store') }}" method="POST" id="enrollmentForm">
        @csrf

        <div class="p-6 space-y-8">
            <!-- ============ STUDENT SELECTION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Student Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Select Student
                        </label>
                        <div class="relative">
                            <select name="student_id" id="student_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_id') border-red-500 @enderror">
                                <option value="">-- Search for a student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                            data-name="{{ $student->full_name }}"
                                            data-number="{{ $student->student_number }}"
                                            data-email="{{ $student->email }}"
                                            data-phone="{{ $student->phone }}"
                                            {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->full_name }} - {{ $student->student_number ?? 'No ID' }} ({{ $student->email ?? 'No email' }})
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                        @error('student_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Student Quick Info Preview -->
                    <div id="studentPreview" class="md:col-span-2 hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user-graduate text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h5 class="text-sm font-medium text-blue-800" id="previewName"></h5>
                                    <div class="mt-1 text-xs text-blue-600 space-y-1">
                                        <p id="previewNumber"></p>
                                        <p id="previewEmail"></p>
                                        <p id="previewPhone"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campus (Auto-filled based on user role) -->
                    @if(auth()->user()->role == 2)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Campus</label>
                        <select name="campus_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('campus_id') border-red-500 @enderror">
                            <option value="">Select Campus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('campus_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                        <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                    @endif
                </div>
            </div>

            <!-- ============ COURSE & INTAKE ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-book-open text-primary mr-2"></i>
                    Course & Intake
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Course -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Course</label>
                        <select name="course_id" id="course_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('course_id') border-red-500 @enderror">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                        data-code="{{ $course->code }}"
                                        data-duration="{{ $course->duration_months ?? 0 }}"
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Intake Month -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Month</label>
                        <select name="intake_month" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_month') border-red-500 @enderror">
                            <option value="">Select Month</option>
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                <option value="{{ $month }}" {{ old('intake_month') == $month ? 'selected' : '' }}>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                        @error('intake_month')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Intake Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Year</label>
                        <select name="intake_year" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_year') border-red-500 @enderror">
                            @for($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ old('intake_year', date('Y')) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        @error('intake_year')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Study Mode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Study Mode</label>
                        <select name="study_mode" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('study_mode') border-red-500 @enderror">
                            <option value="">Select Mode</option>
                            @foreach(['full_time', 'part_time', 'evening', 'weekend', 'online'] as $mode)
                                <option value="{{ $mode }}" {{ old('study_mode', 'full_time') == $mode ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $mode)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('study_mode')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Student Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Student Type</label>
                        <select name="student_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_type') border-red-500 @enderror">
                            <option value="">Select Type</option>
                            @foreach(['new', 'continuing', 'alumnus', 'transfer'] as $type)
                                <option value="{{ $type }}" {{ old('student_type', 'new') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sponsorship Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Sponsorship</label>
                        <select name="sponsorship_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('sponsorship_type') border-red-500 @enderror">
                            <option value="">Select Sponsorship</option>
                            @foreach(['self', 'sponsored', 'government', 'scholarship', 'company'] as $type)
                                <option value="{{ $type }}" {{ old('sponsorship_type', 'self') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('sponsorship_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- ============ FEES ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Fee Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Total Fees -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Total Fees (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number"
                                   name="total_fees"
                                   id="total_fees"
                                   value="{{ old('total_fees') }}"
                                   min="0"
                                   step="100"
                                   required
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('total_fees') border-red-500 @enderror"
                                   placeholder="0.00">
                        </div>
                        @error('total_fees')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount Paid (Optional - for arrears) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number"
                                   name="amount_paid"
                                   id="amount_paid"
                                   value="{{ old('amount_paid', 0) }}"
                                   min="0"
                                   step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('amount_paid') border-red-500 @enderror"
                                   placeholder="0.00">
                        </div>
                        @error('amount_paid')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Balance (Calculated) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Balance (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="text"
                                   id="balance"
                                   readonly
                                   value="0.00"
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Calculated automatically</p>
                    </div>

                    <!-- External Exam Required -->
                    <div class="flex items-center mt-6">
                        <input type="checkbox"
                               name="requires_external_exam"
                               id="requires_external_exam"
                               value="1"
                               {{ old('requires_external_exam') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="requires_external_exam" class="ml-2 text-sm text-gray-700">
                            Requires External Examination
                        </label>
                    </div>
                </div>

                <!-- Exam Body (Shown if exam required) -->
                <div id="exam_body_container" class="mt-4 {{ !old('requires_external_exam') ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                            <select name="exam_body" id="exam_body"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Exam Body</option>
                                <option value="KNEC" {{ old('exam_body') == 'KNEC' ? 'selected' : '' }}>KNEC</option>
                                <option value="NITA" {{ old('exam_body') == 'NITA' ? 'selected' : '' }}>NITA</option>
                                <option value="CDACC" {{ old('exam_body') == 'CDACC' ? 'selected' : '' }}>CDACC</option>
                                <option value="TVETA" {{ old('exam_body') == 'TVETA' ? 'selected' : '' }}>TVETA</option>
                                <option value="OTHER" {{ old('exam_body') == 'OTHER' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ DATES ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Enrollment Dates
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Enrollment Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Enrollment Date</label>
                        <input type="date"
                               name="enrollment_date"
                               value="{{ old('enrollment_date', date('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('enrollment_date') border-red-500 @enderror">
                        @error('enrollment_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Expected End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected End Date</label>
                        <input type="date"
                               name="expected_end_date"
                               id="expected_end_date"
                               value="{{ old('expected_end_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- ============ DURATION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Course Duration
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Duration Months -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                        <input type="number"
                               name="duration_months"
                               id="duration_months"
                               value="{{ old('duration_months') }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 12">
                    </div>
                </div>
            </div>

            <!-- ============ ADDITIONAL INFO ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Additional Information
                </h4>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Initial Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="registered" {{ old('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                        </select>
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks / Notes</label>
                        <textarea name="remarks"
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Any additional notes about this enrollment...">{{ old('remarks') }}</textarea>
                    </div>

                    <!-- Import/Legacy Info -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Code (Optional)</label>
                        <input type="text"
                               name="legacy_code"
                               value="{{ old('legacy_code') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., HDBT/021/2021">
                        <p class="mt-1 text-xs text-gray-500">Original code from CSV import if applicable</p>
                    </div>
                </div>
            </div>

            <!-- ============ QUICK TIPS ============ -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">Quick Tips</h4>
                        <ul class="mt-2 text-xs text-blue-700 space-y-1">
                            <li><i class="fas fa-check-circle mr-1 text-green-500"></i> Balance is automatically calculated as Total Fees - Amount Paid</li>
                            <li><i class="fas fa-check-circle mr-1 text-green-500"></i> You can record payments later from the enrollment detail page</li>
                            <li><i class="fas fa-check-circle mr-1 text-green-500"></i> If the student has already paid some fees, enter the amount in "Amount Paid"</li>
                            <li><i class="fas fa-check-circle mr-1 text-green-500"></i> For CSV imports, you can enter the original legacy code for tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.enrollments.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Create Enrollment
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============ STUDENT PREVIEW ============
        const studentSelect = document.getElementById('student_id');
        const studentPreview = document.getElementById('studentPreview');
        const previewName = document.getElementById('previewName');
        const previewNumber = document.getElementById('previewNumber');
        const previewEmail = document.getElementById('previewEmail');
        const previewPhone = document.getElementById('previewPhone');

        studentSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const name = selected.dataset.name;
                const number = selected.dataset.number;
                const email = selected.dataset.email;
                const phone = selected.dataset.phone;

                previewName.textContent = name;
                previewNumber.textContent = number ? `Student #: ${number}` : 'Student #: Not assigned';
                previewEmail.textContent = email ? `Email: ${email}` : 'Email: Not provided';
                previewPhone.textContent = phone ? `Phone: ${phone}` : 'Phone: Not provided';

                studentPreview.classList.remove('hidden');
            } else {
                studentPreview.classList.add('hidden');
            }
        });

        // Trigger change if there's a selected value
        if (studentSelect.value) {
            studentSelect.dispatchEvent(new Event('change'));
        }

        // ============ BALANCE CALCULATION ============
        const totalFees = document.getElementById('total_fees');
        const amountPaid = document.getElementById('amount_paid');
        const balanceField = document.getElementById('balance');

        function calculateBalance() {
            const total = parseFloat(totalFees.value) || 0;
            const paid = parseFloat(amountPaid.value) || 0;
            const balance = total - paid;

            balanceField.value = balance.toFixed(2);

            // Highlight if balance is positive (still owes money)
            if (balance > 0) {
                balanceField.classList.add('text-red-600');
                balanceField.classList.remove('text-gray-700');
            } else {
                balanceField.classList.remove('text-red-600');
                balanceField.classList.add('text-gray-700');
            }
        }

        if (totalFees && amountPaid) {
            totalFees.addEventListener('input', calculateBalance);
            amountPaid.addEventListener('input', calculateBalance);

            // Initial calculation
            calculateBalance();
        }

        // ============ EXAM BODY TOGGLE ============
        const examCheckbox = document.getElementById('requires_external_exam');
        const examBodyContainer = document.getElementById('exam_body_container');
        const examBodySelect = document.getElementById('exam_body');

        examCheckbox.addEventListener('change', function() {
            if (this.checked) {
                examBodyContainer.classList.remove('hidden');
                examBodySelect.setAttribute('required', 'required');
            } else {
                examBodyContainer.classList.add('hidden');
                examBodySelect.removeAttribute('required');
                examBodySelect.value = '';
            }
        });

        // Trigger if already checked
        if (examCheckbox.checked) {
            examBodyContainer.classList.remove('hidden');
            examBodySelect.setAttribute('required', 'required');
        }

        // ============ DATE VALIDATION ============
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('expected_end_date');

        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                if (this.value && endDate.value) {
                    if (new Date(endDate.value) < new Date(this.value)) {
                        alert('End date cannot be before start date');
                        endDate.value = '';
                    }
                }
            });

            endDate.addEventListener('change', function() {
                if (startDate.value && this.value) {
                    if (new Date(this.value) < new Date(startDate.value)) {
                        alert('End date cannot be before start date');
                        this.value = '';
                    }
                }
            });
        }

        // ============ AUTO-CALCULATE END DATE ============
        const durationMonths = document.getElementById('duration_months');

        if (startDate && durationMonths) {
            startDate.addEventListener('change', function() {
                if (this.value && durationMonths.value) {
                    const start = new Date(this.value);
                    start.setMonth(start.getMonth() + parseInt(durationMonths.value));

                    const year = start.getFullYear();
                    const month = String(start.getMonth() + 1).padStart(2, '0');
                    const day = String(start.getDate()).padStart(2, '0');

                    endDate.value = `${year}-${month}-${day}`;
                }
            });

            durationMonths.addEventListener('input', function() {
                if (startDate.value && this.value) {
                    const start = new Date(startDate.value);
                    start.setMonth(start.getMonth() + parseInt(this.value));

                    const year = start.getFullYear();
                    const month = String(start.getMonth() + 1).padStart(2, '0');
                    const day = String(start.getDate()).padStart(2, '0');

                    endDate.value = `${year}-${month}-${day}`;
                }
            });
        }

        // ============ COURSE SELECTION ============
        const courseSelect = document.getElementById('course_id');

        courseSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value && selected.dataset.duration) {
                const duration = parseInt(selected.dataset.duration);
                if (duration > 0) {
                    durationMonths.value = duration;

                    // Trigger end date calculation if start date is set
                    if (startDate.value) {
                        const start = new Date(startDate.value);
                        start.setMonth(start.getMonth() + duration);

                        const year = start.getFullYear();
                        const month = String(start.getMonth() + 1).padStart(2, '0');
                        const day = String(start.getDate()).padStart(2, '0');

                        endDate.value = `${year}-${month}-${day}`;
                    }
                }
            }
        });
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }

    /* Smooth transitions */
    .hidden {
        display: none !important;
    }

    #exam_body_container {
        transition: all 0.3s ease;
    }
</style>
@endsection
