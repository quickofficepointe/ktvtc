@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Enrollment')
@section('subtitle', 'Update enrollment information')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $enrollment->enrollment_number }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Enrollment</span>
    </a>
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
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center mr-4">
                <i class="fas fa-file-invoice text-primary text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Enrollment: {{ $enrollment->enrollment_number }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $enrollment->student->full_name ?? 'N/A' }} - {{ $enrollment->course->name ?? 'N/A' }}
                </p>
            </div>
            @php
                $statusColors = [
                    'registered' => 'blue',
                    'in_progress' => 'green',
                    'completed' => 'purple',
                    'dropped' => 'red',
                    'suspended' => 'yellow',
                    'deferred' => 'orange',
                ];
                $color = $statusColors[$enrollment->status] ?? 'gray';
            @endphp
            <span class="ml-4 px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                {{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}
            </span>
        </div>
    </div>

    <form action="{{ route('admin.tvet.enrollments.update', $enrollment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="p-6">
            <!-- Student & Course Info (Read-only) -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Student & Course Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <input type="text"
                               value="{{ $enrollment->student->full_name ?? 'N/A' }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                        <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                        <input type="text"
                               value="{{ $enrollment->course->name ?? 'N/A' }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                        <input type="hidden" name="course_id" value="{{ $enrollment->course_id }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Number</label>
                        <input type="text"
                               value="{{ $enrollment->enrollment_number }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono"
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Intake & Mode Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Intake & Mode
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Intake Period -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Period</label>
                        <select name="intake_period"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_period') border-red-500 @enderror"
                                required>
                            <option value="">Select Intake</option>
                            @foreach(['Jan', 'May', 'Sept'] as $period)
                                <option value="{{ $period }}" {{ old('intake_period', $enrollment->intake_period) == $period ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Intake Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Year</label>
                        <select name="intake_year"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_year') border-red-500 @enderror"
                                required>
                            @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ old('intake_year', $enrollment->intake_year) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Study Mode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Study Mode</label>
                        <select name="study_mode"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('study_mode') border-red-500 @enderror"
                                required>
                            @foreach(['full_time', 'part_time', 'evening', 'weekend', 'online'] as $mode)
                                <option value="{{ $mode }}" {{ old('study_mode', $enrollment->study_mode) == $mode ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $mode)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Student Type</label>
                        <select name="student_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_type') border-red-500 @enderror"
                                required>
                            @foreach(['new', 'continuing', 'alumnus', 'transfer'] as $type)
                                <option value="{{ $type }}" {{ old('student_type', $enrollment->student_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sponsorship Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Sponsorship</label>
                        <select name="sponsorship_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('sponsorship_type') border-red-500 @enderror"
                                required>
                            @foreach(['self', 'sponsored', 'government', 'scholarship', 'company'] as $type)
                                <option value="{{ $type }}" {{ old('sponsorship_type', $enrollment->sponsorship_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Campus -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                        @if(auth()->user()->role == 2)
                            <select name="campus_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id', $enrollment->campus_id) == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="campus_id" value="{{ $enrollment->campus_id }}">
                            <input type="text" value="{{ $enrollment->campus->name ?? 'N/A' }}"
                                   class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dates & Duration Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Dates & Duration
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Enrollment Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Date</label>
                        <input type="date" name="enrollment_date"
                               value="{{ old('enrollment_date', $enrollment->enrollment_date->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                        <input type="number" name="expected_duration_months"
                               value="{{ old('expected_duration_months', $enrollment->expected_duration_months) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Number of Terms -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Terms</label>
                        <input type="number" name="number_of_terms"
                               value="{{ old('number_of_terms', $enrollment->number_of_terms) }}"
                               min="1" max="4"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', $enrollment->start_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Expected End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected End Date</label>
                        <input type="date" name="expected_end_date"
                               value="{{ old('expected_end_date', $enrollment->expected_end_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Actual End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Actual End Date</label>
                        <input type="date" name="actual_end_date"
                               value="{{ old('actual_end_date', $enrollment->actual_end_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Financial Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Financial Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Fee Structure Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fee Structure Type</label>
                        <select name="fee_structure_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Type</option>
                            @foreach(['nita', 'cdacc', 'school_assessment', 'mixed'] as $type)
                                <option value="{{ $type }}" {{ old('fee_structure_type', $enrollment->fee_structure_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Total Course Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Course Fee (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="total_course_fee"
                                   step="0.01" min="0"
                                   value="{{ old('total_course_fee', $enrollment->total_course_fee) }}"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Amount Paid -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="amount_paid"
                                   step="0.01" min="0"
                                   value="{{ old('amount_paid', $enrollment->amount_paid) }}"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Balance (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Balance (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="text"
                                   value="{{ number_format($enrollment->balance, 2) }}"
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                        </div>
                    </div>

                    <!-- Completion Percentage -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Completion %</label>
                        <div class="relative">
                            <input type="number" name="completion_percentage"
                                   min="0" max="100" step="1"
                                   value="{{ old('completion_percentage', $enrollment->completion_percentage) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <span class="absolute right-3 top-2 text-gray-500">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam & Certificate Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Examination & Certification
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- External Exam Required -->
                    <div>
                        <div class="flex items-center mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="requires_external_exam" value="1"
                                       {{ old('requires_external_exam', $enrollment->requires_external_exam) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Requires external exam</span>
                            </label>
                        </div>
                    </div>

                    <!-- Exam Body -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                        <select name="external_exam_body"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Exam Body</option>
                            <option value="nita" {{ old('external_exam_body', $enrollment->external_exam_body) == 'nita' ? 'selected' : '' }}>NITA</option>
                            <option value="cdacc" {{ old('external_exam_body', $enrollment->external_exam_body) == 'cdacc' ? 'selected' : '' }}>CDACC</option>
                            <option value="knec" {{ old('external_exam_body', $enrollment->external_exam_body) == 'knec' ? 'selected' : '' }}>KNEC</option>
                        </select>
                    </div>

                    <!-- Exam Registration Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Registration #</label>
                        <input type="text" name="exam_registration_number"
                               value="{{ old('exam_registration_number', $enrollment->exam_registration_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Exam Registration Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                        <input type="date" name="exam_registration_date"
                               value="{{ old('exam_registration_date', $enrollment->exam_registration_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Final Grade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Final Grade</label>
                        <input type="text" name="final_grade"
                               value="{{ old('final_grade', $enrollment->final_grade) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Distinction">
                    </div>

                    <!-- Certificate Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate #</label>
                        <input type="text" name="certificate_number"
                               value="{{ old('certificate_number', $enrollment->certificate_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Certificate Issue Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                        <input type="date" name="certificate_issue_date"
                               value="{{ old('certificate_issue_date', $enrollment->certificate_issue_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Class Award -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Class Award</label>
                        <input type="text" name="class_award"
                               value="{{ old('class_award', $enrollment->class_award) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Status & Remarks Card -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-flag text-primary mr-2"></i>
                    Status & Remarks
                </h4>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Enrollment Status</label>
                        <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('status') border-red-500 @enderror"
                                required>
                            @foreach(['registered', 'in_progress', 'completed', 'dropped', 'discontinued', 'suspended', 'deferred', 'transferred'] as $status)
                                <option value="{{ $status }}" {{ old('status', $enrollment->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Active -->
                    <div>
                        <div class="flex items-center mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', $enrollment->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Active Enrollment</span>
                            </label>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <textarea name="remarks"
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('remarks', $enrollment->remarks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
               class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Update Enrollment</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate balance when total fee or amount paid changes
        const totalFeeInput = document.querySelector('input[name="total_course_fee"]');
        const amountPaidInput = document.querySelector('input[name="amount_paid"]');
        const balanceDisplay = document.querySelector('input[readonly]');

        function updateBalance() {
            const totalFee = parseFloat(totalFeeInput.value) || 0;
            const amountPaid = parseFloat(amountPaidInput.value) || 0;
            const balance = totalFee - amountPaid;

            // Update the balance display
            if (balanceDisplay) {
                balanceDisplay.value = balance.toFixed(2);

                // Highlight positive balance
                if (balance > 0) {
                    balanceDisplay.classList.add('text-red-600');
                    balanceDisplay.classList.remove('text-gray-700');
                } else {
                    balanceDisplay.classList.remove('text-red-600');
                    balanceDisplay.classList.add('text-gray-700');
                }
            }
        }

        if (totalFeeInput && amountPaidInput) {
            totalFeeInput.addEventListener('input', updateBalance);
            amountPaidInput.addEventListener('input', updateBalance);
        }

        // Warn if completion percentage is set to 100% but status not completed
        const completionInput = document.querySelector('input[name="completion_percentage"]');
        const statusSelect = document.querySelector('select[name="status"]');

        if (completionInput && statusSelect) {
            completionInput.addEventListener('change', function() {
                if (this.value >= 100 && statusSelect.value !== 'completed') {
                    if (!confirm('Completion is 100%. Do you want to set status to Completed?')) {
                        this.value = 99;
                    } else {
                        statusSelect.value = 'completed';
                    }
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
</style>
@endsection
