<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseIntakes;
use App\Services\MonthlyIntakeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseIntakesController extends Controller
{
    protected $intakeService;

    public function __construct(MonthlyIntakeService $intakeService)
    {
        $this->intakeService = $intakeService;
    }

    /**
     * Display course intakes for public website
     */
    public function publicIndex()
    {
        // Generate monthly intakes for all courses first
        $this->intakeService->generateForAllCourses();

        // Get all active courses with their monthly intakes
        $courses = Course::with(['department'])
            ->where('is_active', true)
            ->orderBy('featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Manually load and sort intakes
        foreach ($courses as $course) {
            $course->setRelation('intakes', $course->getMonthlyIntakes());
        }

        return view('ktvtc.website.courseintake.courseintake', compact('courses'));
    }

    /**
     * Show intakes for a specific course
     */
    public function showCourseIntakes($slug)
    {
        $course = Course::where('slug', $slug)
            ->where('is_active', true)
            ->with('department')
            ->firstOrFail();

        // Generate monthly intakes for this course
        $this->intakeService->generateForCourse($course);

        $intakes = $course->getMonthlyIntakes();
        $upcomingIntakes = $course->getUpcomingMonthlyIntakes();

        return view('ktvtc.website.courses.intakes', compact('course', 'intakes', 'upcomingIntakes'));
    }

    /**
     * Get next available intake for application
     */
    public function getNextIntake($courseId)
    {
        $course = Course::findOrFail($courseId);
        $nextIntake = $course->next_intake;

        if (!$nextIntake) {
            return response()->json([
                'success' => false,
                'message' => 'No upcoming intakes available'
            ]);
        }

        return response()->json([
            'success' => true,
            'intake' => [
                'id' => $nextIntake->id,
                'month' => $nextIntake->month,
                'year' => $nextIntake->year,
                'deadline' => $nextIntake->formatted_deadline,
                'days_remaining' => $nextIntake->days_until_deadline
            ]
        ]);
    }
}
