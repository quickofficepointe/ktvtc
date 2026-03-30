@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Enrollment')
@section('subtitle', 'Update enrollment information')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
            <a href="{{ route('admin.enrollments.show', $enrollment) }}">{{ $enrollment->enrollment_number ?? 'Enrollment' }}</a>
        </span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Edit</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.enrollments.show', $enrollment) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Enrollment</span>
    </a>
    <a href="{{ route('admin.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                <i class="fas fa-book-open text-primary"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Edit Enrollment: {{ $enrollment->enrollment_number ?? 'N/A' }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $enrollment->student_name ?? ($enrollment->student->full_name ?? 'N/A') }} -
                    {{ $enrollment->course_name ?? ($enrollment->course->name ?? 'N/A') }}
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.enrollments.update', $enrollment) }}" method="POST" id="enrollmentForm">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-8">
            <!-- Student & Course (Read-only) -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Student & Course Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <input type="text"
                               value="{{ $enrollment->student_name ?? ($enrollment->student->full_name ?? 'N/A') }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                        <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                        <input type="text"
                               value="{{ $enrollment->course_name ?? ($enrollment->course->name ?? 'N/A') }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                        <input type="hidden" name="course_id" value="{{ $enrollment->course_id }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Number</label>
                        <input type="text"
                               value="{{ $enrollment->enrollment_number ?? 'N/A' }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono"
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Intake & Mode -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Intake & Mode
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Intake Month -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Month</label>
                        <select name="intake_month" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                <option value="{{ $month }}" {{ old('intake_month', $enrollment->intake_month) == $month ? 'selected' : '' }}>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Intake Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Intake Year</label>
                        <select name="intake_year" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                        <select name="study_mode" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                        <select name="student_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                        <select name="sponsorship_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            @foreach(['self', 'sponsored', 'government', 'scholarship', 'company'] as $type)
                                <option value="{{ $type }}" {{ old('sponsorship_type', $enrollment->sponsorship_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Campus -->
                    @if(auth()->user()->role == 2)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                        <select name="campus_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Campus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" {{ old('campus_id', $enrollment->campus_id) == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="campus_id" value="{{ $enrollment->campus_id }}">
                    @endif
                </div>
            </div>

            <!-- Financial -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Financial Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Total Fees (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number"
                                   name="total_fees"
                                   id="total_fees"
                                   value="{{ old('total_fees', $enrollment->total_fees) }}"
                                   min="0"
                                   step="100"
                                   required
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number"
                                   name="amount_paid"
                                   id="amount_paid"
                                   value="{{ old('amount_paid', $enrollment->amount_paid) }}"
                                   min="0"
                                   step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Balance (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="text"
                                   id="balance"
                                   readonly
                                   value="{{ number_format($enrollment->balance, 2) }}"
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="requires_external_exam"
                               id="requires_external_exam"
                               value="1"
                               {{ old('requires_external_exam', $enrollment->requires_external_exam) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="requires_external_exam" class="ml-2 text-sm text-gray-700">
                            Requires External Examination
                        </label>
                    </div>
                </div>
                <div id="exam_body_container" class="mt-4 {{ !old('requires_external_exam', $enrollment->requires_external_exam) ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                            <select name="exam_body" id="exam_body"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Exam Body</option>
                                <option value="KNEC" {{ old('exam_body', $enrollment->exam_body) == 'KNEC' ? 'selected' : '' }}>KNEC</option>
                                <option value="NITA" {{ old('exam_body', $enrollment->exam_body) == 'NITA' ? 'selected' : '' }}>NITA</option>
                                <option value="CDACC" {{ old('exam_body', $enrollment->exam_body) == 'CDACC' ? 'selected' : '' }}>CDACC</option>
                                <option value="TVETA" {{ old('exam_body', $enrollment->exam_body) == 'TVETA' ? 'selected' : '' }}>TVETA</option>
                                <option value="OTHER" {{ old('exam_body', $enrollment->exam_body) == 'OTHER' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates & Duration -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Dates & Duration
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Enrollment Date</label>
                        <input type="date"
                               name="enrollment_date"
                               value="{{ old('enrollment_date', $enrollment->enrollment_date?->format('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                        <input type="number"
                               name="duration_months"
                               id="duration_months"
                               value="{{ old('duration_months', $enrollment->duration_months) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date', $enrollment->start_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected End Date</label>
                        <input type="date"
                               name="expected_end_date"
                               id="expected_end_date"
                               value="{{ old('expected_end_date', $enrollment->expected_end_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Actual End Date</label>
                        <input type="date"
                               name="actual_end_date"
                               value="{{ old('actual_end_date', $enrollment->actual_end_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Status & Remarks -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-flag text-primary mr-2"></i>
                    Status & Remarks
                </h4>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Status</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            @foreach(['active', 'completed', 'dropped', 'suspended', 'pending'] as $status)
                                <option value="{{ $status }}" {{ old('status', $enrollment->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Code</label>
                        <input type="text"
                               name="legacy_code"
                               value="{{ old('legacy_code', $enrollment->legacy_code) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., HDBT/021/2021">
                    </div>
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
            <a href="{{ route('admin.enrollments.show', $enrollment) }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Update Enrollment
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalFees = document.getElementById('total_fees');
        const amountPaid = document.getElementById('amount_paid');
        const balance = document.getElementById('balance');

        function calculateBalance() {
            const total = parseFloat(totalFees.value) || 0;
            const paid = parseFloat(amountPaid.value) || 0;
            const bal = total - paid;
            balance.value = 'KES ' + bal.toFixed(2);

            if (bal > 0) {
                balance.classList.add('text-red-600');
                balance.classList.remove('text-gray-700');
            } else {
                balance.classList.remove('text-red-600');
                balance.classList.add('text-gray-700');
            }
        }

        totalFees.addEventListener('input', calculateBalance);
        amountPaid.addEventListener('input', calculateBalance);

        // Exam toggle
        const examCheckbox = document.getElementById('requires_external_exam');
        const examContainer = document.getElementById('exam_body_container');
        const examSelect = document.getElementById('exam_body');

        examCheckbox.addEventListener('change', function() {
            if (this.checked) {
                examContainer.classList.remove('hidden');
                examSelect.setAttribute('required', 'required');
            } else {
                examContainer.classList.add('hidden');
                examSelect.removeAttribute('required');
                examSelect.value = '';
            }
        });

        // Date calculations
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('expected_end_date');
        const duration = document.getElementById('duration_months');

        if (startDate && duration) {
            startDate.addEventListener('change', function() {
                if (this.value && duration.value) {
                    const start = new Date(this.value);
                    start.setMonth(start.getMonth() + parseInt(duration.value));
                    endDate.value = start.toISOString().split('T')[0];
                }
            });

            duration.addEventListener('input', function() {
                if (startDate.value && this.value) {
                    const start = new Date(startDate.value);
                    start.setMonth(start.getMonth() + parseInt(this.value));
                    endDate.value = start.toISOString().split('T')[0];
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
