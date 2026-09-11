@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Student Profile')
@section('subtitle', 'View complete student information and records')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Students</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">
            {{ $student->first_name }} {{ $student->last_name }}
        </span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.students.edit', $student) }}"
       class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i><span>Edit Student</span>
    </a>
    <a href="{{ route('admin.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i><span>Back to List</span>
    </a>
</div>
@endsection

@section('content')
{{-- ═══════════ STUDENT HEADER ═══════════ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-primary/10 to-transparent px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    @if($student->passport_photo_path)
                        <div class="w-20 h-20 rounded-full border-4 border-white shadow-md overflow-hidden">
                            <img src="{{ Storage::disk('public')->url($student->passport_photo_path) }}"
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
                </div>
                <div>
                    <div class="flex items-center space-x-2 flex-wrap">
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $student->first_name }} {{ $student->last_name }}
                        </h2>
                        @if($student->middle_name)<span class="text-gray-500 text-lg">{{ $student->middle_name }}</span>@endif
                        @if($student->title)<span class="text-gray-500 text-sm">{{ $student->title }}</span>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        @php
                            $statusBadges = [
                                'active'      => 'bg-green-100 text-green-800',
                                'suspended'   => 'bg-yellow-100 text-yellow-800',
                                'inactive'    => 'bg-gray-100 text-gray-800',
                                'graduated'   => 'bg-purple-100 text-purple-800',
                                'dropped'     => 'bg-red-100 text-red-800',
                                'alumnus'     => 'bg-blue-100 text-blue-800',
                                'prospective' => 'bg-amber-100 text-amber-800',
                                'historical'  => 'bg-gray-100 text-gray-600',
                            ];
                            $statusColor = $statusBadges[$student->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                            <i class="fas fa-circle mr-1 text-xs"></i>{{ ucfirst($student->status) }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $student->student_category ?? 'regular')) }}
                        </span>
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-id-card mr-1"></i>
                            Student #: <span class="font-mono">{{ $student->student_number ?? 'N/A' }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                @if($student->status != 'active')
                    <form action="{{ route('admin.students.activate', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Activate this student?')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>Activate
                        </button>
                    </form>
                @endif
                @if($student->status != 'suspended' && $student->status != 'inactive')
                    <form action="{{ route('admin.students.suspend', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Suspend this student?')"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm flex items-center">
                            <i class="fas fa-pause-circle mr-2"></i>Suspend
                        </button>
                    </form>
                @endif
                <form action="{{ route('admin.students.archive', $student) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Archive this student?')"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm flex items-center">
                        <i class="fas fa-archive mr-2"></i>Archive
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50 border-b border-gray-200">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Registered</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $student->registration_date ? \Carbon\Carbon::parse($student->registration_date)->format('M d, Y') : 'N/A' }}
                    </p>
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
                    <p class="text-sm font-semibold text-gray-800">
                        {{ ucfirst(str_replace('_', ' ', $student->registration_type ?? 'manual')) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ CURRENT ENROLLMENT & FEES ═══════════ --}}
