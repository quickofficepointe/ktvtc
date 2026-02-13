@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Student')
@section('subtitle', 'Update student information and records')

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
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
            <a href="{{ route('admin.tvet.students.show', $student) }}" class="hover:text-primary">
                {{ $student->first_name }} {{ $student->last_name }}
            </a>
        </span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Edit</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.students.show', $student) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Profile</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-user-edit text-primary mr-2"></i>
            Edit Student Information
        </h3>
        <p class="text-sm text-gray-600 mt-1">Update the student's details below. Fields marked with <span class="text-red-500">*</span> are required.</p>
    </div>

    <form action="{{ route('admin.tvet.students.update', $student) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-8">
            <!-- ============ INSTITUTION & LINKS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-university text-primary mr-2"></i>
                    Institution Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Campus -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Campus <span class="text-red-500">*</span>
                        </label>
                        <select name="campus_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('campus_id') border-red-500 @enderror">
                            <option value="">Select Campus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" {{ old('campus_id', $student->campus_id) == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('campus_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Student Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Student Number
                        </label>
                        <input type="text"
                               name="student_number"
                               value="{{ old('student_number', $student->student_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_number') border-red-500 @enderror">
                        @error('student_number')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Application (Read-only if already linked) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Linked Application
                        </label>
                        @if($student->application)
                            <input type="text"
                                   value="{{ $student->application->application_number }}"
                                   class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">Application #: {{ $student->application->application_number }}</p>
                        @else
                            <select name="application_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">No Application Linked</option>
                                @foreach($applications as $application)
                                    <option value="{{ $application->id }}" {{ old('application_id', $student->application_id) == $application->id ? 'selected' : '' }}>
                                        {{ $application->application_number }} - {{ $application->first_name }} {{ $application->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Link to an accepted application (optional)</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ============ PERSONAL INFORMATION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user text-primary mr-2"></i>
                    Personal Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <select name="title"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="Mr." {{ old('title', $student->title) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Ms." {{ old('title', $student->title) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Mrs." {{ old('title', $student->title) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Dr." {{ old('title', $student->title) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Prof." {{ old('title', $student->title) == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                        </select>
                    </div>

                    <!-- First Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="first_name"
                               value="{{ old('first_name', $student->first_name) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <input type="text"
                               name="middle_name"
                               value="{{ old('middle_name', $student->middle_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="last_name"
                               value="{{ old('last_name', $student->last_name) }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $student->email) }}"
                                   placeholder="student@example.com"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('email') border-red-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $student->phone) }}"
                                   placeholder="07XX XXX XXX"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('phone') border-red-500 @enderror">
                        </div>
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <input type="date"
                                   name="date_of_birth"
                                   value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <!-- ID Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                        <select name="id_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="id" {{ old('id_type', $student->id_type) == 'id' ? 'selected' : '' }}>National ID</option>
                            <option value="birth_certificate" {{ old('id_type', $student->id_type) == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                            <option value="passport" {{ old('id_type', $student->id_type) == 'passport' ? 'selected' : '' }}>Passport</option>
                        </select>
                    </div>

                    <!-- ID Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
                        <input type="text"
                               name="id_number"
                               value="{{ old('id_number', $student->id_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Marital Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                        <select name="marital_status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="single" {{ old('marital_status', $student->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ old('marital_status', $student->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ old('marital_status', $student->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ old('marital_status', $student->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ============ CONTACT INFORMATION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                    Contact Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                        <textarea name="address"
                                  rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('address', $student->address) }}</textarea>
                    </div>

                    <!-- City/Town -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City/Town</label>
                        <input type="text"
                               name="city"
                               value="{{ old('city', $student->city) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- County -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                        <input type="text"
                               name="county"
                               value="{{ old('county', $student->county) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                        <input type="text"
                               name="postal_code"
                               value="{{ old('postal_code', $student->postal_code) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Country -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <input type="text"
                               name="country"
                               value="{{ old('country', $student->country ?? 'Kenya') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- ============ NEXT OF KIN ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-users text-primary mr-2"></i>
                    Next of Kin
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text"
                               name="next_of_kin_name"
                               value="{{ old('next_of_kin_name', $student->next_of_kin_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                        <select name="next_of_kin_relationship"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="parent" {{ old('next_of_kin_relationship', $student->next_of_kin_relationship) == 'parent' ? 'selected' : '' }}>Parent</option>
                            <option value="guardian" {{ old('next_of_kin_relationship', $student->next_of_kin_relationship) == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            <option value="spouse" {{ old('next_of_kin_relationship', $student->next_of_kin_relationship) == 'spouse' ? 'selected' : '' }}>Spouse</option>
                            <option value="sibling" {{ old('next_of_kin_relationship', $student->next_of_kin_relationship) == 'sibling' ? 'selected' : '' }}>Sibling</option>
                            <option value="other" {{ old('next_of_kin_relationship', $student->next_of_kin_relationship) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text"
                               name="next_of_kin_phone"
                               value="{{ old('next_of_kin_phone', $student->next_of_kin_phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email"
                               name="next_of_kin_email"
                               value="{{ old('next_of_kin_email', $student->next_of_kin_email) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                        <input type="text"
                               name="next_of_kin_id_number"
                               value="{{ old('next_of_kin_id_number', $student->next_of_kin_id_number) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="next_of_kin_address"
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('next_of_kin_address', $student->next_of_kin_address) }}</textarea>
                </div>
            </div>

            <!-- ============ EMERGENCY CONTACT ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-ambulance text-primary mr-2"></i>
                    Emergency Contact
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text"
                               name="emergency_contact_name"
                               value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                        <input type="text"
                               name="emergency_contact_relationship"
                               value="{{ old('emergency_contact_relationship', $student->emergency_contact_relationship) }}"
                               placeholder="e.g., Parent, Spouse"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Phone</label>
                        <input type="text"
                               name="emergency_contact_phone"
                               value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alternative Phone</label>
                        <input type="text"
                               name="emergency_contact_phone_alt"
                               value="{{ old('emergency_contact_phone_alt', $student->emergency_contact_phone_alt) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- ============ EDUCATION BACKGROUND ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>
                    Education Background
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Highest Education Level</label>
                        <select name="education_level"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="kcse" {{ old('education_level', $student->education_level) == 'kcse' ? 'selected' : '' }}>KCSE</option>
                            <option value="certificate" {{ old('education_level', $student->education_level) == 'certificate' ? 'selected' : '' }}>Certificate</option>
                            <option value="diploma" {{ old('education_level', $student->education_level) == 'diploma' ? 'selected' : '' }}>Diploma</option>
                            <option value="degree" {{ old('education_level', $student->education_level) == 'degree' ? 'selected' : '' }}>Degree</option>
                            <option value="masters" {{ old('education_level', $student->education_level) == 'masters' ? 'selected' : '' }}>Masters</option>
                            <option value="phd" {{ old('education_level', $student->education_level) == 'phd' ? 'selected' : '' }}>PhD</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">School/Institution Name</label>
                        <input type="text"
                               name="school_name"
                               value="{{ old('school_name', $student->school_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year</label>
                        <input type="number"
                               name="graduation_year"
                               value="{{ old('graduation_year', $student->graduation_year) }}"
                               min="1950"
                               max="{{ date('Y') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mean Grade</label>
                        <select name="mean_grade"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="A" {{ old('mean_grade', $student->mean_grade) == 'A' ? 'selected' : '' }}>A</option>
                            <option value="A-" {{ old('mean_grade', $student->mean_grade) == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('mean_grade', $student->mean_grade) == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B" {{ old('mean_grade', $student->mean_grade) == 'B' ? 'selected' : '' }}>B</option>
                            <option value="B-" {{ old('mean_grade', $student->mean_grade) == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="C+" {{ old('mean_grade', $student->mean_grade) == 'C+' ? 'selected' : '' }}>C+</option>
                            <option value="C" {{ old('mean_grade', $student->mean_grade) == 'C' ? 'selected' : '' }}>C</option>
                            <option value="C-" {{ old('mean_grade', $student->mean_grade) == 'C-' ? 'selected' : '' }}>C-</option>
                            <option value="D+" {{ old('mean_grade', $student->mean_grade) == 'D+' ? 'selected' : '' }}>D+</option>
                            <option value="D" {{ old('mean_grade', $student->mean_grade) == 'D' ? 'selected' : '' }}>D</option>
                            <option value="D-" {{ old('mean_grade', $student->mean_grade) == 'D-' ? 'selected' : '' }}>D-</option>
                            <option value="E" {{ old('mean_grade', $student->mean_grade) == 'E' ? 'selected' : '' }}>E</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">KCSE Index Number</label>
                        <input type="text"
                               name="kcse_index_number"
                               value="{{ old('kcse_index_number', $student->kcse_index_number) }}"
                               placeholder="e.g., 12345678901"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- ============ MEDICAL & SPECIAL NEEDS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-heartbeat text-primary mr-2"></i>
                    Medical & Special Needs
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group</label>
                        <select name="blood_group"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">T-Shirt Size</label>
                        <select name="tshirt_size"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select</option>
                            <option value="XS" {{ old('tshirt_size', $student->tshirt_size) == 'XS' ? 'selected' : '' }}>XS</option>
                            <option value="S" {{ old('tshirt_size', $student->tshirt_size) == 'S' ? 'selected' : '' }}>S</option>
                            <option value="M" {{ old('tshirt_size', $student->tshirt_size) == 'M' ? 'selected' : '' }}>M</option>
                            <option value="L" {{ old('tshirt_size', $student->tshirt_size) == 'L' ? 'selected' : '' }}>L</option>
                            <option value="XL" {{ old('tshirt_size', $student->tshirt_size) == 'XL' ? 'selected' : '' }}>XL</option>
                            <option value="XXL" {{ old('tshirt_size', $student->tshirt_size) == 'XXL' ? 'selected' : '' }}>XXL</option>
                            <option value="XXXL" {{ old('tshirt_size', $student->tshirt_size) == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disability Type</label>
                        <input type="text"
                               name="disability_type"
                               value="{{ old('disability_type', $student->disability_type) }}"
                               placeholder="e.g., Physical, Visual"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medical Conditions</label>
                        <textarea name="medical_conditions"
                                  rows="3"
                                  placeholder="Any known medical conditions, allergies, or chronic illnesses"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                        <textarea name="allergies"
                                  rows="3"
                                  placeholder="Any known allergies (food, medication, environmental)"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('allergies', $student->allergies) }}</textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Needs/Requirements</label>
                    <textarea name="special_needs"
                              rows="2"
                              placeholder="Any special accommodations or support needed"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('special_needs', $student->special_needs) }}</textarea>
                </div>
            </div>

            <!-- ============ DOCUMENTS ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Documents
                </h4>

                <!-- Current Documents Display -->
                @if($student->id_document_path || $student->passport_photo_path || $student->education_certificates_path || $student->other_documents)
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h5 class="text-sm font-medium text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Current Documents
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($student->id_document_path)
                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-id-card text-blue-600 mr-2"></i>
                                <span class="text-xs text-gray-700">ID Document</span>
                            </div>
                            <a href="{{ Storage::url($student->id_document_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        @endif

                        @if($student->passport_photo_path)
                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-camera text-green-600 mr-2"></i>
                                <span class="text-xs text-gray-700">Passport Photo</span>
                            </div>
                            <a href="{{ Storage::url($student->passport_photo_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        @endif

                        @if($student->education_certificates_path)
                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf text-purple-600 mr-2"></i>
                                <span class="text-xs text-gray-700">Education Cert</span>
                            </div>
                            <a href="{{ Storage::url($student->education_certificates_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Upload new documents below to replace existing ones.</p>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- ID Document -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Document</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                            <input type="file"
                                   name="id_document"
                                   id="id_document"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="hidden">
                            <div onclick="document.getElementById('id_document').click()" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                                <p class="text-xs text-gray-500">Click to upload</p>
                                <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG (Max 2MB)</p>
                            </div>
                            <div id="id_document_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                        </div>
                    </div>

                    <!-- Passport Photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Passport Photo</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                            <input type="file"
                                   name="passport_photo"
                                   id="passport_photo"
                                   accept=".jpg,.jpeg,.png"
                                   class="hidden">
                            <div onclick="document.getElementById('passport_photo').click()" class="cursor-pointer">
                                <i class="fas fa-camera text-2xl text-gray-400 mb-2"></i>
                                <p class="text-xs text-gray-500">Click to upload</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG (Max 2MB)</p>
                            </div>
                            <div id="passport_photo_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                        </div>
                    </div>

                    <!-- Education Certificates -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Education Certificates</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                            <input type="file"
                                   name="education_certificates"
                                   id="education_certificates"
                                   accept=".pdf"
                                   class="hidden">
                            <div onclick="document.getElementById('education_certificates').click()" class="cursor-pointer">
                                <i class="fas fa-file-pdf text-2xl text-gray-400 mb-2"></i>
                                <p class="text-xs text-gray-500">Click to upload</p>
                                <p class="text-xs text-gray-400 mt-1">PDF (Max 5MB)</p>
                            </div>
                            <div id="education_certificates_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                        </div>
                    </div>
                </div>

                <!-- Other Documents -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Other Documents</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                        <input type="file"
                               name="other_documents[]"
                               id="other_documents"
                               multiple
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="hidden">
                        <div onclick="document.getElementById('other_documents').click()" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                            <p class="text-sm text-gray-600">
                                <span class="text-primary font-medium">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (Max 2MB each)</p>
                        </div>
                        <div id="other_documents_list" class="mt-4 space-y-1 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- ============ ADDITIONAL INFORMATION ============ -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tag text-primary mr-2"></i>
                    Additional Information
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Student Category <span class="text-red-500">*</span>
                        </label>
                        <select name="student_category" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="regular" {{ old('student_category', $student->student_category) == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="alumnus" {{ old('student_category', $student->student_category) == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                            <option value="staff_child" {{ old('student_category', $student->student_category) == 'staff_child' ? 'selected' : '' }}>Staff Child</option>
                            <option value="sponsored" {{ old('student_category', $student->student_category) == 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                            <option value="scholarship" {{ old('student_category', $student->student_category) == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="suspended" {{ old('status', $student->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="alumnus" {{ old('status', $student->status) == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                            <option value="prospective" {{ old('status', $student->status) == 'prospective' ? 'selected' : '' }}>Prospective</option>
                            <option value="historical" {{ old('status', $student->status) == 'historical' ? 'selected' : '' }}>Historical</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Registration Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                        <input type="date"
                               name="registration_date"
                               value="{{ old('registration_date', $student->registration_date ? $student->registration_date->format('Y-m-d') : date('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Legacy Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Student Code</label>
                        <input type="text"
                               name="legacy_student_code"
                               value="{{ old('legacy_student_code', $student->legacy_student_code) }}"
                               placeholder="e.g., SHEP/261/2022"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Remarks/Notes</label>
                    <textarea name="remarks"
                              rows="3"
                              placeholder="Any additional notes or comments about this student"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('remarks', $student->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.tvet.students.show', $student) }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i>
                Update Student
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // File Upload Handlers
    document.addEventListener('DOMContentLoaded', function() {
        // ID Document
        document.getElementById('id_document')?.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2);
                const sizeText = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;
                const div = document.getElementById('id_document_name');
                div.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>${fileName} (${sizeText})</span>`;
                div.classList.remove('hidden');
            }
        });

        // Passport Photo
        document.getElementById('passport_photo')?.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2);
                const sizeText = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;
                const div = document.getElementById('passport_photo_name');
                div.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>${fileName} (${sizeText})</span>`;
                div.classList.remove('hidden');
            }
        });

        // Education Certificates
        document.getElementById('education_certificates')?.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2);
                const sizeText = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;
                const div = document.getElementById('education_certificates_name');
                div.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>${fileName} (${sizeText})</span>`;
                div.classList.remove('hidden');
            }
        });

        // Other Documents
        document.getElementById('other_documents')?.addEventListener('change', function(e) {
            const list = document.getElementById('other_documents_list');
            list.innerHTML = '';
            if (this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const fileSize = (file.size / 1024).toFixed(2);
                    const sizeText = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;
                    list.innerHTML += `<div class="text-sm text-gray-600 flex items-center">
                        <i class="fas fa-file text-blue-500 mr-2"></i>
                        ${file.name} (${sizeText})
                    </div>`;
                }
                list.classList.remove('hidden');
            } else {
                list.classList.add('hidden');
            }
        });
    });
</script>
@endsection
