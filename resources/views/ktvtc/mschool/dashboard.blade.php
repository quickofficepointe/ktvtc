@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Mschool Dashboard</h1>
            <p class="text-gray-600">Welcome to the Mobile School Management System</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="text-sm text-gray-500">
                <i class="fas fa-calendar-alt mr-2"></i>
                {{ now()->format('F d, Y') }}
            </span>
            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-school mr-2"></i>
                {{ auth()->user()->name ?? 'Admin' }}
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Courses -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 mr-4">
                    <i class="fas fa-book-open text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Courses</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalCourses ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.mcourses.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                    <span>View all courses</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 mr-4">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Students</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalStudents ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.students.index') }}" class="text-green-600 hover:text-green-800 text-sm flex items-center">
                    <span>View all students</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Active Enrollments -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 mr-4">
                    <i class="fas fa-graduation-cap text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Active Enrollments</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $activeEnrollments ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.enrollments.index') }}" class="text-purple-600 hover:text-purple-800 text-sm flex items-center">
                    <span>Manage enrollments</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 mr-4">
                    <i class="fas fa-pencil-alt text-orange-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Upcoming Exams</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $upcomingExams ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.exams.index') }}" class="text-orange-600 hover:text-orange-800 text-sm flex items-center">
                    <span>View exams</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Subjects -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-indigo-100 mr-4">
                    <i class="fas fa-book text-indigo-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Subjects</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalSubjects ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.subjects.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                    <span>View subjects</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Mobile Schools -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-teal-100 mr-4">
                    <i class="fas fa-school text-teal-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Mobile Schools</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalMobileSchools ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="" class="text-teal-600 hover:text-teal-800 text-sm flex items-center">
                    <span>View locations</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Course Categories -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-pink-100 mr-4">
                    <i class="fas fa-tags text-pink-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Course Categories</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalCategories ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.course-categories.index') }}" class="text-pink-600 hover:text-pink-800 text-sm flex items-center">
                    <span>Manage categories</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-100 mr-4">
                    <i class="fas fa-calendar-check text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Today's Attendance</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $todayAttendance ?? 0 }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('mschool.attendances.index') }}" class="text-yellow-600 hover:text-yellow-800 text-sm flex items-center">
                    <span>Take attendance</span>
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Enrollment Trends -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Enrollment Trends</h3>
                <select class="text-sm border rounded-lg px-3 py-1" id="enrollmentPeriod">
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>

        <!-- Course Distribution -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Course Distribution</h3>
                <span class="text-sm text-gray-500">By category</span>
            </div>
            <div class="h-64">
                <canvas id="courseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Enrollments -->
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Enrollments</h3>
                <a href="{{ route('mschool.enrollments.index') }}" class="text-blue-600 hover:underline text-sm">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentEnrollments ?? [] as $enrollment)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-user-graduate text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">{{ $enrollment->student->first_name ?? 'Student' }} {{ $enrollment->student->last_name ?? '' }}</p>
                                <p class="text-sm text-gray-500">{{ $enrollment->course->course_name ?? 'Course' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs">
                                {{ $enrollment->status ?? 'Active' }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">{{ $enrollment->created_at ? $enrollment->created_at->diffForHumans() : '' }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">No recent enrollments</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Upcoming Exams</h3>
                <a href="{{ route('mschool.exams.index') }}" class="text-orange-600 hover:underline text-sm">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($upcomingExamsList ?? [] as $exam)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                                <i class="fas fa-pencil-alt text-orange-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">{{ $exam->exam_name ?? 'Exam' }}</p>
                                <p class="text-sm text-gray-500">{{ $exam->course->course_name ?? '' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $exam->exam_date ? $exam->exam_date->format('M d, Y') : '' }}
                            </span>
                            <p class="text-xs text-gray-400">{{ $exam->start_time ?? '09:00' }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">No upcoming exams</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Footer -->
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-2">
                <i class="fas fa-user-plus text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium">Add Student</span>
        </a>

        <a href="" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                <i class="fas fa-book-medical text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium">Add Course</span>
        </a>

        <a href="{{ route('mschool.attendances.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-2">
                <i class="fas fa-qrcode text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium">Take Attendance</span>
        </a>

        <a href="" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-md hover:shadow-lg transition">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mb-2">
                <i class="fas fa-calendar-plus text-orange-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium">Schedule Exam</span>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enrollment Chart
        const enrollmentCtx = document.getElementById('enrollmentChart')?.getContext('2d');
        if (enrollmentCtx) {
            new Chart(enrollmentCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($enrollmentLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [{
                        label: 'Enrollments',
                        data: {!! json_encode($enrollmentData ?? [12, 19, 15, 17, 14, 13]) !!},
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Course Distribution Chart
        const courseCtx = document.getElementById('courseChart')?.getContext('2d');
        if (courseCtx) {
            new Chart(courseCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($courseCategoryLabels ?? ['ICT', 'Business', 'Engineering', 'Healthcare']) !!},
                    datasets: [{
                        data: {!! json_encode($courseCategoryData ?? [30, 25, 20, 15]) !!},
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(139, 92, 246)'
                        ]
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
    });
</script>
@endpush
@endsection
