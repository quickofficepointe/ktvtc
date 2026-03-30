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
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                    {{ $category->name }}
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
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span id="cartSubtotal">KES 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discount</span>
                        <span class="text-red-600" id="cartDiscount">-KES 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax (16%)</span>
                        <span id="cartTax">KES 0.00</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between font-bold">
                            <span>Total</span>
                            <span id="cartTotal" class="text-primary">KES 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Discount -->
                <div class="mt-4">
                    <div class="flex gap-2">
                        <input type="number"
                               id="quickDiscount"
                               placeholder="Discount"
                               class="flex-1 border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        <button onclick="applyQuickDiscount()"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm transition">
                            Apply
                        </button>
                    </div>
                    <div class="flex gap-1 mt-2">
                        @foreach([5, 10, 15, 20] as $discount)
                        <button onclick="setQuickDiscount({{ $discount }})"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 rounded text-xs transition">
                            {{ $discount }}%
                        </button>
                        @endforeach
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
                           placeholder="Phone Number (for M-Pesa)"
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

        <!-- Payment Actions -->
        <div class="space-y-3">
            <!-- M-Pesa Prompt -->
            <button onclick="processMpesaPrompt()"
                    id="mpesaPromptBtn"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-mobile-alt mr-2"></i>
                Process M-Pesa
            </button>

            <!-- Direct M-Pesa -->
            <button onclick="showDirectMpesa()"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-receipt mr-2"></i>
                Record M-Pesa Payment
            </button>

            <!-- Cash Payment -->
            <button onclick="showCashPayment()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-money-bill-wave mr-2"></i>
                Cash Payment
            </button>

            <!-- Complete Sale (disabled until cart has items) -->
            <button onclick="completeSale()"
                    id="completeSaleBtn"
                    disabled
                    class="w-full bg-primary hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition opacity-50 cursor-not-allowed">
                <i class="fas fa-check-circle mr-2"></i>
                Complete Sale
            </button>
        </div>

        <!-- Quick Stats -->
        <div class="mt-4 bg-primary text-white rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">Today's Summary</h3>
                <span id="currentTime" class="text-sm opacity-90"></span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Sales:</span>
                    <span id="todaySales">KES 0</span>
                </div>
                <div class="flex justify-between">
                    <span>Transactions:</span>
                    <span id="todayTransactions">0</span>
                </div>
                <div class="flex justify-between">
                    <span>Pending:</span>
                    <span id="todayPending">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- M-Pesa Payment Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="mpesaModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-green-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">M-Pesa Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-mobile-alt text-green-500 text-4xl mb-3"></i>
                    <p class="text-gray-700">Send M-Pesa prompt to:</p>
                    <p class="text-xl font-bold mt-1" id="mpesaTargetPhone"></p>
                </div>

                <!-- Payment Details -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Amount to pay</p>
                        <p class="text-3xl font-bold text-primary" id="mpesaAmount">KES 0.00</p>
                    </div>
                </div>

                <!-- Payment Status -->
                <div id="mpesaStatus" class="hidden">
                    <div class="mb-4 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mb-2"></div>
                        <p class="text-gray-700">Waiting for payment confirmation...</p>
                        <p class="text-sm text-gray-500 mt-1" id="mpesaStatusMessage"></p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Customer has received the prompt. Please wait for confirmation.
                        </p>
                    </div>
                </div>

                <!-- Manual M-Pesa Details -->
                <div id="manualMpesa" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Receipt Number</label>
                            <input type="text"
                                   id="mpesaReceipt"
                                   placeholder="e.g., RBX1234567"
                                   class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                            <input type="text"
                                   id="mpesaTransactionId"
                                   placeholder="Optional"
                                   class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number Used</label>
                            <input type="tel"
                                   id="manualMpesaPhone"
                                   placeholder="07XXXXXXXX"
                                   class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-between">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <div class="space-x-2">
                    <button type="button"
                            onclick="toggleManualMpesa()"
                            id="manualToggleBtn"
                            class="bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-keyboard mr-1"></i> Enter Manually
                    </button>
                    <button type="button"
                            onclick="sendMpesaPrompt()"
                            id="sendPromptBtn"
                            class="bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-paper-plane mr-1"></i> Send Prompt
                    </button>
                    <button type="button"
                            onclick="confirmManualMpesa()"
                            id="confirmManualBtn"
                            class="hidden bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check mr-1"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cash Payment Modal -->
