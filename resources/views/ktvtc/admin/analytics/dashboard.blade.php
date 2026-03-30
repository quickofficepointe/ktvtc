@extends('ktvtc.admin.layouts.app')

@section('title', 'Analytics Dashboard')
@section('subtitle', 'Comprehensive insights and statistics across all TVET operations')

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
<li class="flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
    <span class="text-sm font-medium text-gray-500">Analytics</span>
</li>
@endsection

@section('content')
<!-- Custom Range Filter (shown when custom range is selected) -->
@if(request('range') == 'custom')
<div class="mb-6 p-4 bg-white rounded-lg shadow-sm border border-gray-200 animate-fade-in">
    <form method="GET" action="{{ route('admin.analytics.dashboard') }}" class="flex items-end space-x-4">
        <input type="hidden" name="range" value="custom">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}" class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
            Apply Filter
        </button>
    </form>
</div>
@endif

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Students -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">+{{ $newStudentsThisMonth ?? 0 }} this month</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Students</h3>
        <div class="flex items-baseline justify-between">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStudents ?? 0) }}</p>
            <p class="text-xs text-gray-500">Active: {{ number_format($activeStudents ?? 0) }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Graduated: {{ number_format($graduatedStudents ?? 0) }}</span>
                <span class="text-gray-600">Dropped: {{ number_format($droppedStudents ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Total Enrollments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-info text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-info bg-info/10 px-2 py-1 rounded-full">{{ number_format($activeEnrollments ?? 0) }} active</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Enrollments</h3>
        <div class="flex items-baseline justify-between">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalEnrollments ?? 0) }}</p>
            <p class="text-xs text-gray-500">This year: {{ number_format($enrollmentsThisYear ?? 0) }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-info h-1.5 rounded-full" style="width: {{ $activeEnrollmentsPercentage ?? 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $activeEnrollmentsPercentage ?? 0 }}% of capacity</p>
        </div>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-success text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-success bg-success/10 px-2 py-1 rounded-full">Collection: {{ $collectionRate ?? 0 }}%</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Revenue</h3>
        <div class="flex items-baseline justify-between">
            <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalRevenue ?? 0) }}</p>
            <p class="text-xs text-gray-500">This period</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Outstanding: KES {{ number_format($outstandingBalance ?? 0) }}</span>
                <span class="text-gray-600">Paid: KES {{ number_format($totalPaid ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Exam Registrations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                <i class="fas fa-certificate text-warning text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-warning bg-warning/10 px-2 py-1 rounded-full">{{ number_format($pendingExamRegistrations ?? 0) }} pending</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Exam Registrations</h3>
        <div class="flex items-baseline justify-between">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalExamRegistrations ?? 0) }}</p>
            <p class="text-xs text-gray-500">Upcoming: {{ number_format($upcomingExams ?? 0) }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Registered: {{ number_format($registeredExams ?? 0) }}</span>
                <span class="text-gray-600">Completed: {{ number_format($completedExams ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Enrollment Trend Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Enrollment Trend</h3>
                <p class="text-sm text-gray-500">Daily enrollment count over time</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="changeChartPeriod('week')" class="px-3 py-1 text-sm rounded-md {{ request('chart_period', 'month') == 'week' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Week</button>
                <button onclick="changeChartPeriod('month')" class="px-3 py-1 text-sm rounded-md {{ request('chart_period', 'month') == 'month' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Month</button>
                <button onclick="changeChartPeriod('year')" class="px-3 py-1 text-sm rounded-md {{ request('chart_period', 'month') == 'year' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Year</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

    <!-- Revenue vs Outstanding -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Revenue Overview</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ $collectionRate ?? 0 }}%</p>
                <p class="text-xs text-gray-600">Collection Rate</p>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-2xl font-bold text-red-600">{{ $defaultRate ?? 0 }}%</p>
                <p class="text-xs text-gray-600">Default Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Demographics Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Gender Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Gender Distribution</h3>
        <div class="flex items-center justify-center h-64">
            <canvas id="genderChart"></canvas>
        </div>
        <div class="mt-4 flex justify-center space-x-6">
            <div class="flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                <span class="text-sm text-gray-600">Male: {{ number_format($maleStudents ?? 0) }}</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 bg-pink-500 rounded-full mr-2"></span>
                <span class="text-sm text-gray-600">Female: {{ number_format($femaleStudents ?? 0) }}</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                <span class="text-sm text-gray-600">Other: {{ number_format($otherGender ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Age Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Age Distribution</h3>
        <div class="h-64">
            <canvas id="ageChart"></canvas>
        </div>
        <div class="mt-4 grid grid-cols-5 gap-2 text-center text-xs">
            <div><span class="block font-medium">Under 18</span><span class="text-gray-600">{{ number_format($ageRanges['under_18'] ?? 0) }}</span></div>
            <div><span class="block font-medium">18-25</span><span class="text-gray-600">{{ number_format($ageRanges['18_25'] ?? 0) }}</span></div>
            <div><span class="block font-medium">26-35</span><span class="text-gray-600">{{ number_format($ageRanges['26_35'] ?? 0) }}</span></div>
            <div><span class="block font-medium">36-45</span><span class="text-gray-600">{{ number_format($ageRanges['36_45'] ?? 0) }}</span></div>
            <div><span class="block font-medium">46+</span><span class="text-gray-600">{{ number_format($ageRanges['46_plus'] ?? 0) }}</span></div>
        </div>
    </div>
</div>

<!-- Payment Methods & Course Popularity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Methods -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @foreach($paymentMethods ?? [] as $method)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ ucfirst($method->payment_method) }}</span>
                    <span class="text-sm text-gray-600">KES {{ number_format($method->total) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full" style="width: {{ ($method->total / ($totalRevenue ?? 1)) * 100 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $method->count }} transactions</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Popular Courses -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 10 Courses by Enrollment</h3>
        <div class="space-y-3">
            @foreach($popularCourses ?? [] as $index => $course)
            <div class="flex items-center">
                <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600 mr-3">{{ $index + 1 }}</span>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $course->name }}</span>
                        <span class="text-sm text-gray-600">{{ $course->total }} students</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-info h-2 rounded-full" style="width: {{ ($course->total / ($popularCourses[0]->total ?? 1)) * 100 }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Status Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Student Status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Status Distribution</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach($studentStatuses ?? [] as $status)
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">{{ ucfirst($status->status) }}</p>
                <p class="text-xl font-bold text-gray-800">{{ $status->count }}</p>
                <p class="text-xs text-gray-500">{{ round(($status->count / ($totalStudents ?? 1)) * 100, 1) }}%</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Enrollment Status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Status Distribution</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach($enrollmentStatuses ?? [] as $status)
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">{{ ucfirst(str_replace('_', ' ', $status->status)) }}</p>
                <p class="text-xl font-bold text-gray-800">{{ $status->count }}</p>
                <p class="text-xs text-gray-500">{{ round(($status->count / ($totalEnrollments ?? 1)) * 100, 1) }}%</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Enrollment Chart
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(enrollmentCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
            datasets: [{
                label: 'Enrollments',
                data: {!! json_encode($enrollmentTrend ?? array_fill(0, 12, 0)) !!},
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
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1F2937',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    borderColor: '#374151',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#E5E7EB',
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 1
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
                legend: {
                    display: false
                },
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

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: [{{ $maleStudents ?? 0 }}, {{ $femaleStudents ?? 0 }}, {{ $otherGender ?? 0 }}],
                backgroundColor: ['#3B82F6', '#EC4899', '#8B5CF6'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Age Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: ['Under 18', '18-25', '26-35', '36-45', '46+'],
            datasets: [{
                label: 'Students',
                data: [
                    {{ $ageRanges['under_18'] ?? 0 }},
                    {{ $ageRanges['18_25'] ?? 0 }},
                    {{ $ageRanges['26_35'] ?? 0 }},
                    {{ $ageRanges['36_45'] ?? 0 }},
                    {{ $ageRanges['46_plus'] ?? 0 }}
                ],
                backgroundColor: '#B91C1C',
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
                    grid: {
                        color: '#E5E7EB'
                    },
                    ticks: {
                        stepSize: 1
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

    // Chart period change
    function changeChartPeriod(period) {
        window.location.href = "{{ route('admin.analytics.dashboard') }}?chart_period=" + period;
    }

    // Export Analytics
    function exportAnalytics() {
        window.location.href = "{{ route('admin.analytics.export') }}";
    }

    // Refresh Data
    function refreshData() {
        location.reload();
    }

    // Initialize tooltips
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded px-2 py-1 z-50';
            tooltip.textContent = this.dataset.tooltip;
            this.appendChild(tooltip);

            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            });
        });
    });
</script>
@endsection
