@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Summary Report')
@section('subtitle', 'Overview of examination registrations and performance')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Examinations</span>
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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Exam Summary</span>
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
    <a href="{{ route('admin.exam-registrations.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registrations</span>
    </a>
</div>
@endsection

@section('content')
<!-- Filter Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Filter Summary</h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.exam-registrations.summary') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select name="year" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ ($selectedYear ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                <select name="exam_body" class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Bodies</option>
                    <option value="KNEC" {{ request('exam_body') == 'KNEC' ? 'selected' : '' }}>KNEC</option>
                    <option value="CDACC" {{ request('exam_body') == 'CDACC' ? 'selected' : '' }}>CDACC</option>
                    <option value="NITA" {{ request('exam_body') == 'NITA' ? 'selected' : '' }}>NITA</option>
                    <option value="TVETA" {{ request('exam_body') == 'TVETA' ? 'selected' : '' }}>TVETA</option>
                </select>
            </div>
            @if(auth()->user()->role == 2)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                <select name="campus_id" class="w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                    Apply Filter
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
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalRegistrations) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-alt text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Registered</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($registeredCount) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ $totalRegistrations > 0 ? round(($registeredCount / $totalRegistrations) * 100, 1) : 0 }}% of total
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Completed</p>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($completedCount) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pass Rate</p>
                <p class="text-3xl font-bold text-blue-600">{{ $passRate }}%</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-star text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Registration by Exam Body -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Registrations by Exam Body</h3>
        <div class="h-64">
            <canvas id="examBodyChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Distribution</h3>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Monthly Trend -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Registrations - {{ $selectedYear ?? now()->year }}</h3>
        <div class="h-64">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Performance by Exam Body -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance by Exam Body</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs font-medium text-gray-500">Exam Body</th>
                        <th class="py-2 text-right text-xs font-medium text-gray-500">Total</th>
                        <th class="py-2 text-right text-xs font-medium text-gray-500">Pass</th>
                        <th class="py-2 text-right text-xs font-medium text-gray-500">Fail</th>
                        <th class="py-2 text-right text-xs font-medium text-gray-500">Pass Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($performanceByBody as $body)
                    <tr>
                        <td class="py-2 text-sm font-medium">{{ $body['name'] }}</td>
                        <td class="py-2 text-sm text-right">{{ $body['total'] }}</td>
                        <td class="py-2 text-sm text-right text-green-600">{{ $body['pass'] }}</td>
                        <td class="py-2 text-sm text-right text-red-600">{{ $body['fail'] }}</td>
                        <td class="py-2 text-sm text-right font-bold">{{ $body['pass_rate'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Courses by Registration -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Top Courses by Registration</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($topCourses as $course)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $course['name'] }}</span>
                        <span class="text-sm text-gray-600">{{ $course['count'] }} students</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $percentage = $totalRegistrations > 0 ? round(($course['count'] / $totalRegistrations) * 100, 1) : 0;
                        @endphp
                        <div class="bg-primary rounded-full h-2" style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% of total</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Results -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Recent Results</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Student</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Exam</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Result</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($recentResults as $result)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $result->student->full_name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $result->exam_body }} - {{ $result->exam_type }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $result->result == 'Pass' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $result->result }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $result->grade ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
                    <h3 class="text-lg font-semibold text-gray-800">Export Exam Summary</h3>
                    <button onclick="closeModal('exportModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('admin.exam-registrations.export') }}" method="GET" id="exportForm">
                    <input type="hidden" name="report" value="summary">
                    <input type="hidden" name="year" value="{{ $selectedYear ?? now()->year }}">
                    <input type="hidden" name="exam_body" value="{{ request('exam_body') }}">
                    <input type="hidden" name="campus_id" value="{{ request('campus_id') }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <select name="format" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="pdf">PDF Report</option>
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Sections</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_charts" value="1" checked class="rounded border-gray-300 text-primary">
                                    <span class="ml-2 text-sm text-gray-600">Include Charts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_tables" value="1" checked class="rounded border-gray-300 text-primary">
                                    <span class="ml-2 text-sm text-gray-600">Include Detailed Tables</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('exportModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitExport()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Exam Body Chart
        const bodyCtx = document.getElementById('examBodyChart')?.getContext('2d');
        if (bodyCtx) {
            new Chart(bodyCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($examBodyLabels) !!},
                    datasets: [{
                        data: {!! json_encode($examBodyData) !!},
                        backgroundColor: ['#B91C1C', '#2563EB', '#059669', '#7C3AED'],
                        borderWidth: 0
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

        // Status Chart
        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'Registered', 'Completed', 'Failed', 'Deferred'],
                    datasets: [{
                        data: [
                            {{ $statusBreakdown['pending'] ?? 0 }},
                            {{ $statusBreakdown['registered'] ?? 0 }},
                            {{ $statusBreakdown['completed'] ?? 0 }},
                            {{ $statusBreakdown['failed'] ?? 0 }},
                            {{ $statusBreakdown['deferred'] ?? 0 }}
                        ],
                        backgroundColor: ['#F59E0B', '#10B981', '#8B5CF6', '#EF4444', '#6B7280'],
                        borderWidth: 0
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

        // Monthly Chart
        const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Registrations',
                        data: {!! json_encode($monthlyData) !!},
                        backgroundColor: '#2563EB',
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
                                stepSize: 1
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
        }
    });

    function exportReport() {
        openModal('exportModal');
    }

    function submitExport() {
        document.getElementById('exportForm').submit();
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('exportModal');
        }
    });
</script>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .hidden {
        display: none !important;
    }
</style>
@endsection