<div class="modal fade fixed inset-0 z-50 overflow-auto hidden" id="cashModal" aria-hidden="true">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div class="modal-container fixed inset-0 flex items-center justify-center p-4">
        <div class="modal-content bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="modal-header bg-blue-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Cash Payment</h3>
                <button class="close-modal text-white text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-money-bill-wave text-blue-500 text-4xl mb-3"></i>
                    <p class="text-gray-700">Amount Due</p>
                    <p class="text-3xl font-bold text-primary" id="cashAmount">KES 0.00</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount Received (KES)</label>
                        <input type="number"
                               id="cashReceived"
                               step="100"
                               min="0"
                               class="w-full border border-gray-300 rounded-lg py-3 px-4 text-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between text-lg">
                            <span>Change Due</span>
                            <span class="font-bold text-green-600" id="cashChange">KES 0.00</span>
                        </div>
                    </div>

                    <!-- Quick Amounts -->
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setCashAmount(1000)"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded transition">
                            KES 1,000
                        </button>
                        <button onclick="setCashAmount(2000)"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded transition">
                            KES 2,000
                        </button>
                        <button onclick="setCashAmount(5000)"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded transition">
                            KES 5,000
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 p-4 rounded-b-xl flex justify-end space-x-3">
                <button type="button"
                        class="close-modal bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="processCashPayment()"
                        class="bg-blue-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-check mr-1"></i> Complete Cash Payment
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
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h4 class="text-xl font-bold text-gray-800 mb-1">Thank You!</h4>
                    <p class="text-gray-600">Transaction completed successfully</p>
                </div>

                <!-- Receipt Preview -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="text-center mb-3">
                        <div class="font-bold text-lg">KCB Taita Taveta University</div>
                        <div class="text-sm text-gray-600" id="receiptShop"></div>
                    </div>

                    <div class="text-sm">
                        <div class="flex justify-between mb-2">
                            <span>Invoice:</span>
                            <span class="font-medium" id="receiptInvoice"></span>
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
                        <div class="border-t border-gray-300 pt-2 mt-2">
                            <div class="flex justify-between font-bold">
                                <span>TOTAL:</span>
                                <span id="receiptTotal"></span>
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

                <!-- Email Receipt Option -->
                <div class="mt-4 text-center">
                    <button onclick="emailReceipt()"
                            class="text-sm text-primary hover:underline">
                        <i class="fas fa-envelope mr-1"></i> Email receipt to customer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .product-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

    .category-chip.active {
        background-color: #E63946;
        color: white;
    }

    .cart-item {
        animation: slideIn 0.3s ease;
    }

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

    .modal {
        animation: fadeIn 0.3s ease;
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
</style>
@endsection

@section('scripts')
<script>
    // Global variables
    let cart = [];
    let products = [];
    let customers = [];
    let currentInvoice = null;
    let currentReceipt = null;
    let mpesaCheckInterval = null;
    let showInStockOnly = true;

    // DOM Ready
    $(document).ready(function() {
        loadProducts();
        loadTodayStats();
        updateTime();
        setInterval(updateTime, 60000); // Update time every minute

        // Event Listeners
        $('#productSearch').on('input', debounce(loadProducts, 300));
        $('#categoryFilter').change(loadProducts);
        $('#customerPhone').on('input', debounce(searchCustomer, 500));
        $('#customerType').change(resetCustomerSearch);

        // Cash received calculation
        $('#cashReceived').on('input', calculateCashChange);

        // Close modals
        $('.close-modal, .modal-overlay').click(function() {
            closeAllModals();
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // F2 - New Sale
            if (e.key === 'F2') {
                e.preventDefault();
                newSale();
            }
            // F3 - Search products
            if (e.key === 'F3') {
                e.preventDefault();
                $('#productSearch').focus();
            }
            // F4 - M-Pesa
            if (e.key === 'F4' && cart.length > 0) {
                e.preventDefault();
                processMpesaPrompt();
            }
            // F5 - Cash
            if (e.key === 'F5' && cart.length > 0) {
                e.preventDefault();
                showCashPayment();
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
            url: '{{ route("api.products.pos") }}',
            method: 'GET',
            data: {
                page: page,
                search: search,
                category_id: category,
                in_stock_only: showInStockOnly,
                with_images: true
            },
            success: function(response) {
                products = response.data;
                renderProducts();
                renderPagination(response);
                updateProductsCount(response.total);
                showLoading(false);
            },
            error: function() {
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

        products.forEach(product => {
            const outOfStock = product.track_inventory && product.current_stock <= 0;
            const lowStock = product.track_inventory && product.current_stock <= product.minimum_stock;

            const productCard = `
                <div class="product-card bg-white border border-gray-200 relative ${outOfStock ? 'out-of-stock' : ''}"
                     onclick="${outOfStock ? '' : `addToCart(${product.id})`}">

                    <!-- Product Image -->
                    <div class="product-image">
                        ${product.image ?
                            `<img src="/storage/${product.thumbnail || product.image}"
                                  alt="${product.product_name}"
                                  class="w-full h-full object-cover">` :
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
                                ${product.track_inventory ? product.current_stock : '∞'}
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
        if (!product) return;

        // Check stock
        if (product.track_inventory && product.current_stock < quantity) {
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
        const completeBtn = $('#completeSaleBtn');

        cartItems.empty();

        if (cart.length === 0) {
            emptyCart.removeClass('hidden');
            cartTotals.addClass('hidden');
            completeBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            return;
        }

        emptyCart.addClass('hidden');
        cartTotals.removeClass('hidden');
        completeBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');

        let subtotal = 0;

        cart.forEach((item, index) => {
            subtotal += item.total;

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

        // Calculate totals
        const discount = parseFloat($('#quickDiscount').val()) || 0;
        const discountAmount = discount > 100 ? discount : (subtotal * discount / 100);
        const tax = (subtotal - discountAmount) * 0.16; // 16% VAT
        const total = subtotal + tax - discountAmount;

        // Update display
        $('#cartSubtotal').text(`KES ${subtotal.toFixed(2)}`);
        $('#cartDiscount').text(`-KES ${discountAmount.toFixed(2)}`);
        $('#cartTax').text(`KES ${tax.toFixed(2)}`);
        $('#cartTotal').text(`KES ${total.toFixed(2)}`);

        // Update payment modals
        $('#mpesaAmount').text(`KES ${total.toFixed(2)}`);
        $('#cashAmount').text(`KES ${total.toFixed(2)}`);
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

    // Discount Functions
    function applyQuickDiscount() {
        const discount = $('#quickDiscount').val();
        if (discount && !isNaN(discount) && discount > 0) {
            updateCart();
            toastr.success('Discount applied');
        }
    }

    function setQuickDiscount(percent) {
        $('#quickDiscount').val(percent);
        updateCart();
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
            url: '{{ route("api.customers.search") }}',
            method: 'GET',
            data: {
                phone: phone,
                type: customerType
            },
            success: function(response) {
                if (response.customers && response.customers.length > 0) {
                    const customer = response.customers[0];
                    showCustomerInfo(customer);
                } else {
                    $('#customerInfo').addClass('hidden');
                }
            }
        });
    }

    function showCustomerInfo(customer) {
        $('#customerName').text(customer.name || 'Customer');
        $('#customerEmail').text(customer.email || 'No email');
        $('#customerInfo').removeClass('hidden');
        $('#mpesaTargetPhone').text(customer.phone);
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

    // Payment Functions
    function processMpesaPrompt() {
        if (cart.length === 0) {
            toastr.error('Cart is empty');
            return;
        }

        const phone = $('#customerPhone').val().trim();
        if (!phone || !phone.match(/^(07|01)\d{8}$/)) {
            toastr.error('Please enter a valid phone number for M-Pesa');
            $('#customerPhone').focus();
            return;
        }

        $('#mpesaTargetPhone').text(phone);
        $('#mpesaModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
        resetMpesaModal();
    }

    function showDirectMpesa() {
        processMpesaPrompt();
        setTimeout(() => toggleManualMpesa(), 100);
    }

    function toggleManualMpesa() {
        $('#mpesaStatus').addClass('hidden');
        $('#manualMpesa').toggleClass('hidden');
        $('#sendPromptBtn').toggleClass('hidden');
        $('#confirmManualBtn').toggleClass('hidden');
        $('#manualToggleBtn').toggleClass('hidden');

        if (!$('#manualMpesa').hasClass('hidden')) {
            $('#manualMpesaPhone').val($('#customerPhone').val());
            $('#mpesaReceipt').focus();
        }
    }

    function sendMpesaPrompt() {
        const phone = $('#customerPhone').val().trim();
        const amount = parseFloat($('#cartTotal').text().replace('KES ', ''));

        $('#mpesaStatus').removeClass('hidden');
        $('#sendPromptBtn').prop('disabled', true);
        $('#mpesaStatusMessage').text('Sending prompt to ' + phone);

        // Simulate M-Pesa API call
        setTimeout(() => {
            $('#mpesaStatusMessage').text('Prompt sent! Waiting for customer confirmation...');
            startMpesaCheck();
        }, 2000);
    }

    function startMpesaCheck() {
        let attempts = 0;
        const maxAttempts = 30; // 30 * 2 seconds = 1 minute timeout

        mpesaCheckInterval = setInterval(() => {
            attempts++;

            if (attempts >= maxAttempts) {
                clearInterval(mpesaCheckInterval);
                $('#mpesaStatusMessage').html('<span class="text-red-600">Payment timeout. Please try again.</span>');
                $('#sendPromptBtn').prop('disabled', false);
                return;
            }

            // In real implementation, check with your M-Pesa API
            $('#mpesaStatusMessage').text(`Checking payment... (${attempts}/${maxAttempts})`);

            // Simulate successful payment after 3 checks
            if (attempts === 3) {
                clearInterval(mpesaCheckInterval);
                completeMpesaPayment('RBX' + Math.random().toString(36).substr(2, 9).toUpperCase());
            }
        }, 2000);
    }

    function completeMpesaPayment(receiptNumber) {
        $('#mpesaStatusMessage').html('<span class="text-green-600">Payment confirmed! Receipt: ' + receiptNumber + '</span>');

        setTimeout(() => {
            processPayment('mpesa', {
                receipt_number: receiptNumber,
                phone: $('#customerPhone').val(),
                amount: parseFloat($('#cartTotal').text().replace('KES ', ''))
            });
        }, 1500);
    }

    function confirmManualMpesa() {
        const receipt = $('#mpesaReceipt').val().trim();
        const phone = $('#manualMpesaPhone').val().trim();

        if (!receipt) {
            toastr.error('Please enter M-Pesa receipt number');
            $('#mpesaReceipt').focus();
            return;
        }

        if (!phone || !phone.match(/^(07|01)\d{8}$/)) {
            toastr.error('Please enter valid phone number');
            $('#manualMpesaPhone').focus();
            return;
        }

        $('#confirmManualBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        setTimeout(() => {
            processPayment('mpesa', {
                receipt_number: receipt,
                transaction_id: $('#mpesaTransactionId').val(),
                phone: phone,
                amount: parseFloat($('#cartTotal').text().replace('KES ', ''))
            });
        }, 1000);
    }

    function showCashPayment() {
        if (cart.length === 0) {
            toastr.error('Cart is empty');
            return;
        }

        $('#cashModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
        $('#cashReceived').val('');
        calculateCashChange();
        $('#cashReceived').focus();
    }

    function calculateCashChange() {
        const total = parseFloat($('#cashAmount').text().replace('KES ', '')) || 0;
        const received = parseFloat($('#cashReceived').val()) || 0;
        const change = received - total;

        $('#cashChange').text(`KES ${change.toFixed(2)}`);

        if (change < 0) {
            $('#cashChange').addClass('text-red-600').removeClass('text-green-600');
        } else {
            $('#cashChange').addClass('text-green-600').removeClass('text-red-600');
        }
    }

    function setCashAmount(amount) {
        $('#cashReceived').val(amount);
        calculateCashChange();
    }

    function processCashPayment() {
        const received = parseFloat($('#cashReceived').val()) || 0;
        const total = parseFloat($('#cashAmount').text().replace('KES ', '')) || 0;

        if (received < total) {
            toastr.error('Amount received is less than total amount');
            return;
        }

        const change = received - total;

        processPayment('cash', {
            amount_received: received,
            change: change
        });
    }

    // Main Payment Processor
    function processPayment(method, details) {
        const customerPhone = $('#customerPhone').val().trim();
        const customerType = $('#customerType').val();
        const customerName = $('#customerName').text();

        const saleData = {
            business_section_id: {{ auth()->user()->business_section_id ?? 1 }},
            shop_id: {{ auth()->user()->shop_id ?? 1 }},
            sale_type: 'pos',
            channel: 'cafeteria',
            customer_phone: customerPhone,
            customer_type: customerType === 'walk_in' ? null : customerType,
            customer_name: customerName || null,
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price
            })),
            payment_method: method,
            [method === 'mpesa' ? 'mpesa_receipt' : '']: details.receipt_number,
            [method === 'mpesa' ? 'transaction_id' : '']: details.transaction_id,
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("sales.store") }}',
            method: 'POST',
            data: saleData,
            success: function(response) {
                currentInvoice = response.invoice_number;
                currentReceipt = response;

                // Close payment modal
                closeAllModals();

                // Show receipt
                showReceipt(response, method, details);

                // Reset cart
                cart = [];
                updateCart();
                clearCustomer();
                $('#quickDiscount').val('');

                // Reload products to update stock
                loadProducts();
                loadTodayStats();
            },
            error: function(xhr) {
                toastr.error('Failed to process sale: ' + (xhr.responseJSON?.error || 'Unknown error'));
                $('#confirmManualBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm Payment');
            }
        });
    }

    function showReceipt(sale, paymentMethod, paymentDetails) {
        $('#receiptInvoice').text(sale.invoice_number);
        $('#receiptDate').text(new Date(sale.created_at).toLocaleString());
        $('#receiptCustomer').text(sale.customer_name || 'Walk-in Customer');
        $('#receiptPayment').text(paymentMethod.toUpperCase());
        $('#receiptTotal').text('KES ' + parseFloat(sale.total_amount).toFixed(2));
        $('#receiptShop').text(sale.shop?.shop_name || 'Cafeteria');

        $('#receiptModal').removeClass('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function completeSale() {
        // Show payment options
        processMpesaPrompt();
    }

    // Utility Functions
    function newSale() {
        closeAllModals();
        cart = [];
        updateCart();
        clearCustomer();
        $('#quickDiscount').val('');
        $('#productSearch').val('').focus();
        toastr.info('New sale started');
    }

    function printReceipt() {
        if (!currentInvoice) return;

        window.open(`/sales/${currentInvoice}/print-receipt`, '_blank');
        toastr.success('Opening receipt for printing...');
    }

    function emailReceipt() {
        if (!currentInvoice) {
            toastr.error('No receipt available');
            return;
        }

        $.ajax({
            url: `/sales/${currentInvoice}/email-receipt`,
            method: 'GET',
            success: function() {
                toastr.success('Receipt sent to customer email');
            },
            error: function() {
                toastr.error('Failed to send email');
            }
        });
    }

    function closeAllModals() {
        $('.modal').addClass('hidden');
        document.body.classList.remove('overflow-hidden');

        // Clear M-Pesa interval
        if (mpesaCheckInterval) {
            clearInterval(mpesaCheckInterval);
            mpesaCheckInterval = null;
        }

        // Reset M-Pesa modal
        resetMpesaModal();
    }

    function resetMpesaModal() {
        $('#mpesaStatus').addClass('hidden');
        $('#manualMpesa').addClass('hidden');
        $('#sendPromptBtn').removeClass('hidden').prop('disabled', false);
        $('#confirmManualBtn').addClass('hidden');
        $('#manualToggleBtn').removeClass('hidden');
        $('#mpesaReceipt').val('');
        $('#mpesaTransactionId').val('');
        $('#manualMpesaPhone').val('');
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

    function loadTodayStats() {
        $.ajax({
            url: '{{ route("api.sales.today-stats") }}',
            method: 'GET',
            success: function(stats) {
                $('#todaySales').text('KES ' + (stats.total_sales || 0).toFixed(2));
                $('#todayTransactions').text(stats.transaction_count || 0);
                $('#todayPending').text(stats.pending_orders || 0);
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
