@extends('layouts.app')

@section('title', 'Application Fee Payment - Kenswed Technical College')

@section('content')
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Application Fee Payment</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Pay KES 500 registration fee to complete your application</p>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">

            <!-- Application Summary -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Application Summary</h3>
                <div class="space-y-2">
                    <p><span class="text-gray-600">Application No:</span> <span class="font-semibold">{{ $application->application_number }}</span></p>
                    <p><span class="text-gray-600">Name:</span> <span class="font-semibold">{{ $application->first_name }} {{ $application->last_name }}</span></p>
                    <p><span class="text-gray-600">Course:</span> <span class="font-semibold">{{ $application->course->name }}</span></p>
                    <p><span class="text-gray-600">Amount:</span> <span class="font-semibold text-[#B91C1C]">KES 500.00</span></p>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h3>

                <div id="paymentForm">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">M-Pesa Phone Number *</label>
                        <input type="text" id="phoneNumber"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                               placeholder="e.g., 0712345678"
                               value="{{ $application->phone }}">
                        <p class="text-sm text-gray-500 mt-1">Enter the M-Pesa registered phone number</p>
                    </div>

                    <button type="button" id="payButton"
                            class="w-full bg-[#B91C1C] text-white px-6 py-4 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-lg shadow-md">
                        Pay KES 500 via M-Pesa
                    </button>
                </div>

                <!-- Processing State -->
                <div id="processingState" class="hidden text-center py-8">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-[#B91C1C] mx-auto mb-4"></div>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">Processing Payment</h4>
                    <p class="text-gray-600 mb-2">Please check your phone and enter your M-Pesa PIN to complete the payment.</p>
                    <p class="text-sm text-gray-500">Do not close this window</p>

                    <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            If you don't receive the M-Pesa prompt within 30 seconds, please wait while we check the status.
                        </p>
                    </div>
                </div>

                <!-- Success State -->
                <div id="successState" class="hidden text-center py-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">Payment Successful!</h4>
                    <p class="text-gray-600 mb-4">Your application fee has been received.</p>
                    <p id="receiptInfo" class="text-sm text-green-600 mb-4"></p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('application.success', $application->id) }}"
                           class="inline-block bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors">
                            Continue to Application
                        </a>
                        <button onclick="window.location.reload()"
                                class="inline-block border border-[#B91C1C] text-[#B91C1C] px-8 py-3 rounded-lg hover:bg-red-50 transition-colors">
                            Pay Another
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                <div id="errorState" class="hidden text-center py-8">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">Payment Failed</h4>
                    <p id="errorMessage" class="text-gray-600 mb-4"></p>
                    <button onclick="resetPayment()"
                            class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors">
                        Try Again
                    </button>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Payment Instructions
                </h4>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                    <li>Enter your M-Pesa registered phone number</li>
                    <li>Click "Pay KES 500 via M-Pesa"</li>
                    <li>You will receive an M-Pesa prompt on your phone</li>
                    <li>Enter your M-Pesa PIN to authorize the payment</li>
                    <li>Wait for confirmation - do not close this window</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
let paymentInterval;
let checkoutRequestId;
let pollingAttempts = 0;
const MAX_POLLING_ATTEMPTS = 30;

document.getElementById('payButton').addEventListener('click', function() {
    let phoneNumber = document.getElementById('phoneNumber').value.trim();

    if (!phoneNumber) {
        Swal.fire({
            icon: 'warning',
            title: 'Phone Number Required',
            text: 'Please enter your M-Pesa registered phone number',
            confirmButtonColor: '#B91C1C'
        });
        return;
    }

    // Format phone number to international format
    let formattedPhone = phoneNumber.replace(/\D/g, '');
    if (formattedPhone.startsWith('0')) {
        formattedPhone = '254' + formattedPhone.substring(1);
    }
    if (!formattedPhone.startsWith('254')) {
        formattedPhone = '254' + formattedPhone;
    }

    // Validate phone number length
    if (formattedPhone.length !== 12) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Phone Number',
            text: 'Please enter a valid 10-digit phone number (e.g., 0712345678)',
            confirmButtonColor: '#B91C1C'
        });
        return;
    }

    // Disable button and show processing
    const payBtn = document.getElementById('payButton');
    payBtn.disabled = true;
    payBtn.innerHTML = '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div> Sending STK Push...</div>';

    // Show processing state
    document.getElementById('paymentForm').classList.add('hidden');
    document.getElementById('processingState').classList.remove('hidden');

    // Initiate payment
    fetch('{{ route("application.payment.initiate", $application->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone_number: formattedPhone })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            checkoutRequestId = data.checkout_request_id;
            // Start checking payment status
            paymentInterval = setInterval(checkPaymentStatus, 3000);
            pollingAttempts = 0;
        } else {
            showError(data.message || 'Payment initiation failed');
            resetPaymentButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Network error. Please check your connection and try again.');
        resetPaymentButton();
    });
});

function checkPaymentStatus() {
    pollingAttempts++;

    if (!checkoutRequestId) return;

    fetch('{{ route("application.payment.status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ checkout_request_id: checkoutRequestId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.status === 'completed') {
            // Payment completed successfully
            clearInterval(paymentInterval);
            document.getElementById('processingState').classList.add('hidden');
            document.getElementById('successState').classList.remove('hidden');

            // Show receipt info
            const receiptInfo = document.getElementById('receiptInfo');
            if (data.mpesa_receipt_number) {
                receiptInfo.innerHTML = `Receipt Number: <strong>${data.mpesa_receipt_number}</strong>`;
            }

            // Auto redirect after 5 seconds
            setTimeout(() => {
                window.location.href = '{{ route("application.success", $application->id) }}';
            }, 5000);

        } else if (data.status === 'failed') {
            // Payment failed
            clearInterval(paymentInterval);
            showError(data.message || 'Payment failed. Please try again.');

        } else if (pollingAttempts >= MAX_POLLING_ATTEMPTS) {
            // Timeout after 90 seconds
            clearInterval(paymentInterval);
            showError('Payment timeout. Please check your M-Pesa messages or try again.');
        }
        // If pending, continue checking
    })
    .catch(error => {
        console.error('Error checking status:', error);
        if (pollingAttempts >= MAX_POLLING_ATTEMPTS) {
            clearInterval(paymentInterval);
            showError('Unable to verify payment status. Please check your M-Pesa messages.');
        }
    });
}

function showError(message) {
    clearInterval(paymentInterval);
    document.getElementById('processingState').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message;

    Swal.fire({
        icon: 'error',
        title: 'Payment Failed',
        text: message,
        confirmButtonColor: '#B91C1C'
    });
}

function resetPaymentButton() {
    const payBtn = document.getElementById('payButton');
    payBtn.disabled = false;
    payBtn.innerHTML = 'Pay KES 500 via M-Pesa';
}

function resetPayment() {
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('paymentForm').classList.remove('hidden');
    document.getElementById('processingState').classList.add('hidden');
    document.getElementById('successState').classList.add('hidden');
    resetPaymentButton();
    pollingAttempts = 0;
    checkoutRequestId = null;
    if (paymentInterval) clearInterval(paymentInterval);
}
</script>
@endsection
