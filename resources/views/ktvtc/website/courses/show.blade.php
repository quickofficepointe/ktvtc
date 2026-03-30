@extends('layouts.app')

@section('seo')
    <meta name="description" content="{{ $course->meta_description ?? Str::limit(strip_tags($course->description), 160) }}">
    <meta name="keywords" content="{{ $course->meta_keywords ?? $course->name }}, technical training, vocational courses, Kenya education">
    <meta property="og:title" content="{{ $course->name }} - Kenswed College">
    <meta property="og:description" content="{{ $course->meta_description ?? Str::limit(strip_tags($course->description), 160) }}">
    <meta property="og:image" content="{{ $course->cover_image_url ?? asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $course->name . ' - Kenswed Technical College')

@section('content')
<!-- Course Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-2/3">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                        <li><span class="text-white">/</span></li>
                        <li><a href="{{ route('course.index') }}" class="hover:underline">Courses</a></li>
                        <li><span class="text-white">/</span></li>
                        <li class="text-white font-semibold">{{ $course->name }}</li>
                    </ol>
                </nav>

                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $course->name }}</h1>

                @if($course->department)
                <a href="{{ route('departments.show', $course->department->slug) }}"
                   class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-medium hover:bg-opacity-30 transition-colors">
                    {{ $course->department->name }}
                </a>
                @endif

                <div class="flex flex-wrap gap-4 mt-4">
                    @if($course->duration)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $course->duration }}</span>
                    </div>
                    @endif

                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($course->level == 'beginner') bg-green-500
                            @elseif($course->level == 'intermediate') bg-yellow-500
                            @else bg-red-500 @endif">
                            {{ ucfirst($course->level) }} Level
                        </span>
                    </div>

                    @if($course->featured)
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-500">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Featured
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="md:w-1/3 mt-6 md:mt-0 text-center">
                <a href="{{ route('application.form', ['course_slug' => $course->slug]) }}">APPLY NOW</a>
            </div>
        </div>
    </div>
</section>

<!-- Course Details Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Social Sharing -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Share this course:</span>
                        <div class="flex space-x-3">
                            <!-- Facebook -->
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#1877F2] text-white p-2 rounded-full hover:bg-[#166FE5] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>

                            <!-- WhatsApp -->
                            <a href="https://wa.me/?text={{ urlencode('Check out this course: ' . $course->name . ' - ' . url()->current()) }}"
                               target="_blank"
                               class="bg-[#25D366] text-white p-2 rounded-full hover:bg-[#128C7E] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893c0-3.176-1.24-6.165-3.495-8.411"/>
                                </svg>
                            </a>

                            <!-- LinkedIn -->
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#0A66C2] text-white p-2 rounded-full hover:bg-[#004182] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>

                            <!-- Twitter -->
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode('Check out this course: ' . $course->name) }}&url={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#1DA1F2] text-white p-2 rounded-full hover:bg-[#0d8bd9] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                @if($course->cover_image_url)
                <img src="{{ $course->cover_image_url }}" alt="{{ $course->name }}" class="w-full h-64 object-cover rounded-lg mb-8 shadow-md">
                @endif

                <!-- Description -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-[#B91C1C] pl-3">Course Overview</h2>
                    <div class="prose max-w-none text-gray-600 leading-relaxed">
                        {!! $course->description !!}
                    </div>
                </div>

                <!-- What You Will Learn -->
                @if($course->what_you_will_learn)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-[#B91C1C] pl-3">What You Will Learn</h2>
                    <div class="prose max-w-none text-gray-600 leading-relaxed">
                        {!! $course->what_you_will_learn !!}
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if($course->requirements)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-[#B91C1C] pl-3">Entry Requirements</h2>
                    <div class="prose max-w-none text-gray-600 leading-relaxed">
                        {!! $course->requirements !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Course Details Card -->
                <div class="bg-white rounded-lg p-6 mb-6 shadow-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Course Details</h3>

                    <div class="space-y-4">
                        @if($course->code)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Course Code:</span>
                            <span class="font-semibold text-[#B91C1C]">{{ $course->code }}</span>
                        </div>
                        @endif

                        @if($course->duration)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Duration:</span>
                            <span class="font-semibold text-[#B91C1C]">{{ $course->duration }}</span>
                        </div>
                        @endif

                        @if($course->total_hours)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Total Hours:</span>
                            <span class="font-semibold text-[#B91C1C]">{{ $course->total_hours }}</span>
                        </div>
                        @endif

                        @if($course->schedule)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Schedule:</span>
                            <span class="font-semibold text-[#B91C1C]">{{ $course->schedule }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Delivery Mode:</span>
                            <span class="font-semibold text-[#B91C1C] capitalize">{{ $course->delivery_mode }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Level:</span>
                            <span class="font-semibold text-[#B91C1C] capitalize">{{ $course->level }}</span>
                        </div>
                    </div>
                </div>

                <!-- Intakes Card -->
                @if($course->intakes->count() > 0)
                <div class="bg-white rounded-lg p-6 mb-6 shadow-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Upcoming Intakes</h3>
                    <div class="space-y-3">
                        @foreach($course->intakes as $intake)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-[#B91C1C] transition-colors">
                            <div>
                                <span class="font-semibold text-gray-800">{{ $intake->month }} {{ $intake->year }}</span>
                                @if($intake->application_deadline)
                                <p class="text-sm text-gray-600">Apply by: {{ \Carbon\Carbon::parse($intake->application_deadline)->format('M j, Y') }}</p>
                                @endif
                            </div>
                            <a href="{{ route('application.form') }}"
                               class="bg-[#B91C1C] text-white px-3 py-1 rounded text-sm hover:bg-[#991B1B] transition-colors font-medium">
                                Apply
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Fees Card -->
                @if($course->fees_breakdown)
    <div class="bg-white rounded-lg p-6 shadow-lg border border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Fees Breakdown</h3>
        <div class="prose max-w-none">
            {!! $course->fees_breakdown !!}
        </div>
    </div>
@endif

        <!-- Related Courses -->
        @if($relatedCourses->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-[#B91C1C] pl-3">Related Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedCourses as $relatedCourse)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow border border-gray-200">
                    @if($relatedCourse->cover_image_url)
                    <img src="{{ $relatedCourse->cover_image_url }}" alt="{{ $relatedCourse->name }}" class="w-full h-40 object-cover">
                    @endif
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">{{ $relatedCourse->name }}</h3>
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit(strip_tags($relatedCourse->description), 80) }}</p>
                        <a href="{{ route('courses.show', $relatedCourse->slug) }}"
                           class="text-[#B91C1C] font-semibold text-sm hover:underline flex items-center">
                            Learn More
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
