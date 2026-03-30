@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Import Historical Data')
@section('subtitle', 'Import student records from previous years')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Import</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Historical Data</span>
    </div>
</li>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
        <h3 class="text-lg font-semibold text-gray-800">Import Historical Student Financial Records</h3>
        <p class="text-sm text-gray-600 mt-1">Upload CSV files from previous years (2020-2026)</p>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('import_stats'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Import Statistics</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600">Students Imported</p>
                        <p class="text-xl font-bold text-gray-800">{{ session('import_stats')['imported'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600">Enrollments Created</p>
                        <p class="text-xl font-bold text-gray-800">{{ session('import_stats')['enrollments'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600">Payments Recorded</p>
                        <p class="text-xl font-bold text-gray-800">{{ session('import_stats')['payments'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600">Errors</p>
                        <p class="text-xl font-bold {{ session('import_stats')['errors'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ session('import_stats')['errors'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.import.historical.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 required">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Upload the CSV file from previous years (max 10MB)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Year</label>
                    <select name="year" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Year</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 required">Campus</label>
                    <select name="campus_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 required">File Format</label>
                    <select name="import_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Format</option>
                        <option value="original_format">Original Format (HDBT/ICP/PA)</option>
                        <option value="2026_format">2026 Format (Detailed)</option>
                        <option value="2025_format">2025 Format</option>
                        <option value="2024_format">2024 Format</option>
                        <option value="2023_format">2023 Format</option>
                        <option value="2022_format">2022 Format</option>
                        <option value="2020_format">2020 Format</option>
                    </select>
                </div>
            </div>

            <!-- File Format Guide -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-amber-800 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    File Format Guide
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="font-medium text-amber-700">Original Format</p>
                        <p class="text-amber-600 text-xs">CODE,ADM,YEAR,ADM,,NAME,CONTACT,,STATUS,MODE,REG,FEE BALANCE,MAY,JUNE,JULY FEES,AUG FEES,SEPT,OCT,NOV,DEC</p>
                    </div>
                    <div>
                        <p class="font-medium text-amber-700">2026/2025 Format</p>
                        <p class="text-amber-600 text-xs">CODE,ADM,YEAR,REG. NO,SEX,NAME,CONTACT,EMAIL,ID NO,ADM DATE,TSHIRT,INTAKE,COURSE,STATUS,MODE,% PAID,BASE FEES,EXTRA PAYMENTS,HOSTEL,FEE PAYABLE,...</p>
                    </div>
                    <div>
                        <p class="font-medium text-amber-700">2022 Format</p>
                        <p class="text-amber-600 text-xs">,ADM,YEAR,ADM,SEX,NAME,CONTACT,EMAIL,ADM DATE,TSHIRT,INTAKE,COURSE,STATUS,MODE,% UNPAID,FEES,DAMAGES,FEE BALANCE,FEE PAID,...</p>
                    </div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Import Options</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="create_missing_students" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Create missing student records automatically</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="update_existing" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Update existing records if found</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="record_payments" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Record individual monthly payments</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="mark_historical" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Mark as historical records</span>
                    </label>
                </div>
            </div>

            <!-- Sample Data Preview -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Sample of your CSV files:</h4>
                <div class="overflow-x-auto bg-gray-50 rounded-lg p-3">
                    <pre class="text-xs text-gray-600">
{{-- Sample from ORIGINAL.csv --}}
HDBT,21,2021,HDBT/021/2021,Ms.,MAGDALINE KATUTA,795029328,HAIRDRESSING AND BEAUTY THERAPY,,REG,CK,"9,000",,,,,,,,

{{-- Sample from 2026.csv --}}
FPCM2/,803,/2026,FPCM2/803/2026,Ms.,SELPHINE KHAVAYI MUKABWAH,(254) 729-606 087,selphinekhavai40@gmail.com,2901 7071,20/05/2025,,JAN,FOOD PRODUCTION(COOKERY) MODULE 2,SELF,REG,77%,"12,000","16,500",0,"28,500",,"-8,434","6,566","21,934",0,"-15,000",0,...

{{-- Sample from 2022.csv --}}
HDBT/,125,/2022,HDBT/125/2022,Ms.,DOROTHY ROSELINE LEAROTE,743335586,lodopapitdorothee@gmail,,,JAN,HAIRDRESSING AND BEAUTY THERAPY,GEP,REG,100%,"18,000",,"18,000",0,,,,,,"1,000",...
                    </pre>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.enrollments.index') }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-upload mr-2"></i>
                    Import Historical Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Export Summary Section -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Export Historical Summary</h3>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.import.historical.export') }}" method="GET" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Year</label>
                <select name="year" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Years</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Campus</label>
                <select name="campus_id" class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-download mr-2"></i>
                    Export Summary CSV
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview file name when selected
        document.querySelector('input[name="csv_file"]').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                // Auto-detect format based on filename
                if (fileName.includes('ORIGINAL')) {
                    document.querySelector('select[name="import_type"]').value = 'original_format';
                } else if (fileName.includes('2026')) {
                    document.querySelector('select[name="import_type"]').value = '2026_format';
                } else if (fileName.includes('2025')) {
                    document.querySelector('select[name="import_type"]').value = '2025_format';
                } else if (fileName.includes('2024')) {
                    document.querySelector('select[name="import_type"]').value = '2024_format';
                } else if (fileName.includes('2023')) {
                    document.querySelector('select[name="import_type"]').value = '2023_format';
                } else if (fileName.includes('2022')) {
                    document.querySelector('select[name="import_type"]').value = '2022_format';
                } else if (fileName.includes('2020')) {
                    document.querySelector('select[name="import_type"]').value = '2020_format';
                }
            }
        });
    });
</script>
@endsection
