<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Super Admin Dashboard') - KTVTC Super Admin</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'super': '#1D3557',      // Dark blue for super admin
                        'super-light': '#457B9D', // Lighter blue
                        'super-dark': '#0A1929',  // Very dark blue
                        'danger': '#E63946',      // Red for super admin actions
                        'warning': '#F4A261',     // Orange
                        'success': '#2A9D8F',     // Teal
                        'info': '#457B9D',        // Info blue
                        'light': '#F1FAEE',       // Light background
                        'gray-50': '#F9FAFB',
                        'gray-100': '#F3F4F6',
                        'gray-200': '#E5E7EB',
                        'gray-800': '#1F2937',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            min-height: 100vh;
        }

        /* Sidebar */
        .super-sidebar {
            background: linear-gradient(180deg, #1D3557 0%, #0A1929 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .super-sidebar.collapsed {
            width: 70px;
        }

        .super-sidebar.collapsed .nav-text,
        .super-sidebar.collapsed .group-title {
            display: none;
        }

        .super-nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .super-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .super-nav-link.active {
            background: linear-gradient(90deg, rgba(69, 123, 157, 0.2) 0%, transparent 100%);
            color: white !important;
            border-left: 4px solid #E63946;
        }

        .super-nav-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #E63946;
            border-radius: 50%;
            animation: pulse-slow 2s infinite;
        }

        /* Status Badges */
        .super-badge {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
        }

        .badge-super {
            background: linear-gradient(135deg, #E63946 0%, #FF6B6B 100%);
            color: white;
        }

        .badge-admin {
            background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
            color: white;
        }

        /* Card Styles */
        .super-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .super-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        /* Table Styles */
        .super-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .super-table thead th {
            background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }

        .super-table tbody tr {
            transition: all 0.2s ease;
        }

        .super-table tbody tr:hover {
            background: linear-gradient(90deg, rgba(69, 123, 157, 0.05) 0%, transparent 100%);
            transform: scale(1.005);
        }

        /* Custom Scrollbar */
        .super-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .super-scrollbar::-webkit-scrollbar-track {
            background: #F1F1F1;
            border-radius: 10px;
        }

        .super-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #457B9D 0%, #1D3557 100%);
            border-radius: 10px;
        }

        .super-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1D3557 0%, #0A1929 100%);
        }

        /* Modal Overlay */
        .modal-overlay {
            background: rgba(13, 27, 42, 0.8);
            backdrop-filter: blur(8px);
        }

        /* Progress Bar */
        .progress-bar {
            background: linear-gradient(90deg, #E63946 0%, #FF6B6B 100%);
        }

        /* Switch Toggle */
        .super-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .super-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .super-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #CBD5E1 0%, #94A3B8 100%);
            transition: .4s;
            border-radius: 34px;
        }

        .super-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .super-slider {
            background: linear-gradient(135deg, #E63946 0%, #FF6B6B 100%);
        }

        input:checked + .super-slider:before {
            transform: translateX(30px);
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, #1D3557 0%, #457B9D 100%);
            color: white;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #E63946 0%, #FF6B6B 100%);
        }

        /* Loading Animation */
        .loading-spinner {
            border: 3px solid #F3F4F6;
            border-top: 3px solid #E63946;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .super-sidebar {
                position: fixed;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .super-sidebar.active {
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
                z-index: 40;
            }

            .mobile-overlay.active {
                display: block;
            }
        }
    </style>

    @yield('styles')
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>

    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-lg z-40 border-b border-gray-200">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <!-- Left Section -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuToggle" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Logo -->
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-super to-super-dark flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-crown text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-gray-800 text-lg">KTVTC <span class="text-super">Super Admin</span></h1>
                        <p class="text-xs text-gray-500">Full System Control Panel</p>
                    </div>
                </div>
            </div>

            <!-- Center: Quick Stats -->
            <div class="hidden lg:flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                    <span class="text-sm text-gray-600">System: <span class="font-semibold text-success">Online</span></span>
                </div>
                <div class="h-6 w-px bg-gray-300"></div>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-users mr-1"></i>
                    <span class="font-semibold">{{ \App\Models\User::count() ?? 0 }}</span> Users
                </div>
                <div class="h-6 w-px bg-gray-300"></div>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-database mr-1"></i>
                    <span class="font-semibold">{{ \App\Models\User::where('is_approved', false)->count() ?? 0 }}</span> Pending
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-4">
                <!-- System Status -->
                <div class="hidden md:flex items-center space-x-3 px-4 py-2 bg-super/5 rounded-lg">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full bg-success"></div>
                        <div class="absolute inset-0 rounded-full bg-success animate-ping opacity-75"></div>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-700">CPU:</span>
                        <span class="text-success font-semibold ml-1">45%</span>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationBtn" class="relative p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-danger text-white text-xs rounded-full flex items-center justify-center font-bold">

                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-gray-800">System Notifications</h3>
                                <button class="text-xs text-danger hover:text-danger/80 font-medium">
                                    <i class="fas fa-trash-alt mr-1"></i> Clear All
                                </button>
                            </div>
                        </div>
                        <div class="max-h-96 overflow-y-auto super-scrollbar">
                            <!-- Notifications will be loaded via AJAX -->
                            <div id="notificationList" class="p-4">
                                <div class="flex justify-center py-8">
                                    <div class="loading-spinner"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                            <a href="{{ route('super-admin.system.logs') }}" class="block text-center text-super hover:text-super-dark font-medium">
                                <i class="fas fa-external-link-alt mr-2"></i>View All Activity Logs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="relative">
                    <button id="quickActionsBtn" class="px-4 py-2 bg-gradient-to-r from-super to-super-dark text-white rounded-lg font-semibold hover:shadow-lg transition-shadow flex items-center space-x-2">
                        <i class="fas fa-bolt"></i>
                        <span>Super Actions</span>
                    </button>

                    <div id="quickActionsDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800">Super Admin Actions</h3>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('super-admin.users.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-super/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user-shield text-super"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Create Admin User</p>
                                    <p class="text-xs text-gray-500">Add new system administrator</p>
                                </div>
                            </a>
                            <a href="{{ route('super-admin.system.backup') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-database text-success"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Backup Database</p>
                                    <p class="text-xs text-gray-500">Create system backup</p>
                                </div>
                            </a>
                            <a href="{{ route('super-admin.system.logs') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-history text-warning"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">View System Logs</p>
                                    <p class="text-xs text-gray-500">Monitor all activities</p>
                                </div>
                            </a>
                            <a href="{{ route('super-admin.roles.index') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tag text-info"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Manage Roles</p>
                                    <p class="text-xs text-gray-500">Configure permissions</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="relative">
                    <button id="userDropdownBtn" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-super to-super-dark flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-shield text-white"></i>
                        </div>
                        <div class="text-left hidden lg:block">
                            <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name ?? 'Super Admin' }}</p>
                            <div class="flex items-center">
                                <span class="super-badge badge-super">SUPER ADMIN</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs ml-2"></i>
                            </div>
                        </div>
                    </button>

                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-super to-super-dark flex items-center justify-center mr-3">
                                    <i class="fas fa-user-shield text-white text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ Auth::user()->name ?? 'Super Admin' }}</p>
                                    <p class="text-sm text-gray-500">{{ Auth::user()->email ?? 'superadmin@ktvtc.ac.ke' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('super-admin.system.settings') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <i class="fas fa-sliders-h text-super mr-3"></i>
                                <span class="font-medium text-gray-700">System Settings</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <i class="fas fa-user-edit text-super mr-3"></i>
                                <span class="font-medium text-gray-700">My Profile</span>
                            </a>
                            <a href="{{ route('super-admin.system.logs') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <i class="fas fa-history text-super mr-3"></i>
                                <span class="font-medium text-gray-700">Activity Logs</span>
                            </a>
                            <div class="border-t border-gray-200 mt-2 pt-2">
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-center w-full p-3 rounded-lg hover:bg-red-50 text-danger font-semibold group">
                                        <i class="fas fa-sign-out-alt mr-3 group-hover:animate-pulse"></i>
                                        <span>Logout from Super Admin</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="sidebar" class="super-sidebar fixed top-16 left-0 bottom-0 w-64 text-white pt-6 z-30 overflow-y-auto super-scrollbar">
        <!-- Search -->
        <div class="px-4 mb-6">
            <div class="relative">
                <input type="text"
                       placeholder="Search commands..."
                       class="w-full pl-10 pr-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-danger/50 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3.5 text-white/50"></i>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="px-4 space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('super-admin.dashboard') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl mb-1 {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                <span class="nav-text font-semibold">Super Dashboard</span>
            </a>

            <!-- USER MANAGEMENT -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-users mr-2"></i>User Management
                </p>
            </div>

            <a href="{{ route('super-admin.users.index') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.users.*') && !request()->routeIs('super-admin.users.create') ? 'active' : '' }}">
                <i class="fas fa-user-cog mr-3 text-lg"></i>
                <span class="nav-text">All Users</span>
                <span class="ml-auto bg-white/20 text-white text-xs px-2 py-1 rounded-lg">
                    {{ \App\Models\User::count() ?? 0 }}
                </span>
            </a>

            <a href="{{ route('super-admin.users.create') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.users.create') ? 'active' : '' }}">
                <i class="fas fa-user-plus mr-3 text-lg"></i>
                <span class="nav-text">Create User</span>
            </a>

            <!-- ROLE MANAGEMENT -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-user-tag mr-2"></i>Role Management
                </p>
            </div>

            <a href="{{ route('super-admin.roles.index') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.roles.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt mr-3 text-lg"></i>
                <span class="nav-text">Roles & Permissions</span>
            </a>

            <!-- SYSTEM MANAGEMENT -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-server mr-2"></i>System Control
                </p>
            </div>

            <a href="{{ route('super-admin.system.settings') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.system.settings') ? 'active' : '' }}">
                <i class="fas fa-cogs mr-3 text-lg"></i>
                <span class="nav-text">System Settings</span>
            </a>

            <a href="{{ route('super-admin.system.database') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.system.database') ? 'active' : '' }}">
                <i class="fas fa-database mr-3 text-lg"></i>
                <span class="nav-text">Database</span>
            </a>

            <a href="{{ route('super-admin.system.logs') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.system.logs') ? 'active' : '' }}">
                <i class="fas fa-history mr-3 text-lg"></i>
                <span class="nav-text">System Logs</span>
                <span class="ml-auto bg-danger/80 text-white text-xs px-2 py-1 rounded-lg animate-pulse">
                    <i class="fas fa-circle text-xs"></i>
                </span>
            </a>

            <!-- MODULE ACCESS -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-universal-access mr-2"></i>Module Access
                </p>
            </div>

            <div class="space-y-1">
                @php
                    $modules = [
                        ['route' => 'admin.dashboard', 'icon' => 'fa-user-tie', 'label' => 'Admin Panel', 'color' => 'bg-blue-500'],
                        ['route' => 'mschool.dashboard', 'icon' => 'fa-school', 'label' => 'Mschool', 'color' => 'bg-green-500'],
                        ['route' => 'library.dashboard', 'icon' => 'fa-book', 'label' => 'Library', 'color' => 'bg-purple-500'],
                        ['route' => 'cafeteria.dashboard', 'icon' => 'fa-utensils', 'label' => 'Cafeteria', 'color' => 'bg-yellow-500'],
                        ['route' => 'website.dashboard', 'icon' => 'fa-globe', 'label' => 'Website', 'color' => 'bg-indigo-500'],
                        ['route' => 'finance.dashboard', 'icon' => 'fa-chart-line', 'label' => 'Finance', 'color' => 'bg-teal-500'],
                    ];
                @endphp

                @foreach($modules as $module)
                    <a href="{{ route($module['route']) }}"
                       class="super-nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/5">
                        <div class="w-8 h-8 rounded-lg {{ $module['color'] }} flex items-center justify-center mr-3">
                            <i class="fas {{ $module['icon'] }} text-white text-sm"></i>
                        </div>
                        <span class="nav-text">{{ $module['label'] }}</span>
                        <span class="ml-auto text-white/50 text-xs">
                            <i class="fas fa-external-link-alt"></i>
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- AUDIT & REPORTS -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-chart-bar mr-2"></i>Audit & Reports
                </p>
            </div>

            <a href="{{ route('super-admin.reports.audit') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('super-admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list mr-3 text-lg"></i>
                <span class="nav-text">Audit Trail</span>
            </a>

            <a href="{{ route('super-admin.reports.activity') }}"
               class="super-nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-chart-pie mr-3 text-lg"></i>
                <span class="nav-text">Activity Reports</span>
            </a>

            <!-- SUPPORT -->
            <div class="mt-8 mb-3">
                <p class="px-4 py-2 text-xs font-bold text-white/50 uppercase tracking-widest group-title">
                    <i class="fas fa-life-ring mr-2"></i>Support
                </p>
            </div>

            <a href="#" class="super-nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-question-circle mr-3 text-lg"></i>
                <span class="nav-text">Super Admin Guide</span>
            </a>

            <a href="#" class="super-nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-bug mr-3 text-lg"></i>
                <span class="nav-text">Report Issue</span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
            <div class="text-center">
                <div class="text-xs text-white/50 mb-2">Super Admin Session</div>
                <div class="flex items-center justify-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                    <div class="text-xs text-white/70">Active • {{ now()->format('H:i') }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main id="mainContent" class="ml-0 lg:ml-64 pt-16 min-h-screen bg-gray-50">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-super-dark/5 to-super/5 border-b border-gray-200 px-6 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('super-admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-super hover:text-super-dark">
                                    <i class="fas fa-home mr-2"></i>
                                    Super Admin
                                </a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-800 mt-2">@yield('title', 'Super Admin Dashboard')</h1>
                    <p class="text-gray-600 mt-1">@yield('subtitle', 'Full system control and administration')</p>
                </div>
                <div class="mt-4 md:mt-0">
                    @yield('header-actions')
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="px-6 pt-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-gradient-to-r from-success/10 to-success/5 border-l-4 border-success rounded-r-lg animate-fade-in">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Success!</p>
                            <p class="text-gray-700">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-gradient-to-r from-danger/10 to-danger/5 border-l-4 border-danger rounded-r-lg animate-fade-in">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-danger/20 flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-circle text-danger"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Error!</p>
                            <p class="text-gray-700">{{ session('error') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-gradient-to-r from-warning/10 to-warning/5 border-l-4 border-warning rounded-r-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-warning/20 flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Please fix the following errors:</p>
                            <ul class="mt-2 list-disc list-inside text-gray-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="p-6">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white px-6 py-4 mt-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-gray-600 text-sm">
                    <p class="font-medium">
                        <i class="fas fa-shield-alt text-super mr-1"></i>
                        KTVTC Super Admin Portal v3.0
                    </p>
                    <p class="text-gray-500 mt-1">© {{ date('Y') }} Kenya TVET Colleges. All rights reserved.</p>
                </div>
                <div class="mt-2 md:mt-0">
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ now()->format('l, F j, Y H:i:s') }}
                        </div>
                        <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                        <span class="text-sm font-medium text-success">System Operational</span>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Modal Container -->
    <div id="modalContainer"></div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2 w-96"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 shadow-2xl">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-gray-700 font-semibold">Processing...</p>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // Mobile Menu Toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });

        // Close sidebar when clicking overlay
        document.getElementById('mobileOverlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.remove('active');
            this.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Dropdown Toggles
        const dropdowns = {
            'notificationBtn': 'notificationDropdown',
            'quickActionsBtn': 'quickActionsDropdown',
            'userDropdownBtn': 'userDropdown'
        };

        Object.keys(dropdowns).forEach(buttonId => {
            const button = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdowns[buttonId]);

            if (button && dropdown) {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');

                    // Close other dropdowns
                    Object.values(dropdowns).forEach(otherId => {
                        if (otherId !== dropdowns[buttonId]) {
                            const otherDropdown = document.getElementById(otherId);
                            if (otherDropdown) otherDropdown.classList.add('hidden');
                        }
                    });
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            Object.keys(dropdowns).forEach(buttonId => {
                const dropdown = document.getElementById(dropdowns[buttonId]);
                const button = document.getElementById(buttonId);

                if (dropdown && button && !dropdown.contains(e.target) && !button.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Load notifications via AJAX
        function loadNotifications() {
            $.ajax({
                url: '',
                method: 'GET',
                success: function(data) {
                    $('#notificationList').html(data);
                }
            });
        }

        // Initialize notifications
        document.getElementById('notificationBtn').addEventListener('click', loadNotifications);

        // Modal System
        window.openModal = function(modalId, options = {}) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                if (options.onOpen) options.onOpen();
            }
        };

        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        };

        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                window.closeModal(e.target.closest('.modal').id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal:not(.hidden)').forEach(modal => {
                    window.closeModal(modal.id);
                });
            }
        });

        // Toast System
        window.showToast = function(message, type = 'success', duration = 5000) {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();

            const colors = {
                success: { bg: 'bg-success/10', border: 'border-success', icon: 'fa-check-circle', text: 'text-success' },
                error: { bg: 'bg-danger/10', border: 'border-danger', icon: 'fa-exclamation-circle', text: 'text-danger' },
                warning: { bg: 'bg-warning/10', border: 'border-warning', icon: 'fa-exclamation-triangle', text: 'text-warning' },
                info: { bg: 'bg-info/10', border: 'border-info', icon: 'fa-info-circle', text: 'text-info' }
            };

            const color = colors[type] || colors.info;

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `p-4 rounded-xl shadow-lg border-l-4 ${color.border} ${color.bg} animate-fade-in`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${color.icon} ${color.text} mr-3 text-lg"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">${message}</p>
                    </div>
                    <button onclick="removeToast('${toastId}')" class="ml-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Auto remove
            setTimeout(() => {
                removeToast(toastId);
            }, duration);
        };

        window.removeToast = function(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        };

        // Loading Overlay
        window.showLoading = function(message = 'Processing...') {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.querySelector('p').textContent = message;
                overlay.classList.remove('hidden');
            }
        };

        window.hideLoading = function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('hidden');
        };

        // Form Submission with Loading
        window.submitForm = function(formId, options = {}) {
            const form = document.getElementById(formId);
            if (!form) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            const buttonText = options.buttonText || 'Processing...';

            if (submitBtn) {
                submitBtn.innerHTML = `
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    ${buttonText}
                `;
                submitBtn.disabled = true;
            }

            showLoading(options.loadingMessage || 'Submitting...');

            // Revert after 30 seconds
            setTimeout(() => {
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
                hideLoading();
            }, 30000);

            form.submit();
        };

        // DataTable Initialization
        window.initDataTable = function(tableId, options = {}) {
            const defaultOptions = {
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            };

            const mergedOptions = { ...defaultOptions, ...options };
            return $('#' + tableId).DataTable(mergedOptions);
        };

        // Bulk Actions
        window.selectAllRows = function(tableId) {
            const selectAll = document.querySelector(`#${tableId} .select-all`);
            const checkboxes = document.querySelectorAll(`#${tableId} .row-select`);

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        };

        window.getSelectedRows = function(tableId) {
            const checkboxes = document.querySelectorAll(`#${tableId} .row-select:checked`);
            return Array.from(checkboxes).map(cb => cb.value);
        };

        window.performBulkAction = function(tableId, action, url) {
            const selected = getSelectedRows(tableId);
            if (selected.length === 0) {
                showToast('Please select at least one item', 'warning');
                return;
            }

            if (confirm(`Are you sure you want to ${action} ${selected.length} item(s)?`)) {
                showLoading(`Processing ${action}...`);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selected
                    },
                    success: function(response) {
                        hideLoading();
                        showToast(response.message || 'Action completed successfully');
                        location.reload();
                    },
                    error: function(xhr) {
                        hideLoading();
                        showToast(xhr.responseJSON?.message || 'An error occurred', 'error');
                    }
                });
            }
        };

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            // Set active nav
            const currentPath = window.location.pathname;
            document.querySelectorAll('.super-nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href.replace(/\/$/, ''))) {
                    link.classList.add('active');
                }
            });

            // Initialize tooltips
            const tooltips = document.querySelectorAll('[data-tooltip]');
            tooltips.forEach(el => {
                el.addEventListener('mouseenter', function(e) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'fixed z-50 px-3 py-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg';
                    tooltip.textContent = this.dataset.tooltip;
                    document.body.appendChild(tooltip);

                    const rect = this.getBoundingClientRect();
                    tooltip.style.top = (rect.top - 40) + 'px';
                    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';

                    this.tooltipElement = tooltip;
                });

                el.addEventListener('mouseleave', function() {
                    if (this.tooltipElement) {
                        this.tooltipElement.remove();
                        this.tooltipElement = null;
                    }
                });
            });

            // Auto-refresh notifications every 60 seconds
            setInterval(loadNotifications, 60000);

            // Check for system alerts
            checkSystemAlerts();
        });

        // System Alerts Check
        function checkSystemAlerts() {
            $.ajax({
                url: '',
                method: 'GET',
                success: function(alerts) {
                    alerts.forEach(alert => {
                        if (alert.type === 'critical') {
                            showToast(alert.message, 'error', 10000);
                        }
                    });
                }
            });
        }

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const clockElements = document.querySelectorAll('.live-clock');
            clockElements.forEach(el => {
                el.textContent = `${dateString} • ${timeString}`;
            });
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>

    @yield('scripts')
</body>
</html>
