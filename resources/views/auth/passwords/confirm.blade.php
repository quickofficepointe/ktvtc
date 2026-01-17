@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8">
    <h2 class="text-center text-lg font-semibold text-black mb-4">Confirm Password</h2>
    <p class="text-center text-sm text-gray-700 mb-6">Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
      @csrf

      <div>
        <label for="current_password" class="sr-only">Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-lock"></i>
          </span>
          <input id="current_password" name="password" type="password" required
            class="block w-full pl-10 pr-12 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('password') border-red-500 @enderror">
          <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center password-toggle" data-target="current_password" aria-label="Show password">
            <i class="fas fa-eye text-gray-600"></i>
          </button>
        </div>
        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">Confirm Password</button>

      @if (Route::has('password.request'))
        <p class="mt-3 text-sm text-center">
          <a href="{{ route('password.request') }}" class="text-red-600 hover:underline">Forgot Your Password?</a>
        </p>
      @endif
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
