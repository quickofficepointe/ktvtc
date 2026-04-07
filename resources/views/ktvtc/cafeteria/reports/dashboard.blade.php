@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports & Analytics')
@section('page-description', 'View comprehensive reports and analytics for your cafeteria operations')

@section('styles')
<style>
    .report-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px -8px rgba(0, 0, 0, 0.15);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
    .quick-stats-card {
        transition: all 0.2s;
    }
    .quick-stats-card:hover {
        background-color: #f8fafc;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Quick Stats for Current Month -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="quick-stats-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">This Month's Revenue</p>
                    <p class="stat-number text-gray-800">KES {{ number_format($monthlyRevenue ?? 0, 2) }}</p>
                    <p class="text-xs {{ ($revenueTrend ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas fa-arrow-{{ ($revenueTrend ?? 0) >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ number_format(abs($revenueTrend ?? 0), 1) }}% vs last month
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="quick-stats-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Transactions</p>
                    <p class="stat-number text-gray-800">{{ number_format($monthlyTransactions ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($avgDailySales ?? 0) }} avg per day</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="quick-stats-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Items Sold</p>
                    <p class="stat-number text-gray-800">{{ number_format($monthlyItemsSold ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">This month</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-boxes text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="quick-stats-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Gross Profit Margin</p>
                    <p class="stat-number text-gray-800">{{ number_format($profitMargin ?? 0, 1) }}%</p>
                    <p class="text-xs {{ ($marginTrend ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas fa-arrow-{{ ($marginTrend ?? 0) >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ number_format(abs($marginTrend ?? 0), 1) }}% vs last month
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-percentage text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Reports Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-chart-line text-primary mr-2"></i> Sales Reports
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Daily Sales Report -->
                <div class="report-card bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.sales.daily') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-blue-500 flex items-center justify-center mb-3">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Daily Sales Report</h4>
                            <p class="text-sm text-gray-500 mt-1">View daily sales performance with detailed breakdown by payment method and items sold</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Today's Sales:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($todaySales ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Transactions:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($todayTransactions ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Sales Report -->
                <div class="report-card bg-gradient-to-br from-green-50 to-white rounded-xl border border-green-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.sales.monthly') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-green-500 flex items-center justify-center mb-3">
                                <i class="fas fa-chart-bar text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Monthly Sales Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Comprehensive monthly sales analysis with trends and comparisons</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Month-to-Date:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($monthToDateSales ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">vs Last Month:</span>
                            <span class="font-semibold {{ ($monthlyChange ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ ($monthlyChange ?? 0) >= 0 ? '+' : '' }}{{ number_format($monthlyChange ?? 0, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Custom Sales Report -->
                <div class="report-card bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.sales.custom') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-purple-500 flex items-center justify-center mb-3">
                                <i class="fas fa-sliders-h text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Custom Sales Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Generate custom reports with date range and category filters</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500">Select custom date range, shops, categories, and payment methods</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase & Inventory Reports -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-truck text-primary mr-2"></i> Purchase & Inventory Reports
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Purchase Summary Report -->
                <div class="report-card bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.purchase.summary') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-orange-500 flex items-center justify-center mb-3">
                                <i class="fas fa-shopping-cart text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Purchase Summary</h4>
                            <p class="text-sm text-gray-500 mt-1">Track purchase orders, supplier performance, and spending trends</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">This Month's Purchases:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($monthlyPurchases ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Purchase Orders:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($purchaseOrdersCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Supplier Purchase Report -->
                <div class="report-card bg-gradient-to-br from-teal-50 to-white rounded-xl border border-teal-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.purchase.supplier') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-teal-500 flex items-center justify-center mb-3">
                                <i class="fas fa-building text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Supplier Purchase Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Analyze purchases by supplier with detailed breakdowns</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Top Supplier:</span>
                            <span class="font-semibold text-gray-800">{{ $topSupplier ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Active Suppliers:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($activeSuppliers ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock Levels Report -->
                <div class="report-card bg-gradient-to-br from-cyan-50 to-white rounded-xl border border-cyan-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.inventory.stock-levels') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-cyan-500 flex items-center justify-center mb-3">
                                <i class="fas fa-boxes text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Stock Levels Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Monitor current inventory levels, low stock, and out-of-stock items</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Low Stock Items:</span>
                            <span class="font-semibold text-red-600">{{ number_format($lowStockItems ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Out of Stock:</span>
                            <span class="font-semibold text-red-600">{{ number_format($outOfStockItems ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Inventory Movement Report -->
                <div class="report-card bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.inventory.movement') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-indigo-500 flex items-center justify-center mb-3">
                                <i class="fas fa-exchange-alt text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Inventory Movement Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Track stock movements, adjustments, and transfers</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Stock Value:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($totalStockValue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Turnover Rate:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($inventoryTurnover ?? 0, 1) }}x</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-chart-pie text-primary mr-2"></i> Financial Reports
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Profit & Loss Report -->
                <div class="report-card bg-gradient-to-br from-red-50 to-white rounded-xl border border-red-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.financial.profit-loss') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-red-500 flex items-center justify-center mb-3">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Profit & Loss Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Comprehensive P&L statement with revenue, COGS, expenses, and profit analysis</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Net Profit:</span>
                            <span class="font-semibold {{ ($netProfit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                KES {{ number_format($netProfit ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Net Margin:</span>
                            <span class="font-semibold {{ ($netMargin ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($netMargin ?? 0, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Revenue Report -->
                <div class="report-card bg-gradient-to-br from-emerald-50 to-white rounded-xl border border-emerald-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.financial.revenue') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-emerald-500 flex items-center justify-center mb-3">
                                <i class="fas fa-dollar-sign text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Revenue Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Detailed revenue analysis by payment method, category, and time period</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">M-Pesa Revenue:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($mpesaRevenue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Cash Revenue:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($cashRevenue ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Expenses Report -->
                <div class="report-card bg-gradient-to-br from-rose-50 to-white rounded-xl border border-rose-200 p-5" onclick="window.location.href='{{ route('cafeteria.reports.financial.expenses') }}'">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-rose-500 flex items-center justify-center mb-3">
                                <i class="fas fa-credit-card text-white text-xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Expenses Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Track all expenses including purchases, operational costs, and overheads</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Expenses:</span>
                            <span class="font-semibold text-gray-800">KES {{ number_format($totalExpenses ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Expense Ratio:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($expenseRatio ?? 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <button onclick="exportAllReports()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-download mr-2"></i> Export All Reports
            </button>
            <button onclick="scheduleReport()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-clock mr-2"></i> Schedule Reports
            </button>
            <button onclick="printDashboard()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-print mr-2"></i> Print Dashboard
            </button>
        </div>
    </div>
</div>

<!-- Quick Stats Script for AJAX loading -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadQuickStats();
    });

    function loadQuickStats() {
        // Load current month stats
        fetch('/cafeteria/reports/quick-stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateQuickStats(data);
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function updateQuickStats(data) {
        // Update the quick stats cards with real data
        const monthlyRevenue = document.querySelector('.stat-number');
        // You can update the numbers dynamically here
    }

    function exportAllReports() {
        window.location.href = '/cafeteria/reports/export/all?format=excel';
    }

    function scheduleReport() {
        Swal.fire({
            title: 'Schedule Report',
            html: `
                <div class="text-left">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select id="reportType" class="w-full border border-gray-300 rounded-lg p-2">
                            <option value="daily">Daily Sales Report</option>
                            <option value="weekly">Weekly Sales Report</option>
                            <option value="monthly">Monthly Sales Report</option>
                            <option value="profit-loss">Profit & Loss Report</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <select id="frequency" class="w-full border border-gray-300 rounded-lg p-2">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" class="w-full border border-gray-300 rounded-lg p-2" placeholder="reports@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                        <select id="format" class="w-full border border-gray-300 rounded-lg p-2">
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#B91C1C',
            confirmButtonText: 'Schedule Report',
            preConfirm: () => {
                return {
                    reportType: document.getElementById('reportType').value,
                    frequency: document.getElementById('frequency').value,
                    email: document.getElementById('email').value,
                    format: document.getElementById('format').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/cafeteria/reports/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(result.value)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', 'Report scheduled successfully!', 'success');
                    } else {
                        Swal.fire('Error', 'Failed to schedule report', 'error');
                    }
                });
            }
        });
    }

    function printDashboard() {
        window.print();
    }
</script>
@endsection
