@extends('layouts.app')

@section('seo')
    <meta name="description" content="Discover Kenswed Mobile Schools - Bringing quality education to communities across Kenya through our innovative mobile school program.">
    <meta name="keywords" content="mobile schools, community education, Kenya education, outreach programs, Kenswed mobile learning">
    <meta property="og:title" content="Mobile Schools - Kenswed Technical College">
    <meta property="og:description" content="Discover Kenswed Mobile Schools - Bringing quality education to communities across Kenya.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Mobile Schools - Kenswed Technical College')

@section('content')
<!-- Mobile Schools Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Mobile Schools</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Bringing quality technical education directly to communities across Kenya through our innovative mobile school program</p>
        </div>
    </div>
</section>

<!-- Mobile Schools Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($mschools->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($mschools as $mschool)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group">
                <!-- Mobile School Image -->
                @if($mschool->cover_image)
    <img src="{{ asset('storage/' . $mschool->cover_image) }}" alt="{{ $mschool->name }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
@else
    <!-- Fallback image -->
@endif

                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 leading-tight group-hover:text-[#B91C1C] transition-colors">{{ $mschool->name }}</h3>

                    <!-- Address -->
                    @if($mschool->address)
                    <div class="flex items-start text-sm text-gray-600 mb-3">
                        <svg class="w-4 h-4 mr-2 text-[#B91C1C] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="flex-1">{{ $mschool->address }}</span>
                    </div>
                    @endif

                    <!-- Description -->
                    @if($mschool->description)
                    <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                        {{ Str::limit($mschool->description, 120) }}
                    </p>
                    @endif

                    <!-- Coordinator Info -->
                    @if($mschool->coordinator_name)
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Coordinator</h4>
                        <div class="space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $mschool->coordinator_name }}
                            </div>

                            @if($mschool->coordinator_phone)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $mschool->coordinator_phone }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex space-x-3 mt-6 pt-4 border-t border-gray-200">
                        @if($mschool->google_map_link)
                        <a href="{{ $mschool->google_map_link }}" target="_blank"
                           class="flex-1 bg-[#B91C1C] text-white text-center py-2.5 px-4 rounded-lg hover:bg-[#991B1B] transition-colors text-sm font-semibold flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            View Map
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Mobile Schools Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently expanding our mobile school program to reach more communities.</p>
            <a href=" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Contact Us for More Info
            </a>
        </div>
        @endif
    </div>
</section>

<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
