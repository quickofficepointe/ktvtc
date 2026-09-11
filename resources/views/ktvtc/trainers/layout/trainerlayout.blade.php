<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTVTC Trainer — @yield('title', 'Dashboard')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:   '#B91C1C',
                        secondary: '#BF1F30',
                        light:     '#FFFFFF',
                        dark:      '#1D3557',
                        accent:    '#A8DADC',
                    },
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #F8F9FA; }
        .sidebar { background: linear-gradient(180deg, #B91C1C 0%, #BF1F30 100%); }
        .nav-link { transition: all .3s ease; }
        .nav-link:hover { background-color: rgba(255,255,255,.1); }
        .nav-link.active { background-color: #FFFFFF !important; color: #B91C1C !important; }
        .badge { display:inline-block; padding:.15rem .5rem; border-radius:.375rem; font-size:.7rem; font-weight:600; }
    </style>
</head>
<body class="bg-gray-50">

    {{-- Top Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-sm h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="flex items-center" href="{{ route('trainers.dashboard') }}">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-2">
                        <i class="fas fa-chalkboard-teacher text-white"></i>
                    </div>
                    <span class="font-bold text-white text-xl">
                        Trainer Portal
                        <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded ml-2">KTVTC</span>
                    </span>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('trainers.attachments.index') }}"
                   class="relative text-white hidden md:inline"
                   title="Pending attachments">
                    <i class="fas fa-bell"></i>
                </a>
                <div class="flex items-center text-white">
                    <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <span class="ml-2 hidden md:inline font-medium">
                        {{ auth()->user()->name ?? 'Trainer' }}
                    </span>
                </div>
            </div>
        </div>
    </nav>

    {{-- Sidebar --}}
    <div class="fixed top-16 left-0 bottom-0 w-64 text-white z-20 overflow-y-auto sidebar hidden md:block" id="sidebar">
        <div class="p-4">

            {{-- Profile --}}
            <div class="flex items-center mb-6 p-3 bg-white bg-opacity-10 rounded-lg">
                <div class="relative mr-3">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-green-400 ring-2 ring-primary"></span>
                </div>
                <div class="truncate">
                    <h6 class="font-medium truncate">{{ auth()->user()->name ?? 'Trainer' }}</h6>
                    <p class="text-xs opacity-75">Trainer</p>
                </div>
            </div>

            {{-- Nav --}}
            @php
                $isDashboard  = request()->routeIs('trainers.dashboard');
                $isStudents   = request()->routeIs('trainers.students.*');
                $isAttach     = request()->routeIs('trainers.attachments.*');
            @endphp

            <ul class="space-y-2">
                <li>
                    <a href="{{ route('trainers.dashboard') }}"
                       class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ $isDashboard ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                </li>

                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">
                        Teaching
                    </p>
                </li>
                <li>
                    <a href="{{ route('trainers.students.index') }}"
                       class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ $isStudents ? 'active' : '' }}">
                        <i class="fas fa-users mr-3"></i> My Students
                    </a>
                </li>
                <li>
                    <a href="{{ route('trainers.attachments.index') }}"
                       class="flex items-center px-3 py-3 text-sm rounded-md nav-link {{ $isAttach ? 'active' : '' }}">
                        <i class="fas fa-briefcase mr-3"></i> Attachments
                    </a>
                </li>

                <li class="mt-4">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white text-opacity-70">
                        Account
                    </p>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center px-3 py-3 text-sm rounded-md nav-link">
                        <i class="fas fa-user-cog mr-3"></i> Profile
                    </a>
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

    {{-- Main Content --}}
    <div class="ml-0 md:ml-64 mt-16 p-4 min-h-screen">
        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-4 rounded-md bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        document.getElementById('mobileSidebarToggle')?.addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden');
        });
    </script>
</body>
</html>