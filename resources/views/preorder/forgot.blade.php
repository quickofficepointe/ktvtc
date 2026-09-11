@extends('layouts.app')

@section('title', 'Forgot PIN — KTVTC Pre-Order')

@section('content')
<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-3">
                    <i class="fas fa-key text-3xl text-yellow-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Forgot Your PIN?</h1>
                <p class="text-sm text-gray-500 mt-1">We'll text a reset code to the phone on file</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('pre-order.forgot.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Your Email / Student Number / Admission Number
                    </label>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" required
                           placeholder="john@ktvtc.ac.ke, FBOM/01653 or 101"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                </div>

                <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Code
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('pre-order.login') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i> Back to login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection