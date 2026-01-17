<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KTVTC Admin')</title>

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
                        'primary': '#B91C1C',   // Website deep red
                        'secondary': '#BF1F30', // Slightly lighter red
                        'light': '#FFFFFF',
                        'dark': '#1D3557',
                        'accent': '#A8DADC',
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
            background: linear-gradient(180deg, #B91C1C 0%, #BF1F30 100%);
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link.active {
            background-color: #FFFFFF !important;
            color: #B91C1C !important;
        }
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .submenu.open {
            max-height: 500px;
        }
        .rotate-90 {
            transform: rotate(90deg);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-sm h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="flex items-center" href="{{ route('mschool.dashboard') }}">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-2">
                        <i class="fas fa-tachometer-alt text-white"></i>
                    </div>
                    <span class="font-bold text-white text-xl">KTVTC Admin <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded ml-2">Dashboard</span></span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <span class="ml-2 hidden md:inline font-medium">{{ Auth::user()->name ?? 'Admin User' }}</span>
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
                    <h6 class="font-medium">{{ Auth::user()->name ?? 'Admin User' }}</h6>
                    <p class="text-xs opacity-75">Mobile School Admin</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('mschool.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('mschool.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <!-- Course Categories -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="categoriesMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-layer-group mr-3"></i> Course Categories
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="categoriesMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="categoriesSubmenu">
                        <li>
                            <a href="{{ route('course-categories.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('course-categories.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Categories
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Courses Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="coursesMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-book mr-3"></i> Courses
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="coursesMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="coursesSubmenu">
                        <li>
                            <a href="{{ route('mcourses.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Courses
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Students Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="studentsMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-users mr-3"></i> Students
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="studentsMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="studentsSubmenu">
                        <li>
                            <a href="{{ route('students.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Students
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Subjects Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="subjectsMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-book-open mr-3"></i> Course Subjects
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="subjectsMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="subjectsSubmenu">
                        <li>
                            <a href="{{ route('subjects.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('course-subjects.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Subjects
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Enrollments Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="enrollmentsMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-clipboard-list mr-3"></i> Enrollments
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="enrollmentsMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="enrollmentsSubmenu">
                        <li>
                            <a href="{{ route('enrollments.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Enrollments
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Exams Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="examsMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-file-alt mr-3"></i> Exams & Results
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="examsMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="examsSubmenu">
                        <li>
                            <a href="{{ route('exams.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('exams.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Exams
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('exam-results.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('exam-results.*') ? 'active' : '' }}">
                                <i class="fas fa-poll mr-3"></i> Exam Results
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Certificates Section -->
                <li>
                    <div class="nav-link px-3 py-3 text-sm rounded-md cursor-pointer flex justify-between items-center" id="certificatesMenuToggle">
                        <div class="flex items-center">
                            <i class="fas fa-certificate mr-3"></i> Certificates
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="certificatesMenuIcon"></i>
                    </div>
                    <ul class="submenu ml-6 mt-1 space-y-1" id="certificatesSubmenu">
                        <li>
                            <a href="{{ route('certificate-templates.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('certificate-templates.*') ? 'active' : '' }}">
                                <i class="fas fa-file-contract mr-3"></i> Templates
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('certificates.index') }}" class="flex items-center px-3 py-2 text-sm rounded-md nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
                                <i class="fas fa-list mr-3"></i> All Certificates
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Attendance Section -->
                <li>
                    <a href="{{ route('attendances.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check mr-3"></i> Attendance
                    </a>
                </li>

                <!-- Logout -->
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

        // Set active navigation link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent default for form submission or external links
                if (this.tagName === 'BUTTON' || this.closest('form') || this.getAttribute('href').startsWith('http')) {
                    return;
                }

                // Remove active class from all links
                document.querySelectorAll('.nav-link').forEach(item => {
                    item.classList.remove('active');
                });

                // Add active class to clicked link
                this.classList.add('active');
            });
        });

        // Toggle submenus
        function setupSubmenuToggle(toggleId, submenuId, iconId) {
            const toggle = document.getElementById(toggleId);
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(iconId);

            if (toggle && submenu && icon) {
                toggle.addEventListener('click', function() {
                    submenu.classList.toggle('open');
                    icon.classList.toggle('rotate-90');
                });
            }
        }

        // Initialize all submenu toggles
        setupSubmenuToggle('categoriesMenuToggle', 'categoriesSubmenu', 'categoriesMenuIcon');
        setupSubmenuToggle('coursesMenuToggle', 'coursesSubmenu', 'coursesMenuIcon');
        setupSubmenuToggle('studentsMenuToggle', 'studentsSubmenu', 'studentsMenuIcon');
        setupSubmenuToggle('subjectsMenuToggle', 'subjectsSubmenu', 'subjectsMenuIcon');
        setupSubmenuToggle('enrollmentsMenuToggle', 'enrollmentsSubmenu', 'enrollmentsMenuIcon');
        setupSubmenuToggle('examsMenuToggle', 'examsSubmenu', 'examsMenuIcon');
        setupSubmenuToggle('certificatesMenuToggle', 'certificatesSubmenu', 'certificatesMenuIcon');

        // Auto-open submenus for active routes
        document.addEventListener('DOMContentLoaded', function() {
            const activeRoutes = {
                'course-categories': 'categoriesSubmenu',
                'courses': 'coursesSubmenu',
                'students': 'studentsSubmenu',
                'course-subjects': 'subjectsSubmenu',
                'enrollments': 'enrollmentsSubmenu',
                'exams': 'examsSubmenu',
                'exam-results': 'examsSubmenu',
                'certificate-templates': 'certificatesSubmenu',
                'certificates': 'certificatesSubmenu'
            };

            Object.entries(activeRoutes).forEach(([route, submenuId]) => {
                if (window.location.href.includes(route)) {
                    const submenu = document.getElementById(submenuId);
                    const toggle = document.getElementById(submenuId.replace('Submenu', 'MenuToggle'));
                    const icon = document.getElementById(submenuId.replace('Submenu', 'MenuIcon'));

                    if (submenu && toggle && icon) {
                        submenu.classList.add('open');
                        icon.classList.add('rotate-90');
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
