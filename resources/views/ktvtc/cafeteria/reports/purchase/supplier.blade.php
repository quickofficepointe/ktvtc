@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Supplier Purchase Report')
@section('page-title', 'Supplier Purchase Report')
@section('page-description', 'Detailed analysis of purchases by supplier')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Reports
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Purchase
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Supplier Report
    </span>
</li>
@endsection

@section('styles')
<style>
    .supplier-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .supplier-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A8A8A8); }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #B87333); }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.reports.purchase.supplier') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date"
                           name="start_date"
                           value="{{ request('start_date', $startDate ?? now()->startOfMonth()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date"
                           name="end_date"
                           value="{{ request('end_date', $endDate ?? now()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Shop Selection -->
                @if(isset($shops) && $shops->count() > 1)
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
                        @foreach($suppliers ?? [] as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition flex items-center">
                        <i class="fas fa-chart-line mr-2"></i> Generate Report
                    </button>
                    <button type="button"
                            onclick="exportReport()"
                            class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition flex items-center">
                        <i class="fas fa-file-excel mr-2"></i> Export
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Top Suppliers Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $topSuppliers = $supplierTotals ?? collect();
            $rank = 1;
        @endphp
        @foreach($topSuppliers->take(3) as $supplierData)
        <div class="supplier-card rounded-xl overflow-hidden shadow-lg">
            <div class="rank-{{ $rank }} p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Rank #{{ $rank }}</p>
                        <h3 class="text-xl font-bold mt-1">{{ $supplierData->supplier->supplier_name ?? 'N/A' }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-30 flex items-center justify-center">
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Total Purchases</p>
                        <p class="text-lg font-bold text-gray-800">KES {{ number_format($supplierData->total_amount ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Orders</p>
                        <p class="text-lg font-bold text-gray-800">{{ number_format($supplierData->order_count ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Average Order</p>
                        <p class="text-lg font-bold text-gray-800">KES {{ number_format(($supplierData->order_count ?? 0) > 0 ? ($supplierData->total_amount ?? 0) / ($supplierData->order_count ?? 1) : 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">% of Total</p>
                        <p class="text-lg font-bold text-primary">{{ number_format(($supplierData->total_amount ?? 0) / (($totalPurchaseAmount ?? 1) * 100), 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>
        @php $rank++; @endphp
        @endforeach
    </div>

    <!-- Supplier Performance Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Supplier Performance</h3>
        <div class="h-80">
            <canvas id="supplierPerformanceChart"></canvas>
        </div>
    </div>

    <!-- Supplier Breakdown Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-building text-primary mr-2"></i> Supplier Breakdown
            </h3>
            <p class="text-sm text-gray-600">Detailed purchase analysis by supplier</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($supplierTotals ?? [] as $index => $supplierData)
                    @php
                        $percentage = ($totalPurchaseAmount ?? 0) > 0 ? (($supplierData->total_amount ?? 0) / ($totalPurchaseAmount ?? 1)) * 100 : 0;
                        $trend = $supplierData->trend ?? 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                {{ $index == 0 ? 'bg-yellow-100 text-yellow-800' : ($index == 1 ? 'bg-gray-100 text-gray-800' : ($index == 2 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-primary"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $supplierData->supplier->supplier_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $supplierData->supplier->supplier_code ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $supplierData->supplier->contact_person ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $supplierData->supplier->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ number_format($supplierData->order_count ?? 0) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                            KES {{ number_format($supplierData->total_amount ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            KES {{ number_format(($supplierData->order_count ?? 0) > 0 ? ($supplierData->total_amount ?? 0) / ($supplierData->order_count ?? 1) : 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-24">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="{{ $trend >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <i class="fas fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }} mr-1"></i>
                                {{ number_format(abs($trend), 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-building text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-600">No supplier data found for selected period</p>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or date range</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($supplierTotals) && $supplierTotals->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="4" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($totalPurchaseAmount ?? 0, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Purchase Orders by Supplier -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-shopping-cart text-primary mr-2"></i> Purchase Orders by Supplier
                </h3>
                <p class="text-sm text-gray-600">Detailed list of purchase orders grouped by supplier</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $purchases->total() ?? 0 }} orders found
            </div>
        </div>

        @if(isset($purchases) && $purchases->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchases as $purchase)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-primary">{{ $purchase->po_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $purchase->supplier->supplier_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $purchase->supplier->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>{{ $purchase->order_date->format('Y-m-d') }}</div>
                            <div class="text-xs text-gray-500">{{ $purchase->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>{{ number_format($purchase->total_quantity ?? 0) }}</div>
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('cafeteria.purchase-orders.show', $purchase) }}"
                               class="text-primary hover:text-red-700">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="5" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($purchases->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $purchases->links() }}
        </div>
        @else
        <div class="p-8 text-center">
            <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-600">No purchase orders found for selected period</p>
            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or date range</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function exportReport() {
        const startDate = '{{ request("start_date", $startDate ?? now()->startOfMonth()->format("Y-m-d")) }}';
        const endDate = '{{ request("end_date", $endDate ?? now()->format("Y-m-d")) }}';
        const shopId = '{{ request("shop_id") }}';
        const supplierId = '{{ request("supplier_id") }}';

        let url = '{{ route("cafeteria.reports.purchase.supplier") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;
        if (supplierId) url += '&supplier_id=' + supplierId;

        window.open(url, '_blank');
    }

    // Supplier Performance Chart
    $(document).ready(function() {
        const supplierData = @json($supplierTotals ?? []);

        if (supplierData.length > 0) {
            const ctx = document.getElementById('supplierPerformanceChart').getContext('2d');
            const labels = supplierData.map(s => s.supplier?.supplier_name ?? 'Unknown');
            const amounts = supplierData.map(s => s.total_amount ?? 0);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Purchase Amount',
                        data: amounts,
                        backgroundColor: 'rgba(230, 57, 70, 0.7)',
                        borderColor: '#E63946',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.raw.toLocaleString();
                                }
                            }
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
        }
    });
</script>
@endsection
