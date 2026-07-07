@extends('ktvtc.finance.layouts.app')

@section('title', 'Finance Settings')
@section('subtitle', 'Manage finance department settings')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- General Settings -->
        <a href="{{ route('finance.settings.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-red-100/50 cursor-pointer group">
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
                <span class="text-sm text-primary group-hover:underline font-medium">Configure →</span>
            </div>
        </a>

        <!-- Fee Structure -->
        <a href="{{ route('finance.settings.fee-structure') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-red-100/50 cursor-pointer group">
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
                <span class="text-sm text-primary group-hover:underline font-medium">Configure →</span>
            </div>
        </a>

        <!-- Financial Year -->
        <a href="{{ route('finance.settings.financial-year') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-red-100/50 cursor-pointer group">
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
                <span class="text-sm text-primary group-hover:underline font-medium">Configure →</span>
            </div>
        </a>
    </div>

    <!-- System Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-server text-primary mr-2"></i>
            System Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Finance Module Version</p>
                <p class="font-medium text-gray-800">v1.0.0</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</p>
                <p class="font-medium text-gray-800">{{ now()->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Database</p>
                <p class="font-medium text-gray-800">{{ env('DB_DATABASE', 'N/A') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Environment</p>
                <p class="font-medium text-gray-800">{{ env('APP_ENV', 'production') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Any page-specific scripts
</script>
@endpush
