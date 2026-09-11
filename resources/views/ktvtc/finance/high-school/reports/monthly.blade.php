@extends('ktvtc.finance.layouts.app')

@section('title', 'Monthly Report')
@section('subtitle', 'Monthly sales, product velocity and behaviour')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Monthly Report</span>
</li>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Month/Year selector --}}
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Month</label>
                <select name="month" class="px-3 py-2 border rounded-lg text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Year</label>
                <select name="year" class="px-3 py-2 border rounded-lg text-sm">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
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
            <p class="text-xs text-gray-500">Active Students</p>
            <p class="text-xl font-bold text-purple-600">{{ number_format($activeStudents) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Avg per Day</p>
            <p class="text-xl font-bold text-yellow-600">KES {{ number_format($averageDaily, 2) }}</p>
        </div>
    </div>

    {{-- Daily trend line --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Daily Sales Trend</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="dailyTrendChart"></canvas>
        </div>
    </div>

    {{-- Hourly heat --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Peak Buying Hours</h3>
        <div style="position: relative; height: 280px;">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    {{-- Product velocity --}}
    <div class="finance-card p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800 text-sm">Top Products &amp; Stock Alert</h3>
            <span class="text-xs text-gray-500">Red rows need restocking</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Product</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Units Sold</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Revenue</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Daily Rate</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Stock</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Days Left</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topProducts ?? [] as $i => $p)
                        @php
                            $needsRestock = isset($p['days_of_stock'])
                                && $p['days_of_stock'] !== null
                                && $p['days_of_stock'] <= 3;
                        @endphp
                        <tr class="{{ $needsRestock ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                            <td class="px-3 py-2">{{ $i + 1 }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $p['product_name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $p['product_code'] }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-bold">{{ number_format($p['quantity']) }}</td>
                            <td class="px-3 py-2 text-right">KES {{ number_format($p['revenue'], 2) }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ $p['daily_rate'] ?? 0 }}/day</td>
                            <td class="px-3 py-2 text-right">{{ number_format($p['current_stock'] ?? 0, 1) }}</td>
                            <td class="px-3 py-2 text-right">
                                @if(!isset($p['days_of_stock']) || $p['days_of_stock'] === null)
                                    <span class="text-gray-400">—</span>
                                @elseif($needsRestock)
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-bold">
                                        {{ $p['days_of_stock'] }} days ⚠
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full">
                                        {{ $p['days_of_stock'] }} days
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-gray-500">No items sold this month</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top spenders --}}
    <div class="finance-card p-4">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Top 10 Spenders of the Month</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            @forelse($topSpenders ?? [] as $spender)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 truncate">{{ $spender['student_name'] }}</p>
                    <p class="text-lg font-bold text-primary">KES {{ number_format($spender['total_spent'], 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $spender['transaction_count'] }} txns</p>
                </div>
            @empty
                <p class="text-gray-500 text-sm col-span-5 text-center">No data</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Daily trend ──
    const trendCanvas = document.getElementById('dailyTrendChart');
    const trendLabels = {!! json_encode(collect($dailyTotals ?? [])->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values()) !!};
    const trendData   = {!! json_encode(collect($dailyTotals ?? [])->pluck('total_spent')->map(fn($v) => (float) $v)->values()) !!};

    if (trendCanvas) {
        if (trendLabels.length === 0) {
            trendCanvas.parentElement.innerHTML =
                '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No data</div>';
        } else {
            new Chart(trendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Daily Spent',
                        data: trendData,
                        borderColor: 'rgba(185, 28, 28, 1)',
                        backgroundColor: 'rgba(185, 28, 28, 0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(185, 28, 28, 1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: c => 'KES ' + Number(c.parsed.y).toLocaleString() } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: v => 'KES ' + Number(v).toLocaleString() } }
                    }
                }
            });
        }
    }

    // ── Hourly peak ──
    const hourCanvas = document.getElementById('hourlyChart');
    if (hourCanvas) {
        const hourlySpend = {!! json_encode(array_values($hourlySpend ?? [])) !!};
        const hourlyCount = {!! json_encode(array_values($hourlyCount ?? [])) !!};
        if (!hourlySpend.some(v => v > 0)) {
            hourCanvas.parentElement.innerHTML =
                '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No data</div>';
        } else {
            const labels = Array.from({length: 24}, (_, i) => String(i).padStart(2,'0') + ':00');
            new Chart(hourCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'KES spent',
                        data: hourlySpend,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: {
                            label: c => 'KES ' + Number(c.parsed.y).toLocaleString()
                                + '  (' + hourlyCount[c.dataIndex] + ' txns)' } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: v => 'KES ' + Number(v).toLocaleString() } }
                    }
                }
            });
        }
    }
});
</script>
@endpush
