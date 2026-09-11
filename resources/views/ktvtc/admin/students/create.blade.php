@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Add New Student')
@section('subtitle', 'Create a new student record manually or from an accepted application')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Add New Student</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Students</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- ═══════════ FORM HEADER ═══════════ --}}
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

        {{-- Source tabs --}}
        <div class="px-6 border-t border-gray-200 bg-gray-50">
            <div class="flex space-x-4">
                <button type="button" onclick="switchSource('manual')" id="tabManual"
                        class="py-3 px-1 border-b-2 border-primary text-primary font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-user-edit"></i><span>Manual Entry</span>
                </button>
                <button type="button" onclick="switchSource('application')" id="tabApplication"
                        class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-file-alt"></i><span>From Application</span>
                </button>
                <button type="button" onclick="switchSource('import')" id="tabImport"
                        class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center space-x-2">
                    <i class="fas fa-upload"></i><span>Bulk Import</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════ MANUAL FORM ═══════════ --}}
    <div id="manualForm" class="block">
        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="registration_type" value="manual_entry">
            <input type="hidden" name="student_number" value="{{ $nextStudentNumber }}">

            <div class="p-6 space-y-8">

                {{-- INSTITUTION --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-university text-primary mr-2"></i> Institution Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            @error('campus_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Student Number</label>
                            <input type="text" id="display_student_number" value="{{ $nextStudentNumber }}" readonly
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Auto-generated starting from <strong>947</strong>.
                                Formatted as <strong>COURSECODE/NUMBER/YEAR</strong> upon enrollment.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PERSONAL --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-primary mr-2"></i> Personal Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <select name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['Mr.','Ms.','Mrs.','Dr.','Prof.'] as $t)
                                    <option value="{{ $t }}" {{ old('title') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('first_name') border-red-500 @enderror">
                            @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('last_name') border-red-500 @enderror">
                            @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="student@example.com"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('email') border-red-500 @enderror">
                            </div>
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="07XX XXX XXX"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('phone') border-red-500 @enderror">
                            </div>
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                            <select name="id_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="id" {{ old('id_type') == 'id' ? 'selected' : '' }}>National ID</option>
                                <option value="birth_certificate" {{ old('id_type') == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                                <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID/Passport Number</label>
                            <input type="text" name="id_number" value="{{ old('id_number') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                            <select name="marital_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['single','married','divorced','widowed'] as $m)
                                    <option value="{{ $m }}" {{ old('marital_status') == $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- CONTACT --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-primary mr-2"></i> Contact Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City/Town</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                            <input type="text" name="county" value="{{ old('county') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input type="text" name="country" value="{{ old('country', 'Kenya') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                {{-- NEXT OF KIN --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-users text-primary mr-2"></i> Next of Kin
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                            <select name="next_of_kin_relationship" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['parent','guardian','spouse','sibling','other'] as $r)
                                    <option value="{{ $r }}" {{ old('next_of_kin_relationship') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="next_of_kin_phone" value="{{ old('next_of_kin_phone') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="next_of_kin_email" value="{{ old('next_of_kin_email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                            <input type="text" name="next_of_kin_id_number" value="{{ old('next_of_kin_id_number') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="next_of_kin_address" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('next_of_kin_address') }}</textarea>
                    </div>
                </div>

                {{-- EMERGENCY --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-ambulance text-primary mr-2"></i> Emergency Contact
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}"
                                   placeholder="e.g., Parent, Spouse"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Phone</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alternative Phone</label>
                            <input type="text" name="emergency_contact_phone_alt" value="{{ old('emergency_contact_phone_alt') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                {{-- EDUCATION --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-graduation-cap text-primary mr-2"></i> Education Background
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Highest Education Level</label>
                            <select name="education_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['kcse','certificate','diploma','degree','masters','phd'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('education_level') == $lvl ? 'selected' : '' }}>{{ strtoupper($lvl) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">School/Institution Name</label>
                            <input type="text" name="school_name" value="{{ old('school_name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year</label>
                            <input type="number" name="graduation_year" value="{{ old('graduation_year') }}"
                                   min="1950" max="{{ date('Y') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mean Grade</label>
                            <select name="mean_grade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['A','A-','B+','B','B-','C+','C','C-','D+','D','D-','E'] as $g)
                                    <option value="{{ $g }}" {{ old('mean_grade') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">KCSE Index Number</label>
                            <input type="text" name="kcse_index_number" value="{{ old('kcse_index_number') }}"
                                   placeholder="e.g., 12345678901"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                {{-- MEDICAL --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-heartbeat text-primary mr-2"></i> Medical & Special Needs
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group</label>
                            <select name="blood_group" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b)
                                    <option value="{{ $b }}" {{ old('blood_group') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">T-Shirt Size</label>
                            <select name="tshirt_size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select</option>
                                @foreach(['XS','S','M','L','XL','XXL','XXXL'] as $s)
                                    <option value="{{ $s }}" {{ old('tshirt_size') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Disability Type</label>
                            <input type="text" name="disability_type" value="{{ old('disability_type') }}"
                                   placeholder="e.g., Physical, Visual"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medical Conditions</label>
                            <textarea name="medical_conditions" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('medical_conditions') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea name="allergies" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('allergies') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Special Needs/Requirements</label>
                        <textarea name="special_needs" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('special_needs') }}</textarea>
                    </div>
                </div>

                {{-- ═══════════ DOCUMENTS (NEW) ═══════════ --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-primary mr-2"></i> Documents
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- ID Document --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Document</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                <input type="file" name="id_document" id="id_document"
                                       accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                <div onclick="document.getElementById('id_document').click()" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                                    <p class="text-xs text-gray-500">Click to upload</p>
                                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG (Max 2MB)</p>
                                </div>
                                <div id="id_document_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                            </div>
                        </div>

                        {{-- Passport Photo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Passport Photo</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                <input type="file" name="passport_photo" id="passport_photo"
                                       accept=".jpg,.jpeg,.png" class="hidden">
                                <div onclick="document.getElementById('passport_photo').click()" class="cursor-pointer">
                                    <i class="fas fa-camera text-2xl text-gray-400 mb-2"></i>
                                    <p class="text-xs text-gray-500">Click to upload</p>
                                    <p class="text-xs text-gray-400 mt-1">JPG, PNG (Max 2MB)</p>
                                </div>
                                <div id="passport_photo_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                            </div>
                        </div>

                        {{-- Education Certificates --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Education Certificates</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                <input type="file" name="education_certificates" id="education_certificates"
                                       accept=".pdf" class="hidden">
                                <div onclick="document.getElementById('education_certificates').click()" class="cursor-pointer">
                                    <i class="fas fa-file-pdf text-2xl text-gray-400 mb-2"></i>
                                    <p class="text-xs text-gray-500">Click to upload</p>
                                    <p class="text-xs text-gray-400 mt-1">PDF (Max 5MB)</p>
                                </div>
                                <div id="education_certificates_name" class="mt-2 text-xs text-gray-600 hidden"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Other Documents --}}
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Other Documents</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                            <input type="file" name="other_documents[]" id="other_documents"
                                   multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
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

                {{-- ADDITIONAL --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tag text-primary mr-2"></i> Additional Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Student Category <span class="text-red-500">*</span></label>
                            <select name="student_category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                @foreach(['regular','alumnus','staff_child','sponsored','scholarship'] as $c)
                                    <option value="{{ $c }}" {{ old('student_category') == $c ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_',' ',$c)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                @foreach(['active','inactive','graduated','suspended','alumnus','prospective','historical'] as $st)
                                    <option value="{{ $st }}" {{ old('status','active') == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                            <input type="date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Legacy Student Code</label>
                            <input type="text" name="legacy_student_code" value="{{ old('legacy_student_code') }}"
                                   placeholder="e.g., SHEP/261/2022"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks/Notes</label>
                        <textarea name="remarks" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.students.index') }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">Cancel</a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> Create Student
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════ APPLICATION FORM PLACEHOLDER ═══════════ --}}
    <div id="applicationForm" class="hidden">
        <div class="p-12 text-center">
            <i class="fas fa-file-alt text-5xl text-gray-300 mb-4"></i>
            <p class="text-lg font-semibold text-gray-700">Create from Application</p>
            <p class="text-sm text-gray-500 mt-1">This feature will let you pick an accepted application and pre-fill the form.</p>
        </div>
    </div>

    {{-- ═══════════ IMPORT PLACEHOLDER ═══════════ --}}
    <div id="importForm" class="hidden">
        <div class="p-12 text-center">
            <i class="fas fa-upload text-5xl text-gray-300 mb-4"></i>
            <p class="text-lg font-semibold text-gray-700">Bulk Import Students</p>
            <p class="text-sm text-gray-500 mt-1">Go to the <a href="{{ route('admin.students.import.view') }}" class="text-primary hover:underline">import page</a> to upload a spreadsheet.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ── Source tab switcher ──
function switchSource(source) {
    const tabs = {
        manual:      document.getElementById('tabManual'),
        application: document.getElementById('tabApplication'),
        import:      document.getElementById('tabImport'),
    };
    const base = 'py-3 px-1 border-b-2 font-medium text-sm flex items-center space-x-2';
    Object.keys(tabs).forEach(k => {
        if (!tabs[k]) return;
        tabs[k].className = base + (k === source
            ? ' border-primary text-primary'
            : ' border-transparent text-gray-500 hover:text-gray-700');
    });

    const forms = {
        manual:      document.getElementById('manualForm'),
        application: document.getElementById('applicationForm'),
        import:      document.getElementById('importForm'),
    };
    Object.keys(forms).forEach(k => {
        if (!forms[k]) return;
        forms[k].className = k === source ? 'block' : 'hidden';
    });
}

// ── File previews ──
document.addEventListener('DOMContentLoaded', function () {
    function bindPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(previewId);
        if (!input || !box) return;
        input.addEventListener('change', function () {
            if (this.files.length > 0) {
                const f = this.files[0];
                const size = (f.size / 1024).toFixed(2);
                const sizeText = size > 1024 ? (size / 1024).toFixed(2) + ' MB' : size + ' KB';
                box.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>${f.name} (${sizeText})</span>`;
                box.classList.remove('hidden');
            }
        });
    }

    bindPreview('id_document', 'id_document_name');
    bindPreview('passport_photo', 'passport_photo_name');
    bindPreview('education_certificates', 'education_certificates_name');

    const other = document.getElementById('other_documents');
    const otherList = document.getElementById('other_documents_list');
    if (other && otherList) {
        other.addEventListener('change', function () {
            otherList.innerHTML = '';
            if (this.files.length > 0) {
                for (const f of this.files) {
                    const size = (f.size / 1024).toFixed(2);
                    const sizeText = size > 1024 ? (size / 1024).toFixed(2) + ' MB' : size + ' KB';
                    otherList.innerHTML += `<div class="text-sm text-gray-600 flex items-center">
                        <i class="fas fa-file text-blue-500 mr-2"></i>${f.name} (${sizeText})
                    </div>`;
                }
                otherList.classList.remove('hidden');
            } else {
                otherList.classList.add('hidden');
            }
        });
    }

    switchSource('manual');
});
</script>
@endsection