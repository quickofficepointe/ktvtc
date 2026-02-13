@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Goods Received Notes')
@section('page-title', 'GRN Management')
@section('page-description', 'Record and manage received goods')

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
        Goods Received Notes
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
                    <i class="fas fa-clipboard-check text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total GRNs</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalGrns }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
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
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $completedCount }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                    <i class="fas fa-flask text-gray-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending QC</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingQualityCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Receipts -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">Today's Receipts</h3>
                <p class="text-sm text-gray-600 mt-1">GRNs received today: {{ $todayGrns->count() }}</p>
            </div>
            <button onclick="openGRNModal()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-clipboard-check mr-2"></i> New GRN
            </button>
        </div>

        @if($todayGrns->count() > 0)
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
                        <td class="px-6 py-4">{{ $grn->supplier->supplier_name }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $grn->total_items }} items</div>
                            <div class="text-sm text-gray-500">{{ $grn->total_quantity }} units</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $qualityStatusColors[$grn->quality_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($grn->quality_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewGRN({{ $grn->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($grn->status == 'draft')
                                <button onclick="editGRN({{ $grn->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
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
            <button onclick="openGRNModal()" class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center mx-auto">
                <i class="fas fa-clipboard-check mr-2"></i> Create First GRN
            </button>
        </div>
        @endif
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
                           placeholder="Search GRN or supplier..."
                           value="{{ request('search') }}"
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>

                <!-- Status Filter -->
                <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <!-- Quality Status Filter -->
                <select id="qualityStatusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Quality Status</option>
                    <option value="pending" {{ request('quality_status') == 'pending' ? 'selected' : '' }}>Pending QC</option>
                    <option value="passed" {{ request('quality_status') == 'passed' ? 'selected' : '' }}>Passed</option>
                    <option value="failed" {{ request('quality_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="partial" {{ request('quality_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <!-- Export Button -->
                <button onclick="exportGRNs()" class="btn-secondary py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>

                <!-- Filter Button -->
                <button onclick="applyFilters()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <nav class="flex overflow-x-auto">
            <button onclick="filterByStatus('')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ !request('status') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All GRNs
                <span class="ml-2 bg-gray-100 text-gray-900 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $totalGrns }}</span>
            </button>
            <button onclick="filterByStatus('draft')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'draft' ? 'border-gray-400 text-gray-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Draft
                <span class="ml-2 bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $draftCount }}</span>
            </button>
            <button onclick="filterByStatus('pending')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending
                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
            </button>
            <button onclick="filterByStatus('completed')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('status') == 'completed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Completed
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $completedCount }}</span>
            </button>
            <button onclick="filterByQualityStatus('pending')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('quality_status') == 'pending' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending QC
                <span class="ml-2 bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingQualityCount }}</span>
            </button>
        </nav>
    </div>

    <!-- GRNs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            GRN Number
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            PO Reference
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Delivery Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Items Received
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Value
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quality Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($grns as $grn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $grn->grn_number }}</div>
                            <div class="text-sm text-gray-500">{{ $grn->delivery_note_number ?? 'No DN#' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($grn->purchaseOrder)
                            <span class="text-blue-600 font-medium">{{ $grn->purchaseOrder->po_number }}</span>
                            @else
                            <span class="text-gray-400">Direct Purchase</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $grn->supplier->supplier_name }}</div>
                            <div class="text-sm text-gray-500">{{ $grn->businessSection->section_name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $grn->delivery_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $grn->total_items }} items</div>
                            <div class="text-sm text-gray-500">{{ $grn->total_quantity }} units</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-lg text-primary">
                                KES {{ number_format($grn->total_value, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$grn->status] }}">
                                {{ ucfirst($grn->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $qualityStatusColors[$grn->quality_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($grn->quality_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <button onclick="viewGRN({{ $grn->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Edit Button (only for draft) -->
                                @if($grn->status == 'draft')
                                <button onclick="editGRN({{ $grn->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif

                                <!-- Quality Check Button -->
                                @if($grn->quality_status == 'pending' && $grn->status != 'draft')
                                <button onclick="openQualityCheckModal({{ $grn->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Quality Check">
                                    <i class="fas fa-flask"></i>
                                </button>
                                @endif

                                <!-- Complete Button -->
                                @if($grn->status == 'pending')
                                <button onclick="completeGRN({{ $grn->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Mark as Complete">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                @endif

                                <!-- Print Button -->
                                <button onclick="printGRN({{ $grn->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Print">
                                    <i class="fas fa-print"></i>
                                </button>

                                <!-- Delete Button (only for draft) -->
                                @if($grn->status == 'draft')
                                <button onclick="confirmDeleteGRN({{ $grn->id }})"
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
                                <i class="fas fa-clipboard-check text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-700">No goods received notes found</p>
                                <p class="text-gray-500 mt-1">Start by creating your first GRN</p>
                                <button onclick="openGRNModal()"
                                        class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-clipboard-check mr-2"></i> Create GRN
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($grns->hasPages())
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
                        <!-- Purchase Order -->
                        <div>
                            <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Purchase Order (Optional)
                            </label>
                            <select id="purchase_order_id" name="purchase_order_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                                    onchange="loadPOProducts(this.value)">
                                <option value="">Select Purchase Order</option>
                                @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                        </div>

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

                        <!-- Delivery Date -->
                        <div>
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Date *
                            </label>
                            <input type="date"
                                   id="delivery_date"
                                   name="delivery_date"
                                   required
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                            <div id="delivery_date_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Delivery Note Number -->
                        <div>
                            <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Note Number
                            </label>
                            <input type="text"
                                   id="delivery_note_number"
                                   name="delivery_note_number"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="vehicle_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Vehicle Number
                            </label>
                            <input type="text"
                                   id="vehicle_number"
                                   name="vehicle_number"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="driver_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Driver Name
                            </label>
                            <input type="text"
                                   id="driver_name"
                                   name="driver_name"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label for="driver_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Driver Phone
                            </label>
                            <input type="text"
                                   id="driver_phone"
                                   name="driver_phone"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Received Items -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Received Items</h4>
                        <button type="button" onclick="addItemRow()" class="btn-primary text-white py-2 px-4 rounded-lg flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
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
                                <!-- Items will be added here dynamically -->
                                <tr id="noItemsRow">
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
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
                                    <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Total Value:
                                    </td>
                                    <td colspan="3" class="px-4 py-3 text-lg font-bold text-primary" id="totalValue">
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
                            <label for="quality_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Quality Notes
                            </label>
                            <textarea id="quality_notes"
                                      name="quality_notes"
                                      rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                General Notes
                            </label>
                            <textarea id="notes"
                                      name="notes"
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
                            onclick="closeGRNModal()"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            onclick="submitGRN()"
                            id="saveGRNBtn"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                        Submit GRN
                    </button>
                </div>
            </div>
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
                    <button onclick="printCurrentGRN()" class="btn-secondary py-2 px-4 rounded-lg flex items-center text-sm">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <button onclick="closeViewGRNModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6" id="grnDetailsContent">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Quality Check Modal -->
<div id="qualityCheckModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 id="qualityCheckModalTitle" class="text-lg font-bold text-gray-900 mb-4">Quality Check</h3>

            <form id="qualityCheckForm">
                @csrf
                <input type="hidden" id="quality_grn_id">

                <div class="space-y-4">
                    <div>
                        <label for="quality_status" class="block text-sm font-medium text-gray-700 mb-2">
                            Quality Status *
                        </label>
                        <select id="quality_status" name="quality_status" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>

                    <div>
                        <label for="quality_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Quality Notes
                        </label>
                        <textarea id="quality_notes_form" name="quality_notes" rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeQualityCheckModal()"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            onclick="submitQualityCheck()"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                        Submit Quality Check
                    </button>
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
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            New Status *
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Status</option>
                            <option value="pending">Mark as Pending</option>
                            <option value="completed">Mark as Complete</option>
                            <option value="rejected">Reject GRN</option>
                        </select>
                    </div>

                    <div id="rejectionReasonSection" class="hidden">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3"
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
                            onclick="updateGRNStatus()"
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
            <h3 class="mt-4 text-lg font-bold text-gray-900 text-center">Delete GRN</h3>
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">
                Are you sure you want to delete this GRN?
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="deleteGRN()"
                        id="confirmDeleteBtn"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Delete GRN
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
let currentGRNId = null;
let products = [];
let itemCounter = 0;

// DOM Elements
const grnModal = document.getElementById('grnModal');
const viewGRNModal = document.getElementById('viewGRNModal');
const qualityCheckModal = document.getElementById('qualityCheckModal');
const statusModal = document.getElementById('statusModal');
const deleteModal = document.getElementById('deleteModal');
const grnForm = document.getElementById('grnForm');
const toast = document.getElementById('toast');

// ======================
// FILTERS & NAVIGATION
// ======================

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const qualityStatus = document.getElementById('qualityStatusFilter').value;
    const url = new URL(window.location.href);

    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');

    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');

    if (qualityStatus) url.searchParams.set('quality_status', qualityStatus);
    else url.searchParams.delete('quality_status');

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

    url.searchParams.delete('quality_status');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterByQualityStatus(qualityStatus) {
    const url = new URL(window.location.href);

    if (qualityStatus) {
        url.searchParams.set('quality_status', qualityStatus);
    } else {
        url.searchParams.delete('quality_status');
    }

    url.searchParams.delete('status');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Event Listeners
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') applyFilters();
});

// ======================
// PRODUCTS MANAGEMENT
// ======================

async function loadPOProducts(poId) {
    if (!poId) return;

    try {
        const response = await fetch(`/grn/by-purchase-order/${poId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const products = await response.json();

            // Clear existing items
            document.querySelectorAll('.item-row').forEach(row => row.remove());

            // Add products from PO
            products.forEach(product => {
                addItemRow(
                    product.id,
                    product.quantity_ordered - (product.quantity_received || 0),
                    0,
                    0,
                    product.unit_price
                );
            });
        }
    } catch (error) {
        console.error('Error loading PO products:', error);
    }
}

// ======================
// ITEMS MANAGEMENT
// ======================

function addItemRow(product = null, received = '', accepted = '', rejected = '', price = '', condition = 'good', batch = '', mfgDate = '', expDate = '') {
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
                    onchange="updateItemDetails(this)">
                <option value="">Select Product</option>
                <!-- Products will be loaded dynamically -->
            </select>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <input type="text"
                       name="items[${rowId}][batch_number]"
                       placeholder="Batch No"
                       value="${batch}"
                       class="px-2 py-1 text-xs border border-gray-300 rounded">
                <input type="date"
                       name="items[${rowId}][manufacturing_date]"
                       value="${mfgDate}"
                       class="px-2 py-1 text-xs border border-gray-300 rounded">
                <input type="date"
                       name="items[${rowId}][expiry_date]"
                       value="${expDate}"
                       class="px-2 py-1 text-xs border border-gray-300 rounded">
            </div>
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][quantity_received]"
                   min="0.001"
                   step="0.001"
                   value="${received}"
                   oninput="calculateItemTotal(this)"
                   class="received-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][quantity_accepted]"
                   min="0"
                   step="0.001"
                   value="${accepted}"
                   oninput="calculateItemTotal(this)"
                   class="accepted-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][quantity_rejected]"
                   min="0"
                   step="0.001"
                   value="${rejected}"
                   oninput="calculateItemTotal(this)"
                   class="rejected-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
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
            <div class="item-total font-medium text-gray-900">KES 0.00</div>
        </td>
        <td class="px-4 py-3">
            <select name="items[${rowId}][condition]"
                    class="condition-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
                <option value="good" ${condition === 'good' ? 'selected' : ''}>Good</option>
                <option value="damaged" ${condition === 'damaged' ? 'selected' : ''}>Damaged</option>
                <option value="expired" ${condition === 'expired' ? 'selected' : ''}>Expired</option>
                <option value="wrong_item" ${condition === 'wrong_item' ? 'selected' : ''}>Wrong Item</option>
            </select>
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

    // Calculate initial total
    calculateItemTotal(row.querySelector('.accepted-input'));

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

function updateItemDetails(select) {
    // Could load product details here if needed
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
        const batch = row.querySelector('input[name*="batch_number"]').value;
        const mfgDate = row.querySelector('input[name*="manufacturing_date"]').value;
        const expDate = row.querySelector('input[name*="expiry_date"]').value;

        if (productId && received && accepted && price) {
            items.push({
                product_id: productId,
                quantity_received: received,
                quantity_accepted: accepted,
                quantity_rejected: rejected || 0,
                unit_price: price,
                condition: condition,
                batch_number: batch || null,
                manufacturing_date: mfgDate || null,
                expiry_date: expDate || null
            });
        }
    });
    return items;
}

// ======================
// GRN MODAL FUNCTIONS
// ======================

function openGRNModal(id = null) {
    resetGRNForm();
    clearErrors();

    if (id) {
        // Edit mode
        document.getElementById('grnModalTitle').textContent = 'Edit Goods Received Note';
        document.getElementById('saveGRNBtn').textContent = 'Update GRN';
        currentGRNId = id;

        // Load GRN data
        fetch(`/grn/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(grn => {
            populateGRNForm(grn);
            grnModal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading GRN data', 'error');
        });
    } else {
        // Create mode
        document.getElementById('grnModalTitle').textContent = 'Create Goods Received Note';
        document.getElementById('saveGRNBtn').textContent = 'Submit GRN';
        document.getElementById('saveDraftBtn').classList.remove('hidden');
        currentGRNId = null;
        grnModal.classList.remove('hidden');
    }
}

function closeGRNModal() {
    grnModal.classList.add('hidden');
    resetGRNForm();
    clearErrors();
}

function populateGRNForm(grn) {
    document.getElementById('grn_id').value = grn.id;
    document.getElementById('purchase_order_id').value = grn.purchase_order_id || '';
    document.getElementById('supplier_id').value = grn.supplier_id;
    document.getElementById('business_section_id').value = grn.business_section_id;
    document.getElementById('shop_id').value = grn.shop_id;
    document.getElementById('delivery_date').value = grn.delivery_date.split('T')[0];
    document.getElementById('delivery_note_number').value = grn.delivery_note_number || '';
    document.getElementById('vehicle_number').value = grn.vehicle_number || '';
    document.getElementById('driver_name').value = grn.driver_name || '';
    document.getElementById('driver_phone').value = grn.driver_phone || '';
    document.getElementById('quality_notes').value = grn.quality_notes || '';
    document.getElementById('notes').value = grn.notes || '';

    // Clear existing items
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = 'none';
    document.getElementById('itemsTableFooter').classList.remove('hidden');

    // Add items
    grn.items.forEach(item => {
        addItemRow(
            item.product_id,
            item.quantity_received,
            item.quantity_accepted,
            item.quantity_rejected,
            item.unit_price,
            item.condition,
            item.batch_number,
            item.manufacturing_date,
            item.expiry_date
        );
    });

    // Hide save draft button for non-draft GRNs
    if (grn.status !== 'draft') {
        document.getElementById('saveDraftBtn').classList.add('hidden');
    }
}

function resetGRNForm() {
    grnForm.reset();
    document.getElementById('grn_id').value = '';
    document.getElementById('delivery_date').value = new Date().toISOString().split('T')[0];

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

function submitGRN() {
    const items = getItemsData();
    if (items.length === 0) {
        showToast('Please add at least one item', 'error');
        return;
    }

    saveGRN(false);
}

function saveAsDraft() {
    const items = getItemsData();
    if (items.length === 0) {
        showToast('Please add at least one item', 'error');
        return;
    }

    saveGRN(true);
}

function saveGRN(isDraft = false) {
    const formData = new FormData(grnForm);
    const items = getItemsData();

    // Add items to form data
    items.forEach((item, index) => {
        for (const [key, value] of Object.entries(item)) {
            if (value !== null && value !== undefined) {
                formData.append(`items[${index}][${key}]`, value);
            }
        }
    });

    const isEdit = !!currentGRNId;
    const url = isEdit ? `/grn/${currentGRNId}` : '/grn';
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
            closeGRNModal();

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
// VIEW GRN
// ======================

function viewGRN(id) {
    fetch(`/grn/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(grn => {
        document.getElementById('viewGRNModalTitle').textContent = grn.grn_number;

        const content = `
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">${grn.grn_number}</h2>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="px-3 py-1 text-sm font-medium rounded-full ${getStatusColor(grn.status)}">
                                ${formatStatus(grn.status)}
                            </span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full ${getQualityStatusColor(grn.quality_status)}">
                                ${formatQualityStatus(grn.quality_status)}
                            </span>
                            <span class="text-gray-600">Received: ${formatDate(grn.received_date)}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-primary">KES ${grn.total_value.toFixed(2)}</div>
                        <div class="text-sm text-gray-600">Total Value</div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Supplier Name</p>
                                <p class="font-medium text-gray-900">${grn.supplier.supplier_name}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Business Section</p>
                                <p class="font-medium text-gray-900">${grn.business_section ? grn.business_section.section_name : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Shop</p>
                                <p class="font-medium text-gray-900">${grn.shop ? grn.shop.name : 'N/A'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Delivery Information</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Delivery Date</p>
                                <p class="font-medium text-gray-900">${formatDate(grn.delivery_date)}</p>
                            </div>
                            ${grn.delivery_note_number ? `
                            <div>
                                <p class="text-sm text-gray-600">Delivery Note Number</p>
                                <p class="font-medium text-gray-900">${grn.delivery_note_number}</p>
                            </div>
                            ` : ''}
                            ${grn.vehicle_number ? `
                            <div>
                                <p class="text-sm text-gray-600">Vehicle Number</p>
                                <p class="font-medium text-gray-900">${grn.vehicle_number}</p>
                            </div>
                            ` : ''}
                            ${grn.driver_name ? `
                            <div>
                                <p class="text-sm text-gray-600">Driver</p>
                                <p class="font-medium text-gray-900">${grn.driver_name} ${grn.driver_phone ? '(' + grn.driver_phone + ')' : ''}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Received Items</h4>
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
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${grn.items.map(item => `
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">${item.product_name}</div>
                                        <div class="text-sm text-gray-500">${item.product_code}</div>
                                        ${item.batch_number ? `<div class="text-xs text-gray-400">Batch: ${item.batch_number}</div>` : ''}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">${item.quantity_received}</div>
                                        <div class="text-sm text-gray-500">${item.unit}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        ${item.quantity_accepted}
                                    </td>
                                    <td class="px-4 py-3">
                                        ${item.quantity_rejected || 0}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        KES ${item.unit_price.toFixed(2)}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        KES ${item.total_value.toFixed(2)}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full ${getConditionColor(item.condition)}">
                                            ${formatCondition(item.condition)}
                                        </span>
                                    </td>
                                </tr>
                                `).join('')}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Total Value:
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-lg font-bold text-primary">
                                        KES ${grn.total_value.toFixed(2)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                ${(grn.quality_notes || grn.notes) ? `
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        ${grn.quality_notes ? `
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Quality Notes</p>
                            <p class="text-gray-600 whitespace-pre-line">${grn.quality_notes}</p>
                        </div>
                        ` : ''}
                        ${grn.notes ? `
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">General Notes</p>
                            <p class="text-gray-600 whitespace-pre-line">${grn.notes}</p>
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
                            <p class="text-sm text-gray-600">Received By</p>
                            <p class="font-medium text-gray-900">${grn.receiver ? grn.receiver.name : 'System'}</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDate(grn.received_date)}</p>
                        </div>
                        ${grn.quality_checked_by ? `
                        <div>
                            <p class="text-sm text-gray-600">Quality Checked By</p>
                            <p class="font-medium text-gray-900">${grn.quality_checker ? grn.quality_checker.name : 'System'}</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDate(grn.quality_checked_at)}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('grnDetailsContent').innerHTML = content;
        viewGRNModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error loading GRN details', 'error');
    });
}

function closeViewGRNModal() {
    viewGRNModal.classList.add('hidden');
}

function printCurrentGRN() {
    window.print();
}

// ======================
// STATUS & QUALITY MANAGEMENT
// ======================

function openQualityCheckModal(id) {
    currentGRNId = id;
    document.getElementById('quality_grn_id').value = id;
    document.getElementById('qualityCheckModalTitle').textContent = 'Quality Check - GRN';
    qualityCheckModal.classList.remove('hidden');
}

function closeQualityCheckModal() {
    qualityCheckModal.classList.add('hidden');
    document.getElementById('qualityCheckForm').reset();
    currentGRNId = null;
}

function submitQualityCheck() {
    const qualityStatus = document.getElementById('quality_status').value;
    const qualityNotes = document.getElementById('quality_notes_form').value;

    if (!qualityStatus) {
        showToast('Please select quality status', 'error');
        return;
    }

    const data = {
        quality_status: qualityStatus,
        quality_notes: qualityNotes
    };

    fetch(`/grn/${currentGRNId}/quality-check`, {
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
            closeQualityCheckModal();

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

function completeGRN(id) {
    openStatusModal(id, 'completed', 'Complete GRN');
}

function openStatusModal(id, defaultStatus = '', title = 'Update GRN Status') {
    currentGRNId = id;
    document.getElementById('status_grn_id').value = id;
    document.getElementById('statusModalTitle').textContent = title;
    document.getElementById('status').value = defaultStatus;

    // Show/hide rejection reason section
    const rejectionSection = document.getElementById('rejectionReasonSection');
    rejectionSection.classList.toggle('hidden', defaultStatus !== 'rejected');

    statusModal.classList.remove('hidden');
}

function closeStatusModal() {
    statusModal.classList.add('hidden');
    document.getElementById('statusForm').reset();
    currentGRNId = null;
}

function updateGRNStatus() {
    const status = document.getElementById('status').value;
    const rejectionReason = document.getElementById('rejection_reason').value;

    if (!status) {
        showToast('Please select a status', 'error');
        return;
    }

    const data = {
        status: status,
        rejection_reason: status === 'rejected' ? rejectionReason : null
    };

    fetch(`/grn/${currentGRNId}/update-status`, {
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

function confirmDeleteGRN(id) {
    currentGRNId = id;
    document.getElementById('deleteMessage').textContent =
        'Are you sure you want to delete this GRN? This action cannot be undone.';
    deleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    currentGRNId = null;
}

function deleteGRN() {
    if (!currentGRNId) return;

    fetch(`/grn/${currentGRNId}`, {
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
// HELPER FUNCTIONS
// ======================

function getStatusColor(status) {
    const colors = {
        'draft': 'bg-gray-100 text-gray-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getQualityStatusColor(status) {
    const colors = {
        'pending': 'bg-gray-100 text-gray-800',
        'passed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'partial': 'bg-yellow-100 text-yellow-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getConditionColor(condition) {
    const colors = {
        'good': 'bg-green-100 text-green-800',
        'damaged': 'bg-red-100 text-red-800',
        'expired': 'bg-red-100 text-red-800',
        'wrong_item': 'bg-yellow-100 text-yellow-800'
    };
    return colors[condition] || 'bg-gray-100 text-gray-800';
}

function formatStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatQualityStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatCondition(condition) {
    return condition.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
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
        closeGRNModal();
        closeViewGRNModal();
        closeQualityCheckModal();
        closeStatusModal();
        closeDeleteModal();
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target === grnModal) closeGRNModal();
    if (e.target === viewGRNModal) closeViewGRNModal();
    if (e.target === qualityCheckModal) closeQualityCheckModal();
    if (e.target === statusModal) closeStatusModal();
    if (e.target === deleteModal) closeDeleteModal();
});

// Status select change
document.getElementById('status').addEventListener('change', function() {
    const rejectionSection = document.getElementById('rejectionReasonSection');
    rejectionSection.classList.toggle('hidden', this.value !== 'rejected');
});

// Quick actions from table
function editGRN(id) {
    openGRNModal(id);
}

function printGRN(id) {
    window.open(`/grn/${id}/print`, '_blank');
}
</script>
@endsection
