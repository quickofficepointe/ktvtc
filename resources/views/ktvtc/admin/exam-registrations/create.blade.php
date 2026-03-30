@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'New Exam Registration')
@section('subtitle', 'Register a student for external examination')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Examinations</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exam Registrations</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">New Registration</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.exam-registrations.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registrations</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">New Exam Registration</h3>
                <p class="text-sm text-gray-600 mt-1">Register a student for external examination</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fields marked with <span class="text-red-500">*</span> are required
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.exam-registrations.store') }}" method="POST" id="registrationForm">
        @csrf

        <div class="p-6 space-y-8">
            <!-- ============ STUDENT SELECTION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Student Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Select Student
                        </label>
                        <div class="relative">
                            <select name="student_id" id="student_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">-- Search by name or student number --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                            data-name="{{ $student->full_name }}"
                                            data-number="{{ $student->student_number }}">
                                        {{ $student->full_name }} - {{ $student->student_number ?? 'No ID' }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Enrollment Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                            Select Enrollment
                        </label>
                        <select name="enrollment_id" id="enrollment_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">-- First select a student --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500" id="enrollmentInfo"></p>
                    </div>

                    <!-- Student Preview -->
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
                                    <div class="mt-1 text-xs text-blue-600">
                                        <p id="previewNumber"></p>
                                        <p id="previewCourse"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ EXAM DETAILS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Examination Details
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Exam Body -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Body</label>
                        <select name="exam_body" id="exam_body" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Exam Body</option>
                            <option value="KNEC" {{ old('exam_body') == 'KNEC' ? 'selected' : '' }}>KNEC</option>
                            <option value="CDACC" {{ old('exam_body') == 'CDACC' ? 'selected' : '' }}>CDACC</option>
                            <option value="NITA" {{ old('exam_body') == 'NITA' ? 'selected' : '' }}>NITA</option>
                            <option value="TVETA" {{ old('exam_body') == 'TVETA' ? 'selected' : '' }}>TVETA</option>
                        </select>
                    </div>

                    <!-- Exam Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Type</label>
                        <input type="text" name="exam_type" id="exam_type" required
                               value="{{ old('exam_type') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Craft Certificate, Diploma">
                    </div>

                    <!-- Registration Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Number</label>
                        <input type="text" name="registration_number" required
                               value="{{ old('registration_number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., KNEC/2024/12345">
                    </div>

                    <!-- Index Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Index Number</label>
                        <input type="text" name="index_number"
                               value="{{ old('index_number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 12345678">
                    </div>

                    <!-- Exam Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Date</label>
                        <input type="date" name="exam_date" required
                               value="{{ old('exam_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Exam Time -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Time</label>
                        <input type="text" name="exam_time"
                               value="{{ old('exam_time') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 8:00 AM - 11:00 AM">
                    </div>

                    <!-- Exam Venue -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Venue</label>
                        <input type="text" name="exam_venue"
                               value="{{ old('exam_venue') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Main Hall, Room 101">
                    </div>
                </div>
            </div>

            <!-- ============ REGISTRATION DETAILS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Registration Details
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Registration Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Date</label>
                        <input type="date" name="registration_date" required
                               value="{{ old('registration_date', date('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Status</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="registered" {{ old('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ============ FEES ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Examination Fees
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Exam Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Fee (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="exam_fee" id="exam_fee"
                                   value="{{ old('exam_fee', 0) }}"
                                   min="0" step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Registration Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Fee (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="registration_fee"
                                   value="{{ old('registration_fee', 0) }}"
                                   min="0" step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Total Fee (Calculated) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Fee (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="total_fee" id="total_fee"
                                   value="{{ old('total_fee', 0) }}"
                                   readonly
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        </div>
                    </div>

                    <!-- Fee Paid -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="fee_paid" id="fee_paid"
                                   value="{{ old('fee_paid', 0) }}"
                                   min="0" step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Balance -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Balance (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="text" id="balance" readonly
                                   value="0.00"
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ REMARKS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Additional Information
                </h4>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Remarks / Notes</label>
                    <textarea name="remarks" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Any additional notes about this registration...">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <!-- ============ QUICK TIPS ============ -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-amber-600 mt-0.5 mr-2"></i>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Quick Tips</p>
                        <ul class="mt-1 text-xs text-amber-700 space-y-1">
                            <li>• Registration number should match the one from the exam body</li>
                            <li>• Index number is optional but recommended for tracking</li>
                            <li>• Exam date is required for printing exam slips</li>
                            <li>• Fee fields help track payment status for exam registration</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.exam-registrations.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Register for Exam
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
        const enrollmentSelect = document.getElementById('enrollment_id');
        const studentPreview = document.getElementById('studentPreview');
        const previewName = document.getElementById('previewName');
        const previewNumber = document.getElementById('previewNumber');
        const previewCourse = document.getElementById('previewCourse');

        studentSelect.addEventListener('change', function() {
            const studentId = this.value;
            enrollmentSelect.innerHTML = '<option value="">Loading enrollments...</option>';

            if (studentId) {
                const selected = this.options[this.selectedIndex];
                previewName.textContent = selected.dataset.name;
                previewNumber.textContent = `Student #: ${selected.dataset.number || 'Not assigned'}`;
                studentPreview.classList.remove('hidden');

                fetch(`/admin/api/students/${studentId}/enrollments`)
                    .then(response => response.json())
                    .then(data => {
                        enrollmentSelect.innerHTML = '<option value="">Select Enrollment</option>';
                        if (data.length > 0) {
                            data.forEach(enrollment => {
                                const option = document.createElement('option');
                                option.value = enrollment.id;
                                option.textContent = `${enrollment.course} (${enrollment.intake})`;
                                option.dataset.course = enrollment.course;
                                enrollmentSelect.appendChild(option);
                            });
                        } else {
                            enrollmentSelect.innerHTML += '<option value="" disabled>No active enrollments found</option>';
                        }
                    });
            } else {
                enrollmentSelect.innerHTML = '<option value="">-- First select a student --</option>';
                studentPreview.classList.add('hidden');
            }
        });

        enrollmentSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value && selected.dataset.course) {
                previewCourse.textContent = `Course: ${selected.dataset.course}`;
            } else {
                previewCourse.textContent = '';
            }
        });

        // ============ FEE CALCULATIONS ============
        const examFee = document.getElementById('exam_fee');
        const regFee = document.querySelector('input[name="registration_fee"]');
        const totalFee = document.getElementById('total_fee');
        const feePaid = document.getElementById('fee_paid');
        const balance = document.getElementById('balance');

        function calculateTotals() {
            const exam = parseFloat(examFee.value) || 0;
            const reg = parseFloat(regFee?.value) || 0;
            const total = exam + reg;
            totalFee.value = total;

            const paid = parseFloat(feePaid.value) || 0;
            const bal = total - paid;
            balance.value = bal.toFixed(2);

            if (bal > 0) {
                balance.classList.add('text-red-600');
                balance.classList.remove('text-gray-700');
            } else {
                balance.classList.remove('text-red-600');
                balance.classList.add('text-gray-700');
            }
        }

        examFee.addEventListener('input', calculateTotals);
        if (regFee) regFee.addEventListener('input', calculateTotals);
        feePaid.addEventListener('input', calculateTotals);

        // ============ FORM VALIDATION ============
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!studentSelect.value) {
                e.preventDefault();
                alert('Please select a student');
                return false;
            }
            if (!enrollmentSelect.value) {
                e.preventDefault();
                alert('Please select an enrollment');
                return false;
            }
        });
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
