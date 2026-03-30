@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Import Students')
@section('subtitle', 'Bulk import students from Excel/CSV file')

@section('breadcrumb')
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
            <a href="{{ route('admin.students.index') }}" class="hover:text-gray-700">Students</a>
        </span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Import</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.students.download-template') }}"
       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2"
       id="downloadTemplateBtn">
        <i class="fas fa-download"></i>
        <span>Download Template</span>
    </a>
    <a href="{{ route('admin.students.export') }}?format=xlsx"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-file-export"></i>
        <span>Export</span>
    </a>
    <a href="{{ route('admin.students.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Manually</span>
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Import Form Card -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-upload text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Import Students</h3>
                        <p class="text-sm text-gray-600">Upload your Excel or CSV file to bulk import students</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Import Statistics -->
                @if(session('import_stats'))
                <div class="mb-6 p-4 rounded-lg {{ session('import_stats.errors') > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-green-50 border border-green-200' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if(session('import_stats.errors') > 0)
                            <i class="fas fa-exclamation-circle text-amber-600 text-xl"></i>
                            @else
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-medium {{ session('import_stats.errors') > 0 ? 'text-amber-800' : 'text-green-800' }}">
                                Import Completed
                            </h4>
                            <div class="mt-2 text-sm {{ session('import_stats.errors') > 0 ? 'text-amber-700' : 'text-green-700' }}">
                                <p><span class="font-bold">{{ session('import_stats.imported') }}</span> students imported successfully</p>
                                <p><span class="font-bold">{{ session('import_stats.errors') }}</span> errors encountered</p>
                                <p><span class="font-bold">{{ session('import_stats.warnings') }}</span> warnings</p>
                                <p class="text-xs text-gray-500 mt-1">Batch: {{ session('import_stats.batch') }}</p>
                            </div>
                            @if(session('import_stats.error_messages') && count(session('import_stats.error_messages')) > 0)
                            <div class="mt-3">
                                <button onclick="showErrorDetails()" class="text-sm text-amber-600 hover:text-amber-800 underline">
                                    View Error Details
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Error Details Modal -->
                <div id="errorDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" onclick="closeErrorModal()">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                            <div class="bg-white px-6 pt-5 pb-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Import Error Details</h3>
                                    <button onclick="closeErrorModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error Message</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach(session('import_stats.error_messages', []) as $error)
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-red-600">{{ $error }}</td>
                                            </tr>
                                            @endforeach
                                            @if(session('import_stats.warning_messages') && count(session('import_stats.warning_messages')) > 0)
                                            <tr><td colspan="1" class="px-6 py-2 bg-gray-50 font-medium text-gray-700">Warnings:</td></tr>
                                            @foreach(session('import_stats.warning_messages', []) as $warning)
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-amber-600">{{ $warning }}</td>
                                            </tr>
                                            @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                                <button onclick="closeErrorModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Import Form -->
                <form action="{{ route('admin.students.import.process') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="importForm"
                      class="space-y-6">
                    @csrf

                    <!-- File Upload Area -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary transition-colors"
                         id="dropZone">
                        <input type="file"
                               name="file"
                               id="fileInput"
                               accept=".xlsx,.xls,.csv"
                               class="hidden"
                               onchange="handleFileSelect(this)">

                        <div class="space-y-4" id="uploadPrompt">
                            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-700 font-medium">Drag and drop your file here or</p>
                                <button type="button"
                                        onclick="document.getElementById('fileInput').click()"
                                        class="text-primary hover:text-primary-dark font-medium">
                                    browse to upload
                                </button>
                            </div>
                            <p class="text-sm text-gray-500">Supported formats: Excel (.xlsx, .xls) or CSV (.csv) (Max 10MB)</p>
                        </div>

                        <!-- File Preview -->
                        <div id="filePreview" class="hidden">
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-file-excel text-green-600 text-xl"></i>
                                    </div>
                                    <div class="text-left">
                                        <p id="fileName" class="font-medium text-gray-900"></p>
                                        <p id="fileSize" class="text-sm text-gray-500"></p>
                                    </div>
                                </div>
                                <button type="button"
                                        onclick="clearFileSelection()"
                                        class="text-gray-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    @error('file')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror

                    <!-- Import Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Batch Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Batch Name (Optional)
                            </label>
                            <input type="text"
                                   name="batch_name"
                                   value="{{ old('batch_name', 'IMPORT_' . now()->format('Ymd_His')) }}"
                                   placeholder="e.g., January 2024 Intake"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Give this import a name for future reference</p>
                        </div>

                        <!-- Campus Selection -->
                        @if(auth()->user()->role == 2)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Campus <span class="text-red-500">*</span>
                            </label>
                            <select name="campus_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required>
                                <option value="">Select Campus</option>
                                @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('campus_id')
                            <p class="mt-1 text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                        @endif
                    </div>

                    <!-- Import Options Checkboxes -->
                    <div class="space-y-3 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-2">Import Options</p>

                        <label class="flex items-center space-x-3">
                            <input type="checkbox"
                                   name="update_existing"
                                   value="1"
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-gray-700">
                                Update existing students (matched by ID number, student number, or legacy code)
                            </span>
                        </label>

                        <label class="flex items-center space-x-3">
                            <input type="checkbox"
                                   name="skip_duplicates"
                                   value="1"
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-gray-700">
                                Skip duplicate records
                            </span>
                        </label>

                        <label class="flex items-center space-x-3">
                            <input type="checkbox"
                                   name="mark_requires_cleanup"
                                   value="1"
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-gray-700">
                                Mark records as "Needs Cleanup" for review (useful for incomplete data)
                            </span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.students.index') }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                id="submitBtn"
                                class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload"></i>
                            <span>Import Students</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-6">
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Import Instructions
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <!-- File Format -->
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">File Format</h4>
                    <p class="text-sm text-gray-600">Use the template file to ensure correct formatting. The import recognizes various column headers:</p>
                </div>

                <!-- Column Mapping -->
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Common Column Headers</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">first_name</span>
                            <span class="text-gray-500 block">First Name, FName</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">last_name</span>
                            <span class="text-gray-500 block">Last Name, LName</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">email</span>
                            <span class="text-gray-500 block">Email Address</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">phone</span>
                            <span class="text-gray-500 block">Phone, Mobile</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">id_number</span>
                            <span class="text-gray-500 block">ID Number, National ID</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">gender</span>
                            <span class="text-gray-500 block">Gender (M/F/Male/Female)</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">date_of_birth</span>
                            <span class="text-gray-500 block">DOB, Birth Date</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="font-medium">student_category</span>
                            <span class="text-gray-500 block">Category, Student Type</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Download template for complete list of supported columns</p>
                </div>

                <!-- Data Validation -->
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Validation Rules</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Gender: male, female, other, M, F</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Status: active, inactive, graduated, etc.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">ID Type: id, birth_certificate, passport</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Dates: YYYY-MM-DD or Excel date format</span>
                        </li>
                    </ul>
                </div>

                <!-- Features -->
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Features</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Intelligent column mapping - recognizes multiple header names</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Updates existing students if matched</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Processes 100 rows at a time for large files</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-2 text-xs"></i>
                            <span class="text-xs">Auto-generates student numbers</span>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-800 mb-2">Need Help?</h4>
                    <p class="text-sm text-blue-600 mb-3">Contact support for assistance with bulk imports</p>
                    <div class="space-y-1 text-sm">
                        <p><i class="fas fa-envelope text-blue-600 w-5"></i> support@ktvtc.ac.ke</p>
                        <p><i class="fas fa-phone text-blue-600 w-5"></i> +254 700 000 000</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-primary/10 mb-4">
                        <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Importing Students</h3>
                    <p class="text-sm text-gray-600 mb-4">Please wait while we process your file...</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div id="progressBar" class="bg-primary h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="progressText" class="text-xs text-gray-500">Processing in batches of 100 rows...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
});

