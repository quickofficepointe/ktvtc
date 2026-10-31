<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KTVTC Academics — @yield('title', 'Dashboard')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#B91C1C', 'primary-dark': '#991B1B', secondary: '#BF1F30' },
                    fontFamily: { sans: ['DM Sans', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #F8F9FA; }
        .sidebar { background: linear-gradient(180deg, #B91C1C 0%, #BF1F30 100%); color: #fff; }
        .nav-link { color: #fff; transition: all .2s ease; }
        .nav-link:hover { background-color: rgba(255,255,255,.1); }
        .nav-link.active { background-color: #FFFFFF !important; color: #B91C1C !important; }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50">

    <nav class="fixed top-0 left-0 right-0 z-30 bg-primary shadow-sm h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center">
                <button class="mr-4 text-white md:hidden" id="mobileSidebarToggle"><i class="fas fa-bars"></i></button>
                <a class="flex items-center" href="{{ route('academics.dashboard') }}">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-2"><i class="fas fa-graduation-cap text-white"></i></div>
                    <span class="font-bold text-white text-xl">Academics <span class="text-sm bg-white/20 px-2 py-1 rounded ml-2">KTVTC</span></span>
                </a>
            </div>
            <div class="flex items-center text-white">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center"><i class="fas fa-user-graduate"></i></div>
                <span class="ml-2 hidden md:inline font-medium">{{ auth()->user()->name ?? 'Academics' }}</span>
            </div>
        </div>
    </nav>

    <div class="fixed top-16 left-0 bottom-0 w-64 z-20 overflow-y-auto sidebar hidden md:block" id="sidebar">
        <div class="p-4">
            <div class="flex items-center mb-6 p-3 bg-white/10 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-3"><i class="fas fa-user-graduate"></i></div>
                <div class="truncate">
                    <h6 class="font-medium truncate">{{ auth()->user()->name ?? 'Academics' }}</h6>
                    <p class="text-xs opacity-75">Academics office</p>
                </div>
            </div>

            @php
                $nav = [
                    ['academics.dashboard', 'fa-tachometer-alt', 'Dashboard', 'academics.dashboard'],
                    'Teaching',
                    ['academics.trainers.index', 'fa-chalkboard-teacher', 'Trainers', 'academics.trainers.*'],
                    ['academics.index', 'fa-book-open', 'Courses & units', 'academics.index|academics.course'],
                    ['course-changes.index', 'fa-exchange-alt', 'Course changes', 'course-changes.*'],
                    'Marks',
                    ['academics.results', 'fa-calculator', 'Marks & grades', 'academics.results'],
                    ['academics.review', 'fa-check-double', 'Results to publish', 'academics.review*'],
                    ['academics.scales', 'fa-sliders-h', 'Grading scales', 'academics.scales'],
                    'Evaluations',
                    ['academics.evaluations', 'fa-star', 'Trainer evaluations', 'academics.evaluations'],
                    ['academics.evaluations.questions', 'fa-list-ul', 'Evaluation questions', 'academics.evaluations.questions'],
                    'Exam bodies',
                    ['academics.exam-registrations.index', 'fa-id-card', 'Exam registrations', 'academics.exam-registrations.index|academics.exam-registrations.show|academics.exam-registrations.create|academics.exam-registrations.edit'],
                    ['academics.exam-registrations.reports.summary', 'fa-chart-bar', 'Exam results summary', 'academics.exam-registrations.reports.*'],
                ];
            @endphp
            <ul class="space-y-1">
                @foreach($nav as $item)
                    @if(is_string($item))
                        <li class="pt-4"><p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/70">{{ $item }}</p></li>
                    @else
                        @php [$route, $icon, $label, $pattern] = $item; @endphp
                        <li>
                            <a href="{{ route($route) }}" class="flex items-center px-3 py-2.5 text-sm rounded-md nav-link {{ request()->routeIs(...explode('|', $pattern)) ? 'active' : '' }}">
                                <i class="fas {{ $icon }} w-5 mr-3 text-center"></i>{{ $label }}
                            </a>
                        </li>
                    @endif
                @endforeach
                <li class="pt-4"><p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/70">Account</p></li>
                <li><a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2.5 text-sm rounded-md nav-link"><i class="fas fa-user-cog w-5 mr-3 text-center"></i>Profile</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-2.5 text-sm rounded-md nav-link text-left"><i class="fas fa-sign-out-alt w-5 mr-3 text-center"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="ml-0 md:ml-64 mt-16 min-h-screen">
        @hasSection('subtitle')
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    @hasSection('breadcrumb')<ol class="flex items-center text-sm text-gray-500 mb-1">@yield('breadcrumb')</ol>@endif
                    <h1 class="text-xl font-bold text-gray-900">@yield('title')</h1>
                    <p class="text-sm text-gray-500">@yield('subtitle')</p>
                </div>
                <div class="flex flex-wrap gap-2">@yield('header-actions')</div>
            </div>
        @endif

        <div class="p-4 md:p-6">
            @foreach(['success' => 'bg-green-50 border-green-200 text-green-800 fa-check-circle', 'error' => 'bg-red-50 border-red-200 text-red-800 fa-times-circle',
                      'warning' => 'bg-amber-50 border-amber-200 text-amber-800 fa-exclamation-triangle', 'info' => 'bg-blue-50 border-blue-200 text-blue-800 fa-info-circle'] as $key => $classes)
                @if(session($key))
                    <div class="mb-4 rounded-md border px-4 py-3 text-sm {{ \Illuminate\Support\Str::beforeLast($classes, ' ') }}"><i class="fas {{ \Illuminate\Support\Str::afterLast($classes, ' ') }} mr-2"></i>{{ session($key) }}</div>
                @endif
            @endforeach
            @if($errors->any())
                <div class="mb-4 rounded-md border px-4 py-3 text-sm bg-red-50 border-red-200 text-red-800"><i class="fas fa-times-circle mr-2"></i>{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        document.getElementById('mobileSidebarToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('hidden'));
    </script>
    @yield('scripts')
</body>
</html>
