@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Exam Registration')
@section('subtitle', 'Update examination registration details')

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
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
            <a href="{{ route('admin.exam-registrations.show', $registration) }}">{{ $registration->registration_number }}</a>
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
    <a href="{{ route('admin.exam-registrations.show', $registration) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Registration</span>
    </a>
    <a href="{{ route('admin.exam-registrations.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registrations</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                <i class="fas fa-file-alt text-primary"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Edit Registration: {{ $registration->registration_number }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $registration->student->full_name ?? 'N/A' }} - {{ $registration->exam_body }} {{ $registration->exam_type }}
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.exam-registrations.update', $registration) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-8">
            <!-- Student & Enrollment (Read-only) -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Student & Enrollment Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <input type="text"
                               value="{{ $registration->student->full_name ?? 'N/A' }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student Number</label>
                        <input type="text"
                               value="{{ $registration->student->student_number ?? 'N/A' }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment</label>
                        <input type="text"
                               value="{{ $registration->enrollment->course->name ?? 'N/A' }} ({{ $registration->enrollment->intake_period ?? '' }} {{ $registration->enrollment->intake_year ?? '' }})"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Exam Details -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Examination Details
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Exam Body -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Body</label>
                        <select name="exam_body" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Exam Body</option>
                            <option value="KNEC" {{ old('exam_body', $registration->exam_body) == 'KNEC' ? 'selected' : '' }}>KNEC</option>
                            <option value="CDACC" {{ old('exam_body', $registration->exam_body) == 'CDACC' ? 'selected' : '' }}>CDACC</option>
                            <option value="NITA" {{ old('exam_body', $registration->exam_body) == 'NITA' ? 'selected' : '' }}>NITA</option>
                            <option value="TVETA" {{ old('exam_body', $registration->exam_body) == 'TVETA' ? 'selected' : '' }}>TVETA</option>
                        </select>
                    </div>

                    <!-- Exam Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Type</label>
                        <input type="text" name="exam_type" required
                               value="{{ old('exam_type', $registration->exam_type) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Registration Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Number</label>
                        <input type="text" name="registration_number" required
                               value="{{ old('registration_number', $registration->registration_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Index Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Index Number</label>
                        <input type="text" name="index_number"
                               value="{{ old('index_number', $registration->index_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Exam Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Exam Date</label>
                        <input type="date" name="exam_date" required
                               value="{{ old('exam_date', $registration->exam_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Exam Time -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Time</label>
                        <input type="text" name="exam_time"
                               value="{{ old('exam_time', $registration->exam_time) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Exam Venue -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exam Venue</label>
                        <input type="text" name="exam_venue"
                               value="{{ old('exam_venue', $registration->exam_venue) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Registration Details -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Registration Details
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Registration Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Registration Date</label>
                        <input type="date" name="registration_date" required
                               value="{{ old('registration_date', $registration->registration_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Status</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="pending" {{ old('status', $registration->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="registered" {{ old('status', $registration->status) == 'registered' ? 'selected' : '' }}>Registered</option>
                            <option value="completed" {{ old('status', $registration->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="deferred" {{ old('status', $registration->status) == 'deferred' ? 'selected' : '' }}>Deferred</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fees -->
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
                                   value="{{ old('exam_fee', $registration->exam_fee ?? 0) }}"
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
                                   value="{{ old('registration_fee', $registration->registration_fee ?? 0) }}"
                                   min="0" step="100"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Total Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Fee (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number" name="total_fee" id="total_fee"
                                   value="{{ old('total_fee', $registration->total_fee ?? 0) }}"
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
                                   value="{{ old('fee_paid', $registration->fee_paid ?? 0) }}"
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
                                   value="{{ number_format(($registration->total_fee - $registration->fee_paid), 2) }}"
                                   class="w-full pl-12 pr-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result (If already entered) -->
            @if($registration->result)
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-star text-primary mr-2"></i>
                    Exam Result
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Result</label>
                        <input type="text"
                               value="{{ $registration->result }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                    @if($registration->grade)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                        <input type="text"
                               value="{{ $registration->grade }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                    @endif
                    @if($registration->certificate_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number</label>
                        <input type="text"
                               value="{{ $registration->certificate_number }}"
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                               readonly>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Remarks -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Remarks
                </h4>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Remarks / Notes</label>
                    <textarea name="remarks" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('remarks', $registration->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.exam-registrations.show', $registration) }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Update Registration
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fee calculations
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
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }
</style>
@endsection
