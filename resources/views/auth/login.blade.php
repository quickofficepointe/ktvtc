@extends('layouts.app')

@section('title', 'Login | Student Portal - Kenswed Technical College')
@section('meta_description', 'Access your Kenswed College student portal account. Login to manage your courses, events, and academic information.')
@section('meta_keywords', 'Kenswed login, student portal, college login, technical college portal, student account')

<!-- Open Graph Tags -->
@section('og_title', 'Student Login | Kenswed College')
@section('og_description', 'Access your student portal at Kenswed Technical and Vocational Training College')
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())

<!-- Noindex for Login Pages -->
@section('robots')
    <meta name="robots" content="noindex, nofollow">
@endsection
@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8">
    <div class="flex items-center justify-center mb-4">
      <img src="/Assets/images/Kenswed_logo.png" alt="Kenswed" class="h-12 w-12 mr-3" onerror="this.onerror=null;this.src='https://placehold.co/48x48/B91C1C/ffffff?text=Logo'">
      <h1 class="text-xl font-extrabold text-black">KENSWED COLLEGE</h1>
    </div>

    <h2 class="text-center text-lg font-semibold text-black mb-6">Sign in to your account</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
      @csrf

      <!-- Email -->
      <div>
        <label for="email" class="sr-only">Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-red-600">
            <i class="fas fa-envelope"></i>
          </span>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
        </div>
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="sr-only">Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-lock"></i>
          </span>

          <input id="password" name="password" type="password" required
            class="block w-full pl-10 pr-12 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('password') border-red-500 @enderror">

          <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle" data-target="password" aria-label="Show password">
            <i class="fas fa-eye text-gray-600"></i>
          </button>
        </div>
        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <!-- Remember & Forgot -->
      <div class="flex items-center justify-between">
        <label class="flex items-center text-sm text-gray-700">
          <input type="checkbox" name="remember" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded" {{ old('remember') ? 'checked' : '' }}>
          <span class="ml-2">Remember me</span>
        </label>

        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" class="text-sm font-medium text-red-600 hover:underline">Forgot?</a>
        @endif
      </div>

      <!-- Submit -->
      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-700">
      Not registered?
      <a href="{{ route('register') }}" class="text-red-600 font-medium hover:underline">Create an account</a>
    </p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.password-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var targetId = btn.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;
      var icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
        btn.setAttribute('aria-label','Hide password');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
        btn.setAttribute('aria-label','Show password');
      }
    });
  });
});
</script>
@endsection
