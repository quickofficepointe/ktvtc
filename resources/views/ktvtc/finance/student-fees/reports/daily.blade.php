@extends('ktvtc.finance.layouts.app')

@section('title', 'Daily Fee Report')
@section('subtitle', 'View daily fee collection details')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.student-fees.index') }}" class="text-gray-600 hover:text-primary">Student Fees</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Daily Report</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.student-fees.export', ['report' => 'daily', 'date' => $date->format('Y-m-d')]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.student-fees.reports.daily') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            @if(isset($campuses) && count($campuses) > 0)
            <div>
                <label class="text-xs font-semibold text-gray-600">Campus</label>
                <select name="campus_id" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
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
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search mr-2"></i> View Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalCollected ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($transactionCount ?? 0) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Average Transaction</p>
            <p class="text-2xl font-bold text-purple-600">KES {{ number_format($averageTransaction ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Date</p>
            <p class="text-lg font-bold text-gray-800">{{ $date->format('d M Y') }}</p>
            <p class="text-xs text-gray-500">{{ $date->format('l') }}</p>
        </div>
    </div>

    <!-- Hourly Breakdown -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Hourly Collection Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Hour</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hourlyData ?? [] as $hour => $amount)
                        @if($amount > 0)
                        <tr class="border-b border-gray-100">
                            <td class="py-2">{{ sprintf('%02d:00 - %02d:59', $hour, $hour) }}</td>
                            <td class="py-2 text-right font-medium">KES {{ number_format($amount, 2) }}</td>
                            <td class="py-2 text-right">
                                @php
                                    $percentage = $totalCollected > 0 ? ($amount / $totalCollected) * 100 : 0;
                                @endphp
                                {{ number_format($percentage, 1) }}%
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No transactions recorded today</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Method Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Payment Method Breakdown</h3>
            <div class="space-y-3">
                @forelse($methodBreakdown ?? [] as $method => $data)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium uppercase">{{ $method }}</span>
                            <span>KES {{ number_format($data['total'] ?? 0, 2) }}</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="fill bg-primary" style="width: {{ $data['percentage'] ?? 0 }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $data['count'] ?? 0 }} transactions</span>
                            <span>{{ $data['percentage'] ?? 0 }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No payment method data</p>
                @endforelse
            </div>
        </div>

        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Transaction List</h3>
            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Receipt</th>
                            <th class="pb-2 font-semibold">Student</th>
                            <th class="pb-2 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions ?? [] as $transaction)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 text-primary font-medium">{{ $transaction->receipt_number }}</td>
                                <td class="py-2">{{ $transaction->student->full_name ?? 'N/A' }}</td>
                                <td class="py-2 text-right font-medium">KES {{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">No transactions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Animate progress bars on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.progress-bar .fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 200);
        });
    });
</script>
@endpush
