@extends('layouts.app')

@section('title', 'Contact Us | Kenswed Technical College - Ngong, Kenya')
@section('meta_description', 'Get in touch with Kenswed Technical and Vocational Training College in Ngong. Contact us for admissions, course information, partnerships, and general inquiries.')
@section('meta_keywords', 'contact Kenswed College, technical college Ngong, vocational training Kenya, admissions contact, college inquiries, visit Kenswed, campus location')

<!-- Open Graph Tags -->
@section('og_title', 'Contact Kenswed Technical College | Ngong, Kenya')
@section('og_description', 'Reach out to Kenswed College for admissions, course information, and partnerships. Visit our campus in Ngong or send us a message.')
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', 'Contact Kenswed College')
@section('twitter_description', 'Get in touch with Kenswed Technical and Vocational Training College in Ngong for admissions and course information')
@section('twitter_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())

<!-- Structured Data for Contact Page -->
@section('structured_data')

@endsection

@section('content')
    <!-- CONTACT SECTION -->
    <section class="py-16 bg-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Contact Information Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Contact Us</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get in touch with Kenswed Technical College. We're here to help with admissions, course information, and any questions you may have.
                </p>
            </div>

            <!-- Quick Contact Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Call Us</h3>
                    <p class="text-gray-600">+254 790 148 509</p>
                    <p class="text-sm text-gray-500">Mon-Fri, 8:00 AM - 5:00 PM</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Email Us</h3>
                    <p class="text-gray-600">info@ktvtc.ac.ke</p>
                    <p class="text-sm text-gray-500">We'll respond within 24 hours</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Visit Us</h3>
                    <p class="text-gray-600">Ngong, Kajiado County</p>
                    <p class="text-sm text-gray-500">Kenswed Road, Off Ngong Road</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden md:flex md:gap-8">
                <!-- Contact Form Section -->
                <div class="w-full md:w-1/2 p-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8">Get in Touch</h2>

                    {{-- Success message --}}
                    @if(session('success'))
                        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Validation errors --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('messages.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Name -->
                        <div>
                            <input
                                type="text"
                                name="name"
                                placeholder="Your full name"
                                value="{{ old('name') }}"
                                required
                                class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <input
                                type="email"
                                name="email"
                                placeholder="Your email address"
                                value="{{ old('email') }}"
                                required
                                class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                            >
                        </div>

                        <!-- Phone -->
                        <div>
                            <input
                                type="text"
                                name="phone"
                                placeholder="Phone number (optional)"
                                value="{{ old('phone') }}"
                                class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                            >
                        </div>

                        <!-- Message -->
                        <div>
                            <textarea
                                name="message"
                                rows="4"
                                placeholder="Type your message here..."
                                required
                                class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                            >{{ old('message') }}</textarea>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="w-full bg-red-600 text-white font-semibold py-4 rounded-lg hover:bg-red-700 transition-colors duration-300"
                        >
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Google Maps Embed Section -->
                <div class="w-full md:w-1/2">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.712206916075!2d36.638279775896585!3d-1.3491832357000195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f03f903a8baa7%3A0xa70e1a7d03ee96bd!2sKenswed%20Technical%20and%20Vocational%20Training%20College!5e0!3m2!1sen!2ske!4v1757846718629!5m2!1sen!2ske"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Kenswed Technical and Vocational Training College Location Map">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
@endsection
