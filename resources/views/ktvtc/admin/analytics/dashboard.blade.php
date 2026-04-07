@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Analytics Dashboard')
@section('subtitle', 'View insights and statistics across the platform')

@section('header-actions')
<div class="flex items-center space-x-3">
    <!-- Date Range Filter -->
    <div class="relative">
        <select id="dateRange" onchange="window.location.href = this.value" class="block w-full pl-4 pr-10 py-2 text-sm border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'today']) }}" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'week']) }}" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'month']) }}" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'quarter']) }}" {{ request('range') == 'quarter' ? 'selected' : '' }}>This Quarter</option>
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'year']) }}" {{ request('range') == 'year' ? 'selected' : '' }}>This Year</option>
            <option value="{{ route('admin.analytics.dashboard', ['range' => 'custom']) }}" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
        </select>
    </div>

    <!-- Export Button -->
    <button onclick="exportAnalytics()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>

    <!-- Refresh Button -->
    <button onclick="refreshData()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center space-x-2">
        <i class="fas fa-sync-alt"></i>
        <span>Refresh</span>
    </button>
</div>
@endsection

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Analytics</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Dashboard</span>
    </div>
</li>
@endsection

@section('content')
<!-- Custom Range Filter -->
@if(request('range') == 'custom')
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.analytics.dashboard') }}" class="flex items-end space-x-4">
        <input type="hidden" name="range" value="custom">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', $startDate ?? now()->subDays(30)->format('Y-m-d')) }}"
                   class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate ?? now()->format('Y-m-d')) }}"
                   class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary px-3 py-2">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
            Apply Filter
        </button>
        <a href="{{ route('admin.analytics.dashboard') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Reset
        </a>
    </form>
