@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8">
    <h2 class="text-center text-lg font-semibold text-black mb-4">Reset Password</h2>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div>
        <label for="email" class="sr-only">Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-envelope"></i>
          </span>
          <input id="email" name="email" type="email" value="{{ $email ?? old('email') }}" required autofocus
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('email') border-red-500 @enderror">
        </div>
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="password" class="sr-only">New Password</label>
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

      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">Reset Password</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.password-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var id = btn.getAttribute('data-target'), input = document.getElementById(id), icon = btn.querySelector('i');
      if (!input) return;
      if (input.type === 'password') { input.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
      else { input.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    });
  });
});
</script>
@endsection
