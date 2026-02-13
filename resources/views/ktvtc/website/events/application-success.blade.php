@extends('layouts.app')

@section('title', 'Application Submitted | Kenswed Technical College')

@section('content')
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Application Submitted Successfully!</h1>
        <p class="text-xl opacity-90">Thank you for registering for our event</p>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
            @if(isset($application))
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Application #{{ $application->id }}</h2>
                <p class="text-gray-600 mb-4">For: {{ $application->event->title }}</p>

                @if($application->application_status === 'pending_payment')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-yellow-800">
                        <strong>Payment Pending:</strong> Please check your phone for the M-Pesa prompt to complete your registration.
                    </p>
                </div>
                @elseif($application->application_status === 'confirmed')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800">
                        <strong>Payment Confirmed!</strong> Your registration is now complete.
                    </p>
                    @if($application->mpesa_reference_number)
                    <p class="text-green-700 mt-2">M-Pesa Receipt: {{ $application->mpesa_reference_number }}</p>
                    @endif
                </div>
                @endif
            </div>
            @else
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Application Submitted!</h2>
                <p class="text-gray-600">Your application has been received successfully.</p>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('events.index') }}"
                   class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors">
                   Browse More Events
                </a>
                <a href="{{ url('/') }}"
                   class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                   Back to Home
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
