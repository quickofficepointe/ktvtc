@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Direct Purchases')
@section('page-title', 'Direct Purchase Management')
@section('page-description', 'Record and manage direct purchases')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Procurement
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        Direct Purchases
    </span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-primary bg-opacity-10 flex items-center justify-center mr-4">
                    <i class="fas fa-shopping-cart text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Purchases</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPurchases }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                    <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Today's Purchases</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $todayPurchases }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending Payments</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingPayments }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cash Purchases</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $cashPurchases }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Purchases -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">Today's Purchases</h3>
                <p class="text-sm text-gray-600 mt-1">Purchases made today: {{ $todayPurchasesList->count() }}</p>
            </div>
            <button onclick="openPurchaseModal()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> New Direct Purchase
            </button>
        </div>

        @if($todayPurchasesList->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($todayPurchasesList as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $purchase->invoice_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $purchase->supplier_name ?? ($purchase->supplier->supplier_name ?? 'Cash Purchase') }}</div>
                            <div class="text-sm text-gray-500">{{ $purchase->businessSection->section_name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $purchase->total_items }} items</div>
                            <div class="text-sm text-gray-500">{{ $purchase->total_quantity }} units</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-lg text-primary">
                                KES {{ number_format($purchase->total_amount, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentMethodColors[$purchase->payment_method] }}">
                                    {{ ucfirst(str_replace('_', ' ', $purchase->payment_method)) }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentStatusColors[$purchase->payment_status] }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewPurchase({{ $purchase->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="printPurchase({{ $purchase->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-600">No direct purchases today</p>
            <button onclick="openPurchaseModal()" class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center mx-auto">
                <i class="fas fa-plus mr-2"></i> Create First Purchase
            </button>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="searchInput"
                           placeholder="Search invoice or supplier..."
                           value="{{ request('search') }}"
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>

                <!-- Payment Status Filter -->
                <select id="paymentStatusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Payment Status</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>

                <!-- Payment Method Filter -->
                <select id="paymentMethodFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Payment Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>

                <!-- Date Range -->
                <div class="flex gap-2">
                    <input type="date"
                           id="fromDate"
                           value="{{ request('from_date') }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                    <input type="date"
                           id="toDate"
                           value="{{ request('to_date') }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Export Button -->
                <button onclick="exportPurchases()" class="btn-secondary py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export
                </button>

                <!-- Filter Button -->
                <button onclick="applyFilters()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <nav class="flex overflow-x-auto">
            <button onclick="filterByStatus('')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ !request('payment_status') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Purchases
                <span class="ml-2 bg-gray-100 text-gray-900 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $totalPurchases }}</span>
            </button>
            <button onclick="filterByStatus('pending')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('payment_status') == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending Payment
                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $pendingPayments }}</span>
            </button>
            <button onclick="filterByStatus('paid')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('payment_status') == 'paid' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Paid
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $directPurchases->where('payment_status', 'paid')->count() }}</span>
            </button>
            <button onclick="filterByMethod('cash')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('payment_method') == 'cash' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Cash
                <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $cashPurchases }}</span>
            </button>
            <button onclick="filterByMethod('mpesa')"
                    class="flex-shrink-0 py-4 px-6 border-b-2 font-medium text-sm {{ request('payment_method') == 'mpesa' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                M-Pesa
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $directPurchases->where('payment_method', 'mpesa')->count() }}</span>
            </button>
        </nav>
    </div>

    <!-- Direct Purchases Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Invoice #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Business Section
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Purchase Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Items
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($directPurchases as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $purchase->invoice_number }}</div>
                            <div class="text-sm text-gray-500">{{ $purchase->shop->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $purchase->supplier_name ?? ($purchase->supplier->supplier_name ?? 'Cash Purchase') }}</div>
                            @if($purchase->supplier_phone)
                            <div class="text-sm text-gray-500">{{ $purchase->supplier_phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $purchase->businessSection->section_name ?? '' }}</div>
                            <div class="text-xs text-gray-500">{{ $purchase->businessSection->section_code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $purchase->purchase_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $purchase->total_items }} items</div>
                            <div class="text-sm text-gray-500">{{ $purchase->total_quantity }} units</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-lg text-primary">
                                KES {{ number_format($purchase->total_amount, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentMethodColors[$purchase->payment_method] }}">
                                    {{ ucfirst(str_replace('_', ' ', $purchase->payment_method)) }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentStatusColors[$purchase->payment_status] }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                                @if($purchase->payment_date)
                                <div class="text-xs text-gray-500">
                                    {{ $purchase->payment_date->format('M d, Y') }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <button onclick="viewPurchase({{ $purchase->id }})"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Edit Button -->
                                <button onclick="editPurchase({{ $purchase->id }})"
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Payment Button -->
                                <button onclick="openPaymentModal({{ $purchase->id }})"
                                        class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50"
                                        title="Update Payment">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>

                                <!-- Approve Button -->
                                @if(!$purchase->approved_by)
                                <button onclick="approvePurchase({{ $purchase->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                @endif

                                <!-- Print Button -->
                                <button onclick="printPurchase({{ $purchase->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50"
                                        title="Print">
                                    <i class="fas fa-print"></i>
                                </button>

                                <!-- Delete Button -->
                                <button onclick="confirmDeletePurchase({{ $purchase->id }})"
                                        class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-700">No direct purchases found</p>
                                <p class="text-gray-500 mt-1">Start by creating your first direct purchase</p>
                                <button onclick="openPurchaseModal()"
                                        class="mt-4 btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Create Purchase
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($directPurchases->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $directPurchases->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create/Edit Purchase Modal -->
<div id="purchaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="purchaseModalTitle" class="text-xl font-bold text-gray-900">Create Direct Purchase</h3>
                <button onclick="closePurchaseModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="purchaseForm" class="p-6">
            @csrf
            <input type="hidden" id="purchase_id" name="id">

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supplier Selection -->
                        <div class="md:col-span-2">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex items-center">
                                    <input type="radio" id="existingSupplier" name="supplier_type" value="existing" checked onclick="toggleSupplierType('existing')" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                    <label for="existingSupplier" class="ml-2 text-sm font-medium text-gray-700">Existing Supplier</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="newSupplier" name="supplier_type" value="new" onclick="toggleSupplierType('new')" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                    <label for="newSupplier" class="ml-2 text-sm font-medium text-gray-700">New/Cash Supplier</label>
                                </div>
                            </div>

                            <!-- Existing Supplier -->
                            <div id="existingSupplierSection">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Supplier
                                </label>
                                <select id="supplier_id" name="supplier_id"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})</option>
                                    @endforeach
                                </select>
                                <div id="supplier_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>

                            <!-- New Supplier -->
                            <div id="newSupplierSection" class="hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Supplier Name *
                                        </label>
                                        <input type="text"
                                               id="supplier_name"
                                               name="supplier_name"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                        <div id="supplier_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                                    </div>

                                    <div>
                                        <label for="supplier_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            Supplier Phone
                                        </label>
                                        <input type="text"
                                               id="supplier_phone"
                                               name="supplier_phone"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Section -->
                        <div>
                            <label for="business_section_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Business Section *
                            </label>
                            <select id="business_section_id" name="business_section_id" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Business Section</option>
                                @foreach($businessSections as $section)
                                <option value="{{ $section->id }}">
                                    {{ $section->section_name }} ({{ $section->section_code }})
                                </option>
                                @endforeach
                            </select>
                            <div id="business_section_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Shop -->
                        <div>
                            <label for="shop_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Shop *
                            </label>
                            <select id="shop_id" name="shop_id" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Shop</option>
                                @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">
                                    {{ $shop->name ?? 'Shop #' . $shop->id }}
                                </option>
                                @endforeach
                            </select>
                            <div id="shop_id_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Purchase Date -->
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Purchase Date *
                            </label>
                            <input type="date"
                                   id="purchase_date"
                                   name="purchase_date"
                                   required
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                            <div id="purchase_date_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Delivery Details -->
                        <div class="md:col-span-2">
                            <label for="delivery_details" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Details
                            </label>
                            <textarea id="delivery_details"
                                      name="delivery_details"
                                      rows="2"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Purchase Items</h4>
                        <button type="button" onclick="addItemRow()" class="btn-primary text-white py-2 px-4 rounded-lg flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Items will be added here dynamically -->
                                <tr id="noItemsRow">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-2"></i>
                                        <p class="text-gray-600">No items added yet</p>
                                        <button type="button" onclick="addItemRow()" class="mt-2 text-primary hover:text-primary-dark font-medium">
                                            Click here to add your first item
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="itemsTableFooter" class="bg-gray-50 hidden">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Subtotal:
                                    </td>
                                    <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-900" id="subtotalAmount">
                                        KES 0.00
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Tax:
                                    </td>
                                    <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-900" id="taxAmount">
                                        KES 0.00
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                                        Total Amount:
                                    </td>
                                    <td colspan="4" class="px-4 py-3 text-lg font-bold text-primary" id="totalAmount">
                                        KES 0.00
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Method *
                            </label>
                            <select id="payment_method" name="payment_method" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit</option>
                            </select>
                            <div id="payment_method_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Status *
                            </label>
                            <select id="payment_status" name="payment_status" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            <div id="payment_status_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Date
                            </label>
                            <input type="date"
                                   id="payment_date"
                                   name="payment_date"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>

                        <div class="md:col-span-3">
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Reference
                            </label>
                            <input type="text"
                                   id="payment_reference"
                                   name="payment_reference"
                                   placeholder="e.g., MPESA Code, Bank Ref, Invoice #"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"></textarea>
                    </div>
                </div>
            </div>
        </form>

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6">
            <div class="flex justify-end space-x-3">
                <button type="button"
                        onclick="closePurchaseModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="savePurchase()"
                        id="savePurchaseBtn"
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                    Save Purchase
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Purchase Modal -->
<div id="viewPurchaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h3 id="viewPurchaseModalTitle" class="text-xl font-bold text-gray-900">Direct Purchase Details</h3>
                <div class="flex items-center space-x-3">
                    <button onclick="printCurrentPurchase()" class="btn-secondary py-2 px-4 rounded-lg flex items-center text-sm">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <button onclick="closeViewPurchaseModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6" id="purchaseDetailsContent">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Payment Update Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 id="paymentModalTitle" class="text-lg font-bold text-gray-900 mb-4">Update Payment</h3>

            <form id="paymentForm">
                @csrf
                <input type="hidden" id="payment_purchase_id">

                <div class="space-y-4">
                    <div>
                        <label for="update_payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Method
                        </label>
                        <select id="update_payment_method" name="payment_method"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>

                    <div>
                        <label for="update_payment_status" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Status *
                        </label>
                        <select id="update_payment_status" name="payment_status" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label for="update_payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Date
                        </label>
                        <input type="date"
                               id="update_payment_date"
                               name="payment_date"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="update_payment_reference" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Reference
                        </label>
                        <input type="text"
                               id="update_payment_reference"
                               name="payment_reference"
                               placeholder="e.g., MPESA Code, Bank Ref"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                            onclick="closePaymentModal()"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            onclick="updatePayment()"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
                        Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 text-center">Delete Purchase</h3>
            <p id="deleteMessage" class="mt-2 text-gray-600 text-center">
                Are you sure you want to delete this purchase?
            </p>

            <div class="mt-6 flex justify-center space-x-3">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="deletePurchase()"
                        id="confirmDeleteBtn"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Delete Purchase
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg border p-4 min-w-[300px] transform transition-all duration-300 translate-x-full">
        <div class="flex items-start">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="ml-3 w-0 flex-1">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
                <p id="toastDescription" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button onclick="hideToast()" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// State
let currentPurchaseId = null;
let products = @json($products->toArray());
let itemCounter = 0;

// DOM Elements
const purchaseModal = document.getElementById('purchaseModal');
const viewPurchaseModal = document.getElementById('viewPurchaseModal');
const paymentModal = document.getElementById('paymentModal');
const deleteModal = document.getElementById('deleteModal');
const purchaseForm = document.getElementById('purchaseForm');
const toast = document.getElementById('toast');

// ======================
// FILTERS & NAVIGATION
// ======================

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const paymentStatus = document.getElementById('paymentStatusFilter').value;
    const paymentMethod = document.getElementById('paymentMethodFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const url = new URL(window.location.href);

    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');

    if (paymentStatus) url.searchParams.set('payment_status', paymentStatus);
    else url.searchParams.delete('payment_status');

    if (paymentMethod) url.searchParams.set('payment_method', paymentMethod);
    else url.searchParams.delete('payment_method');

    if (fromDate) url.searchParams.set('from_date', fromDate);
    else url.searchParams.delete('from_date');

    if (toDate) url.searchParams.set('to_date', toDate);
    else url.searchParams.delete('to_date');

    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterByStatus(status) {
    const url = new URL(window.location.href);

    if (status) {
        url.searchParams.set('payment_status', status);
    } else {
        url.searchParams.delete('payment_status');
    }

    url.searchParams.delete('payment_method');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterByMethod(method) {
    const url = new URL(window.location.href);

    if (method) {
        url.searchParams.set('payment_method', method);
    } else {
        url.searchParams.delete('payment_method');
    }

    url.searchParams.delete('payment_status');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Event Listeners
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') applyFilters();
});

// ======================
// SUPPLIER TYPE TOGGLE
// ======================

function toggleSupplierType(type) {
    const existingSection = document.getElementById('existingSupplierSection');
    const newSection = document.getElementById('newSupplierSection');
    const supplierId = document.getElementById('supplier_id');
    const supplierName = document.getElementById('supplier_name');

    if (type === 'existing') {
        existingSection.classList.remove('hidden');
        newSection.classList.add('hidden');
        supplierId.disabled = false;
        supplierName.disabled = true;
        supplierName.removeAttribute('required');
        supplierId.setAttribute('required', 'required');
    } else {
        existingSection.classList.add('hidden');
        newSection.classList.remove('hidden');
        supplierId.disabled = true;
        supplierName.disabled = false;
        supplierId.removeAttribute('required');
        supplierName.setAttribute('required', 'required');
    }
}

// ======================
// ITEMS MANAGEMENT
// ======================

function getProductOptions() {
    let options = '<option value="">Select Product</option>';
    products.forEach(product => {
        options += `<option value="${product.id}" data-unit="${product.unit}">${product.product_name} (${product.product_code})</option>`;
    });
    return options;
}

function addItemRow(product = null, quantity = '', price = '', batch = '', expiry = '') {
    const tbody = document.getElementById('itemsTableBody');
    const noItemsRow = document.getElementById('noItemsRow');
    const footer = document.getElementById('itemsTableFooter');

    // Hide "no items" message
    if (noItemsRow) {
        noItemsRow.style.display = 'none';
    }

    // Show footer
    footer.classList.remove('hidden');

    // Add new row
    const rowId = 'item_' + itemCounter++;
    const row = document.createElement('tr');
    row.id = rowId;
    row.className = 'item-row';
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="items[${rowId}][product_id]"
                    class="product-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
                ${getProductOptions()}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][quantity]"
                   min="0.001"
                   step="0.001"
                   value="${quantity}"
                   oninput="calculateItemTotal(this)"
                   class="quantity-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
            <span class="item-unit text-xs text-gray-500 mt-1 block"></span>
        </td>
        <td class="px-4 py-3">
            <input type="number"
                   name="items[${rowId}][unit_price]"
                   min="0"
                   step="0.01"
                   value="${price}"
                   oninput="calculateItemTotal(this)"
                   class="price-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="text"
                   name="items[${rowId}][batch_number]"
                   value="${batch}"
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <input type="date"
                   name="items[${rowId}][expiry_date]"
                   value="${expiry}"
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
        </td>
        <td class="px-4 py-3">
            <div class="item-total font-medium text-gray-900">KES 0.00</div>
        </td>
        <td class="px-4 py-3">
            <button type="button"
                    onclick="removeItemRow('${rowId}')"
                    class="text-red-600 hover:text-red-900 p-1">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);

    // Set product if provided
    if (product) {
        row.querySelector('.product-select').value = product;
        updateItemUnit(row.querySelector('.product-select'));
    }

    // Add event listener for product change
    row.querySelector('.product-select').addEventListener('change', function() {
        updateItemUnit(this);
    });

    // Calculate initial total
    calculateItemTotal(row.querySelector('.quantity-input'));

    return row;
}

function removeItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calculateTotals();

        // Show "no items" message if all rows removed
        const itemRows = document.querySelectorAll('.item-row');
        if (itemRows.length === 0) {
            document.getElementById('noItemsRow').style.display = '';
            document.getElementById('itemsTableFooter').classList.add('hidden');
        }
    }
}

function updateItemUnit(select) {
    const row = select.closest('tr');
    const unitSpan = row.querySelector('.item-unit');
    const productId = select.value;

    if (productId) {
        const product = products.find(p => p.id == productId);
        if (product) {
            unitSpan.textContent = product.unit;
        }
    } else {
        unitSpan.textContent = '';
    }
}

function calculateItemTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;

    row.querySelector('.item-total').textContent = 'KES ' + total.toFixed(2);

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let taxAmount = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;

        subtotal += quantity * price;
    });

    // Note: Tax calculation can be added here if needed
    const total = subtotal + taxAmount;

    document.getElementById('subtotalAmount').textContent = 'KES ' + subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = 'KES ' + taxAmount.toFixed(2);
    document.getElementById('totalAmount').textContent = 'KES ' + total.toFixed(2);
}

function getItemsData() {
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        const productId = row.querySelector('.product-select').value;
        const quantity = row.querySelector('.quantity-input').value;
        const price = row.querySelector('.price-input').value;
        const batch = row.querySelector('input[name*="batch_number"]').value;
        const expiry = row.querySelector('input[name*="expiry_date"]').value;

        if (productId && quantity && price) {
            items.push({
                product_id: productId,
                quantity: quantity,
                unit_price: price,
                batch_number: batch || null,
                expiry_date: expiry || null
            });
        }
    });
    return items;
}

