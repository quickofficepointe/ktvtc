@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Admin Dashboard')
@section('subtitle', 'Overview of your TVET institution')

@section('content')
<!-- ============ STATISTICS CARDS ============ -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Students Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Students</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalStudents ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-user-check text-success mr-1"></i>
            <span>{{ $activeStudents ?? 0 }} active</span>
        </div>
    </div>

    <!-- Total Enrollments Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Enrollments</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalEnrollments ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-book-open text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-play-circle text-green-600 mr-1"></i>
            <span>{{ $activeEnrollments ?? 0 }} in progress</span>
        </div>
    </div>

    <!-- Fee Collection Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Collected</p>
                <p class="text-3xl font-bold text-green-600 mt-2">KES {{ number_format($totalCollected ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-coins text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-clock text-amber-600 mr-1"></i>
            <span>KES {{ number_format($outstandingBalance ?? 0) }} outstanding</span>
        </div>
    </div>

    <!-- Today's Collections Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover cursor-pointer" onclick="openTodayPaymentsModal()">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Collection</p>
                <p class="text-3xl font-bold text-primary mt-2">KES {{ number_format($todayCollection ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-calendar-day text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-arrow-up text-success mr-1"></i>
            <span>{{ $todayPayments ?? 0 }} payments today</span>
            <span class="ml-auto text-xs">Click to view</span>
        </div>
    </div>
</div>

<!-- ============ CHARTS ROW ============ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Enrollment Trend Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment Trend (Last 6 Months)</h3>
        <div class="h-64">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

    <!-- Payment Collection Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Collection (Last 6 Months)</h3>
        <div class="h-64">
            <canvas id="paymentChart"></canvas>
        </div>
    </div>
</div>

<!-- ============ STATUS CARDS ROW ============ -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Student Status Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-graduate text-primary mr-2"></i>
            Student Status
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Active</span>
                <span class="text-sm font-semibold text-green-600">{{ $activeStudents ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $activePercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Graduated</span>
                <span class="text-sm font-semibold text-purple-600">{{ $graduatedStudents ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $graduatedPercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Dropped/Suspended</span>
                <span class="text-sm font-semibold text-red-600">{{ $inactiveStudents ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $inactivePercentage ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Enrollment Status Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-line text-primary mr-2"></i>
            Enrollment Status
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">In Progress</span>
                <span class="text-sm font-semibold text-green-600">{{ $inProgressEnrollments ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $inProgressPercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Completed</span>
                <span class="text-sm font-semibold text-blue-600">{{ $completedEnrollments ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $completedPercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Pending Payment</span>
                <span class="text-sm font-semibold text-amber-600">{{ $pendingPaymentEnrollments ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $pendingPaymentPercentage ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-bolt text-primary mr-2"></i>
            Quick Actions
        </h3>
        <div class="space-y-3">
            <a href="" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-3">
                    <i class="fas fa-user-plus text-primary text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Add New Student</p>
                    <p class="text-xs text-gray-500">Create a new student record</p>
                </div>
            </a>

            <a href="" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3">
                    <i class="fas fa-book-open text-blue-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">New Enrollment</p>
                    <p class="text-xs text-gray-500">Enroll student in a course</p>
                </div>
            </a>

            <a href="" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center mr-3">
                    <i class="fas fa-credit-card text-green-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Record Payment</p>
                    <p class="text-xs text-gray-500">Record a fee payment</p>
                </div>
            </a>

            <a href="" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center mr-3">
                    <i class="fas fa-file-alt text-purple-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Exam Registration</p>
                    <p class="text-xs text-gray-500">Register student for exam</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- ============ RECENT ACTIVITY TABLES ============ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Enrollments -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Recent Enrollments</h3>
            <a href="" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Intake</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentEnrollments ?? [] as $enrollment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-2">
                                    <span class="text-xs font-medium text-primary">
                                        {{ substr($enrollment->student_name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $enrollment->student_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->student_number ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $enrollment->course_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->intake_month ?? '' }} {{ $enrollment->intake_year ?? '' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($enrollment->status == 'active') bg-green-100 text-green-800
                                @elseif($enrollment->status == 'completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($enrollment->status ?? 'N/A') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No recent enrollments</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
            <a href="" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPayments ?? [] as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono font-medium text-gray-900">{{ $payment->receipt_number ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-2">
                                    <span class="text-xs font-medium text-green-600">
                                        {{ substr($payment->student_name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900">{{ $payment->student_name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-green-600">KES {{ number_format($payment->amount ?? 0) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ isset($payment->payment_date) ? $payment->payment_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No recent payments</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ TODAY'S PAYMENTS MODAL ============ -->
<div id="todayPaymentsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="closeTodayPaymentsModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Today's Payments</h3>
                    <button onclick="closeTodayPaymentsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($todayPaymentsList ?? [] as $payment)
                            <tr>
                                <td class="px-6 py-4 text-sm font-mono">{{ $payment->receipt_number ?? '' }}</td>
                                <td class="px-6 py-4">{{ $payment->student_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 font-medium text-green-600">KES {{ number_format($payment->amount ?? 0) }}</td>
                                <td class="px-6 py-4">{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                <td class="px-6 py-4">{{ isset($payment->created_at) ? $payment->created_at->format('h:i A') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No payments today</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-right font-bold">Total:</td>
                                <td class="px-6 py-4 font-bold text-primary">KES {{ number_format($todayCollection ?? 0) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeTodayPaymentsModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ MODAL FUNCTIONS ============
    function openTodayPaymentsModal() {
        document.getElementById('todayPaymentsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeTodayPaymentsModal() {
        document.getElementById('todayPaymentsModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ============ CHARTS ============
    document.addEventListener('DOMContentLoaded', function() {
        // Enrollment Chart
        const enrollmentCtx = document.getElementById('enrollmentChart')?.getContext('2d');
        if (enrollmentCtx) {
            new Chart(enrollmentCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [{
                        label: 'New Enrollments',
                        data: {!! json_encode($enrollmentChartData ?? [0, 0, 0, 0, 0, 0]) !!},
                        borderColor: '#B91C1C',
                        backgroundColor: 'rgba(185, 28, 28, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Payment Chart
        const paymentCtx = document.getElementById('paymentChart')?.getContext('2d');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [{
                        label: 'Collections (KES)',
                        data: {!! json_encode($paymentChartData ?? [0, 0, 0, 0, 0, 0]) !!},
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTodayPaymentsModal();
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
