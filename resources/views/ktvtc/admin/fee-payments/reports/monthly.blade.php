@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Monthly Collection Report')
@section('subtitle', 'View monthly payment collections')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Monthly Collection</span>
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
<!-- Month Picker -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Select Month</h3>
    </div>
    <div class="p-6">
        <form method="GET" action="{{ route('admin.fee-payments.reports.monthly') }}" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <select name="month" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $index => $month)
                        <option value="{{ $index + 1 }}" {{ ($selectedMonth ?? now()->month) == ($index + 1) ? 'selected' : '' }}>
                            {{ $month }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select name="year" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @for($year = now()->year - 2; $year <= now()->year + 1; $year++)
                        <option value="{{ $year }}" {{ ($selectedYear ?? now()->year) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
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
        <p class="text-xs text-gray-500 mt-1">{{ $monthName }} {{ $selectedYear }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Number of Transactions</p>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($transactionCount) }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Average per Day</p>
        <p class="text-2xl font-bold text-purple-600">KES {{ number_format($averagePerDay, 2) }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Best Day</p>
        <p class="text-2xl font-bold text-green-600">KES {{ number_format($bestDayAmount, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $bestDayDate }}</p>
    </div>
</div>

<!-- Daily Collection Chart -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daily Collection - {{ $monthName }} {{ $selectedYear }}</h3>
    </div>
    <div class="p-6">
        <div id="dailyChart" style="height: 400px;"></div>
    </div>
</div>

<!-- Monthly Comparison (Last 12 Months) -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Monthly Comparison (Last 12 Months)</h3>
    </div>
    <div class="p-6">
        <div id="monthlyComparisonChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Payment Method Summary -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @foreach($methodSummary as $method => $data)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ ucfirst($method) }}</span>
                    <span class="text-sm text-gray-600">KES {{ number_format($data['total'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary rounded-full h-2" style="width: {{ $data['percentage'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $data['count'] }} transactions</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Courses</h3>
        <div class="space-y-4">
            @foreach($topCourses as $course)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $course['name'] }}</span>
                    <span class="text-sm text-gray-600">KES {{ number_format($course['total'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 rounded-full h-2" style="width: {{ $course['percentage'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $course['count'] }} payments</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Daily Breakdown Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daily Breakdown</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transactions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cash</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">M-Pesa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($dailyBreakdown as $day)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $day['date']->format('M j, Y') }}</td>
                    <td class="px-6 py-4">{{ $day['date']->format('l') }}</td>
                    <td class="px-6 py-4">{{ number_format($day['count']) }}</td>
                    <td class="px-6 py-4 font-medium text-green-600">KES {{ number_format($day['total'], 2) }}</td>
                    <td class="px-6 py-4">KES {{ number_format($day['cash'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4">KES {{ number_format($day['mpesa'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4">KES {{ number_format($day['bank'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="2" class="px-6 py-3 text-right font-medium">Total:</td>
                    <td class="px-6 py-3 font-medium">{{ number_format($transactionCount) }}</td>
                    <td class="px-6 py-3 font-bold text-green-600">KES {{ number_format($totalCollected, 2) }}</td>
                    <td class="px-6 py-3">KES {{ number_format($methodSummary['cash']['total'] ?? 0, 2) }}</td>
                    <td class="px-6 py-3">KES {{ number_format($methodSummary['mpesa']['total'] ?? 0, 2) }}</td>
                    <td class="px-6 py-3">KES {{ number_format($methodSummary['bank']['total'] ?? 0, 2) }}</td>
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
        // Daily Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyLabels) !!},
                datasets: [{
                    label: 'Daily Collection (KES)',
                    data: {!! json_encode($dailyData) !!},
                    borderColor: '#B91C1C',
                    backgroundColor: 'rgba(185, 28, 28, 0.1)',
                    tension: 0.4,
                    fill: true
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
                }
            }
        });

        // Monthly Comparison Chart
        const monthlyCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Monthly Collection (KES)',
                    data: {!! json_encode($monthlyData) !!},
                    backgroundColor: '#3B82F6',
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
                }
            }
        });
    });

    function exportReport() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("admin.fee-payments.export") }}?report=monthly&month=' + params.get('month') +
            '&year=' + params.get('year') + (params.get('campus_id') ? '&campus_id=' + params.get('campus_id') : '');
    }
</script>
@endsection
