@extends('layouts.app')

@section('title', 'Reset Password | Kenswed Technical College')
@section('meta_description', 'Reset your Kenswed College student account password. Request a password reset link.')
@section('meta_keywords', 'password reset, forgot password, account recovery, student account access')

@section('robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-white px-4 py-8">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" alt="Kenswed"
                     class="h-16 w-16 mr-4"
                     onerror="this.onerror=null;this.src='https://placehold.co/64x64/B91C1C/ffffff?text=K'">
                <div class="text-left">
                    <h1 class="text-2xl font-bold text-gray-900">KENSWED COLLEGE</h1>
                    <p class="text-gray-600 text-sm">Technical & Vocational Training</p>
                </div>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Reset Password</h2>
            <p class="text-gray-600 mt-1">Enter your email to receive reset instructions</p>
        </div>

        <!-- Reset Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            @if (session('status'))
                <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-700 text-sm">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <span class="text-red-700 text-sm">
                                @foreach($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter your email address">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Enter the email associated with your account</p>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Send Reset Link
                </button>

                <!-- Login Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-gray-600">
                        Remember your password?
                        <a href="{{ route('login') }}"
                           class="font-semibold text-red-600 hover:text-red-700 hover:underline">
                            Back to Login
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Instructions -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center text-sm text-gray-600">
                <i class="fas fa-info-circle mr-2 text-red-600"></i>
                <span>Check your email inbox (and spam folder) for the reset link</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Auto-focus on email field
    const emailField = document.getElementById('email');
    if (emailField && !emailField.value) {
        setTimeout(() => {
            emailField.focus();
        }, 100);
    }
});
</script>
@endsection
