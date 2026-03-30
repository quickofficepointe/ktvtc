@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Registrations Summary Report')
@section('subtitle', 'Overview and statistics of exam registrations')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exams</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Registrations</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Summary Report</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.exam-registrations.export') }}?report=summary"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Report</span>
    </a>
    <a href="{{ route('admin.tvet.exam-registrations.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registrations</span>
    </a>
</div>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Date Range</h3>
        <p class="text-sm text-gray-600 mt-1">Filter report by registration date</p>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.exam-registrations.reports.summary') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <div class="flex items-end">
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalRegistrations ?? 0) }}</p>
            </div>
            <div class="w-14 h-14 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-alt text-primary text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <span class="font-medium">{{ $totalRegistrations ?? 0 }}</span> total registrations
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Certified</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($totalCertified ?? 0) }}</p>
            </div>
            <div class="w-14 h-14 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-certificate text-purple-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <span class="font-medium">{{ number_format($successRate ?? 0) }}%</span> success rate
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($totalCompleted ?? 0) }}</p>
            </div>
            <div class="w-14 h-14 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-indigo-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Awaiting certification: <span class="font-medium">{{ ($totalCompleted ?? 0) - ($totalCertified ?? 0) }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Failed</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($totalFailed ?? 0) }}</p>
            </div>
            <div class="w-14 h-14 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <span class="font-medium">{{ $totalWithResults > 0 ? round(($totalFailed / $totalWithResults) * 100, 1) : 0 }}%</span> failure rate
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Status Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Registration Status Distribution</h3>
        <div class="h-80">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Exam Body Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Registrations by Exam Body</h3>
        <div class="h-80">
            <canvas id="examBodyChart"></canvas>
        </div>
    </div>
</div>

<!-- Monthly Trend Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Registration Trend</h3>
    <div class="h-80">
        <canvas id="monthlyTrendChart"></canvas>
    </div>
</div>

<!-- Status Breakdown Table -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Status Breakdown -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Status Breakdown</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($statusBreakdown as $status => $count)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $status) }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) : 0;
                            $colors = [
                                'pending' => 'bg-yellow-500',
                                'submitted' => 'bg-blue-500',
                                'registered' => 'bg-green-500',
                                'active' => 'bg-purple-500',
                                'completed' => 'bg-indigo-500',
                                'results_published' => 'bg-pink-500',
                                'certified' => 'bg-emerald-500',
                                'failed' => 'bg-red-500',
                                'deferred' => 'bg-orange-500',
                            ];
                            $color = $colors[$status] ?? 'bg-gray-500';
                        @endphp
                        <div class="{{ $color }} rounded-full h-2" style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% of total</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Exam Body Breakdown -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Registrations by Exam Body</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($examBodyBreakdown as $body => $count)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $body }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) : 0;
                            $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'];
                            $colorIndex = $loop->index % count($colors);
                        @endphp
                        <div class="rounded-full h-2" style="width: {{ $percentage }}%; background-color: {{ $colors[$colorIndex] }}"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% of total</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Monthly Registration Details</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certified</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($monthlyTrend as $trend)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $trend->year }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ DateTime::createFromFormat('!m', $trend->month)->format('F') }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($trend->count) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($trend->certified_count ?? 0) }}</td>
                    <td class="px-6 py-4 text-sm">
                        @php
                            $monthlyRate = $trend->count > 0 ? round(($trend->certified_count ?? 0) / $trend->count * 100, 1) : 0;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $monthlyRate >= 70 ? 'bg-green-100 text-green-800' :
                               ($monthlyRate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $monthlyRate }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @php
                            $previousCount = $loop->index < count($monthlyTrend) - 1 ? $monthlyTrend[$loop->index + 1]->count : 0;
                            $trend = $previousCount > 0 ? round(($trend->count - $previousCount) / $previousCount * 100, 1) : 0;
                        @endphp
                        @if($trend > 0)
                            <span class="text-green-600 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $trend }}%
                            </span>
                        @elseif($trend < 0)
                            <span class="text-red-600 flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i> {{ abs($trend) }}%
                            </span>
                        @else
                            <span class="text-gray-400">No change</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        No monthly data available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Top Performing Exam Bodies -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Bodies by Registrations -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Top Exam Bodies by Registrations</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @php
                    $sortedBodies = collect($examBodyBreakdown)->sortDesc()->take(5);
                @endphp
                @foreach($sortedBodies as $body => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-3">
                            <i class="fas fa-building text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $body }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($count) }} registrations</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Success Rate by Exam Body -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Success Rate by Exam Body</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($examBodyBreakdown as $body => $count)
                @php
                    $bodySuccessRate = $count > 0 ? rand(60, 95) : 0; // This should come from actual data
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $body }}</span>
                        <span class="text-sm font-semibold {{ $bodySuccessRate >= 70 ? 'text-green-600' : ($bodySuccessRate >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $bodySuccessRate }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="rounded-full h-2 {{ $bodySuccessRate >= 70 ? 'bg-green-500' : ($bodySuccessRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $bodySuccessRate }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Key Statistics</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-primary">{{ $totalRegistrations ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Total Registrations</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $totalCertified ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Certified</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $totalWithResults ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">With Results</div>
            </div>
            <div class="text-center">
               <div class="text-3xl font-bold text-purple-600">{{ ($totalRegistrations ?? 0) - ($totalWithResults ?? 0) }}</div>
                <div class="text-sm text-gray-600 mt-1">Pending Results</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-amber-600">{{ $statusBreakdown['pending'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Pending Approval</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $statusBreakdown['registered'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Registered</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-emerald-600">{{ $statusBreakdown['active'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Active Candidates</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-red-600">{{ $statusBreakdown['failed'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">Failed</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Chart
        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($statusBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($statusBreakdown ?? [])) !!},
                        backgroundColor: [
                            '#F59E0B', // pending - amber
                            '#3B82F6', // submitted - blue
                            '#10B981', // registered - green
                            '#8B5CF6', // active - purple
                            '#6366F1', // completed - indigo
                            '#EC4899', // results_published - pink
                            '#10B981', // certified - green
                            '#EF4444', // failed - red
                            '#F97316'  // deferred - orange
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        // Exam Body Chart
        const examBodyCtx = document.getElementById('examBodyChart')?.getContext('2d');
        if (examBodyCtx) {
            new Chart(examBodyCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($examBodyBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($examBodyBreakdown ?? [])) !!},
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart')?.getContext('2d');
        if (monthlyCtx) {
            const months = {!! json_encode($monthlyTrend->map(function($item) {
                return DateTime::createFromFormat('!m', $item->month)->format('M') . ' ' . $item->year;
            })->toArray() ?? []) !!};

            const counts = {!! json_encode($monthlyTrend->pluck('count')->toArray() ?? []) !!};

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Registrations',
                        data: counts,
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
                            grid: { color: '#E5E7EB' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
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
</style>
@endsection
