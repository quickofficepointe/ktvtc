@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollment Report')
@section('subtitle', 'Track student enrollment trends over time')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Students</span>
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
    <button onclick="exportToExcel()"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Report</span>
    </button>
    <a href="{{ route('admin.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Students</span>
    </a>
</div>
@endsection

@section('content')
<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Filter Report</h3>
        <p class="text-sm text-gray-600 mt-1">Select date range and campus to filter enrollment data</p>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.students.reports.enrollment') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Campus Filter (Admin only) -->
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

                <!-- Group By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                    <select name="group_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="day" {{ request('group_by', 'month') == 'day' ? 'selected' : '' }}>Daily</option>
                        <option value="week" {{ request('group_by') == 'week' ? 'selected' : '' }}>Weekly</option>
                        <option value="month" {{ request('group_by', 'month') == 'month' ? 'selected' : '' }}>Monthly</option>
                        <option value="year" {{ request('group_by') == 'year' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.students.reports.enrollment') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Enrolled</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalEnrolled ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            In selected period
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">This Month</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($enrolledThisMonth ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ now()->format('F Y') }}
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">This Year</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($enrolledThisYear ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ now()->year }}
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Average per {{ ucfirst(request('group_by', 'month')) }}</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($averagePerPeriod ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Trend</h3>
    <div class="h-80">
        <canvas id="enrollmentChart"></canvas>
    </div>
</div>

<!-- Enrollment Data Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Enrollment Details</h3>
        <p class="text-sm text-gray-600 mt-1">Breakdown by {{ ucfirst(request('group_by', 'month')) }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Enrollments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cumulative</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($enrollmentData as $data)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        @php
                            $date = \Carbon\Carbon::parse($data->date);
                            $period = match(request('group_by', 'month')) {
                                'day' => $date->format('M j, Y'),
                                'week' => 'Week ' . $date->weekOfYear . ', ' . $date->year,
                                'month' => $date->format('F Y'),
                                'year' => $date->year,
                                default => $date->format('F Y')
                            };
                        @endphp
                        <span class="font-medium text-gray-900">{{ $period }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-900">{{ number_format($data->count) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $percentage = $totalEnrolled > 0 ? round(($data->count / $totalEnrolled) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center">
                            <span class="text-gray-600 mr-2">{{ $percentage }}%</span>
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-600">{{ number_format($data->cumulative ?? 0) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-chart-line text-gray-300 text-4xl mb-3"></i>
                        <p>No enrollment data available for the selected period</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('exportModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Export Report</h3>
                    <button onclick="closeModal('exportModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('admin.students.export') }}" method="GET" id="exportForm">
                    <input type="hidden" name="report" value="enrollment">
                    <input type="hidden" name="date_from" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                    <input type="hidden" name="campus_id" value="{{ request('campus_id') }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <select name="format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Charts</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="include_charts" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-600">Include chart images in report</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('exportModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitExport()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enrollment Chart
        const ctx = document.getElementById('enrollmentChart')?.getContext('2d');
        if (ctx) {
            const labels = {!! json_encode($enrollmentData->pluck('date')->map(function($date) {
                $d = \Carbon\Carbon::parse($date);
                return match(request('group_by', 'month')) {
                    'day' => $d->format('M j'),
                    'week' => 'Wk ' . $d->weekOfYear,
                    'month' => $d->format('M Y'),
                    'year' => $d->year,
                    default => $d->format('M Y')
                };
            })->toArray() ?? []) !!};

            const data = {!! json_encode($enrollmentData->pluck('count')->toArray() ?? []) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Enrollments',
                        data: data,
                        borderColor: '#B91C1C',
                        backgroundColor: 'rgba(185, 28, 28, 0.1)',
                        borderWidth: 2,
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
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5E7EB'
                            },
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Enrollments: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    function exportToExcel() {
        openModal('exportModal');
    }

    function submitExport() {
        document.getElementById('exportForm').submit();
    }

    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('exportModal');
        }
    });
</script>

<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
@endsection
