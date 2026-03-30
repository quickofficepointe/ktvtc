@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Inventory Stock Levels')
@section('page-title', 'Inventory Stock Levels')
@section('page-description', 'Monitor inventory stock and identify low stock items')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Reports
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Inventory Stock Levels
    </span>
</li>
@endsection

@section('content')
<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('cafeteria.reports.inventory.stock-levels') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Shop Selection -->
            @if($shops->count() > 1)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
                <select name="shop_id"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    <option value="">All Shops</option>
                    @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                        {{ $shop->shop_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Stock Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                <select name="stock_status"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    <option value="">All Items</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock Only</option>
                    <option value="normal" {{ request('stock_status') == 'normal' ? 'selected' : '' }}>Normal Stock</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end">
                <button type="submit"
                        class="bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition flex items-center">
                    <i class="fas fa-filter mr-2"></i> Generate Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Inventory Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- Total Items -->
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm opacity-90">Total Items</p>
                <h3 class="text-3xl font-bold mt-2">{{ $stocks->count() }}</h3>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-boxes text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm opacity-90">Low Stock Items</p>
                <h3 class="text-3xl font-bold mt-2">{{ $lowStock->count() }}</h3>
                <p class="text-sm mt-2 opacity-90">Needs Attention</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Out of Stock -->
    <div class="bg-gradient-to-r from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm opacity-90">Out of Stock</p>
                <h3 class="text-3xl font-bold mt-2">{{ $outOfStock->count() }}</h3>
                <p class="text-sm mt-2 opacity-90">Requires Reorder</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-times-circle text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Inventory Value -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm opacity-90">Total Inventory Value</p>
                <h3 class="text-3xl font-bold mt-2">{{ number_format($totalValue, 2) }}</h3>
                <p class="text-sm mt-2 opacity-90">Current Cost Value</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-coins text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stock Levels Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Inventory Stock Levels</h3>
        <p class="text-sm text-gray-600">Showing all tracked inventory items</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Product
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        SKU / Code
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Current Stock
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Min Stock Level
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Stock Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cost Price
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Value
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stocks as $stock)
                @php
                    $product = $stock->product;
                    $isTracked = $product && $product->track_inventory;

                    if (!$isTracked) {
                        $status = 'Not Tracked';
                        $statusColor = 'bg-gray-100 text-gray-800';
                    } elseif ($stock->current_stock <= 0) {
                        $status = 'Out of Stock';
                        $statusColor = 'bg-gray-100 text-gray-800';
                    } elseif ($stock->current_stock <= $product->minimum_stock) {
                        $status = 'Low Stock';
                        $statusColor = 'bg-yellow-100 text-yellow-800';
                    } else {
                        $status = 'In Stock';
                        $statusColor = 'bg-green-100 text-green-800';
                    }

                    $totalValue = $stock->current_stock * ($product->cost_price ?? 0);
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $product->product_name ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $stock->shop->shop_name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $product->product_code ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $product->sku ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $stock->current_stock }}</div>
                        <div class="text-sm text-gray-500">{{ $product->unit_of_measure ?? 'units' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $product->minimum_stock ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($product->cost_price ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ number_format($totalValue, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if($isTracked && $stock->current_stock <= $product->minimum_stock)
                        <a href=""
                           class="text-primary hover:text-red-700 mr-3">
                            <i class="fas fa-shopping-cart mr-1"></i> Reorder
                        </a>
                        @endif
                        <a href=""
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                        No inventory items found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($stocks->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $stocks->links() }}
    </div>
    @endif
</div>

<!-- Low Stock Warning Section -->
@if($lowStock->count() > 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
                Low Stock Alert: {{ $lowStock->count() }} item(s) need attention
            </h3>
            <div class="mt-2 text-sm text-yellow-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($lowStock->take(5) as $stock)
                    <li>
                        {{ $stock->product->product_name ?? 'Unknown' }} -
                        Current: {{ $stock->current_stock }},
                        Minimum: {{ $stock->product->minimum_stock ?? 'N/A' }}
                    </li>
                    @endforeach
                    @if($lowStock->count() > 5)
                    <li>... and {{ $lowStock->count() - 5 }} more items</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Export Options -->
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-sm font-medium text-gray-700">Export Options</h4>
            <p class="text-sm text-gray-500">Export inventory report in different formats</p>
        </div>
        <div class="flex space-x-3">
            <a href="#"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
            <a href="#"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-file-excel mr-2"></i> Excel
            </a>
            <a href="#"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-print mr-2"></i> Print
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript functionality here if needed
});
</script>
@endpush
