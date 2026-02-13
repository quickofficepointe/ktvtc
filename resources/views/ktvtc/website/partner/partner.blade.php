@extends('layouts.app')

@section('seo')
    <meta name="description" content="Meet our valued partners and collaborators at Kenswed Technical College. We work with industry leaders to provide quality education and opportunities.">
    <meta name="keywords" content="partners, collaborators, industry partners, educational partners, Kenswed College">
    <meta property="og:title" content="Our Partners - Kenswed Technical College">
    <meta property="og:description" content="Meet our valued partners and collaborators at Kenswed Technical College. We work with industry leaders to provide quality education and opportunities.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Our Partners - Kenswed Technical College')

@section('content')
<!-- Partners Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Our Partners</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Collaborating with industry leaders to provide exceptional educational experiences and career opportunities</p>
        </div>
    </div>
</section>

<!-- Partners Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($partners->count() > 0)
        <div class="max-w-6xl mx-auto">
            <!-- Partners Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($partners as $partner)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group">
                    <!-- Partner Logo -->
                    <div class="p-6 flex items-center justify-center h-48 bg-white">
                        @if($partner->logo_path)
                            <img src="{{ Storage::url($partner->logo_path) }}"
                                 alt="{{ $partner->name }} Logo"
                                 class="max-w-full max-h-32 object-contain group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-32 h-32 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-full flex items-center justify-center">
                                <span class="text-white text-2xl font-bold">{{ substr($partner->name, 0, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-6 border-t border-gray-200">
                        <!-- Status Badge -->
                        <div class="mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $partner->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-800 border border-gray-200' }}">
                                <span class="w-2 h-2 rounded-full mr-2 {{ $partner->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                {{ $partner->is_active ? 'Active Partner' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Partner Name -->
                        <h3 class="text-xl font-bold text-gray-800 mb-3 leading-tight group-hover:text-[#B91C1C] transition-colors">{{ $partner->name }}</h3>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mt-4">
                            @if($partner->website)
                            <a href="{{ $partner->website }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Visit Website
                            </a>
                            @else
                            <span class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed font-semibold text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                No Website
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Partnership Benefits Section -->
            <div class="mt-16 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] px-6 py-8 text-white">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold mb-4">Benefits of Partnership</h2>
                        <p class="text-red-100 text-lg max-w-2xl mx-auto">Our partnerships create valuable opportunities for students and organizations alike</p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Industry Exposure</h3>
                            <p class="text-gray-600">Students gain real-world experience and industry insights</p>
                        </div>

                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Quality Assurance</h3>
                            <p class="text-gray-600">Partnerships ensure our programs meet industry standards</p>
                        </div>

                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Networking</h3>
                            <p class="text-gray-600">Build professional connections and career opportunities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partnership CTA -->
            <div class="mt-12 text-center">
                <div class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] rounded-2xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Interested in Partnering With Us?</h3>
                    <p class="text-red-100 mb-6 max-w-md mx-auto">Join our network of industry partners and help shape the future of technical education.</p>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center bg-white text-[#B91C1C] px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Become a Partner
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Partners Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently building our network of industry partners. Please check back later.</p>
            <a href="/" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                Return to Homepage
            </a>
        </div>
        @endif
    </div>
</section>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
