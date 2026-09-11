@extends('layouts.app')

@section('title', 'Order Now — KTVTC Cafeteria')
@section('meta_description', 'Order your favorite cafeteria meals in seconds. Pre-order and pick up at the cafeteria.')

@section('content')
<div class="bg-gray-50 min-h-screen py-8" id="portalApp"
     data-account-type="{{ $account->user_type }}"
     data-card-balance="{{ $cardBalance ?? '' }}">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Top bar --}}
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-user text-xl text-red-700"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Welcome</p>
                    <p class="font-bold text-gray-800">{{ $account->display_name }}</p>
                    <p class="text-xs text-gray-500">{{ $account->type_label }}</p>
                </div>
            </div>

            @if($account->isHighSchoolStudent())
                <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-center">
                    <p class="text-xs text-green-700">Card Balance</p>
                    <p class="text-xl font-bold text-green-700">KES {{ number_format($cardBalance ?? 0, 2) }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: Menu --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Shop selector --}}
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="font-bold text-gray-800 mb-3">Choose a Shop</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <button type="button"
                                class="shop-btn border-2 border-gray-200 hover:border-red-500 rounded-lg p-3 text-center transition"
                                data-shop-id="">
                            <i class="fas fa-th-large text-2xl text-red-600 mb-1"></i>
                            <p class="text-sm font-medium">All Shops</p>
                        </button>
                        @foreach($shops as $shop)
                            <button type="button"
                                    class="shop-btn border-2 border-gray-200 hover:border-red-500 rounded-lg p-3 text-center transition"
                                    data-shop-id="{{ $shop->id }}">
                                <i class="fas fa-utensils text-2xl text-red-600 mb-1"></i>
                                <p class="text-sm font-medium">{{ $shop->shop_name }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Search + categories --}}
                <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                    <input type="text" id="searchInput" placeholder="Search products..."
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                    <div id="categoriesList" class="flex flex-wrap gap-2"></div>
                </div>

                {{-- Products grid --}}
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div id="loadingSpinner" class="text-center py-12 hidden">
                        <i class="fas fa-spinner spin text-3xl text-red-600"></i>
                        <p class="text-sm text-gray-500 mt-2">Loading menu...</p>
                    </div>
                    <div id="productsGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                    <div id="noProducts" class="hidden text-center py-12 text-gray-400">
                        <i class="fas fa-utensils text-3xl mb-2"></i>
                        <p>No products found</p>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Cart --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-800">
                            <i class="fas fa-shopping-basket mr-2 text-red-600"></i>Your Cart
                        </h2>
                        <button type="button" id="clearCartBtn" class="text-xs text-red-600 hover:underline hidden">
                            Clear
                        </button>
                    </div>

                    <div id="cartEmpty" class="text-center py-8 text-gray-400">
                        <i class="fas fa-shopping-basket text-3xl mb-2"></i>
                        <p class="text-sm">Cart is empty</p>
                    </div>

                    <div id="cartItems" class="space-y-2 hidden max-h-72 overflow-y-auto"></div>

                    <div id="cartTotals" class="hidden mt-4 pt-4 border-t">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-red-700" id="cartTotal">KES 0.00</span>
                        </div>
                        <button type="button" id="checkoutBtn"
                                class="w-full mt-3 bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span id="checkoutBtnText">
                                @if($account->paysViaCard())
                                    Place Order (Card)
                                @else
                                    Pay with M-Pesa
                                @endif
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- M-Pesa waiting modal --}}
<div id="mpesaModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center">
        <i class="fas fa-mobile-alt text-5xl text-green-600 mb-3"></i>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Check Your Phone</h3>
        <p class="text-sm text-gray-600 mb-4">
            We've sent an M-Pesa prompt to your phone. Enter your PIN to complete the payment.
        </p>
        <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
            <i class="fas fa-spinner spin"></i>
            <span id="mpesaStatusText">Waiting for confirmation...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const accountType = document.getElementById('portalApp').dataset.accountType;
    let cart = [];
    let currentShopId = '';

    // ── Toast helper ──
    function toast(msg, type = 'info') {
        const el = document.createElement('div');
        el.className = 'fixed bottom-6 right-6 z-[100] px-4 py-3 rounded-lg shadow-lg text-sm text-white ' +
            (type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-gray-900');
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    // ── Load menu ──
    async function loadMenu(shopId = '', categoryId = '', search = '') {
        document.getElementById('loadingSpinner').classList.remove('hidden');
        document.getElementById('productsGrid').innerHTML = '';
        document.getElementById('noProducts').classList.add('hidden');

        const params = new URLSearchParams();
        if (shopId) params.append('shop_id', shopId);
        if (categoryId) params.append('category_id', categoryId);
        if (search) params.append('search', search);

        try {
            const res = await fetch('{{ route("pre-order.menu") }}?' + params, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            document.getElementById('loadingSpinner').classList.add('hidden');

            if (!data.success || !data.products.length) {
                document.getElementById('noProducts').classList.remove('hidden');
                return;
            }

            renderProducts(data.products);
            if (!categoryId && !search) renderCategories(data.categories);
        } catch (e) {
            document.getElementById('loadingSpinner').classList.add('hidden');
            document.getElementById('noProducts').classList.remove('hidden');
        }
    }

    function renderProducts(products) {
        const grid = document.getElementById('productsGrid');
        grid.innerHTML = products.map(p => `
            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition ${!p.is_available ? 'opacity-50' : ''}">
                <div class="relative bg-gray-100" style="padding-bottom: 100%;">
                    ${p.image
                        ? `<img src="${p.image}" class="absolute inset-0 w-full h-full object-cover">`
                        : `<div class="absolute inset-0 flex items-center justify-center text-4xl text-gray-300">🍽️</div>`}
                    ${!p.is_available ? '<span class="absolute top-1 right-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded">Out</span>' : ''}
                </div>
                <div class="p-2">
                    <p class="text-sm font-medium text-gray-800 truncate">${p.product_name}</p>
                    <p class="text-xs text-gray-500">KES ${parseFloat(p.selling_price).toFixed(0)}</p>
                    <button type="button"
                            class="add-btn w-full mt-2 text-xs bg-red-700 hover:bg-red-800 text-white py-1.5 rounded transition"
                            data-id="${p.id}"
                            ${!p.is_available ? 'disabled' : ''}>
                        Add
                    </button>
                </div>
            </div>
        `).join('');

        grid.querySelectorAll('.add-btn').forEach(b => {
            b.addEventListener('click', () => addToCart(b.dataset.id));
        });
    }

    function renderCategories(categories) {
        const c = document.getElementById('categoriesList');
        c.innerHTML = `<button type="button" class="cat-chip bg-red-700 text-white text-xs px-3 py-1.5 rounded-full" data-id="">All</button>` +
            categories.map(cat => `
                <button type="button" class="cat-chip bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-3 py-1.5 rounded-full" data-id="${cat.id}">
                    ${cat.category_name}
                </button>
            `).join('');

        c.querySelectorAll('.cat-chip').forEach(b => {
            b.addEventListener('click', () => {
                c.querySelectorAll('.cat-chip').forEach(x => {
                    x.classList.remove('bg-red-700', 'text-white');
                    x.classList.add('bg-gray-100', 'text-gray-700');
                });
                b.classList.remove('bg-gray-100', 'text-gray-700');
                b.classList.add('bg-red-700', 'text-white');
                loadMenu(currentShopId, b.dataset.id);
            });
        });
    }

    // ── Cart operations ──
    async function addToCart(productId) {
        try {
            const res = await fetch('{{ route("pre-order.cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            });
            const data = await res.json();
            if (data.error) { toast(data.error, 'error'); return; }
            toast(data.message || 'Added to cart', 'success');
            refreshCart();
        } catch (e) { toast('Failed to add item', 'error'); }
    }

    async function refreshCart() {
        const res = await fetch('{{ route("pre-order.cart") }}', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        const items = data.items || [];
        const count = data.count || 0;

        document.getElementById('navCartCount')?.classList.toggle('hidden', count === 0);
        const navCount = document.getElementById('navCartCount');
        if (navCount) navCount.textContent = count;

        const cartItems = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartTotals = document.getElementById('cartTotals');
        const clearBtn = document.getElementById('clearCartBtn');

        if (!items.length) {
            cartItems.classList.add('hidden');
            cartEmpty.classList.remove('hidden');
            cartTotals.classList.add('hidden');
            clearBtn.classList.add('hidden');
            return;
        }

        cartEmpty.classList.add('hidden');
        cartItems.classList.remove('hidden');
        cartTotals.classList.remove('hidden');
        clearBtn.classList.remove('hidden');

        cartItems.innerHTML = items.map(it => `
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2 text-sm">
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">${it.product_name}</p>
                    <p class="text-xs text-gray-500">KES ${parseFloat(it.unit_price).toFixed(0)}</p>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="qty-btn w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 text-sm font-bold" data-id="${it.product_id}" data-q="${it.quantity - 1}">−</button>
                    <span class="w-6 text-center font-medium">${it.quantity}</span>
                    <button type="button" class="qty-btn w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 text-sm font-bold" data-id="${it.product_id}" data-q="${it.quantity + 1}">+</button>
                </div>
            </div>
        `).join('');

        document.getElementById('cartTotal').textContent = 'KES ' + parseFloat(data.total).toFixed(2);

        cartItems.querySelectorAll('.qty-btn').forEach(b => {
            b.addEventListener('click', () => updateQty(b.dataset.id, b.dataset.q));
        });
    }

    async function updateQty(id, qty) {
        try {
            const res = await fetch('{{ route("pre-order.cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ product_id: id, quantity: parseInt(qty) })
            });
            await res.json();
            refreshCart();
        } catch (e) {}
    }

    // ── Checkout ──
    async function doCheckout() {
        const btn = document.getElementById('checkoutBtn');
        btn.disabled = true;
        btn.querySelector('#checkoutBtnText').textContent = 'Placing...';

        try {
            const res = await fetch('{{ route("pre-order.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({})
            });
            const data = await res.json();

            if (data.error) {
                toast(data.error, 'error');
                btn.disabled = false;
                btn.querySelector('#checkoutBtnText').textContent =
                    accountType === 'high_school_student' ? 'Place Order (Card)' : 'Pay with M-Pesa';
                return;
            }

            // HS student → immediate success
            if (data.payment_method === 'card') {
                toast(data.message, 'success');
                setTimeout(() => window.location.href = data.redirect, 1200);
                return;
            }

            // M-Pesa → show modal and poll
            if (data.payment_method === 'mpesa') {
                document.getElementById('mpesaModal').style.display = 'flex';
                pollStatus(data.checkout_request_id, data.redirect);
            }

        } catch (e) {
            toast('Checkout failed. Try again.', 'error');
            btn.disabled = false;
        }
    }

    function pollStatus(checkoutId, redirect) {
        let attempts = 0;
        const max = 40; // 40 * 3s = 2 minutes

        const interval = setInterval(async () => {
            attempts++;
            try {
                const res = await fetch('/pre-order/check-status/' + checkoutId, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (['paid', 'accepted', 'ready', 'completed'].includes(data.status)) {
                    clearInterval(interval);
                    document.getElementById('mpesaStatusText').textContent = 'Payment received!';
                    setTimeout(() => window.location.href = data.redirect || redirect, 1000);
                } else if (['failed', 'cancelled'].includes(data.status)) {
                    clearInterval(interval);
                    document.getElementById('mpesaModal').style.display = 'none';
                    toast('Payment failed. Please try again.', 'error');
                    const btn = document.getElementById('checkoutBtn');
                    btn.disabled = false;
                    btn.querySelector('#checkoutBtnText').textContent = 'Pay with M-Pesa';
                } else if (attempts >= max) {
                    clearInterval(interval);
                    document.getElementById('mpesaStatusText').textContent = 'Still waiting... please wait.';
                }
            } catch (e) {}
        }, 3000);
    }

    // ── Wire up ──
    document.querySelectorAll('.shop-btn').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.shop-btn').forEach(x => x.classList.remove('border-red-500', 'bg-red-50'));
            b.classList.add('border-red-500', 'bg-red-50');
            currentShopId = b.dataset.shopId;
            loadMenu(currentShopId);
        });
    });

    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadMenu(currentShopId, '', e.target.value), 300);
    });

    document.getElementById('clearCartBtn').addEventListener('click', async () => {
        if (!confirm('Clear all items?')) return;
        await fetch('{{ route("pre-order.cart.clear") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        });
        refreshCart();
        toast('Cart cleared', 'info');
    });

    document.getElementById('checkoutBtn').addEventListener('click', doCheckout);

    // Initial load
    document.querySelector('.shop-btn[data-shop-id=""]')?.classList.add('border-red-500', 'bg-red-50');
    loadMenu();
    refreshCart();
})();
</script>
@endpush