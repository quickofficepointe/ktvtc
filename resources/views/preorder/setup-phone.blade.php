@extends('layouts.app')

@section('title', 'Add Phone Number — KTVTC Pre-Order')

@section('content')
<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-3">
                    <i class="fas fa-phone text-3xl text-yellow-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Add Phone Number</h1>
                <p class="text-sm text-gray-500 mt-1">Step 2 of 3 — We need a phone to send your code</p>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('pre-order.setup.phone.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                           placeholder="0712 345 678"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Format: 07xxxxxxxx or 2547xxxxxxxx</p>
                </div>

                <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-sms mr-2"></i> Send Code
                </button>
            </form>
        </div>
    </div>
</div>
@endsection