function initializeDragAndDrop() {
    const dropZone = document.getElementById('dropZone');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropZone.classList.add('border-primary', 'bg-primary/5');
    }

    function unhighlight() {
        dropZone.classList.remove('border-primary', 'bg-primary/5');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length) {
            document.getElementById('fileInput').files = files;
            handleFileSelect(document.getElementById('fileInput'));
        }
    }
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showToast('File size must be less than 10MB', 'error');
            clearFileSelection();
            return;
        }

        // Validate file type
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ];

        if (!validTypes.includes(file.type) &&
            !file.name.match(/\.(xlsx|xls|csv)$/i)) {
            showToast('Please upload a valid Excel or CSV file', 'error');
            clearFileSelection();
            return;
        }

        // Show file preview
        document.getElementById('uploadPrompt').classList.add('hidden');
        document.getElementById('filePreview').classList.remove('hidden');
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
    }
}

function clearFileSelection() {
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadPrompt').classList.remove('hidden');
    document.getElementById('filePreview').classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showErrorDetails() {
    document.getElementById('errorDetailsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeErrorModal() {
    document.getElementById('errorDetailsModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function showToast(message, type = 'success') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 ${
        type === 'success' ? 'bg-green-50 border border-green-200' :
        type === 'error' ? 'bg-red-50 border border-red-200' :
        'bg-blue-50 border border-blue-200'
    }`;

    const icon = type === 'success' ? 'fa-check-circle text-green-600' :
                 type === 'error' ? 'fa-exclamation-circle text-red-600' :
                 'fa-info-circle text-blue-600';

    toast.innerHTML = `
        <div class="flex items-start">
            <i class="fas ${icon} mt-0.5 mr-3"></i>
            <div>
                <p class="text-sm font-medium ${
                    type === 'success' ? 'text-green-800' :
                    type === 'error' ? 'text-red-800' :
                    'text-blue-800'
                }">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
        if (toastContainer.children.length === 0) {
            toastContainer.remove();
        }
    }, 5000);
}

// Handle form submission
document.getElementById('importForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files.length) {
        e.preventDefault();
        showToast('Please select a file to import', 'error');
        return;
    }

    // Show progress modal
    document.getElementById('progressModal').classList.remove('hidden');

    // Disable submit button
    document.getElementById('submitBtn').disabled = true;
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeErrorModal();
        document.getElementById('progressModal').classList.add('hidden');
    }
});

// Progress simulation
let progress = 0;
const progressInterval = setInterval(function() {
    if (document.getElementById('progressModal').classList.contains('hidden')) {
        clearInterval(progressInterval);
        progress = 0;
        return;
    }

    progress += Math.random() * 5;
    if (progress > 90) {
        progress = 90;
    }

    document.getElementById('progressBar').style.width = progress + '%';
}, 500);
</script>

<style>
#dropZone {
    transition: all 0.3s ease;
}

#dropZone.dragover {
    border-color: #3B82F6;
    background-color: rgba(59, 130, 246, 0.05);
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar,
.overflow-y-auto::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track,
.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb,
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover,
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Modal animations */
.fixed {
    transition: opacity 0.3s ease;
}

.modal-content {
    max-height: 90vh;
    overflow-y: auto;
}

/* Sticky header */
.sticky {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
@endsection
