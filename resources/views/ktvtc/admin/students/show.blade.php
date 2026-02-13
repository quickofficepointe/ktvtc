@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Profile')
@section('subtitle', 'View complete student information and records')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Students</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $student->first_name }} {{ $student->last_name }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.students.edit', $student) }}"
       class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Student</span>
    </a>
    <a href="{{ route('admin.tvet.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to List</span>
    </a>
</div>
@endsection

@section('content')
<!-- Student Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-primary/10 to-transparent px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    @if($student->passport_photo_path)
                        <div class="w-20 h-20 rounded-full border-4 border-white shadow-md overflow-hidden">
                            <img src="{{ Storage::url($student->passport_photo_path) }}"
                                 alt="{{ $student->first_name }} {{ $student->last_name }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-20 h-20 rounded-full bg-primary-light border-4 border-white shadow-md flex items-center justify-center">
                            <span class="text-2xl font-bold text-primary">
                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center
                        @if($student->status == 'active') bg-success text-white @endif
                        @if($student->status == 'suspended') bg-warning text-white @endif
                        @if($student->status == 'inactive' || $student->status == 'historical') bg-gray-500 text-white @endif
                        @if($student->status == 'graduated') bg-purple-600 text-white @endif
                        @if($student->status == 'dropped') bg-danger text-white @endif">
                        @if($student->status == 'active') <i class="fas fa-check text-xs"></i> @endif
                        @if($student->status == 'suspended') <i class="fas fa-pause text-xs"></i> @endif
                        @if($student->status == 'inactive' || $student->status == 'historical') <i class="fas fa-clock text-xs"></i> @endif
                        @if($student->status == 'graduated') <i class="fas fa-graduation-cap text-xs"></i> @endif
                        @if($student->status == 'dropped') <i class="fas fa-times text-xs"></i> @endif
                    </span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $student->first_name }} {{ $student->last_name }}</h2>
                        @if($student->middle_name)
                            <span class="text-gray-500 text-lg">{{ $student->middle_name }}</span>
                        @endif
                        @if($student->title)
                            <span class="text-gray-500 text-sm">{{ $student->title }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($student->status == 'active') bg-green-100 text-green-800 @endif
                            @if($student->status == 'suspended') bg-yellow-100 text-yellow-800 @endif
                            @if($student->status == 'inactive') bg-gray-100 text-gray-800 @endif
                            @if($student->status == 'graduated') bg-purple-100 text-purple-800 @endif
                            @if($student->status == 'dropped') bg-red-100 text-red-800 @endif
                            @if($student->status == 'alumnus') bg-blue-100 text-blue-800 @endif
                            @if($student->status == 'prospective') bg-amber-100 text-amber-800 @endif
                            @if($student->status == 'historical') bg-gray-100 text-gray-600 @endif">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            {{ ucfirst($student->status) }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($student->student_category == 'regular') bg-green-100 text-green-800 @endif
                            @if($student->student_category == 'alumnus') bg-purple-100 text-purple-800 @endif
                            @if($student->student_category == 'staff_child') bg-blue-100 text-blue-800 @endif
                            @if($student->student_category == 'sponsored') bg-amber-100 text-amber-800 @endif
                            @if($student->student_category == 'scholarship') bg-indigo-100 text-indigo-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $student->student_category)) }}
                        </span>
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-id-card mr-1"></i> Student #: {{ $student->student_number ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                @if($student->status != 'active')
                    <form action="{{ route('admin.tvet.students.activate', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to activate this student?')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Activate
                        </button>
                    </form>
                @endif
                @if($student->status != 'suspended' && $student->status != 'inactive')
                    <form action="{{ route('admin.tvet.students.suspend', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to suspend this student?')"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm flex items-center">
                            <i class="fas fa-pause-circle mr-2"></i>
                            Suspend
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.tvet.students.archive', $student) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Are you sure you want to archive this student?')"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm flex items-center">
                        <i class="fas fa-archive mr-2"></i>
                        Archive
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50 border-b border-gray-200">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Registered</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $student->registration_date ? \Carbon\Carbon::parse($student->registration_date)->format('M d, Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                    <i class="fas fa-id-card text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">ID Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $student->id_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                    <i class="fas fa-university text-purple-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Campus</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $student->campus->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mr-3">
                    <i class="fas fa-tag text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Registration Type</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $student->registration_type_label ?? ucfirst(str_replace('_', ' ', $student->registration_type)) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Personal & Contact Info -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user-circle text-primary mr-2"></i>
                    Personal Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Full Name</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $student->title ? $student->title . ' ' : '' }}
                            {{ $student->first_name }}
                            {{ $student->middle_name ? $student->middle_name . ' ' : '' }}
                            {{ $student->last_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Date of Birth</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A' }}
                            @if($student->date_of_birth)
                                <span class="text-xs text-gray-500 ml-2">({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years)</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Gender</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($student->gender ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Marital Status</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($student->marital_status ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Email Address</p>
                        <p class="text-sm font-medium text-gray-800">
                            @if($student->email)
                                <a href="mailto:{{ $student->email }}" class="text-primary hover:underline">
                                    {{ $student->email }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Phone Number</p>
                        <p class="text-sm font-medium text-gray-800">
                            @if($student->phone)
                                <a href="tel:{{ $student->phone }}" class="text-primary hover:underline">
                                    {{ $student->phone }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ID Type</p>
                        <p class="text-sm font-medium text-gray-800">
                            @if($student->id_type == 'id') National ID
                            @elseif($student->id_type == 'birth_certificate') Birth Certificate
                            @elseif($student->id_type == 'passport') Passport
                            @else N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ID Number</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->id_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                    Contact Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Physical Address</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">City/Town</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->city ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">County</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->county ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Postal Code</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->postal_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Country</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->country ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Education Background Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>
                    Education Background
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Highest Education Level</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($student->education_level ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">School/Institution</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->school_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Graduation Year</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->graduation_year ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Mean Grade</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->mean_grade ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">KCSE Index Number</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->kcse_index_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical & Special Needs Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-heartbeat text-primary mr-2"></i>
                    Medical & Special Needs
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Blood Group</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->blood_group ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">T-Shirt Size</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->tshirt_size ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Disability Type</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->disability_type ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Medical Conditions</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->medical_conditions ?? 'None reported' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Allergies</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->allergies ?? 'None reported' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Special Needs</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->special_needs ?? 'None reported' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Side Information -->
    <div class="space-y-6">
        <!-- Application Information Card -->
        @if($student->application)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Application Details
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Application Number</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->application->application_number }}</p>
                    </div>
                    @if($student->application->course)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Course</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->application->course->name }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Intake Period</p>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($student->application->intake_period ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Study Mode</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->application->study_mode_label ?? ucfirst(str_replace('_', ' ', $student->application->study_mode)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Submitted On</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->application->submitted_at ? \Carbon\Carbon::parse($student->application->submitted_at)->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Next of Kin Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-users text-primary mr-2"></i>
                    Next of Kin
                </h3>
            </div>
            <div class="p-6">
                @if($student->next_of_kin_name)
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Full Name</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->next_of_kin_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Relationship</p>
                            <p class="text-sm font-medium text-gray-800">{{ ucfirst($student->next_of_kin_relationship ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Phone Number</p>
                            <p class="text-sm font-medium text-gray-800">
                                @if($student->next_of_kin_phone)
                                    <a href="tel:{{ $student->next_of_kin_phone }}" class="text-primary hover:underline">
                                        {{ $student->next_of_kin_phone }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Email</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->next_of_kin_email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">ID Number</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->next_of_kin_id_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Address</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->next_of_kin_address ?? 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No next of kin information available</p>
                @endif
            </div>
        </div>

        <!-- Emergency Contact Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-ambulance text-primary mr-2"></i>
                    Emergency Contact
                </h3>
            </div>
            <div class="p-6">
                @if($student->emergency_contact_name)
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Full Name</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->emergency_contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Relationship</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->emergency_contact_relationship ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Primary Phone</p>
                            <p class="text-sm font-medium text-gray-800">
                                @if($student->emergency_contact_phone)
                                    <a href="tel:{{ $student->emergency_contact_phone }}" class="text-primary hover:underline">
                                        {{ $student->emergency_contact_phone }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Alternative Phone</p>
                            <p class="text-sm font-medium text-gray-800">{{ $student->emergency_contact_phone_alt ?? 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No emergency contact information available</p>
                @endif
            </div>
        </div>

        <!-- Additional Information Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Additional Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Legacy Student Code</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->legacy_student_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Import Batch</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->import_batch ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Requires Cleanup</p>
                        <p class="text-sm font-medium">
                            @if($student->requires_cleanup)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    Yes
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    No
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Remarks</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->remarks ?? 'No remarks' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Created At</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->created_at ? $student->created_at->format('M d, Y H:i A') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->updated_at ? $student->updated_at->format('M d, Y H:i A') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Documents Section -->
@if($student->id_document_path || $student->passport_photo_path || $student->education_certificates_path || $student->other_documents)
<div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-file-alt text-primary mr-2"></i>
            Documents
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @if($student->id_document_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-id-card text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">ID Document</p>
                        <p class="text-xs text-gray-500 mb-2">
                            @php
                                $fileName = basename($student->id_document_path);
                                echo Str::limit($fileName, 25);
                            @endphp
                        </p>
                        <a href="{{ Storage::url($student->id_document_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-download mr-1"></i>
                            View/Download
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($student->passport_photo_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-camera text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Passport Photo</p>
                        <p class="text-xs text-gray-500 mb-2">
                            @php
                                $fileName = basename($student->passport_photo_path);
                                echo Str::limit($fileName, 25);
                            @endphp
                        </p>
                        <a href="{{ Storage::url($student->passport_photo_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-download mr-1"></i>
                            View/Download
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($student->education_certificates_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                        <i class="fas fa-file-pdf text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">Education Certificates</p>
                        <p class="text-xs text-gray-500 mb-2">
                            @php
                                $fileName = basename($student->education_certificates_path);
                                echo Str::limit($fileName, 25);
                            @endphp
                        </p>
                        <a href="{{ Storage::url($student->education_certificates_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-download mr-1"></i>
                            View/Download
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($student->other_documents)
                @foreach($student->other_documents as $index => $doc)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mr-3">
                            <i class="fas fa-file text-amber-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">Other Document {{ $index + 1 }}</p>
                            <p class="text-xs text-gray-500 mb-2">{{ Str::limit($doc['name'] ?? 'document', 25) }}</p>
                            @if(isset($doc['path']))
                                <a href="{{ Storage::url($doc['path']) }}"
                                   target="_blank"
                                   class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                                    <i class="fas fa-download mr-1"></i>
                                    View/Download
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif

<!-- Action Buttons -->
<div class="mt-6 flex justify-end space-x-3">
    <a href="{{ route('admin.tvet.students.edit', $student) }}"
       class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors flex items-center">
        <i class="fas fa-edit mr-2"></i>
        Edit Student
    </a>
    <a href="{{ route('admin.tvet.students.details', $student) }}"
       class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        View Full Details
    </a>
    <form action="{{ route('admin.tvet.students.destroy', $student) }}" method="POST" class="inline"
          onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
            <i class="fas fa-trash mr-2"></i>
            Delete Student
        </button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Initialize any JavaScript for the show page
    document.addEventListener('DOMContentLoaded', function() {
        // Add tooltips to document icons
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(el => {
            el.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded-lg';
                tooltip.textContent = this.dataset.tooltip;
                tooltip.style.top = (e.pageY - 30) + 'px';
                tooltip.style.left = (e.pageX - tooltip.offsetWidth / 2) + 'px';
                document.body.appendChild(tooltip);

                this.addEventListener('mouseleave', function() {
                    tooltip.remove();
                }, { once: true });
            });
        });
    });
</script>
@endsection
