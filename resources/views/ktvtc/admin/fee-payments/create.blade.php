@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Record Fee Payment')
@section('subtitle', 'Record a new fee payment')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Management</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
            <a href="{{ route('admin.fees.payments.index') }}">Fee Payments</a>
        </span>
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
    <a href="{{ route('admin.fees.payments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Payments</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Record New Fee Payment</h2>
            <p class="text-sm text-gray-600 mt-1">Fill in the payment details below</p>
        </div>

        <form id="paymentForm" method="POST" action="{{ route('admin.fees.payments.store') }}" class="p-6">
            @csrf

            <div class="space-y-8">
                <!-- Student Selection Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-graduate text-blue-600 mr-2"></i>
                        Student Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Student <span class="text-red-500">*</span>
                            </label>
                            <select name="student_id" id="studentSelect" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                            data-phone="{{ $student->phone }}"
                                            data-email="{{ $student->email }}"
                                            {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }}
                                        @if($student->student_number)
                                            ({{ $student->student_number }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Student Details Display -->
                        <div id="studentDetails" class="hidden">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Phone</p>
                                        <p id="studentPhone" class="font-medium text-gray-800">-</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Email</p>
                                        <p id="studentEmail" class="font-medium text-gray-800">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Fees Section -->
                <div id="pendingFeesSection" class="hidden">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>
                        Pending Fees
                    </h3>

                    <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Invoice</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Course</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Due Date</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Total Amount</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Paid</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Balance</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-600 uppercase">Select</th>
                                </tr>
                            </thead>
                            <tbody id="pendingFeesBody" class="divide-y divide-gray-200">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                        <div id="noPendingFees" class="p-8 text-center hidden">
                            <i class="fas fa-check-circle text-green-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No pending fees found for this student</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-money-check-alt text-purple-600 mr-2"></i>
                        Payment Details
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Amount (KES) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount" id="paymentAmount" required step="0.01" min="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00">
                            <p class="text-xs text-gray-500 mt-1">Maximum payable: <span id="maxAmount" class="font-medium">KES 0.00</span></p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="paymentMethod" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">-- Select Payment Method --</option>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="payment_date" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('payment_date', date('Y-m-d')) }}">
                        </div>

                        <!-- Payment Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="payment_time" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('payment_time', date('H:i')) }}">
                        </div>
                    </div>
                </div>

                <!-- Payer Information -->
                <div id="payerSection">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                        Payer Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payer Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="payer_name" required maxlength="255"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Enter payer's full name"
                                   value="{{ old('payer_name') }}">
                        </div>

                        <!-- Payer Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer Type <span class="text-red-500">*</span>
                            </label>
                            <select name="payer_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">-- Select Payer Type --</option>
                                @foreach($payerTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('payer_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payer Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer Phone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="payer_phone" required maxlength="20"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="07XXXXXXXX"
                                   value="{{ old('payer_phone') }}">
                        </div>

                        <!-- Payer Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer Email
                            </label>
                            <input type="email" name="payer_email" maxlength="255"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="payer@example.com"
                                   value="{{ old('payer_email') }}">
                        </div>

                        <!-- Payer ID Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer ID Number
                            </label>
                            <input type="text" name="payer_id_number" maxlength="50"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="National ID/Passport"
                                   value="{{ old('payer_id_number') }}">
                        </div>

                        <!-- Payer Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payer Address
                            </label>
                            <textarea name="payer_address" rows="2" maxlength="500"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Payer's physical address">{{ old('payer_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Method-Specific Details -->
                <div id="methodDetailsSection">
                    <!-- KCB STK Push Details -->
                    <div id="kcbDetails" class="hidden">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-mobile-alt text-purple-600 mr-2"></i>
                            KCB STK Push Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="kcb_transaction_code" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., TQ123456789"
                                       value="{{ old('kcb_transaction_code') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="kcb_phone_number" maxlength="20"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="07XXXXXXXX"
                                       value="{{ old('kcb_phone_number') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Account Number
                                </label>
                                <input type="text" name="kcb_account_number" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Account number if any"
                                       value="{{ old('kcb_account_number') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Paybill Details -->
                    <div id="paybillDetails" class="hidden">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-wifi text-blue-600 mr-2"></i>
                            Paybill Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Paybill Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="paybill_number" maxlength="20"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., 123456"
                                       value="{{ old('paybill_number') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Account Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="paybill_account_number" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Student number/ID"
                                       value="{{ old('paybill_account_number') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="paybill_transaction_code" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., TQ123456789"
                                       value="{{ old('paybill_transaction_code') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Bank Deposit Details -->
                    <div id="bankDepositDetails" class="hidden">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-university text-green-600 mr-2"></i>
                            Bank Deposit Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Bank Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bank_name" maxlength="100"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., KCB, Equity, Co-op"
                                       value="{{ old('bank_name') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Bank Branch <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bank_branch" maxlength="100"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Branch name"
                                       value="{{ old('bank_branch') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Deposit Slip Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="deposit_slip_number" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Slip reference number"
                                       value="{{ old('deposit_slip_number') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Deposit Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="deposit_date"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('deposit_date') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Cash Details -->
                    <div id="cashDetails" class="hidden">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-money-bill-wave text-yellow-600 mr-2"></i>
                            Cash Payment Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Cash Receipt Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="cash_receipt_number" maxlength="50"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Receipt number"
                                       value="{{ old('cash_receipt_number') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        Additional Information
                    </h3>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Description
                            </label>
                            <input type="text" name="description" maxlength="500"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Tuition fee payment for Semester 1"
                                   value="{{ old('description') }}">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Internal Notes
                            </label>
                            <textarea name="notes" rows="3" maxlength="1000"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Any additional notes or remarks">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Receipt Options -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-receipt text-indigo-600 mr-2"></i>
                        Receipt Options
                    </h3>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <p class="text-sm text-blue-700">Receipt will be generated automatically after payment is recorded.</p>
                        </div>

                        <div class="space-y-3 mt-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="generateReceipt" name="generate_receipt"
                                       class="rounded border-gray-300 text-primary focus:ring-primary" checked>
                                <label for="generateReceipt" class="ml-2 text-sm text-gray-700">
                                    Generate receipt immediately
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="sendEmail" name="send_email"
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="sendEmail" class="ml-2 text-sm text-gray-700">
                                    Send receipt via email
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="sendSms" name="send_sms"
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="sendSms" class="ml-2 text-sm text-gray-700">
                                    Send receipt via SMS
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="student_fee_id" id="studentFeeId">
            <input type="hidden" name="registration_id" id="registrationId">

            <!-- Form Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-4">
                <a href="{{ route('admin.fees.payments.index') }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors font-medium flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSelect = document.getElementById('studentSelect');
    const studentDetails = document.getElementById('studentDetails');
    const studentPhone = document.getElementById('studentPhone');
    const studentEmail = document.getElementById('studentEmail');
    const pendingFeesSection = document.getElementById('pendingFeesSection');
    const pendingFeesBody = document.getElementById('pendingFeesBody');
    const noPendingFees = document.getElementById('noPendingFees');
    const paymentAmount = document.getElementById('paymentAmount');
    const maxAmount = document.getElementById('maxAmount');
    const paymentMethod = document.getElementById('paymentMethod');
    const studentFeeId = document.getElementById('studentFeeId');
    const registrationId = document.getElementById('registrationId');

    let selectedFeeId = null;
    let totalBalance = 0;

    // Show/hide student details
    studentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            studentPhone.textContent = selectedOption.dataset.phone || '-';
            studentEmail.textContent = selectedOption.dataset.email || '-';
            studentDetails.classList.remove('hidden');

            // Load pending fees for selected student
            loadPendingFees(this.value);
        } else {
            studentDetails.classList.add('hidden');
            pendingFeesSection.classList.add('hidden');
            resetPaymentFields();
        }
    });

    // Load pending fees via AJAX
    function loadPendingFees(studentId) {
        fetch(`/admin/fees/payments/get-student-fees?student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.pending_fees.length > 0) {
                        renderPendingFees(data.pending_fees);
                        pendingFeesSection.classList.remove('hidden');
                        noPendingFees.classList.add('hidden');

                        // Calculate total balance
                        totalBalance = data.total_balance;
                        updateMaxAmount();
                    } else {
                        pendingFeesSection.classList.add('hidden');
                        noPendingFees.classList.remove('hidden');
                        resetPaymentFields();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading pending fees:', error);
                showToast('Failed to load pending fees', 'error');
            });
    }

    // Render pending fees table
    function renderPendingFees(fees) {
        pendingFeesBody.innerHTML = '';

        fees.forEach((fee, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            row.innerHTML = `
                <td class="py-3 px-4">
                    <div class="font-medium text-gray-900">${fee.invoice_number}</div>
                    <div class="text-xs text-gray-500">${fee.description}</div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">${fee.course}</td>
                <td class="py-3 px-4 text-sm text-gray-700">${fee.due_date}</td>
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">KES ${parseFloat(fee.total_amount).toFixed(2)}</td>
                <td class="py-3 px-4 text-sm text-green-600">KES ${parseFloat(fee.amount_paid).toFixed(2)}</td>
                <td class="py-3 px-4 text-sm text-red-600 font-medium">KES ${parseFloat(fee.balance).toFixed(2)}</td>
                <td class="py-3 px-4">
                    <input type="radio" name="selected_fee" value="${fee.id}"
                           data-balance="${fee.balance}"
                           data-registration="${fee.registration_id || ''}"
                           onchange="selectFee(this)"
                           class="rounded-full border-gray-300 text-primary focus:ring-primary">
                </td>
            `;

            pendingFeesBody.appendChild(row);

            // Select first fee by default
            if (index === 0) {
                row.querySelector('input[type="radio"]').checked = true;
                selectFee(row.querySelector('input[type="radio"]'));
            }
        });
    }

    // Select a fee
    window.selectFee = function(radio) {
        selectedFeeId = radio.value;
        const balance = parseFloat(radio.dataset.balance);
        const regId = radio.dataset.registration;

        studentFeeId.value = selectedFeeId;
        registrationId.value = regId || '';

        updateMaxAmount();

        // Highlight selected row
        document.querySelectorAll('#pendingFeesBody tr').forEach(row => {
            row.classList.remove('bg-blue-50');
        });
        radio.closest('tr').classList.add('bg-blue-50');
    }

    // Update max payable amount
    function updateMaxAmount() {
        const selectedRadio = document.querySelector('input[name="selected_fee"]:checked');
        if (selectedRadio) {
            const balance = parseFloat(selectedRadio.dataset.balance);
            maxAmount.textContent = `KES ${balance.toFixed(2)}`;
            paymentAmount.max = balance;

            // Set payment amount to balance by default
            paymentAmount.value = balance.toFixed(2);
        } else {
            maxAmount.textContent = 'KES 0.00';
            paymentAmount.max = 0;
        }
    }

    // Reset payment fields
    function resetPaymentFields() {
        selectedFeeId = null;
        studentFeeId.value = '';
        registrationId.value = '';
        paymentAmount.value = '';
        paymentAmount.max = 0;
        maxAmount.textContent = 'KES 0.00';
    }

    // Show/hide method-specific details
    paymentMethod.addEventListener('change', function() {
        // Hide all method details
        document.getElementById('methodDetailsSection').querySelectorAll('div').forEach(div => {
            if (div.id && div.id.endsWith('Details')) {
                div.classList.add('hidden');

                // Clear required attributes from hidden fields
                div.querySelectorAll('input[required], select[required]').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });

        // Show selected method details
        const method = this.value;
        if (method) {
            const detailsDiv = document.getElementById(`${method}Details`);
            if (detailsDiv) {
                detailsDiv.classList.remove('hidden');

                // Add required attributes to visible fields
                detailsDiv.querySelectorAll('input, select').forEach(input => {
                    if (input.name.includes(method)) {
                        input.setAttribute('required', 'required');
                    }
                });
            }
        }
    });

    // Validate amount doesn't exceed balance
    paymentAmount.addEventListener('change', function() {
        const amount = parseFloat(this.value) || 0;
        const selectedRadio = document.querySelector('input[name="selected_fee"]:checked');

        if (selectedRadio) {
            const balance = parseFloat(selectedRadio.dataset.balance);

            if (amount > balance) {
                showToast(`Amount cannot exceed balance of KES ${balance.toFixed(2)}`, 'error');
                this.value = balance.toFixed(2);
            }
        }
    });

    // Form submission validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        if (!studentFeeId.value) {
            e.preventDefault();
            showToast('Please select a fee to pay', 'error');
            return;
        }

        const amount = parseFloat(paymentAmount.value) || 0;
        if (amount <= 0) {
            e.preventDefault();
            showToast('Please enter a valid payment amount', 'error');
            return;
        }

        // Validate method-specific fields
        const method = paymentMethod.value;
        let isValid = true;
        let errorMessage = '';

        switch(method) {
            case 'kcb_stk_push':
                const kcbCode = document.querySelector('input[name="kcb_transaction_code"]').value;
                const kcbPhone = document.querySelector('input[name="kcb_phone_number"]').value;
                if (!kcbCode.trim() || !kcbPhone.trim()) {
                    isValid = false;
                    errorMessage = 'Please fill in all required KCB STK Push details';
                }
                break;

            case 'paybill':
                const paybillNumber = document.querySelector('input[name="paybill_number"]').value;
                const paybillAccount = document.querySelector('input[name="paybill_account_number"]').value;
                const paybillCode = document.querySelector('input[name="paybill_transaction_code"]').value;
                if (!paybillNumber.trim() || !paybillAccount.trim() || !paybillCode.trim()) {
                    isValid = false;
                    errorMessage = 'Please fill in all required Paybill details';
                }
                break;

            case 'bank_deposit':
                const bankName = document.querySelector('input[name="bank_name"]').value;
                const bankBranch = document.querySelector('input[name="bank_branch"]').value;
                const depositSlip = document.querySelector('input[name="deposit_slip_number"]').value;
                if (!bankName.trim() || !bankBranch.trim() || !depositSlip.trim()) {
                    isValid = false;
                    errorMessage = 'Please fill in all required Bank Deposit details';
                }
                break;

            case 'cash':
                const receiptNumber = document.querySelector('input[name="cash_receipt_number"]').value;
                if (!receiptNumber.trim()) {
                    isValid = false;
                    errorMessage = 'Please enter the cash receipt number';
                }
                break;
        }

        if (!isValid) {
            e.preventDefault();
            showToast(errorMessage, 'error');
        }
    });

    // Auto-fill payer phone from selected student
    studentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.phone && selectedOption.dataset.phone !== '') {
            document.querySelector('input[name="payer_phone"]').value = selectedOption.dataset.phone;
        }
    });

    // Toast notification function
    window.showToast = function(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-in ${
            type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
            type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
            'bg-blue-100 text-blue-800 border border-blue-200'
        }`;

        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'success' ? 'fa-check-circle' : 'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    };
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
`;
document.head.appendChild(style);
</script>

<style>
    .form-section {
        transition: all 0.3s ease;
    }

    #pendingFeesBody tr.bg-blue-50 {
        background-color: #eff6ff;
    }

    #pendingFeesBody tr:hover {
        background-color: #f9fafb;
    }

    #pendingFeesBody tr.bg-blue-50:hover {
        background-color: #dbeafe;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        ring-width: 2px;
    }
</style>
@endsection
