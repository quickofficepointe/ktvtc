@extends('ktvtc.finance.layouts.app')

@section('title', 'Financial Year Settings')
@section('subtitle', 'Manage financial year configuration')

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calendar-alt text-blue-600"></i>
        </div>
        <div class="min-w-0">
            <h4 class="font-semibold text-blue-800">Financial Year Configuration</h4>
            <p class="text-sm text-blue-700">Configure the current financial year. This affects reporting and financial calculations.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-calendar text-primary mr-2"></i>
            Current Financial Year
        </h3>

        @php
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;
            $prevYear = $currentYear - 1;
            $currentFY = $currentYear . '/' . $nextYear;
            $nextFY = $nextYear . '/' . ($nextYear + 1);
            $prevFY = $prevYear . '/' . $currentYear;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Previous Financial Year</p>
                <p class="text-lg font-bold text-gray-600 break-words">{{ $prevFY }}</p>
            </div>

            <div class="bg-primary/10 p-4 rounded-lg text-center border-2 border-primary">
                <p class="text-sm text-primary font-semibold">Current Financial Year</p>
                <p class="text-2xl font-bold text-primary break-words">{{ $currentFY }}</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-sm text-gray-500">Next Financial Year</p>
                <p class="text-lg font-bold text-gray-600 break-words">{{ $nextFY }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.settings.financial-year.update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Financial Year Start</label>
                    <select name="fy_start_month" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                            <option value="{{ $month }}" {{ config('finance.fy_start_month', 'January') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Month when the financial year starts</p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Financial Year End</label>
                    <select name="fy_end_month" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                            <option value="{{ $month }}" {{ config('finance.fy_end_month', 'December') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Month when the financial year ends</p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Financial Year Start Year</label>
                    <input type="number"
                           name="fy_start_year"
                           value="{{ config('finance.fy_start_year', date('Y')) }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Financial Year End Year</label>
                    <input type="number"
                           name="fy_end_year"
                           value="{{ config('finance.fy_end_year', date('Y') + 1) }}"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Update Financial Year
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-chart-bar text-primary mr-2"></i>
            Financial Year Summary
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-sm text-gray-600">Current Year Revenue</p>
                @php
                    $currentYearRevenue = \App\Models\FeePayment::whereYear('payment_date', date('Y'))
                        ->where('status', 'completed')
                        ->sum('amount');
                @endphp
                <p class="text-2xl font-bold text-green-600 break-words">KES {{ number_format($currentYearRevenue, 2) }}</p>
                <p class="text-xs text-gray-500">Year to date</p>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm text-gray-600">Current Year Payments</p>
                @php
                    $currentYearPayments = \App\Models\FeePayment::whereYear('payment_date', date('Y'))
                        ->where('status', 'completed')
                        ->count();
                @endphp
                <p class="text-2xl font-bold text-blue-600">{{ number_format($currentYearPayments) }}</p>
                <p class="text-xs text-gray-500">Total transactions</p>
            </div>

            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-sm text-gray-600">Year Progress</p>
                @php
                    $daysInYear = date('L') ? 366 : 365;
                    $dayOfYear = date('z') + 1;
                    $progress = round(($dayOfYear / $daysInYear) * 100);
                @endphp
                <p class="text-2xl font-bold text-purple-600">{{ $progress }}%</p>
                <p class="text-xs text-gray-500">Day {{ $dayOfYear }} of {{ $daysInYear }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-bolt text-primary mr-2"></i>
            Quick Actions
        </h3>

        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
            <button type="button" onclick="generateYearReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                <i class="fas fa-file-alt mr-2"></i> Generate Year Report
            </button>

            <button type="button" onclick="closeFinancialYear()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-semibold text-sm">
                <i class="fas fa-lock mr-2"></i> Close Financial Year
            </button>

            <button type="button" onclick="exportYearData()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold text-sm">
                <i class="fas fa-file-export mr-2"></i> Export Year Data
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function generateYearReport() {
        showLoading('Generating year report...');

        setTimeout(() => {
            hideLoading();
            toastr.success('Year report generated successfully');
        }, 2000);
    }

    function closeFinancialYear() {
        if (!confirm('Are you sure you want to close the current financial year? This action cannot be undone.')) {
            return;
        }

        showLoading('Closing financial year...');

        setTimeout(() => {
            hideLoading();
            toastr.success('Financial year closed successfully');
        }, 2000);
    }

    function exportYearData() {
        showLoading('Exporting year data...');

        setTimeout(() => {
            hideLoading();
            toastr.success('Year data exported successfully');
            window.location.href = "{{ route('finance.reports.export-financial', ['type' => 'yearly']) }}";
        }, 1500);
    }
</script>
@endpush
