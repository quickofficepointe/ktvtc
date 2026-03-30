@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Monthly Payment Report')
@section('subtitle', 'Payment summary for ' . Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'))

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.fee-payments.reports.monthly', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-chevron-left"></i>
        <span>Previous Month</span>
    </a>
    <a href="{{ route('admin.tvet.fee-payments.reports.monthly', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <span>Next Month</span>
        <i class="fas fa-chevron-right"></i>
    </a>
    <a href="{{ route('admin.tvet.fee-payments.reports.monthly', ['month' => $month, 'year' => $year, 'export' => true]) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export CSV</span>
    </a>
</div>
@endsection

@section('content')
<!-- Month/Year Selector -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <form method="GET" action="{{ route('admin.tvet.fee-payments.reports.monthly') }}" class="flex items-end space-x-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
            <select name="month" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                        {{ Carbon\Carbon::create()->month($i)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
            <select name="year" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                @for($i = now()->year - 5; $i <= now()->year + 1; $i++)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
            View Report
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Payments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $summary['total_payments'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-money-check-alt text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            {{ $summary['completed_payments'] }} completed, {{ $summary['pending_payments'] }} pending
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Amount</p>
                <p class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format($summary['total_amount'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-coins text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Avg: KES {{ number_format($summary['average_per_day'], 2) }} per day
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Best Day</p>
                @if($summary['best_day'])
                <p class="text-2xl font-bold text-amber-600 mt-2">KES {{ number_format($summary['best_day']['total'], 2) }}</p>
                @else
                <p class="text-2xl font-bold text-gray-400 mt-2">-</p>
                @endif
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-crown text-amber-600 text-xl"></i>
            </div>
        </div>
        @if($summary['best_day'])
        <div class="mt-4 text-sm text-gray-500">
            {{ $summary['best_day']['count'] }} payments
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Success Rate</p>
                <p class="text-2xl font-bold text-purple-600 mt-2">
                    {{ number_format(($summary['completed_payments'] / max($summary['total_payments'], 1)) * 100, 1) }}%
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            {{ $summary['failed_payments'] }} failed
        </div>
    </div>
</div>

<!-- Daily Breakdown Chart -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Collection Trend</h3>
        <div class="h-80">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @foreach($summary['by_method'] as $method => $data)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">
                        @switch($method)
                            @case('kcb_stk_push') KCB STK Push @break
                            @case('paybill') Paybill @break
                            @case('bank_deposit') Bank Deposit @break
                            @case('cash') Cash @break
                            @case('helb') HELB @break
                            @case('sponsor') Sponsor @break
                            @default {{ ucfirst($method) }}
                        @endswitch
                    </span>
                    <span class="text-sm text-gray-600">{{ number_format(($data['total'] / $summary['total_amount']) * 100, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary rounded-full h-2" style="width: {{ ($data['total'] / $summary['total_amount']) * 100 }}%"></div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-gray-500">
                    <span>{{ $data['count'] }} transactions</span>
                    <span>KES {{ number_format($data['total'], 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Daily Breakdown Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daily Breakdown</h3>
        <p class="text-sm text-gray-600 mt-1">Payment summary by day</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                    <th class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">% of Month</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php
                    $daysInMonth = Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
                @endphp
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                        $dayData = $summary['daily_breakdown'][$date] ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-6">
                            <span class="text-sm font-medium text-gray-900">
                                {{ Carbon\Carbon::createFromDate($year, $month, $day)->format('M j, Y') }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-right">
                            <span class="text-sm text-gray-900">{{ $dayData['count'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-6 text-right">
                            <span class="text-sm font-bold {{ $dayData ? 'text-gray-900' : 'text-gray-400' }}">
                                KES {{ number_format($dayData['total'] ?? 0, 2) }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-right">
                            @if($dayData)
                            <span class="text-sm text-gray-600">
                                {{ number_format(($dayData['total'] / $summary['total_amount']) * 100, 1) }}%
                            </span>
                            @else
                            <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
            <tfoot class="bg-gray-50 font-medium">
                <tr>
                    <td class="py-3 px-6 text-right">Totals:</td>
                    <td class="py-3 px-6 text-right">{{ $summary['completed_payments'] }}</td>
                    <td class="py-3 px-6 text-right text-primary">KES {{ number_format($summary['total_amount'], 2) }}</td>
                    <td class="py-3 px-6 text-right">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
        <p class="text-sm text-gray-600 mt-1">Latest 20 payments in {{ Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments->take(20) as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $payment->payment_date->format('M j, Y') }}</span>
                        <span class="text-xs text-gray-500 block">{{ $payment->payment_time }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-medium text-gray-900">{{ $payment->receipt_number }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $payment->student->full_name ?? 'N/A' }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100 text-purple-800' :
                               ($payment->payment_method == 'paybill' ? 'bg-blue-100 text-blue-800' :
                               ($payment->payment_method == 'bank_deposit' ? 'bg-green-100 text-green-800' :
                               ($payment->payment_method == 'cash' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800'))) }}">
                            {{ $payment->payment_method_label }}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <span class="text-sm font-bold text-gray-900">KES {{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        @if($payment->status == 'completed')
                            @if($payment->is_verified)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $payment->status == 'failed' ? 'bg-red-100 text-red-800' :
                                   ($payment->status == 'reversed' ? 'bg-orange-100 text-orange-800' :
                                   'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-money-check-alt text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payments found for this month</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dailyChart').getContext('2d');

        const dailyData = {!! json_encode($summary['daily_breakdown']) !!};
        const daysInMonth = {{ $daysInMonth }};

        const labels = [];
        const amounts = [];

        for(let day = 1; day <= daysInMonth; day++) {
            const date = '{{ $year }}-{{ str_pad($month, 2, "0", STR_PAD_LEFT) }}-' + String(day).padStart(2, '0');
            labels.push(day);
            amounts.push(dailyData[date]?.total || 0);
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Collection (KES)',
                    data: amounts,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'KES ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
