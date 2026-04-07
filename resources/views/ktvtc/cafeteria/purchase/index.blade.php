@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Order Management')
@section('page-description', 'Create and manage purchase orders')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">Procurement</span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">Purchase Orders</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-file-invoice-dollar text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending Approval</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $approvedCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-truck text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Ordered</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $orderedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search PO or supplier..." value="{{ request('search') }}" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>
                <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <div class="flex gap-2">
                    <input type="date" id="fromDate" value="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                    <input type="date" id="toDate" value="{{ request('to_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="exportPurchaseOrders()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>
                <button onclick="openPOModal()" class="bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-red-700">
                    <i class="fas fa-plus mr-2"></i> New Purchase Order
                </button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <nav class="flex overflow-x-auto">
            <button onclick="filterByStatus('')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ !request('status') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Orders <span class="ml-2 bg-gray-100 text-gray-900 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $totalOrders }}</span>
            </button>
            <button onclick="filterByStatus('draft')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'draft' ? 'border-gray-400 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Draft <span class="ml-2 bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $draftCount }}</span>
            </button>
            <button onclick="filterByStatus('pending_approval')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'pending_approval' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending Approval <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
            </button>
            <button onclick="filterByStatus('approved')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Approved <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $approvedCount }}</span>
            </button>
            <button onclick="filterByStatus('ordered')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'ordered' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Ordered <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $orderedCount }}</span>
            </button>
        </nav>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $po->po_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $po->supplier->supplier_name }}</div>
                            <div class="text-sm text-gray-500">{{ $po->supplier->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $po->businessSection->section_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $po->order_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($po->expected_delivery_date)
                                {{ $po->expected_delivery_date->format('M d, Y') }}
                                @if($po->expected_delivery_date < now() && $po->status == 'ordered')
                                <span class="ml-2 text-xs text-red-600 font-medium">Overdue</span>
                                @endif
                            @else
                                <span class="text-gray-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $po->total_items }} items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-lg text-primary">KES {{ number_format($po->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$po->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewPurchaseOrder({{ $po->id }})" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" title="View Details"><i class="fas fa-eye"></i></button>
                                @if($po->status == 'draft')
                                <button onclick="editPurchaseOrder({{ $po->id }})" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Edit"><i class="fas fa-edit"></i></button>
                                <button onclick="submitForApproval({{ $po->id }})" class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50" title="Submit for Approval"><i class="fas fa-paper-plane"></i></button>
                                @endif
                                @if($po->status == 'pending_approval')
                                <button onclick="approvePurchaseOrder({{ $po->id }})" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Approve"><i class="fas fa-check"></i></button>
                                @endif
                                <button onclick="printPurchaseOrder({{ $po->id }})" class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" title="Print"><i class="fas fa-print"></i></button>
                                @if($po->status == 'draft')
                                <button onclick="confirmDeletePO({{ $po->id }})" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-700">No purchase orders found</p>
                                <button onclick="openPOModal()" class="mt-4 bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-red-700">
                                    <i class="fas fa-plus mr-2"></i> Create Purchase Order
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $purchaseOrders->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create/Edit Purchase Order Modal -->
<div id="poModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="poModalTitle" class="text-xl font-bold text-gray-900">Create Purchase Order</h3>
                <button onclick="closePOModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="poForm" class="p-6">
            @csrf
            <input type="hidden" id="po_id" name="id">

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                            <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Section *</label>
                            <select id="business_section_id" name="business_section_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Business Section</option>
                                @foreach($businessSections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }} ({{ $section->section_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shop *</label>
                            <select id="shop_id" name="shop_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Shop</option>
                                @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->name ?? 'Shop #' . $shop->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Date *</label>
                            <input type="date" id="order_date" name="order_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expected Delivery Date</label>
                            <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
                            <select id="delivery_method" name="delivery_method" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Method</option>
                                <option value="pickup">Pickup</option>
                                <option value="supplier_delivery">Supplier Delivery</option>
                                <option value="courier">Courier</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                        <textarea id="delivery_address" name="delivery_address" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Order Items</h4>
                        <div class="flex gap-2">
                            <button type="button" onclick="showCreateProductModal()" class="bg-green-600 text-white py-2 px-4 rounded-lg flex items-center text-sm hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i> New Product
                            </button>
                            <button type="button" onclick="addItemRow()" class="bg-primary text-white py-2 px-4 rounded-lg flex items-center text-sm hover:bg-red-700">
                                <i class="fas fa-shopping-cart mr-2"></i> Add Item
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr id="noItemsRow">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-2"></i>
                                        <p>No items added yet</p>
                                        <div class="mt-3 flex justify-center gap-3">
                                            <button type="button" onclick="addItemRow()" class="text-primary hover:text-primary-dark font-medium">Add existing product</button>
                                            <button type="button" onclick="showCreateProductModal()" class="text-green-600 hover:text-green-700 font-medium">Create new product</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="itemsTableFooter" class="bg-gray-50 hidden">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                                    <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-900" id="subtotalAmount">KES 0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Tax:</td>
                                    <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-900" id="taxAmount">KES 0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total Amount:</td>
                                    <td colspan="3" class="px-4 py-3 text-lg font-bold text-primary" id="totalAmount">KES 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                            <textarea id="terms_conditions" name="terms_conditions" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-between">
                <button type="button" onclick="saveAsDraft()" id="saveDraftBtn" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Save as Draft</button>
                <div class="flex space-x-3">
                    <button type="button" onclick="closePOModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                    <button type="button" onclick="savePurchaseOrder()" id="savePOBtn" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 font-medium">Save Purchase Order</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Product Modal (Quick Add) -->
<div id="createProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Quick Add Product</h3>
                <button onclick="closeCreateProductModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="quickProductForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="quick_product_name" name="product_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Code *</label>
                        <input type="text" id="quick_product_code" name="product_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Auto-generated if empty">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Section *</label>
                        <select id="quick_business_section_id" name="business_section_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            <option value="">Select Business Section</option>
                            @foreach($businessSections as $section)
                            <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                        <select id="quick_unit" name="unit" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            <option value="">Select Unit</option>
                            <option value="piece">Piece</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="gram">Gram (g)</option>
                            <option value="liter">Liter (L)</option>
                            <option value="packet">Packet</option>
                            <option value="bottle">Bottle</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                        <select id="quick_product_type" name="product_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            <option value="">Select Type</option>
                            <option value="food">Food</option>
                            <option value="beverage">Beverage</option>
                            <option value="gift">Gift</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                        <input type="number" id="quick_selling_price" name="selling_price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="quick_track_inventory" name="track_inventory" value="1" class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="quick_track_inventory" class="ml-2 text-sm text-gray-700">Track Inventory</label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateProductModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-700">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Purchase Order Modal -->
<div id="viewPOModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="viewPOModalTitle" class="text-xl font-bold text-gray-900">Purchase Order Details</h3>
                <div class="flex items-center space-x-3">
                    <button onclick="printCurrentPO()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg flex items-center text-sm"><i class="fas fa-print mr-2"></i> Print</button>
                    <button onclick="closeViewPOModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-6" id="poDetailsContent"></div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 id="statusModalTitle" class="text-lg font-bold text-gray-900 mb-4">Update Status</h3>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="status_po_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="pending_approval">Submit for Approval</option>
                            <option value="approved">Approve</option>
                            <option value="ordered">Mark as Ordered</option>
                            <option value="cancelled">Cancel</option>
                        </select>
                    </div>
                    <div id="approvalNotesSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Approval Notes</label>
                        <textarea id="approval_notes" name="approval_notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                    <button type="button" onclick="updatePOStatus()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 font-medium">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 text-center">Delete Purchase Order</h3>
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">Are you sure you want to delete this purchase order?</p>
            <div class="mt-6 flex justify-center space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                <button type="button" onclick="deletePurchaseOrder()" id="confirmDeleteBtn" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const csrfToken = '{{ csrf_token() }}';
let currentPOId = null;
let products = [];
let productsLoaded = false;
let itemCounter = 0;

// ======================
// PRODUCT LOADING
// ======================

function loadProducts() {
    return new Promise((resolve, reject) => {
        if (productsLoaded && products.length > 0) {
            resolve(products);
            return;
        }

        fetch('/cafeteria/purchase-orders/get-products', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            products = data;
            productsLoaded = true;
            resolve(products);
        })
        .catch(error => {
            console.error('Error loading products:', error);
            reject(error);
        });
    });
}

function getProductOptions(selectedId = null) {
    let options = '<option value="">-- Select Product --</option>';
    if (!products || products.length === 0) {
        options += '<option value="" disabled>Loading products...</option>';
        return options;
    }
    products.forEach(product => {
        const selected = (selectedId && selectedId == product.id) ? 'selected' : '';
        options += `<option value="${product.id}" data-unit="${product.unit}" data-stock="${product.current_stock}" ${selected}>${product.product_name} (${product.product_code})</option>`;
    });
    return options;
}

// ======================
// QUICK PRODUCT CREATION
// ======================

function showCreateProductModal() {
    document.getElementById('quickProductForm').reset();
    document.getElementById('quick_product_code').value = '';
    document.getElementById('quick_track_inventory').checked = true;
    document.getElementById('createProductModal').classList.remove('hidden');
}

function closeCreateProductModal() {
    document.getElementById('createProductModal').classList.add('hidden');
}

document.getElementById('quickProductForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Auto-generate product code if empty
    const productName = document.getElementById('quick_product_name').value;
    const productCode = document.getElementById('quick_product_code').value;
    if (!productCode && productName) {
        formData.set('product_code', productName.substring(0, 10).toUpperCase().replace(/\s+/g, '_'));
    }

    formData.set('is_active', '1');
    formData.set('current_stock', '0');
    formData.set('track_inventory', formData.get('track_inventory') ? '1' : '0');

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';

    try {
        const response = await fetch('/cafeteria/products', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });

        const data = await response.json();

        if (response.ok && data.id) {
            Swal.fire({
                icon: 'success',
                title: 'Product Created!',
                text: `${data.product_name} has been added to inventory.`,
                timer: 2000,
                showConfirmButton: false
            });

            // Reload products and add to current order
            productsLoaded = false;
            await loadProducts();

            // Add the new product to the current order
            await addItemRow(data.id);
            closeCreateProductModal();
        } else {
            const errors = data.errors || {};
            const errorMsg = Object.values(errors).flat()[0] || 'Error creating product';
            Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create product. Please try again.' });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Auto-generate product code from name
document.getElementById('quick_product_name')?.addEventListener('blur', function() {
    const codeInput = document.getElementById('quick_product_code');
    if (!codeInput.value && this.value) {
        codeInput.value = this.value.substring(0, 10).toUpperCase().replace(/\s+/g, '_');
    }
});

// ======================
// ITEMS MANAGEMENT
// ======================

async function addItemRow(productId = null, quantity = '', price = '', tax = '') {
    await loadProducts();

    const tbody = document.getElementById('itemsTableBody');
    const noItemsRow = document.getElementById('noItemsRow');
    const footer = document.getElementById('itemsTableFooter');

    if (noItemsRow) noItemsRow.style.display = 'none';
    if (footer) footer.classList.remove('hidden');

    const rowId = 'item_' + itemCounter++;
    const row = document.createElement('tr');
    row.id = rowId;
    row.className = 'item-row';
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="items[${rowId}][product_id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" onchange="updateItemUnit(this)" required>
                ${getProductOptions(productId)}
            </select>
            <div class="mt-1 flex gap-2">
                <span class="text-xs text-gray-400">Can't find product?</span>
                <button type="button" onclick="showCreateProductModal()" class="text-xs text-green-600 hover:text-green-800 font-medium">Create New</button>
            </div>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][quantity_ordered]" min="0.001" step="0.001" value="${quantity}" oninput="calculateItemTotal(this)" class="quantity-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" required>
            <span class="item-unit text-xs text-gray-500 mt-1 block"></span>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][unit_price]" min="0" step="0.01" value="${price}" oninput="calculateItemTotal(this)" class="price-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][tax_rate]" min="0" max="100" step="0.1" value="${tax}" oninput="calculateItemTotal(this)" class="tax-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
            <span class="text-xs text-gray-500">%</span>
        </td>
        <td class="px-4 py-3">
            <div class="item-total font-medium text-gray-900">KES 0.00</div>
        </td>
        <td class="px-4 py-3">
            <button type="button" onclick="removeItemRow('${rowId}')" class="text-red-600 hover:text-red-900 p-1"><i class="fas fa-trash"></i></button>
        </td>
    `;
    tbody.appendChild(row);

    if (productId) updateItemUnit(row.querySelector('.product-select'));
    calculateItemTotal(row.querySelector('.quantity-input'));
    calculateTotals();
}

function removeItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calculateTotals();
        const itemRows = document.querySelectorAll('.item-row');
        if (itemRows.length === 0) {
            document.getElementById('noItemsRow').style.display = '';
            document.getElementById('itemsTableFooter').classList.add('hidden');
        }
    }
}

function updateItemUnit(select) {
    const row = select.closest('tr');
    const unitSpan = row.querySelector('.item-unit');
    const productId = select.value;
    if (productId) {
        const product = products.find(p => p.id == productId);
        if (product) unitSpan.textContent = product.unit;
    } else {
        unitSpan.textContent = '';
    }
}

function calculateItemTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;
    const subtotal = quantity * price;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount;
    row.querySelector('.item-total').textContent = 'KES ' + total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0, taxAmount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;
        const itemSubtotal = quantity * price;
        subtotal += itemSubtotal;
        taxAmount += itemSubtotal * (taxRate / 100);
    });
    const total = subtotal + taxAmount;
    document.getElementById('subtotalAmount').textContent = 'KES ' + subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = 'KES ' + taxAmount.toFixed(2);
    document.getElementById('totalAmount').textContent = 'KES ' + total.toFixed(2);
}

function getItemsData() {
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        const productId = row.querySelector('.product-select').value;
        const quantity = row.querySelector('.quantity-input').value;
        const price = row.querySelector('.price-input').value;
        const taxRate = row.querySelector('.tax-input').value;
        if (productId && quantity && price) {
            items.push({ product_id: productId, quantity_ordered: quantity, unit_price: price, tax_rate: taxRate || 0 });
        }
    });
    return items;
}

// ======================
// MODAL FUNCTIONS
// ======================

function resetPOForm() {
    document.getElementById('poForm').reset();
    document.getElementById('po_id').value = '';
    document.getElementById('order_date').value = new Date().toISOString().split('T')[0];
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = '';
    document.getElementById('itemsTableFooter').classList.add('hidden');
    itemCounter = 0;
    document.getElementById('saveDraftBtn').classList.remove('hidden');
}

async function openPOModal(id = null) {
    await loadProducts();
    resetPOForm();

    if (id) {
        document.getElementById('poModalTitle').textContent = 'Edit Purchase Order';
        document.getElementById('savePOBtn').textContent = 'Update Purchase Order';
        currentPOId = id;
        const response = await fetch(`/cafeteria/purchase-orders/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const po = await response.json();
        document.getElementById('po_id').value = po.id;
        document.getElementById('supplier_id').value = po.supplier_id;
        document.getElementById('business_section_id').value = po.business_section_id;
        document.getElementById('shop_id').value = po.shop_id;
        document.getElementById('order_date').value = po.order_date ? po.order_date.split('T')[0] : '';
        document.getElementById('expected_delivery_date').value = po.expected_delivery_date ? po.expected_delivery_date.split('T')[0] : '';
        document.getElementById('delivery_method').value = po.delivery_method || '';
        document.getElementById('delivery_address').value = po.delivery_address || '';
        document.getElementById('notes').value = po.notes || '';
        document.getElementById('terms_conditions').value = po.terms_conditions || '';
        for (const item of po.items) {
            await addItemRow(item.product_id, item.quantity_ordered, item.unit_price, item.tax_rate || 0);
        }
        if (po.status !== 'draft') document.getElementById('saveDraftBtn').classList.add('hidden');
    } else {
        document.getElementById('poModalTitle').textContent = 'Create Purchase Order';
        document.getElementById('savePOBtn').textContent = 'Save Purchase Order';
        currentPOId = null;
        await addItemRow();
    }
    document.getElementById('poModal').classList.remove('hidden');
}

function closePOModal() { document.getElementById('poModal').classList.add('hidden'); }

function savePurchaseOrder() { savePO(false); }
function saveAsDraft() { savePO(true); }

async function savePO(isDraft = false) {
    const items = getItemsData();
    if (items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one item to the purchase order.' });
        return;
    }

    const formData = new FormData(document.getElementById('poForm'));
    items.forEach((item, index) => {
        formData.append(`items[${index}][product_id]`, item.product_id);
        formData.append(`items[${index}][quantity_ordered]`, item.quantity_ordered);
        formData.append(`items[${index}][unit_price]`, item.unit_price);
        formData.append(`items[${index}][tax_rate]`, item.tax_rate);
    });

    const isEdit = !!currentPOId;
    const url = isEdit ? `/cafeteria/purchase-orders/${currentPOId}` : '/cafeteria/purchase-orders';
    if (isEdit) formData.append('_method', 'PUT');

    const btn = document.getElementById('savePOBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Success', text: data.message, timer: 2000, showConfirmButton: false });
            closePOModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error saving purchase order' });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ======================
// VIEW FUNCTIONS
// ======================

async function viewPurchaseOrder(id) {
    const response = await fetch(`/cafeteria/purchase-orders/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    const po = await response.json();
    document.getElementById('viewPOModalTitle').textContent = po.po_number;
    document.getElementById('poDetailsContent').innerHTML = `
        <div class="space-y-6">
            <div class="flex justify-between items-start">
                <div><h2 class="text-2xl font-bold text-gray-900">${po.po_number}</h2></div>
                <div class="text-right"><div class="text-lg font-bold text-primary">KES ${po.total_amount.toFixed(2)}</div></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Supplier Information</h4>
                    <p><strong>Name:</strong> ${po.supplier.supplier_name}</p>
                    <p><strong>Phone:</strong> ${po.supplier.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${po.supplier.email || 'N/A'}</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Order Information</h4>
                    <p><strong>Order Date:</strong> ${new Date(po.order_date).toLocaleDateString()}</p>
                    <p><strong>Expected Delivery:</strong> ${po.expected_delivery_date ? new Date(po.expected_delivery_date).toLocaleDateString() : 'Not set'}</p>
                    <p><strong>Status:</strong> ${po.status.replace('_', ' ')}</p>
                </div>
            </div>
            <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Order Items</h4>
                <table class="min-w-full"><thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>${po.items.map(item => `<tr><td>${item.product_name}</td><td>${item.quantity_ordered}</td><td>KES ${item.unit_price.toFixed(2)}</td><td>KES ${item.total_price.toFixed(2)}</td></tr>`).join('')}</tbody>
                <tfoot><tr><td colspan="3" class="text-right"><strong>Total:</strong></td><td><strong>KES ${po.total_amount.toFixed(2)}</strong></td></tr></tfoot>
            </table>
            </div>
        </div>
    `;
    document.getElementById('viewPOModal').classList.remove('hidden');
}

function closeViewPOModal() { document.getElementById('viewPOModal').classList.add('hidden'); }
function printCurrentPO() { window.print(); }

// ======================
// STATUS FUNCTIONS
// ======================

function submitForApproval(id) { openStatusModal(id, 'pending_approval', 'Submit for Approval'); }
function approvePurchaseOrder(id) { openStatusModal(id, 'approved', 'Approve Purchase Order'); }
function editPurchaseOrder(id) { openPOModal(id); }
function printPurchaseOrder(id) { window.open(`/cafeteria/purchase-orders/${id}/print`, '_blank'); }

function openStatusModal(id, defaultStatus, title) {
    currentPOId = id;
    document.getElementById('status_po_id').value = id;
    document.getElementById('statusModalTitle').textContent = title;
    document.getElementById('status').value = defaultStatus;
    document.getElementById('approvalNotesSection').classList.toggle('hidden', defaultStatus !== 'approved');
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); }

async function updatePOStatus() {
    const status = document.getElementById('status').value;
    const approvalNotes = document.getElementById('approval_notes').value;
    if (!status) { Swal.fire({ icon: 'warning', title: 'Missing', text: 'Please select a status' }); return; }

    const response = await fetch(`/cafeteria/purchase-orders/${currentPOId}/update-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ status, approval_notes })
    });
    const data = await response.json();
    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Updated' : 'Error', text: data.message });
    if (data.success) { closeStatusModal(); setTimeout(() => location.reload(), 1500); }
}

// ======================
// DELETE FUNCTIONS
// ======================

function confirmDeletePO(id) {
    currentPOId = id;
    Swal.fire({
        title: 'Delete Purchase Order?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) deletePurchaseOrder();
    });
}

async function deletePurchaseOrder() {
    const response = await fetch(`/cafeteria/purchase-orders/${currentPOId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const data = await response.json();
    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Deleted!' : 'Error', text: data.message });
    if (data.success) setTimeout(() => location.reload(), 1500);
}

function closeDeleteModal() { /* Not used with SweetAlert */ }

// ======================
// FILTER FUNCTIONS
// ======================

function applyFilters() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (fromDate) params.set('from_date', fromDate);
    if (toDate) params.set('to_date', toDate);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

function filterByStatus(status) {
    const params = new URLSearchParams(window.location.search);
    if (status) params.set('status', status);
    else params.delete('status');
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

function exportPurchaseOrders() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    window.open('/cafeteria/purchase-orders/export?' + params.toString(), '_blank');
}

// ======================
// EVENT LISTENERS
// ======================

document.getElementById('searchInput')?.addEventListener('keyup', e => { if (e.key === 'Enter') applyFilters(); });
document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
document.getElementById('fromDate')?.addEventListener('change', applyFilters);
document.getElementById('toDate')?.addEventListener('change', applyFilters);
document.getElementById('status')?.addEventListener('change', function() {
    document.getElementById('approvalNotesSection').classList.toggle('hidden', this.value !== 'approved');
});

// Initialize
loadProducts();
</script>
@endsection
