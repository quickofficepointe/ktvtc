@extends('layouts.app')

@section('title', 'Confirm Password | Kenswed Technical College')
@section('meta_description', 'Confirm your password to continue accessing your Kenswed College student account.')
@section('meta_keywords', 'password confirmation, security verification, student account access')

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
            <h2 class="text-xl font-semibold text-gray-900">Confirm Password</h2>
            <p class="text-gray-600 mt-1">Verify your identity to continue</p>
        </div>

        <!-- Confirmation Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mb-4">
                    <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                </div>
                <p class="text-gray-700">For security reasons, please confirm your password before proceeding.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
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

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter your password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required autofocus
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter your current password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password" aria-label="Show password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Confirm Password
                </button>

                <!-- Forgot Password Link -->
                @if (Route::has('password.request'))
                    <div class="text-center pt-4 border-t border-gray-200">
                        <a href="{{ route('password.request') }}"
                           class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">
                            Forgot Your Password?
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Security Note -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                This extra step helps protect your account from unauthorized access.
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

    // Auto-focus on password field
    const passwordField = document.getElementById('password');
    if (passwordField) {
        setTimeout(() => {
            passwordField.focus();
        }, 100);
    }
});
</script>
@endsection
