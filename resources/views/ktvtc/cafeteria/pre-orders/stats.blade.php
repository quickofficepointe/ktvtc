@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Pre-Order Stats')
@section('subtitle', 'Today and all-time totals')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4">Today ({{ now()->format('d M Y') }})</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-xs text-yellow-700">Pending</p>
                <p class="text-2xl font-bold text-yellow-700">{{ $data['today']['pending'] }}</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs text-green-700">Paid</p>
                <p class="text-2xl font-bold text-green-700">{{ $data['today']['paid'] }}</p>
            </div>
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <p class="text-xs text-indigo-700">Accepted</p>
                <p class="text-2xl font-bold text-indigo-700">{{ $data['today']['accepted'] }}</p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <p class="text-xs text-purple-700">Ready</p>
                <p class="text-2xl font-bold text-purple-700">{{ $data['today']['ready'] }}</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-700">Completed</p>
                <p class="text-2xl font-bold text-gray-700">{{ $data['today']['completed'] }}</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-xs text-red-700">Revenue</p>
                <p class="text-2xl font-bold text-red-700">KES {{ number_format($data['today']['revenue'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4">All Time</h3>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-600">Total Orders</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($data['all_time']['total_orders']) }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <p class="text-xs text-red-700">Total Revenue</p>
                <p class="text-2xl font-bold text-red-700">KES {{ number_format($data['all_time']['total_revenue'], 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection