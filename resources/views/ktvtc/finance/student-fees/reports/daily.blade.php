@extends('ktvtc.finance.layouts.app')

@section('title', 'Daily Fee Report')
@section('subtitle', 'View daily fee collection details')

@section('breadcrumb')
    <li><span class="mx-2">/</span></li>
    <li>
        <a href="{{ route('finance.student-fees.index') }}" class="hover:text-primary transition whitespace-nowrap">
            Student Fees
        </a>
    </li>
    <li><span class="mx-2">/</span></li>
    <li class="text-primary font-medium whitespace-nowrap">Daily Report</li>
@endsection

@section('header-actions')
    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto no-print">
        <a href="{{ route('finance.student-fees.export', ['report' => 'daily', 'date' => optional($date)->format('Y-m-d') ?? now()->format('Y-m-d')]) }}"
           class="inline-flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-file-export"></i>
            Export
        </a>

        <button type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-print"></i>
            Print
        </button>
    </div>
@endsection

@section('content')
@php
    $reportDate = $date ?? now();
    $totalCollected = $totalCollected ?? 0;
    $transactionCount = $transactionCount ?? 0;
    $averageTransaction = $averageTransaction ?? 0;
@endphp

<div class="space-y-6">
    <div class="finance-card relative overflow-hidden p-4 sm:p-6 no-print">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET"
              action="{{ route('finance.student-fees.reports.daily') }}"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Date</label>
                <input type="date"
                       name="date"
                       value="{{ optional($reportDate)->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            @if(isset($campuses) && count($campuses) > 0)
                <div>
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Campus</label>
                    <select name="campus_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <button type="submit"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search"></i>
                    View Report
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">
                KES {{ number_format($totalCollected, 2) }}
            </p>
        </div>

        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-blue-600">
                {{ number_format($transactionCount) }}
            </p>
        </div>

        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Average Transaction</p>
            <p class="text-2xl font-bold text-purple-600">
                KES {{ number_format($averageTransaction, 2) }}
            </p>
        </div>

        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Date</p>
            <p class="text-lg font-bold text-gray-800">
                {{ optional($reportDate)->format('d M Y') ?? 'N/A' }}
            </p>
            <p class="text-xs text-gray-500">
                {{ optional($reportDate)->format('l') ?? '' }}
            </p>
        </div>
    </div>

    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-clock text-primary mr-2"></i>
            Hourly Collection Breakdown
        </h3>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-3 font-semibold">Hour</th>
                        <th class="py-3 px-3 font-semibold text-right">Amount</th>
                        <th class="py-3 px-3 font-semibold text-right">Percentage</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($hourlyData ?? [] as $hour => $amount)
                        @if($amount > 0)
                            @php
                                $percentage = $totalCollected > 0 ? ($amount / $totalCollected) * 100 : 0;
                            @endphp

                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-3">
                                    {{ sprintf('%02d:00 - %02d:59', $hour, $hour) }}
                                </td>

                                <td class="py-3 px-3 text-right font-medium">
                                    KES {{ number_format($amount, 2) }}
                                </td>

                                <td class="py-3 px-3 text-right">
                                    {{ number_format($percentage, 1) }}%
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-gray-500">
                                No transactions recorded for this day
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="finance-card relative overflow-hidden p-4 sm:p-6">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-wallet text-primary mr-2"></i>
                Payment Method Breakdown
            </h3>

            <div class="space-y-4">
                @forelse($methodBreakdown ?? [] as $method => $data)
                    @php
                        $percentage = min(max($data['percentage'] ?? 0, 0), 100);
                    @endphp

                    <div>
                        <div class="flex justify-between text-sm gap-3">
                            <span class="font-medium uppercase">{{ $method }}</span>
                            <span class="font-semibold">KES {{ number_format($data['total'] ?? 0, 2) }}</span>
                        </div>

                        <div class="w-full h-2 bg-gray-100 rounded-full mt-2 overflow-hidden">
                            <div class="progress-fill h-2 bg-primary rounded-full transition-all duration-700"
                                 style="width: {{ $percentage }}%"></div>
                        </div>

                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>{{ $data['count'] ?? 0 }} transactions</span>
                            <span>{{ number_format($percentage, 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">
                        No payment method data
                    </p>
                @endforelse
            </div>
        </div>

        <div class="finance-card relative overflow-hidden p-4 sm:p-6">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-list text-primary mr-2"></i>
                Transaction List
            </h3>

            <div class="overflow-y-auto max-h-96 table-responsive">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-3 px-3 font-semibold">Receipt</th>
                            <th class="py-3 px-3 font-semibold">Student</th>
                            <th class="py-3 px-3 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($transactions ?? [] as $transaction)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-3 text-primary font-medium">
                                    {{ $transaction->receipt_number ?? 'N/A' }}
                                </td>

                                <td class="py-3 px-3">
                                    {{ $transaction->student->full_name ?? 'N/A' }}
                                </td>

                                <td class="py-3 px-3 text-right font-medium">
                                    KES {{ number_format($transaction->amount ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-500">
                                    No transactions
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print,
        nav,
        aside,
        footer {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }

        body {
            background: #fff !important;
        }

        .finance-card {
            box-shadow: none !important;
            break-inside: avoid;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.progress-fill').forEach(function (bar) {
            const width = bar.style.width;
            bar.style.width = '0%';

            setTimeout(function () {
                bar.style.width = width;
            }, 200);
        });
    });
</script>
@endpush
