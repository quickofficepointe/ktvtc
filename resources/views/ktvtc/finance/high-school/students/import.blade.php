@extends('ktvtc.finance.layouts.app')

@section('title', 'Import Students')
@section('subtitle', 'Import high school students from Excel/CSV')

@section('header-actions')
<a href="{{ route('finance.hs-students.index') }}" class="text-gray-600 hover:text-gray-800">
    <i class="fas fa-arrow-left mr-2"></i> Back to Students
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Import Card -->
    <div class="finance-card p-5">
        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4 rounded text-sm">
            <h4 class="font-semibold text-blue-800">📋 Import Instructions</h4>
            <ul class="text-blue-700 mt-1 space-y-1 list-disc list-inside text-xs">
                <li>Download the template CSV file below</li>
                <li>Fill in the student details following the template format</li>
                <li><strong>Required fields:</strong> full_name</li>
                <li><strong>Optional fields:</strong> admission_number, gender, parent_name, parent_phone, class</li>
                <li>If admission_number is empty, it will be auto-generated</li>
                <li>If class is not provided, all students will be assigned to the class you specify</li>
            </ul>
        </div>

        <div class="mb-4">
            <a href="{{ route('finance.hs-students.template') }}" class="text-primary hover:underline flex items-center text-sm">
                <i class="fas fa-download mr-2"></i> Download Template CSV
            </a>
        </div>

        <!-- ✅ FIXED: Using direct URL instead of route() -->
        <form method="POST" action="/finance/hs-students/import" enctype="multipart/form-data" id="import-form">
            @csrf

            <div class="space-y-4">
                <!-- Class Name Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="class_name" value="{{ old('class_name') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                           placeholder="e.g., Form 4, Grade 10 Blue, Form 3" required>
                    <p class="text-xs text-gray-500 mt-1">All students will be assigned to this class if not specified in the file</p>
                    @error('class_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition" 
                     id="drop-zone"
                     ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-blue-50')"
                     ondragleave="event.preventDefault(); this.classList.remove('border-primary', 'bg-blue-50')"
                     ondrop="event.preventDefault(); handleDrop(event)">
                    
                    <div id="upload-content">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 block"></i>
                        <p class="text-gray-600 text-sm mb-1">Click to select or drag and drop your CSV/Excel file</p>
                        <p class="text-xs text-gray-500">Supported formats: .xlsx, .xls, .csv</p>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv"
                               class="mt-3 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer"
                               id="file-input" required
                               onchange="handleFileSelect(event)">
                    </div>

                    <!-- File Preview -->
                    <div id="file-preview" class="hidden mt-3">
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-file-excel text-2xl text-green-600 mr-3"></i>
                                <div class="text-left">
                                    <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                                    <p id="file-size" class="text-xs text-gray-500"></p>
                                </div>
                            </div>
                            <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-check-circle mr-1"></i> File ready for import
                        </p>
                    </div>
                </div>

                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('finance.hs-students.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">
                        Cancel
                    </a>
                    <button type="submit" id="submit-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-upload mr-2"></i> Import Students
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Supported Formats Preview -->
    <div class="finance-card p-4 mt-4">
        <h4 class="font-semibold text-gray-800 text-sm mb-3">📊 Supported File Formats</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Format 1: Standard -->
            <div>
                <p class="text-xs font-semibold text-gray-600 mb-1">Format 1: Standard</p>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">admission_number</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">full_name *</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">class</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">gender</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">parent_name</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">parent_phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-1 text-gray-700">HS/2024/00001</td>
                                <td class="px-2 py-1 text-gray-700 font-medium">John Doe</td>
                                <td class="px-2 py-1 text-gray-700">Form 4</td>
                                <td class="px-2 py-1 text-gray-700">male</td>
                                <td class="px-2 py-1 text-gray-700">Jane Doe</td>
                                <td class="px-2 py-1 text-gray-700">0712345678</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Format 2: ADM NO -->
            <div>
                <p class="text-xs font-semibold text-gray-600 mb-1">Format 2: ADM NO (Your files)</p>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">ADM NO</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">NAME *</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">GENDER</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">PARENT</th>
                                <th class="px-2 py-1 text-left font-semibold text-gray-600">PHONE NUMBER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-1 text-gray-700">1237</td>
                                <td class="px-2 py-1 text-gray-700 font-medium">MECHA IBRAHIM</td>
                                <td class="px-2 py-1 text-gray-700">M</td>
                                <td class="px-2 py-1 text-gray-700">MARICELLA KEMUNTO</td>
                                <td class="px-2 py-1 text-gray-700">0721256876</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 p-2 bg-yellow-50 border-l-4 border-yellow-400 rounded">
            <p class="text-xs text-yellow-700">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Note:</strong> The import will auto-detect which format your file uses. 
                For ADM NO format, the class must be specified in the "Class Name" field above.
            </p>
        </div>
    </div>

    <!-- Sample Data Preview -->
    <div class="finance-card p-4 mt-4">
        <h4 class="font-semibold text-gray-800 text-sm mb-3">📝 Sample Data from Your Files</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">File</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">ADM NO</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">NAME</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">GENDER</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">PARENT</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">PHONE</th>
                        <th class="px-2 py-1 text-left font-semibold text-gray-600">Will Import As</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="px-2 py-1"><span class="text-xs px-1 py-0.5 bg-blue-100 text-blue-700 rounded">Grade 10</span></td>
                        <td class="px-2 py-1 font-mono">1237</td>
                        <td class="px-2 py-1 font-medium">MECHA IBRAHIM</td>
                        <td class="px-2 py-1">M → male</td>
                        <td class="px-2 py-1">MARICELLA KEMUNTO</td>
                        <td class="px-2 py-1 font-mono">0721256876</td>
                        <td class="px-2 py-1 text-green-600">✓ Student</td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-2 py-1"><span class="text-xs px-1 py-0.5 bg-purple-100 text-purple-700 rounded">Form 4</span></td>
                        <td class="px-2 py-1 font-mono">933</td>
                        <td class="px-2 py-1 font-medium">Lonok Simon</td>
                        <td class="px-2 py-1">M → male</td>
                        <td class="px-2 py-1">Mr. Simon Magiri</td>
                        <td class="px-2 py-1 font-mono">0728560430</td>
                        <td class="px-2 py-1 text-green-600">✓ Student</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1"><span class="text-xs px-1 py-0.5 bg-green-100 text-green-700 rounded">Form 3</span></td>
                        <td class="px-2 py-1 font-mono">1009</td>
                        <td class="px-2 py-1 font-medium">Muchiri Shawn</td>
                        <td class="px-2 py-1">M → male</td>
                        <td class="px-2 py-1">Mr. MWAI</td>
                        <td class="px-2 py-1 font-mono">0722697707</td>
                        <td class="px-2 py-1 text-green-600">✓ Student</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-arrow-right mr-1"></i> All students will be imported with their class assigned automatically
        </p>
    </div>
</div>

@push('scripts')
<script>
    // File handling
    let selectedFile = null;

    function handleFileSelect(event) {
        if (event.target.files && event.target.files[0]) {
            const file = event.target.files[0];
            validateAndPreviewFile(file);
        }
    }

    function handleDrop(event) {
        event.preventDefault();
        document.getElementById('drop-zone').classList.remove('border-primary', 'bg-blue-50');
        
        if (event.dataTransfer.files && event.dataTransfer.files[0]) {
            const file = event.dataTransfer.files[0];
            validateAndPreviewFile(file);
            
            const input = document.getElementById('file-input');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        }
    }

    function validateAndPreviewFile(file) {
        const validExtensions = ['.xlsx', '.xls', '.csv'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validExtensions.includes(fileExtension)) {
            showToast('Please upload a valid Excel or CSV file', 'error');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            showToast('File size must be less than 10MB', 'error');
            return;
        }

        selectedFile = file;
        
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = (file.size / 1024).toFixed(2) + ' KB';
        document.getElementById('file-preview').classList.remove('hidden');
        document.getElementById('upload-content').classList.add('hidden');
        document.getElementById('submit-btn').disabled = false;
        
        showToast('File ready for import: ' + file.name, 'success');
    }

    function removeFile() {
        selectedFile = null;
        document.getElementById('file-input').value = '';
        document.getElementById('file-preview').classList.add('hidden');
        document.getElementById('upload-content').classList.remove('hidden');
        document.getElementById('submit-btn').disabled = true;
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) {
            const newContainer = document.createElement('div');
            newContainer.id = 'toast-container';
            newContainer.className = 'fixed bottom-4 right-4 z-50 space-y-2';
            document.body.appendChild(newContainer);
        }
        
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        toast.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg flex items-center transition-all duration-300 transform translate-x-full`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    document.getElementById('import-form')?.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('file-input');
        const className = document.querySelector('input[name="class_name"]');
        
        if (!fileInput.files || fileInput.files.length === 0) {
            e.preventDefault();
            showToast('Please select a file to import', 'error');
            return;
        }
        
        if (!className.value.trim()) {
            e.preventDefault();
            showToast('Please enter a class name', 'error');
            className.focus();
            return;
        }
        
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Importing...';
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.querySelector('.fixed.inset-0');
            if (modal) modal.remove();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        @if(session('import_stats'))
            const stats = @json(session('import_stats'));
            if (stats) {
                let message = `${stats.imported || 0} students imported`;
                if (stats.updated) message += `, ${stats.updated} updated`;
                if (stats.warnings) message += ` with ${stats.warnings} warnings`;
                if (stats.errors) message += ` and ${stats.errors} errors`;
                showToast(message, stats.errors > 0 ? 'error' : 'success');
            }
        @endif
    });
</script>
@endpush

@push('styles')
<style>
    .finance-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
    }
    .finance-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    #drop-zone {
        transition: all 0.3s ease;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #drop-zone:hover {
        border-color: #3B82F6;
        background-color: #EFF6FF;
    }
    
    #file-preview {
        animation: slideDown 0.3s ease;
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
    
    #toast-container > div {
        transition: all 0.3s ease;
        min-width: 300px;
        max-width: 500px;
    }
    
    #toast-container > div.translate-x-full {
        transform: translateX(100%);
    }
    
    table {
        border-collapse: collapse;
    }
    
    table th {
        background-color: #f9fafb;
    }
    
    table td, table th {
        padding: 4px 8px;
    }
    
    @media (max-width: 640px) {
        .max-w-4xl {
            margin: 0 10px;
        }
        .finance-card {
            padding: 15px !important;
        }
        table {
            font-size: 10px;
        }
        table td, table th {
            padding: 3px 5px;
        }
    }
</style>
@endpush
@endsection