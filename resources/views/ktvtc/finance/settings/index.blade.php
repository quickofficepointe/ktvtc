@extends('ktvtc.finance.layouts.app')

@section('title', 'Finance Settings')
@section('subtitle', 'Manage finance department settings')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Settings</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- General Settings -->
        <a href="{{ route('finance.settings.general') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4 group-hover:bg-blue-200 transition">
                    <i class="fas fa-cog text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">General Settings</h3>
                    <p class="text-sm text-gray-500">Manage general finance settings</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>

        <!-- Fee Structure -->
        <a href="{{ route('finance.settings.fee-structure') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4 group-hover:bg-green-200 transition">
                    <i class="fas fa-file-invoice-dollar text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Fee Structure</h3>
                    <p class="text-sm text-gray-500">Manage fee structures and rates</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>

        <!-- Payment Settings -->
        <a href="{{ route('finance.settings.payment') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mr-4 group-hover:bg-purple-200 transition">
                    <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Payment Settings</h3>
                    <p class="text-sm text-gray-500">Configure payment methods and gateways</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>

        <!-- Financial Year -->
        <a href="{{ route('finance.settings.financial-year') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mr-4 group-hover:bg-yellow-200 transition">
                    <i class="fas fa-calendar-alt text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Financial Year</h3>
                    <p class="text-sm text-gray-500">Manage financial year settings</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>

        <!-- Tax Settings -->
        <a href="{{ route('finance.settings.tax') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center mr-4 group-hover:bg-red-200 transition">
                    <i class="fas fa-percentage text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Tax Settings</h3>
                    <p class="text-sm text-gray-500">Configure tax rates and rules</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>

        <!-- Notification Settings -->
        <a href="{{ route('finance.settings.notifications') }}" class="finance-card p-6 hover:shadow-lg transition cursor-pointer group">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center mr-4 group-hover:bg-indigo-200 transition">
                    <i class="fas fa-bell text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Notification Settings</h3>
                    <p class="text-sm text-gray-500">Configure finance notifications</p>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <span class="text-sm text-primary group-hover:underline">Configure →</span>
            </div>
        </a>
    </div>

    <!-- System Info -->
    <div class="finance-card p-6 bg-gray-50">
        <h3 class="font-bold text-gray-800 mb-4">System Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Finance Module Version</p>
                <p class="font-medium text-gray-800">v1.0.0</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Last Updated</p>
                <p class="font-medium text-gray-800">{{ now()->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Database</p>
                <p class="font-medium text-gray-800">{{ env('DB_DATABASE', 'N/A') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Environment</p>
                <p class="font-medium text-gray-800">{{ env('APP_ENV', 'production') }}</p>
            </div>
        </div>
    </div>
</div>
@endpush
