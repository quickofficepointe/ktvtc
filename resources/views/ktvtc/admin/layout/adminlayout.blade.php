<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KTVTC TVET Admin Portal')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#B91C1C',
                        'secondary': '#BF1F30',
                        'primary-light': '#FEE2E2',
                        'primary-dark': '#991B1B',
                        'light': '#FFFFFF',
                        'dark': '#1D3557',
                        'accent': '#A8DADC',
                        'gray-50': '#F9FAFB',
                        'gray-100': '#F3F4F6',
                        'gray-200': '#E5E7EB',
                        'gray-800': '#1F2937',
                        'success': '#10B981',
                        'warning': '#F59E0B',
                        'danger': '#EF4444',
                        'info': '#3B82F6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
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
            background-color: #F8F9FA;
            overflow-x: hidden;
        }

        /* Sidebar - KTVTC Red Theme */
        .sidebar {
            background: linear-gradient(180deg, #B91C1C 0%, #BF1F30 100%);
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar.collapsed .group-title {
            display: none;
        }

        .sidebar.collapsed .badge {
            display: none;
        }

        .nav-link {
            transition: all 0.2s ease;
            position: relative;
            color: rgba(255, 255, 255, 0.9);
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            color: white;
        }

        .nav-link.active {
            background-color: #FFFFFF !important;
            color: #B91C1C !important;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .nav-link.active i {
            color: #B91C1C !important;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #B91C1C;
            border-radius: 0 2px 2px 0;
        }

        .group-title {
            color: rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }

        /* Submenu Styles */
        .submenu {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }

        .submenu-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.2s ease;
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
        }

        .submenu-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .submenu-link.active-submenu {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        /* Modal Styles */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease-out;
        }

        .modal-content {
            animation: slideIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Table Styles */
        .table-container {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .table-row:hover {
            background-color: #F9FAFB;
        }

        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-active {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-inactive {
            background-color: #F3F4F6;
            color: #374151;
        }

        .status-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Card Hover Effects */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(185, 28, 28, 0.1);
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 40;
                height: 100vh;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar:not(.collapsed) {
                width: 260px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 z-30 shadow-sm">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <!-- Left Section -->
            <div class="flex items-center space-x-4">
                <!-- Sidebar Toggle -->
                <button id="sidebarToggle" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Logo -->
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <div class="ml-2">
                        <h1 class="font-bold text-gray-800 text-lg leading-tight">KTVTC</h1>
                        <p class="text-xs text-gray-500">TVET Admin Portal</p>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-3">
                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationBtn" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors relative">
                        <i class="fas fa-bell text-lg"></i>
                        @php
                            $notificationCount = \App\Models\Message::where('status', '!=', 'viewed')->count();
                        @endphp
                        @if($notificationCount > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-danger text-white text-xs rounded-full flex items-center justify-center animate-pulse">
                                {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                        @endif
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-40">
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Notifications</h3>
                            <span class="text-xs text-gray-500">{{ $notificationCount ?? 0 }} unread</span>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @php
                                $recentNotifications = \App\Models\Message::orderBy('created_at', 'desc')->limit(5)->get();
                            @endphp
                            @forelse($recentNotifications as $notification)
                                <a href="#" class="block p-4 hover:bg-gray-50 border-b border-gray-100">
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
                        <div class="p-4 border-t border-gray-200">
                            <a href="{{ route('admin.messages.index') }}" class="block text-center text-primary hover:text-primary-dark text-sm font-medium">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="relative">
                    <button id="quickActionsBtn" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
                        <i class="fas fa-bolt"></i>
                        <span class="hidden sm:inline">Quick Actions</span>
                    </button>

                    <!-- Quick Actions Dropdown - UPDATED WITH NEW ROUTES -->
                    <div id="quickActionsDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-40">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-800">Quick Actions</h3>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('admin.tvet.students.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                    <i class="fas fa-user-plus text-primary"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">Add Student</p>
                                    <p class="text-xs text-gray-500">Register new student</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.tvet.enrollments.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user-graduate text-success"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">New Enrollment</p>
                                    <p class="text-xs text-gray-500">Enroll student in course</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.tvet.course-fee-templates.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-file-invoice text-info"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">Fee Template</p>
                                    <p class="text-xs text-gray-500">Create fee template</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.tvet.academic-terms.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-calendar-plus text-warning"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">Academic Term</p>
                                    <p class="text-xs text-gray-500">Add new term/quarter</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="relative">
                    <button id="userDropdownBtn" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center">
                            <i class="fas fa-user-tie text-primary"></i>
                        </div>
                        <div class="text-left hidden md:block">
                            <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->role == 2 ? 'Administrator' : 'Manager' }}</p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-40">
                        <div class="p-4 border-b border-gray-200">
                            <p class="font-medium text-gray-800">{{ Auth::user()->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->email ?? 'admin@ktvtc.ac.ke' }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-light text-primary">
                                    {{ Auth::user()->role == 2 ? 'Administrator' : 'Manager' }}
                                </span>
                            </p>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('profile.edit') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <i class="fas fa-user-cog text-gray-500 w-5 mr-3"></i>
                                <span class="text-sm text-gray-700">Profile Settings</span>
                            </a>
                            <a href="{{ route('admin.settings.general.index') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 mb-1">
                                <i class="fas fa-cog text-gray-500 w-5 mr-3"></i>
                                <span class="text-sm text-gray-700">System Settings</span>
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex items-center p-3 rounded-lg hover:bg-gray-50 text-red-600 w-full text-left">
                                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                    <span class="text-sm">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar - COMPLETELY UPDATED WITH NEW FEE MANAGEMENT SYSTEM -->
    <aside id="sidebar" class="sidebar fixed top-16 left-0 bottom-0 w-64 z-20 overflow-y-auto custom-scrollbar">
        <div class="p-4">
            <!-- Quick Search -->
            <div class="mb-6">
                <div class="relative">
                    <input type="text"
                           id="sidebarSearch"
                           placeholder="Search modules..."
                           class="w-full pl-10 pr-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 text-sm">
                    <i class="fas fa-search absolute left-3 top-3 text-white/60 text-sm"></i>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3 text-lg {{ request()->routeIs('admin.dashboard') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text font-medium">Dashboard</span>
                </a>

                <!-- MANAGEMENT SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        Management
                    </p>
                </div>

                <!-- User Management -->
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog mr-3 text-lg {{ request()->routeIs('admin.users.*') ? 'text-primary' : 'text-white/80' }}"></i>
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

                <!-- TVET/CDACC SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        TVET/CDACC
                    </p>
                </div>

                <!-- Students Dropdown -->
                <div class="relative">
                    <button id="studentsMenuBtn"
                            class="w-full nav-link flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.students.*') ? 'active' : '' }}">
                        <div class="flex items-center">
                            <i class="fas fa-user-graduate mr-3 text-lg {{ request()->routeIs('admin.tvet.students.*') ? 'text-primary' : 'text-white/80' }}"></i>
                            <span class="nav-text">Students</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ request()->routeIs('admin.tvet.students.*') ? 'text-primary' : 'text-white/60' }}"></i>
                    </button>
                    <div id="studentsMenu" class="submenu ml-4 mt-1 p-2 space-y-1 {{ request()->routeIs('admin.tvet.students.*') ? 'block' : 'hidden' }}">
                        <a href="{{ route('admin.tvet.students.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.students.index') ? 'active-submenu' : '' }}">
                            <i class="fas fa-list mr-2 text-xs"></i>
                            All Students
                        </a>
                        <a href="{{ route('admin.tvet.students.create') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.students.create') ? 'active-submenu' : '' }}">
                            <i class="fas fa-plus-circle mr-2 text-xs"></i>
                            Add New Student
                        </a>
                        <a href="{{ route('admin.tvet.students.import.view') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.students.import.view') || request()->routeIs('admin.tvet.students.import.process') ? 'active-submenu' : '' }}">
                            <i class="fas fa-upload mr-2 text-xs"></i>
                            Bulk Import
                        </a>
                        <a href="{{ route('admin.tvet.students.export') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.students.export') ? 'active-submenu' : '' }}">
                            <i class="fas fa-download mr-2 text-xs"></i>
                            Export Students
                        </a>
                    </div>
                </div>

                <!-- NEW: Academic Terms -->
                <a href="{{ route('admin.tvet.academic-terms.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.academic-terms.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt mr-3 text-lg {{ request()->routeIs('admin.tvet.academic-terms.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Academic Terms</span>
                </a>

                <!-- NEW: Fee Categories (8 Simple Categories) -->
                <a href="{{ route('admin.tvet.fee-categories.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.fee-categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags mr-3 text-lg {{ request()->routeIs('admin.tvet.fee-categories.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Fee Categories</span>
                </a>

                <!-- NEW: Course Fee Templates -->
                <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.course-fee-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice mr-3 text-lg {{ request()->routeIs('admin.tvet.course-fee-templates.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Fee Templates</span>
                </a>

                <!-- NEW: Enrollments Dropdown (with Fee Items) -->
                <div class="relative">
                    <button id="enrollmentsMenuBtn"
                            class="w-full nav-link flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.enrollments.*') ? 'active' : '' }}">
                        <div class="flex items-center">
                            <i class="fas fa-user-graduate mr-3 text-lg {{ request()->routeIs('admin.tvet.enrollments.*') ? 'text-primary' : 'text-white/80' }}"></i>
                            <span class="nav-text">Enrollments</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ request()->routeIs('admin.tvet.enrollments.*') ? 'text-primary' : 'text-white/60' }}"></i>
                    </button>
                    <div id="enrollmentsMenu" class="submenu ml-4 mt-1 p-2 space-y-1 {{ request()->routeIs('admin.tvet.enrollments.*') ? 'block' : 'hidden' }}">
                        <a href="{{ route('admin.tvet.enrollments.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.enrollments.index') ? 'active-submenu' : '' }}">
                            <i class="fas fa-list mr-2 text-xs"></i>
                            All Enrollments
                        </a>
                        <a href="{{ route('admin.tvet.enrollments.create') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.enrollments.create') ? 'active-submenu' : '' }}">
                            <i class="fas fa-plus-circle mr-2 text-xs"></i>
                            New Enrollment
                        </a>
                        <a href="{{ route('admin.tvet.enrollments.reports.enrollment') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.enrollments.reports.enrollment') ? 'active-submenu' : '' }}">
                            <i class="fas fa-chart-line mr-2 text-xs"></i>
                            Enrollment Report
                        </a>
                        <a href="{{ route('admin.tvet.enrollments.reports.financial') }}"
                           class="submenu-link {{ request()->routeIs('admin.tvet.enrollments.reports.financial') ? 'active-submenu' : '' }}">
                            <i class="fas fa-money-bill-wave mr-2 text-xs"></i>
                            Financial Report
                        </a>
                    </div>
                </div>

                <!-- Registrations -->
                <a href="{{ route('admin.tvet.registrations.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.registrations.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check mr-3 text-lg {{ request()->routeIs('admin.tvet.registrations.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Registrations</span>
                    @php
                        $pendingRegistrations = \App\Models\Registration::where('status', 'pending')->count();
                    @endphp
                    @if($pendingRegistrations > 0)
                        <span class="badge ml-auto bg-warning text-white text-xs px-2 py-1 rounded-full">
                            {{ $pendingRegistrations }}
                        </span>
                    @endif
                </a>

                <!-- CDACC -->
                <a href="{{ route('admin.tvet.cdacc.registrations.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.tvet.cdacc.*') ? 'active' : '' }}">
                    <i class="fas fa-certificate mr-3 text-lg {{ request()->routeIs('admin.tvet.cdacc.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">CDACC</span>
                    @php
                        $pendingCdacc = \App\Models\CdaccRegistration::where('cdacc_status', 'pending')->count();
                    @endphp
                    @if($pendingCdacc > 0)
                        <span class="badge ml-auto bg-info text-white text-xs px-2 py-1 rounded-full">
                            {{ $pendingCdacc }}
                        </span>
                    @endif
                </a>

                <!-- APPLICATIONS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        Applications
                    </p>
                </div>

                <!-- Course Applications -->
                <a href="{{ route('admin.applications.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap mr-3 text-lg {{ request()->routeIs('admin.applications.*') ? 'text-primary' : 'text-white/80' }}"></i>
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
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.event-applications.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check mr-3 text-lg {{ request()->routeIs('admin.event-applications.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Event Applications</span>
                </a>

                <!-- COMMUNICATIONS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        Communications
                    </p>
                </div>

                <!-- Messages -->
                <a href="{{ route('admin.messages.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope mr-3 text-lg {{ request()->routeIs('admin.messages.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Messages</span>
                    @if($notificationCount > 0)
                        <span class="badge ml-auto bg-primary text-white text-xs px-2 py-1 rounded-full">
                            {{ $notificationCount }}
                        </span>
                    @endif
                </a>

                <!-- Subscriptions -->
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper mr-3 text-lg {{ request()->routeIs('admin.subscriptions.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Subscriptions</span>
                </a>

                <!-- ANALYTICS SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        Analytics
                    </p>
                </div>

                <!-- Analytics Dashboard -->
                <a href="{{ route('admin.analytics.dashboard.index') }}"
                   class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line mr-3 text-lg {{ request()->routeIs('admin.analytics.*') ? 'text-primary' : 'text-white/80' }}"></i>
                    <span class="nav-text">Analytics</span>
                </a>

                <!-- SYSTEM SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        System
                    </p>
                </div>

                <!-- Settings Dropdown - UPDATED WITH CORRECT ROUTES -->
                <div class="relative">
                    <button id="settingsMenuBtn"
                            class="w-full nav-link flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-3 text-lg {{ request()->routeIs('admin.settings.*') ? 'text-primary' : 'text-white/80' }}"></i>
                            <span class="nav-text">Settings</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ request()->routeIs('admin.settings.*') ? 'text-primary' : 'text-white/60' }}"></i>
                    </button>
                    <div id="settingsMenu" class="submenu ml-4 mt-1 p-2 space-y-1 {{ request()->routeIs('admin.settings.*') ? 'block' : 'hidden' }}">
                        <a href="{{ route('admin.settings.general.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.settings.general.*') ? 'active-submenu' : '' }}">
                            <i class="fas fa-sliders-h mr-2 text-xs"></i>
                            General Settings
                        </a>
                        <a href="{{ route('admin.settings.academic.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.settings.academic.*') ? 'active-submenu' : '' }}">
                            <i class="fas fa-book mr-2 text-xs"></i>
                            Academic Settings
                        </a>
                        <a href="{{ route('admin.settings.payment.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.settings.payment.*') ? 'active-submenu' : '' }}">
                            <i class="fas fa-credit-card mr-2 text-xs"></i>
                            Payment Settings
                        </a>
                        <a href="{{ route('admin.settings.cdacc.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.settings.cdacc.*') ? 'active-submenu' : '' }}">
                            <i class="fas fa-certificate mr-2 text-xs"></i>
                            CDACC Settings
                        </a>
                        <a href="{{ route('admin.settings.backup.index') }}"
                           class="submenu-link {{ request()->routeIs('admin.settings.backup.*') ? 'active-submenu' : '' }}">
                            <i class="fas fa-database mr-2 text-xs"></i>
                            Backup & Restore
                        </a>
                    </div>
                </div>

                <!-- SUPPORT SECTION -->
                <div class="mt-6 mb-2">
                    <p class="px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider group-title">
                        Support
                    </p>
                </div>

                <!-- Help & Support -->
                <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg">
                    <i class="fas fa-question-circle mr-3 text-lg text-white/80"></i>
                    <span class="nav-text">Help & Support</span>
                </a>

                <!-- Documentation -->
                <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg">
                    <i class="fas fa-file-alt mr-3 text-lg text-white/80"></i>
                    <span class="nav-text">Documentation</span>
                </a>
            </nav>

            <!-- System Status -->
            <div class="mt-8 pt-4 border-t border-white/20">
                <div class="px-4 py-3 bg-white/5 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-white/60">System Status</span>
                        <span class="text-xs text-success flex items-center">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            Online
                        </span>
                    </div>
                    <div class="text-xs text-white/40">
                        <p>Version 2.1.0</p>
                        <p class="mt-1">Last backup: {{ now()->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main id="mainContent" class="main-content ml-0 md:ml-64 pt-16 min-h-screen bg-gray-50 transition-all duration-300">
        <!-- Breadcrumb -->
        <div class="bg-white border-b border-gray-200 px-6 py-3">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <!-- Content Area -->
        <div class="p-6">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                        <p class="text-gray-600 mt-1 text-sm">@yield('subtitle', 'Manage your TVET/CDACC operations')</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        @yield('header-actions')
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg animate-slide-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg animate-slide-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg animate-slide-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-yellow-600 hover:text-yellow-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Main Content -->
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white px-6 py-4 mt-auto">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-gray-600 text-sm">
                    <p>&copy; {{ date('Y') }} KTVTC TVET Management System. All rights reserved.</p>
                </div>
                <div class="mt-2 md:mt-0 flex items-center space-x-4">
                    <span class="text-gray-600 text-sm flex items-center">
                        <i class="fas fa-server mr-1"></i>
                        v2.1.0
                    </span>
                    <span class="text-gray-300">|</span>
                    <span class="text-success text-sm flex items-center">
                        <i class="fas fa-circle text-xs mr-1"></i>
                        System Online
                    </span>
                </div>
            </div>
        </footer>
    </main>

    <!-- Modal Container -->
    <div id="modalContainer"></div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <!-- Alpine.js for reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');

            function toggleSidebar() {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    mainContent.classList.remove('md:ml-64');
                    mainContent.classList.add('md:ml-16');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    mainContent.classList.remove('md:ml-16');
                    mainContent.classList.add('md:ml-64');
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            }

            // Check localStorage
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('md:ml-64');
                mainContent.classList.add('md:ml-16');
            }

            // Toggle buttons
            if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);

            // Dropdown Toggles - UPDATED WITH ENROLLMENTS MENU
            const dropdowns = {
                'notificationBtn': 'notificationDropdown',
                'quickActionsBtn': 'quickActionsDropdown',
                'userDropdownBtn': 'userDropdown',
                'studentsMenuBtn': 'studentsMenu',
                'enrollmentsMenuBtn': 'enrollmentsMenu',
                'settingsMenuBtn': 'settingsMenu'
            };

            Object.keys(dropdowns).forEach(buttonId => {
                const button = document.getElementById(buttonId);
                const dropdown = document.getElementById(dropdowns[buttonId]);

                if (button && dropdown) {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');

                        // Rotate chevron for menu buttons
                        if (buttonId.includes('MenuBtn')) {
                            const chevron = button.querySelector('.fa-chevron-down');
                            if (chevron) {
                                chevron.classList.toggle('rotate-180');
                            }
                        }
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

                        // Reset chevron rotation
                        if (buttonId.includes('MenuBtn') && button) {
                            const chevron = button.querySelector('.fa-chevron-down');
                            if (chevron) {
                                chevron.classList.remove('rotate-180');
                            }
                        }
                    }
                });
            });

            // Sidebar Search
            const searchInput = document.getElementById('sidebarSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const navLinks = document.querySelectorAll('.nav-link, .submenu-link');

                    navLinks.forEach(link => {
                        const text = link.textContent.toLowerCase();
                        const parent = link.closest('.relative');

                        if (text.includes(searchTerm)) {
                            if (parent) {
                                parent.style.display = 'block';
                            }
                            link.style.display = 'flex';
                        } else {
                            if (parent && !parent.querySelectorAll('.nav-link:not(.hidden), .submenu-link:not(.hidden)').length) {
                                parent.style.display = 'none';
                            }
                            link.style.display = 'none';
                        }
                    });
                });
            }

            // Mobile sidebar handling
            if (window.innerWidth < 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('md:ml-64');
            }
        });

        // Modal System
        window.openModal = function(modalId, size = 'lg') {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('max-w-sm', 'max-w-md', 'max-w-lg', 'max-w-xl', 'max-w-2xl', 'max-w-3xl', 'max-w-4xl', 'max-w-5xl', 'max-w-6xl', 'max-w-7xl');
                    modalContent.classList.add(`max-w-${size}`);
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

        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                window.closeModal(e.target.closest('[id$="Modal"]')?.id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id$="Modal"]:not(.hidden)').forEach(modal => {
                    window.closeModal(modal.id);
                });
            }
        });

        // Toast System
        window.showToast = function(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 ${
                type === 'success' ? 'bg-green-50 border-l-4 border-green-500' :
                type === 'error' ? 'bg-red-50 border-l-4 border-red-500' :
                type === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-500' :
                'bg-blue-50 border-l-4 border-blue-500'
            }`;

            const icon = type === 'success' ? 'fa-check-circle' :
                        type === 'error' ? 'fa-exclamation-circle' :
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

            const iconColor = type === 'success' ? 'text-green-500' :
                            type === 'error' ? 'text-red-500' :
                            type === 'warning' ? 'text-yellow-500' : 'text-blue-500';

            toast.innerHTML = `
                <div class="flex-shrink-0">
                    <i class="fas ${icon} ${iconColor}"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium ${
                        type === 'success' ? 'text-green-800' :
                        type === 'error' ? 'text-red-800' :
                        type === 'warning' ? 'text-yellow-800' : 'text-blue-800'
                    }">${message}</p>
                </div>
                <button onclick="removeToast('${toastId}')" class="ml-4 ${
                    type === 'success' ? 'text-green-400 hover:text-green-600' :
                    type === 'error' ? 'text-red-400 hover:text-red-600' :
                    type === 'warning' ? 'text-yellow-400 hover:text-yellow-600' : 'text-blue-400 hover:text-blue-600'
                }">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                removeToast(toastId);
            }, 5000);
        };

        window.removeToast = function(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        };

        // Form loading state
        window.submitForm = function(formId, buttonText = 'Processing...') {
            const form = document.getElementById(formId);
            if (!form) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${buttonText}`;
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 30000);
            }

            form.submit();
        };

        // Table row selection
        window.selectAllRows = function(tableId) {
            const selectAll = document.getElementById(`selectAll-${tableId}`);
            const checkboxes = document.querySelectorAll(`#${tableId} .row-checkbox`);

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        };

        window.getSelectedRows = function(tableId) {
            const checkboxes = document.querySelectorAll(`#${tableId} .row-checkbox:checked`);
            return Array.from(checkboxes).map(cb => cb.value);
        };
    </script>

    @yield('scripts')
</body>
</html>
