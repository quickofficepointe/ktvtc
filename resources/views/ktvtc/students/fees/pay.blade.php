@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Pay Fees')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-primary text-white">
            <div class="flex items-center">
                <i class="fas fa-credit-card text-2xl mr-3"></i>
                <div>
                    <h1 class="text-xl font-bold">Pay School Fees</h1>
                    <p class="text-sm opacity-90">Complete your payment securely via KCB M-Pesa</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Enrollment Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-receipt text-primary mr-2"></i> Payment Details
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Course:</span>
                        <span class="font-medium">{{ $enrollment->course_name }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Total Fees:</span>
                        <span class="font-medium">KES {{ number_format($enrollment->total_fees, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Amount Paid:</span>
                        <span class="text-green-600">KES {{ number_format($enrollment->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-gray-200 mt-2">
                        <span class="font-semibold">Outstanding Balance:</span>
                        <span class="font-bold text-red-600 text-lg">KES {{ number_format($enrollment->balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <form id="paymentForm" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Pay (KES)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number"
                               name="amount"
                               id="amount"
                               class="w-full pl-14 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               min="1"
                               max="{{ $enrollment->balance }}"
                               value="{{ min($enrollment->balance, 5000) }}"
                               required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum: KES 1 | Maximum: KES {{ number_format($enrollment->balance, 2) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Phone Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">+254</span>
                        <input type="tel"
                               name="phone"
                               id="phone"
                               class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="712 345 678"
                               value="{{ auth()->user()->phone_number ?? '' }}"
                               required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter the phone number registered with M-Pesa (e.g., 712345678)</p>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-2">Payment Instructions:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>You will receive a prompt on your phone to enter your M-Pesa PIN</li>
                                <li>Payment will be processed via KCB Paybill <span class="font-mono font-bold">7664166</span></li>
                                <li>Your account will be updated automatically upon successful payment</li>
                                <li>You will receive an SMS confirmation after payment</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div id="loadingSpinner" class="hidden text-center py-4">
                    <div class="inline-flex items-center">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mr-3"></div>
                        <span class="text-gray-600">Processing payment request...</span>
                    </div>
                </div>

                <div id="paymentStatus" class="hidden"></div>

                <button type="submit"
                        id="payButton"
                        class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-credit-card mr-2"></i> Pay KES <span id="displayAmount">0</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('student.fees.index') }}" class="text-gray-500 hover:text-primary text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Fee Statement
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const displayAmount = document.getElementById('displayAmount');
    const phoneInput = document.getElementById('phone');
    const payButton = document.getElementById('payButton');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const paymentStatus = document.getElementById('paymentStatus');
    const paymentForm = document.getElementById('paymentForm');

    function updateDisplayAmount() {
        let amount = parseFloat(amountInput.value) || 0;
        displayAmount.textContent = amount.toLocaleString();
    }

    amountInput.addEventListener('input', updateDisplayAmount);
    updateDisplayAmount();

    paymentForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const amount = amountInput.value;
        const phone = phoneInput.value;

        if (!amount || amount <= 0) {
            showStatus('Please enter a valid amount', 'error');
            return;
        }

        if (!phone) {
            showStatus('Please enter your phone number', 'error');
            return;
        }

        // Show loading
        payButton.disabled = true;
        loadingSpinner.classList.remove('hidden');
        paymentStatus.classList.add('hidden');
        payButton.classList.add('opacity-50');

        try {
            const response = await fetch('{{ route("student.fees.initiate", $enrollment) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ amount: amount, phone: phone })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showStatus('Payment request sent! Please check your phone and enter your M-Pesa PIN.', 'success');
                pollPaymentStatus(data.checkout_request_id);
            } else {
                showStatus(data.error || 'Failed to initiate payment. Please try again.', 'error');
                payButton.disabled = false;
                loadingSpinner.classList.add('hidden');
                payButton.classList.remove('opacity-50');
            }
        } catch (error) {
            console.error('Error:', error);
            showStatus('An error occurred. Please try again.', 'error');
            payButton.disabled = false;
            loadingSpinner.classList.add('hidden');
            payButton.classList.remove('opacity-50');
        }
    });

    async function pollPaymentStatus(checkoutRequestId) {
        let attempts = 0;
        const maxAttempts = 30;

        const interval = setInterval(async () => {
            attempts++;

            try {
                const response = await fetch(`{{ url("/student/fees/status") }}/${checkoutRequestId}`);
                const data = await response.json();

                if (data.status === 'completed') {
                    clearInterval(interval);
                    showStatus('Payment successful! Redirecting to fee statement...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("student.fees.index") }}';
                    }, 2000);
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    showStatus(data.message || 'Payment failed. Please try again.', 'error');
                    payButton.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    payButton.classList.remove('opacity-50');
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    showStatus('Payment is taking longer than expected. Please check your transaction history.', 'warning');
                    payButton.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    payButton.classList.remove('opacity-50');
                }
            } catch (error) {
                if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    showStatus('Unable to verify payment status. Please check your transaction history.', 'warning');
                    payButton.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    payButton.classList.remove('opacity-50');
                }
            }
        }, 1000);
    }

    function showStatus(message, type) {
        const statusDiv = document.getElementById('paymentStatus');
        const colors = { success: 'green', error: 'red', warning: 'yellow' };
        const color = colors[type] || 'blue';

        statusDiv.innerHTML = `
            <div class="p-4 rounded-lg bg-${color}-50 border border-${color}-200">
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} text-${color}-600 mr-3"></i>
                    <span class="text-${color}-800 text-sm">${message}</span>
                </div>
            </div>
        `;
        statusDiv.classList.remove('hidden');
    }
});
</script>
@endsection
