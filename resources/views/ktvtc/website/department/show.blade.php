@extends('layouts.app')

@section('seo')
    <meta name="description" content="{{ $department->description ?? 'Explore courses offered in the ' . $department->name . ' department at Kenswed Technical College.' }}">
    <meta name="keywords" content="{{ $department->name }}, courses, programs, Kenswed College">
    <meta property="og:title" content="{{ $department->name }} - Kenswed Technical College">
    <meta property="og:description" content="{{ $department->description ?? 'Explore courses offered in the ' . $department->name . ' department.' }}">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $department->name . ' - Kenswed Technical College')

@section('content')
<!-- Department Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('home') }}" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Home
                    </a>
                </li>
                <li><span class="text-white text-opacity-70">/</span></li>
                <li>
                    <a href="{{ route('course.index') }}" class="text-white hover:text-gray-200 transition-colors">
                        Courses
                    </a>
                </li>
                <li><span class="text-white text-opacity-70">/</span></li>
                <li class="text-white font-semibold" aria-current="page">{{ $department->name }}</li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $department->name }}</h1>
                @if($department->description)
                <p class="text-lg text-white text-opacity-90 max-w-3xl">{{ $department->description }}</p>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2 text-center">
                    <span class="block text-2xl font-bold">{{ $department->courses->count() }}</span>
                    <span class="text-xs text-white text-opacity-90">Courses</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Department Courses Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="lg:w-3/4">
                <!-- Header with Department Navigation -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">
                        Available Courses
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ $department->courses->count() }} found)</span>
                    </h2>

                    <!-- Departments Navigation Dropdown -->
                    @if($otherDepartments->count() > 0)
                    <div class="relative w-full sm:w-auto">
                        <select onchange="window.location.href=this.value"
                                class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent appearance-none bg-white">
                            <option value="{{ route('departments.show', $department->slug) }}" selected>
                                {{ $department->name }}
                            </option>
                            @foreach($otherDepartments as $otherDept)
                            <option value="{{ route('departments.show', $otherDept->slug) }}">
                                {{ $otherDept->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                    @endif
                </div>

                @if($department->courses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($department->courses as $course)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group">
                        @if($course->cover_image_url)
                        <img src="{{ $course->cover_image_url }}" alt="{{ $course->name }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                        <div class="w-full h-48 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                            <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        @endif

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3 group-hover:text-[#B91C1C] transition-colors">
                                {{ $course->name }}
                            </h3>

                            <!-- Course Badges -->
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                @if($course->duration)
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $course->duration }}
                                </span>
                                @endif

                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                                    @if($course->level == 'beginner') bg-green-100 text-green-800
                                    @elseif($course->level == 'intermediate') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h.01M12 7h.01M11 7h.01M10 7h.01M9 7h.01M8 7h.01M7 7h.01M6 7h.01M5 7h.01M4 7h.01M16 7h.01M17 7h.01M18 7h.01M19 7h.01M20 7h.01M21 7h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ ucfirst($course->level) }}
                                </span>

                                @if($course->featured)
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Featured
                                </span>
                                @endif
                            </div>

                            <!-- Short Description -->
                            <p class="text-gray-700 text-sm leading-relaxed mb-4 line-clamp-2">
                                {{ Str::limit(strip_tags($course->description), 120) }}
                            </p>

                            <!-- Course Code (if available) -->
                            @if($course->code)
                            <p class="text-xs text-gray-500 mb-4">
                                <span class="font-medium">Course Code:</span> {{ $course->code }}
                            </p>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                <a href="{{ route('application.form', ['course_slug' => $course->slug]) }}"
                                   class="inline-flex items-center bg-[#B91C1C] text-white font-semibold px-4 py-2 rounded-lg hover:bg-[#991B1B] transition-all duration-200 text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    APPLY NOW
                                </a>
                                <a href="{{ route('courses.show', $course->slug) }}"
                                   class="inline-flex items-center text-[#B91C1C] font-semibold px-4 py-2 rounded-lg hover:bg-red-50 transition-colors duration-200 text-sm border border-[#B91C1C]">
                                    READ MORE
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination if needed -->
                @if(method_exists($department->courses, 'links'))
                <div class="mt-8">
                    {{ $department->courses->links() }}
                </div>
                @endif

                @else
                <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
                    <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No Courses Available</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our course offerings in this department. Please check back later.</p>
                    <a href="{{ route('course.index') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        View All Courses
                    </a>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:w-1/4">
                <!-- All Departments Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        All Departments
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('course.index') }}"
                               class="flex items-center justify-between p-3 rounded-lg hover:bg-red-50 transition-colors {{ request()->is('courses') ? 'bg-red-50 text-[#B91C1C] font-semibold' : 'text-gray-700' }}">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 {{ request()->is('courses') ? 'text-[#B91C1C]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    All Courses
                                </span>
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                    {{ \App\Models\Course::where('is_active', true)->count() }}
                                </span>
                            </a>
                        </li>
                        @foreach($otherDepartments as $otherDept)
                        <li>
                            <a href="{{ route('departments.show', $otherDept->slug) }}"
                               class="flex items-center justify-between p-3 rounded-lg hover:bg-red-50 transition-colors {{ request()->is('departments/'.$otherDept->slug) ? 'bg-red-50 text-[#B91C1C] font-semibold' : 'text-gray-700' }}">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 {{ request()->is('departments/'.$otherDept->slug) ? 'text-[#B91C1C]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ $otherDept->name }}
                                </span>
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                    {{ $otherDept->courses_count }}
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Quick Apply Card -->
                <div class="bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-xl shadow-lg p-6 text-white text-center border border-[#B91C1C]">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Ready to Apply?</h3>
                    <p class="text-sm text-white text-opacity-90 mb-6">Take the first step towards your future career</p>
                    <a href="{{ route('course.index') }}"
                       class="inline-flex items-center justify-center w-full bg-white text-[#B91C1C] font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-md hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        APPLY NOW
                    </a>
                    <p class="text-xs text-white text-opacity-80 mt-4">No application fee required*</p>
                </div>

                <!-- Need Help Card -->
                <div class="bg-gray-100 rounded-xl shadow-lg p-6 mt-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Need Help?
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Our admissions team is here to assist you</p>
                    <div class="space-y-2 text-sm">
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

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Dropdown arrow styling */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
</style>
@endsection