<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kenswed College Admin')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
                        'primary': '#B91C1C',
                        'primary-dark': '#991B1B',
                        'primary-light': '#FEE2E2',
                        'secondary': '#BF1F30',
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
        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FA;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            background: linear-gradient(180deg, #B91C1C 0%, #991B1B 100%);
            box-shadow: 4px 0 20px rgba(185, 28, 28, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-sidebar.collapsed {
            width: 70px;
        }

        .admin-sidebar.collapsed .nav-text,
        .admin-sidebar.collapsed .group-title {
            display: none;
        }

        .admin-sidebar.collapsed .badge {
            display: none;
        }

        .admin-nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            color: rgba(255, 255, 255, 0.9);
        }

        .admin-nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            color: white;
        }

        .admin-nav-link.active {
            background: white !important;
            color: #B91C1C !important;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .admin-nav-link.active i {
            color: #B91C1C !important;
        }

        .admin-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #B91C1C;
            border-radius: 0 2px 2px 0;
        }

        /* Card Styles */
        .admin-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #eef2f0;
        }

        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(185, 28, 28, 0.1);
        }

        /* Table Styles */
        .admin-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .admin-table thead th {
            background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }

        .admin-table tbody tr {
            transition: all 0.2s ease;
        }

        .admin-table tbody tr:hover {
            background: rgba(185, 28, 28, 0.03);
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

        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-active { background: #D1FAE5; color: #065F46; }
        .status-inactive { background: #F3F4F6; color: #374151; }
        .status-warning { background: #FEE2E2; color: #B91C1C; }
        .status-success { background: #D1FAE5; color: #065F46; }

        /* Custom Scrollbar */
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

        /* Modal Styles */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            animation: slideIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Loading States */
        .loading-spinner {
            border: 3px solid #F3F4F6;
            border-top: 3px solid #B91C1C;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        .loading-spinner-sm {
            border: 2px solid #F3F4F6;
            border-top: 2px solid #B91C1C;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Button Loading State */
        .btn-loading {
            opacity: 0.7;
            cursor: wait;
        }

        .btn-loading i {
            animation: spin 1s linear infinite;
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.active {
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

            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>

    @yield('styles')
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>

    <!-- Global Loading Overlay -->
    <div id="globalLoadingOverlay" class="fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center min-w-[280px]">
            <div class="loading-spinner mb-4"></div>
            <p id="loadingMessage" class="text-gray-800 font-semibold text-lg">Processing...</p>
            <p class="text-gray-500 text-sm mt-2">Please wait</p>
        </div>
    </div>

    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-lg z-40 border-b border-gray-200">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <!-- Left Section -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuToggle" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Sidebar Toggle (Desktop) -->
                <button id="sidebarToggle" class="hidden lg:block p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Logo -->
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center mr-3 shadow-md">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-gray-800 text-lg">Kenswed <span class="text-primary">College</span></h1>
                        <p class="text-xs text-gray-500">Administration Portal</p>
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
                            <a href="{{ route('admin.students.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user-graduate text-success"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Add Student</p>
                                    <p class="text-xs text-gray-500">Create new student record</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.enrollments.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-book-open text-info"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">New Enrollment</p>
                                    <p class="text-xs text-gray-500">Enroll student in course</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.fee-payments.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-credit-card text-primary"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Record Payment</p>
                                    <p class="text-xs text-gray-500">Record fee payment</p>
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
                            $notificationCount = \App\Models\Message::where('status', '!=', 'viewed')->count();
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
                                $recentNotifications = \App\Models\Message::orderBy('created_at', 'desc')->limit(5)->get();
                            @endphp
                            @forelse($recentNotifications as $notification)
                                <a href="#" class="block p-4 hover:bg-gray-50 border-b border-gray-100 transition-colors">
                                    <div class="flex items-start">
                                        <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas {{ $notification->icon ?? 'fa-bell' }} text-primary"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate">{{ $notification->subject ?? 'New Notification' }}</p>
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $notification->message ?? 'You have a new notification' }}</p>
                                            <span class="text-xs text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if($notification->status != 'viewed')
                                            <span class="w-2 h-2 bg-primary rounded-full ml-2 flex-shrink-0"></span>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="p-6 text-center">
                                    <i class="fas fa-bell-slash text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-sm text-gray-500">No notifications</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                            <a href="{{ route('admin.messages.index') }}" class="block text-center text-primary hover:text-primary-dark font-medium text-sm transition-colors">
                                View all notifications
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
                            <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name ?? 'Admin User' }}</p>
                            <div class="flex items-center">
                                <span class="status-badge status-active text-xs">{{ Auth::user()->role == 2 ? 'Administrator' : 'Manager' }}</span>
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
                                    <p class="font-bold text-gray-800">{{ Auth::user()->name ?? 'Admin User' }}</p>
                                    <p class="text-sm text-gray-500">{{ Auth::user()->email ?? 'admin@kenswed.ac.ke' }}</p>
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
    <aside id="sidebar" class="admin-sidebar fixed top-16 left-0 bottom-0 w-64 text-white pt-6 z-30 overflow-y-auto admin-scrollbar">
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
                <a href="{{ route('admin.dashboard') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl mb-1 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                    <span class="nav-text font-semibold">Dashboard</span>
                </a>

                <!-- MANAGEMENT SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-cog mr-2"></i>Management
                    </p>
                </div>

                <!-- User Management -->
                <a href="{{ route('admin.users.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog mr-3 text-lg"></i>
                    <span class="nav-text">User Management</span>
                    @php
                        $pendingUsers = \App\Models\User::where('is_approved', false)->count();
                    @endphp
                    @if($pendingUsers > 0)
                        <span class="badge ml-auto bg-warning text-white text-xs px-2 py-1 rounded-full">
                            {{ $pendingUsers }}
                        </span>
                    @endif
                </a>

                <!-- STUDENT MANAGEMENT SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-graduation-cap mr-2"></i>Student Management
                    </p>
                </div>

                <!-- Students -->
                <a href="{{ route('admin.students.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate mr-3 text-lg"></i>
                    <span class="nav-text">Students</span>
                </a>

                <!-- Enrollments -->
                <a href="{{ route('admin.enrollments.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}">
                    <i class="fas fa-book-open mr-3 text-lg"></i>
                    <span class="nav-text">Enrollments</span>
                </a>

                <!-- Fee Payments -->
                <a href="{{ route('admin.fee-payments.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card mr-3 text-lg"></i>
                    <span class="nav-text">Fee Payments</span>
                </a>

                <!-- EXAMINATIONS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-pencil-alt mr-2"></i>Examinations
                    </p>
                </div>

                <!-- Exam Registrations -->
                <a href="{{ route('admin.exam-registrations.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.exam-registrations.*') ? 'active' : '' }}">
                    <i class="fas fa-registered mr-3 text-lg"></i>
                    <span class="nav-text">Exam Registrations</span>
                </a>

                <!-- APPLICATIONS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-file-alt mr-2"></i>Applications
                    </p>
                </div>

                <!-- Course Applications -->
                <a href="{{ route('admin.applications.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap mr-3 text-lg"></i>
                    <span class="nav-text">Course Applications</span>
                    @php
                        $pendingApplications = \App\Models\Application::where('status', 'pending')->count();
                    @endphp
                    @if($pendingApplications > 0)
                        <span class="badge ml-auto bg-warning text-white text-xs px-2 py-1 rounded-full">
                            {{ $pendingApplications }}
                        </span>
                    @endif
                </a>

                <!-- Event Applications -->
                <a href="{{ route('admin.event-applications.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.event-applications.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check mr-3 text-lg"></i>
                    <span class="nav-text">Event Applications</span>
                </a>

                <!-- COMMUNICATIONS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-comments mr-2"></i>Communications
                    </p>
                </div>

                <!-- Messages -->
                <a href="{{ route('admin.messages.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope mr-3 text-lg"></i>
                    <span class="nav-text">Messages</span>
                    @if($notificationCount > 0)
                        <span class="badge ml-auto bg-primary text-white text-xs px-2 py-1 rounded-full">
                            {{ $notificationCount }}
                        </span>
                    @endif
                </a>

                <!-- Subscriptions -->
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper mr-3 text-lg"></i>
                    <span class="nav-text">Subscriptions</span>
                </a>

                <!-- ANALYTICS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-bold text-white/60 uppercase tracking-wider group-title">
                        <i class="fas fa-chart-line mr-2"></i>Analytics
                    </p>
                </div>

                <!-- Analytics Dashboard -->
                <a href="{{ route('admin.analytics.dashboard') }}"
                   class="admin-nav-link flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie mr-3 text-lg"></i>
                    <span class="nav-text">Analytics</span>
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
                        <p>Kenswed College v3.0.0</p>
                        <p class="mt-1">{{ now()->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main id="mainContent" class="ml-0 lg:ml-64 pt-16 min-h-screen bg-gray-50 transition-all duration-300">
        <!-- Breadcrumb -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                                    <i class="fas fa-home mr-2"></i>
                                    Home
                                </a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-800 mt-2">@yield('title', 'Admin Dashboard')</h1>
                    <p class="text-gray-600 mt-1">@yield('subtitle', 'Management Portal')</p>
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
                            <p class="font-semibold text-green-800">Success!</p>
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
                            <p class="font-semibold text-red-800">Error!</p>
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
                            <p class="font-semibold text-yellow-800">Warning!</p>
                            <p class="text-yellow-700">{{ session('warning') }}</p>
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
                            <p class="font-semibold text-yellow-800">Please fix the following errors:</p>
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
                        <i class="fas fa-shield-alt text-primary mr-1"></i>
                        Kenswed College Admin Portal v3.0.0
                    </p>
                    <p class="text-gray-500 mt-1">© {{ date('Y') }} Kenswed Technical and Vocational Training College. All rights reserved.</p>
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
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Sidebar Toggle (Desktop)
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('mainContent');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.remove('lg:ml-64');
                        mainContent.classList.add('lg:ml-16');
                        localStorage.setItem('sidebarCollapsed', 'true');
                    } else {
                        mainContent.classList.remove('lg:ml-16');
                        mainContent.classList.add('lg:ml-64');
                        localStorage.setItem('sidebarCollapsed', 'false');
                    }
                });

                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.remove('lg:ml-64');
                    mainContent.classList.add('lg:ml-16');
                }
            }

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
                    const navLinks = document.querySelectorAll('.admin-nav-link');

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

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[id$="Modal"]:not(.hidden), [id$="Dropdown"]:not(.hidden)').forEach(element => {
                        element.classList.add('hidden');
                    });
                    hideGlobalLoading();
                }
            });
        });

        // ============ GLOBAL LOADING FUNCTIONS ============
        window.showGlobalLoading = function(message = 'Processing...') {
            const overlay = document.getElementById('globalLoadingOverlay');
            const messageEl = document.getElementById('loadingMessage');
            if (overlay) {
                if (messageEl) messageEl.textContent = message;
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        window.hideGlobalLoading = function() {
            const overlay = document.getElementById('globalLoadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        };

        // ============ BUTTON LOADING STATE ============
        window.setButtonLoading = function(button, isLoading = true, loadingText = 'Processing...') {
            if (!button) return;

            if (isLoading) {
                button.dataset.originalText = button.innerHTML;
                button.disabled = true;
                button.classList.add('btn-loading');
                button.innerHTML = `<i class="fas fa-spinner mr-2"></i>${loadingText}`;
            } else {
                button.disabled = false;
                button.classList.remove('btn-loading');
                if (button.dataset.originalText) {
                    button.innerHTML = button.dataset.originalText;
                }
            }
        };

        // ============ FORM SUBMIT WITH LOADING ============
        window.submitFormWithLoading = function(formId, options = {}) {
            const form = document.getElementById(formId);
            if (!form) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            const loadingMessage = options.loadingMessage || 'Submitting...';
            const buttonText = options.buttonText || 'Processing...';

            if (submitBtn) {
                setButtonLoading(submitBtn, true, buttonText);
            }

            showGlobalLoading(loadingMessage);

            // Set timeout to prevent infinite loading
            setTimeout(() => {
                setButtonLoading(submitBtn, false);
                hideGlobalLoading();
            }, 30000);

            form.submit();
        };

        // ============ AJAX REQUEST WITH LOADING ============
        window.ajaxWithLoading = async function(url, options = {}) {
            const showLoader = options.showLoader !== false;
            const loadingMessage = options.loadingMessage || 'Loading...';

            if (showLoader) showGlobalLoading(loadingMessage);

            try {
                const response = await fetch(url, {
                    method: options.method || 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        ...options.headers
                    },
                    body: options.body || null
                });

                if (showLoader) hideGlobalLoading();
                return await response.json();
            } catch (error) {
                if (showLoader) hideGlobalLoading();
                throw error;
            }
        };

        // ============ TABLE ROW SELECTION ============
        window.selectAllRows = function(tableId) {
            const selectAll = document.querySelector(`#${tableId} .select-all`);
            const checkboxes = document.querySelectorAll(`#${tableId} .row-checkbox`);
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll?.checked || false;
            });
        };

        window.getSelectedRows = function(tableId) {
            const checkboxes = document.querySelectorAll(`#${tableId} .row-checkbox:checked`);
            return Array.from(checkboxes).map(cb => cb.value);
        };

        // ============ TOAST SYSTEM ============
        window.showToast = function(message, type = 'success', duration = 5000) {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();

            const colors = {
                success: { bg: 'bg-green-50', border: 'border-green-500', icon: 'fa-check-circle', text: 'text-green-600' },
                error: { bg: 'bg-red-50', border: 'border-red-500', icon: 'fa-exclamation-circle', text: 'text-red-600' },
                warning: { bg: 'bg-yellow-50', border: 'border-yellow-500', icon: 'fa-exclamation-triangle', text: 'text-yellow-600' },
                info: { bg: 'bg-blue-50', border: 'border-blue-500', icon: 'fa-info-circle', text: 'text-blue-600' }
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
                    <button onclick="removeToast('${toastId}')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toastContainer.appendChild(toast);

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

        // ============ MODAL SYSTEM ============
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

        // ============ CONFIRM DIALOG ============
        window.confirmAction = function(message, callback) {
            if (confirm(message)) {
                if (callback) callback();
                return true;
            }
            return false;
        };
    </script>

    @yield('scripts')
</body>
</html>
