@extends('layouts.app')

@section('title', 'Change Password - Student Portal')

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
            <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
            <p class="text-gray-600 mt-1">Please set a new password for security</p>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 md:p-8">
            @if(session('success'))
                <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-700 text-sm">{{ session('success') }}</span>
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

            <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    <strong class="text-yellow-800 mr-2">Security Notice:</strong>
                    <span class="text-yellow-700 text-sm">You are using a default password. Please change it to continue.</span>
                </div>
            </div>

            <form method="POST" action="{{ route('student.force-password-change.update') }}" class="space-y-6">
                @csrf

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter new password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password" aria-label="Show password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="pl-10 pr-12 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Confirm new password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle"
                                data-target="password_confirmation" aria-label="Show password">
                            <i class="fas fa-eye text-gray-600 hover:text-red-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.getAttribute('data-target'));
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endsection
