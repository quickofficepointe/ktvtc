@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Record Payment')
@section('subtitle', 'Record a new fee payment')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Finance</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Payments</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Record Payment</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.fee-payments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Payments</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Record New Payment</h3>
                <p class="text-sm text-gray-600 mt-1">Enter payment details below</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fields marked with <span class="text-red-500">*</span> are required
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.fee-payments.store') }}" method="POST" id="paymentForm">
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
                            Search Student
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

                    <!-- Enrollment Selection (Dynamic) -->
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
                </div>
            </div>

            <!-- ============ PAYMENT DETAILS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-credit-card text-primary mr-2"></i>
                    Payment Details
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Amount (KES)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                            <input type="number"
                                   name="amount"
                                   id="amount"
                                   value="{{ old('amount') }}"
                                   min="1"
                                   step="100"
                                   required
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Payment Method</label>
                        <select name="payment_method" id="payment_method" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="mpesa" {{ old('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="kcb" {{ old('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                            <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Transaction Code (Dynamic based on method) -->
                    <div id="transaction_code_container">
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="transaction_code_label">Transaction Code</label>
                        <input type="text"
                               name="transaction_code"
                               id="transaction_code"
                               value="{{ old('transaction_code') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Enter transaction reference">
                    </div>

                    <!-- Payment Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Payment Date</label>
                        <input type="date"
                               name="payment_date"
                               value="{{ old('payment_date', date('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- For Month (Optional - for CSV imports) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment For Month</label>
                        <select name="payment_for_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">-- Not specified --</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                <option value="{{ $month }}" {{ old('payment_for_month') == $month ? 'selected' : '' }}>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">For tracking CSV imports only</p>
                    </div>
                </div>
            </div>

            <!-- ============ PAYER INFORMATION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user text-primary mr-2"></i>
                    Payer Information (Optional)
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payer Name</label>
                        <input type="text"
                               name="payer_name"
                               value="{{ old('payer_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Full name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payer Phone</label>
                        <input type="text"
                               name="payer_phone"
                               value="{{ old('payer_phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Phone number">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payer Type</label>
                        <select name="payer_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="student">Student</option>
                            <option value="parent">Parent/Guardian</option>
                            <option value="sponsor">Sponsor</option>
                            <option value="employer">Employer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ============ NOTES ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Additional Information
                </h4>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Any additional notes about this payment...">{{ old('notes') }}</textarea>
                </div>

                <!-- Import Source (Hidden - for tracking) -->
                <input type="hidden" name="import_source" value="manual">
            </div>

            <!-- ============ PAYMENT SUMMARY ============ -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-md font-medium text-blue-800 mb-4 flex items-center">
                    <i class="fas fa-file-invoice text-blue-600 mr-2"></i>
                    Payment Summary
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-600">Student</p>
                        <p class="text-sm font-bold text-gray-800" id="summaryStudent">Not selected</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-600">Current Balance</p>
                        <p class="text-sm font-bold text-gray-800" id="summaryBalance">KES 0.00</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-600">New Balance</p>
                        <p class="text-sm font-bold text-green-600" id="summaryNewBalance">KES 0.00</p>
                    </div>
                </div>
            </div>

            <!-- ============ QUICK TIPS ============ -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-amber-600 mt-0.5 mr-2"></i>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Quick Tips</p>
                        <ul class="mt-1 text-xs text-amber-700 space-y-1">
                            <li>• Receipt number will be auto-generated</li>
                            <li>• For M-Pesa payments, enter the transaction code (e.g., PPI23A1B2C)</li>
                            <li>• For bank transfers, enter the reference number</li>
                            <li>• The student's balance will be updated automatically</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.fee-payments.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Record Payment
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSelect = document.getElementById('student_id');
        const enrollmentSelect = document.getElementById('enrollment_id');
        const enrollmentInfo = document.getElementById('enrollmentInfo');
        const amountInput = document.getElementById('amount');
        const summaryStudent = document.getElementById('summaryStudent');
        const summaryBalance = document.getElementById('summaryBalance');
        const summaryNewBalance = document.getElementById('summaryNewBalance');

        // Payment method dynamic label
        const paymentMethod = document.getElementById('payment_method');
        const transactionCodeContainer = document.getElementById('transaction_code_container');
        const transactionCodeLabel = document.getElementById('transaction_code_label');
        const transactionCode = document.getElementById('transaction_code');

        paymentMethod.addEventListener('change', function() {
            const method = this.value;
            if (method === 'mpesa') {
                transactionCodeLabel.textContent = 'M-Pesa Transaction Code *';
                transactionCode.placeholder = 'e.g., PPI23A1B2C';
                transactionCode.setAttribute('required', 'required');
            } else if (method === 'bank') {
                transactionCodeLabel.textContent = 'Bank Reference Number *';
                transactionCode.placeholder = 'e.g., TRX123456';
                transactionCode.setAttribute('required', 'required');
            } else if (method === 'kcb') {
                transactionCodeLabel.textContent = 'KCB Transaction Code *';
                transactionCode.placeholder = 'e.g., KCB123456';
                transactionCode.setAttribute('required', 'required');
            } else {
                transactionCodeLabel.textContent = 'Transaction Code';
                transactionCode.placeholder = 'Enter reference (optional)';
                transactionCode.removeAttribute('required');
            }
        });

        // Load enrollments when student is selected
        studentSelect.addEventListener('change', function() {
            const studentId = this.value;
            enrollmentSelect.innerHTML = '<option value="">Loading enrollments...</option>';

            if (studentId) {
                const selected = this.options[this.selectedIndex];
                summaryStudent.textContent = selected.text.split(' - ')[0];

                fetch(`/admin/api/students/${studentId}/enrollments`)
                    .then(response => response.json())
                    .then(data => {
                        enrollmentSelect.innerHTML = '<option value="">Select Enrollment</option>';
                        if (data.length > 0) {
                            data.forEach(enrollment => {
                                const option = document.createElement('option');
                                option.value = enrollment.id;
                                option.textContent = `${enrollment.course} (Balance: KES ${enrollment.balance})`;
                                option.dataset.balance = enrollment.balance;
                                option.dataset.course = enrollment.course;
                                enrollmentSelect.appendChild(option);
                            });
                        } else {
                            enrollmentSelect.innerHTML += '<option value="" disabled>No active enrollments found</option>';
                        }
                    });
            } else {
                enrollmentSelect.innerHTML = '<option value="">-- First select a student --</option>';
                summaryStudent.textContent = 'Not selected';
            }
        });

        // Update balance when enrollment is selected
        enrollmentSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value && selected.dataset.balance) {
                const balance = parseFloat(selected.dataset.balance);
                summaryBalance.textContent = `KES ${balance.toFixed(2)}`;
                updateNewBalance();
            } else {
                summaryBalance.textContent = 'KES 0.00';
            }
        });

        // Update new balance when amount changes
        amountInput.addEventListener('input', updateNewBalance);

        function updateNewBalance() {
            const currentBalance = parseFloat(summaryBalance.textContent.replace('KES ', '')) || 0;
            const amount = parseFloat(amountInput.value) || 0;
            const newBalance = currentBalance - amount;
            summaryNewBalance.textContent = `KES ${newBalance.toFixed(2)}`;

            if (newBalance < 0) {
                summaryNewBalance.classList.add('text-red-600');
                summaryNewBalance.classList.remove('text-green-600');
            } else {
                summaryNewBalance.classList.remove('text-red-600');
                summaryNewBalance.classList.add('text-green-600');
            }
        }

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
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
            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount');
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
</style>
@endsection
