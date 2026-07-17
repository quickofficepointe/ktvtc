@extends('ktvtc.finance.layouts.app')

@section('title', 'Record Payment')
@section('subtitle', 'Record a new student fee payment')

@section('breadcrumb')
    <li class="mx-2">/</li>
    <li>
        <a href="{{ route('finance.student-fees.index') }}" class="hover:text-primary transition whitespace-nowrap">
            Student Fees
        </a>
    </li>
    <li class="mx-2">/</li>
    <li class="text-primary font-medium whitespace-nowrap">Record Payment</li>
@endsection

@section('header-actions')
    <a href="{{ route('finance.student-fees.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="finance-card relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="POST" action="{{ route('finance.student-fees.store') }}" id="paymentForm" class="p-4 sm:p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Student <span class="text-red-500">*</span>
                    </label>

                    <select name="student_id"
                            id="student_id"
                            data-placeholder="Select Student"
                            class="w-full"
                            required>
                        <option value="">Select Student</option>
                        @foreach($students ?? [] as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->student_number }})
                            </option>
                        @endforeach
                    </select>

                    @error('student_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Enrollment <span class="text-red-500">*</span>
                    </label>

                    <select name="enrollment_id"
                            id="enrollment_id"
                            data-placeholder="Select Enrollment"
                            class="w-full"
                            required>
                        <option value="">Select Student First</option>
                    </select>

                    @error('enrollment_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 bg-gray-50 p-4 sm:p-5 rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</p>
                            <p id="totalFeesDisplay" class="text-lg sm:text-xl font-bold text-gray-800 mt-1">KES 0.00</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</p>
                            <p id="amountPaidDisplay" class="text-lg sm:text-xl font-bold text-green-600 mt-1">KES 0.00</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</p>
                            <p id="balanceDisplay" class="text-lg sm:text-xl font-bold text-yellow-600 mt-1">KES 0.00</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Amount <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-semibold text-sm">KES</span>
                        <input type="number"
                               name="amount"
                               id="amount"
                               step="0.01"
                               min="0.01"
                               value="{{ old('amount') }}"
                               required
                               placeholder="0.00"
                               class="w-full pl-16 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Payment Method <span class="text-red-500">*</span>
                    </label>

                    <select name="payment_method" data-placeholder="Select Method" class="w-full" required>
                        <option value="">Select Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mpesa" {{ old('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="kcb" {{ old('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>

                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Payment Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="payment_date"
                           value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Transaction Code</label>

                    <input type="text"
                           name="transaction_code"
                           value="{{ old('transaction_code') }}"
                           placeholder="e.g. M-Pesa receipt number"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    @error('transaction_code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payment For Month</label>

                    <input type="month"
                           name="payment_for_month"
                           value="{{ old('payment_for_month') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    @error('payment_for_month')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Name</label>

                    <input type="text"
                           name="payer_name"
                           value="{{ old('payer_name') }}"
                           placeholder="Name of person paying"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    @error('payer_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Phone</label>

                    <input type="text"
                           name="payer_phone"
                           value="{{ old('payer_phone') }}"
                           placeholder="Phone number"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    @error('payer_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Type</label>

                    <select name="payer_type" data-placeholder="Select Payer Type" class="w-full">
                        <option value="student" {{ old('payer_type') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="parent" {{ old('payer_type') == 'parent' ? 'selected' : '' }}>Parent</option>
                        <option value="sponsor" {{ old('payer_type') == 'sponsor' ? 'selected' : '' }}>Sponsor</option>
                        <option value="employer" {{ old('payer_type') == 'employer' ? 'selected' : '' }}>Employer</option>
                        <option value="other" {{ old('payer_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>

                    @error('payer_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Notes</label>

                    <textarea name="notes"
                              rows="3"
                              placeholder="Additional notes about this payment..."
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('notes') }}</textarea>

                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('finance.student-fees.index') }}"
                   class="px-6 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-center font-medium text-gray-700">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Initialize Select2 properly
        if ($.fn.select2) {
            $('#student_id').select2({
                placeholder: 'Select Student',
                allowClear: true,
                width: '100%'
            });

            $('#enrollment_id').select2({
                placeholder: 'Select Enrollment',
                allowClear: true,
                width: '100%'
            });

            $('#payment_method').select2({
                placeholder: 'Select Payment Method',
                allowClear: true,
                width: '100%'
            });

            $('#payer_type').select2({
                placeholder: 'Select Payer Type',
                allowClear: true,
                width: '100%'
            });
        }

        // Student dropdown change handler
        $('#student_id').on('change', function () {
            const studentId = $(this).val();
            const enrollmentSelect = $('#enrollment_id');

            // Reset enrollment dropdown
            enrollmentSelect.empty().append('<option value="">Loading...</option>');
            enrollmentSelect.trigger('change.select2');

            // Reset displays
            $('#totalFeesDisplay').text('KES 0.00');
            $('#amountPaidDisplay').text('KES 0.00');
            $('#balanceDisplay').text('KES 0.00');
            $('#amount').removeAttr('max');

            if (!studentId) {
                enrollmentSelect.empty().append('<option value="">Select Student First</option>');
                enrollmentSelect.trigger('change.select2');
                return;
            }

            // ✅ FIX: Use the correct finance API route
            const url = `/finance/api/students/${studentId}/enrollments`;

            showLoading('Loading enrollments...');

            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    hideLoading();
                    enrollmentSelect.empty().append('<option value="">Select Enrollment</option>');

                    if (!data || data.length === 0) {
                        enrollmentSelect.append('<option value="">No active enrollment found</option>');
                        enrollmentSelect.trigger('change.select2');
                        return;
                    }

                    data.forEach(function (enrollment) {
                        const balance = parseFloat(enrollment.balance || 0);
                        const totalFees = parseFloat(enrollment.total_fees || 0);
                        const amountPaid = parseFloat(enrollment.amount_paid || 0);
                        const status = enrollment.status || 'active';

                        enrollmentSelect.append(`
                            <option value="${enrollment.id}"
                                    data-balance="${balance}"
                                    data-total="${totalFees}"
                                    data-paid="${amountPaid}"
                                    data-status="${status}">
                                ${enrollment.course_name} - Balance: KES ${balance.toFixed(2)}
                                ${status !== 'active' ? ' (' + status + ')' : ''}
                            </option>
                        `);
                    });

                    enrollmentSelect.trigger('change.select2');
                },
                error: function (xhr) {
                    hideLoading();
                    console.error('Error loading enrollments:', xhr);

                    enrollmentSelect.empty().append('<option value="">Failed to load enrollments</option>');
                    enrollmentSelect.trigger('change.select2');

                    // Show user-friendly error message
                    if (xhr.status === 404) {
                        toastr.error('API endpoint not found. Please contact system administrator.');
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        toastr.error('You are not authorized to perform this action. Please refresh and try again.');
                    } else if (xhr.status === 500) {
                        toastr.error('Server error. Please contact system administrator.');
                    } else {
                        toastr.error('Unable to load student enrollments. Please try again.');
                    }
                }
            });
        });

        // Enrollment selection change handler
        $('#enrollment_id').on('change', function () {
            const selected = $(this).find('option:selected');

            const balance = parseFloat(selected.data('balance')) || 0;
            const total = parseFloat(selected.data('total')) || 0;
            const paid = parseFloat(selected.data('paid')) || 0;
            const status = selected.data('status') || 'active';

            // Update displays
            $('#balanceDisplay').text('KES ' + balance.toFixed(2));
            $('#totalFeesDisplay').text('KES ' + total.toFixed(2));
            $('#amountPaidDisplay').text('KES ' + paid.toFixed(2));

            // Check if enrollment is active
            if (status !== 'active') {
                $('#amount').prop('disabled', true);
                $('#amount').attr('placeholder', 'Enrollment is ' + status.toUpperCase());
                toastr.warning('This enrollment is ' + status + '. Payments cannot be recorded.', 'Warning');
            } else {
                $('#amount').prop('disabled', false);
                $('#amount').attr('placeholder', 'Enter amount');
            }

            // Set max amount to balance if balance > 0
            if (balance > 0) {
                $('#amount').attr('max', balance);
                $('#amount').attr('data-max-balance', balance);
            } else {
                $('#amount').removeAttr('max');
                $('#amount').attr('placeholder', 'Enrollment is fully paid');
                toastr.success('This enrollment is fully paid!', 'Information');
            }
        });

        // Amount input validation
        $('#amount').on('input', function () {
            const maxBalance = parseFloat($(this).attr('data-max-balance')) || 0;
            const currentValue = parseFloat($(this).value) || 0;

            if (maxBalance > 0 && currentValue > maxBalance) {
                $(this).addClass('border-red-500');
                toastr.error('Amount cannot exceed outstanding balance of KES ' + maxBalance.toFixed(2));
            } else {
                $(this).removeClass('border-red-500');
            }
        });

        // Form submission validation
        $('#paymentForm').on('submit', function (e) {
            const amount = parseFloat($('#amount').val()) || 0;
            const maxBalance = parseFloat($('#amount').attr('data-max-balance')) || 0;

            if (maxBalance > 0 && amount > maxBalance) {
                e.preventDefault();
                toastr.error('Payment amount cannot exceed the outstanding balance of KES ' + maxBalance.toFixed(2));
                return false;
            }

            if (amount <= 0) {
                e.preventDefault();
                toastr.error('Please enter a valid payment amount.');
                return false;
            }

            if (!$('#student_id').val()) {
                e.preventDefault();
                toastr.error('Please select a student.');
                return false;
            }

            if (!$('#enrollment_id').val()) {
                e.preventDefault();
                toastr.error('Please select an enrollment.');
                return false;
            }

            showLoading('Processing payment...');
            return true;
        });
    });

    // Toastr configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000,
        extendedTimeOut: 1000,
        preventDuplicates: true
    };
</script>
@endpush