// ======================
// PURCHASE MODAL FUNCTIONS
// ======================

function openPurchaseModal(id = null) {
    resetPurchaseForm();
    clearErrors();
    toggleSupplierType('existing'); // Reset to existing supplier

    if (id) {
        // Edit mode
        document.getElementById('purchaseModalTitle').textContent = 'Edit Direct Purchase';
        document.getElementById('savePurchaseBtn').textContent = 'Update Purchase';
        currentPurchaseId = id;

        // Load purchase data
        fetch(`/direct-purchases/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(purchase => {
            populatePurchaseForm(purchase);
            purchaseModal.classList.remove('hidden');
        })
        .catch(error => {
                      console.error('Error:', error);
            showToast('Error', 'Failed to load purchase data', 'error');
        });
    } else {
        // Create mode
        document.getElementById('purchaseModalTitle').textContent = 'Create Direct Purchase';
        document.getElementById('savePurchaseBtn').textContent = 'Save Purchase';
        currentPurchaseId = null;

        // Add first item row
        addItemRow();
        purchaseModal.classList.remove('hidden');
    }
}

function closePurchaseModal() {
    purchaseModal.classList.add('hidden');
    resetPurchaseForm();
}

function resetPurchaseForm() {
    purchaseForm.reset();
    document.getElementById('purchase_id').value = '';
    document.getElementById('supplier_id').value = '';
    document.getElementById('purchase_date').value = new Date().toISOString().split('T')[0];

    // Clear items table
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    document.getElementById('noItemsRow').style.display = '';
    document.getElementById('itemsTableFooter').classList.add('hidden');

    itemCounter = 0;
}

function populatePurchaseForm(purchase) {
    // Set basic information
    document.getElementById('purchase_id').value = purchase.id;
    document.getElementById('business_section_id').value = purchase.business_section_id;
    document.getElementById('shop_id').value = purchase.shop_id;
    document.getElementById('purchase_date').value = purchase.purchase_date.split('T')[0];
    document.getElementById('delivery_details').value = purchase.delivery_details || '';

    // Set supplier information
    if (purchase.supplier_id) {
        document.getElementById('existingSupplier').checked = true;
        toggleSupplierType('existing');
        document.getElementById('supplier_id').value = purchase.supplier_id;
    } else {
        document.getElementById('newSupplier').checked = true;
        toggleSupplierType('new');
        document.getElementById('supplier_name').value = purchase.supplier_name || '';
        document.getElementById('supplier_phone').value = purchase.supplier_phone || '';
    }

    // Set payment information
    document.getElementById('payment_method').value = purchase.payment_method;
    document.getElementById('payment_status').value = purchase.payment_status;
    document.getElementById('payment_date').value = purchase.payment_date ? purchase.payment_date.split('T')[0] : '';
    document.getElementById('payment_reference').value = purchase.payment_reference || '';
    document.getElementById('notes').value = purchase.notes || '';

    // Clear existing items and add new ones
    document.querySelectorAll('.item-row').forEach(row => row.remove());
    itemCounter = 0;

    // Add items from the purchase
    if (purchase.items) {
        const items = JSON.parse(purchase.items);
        items.forEach(item => {
            addItemRow(
                item.product_id,
                item.quantity,
                item.unit_price,
                item.batch_number || '',
                item.expiry_date ? item.expiry_date.split('T')[0] : ''
            );
        });
    }

    // Calculate totals
    calculateTotals();
}

function clearErrors() {
    document.querySelectorAll('[id$="_error"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
}

function showFieldErrors(errors) {
    clearErrors();

    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + '_error');
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
    });
}

// ======================
// SAVE/UPDATE PURCHASE
// ======================

function savePurchase() {
    const formData = new FormData(purchaseForm);
    const items = getItemsData();

    if (items.length === 0) {
        showToast('Error', 'Please add at least one item', 'error');
        return;
    }

    // Prepare data
    const data = {
        _token: csrfToken,
        items: items
    };

    // Add form data
    formData.forEach((value, key) => {
        if (key !== 'items' && value !== '') {
            data[key] = value;
        }
    });

    // Handle supplier fields based on type
    if (document.getElementById('existingSupplier').checked) {
        delete data.supplier_name;
        delete data.supplier_phone;
    } else {
        delete data.supplier_id;
    }

    // Determine URL and method
    const url = currentPurchaseId
        ? `/direct-purchases/${currentPurchaseId}`
        : '/direct-purchases';

    const method = currentPurchaseId ? 'PUT' : 'POST';

    // Show loading state
    const saveBtn = document.getElementById('savePurchaseBtn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    // Send request
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Success', result.message, 'success');
            closePurchaseModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            if (result.errors) {
                showFieldErrors(result.errors);
            }
            showToast('Error', result.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to save purchase', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

// ======================
// VIEW PURCHASE FUNCTIONS
// ======================

function viewPurchase(id) {
    currentPurchaseId = id;

    fetch(`/direct-purchases/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(purchase => {
        displayPurchaseDetails(purchase);
        viewPurchaseModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to load purchase details', 'error');
    });
}

function closeViewPurchaseModal() {
    viewPurchaseModal.classList.add('hidden');
    currentPurchaseId = null;
}

function displayPurchaseDetails(purchase) {
    const content = document.getElementById('purchaseDetailsContent');

    // Format items
    let itemsHtml = '';
    if (purchase.items) {
        const items = JSON.parse(purchase.items);
        items.forEach((item, index) => {
            itemsHtml += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">${item.product_name}</div>
                        <div class="text-xs text-gray-500">${item.product_code}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">${item.quantity} ${item.unit}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">KES ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${item.batch_number || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${item.expiry_date ? new Date(item.expiry_date).toLocaleDateString() : '-'}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">KES ${parseFloat(item.total_price).toFixed(2)}</td>
                </tr>
            `;
        });
    }

    // Format dates
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    // Set modal title
    document.getElementById('viewPurchaseModalTitle').textContent = `Direct Purchase: ${purchase.invoice_number}`;

    content.innerHTML = `
        <!-- Header Info -->
        <div class="bg-gray-50 rounded-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Invoice Number</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">${purchase.invoice_number}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Purchase Date</h4>
                    <p class="mt-1 text-lg font-semibold text-gray-900">${formatDate(purchase.purchase_date)}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Amount</h4>
                    <p class="mt-1 text-2xl font-bold text-primary">KES ${parseFloat(purchase.total_amount).toFixed(2)}</p>
                </div>
            </div>
        </div>

        <!-- Supplier & Business Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Supplier Info -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Supplier:</span>
                        <p class="font-medium text-gray-900">${purchase.supplier_name || (purchase.supplier?.supplier_name || 'Cash Purchase')}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Phone:</span>
                        <p class="font-medium text-gray-900">${purchase.supplier_phone || '-'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Delivery Details:</span>
                        <p class="font-medium text-gray-900">${purchase.delivery_details || '-'}</p>
                    </div>
                </div>
            </div>

            <!-- Business Info -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Business Information</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Business Section:</span>
                        <p class="font-medium text-gray-900">${purchase.business_section?.section_name || '-'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Shop:</span>
                        <p class="font-medium text-gray-900">${purchase.shop?.name || '-'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Purchased By:</span>
                        <p class="font-medium text-gray-900">${purchase.purchaser?.name || '-'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Received By:</span>
                        <p class="font-medium text-gray-900">${purchase.receiver?.name || '-'}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${itemsHtml}
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">KES ${parseFloat(purchase.subtotal).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Tax:</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">KES ${parseFloat(purchase.tax_amount).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total:</td>
                                <td class="px-4 py-3 text-lg font-bold text-primary">KES ${parseFloat(purchase.total_amount).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <span class="text-sm text-gray-500">Payment Method:</span>
                    <p class="font-medium text-gray-900">${purchase.payment_method ? purchase.payment_method.replace('_', ' ').toUpperCase() : '-'}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Payment Status:</span>
                    <p class="font-medium text-gray-900">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColorClass(purchase.payment_status, 'paymentStatusColors')}">
                            ${purchase.payment_status ? purchase.payment_status.toUpperCase() : '-'}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Payment Date:</span>
                    <p class="font-medium text-gray-900">${formatDate(purchase.payment_date)}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Reference:</span>
                    <p class="font-medium text-gray-900">${purchase.payment_reference || '-'}</p>
                </div>
            </div>
        </div>

        <!-- Notes & Approval -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Notes -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Notes</h4>
                <p class="text-gray-700">${purchase.notes || 'No notes provided'}</p>
            </div>

            <!-- Approval Info -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Approval Information</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Approved By:</span>
                        <p class="font-medium text-gray-900">${purchase.approver?.name || 'Not Approved'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Approved At:</span>
                        <p class="font-medium text-gray-900">${formatDate(purchase.approved_at)}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Created By:</span>
                        <p class="font-medium text-gray-900">${purchase.creator?.name || '-'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Created At:</span>
                        <p class="font-medium text-gray-900">${formatDate(purchase.created_at)}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getStatusColorClass(status, type) {
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'partial': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800'
    };

    const methodColors = {
        'cash': 'bg-green-100 text-green-800',
        'mpesa': 'bg-blue-100 text-blue-800',
        'bank_transfer': 'bg-purple-100 text-purple-800',
        'credit': 'bg-orange-100 text-orange-800'
    };

    const colors = type === 'paymentStatusColors' ? statusColors : methodColors;
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function printCurrentPurchase() {
    if (!currentPurchaseId) return;

    const printWindow = window.open(`/direct-purchases/${currentPurchaseId}/print`, '_blank');
    if (printWindow) {
        printWindow.focus();
    }
}

// ======================
// PAYMENT MODAL FUNCTIONS
// ======================

function openPaymentModal(id) {
    currentPurchaseId = id;

    // Load current payment details
    fetch(`/direct-purchases/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(purchase => {
        document.getElementById('payment_purchase_id').value = purchase.id;
        document.getElementById('update_payment_method').value = purchase.payment_method;
        document.getElementById('update_payment_status').value = purchase.payment_status;
        document.getElementById('update_payment_date').value = purchase.payment_date ? purchase.payment_date.split('T')[0] : '';
        document.getElementById('update_payment_reference').value = purchase.payment_reference || '';

        document.getElementById('paymentModalTitle').textContent = `Update Payment: ${purchase.invoice_number}`;
        paymentModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to load payment details', 'error');
    });
}

function closePaymentModal() {
    paymentModal.classList.add('hidden');
    document.getElementById('paymentForm').reset();
}

function updatePayment() {
    const formData = new FormData(document.getElementById('paymentForm'));
    const data = {
        _token: csrfToken
    };

    formData.forEach((value, key) => {
        if (value !== '') {
            data[key] = value;
        }
    });

    fetch(`/direct-purchases/${currentPurchaseId}/payment`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Success', result.message, 'success');
            closePaymentModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', result.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to update payment', 'error');
    });
}

// ======================
// DELETE FUNCTIONS
// ======================

function confirmDeletePurchase(id) {
    currentPurchaseId = id;

    // Load purchase info for confirmation message
    fetch(`/direct-purchases/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(purchase => {
        document.getElementById('deleteMessage').textContent =
            `Are you sure you want to delete purchase ${purchase.invoice_number}? This action cannot be undone.`;
        deleteModal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to load purchase details', 'error');
    });
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    currentPurchaseId = null;
}

function deletePurchase() {
    if (!currentPurchaseId) return;

    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.textContent;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';

    fetch(`/direct-purchases/${currentPurchaseId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Success', result.message, 'success');
            closeDeleteModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', result.message || 'An error occurred', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to delete purchase', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = originalText;
    });
}

// ======================
// OTHER FUNCTIONS
// ======================

function editPurchase(id) {
    openPurchaseModal(id);
}

function approvePurchase(id) {
    if (!confirm('Are you sure you want to approve this purchase?')) return;

    fetch(`/direct-purchases/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Success', result.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', result.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to approve purchase', 'error');
    });
}

function printPurchase(id) {
    const printWindow = window.open(`/direct-purchases/${id}/print`, '_blank');
    if (printWindow) {
        printWindow.focus();
    }
}

function exportPurchases() {
    // Build export URL with current filters
    const url = new URL(window.location.href);
    url.searchParams.set('export', 'csv');

    window.open(url.toString(), '_blank');
}

// ======================
// TOAST NOTIFICATION
// ======================

function showToast(title, message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    const toastDescription = document.getElementById('toastDescription');

    // Set icon based on type
    let iconClass, bgColor;
    switch(type) {
        case 'success':
            iconClass = 'fas fa-check-circle text-green-500 text-lg';
            bgColor = 'bg-green-50 border-green-200';
            break;
        case 'error':
            iconClass = 'fas fa-exclamation-circle text-red-500 text-lg';
            bgColor = 'bg-red-50 border-red-200';
            break;
        case 'warning':
            iconClass = 'fas fa-exclamation-triangle text-yellow-500 text-lg';
            bgColor = 'bg-yellow-50 border-yellow-200';
            break;
        default:
            iconClass = 'fas fa-info-circle text-blue-500 text-lg';
            bgColor = 'bg-blue-50 border-blue-200';
    }

    toastIcon.className = iconClass;
    toast.className = `fixed top-4 right-4 z-50 transform transition-all duration-300 translate-x-0 ${bgColor} border rounded-lg shadow-lg p-4 min-w-[300px]`;
    toastMessage.textContent = title;
    toastDescription.textContent = message;
    toast.classList.remove('hidden');

    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('translate-x-full');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 300);
}

// ======================
// INITIALIZATION
// ======================

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target === purchaseModal) {
        closePurchaseModal();
    }
    if (event.target === viewPurchaseModal) {
        closeViewPurchaseModal();
    }
    if (event.target === paymentModal) {
        closePaymentModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePurchaseModal();
        closeViewPurchaseModal();
        closePaymentModal();
        closeDeleteModal();
    }
});

// Initialize date inputs to current date if empty
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];

    if (!document.getElementById('fromDate').value) {
        document.getElementById('fromDate').value = today;
    }

    if (!document.getElementById('toDate').value) {
        document.getElementById('toDate').value = today;
    }

    // Set min dates for date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.hasAttribute('max')) {
            input.setAttribute('max', today);
        }
    });
});
</script>
@endsection
