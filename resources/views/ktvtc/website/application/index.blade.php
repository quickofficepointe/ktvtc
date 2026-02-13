@extends('layouts.app')

@section('seo')
    <meta name="description" content="Apply for courses at Kenswed Technical College. Start your journey towards a rewarding career with our easy online application process.">
    <meta name="keywords" content="application, apply, admission, courses, Kenswed College, enrollment">
    <meta property="og:title" content="Application Form - Kenswed Technical College">
    <meta property="og:description" content="Apply for courses at Kenswed Technical College. Start your journey towards a rewarding career.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Application Form - Kenswed Technical College')

@section('content')
<!-- Application Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Application Form</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Start your journey to a rewarding career at Kenswed Technical College</p>
        </div>
    </div>
</section>

<!-- Application Form Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 font-medium">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 font-medium">{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 font-medium">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-red-700 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Progress Steps -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 -z-10">
                        <div id="progressBar" class="h-1 bg-[#B91C1C] transition-all duration-500" style="width: {{ $course ? '16.66%' : '0%' }}"></div>
                    </div>

                    <!-- Step Indicators -->
                    @php
                        $steps = [
                            1 => ['label' => 'Course', 'sub' => $course ? 'Pre-selected' : 'Select course'],
                            2 => ['label' => 'Campus', 'sub' => 'Choose location'],
                            3 => ['label' => 'Personal', 'sub' => 'Your details'],
                            4 => ['label' => 'Education', 'sub' => 'Background'],
                            5 => ['label' => 'Documents', 'sub' => 'Upload files'],
                            6 => ['label' => 'Review', 'sub' => 'Final check']
                        ];
                    @endphp

                    @foreach($steps as $step => $info)
                        <div class="flex flex-col items-center z-10">
                            <div id="step{{ $step }}Indicator"
                                 class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300
                                        {{ $step == 1 && $course ? 'bg-[#B91C1C] text-white' : ($step == 1 ? 'bg-[#B91C1C] text-white' : 'bg-gray-300 text-gray-600') }}">
                                {{ $step }}
                            </div>
                            <div class="mt-2 text-center">
                                <p class="font-semibold {{ $step == 1 && $course ? 'text-gray-900' : 'text-gray-600' }} text-sm">
                                    {{ $info['label'] }}
                                </p>
                                <p class="text-xs {{ $step == 1 && $course ? 'text-gray-600' : 'text-gray-500' }}">
                                    {{ $info['sub'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Application Form -->
            <form id="applicationForm" action="{{ route('application.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg overflow-hidden">
                @csrf

                <!-- Hidden course_id if pre-selected -->
                @if($course)
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                @endif

                <!-- Step 1: Course Selection -->
                <div id="step1" class="step p-8 {{ $course ? 'active' : 'active' }}">
                    @if($course)
                        <!-- Course is pre-selected -->
                        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <h3 class="text-lg font-bold text-gray-800">Applying for: <span class="text-green-700">{{ $course->name }}</span></h3>
                                </div>
                                <a href="{{ route('application.form') }}"
                                   class="text-sm text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                    Change Course
                                </a>
                            </div>

                            <!-- Course Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm mb-4">
                                @if($course->department)
                                <div>
                                    <span class="text-gray-600">Department:</span>
                                    <p class="font-semibold text-gray-800">{{ $course->department->name }}</p>
                                </div>
                                @endif

                                @if($course->duration)
                                <div>
                                    <span class="text-gray-600">Duration:</span>
                                    <p class="font-semibold text-gray-800">{{ $course->duration }}</p>
                                </div>
                                @endif

                                @if($course->level)
                                <div>
                                    <span class="text-gray-600">Level:</span>
                                    <p class="font-semibold text-gray-800 capitalize">{{ $course->level }}</p>
                                </div>
                                @endif

                                @if($course->code)
                                <div>
                                    <span class="text-gray-600">Code:</span>
                                    <p class="font-semibold text-gray-800">{{ $course->code }}</p>
                                </div>
                                @endif

                                @if($course->delivery_mode)
                                <div>
                                    <span class="text-gray-600">Mode:</span>
                                    <p class="font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $course->delivery_mode)) }}</p>
                                </div>
                                @endif
                            </div>

                            @if($course->description)
                            <div class="border-t pt-4">
                                <p class="text-sm text-gray-700">{{ Str::limit(strip_tags($course->description), 150) }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Intake and Study Mode Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Intake Period *</label>
                                <select name="intake_period" id="intakePeriod" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-colors">
                                    <option value="">-- Select Intake --</option>
                                    @foreach($intakes as $intake)
                                        <option value="{{ $intake->month }} {{ $intake->year }}">
                                            {{ $intake->month }} {{ $intake->year }}
                                            @if($intake->application_deadline)
                                                (Deadline: {{ \Carbon\Carbon::parse($intake->application_deadline)->format('M j, Y') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('intake_period')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode *</label>
                                <select name="study_mode" id="studyMode" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-colors">
                                    <option value="">-- Select Mode --</option>
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="evening">Evening Classes</option>
                                    <option value="weekend">Weekend Classes</option>
                                    <option value="online">Online</option>
                                </select>
                                @error('study_mode')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @else
                        <!-- No course selected - show dropdown -->
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Choose Your Course</h3>
                            <p class="text-gray-600">Select the course you want to apply for</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Course *</label>
                                <select name="course_id" id="courseSelect" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200 text-sm">
                                    <option value="">-- Choose a Course --</option>
                                    @foreach($courses as $courseOption)
                                        <option value="{{ $courseOption->id }}"
                                                data-slug="{{ $courseOption->slug }}"
                                                {{ old('course_id') == $courseOption->id ? 'selected' : '' }}>
                                            {{ $courseOption->name }}
                                            @if($courseOption->department)
                                                - {{ $courseOption->department->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Intake and Study Mode (hidden until course selected) -->
                            <div id="courseDetails" class="hidden space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Intake Period *</label>
                                        <select name="intake_period" id="intakePeriodSelect" required
                                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                            <option value="">-- Select Intake --</option>
                                        </select>
                                        @error('intake_period')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode *</label>
                                        <select name="study_mode" id="studyModeSelect" required
                                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                            <option value="">-- Select Mode --</option>
                                            <option value="full_time">Full Time</option>
                                            <option value="part_time">Part Time</option>
                                            <option value="evening">Evening Classes</option>
                                            <option value="weekend">Weekend Classes</option>
                                            <option value="online">Online</option>
                                        </select>
                                        @error('study_mode')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Course Preview -->
                                <div id="coursePreview" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-800 mb-2">Course Preview</h4>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">Duration:</span>
                                            <span id="previewDuration" class="font-medium"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Level:</span>
                                            <span id="previewLevel" class="font-medium"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end mt-8">
                        @if($course)
                            <button type="button" id="continueToStep2"
                                    class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                Continue to Campus →
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Step 2: Campus Selection -->
                <div id="step2" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Select Campus</h3>
                    <p class="text-gray-600 mb-6">Choose your preferred campus location</p>

                    <div class="space-y-6">
                        <!-- Course Review -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Selected Course</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Course:</span>
                                    <p id="selectedCourseName" class="font-semibold text-gray-800">
                                        {{ $course ? $course->name : 'Not selected' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Intake:</span>
                                    <p id="selectedIntake" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Study Mode:</span>
                                    <p id="selectedStudyMode" class="font-semibold text-gray-800">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Campus Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Campus *</label>
                            @error('campus_id')
                                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="space-y-4">
                                @foreach($campuses as $campus)
                                <div class="border-2 border-gray-300 rounded-lg p-4 hover:border-[#B91C1C] hover:bg-red-50 transition-all duration-200 cursor-pointer campus-option"
                                     data-campus-id="{{ $campus->id }}">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <input type="radio" name="campus_id" value="{{ $campus->id }}"
                                                   id="campus_{{ $campus->id }}" class="hidden campus-radio"
                                                   {{ old('campus_id') == $campus->id ? 'checked' : '' }}>
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center campus-radio-visual">
                                                <div class="w-3 h-3 rounded-full bg-[#B91C1C] hidden"></div>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <label for="campus_{{ $campus->id }}" class="block cursor-pointer">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">{{ $campus->name }}</h4>
                                                        <p class="text-sm text-gray-600 mt-1">
                                                            <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            </svg>
                                                            {{ $campus->address }}, {{ $campus->city }}
                                                        </p>
                                                        @if($campus->phone)
                                                        <p class="text-sm text-gray-600 mt-1">
                                                            <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                            </svg>
                                                            {{ $campus->phone }}
                                                        </p>
                                                        @endif
                                                    </div>
                                                    @if($campus->google_map_link)
                                                    <button type="button"
                                                            class="view-map-btn text-[#B91C1C] hover:text-[#991B1B] text-sm font-medium px-3 py-1 rounded hover:bg-red-50"
                                                            data-map-link="{{ $campus->google_map_link }}"
                                                            data-campus-name="{{ $campus->name }}">
                                                        View Map
                                                    </button>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500 mt-3">Select the campus where you prefer to study.</p>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" id="backToStep1"
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold">
                            ← Back to Course
                        </button>
                        <button type="button" id="continueToStep3" disabled
                                class="bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-semibold cursor-not-allowed transition-colors duration-200">
                            Continue to Personal Info →
                        </button>
                    </div>
                </div>

                <!-- Step 3: Personal Information -->
                <div id="step3" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Personal Information</h3>
                    <p class="text-gray-600 mb-6">Tell us about yourself</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" name="first_name" id="firstName" required
                                   value="{{ old('first_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" name="last_name" id="lastName" required
                                   value="{{ old('last_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" required
                                   value="{{ old('phone') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Type *</label>
                            <select name="id_type" id="idType" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                <option value="">-- Select ID Type --</option>
                                <option value="id" {{ old('id_type') == 'id' ? 'selected' : '' }}>National ID</option>
                                <option value="birth_certificate" {{ old('id_type') == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                            </select>
                            @error('id_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" id="idNumberLabel">ID/Birth Certificate Number *</label>
                            <input type="text" name="id_number" id="idNumber" required
                                   value="{{ old('id_number') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                   placeholder="Enter ID/Birth Certificate number">
                            @error('id_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                            <input type="date" name="date_of_birth" id="dateOfBirth" required
                                   value="{{ old('date_of_birth') }}"
                                   max="{{ date('Y-m-d', strtotime('-16 years')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <select name="gender" id="gender" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                <option value="">-- Select Gender --</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <input type="text" name="address" id="address"
                                   value="{{ old('address') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" id="city"
                                   value="{{ old('city') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                            <input type="text" name="county" id="county"
                                   value="{{ old('county') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" id="postalCode"
                                   value="{{ old('postal_code') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" id="backToStep2"
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold">
                            ← Back to Campus
                        </button>
                        <button type="button" id="continueToStep4"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            Continue to Education →
                        </button>
                    </div>
                </div>

                <!-- Step 4: Education & Background -->
                <div id="step4" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Education Background</h3>
                    <p class="text-gray-600 mb-6">Tell us about your educational history</p>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Highest Education Level *</label>
                                <select name="education_level" id="educationLevel" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                    <option value="">-- Select Level --</option>
                                    <option value="KCSE" {{ old('education_level') == 'KCSE' ? 'selected' : '' }}>KCSE</option>
                                    <option value="KCPE" {{ old('education_level') == 'KCPE' ? 'selected' : '' }}>KCPE</option>
                                    <option value="Diploma" {{ old('education_level') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                    <option value="Degree" {{ old('education_level') == 'Degree' ? 'selected' : '' }}>Degree</option>
                                    <option value="Certificate" {{ old('education_level') == 'Certificate' ? 'selected' : '' }}>Certificate</option>
                                    <option value="Other" {{ old('education_level') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('education_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">School/Institution Name *</label>
                                <input type="text" name="school_name" id="schoolName" required
                                       value="{{ old('school_name') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                @error('school_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year *</label>
                                <input type="number" name="graduation_year" id="graduationYear" required
                                       min="1950" max="{{ date('Y') }}"
                                       value="{{ old('graduation_year') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                @error('graduation_year')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mean Grade *</label>
                                <input type="text" name="mean_grade" id="meanGrade" required
                                       value="{{ old('mean_grade') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                @error('mean_grade')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Application Type *</label>
                            <select name="application_type" id="applicationType" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                <option value="new" {{ old('application_type') == 'new' ? 'selected' : '' }}>New Student</option>
                                <option value="transfer" {{ old('application_type') == 'transfer' ? 'selected' : '' }}>Transfer Student</option>
                                <option value="continuing" {{ old('application_type') == 'continuing' ? 'selected' : '' }}>Continuing Student</option>
                            </select>
                            @error('application_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Emergency Contact Information -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contact Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name *</label>
                                    <input type="text" name="emergency_contact_name" id="emergencyContactName" required
                                           value="{{ old('emergency_contact_name') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                    @error('emergency_contact_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone *</label>
                                    <input type="tel" name="emergency_contact_phone" id="emergencyContactPhone" required
                                           value="{{ old('emergency_contact_phone') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                    @error('emergency_contact_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Relationship *</label>
                                    <input type="text" name="emergency_contact_relationship" id="emergencyContactRelationship" required
                                           value="{{ old('emergency_contact_relationship') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                           placeholder="e.g., Parent, Guardian">
                                    @error('emergency_contact_relationship')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Special Needs -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Needs (if any)</label>
                            <textarea name="special_needs" id="specialNeeds" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                      placeholder="Please specify any special needs or accommodations required...">{{ old('special_needs') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" id="backToStep3"
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold">
                            ← Back to Personal Info
                        </button>
                        <button type="button" id="continueToStep5"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            Continue to Documents →
                        </button>
                    </div>
                </div>

                <!-- Step 5: Documents Upload -->
                <div id="step5" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Required Documents</h3>
                    <p class="text-gray-600 mb-6">Upload the necessary documents for your application</p>

                    <div class="space-y-6">
                        <!-- ID/Birth Certificate Document -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2" id="documentLabel">
                                National ID or Birth Certificate *
                            </label>
                            <input type="file" name="id_document" id="idDocument" accept=".pdf,.jpg,.jpeg,.png" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            @error('id_document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-2" id="documentDescription">
                                Upload a clear copy of your National ID or Birth Certificate
                                (PDF, JPG, PNG - Max 2MB)
                            </p>
                            <div id="idDocumentPreview" class="mt-2 hidden">
                                <img id="idDocumentPreviewImage" class="h-32 rounded-lg border">
                            </div>
                        </div>

                        <!-- Education Certificates -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Education Certificates *</label>
                            <input type="file" name="education_certificates" id="educationCertificates" accept=".pdf,.jpg,.jpeg,.png" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            @error('education_certificates')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-2">
                                Academic certificates and transcripts
                                (PDF, JPG, PNG - Max 5MB)
                            </p>
                            <div id="educationCertificatesPreview" class="mt-2 hidden">
                                <img id="educationCertificatesPreviewImage" class="h-32 rounded-lg border">
                            </div>
                        </div>

                        <!-- Passport Photo -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Passport Photo *</label>
                            <input type="file" name="passport_photo" id="passportPhoto" accept=".jpg,.jpeg,.png" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            @error('passport_photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-2">
                                Recent passport-size photo
                                (JPG, PNG - Max 1MB)
                            </p>
                            <div id="passportPhotoPreview" class="mt-2 hidden">
                                <img id="passportPhotoPreviewImage" class="h-32 rounded-lg border">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" id="backToStep4"
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold">
                            ← Back to Education
                        </button>
                        <button type="button" id="continueToStep6"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            Review Application →
                        </button>
                    </div>
                </div>

                <!-- Step 6: Review & Submit -->
                <div id="step6" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Review Your Application</h3>
                    <p class="text-gray-600 mb-6">Please review all information before submitting</p>

                    <div class="space-y-6" id="reviewContent">
                        <!-- Loading placeholder -->
                        <div id="reviewLoading" class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#B91C1C] mx-auto"></div>
                            <p class="mt-4 text-gray-600">Loading review...</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-yellow-800 text-sm">Please ensure all information is correct. You cannot edit your application after submission.</p>
                        </div>
                    </div>

                    <div class="flex items-center mt-6 mb-6">
                        <input type="checkbox" id="confirmAccuracy" class="w-4 h-4 text-[#B91C1C] border-gray-300 rounded focus:ring-[#B91C1C]">
                        <label for="confirmAccuracy" class="ml-2 text-sm text-gray-700">
                            I confirm that all information provided is accurate and complete
                        </label>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" id="backToStep5"
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold">
                            ← Back to Documents
                        </button>
                        <button type="submit" id="submitButton" disabled
                                class="bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-semibold cursor-not-allowed transition-colors duration-200">
                            Submit Application
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Campus Map Modal -->
<div id="campusMapModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" id="modalOverlay"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white" id="campusMapTitle"></h3>
                <button id="closeCampusMap" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <iframe id="campusMapFrame" class="w-full h-96 rounded-lg" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    // Make functions available globally
    let currentStep = 1;
    const totalSteps = 6;
    let selectedCampusId = null;
    let selectedCourse = @json($course ?? null);

    // Navigation functions
    function updateProgress(step) {
        const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
        document.getElementById('progressBar').style.width = `${progressPercentage}%`;

        // Update step indicators
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step${i}Indicator`);
            if (i <= step) {
                indicator.classList.remove('bg-gray-300', 'text-gray-600');
                indicator.classList.add('bg-[#B91C1C]', 'text-white');
            } else {
                indicator.classList.remove('bg-[#B91C1C]', 'text-white');
                indicator.classList.add('bg-gray-300', 'text-gray-600');
            }
        }
    }

    function nextStep(targetStep) {
        if (validateStep(currentStep)) {
            // Save form data to session storage
            saveFormData();

            // Update progress
            updateProgress(targetStep);

            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.add('hidden');

            // Show next step
            document.getElementById(`step${targetStep}`).classList.remove('hidden');
            document.getElementById(`step${targetStep}`).classList.add('active');

            currentStep = targetStep;

            // Special handling for review step
            if (targetStep === 6) {
                updateReviewSection();
            }

            // Scroll to top of form
            document.getElementById(`step${targetStep}`).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function prevStep(targetStep) {
        // Save form data
        saveFormData();

        // Update progress
        updateProgress(targetStep);

        // Hide current step
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${currentStep}`).classList.add('hidden');

        // Show previous step
        document.getElementById(`step${targetStep}`).classList.remove('hidden');
        document.getElementById(`step${targetStep}`).classList.add('active');

        currentStep = targetStep;

        // Scroll to top of form
        document.getElementById(`step${targetStep}`).scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Campus selection
    function selectCampus(element) {
        const campusId = element.getAttribute('data-campus-id');
        const radio = element.querySelector('.campus-radio');

        // Unselect all
        document.querySelectorAll('.campus-option').forEach(opt => {
            opt.classList.remove('border-[#B91C1C]', 'bg-red-50');
            opt.querySelector('.campus-radio-visual').classList.remove('border-[#B91C1C]');
            opt.querySelector('.campus-radio-visual > div').classList.add('hidden');
        });

        // Select this one
        element.classList.add('border-[#B91C1C]', 'bg-red-50');
        element.querySelector('.campus-radio-visual').classList.add('border-[#B91C1C]');
        element.querySelector('.campus-radio-visual > div').classList.remove('hidden');

        // Set radio value
        radio.checked = true;
        selectedCampusId = campusId;

        // Enable continue button
        const continueBtn = document.getElementById('continueToStep3');
        continueBtn.disabled = false;
        continueBtn.classList.remove('bg-gray-400', 'text-gray-700', 'cursor-not-allowed');
        continueBtn.classList.add('bg-[#B91C1C]', 'text-white', 'hover:bg-[#991B1B]', 'cursor-pointer', 'shadow-md', 'hover:shadow-lg', 'transform', 'hover:-translate-y-0.5');
    }

    // Map functions
    function showCampusMap(mapLink, campusName) {
        document.getElementById('campusMapTitle').textContent = campusName + ' - Location';
        document.getElementById('campusMapFrame').src = mapLink;
        document.getElementById('campusMapModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCampusMapModal() {
        document.getElementById('campusMapModal').classList.add('hidden');
        document.getElementById('campusMapFrame').src = '';
        document.body.style.overflow = 'auto';
    }

    // Validation functions
    function validateStep(step) {
        let isValid = true;
        let firstInvalidField = null;

        switch(step) {
            case 1:
                if (selectedCourse) {
                    // For pre-selected course, validate intake and study mode
                    const intakePeriod = document.querySelector('select[name="intake_period"]');
                    const studyMode = document.querySelector('select[name="study_mode"]');

                    if (!intakePeriod.value) {
                        showValidationError(intakePeriod, 'Please select an intake period');
                        firstInvalidField = intakePeriod;
                        isValid = false;
                    }
                    if (!studyMode.value) {
                        showValidationError(studyMode, 'Please select a study mode');
                        if (!firstInvalidField) firstInvalidField = studyMode;
                        isValid = false;
                    }

                    // Update display for step 2
                    document.getElementById('selectedCourseName').textContent = selectedCourse.name || '{{ $course->name ?? "Not selected" }}';
                    document.getElementById('selectedIntake').textContent = intakePeriod.options[intakePeriod.selectedIndex]?.text || '-';
                    document.getElementById('selectedStudyMode').textContent = studyMode.options[studyMode.selectedIndex]?.text || '-';
                } else {
                    // For dropdown selection
                    const courseSelect = document.getElementById('courseSelect');
                    const intakePeriod = document.getElementById('intakePeriodSelect');
                    const studyMode = document.getElementById('studyModeSelect');

                    if (!courseSelect.value) {
                        showValidationError(courseSelect, 'Please select a course');
                        firstInvalidField = courseSelect;
                        isValid = false;
                    }
                    if (!intakePeriod.value) {
                        showValidationError(intakePeriod, 'Please select an intake period');
                        if (!firstInvalidField) firstInvalidField = intakePeriod;
                        isValid = false;
                    }
                    if (!studyMode.value) {
                        showValidationError(studyMode, 'Please select a study mode');
                        if (!firstInvalidField) firstInvalidField = studyMode;
                        isValid = false;
                    }

                    // Update display for step 2
                    const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                    document.getElementById('selectedCourseName').textContent = selectedOption.text || 'Not selected';
                    document.getElementById('selectedIntake').textContent = intakePeriod.options[intakePeriod.selectedIndex]?.text || '-';
                    document.getElementById('selectedStudyMode').textContent = studyMode.options[studyMode.selectedIndex]?.text || '-';
                }
                break;

            case 2:
                if (!selectedCampusId) {
                    alert('Please select a campus');
                    isValid = false;
                }
                break;

            case 3:
                const personalFields = ['firstName', 'lastName', 'email', 'phone', 'idType', 'idNumber', 'dateOfBirth', 'gender'];
                for (let fieldId of personalFields) {
                    const field = document.getElementById(fieldId);
                    if (field && !field.value.trim()) {
                        const fieldName = fieldId.replace(/([A-Z])/g, ' $1').toLowerCase();
                        showValidationError(field, `Please fill in ${fieldName}`);
                        if (!firstInvalidField) firstInvalidField = field;
                        isValid = false;
                    }
                }
                break;

            case 4:
                const educationFields = ['educationLevel', 'schoolName', 'graduationYear', 'meanGrade', 'applicationType',
                                         'emergencyContactName', 'emergencyContactPhone', 'emergencyContactRelationship'];
                for (let fieldId of educationFields) {
                    const field = document.getElementById(fieldId);
                    if (field && !field.value.trim()) {
                        const fieldName = fieldId.replace(/([A-Z])/g, ' $1').toLowerCase();
                        showValidationError(field, `Please fill in ${fieldName}`);
                        if (!firstInvalidField) firstInvalidField = field;
                        isValid = false;
                    }
                }
                break;

            case 5:
                const fileInputs = ['idDocument', 'educationCertificates', 'passportPhoto'];
                for (let fileInputId of fileInputs) {
                    const input = document.getElementById(fileInputId);
                    if (input && !input.files.length) {
                        const docName = fileInputId.replace(/([A-Z])/g, ' $1').toLowerCase();
                        showValidationError(input, `Please upload ${docName}`);
                        if (!firstInvalidField) firstInvalidField = input;
                        isValid = false;
                    }
                }
                break;
        }

        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    function showValidationError(field, message) {
        // Remove any existing error
        const existingError = field.parentElement.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error mt-1 text-sm text-red-600';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);

        // Highlight field
        field.classList.add('border-red-500', 'ring-2', 'ring-red-200');
        field.addEventListener('input', function() {
            field.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            errorDiv.remove();
        }, { once: true });
    }

    // Other helper functions
    function saveFormData() {
        const formData = {
            step: currentStep,
            // Save form field values as needed
        };
        sessionStorage.setItem('application_data', JSON.stringify(formData));
    }

    function updateReviewSection() {
        const reviewContent = document.getElementById('reviewContent');
        const reviewLoading = document.getElementById('reviewLoading');

        // Show loading
        reviewContent.innerHTML = '';
        reviewContent.appendChild(reviewLoading);
        reviewLoading.classList.remove('hidden');

        // Get form data
        const formData = new FormData(document.getElementById('applicationForm'));

        // Get selected campus name
        let campusName = '-';
        const selectedCampusOption = document.querySelector('.campus-option.border-[#B91C1C]');
        if (selectedCampusOption) {
            campusName = selectedCampusOption.querySelector('h4').textContent;
        }

        // Get course name
        let courseName = selectedCourse?.name || '{{ $course->name ?? "Not selected" }}';
        let courseCode = selectedCourse?.code || '{{ $course->code ?? "N/A" }}';
        let courseDuration = selectedCourse?.duration || '{{ $course->duration ?? "N/A" }}';

        // Get form values
        const firstName = document.querySelector('input[name="first_name"]')?.value || '-';
        const lastName = document.querySelector('input[name="last_name"]')?.value || '-';
        const email = document.querySelector('input[name="email"]')?.value || '-';
        const phone = document.querySelector('input[name="phone"]')?.value || '-';
        const idType = document.querySelector('select[name="id_type"]')?.value || '-';
        const idNumber = document.querySelector('input[name="id_number"]')?.value || '-';
        const dateOfBirth = document.querySelector('input[name="date_of_birth"]')?.value || '-';
        const gender = document.querySelector('select[name="gender"]')?.value || '-';
        const address = document.querySelector('input[name="address"]')?.value || 'Not provided';
        const city = document.querySelector('input[name="city"]')?.value || 'Not provided';
        const county = document.querySelector('input[name="county"]')?.value || 'Not provided';
        const postalCode = document.querySelector('input[name="postal_code"]')?.value || 'Not provided';
        const educationLevel = document.querySelector('select[name="education_level"]')?.value || '-';
        const schoolName = document.querySelector('input[name="school_name"]')?.value || '-';
        const graduationYear = document.querySelector('input[name="graduation_year"]')?.value || '-';
        const meanGrade = document.querySelector('input[name="mean_grade"]')?.value || '-';
        const applicationType = document.querySelector('select[name="application_type"]')?.value || '-';
        const emergencyContactName = document.querySelector('input[name="emergency_contact_name"]')?.value || '-';
        const emergencyContactPhone = document.querySelector('input[name="emergency_contact_phone"]')?.value || '-';
        const emergencyContactRelationship = document.querySelector('input[name="emergency_contact_relationship"]')?.value || '-';
        const specialNeeds = document.querySelector('textarea[name="special_needs"]')?.value || 'None specified';
        const intakePeriod = document.querySelector('select[name="intake_period"]')?.value || '-';
        const studyMode = document.querySelector('select[name="study_mode"]')?.value || '-';

        // Format date
        const formattedDate = dateOfBirth !== '-' ? new Date(dateOfBirth).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '-';

        // Create review HTML
        const reviewHTML = `
            <!-- Campus & Course Information -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Campus & Course Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Campus:</span>
                        <p class="font-semibold text-gray-800">${campusName}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Course:</span>
                        <p class="font-semibold text-gray-800">${courseName}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Study Mode:</span>
                        <p class="font-semibold text-gray-800">${studyMode.replace('_', ' ').toUpperCase()}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Intake Period:</span>
                        <p class="font-semibold text-gray-800">${intakePeriod}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Application Type:</span>
                        <p class="font-semibold text-gray-800">${applicationType.replace('_', ' ').toUpperCase()}</p>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Full Name:</span>
                        <p class="font-semibold text-gray-800">${firstName} ${lastName}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Email:</span>
                        <p class="font-semibold text-gray-800">${email}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Phone:</span>
                        <p class="font-semibold text-gray-800">${phone}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">ID Type:</span>
                        <p class="font-semibold text-gray-800">${idType === 'id' ? 'National ID' : 'Birth Certificate'}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">ID Number:</span>
                        <p class="font-semibold text-gray-800">${idNumber}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Date of Birth:</span>
                        <p class="font-semibold text-gray-800">${formattedDate}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Gender:</span>
                        <p class="font-semibold text-gray-800">${gender.toUpperCase()}</p>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-600">Address:</span>
                        <p class="font-semibold text-gray-800">${[address, city, county, postalCode].filter(Boolean).join(', ') || 'Not provided'}</p>
                    </div>
                </div>
            </div>

            <!-- Education Information -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Education Background</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Education Level:</span>
                        <p class="font-semibold text-gray-800">${educationLevel}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">School Name:</span>
                        <p class="font-semibold text-gray-800">${schoolName}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Graduation Year:</span>
                        <p class="font-semibold text-gray-800">${graduationYear}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Mean Grade:</span>
                        <p class="font-semibold text-gray-800">${meanGrade}</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contact</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Name:</span>
                        <p class="font-semibold text-gray-800">${emergencyContactName}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Phone:</span>
                        <p class="font-semibold text-gray-800">${emergencyContactPhone}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Relationship:</span>
                        <p class="font-semibold text-gray-800">${emergencyContactRelationship}</p>
                    </div>
                </div>
            </div>

            <!-- Special Needs -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Special Requirements</h4>
                <p class="text-sm text-gray-700">${specialNeeds}</p>
            </div>

            <!-- Documents -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Documents to be Submitted</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>${idType === 'id' ? 'National ID' : 'Birth Certificate'}</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Education Certificates</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Passport Photo</span>
                    </li>
                </ul>
            </div>
        `;

        // Update review content
        setTimeout(() => {
            reviewLoading.classList.add('hidden');
            reviewContent.innerHTML = reviewHTML;
        }, 500);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set max date for date of birth (minimum 16 years old)
        const dateOfBirthInput = document.getElementById('dateOfBirth');
        if (dateOfBirthInput) {
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() - 16);
            dateOfBirthInput.max = maxDate.toISOString().split('T')[0];
        }

        // Initialize ID type change listener
        const idTypeSelect = document.getElementById('idType');
        if (idTypeSelect) {
            function updateIdTypeLabels() {
                const idType = document.getElementById('idType')?.value;
                const label = document.getElementById('idNumberLabel');
                const input = document.getElementById('idNumber');
                const documentLabel = document.getElementById('documentLabel');
                const documentDescription = document.getElementById('documentDescription');

                if (idType === 'id') {
                    label.textContent = 'National ID Number *';
                    input.placeholder = 'Enter National ID number (e.g., 12345678)';
                    documentLabel.textContent = 'National ID Document *';
                    documentDescription.textContent = 'Upload a clear copy of your National ID (both sides if applicable) (PDF, JPG, PNG - Max 2MB)';
                } else if (idType === 'birth_certificate') {
                    label.textContent = 'Birth Certificate Number *';
                    input.placeholder = 'Enter Birth Certificate number';
                    documentLabel.textContent = 'Birth Certificate *';
                    documentDescription.textContent = 'Upload a clear copy of your Birth Certificate (PDF, JPG, PNG - Max 2MB)';
                }
            }

            idTypeSelect.addEventListener('change', updateIdTypeLabels);
            updateIdTypeLabels(); // Initial call
        }

        // Initialize file previews
        function previewFile(input, previewContainerId, previewImageId) {
            const file = input.files[0];
            const previewContainer = document.getElementById(previewContainerId);
            const previewImage = document.getElementById(previewImageId);

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        }

        document.getElementById('idDocument')?.addEventListener('change', function(e) {
            previewFile(this, 'idDocumentPreview', 'idDocumentPreviewImage');
        });

        document.getElementById('educationCertificates')?.addEventListener('change', function(e) {
            previewFile(this, 'educationCertificatesPreview', 'educationCertificatesPreviewImage');
        });

        document.getElementById('passportPhoto')?.addEventListener('change', function(e) {
            previewFile(this, 'passportPhotoPreview', 'passportPhotoPreviewImage');
        });

        // Initialize course dropdown if no pre-selected course
        if (!selectedCourse) {
            const courseSelect = document.getElementById('courseSelect');
            if (courseSelect) {
                courseSelect.addEventListener('change', function() {
                    const courseId = this.value;
                    const courseDetailsDiv = document.getElementById('courseDetails');

                    if (courseId) {
                        // Show loading
                        courseDetailsDiv.classList.remove('hidden');
                        document.getElementById('coursePreview').classList.add('hidden');

                        // Fetch course details
                        fetch(`/application/course/${courseId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Update intake options
                                    const intakeSelect = document.getElementById('intakePeriodSelect');
                                    intakeSelect.innerHTML = '<option value="">-- Select Intake --</option>';
                                    data.intakes.forEach(intake => {
                                        const option = document.createElement('option');
                                        option.value = `${intake.month} ${intake.year}`;
                                        option.textContent = intake.display;
                                        intakeSelect.appendChild(option);
                                    });

                                    // Update course preview
                                    const previewDiv = document.getElementById('coursePreview');
                                    document.getElementById('previewDuration').textContent = data.course.duration || 'N/A';
                                    document.getElementById('previewLevel').textContent = data.course.level || 'N/A';
                                    previewDiv.classList.remove('hidden');

                                    // Store in session for review
                                    selectedCourse = data.course;
                                    sessionStorage.setItem('selected_course', JSON.stringify(data.course));
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching course details:', error);
                                alert('Error loading course details. Please try again.');
                            });
                    } else {
                        courseDetailsDiv.classList.add('hidden');
                        selectedCourse = null;
                        sessionStorage.removeItem('selected_course');
                    }
                });
            }
        }

        // Update progress if course is pre-selected
        if (selectedCourse) {
            updateProgress(1);
        }

        // Initialize event listeners for navigation buttons
        document.getElementById('continueToStep2')?.addEventListener('click', function() {
            if (validateStep(1)) {
                this.disabled = true;
                nextStep(2);
            }
        });

        document.getElementById('backToStep1')?.addEventListener('click', function() {
            prevStep(1);
        });

        document.getElementById('continueToStep3')?.addEventListener('click', function() {
            if (validateStep(2)) {
                nextStep(3);
            }
        });

        document.getElementById('backToStep2')?.addEventListener('click', function() {
            prevStep(2);
        });

        document.getElementById('continueToStep4')?.addEventListener('click', function() {
            if (validateStep(3)) {
                nextStep(4);
            }
        });

        document.getElementById('backToStep3')?.addEventListener('click', function() {
            prevStep(3);
        });

        document.getElementById('continueToStep5')?.addEventListener('click', function() {
            if (validateStep(4)) {
                nextStep(5);
            }
        });

        document.getElementById('backToStep4')?.addEventListener('click', function() {
            prevStep(4);
        });

        document.getElementById('continueToStep6')?.addEventListener('click', function() {
            if (validateStep(5)) {
                nextStep(6);
            }
        });

        document.getElementById('backToStep5')?.addEventListener('click', function() {
            prevStep(5);
        });

        // Campus selection event listeners
        document.querySelectorAll('.campus-option').forEach(option => {
            option.addEventListener('click', function() {
                selectCampus(this);
            });
        });

        // Map view buttons
        document.querySelectorAll('.view-map-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const mapLink = this.getAttribute('data-map-link');
                const campusName = this.getAttribute('data-campus-name');
                showCampusMap(mapLink, campusName);
            });
        });

        // Close modal buttons
        document.getElementById('closeCampusMap')?.addEventListener('click', closeCampusMapModal);
        document.getElementById('modalOverlay')?.addEventListener('click', closeCampusMapModal);

        // Confirm accuracy checkbox
        document.getElementById('confirmAccuracy')?.addEventListener('change', function() {
            const submitButton = document.getElementById('submitButton');
            if (this.checked) {
                submitButton.disabled = false;
                submitButton.classList.remove('bg-gray-400', 'text-gray-700', 'cursor-not-allowed');
                submitButton.classList.add('bg-[#B91C1C]', 'text-white', 'hover:bg-[#991B1B]', 'cursor-pointer', 'shadow-md', 'hover:shadow-lg', 'transform', 'hover:-translate-y-0.5');
            } else {
                submitButton.disabled = true;
                submitButton.classList.remove('bg-[#B91C1C]', 'text-white', 'hover:bg-[#991B1B]', 'cursor-pointer', 'shadow-md', 'hover:shadow-lg', 'transform', 'hover:-translate-y-0.5');
                submitButton.classList.add('bg-gray-400', 'text-gray-700', 'cursor-not-allowed');
            }
        });

        // File validation
        function validateFile(input, maxSizeMB, allowedExtensions) {
            const file = input.files[0];
            if (file) {
                const extension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(extension)) {
                    alert(`Please upload a file with one of these extensions: ${allowedExtensions.join(', ')}`);
                    input.value = '';
                    return false;
                }
                if (file.size > maxSizeMB * 1024 * 1024) {
                    alert(`File size must be less than ${maxSizeMB}MB`);
                    input.value = '';
                    return false;
                }
            }
            return true;
        }

        // Initialize file validations
        document.getElementById('idDocument')?.addEventListener('change', function() {
            validateFile(this, 2, ['pdf', 'jpg', 'jpeg', 'png']);
        });

        document.getElementById('educationCertificates')?.addEventListener('change', function() {
            validateFile(this, 5, ['pdf', 'jpg', 'jpeg', 'png']);
        });

        document.getElementById('passportPhoto')?.addEventListener('change', function() {
            validateFile(this, 1, ['jpg', 'jpeg', 'png']);
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCampusMapModal();
            }
        });

        // Prevent form submission on Enter in inputs
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && event.target.type !== 'textarea') {
                    event.preventDefault();
                }
            });
        });

        // Form submission handler
        document.getElementById('applicationForm')?.addEventListener('submit', function(e) {
            // Validate all steps
            for (let step = 1; step <= 5; step++) {
                if (!validateStep(step)) {
                    e.preventDefault();
                    alert('Please complete all required fields before submitting.');
                    return;
                }
            }

            // Check confirmation
            if (!document.getElementById('confirmAccuracy').checked) {
                e.preventDefault();
                alert('Please confirm that all information is accurate.');
                return;
            }

            // Show loading state
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="flex items-center justify-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...</span>';

            // Clear session data
            sessionStorage.removeItem('application_data');
            sessionStorage.removeItem('selected_course');
        });
    });
</script>

<style>
    .step {
        transition: all 0.3s ease-in-out;
    }

    .step.active {
        display: block;
        animation: fadeIn 0.5s ease-in-out;
    }

    .step.hidden {
        display: none;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
    }

    .file\:bg-\[\#B91C1C\]:hover {
        background-color: #991B1B;
    }

    .validation-error {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom scrollbar for form */
    .step::-webkit-scrollbar {
        width: 6px;
    }

    .step::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .step::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .step::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endsection
