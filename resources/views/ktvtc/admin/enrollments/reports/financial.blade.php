@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Financial Report')
@section('subtitle', 'Analyze fee collection and payment trends')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Enrollments</span>
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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Financial Report</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportReport('pdf')"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-file-pdf"></i>
        <span>Export PDF</span>
    </button>
    <button onclick="exportReport('excel')"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-file-excel"></i>
        <span>Export Excel</span>
    </button>
    <a href="{{ route('admin.tvet.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-filter text-primary mr-2"></i>
            Financial Report Filters
        </h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.tvet.enrollments.reports.financial') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Campus Filter -->
                @if(auth()->user()->role == 2)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial Payment</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.tvet.enrollments.reports.financial') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Billed</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($totalBilled, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-invoice text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Total course fees billed
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Collected</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($totalPaid, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            {{ number_format($collectionRate, 1) }}% collection rate
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Outstanding Balance</p>
                <p class="text-2xl font-bold text-red-600 mt-2">KES {{ number_format($totalBalance, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Pending payments
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Collection Efficiency</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($collectionRate, 1) }}%</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-primary rounded-full h-2.5" style="width: {{ $collectionRate }}%"></div>
        </div>
    </div>
</div>

<!-- Financial Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue Trend Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Revenue Trend</h3>
                <p class="text-sm text-gray-500 mt-1">Daily billed vs collected amounts</p>
            </div>
            <button onclick="downloadChart('revenue')" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-download"></i>
            </button>
        </div>
        <div class="h-80">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <!-- Payment Method Distribution -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Payment Methods</h3>
                <p class="text-sm text-gray-500 mt-1">Distribution by payment type</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>
</div>

<!-- Outstanding Balances Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Outstanding Balances</h3>
                <p class="text-sm text-gray-500 mt-1">Enrollments with pending payments</p>
            </div>
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                KES {{ number_format($totalBalance, 2) }} Total Outstanding
            </span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollment #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Fee</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php
                    $outstandingEnrollments = \App\Models\Enrollment::where('balance', '>', 0)
                        ->when(auth()->user()->role != 2, function($q) {
                            return $q->where('campus_id', auth()->user()->campus_id);
                        })
                        ->when(request('campus_id'), function($q, $campusId) {
                            return $q->where('campus_id', $campusId);
                        })
                        ->with(['student', 'course', 'campus'])
                        ->orderBy('balance', 'desc')
                        ->limit(20)
                        ->get();
                @endphp
                @forelse($outstandingEnrollments as $enrollment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ substr($enrollment->student->first_name ?? 'S', 0, 1) }}{{ substr($enrollment->student->last_name ?? 'T', 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $enrollment->student->full_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->student->student_number ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono text-gray-900">{{ $enrollment->enrollment_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $enrollment->course->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $enrollment->intake_period }} {{ $enrollment->intake_year }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                            KES {{ number_format($enrollment->total_course_fee, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                            KES {{ number_format($enrollment->amount_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-red-600">
                            KES {{ number_format($enrollment->balance, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'registered' => 'blue',
                                    'in_progress' => 'green',
                                    'completed' => 'purple',
                                    'dropped' => 'red',
                                    'suspended' => 'yellow',
                                ];
                                $color = $statusColors[$enrollment->status] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.tvet.enrollments.show', $enrollment) }}"
                               class="text-primary hover:text-primary-dark text-sm">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                            <p>No outstanding balances found!</p>
                            <p class="text-sm text-gray-400 mt-1">All enrollments are fully paid</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($outstandingEnrollments->count() >= 20)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-center">
        <a href="{{ route('admin.tvet.enrollments.index', ['balance' => '>0']) }}"
           class="text-sm text-primary hover:text-primary-dark">
            View all outstanding balances
        </a>
    </div>
    @endif
</div>

<!-- Daily Financial Summary -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Daily Financial Summary</h3>
            <span class="text-sm text-gray-500">{{ $financialData->count() }} days</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Billed (KES)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Collected (KES)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance (KES)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Collection Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($financialData as $data)
                    @php
                        $dailyCollectionRate = $data->total_billed > 0 ? ($data->total_paid / $data->total_billed) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($data->date)->format('D, M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                            KES {{ number_format($data->total_billed, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-green-600">
                            KES {{ number_format($data->total_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-red-600">
                            KES {{ number_format($data->total_balance, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                @if($dailyCollectionRate >= 80) bg-green-100 text-green-800
                                @elseif($dailyCollectionRate >= 50) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ number_format($dailyCollectionRate, 1) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No financial data available for the selected period
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Totals</th>
                    <th class="px-6 py-4 text-right text-sm font-medium text-gray-900">KES {{ number_format($financialData->sum('total_billed'), 2) }}</th>
                    <th class="px-6 py-4 text-right text-sm font-medium text-green-600">KES {{ number_format($financialData->sum('total_paid'), 2) }}</th>
                    <th class="px-6 py-4 text-right text-sm font-medium text-red-600">KES {{ number_format($financialData->sum('total_balance'), 2) }}</th>
                    <th class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                        @php
                            $totalBilledSum = $financialData->sum('total_billed');
                            $totalPaidSum = $financialData->sum('total_paid');
                            $overallRate = $totalBilledSum > 0 ? ($totalPaidSum / $totalBilledSum) * 100 : 0;
                        @endphp
                        {{ number_format($overallRate, 1) }}%
                    </th>
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
        initializeCharts();
    });

    function initializeCharts() {
        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueTrendChart')?.getContext('2d');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($financialData->pluck('date')->map(function($date) {
                        return \Carbon\Carbon::parse($date)->format('M d');
                    })) !!},
                    datasets: [
                        {
                            label: 'Billed Amount',
                            data: {!! json_encode($financialData->pluck('total_billed')) !!},
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Collected Amount',
                            data: {!! json_encode($financialData->pluck('total_paid')) !!},
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.raw || 0;
                                    return `${label}: KES ${value.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5E7EB'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentMethodChart')?.getContext('2d');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($paymentMethodBreakdown ?? ['Cash', 'M-Pesa', 'Bank Transfer', 'Cheque'])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($paymentMethodBreakdown ?? [0, 0, 0, 0])) !!},
                        backgroundColor: [
                            '#10B981', // Cash - green
                            '#3B82F6', // M-Pesa - blue
                            '#8B5CF6', // Bank Transfer - purple
                            '#F59E0B', // Cheque - amber
                            '#6B7280'  // Other - gray
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: KES ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function downloadChart(chartType) {
        let canvas;
        if (chartType === 'revenue') {
            canvas = document.getElementById('revenueTrendChart');
        }

        if (canvas) {
            const link = document.createElement('a');
            link.download = `${chartType}-chart.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    }

    function exportReport(format) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = format === 'pdf'
            ? '{{ route("admin.tvet.enrollments.reports.financial", ["export" => "pdf"]) }}'
            : '{{ route("admin.tvet.enrollments.reports.financial", ["export" => "excel"]) }}';

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>

<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endsection
