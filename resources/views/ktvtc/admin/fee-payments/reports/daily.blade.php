@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Daily Collection Report')
@section('subtitle', 'View daily payment collections')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Finance</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Reports</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Daily Collection</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportReport()"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Report</span>
    </button>
    <a href="{{ route('admin.fee-payments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Payments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Date Picker -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Select Date</h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.fee-payments.reports.daily') }}" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Date</label>
                <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
                       class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            @if(auth()->user()->role == 2)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                <select name="campus_id" class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                    View Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Total Collected</p>
        <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalCollected, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $selectedDate->format('F j, Y') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Number of Transactions</p>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($transactionCount) }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Average per Transaction</p>
        <p class="text-2xl font-bold text-purple-600">KES {{ number_format($averageTransaction, 2) }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Pending Verification</p>
        <p class="text-2xl font-bold text-amber-600">{{ number_format($pendingCount) }}</p>
    </div>
</div>

<!-- Hourly Breakdown Chart -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Hourly Collection</h3>
    </div>
    <div class="p-6">
        <div id="hourlyChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Payment Method Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">By Payment Method</h3>
        <div class="space-y-4">
            @foreach($methodBreakdown as $method => $data)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ ucfirst($method) }}</span>
                    <span class="text-sm text-gray-600">KES {{ number_format($data['total'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary rounded-full h-2" style="width: {{ $data['percentage'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $data['count'] }} transactions ({{ $data['percentage'] }}%)</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">By Campus</h3>
        <div class="space-y-4">
            @foreach($campusBreakdown as $campus => $data)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $campus }}</span>
                    <span class="text-sm text-gray-600">KES {{ number_format($data['total'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 rounded-full h-2" style="width: {{ $data['percentage'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $data['count'] }} transactions</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Transactions for {{ $selectedDate->format('F j, Y') }}</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $transaction->created_at->format('h:i A') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.fee-payments.show', $transaction) }}" class="text-primary hover:underline font-mono">
                            {{ $transaction->receipt_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4">{{ $transaction->student->full_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            @if($transaction->payment_method == 'cash') bg-yellow-100 text-yellow-800
                            @elseif($transaction->payment_method == 'mpesa') bg-green-100 text-green-800
                            @elseif($transaction->payment_method == 'bank') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ strtoupper($transaction->payment_method) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-medium text-green-600">KES {{ number_format($transaction->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        @if($transaction->is_verified)
                            <span class="text-green-600 text-sm"><i class="fas fa-check-circle"></i> Verified</span>
                        @else
                            <span class="text-amber-600 text-sm"><i class="fas fa-clock"></i> Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        No transactions found for this date
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right font-medium">Total:</td>
                    <td class="px-6 py-3 font-bold text-green-600">KES {{ number_format($totalCollected, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($hourlyLabels) !!},
                datasets: [{
                    label: 'Collection (KES)',
                    data: {!! json_encode($hourlyData) !!},
                    backgroundColor: '#B91C1C',
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
                            callback: value => 'KES ' + value.toLocaleString()
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });

    function exportReport() {
        window.location.href = '{{ route("admin.fee-payments.export") }}?report=daily&date={{ $selectedDate->format("Y-m-d") }}' +
            (new URLSearchParams(window.location.search).get('campus_id') ? '&campus_id=' + new URLSearchParams(window.location.search).get('campus_id') : '');
    }
</script>
@endsection
