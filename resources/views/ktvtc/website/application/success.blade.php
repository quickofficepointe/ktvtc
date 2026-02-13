@extends('layouts.app')

@section('seo')
    <meta name="description" content="Application submitted successfully - Kenswed Technical College">
    <meta name="keywords" content="application success, admission, Kenswed College">
    <meta property="og:title" content="Application Submitted - Kenswed Technical College">
    <meta property="og:description" content="Your application has been submitted successfully.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Application Submitted - Kenswed Technical College')

@section('content')
<!-- Success Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Application Submitted!</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Thank you for applying to Kenswed Technical College</p>
        </div>
    </div>
</section>

<!-- Success Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Success Card -->
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-4">Application Received Successfully</h2>

                <p class="text-lg text-gray-600 mb-8">
                    Thank you <span class="font-semibold text-[#B91C1C]">{{ $application->first_name }} {{ $application->last_name }}</span>
                    for applying to our <span class="font-semibold">{{ $application->course->name }}</span> program.
                </p>

                <!-- Application Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8 max-w-md mx-auto">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Application Details</h3>
                    <div class="space-y-3 text-left">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Application Number:</span>
                            <span class="font-semibold text-[#B91C1C]">{{ $application->application_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Course:</span>
                            <span class="font-semibold">{{ $application->course->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Intake Period:</span>
                            <span class="font-semibold">{{ $application->intake_period }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Study Mode:</span>
                            <span class="font-semibold capitalize">{{ str_replace('_', ' ', $application->study_mode) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Submission Date:</span>
                            <span class="font-semibold">{{ $application->submitted_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 max-w-2xl mx-auto">
                    <h3 class="text-xl font-semibold text-blue-800 mb-4">What Happens Next?</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-blue-800 mb-2">Application Review</h4>
                            <p class="text-sm text-blue-700">Our admissions team will review your application within 3-5 business days.</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-blue-800 mb-2">Email Confirmation</h4>
                            <p class="text-sm text-blue-700">You'll receive an email confirmation and further instructions.</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-blue-800 mb-2">Interview (If Required)</h4>
                            <p class="text-sm text-blue-700">Some programs may require an interview or additional assessment.</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8 max-w-2xl mx-auto">
                    <h3 class="text-xl font-semibold text-yellow-800 mb-4">Important Information</h3>
                    <ul class="text-left space-y-2 text-yellow-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <span>Keep your application number (<strong>{{ $application->application_number }}</strong>) for future reference</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Check your email regularly (including spam folder) for updates</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span>Contact admissions if you don't hear back within 5 business days</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('application.form') }}"
                       class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold">
                        Apply for Another Course
                    </a>
                    <a href="{{ route('home') }}"
                       class="border border-[#B91C1C] text-[#B91C1C] px-8 py-3 rounded-lg hover:bg-red-50 transition-colors duration-200 font-semibold">
                        Return to Homepage
                    </a>
                    <button onclick="window.print()"
                            class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 font-semibold">
                        Print Confirmation
                    </button>
                </div>

                <!-- Contact Information -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-gray-600 mb-2">Need help? Contact our admissions office:</p>
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 text-sm">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                         +254 790 148 509
                        </div>
                        <div class="flex items-center text-gray-700">
                            <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            admissions@ktvtc.ac.ke
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Print Styles -->
<style>
    @media print {
        .bg-gradient-to-r { background: #B91C1C !important; }
        .shadow-lg { box-shadow: none !important; }
        .flex { display: block !important; }
        .hidden { display: none !important; }
        .gap-4 > * { margin-bottom: 1rem; }
        .text-center { text-align: center !important; }
    }
</style>
@endsection
