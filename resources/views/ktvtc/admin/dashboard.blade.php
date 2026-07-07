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
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-user-check text-success mr-1"></i>
            <span>{{ number_format($activeStudents ?? 0) }} active</span>
        </div>
    </div>

    <!-- Total Enrollments Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Enrollments</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-book-open text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-play-circle text-green-600 mr-1"></i>
            <span>{{ number_format($activeEnrollments ?? 0) }} in progress</span>
        </div>
    </div>

    <!-- Students Without Enrollments -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Students Without Enrollments</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($studentsWithoutEnrollments ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center">
                <i class="fas fa-user-slash text-orange-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-info-circle text-blue-600 mr-1"></i>
            <span>Need enrollment</span>
        </div>
    </div>

    <!-- Course Applications -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Applications</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($pendingApplications ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-file-alt text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-clock text-amber-600 mr-1"></i>
            <span>Awaiting review</span>
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

    <!-- Student Growth Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Growth (Last 6 Months)</h3>
        <div class="h-64">
            <canvas id="studentGrowthChart"></canvas>
        </div>
    </div>
</div>

<!-- ============ STATUS CARDS ROW ============ -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Student Status Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-graduate text-primary mr-2"></i>
            Student Status
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Active</span>
                <span class="text-sm font-semibold text-green-600">{{ number_format($activeStudents ?? 0) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $activePercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Graduated</span>
                <span class="text-sm font-semibold text-purple-600">{{ number_format($graduatedStudents ?? 0) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $graduatedPercentage ?? 0 }}%"></div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <span class="text-sm text-gray-600">Dropped/Suspended</span>
                <span class="text-sm font-semibold text-red-600">{{ number_format($inactiveStudents ?? 0) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $inactivePercentage ?? 0 }}%"></div>
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
            <a href="{{ route('admin.students.create') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center mr-3">
                    <i class="fas fa-user-plus text-primary text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Add New Student</p>
                    <p class="text-xs text-gray-500">Create a new student record</p>
                </div>
            </a>

            <a href="{{ route('admin.enrollments.create') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3">
                    <i class="fas fa-book-open text-blue-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">New Enrollment</p>
                    <p class="text-xs text-gray-500">Enroll student in a course</p>
                </div>
            </a>

            <a href="{{ route('admin.applications.index') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center mr-3">
                    <i class="fas fa-file-alt text-purple-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Review Applications</p>
                    <p class="text-xs text-gray-500">Manage course applications</p>
                </div>
            </a>

            <a href="{{ route('admin.exam-registrations.index') }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center mr-3">
                    <i class="fas fa-pencil-alt text-amber-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Exam Registrations</p>
                    <p class="text-xs text-gray-500">View exam registrations</p>
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
            <a href="{{ route('admin.enrollments.index') }}" class="text-sm text-primary hover:underline">View all</a>
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
                                        {{ substr($enrollment->student->full_name ?? $enrollment->student_name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $enrollment->student->full_name ?? $enrollment->student_name ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->student->student_number ?? $enrollment->student_number ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $enrollment->course->name ?? $enrollment->course_name ?? 'N/A' }}</td>
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

    <!-- Recent Applications -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Recent Applications</h3>
            <a href="{{ route('admin.applications.index') }}" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentApplications ?? [] as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-2">
                                    <span class="text-xs font-medium text-purple-600">
                                        {{ substr($application->first_name ?? $application->name ?? 'A', 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900">
                                    {{ $application->first_name ?? $application->name ?? 'N/A' }}
                                    {{ $application->last_name ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $application->course->name ?? $application->course_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ isset($application->created_at) ? $application->created_at->format('d/m/Y') : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($application->status == 'approved') bg-green-100 text-green-800
                                @elseif($application->status == 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($application->status ?? 'Pending') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No recent applications</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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

        // Student Growth Chart
        const growthCtx = document.getElementById('studentGrowthChart')?.getContext('2d');
        if (growthCtx) {
            new Chart(growthCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [{
                        label: 'Student Growth',
                        data: {!! json_encode($studentGrowthData ?? [0, 0, 0, 0, 0, 0]) !!},
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
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
