@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8">
    <h2 class="text-center text-lg font-semibold text-black mb-4">Forgot Password</h2>

    @if (session('status'))
      <div class="mb-4 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf

      <div>
        <label for="email" class="sr-only">Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-envelope"></i>
          </span>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 @error('email') border-red-500 @enderror">
        </div>
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
        Send Password Reset Link
      </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-700">
      Back to
      <a href="{{ route('login') }}" class="text-red-600 font-medium hover:underline">Login</a>
    </p>
  </div>
</div>
@endsection
