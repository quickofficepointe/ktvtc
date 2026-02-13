@extends('ktvtc.admin.layout.adminlayout')

@section('title', $feeCategory->name)
@section('subtitle', 'Fee category details and usage')

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
    <a href="{{ route('admin.tvet.fee-categories.edit', $feeCategory) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Category</span>
    </a>
    <a href="{{ route('admin.tvet.fee-categories.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Categories</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="relative h-32 bg-gradient-to-r from-primary/20 to-primary/5">
        <div class="absolute -bottom-10 left-6 flex items-end space-x-6">
            <div class="w-24 h-24 rounded-xl bg-white shadow-lg flex items-center justify-center"
                 style="background-color: {{ $feeCategory->color ?? '#3B82F6' }}10">
                <i class="fas {{ $feeCategory->icon ?? 'fa-tag' }} text-4xl" style="color: {{ $feeCategory->color ?? '#3B82F6' }}"></i>
            </div>
            <div class="mb-2">
                <h1 class="text-2xl font-bold text-gray-800">{{ $feeCategory->name }}</h1>
                <div class="flex items-center mt-2 space-x-3">
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono text-gray-700">
                        {{ $feeCategory->code }}
                    </span>
                    @if($feeCategory->campus)
                        <span class="px-3 py-1 bg-blue-100 rounded-lg text-sm text-blue-700">
                            <i class="fas fa-building mr-1"></i> {{ $feeCategory->campus->name }}
                        </span>
                    @else
                        <span class="px-3 py-1 bg-purple-100 rounded-lg text-sm text-purple-700">
                            <i class="fas fa-globe mr-1"></i> Global Category
                        </span>
                    @endif
                    @php
                        $statusColor = $feeCategory->is_active ? 'green' : 'gray';
                        $statusText = $feeCategory->is_active ? 'Active' : 'Inactive';
                    @endphp
                    <span class="px-3 py-1 bg-{{ $statusColor }}-100 rounded-lg text-sm text-{{ $statusColor }}-700">
                        <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i> {{ $statusText }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Category Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Description Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Description
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-700 leading-relaxed">
                    {{ $feeCategory->description ?? 'No description provided.' }}
                </p>
            </div>
        </div>

        <!-- Fee Properties Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-cog text-primary mr-2"></i>
                    Fee Properties
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Frequency</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $feeCategory->frequency_label }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Sort Order</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $feeCategory->sort_order ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Mandatory</p>
                        <p class="text-lg font-semibold">
                            @if($feeCategory->is_mandatory)
                                <span class="text-red-600">Yes</span>
                            @else
                                <span class="text-gray-600">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Refundable</p>
                        <p class="text-lg font-semibold">
                            @if($feeCategory->is_refundable)
                                <span class="text-purple-600">Yes</span>
                            @else
                                <span class="text-gray-600">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Taxable</p>
                        <p class="text-lg font-semibold">
                            @if($feeCategory->is_taxable)
                                <span class="text-amber-600">Yes</span>
                            @else
                                <span class="text-gray-600">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Created</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feeCategory->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feeCategory->updated_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Created By</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feeCategory->creator->name ?? 'System' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggested Items Card -->
        @if($feeCategory->suggested_items && count($feeCategory->suggested_items) > 0)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list text-primary mr-2"></i>
                    Suggested Items
                </h3>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap gap-2">
                    @foreach($feeCategory->suggested_items as $item)
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">
                            {{ $item }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Usage in Templates -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice text-primary mr-2"></i>
                        Used in Fee Templates
                    </h3>
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm text-gray-700">
                        {{ $feeCategory->templateItems->count() }} items
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terms</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($feeCategory->templateItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.tvet.course-fee-templates.show', $item->feeTemplate) }}"
                                   class="text-primary hover:underline">
                                    {{ $item->feeTemplate->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $item->feeTemplate->course->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-lg
                                    @if($item->feeTemplate->exam_type == 'nita') bg-blue-100 text-blue-700
                                    @elseif($item->feeTemplate->exam_type == 'cdacc') bg-green-100 text-green-700
                                    @elseif($item->feeTemplate->exam_type == 'school_assessment') bg-amber-100 text-amber-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ strtoupper($item->feeTemplate->exam_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $item->item_name }}</td>
                            <td class="px-6 py-4 text-sm">KES {{ number_format($item->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ $item->term_label }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-file-invoice text-gray-300 text-3xl mb-2"></i>
                                <p>Not used in any fee templates yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column - Statistics -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Usage Statistics Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    Usage Statistics
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Template Items</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $feeCategory->templateItems->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary rounded-full h-2"
                                 style="width: {{ min($feeCategory->templateItems->count() * 10, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Enrollment Items</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $feeCategory->enrollmentFeeItems->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 rounded-full h-2"
                                 style="width: {{ min($feeCategory->enrollmentFeeItems->count() * 5, 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800">{{ $feeCategory->templateItems->count() }}</p>
                            <p class="text-xs text-gray-500">Templates</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800">{{ $feeCategory->enrollmentFeeItems->count() }}</p>
                            <p class="text-xs text-gray-500">Enrollments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-bolt text-primary mr-2"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <a href="{{ route('admin.tvet.course-fee-templates.create') }}?category_id={{ $feeCategory->id }}"
                       class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="text-sm font-medium text-gray-700">Add to Fee Template</span>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>

                    @if($feeCategory->is_active)
                    <button onclick="updateStatus('{{ $feeCategory->id }}', 'deactivate')"
                            class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="text-sm font-medium text-yellow-600">Deactivate Category</span>
                        <i class="fas fa-pause-circle text-yellow-600"></i>
                    </button>
                    @else
                    <button onclick="updateStatus('{{ $feeCategory->id }}', 'activate')"
                            class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="text-sm font-medium text-green-600">Activate Category</span>
                        <i class="fas fa-check-circle text-green-600"></i>
                    </button>
                    @endif

                    <button onclick="deleteCategory('{{ $feeCategory->id }}')"
                            class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="text-sm font-medium text-red-600">Delete Category</span>
                        <i class="fas fa-trash text-red-600"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Audit Trail Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Audit Trail
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-plus text-blue-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Created</p>
                            <p class="text-xs text-gray-500">{{ $feeCategory->created_at->format('M d, Y \a\t h:i A') }}</p>
                            @if($feeCategory->creator)
                            <p class="text-xs text-gray-500 mt-1">by {{ $feeCategory->creator->name }}</p>
                            @endif
                        </div>
                    </div>

                    @if($feeCategory->created_at != $feeCategory->updated_at)
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-pen text-amber-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Last Updated</p>
                            <p class="text-xs text-gray-500">{{ $feeCategory->updated_at->format('M d, Y \a\t h:i A') }}</p>
                            @if($feeCategory->updater)
                            <p class="text-xs text-gray-500 mt-1">by {{ $feeCategory->updater->name }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Fee Category</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600">
                        Are you sure you want to delete <span class="font-semibold">{{ $feeCategory->name }}</span>?
                    </p>
                    @if($feeCategory->templateItems->count() > 0 || $feeCategory->enrollmentFeeItems->count() > 0)
                        <p class="text-center text-sm text-red-600 mt-2">
                            This category is used in {{ $feeCategory->templateItems->count() }} template(s) and
                            {{ $feeCategory->enrollmentFeeItems->count() }} enrollment(s) and cannot be deleted.
                        </p>
                    @endif
                </div>
                <form id="deleteForm" method="POST" action="{{ route('admin.tvet.fee-categories.destroy', $feeCategory) }}">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitDeleteForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center"
                        @if($feeCategory->templateItems->count() > 0 || $feeCategory->enrollmentFeeItems->count() > 0) disabled @endif>
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ ACTIONS ============
    function updateStatus(categoryId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/fee-categories/${categoryId}/activate`
            : `/admin/tvet/fee-categories/${categoryId}/deactivate`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteCategory(categoryId) {
        @if($feeCategory->templateItems->count() > 0 || $feeCategory->enrollmentFeeItems->count() > 0)
            alert('This category cannot be deleted because it is being used in fee templates or enrollments.');
        @else
            openModal('deleteModal');
        @endif
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
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
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    button[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection
