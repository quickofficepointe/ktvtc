@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Fee Template')
@section('subtitle', 'Update fee template and manage fee items')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fees</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Course Fee Templates</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $courseFeeTemplate->code ?? 'Edit' }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.course-fee-templates.show', $courseFeeTemplate) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Template</span>
    </a>
    <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Templates</span>
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Template Summary Card -->
    <div class="lg:col-span-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-xl bg-primary-light flex items-center justify-center">
                        <i class="fas fa-file-invoice text-primary text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $courseFeeTemplate->name }}</h1>
                        <div class="flex items-center mt-2 space-x-3">
                            @if($courseFeeTemplate->code)
                                <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono text-gray-700">
                                    {{ $courseFeeTemplate->code }}
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                @if($courseFeeTemplate->exam_type == 'nita') bg-blue-100 text-blue-800
                                @elseif($courseFeeTemplate->exam_type == 'cdacc') bg-green-100 text-green-800
                                @elseif($courseFeeTemplate->exam_type == 'school_assessment') bg-amber-100 text-amber-800
                                @else bg-purple-100 text-purple-800
                                @endif">
                                {{ $courseFeeTemplate->exam_type_label }}
                            </span>
                            @if($courseFeeTemplate->campus)
                                <span class="px-3 py-1 bg-blue-100 rounded-lg text-sm text-blue-700">
                                    <i class="fas fa-building mr-1"></i> {{ $courseFeeTemplate->campus->name }}
                                </span>
                            @else
                                <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                                    <i class="fas fa-globe mr-1"></i> Global Template
                                </span>
                            @endif
                            @php
                                $statusColor = $courseFeeTemplate->is_active ? 'green' : 'gray';
                                $statusText = $courseFeeTemplate->is_active ? 'Active' : 'Inactive';
                            @endphp
                            <span class="px-3 py-1 bg-{{ $statusColor }}-100 rounded-lg text-sm text-{{ $statusColor }}-700">
                                <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i> {{ $statusText }}
                            </span>
                            @if($courseFeeTemplate->is_default)
                                <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                                    <i class="fas fa-star mr-1"></i> Default Template
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Course Fee</p>
                    <p class="text-3xl font-bold text-gray-900">KES {{ number_format($courseFeeTemplate->total_amount, 2) }}</p>
                    <div class="flex items-center justify-end mt-1 text-xs text-gray-500">
                        <span class="mr-2">Tuition: KES {{ number_format($courseFeeTemplate->total_tuition_fee, 0) }}</span>
                        <span>Other: KES {{ number_format($courseFeeTemplate->total_other_fees, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Template Settings -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Template Settings Form -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-cog text-primary mr-2"></i>
                    Template Settings
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.tvet.course-fee-templates.update', $courseFeeTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <!-- Template Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Template Name
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $courseFeeTemplate->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Template Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Template Code
                            </label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code', $courseFeeTemplate->code) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Course (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Course
                            </label>
                            <input type="text"
                                   value="{{ $courseFeeTemplate->course->name ?? 'N/A' }}"
                                   class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                            <input type="hidden" name="course_id" value="{{ $courseFeeTemplate->course_id }}">
                        </div>

                        <!-- Exam Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Exam Type
                            </label>
                            <input type="text"
                                   value="{{ $courseFeeTemplate->exam_type_label }}"
                                   class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700"
                                   readonly>
                            <input type="hidden" name="exam_type" value="{{ $courseFeeTemplate->exam_type }}">
                        </div>

                        <!-- Total Terms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Number of Terms
                            </label>
                            <select name="total_terms"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required>
                                <option value="">Select Terms</option>
                                @foreach([1,2,3,4] as $terms)
                                    <option value="{{ $terms }}" {{ old('total_terms', $courseFeeTemplate->total_terms) == $terms ? 'selected' : '' }}>
                                        {{ $terms }} Term{{ $terms > 1 ? 's' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Duration Months -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Duration (Months)
                            </label>
                            <select name="duration_months"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Duration</option>
                                @foreach([1,2,3,6,9,12,18,24] as $months)
                                    <option value="{{ $months }}" {{ old('duration_months', $courseFeeTemplate->duration_months) == $months ? 'selected' : '' }}>
                                        {{ $months }} Month{{ $months > 1 ? 's' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Intake Periods -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Applicable Intake Periods
                            </label>
                            <div class="space-y-2">
                                @php
                                    $intakePeriods = ['Jan', 'May', 'Sept'];
                                    $selectedIntakes = old('intake_periods', $courseFeeTemplate->intake_periods ?? []);
                                @endphp
                                @foreach($intakePeriods as $period)
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="intake_periods[]"
                                               value="{{ $period }}"
                                               {{ in_array($period, $selectedIntakes) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700">{{ $period }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Status Settings -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $courseFeeTemplate->is_active) ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_default"
                                           id="is_default"
                                           value="1"
                                           {{ old('is_default', $courseFeeTemplate->is_default) ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Set as Default Template</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_public"
                                           value="1"
                                           {{ old('is_public', $courseFeeTemplate->is_public) ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Public (Visible to Students)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description', $courseFeeTemplate->description) }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Internal Notes
                            </label>
                            <textarea name="notes"
                                      rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('notes', $courseFeeTemplate->notes) }}</textarea>
                        </div>

                        <!-- Campus Assignment (Admin only) -->
                        @if(auth()->user()->role == 2)
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Campus Assignment
                            </label>
                            <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Global Template (All Campuses)</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id', $courseFeeTemplate->campus_id) == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Update Button -->
                        <div class="pt-4">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                                <i class="fas fa-save"></i>
                                <span>Update Template Settings</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Template Statistics Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    Template Statistics
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Fee Items</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $courseFeeTemplate->feeItems->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary rounded-full h-2"
                                 style="width: {{ min($courseFeeTemplate->feeItems->count() * 10, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Enrollments</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $courseFeeTemplate->enrollments->count() ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 rounded-full h-2"
                                 style="width: {{ min(($courseFeeTemplate->enrollments->count() ?? 0) * 5, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Created</span>
                            <span class="text-sm text-gray-900">{{ $courseFeeTemplate->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-sm text-gray-600">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $courseFeeTemplate->updated_at->format('M d, Y') }}</span>
                        </div>
                        @if($courseFeeTemplate->creator)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Created by: <span class="font-medium text-gray-700">{{ $courseFeeTemplate->creator->name }}</span>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Fee Items Management -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Add Fee Item Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>
                    Add Fee Item
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.tvet.course-fee-templates.fee-items.store', $courseFeeTemplate) }}"
                      method="POST"
                      id="addFeeItemForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Fee Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Fee Category
                            </label>
                            <select name="fee_category_id"
                                    id="fee_category_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                    onchange="updateItemName()">
                                <option value="">Select Category</option>
                                @foreach($feeCategories as $category)
                                    <option value="{{ $category->id }}"
                                            data-code="{{ $category->code }}"
                                            data-suggestions='@json($category->suggested_items)'>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Item Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Item Name
                            </label>
                            <div class="flex">
                                <input type="text"
                                       name="item_name"
                                       id="item_name"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., Tuition Fee Q1"
                                       required>
                                <button type="button"
                                        onclick="showSuggestedItems()"
                                        class="ml-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                                        title="Suggested Items">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Amount (KES)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                <input type="number"
                                       name="amount"
                                       step="0.01"
                                       min="0"
                                       class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="0.00"
                                       required>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity
                            </label>
                            <input type="number"
                                   name="quantity"
                                   value="1"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Applicable Terms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Applicable Terms
                            </label>
                            <select name="applicable_terms"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required>
                                <option value="all">All Terms</option>
                                <option value="1">Term 1 Only</option>
                                <option value="2">Term 2 Only</option>
                                <option value="3">Term 3 Only</option>
                                <option value="4">Term 4 Only</option>
                                <option value="1,2">Terms 1 & 2</option>
                                <option value="1,2,3">Terms 1-3</option>
                                <option value="1,2,3,4">Terms 1-4</option>
                                <option value="1,3">Terms 1 & 3</option>
                                <option value="2,4">Terms 2 & 4</option>
                            </select>
                        </div>

                        <!-- Due Day Offset -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Due Day Offset
                            </label>
                            <input type="number"
                                   name="due_day_offset"
                                   value="0"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Days after term start">
                        </div>

                        <!-- Options -->
                        <div class="md:col-span-2 lg:col-span-3">
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_required"
                                           value="1"
                                           checked
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Required Fee</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_refundable"
                                           value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Refundable</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_advance_payment"
                                           value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Advance Payment</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_visible_to_student"
                                           value="1"
                                           checked
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Visible to Students</span>
                                </label>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number"
                                   name="sort_order"
                                   value="{{ $courseFeeTemplate->feeItems->count() + 1 }}"
                                   min="1"
                                   class="w-full md:w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Add Button -->
                        <div class="md:col-span-2 lg:col-span-3 flex justify-end">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add Fee Item</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Fee Items List Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list text-primary mr-2"></i>
                        Fee Items
                    </h3>
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                        {{ $courseFeeTemplate->feeItems->count() }} items
                    </span>
                </div>
            </div>

            <div class="p-6">
                @if($courseFeeTemplate->feeItems->count() > 0)
                    <div class="space-y-4">
                        @foreach($courseFeeTemplate->feeItems->groupBy('feeCategory.name') as $categoryName => $items)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-primary mr-2"></span>
                                    {{ $categoryName }}
                                </h4>
                                <div class="space-y-3">
                                    @foreach($items as $item)
                                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <span class="text-sm font-medium text-gray-900">{{ $item->item_name }}</span>
                                                    @if(!$item->is_required)
                                                        <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Optional</span>
                                                    @endif
                                                    @if($item->is_refundable)
                                                        <span class="ml-2 px-2 py-0.5 text-xs bg-purple-100 text-purple-600 rounded-full">Refundable</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center mt-1 text-xs text-gray-500">
                                                    <span class="mr-3">
                                                        <i class="fas fa-tag mr-1"></i>
                                                        KES {{ number_format($item->amount, 2) }}
                                                        @if($item->quantity > 1)
                                                            x {{ $item->quantity }}
                                                        @endif
                                                    </span>
                                                    <span class="mr-3">
                                                        <i class="fas fa-calendar-alt mr-1"></i>
                                                        {{ $item->term_label }}
                                                    </span>
                                                    @if($item->due_day_offset > 0)
                                                        <span>
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Due {{ $item->due_day_offset }} days after term start
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-bold text-gray-900 mr-4">
                                                    KES {{ number_format($item->total_amount, 2) }}
                                                </span>
                                                <button type="button"
                                                        onclick="editFeeItem('{{ $item->id }}')"
                                                        class="p-1.5 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.tvet.course-fee-templates.fee-items.destroy', [$courseFeeTemplate, $item]) }}"
                                                      method="POST"
                                                      class="inline-block"
                                                      onsubmit="return confirm('Are you sure you want to delete this fee item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 text-gray-600 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <!-- Total Summary -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-600">Tuition Fees:</span>
                                        <span class="text-sm font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_tuition_fee, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-sm text-gray-600">Other Fees:</span>
                                        <span class="text-sm font-medium text-gray-900">KES {{ number_format($courseFeeTemplate->total_other_fees, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 text-base font-bold">
                                        <span class="text-gray-800">Total:</span>
                                        <span class="text-primary">KES {{ number_format($courseFeeTemplate->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-invoice text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No fee items added yet</p>
                        <p class="text-gray-400 text-sm mt-1">Add fee items using the form above</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bulk Actions Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-tools text-primary mr-2"></i>
                    Bulk Actions
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Duplicate Template -->
                    <button onclick="duplicateTemplate('{{ $courseFeeTemplate->id }}')"
                            class="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-left">
                        <i class="fas fa-copy text-blue-600 text-xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-900">Duplicate Template</p>
                        <p class="text-xs text-gray-600 mt-1">Create a copy of this template</p>
                    </button>

                    <!-- Export Template -->
                    <button onclick="exportTemplate('{{ $courseFeeTemplate->id }}')"
                            class="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-left">
                        <i class="fas fa-download text-green-600 text-xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-900">Export Template</p>
                        <p class="text-xs text-gray-600 mt-1">Download as JSON</p>
                    </button>

                    <!-- Preview Template -->
                    <a href="{{ route('admin.tvet.course-fee-templates.show', $courseFeeTemplate) }}"
                       class="p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-left">
                        <i class="fas fa-eye text-purple-600 text-xl mb-2"></i>
                        <p class="text-sm font-medium text-gray-900">Preview Template</p>
                        <p class="text-xs text-gray-600 mt-1">View full template details</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Item Modal -->
<div id="editFeeItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editFeeItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Fee Item</h3>
                    <button onclick="closeModal('editFeeItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editFeeItemForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <input type="hidden" name="fee_item_id" id="edit_fee_item_id">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                            <input type="text" name="item_name" id="edit_item_name"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount (KES)</label>
                                <input type="number" name="amount" id="edit_amount" step="0.01" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input type="number" name="quantity" id="edit_quantity" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Applicable Terms</label>
                            <select name="applicable_terms" id="edit_applicable_terms"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required>
                                <option value="all">All Terms</option>
                                <option value="1">Term 1 Only</option>
                                <option value="2">Term 2 Only</option>
                                <option value="3">Term 3 Only</option>
                                <option value="4">Term 4 Only</option>
                                <option value="1,2">Terms 1 & 2</option>
                                <option value="1,2,3">Terms 1-3</option>
                                <option value="1,2,3,4">Terms 1-4</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Day Offset</label>
                            <input type="number" name="due_day_offset" id="edit_due_day_offset" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_required" id="edit_is_required" value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Required Fee</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_refundable" id="edit_is_refundable" value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Refundable</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_visible_to_student" id="edit_is_visible_to_student" value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Visible to Students</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('editFeeItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitEditFeeItemForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Suggested Items Modal -->
<div id="suggestedItemsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('suggestedItemsModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Suggested Items</h3>
                    <button onclick="closeModal('suggestedItemsModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="suggestedItemsList" class="space-y-2">
                    <!-- Items will be populated here -->
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeModal('suggestedItemsModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ FEE ITEM MANAGEMENT ============
    function updateItemName() {
        const categorySelect = document.getElementById('fee_category_id');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];

        if (selectedOption.value) {
            const categoryCode = selectedOption.dataset.code;
            const termSelect = document.querySelector('select[name="applicable_terms"]');
            const termValue = termSelect.value;

            let itemName = '';

            switch(categoryCode) {
                case 'TUITION':
                    itemName = 'Tuition Fee';
                    break;
                case 'REGISTRATION':
                    itemName = 'Registration Fee';
                    break;
                case 'EXAMINATION':
                    itemName = 'Exam Fee';
                    break;
                case 'ACCOMMODATION':
                    itemName = 'Hostel Fee';
                    break;
                case 'MATERIALS':
                    itemName = 'Materials Fee';
                    break;
                default:
                    itemName = selectedOption.text + ' Fee';
            }

            if (termValue !== 'all') {
                itemName += ` Term ${termValue.replace(/,/g, ' & ')}`;
            }

            document.getElementById('item_name').value = itemName;
        }
    }

    function showSuggestedItems() {
        const categorySelect = document.getElementById('fee_category_id');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];

        if (!selectedOption.value) {
            alert('Please select a fee category first');
            return;
        }

        const suggestions = selectedOption.dataset.suggestions;
        const itemsList = document.getElementById('suggestedItemsList');

        itemsList.innerHTML = '';

        if (suggestions) {
            const items = JSON.parse(suggestions);
            items.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors';
                button.textContent = item;
                button.onclick = function() {
                    document.getElementById('item_name').value = item;
                    closeModal('suggestedItemsModal');
                };
                itemsList.appendChild(button);
            });
        } else {
            itemsList.innerHTML = '<p class="text-gray-500 text-center py-4">No suggested items for this category</p>';
        }

        openModal('suggestedItemsModal');
    }

    function editFeeItem(itemId) {
        // Fetch item details via AJAX
        fetch(`/admin/tvet/course-fee-templates/fee-items/${itemId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_fee_item_id').value = data.id;
                document.getElementById('edit_item_name').value = data.item_name;
                document.getElementById('edit_amount').value = data.amount;
                document.getElementById('edit_quantity').value = data.quantity;
                document.getElementById('edit_applicable_terms').value = data.applicable_terms;
                document.getElementById('edit_due_day_offset').value = data.due_day_offset || 0;
                document.getElementById('edit_is_required').checked = data.is_required;
                document.getElementById('edit_is_refundable').checked = data.is_refundable;
                document.getElementById('edit_is_visible_to_student').checked = data.is_visible_to_student;

                document.getElementById('editFeeItemForm').action =
                    `/admin/tvet/course-fee-templates/{{ $courseFeeTemplate->id }}/fee-items/${itemId}`;

                openModal('editFeeItemModal');
            });
    }

    function submitEditFeeItemForm() {
        document.getElementById('editFeeItemForm').submit();
    }

    // ============ TEMPLATE ACTIONS ============
    function duplicateTemplate(templateId) {
        if (confirm('Are you sure you want to duplicate this template?')) {
            window.location.href = `/admin/tvet/course-fee-templates/${templateId}/duplicate`;
        }
    }

    function exportTemplate(templateId) {
        window.location.href = `/admin/tvet/course-fee-templates/${templateId}/export`;
    }

    // ============ MODAL FUNCTIONS ============
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Update item name when term selection changes
    document.querySelector('select[name="applicable_terms"]')?.addEventListener('change', updateItemName);

    // Warn when setting as default
    document.getElementById('is_default')?.addEventListener('change', function() {
        if (this.checked) {
            @if(!$courseFeeTemplate->is_default)
                if (!confirm('Setting this as the default template will remove default status from other templates. Continue?')) {
                    this.checked = false;
                }
            @endif
        }
    });

    // Close modals when clicking escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }

    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
