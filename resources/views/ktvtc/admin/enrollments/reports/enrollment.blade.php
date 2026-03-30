@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Enrollment Statistics Report')
@section('subtitle', 'Track enrollment trends and statistics')

@section('breadcrumb')
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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Enrollment Statistics</span>
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
    <a href="{{ route('admin.enrollments.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Enrollments</span>
    </a>
</div>
@endsection

@section('content')
<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Report Filters</h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.enrollments.reports.enrollment') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                    <select name="group_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="month" {{ request('group_by', 'month') == 'month' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarter" {{ request('group_by') == 'quarter' ? 'selected' : '' }}>Quarterly</option>
                        <option value="year" {{ request('group_by') == 'year' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Total Enrollments</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalEnrollments) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Average per Month</p>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($averagePerMonth) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Peak Month</p>
        <p class="text-2xl font-bold text-green-600">{{ $peakMonth ?? 'N/A' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Growth Rate</p>
        <p class="text-2xl font-bold {{ $growthRate >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $growthRate }}%
        </p>
    </div>
</div>

<!-- Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Trend</h3>
    <div class="h-80">
        <canvas id="enrollmentChart"></canvas>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Enrollment Breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Enrollments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% of Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($enrollmentData as $data)
                <tr>
                    <td class="px-6 py-4">{{ $data->period }}</td>
                    <td class="px-6 py-4 font-medium">{{ number_format($data->count) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <span class="mr-3">{{ round(($data->count / $totalEnrollments) * 100, 1) }}%</span>
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: {{ ($data->count / $totalEnrollments) * 100 }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($enrollmentData->pluck('period')) !!},
            datasets: [{
                label: 'Enrollments',
                data: {!! json_encode($enrollmentData->pluck('count')) !!},
                borderColor: '#B91C1C',
                backgroundColor: 'rgba(185, 28, 28, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    function exportReport() {
        window.location.href = '{{ route("admin.enrollments.export") }}?report=enrollment&' + new URLSearchParams(new FormData(document.querySelector('form'))).toString();
    }
</script>
@endsection
