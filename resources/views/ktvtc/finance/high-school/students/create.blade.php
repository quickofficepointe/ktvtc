@extends('ktvtc.finance.layouts.app')

@section('title', 'Add High School Student')
@section('subtitle', 'Create a new high school student record')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.hs-students.index') }}" class="text-gray-600 hover:text-primary">High School Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Add Student</span>
</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="finance-card p-5">
        <form method="POST" action="{{ route('finance.hs-students.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Admission Number *</label>
                    <input type="text" name="admission_number" value="{{ old('admission_number') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                           placeholder="e.g., ADM-2024-001" required>
                    @error('admission_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm" required>
                    @error('full_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Class *</label>
                    <input type="text" name="class" value="{{ old('class') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                           placeholder="e.g., Form 1A, Form 2B" required>
                    @error('class')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Parent/Guardian Phone</label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone') }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                               placeholder="e.g., 0712345678">
                        @error('parent_phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Parent/Guardian Name</label>
                        <input type="text" name="parent_name" value="{{ old('parent_name') }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        @error('parent_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <p class="text-xs text-gray-500 mt-1">Upload a passport photo (optional)</p>
                    @error('profile_picture')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 p-3 rounded-lg text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    A card will be automatically issued for this student upon creation.
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('finance.hs-students.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold text-sm">
                        <i class="fas fa-save mr-2"></i> Create Student
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
