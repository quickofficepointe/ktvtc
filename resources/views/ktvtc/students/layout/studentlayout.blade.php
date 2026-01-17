<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                        'secondary': '#000000',
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
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-sm h-16">
        <div class="flex items-center justify-between h-full px-4">
            <!-- Logo / Title -->
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="font-bold text-white text-xl">Student Dashboard</span>
            </div>

            <!-- User Dropdown -->
            <div class="relative">
                <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                    <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <span class="ml-2 hidden md:inline font-medium">My Account</span>
                    <i class="fas fa-chevron-down ml-1"></i>
                </button>
                <!-- Dropdown menu -->
                <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-md hidden">
                    <a href="{{ route('student.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-home mr-2 text-primary"></i> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-edit mr-2 text-primary"></i> Edit Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2 text-primary"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="fixed top-16 left-0 bottom-0 w-64 text-white z-20 overflow-y-auto sidebar hidden md:block" id="sidebar">
        <div class="p-4">
            <!-- Student Profile -->
            <div class="flex items-center mb-6 p-3 bg-white bg-opacity-10 rounded-lg">
                <div class="relative mr-3">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user-graduate text-white"></i>
                    </div>
                </div>
                <div>
                    <h6 class="font-medium">Student Name</h6>
                    <p class="text-xs opacity-75">Enrolled</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('student.dashboard') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link active">
                        <i class="fas fa-home mr-3"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-user-edit mr-3"></i> Edit Profile
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center px-3 py-3 text-sm rounded-md nav-link w-full text-left">
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

    <script>
        // Toggle sidebar on mobile
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // Dropdown toggle
        document.getElementById('userDropdown').addEventListener('click', function() {
            document.getElementById('dropdownMenu').classList.toggle('hidden');
        });

        // Active nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
