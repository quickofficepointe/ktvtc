{{-- Standard dashboard header: college logo, dashboard name, who is signed in, today's date, optional actions. --}}
@props(['title', 'subtitle' => null])
@php
    $user = auth()->user();
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="h-1.5 bg-gradient-to-r from-red-700 via-red-600 to-red-800"></div>
    <div class="px-5 py-4 flex flex-wrap items-center gap-x-6 gap-y-3">
        <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" alt="Kenswed Technical & Vocational Training College" class="h-12 w-auto">
        <div class="hidden sm:block w-px self-stretch bg-gray-200"></div>
        <div class="flex-1 min-w-[200px]">
            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                {{ $greeting }}{{ $user ? ', ' . \Illuminate\Support\Str::before($user->name, ' ') : '' }}
                · {{ now()->format('l, j F Y') }}
                @if($subtitle) · {{ $subtitle }} @endif
            </p>
        </div>
        @if(isset($actions) && trim($actions) !== '')
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endif
    </div>
</div>
