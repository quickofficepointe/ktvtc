<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ILO') — KTVTC</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary':       '#B91C1C',   // Kenswed maroon
                        'primary-dark':  '#991B1B',
                        'primary-light': '#FEE2E2',
                        'secondary':     '#BF1F30',
                        'success':       '#10B981',
                        'warning':       '#F59E0B',
                        'danger':        '#EF4444',
                        'info':          '#3B82F6',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background: #F8FAFC; }

        /* Sidebar — Kenswed gradient */
        .ilo-sidebar {
            background: linear-gradient(180deg, #B91C1C 0%, #991B1B 100%);
            transition: all .3s ease;
        }
        .ilo-nav-link {
            transition: all .2s ease;
            color: rgba(255, 255, 255, .9);
        }
        .ilo-nav-link:hover {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            transform: translateX(4px);
        }
        .ilo-nav-link.active {
            background: #fff !important;
            color: #B91C1C !important;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
        }
        .ilo-nav-link.active i { color: #B91C1C !important; }

        /* Card hover */
        .card-hover { transition: all .2s ease; }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(185, 28, 28, .08);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50">

{{-- ═══════════ TOP NAV ═══════════ --}}
<nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm border-b border-gray-200 z-40">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">

        <div class="flex items-center gap-3">
            <button id="mobileMenuToggle" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                <i class="fas fa-bars"></i>
            </button>

            <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center shadow-md">
                <i class="fas fa-briefcase text-white"></i>
            </div>

            <div>
                <h1 class="font-bold text-gray-800 leading-tight">
                    Industrial Liaison <span class="text-primary">Office</span>
                </h1>
                <p class="text-xs text-gray-500">Kenswed College · Attachment Management</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden md:inline text-sm text-gray-600">{{ now()->format('d M Y') }}</span>

            <div class="relative">
                <button id="userDropdownBtn" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-primary-dark text-white flex items-center justify-center font-semibold shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'I', 0, 1)) }}
                    </div>
                    <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>

                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 z-50">
                    <div class="p-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user-cog w-5 text-primary"></i><span class="ml-2">Profile</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-5"></i><span class="ml-2">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- ═══════════ SIDEBAR ═══════════ --}}
<aside id="sidebar" class="ilo-sidebar fixed top-16 left-0 bottom-0 w-64 text-white z-30 overflow-y-auto hidden lg:block">
    <div class="p-4">
        <ul class="space-y-1">

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('ilo.dashboard') }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('ilo.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Registry --}}
            <div class="mt-4 px-4 text-xs uppercase tracking-wider text-white/50">Registry</div>

            <li>
                <a href="{{ route('ilo.companies.index') }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('ilo.companies.*') ? 'active' : '' }}">
                    <i class="fas fa-building w-5 mr-3"></i>
                    <span>Companies</span>
                </a>
            </li>

            <li>
                <a href="{{ route('ilo.opportunities.index') }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('ilo.opportunities.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn w-5 mr-3"></i>
                    <span>Opportunities</span>
                </a>
            </li>

            {{-- Workflow --}}
            <div class="mt-4 px-4 text-xs uppercase tracking-wider text-white/50">Workflow</div>

            <li>
                <a href="{{ route('ilo.applications.index') }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('ilo.applications.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature w-5 mr-3"></i>
                    <span>Applications</span>
                    @php
                        $pending = rescue(fn () => \App\Models\AttachmentApplication::pending()->count(), 0);
                    @endphp
                    @if($pending > 0)
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pending }}</span>
                    @endif
                </a>
            </li>

            <li>
                <a href="{{ route('ilo.attachments.index') }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('ilo.attachments.*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate w-5 mr-3"></i>
                    <span>Attachments</span>
                    @php
                        $pendingAttachments = rescue(fn () => \App\Models\Attachment::where('status', 'submitted')->count(), 0);
                    @endphp
                    @if($pendingAttachments > 0)
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingAttachments }}</span>
                    @endif
                </a>
            </li>

            {{-- Optional: dedicated self-found shortcut --}}
            <li>
                <a href="{{ route('ilo.attachments.index', ['company_type' => 'self']) }}"
                   class="ilo-nav-link flex items-center px-4 py-3 rounded-lg">
                    <i class="fas fa-handshake w-5 mr-3"></i>
                    <span>Self-Found</span>
                    @php
                        $selfFound = rescue(fn () => \App\Models\Attachment::whereNull('company_id')->where('status', 'submitted')->count(), 0);
                    @endphp
                    @if($selfFound > 0)
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $selfFound }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    <div class="absolute bottom-0 left-0 right-0 p-4 text-xs text-white/60 text-center border-t border-white/10">
        <p>Kenswed College · ILO</p>
        <p class="mt-0.5">v1.0.0</p>
    </div>
</aside>

{{-- ═══════════ MAIN ═══════════ --}}
<main class="lg:ml-64 pt-16 min-h-screen">

    {{-- Page header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">@yield('title', 'ILO')</h2>
                @hasSection('subtitle')
                    <p class="text-sm text-gray-500 mt-1">@yield('subtitle')</p>
                @endif
            </div>
            <div>
                @yield('header-actions')
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session()->hasAny(['success', 'error', 'warning', 'info']))
        <div class="px-6 pt-4">
            @foreach(['success' => 'green', 'error' => 'red', 'warning' => 'yellow', 'info' => 'blue'] as $key => $color)
                @if(session($key))
                    <div class="mb-3 p-3 rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 text-{{ $color }}-800 text-sm flex items-center justify-between">
                        <span><i class="fas fa-info-circle mr-2"></i>{{ session($key) }}</span>
                        <button onclick="this.parentElement.remove()" class="text-{{ $color }}-400 hover:text-{{ $color }}-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="px-6 pt-4">
            <div class="mb-3 p-3 rounded-lg border border-red-200 bg-red-50 text-red-800 text-sm">
                <p class="font-semibold mb-1">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Please fix:
                </p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Page content --}}
    <div class="p-6">
        @yield('content')
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) toggle.addEventListener('click', () => sidebar.classList.toggle('hidden'));

    const btn = document.getElementById('userDropdownBtn');
    const drop = document.getElementById('userDropdown');
    if (btn && drop) {
        btn.addEventListener('click', (e) => { e.stopPropagation(); drop.classList.toggle('hidden'); });
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden');
        });
    }
});
</script>

@stack('scripts')
</body>
</html>