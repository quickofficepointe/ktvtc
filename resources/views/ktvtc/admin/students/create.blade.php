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
        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="registration_type" value="manual_entry">
            <!-- Hidden field to pass the student number from the view -->
            <input type="hidden" name="student_number" value="{{ $nextStudentNumber }}">

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

                        <!-- Student Number - READONLY (Auto-generated) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Student Number
                            </label>
                            <input type="text"
                                   id="display_student_number"
                                   value="{{ $nextStudentNumber }}"
                                   readonly
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed @error('student_number') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Auto-generated starting from <strong>947</strong>.
                                Will be formatted as <strong>COURSECODE/NUMBER/YEAR</strong> upon enrollment.
                            </p>
                            @error('student_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
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

                <!-- Continue with the rest of the form... -->
                <!-- (Keep all the other form sections the same) -->

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

                <!-- [Rest of the form sections remain the same - Next of Kin, Emergency Contact, Education Background, Medical & Special Needs, Documents, Additional Information] -->

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.students.index') }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Create Student
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ============ FROM APPLICATION FORM ============ -->
    <div id="applicationForm" class="hidden">
        <!-- Keep the application form section the same -->
    </div>

    <!-- ============ BULK IMPORT REDIRECT ============ -->
    <div id="importForm" class="hidden">
        <!-- Keep the import form section the same -->
    </div>
</div>

<!-- Inline JavaScript -->
<script>
    // ============ TAB SWITCHING ============
    function switchSource(source) {
        console.log('switchSource called with:', source);
        
        // Update tab styles
        var tabManual = document.getElementById('tabManual');
        var tabApplication = document.getElementById('tabApplication');
        var tabImport = document.getElementById('tabImport');
        
        if (tabManual) {
            tabManual.className = 'py-3 px-1 border-b-2 ' + (source === 'manual' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';
        }
        if (tabApplication) {
            tabApplication.className = 'py-3 px-1 border-b-2 ' + (source === 'application' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';
        }
        if (tabImport) {
            tabImport.className = 'py-3 px-1 border-b-2 ' + (source === 'import' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700') + ' font-medium text-sm flex items-center space-x-2';
        }

        // Show/hide forms
        var manualForm = document.getElementById('manualForm');
        var applicationForm = document.getElementById('applicationForm');
        var importForm = document.getElementById('importForm');
        
        if (manualForm) manualForm.className = source === 'manual' ? 'block' : 'hidden';
        if (applicationForm) applicationForm.className = source === 'application' ? 'block' : 'hidden';
        if (importForm) importForm.className = source === 'import' ? 'block' : 'hidden';
    }

    // ============ APPLICATION DATA LOADING ============
    function loadApplicationData() {
        // Keep existing loadApplicationData function
        var select = document.getElementById('application_select');
        var preview = document.getElementById('applicationDataPreview');
        var additionalFields = document.getElementById('additionalFields');
        var importBtn = document.getElementById('importApplicationBtn');
        var hiddenInput = document.getElementById('application_id_hidden');

        if (select && select.value) {
            var option = select.options[select.selectedIndex];
            var data = JSON.parse(option.dataset.application);

            if (hiddenInput) hiddenInput.value = select.value;
            if (preview) preview.classList.remove('hidden');
            if (additionalFields) additionalFields.classList.remove('hidden');

            var summary = '';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Application #</p>' +
                            '<p class="text-sm font-medium">' + data.application_number + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Full Name</p>' +
                            '<p class="text-sm font-medium">' + data.first_name + ' ' + (data.middle_name ? data.middle_name + ' ' : '') + data.last_name + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Course</p>' +
                            '<p class="text-sm font-medium">' + data.course_name + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Campus</p>' +
                            '<p class="text-sm font-medium">' + data.campus_name + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Email</p>' +
                            '<p class="text-sm font-medium">' + (data.email || 'N/A') + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Phone</p>' +
                            '<p class="text-sm font-medium">' + (data.phone || 'N/A') + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">ID Number</p>' +
                            '<p class="text-sm font-medium">' + (data.id_number || 'N/A') + '</p>' +
                        '</div>';
            summary += '<div class="bg-white p-3 rounded-lg border border-gray-200">' +
                            '<p class="text-xs text-gray-500">Date of Birth</p>' +
                            '<p class="text-sm font-medium">' + (data.date_of_birth || 'N/A') + '</p>' +
                        '</div>';

            var summaryContainer = document.getElementById('applicationSummary');
            if (summaryContainer) summaryContainer.innerHTML = summary;

            if (importBtn) {
                importBtn.disabled = false;
                importBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } else {
            if (preview) preview.classList.add('hidden');
            if (additionalFields) additionalFields.classList.add('hidden');
            if (importBtn) {
                importBtn.disabled = true;
                importBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (hiddenInput) hiddenInput.value = '';
        }
    }

    // ============ FILE UPLOAD HANDLERS ============
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - initializing form handlers');
        
        // [Keep all the file upload handlers the same]
        
        // ID Document
        var idDoc = document.getElementById('id_document');
        if (idDoc) {
            idDoc.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    var fileName = this.files[0].name;
                    var fileSize = (this.files[0].size / 1024).toFixed(2);
                    var sizeText = fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB';
                    var div = document.getElementById('id_document_name');
                    if (div) {
                        div.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>' + fileName + ' (' + sizeText + ')</span>';
                        div.classList.remove('hidden');
                    }
                }
            });
        }

        // Passport Photo
        var passportPhoto = document.getElementById('passport_photo');
        if (passportPhoto) {
            passportPhoto.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    var fileName = this.files[0].name;
                    var fileSize = (this.files[0].size / 1024).toFixed(2);
                    var sizeText = fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB';
                    var div = document.getElementById('passport_photo_name');
                    if (div) {
                        div.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>' + fileName + ' (' + sizeText + ')</span>';
                        div.classList.remove('hidden');
                    }
                }
            });
        }

        // Education Certificates
        var eduCerts = document.getElementById('education_certificates');
        if (eduCerts) {
            eduCerts.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    var fileName = this.files[0].name;
                    var fileSize = (this.files[0].size / 1024).toFixed(2);
                    var sizeText = fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB';
                    var div = document.getElementById('education_certificates_name');
                    if (div) {
                        div.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>' + fileName + ' (' + sizeText + ')</span>';
                        div.classList.remove('hidden');
                    }
                }
            });
        }

        // Other Documents
        var otherDocs = document.getElementById('other_documents');
        if (otherDocs) {
            otherDocs.addEventListener('change', function(e) {
                var list = document.getElementById('other_documents_list');
                if (list) {
                    list.innerHTML = '';
                    if (this.files.length > 0) {
                        for (var i = 0; i < this.files.length; i++) {
                            var file = this.files[i];
                            var fileSize = (file.size / 1024).toFixed(2);
                            var sizeText = fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB';
                            list.innerHTML += '<div class="text-sm text-gray-600 flex items-center">' +
                                '<i class="fas fa-file text-blue-500 mr-2"></i>' +
                                file.name + ' (' + sizeText + ')' +
                            '</div>';
                        }
                        list.classList.remove('hidden');
                    } else {
                        list.classList.add('hidden');
                    }
                }
            });
        }

        // ============ FORM VALIDATION ============
        var forms = document.querySelectorAll('form[action*="store"]');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var manualForm = document.getElementById('manualForm');
                if (manualForm && manualForm.classList.contains('block')) {
                    var campus = this.querySelector('[name="campus_id"]');
                    var firstName = this.querySelector('[name="first_name"]');
                    var lastName = this.querySelector('[name="last_name"]');
                    var category = this.querySelector('[name="student_category"]');

                    if (!campus || !campus.value) {
                        e.preventDefault();
                        alert('Please select a campus');
                        if (campus) campus.focus();
                        return false;
                    }

                    if (!firstName || !firstName.value) {
                        e.preventDefault();
                        alert('Please enter first name');
                        if (firstName) firstName.focus();
                        return false;
                    }

                    if (!lastName || !lastName.value) {
                        e.preventDefault();
                        alert('Please enter last name');
                        if (lastName) lastName.focus();
                        return false;
                    }

                    if (!category || !category.value) {
                        e.preventDefault();
                        alert('Please select student category');
                        if (category) category.focus();
                        return false;
                    }
                }
            });
        });

        // Initialize with manual form visible
        switchSource('manual');
    });
</script>
@endsection