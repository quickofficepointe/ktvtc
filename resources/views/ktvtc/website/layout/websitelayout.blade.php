<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTVTC Website Manager</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'primary': '#B91C1C',   // Deep red
              'secondary': '#BF1F30',
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
                <a class="flex items-center" href="#">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-2">
                        <i class="fas fa-globe text-white"></i>
                    </div>
                    <span class="font-bold text-white text-xl">Website Manager</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <span class="ml-2 hidden md:inline font-medium">Manager</span>
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
                    <h6 class="font-medium">Manager</h6>
                    <p class="text-xs opacity-75">Website Admin</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('website.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('website.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <!-- Website Content -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">Website Content</p>
                </li>
                <li>
                    <a href="{{ route('banners.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('banners.index') ? 'active' : '' }}">
                        <i class="fas fa-image mr-3"></i> Banners
                    </a>
                </li>
                <li>
                    <a href="{{ route('blogs.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('blogs.index') ? 'active' : '' }}">
                        <i class="fas fa-blog mr-3"></i> Blogs
                    </a>
                </li>
                <li>
                    <a href="{{ route('blog-categories.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('blog-categories.index') ? 'active' : '' }}">
                        <i class="fas fa-th-list mr-3"></i> Blog Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('galleries.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('galleries.index') ? 'active' : '' }}">
                        <i class="fas fa-photo-video mr-3"></i> Gallery
                    </a>
                </li>
                <li>
                    <a href="{{ route('campuses.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('campuses.index') ? 'active' : '' }}">
                        <i class="fas fa-school mr-3"></i> Campuses
                    </a>
                </li>
                <li>
                    <a href="{{ route('downloads.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('downloads.index') ? 'active' : '' }}">
                        <i class="fas fa-file-download mr-3"></i> Downloads
                    </a>
                </li>
                <li>
                    <a href="{{ route('testimonials.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('testimonials.index') ? 'active' : '' }}">
                        <i class="fas fa-comment-dots mr-3"></i> Testimonials
                    </a>
                </li>

                <!-- Academic -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">Academic</p>
                </li>
                <li>
                    <a href="{{ route('departments.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('departments.index') ? 'active' : '' }}">
                        <i class="fas fa-building mr-3"></i> Departments
                    </a>
                </li>
                <li>
                    <a href="{{ route('courses.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                        <i class="fas fa-book mr-3"></i> Courses
                    </a>
                </li>
   <!--
                <li>
                    <a href="{{ route('course-intakes.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('course-intakes.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt mr-3"></i> Course Intakes
                    </a>
                </li>
                <li>
                     -->
                    <a href="{{ route('mschools.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('mschools.index') ? 'active' : '' }}">
                        <i class="fas fa-map-marker-alt mr-3"></i> Mobile Schools
                    </a>
                </li>

                <!-- Events & Communication -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">Events & Communication</p>
                </li>
                <li>
                    <a href="{{ route('events.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('events.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt mr-3"></i> Events
                    </a>
                </li>
 <!--
                <li>
                    <a href="{{ route('messages.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}">
                        <i class="fas fa-envelope mr-3"></i> Messages
                    </a>
                </li>
                <li>
                    <a href="{{ route('subscriptions.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('subscriptions.index') ? 'active' : '' }}">
                        <i class="fas fa-user-check mr-3"></i> Subscriptions
                    </a>
                </li>
                 -->
                <li>
                    <a href="{{ route('faqs.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('faqs.index') ? 'active' : '' }}">
                        <i class="fas fa-question-circle mr-3"></i> FAQs
                    </a>
                </li>

                <!-- Settings -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">Settings</p>
                </li>
                <li>
                    <a href="{{ route('about-pages.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('about-pages.index') ? 'active' : '' }}">
                        <i class="fas fa-info-circle mr-3"></i> About Page
                    </a>
                </li>
                <li>
                    <a href="{{ route('contact-infos.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('contact-infos.index') ? 'active' : '' }}">
                        <i class="fas fa-phone mr-3"></i> Contact Info
                    </a>
                </li>
                <li>
                    <a href="{{ route('policies.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('policies.index') ? 'active' : '' }}">
                        <i class="fas fa-file-alt mr-3"></i> Policies
                    </a>
                </li>
                <li>
                    <a href="{{ route('partners.index') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ request()->routeIs('partners.index') ? 'active' : '' }}">
                        <i class="fas fa-handshake mr-3"></i> Partners
                    </a>
                </li>

                <!-- Account -->
                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">Account</p>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-3 text-sm rounded-md nav-link text-left hover:bg-white hover:bg-opacity-10 transition">
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
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        // Initialize Summernote
        $(document).ready(function() {
            // Initialize on any textarea with class 'summernote'
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });

        // Toggle sidebar on mobile
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('mobileSidebarToggle');

            if (window.innerWidth < 768 &&
                !sidebar.contains(event.target) &&
                !toggleBtn.contains(event.target) &&
                !sidebar.classList.contains('hidden')) {
                sidebar.classList.add('hidden');
            }
        });

        // Set active navigation link based on current URL
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;

            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }

                link.addEventListener('click', function() {
                    document.querySelectorAll('.nav-link').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Close sidebar on mobile after clicking a link
                    if (window.innerWidth < 768) {
                        document.getElementById('sidebar').classList.add('hidden');
                    }
                });
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.remove('hidden');
            } else {
                document.getElementById('sidebar').classList.add('hidden');
            }
        });
    </script>

</body>
</html>
