{{-- resources/views/stock_adjustments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustment Management')
@section('page-description', 'Adjust inventory stock levels')

@section('content')
<div class="space-y-6">
    <!-- Adjustment Types -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <button onclick="createAdjustment('physical_count')"
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-center">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard-check text-blue-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">Physical Count</h4>
            <p class="text-sm text-gray-600">Regular stock taking</p>
        </button>

        <button onclick="createAdjustment('damage')"
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">Damage</h4>
            <p class="text-sm text-gray-600">Record damaged items</p>
        </button>

        <button onclick="createAdjustment('expiry')"
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-center">
            <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-times text-yellow-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">Expiry</h4>
            <p class="text-sm text-gray-600">Expired stock write-off</p>
        </button>

        <button onclick="createAdjustment('other')"
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-ellipsis-h text-gray-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">Other</h4>
            <p class="text-sm text-gray-600">Other adjustments</p>
        </button>
    </div>

    <!-- Recent Adjustments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Recent Adjustments</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adjustment #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items Adjusted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adjustments as $adjustment)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $adjustment->adjustment_number }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', $adjustment->adjustment_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $adjustment->total_items }} items</td>
                        <td class="px-6 py-4 font-medium {{ $adjustment->total_value_adjusted >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $adjustment->total_value_adjusted >= 0 ? '+' : '' }}KES {{ number_format(abs($adjustment->total_value_adjusted), 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($adjustment->status == 'approved')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Approved
                            </span>
                            @elseif($adjustment->status == 'pending_approval')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @elseif($adjustment->status == 'rejected')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Rejected
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($adjustment->status) }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $adjustment->adjustment_date->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