@if($currentEnrollment)
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-book-open text-primary mr-2"></i> Current Enrollment
        </h3>
        @php
            $enrStatus = $currentEnrollment->status;
            $enrColor = match($enrStatus) {
                'active'      => 'bg-green-100 text-green-700',
                'in_progress' => 'bg-blue-100 text-blue-700',
                'completed'   => 'bg-purple-100 text-purple-700',
                'graduated'   => 'bg-indigo-100 text-indigo-700',
                'suspended'   => 'bg-yellow-100 text-yellow-700',
                'dropped'     => 'bg-red-100 text-red-700',
                default       => 'bg-gray-100 text-gray-700',
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $enrColor }}">
            <i class="fas fa-circle mr-1 text-xs"></i>
            {{ ucfirst(str_replace('_', ' ', $enrStatus)) }}
        </span>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">Student Number</p>
                <p class="text-sm font-mono font-semibold text-gray-800">
                    {{ $currentEnrollment->student_number ?? $student->student_number ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Course</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $currentEnrollment->course_name ?? $currentEnrollment->course->name ?? '—' }}
                    @php $cc = $currentEnrollment->course_code ?? $currentEnrollment->course->code ?? null; @endphp
                    @if($cc)<span class="text-xs text-gray-500 font-mono">({{ $cc }})</span>@endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Enrolled On</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $currentEnrollment->enrollment_date
                        ? \Carbon\Carbon::parse($currentEnrollment->enrollment_date)->format('M d, Y')
                        : ($currentEnrollment->created_at ? $currentEnrollment->created_at->format('M d, Y') : '—') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Current Term</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $currentEnrollment->term->name ?? $currentTerm->name ?? '—' }}
                </p>
                @if($currentEnrollment->term?->code)
                    <p class="text-xs text-gray-400 font-mono">{{ $currentEnrollment->term->code }}</p>
                @endif
            </div>
        </div>

        {{-- Fee summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-xs text-blue-600 mb-1">Total Fees</p>
                <p class="text-lg font-bold text-blue-800">
                    KES {{ number_format((float) $currentEnrollment->total_fees, 2) }}
                </p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-xs text-green-600 mb-1">Amount Paid</p>
                <p class="text-lg font-bold text-green-800">
                    KES {{ number_format((float) $currentEnrollment->amount_paid, 2) }}
                </p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-xs text-red-600 mb-1">Balance</p>
                <p class="text-lg font-bold text-red-800">
                    KES {{ number_format((float) $currentEnrollment->balance, 2) }}
                </p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-xs text-purple-600 mb-1">Payment Progress</p>
                <p class="text-lg font-bold text-purple-800">
                    {{ (int) ($currentEnrollment->payment_progress ?? 0) }}%
                </p>
                <div class="w-full h-1.5 bg-purple-200 rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-purple-600 rounded-full"
                         style="width: {{ (int) ($currentEnrollment->payment_progress ?? 0) }}%"></div>
                </div>
            </div>
        </div>

        {{-- All enrollments shortcut --}}
        @if($student->enrollments->count() > 1)
            <div class="mt-4 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                This student has <strong>{{ $student->enrollments->count() }}</strong> enrollments in total.
            </div>
        @endif
    </div>
</div>
@else
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-book-open text-primary mr-2"></i> Enrollment
        </h3>
    </div>
    <div class="p-6 text-center">
        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
        <p class="text-sm text-gray-500">This student has no enrollments yet.</p>
    </div>
</div>
@endif

{{-- ═══════════ MAIN GRID ═══════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- LEFT COLUMN --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- PERSONAL --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user-circle text-primary mr-2"></i> Personal Information
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
                                <a href="mailto:{{ $student->email }}" class="text-primary hover:underline">{{ $student->email }}</a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Phone Number</p>
                        <p class="text-sm font-medium text-gray-800">
                            @if($student->phone)
                                <a href="tel:{{ $student->phone }}" class="text-primary hover:underline">{{ $student->phone }}</a>
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

        {{-- CONTACT --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-map-marker-alt text-primary mr-2"></i> Contact Information
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

        {{-- EDUCATION --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i> Education Background
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Highest Education Level</p>
                        <p class="text-sm font-medium text-gray-800">{{ strtoupper($student->education_level ?? 'N/A') }}</p>
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

        {{-- MEDICAL --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-heartbeat text-primary mr-2"></i> Medical & Special Needs
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

    {{-- RIGHT COLUMN --}}
    <div class="space-y-6">

        {{-- APPLICATION --}}
        @if($student->application)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i> Application Details
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
                </div>
            </div>
        </div>
        @endif

        {{-- NEXT OF KIN --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-users text-primary mr-2"></i> Next of Kin
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
                                    <a href="tel:{{ $student->next_of_kin_phone }}" class="text-primary hover:underline">{{ $student->next_of_kin_phone }}</a>
                                @else N/A @endif
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

        {{-- EMERGENCY --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-ambulance text-primary mr-2"></i> Emergency Contact
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
                                    <a href="tel:{{ $student->emergency_contact_phone }}" class="text-primary hover:underline">{{ $student->emergency_contact_phone }}</a>
                                @else N/A @endif
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

        {{-- ADDITIONAL --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i> Additional Information
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Yes</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Remarks</p>
                        <p class="text-sm font-medium text-gray-800">{{ $student->remarks ?? 'No remarks' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Created At</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $student->created_at ? $student->created_at->format('M d, Y H:i A') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $student->updated_at ? $student->updated_at->format('M d, Y H:i A') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ DOCUMENTS (FIXED) ═══════════ --}}
@php
    $hasDocs = $student->id_document_path
        || $student->passport_photo_path
        || $student->education_certificates_path
        || (is_array($student->other_documents) && count($student->other_documents) > 0);
@endphp

@if($hasDocs)
<div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-file-alt text-primary mr-2"></i> Documents
        </h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- ID document --}}
            @if($student->id_document_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-id-card text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">ID Document</p>
                        <p class="text-xs text-gray-500 mb-2 truncate">{{ basename($student->id_document_path) }}</p>
                        <a href="{{ Storage::disk('public')->url($student->id_document_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-external-link-alt mr-1"></i> View
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Passport photo --}}
            @if($student->passport_photo_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-camera text-green-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">Passport Photo</p>
                        <p class="text-xs text-gray-500 mb-2 truncate">{{ basename($student->passport_photo_path) }}</p>
                        <a href="{{ Storage::disk('public')->url($student->passport_photo_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-external-link-alt mr-1"></i> View
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Education cert --}}
            @if($student->education_certificates_path)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                        <i class="fas fa-file-pdf text-purple-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">Education Certificate</p>
                        <p class="text-xs text-gray-500 mb-2 truncate">{{ basename($student->education_certificates_path) }}</p>
                        <a href="{{ Storage::disk('public')->url($student->education_certificates_path) }}"
                           target="_blank"
                           class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                            <i class="fas fa-external-link-alt mr-1"></i> View
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Other documents --}}
            @if(is_array($student->other_documents))
                @foreach($student->other_documents as $index => $doc)
                    @php
                        $docName = is_array($doc) ? ($doc['name'] ?? ('Document ' . ($index + 1))) : basename($doc);
                        $docPath = is_array($doc) ? ($doc['path'] ?? null) : $doc;
                    @endphp
                    @if($docPath)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mr-3">
                                <i class="fas fa-file text-amber-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">Other Document</p>
                                <p class="text-xs text-gray-500 mb-2 truncate">{{ $docName }}</p>
                                <a href="{{ Storage::disk('public')->url($docPath) }}"
                                   target="_blank"
                                   class="inline-flex items-center text-xs text-primary hover:text-primary-dark">
                                    <i class="fas fa-external-link-alt mr-1"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif

        </div>
    </div>
</div>
@endif

{{-- ═══════════ ACTIONS ═══════════ --}}
<div class="mt-6 flex justify-end space-x-3">
    <a href="{{ route('admin.students.edit', $student) }}"
       class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors flex items-center">
        <i class="fas fa-edit mr-2"></i> Edit Student
    </a>
    <a href="{{ route('admin.students.details', $student) }}"
       class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center">
        <i class="fas fa-info-circle mr-2"></i> View Full Details
    </a>
    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline"
          onsubmit="return confirm('Delete this student? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
            <i class="fas fa-trash mr-2"></i> Delete Student
        </button>
    </form>
</div>
@endsection