<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KTVTC Student Dashboard</title>

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
                        'primary': '#E63946',
                        'primary-dark': '#C1121F',
                        'secondary': '#000000',
                        'light': '#FFFFFF',
                        'dark': '#1D3557',
                        'accent': '#A8DADC',
                        'success': '#10B981',
                        'warning': '#F59E0B',
                        'danger': '#EF4444',
                        'info': '#3B82F6',
                    },
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
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
            font-family: 'DM Sans', sans-serif;
            background-color: #F8F9FA;
        }
        .sidebar {
            background: linear-gradient(180deg, #E63946 0%, #C1121F 100%);
            transition: all 0.3s ease;
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: #FFFFFF !important;
            color: #E63946 !important;
        }
        .nav-link.active i {
            color: #E63946 !important;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 40;
            }
            .sidebar.active {
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
                z-index: 35;
            }
            .mobile-overlay.active {
                display: block;
            }
        }
    </style>

    @yield('styles')
</head>
<body class="bg-gray-50">
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-md h-16">
        <div class="flex items-center justify-between h-full px-4">
            <!-- Logo / Title -->
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <span class="font-bold text-white text-lg hidden sm:inline">KTVTC Student Portal</span>
                    <span class="font-bold text-white text-lg sm:hidden">Portal</span>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="relative">
                <button class="flex items-center text-white focus:outline-none hover:bg-white hover:bg-opacity-10 rounded-lg px-2 py-1 transition-colors" id="userDropdown">
                    <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="ml-2 hidden md:inline font-medium">{{ auth()->user()->name ?? 'Student' }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                </button>

                <!-- Dropdown menu -->
                <div id="dropdownMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 hidden z-50">
                    <div class="p-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name ?? 'Student' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('student.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-home w-5 text-primary"></i>
                            <span class="ml-2">Dashboard</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user-edit w-5 text-primary"></i>
                            <span class="ml-2">Edit Profile</span>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span class="ml-2">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar fixed top-16 left-0 bottom-0 w-64 text-white z-30 overflow-y-auto" id="sidebar">
        <div class="p-4">
            <!-- Student Profile -->
            <div class="flex items-center mb-6 p-3 bg-white bg-opacity-10 rounded-xl">
                <div class="relative mr-3">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user-graduate text-white text-lg"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h6 class="font-semibold text-sm truncate">{{ auth()->user()->name ?? 'Student Name' }}</h6>
                    <p class="text-xs opacity-75">Student</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('student.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-lg nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home w-5 mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('student.fees.index') }}" class="flex items-center px-3 py-3 text-sm rounded-lg nav-link {{ request()->routeIs('student.fees.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card w-5 mr-3"></i>
                        <span>Fee Statement</span>
                        @php
                            $user = auth()->user();
                            $student = $user ? $user->student : null;
                            $hasBalance = $student ? \App\Models\Enrollment::where('student_id', $student->id)->where('balance', '>', 0)->exists() : false;
                        @endphp
                        @if($hasBalance)
                            <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">Due</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-3 text-sm rounded-lg nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="fas fa-user-edit w-5 mr-3"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="pt-4 mt-2 border-t border-white border-opacity-20">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center px-3 py-3 text-sm rounded-lg nav-link w-full text-left hover:bg-white hover:bg-opacity-10 transition-colors">
                            <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Footer Info -->
        <div class="absolute bottom-0 left-0 right-0 p-4 text-xs text-white text-center opacity-60">
            <p>KTVTC Student Portal</p>
            <p>v1.0.0</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-0 md:ml-64 mt-16 p-4 md:p-6 min-h-screen transition-all duration-300" id="mainContent">
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar on mobile
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mobileOverlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
                });
            }

            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Dropdown toggle
            const userDropdown = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');

            if (userDropdown && dropdownMenu) {
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }

            // Close sidebar when clicking a link on mobile
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        sidebar.classList.remove('active');
                        mobileOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) return;

            const toastId = 'toast-' + Date.now();
            const colors = {
                success: 'bg-green-50 border-green-500 text-green-700',
                error: 'bg-red-50 border-red-500 text-red-700',
                warning: 'bg-yellow-50 border-yellow-500 text-yellow-700',
                info: 'bg-blue-50 border-blue-500 text-blue-700'
            };

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `p-3 mb-2 rounded-lg border-l-4 ${colors[type] || colors.info} shadow-lg animate-fade-in`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                    <span class="text-sm">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }

        // Loading overlay function
        function showLoading(message = 'Processing...') {
            let overlay = document.getElementById('loadingOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'loadingOverlay';
                overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                overlay.innerHTML = `
                    <div class="bg-white rounded-lg p-6 flex flex-col items-center">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mb-3"></div>
                        <p class="text-gray-700" id="loadingMessage">Processing...</p>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
            const msgEl = document.getElementById('loadingMessage');
            if (msgEl) msgEl.textContent = message;
            overlay.classList.remove('hidden');
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('hidden');
        }

        // Add toast container if not exists
        if (!document.getElementById('toastContainer')) {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'fixed bottom-4 right-4 z-50 space-y-2 w-96';
            document.body.appendChild(container);
        }
    </script>

    @yield('scripts')
</body>
</html>
