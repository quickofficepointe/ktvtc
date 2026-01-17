@extends('layouts.app')

@section('seo')
    <meta name="description" content="Learn about {{ $partner->name }}, a valued partner of Kenswed Technical College.">
    <meta name="keywords" content="{{ $partner->name }}, partner, collaboration, Kenswed College">
    <meta property="og:title" content="{{ $partner->name }} - Kenswed Technical College Partner">
    <meta property="og:description" content="Learn about {{ $partner->name }}, a valued partner of Kenswed Technical College.">
    <meta property="og:image" content="{{ $partner->logo_path ? Storage::url($partner->logo_path) : asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $partner->name . ' - Kenswed Technical College Partner')

@section('content')
<!-- Partner Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('partners.index') }}" class="hover:underline">Partners</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ Str::limit($partner->name, 30) }}</li>
                </ol>
            </nav>

            <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                <!-- Partner Logo -->
                <div class="flex-shrink-0">
                    @if($partner->logo_path)
                        <img src="{{ Storage::url($partner->logo_path) }}"
                             alt="{{ $partner->name }} Logo"
                             class="w-32 h-32 md:w-40 md:h-40 object-contain bg-white rounded-xl p-4 shadow-lg">
                    @else
                        <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-3xl font-bold text-[#B91C1C]">{{ substr($partner->name, 0, 2) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Partner Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $partner->name }}</h1>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        <span class="inline-flex items-center px-3 py-1 bg-white bg-opacity-20 rounded-full">
                            <span class="w-2 h-2 rounded-full mr-2 {{ $partner->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                            {{ $partner->is_active ? 'Active Partner' : 'Inactive Partner' }}
                        </span>
                        @if($partner->website)
                        <a href="{{ $partner->website }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-3 py-1 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Visit Website
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partner Content Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Partnership Details -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Partnership Information</h2>
                            <p class="text-sm text-gray-600">Collaboration details and benefits</p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 md:p-8">
                    <div class="prose max-w-none text-gray-700">
                        <p class="text-lg leading-relaxed">
                            We are proud to partner with <strong>{{ $partner->name }}</strong> to enhance our educational programs and provide valuable opportunities for our students. This collaboration strengthens our commitment to delivering industry-relevant education and creating pathways to successful careers.
                        </p>

                        <!-- Partnership Benefits -->
                        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-red-50 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-[#B91C1C] mb-2">Benefits for Students</h3>
                                <ul class="space-y-2 text-gray-700">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Industry exposure and insights
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Practical learning opportunities
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Career development pathways
                                    </li>
                                </ul>
                            </div>

                            <div class="bg-blue-50 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-blue-600 mb-2">Collaboration Areas</h3>
                                <ul class="space-y-2 text-gray-700">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Curriculum development
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Industry placements
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Knowledge sharing
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- External Link -->
                        @if($partner->website)
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900">Learn More About {{ $partner->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">Visit their official website for more information</p>
                                </div>
                                <a href="{{ $partner->website }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-4 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors font-semibold">
                                    Visit Website
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between mt-8">
                <a href="{{ route('partner.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All Partners
                </a>

                <a href="{{ route('contact') }}"
                   class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Interested in Partnership?
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
