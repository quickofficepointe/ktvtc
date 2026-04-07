@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Inventory Movement Report')
@section('page-title', 'Inventory Movement Report')
@section('page-description', 'Track stock movements, adjustments, and transfers')

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
        Inventory
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Stock Movement
    </span>
</li>
@endsection

@section('styles')
<style>
    .movement-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .movement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
    .movement-in { background-color: rgba(16, 185, 129, 0.1); border-left: 3px solid #10B981; }
    .movement-out { background-color: rgba(239, 68, 68, 0.1); border-left: 3px solid #EF4444; }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.reports.inventory.movement') }}" class="space-y-4">
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

                <!-- Movement Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Movement Type</label>
                    <select name="movement_type"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Types</option>
                        @foreach($movementTypes ?? [] as $key => $label)
                        <option value="{{ $key }}" {{ request('movement_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
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

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Movements -->
        <div class="movement-card bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Movements</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($movements->total() ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ $movements->count() ?? 0 }} records in this period
            </div>
        </div>

        <!-- Stock In -->
        <div class="movement-card bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Stock In</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalIn ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Units added to inventory
            </div>
        </div>

        <!-- Stock Out -->
        <div class="movement-card bg-gradient-to-r from-red-500 to-orange-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Stock Out</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalOut ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-arrow-up text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Units removed from inventory
            </div>
        </div>

        <!-- Net Movement -->
        <div class="movement-card bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Net Movement</p>
                    <p class="text-2xl font-bold mt-1">
                        <span class="{{ ($totalIn ?? 0) - ($totalOut ?? 0) >= 0 ? 'text-green-300' : 'text-red-300' }}">
                            {{ number_format(($totalIn ?? 0) - ($totalOut ?? 0), 2) }}
                        </span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ (($totalIn ?? 0) - ($totalOut ?? 0)) >= 0 ? 'Net addition' : 'Net reduction' }}
            </div>
        </div>
    </div>

    <!-- Movement Type Breakdown Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Movement by Type Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Movement by Type</h3>
            <div class="h-64">
                <canvas id="movementTypeChart"></canvas>
            </div>
        </div>

        <!-- Daily Movement Trend Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Daily Movement Trend</h3>
            <div class="h-64">
                <canvas id="dailyMovementChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Moving Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-chart-line text-primary mr-2"></i> Top Moving Products
            </h3>
            <p class="text-sm text-gray-600">Products with highest movement volume</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topMovingProducts ?? [] as $product)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-primary text-sm"></i>
                                </div>
                                <div class="font-medium text-gray-900">{{ $product->product_name ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->product_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-green-600 font-medium">
                            +{{ number_format($product->stock_in ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">
                            -{{ number_format($product->stock_out ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold {{ ($product->stock_in ?? 0) - ($product->stock_out ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format(($product->stock_in ?? 0) - ($product->stock_out ?? 0), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($product->current_stock ?? 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No movement data available
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-list text-primary mr-2"></i> Stock Movement Details
                </h3>
                <p class="text-sm text-gray-600">Detailed list of all inventory movements</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $movements->total() ?? 0 }} movements found
            </div>
        </div>

        @if(isset($movements) && $movements->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movement #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($movements as $movement)
                    @php
                        $isIn = in_array($movement->movement_type, ['purchase', 'transfer_in', 'adjustment_in', 'production_in', 'return_in']);
                        $movementClass = $isIn ? 'movement-in' : 'movement-out';
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $movementClass }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $movement->movement_date->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $movement->movement_date->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-mono text-sm text-gray-900">{{ $movement->movement_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $movement->product->product_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $movement->product->product_code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $movement->movement_type == 'purchase' ? 'bg-green-100 text-green-800' :
                                   ($movement->movement_type == 'sale' ? 'bg-blue-100 text-blue-800' :
                                   ($movement->movement_type == 'transfer_in' ? 'bg-purple-100 text-purple-800' :
                                   ($movement->movement_type == 'transfer_out' ? 'bg-orange-100 text-orange-800' :
                                   ($movement->movement_type == 'adjustment_in' ? 'bg-yellow-100 text-yellow-800' :
                                   ($movement->movement_type == 'adjustment_out' ? 'bg-red-100 text-red-800' :
                                   'bg-gray-100 text-gray-800'))))) }}">
                                {{ $movementTypes[$movement->movement_type] ?? ucfirst($movement->movement_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold {{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isIn ? '+' : '-' }}{{ number_format($movement->quantity, 2) }} {{ $movement->unit }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            KES {{ number_format($movement->unit_cost ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            KES {{ number_format($movement->total_cost ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                {{ number_format($movement->previous_stock, 2) }} → {{ number_format($movement->new_stock, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $movement->reference_number ?? 'N/A' }}</div>
                            @if($movement->reason)
                            <div class="text-xs text-gray-500">{{ Str::limit($movement->reason, 30) }}</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="4" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4">
                            <span class="text-green-600">+{{ number_format($totalIn ?? 0, 2) }}</span> /
                            <span class="text-red-600">-{{ number_format($totalOut ?? 0, 2) }}</span>
                        </td>
                        <td colspan="2" class="px-6 py-4 text-primary">KES {{ number_format($movements->sum('total_cost'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $movements->links() }}
        </div>
        @else
        <div class="p-8 text-center">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-exchange-alt text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-600">No stock movements found for selected period</p>
                <p class="text-sm text-gray-500 mt-1">Try adjusting your date range or filter criteria</p>
            </div>
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
        const movementType = '{{ request("movement_type") }}';

        let url = '{{ route("cafeteria.reports.inventory.movement") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;
        if (movementType) url += '&movement_type=' + movementType;

        window.open(url, '_blank');
    }

    // Movement Type Chart
    $(document).ready(function() {
        const movementTypeData = @json($movementTypeBreakdown ?? []);

        if (Object.keys(movementTypeData).length > 0) {
            const ctx = document.getElementById('movementTypeChart').getContext('2d');
            const labels = Object.keys(movementTypeData);
            const data = Object.values(movementTypeData);
            const colors = ['#10B981', '#EF4444', '#8B5CF6', '#F59E0B', '#3B82F6', '#EC4899', '#06B6D4'];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Movement Count',
                        data: data,
                        backgroundColor: colors.slice(0, labels.length),
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' movements';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Movements'
                            }
                        }
                    }
                }
            });
        }

        // Daily Movement Chart
        const dailyData = @json($dailyMovements ?? []);

        if (dailyData.length > 0) {
            const dailyCtx = document.getElementById('dailyMovementChart').getContext('2d');
            const labels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            });
            const stockIn = dailyData.map(item => item.stock_in);
            const stockOut = dailyData.map(item => item.stock_out);

            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Stock In',
                            data: stockIn,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Stock Out',
                            data: stockOut,
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
