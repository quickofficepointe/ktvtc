@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Add New Student')
@section('subtitle', 'Create a new student record manually or from an accepted application')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Add New Student</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Students</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <!-- Form Header with Tabs -->
    <div class="border-b border-gray-200">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Student Information</h3>
                    <p class="text-sm text-gray-600 mt-1">Choose your preferred method to add a student</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Fields marked with <span class="text-red-500">*</span> are required
                    </span>
                </div>
            </div>
        </div>

        <!-- Source Selection Tabs -->
        <div class="px-6 border-t border-gray-200 bg-gray-50">
            <div class="flex space-x-4">
                <button type="button"
                        onclick="switchSource('manual')"
                        id="tabManual"
                        class="py-3 px-1 border-b-2 border-primary text-primary font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-user-edit"></i>
                    <span>Manual Entry</span>
                </button>
                <button type="button"
                        onclick="switchSource('application')"
                        id="tabApplication"
                        class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-file-alt"></i>
                    <span>From Application</span>
                </button>
                <button type="button"
                        onclick="switchSource('import')"
                        id="tabImport"
                        class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-upload"></i>
                    <span>Bulk Import</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============ MANUAL ENTRY FORM ============ -->
    <div id="manualForm" class="block">
        <form action="{{ route('admin.tvet.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="registration_type" value="manual_entry">

            <div class="p-6 space-y-8">
                <!-- Institution Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-university text-primary mr-2"></i>
                        Institution Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campus -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Campus <span class="text-red-500">*</span>
                            </label>
                            <select name="campus_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('campus_id') border-red-500 @enderror">
                                <option value="">Select Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
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
                                   value="{{ old('student_number') }}"
                                   placeholder="Leave empty to auto-generate"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('student_number') border-red-500 @enderror">
                            @error('student_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Format: STU/YYYY/MM/XXXX</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-primary mr-2"></i>
                        Personal Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <select name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="Mr." {{ old('title') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Ms." {{ old('title') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Mrs." {{ old('title') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Dr." {{ old('title') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                <option value="Prof." {{ old('title') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       placeholder="student@example.com"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('email') border-red-500 @enderror">
                            </div>
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       placeholder="07XX XXX XXX"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('phone') border-red-500 @enderror">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                            <select name="id_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="id" {{ old('id_type') == 'id' ? 'selected' : '' }}>National ID</option>
                                <option value="birth_certificate" {{ old('id_type') == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                                <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
                            <input type="text" name="id_number" value="{{ old('id_number') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                            <select name="marital_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                        Contact Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('address') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City/Town</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                            <input type="text" name="county" value="{{ old('county') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input type="text" name="country" value="{{ old('country', 'Kenya') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Next of Kin -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-users text-primary mr-2"></i>
                        Next of Kin
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                            <select name="next_of_kin_relationship" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="parent" {{ old('next_of_kin_relationship') == 'parent' ? 'selected' : '' }}>Parent</option>
                                <option value="guardian" {{ old('next_of_kin_relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                <option value="spouse" {{ old('next_of_kin_relationship') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="sibling" {{ old('next_of_kin_relationship') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                <option value="other" {{ old('next_of_kin_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="next_of_kin_phone" value="{{ old('next_of_kin_phone') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="next_of_kin_email" value="{{ old('next_of_kin_email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                            <input type="text" name="next_of_kin_id_number" value="{{ old('next_of_kin_id_number') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="next_of_kin_address" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('next_of_kin_address') }}</textarea>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-ambulance text-primary mr-2"></i>
                        Emergency Contact
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}"
                                   placeholder="e.g., Parent, Spouse"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Phone</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alternative Phone</label>
                            <input type="text" name="emergency_contact_phone_alt" value="{{ old('emergency_contact_phone_alt') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Education Background -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-graduation-cap text-primary mr-2"></i>
                        Education Background
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Highest Education Level</label>
                            <select name="education_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="kcse" {{ old('education_level') == 'kcse' ? 'selected' : '' }}>KCSE</option>
                                <option value="certificate" {{ old('education_level') == 'certificate' ? 'selected' : '' }}>Certificate</option>
                                <option value="diploma" {{ old('education_level') == 'diploma' ? 'selected' : '' }}>Diploma</option>
                                <option value="degree" {{ old('education_level') == 'degree' ? 'selected' : '' }}>Degree</option>
                                <option value="masters" {{ old('education_level') == 'masters' ? 'selected' : '' }}>Masters</option>
                                <option value="phd" {{ old('education_level') == 'phd' ? 'selected' : '' }}>PhD</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">School/Institution Name</label>
                            <input type="text" name="school_name" value="{{ old('school_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year</label>
                            <input type="number" name="graduation_year" value="{{ old('graduation_year') }}"
                                   min="1950" max="{{ date('Y') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mean Grade</label>
                            <select name="mean_grade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="A" {{ old('mean_grade') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="A-" {{ old('mean_grade') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('mean_grade') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B" {{ old('mean_grade') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="B-" {{ old('mean_grade') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="C+" {{ old('mean_grade') == 'C+' ? 'selected' : '' }}>C+</option>
                                <option value="C" {{ old('mean_grade') == 'C' ? 'selected' : '' }}>C</option>
                                <option value="C-" {{ old('mean_grade') == 'C-' ? 'selected' : '' }}>C-</option>
                                <option value="D+" {{ old('mean_grade') == 'D+' ? 'selected' : '' }}>D+</option>
                                <option value="D" {{ old('mean_grade') == 'D' ? 'selected' : '' }}>D</option>
                                <option value="D-" {{ old('mean_grade') == 'D-' ? 'selected' : '' }}>D-</option>
                                <option value="E" {{ old('mean_grade') == 'E' ? 'selected' : '' }}>E</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">KCSE Index Number</label>
                            <input type="text" name="kcse_index_number" value="{{ old('kcse_index_number') }}"
                                   placeholder="e.g., 12345678901"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Medical & Special Needs -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-heartbeat text-primary mr-2"></i>
                        Medical & Special Needs
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group</label>
                            <select name="blood_group" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">T-Shirt Size</label>
                            <select name="tshirt_size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select</option>
                                <option value="XS" {{ old('tshirt_size') == 'XS' ? 'selected' : '' }}>XS</option>
                                <option value="S" {{ old('tshirt_size') == 'S' ? 'selected' : '' }}>S</option>
                                <option value="M" {{ old('tshirt_size') == 'M' ? 'selected' : '' }}>M</option>
                                <option value="L" {{ old('tshirt_size') == 'L' ? 'selected' : '' }}>L</option>
                                <option value="XL" {{ old('tshirt_size') == 'XL' ? 'selected' : '' }}>XL</option>
                                <option value="XXL" {{ old('tshirt_size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                                <option value="XXXL" {{ old('tshirt_size') == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Disability Type</label>
                            <input type="text" name="disability_type" value="{{ old('disability_type') }}"
                                   placeholder="e.g., Physical, Visual"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medical Conditions</label>
                            <textarea name="medical_conditions" rows="3"
                                      placeholder="Any known medical conditions, allergies, or chronic illnesses"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('medical_conditions') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea name="allergies" rows="3"
                                      placeholder="Any known allergies (food, medication, environmental)"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('allergies') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Special Needs/Requirements</label>
                        <textarea name="special_needs" rows="2"
                                  placeholder="Any special accommodations or support needed"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('special_needs') }}</textarea>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-primary mr-2"></i>
                        Documents
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- ID Document -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Document</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                <input type="file" name="id_document" id="id_document" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
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
                                <input type="file" name="passport_photo" id="passport_photo" accept=".jpg,.jpeg,.png" class="hidden">
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
                                <input type="file" name="education_certificates" id="education_certificates" accept=".pdf" class="hidden">
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
                            <input type="file" name="other_documents[]" id="other_documents" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
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

                <!-- Additional Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tag text-primary mr-2"></i>
                        Additional Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Student Category <span class="text-red-500">*</span>
                            </label>
                            <select name="student_category" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="regular" {{ old('student_category', 'regular') == 'regular' ? 'selected' : '' }}>Regular</option>
                                <option value="alumnus" {{ old('student_category') == 'alumnus' ? 'selected' : '' }}>Alumnus</option>
                                <option value="staff_child" {{ old('student_category') == 'staff_child' ? 'selected' : '' }}>Staff Child</option>
                                <option value="sponsored" {{ old('student_category') == 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                                <option value="scholarship" {{ old('student_category') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="prospective" {{ old('status') == 'prospective' ? 'selected' : '' }}>Prospective</option>
                                <option value="historical" {{ old('status') == 'historical' ? 'selected' : '' }}>Historical</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                            <input type="date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Student Code</label>
                            <input type="text" name="legacy_student_code" value="{{ old('legacy_student_code') }}"
                                   placeholder="e.g., SHEP/261/2022"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks/Notes</label>
                        <textarea name="remarks" rows="3"
                                  placeholder="Any additional notes or comments about this student"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.tvet.students.index') }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Student
                </button>
            </div>
        </form>
    </div>

    <!-- ============ FROM APPLICATION FORM ============ -->
    <div id="applicationForm" class="hidden">
        <div class="p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Create Student from Accepted Application</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Select an accepted application below. The student's information will be automatically mapped from the application.
                            You can add additional information before submitting.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.tvet.students.store') }}" method="POST" id="applicationImportForm">
                @csrf
                <input type="hidden" name="registration_type" value="online_application">
                <input type="hidden" name="application_id" id="application_id_hidden" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Application Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Application <span class="text-red-500">*</span>
                        </label>
                        <select name="application_id" id="application_select" onchange="loadApplicationData()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                required>
                            <option value="">-- Select an accepted application --</option>
                            @foreach($applications as $application)
                                <option value="{{ $application->id }}"
                                        data-application='{{ json_encode([
                                            'application_number' => $application->application_number,
                                            'first_name' => $application->first_name,
                                            'last_name' => $application->last_name,
                                            'middle_name' => $application->middle_name,
                                            'email' => $application->email,
                                            'phone' => $application->phone,
                                            'id_type' => $application->id_type,
                                            'id_number' => $application->id_number,
                                            'date_of_birth' => $application->date_of_birth,
                                            'gender' => $application->gender,
                                            'address' => $application->address,
                                            'city' => $application->city,
                                            'county' => $application->county,
                                            'postal_code' => $application->postal_code,
                                            'country' => $application->country,
                                            'campus_id' => $application->campus_id,
                                            'campus_name' => $application->campus->name ?? 'N/A',
                                            'course_name' => $application->course->name ?? 'N/A',
                                            'emergency_contact_name' => $application->emergency_contact_name,
                                            'emergency_contact_phone' => $application->emergency_contact_phone,
                                            'emergency_contact_relationship' => $application->emergency_contact_relationship,
                                            'education_level' => $application->education_level,
                                            'school_name' => $application->school_name,
                                            'graduation_year' => $application->graduation_year,
                                            'mean_grade' => $application->mean_grade,
                                            'special_needs' => $application->special_needs,
                                        ]) }}'>
                                    {{ $application->application_number }} - {{ $application->first_name }} {{ $application->last_name }}
                                    ({{ $application->course->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @if($applications->isEmpty())
                            <p class="mt-2 text-sm text-amber-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                No accepted applications available. Please accept applications first.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Application Data Preview -->
                <div id="applicationDataPreview" class="bg-gray-50 p-6 rounded-lg hidden">
                    <h4 class="text-md font-medium text-gray-800 mb-4">Application Summary</h4>
                    <div id="applicationSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <!-- Dynamically filled by JavaScript -->
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-600 mb-2">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                            The following fields will be automatically populated from the application:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs text-gray-600">
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> First Name</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Last Name</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Email</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Phone</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> ID Number</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Date of Birth</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Gender</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Address</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Emergency Contact</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Education</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Special Needs</span>
                            <span class="flex items-center"><i class="fas fa-check text-green-500 mr-1"></i> Campus</span>
                        </div>
                    </div>

                    <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-800 flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>
                                <strong>Next of Kin & Additional Fields:</strong> These fields are not in the application and can be filled below.
                                The emergency contact from the application will be used as the next of kin.
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Additional Fields for Application Import -->
                <div id="additionalFields" class="mt-6 hidden">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-plus-circle text-primary mr-2"></i>
                            Additional Information
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Student Category <span class="text-red-500">*</span>
                                </label>
                                <select name="student_category" id="student_category" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="regular">Regular</option>
                                    <option value="alumnus">Alumnus</option>
                                    <option value="staff_child">Staff Child</option>
                                    <option value="sponsored">Sponsored</option>
                                    <option value="scholarship">Scholarship</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="active">Active</option>
                                    <option value="prospective">Prospective</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                <select name="title" id="title"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select</option>
                                    <option value="Mr.">Mr.</option>
                                    <option value="Ms.">Ms.</option>
                                    <option value="Mrs.">Mrs.</option>
                                    <option value="Dr.">Dr.</option>
                                    <option value="Prof.">Prof.</option>
                                </select>
                            </div>

                            <!-- Marital Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                <select name="marital_status" id="marital_status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                            </div>

                            <!-- T-Shirt Size -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">T-Shirt Size</label>
                                <select name="tshirt_size" id="tshirt_size"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select</option>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                    <option value="XXXL">XXXL</option>
                                </select>
                            </div>

                            <!-- Blood Group -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group</label>
                                <select name="blood_group" id="blood_group"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>

                            <!-- Disability Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Disability Type</label>
                                <input type="text" name="disability_type" id="disability_type"
                                       placeholder="e.g., Physical, Visual"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- KCSE Index Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">KCSE Index Number</label>
                                <input type="text" name="kcse_index_number" id="kcse_index_number"
                                       placeholder="e.g., 12345678901"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- Registration Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                                <input type="date" name="registration_date" id="registration_date"
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- Legacy Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Student Code</label>
                                <input type="text" name="legacy_student_code" id="legacy_student_code"
                                       placeholder="e.g., SHEP/261/2022"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Medical Conditions -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medical Conditions</label>
                            <textarea name="medical_conditions" id="medical_conditions" rows="2"
                                      placeholder="Any known medical conditions"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>

                        <!-- Allergies -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea name="allergies" id="allergies" rows="2"
                                      placeholder="Any known allergies"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>

                        <!-- Remarks -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks/Notes</label>
                            <textarea name="remarks" id="remarks" rows="2"
                                      placeholder="Additional notes about this student"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <button type="button" onclick="switchSource('manual')"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="importApplicationBtn" disabled
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center opacity-50 cursor-not-allowed">
                        <i class="fas fa-file-import mr-2"></i>
                        Create Student from Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ BULK IMPORT REDIRECT ============ -->
    <div id="importForm" class="hidden">
        <div class="p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Bulk Import Students</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Use the bulk import feature to add multiple students at once from an Excel or CSV file.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Template Download Card -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-download text-primary"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-800">Download Template</h4>
                            <p class="text-xs text-gray-500">Get started with our Excel/CSV template</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Download our template with the correct column headers and format.
                    </p>
                    <div class="space-y-3">
                        <a href="{{ route('admin.tvet.students.export') }}?format=xlsx&template=true"
                           class="w-full px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                            <i class="fas fa-file-excel"></i>
                            <span>Excel Template (.xlsx)</span>
                        </a>
                        <a href="{{ route('admin.tvet.students.export') }}?format=csv&template=true"
                           class="w-full px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                            <i class="fas fa-file-csv"></i>
                            <span>CSV Template (.csv)</span>
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 text-center mt-3">
                        Contains all supported columns with example data
                    </p>
                </div>

                <!-- Go to Import Page Card -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-upload text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-800">Import Your File</h4>
                            <p class="text-xs text-gray-500">Upload and process your data file</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Go to the dedicated bulk import page to upload your completed template.
                    </p>
                    <a href="{{ route('admin.tvet.students.import.view') }}"
                       class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-arrow-right"></i>
                        <span>Go to Bulk Import</span>
                    </a>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">Import Tips</h4>
                        <ul class="mt-1 text-xs text-yellow-700 list-disc list-inside space-y-1">
                            <li>Maximum file size: 10MB</li>
                            <li>Supported formats: .xlsx, .xls, .csv</li>
                            <li>First row must contain column headers</li>
                            <li>Download the template to ensure correct column names</li>
                            <li>Check for duplicate ID numbers before importing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ TAB SWITCHING ============
    function switchSource(source) {
        // Update tab styles
        document.getElementById('tabManual').className = 'py-3 px-1 border-b-2 ' + (source === 'manual' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';
        document.getElementById('tabApplication').className = 'py-3 px-1 border-b-2 ' + (source === 'application' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';
        document.getElementById('tabImport').className = 'py-3 px-1 border-b-2 ' + (source === 'import' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';

        // Show/hide forms
        document.getElementById('manualForm').className = source === 'manual' ? 'block' : 'hidden';
        document.getElementById('applicationForm').className = source === 'application' ? 'block' : 'hidden';
        document.getElementById('importForm').className = source === 'import' ? 'block' : 'hidden';
    }

    // ============ FILE UPLOAD HANDLERS ============
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

    // ============ APPLICATION DATA LOADING ============
    function loadApplicationData() {
        const select = document.getElementById('application_select');
        const preview = document.getElementById('applicationDataPreview');
        const additionalFields = document.getElementById('additionalFields');
        const importBtn = document.getElementById('importApplicationBtn');
        const hiddenInput = document.getElementById('application_id_hidden');

        if (select.value) {
            const option = select.options[select.selectedIndex];
            const data = JSON.parse(option.dataset.application);

            // Set hidden input value
            hiddenInput.value = select.value;

            // Show preview and additional fields
            preview.classList.remove('hidden');
            additionalFields.classList.remove('hidden');

            // Generate summary HTML
            let summary = '';
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Application #</p>
                            <p class="text-sm font-medium">${data.application_number}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Full Name</p>
                            <p class="text-sm font-medium">${data.first_name} ${data.middle_name ? data.middle_name + ' ' : ''}${data.last_name}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Course</p>
                            <p class="text-sm font-medium">${data.course_name}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Campus</p>
                            <p class="text-sm font-medium">${data.campus_name}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium">${data.email || 'N/A'}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm font-medium">${data.phone || 'N/A'}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">ID Number</p>
                            <p class="text-sm font-medium">${data.id_number || 'N/A'}</p>
                        </div>`;
            summary += `<div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500">Date of Birth</p>
                            <p class="text-sm font-medium">${data.date_of_birth || 'N/A'}</p>
                        </div>`;

            document.getElementById('applicationSummary').innerHTML = summary;

            // Enable import button
            importBtn.disabled = false;
            importBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            preview.classList.add('hidden');
            additionalFields.classList.add('hidden');
            importBtn.disabled = true;
            importBtn.classList.add('opacity-50', 'cursor-not-allowed');
            hiddenInput.value = '';
        }
    }

    // ============ FORM VALIDATION ============
    document.querySelector('form[action*="store"]')?.addEventListener('submit', function(e) {
        // Only validate manual form
        if (document.getElementById('manualForm').classList.contains('block')) {
            const campus = this.querySelector('[name="campus_id"]');
            const firstName = this.querySelector('[name="first_name"]');
            const lastName = this.querySelector('[name="last_name"]');
            const category = this.querySelector('[name="student_category"]');

            if (!campus.value) {
                e.preventDefault();
                alert('Please select a campus');
                campus.focus();
                return false;
            }

            if (!firstName.value) {
                e.preventDefault();
                alert('Please enter first name');
                firstName.focus();
                return false;
            }

            if (!lastName.value) {
                e.preventDefault();
                alert('Please enter last name');
                lastName.focus();
                return false;
            }

            if (!category.value) {
                e.preventDefault();
                alert('Please select student category');
                category.focus();
                return false;
            }
        }
    });

    // Initialize with manual form visible
    document.addEventListener('DOMContentLoaded', function() {
        switchSource('manual');
    });
</script>
@endsection
