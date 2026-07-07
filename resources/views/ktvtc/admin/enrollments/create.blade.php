@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Create Enrollment')
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--classic .select2-selection--single {
        height: 42px;
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
    }
    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }
    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-container--classic .select2-dropdown {
        border-color: #d1d5db;
        border-radius: 8px;
    }
    .select2-container--classic .select2-search--dropdown .select2-search__field {
        border-radius: 6px;
        border-color: #d1d5db;
    }
    .select2-container--classic.select2-container--open .select2-dropdown {
        border-color: #B91C1C;
    }
    .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #B91C1C;
    }
    .fee-breakdown-popup {
        display: none;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .fee-breakdown-popup.show {
        display: block;
    }
    .fee-breakdown-item {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    }
    .fee-breakdown-item:last-child {
        border-bottom: none;
    }
    .fee-breakdown-total {
        font-weight: bold;
        border-top: 2px solid #e5e7eb;
        padding-top: 8px;
        margin-top: 4px;
    }
    .fee-breakdown-total .amount {
        color: #B91C1C;
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Student Enrollment</h3>
            <p class="text-sm text-gray-600 mt-1">Enroll a student in a course program</p>
        </div>

        <form action="{{ route('admin.enrollments.store') }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">
                <!-- 🔥 STUDENT SELECTION - SEARCHABLE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Student <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="student_id" id="student_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_id') border-red-500 @enderror">
                            <option value="">-- Search for a student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->full_name }} ({{ $student->student_number ?? 'No ID' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    @error('student_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Only students who are not currently enrolled in an active course are shown.
                    </p>
                </div>

                <!-- 🔥 Course Selection - WITH FEE DATA -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Course <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="course_id" id="course_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('course_id') border-red-500 @enderror">
                            <option value="">-- Search for a course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                        data-fee-breakdown='@json($course->fee_breakdown ?? [])'
                                        data-total-fee="{{ $course->total_fee ?? 0 }}"
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code ?? 'N/A' }}) - KES {{ number_format($course->total_fee ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    @error('course_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 🔥 Fee Breakdown Display -->
                <div id="feeBreakdown" class="fee-breakdown-popup">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Fee Breakdown</h4>
                    <div id="feeBreakdownItems"></div>
                    <div class="fee-breakdown-total flex justify-between">
                        <span>Total:</span>
                        <span class="amount font-bold" id="feeTotalDisplay">KES 0.00</span>
                    </div>
                </div>

                <!-- Campus -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('campus_id') border-red-500 @enderror">
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

                <!-- Financial Information -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Fees <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">KES</span>
                        </div>
                        <input type="number" name="total_fees" id="total_fees" value="{{ old('total_fees') }}" step="0.01" min="0" required
                               class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('total_fees') border-red-500 @enderror">
                    </div>
                    @error('total_fees')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Fees will be auto-filled when you select a course. You can manually adjust if needed.
                    </p>
                </div>

                <!-- Intake Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Intake Year <span class="text-red-500">*</span></label>
                        <select name="intake_year" id="intake_year" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_year') border-red-500 @enderror">
                            <option value="">Select Year</option>
                            @php
                                $currentYear = date('Y');
                                for($y = $currentYear - 1; $y <= $currentYear + 3; $y++) {
                                    $selected = old('intake_year', $currentYear) == $y ? 'selected' : '';
                                    echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
                                }
                            @endphp
                        </select>
                        @error('intake_year')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Intake Month <span class="text-red-500">*</span></label>
                        <select name="intake_month" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('intake_month') border-red-500 @enderror">
                            <option value="">Select Month</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                <option value="{{ $month }}" {{ old('intake_month') == $month ? 'selected' : '' }}>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                        @error('intake_month')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="enrollment_date" value="{{ old('enrollment_date', date('Y-m-d')) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('enrollment_date') border-red-500 @enderror">
                        @error('enrollment_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected End Date</label>
                        <input type="date" name="expected_end_date" value="{{ old('expected_end_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('expected_end_date') border-red-500 @enderror">
                        @error('expected_end_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status & Study Mode -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="dropped" {{ old('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="graduated" {{ old('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode <span class="text-red-500">*</span></label>
                        <select name="study_mode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('study_mode') border-red-500 @enderror">
                            <option value="full_time" {{ old('study_mode') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ old('study_mode') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="evening" {{ old('study_mode') == 'evening' ? 'selected' : '' }}>Evening</option>
                            <option value="weekend" {{ old('study_mode') == 'weekend' ? 'selected' : '' }}>Weekend</option>
                            <option value="online" {{ old('study_mode') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="hybrid" {{ old('study_mode') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        @error('study_mode')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Student Type & Sponsorship -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student Type <span class="text-red-500">*</span></label>
                        <select name="student_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_type') border-red-500 @enderror">
                            <option value="new" {{ old('student_type') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="continuing" {{ old('student_type') == 'continuing' ? 'selected' : '' }}>Continuing</option>
                            <option value="alumnus" {{ old('student_type') == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                            <option value="transfer" {{ old('student_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                        @error('student_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sponsorship Type <span class="text-red-500">*</span></label>
                        <select name="sponsorship_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('sponsorship_type') border-red-500 @enderror">
                            <option value="self" {{ old('sponsorship_type') == 'self' ? 'selected' : '' }}>Self Sponsored</option>
                            <option value="sponsored" {{ old('sponsorship_type') == 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                            <option value="government" {{ old('sponsorship_type') == 'government' ? 'selected' : '' }}>Government</option>
                            <option value="scholarship" {{ old('sponsorship_type') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                            <option value="company" {{ old('sponsorship_type') == 'company' ? 'selected' : '' }}>Company</option>
                        </select>
                        @error('sponsorship_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Duration & Exam -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                        <input type="number" name="duration_months" value="{{ old('duration_months') }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('duration_months') border-red-500 @enderror">
                        @error('duration_months')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requires External Exam</label>
                        <select name="requires_external_exam" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="0" {{ old('requires_external_exam') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('requires_external_exam') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                        @error('requires_external_exam')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                    <textarea name="remarks" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 🔥 Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Student Number Formatting</h4>
                            <p class="text-xs text-blue-700 mt-1">
                                When you enroll a student, their student number will be automatically formatted as:
                                <strong class="text-primary">COURSECODE/STUDENT_NUMBER/YEAR</strong>
                            </p>
                            <p class="text-xs text-blue-700 mt-1">
                                Example: If the student number is <strong>947</strong> and they enroll in <strong>ICT</strong> for <strong>2026</strong>,
                                the new student number will be <strong>ICT/947/2026</strong>.
                            </p>
                            <p class="text-xs text-blue-700 mt-1">
                                <i class="fas fa-arrow-up text-green-600 mr-1"></i>
                                Student numbers increment from <strong>947</strong> automatically.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // 🔥 Initialize Select2 for Student dropdown with AJAX
        $('#student_id').select2({
            placeholder: 'Search for a student...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("admin.enrollments.api.students") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        exclude_enrolled: true
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(student) {
                            return {
                                id: student.id,
                                text: student.full_name + ' (' + (student.student_number || 'No ID') + ')'
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // 🔥 Initialize Select2 for Course dropdown
        $('#course_id').select2({
            placeholder: 'Search for a course...',
            allowClear: true,
            width: '100%'
        });

        // 🔥 Course selection handler - Auto-fill fees
        $('#course_id').on('change', function() {
            const selected = $(this).find(':selected');
            const feeBreakdown = selected.data('fee-breakdown');
            const totalFee = selected.data('total-fee') || 0;

            // Auto-fill total fees
            $('#total_fees').val(totalFee);

            // Display fee breakdown
            if (feeBreakdown && Object.keys(feeBreakdown).length > 0) {
                let html = '';
                let total = 0;

                for (const [key, value] of Object.entries(feeBreakdown)) {
                    let amount = 0;
                    let description = '';

                    if (typeof value === 'object' && value !== null) {
                        amount = value.amount || 0;
                        description = value.description || '';
                    } else {
                        amount = parseFloat(value) || 0;
                    }

                    total += amount;
                    const formattedAmount = 'KES ' + amount.toFixed(2);

                    html += `
                        <div class="fee-breakdown-item">
                            <span>${key}${description ? ' <span class="text-gray-500 text-xs">' + description + '</span>' : ''}</span>
                            <span class="font-medium">${formattedAmount}</span>
                        </div>
                    `;
                }

                $('#feeBreakdownItems').html(html);
                $('#feeTotalDisplay').text('KES ' + total.toFixed(2));
                $('#feeBreakdown').addClass('show');
            } else {
                $('#feeBreakdown').removeClass('show');
                $('#feeBreakdownItems').html('');
                $('#feeTotalDisplay').text('KES 0.00');
            }

            // Update preview
            updatePreview();
        });

        // 🔥 Preview student number format when course is selected
        function updatePreview() {
            const courseText = $('#course_id option:selected').text();
            const courseCode = courseText.split('(').pop()?.replace(')', '').trim() || 'COURSE';

            // Get the student number from the student dropdown
            const studentText = $('#student_id option:selected').text();
            const studentNumber = studentText.match(/\(([^)]+)\)/)?.[1] || '947';

            // Get the year
            const year = $('#intake_year').val() || new Date().getFullYear();

            if (courseCode && studentNumber) {
                $('#previewStudentNumber').text(courseCode.toUpperCase() + '/' + studentNumber + '/' + year);
                $('#coursePreview').removeClass('hidden');
            } else {
                $('#coursePreview').addClass('hidden');
            }
        }

        // 🔥 Update preview when year changes
        $('#intake_year').on('change', updatePreview);

        // 🔥 Update preview when student changes
        $('#student_id').on('change', updatePreview);

        // 🔥 Trigger initial load if course is pre-selected
        if ($('#course_id').val()) {
            $('#course_id').trigger('change');
        }

        // Initial preview if values exist
        updatePreview();

        // 🔥 Manual total fees input - show warning if different from course fee
        $('#total_fees').on('change', function() {
            const selected = $('#course_id').find(':selected');
            const courseFee = selected.data('total-fee') || 0;
            const enteredFee = parseFloat($(this).val()) || 0;

            if (courseFee > 0 && enteredFee !== courseFee) {
                // Show a subtle warning but don't block submission
                const warning = `
                    <div class="mt-1 text-xs text-amber-600 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Fee differs from course fee (KES ${courseFee.toFixed(2)}).
                        This will be recorded in the audit trail.
                    </div>
                `;

                // Remove existing warning
                $(this).siblings('.fee-warning').remove();
                $(this).after(`<div class="fee-warning mt-1 text-xs text-amber-600 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Fee differs from course fee (KES ${courseFee.toFixed(2)}).
                    This will be recorded in the audit trail.
                </div>`);
            } else {
                $(this).siblings('.fee-warning').remove();
            }
        });
    });
</script>
@endpush
