@extends('layouts.app')

@section('title', 'Order Food Online - KTVTC Cafeteria')
@section('meta_description', 'Order delicious meals online from KTVTC Cafeteria. Pickup or delivery to KTVTC campus locations.')
@section('meta_keywords', 'KTVTC cafeteria, order food online, campus food delivery')

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

    /* Loading skeleton animation */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Simplified Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Cafeteria Online</h1>
            <p class="text-gray-600 mt-1">Order your favorite meals in seconds</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Cafeteria & Menu (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cafeteria Selection - Simplified -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Choose Cafeteria</h2>
                    @if($cafeterias->isEmpty())
                        <div class="text-center py-6">
                            <i class="fas fa-utensils text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500">No cafeterias available</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="cafeteriasGrid">
                            @foreach($cafeterias as $cafeteria)
                            <div class="cafeteria-card p-3 border-2 border-gray-200 rounded-xl cursor-pointer transition text-center"
                                 data-cafeteria-id="{{ $cafeteria->id }}">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-utensils text-red-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 text-sm">{{ $cafeteria->shop_name }}</h3>
                                <p class="text-xs text-gray-500">{{ $cafeteria->location ?? 'Campus' }}</p>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Menu Section (hidden until cafeteria selected) -->
                <div id="menuSection" class="hidden">
                    <!-- Categories -->
                    <div id="categoriesSection" class="bg-white rounded-xl shadow-sm p-5 mb-4 hidden">
                        <div class="flex flex-wrap gap-2" id="categoriesList"></div>
                    </div>

                    <!-- Products with Search -->
                    <div id="productsSection" class="bg-white rounded-xl shadow-sm p-5 hidden">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
                            <div class="relative w-48">
                                <input type="text" id="productSearch"
                                       placeholder="Search..."
                                       class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-sm"></i>
                            </div>
                        </div>

                        <!-- Loading Skeleton -->
                        <div id="productsLoading">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @for($i = 0; $i < 4; $i++)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="skeleton h-28 rounded-lg mb-3"></div>
                                    <div class="skeleton h-4 w-3/4 rounded mb-2"></div>
                                    <div class="skeleton h-3 w-full rounded mb-2"></div>
                                    <div class="flex justify-between items-center mt-2">
                                        <div class="skeleton h-5 w-16 rounded"></div>
                                        <div class="skeleton h-8 w-16 rounded"></div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden"></div>

                        <!-- Empty State -->
                        <div id="productsEmpty" class="text-center py-8 hidden">
                            <i class="fas fa-utensils text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500">No items available</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Cart & Order (1/3 width) -->
            <div class="lg:col-span-1 space-y-5">
                <!-- Cart Summary -->
                <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Your Order</h2>
                        <button id="clearCartBtn"
                                class="text-xs text-red-600 hover:text-red-800 hidden">
                            <i class="fas fa-trash mr-1"></i> Clear
                        </button>
                    </div>

                    <div id="cartContainer">
                        <div id="emptyCart" class="text-center py-6">
                            <i class="fas fa-shopping-basket text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">Cart is empty</p>
                        </div>

                        <div id="cartItems" class="space-y-2 hidden"></div>

                        <div id="cartTotals" class="hidden mt-4 pt-3 border-t border-gray-200">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span id="cartSubtotal" class="font-medium">KES 0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Delivery</span>
                                    <span id="deliveryFeeDisplay" class="font-medium">KES 0</span>
                                </div>
                                <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-200">
                                    <span class="text-gray-900">Total</span>
                                    <span id="cartTotal" class="text-red-600">KES 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simplified Order Type Toggle -->
                    <div id="orderTypeSection" class="hidden mt-4 pt-3 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <button type="button"
                                    class="order-type-btn py-2 text-sm font-medium border-2 border-gray-200 rounded-lg transition"
                                    data-type="pickup">
                                <i class="fas fa-store mr-1"></i> Pickup
                            </button>
                            <button type="button"
                                    class="order-type-btn py-2 text-sm font-medium border-2 border-gray-200 rounded-lg transition"
                                    data-type="delivery">
                                <i class="fas fa-motorcycle mr-1"></i> Delivery
                            </button>
                        </div>

                        <!-- Pickup Locations (simplified) -->
                        <div id="pickupOptions" class="space-y-2 hidden">
                            <label class="text-xs font-medium text-gray-700">Pickup Location</label>
                            @foreach($locations as $key => $location)
                                @if($location['type'] === 'pickup')
                                <div class="location-option">
                                    <input type="radio" id="pickup_{{ $key }}" name="location_id" value="{{ $key }}"
                                           class="hidden peer" @if($loop->first) checked @endif>
                                    <label for="pickup_{{ $key }}"
                                           class="block p-2 text-sm border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-red-600 peer-checked:bg-red-50">
                                        {{ $location['name'] }}
                                    </label>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Delivery Locations (simplified) -->
                        <div id="deliveryOptions" class="space-y-2 hidden">
                            <label class="text-xs font-medium text-gray-700">Delivery Location</label>
                            @foreach($locations as $key => $location)
                                @if($location['type'] === 'delivery')
                                <div class="location-option">
                                    <input type="radio" id="delivery_{{ $key }}" name="location_id" value="{{ $key }}"
                                           class="hidden peer" @if($loop->first) checked @endif>
                                    <label for="delivery_{{ $key }}"
                                           class="block p-2 text-sm border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-red-600 peer-checked:bg-red-50">
                                        {{ $location['name'] }}
                                    </label>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Other location details (hidden until selected) -->
                        <div id="otherLocationDetails" class="mt-2 hidden">
                            <textarea name="location_details" id="locationDetails"
                                      placeholder="Specify exact location..."
                                      rows="2"
                                      class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                        </div>

                        <!-- Name & Phone (simplified) -->
                        <div class="space-y-2 mt-3 pt-2 border-t border-gray-100">
                            <input type="text" id="customerName" placeholder="Your name"
                                   class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-red-500 outline-none">
                            <input type="tel" id="customerPhone" placeholder="Phone number"
                                   class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-red-500 outline-none">
                        </div>

                        <!-- Place Order Button -->
                        <button id="checkoutBtn"
                                class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg transition shadow-md">
                            <i class="fas fa-shopping-cart mr-2"></i> Place Order
                        </button>

                        <p class="text-center text-xs text-gray-500 mt-2">
                            <i class="fas fa-shield-alt mr-1"></i> M-Pesa payment on delivery
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 overflow-auto hidden" aria-hidden="true">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl">
            <div class="bg-green-600 text-white p-5 rounded-t-xl text-center">
                <i class="fas fa-check-circle text-4xl mb-2"></i>
                <h3 class="text-xl font-bold">Order Placed!</h3>
            </div>
            <div class="p-5 text-center">
                <p class="text-gray-600 mb-3">Your order has been received</p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <div class="font-bold text-red-600 text-sm mb-1" id="confirmationInvoice"></div>
                    <div class="text-xs text-gray-500">Order ID</div>
                </div>
                <div class="space-y-2 text-left text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ready in:</span>
                        <span class="font-medium" id="confirmationTime">20-30 min</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Total:</span>
                        <span class="text-red-600" id="confirmationTotal">KES 0</span>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-between">
                <button onclick="printConfirmation()"
                        class="px-4 py-2 text-sm border border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <button onclick="newOrder()"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-plus mr-1"></i> New Order
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Global variables
    let cart = [];
    let selectedCafeteria = null;
    let selectedOrderType = 'pickup';
    let allProducts = [];
    let isSubmitting = false;

    $(document).ready(function() {
        initCafeteriaSelection();
        setupEventListeners();

        // Auto-select if only one cafeteria
        @if($cafeterias->count() === 1)
            const firstCafeteria = $('.cafeteria-card').first();
            if (firstCafeteria.length) {
                selectCafeteria(firstCafeteria.data('cafeteria-id'));
            }
        @endif
    });

    function initCafeteriaSelection() {
        $('.cafeteria-card').off('click').on('click', function() {
            $('.cafeteria-card').removeClass('border-red-600 bg-red-50');
            $(this).addClass('border-red-600 bg-red-50');
            selectCafeteria($(this).data('cafeteria-id'));
        });
    }

    function selectCafeteria(cafeteriaId) {
        if (!cafeteriaId) return;
        selectedCafeteria = cafeteriaId;
        $('#shopId').val(cafeteriaId);
        $('#menuSection').removeClass('hidden');
        loadMenu(cafeteriaId);
    }

    async function loadMenu(cafeteriaId) {
        $('#productsLoading').removeClass('hidden');
        $('#productsGrid').addClass('hidden');
        $('#productsSection').addClass('hidden');

        try {
            const response = await $.ajax({
                url: '{{ route("public.cafeteria.getMenu") }}',
                method: 'GET',
                data: { shop_id: cafeteriaId },
                timeout: 10000
            });

            if (response.success) {
                allProducts = response.products || [];

                if (response.categories && response.categories.length > 0) {
                    renderCategories(response.categories);
                }

                renderProducts(allProducts);
                $('#productsLoading').addClass('hidden');
                $('#productsSection').removeClass('hidden');
                $('#productsGrid').removeClass('hidden');

                // Reset cart when changing cafeteria
                cart = [];
                updateCart();

                showToast(`Menu loaded`, 'success');
            } else {
                throw new Error(response.error || 'Failed to load menu');
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            showToast('Failed to load menu', 'error');
            $('#productsLoading').addClass('hidden');
            $('#productsEmpty').removeClass('hidden');
        }
    }

    function renderCategories(categories) {
        const container = $('#categoriesList');
        container.empty();

        container.append(`
            <button class="category-chip px-3 py-1.5 text-sm bg-red-600 text-white rounded-full transition active"
                    data-category-id="all">All</button>
        `);

        categories.forEach(category => {
            container.append(`
                <button class="category-chip px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition"
                        data-category-id="${category.id}">${category.category_name}</button>
            `);
        });

        $('.category-chip').off('click').on('click', function() {
            $('.category-chip').removeClass('bg-red-600 text-white').addClass('bg-gray-100 text-gray-700');
            $(this).removeClass('bg-gray-100 text-gray-700').addClass('bg-red-600 text-white');
            filterProductsByCategory($(this).data('category-id'));
        });
    }

    function renderProducts(products) {
        const container = $('#productsGrid');
        container.empty();

        if (!products || products.length === 0) {
            $('#productsEmpty').removeClass('hidden');
            $('#productsGrid').addClass('hidden');
            return;
        }

        $('#productsEmpty').addClass('hidden');
        $('#productsGrid').removeClass('hidden');

        products.forEach(product => {
            const productCard = `
                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition">
                    <div class="relative h-28 bg-gray-100 flex items-center justify-center">
                        ${product.image ?
                            `<img src="/storage/${product.image}" alt="${product.product_name}" class="h-full w-full object-cover">` :
                            `<i class="fas fa-utensils text-gray-300 text-2xl"></i>`
                        }
                        ${product.is_featured ?
                            `<span class="absolute top-1 right-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded">★</span>` : ''
                        }
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-800 text-sm mb-0.5">${product.product_name}</h3>
                        <p class="text-xs text-gray-500 mb-2 truncate">${product.description || ''}</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-red-600 text-sm">KES ${parseFloat(product.selling_price).toFixed(0)}</span>
                            <button class="add-to-cart-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs"
                                    data-product-id="${product.id}">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.append(productCard);
        });

        $('.add-to-cart-btn').off('click').on('click', function(e) {
            e.stopPropagation();
            addToCart($(this).data('product-id'), 1);
        });
    }

    function addToCart(productId, quantity = 1) {
        const product = allProducts.find(p => p.id == productId);
        if (!product) return;

        const existingIndex = cart.findIndex(item => item.product_id == productId);

        if (existingIndex > -1) {
            cart[existingIndex].quantity += quantity;
        } else {
            cart.push({
                product_id: product.id,
                product_name: product.product_name,
                unit_price: parseFloat(product.selling_price),
                quantity: quantity,
                image: product.image
            });
        }

        updateCart();
        showOrderInfo();
        showToast(`${product.product_name} added`, 'success');
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
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg text-sm">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">${item.product_name}</div>
                        <div class="text-gray-500">KES ${item.unit_price.toFixed(0)}</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${index}, ${item.quantity - 1})"
                                class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 text-xs">-</button>
                        <span class="w-6 text-center">${item.quantity}</span>
                        <button onclick="updateQuantity(${index}, ${item.quantity + 1})"
                                class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 text-xs">+</button>
                        <span class="font-medium w-16 text-right">KES ${itemTotal.toFixed(0)}</span>
                    </div>
                </div>
            `;
            cartItems.append(cartItem);
        });

        const deliveryFee = selectedOrderType === 'delivery' ? 50 : 0;
        const total = subtotal + deliveryFee;

        $('#cartSubtotal').text(`KES ${subtotal.toFixed(0)}`);
        $('#deliveryFeeDisplay').text(`KES ${deliveryFee.toFixed(0)}`);
        $('#cartTotal').text(`KES ${total.toFixed(0)}`);
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
        if (confirm('Clear all items?')) {
            cart = [];
            updateCart();
            showToast('Cart cleared', 'info');
        }
    }

    function showOrderInfo() {
        $('#orderTypeSection').removeClass('hidden');
        updateOrderType();
    }

    function updateOrderType() {
        $('.order-type-btn').removeClass('active');
        $(`.order-type-btn[data-type="${selectedOrderType}"]`).addClass('active');

        if (selectedOrderType === 'pickup') {
            $('#pickupOptions').removeClass('hidden');
            $('#deliveryOptions').addClass('hidden');
        } else {
            $('#pickupOptions').addClass('hidden');
            $('#deliveryOptions').removeClass('hidden');
        }

        updateCart();
    }

    function setupEventListeners() {
        $('#clearCartBtn').off('click').on('click', clearCart);

        $('#productSearch').off('input').on('input', debounce(function() {
            const searchTerm = $(this).val().toLowerCase();
            if (searchTerm) {
                const filtered = allProducts.filter(p =>
                    p.product_name.toLowerCase().includes(searchTerm) ||
                    (p.description && p.description.toLowerCase().includes(searchTerm))
                );
                renderProducts(filtered);
            } else {
                renderProducts(allProducts);
            }
        }, 300));

        $('.order-type-btn').off('click').on('click', function() {
            selectedOrderType = $(this).data('type');
            updateOrderType();
        });

        $('input[name="location_id"]').off('change').on('change', function() {
            if ($(this).val() === 'other_specified') {
                $('#otherLocationDetails').removeClass('hidden');
            } else {
                $('#otherLocationDetails').addClass('hidden');
            }
        });

        $('#checkoutBtn').off('click').on('click', validateAndCheckout);
    }

    function filterProductsByCategory(categoryId) {
        if (categoryId === 'all') {
            renderProducts(allProducts);
        } else {
            renderProducts(allProducts.filter(p => p.category_id == categoryId));
        }
    }

    function validateAndCheckout() {
        if (isSubmitting) return;

        const name = $('#customerName').val().trim();
        const phone = $('#customerPhone').val().trim();
        const phoneRegex = /^(07|01)\d{8}$/;

        if (!name) {
            showToast('Enter your name', 'error');
            $('#customerName').focus();
            return;
        }
        if (!phone || !phoneRegex.test(phone.replace(/\s+/g, ''))) {
            showToast('Valid phone number required (07xxxxxxxx)', 'error');
            $('#customerPhone').focus();
            return;
        }
        if (cart.length === 0) {
            showToast('Cart is empty', 'error');
            return;
        }
        if (!selectedCafeteria) {
            showToast('Select a cafeteria', 'error');
            return;
        }

        if (selectedOrderType === 'delivery') {
            const selectedLocation = $('input[name="location_id"]:checked').val();
            if (selectedLocation === 'other_specified') {
                const details = $('#locationDetails').val().trim();
                if (!details || details.length < 5) {
                    showToast('Please specify delivery location', 'error');
                    $('#locationDetails').focus();
                    return;
                }
            }
        }

        isSubmitting = true;
        $('#checkoutBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Placing...');

        const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        const deliveryFee = selectedOrderType === 'delivery' ? 50 : 0;

        const orderData = {
            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
            shop_id: selectedCafeteria,
            customer_name: name,
            customer_phone: phone,
            customer_email: $('#customerEmail').val() || '',
            order_type: selectedOrderType,
            location_id: $('input[name="location_id"]:checked').val(),
            location_details: $('input[name="location_id"]:checked').val() === 'other_specified' ? $('#locationDetails').val() : '',
            pickup_time: 'asap',
            special_instructions: '',
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            })),
            delivery_fee: deliveryFee,
            total_amount: subtotal + deliveryFee
        };

        placeOrder(orderData);
    }

    async function placeOrder(orderData) {
        try {
            const response = await $.ajax({
                url: '{{ route("public.cafeteria.placeOrder") }}',
                method: 'POST',
                data: orderData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                timeout: 30000
            });

            if (response.success) {
                showToast('Order placed successfully!', 'success');
                showConfirmation(response.order);
                cart = [];
                updateCart();
                $('#customerName').val('');
                $('#customerPhone').val('');
            } else {
                throw new Error(response.error || 'Order failed');
            }
        } catch (error) {
            console.error('Order error:', error);
            showToast(error.responseJSON?.error || 'Order failed. Please try again.', 'error');
        } finally {
            isSubmitting = false;
            $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-shopping-cart mr-2"></i> Place Order');
        }
    }

    function showConfirmation(order) {
        $('#confirmationInvoice').text(order.invoice_number || 'Order');
        $('#confirmationTime').text(order.estimated_time || '20-30 min');
        $('#confirmationTotal').text(`KES ${order.total_amount || 0}`);
        $('#confirmationModal').removeClass('hidden');
    }

    function printConfirmation() {
        const printContent = `
            <html><head><title>Order Confirmation</title>
            <style>body{font-family:Arial;padding:20px;text-align:center}</style></head>
            <body>
                <h2>KTVTC Cafeteria</h2>
                <p>Order: ${$('#confirmationInvoice').text()}</p>
                <p>Ready in: ${$('#confirmationTime').text()}</p>
                <p>Total: ${$('#confirmationTotal').text()}</p>
                <p>Thank you!</p>
            </body></html>`;
        const w = window.open('', '_blank');
        w.document.write(printContent);
        w.document.close();
        w.print();
    }

    function newOrder() {
        $('#confirmationModal').addClass('hidden');
        cart = [];
        updateCart();
        $('#orderTypeSection').addClass('hidden');
        $('#customerName, #customerPhone').val('');
        isSubmitting = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white text-sm z-50 transform translate-x-full transition-transform duration-300">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i> ${message}
            </div>
        `);
        toast.addClass(type === 'success' ? 'bg-green-500' : 'bg-red-500');
        $('body').append(toast);
        setTimeout(() => toast.css('transform', 'translateX(0)'), 10);
        setTimeout(() => {
            toast.css('transform', 'translateX(100%)');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
</script>
@endsection
