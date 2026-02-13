@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Create Payment Plan')
@section('subtitle', 'Create a new payment plan for a student')

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
        <a href="{{ route('admin.fees.payment-plans.index') }}" class="ml-1 text-sm font-medium text-gray-500 md:ml-2 hover:text-primary">Payment Plans</a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Create Payment Plan</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.fees.payment-plans.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to List
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create New Payment Plan</h3>
            <p class="text-sm text-gray-600 mt-1">Fill in the details below to create a payment plan for a student.</p>
        </div>

        <form method="POST" action="{{ route('admin.fees.payment-plans.store') }}" id="paymentPlanForm">
            @csrf

            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Student <span class="text-red-500">*</span>
                            </label>
                            <select name="student_id" id="student_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    onchange="loadStudentRegistrations(this.value)">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" data-phone="{{ $student->phone_number ?? '' }}">
                                        {{ $student->name }} - {{ $student->admission_number ?? $student->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Registration Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Registration <span class="text-red-500">*</span>
                            </label>
                            <select name="registration_id" id="registration_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    onchange="loadFeeStructure(this.value)">
                                <option value="">Select Registration</option>
                                @foreach($registrations as $registration)
                                    <option value="{{ $registration->id }}"
                                            data-student="{{ $registration->student_id }}"
                                            data-course="{{ $registration->course_id }}"
                                            data-campus="{{ $registration->campus_id }}">
                                        {{ $registration->student->name ?? 'N/A' }} -
                                        {{ $registration->course->name ?? 'N/A' }}
                                        ({{ $registration->campus->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('registration_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fee Structure -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fee Structure (Optional)
                            </label>
                            <select name="fee_structure_id" id="fee_structure_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Fee Structure</option>
                                @foreach($feeStructures as $feeStructure)
                                    <option value="{{ $feeStructure->id }}"
                                            data-amount="{{ $feeStructure->total_course_fee }}">
                                        {{ $feeStructure->course->name ?? 'N/A' }} -
                                        {{ $feeStructure->campus->name ?? 'N/A' }} -
                                        KES {{ number_format($feeStructure->total_course_fee, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_structure_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Plan Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Plan Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="plan_name" id="plan_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., John Doe - Computer Science Payment Plan"
                                   value="{{ old('plan_name') }}">
                            @error('plan_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Plan Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Plan Type <span class="text-red-500">*</span>
                            </label>
                            <select name="plan_type" id="plan_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    onchange="updateInstallmentFields()">
                                <option value="">Select Plan Type</option>
                                @foreach($planTypes as $type)
                                    <option value="{{ $type }}" {{ old('plan_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Installment Frequency -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Installment Frequency
                            </label>
                            <select name="installment_frequency" id="installment_frequency"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                @foreach($installmentFrequencies as $value => $label)
                                    <option value="{{ $value }}" {{ old('installment_frequency') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('installment_frequency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">Financial Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Total Course Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Total Course Amount (KES) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_course_amount" id="total_course_amount" required step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00"
                                   value="{{ old('total_course_amount') }}"
                                   onchange="calculateInstallments()">
                            @error('total_course_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Discount Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Discount Amount (KES)
                            </label>
                            <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00"
                                   value="{{ old('discount_amount', 0) }}"
                                   onchange="calculateInstallments()">
                            @error('discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Net Amount (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Net Amount (KES)
                            </label>
                            <input type="text" id="net_amount" readonly
                                   class="w-full px-4 py-2 border border-gray-300 bg-gray-50 rounded-lg"
                                   placeholder="0.00"
                                   value="0.00">
                            <p class="mt-1 text-xs text-gray-500">Total Amount - Discount</p>
                        </div>

                        <!-- Discount Reason -->
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Discount Reason (if any)
                            </label>
                            <textarea name="discount_reason" id="discount_reason" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Enter reason for discount...">{{ old('discount_reason') }}</textarea>
                            @error('discount_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Installment Schedule -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">Installment Schedule</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Number of Installments -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Number of Installments <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="number_of_installments" id="number_of_installments" required min="1" max="60"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 12"
                                   value="{{ old('number_of_installments', 1) }}"
                                   onchange="calculateInstallments()">
                            @error('number_of_installments')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   onchange="updateFirstPaymentDate()">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                End Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" id="end_date" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Payment Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="first_payment_date" id="first_payment_date" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('first_payment_date', date('Y-m-d')) }}">
                            @error('first_payment_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Late Fee Percentage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Fee Percentage (%)
                            </label>
                            <input type="number" name="late_fee_percentage" id="late_fee_percentage" step="0.01" min="0" max="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="5.00"
                                   value="{{ old('late_fee_percentage', 5.00) }}">
                            @error('late_fee_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grace Period Days -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Grace Period (Days)
                            </label>
                            <input type="number" name="grace_period_days" id="grace_period_days" min="0" max="30"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="7"
                                   value="{{ old('grace_period_days', 7) }}">
                            @error('grace_period_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Installment Preview -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4" id="installmentPreview" style="display: none;">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Installment Preview</h5>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount (KES)</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="installmentTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Installment rows will be inserted here -->
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="2" class="px-3 py-2 text-sm font-medium text-gray-700">Total</td>
                                        <td id="totalInstallmentAmount" class="px-3 py-2 text-sm font-medium text-gray-900">KES 0.00</td>
                                        <td class="px-3 py-2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3 text-xs text-gray-500">
                            <p><span id="installmentSummary">No installments calculated yet.</span></p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">Advanced Settings</h4>
                    <div class="space-y-4">
                        <!-- Auto Generate Invoices -->
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_generate_invoices" id="auto_generate_invoices" value="1"
                                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                   {{ old('auto_generate_invoices') ? 'checked' : '' }}>
                            <label for="auto_generate_invoices" class="ml-2 block text-sm text-gray-700">
                                Auto-generate invoices
                            </label>
                        </div>

                        <!-- Invoice Days Before Due -->
                        <div id="invoiceDaysContainer" class="ml-6" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Generate invoices (days before due)
                            </label>
                            <input type="number" name="invoice_days_before_due" id="invoice_days_before_due" min="1" max="30"
                                   class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="7"
                                   value="{{ old('invoice_days_before_due', 7) }}">
                            @error('invoice_days_before_due')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Terms and Conditions
                            </label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Enter payment plan terms and conditions...">{{ old('terms_and_conditions') }}</textarea>
                            @error('terms_and_conditions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Any additional notes or comments...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Hidden field for custom schedule -->
                <input type="hidden" name="installment_schedule" id="installment_schedule">

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.fees.payment-plans.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="button" onclick="previewPlan()"
                            class="px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                        Preview Plan
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                        Create Payment Plan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 modal-overlay" onclick="closeModal('previewModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Payment Plan Preview</h3>
                    <button onclick="closeModal('previewModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="previewContent" class="space-y-4">
                    <!-- Preview content will be inserted here -->
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('previewModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Close
                </button>
                <button type="button" onclick="submitForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                    Confirm & Create Plan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// DOM Elements
const autoGenerateCheckbox = document.getElementById('auto_generate_invoices');
const invoiceDaysContainer = document.getElementById('invoiceDaysContainer');

// Toggle invoice days field
if (autoGenerateCheckbox) {
    autoGenerateCheckbox.addEventListener('change', function() {
        invoiceDaysContainer.style.display = this.checked ? 'block' : 'none';
    });

    // Initial state
    invoiceDaysContainer.style.display = autoGenerateCheckbox.checked ? 'block' : 'none';
}

// Load student registrations when student is selected
function loadStudentRegistrations(studentId) {
    const registrationSelect = document.getElementById('registration_id');

    if (!studentId) {
        // Reset registration options
        registrationSelect.innerHTML = '<option value="">Select Registration</option>';
        return;
    }

    // Filter registrations for selected student
    const options = registrationSelect.querySelectorAll('option');
    let hasRegistrations = false;

    registrationSelect.innerHTML = '<option value="">Select Registration</option>';

    options.forEach(option => {
        if (option.value && option.dataset.student === studentId) {
            registrationSelect.appendChild(option.cloneNode(true));
            hasRegistrations = true;
        }
    });

    if (!hasRegistrations) {
        const noOption = document.createElement('option');
        noOption.value = '';
        noOption.textContent = 'No registrations found for this student';
        registrationSelect.appendChild(noOption);
    }
}

// Load fee structure when registration is selected
function loadFeeStructure(registrationId) {
    const feeStructureSelect = document.getElementById('fee_structure_id');

    if (!registrationId) {
        return;
    }

    // Find selected registration
    const registrationOption = document.querySelector(`#registration_id option[value="${registrationId}"]`);
    if (!registrationOption) return;

    const courseId = registrationOption.dataset.course;
    const campusId = registrationOption.dataset.campus;

    // Find matching fee structure
    const options = feeStructureSelect.querySelectorAll('option');
    let foundMatch = false;

    feeStructureSelect.innerHTML = '<option value="">Select Fee Structure</option>';

    options.forEach(option => {
        if (option.value && option.dataset.course === courseId && option.dataset.campus === campusId) {
            feeStructureSelect.appendChild(option.cloneNode(true));
            foundMatch = true;
        }
    });

    // Auto-select if only one match
    if (foundMatch) {
        const matchingOptions = feeStructureSelect.querySelectorAll('option[value]');
        if (matchingOptions.length === 1) {
            feeStructureSelect.value = matchingOptions[0].value;

            // Set total amount from fee structure
            const totalAmountInput = document.getElementById('total_course_amount');
            const selectedOption = feeStructureSelect.options[feeStructureSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.amount) {
                totalAmountInput.value = selectedOption.dataset.amount;
                calculateInstallments();
            }
        }
    }
}

// Calculate installments
function calculateInstallments() {
    const totalAmount = parseFloat(document.getElementById('total_course_amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const numInstallments = parseInt(document.getElementById('number_of_installments').value) || 1;
    const planType = document.getElementById('plan_type').value;
    const firstPaymentDate = document.getElementById('first_payment_date').value;

    if (!totalAmount || totalAmount <= 0 || !firstPaymentDate) {
        return;
    }

    // Calculate net amount
    const netAmount = totalAmount - discountAmount;
    document.getElementById('net_amount').value = netAmount.toFixed(2);

    // Calculate installment amount
    const installmentAmount = netAmount / numInstallments;
    const roundedAmount = Math.round(installmentAmount * 100) / 100;

    // Calculate last installment amount (for rounding differences)
    const totalAllocated = roundedAmount * (numInstallments - 1);
    const lastInstallmentAmount = netAmount - totalAllocated;

    // Generate installment schedule
    const schedule = [];
    const tableBody = document.getElementById('installmentTableBody');
    tableBody.innerHTML = '';

    let dueDate = new Date(firstPaymentDate);
    let totalCalculated = 0;

    for (let i = 1; i <= numInstallments; i++) {
        const amount = (i === numInstallments) ? lastInstallmentAmount : roundedAmount;
        totalCalculated += amount;

        // Format date
        const formattedDate = dueDate.toISOString().split('T')[0];
        const displayDate = dueDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Add to schedule
        schedule.push({
            installment_number: i,
            amount: amount,
            due_date: formattedDate
        });

        // Add to table
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-3 py-2 text-sm text-gray-900">${i}</td>
            <td class="px-3 py-2 text-sm text-gray-900">${displayDate}</td>
            <td class="px-3 py-2 text-sm text-gray-900 font-medium">KES ${amount.toFixed(2)}</td>
            <td class="px-3 py-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Pending
                </span>
            </td>
        `;
        tableBody.appendChild(row);

        // Calculate next due date based on plan type
        if (i < numInstallments) {
            switch (planType) {
                case 'monthly':
                    dueDate.setMonth(dueDate.getMonth() + 1);
                    break;
                case 'quarterly':
                    dueDate.setMonth(dueDate.getMonth() + 3);
                    break;
                case 'semester':
                    dueDate.setMonth(dueDate.getMonth() + 6);
                    break;
                case 'annual':
                    dueDate.setFullYear(dueDate.getFullYear() + 1);
                    break;
                default:
                    dueDate.setMonth(dueDate.getMonth() + 1);
            }
        }
    }

    // Update summary
    document.getElementById('totalInstallmentAmount').textContent = `KES ${totalCalculated.toFixed(2)}`;
    document.getElementById('installmentSummary').textContent =
        `${numInstallments} installments of KES ${roundedAmount.toFixed(2)} each`;

    // Show preview
    document.getElementById('installmentPreview').style.display = 'block';

    // Store schedule in hidden field
    document.getElementById('installment_schedule').value = JSON.stringify(schedule);
}

// Update first payment date based on start date
function updateFirstPaymentDate() {
    const startDate = document.getElementById('start_date').value;
    const firstPaymentDateInput = document.getElementById('first_payment_date');

    if (startDate) {
        // Set first payment date to start date if not already set
        if (!firstPaymentDateInput.value || new Date(firstPaymentDateInput.value) < new Date(startDate)) {
            firstPaymentDateInput.value = startDate;
            firstPaymentDateInput.min = startDate;
        }
    }
}

// Update installment fields based on plan type
function updateInstallmentFields() {
    const planType = document.getElementById('plan_type').value;
    const frequencySelect = document.getElementById('installment_frequency');

    switch (planType) {
        case 'monthly':
            frequencySelect.value = 'monthly';
            break;
        case 'quarterly':
            frequencySelect.value = 'quarterly';
            break;
        case 'semester':
            frequencySelect.value = 'semester';
            break;
        case 'annual':
            frequencySelect.value = 'annual';
            break;
        case 'custom':
            frequencySelect.value = 'custom';
            break;
    }

    calculateInstallments();
}

// Preview plan
function previewPlan() {
    // Get form data
    const formData = new FormData(document.getElementById('paymentPlanForm'));
    const studentId = document.getElementById('student_id').value;
    const studentOption = document.querySelector(`#student_id option[value="${studentId}"]`);
    const registrationId = document.getElementById('registration_id').value;
    const registrationOption = document.querySelector(`#registration_id option[value="${registrationId}"]`);

    if (!studentId || !registrationId) {
        alert('Please select a student and registration.');
        return;
    }

    // Build preview content
    let previewHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h5 class="text-sm font-medium text-gray-500">Student</h5>
                    <p class="text-sm text-gray-900">${studentOption ? studentOption.textContent : 'N/A'}</p>
                </div>
                <div>
                    <h5 class="text-sm font-medium text-gray-500">Registration</h5>
                    <p class="text-sm text-gray-900">${registrationOption ? registrationOption.textContent : 'N/A'}</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <h5 class="text-sm font-medium text-gray-500">Plan Type</h5>
                    <p class="text-sm text-gray-900">${formData.get('plan_type') ? formData.get('plan_type').replace('_', ' ') : 'N/A'}</p>
                </div>
                <div>
                    <h5 class="text-sm font-medium text-gray-500">Total Amount</h5>
                    <p class="text-sm text-gray-900">KES ${parseFloat(formData.get('total_course_amount') || 0).toFixed(2)}</p>
                </div>
                <div>
                    <h5 class="text-sm font-medium text-gray-500">Installments</h5>
                    <p class="text-sm text-gray-900">${formData.get('number_of_installments') || 1}</p>
                </div>
            </div>

            <div>
                <h5 class="text-sm font-medium text-gray-500 mb-2">Installment Schedule</h5>
                <div class="bg-gray-50 rounded p-3">
                    ${document.getElementById('installmentPreview').innerHTML}
                </div>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Please review all details before creating the payment plan.
                            Once created, the plan will need approval before becoming active.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('previewContent').innerHTML = previewHTML;
    openModal('previewModal', '4xl');
}

// Submit form
function submitForm() {
    document.getElementById('paymentPlanForm').submit();
}

// Modal functions
function openModal(modalId, size = 'lg') {
    const modal = document.getElementById(modalId);
    const modalContent = modal.querySelector('.modal-content');

    // Set size class
    modalContent.className = modalContent.className.replace(/sm:max-w-\w+/, `sm:max-w-${size}`);

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').min = today;
    document.getElementById('first_payment_date').min = today;
    document.getElementById('end_date').min = today;

    // Auto-generate plan name
    document.getElementById('student_id').addEventListener('change', function() {
        const studentOption = this.options[this.selectedIndex];
        const planNameInput = document.getElementById('plan_name');

        if (studentOption && studentOption.value && !planNameInput.value) {
            const studentName = studentOption.textContent.split(' - ')[0];
            planNameInput.value = `${studentName} - Payment Plan`;
        }
    });

    // Load fee structure amount when selected
    document.getElementById('fee_structure_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const totalAmountInput = document.getElementById('total_course_amount');

        if (selectedOption && selectedOption.dataset.amount) {
            totalAmountInput.value = selectedOption.dataset.amount;
            calculateInstallments();
        }
    });

    // Auto-calculate installments when relevant fields change
    const calculationFields = ['total_course_amount', 'discount_amount', 'number_of_installments', 'plan_type', 'first_payment_date'];
    calculationFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', calculateInstallments);
        }
    });

    // Calculate on page load if there's data
    setTimeout(calculateInstallments, 500);
});
</script>

<style>
    /* Custom scrollbar for modal */
    .modal-content {
        max-height: 85vh;
        overflow-y: auto;
    }

    /* Hide number input arrows */
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endsection
