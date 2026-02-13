@extends('layouts.app')

@section('seo')
    <meta name="description" content="View all course intakes and application deadlines at Kenswed Technical College. Plan your education with our upcoming intake schedules.">
    <meta name="keywords" content="course intakes, application deadlines, admission dates, Kenswed College, intake schedule">
    <meta property="og:title" content="Course Intakes & Application Deadlines - Kenswed Technical College">
    <meta property="og:description" content="View all course intakes and application deadlines at Kenswed Technical College. Plan your education with our upcoming intake schedules.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Course Intakes & Application Deadlines - Kenswed Technical College')

@section('content')
<!-- Course Intakes Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Course Intakes</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Plan your education journey with our upcoming course intake schedules and application deadlines</p>
        </div>
    </div>
</section>

<!-- Course Intakes Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($courses->count() > 0)
        <div class="max-w-6xl mx-auto">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 text-center">
                    <div class="text-3xl font-bold text-[#B91C1C] mb-2">{{ $courses->count() }}</div>
                    <div class="text-gray-600 font-medium">Total Courses</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 text-center">
                    @php
                        $totalIntakes = 0;
                        foreach($courses as $course) {
                            $totalIntakes += $course->intakes->where('is_active', true)->count();
                        }
                    @endphp
                    <div class="text-3xl font-bold text-[#B91C1C] mb-2">{{ $totalIntakes }}</div>
                    <div class="text-gray-600 font-medium">Upcoming Intakes</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 text-center">
                    @php
                        $currentYear = date('Y');
                        $currentYearIntakes = 0;
                        foreach($courses as $course) {
                            $currentYearIntakes += $course->intakes->where('is_active', true)->where('year', $currentYear)->count();
                        }
                    @endphp
                    <div class="text-3xl font-bold text-[#B91C1C] mb-2">{{ $currentYearIntakes }}</div>
                    <div class="text-gray-600 font-medium">{{ $currentYear }} Intakes</div>
                </div>
            </div>

            <!-- Courses with Intakes -->
            <div class="space-y-8">
                @foreach($courses as $course)
                    @if($course->intakes->where('is_active', true)->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <!-- Course Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ $course->name }}</h2>
                                    @if($course->department)
                                    <div class="flex items-center mt-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-[#B91C1C]">
                                            {{ $course->department->name }}
                                        </span>
                                        @if($course->duration)
                                        <span class="ml-3 text-gray-600 text-sm">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $course->duration }}
                                        </span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-2 md:mt-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ $course->intakes->where('is_active', true)->count() }} Upcoming Intakes
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Intakes Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Intake Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Application Deadline</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($course->intakes->where('is_active', true)->sortBy(['year', function($intake) {
                                        $months = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
                                        return $months[$intake->month];
                                    }]) as $intake)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <!-- Intake Period -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $intake->month }} {{ $intake->year }}</div>
                                                    <div class="text-sm text-gray-500">Intake Period</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Application Deadline -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($intake->application_deadline)
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($intake->application_deadline);
                                                    $isPast = $deadline->isPast();
                                                    $isNear = $deadline->diffInDays(now()) <= 30;
                                                @endphp
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 w-8 h-8 {{ $isPast ? 'bg-red-100' : ($isNear ? 'bg-yellow-100' : 'bg-green-100') }} rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 {{ $isPast ? 'text-red-600' : ($isNear ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-semibold {{ $isPast ? 'text-red-600' : ($isNear ? 'text-yellow-600' : 'text-green-600') }}">
                                                            {{ $deadline->format('M j, Y') }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            @if($isPast)
                                                                Deadline passed
                                                            @elseif($isNear)
                                                                Apply soon
                                                            @else
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">No deadline set</span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $currentMonth = date('F');
                                                $currentYear = date('Y');
                                                $intakeStatus = '';
                                                $statusColor = '';

                                                if ($intake->year < $currentYear || ($intake->year == $currentYear && array_search($intake->month, ['January','February','March','April','May','June','July','August','September','October','November','December']) < array_search($currentMonth, ['January','February','March','April','May','June','July','August','September','October','November','December']))) {
                                                    $intakeStatus = 'Past';
                                                    $statusColor = 'bg-gray-100 text-gray-800';
                                                } elseif ($intake->year == $currentYear && $intake->month == $currentMonth) {
                                                    $intakeStatus = 'Current';
                                                    $statusColor = 'bg-blue-100 text-blue-800';
                                                } else {
                                                    $intakeStatus = 'Upcoming';
                                                    $statusColor = 'bg-green-100 text-green-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                                <span class="w-2 h-2 rounded-full mr-2
                                                    {{ $intakeStatus == 'Past' ? 'bg-gray-400' :
                                                       ($intakeStatus == 'Current' ? 'bg-blue-400' : 'bg-green-400') }}"></span>
                                                {{ $intakeStatus }}
                                            </span>
                                        </td>

                                        <!-- Notes -->
                                        <td class="px-6 py-4">
                                            @if($intake->notes)
                                                <p class="text-sm text-gray-600 max-w-xs">{{ $intake->notes }}</p>
                                            @else
                                                <span class="text-sm text-gray-400">No additional notes</span>
                                            @endif
                                        </td>

                                        <!-- Action -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($intake->application_deadline && !\Carbon\Carbon::parse($intake->application_deadline)->isPast())
                                            <a href="{{ route('application.form') }}"
                                               class="inline-flex items-center px-4 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Apply Now
                                            </a>
                                            @else
                                            <span class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed font-semibold text-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Applications Closed
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- No Active Intakes Message -->
            @if(!$courses->contains(function($course) { return $course->intakes->where('is_active', true)->count() > 0; }))
            <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">No Upcoming Intakes</h3>
                <p class="text-gray-600 max-w-md mx-auto mb-6">There are currently no upcoming course intakes scheduled. Please check back later for new intake announcements.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                        Browse All Courses
                    </a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        Contact Admissions
                    </a>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Courses Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">There are currently no courses available for enrollment. Please check back later for new course offerings.</p>
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
