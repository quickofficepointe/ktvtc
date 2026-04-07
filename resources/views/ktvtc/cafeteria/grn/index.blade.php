@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Goods Received Notes')
@section('page-title', 'GRN Management')
@section('page-description', 'Record and manage received goods')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">Procurement</span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">Goods Received Notes</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-clipboard-check text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total GRNs</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalGrns ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $completedCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                    <i class="fas fa-flask text-gray-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending QC</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingQualityCount ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Receipts -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">Today's Receipts</h3>
                <p class="text-sm text-gray-600 mt-1">GRNs received today: {{ $todayGrns->count() ?? 0 }}</p>
            </div>
            <button onclick="openGRNModal()" class="bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-red-700">
                <i class="fas fa-clipboard-check mr-2"></i> New GRN
            </button>
        </div>

        @if(isset($todayGrns) && $todayGrns->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items Received</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quality Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($todayGrns as $grn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $grn->grn_number }}</td>
                        <td class="px-6 py-4">
                            @if($grn->purchaseOrder)
                            <span class="text-blue-600 font-medium">{{ $grn->purchaseOrder->po_number }}</span>
                            @else
                            <span class="text-gray-400">Direct</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $grn->supplier->supplier_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $grn->total_items ?? 0 }} items</div>
                            <div class="text-sm text-gray-500">{{ $grn->total_quantity ?? 0 }} units</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $grn->quality_status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($grn->quality_status == 'passed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($grn->quality_status ?? 'Pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewGRN({{ $grn->id }})" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" title="View Details"><i class="fas fa-eye"></i></button>
                                @if(($grn->status ?? 'draft') == 'draft')
                                <button onclick="editGRN({{ $grn->id }})" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Edit"><i class="fas fa-edit"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-truck-loading text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-600">No GRNs received today</p>
            <button onclick="openGRNModal()" class="mt-4 bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center mx-auto hover:bg-red-700">
                <i class="fas fa-clipboard-check mr-2"></i> Create First GRN
            </button>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search GRN or supplier..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>
                <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="qualityStatusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Quality Status</option>
                    <option value="pending">Pending QC</option>
                    <option value="passed">Passed</option>
                    <option value="failed">Failed</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="exportGRNs()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>
                <button onclick="applyFilters()" class="bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-red-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <nav class="flex overflow-x-auto">
            <button onclick="filterByStatus('')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ !request('status') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All GRNs <span class="ml-2 bg-gray-100 text-gray-900 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $totalGrns ?? 0 }}</span>
            </button>
            <button onclick="filterByStatus('draft')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'draft' ? 'border-gray-400 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Draft <span class="ml-2 bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $draftCount ?? 0 }}</span>
            </button>
            <button onclick="filterByStatus('pending')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingCount ?? 0 }}</span>
            </button>
            <button onclick="filterByStatus('completed')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'completed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Completed <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $completedCount ?? 0 }}</span>
            </button>
            <button onclick="filterByQualityStatus('pending')" class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('quality_status') == 'pending' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending QC <span class="ml-2 bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingQualityCount ?? 0 }}</span>
            </button>
        </nav>
    </div>

    <!-- GRNs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quality</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($grns ?? [] as $grn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $grn->grn_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($grn->purchaseOrder)
                            <span class="text-blue-600">{{ $grn->purchaseOrder->po_number }}</span>
                            @else
                            <span class="text-gray-400">Direct</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $grn->supplier->supplier_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $grn->delivery_date ? $grn->delivery_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $grn->total_items ?? 0 }} items</td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">KES {{ number_format($grn->total_value ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $grn->status == 'draft' ? 'bg-gray-100 text-gray-800' : ($grn->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($grn->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($grn->status ?? 'Draft') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $grn->quality_status == 'pending' ? 'bg-gray-100 text-gray-800' : ($grn->quality_status == 'passed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($grn->quality_status ?? 'Pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewGRN({{ $grn->id }})" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" title="View"><i class="fas fa-eye"></i></button>
                                @if(($grn->status ?? 'draft') == 'draft')
                                <button onclick="editGRN({{ $grn->id }})" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Edit"><i class="fas fa-edit"></i></button>
                                @endif
                                @if(($grn->quality_status ?? 'pending') == 'pending' && ($grn->status ?? '') != 'draft')
                                <button onclick="openQualityCheckModal({{ $grn->id }})" class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" title="Quality Check"><i class="fas fa-flask"></i></button>
                                @endif
                                @if(($grn->status ?? '') == 'pending')
                                <button onclick="completeGRN({{ $grn->id }})" class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Complete"><i class="fas fa-check-circle"></i></button>
                                @endif
                                <button onclick="printGRN({{ $grn->id }})" class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" title="Print"><i class="fas fa-print"></i></button>
                                @if(($grn->status ?? 'draft') == 'draft')
                                <button onclick="confirmDeleteGRN({{ $grn->id }})" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-clipboard-check text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-700">No goods received notes found</p>
                                <button onclick="openGRNModal()" class="mt-4 bg-primary text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-red-700">
                                    <i class="fas fa-clipboard-check mr-2"></i> Create GRN
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($grns) && $grns->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $grns->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create/Edit GRN Modal -->
<div id="grnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="grnModalTitle" class="text-xl font-bold text-gray-900">Create Goods Received Note</h3>
                <button onclick="closeGRNModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="grnForm" class="p-6">
            @csrf
            <input type="hidden" id="grn_id" name="id">

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order (Optional)</label>
                            <select id="purchase_order_id" name="purchase_order_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" onchange="loadPOProducts(this.value)">
                                <option value="">Select Purchase Order</option>
                                @foreach($purchaseOrders ?? [] as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                            <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Section *</label>
                            <select id="business_section_id" name="business_section_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Business Section</option>
                                @foreach($businessSections ?? [] as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shop *</label>
                            <select id="shop_id" name="shop_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Shop</option>
                                @foreach($shops ?? [] as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->name ?? 'Shop #' . $shop->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Date *</label>
                            <input type="date" id="delivery_date" name="delivery_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Note Number</label>
                            <input type="text" id="delivery_note_number" name="delivery_note_number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>

                <!-- Received Items -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Received Items</h4>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accepted</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr id="noItemsRow">
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
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
                                    <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total Value:</td>
                                    <td colspan="3" class="px-4 py-3 text-lg font-bold text-primary" id="totalValue">KES 0.00</td>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quality Notes</label>
                            <textarea id="quality_notes" name="quality_notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">General Notes</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-between">
                <button type="button" onclick="saveAsDraft()" id="saveDraftBtn" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Save as Draft</button>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeGRNModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                    <button type="button" onclick="submitGRN()" id="saveGRNBtn" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700 font-medium">Submit GRN</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Create Product Modal -->
<div id="createProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Quick Add Product</h3>
                <button onclick="closeCreateProductModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="quickProductForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="quick_product_name" name="product_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Code *</label>
                        <input type="text" id="quick_product_code" name="product_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Auto-generated if empty">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Section *</label>
                        <select id="quick_business_section_id" name="business_section_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                            <option value="">Select Business Section</option>
                            @foreach($businessSections ?? [] as $section)
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                        <input type="number" id="quick_cost_price" name="cost_price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="quick_track_inventory" name="track_inventory" value="1" class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary" checked>
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

<!-- View GRN Modal -->
<div id="viewGRNModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="viewGRNModalTitle" class="text-xl font-bold text-gray-900">GRN Details</h3>
                <div class="flex items-center space-x-3">
                    <button onclick="printCurrentGRN()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg flex items-center text-sm"><i class="fas fa-print mr-2"></i> Print</button>
                    <button onclick="closeViewGRNModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-6" id="grnDetailsContent"></div>
    </div>
</div>

<!-- Quality Check Modal -->
<div id="qualityCheckModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quality Check</h3>
            <form id="qualityCheckForm">
                @csrf
                <input type="hidden" id="quality_grn_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quality Status *</label>
                        <select id="quality_status" name="quality_status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quality Notes</label>
                        <textarea id="quality_notes_form" name="quality_notes" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeQualityCheckModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" onclick="submitQualityCheck()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700">Submit Quality Check</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 id="statusModalTitle" class="text-lg font-bold text-gray-900 mb-4">Update GRN Status</h3>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="status_grn_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Status *</label>
                        <select id="status" name="status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="pending">Mark as Pending</option>
                            <option value="completed">Mark as Complete</option>
                            <option value="rejected">Reject GRN</option>
                        </select>
                    </div>
                    <div id="rejectionReasonSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" onclick="updateGRNStatus()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-red-700">Update Status</button>
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
            <h3 class="mt-4 text-lg font-bold text-gray-900 text-center">Delete GRN</h3>
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">Are you sure you want to delete this GRN?</p>
            <div class="mt-6 flex justify-center space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="deleteGRN()" id="confirmDeleteBtn" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
let currentGRNId = null;
let products = [];
let productsLoaded = false;
let itemCounter = 0;

// ====================== PRODUCT LOADING ======================
async function loadProducts() {
    return new Promise((resolve, reject) => {
        if (productsLoaded && products.length > 0) {
            resolve(products);
            return;
        }
        fetch('/cafeteria/api/products', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            products = data.data || data;
            productsLoaded = true;
            resolve(products);
        })
        .catch(error => { console.error('Error loading products:', error); reject(error); });
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
        options += `<option value="${product.id}" data-unit="${product.unit}" data-price="${product.cost_price || product.selling_price}" ${selected}>${product.product_name} (${product.product_code})</option>`;
    });
    return options;
}

// ====================== QUICK PRODUCT CREATION ======================
function showCreateProductModal() {
    document.getElementById('quickProductForm').reset();
    document.getElementById('quick_product_code').value = '';
    document.getElementById('quick_track_inventory').checked = true;
    document.getElementById('createProductModal').classList.remove('hidden');
}
function closeCreateProductModal() { document.getElementById('createProductModal').classList.add('hidden'); }

document.getElementById('quickProductForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const productName = document.getElementById('quick_product_name').value;
    const productCode = document.getElementById('quick_product_code').value;
    if (!productCode && productName) formData.set('product_code', productName.substring(0, 10).toUpperCase().replace(/\s+/g, '_'));
    formData.set('is_active', '1');
    formData.set('current_stock', '0');
    formData.set('track_inventory', formData.get('track_inventory') ? '1' : '0');
    formData.set('shop_id', document.getElementById('shop_id')?.value || 1);

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
        if (response.ok && (data.id || data.data?.id)) {
            const newProduct = data.id ? data : data.data;
            Swal.fire({ icon: 'success', title: 'Product Created!', text: `${newProduct.product_name} has been added.`, timer: 2000, showConfirmButton: false });
            productsLoaded = false;
            await loadProducts();
            await addItemRow(newProduct.id);
            closeCreateProductModal();
        } else {
            const errors = data.errors || {};
            Swal.fire({ icon: 'error', title: 'Error', text: Object.values(errors).flat()[0] || 'Error creating product' });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create product.' });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.getElementById('quick_product_name')?.addEventListener('blur', function() {
    const codeInput = document.getElementById('quick_product_code');
    if (!codeInput.value && this.value) codeInput.value = this.value.substring(0, 10).toUpperCase().replace(/\s+/g, '_');
});

// ====================== ITEMS MANAGEMENT ======================
async function addItemRow(productId = null, received = '', accepted = '', rejected = '', price = '', condition = 'good') {
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
            <select name="items[${rowId}][product_id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" onchange="updateProductPrice(this)" required>
                ${getProductOptions(productId)}
            </select>
            <button type="button" onclick="showCreateProductModal()" class="text-xs text-green-600 hover:text-green-800 mt-1">+ Create New Product</button>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][quantity_received]" min="0.001" step="0.001" value="${received}" oninput="calculateItemTotal(this)" class="received-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][quantity_accepted]" min="0" step="0.001" value="${accepted}" oninput="calculateItemTotal(this)" class="accepted-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][quantity_rejected]" min="0" step="0.001" value="${rejected}" oninput="calculateItemTotal(this)" class="rejected-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${rowId}][unit_price]" min="0" step="0.01" value="${price}" oninput="calculateItemTotal(this)" class="price-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" required>
        </td>
        <td class="px-4 py-3">
            <div class="item-total font-medium text-gray-900">KES 0.00</div>
        </td>
        <td class="px-4 py-3">
            <select name="items[${rowId}][condition]" class="condition-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
                <option value="good" ${condition === 'good' ? 'selected' : ''}>Good</option>
                <option value="damaged" ${condition === 'damaged' ? 'selected' : ''}>Damaged</option>
                <option value="expired" ${condition === 'expired' ? 'selected' : ''}>Expired</option>
                <option value="wrong_item" ${condition === 'wrong_item' ? 'selected' : ''}>Wrong Item</option>
            </select>
        </td>
        <td class="px-4 py-3">
            <button type="button" onclick="removeItemRow('${rowId}')" class="text-red-600 hover:text-red-900 p-1"><i class="fas fa-trash"></i></button>
        </td>
    `;
    tbody.appendChild(row);
    if (productId) updateProductPrice(row.querySelector('.product-select'));
    calculateItemTotal(row.querySelector('.accepted-input'));
    calculateTotals();
}

function updateProductPrice(select) {
    const row = select.closest('tr');
    const priceInput = row.querySelector('.price-input');
    const productId = select.value;
    if (productId) {
        const product = products.find(p => p.id == productId);
        if (product && product.cost_price) priceInput.value = product.cost_price;
        else if (product && product.selling_price) priceInput.value = product.selling_price;
        calculateItemTotal(priceInput);
    }
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

function calculateItemTotal(input) {
    const row = input.closest('tr');
    const accepted = parseFloat(row.querySelector('.accepted-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = accepted * price;
    row.querySelector('.item-total').textContent = 'KES ' + total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let totalValue = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const accepted = parseFloat(row.querySelector('.accepted-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        totalValue += accepted * price;
    });
    document.getElementById('totalValue').textContent = 'KES ' + totalValue.toFixed(2);
}

function getItemsData() {
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        const productId = row.querySelector('.product-select').value;
        const received = row.querySelector('.received-input').value;
        const accepted = row.querySelector('.accepted-input').value;
        const rejected = row.querySelector('.rejected-input').value;
        const price = row.querySelector('.price-input').value;
        const condition = row.querySelector('.condition-select').value;
        if (productId && received && accepted && price) {
            items.push({ product_id: productId, quantity_received: received, quantity_accepted: accepted, quantity_rejected: rejected || 0, unit_price: price, condition: condition });
        }
    });
    return items;
}

// ====================== LOAD PO PRODUCTS ======================
async function loadPOProducts(poId) {
    if (!poId) return;
    try {
        const response = await fetch(`/cafeteria/grn/by-purchase-order/${poId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (response.ok) {
            const poProducts = await response.json();
            document.querySelectorAll('.item-row').forEach(row => row.remove());
            for (const product of poProducts) {
                await addItemRow(product.product_id, product.quantity_ordered, product.quantity_ordered, 0, product.unit_price);
            }
        }
    } catch (error) { console.error('Error loading PO products:', error); }
}

