@extends('ktvtc.finance.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Finance Overview & Analytics')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .stat-card .icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .trend-up { color: #10B981; }
    .trend-down { color: #EF4444; }
    .progress-bar {
        height: 6px;
        border-radius: 3px;
        background: #E5E7EB;
        overflow: hidden;
    }
    .progress-bar .fill {
        height: 100%;
        border-radius: 3px;
        transition: width 1s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Collected -->
        <div class="finance-card p-6 stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Collected</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($totalCollected ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">All time collections</p>
                </div>
                <div class="icon bg-green-100 text-green-600">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="progress-bar">
                    <div class="fill bg-green-500" style="width: {{ $collectionRate ?? 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Collection Rate: {{ $collectionRate ?? 0 }}%</p>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="finance-card p-6 stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Collection</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($todayCollection ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $todayPaymentsCount ?? 0 }} transactions today</p>
                </div>
                <div class="icon bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">Today</span>
                <span class="text-xs font-semibold text-blue-600">{{ $todayPaymentsCount ?? 0 }} payments</span>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="finance-card p-6 stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Outstanding Balance</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">KES {{ number_format($outstandingBalance ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Pending student fees</p>
                </div>
                <div class="icon bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('finance.student-fees.reports.outstanding') }}" class="text-xs text-primary hover:underline">View outstanding students →</a>
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="finance-card p-6 stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Verifications</p>
                    <h3 class="text-2xl font-bold text-orange-600 mt-1">{{ $pendingVerifications ?? 0 }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Awaiting verification</p>
                </div>
                <div class="icon bg-orange-100 text-orange-600">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-3">
                @if(($pendingVerifications ?? 0) > 0)
                    <a href="{{ route('finance.student-fees.index', ['is_verified' => 0]) }}" class="text-xs text-primary hover:underline">Verify now →</a>
                @else
                    <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i> All verified</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Second Row: Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Monthly Collection -->
        <div class="finance-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Monthly Collection</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-1">KES {{ number_format($monthlyCollection ?? 0, 2) }}</h3>
                </div>
                <div class="icon bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ now()->format('F Y') }}</p>
        </div>

        <!-- Today's Transactions -->
        <div class="finance-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Transactions</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-1">{{ $todayTransactions ?? 0 }}</h3>
                    <p class="text-xs text-gray-500">KES {{ number_format($todayTransactionAmount ?? 0, 2) }}</p>
                </div>
                <div class="icon bg-indigo-100 text-indigo-600">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
        </div>

        <!-- Pending Transactions -->
        <div class="finance-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Transactions</p>
                    <h3 class="text-xl font-bold text-orange-600 mt-1">{{ $pendingTransactions ?? 0 }}</h3>
                    <p class="text-xs text-gray-500">Awaiting processing</p>
                </div>
                <div class="icon bg-orange-100 text-orange-600">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="mt-3">
                @if(($pendingTransactions ?? 0) > 0)
                    <a href="{{ route('finance.transactions.pending') }}" class="text-xs text-primary hover:underline">View pending →</a>
                @endif
            </div>
        </div>

        <!-- Collection Rate -->
        <div class="finance-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Collection Rate</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-1">{{ $collectionRate ?? 0 }}%</h3>
                    <p class="text-xs text-gray-500">Overall performance</p>
                </div>
                <div class="icon bg-teal-100 text-teal-600">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="progress-bar">
                    <div class="fill bg-teal-500" style="width: {{ $collectionRate ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trend Chart -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Monthly Collection Trend</h3>
            <canvas id="monthlyTrendChart" height="250"></canvas>
        </div>

        <!-- Payment Methods Chart -->
        <div class="finance-card p-6">
            <h3 class="font-bold text-gray-800 mb-4">Payment Methods</h3>
            <canvas id="paymentMethodsChart" height="250"></canvas>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments -->
        <div class="finance-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Recent Payments</h3>
                <a href="{{ route('finance.student-fees.index') }}" class="text-sm text-primary hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Student</th>
                            <th class="pb-2 font-semibold">Amount</th>
                            <th class="pb-2 font-semibold">Method</th>
                            <th class="pb-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments ?? [] as $payment)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">
                                    <span class="font-medium">{{ $payment->student->full_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $payment->student->student_number ?? '' }}</span>
                                </td>
                                <td class="py-2 font-semibold text-gray-800">KES {{ number_format($payment->amount, 2) }}</td>
                                <td class="py-2">
                                    <span class="text-xs uppercase">{{ $payment->payment_method }}</span>
                                </td>
                                <td class="py-2">
                                    @if($payment->is_verified)
                                        <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                    @else
                                        <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No recent payments</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="finance-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Recent Transactions</h3>
                <a href="{{ route('finance.transactions.index') }}" class="text-sm text-primary hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Reference</th>
                            <th class="pb-2 font-semibold">Amount</th>
                            <th class="pb-2 font-semibold">Method</th>
                            <th class="pb-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions ?? [] as $transaction)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">
                                    <span class="font-medium">{{ $transaction->transaction_number }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $transaction->sale->invoice_number ?? 'N/A' }}</span>
                                </td>
                                <td class="py-2 font-semibold text-gray-800">KES {{ number_format($transaction->amount, 2) }}</td>
                                <td class="py-2">
                                    <span class="text-xs uppercase">{{ $transaction->payment_method }}</span>
                                </td>
                                <td class="py-2">
                                    @if($transaction->status === 'completed')
                                        <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                    @else
                                        <span class="status-badge status-failed">{{ $transaction->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No recent transactions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Fee Payments (Full List) -->
    <div class="finance-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Recent Fee Payments</h3>
            <a href="{{ route('finance.student-fees.index') }}" class="text-sm text-primary hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Receipt</th>
                        <th class="pb-2 font-semibold">Student</th>
                        <th class="pb-2 font-semibold">Amount</th>
                        <th class="pb-2 font-semibold">Method</th>
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold">Status</th>
                        <th class="pb-2 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFeePayments ?? [] as $payment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">
                                <span class="font-medium text-primary">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="py-2">
                                <span>{{ $payment->student->full_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $payment->student->student_number ?? '' }}</span>
                            </td>
                            <td class="py-2 font-semibold text-gray-800">KES {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs uppercase">{{ $payment->payment_method }}</span>
                            </td>
                            <td class="py-2 text-gray-600">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="py-2">
                                @if($payment->is_verified && $payment->status === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @elseif($payment->status === 'completed' && !$payment->is_verified)
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif($payment->status === 'reversed')
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Reversed</span>
                                @else
                                    <span class="status-badge status-pending">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <a href="{{ route('finance.student-fees.show', $payment) }}" class="text-primary hover:text-primary-dark mr-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.student-fees.receipt', $payment) }}" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-500">No recent fee payments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Stats Footer -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Student::count() ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Students</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Enrollment::where('status', 'active')->count() ?? 0 }}</p>
            <p class="text-xs text-gray-500">Active Enrollments</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\FeePayment::where('status', 'completed')->count() ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Payments</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\FeePayment::where('status', 'completed')->where('is_verified', false)->count() ?? 0 }}</p>
            <p class="text-xs text-gray-500">Pending Verification</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels ?? []) !!},
                    datasets: [{
                        label: 'Collection (KES)',
                        data: {!! json_encode($monthlyData ?? []) !!},
                        backgroundColor: 'rgba(5, 150, 105, 0.7)',
                        borderColor: 'rgba(5, 150, 105, 1)',
                        borderWidth: 2,
                        borderRadius: 6
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

        // Payment Methods Chart
        const methodsCtx = document.getElementById('paymentMethodsChart');
        if (methodsCtx) {
            const methodData = {!! json_encode($paymentMethods ?? []) !!};
            const labels = methodData.map(item => item.payment_method.toUpperCase());
            const data = methodData.map(item => item.total);
            const colors = ['#059669', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6'];

            new Chart(methodsCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, data.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Animate progress bars on load
        document.querySelectorAll('.progress-bar .fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush
