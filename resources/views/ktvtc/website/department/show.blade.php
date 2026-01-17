@extends('layouts.app')

@section('content')
<!-- Department Hero Section -->
<section class="bg-gradient-to-r from-primary to-red-600 py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                <li><span class="text-white">/</span></li>
                <li><a href="{{ route('courses.index') }}" class="hover:underline">Courses</a></li>
                <li><span class="text-white">/</span></li>
                <li class="text-white font-semibold">{{ $department->name }}</li>
            </ol>
        </nav>

        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $department->name }}</h1>

        @if($department->description)
        <p class="text-lg text-white text-opacity-90 max-w-3xl">{{ $department->description }}</p>
        @endif
    </div>
</section>

<!-- Department Courses Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="lg:w-3/4">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Courses ({{ $department->courses->count() }})</h2>

                    <!-- Departments Navigation -->
                    @if($otherDepartments->count() > 0)
                    <div class="relative">
                        <select onchange="window.location.href=this.value"
                                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="{{ route('departments.show', $department->slug) }}" selected>
                                {{ $department->name }}
                            </option>
                            @foreach($otherDepartments as $otherDept)
                            <option value="{{ route('departments.show', $otherDept->slug) }}">
                                {{ $otherDept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                @if($department->courses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($department->courses as $course)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        @if($course->cover_image_url)
                        <img src="{{ $course->cover_image_url }}" alt="{{ $course->name }}" class="w-full h-48 object-cover">
                        @else
                        <div class="w-full h-48 bg-gradient-to-br from-primary to-red-600 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        @endif

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $course->name }}</h3>

                            <div class="flex items-center space-x-2 mb-3">
                                @if($course->duration)
                                <span class="text-sm text-gray-600">{{ $course->duration }}</span>
                                @endif

                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    @if($course->level == 'beginner') bg-green-100 text-green-800
                                    @elseif($course->level == 'intermediate') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($course->level) }}
                                </span>

                                @if($course->featured)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    Featured
                                </span>
                                @endif
                            </div>

                            <p class="text-gray-700 text-sm leading-relaxed mb-4 line-clamp-2">
                                {{ Str::limit(strip_tags($course->description), 100) }}
                            </p>

                            <div class="flex justify-between items-center">
                                <a href="{{ route('application.form') }}"
                                   class="bg-[#BF1F30] text-white font-semibold px-4 py-2 rounded-md hover:bg-red-800 transition-colors duration-200 text-sm">
                                    APPLY NOW!
                                </a>
                                <a href="{{ route('courses.show', $course->slug) }}"
                                   class="text-red-700 font-semibold px-4 py-2 rounded-md hover:underline transition-colors duration-200 text-sm border border-[#BF1F30]">
                                    READ MORE &gt;&gt;
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No courses available in this department</h3>
                    <p class="text-gray-600 mb-4">Please check back later for new course offerings.</p>
                    <a href="{{ route('course.index') }}" class="text-primary hover:underline">View all courses</a>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:w-1/4">
                <!-- All Departments -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">All Departments</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('course.index') }}"
                               class="flex items-center justify-between text-gray-600 hover:text-primary transition-colors {{ request()->is('courses') ? 'text-primary font-semibold' : '' }}">
                                <span>All Courses</span>
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                    {{ \App\Models\Course::where('is_active', true)->count() }}
                                </span>
                            </a>
                        </li>
                        @foreach($otherDepartments as $otherDept)
                        <li>
                            <a href="{{ route('departments.show', $otherDept->slug) }}"
                               class="flex items-center justify-between text-gray-600 hover:text-primary transition-colors {{ request()->is('departments/'.$otherDept->slug) ? 'text-primary font-semibold' : '' }}">
                                <span>{{ $otherDept->name }}</span>
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                    {{ $otherDept->courses_count }}
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Quick Apply -->
                <div class="bg-primary rounded-lg p-6 text-white text-center">
                    <h3 class="text-lg font-semibold mb-2">Ready to Apply?</h3>
                    <p class="text-sm text-white text-opacity-90 mb-4">Start your journey today</p>
                    <a href="{{ route('application.form') }}"
                       class="bg-white text-primary font-semibold px-6 py-3 rounded-md hover:bg-gray-100 transition-colors duration-200 inline-block">
                        APPLY NOW
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
