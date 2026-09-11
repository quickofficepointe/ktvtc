@extends('layouts.app')

@section('title', 'Reset PIN — KTVTC Pre-Order')

@section('content')
<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-3">
                    <i class="fas fa-lock text-3xl text-green-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Reset Your PIN</h1>
                <p class="text-sm text-gray-500 mt-1">Enter the code we sent you</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('pre-order.reset.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">6-Digit Reset Code</label>
                    <input type="text" name="code" inputmode="numeric" maxlength="6" pattern="\d{6}" required
                           placeholder="000000"
                           class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <div class="border-t pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">New 4-Digit PIN</label>
                    <input type="password" name="pin" inputmode="numeric" maxlength="4" pattern="\d{4}" required
                           placeholder="••••"
                           class="w-full px-4 py-2.5 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New PIN</label>
                    <input type="password" name="pin_confirmation" inputmode="numeric" maxlength="4" pattern="\d{4}" required
                           placeholder="••••"
                           class="w-full px-4 py-2.5 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-check-circle mr-2"></i> Reset PIN
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('pre-order.forgot') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-redo mr-1"></i> Didn't receive code? Send again
                </a>
            </div>
        </div>
    </div>
</div>
@endsection