@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'POS Terminal')
@section('page-title', 'Point of Sale')
@section('page-description', 'Process sales quickly and efficiently')

@section('breadcrumbs')
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-gray-700">
        Sales
    </span>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="inline-flex items-center text-sm font-medium text-primary">
        POS Terminal
    </span>
</li>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
    <!-- Left Column - Products (3 columns) -->
    <div class="xl:col-span-3">
        <!-- Search and Filters -->
        <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search Bar -->
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    <input type="text"
                           id="productSearch"
                           placeholder="Search products by name, code, or barcode..."
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"
                           autocomplete="off">
                </div>

                <!-- Quick Filters -->
                <div class="flex gap-2">
                    <select id="categoryFilter"
                            class="border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>

                    <button onclick="toggleStockFilter()"
                            id="stockFilterBtn"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-filter mr-2"></i>In Stock
                    </button>
                </div>
            </div>

            <!-- Quick Category Chips -->
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($categories->take(8) as $category)
                <button onclick="filterByCategory({{ $category->id }})"
                        class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition category-chip">
                    {{ $category->category_name }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Products Header -->
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Products</h3>
                <div class="text-sm text-gray-500">
                    <span id="productsCount">0</span> products found
                </div>
            </div>

            <!-- Products Container -->
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="productsGrid">
                    <!-- Products loaded via AJAX -->
                </div>

                <!-- Loading/Empty States -->
                <div id="productsLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-3"></div>
                    <p class="text-gray-600">Loading products...</p>
                </div>

                <div id="productsEmpty" class="hidden text-center py-12">
                    <i class="fas fa-search text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-600">No products found</p>
                    <p class="text-sm text-gray-500 mt-1">Try a different search term</p>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-center" id="productsPagination"></div>
            </div>
        </div>
    </div>

    <!-- Right Column - Cart & Payment (1 column) -->
    <div class="xl:col-span-1">
        <!-- Cart Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Current Sale</h3>
                <button onclick="clearCart()"
                        class="text-sm text-red-600 hover:text-red-800 flex items-center">
                    <i class="fas fa-trash-alt mr-1"></i> Clear
                </button>
            </div>

            <!-- Cart Items -->
            <div class="max-h-64 overflow-y-auto mb-4" id="cartContainer">
                <div id="emptyCart" class="text-center py-6">
                    <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-2"></i>
                    <p class="text-gray-500">Cart is empty</p>
                </div>
                <div id="cartItems"></div>
            </div>

            <!-- Cart Totals -->
            <div id="cartTotals" class="hidden">
                <div class="space-y-2 text-sm">
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between font-bold">
                            <span>Total</span>
                            <span id="cartTotal" class="text-primary">KES 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">Customer</h3>

            <div class="space-y-3">
                <div>
                    <select id="customerType"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <option value="walk_in">Walk-in Customer</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="visitor">Visitor</option>
                    </select>
                </div>

                <div>
                    <input type="text"
                           id="customerPhone"
                           placeholder="Phone Number"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <div id="customerInfo" class="hidden bg-gray-50 p-3 rounded-lg text-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium" id="customerName"></p>
                            <p class="text-gray-500" id="customerEmail"></p>
                        </div>
                        <button onclick="clearCustomer()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">Select Payment Method</h3>
            <div class="space-y-3">
                <!-- STK Push (Highest Priority) -->
                <button onclick="selectPaymentMethod('mpesa_stk')"
                        class="payment-method-btn w-full flex items-center justify-between p-4 border-2 border-gray-200 bg-white rounded-xl hover:border-primary hover:bg-primary hover:bg-opacity-5 transition transform hover:-translate-y-0.5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center mr-3">
                            <i class="fas fa-bolt text-white text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-bold text-gray-800">STK Push</div>
                            <div class="text-xs text-gray-500">Instant prompt to phone</div>
                        </div>
                    </div>
                    <div class="bg-primary text-white text-xs font-bold px-2 py-1 rounded-full">
                        RECOMMENDED
                    </div>
                </button>

                <!-- Manual M-Pesa -->
                <button onclick="selectPaymentMethod('mpesa_manual')"
                        class="payment-method-btn w-full flex items-center p-4 border-2 border-gray-200 bg-white rounded-xl hover:border-green-500 hover:bg-green-50 transition">
                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mr-3">
                        <i class="fas fa-mobile-alt text-white text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-gray-800">M-Pesa Manual</div>
                        <div class="text-xs text-gray-500">Paybill/Buy Goods</div>
                    </div>
                </button>

                <!-- Swiss App -->
                <button onclick="selectPaymentMethod('swiss_app')"
                        class="payment-method-btn w-full flex items-center p-4 border-2 border-gray-200 bg-white rounded-xl hover:border-blue-500 hover:bg-blue-50 transition">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                        <i class="fas fa-qrcode text-white text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-gray-800">Swiss App</div>
                        <div class="text-xs text-gray-500">Scan QR code to pay</div>
                    </div>
                </button>

                <!-- Credit -->
                <button onclick="selectPaymentMethod('credit')"
                        class="payment-method-btn w-full flex items-center p-4 border-2 border-gray-200 bg-white rounded-xl hover:border-orange-500 hover:bg-orange-50 transition">
                    <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center mr-3">
                        <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-gray-800">Credit</div>
                        <div class="text-xs text-gray-500">Pay at month-end</div>
                    </div>
                </button>

                <!-- Cash -->
                <button onclick="selectPaymentMethod('cash')"
                        class="payment-method-btn w-full flex items-center p-4 border-2 border-gray-200 bg-white rounded-xl hover:border-gray-500 hover:bg-gray-50 transition">
                    <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill text-white text-lg"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-gray-800">Cash</div>
                        <div class="text-xs text-gray-500">Pay with cash</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Action Button -->
        <button onclick="processPaymentByMethod()"
                id="processPaymentBtn"
                disabled
                class="w-full bg-primary hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition opacity-50 cursor-not-allowed mb-4 shadow-lg">
            <i class="fas fa-bolt mr-2"></i>
            <span id="paymentBtnText">Select Payment Method</span>
        </button>

        <!-- Quick Stats -->
        <div class="mt-4 bg-gradient-to-r from-primary to-red-600 text-white rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">Today's Summary</h3>
                <span id="currentTime" class="text-sm opacity-90">{{ now()->format('h:i A') }}</span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Sales:</span>
                    <span id="todaySales">KES {{ number_format($todayStats['total_sales'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Transactions:</span>
                    <span id="todayTransactions">{{ $todayStats['transaction_count'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pending:</span>
                    <span id="todayPending">{{ $todayStats['pending_orders'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STK Push Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="stkPushModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">STK Push Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800">Instant STK Push</h4>
                    <p class="text-gray-600 mt-1">Sending payment prompt to:</p>
                    <p class="text-xl font-bold mt-1 text-primary" id="stkTargetPhone"></p>
                </div>

                <!-- Payment Details -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <div class="text-center">
                        <p class="text-sm text-gray-700 font-medium">Amount to pay</p>
                        <p class="text-3xl font-bold text-primary" id="stkAmount">KES 0.00</p>
                    </div>
                </div>

                <!-- Payment Status -->
                <div id="stkStatus" class="hidden">
                    <div class="mb-4 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                        <p class="text-gray-700 font-medium">Sending STK Push...</p>
                        <p class="text-sm text-gray-500 mt-1" id="stkStatusMessage">Please wait while we send the prompt</p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-yellow-600 mt-0.5 mr-2"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Check customer's phone</p>
                                <p class="text-xs text-yellow-700 mt-1">Customer has received the STK prompt. Ask them to enter their PIN to complete payment.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STK Polling Status -->
                <div id="stkPollingStatus" class="hidden">
                    <!-- Waiting for payment -->
                    <div id="stkWaiting" class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200 mb-4">
                        <i class="fas fa-clock text-yellow-500 text-3xl mb-2"></i>
                        <p class="text-yellow-700 font-medium">Waiting for Payment</p>
                        <p class="text-sm text-yellow-600 mt-1">Customer must enter PIN on their phone</p>
                        <div class="mt-2 text-xs text-yellow-600">
                            <span id="pollingTimer">0</span>/30 seconds
                        </div>
                    </div>

                    <!-- Payment successful -->
                    <div id="stkSuccess" class="hidden text-center p-4 bg-green-50 rounded-lg border border-green-200 mb-4">
                        <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                        <p class="text-green-700 font-medium">Payment Confirmed!</p>
                        <p class="text-sm text-green-600">Receipt: <span id="stkReceiptNumber" class="font-bold"></span></p>
                    </div>

                    <!-- Payment failed -->
                    <div id="stkFailed" class="hidden text-center p-4 bg-red-50 rounded-lg border border-red-200 mb-4">
                        <i class="fas fa-times-circle text-red-500 text-3xl mb-2"></i>
                        <p class="text-red-700 font-medium" id="stkFailedTitle">Payment Failed</p>
                        <p class="text-sm text-red-600 mt-1" id="stkFailedReason"></p>
                        <button onclick="retrySTKPush()"
                                class="mt-3 bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-redo mr-1"></i> Retry Payment
                        </button>
                    </div>

                    <!-- Customer didn't respond -->
                    <div id="stkTimeout" class="hidden text-center p-4 bg-orange-50 rounded-lg border border-orange-200 mb-4">
                        <i class="fas fa-exclamation-triangle text-orange-500 text-3xl mb-2"></i>
                        <p class="text-orange-700 font-medium">Customer Didn't Respond</p>
                        <p class="text-sm text-orange-600 mt-1">The STK prompt timed out. Customer needs to check phone.</p>
                        <div class="mt-3 space-y-2">
                            <button onclick="retrySTKPush()"
                                    class="w-full bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-redo mr-1"></i> Send STK Again
                            </button>
                            <button onclick="switchToManualPayment('mpesa_manual')"
                                    class="w-full bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                                <i class="fas fa-keyboard mr-1"></i> Enter Receipt Manually
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="sendSTKPush()"
                        id="sendStkBtn"
                        class="bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition shadow-md">
                    <i class="fas fa-paper-plane mr-1"></i> Send STK Push
                </button>
                <button type="button"
                        onclick="closeSTKModal()"
                        id="closeStkBtn"
                        class="hidden bg-gray-500 text-white font-medium py-2 px-4 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manual M-Pesa Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="mpesaManualModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-green-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Manual M-Pesa Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-mobile-alt text-green-500 text-4xl mb-3"></i>
                    <h4 class="text-lg font-bold text-gray-800">M-Pesa Payment Received</h4>
                    <p class="text-gray-600 mt-1">Customer has paid via M-Pesa</p>
                </div>

                <div class="bg-green-50 p-4 rounded-lg mb-6 border border-green-200">
                    <div class="text-center">
                        <p class="text-sm text-green-700 font-medium">Amount Paid</p>
                        <p class="text-3xl font-bold text-green-700" id="mpesaManualAmount">KES 0.00</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Receipt Number (Optional)</label>
                        <input type="text"
                               id="mpesaManualReceipt"
                               placeholder="e.g., RBX1234567"
                               class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number Used</label>
                        <input type="tel"
                               id="mpesaManualPhone"
                               placeholder="07XXXXXXXX"
                               class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                               value="">
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm mt-4">
                    <p class="text-gray-600"><i class="fas fa-info-circle text-green-500 mr-1"></i> Customer can pay via Paybill or Buy Goods.</p>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmManualMpesa()"
                        id="confirmMpesaManualBtn"
                        class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-1"></i> Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Swiss App Payment Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="swissAppModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-blue-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Swiss App Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-qrcode text-blue-500 text-4xl mb-3"></i>
                    <h4 class="text-lg font-bold text-gray-800">Swiss App Payment Received</h4>
                    <p class="text-gray-600 mt-1">Record the payment details</p>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-200">
                    <div class="text-center">
                        <p class="text-sm text-blue-700 font-medium">Amount Paid</p>
                        <p class="text-3xl font-bold text-blue-700" id="swissAmount">KES 0.00</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Swiss App Reference Number (Optional)</label>
                    <input type="text"
                           id="swissReference"
                           placeholder="Enter reference number"
                           class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                    <p class="text-gray-600"><i class="fas fa-info-circle text-blue-500 mr-1"></i> Swiss App payments are instant.</p>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmSwissAppPayment()"
                        id="confirmSwissBtn"
                        class="bg-blue-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-check mr-1"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cash Payment Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="cashModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-gray-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Cash Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-money-bill text-gray-500 text-4xl mb-3"></i>
                    <h4 class="text-lg font-bold text-gray-800">Cash Payment Received</h4>
                    <p class="text-gray-600 mt-1">Customer paid with cash</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <div class="text-center">
                        <p class="text-sm text-gray-700 font-medium">Amount Received</p>
                        <p class="text-3xl font-bold text-gray-700" id="cashAmount">KES 0.00</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Tendered (Optional)</label>
                    <input type="number"
                           id="cashAmountTendered"
                           placeholder="Enter amount received"
                           class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-gray-500 focus:border-gray-500 outline-none transition">
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                    <p class="text-gray-600"><i class="fas fa-info-circle text-gray-500 mr-1"></i> Cash payments are instant.</p>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmCashPayment()"
                        id="confirmCashBtn"
                        class="bg-gray-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-check mr-1"></i> Confirm Cash Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Credit Sale Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="creditModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-orange-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Credit Sale</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-file-invoice-dollar text-orange-500 text-4xl mb-3"></i>
                    <h4 class="text-lg font-bold text-gray-800">Record Credit Sale</h4>
                    <p class="text-gray-600 mt-1">Customer will pay at month-end</p>
                </div>

                <div class="bg-orange-50 p-4 rounded-lg mb-6 border border-orange-200">
                    <div class="text-center">
                        <p class="text-sm text-orange-700 font-medium">Amount to be billed</p>
                        <p class="text-3xl font-bold text-orange-700" id="creditAmount">KES 0.00</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                    <input type="text"
                           id="creditCustomerName"
                           value=""
                           class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                           placeholder="Enter customer name"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Phone Number *</label>
                    <input type="tel"
                           id="creditCustomerPhone"
                           value=""
                           class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                           placeholder="07XXXXXXXX (for billing)"
                           required>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                    <p class="text-gray-600"><i class="fas fa-info-circle text-blue-500 mr-1"></i> This sale will be recorded as credit and billed at the end of the month.</p>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmCreditSale()"
                        id="confirmCreditBtn"
                        class="bg-orange-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-orange-700 transition">
                    <i class="fas fa-check mr-1"></i> Record Credit Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="receiptModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-primary text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Sale Complete!</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <div id="receiptIcon" class="inline-block mb-4">
                        <i class="fas fa-check-circle text-green-500 text-5xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-1">Thank You!</h4>
                    <p class="text-gray-600" id="receiptSubtitle">Transaction completed successfully</p>
                </div>

                <!-- Receipt Preview -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="text-center mb-3">
                        <div class="font-bold text-lg text-primary">KTVTC Cafeteria</div>
                        <div class="text-sm text-gray-600" id="receiptShop">{{ $defaultShop->shop_name ?? 'Cafeteria' }}</div>
                    </div>

                    <div class="text-sm">
                        <div class="flex justify-between mb-2">
                            <span>Invoice:</span>
                            <span class="font-medium text-primary" id="receiptInvoice"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Date:</span>
                            <span id="receiptDate"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Customer:</span>
                            <span id="receiptCustomer"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Payment:</span>
                            <span id="receiptPayment"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Phone:</span>
                            <span id="receiptPhone"></span>
                        </div>
                        <div class="border-t border-gray-300 pt-2 mt-2">
                            <div class="flex justify-between font-bold">
                                <span class="text-primary">TOTAL:</span>
                                <span class="text-primary" id="receiptTotal"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="printReceipt()"
                            class="bg-white text-primary border border-primary font-medium py-3 px-4 rounded-lg hover:bg-primary hover:text-white transition">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <button onclick="newSale()"
                            class="btn-primary text-white font-medium py-3 px-4 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-plus mr-2"></i> New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Product Card */
    .product-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(230, 57, 70, 0.15);
        border-color: #E63946;
    }

    .product-card.out-of-stock .product-overlay {
        position: absolute;
        inset: 0;
        background: rgba(239, 68, 68, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-card .product-image {
        height: 120px;
        background-color: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Category Chips */
    .category-chip.active {
        background-color: #E63946;
        color: white;
    }

    /* Cart Item Animations */
    .cart-item {
        animation: slideIn 0.3s ease;
    }

    /* Payment Method Buttons */
    .payment-method-btn.active {
        border-color: #E63946 !important;
        box-shadow: 0 4px 12px rgba(230, 57, 70, 0.2);
        transform: translateY(-1px);
        background-color: rgba(230, 57, 70, 0.05) !important;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .modal {
        animation: fadeIn 0.3s ease;
    }

    /* Status Colors */
    .status-pending { background-color: #fef3c7; color: #92400e; }
    .status-success { background-color: #d1fae5; color: #065f46; }
    .status-failed { background-color: #fee2e2; color: #991b1b; }
    .status-timeout { background-color: #ffedd5; color: #9a3412; }
</style>
@endsection

@section('scripts')
<script>
    // Global variables
    let cart = [];
    let products = [];
    let currentInvoice = null;
    let currentReceipt = null;
    let stkPollingInterval = null;
    let showInStockOnly = true;
    let csrfToken = '{{ csrf_token() }}';
    let selectedPaymentMethod = null;
    let currentSTKCheckoutId = null;
    let pollingAttempts = 0;
    const MAX_POLLING_ATTEMPTS = 30;
    let pollingTimer = 0;

    // DOM Ready
    $(document).ready(function() {
        loadProducts();
        updateTodayStats();
        updateTime();
        setInterval(updateTime, 60000);

        // Event Listeners
        $('#productSearch').on('input', debounce(loadProducts, 300));
        $('#categoryFilter').change(loadProducts);
        $('#customerPhone').on('input', debounce(searchCustomer, 500));
        $('#customerType').change(resetCustomerSearch);

        // Close modals
        $('.close-modal, .modal-overlay').click(function() {
            closeAllModals();
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // F2 - New Sale
            if (e.key === 'F2' || e.keyCode === 113) {
                e.preventDefault();
                newSale();
            }
            // F3 - Search products
            if (e.key === 'F3' || e.keyCode === 114) {
                e.preventDefault();
                $('#productSearch').focus();
            }
            // F4 - STK Push (Highest priority)
            if ((e.key === 'F4' || e.keyCode === 115) && cart.length > 0) {
                e.preventDefault();
                selectPaymentMethod('mpesa_stk');
                processPaymentByMethod();
            }
            // F5 - Manual M-Pesa
            if ((e.key === 'F5' || e.keyCode === 116) && cart.length > 0) {
                e.preventDefault();
                selectPaymentMethod('mpesa_manual');
                processPaymentByMethod();
            }
            // F6 - Swiss App
            if ((e.key === 'F6' || e.keyCode === 117) && cart.length > 0) {
                e.preventDefault();
                selectPaymentMethod('swiss_app');
                processPaymentByMethod();
            }
            // F7 - Credit
            if ((e.key === 'F7' || e.keyCode === 118) && cart.length > 0) {
                e.preventDefault();
                selectPaymentMethod('credit');
                processPaymentByMethod();
            }
            // F8 - Cash
            if ((e.key === 'F8' || e.keyCode === 119) && cart.length > 0) {
                e.preventDefault();
                selectPaymentMethod('cash');
                processPaymentByMethod();
            }
            // Escape - Close modals
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });
    });

    // Product Functions
    function loadProducts(page = 1) {
        const search = $('#productSearch').val();
        const category = $('#categoryFilter').val();

        showLoading(true);

        $.ajax({
            url: '{{ route("cafeteria.api.products.pos") }}',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
            data: {
                page: page,
                search: search,
                category_id: category,
                in_stock_only: showInStockOnly
            },
            success: function(response) {
                if (response.data) {
                    products = response.data;
                    renderProducts();
                    renderPagination(response);
                    updateProductsCount(response.total);
                }
                showLoading(false);
            },
            error: function(xhr, status, error) {
                showLoading(false);
                toastr.error('Failed to load products');
            }
        });
    }

    function renderProducts() {
        const grid = $('#productsGrid');
        grid.empty();

        if (products.length === 0) {
            $('#productsEmpty').removeClass('hidden');
            return;
        }

        $('#productsEmpty').addClass('hidden');

        products.forEach((product, index) => {
            const outOfStock = product.track_inventory && (product.current_stock <= 0 || product.available_stock <= 0);
            const lowStock = product.track_inventory && product.current_stock <= (product.minimum_stock || 5);

            const productCard = `
                <div class="product-card bg-white relative ${outOfStock ? 'out-of-stock' : ''}"
                     onclick="${outOfStock ? '' : `addToCart(${product.id})`}">

                    <!-- Product Image -->
                    <div class="product-image">
                        ${product.image ?
                            `<img src="/storage/${product.image}"
                                  alt="${product.product_name}"
                                  class="w-full h-full object-cover"
                                  onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-family=%22Arial%22>No Image</text></svg>'">` :
                            `<i class="fas ${getProductIcon(product.product_type)} text-gray-300 text-3xl"></i>`
                        }
                        ${outOfStock ? `
                            <div class="product-overlay">
                                <span class="bg-red-600 text-white px-3 py-1 text-xs font-bold rounded-full">
                                    OUT OF STOCK
                                </span>
                            </div>
                        ` : ''}
                        ${lowStock && !outOfStock ? `
                            <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                Low Stock
                            </div>
                        ` : ''}
                    </div>

                    <!-- Product Info -->
                    <div class="p-3">
                        <div class="mb-1">
                            <h4 class="font-medium text-gray-900 text-sm truncate">${product.product_name}</h4>
                            <div class="text-xs text-gray-500 truncate">${product.product_code}</div>
                        </div>

                        <div class="flex justify-between items-center mt-2">
                            <div class="text-lg font-bold text-primary">
                                KES ${parseFloat(product.selling_price).toFixed(2)}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${product.track_inventory ? (product.current_stock || 0) : '∞'}
                            </div>
                        </div>

                        <!-- Quick Add Buttons -->
                        <div class="flex gap-1 mt-2">
                            <button onclick="event.stopPropagation(); addToCart(${product.id}, 1)"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-1.5 rounded transition"
                                    ${outOfStock ? 'disabled' : ''}>
                                +1
                            </button>
                            <button onclick="event.stopPropagation(); addToCart(${product.id}, 2)"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-1.5 rounded transition"
                                    ${outOfStock ? 'disabled' : ''}>
                                +2
                            </button>
                            <button onclick="event.stopPropagation(); addToCart(${product.id}, 5)"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-1.5 rounded transition"
                                    ${outOfStock ? 'disabled' : ''}>
                                +5
                            </button>
                        </div>
                    </div>
                </div>
            `;
            grid.append(productCard);
        });
    }

    function getProductIcon(type) {
        const icons = {
            'food': 'fa-utensils',
            'beverage': 'fa-coffee',
            'snack': 'fa-cookie-bite',
            'gift': 'fa-gift',
            'stationery': 'fa-pen',
            'other': 'fa-box'
        };
        return icons[type] || 'fa-box';
    }

    function renderPagination(response) {
        const pagination = $('#productsPagination');
        pagination.empty();

        if (response.last_page <= 1) return;

        const currentPage = response.current_page;
        const totalPages = response.last_page;

        // Previous button
        if (currentPage > 1) {
            pagination.append(`
                <button onclick="loadProducts(${currentPage - 1})"
                        class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `);
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                pagination.append(`
                    <button class="px-3 py-1 rounded-lg bg-primary text-white">
                        ${i}
                    </button>
                `);
            } else if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                pagination.append(`
                    <button onclick="loadProducts(${i})"
                            class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                        ${i}
                    </button>
                `);
            }
        }

        // Next button
        if (currentPage < totalPages) {
            pagination.append(`
                <button onclick="loadProducts(${currentPage + 1})"
                        class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `);
        }
    }

    // Cart Functions
    function addToCart(productId, quantity = 1) {
        const product = products.find(p => p.id === productId);
        if (!product) {
            return;
        }

        // Check stock
        if (product.track_inventory && (product.current_stock < quantity || product.available_stock < quantity)) {
            toastr.error(`Only ${product.current_stock} available in stock`);
            return;
        }

        // Find existing item
        const existingIndex = cart.findIndex(item => item.product_id === productId);

        if (existingIndex > -1) {
            const newQuantity = cart[existingIndex].quantity + quantity;

            if (product.track_inventory && product.current_stock < newQuantity) {
                toastr.error(`Cannot add more. Available: ${product.current_stock}`);
                return;
            }

            cart[existingIndex].quantity = newQuantity;
            cart[existingIndex].total = cart[existingIndex].unit_price * newQuantity;
        } else {
            cart.push({
                product_id: productId,
                product_name: product.product_name,
                product_code: product.product_code,
                unit_price: parseFloat(product.selling_price),
                quantity: quantity,
                total: parseFloat(product.selling_price) * quantity,
                track_inventory: product.track_inventory,
                current_stock: product.current_stock
            });
        }

        updateCart();
        toastr.success(`${quantity}x ${product.product_name} added to cart`);
    }

    function updateCart() {
        const cartItems = $('#cartItems');
        const emptyCart = $('#emptyCart');
        const cartTotals = $('#cartTotals');
        const processBtn = $('#processPaymentBtn');

        cartItems.empty();

        if (cart.length === 0) {
            emptyCart.removeClass('hidden');
            cartTotals.addClass('hidden');
            processBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            selectedPaymentMethod = null;
            resetPaymentButtons();
            return;
        }

        emptyCart.addClass('hidden');
        cartTotals.removeClass('hidden');
        updatePaymentButton();

        let total = 0;

        cart.forEach((item, index) => {
            total += item.total;

            const cartItem = `
                <div class="cart-item bg-gray-50 rounded-lg p-3 mb-2">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 text-sm">${item.product_name}</div>
                            <div class="text-xs text-gray-500">${item.product_code}</div>
                        </div>
                        <button onclick="removeFromCart(${index})"
                                class="text-gray-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm font-bold text-primary">
                            KES ${item.unit_price.toFixed(2)}
                        </div>

                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${index}, ${item.quantity - 1})"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="text-sm font-medium w-8 text-center">${item.quantity}</span>
                            <button onclick="updateQuantity(${index}, ${item.quantity + 1})"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>

                        <div class="text-sm font-medium">
                            KES ${item.total.toFixed(2)}
                        </div>
                    </div>
                </div>
            `;
            cartItems.append(cartItem);
        });

        // Update display
        $('#cartTotal').text(`KES ${total.toFixed(2)}`);
        $('#stkAmount').text(`KES ${total.toFixed(2)}`);
        $('#mpesaManualAmount').text(`KES ${total.toFixed(2)}`);
        $('#swissAmount').text(`KES ${total.toFixed(2)}`);
        $('#cashAmount').text(`KES ${total.toFixed(2)}`);
        $('#creditAmount').text(`KES ${total.toFixed(2)}`);
    }

    function updateQuantity(index, newQuantity) {
        if (newQuantity < 1) {
            removeFromCart(index);
            return;
        }

        const item = cart[index];
        const product = products.find(p => p.id === item.product_id);

        if (product && product.track_inventory && product.current_stock < newQuantity) {
            toastr.error(`Only ${product.current_stock} available`);
            return;
        }

        item.quantity = newQuantity;
        item.total = item.unit_price * newQuantity;
        updateCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCart();
        toastr.info('Item removed from cart');
    }

    function clearCart() {
        if (cart.length === 0) return;

        if (confirm('Clear all items from cart?')) {
            cart = [];
            updateCart();
            toastr.info('Cart cleared');
        }
    }

    // Customer Functions
    function searchCustomer() {
        const phone = $('#customerPhone').val().trim();
        const customerType = $('#customerType').val();

        if (phone.length < 10) {
            $('#customerInfo').addClass('hidden');
            return;
        }

        $.ajax({
            url: '{{ route("sales.search.customers") }}',
            method: 'GET',
            data: {
                phone: phone,
                type: customerType === 'walk_in' ? null : customerType
            },
            success: function(response) {
                if (response.customers && response.customers.length > 0) {
                    const customer = response.customers[0];
                    $('#customerName').text(customer.name || 'Customer');
                    $('#customerEmail').text(customer.email || 'No email');
                    $('#customerInfo').removeClass('hidden');
                    $('#stkTargetPhone').text(customer.phone);
                    $('#mpesaManualPhone').val(customer.phone);
                } else {
                    $('#customerName').text('Customer (' + phone + ')');
                    $('#customerEmail').text('No email');
                    $('#customerInfo').removeClass('hidden');
                    $('#stkTargetPhone').text(phone);
                    $('#mpesaManualPhone').val(phone);
                }
            },
            error: function(xhr) {
                // Fallback on error
                $('#customerName').text('Customer (' + phone + ')');
                $('#customerEmail').text('No email');
                $('#customerInfo').removeClass('hidden');
                $('#stkTargetPhone').text(phone);
                $('#mpesaManualPhone').val(phone);
            }
        });
    }

    function clearCustomer() {
        $('#customerPhone').val('');
        $('#customerInfo').addClass('hidden');
        $('#customerName').text('');
        $('#customerEmail').text('');
    }

    function resetCustomerSearch() {
        clearCustomer();
    }

    // Payment Method Functions
    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;

        // Reset all buttons
        $('.payment-method-btn').removeClass('active');

        // Highlight selected button
        $(`.payment-method-btn[onclick*="${method}"]`).addClass('active');

        switch(method) {
            case 'mpesa_stk':
                $('#paymentBtnText').html('<i class="fas fa-bolt mr-1"></i> Send STK Push');
                break;
            case 'mpesa_manual':
                $('#paymentBtnText').html('<i class="fas fa-mobile-alt mr-1"></i> Record M-Pesa');
                break;
            case 'swiss_app':
                $('#paymentBtnText').html('<i class="fas fa-qrcode mr-1"></i> Record Swiss App');
                break;
            case 'cash':
                $('#paymentBtnText').html('<i class="fas fa-money-bill mr-1"></i> Record Cash');
                break;
            case 'credit':
                $('#paymentBtnText').html('<i class="fas fa-file-invoice-dollar mr-1"></i> Record Credit Sale');
                break;
        }

        updatePaymentButton();
    }

    function resetPaymentButtons() {
        $('.payment-method-btn').removeClass('active');
        $('#paymentBtnText').text('Select Payment Method');
    }

    function updatePaymentButton() {
        const cartTotal = getCartTotal();
        const btn = $('#processPaymentBtn');

        if (cartTotal > 0 && selectedPaymentMethod) {
            btn.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
        } else {
            btn.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
        }
    }

    function getCartTotal() {
        return cart.reduce((total, item) => total + item.total, 0);
    }

    // Main Payment Processing
    function processPaymentByMethod() {
        if (!selectedPaymentMethod) {
            toastr.error('Please select a payment method');
            return;
        }

        if (cart.length === 0) {
            toastr.error('Cart is empty');
            return;
        }

        switch(selectedPaymentMethod) {
            case 'mpesa_stk':
                processSTKPush();
                break;
            case 'mpesa_manual':
                processMpesaManual();
                break;
            case 'swiss_app':
                processSwissApp();
                break;
            case 'cash':
                processCash();
                break;
            case 'credit':
                processCredit();
                break;
        }
    }

    // STK Push Payment
    function processSTKPush() {
        const phone = $('#customerPhone').val().trim();
        if (!phone || !phone.match(/^(07|01)\d{8}$/)) {
            toastr.error('Please enter a valid phone number for STK Push');
            $('#customerPhone').focus();
            return;
        }

        $('#stkTargetPhone').text(phone);
        $('#stkStatus').addClass('hidden');
        $('#stkPollingStatus').addClass('hidden');
        $('#stkWaiting').addClass('hidden');
        $('#stkSuccess').addClass('hidden');
        $('#stkFailed').addClass('hidden');
        $('#stkTimeout').addClass('hidden');
        $('#sendStkBtn').prop('disabled', false);
        $('#closeStkBtn').addClass('hidden');

        $('#stkPushModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function sendSTKPush() {
        const phone = $('#customerPhone').val().trim();
        const amount = getCartTotal();
        const items = cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
        }));

        $('#stkStatus').removeClass('hidden');
        $('#sendStkBtn').prop('disabled', true);
        $('#stkStatusMessage').text('Sending STK Push to ' + phone);

        $.ajax({
            url: '{{ route("sales.initiate.mpesa") }}',
            method: 'POST',
            data: {
                amount: amount,
                phone: phone,
                items: items,
                customer_name: $('#customerName').text(),
                is_stk: true,
                _token: csrfToken
            },
            success: function(response) {
                if (response.status === 'success' || response.success) {
                    const checkoutRequestId = response.data.checkout_request_id;
                    if (checkoutRequestId) {
                        currentSTKCheckoutId = checkoutRequestId;
                        $('#stkStatus').addClass('hidden');
                        $('#stkPollingStatus').removeClass('hidden');
                        $('#stkWaiting').removeClass('hidden');
                        startSTKPolling(checkoutRequestId);
                    } else {
                        showSTKError('Failed to send STK Push');
                    }
                } else {
                    const errorMsg = response.message || response.error || 'Failed to send STK Push';
                    showSTKError(errorMsg);
                }
            },
            error: function(xhr, status, error) {
                showSTKError('Error connecting to payment service');
            }
        });
    }

    function startSTKPolling(checkoutRequestId) {
        pollingAttempts = 0;
        pollingTimer = 0;

        // Update timer every second
        const timerInterval = setInterval(() => {
            pollingTimer++;
            $('#pollingTimer').text(pollingTimer);
        }, 1000);

        stkPollingInterval = setInterval(() => {
            pollingAttempts++;

            if (pollingAttempts >= MAX_POLLING_ATTEMPTS) {
                clearInterval(stkPollingInterval);
                clearInterval(timerInterval);
                showSTKTimeout();
                return;
            }

            $.ajax({
                url: '{{ route("cafeteria.sales.pos.check-mpesa-status") }}',
                method: 'POST',
                data: {
                    checkout_request_id: checkoutRequestId,
                    _token: csrfToken
                },
                success: function(response) {
                    console.log('STK Polling Response:', response);

                    // Check for completed payment
                    if (response.status === 'completed' ||
                        (response.success === true && response.result_code === 0) ||
                        response.result_code === 0) {

                        clearInterval(stkPollingInterval);
                        clearInterval(timerInterval);

                        const receipt = response.mpesa_receipt_number ||
                                       response.receipt_number;

                        console.log('Payment SUCCESS detected! Status:', response.status, 'Receipt:', receipt);
                        showSTKSuccess(receipt);

                    } else if (response.status === 'failed' ||
                               (response.result_code && response.result_code > 0) ||
                               response.success === false) {

                        clearInterval(stkPollingInterval);
                        clearInterval(timerInterval);
                        handleSTKErrorCode(response.result_code, response.result_description || response.message);

                    } else if (response.status === 'pending' || response.status === 'initiated') {
                        // Still pending - continue polling
                        $('#stkStatusMessage').text(response.message || 'Waiting for customer to enter PIN...');
                    } else {
                        // Unknown status - continue polling
                        console.log('Unknown status, continuing polling:', response.status);
                    }
                },
                error: function(xhr) {
                    console.log('Polling error, attempt:', pollingAttempts);
                    // Don't show error for every failed attempt
                }
            });
        }, 2000);
    }

    function handleSTKErrorCode(resultCode, description) {
        clearInterval(stkPollingInterval);

        // Convert to string for comparison
        const codeStr = resultCode.toString();

        switch(codeStr) {
            case '1037': // DS timeout - user cannot be reached
                showSTKTimeout();
                break;
            case '1032': // Request cancelled by user
                showSTKError('Customer cancelled the payment', 'Payment Cancelled');
                break;
            case '2001': // Insufficient balance
                showSTKError('Customer has insufficient balance in M-Pesa', 'Insufficient Balance');
                break;
            default:
                showSTKError(description || 'Payment failed. Code: ' + codeStr, 'Payment Failed');
                break;
        }
    }

    function showSTKSuccess(receiptNumber) {
        console.log('STK Payment Successful! Receipt:', receiptNumber);

        // Show success message
        $('#stkWaiting').addClass('hidden');
        $('#stkSuccess').removeClass('hidden');
        $('#stkReceiptNumber').text(receiptNumber);
        $('#sendStkBtn').addClass('hidden');
        $('#closeStkBtn').removeClass('hidden');

        // Show success toast
        toastr.success('Payment confirmed! Receipt: ' + receiptNumber);

        // Close the STK modal and show receipt after 2 seconds
        setTimeout(() => {
            // First close the STK modal
            $('#stkPushModal').addClass('hidden');
            document.body.classList.remove('overflow-hidden');

            // Then process the payment and show receipt
            processPayment('mpesa_stk', {
                mpesa_receipt: receiptNumber,
                phone: $('#customerPhone').val()
            });
        }, 2000);
    }

    function showSTKError(message, title = 'Payment Failed') {
        $('#stkWaiting').addClass('hidden');
        $('#stkFailed').removeClass('hidden');
        $('#stkFailedTitle').text(title);
        $('#stkFailedReason').text(message);
        $('#sendStkBtn').prop('disabled', false);
    }

    function showSTKTimeout() {
        $('#stkWaiting').addClass('hidden');
        $('#stkTimeout').removeClass('hidden');
        $('#sendStkBtn').addClass('hidden');
        $('#closeStkBtn').removeClass('hidden');
    }

    function retrySTKPush() {
        $('#stkFailed').addClass('hidden');
        $('#stkTimeout').addClass('hidden');
        $('#stkWaiting').removeClass('hidden');
        $('#closeStkBtn').addClass('hidden');
        $('#sendStkBtn').removeClass('hidden').prop('disabled', false);

        pollingAttempts = 0;
        pollingTimer = 0;
        startSTKPolling(currentSTKCheckoutId);
    }

    function switchToManualPayment(method) {
        closeAllModals();
        selectPaymentMethod(method);
        setTimeout(() => {
            processPaymentByMethod();
        }, 300);
    }

    function closeSTKModal() {
        closeAllModals();
    }

    // Manual M-Pesa Payment
    function processMpesaManual() {
        const total = getCartTotal();
        $('#mpesaManualAmount').text('KES ' + total.toFixed(2));
        $('#mpesaManualReceipt').val('');
        $('#mpesaManualPhone').val($('#customerPhone').val());

        $('#mpesaManualModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function confirmManualMpesa() {
        const receipt = $('#mpesaManualReceipt').val().trim();
        const phone = $('#mpesaManualPhone').val().trim() || $('#customerPhone').val().trim();

        // Phone is now optional
        if (phone && !phone.match(/^(07|01)\d{8}$/)) {
            toastr.error('Please enter a valid phone number');
            $('#mpesaManualPhone').focus();
            return;
        }

        $('#confirmMpesaManualBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        processPayment('mpesa_manual', {
            mpesa_receipt: receipt || null,
            customer_phone: phone || null
        });
    }

    // Swiss App Payment
    function processSwissApp() {
        const total = getCartTotal();
        $('#swissAmount').text('KES ' + total.toFixed(2));
        $('#swissReference').val('');

        $('#swissAppModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function confirmSwissAppPayment() {
        const reference = $('#swissReference').val().trim();

        $('#confirmSwissBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        processPayment('swiss_app', {
            swiss_reference: reference || null,
            phone: $('#customerPhone').val()
        });
    }

    // Cash Payment
    function processCash() {
        const total = getCartTotal();
        $('#cashAmount').text('KES ' + total.toFixed(2));
        $('#cashAmountTendered').val(total.toFixed(2));

        $('#cashModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function confirmCashPayment() {
        const amountTendered = parseFloat($('#cashAmountTendered').val()) || getCartTotal();
        const total = getCartTotal();

        if (amountTendered < total) {
            toastr.error('Amount tendered is less than total amount');
            return;
        }

        $('#confirmCashBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        processPayment('cash', {
            amount_tendered: amountTendered,
            change: amountTendered - total,
            phone: $('#customerPhone').val()
        });
    }

    // Credit Sale
    function processCredit() {
        const total = getCartTotal();
        const customerPhone = $('#customerPhone').val().trim();
        const customerName = $('#customerName').text();

        $('#creditAmount').text('KES ' + total.toFixed(2));
        $('#creditCustomerPhone').val(customerPhone || '');
        $('#creditCustomerName').val(customerName || '');

        $('#creditModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function confirmCreditSale() {
        const phone = $('#creditCustomerPhone').val().trim();
        const name = $('#creditCustomerName').val().trim();

        if (!phone || !phone.match(/^(07|01)\d{8}$/)) {
            toastr.error('Please enter a valid phone number for credit sale');
            $('#creditCustomerPhone').focus();
            return;
        }

        if (!name) {
            toastr.error('Please enter customer name for credit sale');
            $('#creditCustomerName').focus();
            return;
        }

        $('#confirmCreditBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        processPayment('credit', {
            customer_name: name,
            customer_phone: phone
        });
    }

    // Main Payment Processor - FIXED
    function processPayment(method, details) {
        const customerPhone = details.customer_phone || $('#customerPhone').val().trim() || null;
        const customerType = $('#customerType').val();
        const customerName = details.customer_name || $('#customerName').text() || 'Walk-in Customer';

        // Fix for backend validation - use correct payment method names
        let backendPaymentMethod = method;
        if (method === 'mpesa_stk') {
            backendPaymentMethod = 'mpesa'; // Backend expects 'mpesa' for STK payments
        }

        const saleData = {
            business_section_id: {{ $defaultBusinessSection->id ?? 1 }},
            shop_id: {{ $defaultShop->id ?? 1 }},
            sale_type: 'pos',
            channel: 'cafeteria',
            customer_phone: customerPhone,
            customer_type: customerType === 'walk_in' ? null : customerType,
            customer_name: customerName,
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                is_production_item: false // FIX: Add this required field
            })),
            payment_method: backendPaymentMethod,
            mpesa_receipt: method === 'mpesa_stk' || method === 'mpesa_manual' ? details.mpesa_receipt : null,
            swiss_reference: method === 'swiss_app' ? details.swiss_reference : null,
            _token: csrfToken
        };

        console.log('Sending sale data:', saleData);

        $.ajax({
            url: '{{ route("cafeteria.sales.store") }}',
            method: 'POST',
            data: saleData,
            success: function(response) {
                console.log('Sale response:', response);

                if (response.success) {
                    currentInvoice = response.sale.invoice_number;
                    currentReceipt = response.sale;

                    // Close ALL modals
                    closeAllModals();

                    // Show receipt
                    showReceipt(response.sale, method, details);

                    // Reset cart
                    cart = [];
                    updateCart();
                    clearCustomer();
                    selectedPaymentMethod = null;
                    resetPaymentButtons();

                    // Reload products to update stock
                    loadProducts();
                    updateTodayStats();

                    // Show success message
                    toastr.success('Sale completed successfully! Invoice: ' + currentInvoice);
                } else {
                    toastr.error('Failed to process sale: ' + (response.error || 'Unknown error'));
                    // Re-enable buttons on error
                    reenablePaymentButtons();
                }
            },
            error: function(xhr) {
                console.log('Sale error:', xhr.responseJSON);
                toastr.error('Failed to process sale: ' + (xhr.responseJSON?.error || 'Unknown error'));
                // Re-enable buttons on error
                reenablePaymentButtons();
            }
        });
    }

    function reenablePaymentButtons() {
        $('#confirmMpesaManualBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Record Payment');
        $('#confirmSwissBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm Payment');
        $('#confirmCashBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm Cash Payment');
        $('#confirmCreditBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Record Credit Sale');
        $('#sendStkBtn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send STK Push');
    }

    function showReceipt(sale, paymentMethod, paymentDetails) {
        $('#receiptInvoice').text(sale.invoice_number);
        $('#receiptDate').text(new Date(sale.created_at).toLocaleString());
        $('#receiptCustomer').text(sale.customer_name || 'Walk-in Customer');
        $('#receiptPhone').text(sale.customer_phone || 'N/A');

        // Format payment method display
        let paymentDisplay = '';
        let subtitle = 'Transaction completed successfully';
        let iconColor = 'text-green-500';

        switch(paymentMethod) {
            case 'mpesa_stk':
                paymentDisplay = 'STK Push';
                subtitle = 'STK Push payment confirmed';
                iconColor = 'text-primary';
                $('#receiptIcon').html('<i class="fas fa-bolt ' + iconColor + ' text-5xl"></i>');
                if (paymentDetails.mpesa_receipt) {
                    paymentDisplay += ` (${paymentDetails.mpesa_receipt})`;
                }
                break;
            case 'mpesa_manual':
                paymentDisplay = 'M-Pesa Manual';
                subtitle = 'Manual M-Pesa payment recorded';
                iconColor = 'text-green-500';
                $('#receiptIcon').html('<i class="fas fa-mobile-alt ' + iconColor + ' text-5xl"></i>');
                if (paymentDetails.mpesa_receipt) {
                    paymentDisplay += ` (${paymentDetails.mpesa_receipt})`;
                }
                break;
            case 'swiss_app':
                paymentDisplay = 'Swiss App';
                subtitle = 'Swiss App payment recorded';
                iconColor = 'text-blue-500';
                $('#receiptIcon').html('<i class="fas fa-qrcode ' + iconColor + ' text-5xl"></i>');
                if (paymentDetails.swiss_reference) {
                    paymentDisplay += ` (Ref: ${paymentDetails.swiss_reference})`;
                }
                break;
            case 'cash':
                paymentDisplay = 'CASH';
                subtitle = 'Cash payment received';
                iconColor = 'text-gray-500';
                $('#receiptIcon').html('<i class="fas fa-money-bill ' + iconColor + ' text-5xl"></i>');
                if (paymentDetails.amount_tendered) {
                    const change = paymentDetails.change || 0;
                    paymentDisplay += ` (Tendered: KES ${paymentDetails.amount_tendered.toFixed(2)})`;
                    if (change > 0) {
                        paymentDisplay += ` (Change: KES ${change.toFixed(2)})`;
                    }
                }
                break;
            case 'credit':
                paymentDisplay = 'CREDIT SALE';
                subtitle = 'Credit sale recorded - Pay at month-end';
                iconColor = 'text-orange-500';
                $('#receiptIcon').html('<i class="fas fa-file-invoice-dollar ' + iconColor + ' text-5xl"></i>');
                break;
        }

        $('#receiptSubtitle').text(subtitle);
        $('#receiptPayment').text(paymentDisplay);
        $('#receiptTotal').text('KES ' + parseFloat(sale.total_amount).toFixed(2));

        $('#receiptModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function newSale() {
        closeAllModals();
        cart = [];
        updateCart();
        clearCustomer();
        selectedPaymentMethod = null;
        resetPaymentButtons();
        $('#productSearch').val('').focus();
        toastr.info('New sale started');
    }

    function printReceipt() {
        if (!currentInvoice) {
            return;
        }

        window.open(`/sales/${currentInvoice}/print-receipt`, '_blank');
        toastr.success('Opening receipt for printing...');
    }

    function closeAllModals() {
        // Close all modals
        $('.modal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');

        // Clear any intervals
        if (stkPollingInterval) {
            clearInterval(stkPollingInterval);
            stkPollingInterval = null;
        }

        // Reset polling variables
        pollingAttempts = 0;
        pollingTimer = 0;

        // Reset STK modal state
        $('#stkStatus').addClass('hidden');
        $('#stkPollingStatus').addClass('hidden');
        $('#stkWaiting').addClass('hidden');
        $('#stkSuccess').addClass('hidden');
        $('#stkFailed').addClass('hidden');
        $('#stkTimeout').addClass('hidden');
        $('#sendStkBtn').removeClass('hidden').prop('disabled', false);
        $('#closeStkBtn').addClass('hidden');
    }

    function toggleStockFilter() {
        showInStockOnly = !showInStockOnly;
        $('#stockFilterBtn').html(
            showInStockOnly ?
            '<i class="fas fa-filter mr-2"></i>In Stock' :
            '<i class="fas fa-filter mr-2"></i>All Items'
        );
        loadProducts();
    }

    function filterByCategory(categoryId) {
        $('#categoryFilter').val(categoryId);
        $('.category-chip').removeClass('active');
        $(`.category-chip[onclick*="${categoryId}"]`).addClass('active');
        loadProducts();
    }

    function updateProductsCount(count) {
        $('#productsCount').text(count);
    }

    function showLoading(show) {
        if (show) {
            $('#productsLoading').removeClass('hidden');
            $('#productsGrid').addClass('hidden');
        } else {
            $('#productsLoading').addClass('hidden');
            $('#productsGrid').removeClass('hidden');
        }
    }

    function updateTodayStats() {
        $.ajax({
            url: '{{ route("sales.today.stats") }}',
            method: 'GET',
            success: function(stats) {
                $('#todaySales').text('KES ' + (stats.total_sales || 0).toFixed(2));
                $('#todayTransactions').text(stats.transaction_count || 0);
                $('#todayPending').text(stats.pending_orders || 0);
            },
            error: function(xhr) {
                console.log('Failed to update stats');
            }
        });
    }

    function updateTime() {
        const now = new Date();
        $('#currentTime').text(now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
</script>
@endsection
