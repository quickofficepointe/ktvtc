{{-- resources/views/public/cafeteria/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Order Food Online - KTVTC Cafeteria')
@section('meta_description', 'Order delicious meals online from KTVTC Cafeteria. Pickup or free delivery to KTVTC/Kenswed locations.')
@section('meta_keywords', 'KTVTC cafeteria, order food online, campus food delivery, Ngong food delivery')

@section('styles')
<style>
    .cafeteria-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(185, 28, 28, 0.15); }
    .product-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(185, 28, 28, 0.1); }
    .location-option:hover { background-color: rgba(185, 28, 28, 0.05); }
    .location-option input:checked + label { border-color: #B91C1C !important; background-color: rgba(185, 28, 28, 0.1) !important; }
    .order-type-btn.active { border-color: #B91C1C !important; background-color: rgba(185, 28, 28, 0.1) !important; }
    .category-chip.active { background-color: #B91C1C !important; color: white !important; }
    .cart-item { animation: slideIn 0.3s ease; }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Order Food Online</h1>
            <p class="text-lg text-gray-600">Choose your cafeteria and place your order</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Cafeteria & Menu -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cafeteria Selection -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Select Cafeteria</h2>
                    @if($cafeterias->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-utensils text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-600">No cafeterias available at the moment</p>
                            <p class="text-sm text-gray-500 mt-1">Please check back later</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="cafeteriasGrid">
                            @foreach($cafeterias as $cafeteria)
                            <div class="cafeteria-card p-4 border-2 border-gray-200 rounded-xl cursor-pointer transition"
                                 data-cafeteria-id="{{ $cafeteria->id }}">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-utensils text-red-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">{{ $cafeteria->shop_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $cafeteria->location ?? 'Main Campus' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Menu Categories & Products -->
                <div id="menuSection" class="hidden">
                    <!-- Categories will be loaded here -->
                    <div id="categoriesSection" class="bg-white rounded-xl shadow-sm p-6 mb-6 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Categories</h2>
                        <div class="flex flex-wrap gap-2" id="categoriesList"></div>
                    </div>

                    <!-- Products -->
                    <div id="productsSection" class="bg-white rounded-xl shadow-sm p-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                            <h2 class="text-2xl font-bold text-gray-900">Menu</h2>
                            <div class="relative w-full md:w-64">
                                <input type="text" id="productSearch"
                                       placeholder="Search menu items..."
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="productsGrid"></div>

                        <!-- Loading State -->
                        <div id="productsLoading" class="text-center py-12 hidden">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                            <p class="mt-2 text-gray-600">Loading menu...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="productsEmpty" class="text-center py-12 hidden">
                            <i class="fas fa-utensils text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-600">No items available</p>
                            <p class="text-sm text-gray-500">Check back later or select another category</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Cart & Order -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Cart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Your Order</h2>
                        <button id="clearCartBtn"
                                class="text-sm text-red-600 hover:text-red-800 hidden flex items-center">
                            <i class="fas fa-trash mr-1"></i> Clear
                        </button>
                    </div>

                    <div id="cartContainer">
                        <div id="emptyCart" class="text-center py-8">
                            <i class="fas fa-shopping-basket text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-600">Your cart is empty</p>
                            <p class="text-sm text-gray-500 mt-1">Select items from the menu</p>
                        </div>

                        <div id="cartItems" class="space-y-3 hidden"></div>

                        <div id="cartTotals" class="hidden mt-6 pt-6 border-t border-gray-200">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span id="cartSubtotal" class="font-medium">KES 0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Service Fee</span>
                                    <span id="serviceFee" class="font-medium">KES 0.00</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg pt-3 border-t border-gray-200">
                                    <span class="text-gray-900">Total</span>
                                    <span id="cartTotal" class="text-red-600">KES 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div id="orderInfoSection" class="bg-white rounded-xl shadow-sm p-6 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Order Details</h2>

                    <form id="orderForm">
                        @csrf
                        <input type="hidden" name="shop_id" id="shopId">

                        <div class="space-y-6">
                            <!-- Order Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">How would you like to receive your order?</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button"
                                            class="order-type-btn py-3 px-4 border-2 border-gray-200 rounded-lg hover:bg-gray-50 transition"
                                            data-type="pickup">
                                        <i class="fas fa-store mr-2"></i> Pickup
                                        <div class="text-xs text-gray-500 mt-1">Collect at cafeteria</div>
                                    </button>
                                    <button type="button"
                                            class="order-type-btn py-3 px-4 border-2 border-gray-200 rounded-lg hover:bg-gray-50 transition"
                                            data-type="delivery">
                                        <i class="fas fa-motorcycle mr-2"></i> Delivery
                                        <div class="text-xs text-gray-500 mt-1">We bring it to you</div>
                                    </button>
                                </div>
                            </div>

                            <!-- Pickup Options -->
                            <div id="pickupOptions" class="space-y-4 hidden">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Where would you like to pick up?</label>
                                    <div class="space-y-2">
                                        @foreach($locations as $key => $location)
                                            @if($location['type'] === 'pickup')
                                            <div class="location-option">
                                                <input type="radio"
                                                       id="location_{{ $key }}"
                                                       name="location_id"
                                                       value="{{ $key }}"
                                                       class="hidden peer"
                                                       @if($loop->first) checked @endif>
                                                <label for="location_{{ $key }}"
                                                       class="block p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-red-600 peer-checked:bg-red-50 transition">
                                                    <div class="font-medium text-gray-900">{{ $location['name'] }}</div>
                                                    <div class="text-sm text-gray-500 mt-1">{{ $location['instructions'] }}</div>
                                                    <div class="text-xs text-gray-400 mt-2">
                                                        <i class="fas fa-clock mr-1"></i> {{ $location['available_times'] }}
                                                    </div>
                                                </label>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Options -->
                            <div id="deliveryOptions" class="space-y-4 hidden">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Where should we deliver?</label>
                                    <div class="space-y-2 max-h-64 overflow-y-auto p-1">
                                        @foreach($locations as $key => $location)
                                            @if($location['type'] === 'delivery')
                                            <div class="location-option">
                                                <input type="radio"
                                                       id="location_{{ $key }}"
                                                       name="location_id"
                                                       value="{{ $key }}"
                                                       class="hidden peer"
                                                       @if($key === 'delivery_staff_room') checked @endif>
                                                <label for="location_{{ $key }}"
                                                       class="block p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-red-600 peer-checked:bg-red-50 transition">
                                                    <div class="font-medium text-gray-900">{{ $location['name'] }}</div>
                                                    <div class="text-sm text-gray-500 mt-1">{{ $location['instructions'] }}</div>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        <i class="fas fa-clock mr-1"></i> {{ $location['available_times'] }}
                                                    </div>
                                                    @if($key === 'other_specified')
                                                        <div class="mt-3 hidden" id="otherLocationDetails">
                                                            <textarea name="location_details"
                                                                      placeholder="Please specify the exact location..."
                                                                      rows="2"
                                                                      class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"></textarea>
                                                        </div>
                                                    @endif
                                                </label>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Time Selection -->
                            <div id="timeSelection">
                                <label class="block text-sm font-medium text-gray-700 mb-2">When would you like it?</label>
                                <select name="pickup_time"
                                        id="pickupTime"
                                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                                    <option value="asap">ASAP (Ready in 20-30 minutes)</option>
                                    <option value="30">30 minutes from now</option>
                                    <option value="45">45 minutes from now</option>
                                    <option value="60">1 hour from now</option>
                                    <option value="custom">Specific time (enter below)</option>
                                </select>

                                <div id="customTimeSection" class="mt-3 hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Enter specific time</label>
                                    <input type="time"
                                           name="custom_time"
                                           min="07:00"
                                           max="18:00"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                    <p class="text-xs text-gray-500 mt-1">Cafeteria hours: 7:00 AM - 6:00 PM</p>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="space-y-4">
                                <h3 class="font-medium text-gray-900 border-b pb-2">Your Information</h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                    <input type="text"
                                           name="customer_name"
                                           id="customerName"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"
                                           placeholder="Your name"
                                           required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                    <input type="tel"
                                           name="customer_phone"
                                           id="customerPhone"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"
                                           placeholder="0712 345 678"
                                           pattern="^(07|01)\d{8}$"
                                           required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                    <input type="email"
                                           name="customer_email"
                                           id="customerEmail"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"
                                           placeholder="email@example.com">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Special Instructions (Optional)
                                    </label>
                                    <textarea name="special_instructions"
                                              id="orderNotes"
                                              rows="3"
                                              class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"
                                              placeholder="Any allergies or special requests?"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Checkout Button -->
                <div id="checkoutSection" class="hidden">
                    <div class="bg-red-600 text-white rounded-xl p-6 mb-4">
                        <h3 class="text-xl font-bold mb-2">Ready to Order?</h3>
                        <p class="opacity-90">Complete your order with secure payment</p>
                    </div>

                    <button id="checkoutBtn"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-4 rounded-xl text-lg transition shadow-lg">
                        <i class="fas fa-lock mr-2"></i> Proceed to Payment
                    </button>

                    <p class="text-center text-sm text-gray-500 mt-3">
                        <i class="fas fa-shield-alt mr-1"></i> Secure payment powered by KCB M-Pesa
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 overflow-auto hidden" aria-hidden="true">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl">
            <div class="bg-green-600 text-white p-6 rounded-t-xl">
                <h3 class="text-2xl font-bold">Order Confirmed!</h3>
            </div>

            <div class="p-6 text-center">
                <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                <h4 class="text-xl font-bold text-gray-900 mb-2">Thank You!</h4>
                <p class="text-gray-600 mb-4">Your order has been received and is being prepared.</p>

                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="font-bold text-lg text-red-600 mb-1" id="confirmationInvoice"></div>
                    <div class="text-sm text-gray-600">Order Number</div>
                </div>

                <div class="space-y-2 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Est. Ready Time:</span>
                        <span class="font-medium" id="confirmationTime"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Location:</span>
                        <span class="font-medium" id="confirmationLocation"></span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-3">
                        <span>Total Paid:</span>
                        <span class="text-red-600" id="confirmationTotal"></span>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 flex justify-between">
                <button onclick="printConfirmation()"
                        class="px-6 py-3 border border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition">
                    <i class="fas fa-print mr-2"></i> Print Receipt
                </button>
                <button onclick="newOrder()"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-plus mr-2"></i> New Order
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    console.log('Online ordering script loaded');

    // Global variables
    let cart = [];
    let selectedCafeteria = null;
    let selectedOrderType = 'pickup';
    let allProducts = [];

    $(document).ready(function() {
        console.log('Document ready');
        console.log('Cafeteria cards found:', $('.cafeteria-card').length);

        // Initialize cafeteria selection
        initCafeteriaSelection();

        // Initialize other event listeners
        setupEventListeners();

        // Initialize with first cafeteria if only one exists
        @if($cafeterias->count() === 1)
            console.log('Auto-selecting single cafeteria');
            const firstCafeteria = $('.cafeteria-card').first();
            if (firstCafeteria.length) {
                const cafeteriaId = firstCafeteria.data('cafeteria-id');
                selectCafeteria(cafeteriaId);
            }
        @endif
    });

    function initCafeteriaSelection() {
        console.log('Initializing cafeteria selection...');

        // Remove any existing click handlers first
        $('.cafeteria-card').off('click');

        // Add click handler to cafeteria cards
        $('.cafeteria-card').on('click', function() {
            console.log('Cafeteria card clicked');
            const cafeteriaId = $(this).data('cafeteria-id');
            console.log('Cafeteria ID:', cafeteriaId);
            selectCafeteria(cafeteriaId);
        });

        console.log('Cafeteria click handlers attached');
    }

    function selectCafeteria(cafeteriaId) {
        console.log('selectCafeteria called with ID:', cafeteriaId);

        if (!cafeteriaId) {
            console.error('No cafeteria ID provided');
            return;
        }

        selectedCafeteria = cafeteriaId;

        // Visual feedback - highlight selected cafeteria
        $('.cafeteria-card').removeClass('border-red-600 bg-red-50');
        $(`.cafeteria-card[data-cafeteria-id="${cafeteriaId}"]`)
            .addClass('border-red-600 bg-red-50');

        console.log('Cafeteria selected:', cafeteriaId);

        // Show menu section
        $('#menuSection').removeClass('hidden');

        // Set shop ID in form
        $('#shopId').val(cafeteriaId);

        // Load the menu
        loadMenu(cafeteriaId);
    }

    async function loadMenu(cafeteriaId) {
        console.log('Loading menu for cafeteria:', cafeteriaId);

        // Show loading state
        $('#productsLoading').removeClass('hidden');
        $('#productsGrid').addClass('hidden');
        $('#categoriesSection').addClass('hidden');
        $('#productsSection').addClass('hidden');

        try {
            const response = await $.ajax({
                url: '{{ route("public.cafeteria.getMenu") }}',
                method: 'GET',
                data: { shop_id: cafeteriaId },
                timeout: 10000 // 10 second timeout
            });

            console.log('Menu loaded successfully:', response);

            if (response.success) {
                allProducts = response.products || [];

                // Render categories if available
                if (response.categories && response.categories.length > 0) {
                    renderCategories(response.categories);
                    $('#categoriesSection').removeClass('hidden');
                }

                // Render products
                renderProducts(allProducts);

                // Show products section
                $('#productsLoading').addClass('hidden');
                $('#productsSection').removeClass('hidden');
                $('#productsGrid').removeClass('hidden');

                // Reset cart for new cafeteria
                cart = [];
                updateCart();

                console.log('Menu rendering complete. Products:', allProducts.length);

                // Show toast notification
                showToast(`Menu loaded for ${response.shop.shop_name}`, 'success');
            } else {
                throw new Error(response.error || 'Failed to load menu');
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            showToast('Failed to load menu. Please try again.', 'error');
            $('#productsLoading').addClass('hidden');
            $('#productsEmpty').removeClass('hidden');
        }
    }

    function renderCategories(categories) {
        const container = $('#categoriesList');
        container.empty();

        // Add "All" category
        container.append(`
            <button class="category-chip px-4 py-2 bg-red-600 text-white rounded-full transition active"
                    data-category-id="all">
                All Items
            </button>
        `);

        categories.forEach(category => {
            container.append(`
                <button class="category-chip px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition"
                        data-category-id="${category.id}">
                    ${category.category_name}
                </button>
            `);
        });

        // Add click handlers for category chips
        $('.category-chip').off('click').on('click', function() {
            const categoryId = $(this).data('category-id');
            console.log('Category selected:', categoryId);
            filterProductsByCategory(categoryId);
        });
    }

    function renderProducts(products) {
        const container = $('#productsGrid');
        container.empty();

        if (!products || products.length === 0) {
            $('#productsEmpty').removeClass('hidden');
            return;
        }

        $('#productsEmpty').addClass('hidden');

        products.forEach(product => {
            const productCard = `
                <div class="product-card bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition">
                    <div class="relative">
                        <div class="h-40 bg-gray-100 flex items-center justify-center">
                            ${product.image ?
                                `<img src="/storage/${product.image}" alt="${product.product_name}" class="h-full w-full object-cover">` :
                                `<i class="fas fa-utensils text-gray-300 text-4xl"></i>`
                            }
                            ${product.is_featured ?
                                `<span class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded">Featured</span>` : ''
                            }
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 mb-1">${product.product_name}</h3>
                        <p class="text-sm text-gray-600 mb-2 truncate">${product.description || ''}</p>

                        <div class="flex justify-between items-center">
                            <div class="text-lg font-bold text-red-600">
                                KES ${parseFloat(product.selling_price).toFixed(2)}
                            </div>

                            <button class="add-to-cart-btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition"
                                    data-product-id="${product.id}">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.append(productCard);
        });

        // Add click handlers for add to cart buttons
        $('.add-to-cart-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            const productId = $(this).data('product-id');
            console.log('Add to cart clicked for product:', productId);
            addToCart(productId, 1);
        });
    }

    function addToCart(productId, quantity = 1) {
        const product = allProducts.find(p => p.id == productId);
        if (!product) {
            console.error('Product not found:', productId);
            return;
        }

        // Check if already in cart
        const existingIndex = cart.findIndex(item => item.product_id == productId);

        if (existingIndex > -1) {
            cart[existingIndex].quantity += quantity;
        } else {
            cart.push({
                product_id: product.id,
                product_name: product.product_name,
                product_code: product.product_code,
                unit_price: parseFloat(product.selling_price),
                quantity: quantity,
                image: product.image
            });
        }

        updateCart();
        showOrderInfo();

        // Show notification
        showToast(`${product.product_name} added to cart`, 'success');
    }

    function updateCart() {
        const cartItems = $('#cartItems');
        const emptyCart = $('#emptyCart');
        const cartTotals = $('#cartTotals');
        const clearBtn = $('#clearCartBtn');

        cartItems.empty();

        if (cart.length === 0) {
            emptyCart.removeClass('hidden');
            cartTotals.addClass('hidden');
            clearBtn.addClass('hidden');
            $('#checkoutSection').addClass('hidden');
            return;
        }

        emptyCart.addClass('hidden');
        cartTotals.removeClass('hidden');
        clearBtn.removeClass('hidden');

        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.unit_price * item.quantity;
            subtotal += itemTotal;

            const cartItem = `
                <div class="cart-item flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-200 mr-3">
                            ${item.image ?
                                `<img src="/storage/${item.image}" class="w-full h-full object-cover">` :
                                `<i class="fas fa-utensils text-gray-400 w-full h-full flex items-center justify-center"></i>`
                            }
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">${item.product_name}</div>
                            <div class="text-sm text-gray-500">KES ${item.unit_price.toFixed(2)}</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${index}, ${item.quantity - 1})"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center font-medium">${item.quantity}</span>
                        <button onclick="updateQuantity(${index}, ${item.quantity + 1})"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>

                    <div class="font-medium ml-2">
                        KES ${itemTotal.toFixed(2)}
                    </div>
                </div>
            `;

            cartItems.append(cartItem);
        });

        const deliveryFee = selectedOrderType === 'delivery' ? 50 : 0;
        const serviceFee = 0;
        const total = subtotal + deliveryFee + serviceFee;

        // Update cart totals to show delivery fee
        let totalsHTML = `
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span id="cartSubtotal" class="font-medium">KES ${subtotal.toFixed(2)}</span>
            </div>
        `;

        if (deliveryFee > 0) {
            totalsHTML += `
                <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span id="deliveryFee" class="font-medium">KES ${deliveryFee.toFixed(2)}</span>
                </div>
            `;
        }

        totalsHTML += `
            <div class="flex justify-between font-bold text-lg pt-3 border-t border-gray-200">
                <span class="text-gray-900">Total</span>
                <span id="cartTotal" class="text-red-600">KES ${total.toFixed(2)}</span>
            </div>
        `;

        $('#cartTotals').html(totalsHTML);

        // Show checkout if cart has items
        if (cart.length > 0) {
            $('#checkoutSection').removeClass('hidden');
        }
    }

    function updateQuantity(index, newQuantity) {
        if (newQuantity < 1) {
            cart.splice(index, 1);
        } else {
            cart[index].quantity = newQuantity;
        }

        updateCart();
    }

    function clearCart() {
        if (confirm('Clear all items from cart?')) {
            cart = [];
            updateCart();
            showToast('Cart cleared', 'info');
        }
    }

    function showOrderInfo() {
        $('#orderInfoSection').removeClass('hidden');

        // Default to pickup
        selectedOrderType = 'pickup';
        updateOrderType();

        // Setup order type buttons
        $('.order-type-btn').off('click').on('click', function() {
            selectedOrderType = $(this).data('type');
            updateOrderType();
        });
    }

    function updateOrderType() {
        // Update button styles
        $('.order-type-btn').removeClass('active');
        $(`.order-type-btn[data-type="${selectedOrderType}"]`).addClass('active');

        // Show appropriate options
        if (selectedOrderType === 'pickup') {
            $('#pickupOptions').removeClass('hidden');
            $('#deliveryOptions').addClass('hidden');
            $('input[name="location_id"][value="pickup_cafeteria"]').prop('checked', true).trigger('change');
        } else {
            $('#pickupOptions').addClass('hidden');
            $('#deliveryOptions').removeClass('hidden');
            $('input[name="location_id"][value="delivery_staff_room"]').prop('checked', true).trigger('change');
        }

        // Update cart totals with delivery fee
        updateCart();
    }

    // Initialize event listeners
    function setupEventListeners() {
        // Checkout button
        $('#checkoutBtn').off('click').on('click', validateAndCheckout);

        // Clear cart
        $('#clearCartBtn').off('click').on('click', clearCart);

        // Product search
        $('#productSearch').off('input').on('input', debounce(filterProducts, 300));

        // Location radio change
        $('input[name="location_id"]').off('change').on('change', function() {
            const selectedLocation = $(this).val();
            if (selectedLocation === 'other_specified') {
                $('#otherLocationDetails').removeClass('hidden');
            } else {
                $('#otherLocationDetails').addClass('hidden');
            }
        });

        // Pickup time change
        $('#pickupTime').off('change').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customTimeSection').removeClass('hidden');
            } else {
                $('#customTimeSection').addClass('hidden');
            }
        });
    }

    function filterProducts() {
        const searchTerm = $('#productSearch').val().toLowerCase();

        if (searchTerm) {
            const filtered = allProducts.filter(product =>
                product.product_name.toLowerCase().includes(searchTerm) ||
                (product.description && product.description.toLowerCase().includes(searchTerm))
            );
            renderProducts(filtered);
        } else {
            renderProducts(allProducts);
        }
    }

    function filterProductsByCategory(categoryId) {
        // Update active category
        $('.category-chip').removeClass('active bg-red-600 text-white');
        $(`.category-chip[data-category-id="${categoryId}"]`).addClass('active bg-red-600 text-white');

        if (categoryId === 'all') {
            renderProducts(allProducts);
            return;
        }

        const filtered = allProducts.filter(product =>
            product.category_id == categoryId
        );

        renderProducts(filtered);
    }

    // FORM VALIDATION AND CHECKOUT FUNCTIONS
    function validateOrderForm() {
        const name = $('#customerName').val().trim();
        const phone = $('#customerPhone').val().trim();
        const phoneRegex = /^(07|01)\d{8}$/;

        if (!name) {
            showToast('Please enter your name', 'error');
            $('#customerName').focus();
            return false;
        }

        if (!phone || !phoneRegex.test(phone.replace(/\s+/g, ''))) {
            showToast('Please enter a valid Kenyan phone number (e.g., 0712345678)', 'error');
            $('#customerPhone').focus();
            return false;
        }

        if (cart.length === 0) {
            showToast('Please add items to your cart before ordering', 'error');
            return false;
        }

        if (!selectedCafeteria) {
            showToast('Please select a cafeteria first', 'error');
            return false;
        }

        // Validate delivery location if delivery type
        if (selectedOrderType === 'delivery') {
            const selectedLocation = $('input[name="location_id"]:checked').val();
            if (selectedLocation === 'other_specified') {
                const locationDetails = $('textarea[name="location_details"]').val().trim();
                if (!locationDetails) {
                    showToast('Please specify delivery location details', 'error');
                    $('textarea[name="location_details"]').focus();
                    return false;
                }
                if (locationDetails.length < 10) {
                    showToast('Please provide more specific delivery location details (at least 10 characters)', 'error');
                    $('textarea[name="location_details"]').focus();
                    return false;
                }
            }
        }

        // Validate custom time if selected
        if ($('#pickupTime').val() === 'custom') {
            const customTime = $('input[name="custom_time"]').val();
            if (!customTime) {
                showToast('Please select a specific time for your order', 'error');
                $('input[name="custom_time"]').focus();
                return false;
            }

            // Validate time is within cafeteria hours (7 AM - 6 PM)
            const selectedTime = new Date();
            const [hours, minutes] = customTime.split(':');
            selectedTime.setHours(parseInt(hours), parseInt(minutes), 0);

            const now = new Date();
            const minTime = new Date(now);
            minTime.setHours(7, 0, 0, 0);

            const maxTime = new Date(now);
            maxTime.setHours(18, 0, 0, 0);

            if (selectedTime < minTime || selectedTime > maxTime) {
                showToast('Please select a time between 7:00 AM and 6:00 PM', 'error');
                $('input[name="custom_time"]').focus();
                return false;
            }
        }

        return true;
    }

    function validateAndCheckout() {
        // Validate form
        if (!validateOrderForm()) {
            return;
        }

        // Get CSRF token from meta tag
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        console.log('CSRF Token from meta:', csrfToken);

        // Calculate total with delivery fee
        const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        const deliveryFee = selectedOrderType === 'delivery' ? 50 : 0;
        const total = subtotal + deliveryFee;

        // Prepare order data
        const orderData = {
            _token: csrfToken,
            shop_id: selectedCafeteria,
            customer_name: $('#customerName').val(),
            customer_phone: $('#customerPhone').val(),
            customer_email: $('#customerEmail').val() || '',
            order_type: selectedOrderType,
            location_id: $('input[name="location_id"]:checked').val(),
            location_details: $('input[name="location_id"]:checked').val() === 'other_specified'
                ? $('textarea[name="location_details"]').val()
                : '',
            pickup_time: $('#pickupTime').val(),
            custom_time: $('#pickupTime').val() === 'custom' ? $('input[name="custom_time"]').val() : '',
            special_instructions: $('#orderNotes').val(),
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            })),
            delivery_fee: deliveryFee,
            total_amount: total
        };

        console.log('Placing order with data:', orderData);

        // Disable checkout button
        $('#checkoutBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

        placeOrder(orderData);
    }

    async function placeOrder(orderData) {
        try {
            const response = await $.ajax({
                url: '{{ route("public.cafeteria.placeOrder") }}',
                method: 'POST',
                data: orderData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                timeout: 30000 // 30 second timeout
            });

            console.log('Order response:', response);

            if (response.success) {
                // Show payment pending message
                showToast('Payment request sent to your phone. Please complete M-Pesa payment.', 'info');

                // Start polling for payment status
                const checkoutRequestId = response.order.checkout_request_id;
                const tempSaleId = response.order.temporary_id;

                if (checkoutRequestId && tempSaleId) {
                    startPaymentPolling(checkoutRequestId, tempSaleId);
                } else {
                    // Fallback to old confirmation
                    showConfirmation(response.order);
                }
            } else {
                throw new Error(response.error || 'Failed to place order');
            }
        } catch (error) {
            console.error('Order error:', error);
            console.error('Error status:', error.status);
            console.error('Error response:', error.responseJSON);

            let errorMessage = 'Failed to place order. Please try again.';

            if (error.status === 419) {
                errorMessage = 'Session expired. Please refresh the page and try again.';
            } else if (error.responseJSON?.error) {
                errorMessage = error.responseJSON.error;
            } else if (error.responseJSON?.errors) {
                const errors = Object.values(error.responseJSON.errors).flat();
                errorMessage = errors.join(', ');
            }

            showToast(errorMessage, 'error');
            $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-lock mr-2"></i> Proceed to Payment');
        }
    }

    function startPaymentPolling(checkoutRequestId, tempSaleId) {
        let pollCount = 0;
        const maxPolls = 60; // Poll for 5 minutes (60 * 5 seconds)

        // Show polling status
        $('#checkoutBtn').html('<i class="fas fa-spinner fa-spin mr-2"></i> Waiting for Payment...');

        const pollInterval = setInterval(async () => {
            pollCount++;

            if (pollCount > maxPolls) {
                clearInterval(pollInterval);
                showToast('Payment timeout. Please try again.', 'error');
                $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-lock mr-2"></i> Try Again');
                return;
            }

            try {
                const response = await $.ajax({
                    url: '{{ route("public.cafeteria.checkPaymentStatus") }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        checkout_request_id: checkoutRequestId,
                        temp_sale_id: tempSaleId
                    },
                    timeout: 5000
                });

                console.log('Polling response:', response);

                if (response.status === 'completed') {
                    clearInterval(pollInterval);

                    // Update confirmation modal with final order details
                    if (response.order) {
                        $('#confirmationInvoice').text(response.order.invoice_number || 'Online Order');
                        $('#confirmationTime').text(response.order.estimated_time || '20-30 minutes');
                        $('#confirmationLocation').text(response.order.pickup_location || 'Cafeteria');
                        $('#confirmationTotal').text(`KES ${response.order.total_amount || 0}`);

                        // Show success message
                        showToast('Payment confirmed! Order placed successfully.', 'success');

                        // Show confirmation modal after a brief delay
                        setTimeout(() => {
                            $('#confirmationModal').removeClass('hidden');
                        }, 1500);
                    } else {
                        showConfirmation({
                            invoice_number: 'Online Order',
                            estimated_time: '20-30 minutes',
                            pickup_location: 'Cafeteria',
                            total_amount: 'Pending'
                        });
                    }

                } else if (response.status === 'failed') {
                    clearInterval(pollInterval);
                    showToast('Payment failed. Please try again.', 'error');
                    $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-lock mr-2"></i> Try Again');
                }
                // Continue polling if still pending

            } catch (error) {
                console.error('Polling error:', error);
                // Continue polling on error
            }
        }, 5000); // Poll every 5 seconds
    }

    function showConfirmation(order) {
        $('#confirmationInvoice').text(order.invoice_number || 'Online Order');
        $('#confirmationTime').text(order.estimated_time || '20-30 minutes');
        $('#confirmationLocation').text(order.pickup_location || 'Cafeteria');
        $('#confirmationTotal').text(`KES ${order.total_amount || 0}`);

        $('#confirmationModal').removeClass('hidden');
    }

    function printConfirmation() {
        const printContent = `
            <html>
            <head>
                <title>Order Confirmation - KTVTC Cafeteria</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .invoice { font-size: 24px; font-weight: bold; color: #B91C1C; }
                    .details { margin: 20px 0; }
                    .details div { margin: 5px 0; }
                    .total { font-size: 18px; font-weight: bold; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>KTVTC Cafeteria</h2>
                    <p>Order Confirmation</p>
                </div>
                <div class="invoice">${$('#confirmationInvoice').text()}</div>
                <div class="details">
                    <div><strong>Estimated Time:</strong> ${$('#confirmationTime').text()}</div>
                    <div><strong>Location:</strong> ${$('#confirmationLocation').text()}</div>
                    <div><strong>Total:</strong> ${$('#confirmationTotal').text()}</div>
                </div>
                <p>Thank you for your order!</p>
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    function newOrder() {
        $('#confirmationModal').addClass('hidden');

        // Reset everything
        cart = [];
        updateCart();

        // Reset form
        $('#orderForm')[0].reset();
        $('#otherLocationDetails').addClass('hidden');
        $('#customTimeSection').addClass('hidden');

        // Reset UI
        $('#orderInfoSection').addClass('hidden');
        $('#checkoutSection').addClass('hidden');

        // Reset checkout button
        $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-lock mr-2"></i> Proceed to Payment');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toast = $(`
            <div class="fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white z-50 max-w-sm transform translate-x-full transition-transform duration-300">
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);

        // Set background color based on type
        const bgColor = type === 'success' ? 'bg-green-500' :
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        toast.addClass(bgColor);

        // Add to body
        $('body').append(toast);

        // Animate in
        setTimeout(() => {
            toast.css('transform', 'translateX(0)');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.css('transform', 'translateX(100%)');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
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
