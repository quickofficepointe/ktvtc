@extends('ktvtc.finance.layouts.app')

@section('title', 'Record Payment')
@section('subtitle', 'Record a new student fee payment')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="POST" action="{{ route('finance.student-fees.store') }}" id="paymentForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student Selection -->
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Student <span class="text-red-500">*</span>
                    </label>
                    <select name="student_id" id="student_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" required>
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

                <!-- Enrollment Selection -->
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Enrollment <span class="text-red-500">*</span>
                    </label>
                    <select name="enrollment_id" id="enrollment_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" required>
                        <option value="">Select Student First</option>
                    </select>
                    @error('enrollment_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Balance Display Card -->
                <div class="md:col-span-2 bg-gradient-to-r from-gray-50 to-gray-100/50 p-5 rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</p>
                            <p id="totalFeesDisplay" class="text-xl font-bold text-gray-800 mt-1">KES 0.00</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</p>
                            <p id="amountPaidDisplay" class="text-xl font-bold text-green-600 mt-1">KES 0.00</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</p>
                            <p id="balanceDisplay" class="text-xl font-bold text-yellow-600 mt-1">KES 0.00</p>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-semibold text-sm">KES</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                               class="w-full pl-16 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                               value="{{ old('amount') }}" required placeholder="0.00">
                    </div>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" required>
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

                <!-- Payment Date -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" required>
                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Code -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Transaction Code</label>
                    <input type="text" name="transaction_code" value="{{ old('transaction_code') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                           placeholder="e.g., M-Pesa receipt number">
                    @error('transaction_code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment For Month -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payment For Month</label>
                    <input type="month" name="payment_for_month" value="{{ old('payment_for_month') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    @error('payment_for_month')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payer Name -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Name</label>
                    <input type="text" name="payer_name" value="{{ old('payer_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                           placeholder="Name of person paying">
                    @error('payer_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payer Phone -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Phone</label>
                    <input type="text" name="payer_phone" value="{{ old('payer_phone') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                           placeholder="Phone number">
                    @error('payer_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payer Type -->
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Payer Type</label>
                    <select name="payer_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
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

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Notes</label>
                    <textarea name="notes" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" rows="3" placeholder="Additional notes about this payment...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('finance.student-fees.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 text-center font-medium text-gray-700">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition duration-200 font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // When student changes, load enrollments
        $('#student_id').change(function() {
            const studentId = $(this).val();
            if (studentId) {
                $.ajax({
                    url: `/api/students/${studentId}/enrollments`,
                    method: 'GET',
                    success: function(data) {
                        const select = $('#enrollment_id');
                        select.empty().append('<option value="">Select Enrollment</option>');
                        data.forEach(function(enrollment) {
                            select.append(
                                `<option value="${enrollment.id}" data-balance="${enrollment.balance}" data-total="${enrollment.total_fees}" data-paid="${enrollment.amount_paid}">
                                    ${enrollment.course_name} - Balance: KES ${parseFloat(enrollment.balance).toFixed(2)}
                                </option>`
                            );
                        });
                    }
                });
            }
        });

        // When enrollment changes, update balance display
        $('#enrollment_id').change(function() {
            const selected = $(this).find('option:selected');
            const balance = parseFloat(selected.data('balance')) || 0;
            const total = parseFloat(selected.data('total')) || 0;
            const paid = parseFloat(selected.data('paid')) || 0;

            $('#balanceDisplay').text('KES ' + balance.toFixed(2));
            $('#totalFeesDisplay').text('KES ' + total.toFixed(2));
            $('#amountPaidDisplay').text('KES ' + paid.toFixed(2));

            // Set max amount to balance
            $('#amount').attr('max', balance);
        });
    });
</script>
@endpush