// ====================== GRN MODAL FUNCTIONS ======================
async function openGRNModal(id = null) {
    await loadProducts();
    resetGRNForm();
    if (id) {
        document.getElementById('grnModalTitle').textContent = 'Edit GRN';
        document.getElementById('saveGRNBtn').textContent = 'Update GRN';
        currentGRNId = id;
        const response = await fetch(`/cafeteria/grn/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const grn = await response.json();
        document.getElementById('grn_id').value = grn.id;
        document.getElementById('purchase_order_id').value = grn.purchase_order_id || '';
        document.getElementById('supplier_id').value = grn.supplier_id;
        document.getElementById('business_section_id').value = grn.business_section_id;
        document.getElementById('shop_id').value = grn.shop_id;
        document.getElementById('delivery_date').value = grn.delivery_date?.split('T')[0] || '';
        document.getElementById('delivery_note_number').value = grn.delivery_note_number || '';
        document.getElementById('quality_notes').value = grn.quality_notes || '';
        document.getElementById('notes').value = grn.notes || '';
        for (const item of grn.items) {
            await addItemRow(item.product_id, item.quantity_received, item.quantity_accepted, item.quantity_rejected, item.unit_price, item.condition);
        }
        if (grn.status !== 'draft') document.getElementById('saveDraftBtn').classList.add('hidden');
    } else {
        document.getElementById('grnModalTitle').textContent = 'Create GRN';
        document.getElementById('saveGRNBtn').textContent = 'Submit GRN';
        currentGRNId = null;
        await addItemRow();
    }
    document.getElementById('grnModal').classList.remove('hidden');
}

function closeGRNModal() { document.getElementById('grnModal').classList.add('hidden'); }
function resetGRNForm() {
    document.getElementById('grnForm').reset();
    document.getElementById('grn_id').value = '';
    document.getElementById('delivery_date').value = new Date().toISOString().split('T')[0];
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = '';
    document.getElementById('itemsTableFooter').classList.add('hidden');
    itemCounter = 0;
    document.getElementById('saveDraftBtn').classList.remove('hidden');
}
function submitGRN() { saveGRN(false); }
function saveAsDraft() { saveGRN(true); }

async function saveGRN(isDraft = false) {
    const items = getItemsData();
    if (items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one item.' });
        return;
    }
    const formData = new FormData(document.getElementById('grnForm'));
    items.forEach((item, index) => { Object.entries(item).forEach(([key, value]) => formData.append(`items[${index}][${key}]`, value)); });
    const isEdit = !!currentGRNId;
    const url = isEdit ? `/cafeteria/grn/${currentGRNId}` : '/cafeteria/grn';
    if (isEdit) formData.append('_method', 'PUT');
    const btn = document.getElementById('saveGRNBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    try {
        const response = await fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Success', text: data.message, timer: 2000, showConfirmButton: false });
            closeGRNModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error saving GRN' });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ====================== VIEW FUNCTIONS ======================
async function viewGRN(id) {
    const response = await fetch(`/cafeteria/grn/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    const grn = await response.json();
    document.getElementById('viewGRNModalTitle').textContent = grn.grn_number;
    document.getElementById('grnDetailsContent').innerHTML = `
        <div class="space-y-6">
            <div class="flex justify-between items-start">
                <div><h2 class="text-2xl font-bold text-gray-900">${grn.grn_number}</h2></div>
                <div class="text-right"><div class="text-lg font-bold text-primary">KES ${(grn.total_value || 0).toFixed(2)}</div></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Supplier Information</h4><p><strong>Name:</strong> ${grn.supplier?.supplier_name || 'N/A'}</p><p><strong>Business Section:</strong> ${grn.business_section?.section_name || 'N/A'}</p></div>
                <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Delivery Information</h4><p><strong>Delivery Date:</strong> ${grn.delivery_date ? new Date(grn.delivery_date).toLocaleDateString() : 'N/A'}</p><p><strong>Delivery Note:</strong> ${grn.delivery_note_number || 'N/A'}</p></div>
            </div>
            <div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-4">Received Items</h4>
                <table class="min-w-full"><thead><tr><th>Product</th><th>Received</th><th>Accepted</th><th>Rejected</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>${(grn.items || []).map(item => `<tr><td>${item.product_name}</td><td>${item.quantity_received}</td><td>${item.quantity_accepted}</td><td>${item.quantity_rejected || 0}</td><td>KES ${(item.unit_price || 0).toFixed(2)}</td><td>KES ${(item.total_value || 0).toFixed(2)}</td></tr>`).join('')}</tbody>
                <tfoot><tr><td colspan="5" class="text-right"><strong>Total:</strong></td><td><strong>KES ${(grn.total_value || 0).toFixed(2)}</strong></td></tr></tfoot>
                </table>
            </div>
            ${grn.quality_notes ? `<div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-2">Quality Notes</h4><p>${grn.quality_notes}</p></div>` : ''}
            ${grn.notes ? `<div class="bg-gray-50 p-6 rounded-xl"><h4 class="font-medium mb-2">Notes</h4><p>${grn.notes}</p></div>` : ''}
        </div>
    `;
    document.getElementById('viewGRNModal').classList.remove('hidden');
}
function closeViewGRNModal() { document.getElementById('viewGRNModal').classList.add('hidden'); }
function printCurrentGRN() { window.print(); }
function editGRN(id) { openGRNModal(id); }
function printGRN(id) { window.open(`/cafeteria/grn/${id}/print`, '_blank'); }

// ====================== QUALITY & STATUS FUNCTIONS ======================
function openQualityCheckModal(id) {
    currentGRNId = id;
    document.getElementById('quality_grn_id').value = id;
    document.getElementById('qualityCheckModal').classList.remove('hidden');
}
function closeQualityCheckModal() { document.getElementById('qualityCheckModal').classList.add('hidden'); }

async function submitQualityCheck() {
    const qualityStatus = document.getElementById('quality_status').value;
    const qualityNotes = document.getElementById('quality_notes_form').value;
    if (!qualityStatus) { Swal.fire({ icon: 'warning', title: 'Missing', text: 'Please select quality status' }); return; }
    const response = await fetch(`/cafeteria/grn/${currentGRNId}/quality-check`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ quality_status: qualityStatus, quality_notes: qualityNotes })
    });
    const data = await response.json();
    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Updated' : 'Error', text: data.message });
    if (data.success) { closeQualityCheckModal(); setTimeout(() => location.reload(), 1500); }
}

function completeGRN(id) { openStatusModal(id, 'completed', 'Complete GRN'); }
function openStatusModal(id, defaultStatus, title) {
    currentGRNId = id;
    document.getElementById('status_grn_id').value = id;
    document.getElementById('statusModalTitle').textContent = title;
    document.getElementById('status').value = defaultStatus;
    document.getElementById('rejectionReasonSection').classList.toggle('hidden', defaultStatus !== 'rejected');
    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); }

async function updateGRNStatus() {
    const status = document.getElementById('status').value;
    const rejectionReason = document.getElementById('rejection_reason').value;
    if (!status) { Swal.fire({ icon: 'warning', title: 'Missing', text: 'Please select a status' }); return; }
    const response = await fetch(`/cafeteria/grn/${currentGRNId}/update-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ status, rejection_reason: status === 'rejected' ? rejectionReason : null })
    });
    const data = await response.json();
    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Updated' : 'Error', text: data.message });
    if (data.success) { closeStatusModal(); setTimeout(() => location.reload(), 1500); }
}

// ====================== DELETE FUNCTIONS ======================
function confirmDeleteGRN(id) {
    currentGRNId = id;
    Swal.fire({
        title: 'Delete GRN?', text: "This action cannot be undone!", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!'
    }).then((result) => { if (result.isConfirmed) deleteGRN(); });
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); }
async function deleteGRN() {
    const response = await fetch(`/cafeteria/grn/${currentGRNId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const data = await response.json();
    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Deleted!' : 'Error', text: data.message });
    if (data.success) setTimeout(() => location.reload(), 1500);
}

// ====================== FILTER FUNCTIONS ======================
function applyFilters() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput')?.value;
    const status = document.getElementById('statusFilter')?.value;
    const qualityStatus = document.getElementById('qualityStatusFilter')?.value;
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (qualityStatus) params.set('quality_status', qualityStatus);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}
function filterByStatus(status) {
    const url = new URL(window.location.href);
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
function filterByQualityStatus(qualityStatus) {
    const url = new URL(window.location.href);
    if (qualityStatus) url.searchParams.set('quality_status', qualityStatus);
    else url.searchParams.delete('quality_status');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
function exportGRNs() { window.open('/cafeteria/grn/export', '_blank'); }

document.getElementById('searchInput')?.addEventListener('keyup', e => { if (e.key === 'Enter') applyFilters(); });
document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
document.getElementById('qualityStatusFilter')?.addEventListener('change', applyFilters);
document.getElementById('status')?.addEventListener('change', function() {
    document.getElementById('rejectionReasonSection').classList.toggle('hidden', this.value !== 'rejected');
});

// Initialize
loadProducts();
</script>
@endsection
