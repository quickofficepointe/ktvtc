@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Import Students')
@section('subtitle', 'Bulk import students from Excel or CSV file')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Import Students</span>
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
    <a href="{{ route('admin.tvet.students.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Manually</span>
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Import Panel -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Import Students from File</h3>
                        <p class="text-sm text-gray-600 mt-1">Upload Excel (.xlsx, .xls) or CSV file containing student records</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Max: 10MB
                        </span>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mx-6 mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        @if(session('import_stats'))
                            @php $stats = session('import_stats'); @endphp
                            <div class="mt-2 text-sm text-green-700">
                                <div class="flex items-center space-x-4">
                                    <span><span class="font-bold">{{ $stats['imported'] }}</span> records imported</span>
                                    @if($stats['warnings'] > 0)
                                        <span class="text-amber-600"><span class="font-bold">{{ $stats['warnings'] }}</span> warnings</span>
                                    @endif
                                    @if($stats['errors'] > 0)
                                        <span class="text-red-600"><span class="font-bold">{{ $stats['errors'] }}</span> errors</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-gray-600">Batch: {{ $stats['batch'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">Import failed with the following errors:</p>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Import Form -->
            <div class="p-6">
                <form action="{{ route('admin.tvet.students.import.process') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="importForm"
                      class="space-y-6">
                    @csrf

                    <!-- File Upload Area -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select File <span class="text-red-500">*</span>
                        </label>
                        <div id="dropZone"
                             class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary hover:bg-blue-50/20 transition-all duration-200 cursor-pointer">
                            <input type="file"
                                   name="file"
                                   id="fileInput"
                                   accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   onchange="handleFileSelect(this)">

                            <div id="uploadPrompt" class="space-y-3">
                                <div class="flex justify-center">
                                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-gray-700 font-medium">
                                        <span class="text-primary">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Supported formats: XLSX, XLS, CSV (Max size: 10MB)
                                    </p>
                                </div>
                            </div>

                            <div id="fileInfo" class="hidden">
                                <div class="flex items-center justify-between bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i id="fileIcon" class="fas fa-file-excel text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="text-left">
                                            <p id="fileName" class="text-sm font-medium text-gray-800"></p>
                                            <p id="fileSize" class="text-xs text-gray-500"></p>
                                        </div>
                                    </div>
                                    <button type="button"
                                            onclick="removeFile()"
                                            class="p-1 hover:bg-blue-200 rounded-full transition-colors">
                                        <i class="fas fa-times text-blue-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Import Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Batch Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Batch Name <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <input type="text"
                                       name="batch_name"
                                       id="batch_name"
                                       value="{{ old('batch_name', 'IMPORT_' . date('Ymd_His')) }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                       placeholder="e.g., January 2024 Intake">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Give this import a unique name for tracking</p>
                        </div>

                        <!-- Campus Assignment (Admin Only) -->
                        @if(auth()->user()->role == 2)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Assign to Campus <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-university text-gray-400"></i>
                                </div>
                                <select name="campus_id"
                                        id="campus_id"
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm appearance-none">
                                    <option value="">Default Campus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                            {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Leave empty to use your default campus</p>
                        </div>
                        @else
                            <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                        @endif
                    </div>

                    <!-- Import Settings -->
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h4 class="text-sm font-medium text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-cog text-primary mr-2"></i>
                            Import Settings
                        </h4>
                        <div class="space-y-3">
                            <label class="flex items-center space-x-3">
                                <input type="checkbox"
                                       name="update_existing"
                                       value="1"
                                       {{ old('update_existing') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="text-sm text-gray-700">
                                    Update existing records <span class="text-xs text-gray-500">(Match by ID Number or Student Number)</span>
                                </span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox"
                                       name="skip_errors"
                                       value="1"
                                       {{ old('skip_errors') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="text-sm text-gray-700">
                                    Skip rows with errors <span class="text-xs text-gray-500">(Continue importing valid rows)</span>
                                </span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox"
                                       name="generate_student_numbers"
                                       value="1"
                                       {{ old('generate_student_numbers', '1') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="text-sm text-gray-700">
                                    Auto-generate missing student numbers
                                </span>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox"
                                       name="mark_for_cleanup"
                                       value="1"
                                       {{ old('mark_for_cleanup') ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="text-sm text-gray-700">
                                    Mark incomplete records for cleanup
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.tvet.students.index') }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit"
                                id="submitBtn"
                                class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors text-sm font-medium flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload"></i>
                            <span>Import Students</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import History Section -->
        @if(session('import_stats') && isset($stats['error_messages']) && count($stats['error_messages']) > 0)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Import Errors</h3>
                        <p class="text-sm text-gray-600 mt-1">Rows that failed to import</p>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                        {{ count($stats['error_messages']) }} Errors
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-red-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <ul class="space-y-2">
                        @foreach($stats['error_messages'] as $error)
                            <li class="text-sm text-red-700 flex items-start">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-2 flex-shrink-0"></i>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if(session('import_stats') && isset($stats['warning_messages']) && count($stats['warning_messages']) > 0)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Import Warnings</h3>
                        <p class="text-sm text-gray-600 mt-1">Rows with potential issues</p>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">
                        {{ count($stats['warning_messages']) }} Warnings
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-amber-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <ul class="space-y-2">
                        @foreach($stats['warning_messages'] as $warning)
                            <li class="text-sm text-amber-700 flex items-start">
                                <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 mr-2 flex-shrink-0"></i>
                                <span>{{ $warning }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar - Instructions & Template -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Download Template Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-6">
            <div class="px-6 py-4 bg-gradient-to-r from-primary/10 to-transparent border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-download text-primary mr-2"></i>
                    Download Template
                </h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">
                    Download our Excel template with the correct column headers and format.
                </p>
                <div class="space-y-3">
                    <a href="{{ route('admin.tvet.students.export') }}?format=xlsx&template=true"
                       class="w-full px-4 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-file-excel"></i>
                        <span>Excel Template (.xlsx)</span>
                    </a>
                    <a href="{{ route('admin.tvet.students.export') }}?format=csv&template=true"
                       class="w-full px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-file-csv"></i>
                        <span>CSV Template (.csv)</span>
                    </a>
                </div>
                <p class="text-xs text-gray-500 text-center mt-3">
                    Contains all supported columns with example data
                </p>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-transparent border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Import Instructions
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-5">
                    <!-- File Format -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-800 mb-2 flex items-center">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">1</span>
                            File Format
                        </h4>
                        <p class="text-sm text-gray-600 ml-7">
                            Use <span class="font-medium">.xlsx, .xls, or .csv</span> files. The first row must contain column headers.
                        </p>
                    </div>

                    <!-- Required Fields -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-800 mb-2 flex items-center">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">2</span>
                            Recommended Fields
                        </h4>
                        <div class="ml-7">
                            <ul class="text-sm text-gray-600 space-y-1.5">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">First Name</span> - Required for identification</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Last Name</span> - Required for identification</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">ID Number</span> or <span class="font-medium">Student Number</span> - For duplicate checking</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Email/Phone</span> - For contact information</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Supported Columns -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-800 mb-2 flex items-center">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">3</span>
                            Supported Columns
                        </h4>
                        <div class="ml-7">
                            <div class="bg-gray-50 rounded-lg p-3 max-h-64 overflow-y-auto custom-scrollbar">
                                <ul class="text-xs text-gray-600 space-y-1.5">
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        student_number, legacy_code, first_name, last_name, middle_name
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        title, email, phone, id_type, id_number
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        date_of_birth, gender, marital_status
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        address, city, county, postal_code, country
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        next_of_kin_name, next_of_kin_phone, next_of_kin_relationship
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        emergency_contact_name, emergency_contact_phone
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        education_level, school_name, graduation_year, mean_grade
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        medical_conditions, allergies, blood_group, special_needs
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-tag text-primary mr-2 mt-0.5"></i>
                                        tshirt_size, student_category, status, remarks
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Data Validation -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-800 mb-2 flex items-center">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs mr-2">4</span>
                            Data Validation Rules
                        </h4>
                        <div class="ml-7">
                            <ul class="text-xs text-gray-600 space-y-1.5">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Gender:</span> male, female, other (or M, F)</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">ID Type:</span> id, birth_certificate, passport</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Status:</span> active, inactive, graduated, dropped, suspended, alumnus, prospective, historical</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Category:</span> regular, alumnus, staff_child, sponsored, scholarship</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 text-xs mt-1 mr-2"></i>
                                    <span><span class="font-medium">Dates:</span> YYYY-MM-DD format</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Pro Tips
                        </h4>
                        <ul class="text-xs text-gray-600 space-y-1.5 ml-6 list-disc">
                            <li>Maximum 1000 rows per import for optimal performance</li>
                            <li>Remove empty rows and columns to reduce errors</li>
                            <li>Check for duplicate ID numbers before importing</li>
                            <li>Use batch names to track different import sessions</li>
                            <li>Review warnings after import to clean up data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Imports Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-transparent border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-history text-purple-600 mr-2"></i>
                    Recent Imports
                </h3>
            </div>
            <div class="p-6">
                @php
                    $recentBatches = \App\Models\Student::whereNotNull('import_batch')
                        ->select('import_batch', \DB::raw('count(*) as total'))
                        ->groupBy('import_batch')
                        ->orderByRaw('MAX(created_at) DESC')
                        ->limit(5)
                        ->get();
                @endphp

                @if($recentBatches->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentBatches as $batch)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $batch->import_batch }}</p>
                                    <p class="text-xs text-gray-500">{{ $batch->total }} students</p>
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($batch->created_at)->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-500">No recent imports</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // File Upload Handling
    function handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            alert('File size exceeds 10MB limit. Please select a smaller file.');
            input.value = '';
            return;
        }

        // Validate file type
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'text/csv', // .csv
            'application/csv', // .csv
            'text/plain' // .csv
        ];

        const isCsv = file.name.toLowerCase().endsWith('.csv');
        const isExcel = file.name.toLowerCase().endsWith('.xlsx') || file.name.toLowerCase().endsWith('.xls');

        if (!isCsv && !isExcel) {
            alert('Invalid file type. Please upload Excel (.xlsx, .xls) or CSV (.csv) files.');
            input.value = '';
            return;
        }

        // Update UI
        const fileName = file.name;
        const fileSize = (file.size / 1024).toFixed(2);
        const sizeText = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;

        // Set file icon based on type
        const fileIcon = document.getElementById('fileIcon');
        if (fileName.toLowerCase().endsWith('.csv')) {
            fileIcon.className = 'fas fa-file-csv text-blue-600 text-xl';
        } else {
            fileIcon.className = 'fas fa-file-excel text-blue-600 text-xl';
        }

        document.getElementById('fileName').textContent = fileName;
        document.getElementById('fileSize').textContent = sizeText;
        document.getElementById('uploadPrompt').classList.add('hidden');
        document.getElementById('fileInfo').classList.remove('hidden');

        // Auto-generate batch name from filename
        const batchNameInput = document.getElementById('batch_name');
        if (batchNameInput && batchNameInput.value === 'IMPORT_' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '_' +
            new Date().toTimeString().slice(0,2) + new Date().toTimeString().slice(3,5) + new Date().toTimeString().slice(6,8)) {
            const baseName = fileName.replace(/\.[^/.]+$/, "");
            batchNameInput.value = `IMPORT_${baseName}_${new Date().toISOString().slice(0,10).replace(/-/g,'')}`;
        }
    }

    function removeFile() {
        const fileInput = document.getElementById('fileInput');
        fileInput.value = '';
        document.getElementById('uploadPrompt').classList.remove('hidden');
        document.getElementById('fileInfo').classList.add('hidden');

        // Reset batch name to default if it was auto-generated
        const batchNameInput = document.getElementById('batch_name');
        if (batchNameInput && batchNameInput.value.startsWith('IMPORT_')) {
            batchNameInput.value = 'IMPORT_' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '_' +
                new Date().toTimeString().slice(0,2) + new Date().toTimeString().slice(3,5) + new Date().toTimeString().slice(6,8);
        }
    }

    // Drag and Drop
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const importForm = document.getElementById('importForm');
        const submitBtn = document.getElementById('submitBtn');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone on drag over
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-primary', 'bg-blue-50');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-primary', 'bg-blue-50');
            }, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(fileInput);
            }
        }, false);

        // Form submission
        importForm.addEventListener('submit', function(e) {
            if (!fileInput.files.length > 0) {
                e.preventDefault();
                alert('Please select a file to import');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span>Importing... (Please wait)</span>
            `;
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Batch name auto-formatting
    document.getElementById('batch_name')?.addEventListener('input', function(e) {
        // Replace spaces and special characters with underscores
        this.value = this.value.replace(/[^a-zA-Z0-9_\-]/g, '_');
    });

    // File input click on drop zone
    document.getElementById('dropZone')?.addEventListener('click', function(e) {
        if (e.target !== document.getElementById('fileInfo') &&
            !document.getElementById('fileInfo').contains(e.target)) {
            document.getElementById('fileInput').click();
        }
    });

    // Prevent click on file info from triggering file input
    document.getElementById('fileInfo')?.addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    #dropZone {
        transition: all 0.2s ease;
    }

    #dropZone:hover {
        border-color: #2563eb;
        background-color: #eff6ff;
    }

    #dropZone.border-primary {
        border-color: #2563eb;
        background-color: #eff6ff;
    }
</style>
@endsection
