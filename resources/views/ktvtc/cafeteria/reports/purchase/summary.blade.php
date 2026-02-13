@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Purchase Summary Report')
@section('page-title', 'Purchase Summary Report')
@section('page-description', 'View purchase orders and supplier analysis')

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
        Purchase Summary
    </span>
</li>
@endsection

@section('content')
<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('cafeteria.reports.purchase.summary') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ $startDate }}"
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                       max="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ $endDate }}"
                       class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                       max="{{ now()->format('Y-m-d') }}">
            </div>

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

            <!-- Supplier Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_id"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
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

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- Total Purchases -->
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Total Purchases</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($totalPurchases, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ $purchases->count() }} orders, {{ $totalItems }} items
        </div>
    </div>

    <!-- Completed Orders -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Completed</p>
                <p class="text-2xl font-bold mt-1">{{ $completedPurchases }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ $purchases->count() > 0 ? round(($completedPurchases / $purchases->count()) * 100) : 0 }}% completion rate
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Pending</p>
                <p class="text-2xl font-bold mt-1">{{ $pendingPurchases }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            Awaiting delivery/completion
        </div>
    </div>

    <!-- Average Order -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Average Order</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($purchases->count() > 0 ? $totalPurchases / $purchases->count() : 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            Per order average
        </div>
    </div>
</div>

<!-- Supplier Breakdown -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Supplier Breakdown</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Supplier
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Orders
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Average Order
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Percentage
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($supplierBreakdown as $supplierData)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-gray-900">{{ $supplierData['name'] }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $supplierData['count'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                        KES {{ number_format($supplierData['amount'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        KES {{ number_format($supplierData['count'] > 0 ? $supplierData['amount'] / $supplierData['count'] : 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $totalPurchases > 0 ? round(($supplierData['amount'] / $totalPurchases) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Purchase Orders List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-semibold text-gray-800">Purchase Orders</h3>
        <div class="text-sm text-gray-500">
            {{ $purchases->count() }} orders found
        </div>
    </div>

    @if($purchases->isEmpty())
    <div class="p-8 text-center">
        <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-600">No purchase orders found for selected period</p>
        <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order #
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Supplier
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Items
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($purchases as $purchase)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-primary">{{ $purchase->order_number }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $purchase->supplier->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $purchase->supplier->phone ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>{{ $purchase->order_date->format('Y-m-d') }}</div>
                        <div class="text-sm text-gray-500">{{ $purchase->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>{{ $purchase->total_quantity }}</div>
                        <div class="text-xs text-gray-500">{{ $purchase->items->count() }} types</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $purchase->status === 'completed' ? 'bg-green-100 text-green-800' :
                               ($purchase->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                        KES {{ number_format($purchase->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('cafeteria.purchase-orders.show', $purchase) }}"
                           class="text-primary hover:text-red-700">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- Monthly Trend Chart -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="font-semibold text-gray-800 mb-4">Monthly Purchase Trend</h3>
    <div class="h-64">
        <canvas id="purchaseTrendChart"></canvas>
    </div>
</div>

<!-- Export Button -->
<div class="mt-6 text-center">
    <button onclick="exportReport()"
            class="bg-primary text-white font-medium py-3 px-6 rounded-lg hover:bg-red-700 transition inline-flex items-center">
        <i class="fas fa-file-export mr-2"></i> Export Report to Excel
    </button>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function exportReport() {
        const startDate = '{{ $startDate }}';
        const endDate = '{{ $endDate }}';
        const shopId = '{{ request("shop_id") }}';
        const supplierId = '{{ request("supplier_id") }}';

        let url = '{{ route("cafeteria.reports.purchase.summary") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;
        if (supplierId) url += '&supplier_id=' + supplierId;

        window.open(url, '_blank');
    }

    // Monthly Trend Chart
    $(document).ready(function() {
        const ctx = document.getElementById('purchaseTrendChart').getContext('2d');
        const monthlyData = @json($monthlyTrend);

        const labels = monthlyData.map(item => item.month);
        const data = monthlyData.map(item => item.amount);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Purchase Amount',
                    data: data,
                    borderColor: '#E63946',
                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
