@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Invoice Items')
@section('subtitle', 'Manage line items for invoices')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Finance</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Invoices</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Invoice #{{ $invoice->invoice_number ?? 'Items' }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createItemModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Item</span>
    </button>
    <a href="{{ route('admin.tvet.invoices.show', $invoice ?? 0) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Invoice</span>
    </a>
</div>
@endsection

@section('content')
<!-- Invoice Summary Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-xl bg-primary-light flex items-center justify-center">
                    <i class="fas fa-file-invoice text-primary text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Invoice #{{ $invoice->invoice_number ?? 'N/A' }}</h2>
                    <p class="text-gray-600">{{ $invoice->description ?? 'Invoice items management' }}</p>
                </div>
            </div>
            @if(isset($invoice))
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($invoice->total_amount, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Balance</p>
                    <p class="text-2xl font-bold {{ $invoice->balance > 0 ? 'text-amber-600' : 'text-green-600' }}">
                        KES {{ number_format($invoice->balance, 2) }}
                    </p>
                </div>
                <div>
                    @php
                        $statusColors = [
                            'draft' => 'gray',
                            'sent' => 'blue',
                            'partial' => 'yellow',
                            'paid' => 'green',
                            'overdue' => 'red',
                            'cancelled' => 'gray',
                        ];
                        $color = $statusColors[$invoice->status] ?? 'gray';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                        <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Invoice Items</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $items->count() ?? 0 }} items found</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" id="tableSearch" placeholder="Search items..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
                @if(isset($invoice) && !in_array($invoice->status, ['paid', 'cancelled']))
                <button onclick="openBulkEditModal()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center space-x-2">
                    <i class="fas fa-pen-fancy"></i>
                    <span>Bulk Edit</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="itemsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Linked Fee</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($items as $index => $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6 text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->description }}</p>
                            @if($item->enrollment_fee_item_id)
                                <span class="text-xs text-blue-600 inline-flex items-center mt-1">
                                    <i class="fas fa-link mr-1"></i>
                                    Linked to fee item
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6 text-sm text-gray-900">{{ $item->quantity }}</td>
                    <td class="py-3 px-6 text-sm text-gray-900">KES {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3 px-6 text-sm {{ $item->discount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        @if($item->discount > 0)
                            KES {{ number_format($item->discount, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-6 text-sm {{ $item->tax > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                        @if($item->tax > 0)
                            KES {{ number_format($item->tax, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-bold text-gray-900">KES {{ number_format($item->total, 2) }}</span>
                    </td>
                    <td class="py-3 px-6">
                        @if($item->enrollment_fee_item_id)
                            <button onclick="viewFeeItem({{ $item->enrollment_fee_item_id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 underline">
                                View Fee Item
                            </button>
                        @else
                            <span class="text-xs text-gray-400">Not linked</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center space-x-2">
                            @if(!isset($invoice) || !in_array($invoice->status, ['paid', 'cancelled']))
                            <button onclick="editItem({{ $item->id }}, {{ $index }})"
                                    class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Edit Item">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="duplicateItem({{ $item->id }})"
                                    class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Duplicate Item">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button onclick="deleteItem({{ $item->id }})"
                                    class="p-2 text-gray-600 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Delete Item">
                                <i class="fas fa-trash"></i>
                            </button>
                            @else
                            <span class="text-xs text-gray-400">Locked</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No invoice items found</p>
                            <p class="text-gray-400 text-sm mt-1">Add items to this invoice</p>
                            @if(!isset($invoice) || !in_array($invoice->status, ['paid', 'cancelled']))
                            <button onclick="openModal('createItemModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add First Item
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($items->count() > 0)
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="6" class="py-4 px-6 text-right font-medium text-gray-700">Subtotal:</td>
                    <td class="py-4 px-6 font-bold text-gray-900" id="subtotal">KES {{ number_format($items->sum('total'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
                @if(isset($invoice))
                <tr>
                    <td colspan="6" class="py-2 px-6 text-right text-sm text-gray-600">Discount:</td>
                    <td class="py-2 px-6 text-sm font-medium text-red-600">KES {{ number_format($invoice->discount, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="6" class="py-2 px-6 text-right text-sm text-gray-600">Tax:</td>
                    <td class="py-2 px-6 text-sm font-medium text-amber-600">KES {{ number_format($invoice->tax, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
                <tr class="border-t border-gray-200">
                    <td colspan="6" class="py-4 px-6 text-right font-bold text-gray-800">Total:</td>
                    <td class="py-4 px-6 font-bold text-primary text-lg">KES {{ number_format($invoice->total_amount, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    <!-- Pagination if needed -->
    @if(isset($items) && $items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $items->firstItem() }}</span> to
                <span class="font-medium">{{ $items->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($items->total()) }}</span> items
            </div>
            <div class="flex items-center space-x-2">
                {{ $items->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Create Item Modal -->
<div id="createItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Add Invoice Item</h3>
                        <p class="text-sm text-gray-600">Add a new line item to the invoice</p>
                    </div>
                    <button onclick="closeModal('createItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createItemForm" method="POST" action="{{ route('admin.tvet.invoices.items.store', $invoice ?? 0) }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Link to Fee Item (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Link to Enrollment Fee Item (Optional)
                            </label>
                            <select name="enrollment_fee_item_id" id="enrollment_fee_item_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">-- No Link --</option>
                                @if(isset($invoice) && $invoice->enrollment)
                                    @foreach($invoice->enrollment->feeItems as $feeItem)
                                        <option value="{{ $feeItem->id }}">
                                            {{ $feeItem->item_name }} - KES {{ number_format($feeItem->amount, 2) }} x {{ $feeItem->quantity }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Link to an existing enrollment fee item</p>
                        </div>

                        <!-- Item Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Description</label>
                            <input type="text" name="description" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Tuition Fee - Term 1"
                                   onchange="updateFromFeeItem()">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Quantity</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" step="1" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       onchange="calculateItemTotal()">
                            </div>

                            <!-- Unit Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">Unit Price (KES)</label>
                                <input type="number" name="unit_price" id="unit_price" value="0" min="0" step="0.01" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       onchange="calculateItemTotal()">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Discount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Discount (KES)</label>
                                <input type="number" name="discount" id="discount" value="0" min="0" step="0.01"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       onchange="calculateItemTotal()">
                            </div>

                            <!-- Tax -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax (KES)</label>
                                <input type="number" name="tax" id="tax" value="0" min="0" step="0.01"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       onchange="calculateItemTotal()">
                            </div>
                        </div>

                        <!-- Total Preview -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Line Total:</span>
                                <span class="text-lg font-bold text-primary" id="item_total_preview">KES 0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-gray-500 mt-1">
                                <span>Subtotal: <span id="subtotal_preview">0.00</span></span>
                                <span>Discount: <span id="discount_preview">0.00</span></span>
                                <span>Tax: <span id="tax_preview">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('createItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="submitCreateForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Add Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Edit Invoice Item</h3>
                        <p class="text-sm text-gray-600">Update line item details</p>
                    </div>
                    <button onclick="closeModal('editItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="editFormContent"></div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('editItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitEditForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulkEditModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkEditModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Bulk Edit Items</h3>
                        <p class="text-sm text-gray-600">Update multiple items at once</p>
                    </div>
                    <button onclick="closeModal('bulkEditModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="bulkEditForm" method="POST" action="{{ route('admin.tvet.invoices.items.bulk.update', $invoice ?? 0) }}">
                    @csrf
                    <div class="space-y-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Item</th>
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Quantity</th>
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Unit Price</th>
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Discount</th>
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Tax</th>
                                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkEditItemsList">
                                    <!-- Items will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Bulk Actions</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button type="button" onclick="applyToAll('discount', 0)"
                                        class="px-3 py-1 bg-white border border-blue-300 text-blue-700 rounded-lg text-sm hover:bg-blue-50">
                                    Clear Discounts
                                </button>
                                <button type="button" onclick="applyToAll('tax', 0)"
                                        class="px-3 py-1 bg-white border border-blue-300 text-blue-700 rounded-lg text-sm hover:bg-blue-50">
                                    Clear Taxes
                                </button>
                                <button type="button" onclick="applyToAll('quantity', 1)"
                                        class="px-3 py-1 bg-white border border-blue-300 text-blue-700 rounded-lg text-sm hover:bg-blue-50">
                                    Set Qty to 1
                                </button>
                                <button type="button" onclick="recalculateAll()"
                                        class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                                    Recalculate All
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('bulkEditModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkEdit()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update All
                </button>
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Invoice Item</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this invoice item?
                    </p>
                </div>
                <form id="deleteForm" method="POST">
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
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
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
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickSearch();
        initializeFeeItemLink();
    });

    // ============ TABLE FUNCTIONS ============
    function refreshTable() {
        location.reload();
    }

    function initializeQuickSearch() {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#itemsTable tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    }

    // ============ FEE ITEM LINK ============
    function initializeFeeItemLink() {
        const feeItemSelect = document.getElementById('enrollment_fee_item_id');
        if (feeItemSelect) {
            feeItemSelect.addEventListener('change', updateFromFeeItem);
        }
    }

    function updateFromFeeItem() {
        const select = document.getElementById('enrollment_fee_item_id');
        const description = document.querySelector('input[name="description"]');
        const quantity = document.getElementById('quantity');
        const unitPrice = document.getElementById('unit_price');

        if (select && select.selectedIndex > 0) {
            const selected = select.options[select.selectedIndex];
            // This would need data attributes on options to auto-fill
            // For now, just a placeholder
            console.log('Selected fee item:', selected.value);
        }
    }

    // ============ ITEM CALCULATIONS ============
    function calculateItemTotal() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;

        const subtotal = quantity * unitPrice;
        const total = subtotal - discount + tax;

        document.getElementById('subtotal_preview').textContent = subtotal.toFixed(2);
        document.getElementById('discount_preview').textContent = discount.toFixed(2);
        document.getElementById('tax_preview').textContent = tax.toFixed(2);
        document.getElementById('item_total_preview').textContent = 'KES ' + total.toFixed(2);
    }

    // ============ ITEM ACTIONS ============
    function editItem(itemId, index) {
        fetch(`/admin/tvet/invoices/items/${itemId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editFormContent').innerHTML = html;
                openModal('editItemModal');
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                alert('Failed to load edit form');
            });
    }

    function submitEditForm() {
        const form = document.getElementById('editForm');
        if (form) {
            form.submit();
        }
    }

    function duplicateItem(itemId) {
        if (confirm('Duplicate this item?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tvet/invoices/items/${itemId}/duplicate`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteItem(itemId) {
        document.getElementById('deleteForm').action = `/admin/tvet/invoices/items/${itemId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    function viewFeeItem(feeItemId) {
        // This would navigate to the enrollment fee item view
        window.location.href = `/admin/tvet/enrollments/fee-items/${feeItemId}`;
    }

    // ============ CREATE FORM ============
    function submitCreateForm() {
        document.getElementById('createItemForm').submit();
    }

    // ============ BULK EDIT ============
    function openBulkEditModal() {
        // Populate bulk edit items
        const itemsList = document.getElementById('bulkEditItemsList');
        itemsList.innerHTML = '';

        @foreach($items as $index => $item)
        itemsList.innerHTML += `
            <tr class="border-b">
                <td class="py-2 px-4">
                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    <div class="text-sm">{{ $item->description }}</div>
                </td>
                <td class="py-2 px-4">
                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1"
                           class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                           onchange="updateBulkItemTotal(this, {{ $index }})">
                </td>
                <td class="py-2 px-4">
                    <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="0.01"
                           class="w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                           onchange="updateBulkItemTotal(this, {{ $index }})">
                </td>
                <td class="py-2 px-4">
                    <input type="number" name="items[{{ $index }}][discount]" value="{{ $item->discount }}" min="0" step="0.01"
                           class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                           onchange="updateBulkItemTotal(this, {{ $index }})">
                </td>
                <td class="py-2 px-4">
                    <input type="number" name="items[{{ $index }}][tax]" value="{{ $item->tax }}" min="0" step="0.01"
                           class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                           onchange="updateBulkItemTotal(this, {{ $index }})">
                </td>
                <td class="py-2 px-4">
                    <span class="text-sm font-medium" id="bulk-total-{{ $index }}">
                        {{ number_format($item->total, 2) }}
                    </span>
                </td>
            </tr>
        `;
        @endforeach

        openModal('bulkEditModal');
    }

    function updateBulkItemTotal(element, index) {
        const row = element.closest('tr');
        const quantity = parseFloat(row.querySelector('input[name$="[quantity]"]').value) || 0;
        const unitPrice = parseFloat(row.querySelector('input[name$="[unit_price]"]').value) || 0;
        const discount = parseFloat(row.querySelector('input[name$="[discount]"]').value) || 0;
        const tax = parseFloat(row.querySelector('input[name$="[tax]"]').value) || 0;

        const subtotal = quantity * unitPrice;
        const total = subtotal - discount + tax;

        document.getElementById(`bulk-total-${index}`).textContent = total.toFixed(2);
    }

    function applyToAll(field, value) {
        const inputs = document.querySelectorAll(`input[name$="[${field}]"]`);
        inputs.forEach(input => {
            input.value = value;
            // Trigger change event to update totals
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        });
    }

    function recalculateAll() {
        @foreach($items as $index => $item)
            const row{{ $index }} = document.querySelector(`input[name="items[{{ $index }}][quantity]"]`).closest('tr');
            const qty{{ $index }} = parseFloat(row{{ $index }}.querySelector('input[name$="[quantity]"]').value) || 0;
            const price{{ $index }} = parseFloat(row{{ $index }}.querySelector('input[name$="[unit_price]"]').value) || 0;
            const disc{{ $index }} = parseFloat(row{{ $index }}.querySelector('input[name$="[discount]"]').value) || 0;
            const tax{{ $index }} = parseFloat(row{{ $index }}.querySelector('input[name$="[tax]"]').value) || 0;

            const subtotal{{ $index }} = qty{{ $index }} * price{{ $index }};
            const total{{ $index }} = subtotal{{ $index }} - disc{{ $index }} + tax{{ $index }};

            document.getElementById(`bulk-total-{{ $index }}`).textContent = total{{ $index }}.toFixed(2);
        @endforeach
    }

    function submitBulkEdit() {
        document.getElementById('bulkEditForm').submit();
    }

    // ============ MODAL FUNCTIONS ============
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';

        if (modalId === 'editItemModal') {
            document.getElementById('editFormContent').innerHTML = '';
        }
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
    #itemsTable {
        min-width: 1200px;
    }

    @media (max-width: 768px) {
        #itemsTable {
            min-width: 100%;
        }
    }

    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
