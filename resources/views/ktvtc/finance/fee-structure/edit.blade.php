@extends('ktvtc.finance.layouts.app')

@section('title', 'Edit Fee Structure: ' . $course->name)
@section('subtitle', 'Modify course fee breakdown')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Structure</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $course->name }}</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Edit</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.show', $course) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Status Banner -->
    @if($course->hasPendingFeeChanges())
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-clock text-amber-600 text-lg mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-amber-800">Pending Approval</p>
                    <p class="text-xs text-amber-700">This fee structure has pending changes waiting for approval.</p>
                </div>
            </div>
        </div>
    @endif

    @if($course->isFeeStructureApproved())
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-lg mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">Approved</p>
                    <p class="text-xs text-green-700">This fee structure has been approved.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
            <h2 class="text-lg font-semibold text-gray-800">
                Edit Fee Structure: {{ $course->name }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Current Version: {{ $course->fee_version ?? 'v1.0' }}</p>
        </div>

        <form action="{{ route('finance.fee-structure.update', $course) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <!-- Fee Items -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Fee Items <span class="text-red-500">*</span>
                        </label>
                        <button type="button" onclick="addFeeRow()"
                                class="px-3 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition-colors flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i>
                            Add Fee
                        </button>
                    </div>

                    <div id="feeItemsContainer" class="space-y-3">
                        @if(!empty($feeItems))
                            @foreach($feeItems as $index => $item)
                            <div class="fee-row grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div class="md:col-span-4">
                                    <input type="text" name="fees[{{ $index }}][name]"
                                           value="{{ $item['name'] }}"
                                           placeholder="Fee name (e.g., Tuition)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                           required>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">KES</span>
                                        <input type="number" name="fees[{{ $index }}][amount]"
                                               value="{{ $item['amount'] }}"
                                               placeholder="0.00"
                                               step="0.01"
                                               min="0"
                                               class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                               required>
                                    </div>
                                </div>
                                <div class="md:col-span-4">
                                    <input type="text" name="fees[{{ $index }}][description]"
                                           value="{{ $item['description'] ?? '' }}"
                                           placeholder="Description (optional)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                                <div class="md:col-span-1 flex justify-end">
                                    <button type="button" onclick="removeFeeRow(this)"
                                            class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="fee-row grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div class="md:col-span-4">
                                    <input type="text" name="fees[0][name]"
                                           placeholder="Fee name (e.g., Tuition)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                           required>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">KES</span>
                                        <input type="number" name="fees[0][amount]"
                                               placeholder="0.00"
                                               step="0.01"
                                               min="0"
                                               class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                               required>
                                    </div>
                                </div>
                                <div class="md:col-span-4">
                                    <input type="text" name="fees[0][description]"
                                           placeholder="Description (optional)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                                <div class="md:col-span-1 flex justify-end">
                                    <button type="button" onclick="removeFeeRow(this)"
                                            class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Add all fee items that make up the total course cost.
                    </p>
                </div>

                <!-- Modification Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Modification Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="modification_reason" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Please explain why you are modifying the fee structure..."
                              required>{{ old('modification_reason') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        This will be recorded in the audit trail.
                    </p>
                </div>

                <!-- Current Total Preview -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Current Total:</span>
                        <span class="text-lg font-bold text-primary" id="totalPreview">KES 0.00</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-calculator mr-1"></i>
                        Total updates automatically as you add or modify fee items.
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('finance.fee-structure.show', $course) }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Fee Structure
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let feeIndex = {{ count($feeItems ?? []) }};

    function addFeeRow() {
        const container = document.getElementById('feeItemsContainer');
        const row = document.createElement('div');
        row.className = 'fee-row grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-gray-50 p-3 rounded-lg border border-gray-200';
        row.innerHTML = `
            <div class="md:col-span-4">
                <input type="text" name="fees[${feeIndex}][name]"
                       placeholder="Fee name (e.g., Tuition)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                       required>
            </div>
            <div class="md:col-span-3">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">KES</span>
                    <input type="number" name="fees[${feeIndex}][amount]"
                           placeholder="0.00"
                           step="0.01"
                           min="0"
                           class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                           required onchange="updateTotal()" onkeyup="updateTotal()">
                </div>
            </div>
            <div class="md:col-span-4">
                <input type="text" name="fees[${feeIndex}][description]"
                       placeholder="Description (optional)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
            <div class="md:col-span-1 flex justify-end">
                <button type="button" onclick="removeFeeRow(this)"
                        class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        feeIndex++;
        updateTotal();
    }

    function removeFeeRow(button) {
        const row = button.closest('.fee-row');
        if (document.querySelectorAll('.fee-row').length > 1) {
            row.remove();
            updateTotal();
        } else {
            alert('You must have at least one fee item.');
        }
    }

    function updateTotal() {
        const inputs = document.querySelectorAll('input[name$="[amount]"]');
        let total = 0;
        inputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        document.getElementById('totalPreview').textContent = 'KES ' + total.toFixed(2);
    }

    // Update total on page load
    document.addEventListener('DOMContentLoaded', updateTotal);
</script>
@endpush