</div>
@endif

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Students -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">
                +{{ number_format($newStudentsThisMonth ?? 0) }} this month
            </span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Students</h3>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStudents ?? 0) }}</p>
        <div class="mt-2 flex items-center justify-between text-xs">
            <span class="text-gray-500">Active: {{ number_format($activeStudents ?? 0) }}</span>
            <span class="text-gray-500">Graduated: {{ number_format($graduatedStudents ?? 0) }}</span>
        </div>
    </div>

    <!-- Total Enrollments -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                {{ number_format($activeEnrollments ?? 0) }} active
            </span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Enrollments</h3>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalEnrollments ?? 0) }}</p>
        <div class="mt-2">
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $activeEnrollmentsPercentage ?? 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ $activeEnrollmentsPercentage ?? 0 }}% active</p>
        </div>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">
                Collection: {{ number_format($collectionRate ?? 0, 1) }}%
            </span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Revenue</h3>
        <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalRevenue ?? 0, 2) }}</p>
        <div class="mt-2 flex items-center justify-between text-xs">
            <span class="text-green-600">Paid: KES {{ number_format($totalPaid ?? 0, 2) }}</span>
            <span class="text-red-600">Outstanding: KES {{ number_format($outstandingBalance ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Exam Registrations -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-certificate text-amber-600 text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                {{ number_format($pendingExamRegistrations ?? 0) }} pending
            </span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Exam Registrations</h3>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalExamRegistrations ?? 0) }}</p>
        <div class="mt-2 flex items-center justify-between text-xs">
            <span class="text-gray-500">Upcoming: {{ number_format($upcomingExams ?? 0) }}</span>
            <span class="text-gray-500">Completed: {{ number_format($completedExams ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Enrollment Trend Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Enrollment Trend</h3>
                <p class="text-sm text-gray-500">Monthly enrollment count over time</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="changeChartPeriod('6months')" class="px-3 py-1 text-xs rounded-md {{ ($chartPeriod ?? '12months') == '6months' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">6 Months</button>
                <button onclick="changeChartPeriod('12months')" class="px-3 py-1 text-xs rounded-md {{ ($chartPeriod ?? '12months') == '12months' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">12 Months</button>
                <button onclick="changeChartPeriod('24months')" class="px-3 py-1 text-xs rounded-md {{ ($chartPeriod ?? '12months') == '24months' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">24 Months</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

    <!-- Revenue Pie Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Revenue Overview</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ number_format($collectionRate ?? 0, 1) }}%</p>
                <p class="text-xs text-gray-600">Collection Rate</p>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-2xl font-bold text-red-600">{{ number_format($defaultRate ?? 0, 1) }}%</p>
                <p class="text-xs text-gray-600">Default Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods & Course Popularity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Methods -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @forelse($paymentMethods ?? [] as $method)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">
                        @if($method->payment_method == 'mpesa') M-Pesa
                        @elseif($method->payment_method == 'cash') Cash
                        @elseif($method->payment_method == 'bank') Bank Transfer
                        @elseif($method->payment_method == 'kcb') KCB Bank
                        @else {{ ucfirst($method->payment_method) }}
                        @endif
                    </span>
                    <span class="text-sm text-gray-600">KES {{ number_format($method->total, 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full" style="width: {{ ($method->total / max($totalRevenue, 1)) * 100 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($method->count) }} transactions</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No payment data available</p>
            @endforelse
        </div>
    </div>

    <!-- Popular Courses -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Courses by Enrollment</h3>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @forelse($popularCourses ?? [] as $index => $course)
            <div class="flex items-center">
                <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600 mr-3">{{ $index + 1 }}</span>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ Str::limit($course->course_name ?? $course->name, 30) }}</span>
                        <span class="text-sm text-gray-600">{{ number_format($course->total) }} students</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php $maxTotal = $popularCourses[0]->total ?? 1; @endphp
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($course->total / $maxTotal) * 100 }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No course data available</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Status Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Enrollment Status -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Status Distribution</h3>
        <div class="grid grid-cols-2 gap-4">
            @forelse($enrollmentStatuses ?? [] as $status)
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">{{ ucfirst(str_replace('_', ' ', $status->status)) }}</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($status->count) }}</p>
                <p class="text-xs text-gray-500">{{ round(($status->count / max($totalEnrollments, 1)) * 100, 1) }}%</p>
            </div>
            @empty
            <div class="col-span-2 text-center py-4 text-gray-500">No enrollment status data available</div>
            @endforelse
        </div>
    </div>

    <!-- Exam Body Breakdown -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Exam Registrations by Body</h3>
        <div class="space-y-4">
            @forelse($examBodyBreakdown ?? [] as $body)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $body->exam_body }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($body->count) }} registrations</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($body->count / max($totalExamRegistrations, 1)) * 100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No exam registration data available</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Payments</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt No.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentPayments ?? [] as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-800">{{ $payment->receipt_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $payment->student->full_name ?? $payment->student_name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->enrollment->course_name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($payment->payment_method == 'mpesa') bg-green-100 text-green-800
                            @elseif($payment->payment_method == 'cash') bg-yellow-100 text-yellow-800
                            @elseif($payment->payment_method == 'bank') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $payment->payment_method_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-green-600 text-right">KES {{ number_format($payment->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">No recent payments</td>
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
    // Enrollment Chart
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    let enrollmentChart = new Chart(enrollmentCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Enrollments',
                data: {!! json_encode($enrollmentTrend ?? []) !!},
                borderColor: '#B91C1C',
                backgroundColor: 'rgba(185, 28, 28, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#B91C1C',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#E5E7EB' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Outstanding'],
            datasets: [{
                data: [{{ $totalPaid ?? 0 }}, {{ $outstandingBalance ?? 0 }}],
                backgroundColor: ['#10B981', '#EF4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return `${label}: KES ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    function changeChartPeriod(period) {
        let url = new URL(window.location.href);
        url.searchParams.set('chart_period', period);
        window.location.href = url.toString();
    }

    function exportAnalytics() {
        let url = new URL(window.location.href);
        url.searchParams.set('export', 'true');
        window.location.href = url.toString();
    }

    function refreshData() {
        location.reload();
    }
</script>

<style>
    .card-hover {
        transition: all 0.2s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
