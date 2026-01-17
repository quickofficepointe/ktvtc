@extends('layouts.app')

@section('seo')
    @if($aboutPage)
        <meta name="description" content="{{ $aboutPage->meta_description ?? 'Learn about Kenswed Technical College - our mission, vision, values, and commitment to quality technical education in Kenya.' }}">
        <meta name="keywords" content="about us, mission, vision, values, Kenswed College, technical education, Kenya">
        <meta property="og:title" content="{{ $aboutPage->meta_title ?? 'About Us - Kenswed Technical College' }}">
        <meta property="og:description" content="{{ $aboutPage->meta_description ?? 'Learn about Kenswed Technical College - our mission, vision, values, and commitment to quality technical education.' }}">
        <meta property="og:image" content="{{ $aboutPage->banner_image ?? asset('Assets/images/Kenswed_logo.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="description" content="Learn about Kenswed Technical College - our mission, vision, values, and commitment to quality technical education in Kenya.">
        <meta name="keywords" content="about us, mission, vision, values, Kenswed College, technical education, Kenya">
        <meta property="og:title" content="About Us - Kenswed Technical College">
        <meta property="og:description" content="Learn about Kenswed Technical College - our mission, vision, values, and commitment to quality technical education.">
        <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
    @endif
@endsection

@section('title', $aboutPage->meta_title ?? 'About Us - Kenswed Technical College')

@section('content')
<!-- About Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-20 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">About Kenswed</h1>
            <p class="text-xl md:text-2xl opacity-90 leading-relaxed">
                Transforming Lives Through Quality Technical and Vocational Education
            </p>
        </div>
    </div>
</section>

<!-- Our Story Section -->
@if($aboutPage && $aboutPage->our_story)
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="lg:pr-8">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <div class="prose prose-lg text-gray-600 leading-relaxed">
                        {!! $aboutPage->our_story !!}
                    </div>
                </div>

                <!-- Image -->
                <div class="lg:pl-8">
                    @if($aboutImages->count() > 0)
                        <img src="{{ $aboutImages->first()->image_path }}"
                             alt="Kenswed College Campus"
                             class="w-full h-96 object-cover rounded-2xl shadow-2xl">
                    @else
                        <div class="w-full h-96 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl shadow-2xl flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Mission & Vision Section -->
@if($aboutPage && ($aboutPage->mission || $aboutPage->vision))
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mission -->
                @if($aboutPage->mission)
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    </div>
                    <p class="text-gray-600 text-lg leading-relaxed text-center">
                        {{ $aboutPage->mission }}
                    </p>
                </div>
                @endif

                <!-- Vision -->
                @if($aboutPage->vision)
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    </div>
                    <p class="text-gray-600 text-lg leading-relaxed text-center">
                        {{ $aboutPage->vision }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

<!-- Core Values Section -->
@if($aboutPage && $aboutPage->core_values)
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Core Values</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">The principles that guide everything we do</p>
            </div>

            <div class="prose prose-lg max-w-none text-gray-600 leading-relaxed">
                {!! $aboutPage->core_values !!}
            </div>
        </div>
    </div>
</section>
@endif

<!-- Gallery Section -->
@if($aboutImages->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Campus Life</h2>
                <p class="text-xl text-gray-600">Experience the vibrant environment at Kenswed College</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($aboutImages as $image)
                <div class="group relative bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    <img src="{{ $image->image_path }}"
                         alt="{{ $image->caption ?? 'Kenswed College' }}"
                         class="w-full h-64 object-cover">

                    @if($image->caption)
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-end">
                        <div class="p-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 w-full">
                            <p class="text-sm font-medium">{{ $image->caption }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- Video Section -->
@if($aboutPage && $aboutPage->video_url)
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Watch Our Story</h2>
                <p class="text-xl text-gray-600">See what makes Kenswed College unique</p>
            </div>

            <div class="bg-gray-900 rounded-2xl overflow-hidden shadow-2xl">
                <div class="aspect-w-16 aspect-h-9">
                    <iframe src="{{ $aboutPage->video_url }}"
                            class="w-full h-96"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Stats Section -->
<section class="py-16 bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl md:text-4xl font-bold mb-2">500+</div>
                    <div class="text-red-100 font-medium">Students Enrolled</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold mb-2">50+</div>
                    <div class="text-red-100 font-medium">Courses Offered</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold mb-2">100+</div>
                    <div class="text-red-100 font-medium">Successful Graduates</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold mb-2">10+</div>
                    <div class="text-red-100 font-medium">Years of Excellence</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Ready to Start Your Journey?</h2>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Join the Kenswed family and take the first step towards a rewarding career in technical education.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('courses.index') }}"
                   class="inline-flex items-center px-8 py-4 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-lg">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Explore Our Courses
                </a>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center px-8 py-4 border-2 border-[#B91C1C] text-[#B91C1C] rounded-lg hover:bg-[#B91C1C] hover:text-white transition-colors duration-200 font-semibold text-lg">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .prose {
        max-width: none;
    }

    .prose p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
    }

    .prose ul, .prose ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }

    .prose li {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .prose strong {
        font-weight: 600;
        color: #374151;
    }

    .prose h2, .prose h3 {
        color: #1f2937;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .aspect-w-16 {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
    }

    .aspect-w-16 iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endsection
