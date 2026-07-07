@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure History: ' . $course->name)
@section('subtitle', 'View all changes to fee structure')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Structure</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $course->name }}</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">History</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.show', $course) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
            <h2 class="text-lg font-semibold text-gray-800">
                Fee Structure History: {{ $course->name }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Current Version: {{ $course->fee_version ?? 'v1.0' }}</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Current Version -->
            <div>
                <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Current Version ({{ $history['current']['version'] }})
                </h3>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Total Fees</p>
                            <p class="text-lg font-bold text-primary">KES {{ number_format($history['current']['total'] ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Last Modified</p>
                            <p class="text-sm font-medium text-gray-800">{{ $history['current']['modified_at'] ?? 'Never' }}</p>
                            <p class="text-xs text-gray-500">by {{ $history['current']['modified_by'] ?? 'N/A' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Modification Reason</p>
                            <p class="text-sm text-gray-800">{{ $history['current']['reason'] ?? 'No reason provided' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Fee Items</p>
                            <div class="mt-2 space-y-1">
                                @if(!empty($history['current']['fees']))
                                    @foreach($history['current']['fees'] as $key => $value)
                                        <div class="flex items-center justify-between text-sm border-b border-gray-100 py-1">
                                            <span class="text-gray-700">{{ $key }}</span>
                                            <span class="font-medium text-gray-900">KES {{ number_format(is_array($value) ? ($value['amount'] ?? 0) : $value, 2) }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-sm">No fee items</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($course->isFeeStructureApproved())
                        <div class="bg-green-50 rounded-lg p-2 text-center">
                            <span class="text-sm text-green-700">
                                <i class="fas fa-check-circle mr-1"></i>
                                Approved by {{ $history['current']['approved_by'] ?? 'N/A' }}
                                on {{ $history['current']['approved_at'] ?? 'N/A' }}
                            </span>
                        </div>
                    @elseif($course->hasPendingFeeChanges())
                        <div class="bg-amber-50 rounded-lg p-2 text-center">
                            <span class="text-sm text-amber-700">
                                <i class="fas fa-clock mr-1"></i>
                                Pending Approval
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Previous Version -->
            @if($history['previous'])
                <div>
                    <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-history text-blue-600 mr-2"></i>
                        Previous Version
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Total Fees</p>
                                <p class="text-lg font-bold text-gray-600">KES {{ number_format($history['previous']['total'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-check mr-1"></i> Replaced
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Fee Items</p>
                                <div class="mt-2 space-y-1">
                                    @if(!empty($history['previous']['fees']))
                                        @foreach($history['previous']['fees'] as $key => $value)
                                            <div class="flex items-center justify-between text-sm border-b border-gray-100 py-1">
                                                <span class="text-gray-700">{{ $key }}</span>
                                                <span class="font-medium text-gray-600">KES {{ number_format(is_array($value) ? ($value['amount'] ?? 0) : $value, 2) }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-sm">No fee items</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-6 text-center border border-gray-200">
                    <i class="fas fa-info-circle text-gray-300 text-3xl mb-3 block"></i>
                    <p class="text-gray-500">No previous version available</p>
                </div>
            @endif

            <!-- Version Timeline -->
            <div>
                <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-timeline text-indigo-600 mr-2"></i>
                    Version Timeline
                </h3>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flow-root">
                        <ul class="space-y-3">
                            <li class="relative pl-8">
                                <div class="absolute left-0 top-0 h-full w-0.5 bg-green-300"></div>
                                <div class="absolute left-0 top-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Current Version</p>
                                    <p class="text-xs text-gray-500">{{ $history['current']['version'] }}</p>
                                </div>
                            </li>
                            @if($history['previous'])
                                <li class="relative pl-8">
                                    <div class="absolute left-0 top-0 h-full w-0.5 bg-blue-300"></div>
                                    <div class="absolute left-0 top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Previous Version</p>
                                        <p class="text-xs text-gray-500">Replaced on {{ $history['current']['modified_at'] ?? 'N/A' }}</p>
                                    </div>
                                </li>
                            @endif
                            <li class="relative pl-8">
                                <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-300"></div>
                                <div class="absolute left-0 top-1 w-4 h-4 rounded-full bg-gray-400 border-2 border-white shadow"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Initial Version</p>
                                    <p class="text-xs text-gray-500">Created when course was added</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
