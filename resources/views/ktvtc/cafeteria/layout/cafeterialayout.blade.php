<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KTVTC Cafeteria @yield('title')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#E63946',
                        'primary-dark': '#C1121F',
                        'success': '#10B981',
                        'warning': '#F59E0B',
                        'danger': '#EF4444',
                        'info': '#3B82F6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FA;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #E63946 0%, #C1121F 100%);
            position: fixed;
            top: 64px;
            bottom: 0;
            width: 260px;
            z-index: 40;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
        }

        /* Desktop - Sidebar visible */
        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
                left: 0;
            }
            .main-content {
                margin-left: 260px;
            }
            .mobile-menu-btn {
                display: none;
            }
            .mobile-overlay {
                display: none !important;
            }
        }

        /* Mobile - Sidebar hidden by default */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                left: 0;
                width: 260px;
                z-index: 1000;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            .mobile-overlay.open {
                display: block;
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-btn {
                display: block;
            }
        }

        .nav-link {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background: white !important;
            color: #E63946 !important;
            font-weight: 600;
        }

        .btn-primary {
            background: #E63946;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #C1121F;
        }

        /* Loading Overlay */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 45px;
            height: 45px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #E63946;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }
    </style>

    @yield('styles')
</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="bg-white rounded-xl p-6 text-center min-w-[220px] shadow-2xl">
        <div class="spinner"></div>
        <p class="text-gray-800 font-semibold" id="loadingMessage">Processing...</p>
        <p class="text-gray-500 text-xs mt-1">Please wait</p>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="mobileOverlay" class="mobile-overlay"></div>

