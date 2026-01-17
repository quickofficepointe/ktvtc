@extends('layouts.app')
@section('title', 'Register | Create Student Account - Kenswed Technical College')
@section('meta_description', 'Create your student account for Kenswed College portal. Register to access courses, events, and academic resources.')
@section('meta_keywords', 'Kenswed registration, create student account, college sign up, technical college registration')

<!-- Open Graph Tags -->
@section('og_title', 'Student Registration | Kenswed College')
@section('og_description', 'Create your student account for Kenswed Technical College portal')
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())

<!-- Noindex for Registration Pages -->
@section('robots')
    <meta name="robots" content="noindex, nofollow">
@endsection
@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8">
    <div class="flex items-center justify-center mb-4">
      <img src="/Assets/images/Kenswed_logo.png" alt="Kenswed" class="h-12 w-12 mr-3">
      <h1 class="text-xl font-extrabold text-black">Create account</h1>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
      @csrf

      <!-- Name -->
      <div>
        <label for="name" class="sr-only">Name</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-user"></i>
          </span>
          <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('name') border-red-500 @enderror">
        </div>
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="sr-only">Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-envelope"></i>
          </span>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('email') border-red-500 @enderror">
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

      <!-- Confirm -->
      <div>
        <label for="password_confirmation" class="sr-only">Confirm Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-lock"></i>
          </span>
          <input id="password_confirmation" name="password_confirmation" type="password" required
            class="block w-full pl-10 pr-12 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
          <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle" data-target="password_confirmation" aria-label="Show confirm password">
            <i class="fas fa-eye text-gray-600"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
        Register
      </button>

    </form>

    <p class="mt-5 text-center text-sm text-gray-700">
      Already registered?
      <a href="{{ route('login') }}" class="text-red-600 font-medium hover:underline">Login</a>
    </p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.password-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = btn.getAttribute('data-target'), input = document.getElementById(id), icon = btn.querySelector('i');
      if (!input) return;
      if (input.type === 'password') { input.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); btn.setAttribute('aria-label','Hide'); }
      else { input.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); btn.setAttribute('aria-label','Show'); }
    });
  });
});
</script>
@endsection
