<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTVTC Admin</title>

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
          'secondary': '#BF1F30', // Slightly lighter red (used in navbar/socials)
          'light': '#FFFFFF',
          'dark': '#1D3557',      // Keep if needed for contrast
          'accent': '#A8DADC',    // Optional accent if you want teal
        },
        fontFamily: {
          sans: ['Inter', 'sans-serif'], // Match website font
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
                        <i class="fas fa-tachometer-alt text-white"></i>
                    </div>
                    <span class="font-bold text-white text-xl">Admin Dashboard <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded ml-2">Template</span></span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="flex items-center text-white focus:outline-none" id="userDropdown">
                        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <span class="ml-2 hidden md:inline font-medium">Admin User</span>
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
                    <h6 class="font-medium">Admin User</h6>
                    <p class="text-xs opacity-75">Administrator</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="space-y-2">
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link active">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">MANAGEMENT</p>
                </li>
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-users mr-3"></i> User Management
                    </a>
                </li>

                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">ORDERS & SALES</p>
                </li>
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-shopping-cart mr-3"></i> All Orders
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-chart-bar mr-3"></i> Sales Reports
                    </a>
                </li>

                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">PRODUCTS</p>
                </li>
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-tire mr-3"></i> Product Categories
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-tire mr-3"></i> Products
                    </a>
                </li>

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

        // Set active navigation link
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
