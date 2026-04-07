@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Custom Sales Report')
@section('page-title', 'Custom Sales Report')
@section('page-description', 'Generate custom sales reports with flexible filtering options')

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
        Sales
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Custom Report
    </span>
</li>
@endsection

@section('styles')
<style>
    .filter-card {
        transition: all 0.2s;
    }
    .filter-card:hover {
        border-color: #E63946;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Advanced Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Report Filters</h3>
        <form method="GET" action="{{ route('cafeteria.reports.sales.custom') }}" id="customReportForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date"
                           name="start_date"
                           value="{{ request('start_date', $startDate ?? now()->startOfMonth()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input type="date"
                           name="end_date"
                           value="{{ request('end_date', $endDate ?? now()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                           required>
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

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Category</label>
                    <select name="category_id"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Categories</option>
                        @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Methods</option>
                        <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select name="payment_status"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <!-- Sale Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Type</label>
                    <select name="sale_type"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Types</option>
                        <option value="pos" {{ request('sale_type') == 'pos' ? 'selected' : '' }}>POS Sale</option>
                        <option value="online" {{ request('sale_type') == 'online' ? 'selected' : '' }}>Online Order</option>
                        <option value="delivery" {{ request('sale_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                    </select>
                </div>

                <!-- Min Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Amount (KES)</label>
                    <input type="number"
                           name="min_amount"
                           value="{{ request('min_amount') }}"
                           step="0.01"
                           min="0"
                           placeholder="Enter amount"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                @if(request()->anyFilled(['start_date', 'end_date', 'shop_id', 'category_id', 'payment_method', 'payment_status', 'sale_type', 'min_amount']))
                <a href="{{ route('cafeteria.reports.sales.custom') }}"
                   class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg hover:bg-gray-300 transition flex items-center">
                    <i class="fas fa-times mr-2"></i> Clear All
                </a>
                @endif
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
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Revenue</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format($sales->sum('total_amount') ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Transactions</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($sales->total() ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Average Order Value</p>
                    <p class="text-2xl font-bold mt-1">KES {{ number_format(($sales->total() ?? 0) > 0 ? ($sales->sum('total_amount') ?? 0) / ($sales->total() ?? 1) : 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-indigo-500 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Items Sold</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($sales->sum('total_items') ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-chart-bar text-primary mr-2"></i> Sales Results
                </h3>
                <p class="text-sm text-gray-600">
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() ?? 0 }} transactions
                </p>
            </div>
            <div class="flex space-x-2">
                <button onclick="printReport()"
                        class="bg-gray-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-gray-700 transition">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        @if(isset($sales) && $sales->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-primary">{{ $sale->invoice_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>{{ $sale->sale_date->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $sale->customer_name ?? 'Walk-in Customer' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->customer_phone ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format($sale->total_items) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $sale->payment_method === 'mpesa' ? 'bg-green-100 text-green-800' :
                                   ($sale->payment_method === 'cash' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($sale->payment_method ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                   ($sale->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-red-100 text-red-800') }}">
                                {{ ucfirst($sale->payment_status ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-primary">
                            KES {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $sale->cashier->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-bold">
                        <td colspan="6" class="px-6 py-4 text-right text-gray-700">TOTAL:</td>
                        <td class="px-6 py-4 text-primary">KES {{ number_format($sales->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
        @else
        <div class="p-8 text-center">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-chart-line text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-600">No sales data found for selected filters</p>
                <p class="text-sm text-gray-500 mt-1">Try adjusting your date range or filter criteria</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function exportReport() {
        const form = document.getElementById('customReportForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();

        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        params.append('export', 'excel');

        window.location.href = '{{ route("cafeteria.reports.sales.custom") }}?' + params.toString();
    }

    function printReport() {
        window.print();
    }

    // Validate date range
    document.getElementById('customReportForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.querySelector('input[name="start_date"]').value);
        const endDate = new Date(document.querySelector('input[name="end_date"]').value);

        if (startDate > endDate) {
            e.preventDefault();
            alert('Start date cannot be after end date');
        }
    });
</script>
@endsection
