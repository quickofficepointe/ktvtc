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
            <!-- Progress Steps -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 -z-10">
                        <div id="progressBar" class="h-1 bg-[#B91C1C] transition-all duration-500" style="width: 0%"></div>
                    </div>

                    <!-- Step 1 -->
                    <div class="flex flex-col items-center z-10">
                        <div id="step1Indicator" class="w-10 h-10 bg-[#B91C1C] rounded-full flex items-center justify-center text-white font-bold transition-all duration-300">
                            1
                        </div>
                        <div class="mt-2 text-center">
                            <p class="font-semibold text-gray-900 text-sm">Course Selection</p>
                            <p class="text-xs text-gray-600">Choose your course</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center z-10">
                        <div id="step2Indicator" class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold transition-all duration-300">
                            2
                        </div>
                        <div class="mt-2 text-center">
                            <p class="font-semibold text-gray-600 text-sm">Personal Info</p>
                            <p class="text-xs text-gray-500">Your details</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center z-10">
                        <div id="step3Indicator" class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold transition-all duration-300">
                            3
                        </div>
                        <div class="mt-2 text-center">
                            <p class="font-semibold text-gray-600 text-sm">Education</p>
                            <p class="text-xs text-gray-500">Background</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex flex-col items-center z-10">
                        <div id="step4Indicator" class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold transition-all duration-300">
                            4
                        </div>
                        <div class="mt-2 text-center">
                            <p class="font-semibold text-gray-600 text-sm">Documents</p>
                            <p class="text-xs text-gray-500">Upload files</p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="flex flex-col items-center z-10">
                        <div id="step5Indicator" class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold transition-all duration-300">
                            5
                        </div>
                        <div class="mt-2 text-center">
                            <p class="font-semibold text-gray-600 text-sm">Review</p>
                            <p class="text-xs text-gray-500">Final check</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <form id="applicationForm" action="{{ route('application.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg overflow-hidden">
                @csrf

                <!-- Step 1: Course Selection -->
                <div id="step1" class="step p-8 active">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Choose Your Course</h3>
                    <p class="text-gray-600 mb-6">Select the course you want to apply for</p>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Course *</label>
                            <select name="course_id" id="courseSelect" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200">
                                <option value="">-- Choose a Course --</option>
                                @foreach($courses as $courseOption)
                                    <option value="{{ $courseOption->id }}" {{ $course && $course->id == $courseOption->id ? 'selected' : '' }}>
                                        {{ $courseOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($course)
                        <div id="courseDetails" class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Course Information</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Department:</span>
                                    <p class="font-semibold">{{ $course->department->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Duration:</span>
                                    <p class="font-semibold">{{ $course->duration ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Level:</span>
                                    <p class="font-semibold capitalize">{{ $course->level }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Code:</span>
                                    <p class="font-semibold">{{ $course->code ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Intake Period *</label>
                                <select name="intake_period" id="intakePeriod" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
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
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Study Mode *</label>
                                <select name="study_mode" id="studyMode" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="evening">Evening Classes</option>
                                    <option value="weekend">Weekend Classes</option>
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="button" onclick="nextStep(2)"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold">
                            Continue to Personal Info →
                        </button>
                    </div>
                </div>

                <!-- Step 2: Personal Information -->
                <div id="step2" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Personal Information</h3>
                    <p class="text-gray-600 mb-6">Tell us about yourself</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" name="first_name" id="firstName" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" name="last_name" id="lastName" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" id="email" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                            <input type="text" name="id_number" id="idNumber" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                            <input type="date" name="date_of_birth" id="dateOfBirth" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <select name="gender" id="gender" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                <option value="">-- Select Gender --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <input type="text" name="address" id="address"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" id="city"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                            <input type="text" name="county" id="county"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" id="postalCode"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="prevStep(1)"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors duration-200 font-semibold">
                            ← Back
                        </button>
                        <button type="button" onclick="nextStep(3)"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold">
                            Continue to Education →
                        </button>
                    </div>
                </div>

                <!-- Step 3: Education & Background -->
                <div id="step3" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Education Background</h3>
                    <p class="text-gray-600 mb-6">Tell us about your educational history</p>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Highest Education Level *</label>
                                <select name="education_level" id="educationLevel" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                    <option value="">-- Select Level --</option>
                                    <option value="KCSE">KCSE</option>
                                    <option value="KCPE">KCPE</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Degree">Degree</option>
                                    <option value="Certificate">Certificate</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">School/Institution Name *</label>
                                <input type="text" name="school_name" id="schoolName" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year *</label>
                                <input type="number" name="graduation_year" id="graduationYear" required min="1950" max="{{ date('Y') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mean Grade *</label>
                                <input type="text" name="mean_grade" id="meanGrade" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Application Type *</label>
                            <select name="application_type" id="applicationType" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                <option value="new">New Student</option>
                                <option value="transfer">Transfer Student</option>
                                <option value="continuing">Continuing Student</option>
                            </select>
                        </div>

                        <!-- Emergency Contact Information -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contact Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name *</label>
                                    <input type="text" name="emergency_contact_name" id="emergencyContactName" required
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone *</label>
                                    <input type="tel" name="emergency_contact_phone" id="emergencyContactPhone" required
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Relationship *</label>
                                    <input type="text" name="emergency_contact_relationship" id="emergencyContactRelationship" required
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent" placeholder="e.g., Parent, Guardian">
                                </div>
                            </div>
                        </div>

                        <!-- Special Needs -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Needs (if any)</label>
                            <textarea name="special_needs" id="specialNeeds" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                      placeholder="Please specify any special needs or accommodations required..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="prevStep(2)"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors duration-200 font-semibold">
                            ← Back
                        </button>
                        <button type="button" onclick="nextStep(4)"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold">
                            Continue to Documents →
                        </button>
                    </div>
                </div>

                <!-- Step 4: Documents Upload -->
                <div id="step4" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Required Documents</h3>
                    <p class="text-gray-600 mb-6">Upload the necessary documents for your application</p>

                    <div class="space-y-6">
                        <!-- ID Document -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Document *</label>
                            <input type="file" name="id_document" id="idDocument" accept=".pdf" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            <p class="text-sm text-gray-500 mt-2">National ID or Passport (PDF format only - Max 2MB)</p>
                        </div>

                        <!-- Education Certificates -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Education Certificates *</label>
                            <input type="file" name="education_certificates" id="educationCertificates" accept=".pdf" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            <p class="text-sm text-gray-500 mt-2">Academic certificates and transcripts combined in one PDF (PDF format only - Max 5MB)</p>
                        </div>

                        <!-- Passport Photo -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-[#B91C1C] transition-colors duration-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Passport Photo *</label>
                            <input type="file" name="passport_photo" id="passportPhoto" accept=".jpg,.jpeg,.png" required
                                   class="w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-[#991B1B]">
                            <p class="text-sm text-gray-500 mt-2">Recent passport-size photo (JPG, PNG - Max 2MB)</p>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="prevStep(3)"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors duration-200 font-semibold">
                            ← Back
                        </button>
                        <button type="button" onclick="nextStep(5)"
                                class="bg-[#B91C1C] text-white px-8 py-3 rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold">
                            Review Application →
                        </button>
                    </div>
                </div>

                <!-- Step 5: Review & Submit -->
                <div id="step5" class="step p-8 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Review Your Application</h3>
                    <p class="text-gray-600 mb-6">Please review all information before submitting</p>

                    <div class="space-y-6">
                        <!-- Course Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Course Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Course:</span>
                                    <p id="reviewCourse" class="font-semibold">{{ $course->name ?? 'Not selected' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Study Mode:</span>
                                    <p id="reviewStudyMode" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Intake Period:</span>
                                    <p id="reviewIntake" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Application Type:</span>
                                    <p id="reviewApplicationType" class="font-semibold">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Full Name:</span>
                                    <p id="reviewFullName" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Email:</span>
                                    <p id="reviewEmail" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Phone:</span>
                                    <p id="reviewPhone" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">ID Number:</span>
                                    <p id="reviewIdNumber" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Date of Birth:</span>
                                    <p id="reviewDateOfBirth" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Gender:</span>
                                    <p id="reviewGender" class="font-semibold">-</p>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="text-gray-600">Address:</span>
                                    <p id="reviewAddress" class="font-semibold">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Education Information -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Education Background</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Education Level:</span>
                                    <p id="reviewEducationLevel" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">School Name:</span>
                                    <p id="reviewSchoolName" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Graduation Year:</span>
                                    <p id="reviewGraduationYear" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Mean Grade:</span>
                                    <p id="reviewMeanGrade" class="font-semibold">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contact</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Name:</span>
                                    <p id="reviewEmergencyContactName" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Phone:</span>
                                    <p id="reviewEmergencyContactPhone" class="font-semibold">-</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Relationship:</span>
                                    <p id="reviewEmergencyContactRelationship" class="font-semibold">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Special Needs -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Special Requirements</h4>
                            <p id="reviewSpecialNeeds" class="text-sm">None specified</p>
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
                        <button type="button" onclick="prevStep(4)"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors duration-200 font-semibold">
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

<script>
    let currentStep = 1;
    const totalSteps = 5;

    // Course selection handler
    document.getElementById('courseSelect').addEventListener('change', function() {
        const courseId = this.value;
        if (courseId) {
            window.location.href = `{{ route('application.form') }}?course_id=${courseId}`;
        }
    });

    // Step navigation
    function nextStep(step) {
        if (validateStep(currentStep)) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.add('hidden');

            document.getElementById(`step${step}`).classList.remove('hidden');
            document.getElementById(`step${step}`).classList.add('active');

            updateProgress(step);
            currentStep = step;

            // Update review section on step 5
            if (step === 5) {
                updateReviewSection();
            }
        }
    }

    function prevStep(step) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${currentStep}`).classList.add('hidden');

        document.getElementById(`step${step}`).classList.remove('hidden');
        document.getElementById(`step${step}`).classList.add('active');

        updateProgress(step);
        currentStep = step;
    }

    function updateProgress(step) {
        const progress = ((step - 1) / (totalSteps - 1)) * 100;
        document.getElementById('progressBar').style.width = `${progress}%`;

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

    function validateStep(step) {
        switch(step) {
            case 1:
                const courseId = document.querySelector('select[name="course_id"]').value;
                const intakePeriod = document.querySelector('select[name="intake_period"]')?.value;
                const studyMode = document.querySelector('select[name="study_mode"]')?.value;

                if (!courseId) {
                    alert('Please select a course');
                    return false;
                }
                if (!intakePeriod) {
                    alert('Please select an intake period');
                    return false;
                }
                if (!studyMode) {
                    alert('Please select a study mode');
                    return false;
                }
                return true;

            case 2:
                const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'id_number', 'date_of_birth', 'gender'];
                for (let field of requiredFields) {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (!input.value.trim()) {
                        alert(`Please fill in ${field.replace('_', ' ')}`);
                        input.focus();
                        return false;
                    }
                }
                return true;

            case 3:
                const educationFields = ['education_level', 'school_name', 'graduation_year', 'mean_grade', 'application_type', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship'];
                for (let field of educationFields) {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (!input.value.trim()) {
                        alert(`Please fill in ${field.replace('_', ' ')}`);
                        input.focus();
                        return false;
                    }
                }
                return true;

            case 4:
                const fileInputs = ['id_document', 'education_certificates', 'passport_photo'];
                for (let fileInput of fileInputs) {
                    const input = document.querySelector(`[name="${fileInput}"]`);
                    if (!input.files.length) {
                        alert(`Please upload ${fileInput.replace('_', ' ')}`);
                        return false;
                    }
                }
                return true;

            default:
                return true;
        }
    }

    function updateReviewSection() {
        // Course Information
        const courseSelect = document.querySelector('select[name="course_id"]');
        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        document.getElementById('reviewCourse').textContent = selectedOption.text;

        const studyMode = document.querySelector('select[name="study_mode"]');
        document.getElementById('reviewStudyMode').textContent = studyMode.options[studyMode.selectedIndex].text;

        const intakePeriod = document.querySelector('select[name="intake_period"]');
        document.getElementById('reviewIntake').textContent = intakePeriod.options[intakePeriod.selectedIndex].text;

        const applicationType = document.querySelector('select[name="application_type"]');
        document.getElementById('reviewApplicationType').textContent = applicationType.options[applicationType.selectedIndex].text;

        // Personal Information
        const firstName = document.querySelector('input[name="first_name"]').value;
        const lastName = document.querySelector('input[name="last_name"]').value;
        document.getElementById('reviewFullName').textContent = `${firstName} ${lastName}`;

        document.getElementById('reviewEmail').textContent = document.querySelector('input[name="email"]').value;
        document.getElementById('reviewPhone').textContent = document.querySelector('input[name="phone"]').value;
        document.getElementById('reviewIdNumber').textContent = document.querySelector('input[name="id_number"]').value;
        document.getElementById('reviewDateOfBirth').textContent = document.querySelector('input[name="date_of_birth"]').value;
        document.getElementById('reviewGender').textContent = document.querySelector('select[name="gender"]').value;

        const address = document.querySelector('input[name="address"]').value;
        const city = document.querySelector('input[name="city"]').value;
        const county = document.querySelector('input[name="county"]').value;
        const postalCode = document.querySelector('input[name="postal_code"]').value;
        const fullAddress = [address, city, county, postalCode].filter(Boolean).join(', ');
        document.getElementById('reviewAddress').textContent = fullAddress || 'Not provided';

        // Education Information
        document.getElementById('reviewEducationLevel').textContent = document.querySelector('select[name="education_level"]').value;
        document.getElementById('reviewSchoolName').textContent = document.querySelector('input[name="school_name"]').value;
        document.getElementById('reviewGraduationYear').textContent = document.querySelector('input[name="graduation_year"]').value;
        document.getElementById('reviewMeanGrade').textContent = document.querySelector('input[name="mean_grade"]').value;

        // Emergency Contact
        document.getElementById('reviewEmergencyContactName').textContent = document.querySelector('input[name="emergency_contact_name"]').value;
        document.getElementById('reviewEmergencyContactPhone').textContent = document.querySelector('input[name="emergency_contact_phone"]').value;
        document.getElementById('reviewEmergencyContactRelationship').textContent = document.querySelector('input[name="emergency_contact_relationship"]').value;

        // Special Needs
        const specialNeeds = document.querySelector('textarea[name="special_needs"]').value;
        document.getElementById('reviewSpecialNeeds').textContent = specialNeeds || 'None specified';
    }

    // Confirm accuracy checkbox
    document.getElementById('confirmAccuracy').addEventListener('change', function() {
        const submitButton = document.getElementById('submitButton');
        if (this.checked) {
            submitButton.disabled = false;
            submitButton.classList.remove('bg-gray-400', 'text-gray-700', 'cursor-not-allowed');
            submitButton.classList.add('bg-[#B91C1C]', 'text-white', 'hover:bg-[#991B1B]', 'cursor-pointer');
        } else {
            submitButton.disabled = true;
            submitButton.classList.remove('bg-[#B91C1C]', 'text-white', 'hover:bg-[#991B1B]', 'cursor-pointer');
            submitButton.classList.add('bg-gray-400', 'text-gray-700', 'cursor-not-allowed');
        }
    });

    // File validation for PDF only on ID and Certificates
    document.getElementById('idDocument').addEventListener('change', function() {
        validatePDFFile(this, 2);
    });

    document.getElementById('educationCertificates').addEventListener('change', function() {
        validatePDFFile(this, 5);
    });

    document.getElementById('passportPhoto').addEventListener('change', function() {
        validateImageFile(this, 2);
    });

    function validatePDFFile(input, maxSizeMB) {
        const file = input.files[0];
        if (file) {
            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file only.');
                input.value = '';
                return;
            }
            if (file.size > maxSizeMB * 1024 * 1024) {
                alert(`File size must be less than ${maxSizeMB}MB`);
                input.value = '';
            }
        }
    }

    function validateImageFile(input, maxSizeMB) {
        const file = input.files[0];
        if (file) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please upload a JPG, JPEG, or PNG file only.');
                input.value = '';
                return;
            }
            if (file.size > maxSizeMB * 1024 * 1024) {
                alert(`File size must be less than ${maxSizeMB}MB`);
                input.value = '';
            }
        }
    }

    // Initialize progress
    updateProgress(1);
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
        ring: 2px;
    }

    .file\:bg-\[\#B91C1C\]:hover {
        background-color: #991B1B;
    }
</style>
@endsection
