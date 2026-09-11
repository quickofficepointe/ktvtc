@extends('layouts.app')

@section('title', 'Pre-Order Login — KTVTC Cafeteria')
@section('meta_description', 'Log in to the KTVTC cafeteria pre-order portal to order meals and pay via M-Pesa or your student card.')
@section('meta_keywords', 'KTVTC cafeteria, pre-order, student portal, cafeteria login')

@section('content')
<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-3">
                    <i class="fas fa-utensils text-3xl text-red-700"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Pre-Order Login</h1>
                <p class="text-sm text-gray-500 mt-1">Students, staff &amp; college students</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('pre-order.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email / Student Number / Admission Number
                    </label>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" required
                           placeholder="john@ktvtc.ac.ke, FBOM/01653 or 101"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">
                        Staff: use your <strong>@ktvtc.ac.ke</strong> or <strong>@kenswed.org</strong> email
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">4-Digit PIN</label>
                    <input type="password" name="pin" inputmode="numeric" maxlength="4" pattern="\d{4}" required
                           placeholder="••••"
                           class="w-full px-4 py-2.5 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>

                <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-sign-in-alt mr-2"></i> Log In
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-sm">
                <a href="{{ route('pre-order.setup') }}" class="text-red-700 hover:underline">
                    <i class="fas fa-user-plus mr-1"></i> First time? Set up PIN
                </a>
                <a href="{{ route('pre-order.forgot') }}" class="text-gray-500 hover:text-gray-700">
                    Forgot PIN?
                </a>
            </div>
        </div>
    </div>
</div>
@endsection