@extends('ktvtc.finance.layouts.app')

@section('title', 'Monthly Fee Report')
@section('subtitle', 'View monthly fee collection details')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.student-fees.index') }}" class="text-gray-600 hover:text-primary">Student Fees</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Monthly Report</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.student-fees.export', ['report' => 'monthly', 'month' => $month, 'year' => $year]) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Month Selector -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.student-fees.reports.monthly') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Month</label>
                <select name="month" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Year</label>
                <select name="year" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @for($y = now()->year - 5; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalCollected ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($transactionCount ?? 0) }}</p>
        </div>
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">{{ $monthName }} {{ $year }}</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($transactionCount > 0 ? $totalCollected / $transactionCount : 0, 2) }} avg per transaction</p>
        </div>
    </div>

    <!-- Daily Breakdown Chart -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Daily Collection Breakdown</h3>
        <canvas id="dailyChart" height="250"></canvas>
    </div>

    <!-- Monthly Comparison -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Monthly Comparison (Last 12 Months)</h3>
        <canvas id="monthlyComparisonChart" height="250"></canvas>
    </div>

    <!-- Method Summary -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">Payment Method Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($methodSummary ?? [] as $method => $data)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase">{{ $method }}</p>
                    <p class="text-xl font-bold text-gray-800">KES {{ number_format($data['total'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $data['count'] ?? 0 }} transactions ({{ $data['percentage'] ?? 0 }}%)</p>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4 col-span-4">No payment method data</p>
            @endforelse
        </div>
    </div>

    <!-- Transaction List -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 mb-4">All Transactions</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold">Receipt</th>
                        <th class="pb-2 font-semibold">Student</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold">Method</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments ?? [] as $payment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="py-2 text-primary font-medium">{{ $payment->receipt_number }}</td>
                            <td class="py-2">{{ $payment->student->full_name ?? 'N/A' }}</td>
                            <td class="py-2 text-right font-medium">KES {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $payment->payment_method }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="py-2 font-bold">Total</td>
                        <td class="py-2 text-right font-bold">KES {{ number_format($totalCollected ?? 0, 2) }}</td>
                        <td class="py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Chart
        const dailyCtx = document.getElementById('dailyChart');
        if (dailyCtx) {
            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($dailyLabels ?? []) !!},
                    datasets: [{
                        label: 'Collection (KES)',
                        data: {!! json_encode($dailyData ?? []) !!},
                        backgroundColor: 'rgba(5, 150, 105, 0.7)',
                        borderColor: 'rgba(5, 150, 105, 1)',
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Comparison Chart
        const comparisonCtx = document.getElementById('monthlyComparisonChart');
        if (comparisonCtx) {
            new Chart(comparisonCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyLabels ?? []) !!},
                    datasets: [{
                        label: 'Monthly Collection (KES)',
                        data: {!! json_encode($monthlyData ?? []) !!},
                        borderColor: 'rgba(5, 150, 105, 1)',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(5, 150, 105, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
