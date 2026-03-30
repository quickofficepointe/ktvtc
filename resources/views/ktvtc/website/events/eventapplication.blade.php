@extends('layouts.app')

@section('title', 'Apply for ' . $event->title . ' | Kenswed Technical College')
@section('meta_description', 'Register for ' . $event->title . ' at Kenswed College. Event date: ' . \Carbon\Carbon::parse($event->event_start_date)->format('F j, Y') . '. Location: ' . $event->location . '. Complete your application online.')
@section('meta_keywords', $event->title . ' registration, event application, Kenswed College events, ' . $event->event_type . ' registration, student events Kenya, technical workshop application')

<!-- Open Graph Tags -->
@section('og_title', 'Apply for ' . $event->title . ' | Kenswed College')
@section('og_description', 'Register online for ' . $event->title . ' at Kenswed Technical College. ' . ($event->short_description ?: 'Join our upcoming event.'))
@section('og_url', url()->current())
@section('og_image', $event->banner_image ? Storage::url($event->banner_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', 'Apply: ' . $event->title)
@section('twitter_description', 'Register for ' . $event->title . ' at Kenswed College - ' . \Carbon\Carbon::parse($event->event_start_date)->format('M j, Y'))
@section('twitter_image', $event->banner_image ? Storage::url($event->banner_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())

@section('content')
<!-- Application Header -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto">
            <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center text-white text-sm font-semibold mb-4 hover:underline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Event Details
            </a>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Apply for {{ $event->title }}</h1>
            <p class="text-xl opacity-90">Complete the form below to register for this event</p>
        </div>
    </div>
</section>

<!-- Application Form -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Event Summary -->
                <div class="bg-gray-50 border-b border-gray-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $event->title }}</h2>
                            <p class="text-gray-600 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($event->event_start_date)->format('l, F j, Y') }}
                                @if($event->event_start_date->format('H:i') != '00:00')
                                    at {{ $event->event_start_date->format('g:i A') }}
                                @endif
                            </p>
                            <p class="text-gray-600">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $event->location }}
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0 text-right">
                            @if($event->is_paid)
                                @if($event->early_bird_price && $event->early_bird_end_date > now())
                                    <div class="text-2xl font-bold text-green-800">KSh {{ number_format($event->early_bird_price) }}</div>
                                    <div class="text-sm text-green-600">Early Bird Price (per person)</div>
                                    <div class="text-xs text-gray-500 mt-1">Early bird ends: {{ $event->early_bird_end_date->format('M j, Y') }}</div>
                                @else
                                    <div class="text-2xl font-bold text-gray-800">KSh {{ number_format($event->price) }}</div>
                                    <div class="text-sm text-gray-600">Regular Price (per person)</div>
                                @endif
                            @else
                                <div class="text-2xl font-bold text-green-800">Free</div>
                                <div class="text-sm text-green-600">No registration fee</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Status Alert -->
                <div id="payment-alert" class="hidden m-6"></div>

                <!-- Application Form -->
                <form id="event-application-form" class="p-6">
                    @csrf

                    <!-- Parent/Guardian Information -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Parent/Guardian Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Parent Name -->
                            <div>
                                <label for="parent_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                                <input type="text"
                                       id="parent_name"
                                       name="parent_name"
                                       value="{{ old('parent_name') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your full name"
                                       required>
                                <div id="parent_name_error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>

                            <!-- Parent Contact -->
                            <div>
                                <label for="parent_contact" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel"
                                       id="parent_contact"
                                       name="parent_contact"
                                       value="{{ old('parent_contact') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200"
                                       placeholder="e.g., 0712 345 678"
                                       required>
                                <div id="parent_contact_error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>

                            <!-- Parent Email -->
                            <div class="md:col-span-2">
                                <label for="parent_email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                                <input type="email"
                                       id="parent_email"
                                       name="parent_email"
                                       value="{{ old('parent_email') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your email address"
                                       required>
                                <div id="parent_email_error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Number of People -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Number of Participants</h3>

                        <div class="max-w-xs">
                            <label for="number_of_people" class="block text-sm font-semibold text-gray-700 mb-2">How many people are you registering? *</label>
                            <select id="number_of_people"
                                    name="number_of_people"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200"
                                    required>
                                <option value="">Select number</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('number_of_people') == $i ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'person' : 'people' }}</option>
                                @endfor
                            </select>
                            <div id="number_of_people_error" class="text-red-600 text-sm mt-1 hidden"></div>
                        </div>
                    </div>

                    <!-- Attendees Information -->
                    <div id="attendees-section" class="mb-8 hidden">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Participants Information</h3>
                        <p class="text-gray-600 mb-6">Please provide details for each participant you're registering.</p>

                        <div id="attendees-container" class="space-y-6">
                            <!-- Attendee fields will be dynamically added here -->
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($event->is_paid)
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Payment Information</h3>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-blue-800 mb-2">KCB M-Pesa Payment</h4>
                                    <p class="text-blue-700 text-sm mb-2">
                                        After submitting the form, you will receive an M-Pesa prompt on your phone to complete the payment.
                                        Please ensure your phone is nearby and ready to authorize the payment.
                                    </p>
                                    <p class="text-blue-600 text-xs">
                                        <strong>Note:</strong> You will be charged KSh <span id="payment-amount">0</span> for {{ $event->title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Summary -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Order Summary</h3>

                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-600">Number of participants:</span>
                                <span id="summary-count" class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-600">Price per person:</span>
                                <span class="font-semibold">
                                    @if($event->is_paid)
                                        @if($event->early_bird_price && $event->early_bird_end_date > now())
                                            KSh {{ number_format($event->early_bird_price) }}
                                        @else
                                            KSh {{ number_format($event->price) }}
                                        @endif
                                    @else
                                        Free
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                                <span class="text-lg font-bold text-gray-800">Total Amount:</span>
                                <span id="summary-total" class="text-lg font-bold text-[#B91C1C]">KSh 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            By submitting this form, you agree to our
                            <a href="#" class="text-[#B91C1C] hover:underline">Terms of Service</a> and
                            <a href="#" class="text-[#B91C1C] hover:underline">Privacy Policy</a>.
                        </p>

                        <button type="submit"
                                id="submit-btn"
                                class="bg-[#B91C1C] text-white px-8 py-4 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors duration-200 focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submit-text">
                                @if($event->is_paid)
                                    Pay & Submit Application
                                @else
                                    Submit Application
                                @endif
                            </span>
                            <span id="submit-loading" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Payment Processing Modal -->
                <div id="payment-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Payment Required</h3>
                            <div class="mt-2 px-7 py-3">
                                <p class="text-sm text-gray-500 mb-2">
                                    Please check your phone for an M-Pesa prompt to complete your registration.
                                </p>
                                <p class="text-xs text-gray-400">
                                    Amount: <strong id="modal-amount">KSh 0</strong>
                                </p>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button id="check-payment-status"
                                        class="px-4 py-2 bg-[#B91C1C] text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-[#991B1B] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] mb-2">
                                    Check Payment Status
                                </button>
                                <button id="close-modal"
                                        class="px-4 py-2 border border-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('event-application-form');
    const numberOfPeopleSelect = document.getElementById('number_of_people');
    const attendeesSection = document.getElementById('attendees-section');
    const attendeesContainer = document.getElementById('attendees-container');
    const summaryCount = document.getElementById('summary-count');
    const summaryTotal = document.getElementById('summary-total');
    const paymentAmount = document.getElementById('payment-amount');
    const modalAmount = document.getElementById('modal-amount');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    const paymentModal = document.getElementById('payment-modal');
    const checkPaymentBtn = document.getElementById('check-payment-status');
    const closeModalBtn = document.getElementById('close-modal');
    const paymentAlert = document.getElementById('payment-alert');

    const pricePerPerson = {{ $event->is_paid ? ($event->early_bird_price && $event->early_bird_end_date > now() ? $event->early_bird_price : $event->price) : 0 }};
    let currentCheckoutRequestId = null;
    let paymentStatusInterval = null;

    function updateSummary() {
        const count = parseInt(numberOfPeopleSelect.value) || 0;
        const total = count * pricePerPerson;

        summaryCount.textContent = count;
        summaryTotal.textContent = `KSh ${total.toLocaleString()}`;
        paymentAmount.textContent = total.toLocaleString();
        modalAmount.textContent = `KSh ${total.toLocaleString()}`;
    }

    function generateAttendeeFields(count) {
        attendeesContainer.innerHTML = '';

        for (let i = 0; i < count; i++) {
            const attendeeHtml = `
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4">Participant ${i + 1}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="attendees[${i}][name]" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text"
                                   id="attendees[${i}][name]"
                                   name="attendees[${i}][name]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                   placeholder="Full name"
                                   required>
                            <div id="attendees_${i}_name_error" class="text-red-600 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label for="attendees[${i}][school]" class="block text-sm font-medium text-gray-700 mb-1">School *</label>
                            <input type="text"
                                   id="attendees[${i}][school]"
                                   name="attendees[${i}][school]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                   placeholder="School name"
                                   required>
                            <div id="attendees_${i}_school_error" class="text-red-600 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label for="attendees[${i}][age]" class="block text-sm font-medium text-gray-700 mb-1">Age *</label>
                            <input type="number"
                                   id="attendees[${i}][age]"
                                   name="attendees[${i}][age]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                   placeholder="Age"
                                   min="3"
                                   max="25"
                                   required>
                            <div id="attendees_${i}_age_error" class="text-red-600 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                </div>
            `;
            attendeesContainer.insertAdjacentHTML('beforeend', attendeeHtml);
        }
    }

    function showAlert(message, type = 'error') {
        const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
        const borderColor = type === 'success' ? 'border-green-200' : 'border-red-200';

        paymentAlert.innerHTML = `
            <div class="${bgColor} border ${borderColor} rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 ${textColor} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' ?
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                        }
                    </svg>
                    <span class="${textColor} text-sm font-medium">${message}</span>
                </div>
            </div>
        `;
        paymentAlert.classList.remove('hidden');

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                hideAlert();
            }, 5000);
        }
    }

    function hideAlert() {
        paymentAlert.classList.add('hidden');
    }

    function clearErrors() {
        document.querySelectorAll('[id$="_error"]').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
    }

    function setLoading(loading) {
        if (loading) {
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
        } else {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
        }
    }

    function startPaymentStatusPolling(checkoutRequestId) {
        // Clear any existing interval
        if (paymentStatusInterval) {
            clearInterval(paymentStatusInterval);
        }

        // Check status every 5 seconds
        paymentStatusInterval = setInterval(() => {
            checkPaymentStatus(checkoutRequestId);
        }, 5000);
    }

    function stopPaymentStatusPolling() {
        if (paymentStatusInterval) {
            clearInterval(paymentStatusInterval);
            paymentStatusInterval = null;
        }
    }

    async function checkPaymentStatus(checkoutRequestId) {
        try {
            const response = await fetch('{{ route("event.payment.status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    checkout_request_id: checkoutRequestId
                })
            });

            const result = await response.json();

            if (result.success) {
                if (result.status === 'completed') {
                    stopPaymentStatusPolling();
                    paymentModal.classList.add('hidden');
                    showAlert('Payment completed successfully! Your registration is now confirmed.', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("event.application.success") }}?application=' + result.application_id;
                    }, 2000);
                } else if (result.status === 'failed') {
                    stopPaymentStatusPolling();
                    paymentModal.classList.add('hidden');
                    showAlert('Payment failed: ' + (result.result_description || 'Please try again.'));
                }
                // If still initiated, do nothing - continue polling
            } else {
                showAlert('Unable to check payment status. Please try again.');
            }
        } catch (error) {
            console.error('Error checking payment status:', error);
            showAlert('Error checking payment status. Please try again.');
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        hideAlert();
        setLoading(true);

        const formData = new FormData(form);
        const data = {
            parent_name: formData.get('parent_name'),
            parent_contact: formData.get('parent_contact'),
            parent_email: formData.get('parent_email'),
            number_of_people: parseInt(formData.get('number_of_people')),
            attendees: []
        };

        // Validate attendees data
        let hasErrors = false;
        for (let i = 0; i < data.number_of_people; i++) {
            const name = formData.get(`attendees[${i}][name]`);
            const school = formData.get(`attendees[${i}][school]`);
            const age = formData.get(`attendees[${i}][age]`);

            if (!name || !school || !age) {
                hasErrors = true;
                break;
            }

            data.attendees.push({
                name: name,
                school: school,
                age: parseInt(age)
            });
        }

        if (hasErrors) {
            showAlert('Please fill in all attendee details.');
            setLoading(false);
            return;
        }

        try {
            const response = await fetch('{{ route("event.payment.process", $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                if (result.requires_payment) {
                    currentCheckoutRequestId = result.checkout_request_id;
                    paymentModal.classList.remove('hidden');
                    startPaymentStatusPolling(currentCheckoutRequestId);
                    showAlert('Payment request sent! Please check your phone for the M-Pesa prompt.', 'success');
                } else {
                    showAlert('Application submitted successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("event.application.success") }}?application=' + result.application_id;
                    }, 2000);
                }
            } else {
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const errorElement = document.getElementById(field + '_error');
                        if (errorElement) {
                            errorElement.textContent = result.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                } else {
                    showAlert(result.message || 'An error occurred. Please try again.');
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            showAlert('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    });

    checkPaymentBtn.addEventListener('click', function() {
        if (currentCheckoutRequestId) {
            checkPaymentStatus(currentCheckoutRequestId);
        }
    });

    closeModalBtn.addEventListener('click', function() {
        paymentModal.classList.add('hidden');
        stopPaymentStatusPolling();
    });

    numberOfPeopleSelect.addEventListener('change', function() {
        const count = parseInt(this.value);

        if (count > 0) {
            generateAttendeeFields(count);
            attendeesSection.classList.remove('hidden');
            updateSummary();
        } else {
            attendeesSection.classList.add('hidden');
            attendeesContainer.innerHTML = '';
            updateSummary();
        }
    });

    // Initialize form if there are old values
    const oldNumberOfPeople = {{ old('number_of_people', 0) }};
    if (oldNumberOfPeople > 0) {
        numberOfPeopleSelect.value = oldNumberOfPeople;
        generateAttendeeFields(oldNumberOfPeople);
        attendeesSection.classList.remove('hidden');
        updateSummary();
    }

    // Initialize summary on page load
    updateSummary();
});
</script>

<style>
input:focus, select:focus {
    outline: none;
    ring: 2px;
}
</style>
@endsection
