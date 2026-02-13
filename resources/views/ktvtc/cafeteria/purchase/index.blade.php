@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Order Management')
@section('page-description', 'Create and manage purchase orders')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Procurement
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Purchase Orders
    </span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
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

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
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

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
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

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
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
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="searchInput"
                           placeholder="Search PO or supplier..."
                           value="{{ request('search') }}"
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>

                <!-- Status Filter -->
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

                <!-- Date Range -->
                <div class="flex gap-2">
                    <input type="date"
                           id="fromDate"
                           value="{{ request('from_date') }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                    <input type="date"
                           id="toDate"
                           value="{{ request('to_date') }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Export Button -->
                <button onclick="exportPurchaseOrders()" class="btn-secondary py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>

                <!-- New Purchase Order Button -->
                <button onclick="openPOModal()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Purchase Order
                </button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <nav class="flex overflow-x-auto">
            <button onclick="filterByStatus('')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ !request('status') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Orders
                <span class="ml-2 bg-gray-100 text-gray-900 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $totalOrders }}</span>
            </button>
            <button onclick="filterByStatus('draft')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'draft' ? 'border-gray-400 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Draft
                <span class="ml-2 bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $draftCount }}</span>
            </button>
            <button onclick="filterByStatus('pending_approval')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'pending_approval' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending Approval
                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
            </button>
            <button onclick="filterByStatus('approved')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Approved
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $approvedCount }}</span>
            </button>
            <button onclick="filterByStatus('ordered')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'ordered' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Ordered
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $orderedCount }}</span>
            </button>
        </nav>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            PO Number
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Business Section
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Delivery Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Items
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $po->po_number }}</div>
                            <div class="text-sm text-gray-500">
                                @if($po->shop && $po->shop->name)
                                    {{ $po->shop->name }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $po->supplier->supplier_name }}</div>
                            <div class="text-sm text-gray-500">{{ $po->supplier->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($po->businessSection)
                                <div class="text-sm text-gray-900">{{ $po->businessSection->section_name }}</div>
                                <div class="text-xs text-gray-500">{{ $po->businessSection->section_code }}</div>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $po->order_date->format('M d, Y') }}
                        </td>
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
                            <div class="text-sm text-gray-500">{{ $po->total_quantity }} {{ $po->items->first()->unit ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-lg text-primary">
                                KES {{ number_format($po->total_amount, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$po->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <button onclick="viewPurchaseOrder({{ $po->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Edit Button (only for draft) -->
                                @if($po->status == 'draft')
                                <button onclick="editPurchaseOrder({{ $po->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif

                                <!-- Status Actions -->
                                @if($po->status == 'draft')
                                <button onclick="submitForApproval({{ $po->id }})"
                                        class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50"
                                        title="Submit for Approval">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                @endif

                                @if($po->status == 'pending_approval' && auth()->user()->can('approve-purchase-orders'))
                                <button onclick="approvePurchaseOrder({{ $po->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif

                                <!-- Print Button -->
                                <button onclick="printPurchaseOrder({{ $po->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Print">
                                    <i class="fas fa-print"></i>
                                </button>

                                <!-- Delete Button (only for draft) -->
                                @if($po->status == 'draft')
                                <button onclick="confirmDeletePO({{ $po->id }})"
                                        class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
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
                                <p class="text-gray-500 mt-1">Start by creating your first purchase order</p>
                                <button onclick="openPOModal()"
                                        class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Create Purchase Order
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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
                        <!-- Supplier -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Supplier *
                            </label>
                            <select id="supplier_id" name="supplier_id" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})</option>
                                @endforeach
                            </select>
                            <div id="supplier_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Business Section -->
                        <div>
                            <label for="business_section_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Business Section *
                            </label>
                            <select id="business_section_id" name="business_section_id" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Business Section</option>
                                @foreach($businessSections as $section)
                                <option value="{{ $section->id }}">
                                    {{ $section->section_name }} ({{ $section->section_code }})
                                </option>
                                @endforeach
                            </select>
                            <div id="business_section_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Shop -->
                        <div>
                            <label for="shop_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Shop *
                            </label>
                            <select id="shop_id" name="shop_id" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Shop</option>
                                @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">
                                    {{ $shop->name ?? 'Shop #' . $shop->id }}
                                </option>
                                @endforeach
                            </select>
                            <div id="shop_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Order Date -->
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Order Date *
                            </label>
                            <input type="date"
                                   id="order_date"
                                   name="order_date"
                                   required
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                            <div id="order_date_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Expected Delivery Date -->
                        <div>
                            <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Expected Delivery Date
                            </label>
                            <input type="date"
                                   id="expected_delivery_date"
                                   name="expected_delivery_date"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <!-- Delivery Method -->
                        <div>
                            <label for="delivery_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Method
                            </label>
                            <select id="delivery_method" name="delivery_method"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Method</option>
                                <option value="pickup">Pickup</option>
                                <option value="supplier_delivery">Supplier Delivery</option>
                                <option value="courier">Courier</option>
                            </select>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="mt-4">
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Delivery Address
                        </label>
                        <textarea id="delivery_address"
                                  name="delivery_address"
                                  rows="2"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Order Items</h4>
                        <button type="button" onclick="addItemRow()" class="btn-primary text-white py-2 px-4 rounded-lg flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
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
                                <!-- Items will be added here dynamically -->
                                <tr id="noItemsRow">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-2"></i>
                                        <p class="text-gray-600">No items added yet</p>
                                        <button type="button" onclick="addItemRow()" class="mt-2 text-primary hover:text-primary-dark font-medium">
                                            Click here to add your first item
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="itemsTableFooter" class="bg-gray-50 hidden">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Subtotal:
                                    </td>
                                    <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-900" id="subtotalAmount">
                                        KES 0.00
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Tax:
                                    </td>
                                    <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-900" id="taxAmount">
                                        KES 0.00
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Total Amount:
                                    </td>
                                    <td colspan="3" class="px-4 py-3 text-lg font-bold text-primary" id="totalAmount">
                                        KES 0.00
                                    </td>
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
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>

                        <div>
                            <label for="terms_conditions" class="block text-sm font-medium text-gray-700 mb-2">
                                Terms & Conditions
                            </label>
                            <textarea id="terms_conditions"
                                      name="terms_conditions"
                                      rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-between">
                <div>
                    <button type="button"
                            onclick="saveAsDraft()"
                            id="saveDraftBtn"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Save as Draft
                    </button>
                </div>

                <div class="flex space-x-3">
                    <button type="button"
                            onclick="closePOModal()"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            onclick="savePurchaseOrder()"
                            id="savePOBtn"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                        Save Purchase Order
                    </button>
                </div>
            </div>
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
                    <button onclick="printCurrentPO()" class="btn-secondary py-2 px-4 rounded-lg flex items-center text-sm">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <button onclick="closeViewPOModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6" id="poDetailsContent">
            <!-- Content loaded dynamically -->
        </div>
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
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            New Status
                        </label>
                        <select id="status" name="status"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="pending_approval">Submit for Approval</option>
                            <option value="approved">Approve</option>
                            <option value="ordered">Mark as Ordered</option>
                            <option value="cancelled">Cancel</option>
                        </select>
                    </div>

                    <div id="approvalNotesSection" class="hidden">
                        <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Approval Notes
                        </label>
                        <textarea id="approval_notes" name="approval_notes" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeStatusModal()"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            onclick="updatePOStatus()"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                        Update Status
                    </button>
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
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">
                Are you sure you want to delete this purchase order?
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="deletePurchaseOrder()"
                        id="confirmDeleteBtn"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Delete Purchase Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg border p-4 min-w-[300px] transform transition-all duration-300 translate-x-full">
        <div class="flex items-start">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="ml-3 w-0 flex-1">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
                <p id="toastDescription" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button onclick="hideToast()" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// State
let currentPOId = null;
let products = [];
let itemCounter = 0;

// DOM Elements
const poModal = document.getElementById('poModal');
const viewPOModal = document.getElementById('viewPOModal');
const statusModal = document.getElementById('statusModal');
const deleteModal = document.getElementById('deleteModal');
const poForm = document.getElementById('poForm');
const toast = document.getElementById('toast');

// ======================
// FILTERS & NAVIGATION
// ======================

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const url = new URL(window.location.href);

    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');

    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');

    if (fromDate) url.searchParams.set('from_date', fromDate);
    else url.searchParams.delete('from_date');

    if (toDate) url.searchParams.set('to_date', toDate);
    else url.searchParams.delete('to_date');

    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterByStatus(status) {
    const url = new URL(window.location.href);

    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }

    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Event Listeners
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') applyFilters();
});

document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('fromDate').addEventListener('change', applyFilters);
document.getElementById('toDate').addEventListener('change', applyFilters);

// ======================
// PRODUCTS MANAGEMENT
// ======================

function loadProducts() {
    if (products.length === 0) {
        fetch('/purchase-orders/get-products', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            products = data;
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
    }
}

function getProductOptions() {
    let options = '<option value="">Select Product</option>';
    products.forEach(product => {
        options += `<option value="${product.id}" data-unit="${product.unit}" data-stock="${product.current_stock}">${product.product_name} (${product.product_code})</option>`;
    });
    return options;
}

// ======================
// ITEMS MANAGEMENT
// ======================

function addItemRow(product = null, quantity = '', price = '', tax = '', notes = '') {
    const tbody = document.getElementById('itemsTableBody');
    const noItemsRow = document.getElementById('noItemsRow');
    const footer = document.getElementById('itemsTableFooter');

    // Hide "no items" message
    if (noItemsRow) {
        noItemsRow.style.display = 'none';
    }

    // Show footer
    footer.classList.remove('hidden');

    // Add new row
    const rowId = 'item_' + itemCounter++;
    const row = document.createElement('tr');
    row.id = rowId;
    row.className = 'item-row';
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="items[${rowId}][product_id]"
                    class="product-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary"
                    onchange="updateItemUnit(this)">
                ${getProductOptions()}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][quantity_ordered]"
                   min="0.001"
                   step="0.001"
                   value="${quantity}"
                   oninput="calculateItemTotal(this)"
                   class="quantity-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
            <span class="item-unit text-xs text-gray-500 mt-1 block"></span>
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][unit_price]"
                   min="0"
                   step="0.01"
                   value="${price}"
                   oninput="calculateItemTotal(this)"
                   class="price-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][tax_rate]"
                   min="0"
                   max="100"
                   step="0.1"
                   value="${tax}"
                   oninput="calculateItemTotal(this)"
                   class="tax-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
            <span class="text-xs text-gray-500">%</span>
        </td>
        <td class="px-4 py-3">
            <div class="item-total font-medium text-gray-900">KES 0.00</div>
        </td>
        <td class="px-4 py-3">
            <button type="button"
                    onclick="removeItemRow('${rowId}')"
                    class="text-red-600 hover:text-red-900 p-1">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);

    // Set product if provided
    if (product) {
        row.querySelector('.product-select').value = product;
        updateItemUnit(row.querySelector('.product-select'));
    }

    // Calculate initial total
    calculateItemTotal(row.querySelector('.quantity-input'));

    return row;
}

function removeItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calculateTotals();

        // Show "no items" message if all rows removed
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
        if (product) {
            unitSpan.textContent = product.unit;
        }
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
    let subtotal = 0;
    let taxAmount = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;

        const itemSubtotal = quantity * price;
        const itemTax = itemSubtotal * (taxRate / 100);

        subtotal += itemSubtotal;
        taxAmount += itemTax;
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
            items.push({
                product_id: productId,
                quantity_ordered: quantity,
                unit_price: price,
                tax_rate: taxRate || 0
            });
        }
    });
    return items;
}

// ======================
// PURCHASE ORDER MODAL
// ======================

function openPOModal(id = null) {
    loadProducts(); // Load products if not loaded
    resetPOForm();
    clearErrors();

    if (id) {
        // Edit mode
        document.getElementById('poModalTitle').textContent = 'Edit Purchase Order';
        document.getElementById('savePOBtn').textContent = 'Update Purchase Order';
        currentPOId = id;

        // Load purchase order data
        fetch(`/purchase-orders/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(po => {
            populatePOForm(po);
            poModal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading purchase order data', 'error');
        });
    } else {
        // Create mode
        document.getElementById('poModalTitle').textContent = 'Create Purchase Order';
        document.getElementById('savePOBtn').textContent = 'Save Purchase Order';
        document.getElementById('saveDraftBtn').classList.remove('hidden');
        currentPOId = null;
        poModal.classList.remove('hidden');
    }
}

function closePOModal() {
    poModal.classList.add('hidden');
    resetPOForm();
    clearErrors();
}

function populatePOForm(po) {
    document.getElementById('po_id').value = po.id;
    document.getElementById('supplier_id').value = po.supplier_id;
    document.getElementById('business_section_id').value = po.business_section_id;
    document.getElementById('shop_id').value = po.shop_id;
    document.getElementById('order_date').value = po.order_date.split('T')[0];
    document.getElementById('expected_delivery_date').value = po.expected_delivery_date ? po.expected_delivery_date.split('T')[0] : '';
    document.getElementById('delivery_method').value = po.delivery_method || '';
    document.getElementById('delivery_address').value = po.delivery_address || '';
    document.getElementById('notes').value = po.notes || '';
    document.getElementById('terms_conditions').value = po.terms_conditions || '';

    // Clear existing items
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = 'none';
    document.getElementById('itemsTableFooter').classList.remove('hidden');

    // Add items
    po.items.forEach(item => {
        addItemRow(
            item.product_id,
            item.quantity_ordered,
            item.unit_price,
            item.tax_rate,
            item.notes
        );
    });

    // Hide save draft button for non-draft POs
    if (po.status !== 'draft') {
        document.getElementById('saveDraftBtn').classList.add('hidden');
    }
}

function resetPOForm() {
    poForm.reset();
    document.getElementById('po_id').value = '';
    document.getElementById('order_date').value = new Date().toISOString().split('T')[0];

    // Clear items
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = '';
    document.getElementById('itemsTableFooter').classList.add('hidden');
    itemCounter = 0;

    // Show save draft button
    document.getElementById('saveDraftBtn').classList.remove('hidden');
}

function clearErrors() {
    document.querySelectorAll('[id$="_error"]').forEach(element => {
        element.textContent = '';
        element.classList.add('hidden');
    });
}

function showErrors(errors) {
    clearErrors();

    for (const [field, messages] of Object.entries(errors)) {
        const errorElement = document.getElementById(`${field}_error`);
        if (errorElement) {
            errorElement.textContent = messages[0];
            errorElement.classList.remove('hidden');
        }
    }
}

function savePurchaseOrder() {
    const items = getItemsData();
    if (items.length === 0) {
        showToast('Please add at least one item', 'error');
        return;
    }

    savePO(false);
}

function saveAsDraft() {
    const items = getItemsData();
    if (items.length === 0) {
        showToast('Please add at least one item', 'error');
        return;
    }

    savePO(true);
}

function savePO(isDraft = false) {
    const formData = new FormData(poForm);
    const items = getItemsData();

    // Add items to form data
    items.forEach((item, index) => {
        formData.append(`items[${index}][product_id]`, item.product_id);
        formData.append(`items[${index}][quantity_ordered]`, item.quantity_ordered);
        formData.append(`items[${index}][unit_price]`, item.unit_price);
        formData.append(`items[${index}][tax_rate]`, item.tax_rate);
    });

    const isEdit = !!currentPOId;
    const url = isEdit ? `/purchase-orders/${currentPOId}` : '/purchase-orders';
    const method = isEdit ? 'PUT' : 'POST';

    // Add _method for PUT requests
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closePOModal();

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showErrors(data.errors || { general: [data.message] });
            showToast('Please fix the errors in the form', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// ======================
// VIEW PURCHASE ORDER
// ======================

function viewPurchaseOrder(id) {
    fetch(`/purchase-orders/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(po => {
        document.getElementById('viewPOModalTitle').textContent = po.po_number;

        const content = `
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">${po.po_number}</h2>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="px-3 py-1 text-sm font-medium rounded-full ${getStatusColor(po.status)}">
                                ${formatStatus(po.status)}
                            </span>
                            <span class="text-gray-600">Created: ${formatDate(po.created_at)}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-primary">KES ${po.total_amount.toFixed(2)}</div>
                        <div class="text-sm text-gray-600">Total Amount</div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Supplier Name</p>
                                <p class="font-medium text-gray-900">${po.supplier.supplier_name}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Contact Person</p>
                                <p class="font-medium text-gray-900">${po.supplier.contact_person || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="font-medium text-gray-900">${po.supplier.phone || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900">${po.supplier.email || 'N/A'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Order Information</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Business Section</p>
                                <p class="font-medium text-gray-900">${po.business_section ? po.business_section.section_name : 'N/A'} (${po.business_section ? po.business_section.section_code : 'N/A'})</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Shop</p>
                                <p class="font-medium text-gray-900">${po.shop ? po.shop.name : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Order Date</p>
                                <p class="font-medium text-gray-900">${formatDate(po.order_date)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Expected Delivery</p>
                                <p class="font-medium text-gray-900">${po.expected_delivery_date ? formatDate(po.expected_delivery_date) : 'Not set'}</p>
                            </div>
                            ${po.delivery_method ? `
                            <div>
                                <p class="text-sm text-gray-600">Delivery Method</p>
                                <p class="font-medium text-gray-900">${formatDeliveryMethod(po.delivery_method)}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Order Items</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${po.items.map(item => `
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">${item.product_name}</div>
                                        <div class="text-sm text-gray-500">${item.product_code}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">${item.quantity_ordered}</div>
                                        <div class="text-sm text-gray-500">${item.unit}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        KES ${item.unit_price.toFixed(2)}
                                    </td>
                                    <td class="px-4 py-3">
                                        ${item.tax_rate}%
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        KES ${item.total_price.toFixed(2)}
                                    </td>
                                </tr>
                                `).join('')}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Subtotal:
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">
                                        KES ${po.subtotal.toFixed(2)}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Tax:
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">
                                        KES ${po.tax_amount.toFixed(2)}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Total Amount:
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-lg font-bold text-primary">
                                        KES ${po.total_amount.toFixed(2)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                ${(po.notes || po.terms_conditions) ? `
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        ${po.notes ? `
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Notes</p>
                            <p class="text-gray-600 whitespace-pre-line">${po.notes}</p>
                        </div>
                        ` : ''}
                        ${po.terms_conditions ? `
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Terms & Conditions</p>
                            <p class="text-gray-600 whitespace-pre-line">${po.terms_conditions}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ` : ''}

                <!-- System Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">System Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Created By</p>
                            <p class="font-medium text-gray-900">${po.creator ? po.creator.name : 'System'}</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDate(po.created_at)}</p>
                        </div>
                        ${po.approved_by ? `
                        <div>
                            <p class="text-sm text-gray-600">Approved By</p>
                            <p class="font-medium text-gray-900">${po.approver ? po.approver.name : 'System'}</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDate(po.approved_at)}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('poDetailsContent').innerHTML = content;
        viewPOModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error loading purchase order details', 'error');
    });
}

function closeViewPOModal() {
    viewPOModal.classList.add('hidden');
}

function printCurrentPO() {
    // Implement print functionality
    window.print();
}

function getStatusColor(status) {
    const colors = {
        'draft': 'bg-gray-100 text-gray-800',
        'pending_approval': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'ordered': 'bg-blue-100 text-blue-800',
        'partial': 'bg-indigo-100 text-indigo-800',
        'received': 'bg-purple-100 text-purple-800',
        'cancelled': 'bg-red-100 text-red-800',
        'closed': 'bg-gray-800 text-white'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatStatus(status) {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDeliveryMethod(method) {
    const methods = {
        'pickup': 'Pickup',
        'supplier_delivery': 'Supplier Delivery',
        'courier': 'Courier'
    };
    return methods[method] || method;
}

// ======================
// STATUS MANAGEMENT
// ======================

function submitForApproval(id) {
    openStatusModal(id, 'pending_approval', 'Submit for Approval');
}

function approvePurchaseOrder(id) {
    openStatusModal(id, 'approved', 'Approve Purchase Order');
}

function openStatusModal(id, defaultStatus = '', title = 'Update Status') {
    currentPOId = id;
    document.getElementById('status_po_id').value = id;
    document.getElementById('statusModalTitle').textContent = title;
    document.getElementById('status').value = defaultStatus;

    // Show/hide approval notes section
    const notesSection = document.getElementById('approvalNotesSection');
    notesSection.classList.toggle('hidden', defaultStatus !== 'approved');

    statusModal.classList.remove('hidden');
}

function closeStatusModal() {
    statusModal.classList.add('hidden');
    document.getElementById('statusForm').reset();
    currentPOId = null;
}

function updatePOStatus() {
    const status = document.getElementById('status').value;
    const approvalNotes = document.getElementById('approval_notes').value;

    if (!status) {
        showToast('Please select a status', 'error');
        return;
    }

    const data = {
        status: status,
        approval_notes: approvalNotes
    };

    fetch(`/purchase-orders/${currentPOId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeStatusModal();

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// ======================
// DELETE FUNCTIONS
// ======================

function confirmDeletePO(id) {
    currentPOId = id;
    document.getElementById('deleteMessage').textContent =
        'Are you sure you want to delete this purchase order? This action cannot be undone.';
    deleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    currentPOId = null;
}

function deletePurchaseOrder() {
    if (!currentPOId) return;

    fetch(`/purchase-orders/${currentPOId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeDeleteModal();

            // Remove row from table or reload
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
            closeDeleteModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        closeDeleteModal();
    });
}

// ======================
// EXPORT FUNCTION
// ======================

function exportPurchaseOrders() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    let url = '/purchase-orders/export';
    const params = new URLSearchParams();

    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    window.open(url, '_blank');
    showToast('Export started. Please check your downloads.', 'success');
}

// ======================
// TOAST NOTIFICATION
// ======================

function showToast(message, type = 'success', description = '') {
    const toastMessage = document.getElementById('toastMessage');
    const toastDescription = document.getElementById('toastDescription');
    const toastIcon = document.getElementById('toastIcon');

    toastMessage.textContent = message;
    toastDescription.textContent = description;

    // Set icon based on type
    if (type === 'success') {
        toastIcon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    } else if (type === 'error') {
        toastIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    } else {
        toastIcon.innerHTML = '<i class="fas fa-info-circle text-blue-500 text-xl"></i>';
        toastIcon.className = 'flex-shrink-0';
    }

    // Show toast
    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.querySelector('.transform').classList.remove('translate-x-full');
    }, 10);

    // Auto hide after 5 seconds
    setTimeout(hideToast, 5000);
}

function hideToast() {
    toast.querySelector('.transform').classList.add('translate-x-full');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 300);
}

// ======================
// EVENT LISTENERS
// ======================

// Close modals on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePOModal();
        closeViewPOModal();
        closeStatusModal();
        closeDeleteModal();
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target === poModal) closePOModal();
    if (e.target === viewPOModal) closeViewPOModal();
    if (e.target === statusModal) closeStatusModal();
    if (e.target === deleteModal) closeDeleteModal();
});

// Status select change
document.getElementById('status').addEventListener('change', function() {
    const notesSection = document.getElementById('approvalNotesSection');
    notesSection.classList.toggle('hidden', this.value !== 'approved');
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});

// Quick actions from table
function editPurchaseOrder(id) {
    openPOModal(id);
}

function printPurchaseOrder(id) {
    // Implement print functionality
    window.open(`/purchase-orders/${id}/print`, '_blank');
}
</script>
@endsection
