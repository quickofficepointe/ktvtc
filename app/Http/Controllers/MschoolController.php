<?php

namespace App\Http\Controllers;

use App\Models\MCourse;
use App\Models\MStudent;
use App\Models\MEnrollment;
use App\Models\MExam;
use App\Models\MSubject;
use App\Models\MobileSchool;
use App\Models\MCourseCategories;
use App\Models\MAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MschoolController extends Controller
{
    public function dashboard()
    {
        // Main statistics
        $totalCourses = MCourse::count();
        $totalStudents = MStudent::count();
        $activeEnrollments = MEnrollment::where('status', 'active')->count();
        $upcomingExams = MExam::where('exam_date', '>=', now())->count();

        // Additional statistics
        $totalSubjects = MSubject::count();
        $totalMobileSchools = MobileSchool::count();
        $totalCategories = MCourseCategories::count();
        $todayAttendance = MAttendance::whereDate('attendance_date', today())->count();

        // Recent data
        $recentEnrollments = MEnrollment::with(['student', 'course'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingExamsList = MExam::with(['course'])
            ->where('exam_date', '>=', now())
            ->orderBy('exam_date')
            ->take(5)
            ->get();

        // Chart data - Enrollment trends (last 6 months)
        $enrollmentLabels = [];
        $enrollmentData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $enrollmentLabels[] = $month->format('M');

            $count = MEnrollment::whereYear('enrollment_date', $month->year)
                ->whereMonth('enrollment_date', $month->month)
                ->count();

            $enrollmentData[] = $count;
        }

        // Chart data - Course distribution by category
        $courseCategoryData = MCourseCategories::withCount('courses')
            ->having('courses_count', '>', 0)
            ->get();

        $courseCategoryLabels = $courseCategoryData->pluck('category_name')->toArray();
        $courseCategoryCounts = $courseCategoryData->pluck('courses_count')->toArray();

        return view('ktvtc.mschool.dashboard', compact(
            'totalCourses',
            'totalStudents',
            'activeEnrollments',
            'upcomingExams',
            'totalSubjects',
            'totalMobileSchools',
            'totalCategories',
            'todayAttendance',
            'recentEnrollments',
            'upcomingExamsList',
            'enrollmentLabels',
            'enrollmentData',
            'courseCategoryLabels',
            'courseCategoryCounts'
        ));
    }
}
