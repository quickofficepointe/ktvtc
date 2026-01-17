@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Daily Production Management')
@section('page-title', 'Daily Production Management')
@section('page-description', 'Manage daily production records, track raw materials, and monitor production efficiency')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Production
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Daily Production
    </span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-industry text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Productions</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $productions->total() }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Completed Today</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $todayProduction ? 1 : 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clipboard-check text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Verified</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $productions->where('status', 'verified')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $productions->where('status', 'in_progress')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Action Buttons -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <form method="GET" action="{{ route('cafeteria.daily-productions.index') }}" id="filterForm" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="relative">
                        <input type="date"
                               name="start_date"
                               value="{{ $filters['start_date'] ?? '' }}"
                               class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    </div>

                    <div class="relative">
                        <input type="date"
                               name="end_date"
                               value="{{ $filters['end_date'] ?? '' }}"
                               class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    </div>

                    <select name="shop_id" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ ($filters['shop_id'] ?? '') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_code }} - {{ $shop->shop_name }}
                        </option>
                        @endforeach
                    </select>

                    <select name="status" class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">All Status</option>
                        <option value="draft" {{ ($filters['status'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_progress" {{ ($filters['status'] ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="verified" {{ ($filters['status'] ?? '') == 'verified' ? 'selected' : '' }}>Verified</option>
                    </select>

                    <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>

                    @if(count($filters) > 0)
                    <a href="{{ route('cafeteria.daily-productions.index') }}" class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                    @endif
                </form>
            </div>

            <div class="flex gap-3">
                @if(!$todayProduction)
                <button onclick="showCreateModal()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Today's Production
                </button>
                @else
                <button onclick="window.location.href='{{ route('cafeteria.daily-productions.index', ['view' => true, 'id' => $todayProduction->id]) }}'"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-eye mr-2"></i> View Today's
                </button>
                @endif
                <button onclick="showStatistics()"
                        class="bg-white text-purple-600 border border-purple-600 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-purple-600 hover:text-white transition">
                    <i class="fas fa-chart-bar mr-2"></i> Statistics
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Production List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Production Records</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($productions as $prod)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $prod->production_date->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $prod->production_date->format('l') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $prod->shop->shop_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $prod->shop->shop_code ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $prod->total_items_produced }} items</div>
                                    <div class="text-xs text-gray-500">
                                        Sold: {{ $prod->total_items_sold }} | Waste: {{ $prod->total_items_wasted }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'verified' => 'bg-purple-100 text-purple-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$prod->status] }}">
                                        {{ ucfirst(str_replace('_', ' ', $prod->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewProduction({{ $prod->id }})"
                                                class="text-blue-600 hover:text-blue-900 transition"
                                                title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($prod->status !== 'verified')
                                        <button onclick="editProduction({{ $prod->id }})"
                                                class="text-green-600 hover:text-green-900 transition"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                        @if($prod->status === 'completed' && !$prod->verified_at)
                                        <button onclick="verifyProduction({{ $prod->id }})"
                                                class="text-purple-600 hover:text-purple-900 transition"
                                                title="Verify">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        @endif
                                        @if($prod->status !== 'verified')
                                        <button onclick="deleteProduction({{ $prod->id }})"
                                                class="text-red-600 hover:text-red-900 transition"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-industry text-gray-400 text-4xl mb-3"></i>
                                        <p class="text-gray-600">No production records found</p>
                                        <button onclick="showCreateModal()" class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg inline-flex items-center">
                                            <i class="fas fa-plus mr-2"></i> Create First Production
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($productions->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $productions->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: View/Edit Panel -->
        <div class="lg:col-span-1">
            @if($viewMode === 'view' && $production)
                <!-- View Panel -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Production Details</h3>
                        <button onclick="closeViewPanel()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-4 space-y-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                        <!-- Basic Info -->
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-2">Basic Information</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-gray-600">Date:</div>
                                <div class="font-medium">{{ $production->production_date->format('d M Y') }}</div>

                                <div class="text-gray-600">Shop:</div>
                                <div class="font-medium">{{ $production->shop->shop_name }}</div>

                                <div class="text-gray-600">Status:</div>
                                <div class="font-medium">
                                    <span class="px-2 py-1 text-xs rounded-full {{
                                        $production->status === 'draft' ? 'bg-gray-100 text-gray-800' :
                                        ($production->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                        ($production->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'))
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $production->status)) }}
                                    </span>
                                </div>

                                @if($production->verified_at)
                                <div class="text-gray-600">Verified:</div>
                                <div class="font-medium">{{ $production->verified_at->format('d M Y H:i') }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-2">Production Summary</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-gray-600">Items Produced:</div>
                                <div class="font-medium">{{ $production->total_items_produced }}</div>

                                <div class="text-gray-600">Items Sold:</div>
                                <div class="font-medium text-green-600">{{ $production->total_items_sold }}</div>

                                <div class="text-gray-600">Items Wasted:</div>
                                <div class="font-medium text-red-600">{{ $production->total_items_wasted }}</div>

                                <div class="text-gray-600">Raw Material Cost:</div>
                                <div class="font-medium">KES {{ number_format($production->total_raw_material_cost, 2) }}</div>

                                <div class="text-gray-600">Production Cost:</div>
                                <div class="font-medium">KES {{ number_format($production->total_production_cost, 2) }}</div>

                                <div class="text-gray-600">Sales Value:</div>
                                <div class="font-medium text-green-600">KES {{ number_format($production->total_sales_value, 2) }}</div>

                                @php
                                    $profit = $production->total_sales_value - $production->total_production_cost;
                                    $margin = $production->total_production_cost > 0 ? ($profit / $production->total_production_cost) * 100 : 0;
                                @endphp
                                <div class="text-gray-600">Profit:</div>
                                <div class="font-medium {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    KES {{ number_format($profit, 2) }}
                                </div>

                                <div class="text-gray-600">Margin:</div>
                                <div class="font-medium {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($margin, 1) }}%
                                </div>
                            </div>
                        </div>

                        <!-- Production Items -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Production Items</h4>
                            <div class="space-y-2">
                                @foreach($production->productionItems as $item)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-medium">{{ $item->product->product_name }}</span>
                                        <span class="text-sm text-gray-600">{{ $item->actual_quantity }} {{ $item->product->unit }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1 text-xs text-gray-500">
                                        <div>Planned: {{ $item->planned_quantity }}</div>
                                        <div>Sold: {{ $item->quantity_sold }}</div>
                                        <div>Wasted: {{ $item->quantity_wasted }}</div>
                                        <div>Remaining: {{ $item->remaining_quantity }}</div>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <span class="text-gray-600">Cost: KES {{ number_format($item->unit_production_cost, 2) }}/unit</span>
                                        <span class="ml-2 text-gray-600">Price: KES {{ number_format($item->unit_selling_price, 2) }}/unit</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Raw Materials -->
                        @if($production->rawMaterialUsages->count() > 0)
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Raw Materials Used</h4>
                            <div class="space-y-2">
                                @foreach($production->rawMaterialUsages as $material)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-medium">{{ $material->rawMaterial->product_name }}</span>
                                        <span class="text-sm text-gray-600">{{ $material->quantity_used }} {{ $material->unit }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Cost: KES {{ number_format($material->unit_cost, 2) }}/unit
                                        <span class="ml-2">Total: KES {{ number_format($material->total_cost, 2) }}</span>
                                    </div>
                                    @if($material->producedProduct)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Used for: {{ $material->producedProduct->product_name }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Notes -->
                        @if($production->notes || $production->challenges || $production->suggestions)
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Notes & Comments</h4>
                            <div class="space-y-3">
                                @if($production->notes)
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Notes:</div>
                                    <div class="text-sm text-gray-700 mt-1">{{ $production->notes }}</div>
                                </div>
                                @endif

                                @if($production->challenges)
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Challenges:</div>
                                    <div class="text-sm text-gray-700 mt-1">{{ $production->challenges }}</div>
                                </div>
                                @endif

                                @if($production->suggestions)
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Suggestions:</div>
                                    <div class="text-sm text-gray-700 mt-1">{{ $production->suggestions }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="p-4 border-t border-gray-200 flex justify-between">
                        @if($production->status !== 'verified')
                            @if($production->status === 'completed')
                            <button onclick="verifyProduction({{ $production->id }})"
                                    class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-check-double mr-2"></i> Verify Production
                            </button>
                            @else
                            <button onclick="editProduction({{ $production->id }})"
                                    class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </button>
                            @endif
                        @endif

                        @if($production->status === 'draft' || $production->status === 'in_progress')
                        <button onclick="updateSales({{ $production->id }})"
                                class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center hover:bg-green-700 transition">
                            <i class="fas fa-chart-line mr-2"></i> Update Sales
                        </button>
                        @endif
                    </div>
                </div>
            @elseif($viewMode === 'edit' && $production)
                <!-- Edit Panel -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Edit Production</h3>
                        <button onclick="closeEditPanel()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-4 space-y-4 max-h-[calc(100vh-300px)] overflow-y-auto">
                        <!-- Edit form will be loaded via AJAX -->
                        <div id="editFormContainer">
                            <!-- Form will be loaded here -->
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State Panel -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="text-center">
                        <i class="fas fa-industry text-gray-400 text-4xl mb-3"></i>
                        <h3 class="font-medium text-gray-700 mb-2">No Production Selected</h3>
                        <p class="text-gray-500 text-sm mb-4">Select a production record to view details or create a new one.</p>
                        <button onclick="showCreateModal()"
                                class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center mx-auto">
                            <i class="fas fa-plus mr-2"></i> Create New Production
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="createModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-4xl rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-xl font-bold">Create Daily Production</h3>
                <button onclick="closeModal('#createModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <form id="createForm" class="space-y-6">
                    @csrf
                    <input type="hidden" id="productionId" name="id">

                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Basic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="production_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Production Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       id="production_date"
                                       name="production_date"
                                       value="{{ $today }}"
                                       max="{{ $today }}"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                       required>
                            </div>

                            <div>
                                <label for="shop_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Shop <span class="text-red-500">*</span>
                                </label>
                                <select id="shop_id"
                                        name="shop_id"
                                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                        required>
                                    <option value="">Select Shop</option>
                                    @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ auth()->user()->shop_id == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->shop_code }} - {{ $shop->shop_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Production Items -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Production Items</h4>
                        <div id="productionItemsContainer" class="space-y-4">
                            <!-- Items will be added here -->
                        </div>
                        <button type="button"
                                onclick="addProductionItem()"
                                class="mt-4 bg-white text-primary border border-primary font-medium py-2 px-4 rounded-lg flex items-center hover:bg-primary hover:text-white transition">
                            <i class="fas fa-plus mr-2"></i> Add Production Item
                        </button>
                    </div>

                    <!-- Raw Materials -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Raw Materials Used (Optional)</h4>
                        <div id="rawMaterialsContainer" class="space-y-4">
                            <!-- Raw materials will be added here -->
                        </div>
                        <button type="button"
                                onclick="addRawMaterial()"
                                class="mt-4 bg-white text-gray-600 border border-gray-300 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-50 transition">
                            <i class="fas fa-plus mr-2"></i> Add Raw Material
                        </button>
                    </div>

                    <!-- Notes -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Notes & Comments</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    General Notes
                                </label>
                                <textarea id="notes"
                                          name="notes"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                          placeholder="Any general notes about today's production..."></textarea>
                            </div>

                            <div>
                                <label for="challenges" class="block text-sm font-medium text-gray-700 mb-1">
                                    Challenges Faced
                                </label>
                                <textarea id="challenges"
                                          name="challenges"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                          placeholder="Any challenges faced during production..."></textarea>
                            </div>

                            <div>
                                <label for="suggestions" class="block text-sm font-medium text-gray-700 mb-1">
                                    Suggestions for Improvement
                                </label>
                                <textarea id="suggestions"
                                          name="suggestions"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                          placeholder="Any suggestions for improving production..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3 sticky bottom-0 z-10">
                <button type="button"
                        onclick="closeModal('#createModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        id="saveProductionBtn"
                        onclick="saveProduction()"
                        class="btn-primary text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Save Production
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sales Update Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="salesModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-green-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Update Sales Data</h3>
                <button onclick="closeModal('#salesModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <form id="salesForm">
                    <input type="hidden" id="salesProductionId">
                    <div id="salesItemsContainer" class="space-y-4">
                        <!-- Sales items will be loaded here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3">
                <button type="button"
                        onclick="closeModal('#salesModal')"
                        class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        id="updateSalesBtn"
                        onclick="submitSalesUpdate()"
                        class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition">
                    Update Sales
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="statisticsModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl">
            <div class="modal-header bg-purple-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Production Statistics</h3>
                <button onclick="closeModal('#statisticsModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <div id="statisticsContent">
                    <!-- Statistics will be loaded here -->
                </div>
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
                    Are you sure you want to delete this production record?
                </p>
                <input type="hidden" id="deleteProductionId">
                <div class="flex justify-center space-x-3">
                    <button type="button"
                            onclick="closeModal('#deleteModal')"
                            class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-6 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="button"
                            id="confirmDeleteBtn"
                            onclick="confirmDelete()"
                            class="bg-red-600 text-white font-medium py-2 px-6 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Production Items Data
    const productionItems = @json($productionItems);
    const rawMaterials = @json($rawMaterials);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize if in edit mode
        @if($viewMode === 'edit' && $production)
            loadEditForm(@json($production->id));
        @endif
    });

    // Production Item Template
    function getProductionItemTemplate(index = 0, data = null) {
        const productOptions = productionItems.map(item =>
            `<option value="${item.id}" ${data && data.product_id == item.id ? 'selected' : ''}>
                ${item.product_code} - ${item.product_name} (${item.unit})
            </option>`
        ).join('');

        return `
            <div class="border border-gray-200 rounded-lg p-4 production-item" data-index="${index}">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium text-gray-700">Production Item #${index + 1}</h5>
                    <button type="button" onclick="removeProductionItem(${index})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${index}][product_id]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition production-product"
                                required>
                            <option value="">Select Product</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Planned Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="items[${index}][planned_quantity]"
                               step="0.001"
                               min="0"
                               value="${data ? data.planned_quantity : 0}"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Actual Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="items[${index}][actual_quantity]"
                               step="0.001"
                               min="0"
                               value="${data ? data.actual_quantity : 0}"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary outline-none transition"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Selling Price (KES) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="items[${index}][unit_selling_price]"
                               step="0.01"
                               min="0"
                               value="${data ? data.unit_selling_price : 0}"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                               required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="items[${index}][notes]"
                              rows="1"
                              class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">${data ? data.notes || '' : ''}</textarea>
                </div>
                <input type="hidden" name="items[${index}][id]" value="${data ? data.id || '' : ''}">
            </div>
        `;
    }

    // Raw Material Template
    function getRawMaterialTemplate(index = 0, data = null) {
        const rawMaterialOptions = rawMaterials.map(material =>
            `<option value="${material.id}" ${data && data.raw_material_product_id == material.id ? 'selected' : ''}>
                ${material.product_code} - ${material.product_name}
            </option>`
        ).join('');

        const productOptions = productionItems.map(item =>
            `<option value="${item.id}" ${data && data.produced_product_id == item.id ? 'selected' : ''}>
                ${item.product_name}
            </option>`
        ).join('');

        return `
            <div class="border border-gray-200 rounded-lg p-4 raw-material" data-index="${index}">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium text-gray-700">Raw Material #${index + 1}</h5>
                    <button type="button" onclick="removeRawMaterial(${index})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Raw Material <span class="text-red-500">*</span>
                        </label>
                        <select name="raw_materials[${index}][raw_material_product_id]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                                required>
                            <option value="">Select Raw Material</option>
                            ${rawMaterialOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Used For (Optional)
                        </label>
                        <select name="raw_materials[${index}][produced_product_id]"
                                class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                            <option value="">Not Specified</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quantity Used <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="raw_materials[${index}][quantity_used]"
                               step="0.001"
                               min="0.001"
                               value="${data ? data.quantity_used : ''}"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Unit Cost (KES) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="raw_materials[${index}][unit_cost]"
                               step="0.01"
                               min="0"
                               value="${data ? data.unit_cost : ''}"
                               class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                               required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="raw_materials[${index}][notes]"
                              rows="1"
                              class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">${data ? data.notes || '' : ''}</textarea>
                </div>
                <input type="hidden" name="raw_materials[${index}][id]" value="${data ? data.id || '' : ''}">
            </div>
        `;
    }

    // Add production item
    function addProductionItem(data = null) {
        const container = document.getElementById('productionItemsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getProductionItemTemplate(index, data));
    }

    // Add raw material
    function addRawMaterial(data = null) {
        const container = document.getElementById('rawMaterialsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getRawMaterialTemplate(index, data));
    }

    // Remove production item
    function removeProductionItem(index) {
        const item = document.querySelector(`.production-item[data-index="${index}"]`);
        if (item) item.remove();
        reindexProductionItems();
    }

    // Remove raw material
    function removeRawMaterial(index) {
        const item = document.querySelector(`.raw-material[data-index="${index}"]`);
        if (item) item.remove();
        reindexRawMaterials();
    }

    // Reindex production items
    function reindexProductionItems() {
        const items = document.querySelectorAll('.production-item');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
            item.querySelector('h5').textContent = `Production Item #${index + 1}`;

            // Update input names
            const inputs = item.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.setAttribute('name', newName);
            });
        });
    }

    // Reindex raw materials
    function reindexRawMaterials() {
        const items = document.querySelectorAll('.raw-material');
        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
            item.querySelector('h5').textContent = `Raw Material #${index + 1}`;

            // Update input names
            const inputs = item.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.setAttribute('name', newName);
            });
        });
    }

    // Show create modal
    function showCreateModal() {
        // Reset form
        document.getElementById('createForm').reset();
        document.getElementById('productionId').value = '';

        // Clear containers
        document.getElementById('productionItemsContainer').innerHTML = '';
        document.getElementById('rawMaterialsContainer').innerHTML = '';

        // Add initial production item
        addProductionItem();

        // Show modal
        document.getElementById('createModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // Load edit form
    async function loadEditForm(productionId) {
        try {
            const response = await fetch(`/cafeteria/daily-productions/${productionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) throw new Error('Failed to load production data');

            const production = await response.json();

            let html = `
                <form id="editForm" class="space-y-6">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="editProductionId" value="${production.id}">

                    <!-- Notes Section -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Notes & Comments</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="edit_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    General Notes
                                </label>
                                <textarea id="edit_notes"
                                          name="notes"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">${production.notes || ''}</textarea>
                            </div>

                            <div>
                                <label for="edit_challenges" class="block text-sm font-medium text-gray-700 mb-1">
                                    Challenges Faced
                                </label>
                                <textarea id="edit_challenges"
                                          name="challenges"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">${production.challenges || ''}</textarea>
                            </div>

                            <div>
                                <label for="edit_suggestions" class="block text-sm font-medium text-gray-700 mb-1">
                                    Suggestions for Improvement
                                </label>
                                <textarea id="edit_suggestions"
                                          name="suggestions"
                                          rows="2"
                                          class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">${production.suggestions || ''}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Production Items -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Production Items</h4>
                        <div id="editProductionItemsContainer" class="space-y-4">
            `;

            // Add production items
            production.production_items.forEach((item, index) => {
                html += getProductionItemTemplate(index, item);
            });

            html += `
                        </div>
                        <button type="button"
                                onclick="addEditProductionItem()"
                                class="mt-4 bg-white text-primary border border-primary font-medium py-2 px-4 rounded-lg flex items-center hover:bg-primary hover:text-white transition">
                            <i class="fas fa-plus mr-2"></i> Add Production Item
                        </button>
                    </div>

                    <!-- Raw Materials -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4 pb-2 border-b">Raw Materials Used</h4>
                        <div id="editRawMaterialsContainer" class="space-y-4">
            `;

            // Add raw materials
            production.raw_material_usages.forEach((material, index) => {
                html += getRawMaterialTemplate(index, material);
            });

            html += `
                        </div>
                        <button type="button"
                                onclick="addEditRawMaterial()"
                                class="mt-4 bg-white text-gray-600 border border-gray-300 font-medium py-2 px-4 rounded-lg flex items-center hover:bg-gray-50 transition">
                            <i class="fas fa-plus mr-2"></i> Add Raw Material
                        </button>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <button type="button"
                                onclick="updateProduction()"
                                class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center w-full justify-center">
                            <i class="fas fa-save mr-2"></i> Update Production
                        </button>
                    </div>
                </form>
            `;

            document.getElementById('editFormContainer').innerHTML = html;

        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load production data for editing');
        }
    }

    // Add item in edit form
    function addEditProductionItem() {
        const container = document.getElementById('editProductionItemsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getProductionItemTemplate(index));
    }

    // Add raw material in edit form
    function addEditRawMaterial() {
        const container = document.getElementById('editRawMaterialsContainer');
        const index = container.children.length;
        container.insertAdjacentHTML('beforeend', getRawMaterialTemplate(index));
    }

    // Save production (create)
    async function saveProduction() {
        const form = document.getElementById('createForm');
        const formData = new FormData(form);

        // Convert form data to JSON
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                // Handle array data (items and raw_materials)
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

        // Convert arrays to proper format
        if (data.items) {
            data.items = Object.values(data.items);
        }
        if (data.raw_materials) {
            data.raw_materials = Object.values(data.raw_materials);
        }

        // Show loading
        const saveBtn = document.getElementById('saveProductionBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        try {
            const response = await fetch('{{ route("cafeteria.daily-productions.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    let errorMessages = '';
                    for (const field in result.errors) {
                        errorMessages += result.errors[field].join('<br>') + '<br>';
                    }
                    alert(errorMessages);
                } else {
                    alert(result.message || 'Failed to save production');
                }
            } else {
                alert('Production created successfully!');
                closeModal('#createModal');
                // Redirect to view the new production
                window.location.href = `{{ route('cafeteria.daily-productions.index') }}?view=true&id=${result.production_id}`;
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the production');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    // Update production (edit)
    async function updateProduction() {
        const productionId = document.getElementById('editProductionId').value;
        const form = document.getElementById('editForm');
        const formData = new FormData(form);

        // Convert form data to JSON (similar to saveProduction)
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
            } else if (key !== '_method') {
                data[key] = value;
            }
        }

        if (data.items) {
            data.items = Object.values(data.items);
        }
        if (data.raw_materials) {
            data.raw_materials = Object.values(data.raw_materials);
        }

        try {
            const response = await fetch(`/cafeteria/daily-productions/${productionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    let errorMessages = '';
                    for (const field in result.errors) {
                        errorMessages += result.errors[field].join('<br>') + '<br>';
                    }
                    alert(errorMessages);
                } else {
                    alert(result.message || 'Failed to update production');
                }
            } else {
                alert('Production updated successfully!');
                // Reload the page to show updated data
                window.location.reload();
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the production');
        }
    }

    // Update sales
    async function updateSales(productionId) {
        try {
            // Load production data
            const response = await fetch(`/cafeteria/daily-productions/${productionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) throw new Error('Failed to load production data');

            const production = await response.json();

            // Build sales form
            let html = '';
            production.production_items.forEach((item, index) => {
                html += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h5 class="font-medium text-gray-700">${item.product.product_name}</h5>
                                <div class="text-sm text-gray-500">
                                    Produced: ${item.actual_quantity} ${item.product.unit} |
                                    Remaining: ${item.remaining_quantity} ${item.product.unit}
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantity Sold <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="sales[${index}][quantity_sold]"
                                       step="0.001"
                                       min="0"
                                       max="${item.actual_quantity - item.quantity_wasted}"
                                       value="${item.quantity_sold}"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition sales-quantity"
                                       required
                                       oninput="validateSalesQuantity(this, ${item.actual_quantity}, ${item.quantity_wasted})">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantity Wasted <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="sales[${index}][quantity_wasted]"
                                       step="0.001"
                                       min="0"
                                       max="${item.actual_quantity - item.quantity_sold}"
                                       value="${item.quantity_wasted}"
                                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition waste-quantity"
                                       required
                                       oninput="validateWasteQuantity(this, ${item.actual_quantity}, ${item.quantity_sold})">
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Total Available: ${item.actual_quantity} ${item.product.unit}
                        </div>
                        <input type="hidden" name="sales[${index}][item_id]" value="${item.id}">
                    </div>
                `;
            });

            document.getElementById('salesItemsContainer').innerHTML = html;
            document.getElementById('salesProductionId').value = productionId;

            // Show modal
            document.getElementById('salesModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load sales data');
        }
    }

    // Validate sales quantity
    function validateSalesQuantity(input, max, currentWaste) {
        const wasteInput = input.closest('.border').querySelector('.waste-quantity');
        const maxAllowed = max - parseFloat(wasteInput.value || currentWaste);

        if (parseFloat(input.value) > maxAllowed) {
            input.value = maxAllowed;
        }
    }

    // Validate waste quantity
    function validateWasteQuantity(input, max, currentSales) {
        const salesInput = input.closest('.border').querySelector('.sales-quantity');
        const maxAllowed = max - parseFloat(salesInput.value || currentSales);

        if (parseFloat(input.value) > maxAllowed) {
            input.value = maxAllowed;
        }
    }

    // Submit sales update
    async function submitSalesUpdate() {
        const productionId = document.getElementById('salesProductionId').value;
        const form = document.getElementById('salesForm');
        const formData = new FormData(form);

        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                const match = key.match(/sales\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const [, index, field] = match;
                    if (!data.sales) data.sales = [];
                    if (!data.sales[index]) data.sales[index] = {};
                    data.sales[index][field] = value;
                }
            }
        }

        data.sales = Object.values(data.sales);

        // Show loading
        const updateBtn = document.getElementById('updateSalesBtn');
        const originalText = updateBtn.innerHTML;
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

        try {
            const response = await fetch(`/cafeteria/daily-productions/${productionId}/update-sales`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || 'Failed to update sales data');
            } else {
                alert('Sales data updated successfully!');
                closeModal('#salesModal');
                // Reload to show updated data
                window.location.reload();
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating sales data');
        } finally {
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalText;
        }
    }

    // Verify production
    async function verifyProduction(productionId) {
        if (!confirm('Are you sure you want to verify this production record?')) return;

        try {
            const response = await fetch(`/cafeteria/daily-productions/${productionId}/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || 'Failed to verify production');
            } else {
                alert('Production verified successfully!');
                window.location.reload();
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while verifying production');
        }
    }

    // Show statistics
    async function showStatistics() {
        try {
            const response = await fetch('{{ route("cafeteria.daily-productions.statistics") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) throw new Error('Failed to load statistics');

            const stats = await response.json();

            const html = `
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Productions</div>
                            <div class="text-2xl font-bold text-gray-800">${stats.total_records || 0}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Items Produced</div>
                            <div class="text-2xl font-bold text-gray-800">${parseFloat(stats.total_items_produced || 0).toLocaleString()}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Items Sold</div>
                            <div class="text-2xl font-bold text-green-600">${parseFloat(stats.total_items_sold || 0).toLocaleString()}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Items Wasted</div>
                            <div class="text-2xl font-bold text-red-600">${parseFloat(stats.total_items_wasted || 0).toLocaleString()}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Raw Material Cost</div>
                            <div class="text-2xl font-bold text-gray-800">KES ${parseFloat(stats.total_raw_material_cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Production Cost</div>
                            <div class="text-2xl font-bold text-gray-800">KES ${parseFloat(stats.total_production_cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Total Sales Value</div>
                            <div class="text-2xl font-bold text-green-600">KES ${parseFloat(stats.total_sales_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Average Profit</div>
                            <div class="text-2xl font-bold ${stats.avg_profit >= 0 ? 'text-green-600' : 'text-red-600'}">
                                KES ${parseFloat(stats.avg_profit || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600 mb-2">Average Margin Percentage</div>
                        <div class="text-3xl font-bold ${stats.avg_margin_percentage >= 0 ? 'text-green-600' : 'text-red-600'}">
                            ${parseFloat(stats.avg_margin_percentage || 0).toFixed(1)}%
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('statisticsContent').innerHTML = html;
            document.getElementById('statisticsModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load statistics');
        }
    }

    // Delete production
    function deleteProduction(productionId) {
        document.getElementById('deleteProductionId').value = productionId;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // Confirm delete
    async function confirmDelete() {
        const productionId = document.getElementById('deleteProductionId').value;

        // Show loading
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';

        try {
            const response = await fetch(`/cafeteria/daily-productions/${productionId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || 'Failed to delete production');
            } else {
                alert('Production deleted successfully!');
                closeModal('#deleteModal');
                window.location.reload();
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting production');
        } finally {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    }

    // View production
    function viewProduction(productionId) {
        window.location.href = `{{ route('cafeteria.daily-productions.index') }}?view=true&id=${productionId}`;
    }

    // Edit production
    function editProduction(productionId) {
        window.location.href = `{{ route('cafeteria.daily-productions.index') }}?edit=true&id=${productionId}`;
    }

    // Close view panel
    function closeViewPanel() {
        window.location.href = '{{ route("cafeteria.daily-productions.index") }}';
    }

    // Close edit panel
    function closeEditPanel() {
        window.location.href = '{{ route("cafeteria.daily-productions.index") }}';
    }

    // Close modal
    function closeModal(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection
