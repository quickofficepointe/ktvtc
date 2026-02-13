@extends('layouts.app')

@section('seo')
    <meta name="description" content="Explore our comprehensive range of technical and vocational courses at Kenswed College. Kickstart your career with industry-relevant training programs.">
    <meta name="keywords" content="technical courses, vocational training, Kenya education, career courses, skills development">
    <meta property="og:title" content="Our Courses - Kenswed Technical College">
    <meta property="og:description" content="Explore our comprehensive range of technical and vocational courses at Kenswed College.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Our Courses - Kenswed Technical College')

@section('content')
<!-- OUR COURSES SECTION -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-4">Our Courses</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kickstart your career with industry-relevant technical and vocational training programs designed for success.</p>

            <!-- Departments Filter -->
            @if($departments->count() > 0)
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('courses.index') }}"
                   class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                          {{ request()->is('courses') ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                    All Courses
                </a>
                @foreach($departments as $dept)
                <a href="{{ route('departments.show', $dept->slug) }}"
                   class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                          {{ request()->is('departments/'.$dept->slug) ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                    {{ $dept->name }} ({{ $dept->courses_count }})
                </a>
                @endforeach
            </div>
            @endif
        </div>

        @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($courses as $course)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20">
                <!-- Course Image -->
                @if($course->cover_image_url)
                    <img src="{{ $course->cover_image_url }}" alt="{{ $course->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                        <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif

                <div class="p-6">
                    <!-- Department Badge -->
                    @if($course->department)
                    <div class="mb-3">
                        <a href="{{ route('departments.show', $course->department->slug) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-[#B91C1C] border border-red-200 hover:bg-red-100 transition-colors">
                            {{ $course->department->name }}
                        </a>
                    </div>
                    @endif

                    <h3 class="text-xl font-bold text-gray-800 mb-2 leading-tight">{{ $course->name }}</h3>

                    <!-- Course Code -->
                    @if($course->code)
                    <p class="text-sm text-gray-600 mb-2 font-medium">Code: <span class="text-[#B91C1C]">{{ $course->code }}</span></p>
                    @endif

                    <!-- Duration -->
                    <div class="flex items-center text-sm text-gray-700 mb-3">
                        <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Duration: <span class="font-semibold text-[#B91C1C] ml-1">{{ $course->duration ?? 'Flexible' }}</span>
                    </div>

                    <!-- Level & Featured Badges -->
                    <div class="mb-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            @if($course->level == 'beginner') bg-green-100 text-green-800 border border-green-200
                            @elseif($course->level == 'intermediate') bg-yellow-100 text-yellow-800 border border-yellow-200
                            @else bg-red-100 text-red-800 border border-red-200 @endif">
                            {{ ucfirst($course->level) }} Level
                        </span>

                        @if($course->featured)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Featured
                        </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                        {{ Str::limit(strip_tags($course->description), 120) }}
                    </p>

                    <!-- Schedule -->
                    @if($course->schedule)
                    <div class="flex items-center text-xs text-gray-600 mb-3">
                        <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $course->schedule }}
                    </div>
                    @endif

                    <!-- Intakes -->
                    @if($course->intakes->count() > 0)
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Next Intakes:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($course->intakes->take(2) as $intake)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                {{ $intake->month }} {{ $intake->year }}
                            </span>
                            @endforeach
                            @if($course->intakes->count() > 2)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                +{{ $course->intakes->count() - 2 }} more
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                      <a href="{{ route('application.form', ['course_slug' => $course->slug]) }}">APPLY NOW</a>
                        <a href="{{ route('courses.show', $course->slug) }}"
                           class="text-[#B91C1C] font-semibold px-4 py-2.5 rounded-lg hover:bg-red-50 transition-all duration-200 text-sm border border-[#B91C1C] hover:border-[#991B1B] flex items-center">
                            READ MORE
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($courses->hasPages())
        <div class="mt-12 flex justify-center">
            <div class="flex space-x-2">
                {{-- Previous Page Link --}}
                @if($courses->onFirstPage())
                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Previous</span>
                @else
                <a href="{{ $courses->previousPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Previous</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach($courses->getUrlRange(1, $courses->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="px-4 py-2 rounded-lg transition-colors {{ $courses->currentPage() == $page ? 'bg-[#B91C1C] text-white' : 'bg-white text-[#B91C1C] border border-[#B91C1C] hover:bg-red-50' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next Page Link --}}
                @if($courses->hasMorePages())
                <a href="{{ $courses->nextPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Next</a>
                @else
                <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
        @endif

        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Courses Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our course offerings. Please check back later for new programs.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Return to Homepage
            </a>
        </div>
        @endif
    </div>
</section>

<!-- STUDENT LIFE SECTION -->
<section class="py-20 relative overflow-hidden bg-gradient-to-r from-[#B91C1C] to-[#BF1F30]">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="relative z-10 container mx-auto px-4">
        <div class="text-center text-white mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Experience Student Life</h2>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Join a vibrant community of learners and build lifelong connections</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white border border-white/20">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Vibrant Community</h3>
                <p class="opacity-90">Join over 1,000 students in a supportive and inclusive learning environment.</p>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white border border-white/20">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Modern Facilities</h3>
                <p class="opacity-90">State-of-the-art labs and learning spaces designed for hands-on training.</p>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white border border-white/20">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Career Support</h3>
                <p class="opacity-90">Comprehensive career services including internships and job placement assistance.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION
<section class="relative py-20 bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden">
    <div class="absolute inset-0 bg-[url('Assets/images/CTA_bg.png')] bg-cover bg-center opacity-10"></div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Ready to Start Your Rewarding Career?</h2>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">Take the first step towards your future with industry-relevant training at Kenswed College.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href=""
               class="bg-[#B91C1C] text-white font-bold px-8 py-4 rounded-lg hover:bg-[#991B1B] transition-all duration-200 text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                APPLY NOW!
            </a>
            <a href=""
               class="bg-transparent text-white font-semibold px-8 py-4 rounded-lg hover:bg-white/10 transition-all duration-200 text-lg border-2 border-white">
                CONTACT US
            </a>
        </div>
    </div>
</section>
-->
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
