@extends('ktvtc.finance.layouts.app')

@section('title', 'Record Payment')
@section('subtitle', 'Record a new student fee payment')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.student-fees.index') }}" class="text-gray-600 hover:text-primary">Student Fees</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Record Payment</span>
</li>
@endsection

@section('content')
<div class="finance-card p-6 max-w-3xl mx-auto">
    <form method="POST" action="{{ route('finance.student-fees.store') }}" id="paymentForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Student Selection -->
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-700">Student *</label>
                <select name="student_id" id="student_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
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
                <label class="text-sm font-semibold text-gray-700">Enrollment *</label>
                <select name="enrollment_id" id="enrollment_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <option value="">Select Student First</option>
                </select>
                @error('enrollment_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Balance Display -->
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Outstanding Balance:</span>
                    <span id="balanceDisplay" class="text-xl font-bold text-yellow-600">KES 0.00</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm font-medium text-gray-600">Total Fees:</span>
                    <span id="totalFeesDisplay" class="text-lg font-semibold text-gray-800">KES 0.00</span>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <span class="text-sm font-medium text-gray-600">Amount Paid:</span>
                    <span id="amountPaidDisplay" class="text-lg font-semibold text-green-600">KES 0.00</span>
                </div>
            </div>

            <!-- Amount -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Amount *</label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500">KES</span>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                           class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                           value="{{ old('amount') }}" required>
                </div>
                @error('amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Method -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Payment Method *</label>
                <select name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
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
                <label class="text-sm font-semibold text-gray-700">Payment Date *</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                @error('payment_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Transaction Code -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Transaction Code</label>
                <input type="text" name="transaction_code" value="{{ old('transaction_code') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                       placeholder="e.g., M-Pesa receipt number">
                @error('transaction_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment For Month -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Payment For Month</label>
                <input type="month" name="payment_for_month" value="{{ old('payment_for_month') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                @error('payment_for_month')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payer Name -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Payer Name</label>
                <input type="text" name="payer_name" value="{{ old('payer_name') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                       placeholder="Name of person paying">
                @error('payer_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payer Phone -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Payer Phone</label>
                <input type="text" name="payer_phone" value="{{ old('payer_phone') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                       placeholder="Phone number">
                @error('payer_phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payer Type -->
            <div>
                <label class="text-sm font-semibold text-gray-700">Payer Type</label>
                <select name="payer_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
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
                <label class="text-sm font-semibold text-gray-700">Notes</label>
                <textarea name="notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
            <a href="{{ route('finance.student-fees.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                <i class="fas fa-save mr-2"></i> Record Payment
            </button>
        </div>
    </form>
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
