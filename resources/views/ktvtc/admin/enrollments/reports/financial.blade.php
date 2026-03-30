@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Financial Report')
@section('subtitle', 'Track fee collection and outstanding balances')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Financial Report</span>
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
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Total Fees</p>
        <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalFees) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Collected</p>
        <p class="text-2xl font-bold text-green-600">KES {{ number_format($collected) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Outstanding</p>
        <p class="text-2xl font-bold text-red-600">KES {{ number_format($outstanding) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-600">Collection Rate</p>
        <p class="text-2xl font-bold text-blue-600">{{ $collectionRate }}%</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Collection</h3>
        <div class="h-64">
            <canvas id="collectionChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Outstanding by Course</h3>
        <div class="h-64">
            <canvas id="outstandingChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Debtors -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Top Outstanding Balances</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($topDebtors as $debtor)
                <tr>
                    <td class="px-6 py-4">{{ $debtor->student_name }}</td>
                    <td class="px-6 py-4">{{ $debtor->course_name }}</td>
                    <td class="px-6 py-4">KES {{ number_format($debtor->total_fees) }}</td>
                    <td class="px-6 py-4">KES {{ number_format($debtor->amount_paid) }}</td>
                    <td class="px-6 py-4 font-bold text-red-600">KES {{ number_format($debtor->balance) }}</td>
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
    // Collection Chart
    new Chart(document.getElementById('collectionChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Amount Collected (KES)',
                data: {!! json_encode($monthlyData) !!},
                backgroundColor: '#10B981'
            }]
        }
    });

    // Outstanding Chart
    new Chart(document.getElementById('outstandingChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($courseLabels) !!},
            datasets: [{
                data: {!! json_encode($courseData) !!},
                backgroundColor: ['#B91C1C', '#3B82F6', '#F59E0B', '#10B981', '#8B5CF6']
            }]
        }
    });

    function exportReport() {
        window.location.href = '{{ route("admin.enrollments.export") }}?report=financial';
    }
</script>
@endsection
