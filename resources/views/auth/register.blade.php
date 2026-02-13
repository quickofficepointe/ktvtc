@extends('layouts.app')

@section('title', 'Register | Create Student Account - Kenswed Technical College')
@section('meta_description', 'Create your student account for Kenswed College portal. Register to access courses, events, and academic resources.')
@section('meta_keywords', 'Kenswed registration, create student account, college sign up, technical college registration')

@section('og_title', 'Student Registration | Kenswed College')
@section('og_description', 'Create your student account for Kenswed Technical College portal')
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

@section('canonical', url()->current())

@section('robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-white px-4 py-8">
    <div class="w-full max-w-md">
        <!-- Logo Header -->
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
            <h2 class="text-xl font-semibold text-gray-900">Create Student Account</h2>
            <p class="text-gray-600 mt-1">Fill in your details to get started</p>
        </div>

        <!-- Registration Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            @if($errors->any())
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <span class="text-red-700 text-sm">
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                               class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter your full name">
                    </div>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                               class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="student@example.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Create a password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password" aria-label="Show password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Re-enter your password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password_confirmation" aria-label="Show confirm password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="flex items-start">
                    <input type="checkbox" name="terms" id="terms" required
                           class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 text-sm text-gray-700">
                        I agree to the
                        <a href="{{ route('policie.index') }}" class="text-red-600 hover:underline">Terms of Service</a>
                        and
                        <a href="{{ route('policie.index') }}" class="text-red-600 hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Create Account
                </button>

                <!-- Login Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}"
                           class="font-semibold text-red-600 hover:text-red-700 hover:underline">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                After registration, you can complete your profile with additional details
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
        });
    });
});
</script>
@endsection
