@extends('ktvtc.finance.layouts.app')

@section('title', 'Daily Report')
@section('subtitle', 'Sales and behaviour for a single day')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Daily Report</span>
</li>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Date picker --}}
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Date</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">
                <i class="fas fa-search mr-2"></i> View
            </button>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Spent</p>
            <p class="text-xl font-bold text-green-600">KES {{ number_format($totalSpent, 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Transactions</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalTransactions) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Students</p>
            <p class="text-xl font-bold text-purple-600">{{ number_format($totalStudents) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Avg per Txn</p>
            <p class="text-xl font-bold text-yellow-600">KES {{ number_format($averagePerTxn, 2) }}</p>
        </div>
    </div>

    {{-- Hourly heat chart --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Spending by Hour of Day</h3>
        <div style="position: relative; height: 280px;">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    {{-- Top spenders --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Top 10 Spenders</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            @forelse($topSpenders ?? [] as $spender)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 truncate">{{ $spender['student_name'] }}</p>
                    <p class="text-lg font-bold text-primary">KES {{ number_format($spender['total_spent'], 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $spender['transaction_count'] }} txns</p>
                </div>
            @empty
                <p class="text-gray-500 text-sm col-span-5 text-center">No purchases</p>
            @endforelse
        </div>
    </div>

    {{-- Top products of the day --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Top Products Sold Today</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Product</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Units Sold</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topProducts ?? [] as $i => $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $i + 1 }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $p['product_name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $p['product_code'] }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-bold">{{ number_format($p['quantity']) }}</td>
                            <td class="px-3 py-2 text-right">KES {{ number_format($p['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-gray-500">No items sold this day</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Transactions table --}}
    <div class="finance-card p-3 overflow-hidden">
        <h3 class="font-bold text-gray-800 text-sm mb-3 px-2">All Transactions</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Time</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Items</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usage ?? [] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-600">{{ $row['time']->format('H:i') }}</td>
                            <td class="px-3 py-2 font-medium">{{ $row['student_name'] }}</td>
                            <td class="px-3 py-2 text-sm">{{ $row['student_class'] }}</td>
                            <td class="px-3 py-2 text-sm">{{ $row['description'] }}</td>
                            <td class="px-3 py-2 text-right font-bold text-red-600">-KES {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-gray-500">No purchases on this date</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('hourlyChart');
    if (!canvas) return;

    const hourlySpend = {!! json_encode(array_values($hourlySpend ?? [])) !!};
    const hourlyCount = {!! json_encode(array_values($hourlyCount ?? [])) !!};
    const hasData = hourlySpend.some(v => v > 0);

    if (!hasData) {
        canvas.parentElement.innerHTML =
            '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No purchases</div>';
        return;
    }

    const labels = Array.from({length: 24}, (_, i) => String(i).padStart(2, '0') + ':00');

    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'KES spent',
                data: hourlySpend,
                backgroundColor: 'rgba(185, 28, 28, 0.7)',
                borderColor: 'rgba(185, 28, 28, 1)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'KES ' + Number(ctx.parsed.y).toLocaleString()
                            + '  (' + hourlyCount[ctx.dataIndex] + ' txns)'
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => 'KES ' + Number(v).toLocaleString() }
                }
            }
        }
    });
});
</script>
@endpush
