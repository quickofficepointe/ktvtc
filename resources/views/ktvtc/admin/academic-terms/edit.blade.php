@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Academic Term')
@section('subtitle', 'Update academic term information')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Academic</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Terms</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $academicTerm->code }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.academic-terms.show', $academicTerm) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Term</span>
    </a>
    <a href="{{ route('admin.tvet.academic-terms.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Terms</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center mr-4">
                <i class="fas fa-calendar-alt text-primary text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $academicTerm->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">Code: <span class="font-mono">{{ $academicTerm->code }}</span></p>
            </div>
            @if($academicTerm->is_current)
                <span class="ml-4 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium flex items-center">
                    <i class="fas fa-circle mr-1 text-green-500 text-xs"></i>
                    Current Term
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.tvet.academic-terms.update', $academicTerm) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Term Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Basic Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Term Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Term Name
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $academicTerm->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Term Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Term Code
                            </label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code', $academicTerm->code) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono @error('code') border-red-500 @enderror"
                                   required>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Short Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Short Code
                            </label>
                            <input type="text"
                                   name="short_code"
                                   value="{{ old('short_code', $academicTerm->short_code) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Term Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Term Number
                            </label>
                            <select name="term_number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('term_number') border-red-500 @enderror"
                                    required>
                                <option value="">Select Term</option>
                                @foreach($termNumbers as $number)
                                    <option value="{{ $number }}" {{ old('term_number', $academicTerm->term_number) == $number ? 'selected' : '' }}>
                                        Term {{ $number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Academic Year -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Academic Year
                            </label>
                            <select name="academic_year"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('academic_year') border-red-500 @enderror"
                                    required>
                                <option value="">Select Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}" {{ old('academic_year', $academicTerm->academic_year) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Academic Year Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Academic Year Display
                            </label>
                            <input type="text"
                                   name="academic_year_name"
                                   value="{{ old('academic_year_name', $academicTerm->academic_year_name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 2024/2025">
                        </div>
                    </div>
                </div>

                <!-- Term Dates Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        Term Dates
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Start Date
                            </label>
                            <input type="date"
                                   name="start_date"
                                   value="{{ old('start_date', $academicTerm->start_date->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('start_date') border-red-500 @enderror"
                                   required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                End Date
                            </label>
                            <input type="date"
                                   name="end_date"
                                   value="{{ old('end_date', $academicTerm->end_date->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('end_date') border-red-500 @enderror"
                                   required>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fee Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Fee Due Date
                            </label>
                            <input type="date"
                                   name="fee_due_date"
                                   value="{{ old('fee_due_date', $academicTerm->fee_due_date->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('fee_due_date') border-red-500 @enderror"
                                   required>
                            @error('fee_due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Registration Dates Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-door-open text-primary mr-2"></i>
                        Registration Period
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Registration Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Registration Start Date
                            </label>
                            <input type="date"
                                   name="registration_start_date"
                                   value="{{ old('registration_start_date', $academicTerm->registration_start_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Registration End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Registration End Date
                            </label>
                            <input type="date"
                                   name="registration_end_date"
                                   value="{{ old('registration_end_date', $academicTerm->registration_end_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Late Registration Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Registration Start
                            </label>
                            <input type="date"
                                   name="late_registration_start_date"
                                   value="{{ old('late_registration_start_date', $academicTerm->late_registration_start_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Late Registration End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Registration End
                            </label>
                            <input type="date"
                                   name="late_registration_end_date"
                                   value="{{ old('late_registration_end_date', $academicTerm->late_registration_end_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-4 flex items-center">
                        <input type="checkbox"
                               name="allow_late_registration"
                               id="allow_late_registration"
                               value="1"
                               {{ old('allow_late_registration', $academicTerm->allow_late_registration) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="allow_late_registration" class="ml-2 text-sm text-gray-700">
                            Allow Late Registration
                        </label>
                    </div>
                </div>

                <!-- Exam Dates Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-primary mr-2"></i>
                        Examination Period
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Exam Registration Start -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Exam Registration Start
                            </label>
                            <input type="date"
                                   name="exam_registration_start_date"
                                   value="{{ old('exam_registration_start_date', $academicTerm->exam_registration_start_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Exam Registration End -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Exam Registration End
                            </label>
                            <input type="date"
                                   name="exam_registration_end_date"
                                   value="{{ old('exam_registration_end_date', $academicTerm->exam_registration_end_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Exam Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Exam Start Date
                            </label>
                            <input type="date"
                                   name="exam_start_date"
                                   value="{{ old('exam_start_date', $academicTerm->exam_start_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Exam End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Exam End Date
                            </label>
                            <input type="date"
                                   name="exam_end_date"
                                   value="{{ old('exam_end_date', $academicTerm->exam_end_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings & Campus -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Campus Assignment Card -->
                @if(auth()->user()->role == 2)
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-building text-primary mr-2"></i>
                        Campus Assignment
                    </h4>

                    <div class="space-y-4">
                        <!-- Global or Campus-specific -->
                        <div>
                            <div class="flex items-center mb-3">
                                <input type="radio"
                                       name="campus_scope"
                                       id="scope_global"
                                       value="global"
                                       {{ !$academicTerm->campus_id ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_global" class="ml-2 text-sm text-gray-700">
                                    Global Term (All Campuses)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio"
                                       name="campus_scope"
                                       id="scope_specific"
                                       value="specific"
                                       {{ $academicTerm->campus_id ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_specific" class="ml-2 text-sm text-gray-700">
                                    Campus-Specific
                                </label>
                            </div>
                        </div>

                        <!-- Campus Select (hidden if global) -->
                        <div id="campus-select-container" class="{{ !$academicTerm->campus_id ? 'hidden' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Campus
                            </label>
                            <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Choose Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id', $academicTerm->campus_id) == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Status Settings Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-flag text-primary mr-2"></i>
                        Status Settings
                    </h4>

                    <div class="space-y-4">
                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $academicTerm->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Active
                            </label>
                        </div>

                        <!-- Is Current -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_current"
                                   id="is_current"
                                   value="1"
                                   {{ old('is_current', $academicTerm->is_current) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_current" class="ml-2 text-sm text-gray-700">
                                Set as Current Term
                            </label>
                            @if($academicTerm->is_current)
                                <span class="ml-2 text-xs text-green-600">(Current)</span>
                            @endif
                        </div>

                        <!-- Is Registration Open -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_registration_open"
                                   id="is_registration_open"
                                   value="1"
                                   {{ old('is_registration_open', $academicTerm->is_registration_open) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_registration_open" class="ml-2 text-sm text-gray-700">
                                Registration Open
                            </label>
                        </div>

                        <!-- Lock Fee Generation -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_fee_generation_locked"
                                   id="is_fee_generation_locked"
                                   value="1"
                                   {{ old('is_fee_generation_locked', $academicTerm->is_fee_generation_locked) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_fee_generation_locked" class="ml-2 text-sm text-gray-700">
                                Lock Fee Generation
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Financial Settings Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-money-bill text-primary mr-2"></i>
                        Financial Settings
                    </h4>

                    <div class="space-y-4">
                        <!-- Late Registration Fee -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Registration Fee (KES)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                <input type="number"
                                       name="late_registration_fee"
                                       value="{{ old('late_registration_fee', $academicTerm->late_registration_fee ?? 0) }}"
                                       min="0"
                                       step="100"
                                       class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Late Payment Percentage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Payment Penalty (%)
                            </label>
                            <div class="relative">
                                <input type="number"
                                       name="late_payment_percentage"
                                       value="{{ old('late_payment_percentage', $academicTerm->late_payment_percentage ?? 0) }}"
                                       min="0"
                                       max="100"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <span class="absolute right-3 top-2 text-gray-500">%</span>
                            </div>
                        </div>

                        <!-- Late Payment Fixed Fee -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Late Payment Fixed Fee (KES)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                <input type="number"
                                       name="late_payment_fee"
                                       value="{{ old('late_payment_fee', $academicTerm->late_payment_fee ?? 0) }}"
                                       min="0"
                                       step="100"
                                       class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description & Notes Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-primary mr-2"></i>
                        Description & Notes
                    </h4>

                    <div class="space-y-4">
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Brief description of this term...">{{ old('description', $academicTerm->description) }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Internal Notes
                            </label>
                            <textarea name="notes"
                                      rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Internal notes (only visible to staff)">{{ old('notes', $academicTerm->notes) }}</textarea>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', $academicTerm->sort_order ?? 0) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Term Statistics Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        Term Statistics
                    </h4>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Enrollments</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $academicTerm->enrollments->count() ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Created</span>
                            <span class="text-sm text-gray-900">{{ $academicTerm->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $academicTerm->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    @if($academicTerm->creator)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Created by: <span class="font-medium text-gray-700">{{ $academicTerm->creator->name }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.tvet.academic-terms.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Update Term</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // ============ CAMPUS SCOPE ============
    function toggleCampusSelect() {
        const isSpecific = document.getElementById('scope_specific')?.checked;
        const container = document.getElementById('campus-select-container');

        if (container) {
            if (isSpecific) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                const campusSelect = document.querySelector('[name="campus_id"]');
                if (campusSelect) campusSelect.value = '';
            }
        }
    }

    // ============ DATE VALIDATION ============
    document.querySelector('[name="start_date"]')?.addEventListener('change', function() {
        const endDate = document.querySelector('[name="end_date"]');
        const dueDate = document.querySelector('[name="fee_due_date"]');

        if (endDate && new Date(endDate.value) < new Date(this.value)) {
            alert('End date cannot be before start date');
            endDate.value = this.value;
        }
        if (dueDate && new Date(dueDate.value) < new Date(this.value)) {
            alert('Fee due date should be on or after term start date');
            dueDate.value = this.value;
        }
    });

    document.querySelector('[name="end_date"]')?.addEventListener('change', function() {
        const startDate = document.querySelector('[name="start_date"]');
        if (startDate && new Date(this.value) < new Date(startDate.value)) {
            alert('End date cannot be before start date');
            this.value = startDate.value;
        }
    });

    document.querySelector('[name="fee_due_date"]')?.addEventListener('change', function() {
        const startDate = document.querySelector('[name="start_date"]');
        if (startDate && new Date(this.value) < new Date(startDate.value)) {
            alert('Fee due date should be on or after term start date');
            this.value = startDate.value;
        }
    });

    // ============ WARNING FOR CURRENT TERM ============
    document.getElementById('is_current')?.addEventListener('change', function() {
        if (this.checked) {
            @if($academicTerm->is_current)
                // Already current, no warning
            @else
                if (!confirm('Setting this as the current term will remove current status from other terms. Continue?')) {
                    this.checked = false;
                }
            @endif
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCampusSelect();
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
