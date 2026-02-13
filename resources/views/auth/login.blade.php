@extends('layouts.app')

@section('title', 'Login | Student Portal - Kenswed Technical College')
@section('meta_description', 'Access your Kenswed College student portal account. Login to manage courses, events, and academic information.')
@section('meta_keywords', 'Kenswed login, student portal, college login, technical college portal, student account')

@section('og_title', 'Student Login | Kenswed College')
@section('og_description', 'Access your student portal at Kenswed Technical and Vocational Training College')
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
            <h2 class="text-xl font-semibold text-gray-900">Student Portal Login</h2>
            <p class="text-gray-600 mt-1">Enter your credentials to access your account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            @if(session('registered'))
                <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-700 text-sm">Account created successfully! Please login to continue.</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
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

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
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
                               placeholder="student@example.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">
                                Forgot Password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter your password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password" aria-label="Show password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Keep me signed in
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Sign In to Portal
                </button>

                <!-- Registration Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-gray-600">
                        New student?
                        <a href="{{ route('register') }}"
                           class="font-semibold text-red-600 hover:text-red-700 hover:underline">
                            Create an account
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Support Info -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Need help? Contact IT Support:
                <a href="tel:+254790148509" class="text-red-600 hover:underline">+254 790 148 509</a>
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
