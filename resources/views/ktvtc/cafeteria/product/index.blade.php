@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Products Management')
@section('page-title', 'Products Management')
@section('page-description', 'Manage all products across different business sections')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Inventory
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Products
    </span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header with Stats and Actions -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-box text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Active Products</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['low_stock'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Featured</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['featured'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mr-4">
                    <i class="fas fa-industry text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Production Items</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['production'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Action Buttons -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <form method="GET" action="{{ route('cafeteria.products.index') }}" id="filterForm" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               id="searchInput"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Search products..."
                               class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>

                    <select name="business_section_id" id="sectionFilter" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Sections</option>
                        @foreach($businessSections as $section)
                        <option value="{{ $section->id }}" {{ ($filters['business_section_id'] ?? '') == $section->id ? 'selected' : '' }}>
                            {{ $section->section_code }} - {{ $section->section_name }}
                        </option>
                        @endforeach
                    </select>

                    <select name="category_id" id="categoryFilter" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_code }} - {{ $category->category_name }}
                        </option>
                        @endforeach
                    </select>

                    <select name="product_type" id="typeFilter" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Types</option>
                        <option value="food" {{ ($filters['product_type'] ?? '') == 'food' ? 'selected' : '' }}>Food</option>
                        <option value="beverage" {{ ($filters['product_type'] ?? '') == 'beverage' ? 'selected' : '' }}>Beverage</option>
                        <option value="gift" {{ ($filters['product_type'] ?? '') == 'gift' ? 'selected' : '' }}>Gift</option>
                        <option value="raw_material" {{ ($filters['product_type'] ?? '') == 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                        <option value="stationery" {{ ($filters['product_type'] ?? '') == 'stationery' ? 'selected' : '' }}>Stationery</option>
                        <option value="uniform" {{ ($filters['product_type'] ?? '') == 'uniform' ? 'selected' : '' }}>Uniform</option>
                        <option value="other" {{ ($filters['product_type'] ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>

                    <select name="is_active" id="statusFilter" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Status</option>
                        <option value="1" {{ isset($filters['is_active']) && $filters['is_active'] === '1' ? 'selected' : '' }}>Active Only</option>
                        <option value="0" {{ isset($filters['is_active']) && $filters['is_active'] === '0' ? 'selected' : '' }}>Inactive Only</option>
                    </select>

                    <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>

                    @if(count($filters) > 0)
                    <a href="{{ route('cafeteria.products.index') }}" class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                    @endif
                </form>
            </div>

            <div class="flex gap-3">
                <button onclick="exportToExcel()"
                        class="bg-white text-primary border border-primary font-medium py-2 px-4 rounded-lg flex items-center hover:bg-primary hover:text-white transition">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>

                <button onclick="showBulkUpdateModal()"
                        class="bg-white text-purple-600 border border-purple-600 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-purple-600 hover:text-white transition">
                    <i class="fas fa-bolt mr-2"></i> Bulk Update
                </button>

                <button onclick="showCreateModal()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Product
                </button>
            </div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">All Products</h3>
                <div class="flex items-center space-x-3">
                    <button onclick="toggleViewMode('grid')"
                            class="text-gray-600 hover:text-primary p-2 view-mode-btn" data-mode="grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button onclick="toggleViewMode('list')"
                            class="text-gray-600 hover:text-primary p-2 view-mode-btn active" data-mode="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div id="gridView" class="p-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="productsGrid">
                @forelse($products as $product)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
                    <!-- Product Image -->
                    <div class="h-48 bg-gray-100 flex items-center justify-center relative">
                        @if($product->image && Storage::exists($product->image))
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-box text-gray-300 text-4xl"></i>
                        @endif
                        @if($product->is_featured)
                            <div class="absolute top-2 left-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1"></i> Featured
                                </span>
                            </div>
                        @endif
                        @if($product->track_inventory && $product->current_stock <= $product->reorder_level)
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium text-gray-900 truncate">{{ $product->product_name }}</h4>
                                <div class="text-sm text-gray-500">{{ $product->product_code }}</div>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $product->product_type == 'food' ? 'bg-orange-100 text-orange-800' : ($product->product_type == 'beverage' ? 'bg-blue-100 text-blue-800' : ($product->product_type == 'gift' ? 'bg-purple-100 text-purple-800' : ($product->product_type == 'raw_material' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) }}">
                                {{ ucfirst(str_replace('_', ' ', $product->product_type)) }}
                            </span>
                        </div>

                        <div class="text-sm text-gray-600 mb-3">
                            {{ $product->businessSection->section_name ?? '' }} • {{ $product->category->category_name ?? 'No Category' }}
                        </div>

                        <!-- Pricing -->
                        <div class="mb-3">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-primary">KES {{ number_format($product->selling_price, 2) }}</span>
                                @if($product->cost_price && $product->cost_price > 0)
                                    @php
                                        $profitMargin = (($product->selling_price - $product->cost_price) / $product->cost_price) * 100;
                                    @endphp
                                    <span class="text-xs {{ $profitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($profitMargin, 1) }}% margin
                                    </span>
                                @endif
                            </div>
                            @if($product->cost_price && $product->cost_price > 0)
                                <div class="text-xs text-gray-500">Cost: KES {{ number_format($product->cost_price, 2) }}</div>
                            @endif
                        </div>

                        <!-- Stock Info -->
                        @if($product->track_inventory)
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Stock Level</span>
                                <span class="font-medium {{ $product->current_stock <= $product->reorder_level ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($product->current_stock, 2) }} {{ $product->unit }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $maxLevel = max($product->reorder_level * 2, $product->current_stock, 1);
                                    $stockPercent = min(100, ($product->current_stock / $maxLevel) * 100);
                                @endphp
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stockPercent }}%"></div>
                            </div>
                            @if($product->reorder_level > 0)
                            <div class="text-xs text-gray-500 mt-1">Reorder: {{ number_format($product->reorder_level, 2) }} | Min: {{ number_format($product->min_stock_level, 2) }}</div>
                            @endif
                        </div>
                        @else
                        <div class="mb-4 text-sm text-gray-500">No inventory tracking</div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button onclick="showViewModal({{ $product->id }})"
                                        class="text-blue-600 hover:text-blue-800 p-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="showEditModal({{ $product->id }})"
                                        class="text-green-600 hover:text-green-800 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($product->track_inventory)
                                    <button onclick="showStockModal({{ $product->id }})"
                                            class="text-purple-600 hover:text-purple-800 p-1">
                                        <i class="fas fa-boxes"></i>
                                    </button>
                                @endif
                            </div>
                            <div>
                                @if($product->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-box text-gray-400 text-4xl mb-3"></i>
                    <p class="text-gray-600">No products found</p>
                    <button onclick="showCreateModal()" class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i> Create First Product
                    </button>
                </div>
                @endforelse
            </div>

            <!-- Grid View Pagination -->
            @if($products->hasPages())
            <div class="mt-6">
                {{ $products->links() }}
            </div>
            @endif
        </div>

        <!-- List View -->
        <div id="listView">
            <div class="overflow-x-auto">
                <table id="productsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                        @php
                            $isLowStock = $product->track_inventory && $product->current_stock <= $product->reorder_level;
                            $profitMargin = $product->cost_price && $product->cost_price > 0 ? (($product->selling_price - $product->cost_price) / $product->cost_price * 100) : null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($product->image && Storage::exists($product->image))
                                        <div class="flex-shrink-0 h-10 w-10 mr-3">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->product_name }}">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 h-10 w-10 mr-3 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $product->product_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->product_code }}</div>
                                        <div class="text-xs text-gray-400">{{ $product->unit }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->businessSection->section_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $product->businessSection->section_type ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $product->category->category_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $product->category->category_code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">KES {{ number_format($product->selling_price, 2) }}</div>
                                @if($product->cost_price && $product->cost_price > 0)
                                    <div class="text-xs text-gray-500">Cost: KES {{ number_format($product->cost_price, 2) }}</div>
                                @endif
                                @if($profitMargin !== null)
                                    <div class="text-xs {{ $profitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($profitMargin, 1) }}% margin
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->track_inventory)
                                    <div>
                                        <div class="text-sm {{ $isLowStock ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                            {{ number_format($product->current_stock, 2) }} {{ $product->unit }}
                                        </div>
                                        @if($product->reorder_level > 0 || $product->min_stock_level > 0)
                                        <div class="text-xs text-gray-500">
                                            @if($product->reorder_level > 0)Reorder: {{ number_format($product->reorder_level, 2) }}@endif
                                            @if($product->min_stock_level > 0) | Min: {{ number_format($product->min_stock_level, 2) }}@endif
                                        </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">Not tracked</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactive
                                    </span>
                                @endif
                                @if($product->is_featured)
                                    <span class="ml-1 px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-star mr-1"></i> Featured
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="showViewModal({{ $product->id }})"
                                            class="text-blue-600 hover:text-blue-900 transition"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="showEditModal({{ $product->id }})"
                                            class="text-green-600 hover:text-green-900 transition"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($product->track_inventory)
                                        <button onclick="showStockModal({{ $product->id }})"
                                                class="text-purple-600 hover:text-purple-900 transition"
                                                title="Update Stock">
                                            <i class="fas fa-boxes"></i>
                                        </button>
                                    @endif
                                    <button onclick="showDeleteModal({{ $product->id }}, '{{ addslashes($product->product_name) }}')"
                                            class="text-red-600 hover:text-red-900 transition"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box text-gray-400 text-4xl mb-3"></i>
                                    <p class="text-gray-600">No products found</p>
                                    <button onclick="showCreateModal()" class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg inline-flex items-center">
                                        <i class="fas fa-plus mr-2"></i> Create First Product
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="productModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-4xl rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <!-- Modal header -->
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-xl font-bold" id="modalTitle">Create New Product</h3>
                <button onclick="closeModal('#productModal')" class="text-white text-2xl">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="p-6">
                <form id="productForm" action="{{ route('cafeteria.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" id="productId" name="id">
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Basic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="product_code" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="product_code"
                                       name="product_code"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                       required>
                            </div>

                            <div>
                                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="product_name"
                                       name="product_name"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                       required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="business_section_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Business Section <span class="text-red-500">*</span>
                                </label>
                                <select id="business_section_id"
                                        name="business_section_id"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                        required>
                                    <option value="">Select Business Section</option>
                                    @foreach($businessSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_code }} - {{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category
                                </label>
                                <select id="category_id"
                                        name="category_id"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                                    <option value="">No Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_code }} - {{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"></textarea>
                        </div>
                    </div>

                    <!-- Product Classification -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Product Classification</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="product_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Type <span class="text-red-500">*</span>
                                </label>
                                <select id="product_type"
                                        name="product_type"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="food">Food</option>
                                    <option value="beverage">Beverage</option>
                                    <option value="gift">Gift</option>
                                    <option value="raw_material">Raw Material</option>
                                    <option value="stationery">Stationery</option>
                                    <option value="uniform">Uniform</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">
                                    Unit <span class="text-red-500">*</span>
                                </label>
                                <select id="unit"
                                        name="unit"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                        required>
                                    <option value="">Select Unit</option>
                                    <option value="piece">Piece</option>
                                    <option value="plate">Plate</option>
                                    <option value="bowl">Bowl</option>
                                    <option value="cup">Cup</option>
                                    <option value="bottle">Bottle</option>
                                    <option value="packet">Packet</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="gram">Gram (g)</option>
                                    <option value="liter">Liter (L)</option>
                                    <option value="dozen">Dozen</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing - Fixed for manual input -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Pricing</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Selling Price <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">KES</span>
                                    </div>
                                    <input type="text"
                                           id="selling_price"
                                           name="selling_price"
                                           class="w-full border border-gray-300 rounded-lg py-2 pl-12 pr-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                           required
                                           oninput="validatePriceInput(this)">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <span class="text-gray-400 text-sm">per unit</span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Enter amount like: 100, 100.50, 99.99</div>
                            </div>

                            <div>
                                <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cost Price
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">KES</span>
                                    </div>
                                    <input type="text"
                                           id="cost_price"
                                           name="cost_price"
                                           class="w-full border border-gray-300 rounded-lg py-2 pl-12 pr-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                           oninput="validatePriceInput(this)">
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Optional: Enter the cost price for margin calculation</div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Management -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Inventory Management</h4>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       id="track_inventory"
                                       name="track_inventory"
                                       value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                                       checked>
                                <label for="track_inventory" class="ml-2 text-sm font-medium text-gray-700">
                                    Track Inventory
                                </label>
                            </div>

                            <div id="inventoryFields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="current_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                        Current Stock
                                    </label>
                                    <input type="number"
                                           id="current_stock"
                                           name="current_stock"
                                           step="0.001"
                                           min="0"
                                           value="0"
                                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                                </div>

                                <div class="relative">
                                    <label for="reorder_level_percent" class="block text-sm font-medium text-gray-700 mb-1">
                                        Reorder Level (% of Current Stock)
                                    </label>
                                    <div class="flex items-center">
                                        <input type="number"
                                               id="reorder_level_percent"
                                               name="reorder_level_percent"
                                               min="0"
                                               max="100"
                                               value="20"
                                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition pr-12">
                                        <span class="absolute right-3 text-gray-500">%</span>
                                    </div>
                                    <input type="hidden" id="reorder_level" name="reorder_level" value="0">
                                    <div class="text-xs text-gray-500 mt-1">Reorder when stock falls below <span id="reorderValue">0</span> units</div>
                                </div>

                                <div class="relative">
                                    <label for="min_stock_level_percent" class="block text-sm font-medium text-gray-700 mb-1">
                                        Minimum Stock (% of Current Stock)
                                    </label>
                                    <div class="flex items-center">
                                        <input type="number"
                                               id="min_stock_level_percent"
                                               name="min_stock_level_percent"
                                               min="0"
                                               max="100"
                                               value="10"
                                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition pr-12">
                                        <span class="absolute right-3 text-gray-500">%</span>
                                    </div>
                                    <input type="hidden" id="min_stock_level" name="min_stock_level" value="0">
                                    <div class="text-xs text-gray-500 mt-1">Minimum stock should be <span id="minStockValue">0</span> units</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Production & Shop Assignment -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Additional Settings</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center mb-4">
                                    <input type="checkbox"
                                           id="is_production_item"
                                           name="is_production_item"
                                           value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="is_production_item" class="ml-2 text-sm font-medium text-gray-700">
                                        Production Item (Cafeteria)
                                    </label>
                                </div>

                                <div id="recipeDetailsContainer" class="hidden">
                                    <label for="recipe_details" class="block text-sm font-medium text-gray-700 mb-1">
                                        Recipe Details (JSON)
                                    </label>
                                    <textarea id="recipe_details"
                                              name="recipe_details"
                                              rows="3"
                                              class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition font-mono text-sm"
                                              placeholder='{"ingredients": [], "instructions": ""}'></textarea>
                                </div>
                            </div>

                            <div>
                                <label for="shop_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Assign to Shop
                                </label>
                                <select id="shop_id"
                                        name="shop_id"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                                    <option value="">No Specific Shop</option>
                                    @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}">{{ $shop->shop_code }} - {{ $shop->shop_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Display -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Status & Display</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                                       checked>
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                    Active Product
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox"
                                       id="is_featured"
                                       name="is_featured"
                                       value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <label for="is_featured" class="ml-2 text-sm font-medium text-gray-700">
                                    Featured Product
                                </label>
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                    Sort Order
                                </label>
                                <input type="number"
                                       id="sort_order"
                                       name="sort_order"
                                       value="0"
                                       min="0"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <!-- Image Upload -->
<div>
    <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Product Image</h4>
    <div class="space-y-4">
        <div id="imagePreview" class="hidden">
            <img id="previewImage" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
            <button type="button" onclick="removeImage()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                <i class="fas fa-trash mr-1"></i> Remove Image
            </button>
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                Upload Image
            </label>
            <input type="file"
                   id="image"
                   name="image"
                   accept="image/*"
                   class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                   onchange="previewFile(this)">
            <div class="text-xs text-gray-500 mt-1">Max size: 2MB • Supported formats: JPG, PNG, WebP</div>
            <input type="hidden" id="existing_image" name="existing_image">
        </div>
    </div>
</div>
                </form>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3 sticky bottom-0 z-10">
                <button type="button"
                        onclick="closeModal('#productModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        id="saveProductBtn"
                        onclick="saveProduct()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Save Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="stockModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="stockModalTitle">Update Stock</h3>
                <button onclick="closeModal('#stockModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <form id="stockForm">
                    @csrf
                    <input type="hidden" id="stockProductId" name="product_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <div id="stockProductInfo" class="text-sm text-gray-600 p-3 bg-gray-50 rounded-lg"></div>
                        </div>

                        <div>
                            <label for="stockShopId" class="block text-sm font-medium text-gray-700 mb-1">
                                Shop <span class="text-red-500">*</span>
                            </label>
                            <select id="stockShopId"
                                    name="shop_id"
                                    class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                    required>
                                <option value="">Select Shop</option>
                                @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->shop_code }} - {{ $shop->shop_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="movement_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Movement Type <span class="text-red-500">*</span>
                            </label>
                            <select id="movement_type"
                                    name="movement_type"
                                    class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                    required>
                                <option value="adjustment_in">Stock In (Addition)</option>
                                <option value="adjustment_out">Stock Out (Deduction)</option>
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   id="quantity"
                                   name="quantity"
                                   step="0.001"
                                   min="0.001"
                                   class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                   required>
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                                Reason
                            </label>
                            <textarea id="reason"
                                      name="reason"
                                      rows="2"
                                      class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                      placeholder="Reason for stock adjustment (e.g., stock take, received goods, etc.)"></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Notes
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="2"
                                      class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                      placeholder="Additional notes"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3">
                <button type="button"
                        onclick="closeModal('#stockModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        id="updateStockBtn"
                        onclick="updateStock()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg">
                    Update Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="deleteModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center mb-2">Confirm Deletion</h3>
                <p class="text-gray-600 text-center mb-6" id="deleteMessage">
                    Are you sure you want to delete this product?
                </p>
                <div class="flex justify-center space-x-3">
                    <button type="button"
                            onclick="closeModal('#deleteModal')"
                            class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-6 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                id="confirmDeleteBtn"
                                class="bg-red-600 text-white font-medium py-2 px-6 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash mr-2"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="viewModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-3xl rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold" id="viewModalTitle">Product Details</h3>
                <button onclick="closeModal('#viewModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <div id="productDetailsContent">
                    <!-- Details will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Price validation function
    function validatePriceInput(input) {
        // Remove any non-numeric characters except decimal point
        input.value = input.value.replace(/[^0-9.]/g, '');

        // Ensure only one decimal point
        const parts = input.value.split('.');
        if (parts.length > 2) {
            input.value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Limit to 2 decimal places
        if (parts.length === 2 && parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].substring(0, 2);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Set initial view mode
        toggleViewMode('list');

        // Track inventory checkbox
        const trackInventoryCheckbox = document.getElementById('track_inventory');
        if (trackInventoryCheckbox) {
            trackInventoryCheckbox.addEventListener('change', function() {
                toggleInventoryFields(this.checked);
            });
        }

        // Production item checkbox
        const productionItemCheckbox = document.getElementById('is_production_item');
        if (productionItemCheckbox) {
            productionItemCheckbox.addEventListener('change', function() {
                toggleRecipeDetails(this.checked);
            });
        }

        // Apply price validation to price inputs
        const sellingPriceInput = document.getElementById('selling_price');
        const costPriceInput = document.getElementById('cost_price');

        if (sellingPriceInput) {
            sellingPriceInput.addEventListener('input', function(e) {
                validatePriceInput(this);
            });
        }

        if (costPriceInput) {
            costPriceInput.addEventListener('input', function(e) {
                validatePriceInput(this);
            });
        }

        // Calculate stock levels based on current stock and percentages
        const currentStockInput = document.getElementById('current_stock');
        const reorderPercentInput = document.getElementById('reorder_level_percent');
        const minStockPercentInput = document.getElementById('min_stock_level_percent');

        if (currentStockInput && reorderPercentInput && minStockPercentInput) {
            currentStockInput.addEventListener('input', calculateStockLevels);
            reorderPercentInput.addEventListener('input', calculateStockLevels);
            minStockPercentInput.addEventListener('input', calculateStockLevels);

            // Initial calculation
            calculateStockLevels();
        }

        // Initialize inventory fields
        toggleInventoryFields(trackInventoryCheckbox ? trackInventoryCheckbox.checked : false);
        toggleRecipeDetails(productionItemCheckbox ? productionItemCheckbox.checked : false);
    });

    function calculateStockLevels() {
        const currentStock = parseFloat(document.getElementById('current_stock').value) || 0;
        const reorderPercent = parseFloat(document.getElementById('reorder_level_percent').value) || 20;
        const minStockPercent = parseFloat(document.getElementById('min_stock_level_percent').value) || 10;

        // Calculate actual values
        const reorderLevel = (currentStock * reorderPercent) / 100;
        const minStockLevel = (currentStock * minStockPercent) / 100;

        // Update hidden inputs
        document.getElementById('reorder_level').value = reorderLevel.toFixed(3);
        document.getElementById('min_stock_level').value = minStockLevel.toFixed(3);

        // Update display
        const reorderValue = document.getElementById('reorderValue');
        const minStockValue = document.getElementById('minStockValue');
        if (reorderValue) reorderValue.textContent = reorderLevel.toFixed(2);
        if (minStockValue) minStockValue.textContent = minStockLevel.toFixed(2);
    }

    function toggleViewMode(mode) {
        const viewMode = mode;
        const viewModeButtons = document.querySelectorAll('.view-mode-btn');
        viewModeButtons.forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtn = document.querySelector(`.view-mode-btn[data-mode="${mode}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        if (mode === 'grid') {
            if (gridView) gridView.classList.remove('hidden');
            if (listView) listView.classList.add('hidden');
        } else {
            if (gridView) gridView.classList.add('hidden');
            if (listView) listView.classList.remove('hidden');
        }
    }

    function showCreateModal() {
        document.getElementById('modalTitle').textContent = 'Create New Product';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('productForm').action = "{{ route('cafeteria.products.store') }}";
        document.getElementById('imagePreview').classList.add('hidden');

        // Reset checkboxes
        document.getElementById('track_inventory').checked = true;
        document.getElementById('is_active').checked = true;
        document.getElementById('is_production_item').checked = false;
        document.getElementById('is_featured').checked = false;

        // Reset percentage inputs
        document.getElementById('reorder_level_percent').value = '20';
        document.getElementById('min_stock_level_percent').value = '10';

        // Reset price inputs (type should already be text)
        document.getElementById('selling_price').value = '';
        document.getElementById('cost_price').value = '';

        toggleInventoryFields(true);
        toggleRecipeDetails(false);
        calculateStockLevels();

        document.getElementById('productModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    async function showEditModal(productId) {
        try {
            const response = await fetch(`/cafeteria/products/${productId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to fetch product');
            }

            const product = await response.json();

            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('productForm').action = `/cafeteria/products/${product.id}`;

            // Fill form fields
            document.getElementById('product_code').value = product.product_code;
            document.getElementById('product_name').value = product.product_name;
            document.getElementById('business_section_id').value = product.business_section_id;
            document.getElementById('category_id').value = product.category_id;
            document.getElementById('description').value = product.description || '';
            document.getElementById('product_type').value = product.product_type;
            document.getElementById('unit').value = product.unit;

            // Set price values (inputs are already text type)
            document.getElementById('selling_price').value = product.selling_price;
            document.getElementById('cost_price').value = product.cost_price || '';

            document.getElementById('current_stock').value = product.current_stock;
            document.getElementById('track_inventory').checked = product.track_inventory;
            document.getElementById('is_production_item').checked = product.is_production_item;
            document.getElementById('recipe_details').value = product.recipe_details || '';
            document.getElementById('shop_id').value = product.shop_id || '';
            document.getElementById('is_active').checked = product.is_active;
            document.getElementById('is_featured').checked = product.is_featured;
            document.getElementById('sort_order').value = product.sort_order || 0;

            // Calculate percentages
            const currentStock = parseFloat(product.current_stock) || 0;
            const reorderLevel = parseFloat(product.reorder_level) || 0;
            const minStockLevel = parseFloat(product.min_stock_level) || 0;

            let reorderPercent = 20;
            let minStockPercent = 10;

            if (currentStock > 0) {
                reorderPercent = Math.round((reorderLevel / currentStock) * 100);
                minStockPercent = Math.round((minStockLevel / currentStock) * 100);
            }

            document.getElementById('reorder_level_percent').value = reorderPercent;
            document.getElementById('min_stock_level_percent').value = minStockPercent;

            // Handle image preview
            const previewImage = document.getElementById('previewImage');
            const imagePreview = document.getElementById('imagePreview');
            if (product.image && product.image !== 'null') {
                previewImage.src = `/storage/${product.image}`;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }

            toggleInventoryFields(product.track_inventory);
            toggleRecipeDetails(product.is_production_item);
            calculateStockLevels();

            document.getElementById('productModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load product data: ' + error.message);
        }
    }

    async function showViewModal(productId) {
        try {
            const response = await fetch(`/cafeteria/products/${productId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch product');

            const product = await response.json();

            document.getElementById('viewModalTitle').textContent = `Product: ${product.product_name}`;

            // Format product details
            let profitMarginHtml = '';
            if (product.cost_price && product.cost_price > 0) {
                const profitMargin = ((product.selling_price - product.cost_price) / product.cost_price) * 100;
                profitMarginHtml = `
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Profit Margin:</dt>
                        <dd class="font-medium ${profitMargin >= 0 ? 'text-green-600' : 'text-red-600'}">
                            ${profitMargin.toFixed(1)}%
                        </dd>
                    </div>
                `;
            }

            let inventoryHtml = '';
            if (product.track_inventory) {
                inventoryHtml = `
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Inventory</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Current Stock:</dt>
                                <dd class="font-medium ${product.current_stock <= product.reorder_level ? 'text-red-600' : 'text-green-600'}">
                                    ${parseFloat(product.current_stock).toFixed(2)} ${product.unit}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Reorder Level:</dt>
                                <dd class="font-medium">${parseFloat(product.reorder_level).toFixed(2)} ${product.unit}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Minimum Stock:</dt>
                                <dd class="font-medium">${parseFloat(product.min_stock_level).toFixed(2)} ${product.unit}</dd>
                            </div>
                        </dl>
                    </div>
                `;
            }

            const details = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Basic Information</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Product Code:</dt>
                                    <dd class="font-medium">${product.product_code}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Product Name:</dt>
                                    <dd class="font-medium">${product.product_name}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Business Section:</dt>
                                    <dd class="font-medium">${product.business_section?.section_name || 'N/A'}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Category:</dt>
                                    <dd class="font-medium">${product.category?.category_name || '-'}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Type:</dt>
                                    <dd class="font-medium capitalize">${product.product_type.replace('_', ' ')}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Unit:</dt>
                                    <dd class="font-medium">${product.unit}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Pricing</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Selling Price:</dt>
                                    <dd class="font-medium text-green-600">KES ${parseFloat(product.selling_price).toFixed(2)}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Cost Price:</dt>
                                    <dd class="font-medium">${product.cost_price ? 'KES ' + parseFloat(product.cost_price).toFixed(2) : 'N/A'}</dd>
                                </div>
                                ${profitMarginHtml}
                            </dl>
                        </div>
                    </div>

                    ${inventoryHtml}

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Status</h4>
                        <div class="flex space-x-4">
                            <span class="px-3 py-1 text-sm rounded-full ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${product.is_active ? 'Active' : 'Inactive'}
                            </span>
                            ${product.is_featured ? `
                                <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">
                                    Featured
                                </span>
                            ` : ''}
                            ${product.is_production_item ? `
                                <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">
                                    Production Item
                                </span>
                            ` : ''}
                        </div>
                    </div>

                    ${product.description ? `
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                            <p class="text-gray-600">${product.description}</p>
                        </div>
                    ` : ''}
                </div>
            `;

            document.getElementById('productDetailsContent').innerHTML = details;
            document.getElementById('viewModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load product details: ' + error.message);
        }
    }

    async function showStockModal(productId) {
        try {
            const response = await fetch(`/cafeteria/products/${productId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch product');

            const product = await response.json();

            document.getElementById('stockModalTitle').textContent = `Update Stock - ${product.product_name}`;
            document.getElementById('stockProductId').value = product.id;
            document.getElementById('stockProductInfo').innerHTML = `
                <div class="font-medium">${product.product_name}</div>
                <div class="text-xs">Current Stock: ${parseFloat(product.current_stock).toFixed(2)} ${product.unit}</div>
                ${product.reorder_level > 0 ? `<div class="text-xs">Reorder Level: ${parseFloat(product.reorder_level).toFixed(2)} ${product.unit}</div>` : ''}
            `;
            document.getElementById('quantity').value = '';
            document.getElementById('reason').value = '';
            document.getElementById('notes').value = '';

            document.getElementById('stockModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load product data: ' + error.message);
        }
    }

    function showDeleteModal(productId, productName) {
        document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${productName}"? This action cannot be undone.`;
        document.getElementById('deleteForm').action = `/cafeteria/products/${productId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');
    }

    async function saveProduct() {
        const form = document.getElementById('productForm');
        if (!form) {
            alert('Form not found!');
            return;
        }

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let errorMessage = '';

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                errorMessage += `${field.previousElementSibling?.textContent || 'Field'} is required\n`;
            }
        });

        // Validate price format
        const sellingPrice = document.getElementById('selling_price').value;
        if (sellingPrice && !/^\d+(\.\d{1,2})?$/.test(sellingPrice)) {
            isValid = false;
            errorMessage += 'Selling price must be a valid number with up to 2 decimal places\n';
        }

        const costPrice = document.getElementById('cost_price').value;
        if (costPrice && !/^\d+(\.\d{1,2})?$/.test(costPrice)) {
            isValid = false;
            errorMessage += 'Cost price must be a valid number with up to 2 decimal places\n';
        }

        if (!isValid) {
            alert(errorMessage);
            return;
        }

        const formData = new FormData(form);
        const method = document.getElementById('formMethod').value;
        const url = form.action;

        // Add CSRF token to FormData
        formData.append('_token', csrfToken);
        formData.append('_method', method);

        // Show loading state
        const saveBtn = document.getElementById('saveProductBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    // Display validation errors
                    let errorMessages = '';
                    for (const field in data.errors) {
                        errorMessages += data.errors[field].join('<br>') + '<br>';
                    }
                    alert(errorMessages);
                } else {
                    alert(data.message || 'An error occurred while saving the product');
                }
            } else {
                alert(method === 'POST' ? 'Product created successfully' : 'Product updated successfully');
                closeModal('#productModal');
                // Reload the page to see changes
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the product. Please try again.');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Product';
            }
        }
    }

    async function updateStock() {
        const productId = document.getElementById('stockProductId').value;
        const form = document.getElementById('stockForm');
        if (!form) {
            alert('Stock form not found!');
            return;
        }

        // Validate required fields
        const quantity = document.getElementById('quantity').value;
        const shopId = document.getElementById('stockShopId').value;

        if (!quantity || !shopId) {
            alert('Please fill all required fields');
            return;
        }

        const formData = new FormData(form);
        formData.append('_token', csrfToken);

        // Show loading state
        const updateBtn = document.getElementById('updateStockBtn');
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
        }

        try {
            const response = await fetch(`/cafeteria/products/${productId}/update-stock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    let errorMessages = '';
                    for (const field in data.errors) {
                        errorMessages += data.errors[field].join('<br>') + '<br>';
                    }
                    alert(errorMessages);
                } else {
                    alert(data.message || 'An error occurred while updating stock');
                }
            } else {
                alert('Stock updated successfully');
                closeModal('#stockModal');
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating stock');
        } finally {
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = 'Update Stock';
            }
        }
    }

    function exportToExcel() {
        const form = document.getElementById('filterForm');
        if (!form) return;

        const params = new URLSearchParams(new FormData(form));
        params.append('export', 'excel');

        window.location.href = `{{ route('cafeteria.products.index') }}?${params.toString()}`;
    }

    function showBulkUpdateModal() {
        alert('Bulk update feature coming soon!');
    }

    // Helper functions
    function toggleInventoryFields(enabled) {
        const inventoryFields = document.getElementById('inventoryFields');
        const currentStock = document.getElementById('current_stock');
        const reorderPercent = document.getElementById('reorder_level_percent');
        const minStockPercent = document.getElementById('min_stock_level_percent');

        if (inventoryFields) {
            if (enabled) {
                inventoryFields.classList.remove('hidden');
                if (currentStock) currentStock.disabled = false;
                if (reorderPercent) reorderPercent.disabled = false;
                if (minStockPercent) minStockPercent.disabled = false;
            } else {
                inventoryFields.classList.add('hidden');
                if (currentStock) currentStock.disabled = true;
                if (reorderPercent) reorderPercent.disabled = true;
                if (minStockPercent) minStockPercent.disabled = true;
            }
        }
    }

    function toggleRecipeDetails(enabled) {
        const recipeContainer = document.getElementById('recipeDetailsContainer');
        const recipeDetails = document.getElementById('recipe_details');

        if (recipeContainer) {
            if (enabled) {
                recipeContainer.classList.remove('hidden');
                if (recipeDetails) recipeDetails.disabled = false;
            } else {
                recipeContainer.classList.add('hidden');
                if (recipeDetails) recipeDetails.disabled = true;
            }
        }
    }

    function previewFile() {
        const preview = document.getElementById('previewImage');
        const file = document.getElementById('image').files[0];
        if (!file || !preview) return;

        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) imagePreview.classList.remove('hidden');
        }

        reader.readAsDataURL(file);
    }

    function removeImage() {
        document.getElementById('image').value = '';
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) imagePreview.classList.add('hidden');
    }
</script>
@endsection
