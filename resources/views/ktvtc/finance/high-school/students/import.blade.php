@extends('ktvtc.finance.layouts.app')

@section('title', 'Import Students')
@section('subtitle', 'Import high school students from Excel/CSV')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.hs-students.index') }}" class="text-gray-600 hover:text-primary">High School Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Import</span>
</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="finance-card p-5">
        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4 rounded text-sm">
            <h4 class="font-semibold text-blue-800">Import Instructions</h4>
            <ul class="text-blue-700 mt-1 space-y-1 list-disc list-inside text-xs">
                <li>Download the template CSV file below</li>
                <li>Fill in the student details following the template format</li>
                <li>Required fields: admission_number, full_name, class</li>
                <li>Optional fields: parent_phone, parent_name</li>
                <li>Upload the completed file for import</li>
                <li>Cards will be automatically created for all imported students</li>
            </ul>
        </div>

        <div class="mb-4">
            <a href="{{ route('finance.hs-students.template') }}" class="text-primary hover:underline flex items-center text-sm">
                <i class="fas fa-download mr-2"></i> Download Template CSV
            </a>
        </div>

        <form method="POST" action="{{ route('finance.hs-students.import.process') }}" enctype="multipart/form-data">
            @csrf

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <div class="mb-3">
                    <i class="fas fa-file-upload text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-600 text-sm mb-1">Click to select or drag and drop your CSV/Excel file</p>
                <p class="text-xs text-gray-500">Supported formats: .xlsx, .xls, .csv</p>
                <input type="file" name="file" accept=".xlsx,.xls,.csv"
                       class="mt-3 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer"
                       required>
                @error('file')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
                <a href="{{ route('finance.hs-students.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                    <i class="fas fa-upload mr-2"></i> Import Students
                </button>
            </div>
        </form>
    </div>

    <!-- Sample Preview -->
    <div class="finance-card p-4 mt-4">
        <h4 class="font-semibold text-gray-800 text-sm mb-3">Expected Format</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">admission_number</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">full_name</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">parent_phone</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">parent_name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="px-3 py-2 text-gray-700">ADM-2024-001</td>
                        <td class="px-3 py-2 text-gray-700">John Doe</td>
                        <td class="px-3 py-2 text-gray-700">Form 1A</td>
                        <td class="px-3 py-2 text-gray-700">0712345678</td>
                        <td class="px-3 py-2 text-gray-700">Jane Doe</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 text-gray-700">ADM-2024-002</td>
                        <td class="px-3 py-2 text-gray-700">Jane Smith</td>
                        <td class="px-3 py-2 text-gray-700">Form 2B</td>
                        <td class="px-3 py-2 text-gray-700">0723456789</td>
                        <td class="px-3 py-2 text-gray-700">John Smith</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-1">* Required fields</p>
    </div>
</div>
@endsection
