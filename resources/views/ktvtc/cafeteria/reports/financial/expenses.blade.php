@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Expenses Report')
@section('page-title', 'Expenses Report')
@section('page-description', 'Track and analyze all business expenses including purchases, operational costs, and overheads')

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
        Financial
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Expenses
    </span>
</li>
@endsection

@section('styles')
<style>
    .expense-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .expense-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.reports.financial.expenses') }}" class="space-y-4">
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
        <!-- Total Expenses -->
        <div class="expense-card bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($purchaseExpenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-credit-card text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                {{ $purchaseOrdersCount ?? 0 }} purchase orders
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="expense-card bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Average Order Value</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format(($purchaseOrdersCount ?? 0) > 0 ? ($purchaseExpenses ?? 0) / ($purchaseOrdersCount ?? 1) : 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Per purchase order average
            </div>
        </div>

        <!-- Daily Average Expense -->
        <div class="expense-card bg-gradient-to-r from-purple-500 to-indigo-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Daily Average</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($dailyAverage ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                Over selected period
            </div>
        </div>

        <!-- Expense Trend -->
        <div class="expense-card bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Expense Trend</p>
                    <p class="text-2xl font-bold mt-1">
                        @php
                            $trend = $expenseTrend ?? 0;
                        @endphp
                        <span class="{{ $trend >= 0 ? 'text-green-300' : 'text-red-300' }}">
                            {{ $trend >= 0 ? '+' : '' }}{{ number_format($trend, 1) }}%
                        </span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }} text-xl"></i>
                </div>
            </div>
            <div class="mt-2 text-sm opacity-80">
                vs previous period
            </div>
        </div>
    </div>

    <!-- Monthly Expense Trend Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Monthly Expense Trend</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Total: KES {{ number_format($monthlyExpenses->sum('amount') ?? 0, 2) }}</span>
            </div>
        </div>
        <div class="h-80">
            <canvas id="expenseTrendChart"></canvas>
        </div>
    </div>

    <!-- Supplier Expense Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-building text-primary mr-2"></i> Supplier Expense Breakdown
            </h3>
            <p class="text-sm text-gray-600">Detailed breakdown of expenses by supplier</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($supplierExpenses ?? [] as $expense)
                    @php
                        $supplier = $expense->supplier;
                        $percentage = ($purchaseExpenses ?? 0) > 0 ? ($expense->total / ($purchaseExpenses ?? 1)) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-primary text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $supplier->supplier_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $supplier->supplier_code ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $supplier->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ number_format($expense->order_count ?? 0) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-primary">KES {{ number_format($expense->total ?? 0, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">KES {{ number_format(($expense->order_count ?? 0) > 0 ? ($expense->total ?? 0) / ($expense->order_count ?? 1) : 0, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-24">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-chart-pie text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-600">No expense data found</p>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or date range</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(isset($supplierExpenses) && $supplierExpenses->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="3" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($purchaseExpenses ?? 0, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Purchase Orders List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-shopping-cart text-primary mr-2"></i> Purchase Orders
                </h3>
                <p class="text-sm text-gray-600">List of all purchase orders in the selected period</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ isset($purchases) ? $purchases->total() : 0 }} orders found
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
                                   ($purchase->status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                   'bg-gray-100 text-gray-800')) }}">
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

    <!-- Expense Analysis Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Top Expense Category -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-800">Top Expense Category</h4>
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-tag text-orange-600"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">
                {{ $topExpenseCategory ?? 'N/A' }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
                {{ number_format($topExpensePercentage ?? 0, 1) }}% of total expenses
            </div>
        </div>

        <!-- Expense vs Revenue Ratio -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-800">Expense to Revenue Ratio</h4>
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-chart-pie text-blue-600"></i>
                </div>
            </div>
            <div class="text-2xl font-bold {{ ($expenseRatio ?? 0) > 70 ? 'text-red-600' : (($expenseRatio ?? 0) > 50 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ number_format($expenseRatio ?? 0, 1) }}%
            </div>
            <div class="text-sm text-gray-500 mt-1">
                of total revenue
            </div>
        </div>

        <!-- Projected Monthly Expense -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-800">Projected Monthly Expense</h4>
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">
                KES {{ number_format($projectedMonthlyExpense ?? 0, 2) }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
                Based on current trend
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Export Report
    function exportReport() {
        const startDate = '{{ request("start_date", $startDate ?? now()->startOfMonth()->format("Y-m-d")) }}';
        const endDate = '{{ request("end_date", $endDate ?? now()->format("Y-m-d")) }}';
        const shopId = '{{ request("shop_id") }}';

        let url = '{{ route("cafeteria.reports.financial.expenses") }}?export=excel' +
                  '&start_date=' + startDate +
                  '&end_date=' + endDate;

        if (shopId) url += '&shop_id=' + shopId;

        window.open(url, '_blank');
    }

    // Monthly Expense Trend Chart
    $(document).ready(function() {
        const monthlyData = @json($monthlyExpenses ?? []);

        if (monthlyData.length > 0) {
            const ctx = document.getElementById('expenseTrendChart').getContext('2d');
            const labels = monthlyData.map(item => item.month);
            const data = monthlyData.map(item => item.amount);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Expenses',
                        data: data,
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
        } else {
            // Show message if no data
            document.getElementById('expenseTrendChart').innerHTML =
                '<div class="flex items-center justify-center h-full"><p class="text-gray-500">No data available for the selected period</p></div>';
        }
    });
</script>
@endsection
