@extends('layouts.app')

@section('title', 'Your Cart — KTVTC Pre-Order')

@section('content')
<div class="min-h-[70vh] bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <i class="fas fa-shopping-basket text-5xl text-red-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Your Cart</h1>
            <p class="text-gray-600 mb-6">
                The cart lives on the menu page for a smooth experience.
            </p>
            <a href="{{ route('pre-order.home') }}"
               class="inline-block bg-red-700 hover:bg-red-800 text-white font-semibold py-3 px-6 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Menu
            </a>
        </div>
    </div>
</div>
@endsection