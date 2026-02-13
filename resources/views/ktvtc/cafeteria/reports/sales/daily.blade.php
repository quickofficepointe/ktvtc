@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Daily Sales Report')
@section('page-title', 'Daily Sales Report')
@section('page-description', 'View sales transactions for a specific day')

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
        Daily Sales
    </span>
</li>
@endsection

@section('content')
<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('cafeteria.reports.sales.daily') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date"
                       name="date"
                       value="{{ $date }}"
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
    <!-- Total Sales -->
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Total Sales</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($totalSales, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>
        @if($salesChange != 0)
        <div class="mt-2 text-sm">
            <span class="{{ $salesChange > 0 ? 'text-green-300' : 'text-red-300' }}">
                <i class="fas fa-arrow-{{ $salesChange > 0 ? 'up' : 'down' }} mr-1"></i>
                {{ abs($salesChange) }}% {{ $salesChange > 0 ? 'increase' : 'decrease' }} from previous day
            </span>
        </div>
        @endif
    </div>

    <!-- Transactions -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Transactions</p>
                <p class="text-2xl font-bold mt-1">{{ $totalTransactions }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ $totalItems }} total items sold
        </div>
    </div>

    <!-- Average Sale -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Average Sale</p>
                <p class="text-2xl font-bold mt-1">KES {{ number_format($totalTransactions > 0 ? $totalSales / $totalTransactions : 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            Per transaction average
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-80">Pending Orders</p>
                <p class="text-2xl font-bold mt-1">{{ $sales->where('order_status', 'pending')->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm opacity-80">
            Awaiting completion
        </div>
    </div>
</div>

<!-- Payment Method Breakdown -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Payment Method Breakdown</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Payment Method
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Transactions
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Percentage
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($paymentBreakdown as $method => $data)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ ucfirst($method) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $data['count'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary">
                        KES {{ number_format($data['amount'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $totalSales > 0 ? round(($data['amount'] / $totalSales) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Sales List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-semibold text-gray-800">Sales Transactions - {{ $date }}</h3>
        <div class="text-sm text-gray-500">
            {{ $sales->count() }} transactions found
        </div>
    </div>

    @if($sales->isEmpty())
    <div class="p-8 text-center">
        <i class="fas fa-chart-bar text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-600">No sales found for {{ $date }}</p>
        <p class="text-sm text-gray-500 mt-1">Try selecting a different date</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Invoice
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Customer
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Time
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Items
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Payment
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Amount
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($sales as $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-primary">{{ $sale->invoice_number }}</div>
                        <div class="text-xs text-gray-500">by {{ $sale->cashier->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $sale->customer_name }}</div>
                        <div class="text-sm text-gray-500">{{ $sale->customer_phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>{{ $sale->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>{{ $sale->total_items }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $sale->payment_method === 'mpesa' ? 'bg-green-100 text-green-800' :
                               ($sale->payment_method === 'cash' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                               ($sale->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-red-100 text-red-800') }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-primary">
                        KES {{ number_format($sale->total_amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-right font-bold text-gray-700">
                        TOTAL:
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-primary">
                        KES {{ number_format($totalSales, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
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
<script>
    function exportReport() {
        const date = '{{ $date }}';
        const shopId = '{{ request("shop_id") }}';

        let url = '{{ route("cafeteria.reports.sales.daily") }}?export=excel&date=' + date;
        if (shopId) url += '&shop_id=' + shopId;

        window.open(url, '_blank');
    }

    // Update date input max to today
    $(document).ready(function() {
        const today = new Date().toISOString().split('T')[0];
        $('input[name="date"]').attr('max', today);
    });
</script>
@endsection
