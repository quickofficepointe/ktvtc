<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KTVTC Finance @yield('title')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#059669',
                        'primary-dark': '#047857',
                        'primary-light': '#D1FAE5',
                        'secondary': '#0D9488',
                        'success': '#10B981',
                        'warning': '#F59E0B',
                        'danger': '#EF4444',
                        'info': '#3B82F6',
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
                        'spin-slow': 'spin 1.5s linear infinite',
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
            background: linear-gradient(180deg, #059669 0%, #047857 100%);
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
                width: 280px;
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

        .nav-link {
            transition: all 0.2s ease;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.85);
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            transform: translateX(3px);
        }

        .nav-link.active {
            background: white !important;
            color: #059669 !important;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .nav-link.active i {
            color: #059669 !important;
        }

        .nav-link i {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-primary {
            background: #059669;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #047857;
        }

        /* Card Styles */
        .finance-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #eef2f0;
        }

        .finance-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(5, 150, 105, 0.1);
        }

        /* Status Badges */
        .status-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-paid { background: #D1FAE5; color: #065F46; }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-failed { background: #FEE2E2; color: #B91C1C; }
        .status-verified { background: #DBEAFE; color: #1E40AF; }
        .status-unverified { background: #F3F4F6; color: #374151; }
        .status-reversed { background: #FEF2F2; color: #991B1B; }

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
            border-top: 4px solid #059669;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar for sidebar */
        .admin-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .admin-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .admin-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .admin-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Table Styles */
        .finance-table thead th {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 0.75rem 1rem;
        }

        .finance-table tbody tr {
            transition: all 0.2s ease;
        }

        .finance-table tbody tr:hover {
            background: rgba(5, 150, 105, 0.03);
        }

        /* Modal */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            animation: slideIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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

<!-- Top Navigation -->
<nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-lg z-50 border-b border-gray-200">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
        <!-- Left Section -->
        <div class="flex items-center space-x-4">
            <!-- Mobile Menu Toggle -->
            <button id="mobileMenuToggle" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Logo -->
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-coins text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-gray-800 text-lg">Finance <span class="text-primary">Department</span></h1>
                    <p class="text-xs text-gray-500">KTVTC Finance Management</p>
                </div>
            </div>
        </div>

        <!-- Center: Quick Stats -->
        <div class="hidden lg:flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                <span class="text-sm text-gray-600">System: <span class="font-semibold text-success">Operational</span></span>
            </div>
            <div class="h-6 w-px bg-gray-300"></div>
            <div class="text-sm text-gray-600">
                <i class="fas fa-users mr-1 text-primary"></i>
                <span class="font-semibold">{{ number_format(\App\Models\Student::count() ?? 0) }}</span> Students
            </div>
            <div class="h-6 w-px bg-gray-300"></div>
            <div class="text-sm text-gray-600">
                <i class="fas fa-clock mr-1 text-primary"></i>
                <span class="font-semibold">{{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-4">
            <!-- Quick Actions -->
            <div class="relative">
                <button id="quickActionsBtn" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                    <i class="fas fa-bolt"></i>
                    <span class="hidden sm:inline">Quick Actions</span>
                </button>

                <div id="quickActionsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                        <h3 class="font-bold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('finance.student-fees.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center mr-3">
                                <i class="fas fa-credit-card text-success"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Record Payment</p>
                                <p class="text-xs text-gray-500">Record student fee payment</p>
                            </div>
                        </a>

                        <a href="{{ route('finance.transactions.index') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center mr-3">
                                <i class="fas fa-exchange-alt text-info"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">View Transactions</p>
                                <p class="text-xs text-gray-500">Review all transactions</p>
                            </div>
                        </a>

                        <a href="{{ route('finance.student-fees.reports.outstanding') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Outstanding Balance</p>
                                <p class="text-xs text-gray-500">View outstanding fees</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" class="relative p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-lg"></i>
                    @php
                        $notificationCount = \App\Models\FeePayment::where('status', 'completed')->where('is_verified', false)->count();
                    @endphp
                    @if($notificationCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[20px] h-5 bg-danger text-white text-xs rounded-full flex items-center justify-center font-bold animate-pulse px-1">
                            {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                        </span>
                    @endif
                </button>

                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-800">Notifications</h3>
                            <span class="text-xs text-gray-500">{{ $notificationCount ?? 0 }} unread</span>
                        </div>
                    </div>
                    <div class="max-h-96 overflow-y-auto admin-scrollbar">
                        @php
                            $recentPayments = \App\Models\FeePayment::where('status', 'completed')
                                ->where('is_verified', false)
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($recentPayments as $payment)
                            <a href="{{ route('finance.student-fees.show', $payment) }}" class="block p-4 hover:bg-gray-50 border-b border-gray-100 transition-colors">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-credit-card text-primary"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">New Payment: KES {{ number_format($payment->amount, 2) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $payment->student->full_name ?? 'N/A' }} - {{ $payment->payment_method }}</p>
                                        <span class="text-xs text-gray-400 mt-1 block">{{ $payment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if(!$payment->is_verified)
                                        <span class="w-2 h-2 bg-warning rounded-full ml-2 flex-shrink-0"></span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center">
                                <i class="fas fa-check-circle text-gray-300 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-500">No pending verifications</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        <a href="{{ route('finance.student-fees.index', ['is_verified' => 0]) }}" class="block text-center text-primary hover:text-primary-dark font-medium text-sm transition-colors">
                            View all pending verifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="relative">
                <button id="userDropdownBtn" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center shadow-md">
                        <i class="fas fa-user-tie text-white text-sm"></i>
                    </div>
                    <div class="text-left hidden lg:block">
                        <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name ?? 'Finance Officer' }}</p>
                        <div class="flex items-center">
                            <span class="status-badge status-verified text-xs">Finance</span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs ml-2"></i>
                        </div>
                    </div>
                </button>

                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-user-tie text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">{{ Auth::user()->name ?? 'Finance Officer' }}</p>
                                <p class="text-sm text-gray-500">{{ Auth::user()->email ?? 'finance@ktvtc.ac.ke' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1 transition-colors">
                            <i class="fas fa-user-cog text-primary w-5 mr-3"></i>
                            <span class="font-medium text-gray-700">Profile Settings</span>
                        </a>
                        <div class="border-t border-gray-200 my-2"></div>
                        <form method="POST" action="{{ route('logout') }}" class="w-full" id="logoutForm">
                            @csrf
                            <button type="submit" class="flex items-center w-full p-3 rounded-lg hover:bg-red-50 text-danger font-semibold transition-colors">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar text-white pt-6 z-30 overflow-y-auto admin-scrollbar">
    <div class="px-4">
        <!-- Quick Search -->
        <div class="mb-6">
            <div class="relative">
                <input type="text"
                       id="sidebarSearch"
                       placeholder="Search modules..."
                       class="w-full pl-10 pr-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 text-sm transition-all">
                <i class="fas fa-search absolute left-3 top-3 text-white/60 text-sm"></i>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('finance.dashboard') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl mb-1 {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                <span class="nav-text font-semibold">Dashboard</span>
            </a>

            <!-- Student Fees Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider">
                    <i class="fas fa-user-graduate mr-2"></i>Student Fees
                </p>
            </div>

            <a href="{{ route('finance.student-fees.index') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('finance.student-fees.*') ? 'active' : '' }}">
                <i class="fas fa-credit-card mr-3 text-lg"></i>
                <span class="nav-text">All Payments</span>
                @php
                    $pendingVerification = \App\Models\FeePayment::where('status', 'completed')->where('is_verified', false)->count();
                @endphp
                @if($pendingVerification > 0)
                    <span class="badge ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                        {{ $pendingVerification }}
                    </span>
                @endif
            </a>

            <a href="{{ route('finance.student-fees.create') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-plus-circle mr-3 text-lg"></i>
                <span class="nav-text">Record Payment</span>
            </a>

            <a href="{{ route('finance.student-fees.reports.outstanding') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
                <span class="nav-text">Outstanding Balance</span>
            </a>

            <!-- Transactions Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </p>
            </div>

            <a href="{{ route('finance.transactions.index') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('finance.transactions.*') ? 'active' : '' }}">
                <i class="fas fa-list mr-3 text-lg"></i>
                <span class="nav-text">All Transactions</span>
            </a>

            <a href="{{ route('finance.transactions.mpesa') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-mobile-alt mr-3 text-lg"></i>
                <span class="nav-text">M-Pesa Transactions</span>
            </a>

            <a href="{{ route('finance.transactions.pending') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-clock mr-3 text-lg"></i>
                <span class="nav-text">Pending</span>
                @php
                    $pendingTransactions = \App\Models\PaymentTransaction::where('status', 'pending')->count();
                @endphp
                @if($pendingTransactions > 0)
                    <span class="badge ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                        {{ $pendingTransactions }}
                    </span>
                @endif
            </a>

            <!-- Reports Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </p>
            </div>

            <a href="{{ route('finance.reports.profit-loss') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-chart-line mr-3 text-lg"></i>
                <span class="nav-text">Profit & Loss</span>
            </a>

            <a href="{{ route('finance.reports.revenue') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-coins mr-3 text-lg"></i>
                <span class="nav-text">Revenue Report</span>
            </a>

            <a href="{{ route('finance.reports.expenses') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-shopping-cart mr-3 text-lg"></i>
                <span class="nav-text">Expenses Report</span>
            </a>

            <a href="{{ route('finance.reports.balance-sheet') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-balance-scale mr-3 text-lg"></i>
                <span class="nav-text">Balance Sheet</span>
            </a>

            <!-- Student Financials -->
            <div class="mt-6 mb-2">
                <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider">
                    <i class="fas fa-search mr-2"></i>Student Lookup
                </p>
            </div>

            <a href="{{ route('finance.students.search') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-search mr-3 text-lg"></i>
                <span class="nav-text">Search Students</span>
            </a>

            <!-- Settings -->
            <div class="mt-6 mb-2">
                <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider">
                    <i class="fas fa-cog mr-2"></i>Settings
                </p>
            </div>

            <a href="{{ route('finance.settings.index') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('finance.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog mr-3 text-lg"></i>
                <span class="nav-text">Settings</span>
            </a>

            <a href="{{ route('finance.settings.fee-structure') }}"
               class="nav-link flex items-center px-4 py-3 rounded-xl">
                <i class="fas fa-file-invoice mr-3 text-lg"></i>
                <span class="nav-text">Fee Structure</span>
            </a>
        </nav>

        <!-- System Status -->
        <div class="mt-8 pt-4 border-t border-white/20">
            <div class="px-4 py-3 bg-white/5 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-white/60">System Status</span>
                    <span class="text-xs text-success flex items-center">
                        <i class="fas fa-circle mr-1 text-xs animate-pulse"></i>
                        Operational
                    </span>
                </div>
                <div class="text-xs text-white/40">
                    <p>KTVTC Finance v1.0.0</p>
                    <p class="mt-1">{{ now()->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Main Content -->
<main id="mainContent" class="main-content pt-16 min-h-screen bg-gray-50 transition-all duration-300">
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('finance.dashboard') }}" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                                <i class="fas fa-home mr-2"></i>
                                Home
                            </a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">@yield('title', 'Finance Dashboard')</h1>
                <p class="text-gray-600 mt-1">@yield('subtitle', 'Finance Management Portal')</p>
            </div>
            <div class="mt-4 md:mt-0">
                @yield('header-actions')
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="px-6 pt-6">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg animate-fade-in shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Success</p>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.closest('.animate-fade-in').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg animate-fade-in shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Error</p>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                    <button onclick="this.closest('.animate-fade-in').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg animate-fade-in shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-yellow-800">Warning</p>
                        <p class="text-yellow-700">{{ session('warning') }}</p>
                    </div>
                    <button onclick="this.closest('.animate-fade-in').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg animate-fade-in shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Information</p>
                        <p class="text-blue-700">{{ session('info') }}</p>
                    </div>
                    <button onclick="this.closest('.animate-fade-in').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-yellow-800">Please fix the following errors</p>
                        <ul class="mt-2 list-disc list-inside text-yellow-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button onclick="this.closest('.bg-yellow-50').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="p-6">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="border-t border-gray-200 bg-white px-6 py-4 mt-8 shadow-inner">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="text-gray-600 text-sm">
                <p class="font-medium">
                    <i class="fas fa-coins text-primary mr-1"></i>
                    KTVTC Finance Department v1.0.0
                </p>
                <p class="text-gray-500 mt-1">© {{ date('Y') }} KTVTC. All rights reserved.</p>
            </div>
            <div class="mt-2 md:mt-0">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1 text-primary"></i>
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

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('open');
                document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            });
        }

        // Close sidebar on window resize (when switching to desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
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

                    Object.keys(dropdowns).forEach(otherId => {
                        if (otherId !== buttonId) {
                            const otherDropdown = document.getElementById(dropdowns[otherId]);
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

                if (dropdown && !dropdown.contains(e.target) && !button?.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Sidebar Search
        const searchInput = document.getElementById('sidebarSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const navLinks = document.querySelectorAll('.nav-link');

                navLinks.forEach(link => {
                    const text = link.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        link.style.display = 'flex';
                    } else {
                        link.style.display = 'none';
                    }
                });
            });
        }

        // Initialize DataTables
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

        // Modal Functions
        window.openModal = function(modalId, size = 'lg') {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    const sizeClasses = {
                        'sm': 'max-w-md',
                        'md': 'max-w-lg',
                        'lg': 'max-w-2xl',
                        'xl': 'max-w-4xl',
                        '2xl': 'max-w-6xl'
                    };
                    modalContent.classList.remove('max-w-md', 'max-w-lg', 'max-w-2xl', 'max-w-4xl', 'max-w-6xl');
                    modalContent.classList.add(sizeClasses[size] || 'max-w-2xl');
                }
            }
        };

        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        };

        // Toast System
        window.showToast = function(message, type = 'success', duration = 5000) {
            toastr[type](message);
        };

        // AJAX with loading
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
    });
</script>

@yield('scripts')
</body>
</html>
