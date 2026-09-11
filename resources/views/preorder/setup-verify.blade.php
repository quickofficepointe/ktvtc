@extends('layouts.app')

@section('title', 'Verify & Set PIN — KTVTC Pre-Order')

@section('content')
@php
    $pending = session('preorder_setup_pending', []);
    $skipVerification = $pending['skip_verification'] ?? false;
@endphp

<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $skipVerification ? 'bg-green-100' : 'bg-blue-100' }} mb-3">
                    <i class="fas {{ $skipVerification ? 'fa-user-check text-green-600' : 'fa-shield-alt text-blue-600' }} text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $skipVerification ? 'Welcome!' : 'Verify & Set PIN' }}
                </h1>
                @if($skipVerification)
                    <p class="text-sm text-gray-500 mt-1">
                        Hi {{ $pending['display_name'] ?? 'there' }} — just set your 4-digit PIN.
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-1">
                        Step 3 of 3 — Code sent to {{ $maskedPhone ?? 'your phone' }}
                    </p>
                @endif
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('pre-order.setup.verify.submit') }}" class="space-y-4">
                @csrf

                @unless($skipVerification)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">6-Digit Code</label>
                        <input type="text" name="code" inputmode="numeric" maxlength="6" pattern="\d{6}" required
                               placeholder="000000"
                               class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                        <p class="text-xs text-gray-500 mt-1">Enter the code we texted you.</p>
                    </div>

                    <div class="border-t pt-4"></div>
                @endunless

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Set 4-Digit PIN</label>
                    <input type="password" name="pin" inputmode="numeric" maxlength="4" pattern="\d{4}" required
                           placeholder="••••"
                           class="w-full px-4 py-2.5 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm PIN</label>
                    <input type="password" name="pin_confirmation" inputmode="numeric" maxlength="4" pattern="\d{4}" required
                           placeholder="••••"
                           class="w-full px-4 py-2.5 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ $skipVerification ? 'Set PIN & Enter' : 'Verify & Finish' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection