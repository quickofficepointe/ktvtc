<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTVTC Cafeteria @yield('title')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#E63946',      /* Vibrant red */
                        'secondary': '#000000',     /* Black */
                        'light': '#FFFFFF',        /* White */
                        'dark': '#1D3557',         /* Dark blue */
                        'accent': '#A8DADC',       /* Light teal */
                        'success': '#10B981',      /* Green */
                        'warning': '#F59E0B',      /* Amber */
                        'danger': '#EF4444',       /* Red */
                        'info': '#3B82F6',         /* Blue */
                        'cafeteria': {
                            'food': '#F97316',     /* Orange */
                            'beverage': '#8B5CF6', /* Purple */
                            'snack': '#F59E0B',    /* Amber */
                            'gift': '#10B981',     /* Green */
                        }
                    },
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #F8F9FA;
        }
        .sidebar {
            background: linear-gradient(180deg, #E63946 0%, #C1121F 100%);
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: #FFFFFF !important;
            color: #E63946 !important;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .modal-content {
            border-radius: 12px;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(230, 57, 70, 0.3);
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #EF4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-primary shadow-lg h-16">
        <div class="flex items-center justify-between h-full px-4 md:px-6">
            <div class="flex items-center">
                <!-- Mobile Menu Toggle -->
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Logo & Brand -->
                <a class="flex items-center" href="{{ route('cafeteria.dashboard') }}">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-2">
                        <i class="fas fa-utensils text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="font-bold text-white text-xl">KTVTC Cafeteria</span>
                        <span class="block text-xs text-white text-opacity-80">Management System</span>
                    </div>
                </a>
            </div>

            <!-- Right Side Navigation -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="relative text-white focus:outline-none" id="notificationsDropdown">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="notification-badge" id="notificationCount"></span>
                    </button>
                </div>

                <!-- Quick Actions Dropdown -->
                <div class="relative">
                    <button class="text-white focus:outline-none" id="quickActionsDropdown">
                        <i class="fas fa-bolt text-xl"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl hidden" id="quickActionsMenu">
                        <div class="p-4">
                            <h6 class="font-semibold text-gray-800 mb-3">Quick Actions</h6>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('cafeteria.sales.pos') }}" class="p-3 bg-gray-50 rounded-lg text-center hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-plus text-lg mb-1"></i>
                                    <p class="text-xs font-medium">New Sale</p>
                                </a>
                                <a href="{{ route('cafeteria.reports.sales.daily') }}" class="p-3 bg-gray-50 rounded-lg text-center hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-chart-bar text-lg mb-1"></i>
                                    <p class="text-xs font-medium">Daily Report</p>
                                </a>
                                <a href="{{ route('cafeteria.products.create') }}" class="p-3 bg-gray-50 rounded-lg text-center hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-box text-lg mb-1"></i>
                                    <p class="text-xs font-medium">Add Product</p>
                                </a>
                                <a href="{{ route('cafeteria.purchase-orders.create') }}" class="p-3 bg-gray-50 rounded-lg text-center hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-shopping-cart text-lg mb-1"></i>
                                    <p class="text-xs font-medium">Purchase Order</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                        <div class="w-9 h-9 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <div class="hidden md:block ml-3 text-left">
                            <span class="font-medium text-sm">{{ auth()->user()->name }}</span>
                            @if(auth()->user()->role_name)
                            <span class="badge bg-{{ auth()->user()->role_badge ?? 'primary' }}">
                                {{ auth()->user()->role_name }}
                            </span>
                            @endif
                        </div>
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl hidden" id="userMenu">
                        <div class="py-2">
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-3 text-gray-500"></i> My Profile
                            </a>
                            <a href="{{ route('cafeteria.settings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-3 text-gray-500"></i> Settings
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar - Organized by Categories -->
    <div class="fixed top-16 left-0 bottom-0 w-64 text-white z-40 overflow-y-auto sidebar hidden md:block transform transition-transform duration-300" id="sidebar">
        <div class="p-4">
            <!-- Navigation Links -->
            <ul class="space-y-1">
                <!-- DASHBOARD -->
                <li>
                    <a href="{{ route('cafeteria.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <!-- ========== SALES & ORDERS ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">SALES & ORDERS</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.sales.pos') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.sales.pos') ? 'active' : '' }}">
                        <i class="fas fa-cash-register mr-3"></i> POS Terminal
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.daily-productions.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.daily-productions.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list mr-3"></i> Daily Production
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.sales.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.sales.index') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart mr-3"></i> Sales List
                    </a>
                </li>
                <!--
                <li>
                    <a href="{{ route('cafeteria.sales.pending-payment') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.sales.pending-payment') ? 'active' : '' }}">
                        <i class="fas fa-clock mr-3"></i> Pending Payments
                    </a>
                </li>
                -->
                <!-- ========== PRODUCT MANAGEMENT ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">PRODUCT MANAGEMENT</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.categories.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags mr-3"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.products.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.products.*') ? 'active' : '' }}">
                        <i class="fas fa-utensils mr-3"></i> Products
                    </a>
                </li>
                 <!--
                <li>
                    <a href="{{ route('cafeteria.products.low-stock') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.products.low-stock') ? 'active' : '' }}">
                        <i class="fas fa-exclamation-triangle mr-3"></i> Low Stock Alert
                    </a>
                </li>
                -->
                <!-- ========== INVENTORY MANAGEMENT ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">INVENTORY MANAGEMENT</p>
                </li>

                <li>
                    <a href="{{ route('cafeteria.inventory.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.inventory.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes mr-3"></i> Inventory Control
                    </a>
                </li>
                  <!--
                <li>
                    <a href="{{ route('cafeteria.stock-adjustments.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.stock-adjustments.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt mr-3"></i> Stock Adjustments
                    </a>
                </li>

                <li>
                    <a href="{{ route('cafeteria.stock-alerts.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.stock-alerts.*') ? 'active' : '' }}">
                        <i class="fas fa-bell mr-3"></i> Stock Alerts
                    </a>
                </li>
  -->
                <!-- ========== PURCHASE & SUPPLIERS ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">PURCHASE & SUPPLIERS</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.suppliers.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck mr-3"></i> Suppliers
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.purchase-orders.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.purchase-orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-basket mr-3"></i> Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.grn.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.grn.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check mr-3"></i> Goods Received
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.direct-purchases.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.direct-purchases.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart mr-3"></i> Direct Purchases
                    </a>
                </li>

                <!-- ========== REPORTS & ANALYTICS ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">REPORTS & ANALYTICS</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.reports.sales.daily') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.reports.sales.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-day mr-3"></i> Sales Reports
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.reports.inventory.stock-levels') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.reports.inventory.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie mr-3"></i> Inventory Reports
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.reports.financial.profit-loss') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.reports.financial.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave mr-3"></i> Financial Reports
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.reports.purchase.summary') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.reports.purchase.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar mr-3"></i> Purchase Reports
                    </a>
                </li>

                <!-- ========== PAYMENTS & FINANCE ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">PAYMENTS & FINANCE</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.payments.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.payments.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card mr-3"></i> Payment Transactions
                    </a>
                </li>

                <!-- ========== SYSTEM & SETTINGS ========== -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">SYSTEM & SETTINGS</p>
                </li>
                <li>
                    <a href="{{ route('cafeteria.settings.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog mr-3"></i> System Settings
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.users.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users mr-3"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.business-sections.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.business-sections.*') ? 'active' : '' }}">
                        <i class="fas fa-building mr-3"></i> Business Sections
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.shops.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.shops.*') ? 'active' : '' }}">
                        <i class="fas fa-store mr-3"></i> Shop Management
                    </a>
                </li>
                <li>
                    <a href="{{ route('cafeteria.backup') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('cafeteria.backup') ? 'active' : '' }}">
                        <i class="fas fa-database mr-3"></i> Backup & Restore
                    </a>
                </li>
            </ul>

            <!-- Quick Stats -->
            <div class="mt-8 p-3 bg-white bg-opacity-10 rounded-lg">
                <p class="text-xs font-semibold mb-2 text-white text-opacity-80">TODAY'S SNAPSHOT</p>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs">Today's Sales</span>
                        <span class="text-xs font-semibold" id="sidebarTodaySales">--</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs">Today's Orders</span>
                        <span class="text-xs font-semibold" id="sidebarTodayOrders">--</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs">Low Stock Items</span>
                        <span class="text-xs font-semibold" id="sidebarLowStock">--</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-0 md:ml-64 mt-16 p-4 md:p-6 min-h-screen" id="mainContent">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-gray-600">@yield('page-description', 'Cafeteria Management Dashboard')</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex space-x-3">
                        @yield('page-actions')
                        <!-- Quick Action Buttons -->
                        <a href="{{ route('cafeteria.sales.pos') }}" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> New Sale
                        </a>
                        <a href="{{ route('cafeteria.reports.sales.daily') }}" class="bg-white text-primary border border-primary font-medium py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-print mr-2"></i> Daily Report
                        </a>
                    </div>
                </div>
            </div>
            <!-- Breadcrumb -->
            <nav class="flex mt-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('cafeteria.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                    </li>
                    @yield('breadcrumbs')
                </ol>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-gray-500 text-sm">
            <p>KTVTC Cafeteria Management System v1.0 • © {{ date('Y') }} • Made with <i class="fas fa-heart text-red-500"></i> by KTVTC</p>
            <p class="mt-1">Server Time: <span id="serverTime">{{ now()->format('Y-m-d H:i:s') }}</span></p>
        </footer>
    </div>

    <!-- ========== SCRIPTS ========== -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Main JavaScript -->
    <script>
        // Initialize Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Sidebar Toggle
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // Dropdown Toggles
        const dropdowns = {
            'userDropdown': 'userMenu',
            'notificationsDropdown': 'notificationsMenu',
            'quickActionsDropdown': 'quickActionsMenu'
        };

        Object.keys(dropdowns).forEach(dropdownId => {
            const button = document.getElementById(dropdownId);
            const menu = document.getElementById(dropdowns[dropdownId]);

            if (button && menu) {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            Object.values(dropdowns).forEach(menuId => {
                const menu = document.getElementById(menuId);
                if (menu && !menu.contains(e.target)) {
                    const buttonId = Object.keys(dropdowns).find(key => dropdowns[key] === menuId);
                    const button = document.getElementById(buttonId);
                    if (button && !button.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                }
            });
        });

        // Update server time
        function updateServerTime() {
            const now = new Date();
            document.getElementById('serverTime').textContent = now.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        setInterval(updateServerTime, 1000);

        // Initialize Select2
        $(document).ready(function() {
            $('select').select2({
                theme: 'classic',
                width: '100%'
            });

            // Initialize DataTables on tables with class 'datatable'
            $('.datatable').DataTable({
                dom: 'Bfrtip',
                buttons: ['print', 'copy', 'excel'],
                pageLength: 25,
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
        });

        // AJAX error handling
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status !== 422) { // Don't show validation errors
                toastr.error('An error occurred: ' + thrownError);
            }
        });

        // CSRF Token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Load sidebar stats
        function loadSidebarStats() {
            $.ajax({
                url: '/cafeteria/stats',
                method: 'GET',
                success: function(data) {
                    if (data.today_sales) {
                        $('#sidebarTodaySales').text('KES ' + parseFloat(data.today_sales).toFixed(2));
                    }
                    if (data.today_orders) {
                        $('#sidebarTodayOrders').text(data.today_orders);
                    }
                    if (data.low_stock_items) {
                        $('#sidebarLowStock').text(data.low_stock_items);
                    }
                }
            });
        }

        // Load sidebar stats on page load
        $(document).ready(function() {
            loadSidebarStats();
        });

        // Auto-refresh sidebar stats every 60 seconds
        setInterval(loadSidebarStats, 60000);

        // Print receipt function
        function printReceipt(orderId) {
            window.open('/cafeteria/sales/' + orderId + '/print-receipt', '_blank');
        }

        // Mark notification as read
        function markNotificationAsRead(notificationId) {
            $.ajax({
                url: '/cafeteria/notifications/' + notificationId + '/mark-read',
                method: 'POST',
                success: function(response) {
                    toastr.success('Notification marked as read');
                    // Update notification count
                    let count = parseInt($('#notificationCount').text());
                    if (count > 0) {
                        $('#notificationCount').text(count - 1);
                    }
                }
            });
        }

        // Load notifications
        function loadNotifications() {
            $.get('/cafeteria/api/notifications', function(data) {
                // Update notification count
                if (data.unread_count !== undefined) {
                    $('#notificationCount').text(data.unread_count > 0 ? data.unread_count : '');
                }
            });
        }

        // Load notifications on page load
        $(document).ready(function() {
            loadNotifications();
        });
    </script>

    @yield('scripts')
</body>
</html>
