@extends('ktvtc.cafeteria.layout.cafeterialayout')


@section('title', 'Inventory Management')
@section('page-title', 'Inventory Management')
@section('page-description', 'Track stock levels and movements')

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stocks->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">In Stock</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stocks->where('available_stock', '>', 0)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stocks->where('low_stock_alert', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center mr-4">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stocks->where('out_of_stock_alert', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-3">
            <button onclick="showAdjustmentModal()"
                    class="bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-primary-dark transition">
                <i class="fas fa-adjust mr-2"></i> Stock Adjustment
            </button>
            <button onclick="showTransferModal()"
                    class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-green-700 transition">
                <i class="fas fa-truck-moving mr-2"></i> Stock Transfer
            </button>
            <button onclick="exportData()"
                    class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-50 transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <div class="px-6 pt-4">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button onclick="switchTab('stocks')" id="stocks-tab"
                            class="px-3 py-2 font-medium text-sm rounded-md bg-primary text-white">
                        <i class="fas fa-boxes mr-2"></i> Stock Levels
                    </button>
                    <button onclick="switchTab('movements')" id="movements-tab"
                            class="px-3 py-2 font-medium text-sm rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-exchange-alt mr-2"></i> Stock Movements
                    </button>
                </nav>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200">
            <div id="stocks-filters" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <select id="stock-shop-filter" class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>

                    <select id="stock-product-filter" class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>

                    <input type="text" id="stock-search" placeholder="Search products..."
                           class="border border-gray-300 rounded-lg py-2 px-3 w-full">

                    <button onclick="applyStockFilters()"
                            class="bg-primary text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </div>

            <div id="movements-filters" class="space-y-4 hidden">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <select id="movement-shop-filter" class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>

                    <select id="movement-type-filter" class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                        <option value="">All Types</option>
                        @foreach($movementTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" id="movement-start-date"
                               class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                        <input type="date" id="movement-end-date"
                               class="border border-gray-300 rounded-lg py-2 px-3 w-full">
                    </div>

                    <button onclick="applyMovementFilters()"
                            class="bg-primary text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <div id="stocks-content">
                <!-- Stocks Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Movement</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="stocks-table-body">
                            @foreach($stocks as $stock)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="fas fa-box text-gray-500"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $stock->product->product_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $stock->product->product_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $stock->shop->shop_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $stock->shop->shop_code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $stock->current_stock }} {{ $stock->product->unit }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ $stock->available_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $stock->available_stock }} {{ $stock->product->unit }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">KES {{ number_format($stock->stock_value, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($stock->out_of_stock_alert)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                    @elseif($stock->low_stock_alert)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        Low Stock
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        In Stock
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $stock->last_movement_at ? $stock->last_movement_at->format('M d, Y') : 'Never' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($stocks->hasPages())
                <div class="mt-4">
                    {{ $stocks->links() }}
                </div>
                @endif
            </div>

            <div id="movements-content" class="hidden">
                <!-- Movements Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="movements-table-body">
                            @foreach($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->movement_date->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->movement_date->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $movement->product->product_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $movement->product->product_code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->shop->shop_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'purchase' => 'bg-green-100 text-green-800',
                                            'sale' => 'bg-blue-100 text-blue-800',
                                            'transfer_in' => 'bg-purple-100 text-purple-800',
                                            'transfer_out' => 'bg-purple-100 text-purple-800',
                                            'adjustment_in' => 'bg-yellow-100 text-yellow-800',
                                            'adjustment_out' => 'bg-red-100 text-red-800',
                                            'production_in' => 'bg-indigo-100 text-indigo-800',
                                            'production_usage' => 'bg-indigo-100 text-indigo-800',
                                            'wastage' => 'bg-red-100 text-red-800',
                                            'damaged' => 'bg-red-100 text-red-800',
                                            'return_in' => 'bg-green-100 text-green-800',
                                            'return_out' => 'bg-red-100 text-red-800',
                                        ];
                                        $typeColor = $typeColors[$movement->movement_type] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColor }}">
                                        {{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ in_array($movement->movement_type, ['purchase', 'transfer_in', 'adjustment_in', 'production_in', 'return_in']) ? 'text-green-600' : 'text-red-600' }}">
                                        {{ in_array($movement->movement_type, ['purchase', 'transfer_in', 'adjustment_in', 'production_in', 'return_in']) ? '+' : '-' }}
                                        {{ $movement->quantity }} {{ $movement->product->unit }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $movement->previous_stock }} → {{ $movement->new_stock }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">KES {{ number_format($movement->total_cost, 2) }}</div>
                                    <div class="text-xs text-gray-500">KES {{ number_format($movement->unit_cost, 2) }}/unit</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->reference_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->reason }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement->recorder->name ?? 'System' }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($movements->hasPages())
                <div class="mt-4">
                    {{ $movements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="adjustmentModal">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-4xl rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-xl font-bold">Stock Adjustment</h3>
                <button onclick="closeModal('#adjustmentModal')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="adjustmentForm" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Shop <span class="text-red-500">*</span>
                        </label>
                        <select id="adjustment_shop_id" name="shop_id" class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Adjustment Type <span class="text-red-500">*</span>
                        </label>
                        <select id="adjustment_type" name="adjustment_type" class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Type</option>
                            @foreach($adjustmentCategories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="adjustment_date" name="adjustment_date"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reason <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="adjustment_reason" name="reason"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="adjustment_notes" name="notes" rows="2"
                              class="w-full border border-gray-300 rounded-lg py-2 px-3"></textarea>
                </div>

                <!-- Adjustment Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700">Adjustment Items</h4>
                        <button type="button" onclick="addAdjustmentItem()"
                                class="text-primary hover:text-primary-dark">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                    <div id="adjustmentItemsContainer" class="space-y-4">
                        <!-- Items will be added here -->
                    </div>
                </div>
            </form>
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3 sticky bottom-0 z-10">
                <button type="button" onclick="closeModal('#adjustmentModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg">
                    Cancel
                </button>
                <button type="button" onclick="submitAdjustment()"
                        class="bg-primary text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Save Adjustment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Transfer Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="transferModal">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-4xl rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="modal-header bg-green-600 text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-xl font-bold">Stock Transfer</h3>
                <button onclick="closeModal('#transferModal')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="transferForm" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            From Shop <span class="text-red-500">*</span>
                        </label>
                        <select id="from_shop_id" name="from_shop_id" class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            To Shop <span class="text-red-500">*</span>
                        </label>
                        <select id="to_shop_id" name="to_shop_id" class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="transfer_date" name="transfer_date"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reason <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="transfer_reason" name="transfer_reason"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="transfer_notes" name="notes" rows="2"
                              class="w-full border border-gray-300 rounded-lg py-2 px-3"></textarea>
                </div>

                <!-- Transfer Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700">Transfer Items</h4>
                        <button type="button" onclick="addTransferItem()"
                                class="text-green-600 hover:text-green-700">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                    <div id="transferItemsContainer" class="space-y-4">
                        <!-- Items will be added here -->
                    </div>
                </div>
            </form>
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3 sticky bottom-0 z-10">
                <button type="button" onclick="closeModal('#transferModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg">
                    Cancel
                </button>
                <button type="button" onclick="submitTransfer()"
                        class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Transfer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const products = @json($products);

    // Initialize date fields
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('adjustment_date').value = today;
        document.getElementById('transfer_date').value = today;

        // Set default end date for movements filter
        document.getElementById('movement-start-date').value = today;
        document.getElementById('movement-end-date').value = today;
    });

    // Tab switching
    function switchTab(tab) {
        if (tab === 'stocks') {
            document.getElementById('stocks-tab').classList.add('bg-primary', 'text-white');
            document.getElementById('movements-tab').classList.remove('bg-primary', 'text-white');
            document.getElementById('movements-tab').classList.add('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-100');

            document.getElementById('stocks-content').classList.remove('hidden');
            document.getElementById('movements-content').classList.add('hidden');
            document.getElementById('stocks-filters').classList.remove('hidden');
            document.getElementById('movements-filters').classList.add('hidden');
        } else {
            document.getElementById('movements-tab').classList.add('bg-primary', 'text-white');
            document.getElementById('stocks-tab').classList.remove('bg-primary', 'text-white');
            document.getElementById('stocks-tab').classList.add('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-100');

            document.getElementById('movements-content').classList.remove('hidden');
            document.getElementById('stocks-content').classList.add('hidden');
            document.getElementById('movements-filters').classList.remove('hidden');
            document.getElementById('stocks-filters').classList.add('hidden');
        }
    }

    // Filter functions
    async function applyStockFilters() {
        const shopId = document.getElementById('stock-shop-filter').value;
        const productId = document.getElementById('stock-product-filter').value;
        const search = document.getElementById('stock-search').value;

        try {
            const response = await fetch(`/cafeteria/inventory?shop_id=${shopId}&product_id=${productId}&search=${search}`);
            window.location.href = `/cafeteria/inventory?shop_id=${shopId}&product_id=${productId}&search=${search}`;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function applyMovementFilters() {
        const shopId = document.getElementById('movement-shop-filter').value;
        const type = document.getElementById('movement-type-filter').value;
        const startDate = document.getElementById('movement-start-date').value;
        const endDate = document.getElementById('movement-end-date').value;

        try {
            window.location.href = `/cafeteria/inventory?movement_shop_id=${shopId}&movement_type=${type}&movement_start_date=${startDate}&movement_end_date=${endDate}`;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Modal functions
    function showAdjustmentModal() {
        document.getElementById('adjustmentModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        addAdjustmentItem();
    }

    function showTransferModal() {
        document.getElementById('transferModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        addTransferItem();
    }

    function closeModal(modalId) {
        document.querySelector(modalId).classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Template functions
    function getAdjustmentItemTemplate(index = 0) {
        const productOptions = products.map(product =>
            `<option value="${product.id}">${product.product_code} - ${product.product_name} (${product.unit})</option>`
        ).join('');

        return `
            <div class="border border-gray-200 rounded-lg p-4 adjustment-item" data-index="${index}">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium text-gray-700">Item #${index + 1}</h5>
                    <button type="button" onclick="removeAdjustmentItem(${index})"
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${index}][product_id]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Product</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="items[${index}][quantity]" step="0.001" min="0.001"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Unit Cost
                        </label>
                        <input type="number" name="items[${index}][unit_cost]" step="0.01" min="0"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Adjustment <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${index}][adjustment_direction]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="in">Add to Stock</option>
                            <option value="out">Remove from Stock</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <input type="hidden" name="items[${index}][unit]" value="">
                </div>
            </div>
        `;
    }

    function getTransferItemTemplate(index = 0) {
        const productOptions = products.map(product =>
            `<option value="${product.id}">${product.product_code} - ${product.product_name} (${product.unit})</option>`
        ).join('');

        return `
            <div class="border border-gray-200 rounded-lg p-4 transfer-item" data-index="${index}">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium text-gray-700">Item #${index + 1}</h5>
                    <button type="button" onclick="removeTransferItem(${index})"
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${index}][product_id]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                            <option value="">Select Product</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="items[${index}][quantity]" step="0.001" min="0.001"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Unit Cost
                        </label>
                        <input type="number" name="items[${index}][unit_cost]" step="0.01" min="0"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3">
                    </div>
                </div>
                <div class="mt-3">
                    <input type="hidden" name="items[${index}][unit]" value="">
                </div>
            </div>
        `;
    }

    // Item management functions
    function addAdjustmentItem() {
        const container = document.getElementById('adjustmentItemsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getAdjustmentItemTemplate(index));
    }

    function addTransferItem() {
        const container = document.getElementById('transferItemsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getTransferItemTemplate(index));
    }

    function removeAdjustmentItem(index) {
        const item = document.querySelector(`.adjustment-item[data-index="${index}"]`);
        if (item) item.remove();
        reindexAdjustmentItems();
    }

    function removeTransferItem(index) {
        const item = document.querySelector(`.transfer-item[data-index="${index}"]`);
        if (item) item.remove();
        reindexTransferItems();
    }

    function reindexAdjustmentItems() {
        const items = document.querySelectorAll('.adjustment-item');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
            item.querySelector('h5').textContent = `Item #${index + 1}`;

            // Update input names
            const inputs = item.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.setAttribute('name', newName);
            });
        });
    }

    function reindexTransferItems() {
        const items = document.querySelectorAll('.transfer-item');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
            item.querySelector('h5').textContent = `Item #${index + 1}`;

            // Update input names
            const inputs = item.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.setAttribute('name', newName);
            });
        });
    }

    // Form submission
    async function submitAdjustment() {
        const form = document.getElementById('adjustmentForm');
        const formData = new FormData(form);

        // Convert to JSON
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                const match = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const [, arrayName, index, field] = match;
                    if (!data[arrayName]) data[arrayName] = [];
                    if (!data[arrayName][index]) data[arrayName][index] = {};
                    data[arrayName][index][field] = value;
                }
            } else {
                data[key] = value;
            }
        }

        if (data.items) {
            data.items = Object.values(data.items);
        }

        try {
            const response = await fetch('{{ route("cafeteria.inventory.adjustment.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert('Adjustment saved successfully!');
                closeModal('#adjustmentModal');
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred: ' + error.message);
        }
    }

    async function submitTransfer() {
        const form = document.getElementById('transferForm');
        const formData = new FormData(form);

        // Convert to JSON
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                const match = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const [, arrayName, index, field] = match;
                    if (!data[arrayName]) data[arrayName] = [];
                    if (!data[arrayName][index]) data[arrayName][index] = {};
                    data[arrayName][index][field] = value;
                }
            } else {
                data[key] = value;
            }
        }

        if (data.items) {
            data.items = Object.values(data.items);
        }

        try {
            const response = await fetch('{{ route("cafeteria.inventory.transfer.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert('Transfer completed successfully!');
                closeModal('#transferModal');
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred: ' + error.message);
        }
    }

    // Export function
    function exportData() {
        alert('Export functionality will be implemented soon!');
    }
</script>
@endsection
