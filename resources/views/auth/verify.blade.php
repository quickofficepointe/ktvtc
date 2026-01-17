@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-white px-4 py-10">
  <div class="w-full max-w-md bg-white border border-gray-200 shadow-lg rounded-2xl p-8 text-center">
    <img src="/Assets/images/Kenswed_logo.png" alt="Kenswed" class="h-12 w-12 mx-auto mb-3">
    <h2 class="text-lg font-semibold text-black mb-3">Verify Your Email</h2>

    @if (session('resent'))
      <div class="mb-4 text-sm text-green-700">A fresh verification link has been sent to your email address.</div>
    @endif

    <p class="text-gray-700 mb-6">Before proceeding, please check your email for a verification link. If you did not receive the email, you can request another below.</p>

    <form method="POST" action="{{ route('verification.resend') }}">
      @csrf
      <button type="submit" class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
        Resend Verification Email
      </button>
    </form>

    <p class="mt-4 text-sm text-gray-600">
      <a href="{{ route('login') }}" class="text-red-600 hover:underline">Back to login</a>
    </p>
  </div>
</div>
@endsection
