@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollment Report')
@section('subtitle', 'Analyze enrollment trends and statistics')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Enrollment Report</span>
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
            Report Filters
        </h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.tvet.enrollments.reports.enrollment') }}" class="space-y-6">
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

                <!-- Course Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
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
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.tvet.enrollments.reports.enrollment') }}"
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

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Enrollments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalEnrollments) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            {{ $enrollmentData->count() }} days of data
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Course Fees</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($totalFees, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Avg. KES {{ number_format($averageFee, 2) }} per enrollment
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Daily Average</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format(round($totalEnrollments / max($enrollmentData->count(), 1)), 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Enrollments per day
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Peak Day</p>
                @php
                    $peakDay = $enrollmentData->sortByDesc('count')->first();
                @endphp
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $peakDay ? number_format($peakDay->count) : 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $peakDay ? \Carbon\Carbon::parse($peakDay->date)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-fire text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Trend Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Enrollment Trend</h3>
            <p class="text-sm text-gray-500 mt-1">Daily enrollment count over selected period</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="toggleChartType()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-chart-bar"></i>
            </button>
            <button onclick="downloadChart()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>
    <div class="h-80">
        <canvas id="enrollmentTrendChart"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Courses Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 10 Courses by Enrollment</h3>
        <div class="h-64">
            <canvas id="topCoursesChart"></canvas>
        </div>
    </div>

    <!-- Enrollment by Status -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Status Distribution</h3>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Monthly Summary -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Monthly Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg. Fee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $monthlyData = $enrollmentData->groupBy(function($item) {
                            return \Carbon\Carbon::parse($item->date)->format('Y-m');
                        });
                    @endphp
                    @foreach($monthlyData as $month => $items)
                        @php
                            $monthTotal = $items->sum('count');
                            $monthFees = $items->sum('total_fees');
                            $monthAvg = $items->avg('average_fee');
                            $monthName = \Carbon\Carbon::parse($items->first()->date)->format('F Y');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $monthName }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($monthTotal) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">KES {{ number_format($monthFees, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">KES {{ number_format($monthAvg, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Course Breakdown Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Course Enrollment Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">% of Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($courseBreakdown as $course)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $course->course->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $course->course->code ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($course->count) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">
                                {{ $totalEnrollments > 0 ? round(($course->count / $totalEnrollments) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daily Enrollment Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Daily Enrollment Details</h3>
            <span class="text-sm text-gray-500">{{ $enrollmentData->count() }} days</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Fees (KES)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Average Fee (KES)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($enrollmentData as $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($data->date)->format('D, M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                            {{ number_format($data->count) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                            KES {{ number_format($data->total_fees, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                            KES {{ number_format($data->average_fee, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No enrollment data available for the selected period
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
        initializeCharts();
    });

    function initializeCharts() {
        // Enrollment Trend Chart
        const trendCtx = document.getElementById('enrollmentTrendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($enrollmentData->pluck('date')->map(function($date) {
                        return \Carbon\Carbon::parse($date)->format('M d');
                    })) !!},
                    datasets: [{
                        label: 'Enrollments',
                        data: {!! json_encode($enrollmentData->pluck('count')) !!},
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3B82F6',
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
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} enrollments`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5E7EB'
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

        // Top Courses Chart
        const coursesCtx = document.getElementById('topCoursesChart')?.getContext('2d');
        if (coursesCtx) {
            new Chart(coursesCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($courseBreakdown->take(10)->map(function($item) {
                        return $item->course->name ?? 'N/A';
                    })) !!},
                    datasets: [{
                        label: 'Number of Enrollments',
                        data: {!! json_encode($courseBreakdown->take(10)->pluck('count')) !!},
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5E7EB'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($statusBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($statusBreakdown ?? [])) !!},
                        backgroundColor: [
                            '#3B82F6', // registered - blue
                            '#10B981', // in_progress - green
                            '#8B5CF6', // completed - purple
                            '#EF4444', // dropped - red
                            '#F59E0B', // suspended - amber
                            '#6B7280'  // others - gray
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
                        }
                    }
                }
            });
        }
    }

    function toggleChartType() {
        // Toggle between line and bar chart
        const canvas = document.getElementById('enrollmentTrendChart');
        const ctx = canvas.getContext('2d');
        // Implementation would require destroying and recreating chart
        alert('Chart type toggle coming soon');
    }

    function downloadChart() {
        const canvas = document.getElementById('enrollmentTrendChart');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'enrollment-trend-chart.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    }

    function exportReport(format) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = format === 'pdf'
            ? '{{ route("admin.tvet.enrollments.reports.enrollment", ["export" => "pdf"]) }}'
            : '{{ route("admin.tvet.enrollments.reports.enrollment", ["export" => "excel"]) }}';

        // Add current filters to export
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
