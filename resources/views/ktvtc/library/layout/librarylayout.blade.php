<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KTVTC Library Management System')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#E63946',  /* Vibrant red */
                        'secondary': '#000000', /* Black */
                        'light': '#FFFFFF',    /* White */
                        'dark': '#1D3557',     /* Dark blue */
                        'accent': '#A8DADC',   /* Light teal */
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
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link.active {
            background-color: #FFFFFF !important;
            color: #E63946 !important;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-sm h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="flex items-center" href="{{ route('library.dashboard') }}">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-2">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <span class="font-bold text-white text-xl">KTVTC Library</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="text-white focus:outline-none relative" id="notificationsDropdown">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-accent text-dark text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            3
                        </span>
                    </button>
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <span class="ml-2 hidden md:inline font-medium">Library Admin</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="fixed top-16 left-0 bottom-0 w-64 text-white z-20 overflow-y-auto sidebar hidden md:block" id="sidebar">
        <div class="p-4">
            <!-- User Profile -->
            <div class="flex items-center mb-6 p-3 bg-white bg-opacity-10 rounded-lg">
                <div class="relative mr-3">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-green-400 ring-2 ring-primary"></span>
                </div>
                <div>
                    <h6 class="font-medium">Library Admin</h6>
                    <p class="text-xs opacity-75">Administrator</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('library.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('library.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <!-- CATALOG MANAGEMENT -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">CATALOG MANAGEMENT</p>
                </li>
                <li>
                    <a href="{{ route('books.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}">
                        <i class="fas fa-book mr-3"></i> Books
                    </a>
                </li>
                <li>
                    <a href="{{ route('items.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="fas fa-copy mr-3"></i> Book Copies
                    </a>
                </li>
                <li>
                    <a href="{{ route('authors.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('authors.*') ? 'active' : '' }}">
                        <i class="fas fa-user-edit mr-3"></i> Authors
                    </a>
                </li>
                <li>
                    <a href="{{ route('book-categories.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('book-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags mr-3"></i> Categories
                    </a>
                </li>

                <!-- MEMBER MANAGEMENT -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">MEMBER MANAGEMENT</p>
                </li>
                <li>
                    <a href="{{ route('members.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
                        <i class="fas fa-users mr-3"></i> Members
                    </a>
                </li>
                <li>
                    <a href="{{ route('transactions.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt mr-3"></i> Transactions
                    </a>
                </li>
                <li>
                    <a href="{{ route('reservations.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check mr-3"></i> Reservations
                    </a>
                </li>
                <li>
                    <a href="{{ route('reading-histories.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('reading-histories.*') ? 'active' : '' }}">
                        <i class="fas fa-history mr-3"></i> Reading History
                    </a>
                </li>

                <!-- ACQUISITION & WEEDING -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">ACQUISITION & WEEDING</p>
                </li>
                <li>
                    <a href="{{ route('acquisition-requests.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('acquisition-requests.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart mr-3"></i> Acquisition Requests
                    </a>
                </li>
                <li>
                    <a href="{{ route('weeding-candidates.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('weeding-candidates.*') ? 'active' : '' }}">
                        <i class="fas fa-recycle mr-3"></i> Weeding Candidates
                    </a>
                </li>

                <!-- REPORTS & ANALYTICS -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">REPORTS & ANALYTICS</p>
                </li>
                <li>
                    <a href="{{ route('book-popularities.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('book-popularities.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line mr-3"></i> Book Popularity
                    </a>
                </li>
                <li>
                    <a href="{{ route('usage-statistics.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('usage-statistics.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar mr-3"></i> Usage Statistics
                    </a>
                </li>

                <!-- SYSTEM SETTINGS -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">SYSTEM SETTINGS</p>
                </li>
                <li>
                    <a href="{{ route('branches.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <i class="fas fa-building mr-3"></i> Branches
                    </a>
                </li>
                <li>
                    <a href="{{ route('fine-rules.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('fine-rules.*') ? 'active' : '' }}">
                        <i class="fas fa-money-check-alt mr-3"></i> Fine Rules
                    </a>
                </li>
                <li>
                    <a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell mr-3"></i> Notifications
                    </a>
                </li>

                <!-- ACCOUNT -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">ACCOUNT</p>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center px-3 py-3 text-sm rounded-md nav-link text-left">
                            <i class="fas fa-sign-out-alt mr-3"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-0 md:ml-64 mt-16 p-4 min-h-screen" id="mainContent">
        @yield('content')
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // Set active navigation link based on current URL
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href.replace(route('library.dashboard'), ''))) {
                    link.classList.add('active');
                }
            });
        });

        // Auto-hide sidebar on mobile after clicking a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    document.getElementById('sidebar').classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
