@extends('layouts.app')

@section('title', 'Verify Email | Kenswed Technical College')
@section('meta_description', 'Verify your email address to complete your Kenswed College student account setup.')
@section('meta_keywords', 'email verification, account verification, student account activation')

@section('robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-white px-4 py-8">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-6">
                <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" alt="Kenswed"
                     class="h-16 w-16 mr-4"
                     onerror="this.onerror=null;this.src='https://placehold.co/64x64/B91C1C/ffffff?text=K'">
                <div class="text-left">
                    <h1 class="text-2xl font-bold text-gray-900">KENSWED COLLEGE</h1>
                    <p class="text-gray-600 text-sm">Technical & Vocational Training</p>
                </div>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Verify Your Email</h2>
            <p class="text-gray-600 mt-2">Complete your account setup</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <i class="fas fa-envelope text-red-600 text-2xl"></i>
                </div>

                @if (session('resent'))
                    <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-green-700 text-sm">A fresh verification link has been sent to your email address.</span>
                        </div>
                    </div>
                @endif

                <h3 class="text-lg font-semibold text-gray-900 mb-2">Check Your Email</h3>
                <p class="text-gray-700">
                    We've sent a verification link to your email address.
                    Please click the link to verify your account and continue.
                </p>
            </div>

            <!-- Resend Form -->
            <form method="POST" action="{{ route('verification.resend') }}" class="mt-8">
                @csrf
                <p class="text-sm text-gray-600 mb-4 text-center">
                    Didn't receive the email? Check your spam folder or request a new link.
                </p>

                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Resend Verification Email
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center pt-6 mt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    Already verified?
                    <a href="{{ route('login') }}"
                       class="font-semibold text-red-600 hover:text-red-700 hover:underline">
                        Proceed to Login
                    </a>
                </p>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 text-center">
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center justify-center">
                    <i class="fas fa-info-circle mr-2 text-red-600"></i>
                    <span>Check your inbox (and spam folder) for the verification email</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-clock mr-2 text-red-600"></i>
                    <span>Verification links expire after 24 hours</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Show success message animation
    const successMessage = document.querySelector('.bg-green-50');
    if (successMessage) {
        successMessage.style.opacity = '0';
        successMessage.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            successMessage.style.transition = 'all 0.3s ease';
            successMessage.style.opacity = '1';
            successMessage.style.transform = 'translateY(0)';
        }, 100);
    }
});
</script>
@endsection
