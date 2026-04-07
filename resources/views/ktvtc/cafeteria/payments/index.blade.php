@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Payment Transactions')
@section('page-title', 'Payment Management')
@section('page-description', 'View and manage all payment transactions from Online Orders and POS Sales')

@section('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .source-badge-online {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .source-badge-pos {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .transaction-row:hover {
        background-color: rgba(185, 28, 28, 0.05);
    }
    .filter-active {
        background-color: #B91C1C !important;
        color: white !important;
        border-color: #B91C1C !important;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['total_count'] ?? 0 }} transactions</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">M-Pesa Payments</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['mpesa_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['mpesa_count'] ?? 0 }} transactions</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fab fa-mpesa text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Cash Payments</p>
                    <p class="text-2xl font-bold text-gray-800">KES {{ number_format($stats['cash_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['cash_count'] ?? 0 }} transactions</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-hand-holding-usd text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Payments</p>
                    <p class="text-2xl font-bold text-red-600">KES {{ number_format($stats['pending_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['pending_count'] ?? 0 }} pending</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Daily Revenue (Last 7 Days)</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500">Total: KES {{ number_format(collect($chartData['daily'])->sum('amount'), 2) }}</span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Payment Methods</h3>
                <span class="text-xs text-gray-500">Last 30 days</span>
            </div>
            <div class="chart-container">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Source Breakdown Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Online Orders vs POS Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Sales Source Breakdown</h3>
                <div class="flex space-x-2">
                    <span class="px-2 py-1 text-xs rounded-full source-badge-online text-white">Online Orders</span>
                    <span class="px-2 py-1 text-xs rounded-full source-badge-pos text-white">POS Sales</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">KES {{ number_format($sourceBreakdown['online']['amount'] ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-600">Online Orders</div>
                    <div class="text-xs text-gray-500">{{ $sourceBreakdown['online']['count'] ?? 0 }} orders</div>
                    <div class="text-xs text-purple-600 mt-1">{{ $sourceBreakdown['online']['percentage'] ?? 0 }}% of total</div>
                </div>
                <div class="text-center p-4 bg-pink-50 rounded-lg">
                    <div class="text-2xl font-bold text-pink-600">KES {{ number_format($sourceBreakdown['pos']['amount'] ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-600">POS Sales</div>
                    <div class="text-xs text-gray-500">{{ $sourceBreakdown['pos']['count'] ?? 0 }} sales</div>
                    <div class="text-xs text-pink-600 mt-1">{{ $sourceBreakdown['pos']['percentage'] ?? 0 }}% of total</div>
                </div>
            </div>
            <div class="chart-container" style="height: 200px;">
                <canvas id="sourceBreakdownChart"></canvas>
            </div>
        </div>

        <!-- Hourly Sales Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Today's Sales by Hour</h3>
                <span class="text-xs text-gray-500">{{ now()->format('F j, Y') }}</span>
            </div>
            <div class="chart-container">
                <canvas id="hourlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('cafeteria.payments.index') }}" id="filterForm" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select name="date_range" id="dateRange" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div id="customDateRange" class="flex gap-4 {{ request('date_range') == 'custom' ? '' : 'hidden' }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded-lg p-2">
                </div>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select name="payment_method" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">All Methods</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" placeholder="Invoice #, Receipt #, Customer..." value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-filter mr-2"></i> Apply
                </button>
                @if(request()->anyFilled(['payment_method', 'status', 'search', 'date_range']))
                <a href="{{ route('cafeteria.payments.index') }}" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
                @endif
            </div>

            <div>
                <button type="button" onclick="exportTransactions()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-excel mr-2"></i> Export
                </button>
            </div>
        </form>
    </div>

    <!-- Source Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <div class="px-6 pt-4">
                <nav class="flex space-x-4" aria-label="Source Tabs">
                    <button onclick="filterBySource('all')" id="source-all-tab"
                            class="source-tab px-4 py-2 font-medium text-sm rounded-md bg-red-600 text-white">
                        <i class="fas fa-chart-line mr-2"></i> All Transactions
                    </button>
                    <button onclick="filterBySource('online')" id="source-online-tab"
                            class="source-tab px-4 py-2 font-medium text-sm rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-globe mr-2"></i> Online Orders
                    </button>
                    <button onclick="filterBySource('pos')" id="source-pos-tab"
                            class="source-tab px-4 py-2 font-medium text-sm rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-store mr-2"></i> POS Sales
                    </button>
                </nav>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    @php
                        $sale = $transaction->sale;
                        // Determine source using sale_type and channel
                        $isOnline = $sale && ($sale->sale_type === 'online' || in_array($sale->channel, ['website', 'mobile_app']));
                        $source = $isOnline ? 'online' : 'pos';
                        $sourceLabel = $source === 'online' ? 'Online Order' : 'POS Sale';
                        $sourceBadgeClass = $source === 'online'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-pink-100 text-pink-800';
                        $sourceIcon = $source === 'online' ? 'fa-globe' : 'fa-store';
                    @endphp
                    <tr class="transaction-row hover:bg-gray-50 transition" data-source="{{ $source }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $transaction->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale)
                            <a href="{{ route('cafeteria.sales.show', $sale->id) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $sale->invoice_number }}
                            </a>
                            @if($sale->customer_name)
                            <div class="text-xs text-gray-500">{{ $sale->customer_name }}</div>
                            @endif
                            @else
                            <span class="text-gray-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $sale?->customer_name ?? 'Walk-in Customer' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale?->customer_phone ?? $transaction->phone_number ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($transaction->payment_method == 'mpesa')
                                <i class="fab fa-mpesa text-green-600 mr-2 text-lg"></i>
                                <span>M-Pesa</span>
                                @elseif($transaction->payment_method == 'cash')
                                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                                <span>Cash</span>
                                @elseif($transaction->payment_method == 'card')
                                <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                                <span>Card</span>
                                @else
                                <i class="fas fa-university text-purple-600 mr-2"></i>
                                <span>Bank Transfer</span>
                                @endif
                            </div>
                            @if($transaction->mpesa_receipt)
                            <div class="text-xs text-gray-500 mt-1">Receipt: {{ $transaction->mpesa_receipt }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">KES {{ number_format($transaction->amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($transaction->status == 'completed')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Completed
                            </span>
                            @elseif($transaction->status == 'pending')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @elseif($transaction->status == 'failed')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Failed
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                <i class="fas fa-undo mr-1"></i> Reversed
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sourceBadgeClass }}">
                                <i class="fas {{ $sourceIcon }} mr-1"></i>
                                {{ $sourceLabel }}
                            </span>
                            @if($sale && $sale->sale_type)
                            <div class="text-xs text-gray-400 mt-1">{{ ucfirst($sale->sale_type) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="viewTransaction({{ $transaction->id }})"
                                        class="text-blue-600 hover:text-blue-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="printReceipt({{ $transaction->id }})"
                                        class="text-gray-600 hover:text-gray-900" title="Print Receipt">
                                    <i class="fas fa-print"></i>
                                </button>
                                @if($transaction->status == 'pending')
                                <button onclick="markCompleted({{ $transaction->id }})"
                                        class="text-green-600 hover:text-green-900" title="Mark Completed">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                @endif
                                @if($transaction->status == 'completed')
                                <button onclick="reverseTransaction({{ $transaction->id }})"
                                        class="text-red-600 hover:text-red-900" title="Reverse">
                                    <i class="fas fa-undo-alt"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-receipt text-gray-400 text-4xl mb-3"></i>
                                <p class="text-gray-600">No transactions found</p>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="transactionModal" class="fixed inset-0 z-50 overflow-auto hidden" aria-hidden="true">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl">
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Transaction Details</h3>
                <button onclick="closeTransactionModal()" class="text-white text-2xl">&times;</button>
            </div>
            <div id="transactionDetails" class="p-6">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeTransactionModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Chart.js initialization
    let dailyChart, paymentMethodChart, sourceChart, hourlyChart;

    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        setupEventListeners();
    });

    function initializeCharts() {
        // Daily Revenue Chart
        const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
        const dailyData = @json($chartData['daily']);

        dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => d.date),
                datasets: [{
                    label: 'Revenue (KES)',
                    data: dailyData.map(d => d.amount),
                    borderColor: '#B91C1C',
                    backgroundColor: 'rgba(185, 28, 28, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#B91C1C',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `KES ${context.raw.toLocaleString()}`;
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

        // Payment Method Chart
        const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const methodData = @json($chartData['method_breakdown']);

        paymentMethodChart = new Chart(methodCtx, {
            type: 'doughnut',
            data: {
                labels: ['M-Pesa', 'Cash', 'Card'],
                datasets: [{
                    data: [methodData.mpesa, methodData.cash, methodData.card],
                    backgroundColor: ['#25A25A', '#F59E0B', '#3B82F6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = methodData.mpesa + methodData.cash + methodData.card;
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return `${context.label}: KES ${context.raw.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Source Breakdown Chart
        const sourceCtx = document.getElementById('sourceBreakdownChart').getContext('2d');
        const sourceData = @json($sourceBreakdown);

        sourceChart = new Chart(sourceCtx, {
            type: 'pie',
            data: {
                labels: ['Online Orders', 'POS Sales'],
                datasets: [{
                    data: [sourceData.online.amount, sourceData.pos.amount],
                    backgroundColor: ['#8B5CF6', '#EC4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = sourceData.online.amount + sourceData.pos.amount;
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return `${context.label}: KES ${context.raw.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Hourly Sales Chart
        const hourlyCtx = document.getElementById('hourlySalesChart').getContext('2d');
        const hourlyData = @json($chartData['hourly']);

        hourlyChart = new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(h => h.hour),
                datasets: [{
                    label: 'Sales (KES)',
                    data: hourlyData.map(h => h.amount),
                    backgroundColor: '#B91C1C',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `KES ${context.raw.toLocaleString()}`;
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

    function setupEventListeners() {
        // Date range filter
        $('#dateRange').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customDateRange').removeClass('hidden');
            } else {
                $('#customDateRange').addClass('hidden');
                $('#filterForm').submit();
            }
        });

        // Auto-submit on custom date change
        $('#customDateRange input').on('change', function() {
            if ($('#dateRange').val() === 'custom') {
                $('#filterForm').submit();
            }
        });
    }

    function filterBySource(source) {
        // Update active tab styling
        $('.source-tab').removeClass('bg-red-600 text-white').addClass('text-gray-500 hover:text-gray-700 hover:bg-gray-100');
        $(`#source-${source}-tab`).addClass('bg-red-600 text-white').removeClass('text-gray-500 hover:text-gray-700 hover:bg-gray-100');

        // Filter table rows
        if (source === 'all') {
            $('tbody tr').show();
        } else {
            $('tbody tr').each(function() {
                if ($(this).data('source') === source) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    async function viewTransaction(id) {
        try {
            const response = await fetch(`/cafeteria/payments/${id}`);
            const transaction = await response.json();

            const sale = transaction.sale;
            const isOnline = sale && (sale.sale_type === 'online' || (sale.channel && ['website', 'mobile_app'].includes(sale.channel)));

            const detailsHtml = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Transaction #</p>
                            <p class="font-medium">${transaction.transaction_number}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Status</p>
                            <span class="px-2 py-1 text-xs rounded-full ${transaction.status === 'completed' ? 'bg-green-100 text-green-800' : transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                ${transaction.status.toUpperCase()}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500">Amount</p>
                        <p class="text-2xl font-bold text-red-600">KES ${parseFloat(transaction.amount).toLocaleString()}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Payment Method</p>
                            <p class="font-medium capitalize">${transaction.payment_method}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">M-Pesa Receipt</p>
                            <p class="font-mono text-sm">${transaction.mpesa_receipt || 'N/A'}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Created At</p>
                            <p class="text-sm">${new Date(transaction.created_at).toLocaleString()}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Completed At</p>
                            <p class="text-sm">${transaction.completed_at ? new Date(transaction.completed_at).toLocaleString() : 'N/A'}</p>
                        </div>
                    </div>

                    ${sale ? `
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="font-medium text-gray-900 mb-2">Sale Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Invoice #</p>
                                <p class="font-medium">${sale.invoice_number}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Customer</p>
                                <p class="font-medium">${sale.customer_name || 'Walk-in Customer'}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="font-medium">${sale.customer_phone || 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Sale Type</p>
                                <p class="font-medium capitalize">${sale.sale_type || 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Channel</p>
                                <p class="font-medium capitalize">${sale.channel || 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500">Source</p>
                                <p class="font-medium">${isOnline ? 'Online Order' : 'POS Sale'}</p>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;

            $('#transactionDetails').html(detailsHtml);
            $('#transactionModal').removeClass('hidden');
        } catch (error) {
            console.error('Error fetching transaction:', error);
            Swal.fire('Error', 'Failed to load transaction details', 'error');
        }
    }

    function closeTransactionModal() {
        $('#transactionModal').addClass('hidden');
    }

    async function markCompleted(id) {
        const result = await Swal.fire({
            title: 'Mark as Completed?',
            text: 'This will mark the transaction as completed and update the sale status.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            confirmButtonText: 'Yes, mark completed'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/cafeteria/payments/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: 'completed' })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Success', 'Transaction marked as completed', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Failed to update');
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
    }

    async function reverseTransaction(id) {
        const result = await Swal.fire({
            title: 'Reverse Transaction?',
            text: 'This action cannot be undone. The payment will be reversed and the sale status updated.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Yes, reverse transaction'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/cafeteria/payments/${id}/reverse`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Success', 'Transaction reversed successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Failed to reverse');
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
    }

    function printReceipt(id) {
        window.open(`/cafeteria/payments/${id}/print`, '_blank');
    }

    function exportTransactions() {
        const form = $('#filterForm');
        const params = form.serialize();
        window.location.href = `/cafeteria/payments/export?${params}`;
    }
</script>
@endsection
