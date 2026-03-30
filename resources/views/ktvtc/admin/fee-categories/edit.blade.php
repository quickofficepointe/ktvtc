@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Fee Category')
@section('subtitle', 'Update fee category information')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Categories</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $feeCategory->code }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.fee-categories.show', $feeCategory) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-eye"></i>
        <span>View Category</span>
    </a>
    <a href="{{ route('admin.tvet.fee-categories.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Categories</span>
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: {{ $feeCategory->color ?? '#3B82F6' }}20">
                <i class="fas {{ $feeCategory->icon ?? 'fa-tag' }} text-2xl" style="color: {{ $feeCategory->color ?? '#3B82F6' }}"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $feeCategory->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">Code: <span class="font-mono">{{ $feeCategory->code }}</span></p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.tvet.fee-categories.update', $feeCategory) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Basic Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Basic Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Category Name
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $feeCategory->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category Code -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Category Code
                            </label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code', $feeCategory->code) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono @error('code') border-red-500 @enderror"
                                   required>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description', $feeCategory->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Fee Properties Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-cog text-primary mr-2"></i>
                        Fee Properties
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Frequency -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Frequency
                            </label>
                            <select name="frequency"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent @error('frequency') border-red-500 @enderror"
                                    required>
                                <option value="">Select Frequency</option>
                                @foreach($frequencies as $value => $label)
                                    <option value="{{ $value }}" {{ old('frequency', $feeCategory->frequency) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('frequency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', $feeCategory->sort_order ?? 0) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <!-- Is Mandatory -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_mandatory"
                                   id="is_mandatory"
                                   value="1"
                                   {{ old('is_mandatory', $feeCategory->is_mandatory) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_mandatory" class="ml-2 text-sm text-gray-700">
                                Mandatory Fee
                            </label>
                        </div>

                        <!-- Is Refundable -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_refundable"
                                   id="is_refundable"
                                   value="1"
                                   {{ old('is_refundable', $feeCategory->is_refundable) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_refundable" class="ml-2 text-sm text-gray-700">
                                Refundable
                            </label>
                        </div>

                        <!-- Is Taxable -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_taxable"
                                   id="is_taxable"
                                   value="1"
                                   {{ old('is_taxable', $feeCategory->is_taxable) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_taxable" class="ml-2 text-sm text-gray-700">
                                Taxable
                            </label>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $feeCategory->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Suggested Items Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list text-primary mr-2"></i>
                        Suggested Items
                    </h4>
                    <p class="text-xs text-gray-500 mb-4">
                        Common item names for quick selection in fee templates
                    </p>

                    <div id="suggested-items-container">
                        @php
                            $suggestedItems = old('suggested_items', $feeCategory->suggested_items ?? ['ID Fee', 'Medical Fee', 'Caution Fee']);
                        @endphp

                        @foreach($suggestedItems as $item)
                            <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                                <input type="text"
                                       name="suggested_items[]"
                                       value="{{ $item }}"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="e.g., ID Fee">
                                <button type="button"
                                        onclick="removeSuggestedItem(this)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            onclick="addSuggestedItem()"
                            class="mt-2 px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg transition-colors flex items-center space-x-2">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Another Item</span>
                    </button>
                </div>
            </div>

            <!-- Right Column - Display Settings & Campus -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Display Settings Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-palette text-primary mr-2"></i>
                        Display Settings
                    </h4>

                    <div class="space-y-4">
                        <!-- Icon Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Icon
                            </label>
                            <select name="icon" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Icon</option>
                                @foreach($icons as $value => $label)
                                    <option value="{{ $value }}" {{ old('icon', $feeCategory->icon) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2 flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200">
                                <i id="icon-preview" class="fas {{ old('icon', $feeCategory->icon ?? 'fa-tag') }} text-3xl"
                                   style="color: {{ old('color', $feeCategory->color ?? '#3B82F6') }}"></i>
                            </div>
                        </div>

                        <!-- Color Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Color
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($colors as $value => $label)
                                    <div class="relative">
                                        <input type="radio"
                                               name="color"
                                               id="color_{{ $loop->index }}"
                                               value="{{ $value }}"
                                               {{ old('color', $feeCategory->color ?? '#3B82F6') == $value ? 'checked' : '' }}
                                               class="hidden color-radio"
                                               onchange="updateColorPreview('{{ $value }}')">
                                        <label for="color_{{ $loop->index }}"
                                               class="block w-full aspect-square rounded-lg cursor-pointer border-2 transition-all"
                                               style="background-color: {{ $value }}; border-color: {{ (old('color', $feeCategory->color ?? '#3B82F6') == $value) ? '#000' : 'transparent' }}">
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

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
                                       {{ !$feeCategory->campus_id ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_global" class="ml-2 text-sm text-gray-700">
                                    Global Category (All Campuses)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio"
                                       name="campus_scope"
                                       id="scope_specific"
                                       value="specific"
                                       {{ $feeCategory->campus_id ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                       onchange="toggleCampusSelect()">
                                <label for="scope_specific" class="ml-2 text-sm text-gray-700">
                                    Campus-Specific
                                </label>
                            </div>
                        </div>

                        <!-- Campus Select (hidden if global) -->
                        <div id="campus-select-container" class="{{ !$feeCategory->campus_id ? 'hidden' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Campus
                            </label>
                            <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Choose Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id', $feeCategory->campus_id) == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Category Stats Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        Category Usage
                    </h4>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Template Items</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $feeCategory->templateItems->count() ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Enrollment Items</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $feeCategory->enrollmentFeeItems->count() ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Created</span>
                            <span class="text-sm text-gray-900">{{ $feeCategory->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $feeCategory->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    @if($feeCategory->creator)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Created by: <span class="font-medium text-gray-700">{{ $feeCategory->creator->name }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.tvet.fee-categories.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Update Category</span>
            </button>
        </div>
    </form>
</div>

<!-- Template for new suggested item row -->
<template id="suggested-item-template">
    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
        <input type="text"
               name="suggested_items[]"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
               placeholder="e.g., New Item">
        <button type="button"
                onclick="removeSuggestedItem(this)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>
@endsection

@section('scripts')
<script>
    // ============ SUGGESTED ITEMS ============
    function addSuggestedItem() {
        const template = document.getElementById('suggested-item-template');
        const clone = template.content.cloneNode(true);
        document.getElementById('suggested-items-container').appendChild(clone);
    }

    function removeSuggestedItem(button) {
        const row = button.closest('.suggested-item-row');
        const container = document.getElementById('suggested-items-container');
        if (container.children.length > 1) {
            row.remove();
        } else {
            alert('You need at least one suggested item row');
        }
    }

    // ============ DISPLAY SETTINGS ============
    function updateColorPreview(color) {
        const iconPreview = document.getElementById('icon-preview');
        iconPreview.style.color = color;

        // Update border styles
        document.querySelectorAll('.color-radio + label').forEach(label => {
            label.style.borderColor = 'transparent';
        });

        document.querySelectorAll('.color-radio:checked + label').forEach(label => {
            label.style.borderColor = '#000';
        });
    }

    // Update icon preview when icon changes
    document.querySelector('[name="icon"]')?.addEventListener('change', function() {
        const iconPreview = document.getElementById('icon-preview');
        iconPreview.className = `fas ${this.value} text-3xl`;
    });

    // ============ CAMPUS SCOPE ============
    function toggleCampusSelect() {
        const isSpecific = document.getElementById('scope_specific').checked;
        const container = document.getElementById('campus-select-container');

        if (isSpecific) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            document.querySelector('[name="campus_id"]').value = '';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial icon preview
        const iconInput = document.querySelector('[name="icon"]');
        if (iconInput && iconInput.value) {
            document.getElementById('icon-preview').className = `fas ${iconInput.value} text-3xl`;
        }

        // Set initial color preview
        const selectedColor = document.querySelector('.color-radio:checked');
        if (selectedColor) {
            updateColorPreview(selectedColor.value);
        }

        // Initialize campus select visibility
        toggleCampusSelect();
    });
</script>

<style>
    .required:after {
        content: " *";
        color: #EF4444;
    }

    .color-radio:checked + label {
        border-width: 3px;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
