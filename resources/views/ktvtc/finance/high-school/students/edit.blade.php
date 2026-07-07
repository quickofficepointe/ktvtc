@extends('ktvtc.finance.layouts.app')

@section('title', 'Edit Student')
@section('subtitle', 'Update high school student information')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.hs-students.index') }}" class="text-gray-600 hover:text-primary">High School Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Edit Student</span>
</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="finance-card p-5">
        <form method="POST" action="{{ route('finance.hs-students.update', $student) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Admission Number</label>
                    <input type="text" value="{{ $student->admission_number }}"
                           class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-gray-500 text-sm" disabled>
                    <p class="text-xs text-gray-500 mt-1">Admission number cannot be changed</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm" required>
                    @error('full_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Class *</label>
                    <input type="text" name="class" value="{{ old('class', $student->class) }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                           placeholder="e.g., Form 1A, Form 2B" required>
                    @error('class')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Parent/Guardian Phone</label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                               placeholder="e.g., 0712345678">
                        @error('parent_phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Parent/Guardian Name</label>
                        <input type="text" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        @error('parent_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Status</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        <option value="active" {{ $student->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $student->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="graduated" {{ $student->status == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Profile Picture</label>
                    @if($student->profile_picture)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="Current Photo" class="w-16 h-16 rounded-full object-cover">
                        </div>
                    @endif
                    <input type="file" name="profile_picture" accept="image/*"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current photo</p>
                    @error('profile_picture')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('finance.hs-students.show', $student) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold text-sm">
                        <i class="fas fa-save mr-2"></i> Update Student
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