<!-- Navbar -->
<nav class="fixed top-0 left-0 right-0 h-16 bg-primary shadow-lg z-50">
    <div class="flex items-center justify-between h-full px-4 md:px-6">
        <div class="flex items-center">
            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="mobile-menu-btn mr-3 text-white w-10 h-10 rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <!-- Logo -->
            <a href="{{ route('cafeteria.dashboard') }}" class="flex items-center">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center mr-2">
                    <i class="fas fa-utensils text-white text-lg"></i>
                </div>
                <span class="font-bold text-white text-lg">Cafeteria</span>
            </a>
        </div>

        <div class="flex items-center space-x-3">
            <!-- User Dropdown -->
            <div class="relative">
                <button id="userBtn" class="flex items-center text-white hover:bg-white/10 px-2 py-1 rounded-lg transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <span class="hidden md:block ml-2 text-sm font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs hidden md:block"></i>
                </button>
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50 border border-gray-100">
                    <div class="py-2">
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user w-4 mr-3 text-gray-400"></i> My Profile
                        </a>
                        <a href="{{ route('cafeteria.settings.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-cog w-4 mr-3 text-gray-400"></i> Settings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt w-4 mr-3"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
    <div class="p-4">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('cafeteria.dashboard') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Sales -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Sales</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.sales.pos') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.sales.pos') ? 'active' : '' }}">
                    <i class="fas fa-cash-register w-5 mr-3"></i>
                    <span>POS Terminal</span>
                </a>
            </li>
            <li>
                <a href="{{ route('cafeteria.sales.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.sales.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart w-5 mr-3"></i>
                    <span>Sales List</span>
                </a>
            </li>
            <li>
                <a href="{{ route('cafeteria.daily-productions.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.daily-productions.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 mr-3"></i>
                    <span>Daily Production</span>
                </a>
            </li>

            <!-- Products -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Products</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.categories.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags w-5 mr-3"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="{{ route('cafeteria.products.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.products.*') ? 'active' : '' }}">
                    <i class="fas fa-utensils w-5 mr-3"></i>
                    <span>Products</span>
                </a>
            </li>

            <!-- Inventory -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Inventory</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.inventory.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes w-5 mr-3"></i>
                    <span>Inventory Control</span>
                </a>
            </li>

            <!-- Purchases -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Purchases</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.suppliers.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck w-5 mr-3"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('cafeteria.purchase-orders.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.purchase-orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-basket w-5 mr-3"></i>
                    <span>Purchase Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ route('cafeteria.grn.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.grn.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 mr-3"></i>
                    <span>Goods Received</span>
                </a>
            </li>

            <!-- Reports -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Reports</p>
            </li>
           <li>
    <a href="{{ route('cafeteria.reports.dashboard') }}"
       class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-pie w-5 mr-3"></i>
        <span>Reports</span>
    </a>
</li>

            <!-- Payments -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">Finance</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.payments.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5 mr-3"></i>
                    <span>Payments</span>
                </a>
            </li>

            <!-- Settings -->
            <li class="mt-4">
                <p class="px-3 text-xs font-semibold text-white/70 uppercase tracking-wider">System</p>
            </li>
            <li>
                <a href="{{ route('cafeteria.settings.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg nav-link {{ request()->routeIs('cafeteria.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content pt-16 min-h-screen">
    <!-- Breadcrumb & Header -->
    <div class="bg-white border-b px-4 py-4 md:px-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                <p class="text-sm text-gray-500">@yield('subtitle', 'Cafeteria Management System')</p>
            </div>
            <div class="mt-3 md:mt-0">
                @yield('header-actions')
            </div>
        </div>
        <!-- Breadcrumb -->
        <div class="mt-2">
            <ol class="flex text-sm text-gray-500">
                <li><a href="{{ route('cafeteria.dashboard') }}" class="hover:text-primary transition">Home</a></li>
                @yield('breadcrumb')
            </ol>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="px-4 pt-4 md:px-6">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <ul class="list-disc list-inside mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4 md:p-6">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="border-t bg-white px-4 py-3 md:px-6 text-center text-gray-500 text-sm mt-6">
        <p>KTVTC Cafeteria Management System v1.0 &copy; {{ date('Y') }}</p>
    </footer>
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    (function() {
        // Toastr configuration
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000,
            extendedTimeOut: 1000
        };

        // Loading overlay functions
        window.showLoading = function(message = 'Processing...') {
            const overlay = document.getElementById('loadingOverlay');
            const messageEl = document.getElementById('loadingMessage');
            if (overlay) {
                if (messageEl) messageEl.textContent = message;
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.hideLoading = function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        // Auto show loading on form submit
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM' && !form.hasAttribute('data-no-loading')) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    showLoading('Submitting...');
                }
            }
        });

        // Mobile sidebar toggle
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('open');
            if (mobileOverlay) mobileOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (mobileOverlay) mobileOverlay.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (mobileBtn) {
            mobileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                openSidebar();
            });
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar on window resize (when switching to desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                closeSidebar();
            }
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // User dropdown
        const userBtn = document.getElementById('userBtn');
        const userMenu = document.getElementById('userMenu');

        if (userBtn && userMenu) {
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function() {
                userMenu.classList.add('hidden');
            });

            userMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        pageLength: 25,
                        language: {
                            search: "Search:",
                            searchPlaceholder: "Search...",
                            lengthMenu: "_MENU_ records per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "Showing 0 to 0 of 0 entries",
                            zeroRecords: "No matching records found"
                        },
                        dom: '<"flex justify-between items-center mb-4"<"dt-buttons"B><"dt-search"f>>rt<"flex justify-between items-center mt-4"<"dt-info"i><"dt-pagination"p>>',
                        buttons: ['copy', 'excel', 'print']
                    });
                }
            });

            // Initialize Select2
            $('select:not(.no-select2)').each(function() {
                $(this).select2({
                    theme: 'classic',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Select an option'
                });
            });
        });

        // Helper function for AJAX requests with loading
        window.ajaxWithLoading = function(url, options = {}) {
            showLoading(options.loadingMessage || 'Loading...');

            return $.ajax({
                url: url,
                method: options.method || 'GET',
                data: options.data || {},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    if (options.onSuccess) options.onSuccess(response);
                },
                error: function(xhr) {
                    hideLoading();
                    if (options.onError) options.onError(xhr);
                    else toastr.error('An error occurred. Please try again.');
                }
            });
        };
    })();
</script>

@yield('scripts')
</body>
</html>
