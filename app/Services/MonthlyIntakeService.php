<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseIntakes;
use Carbon\Carbon;

class MonthlyIntakeService
{
    /**
     * Generate monthly intakes for all active courses
     */
    public function generateForAllCourses($startYear = 2026, $endYear = 2027)
    {
        $courses = Course::active()->get();
        $results = [
            'total_courses' => $courses->count(),
            'intakes_created' => 0,
            'intakes_updated' => 0
        ];

        foreach ($courses as $course) {
            $result = $this->generateForCourse($course, $startYear, $endYear);
            $results['intakes_created'] += $result['created'];
            $results['intakes_updated'] += $result['updated'];
        }

        return $results;
    }

    /**
     * Generate monthly intakes for a specific course
     * Now with fixed start and end years
     */
    public function generateForCourse(Course $course, $startYear = 2026, $endYear = 2027)
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        $created = 0;
        $updated = 0;

        // Loop through all years from startYear to endYear
        for ($year = $startYear; $year <= $endYear; $year++) {
            // Loop through all 12 months for each year
            for ($month = 1; $month <= 12; $month++) {
                $monthName = $months[$month - 1];

                $existingIntake = $course->intakes()
                    ->where('month', $monthName)
                    ->where('year', $year)
                    ->first();

                if (!$existingIntake) {
                    // Calculate deadline (15th of previous month)
                    $deadlineMonth = $month - 1;
                    $deadlineYear = $year;

                    if ($deadlineMonth < 1) {
                        $deadlineMonth = 12;
                        $deadlineYear = $year - 1;
                    }

                    $applicationDeadline = Carbon::create($deadlineYear, $deadlineMonth, 15);

                    // Create intake with custom notes
                    $course->intakes()->create([
                        'month' => $monthName,
                        'year' => $year,
                        'application_deadline' => $applicationDeadline,
                        'notes' => 'Monthly intake for ' . $course->name . ' - ' . $monthName . ' ' . $year,
                        'is_active' => true,
                        'created_by' => 1, // System user
                    ]);

                    $created++;
                } else {
                    // Update existing intake if deadline is wrong
                    $deadlineMonth = $month - 1;
                    $deadlineYear = $year;

                    if ($deadlineMonth < 1) {
                        $deadlineMonth = 12;
                        $deadlineYear = $year - 1;
                    }

                    $newDeadline = Carbon::create($deadlineYear, $deadlineMonth, 15);

                    if (!$existingIntake->application_deadline ||
                        $existingIntake->application_deadline->format('Y-m-d') != $newDeadline->format('Y-m-d')) {

                        $existingIntake->update([
                            'application_deadline' => $newDeadline,
                            'is_active' => true,
                            'updated_by' => 1
                        ]);

                        $updated++;
                    }
                }
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * Generate for specific year range
     */
    public function generateForYearRange($startYear, $endYear)
    {
        return $this->generateForAllCourses($startYear, $endYear);
    }

    /**
     * Generate for 2026 only
     */
    public function generateFor2026()
    {
        return $this->generateForAllCourses(2026, 2026);
    }

    /**
     * Generate for 2027 only
     */
    public function generateFor2027()
    {
        return $this->generateForAllCourses(2027, 2027);
    }

    /**
     * Generate for 2026-2028 (if needed)
     */
    public function generateFor2026To2028()
    {
        return $this->generateForAllCourses(2026, 2028);
    }

    /**
     * Clean up past intakes (older than 1 year)
     */
    public function cleanupPastIntakes()
    {
        $oneYearAgo = Carbon::now()->subYear();

        $deleted = CourseIntakes::where('application_deadline', '<', $oneYearAgo)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return $deleted;
    }

    /**
     * Get all upcoming intakes across all courses
     * Modified to show from 2026 onwards
     */
    public function getAllUpcomingIntakes($limit = 100)
    {
        return CourseIntakes::with('course.department')
            ->where('is_active', true)
            ->where('year', '>=', 2026) // Only show from 2026 onwards
            ->where(function($query) {
                $query->whereNull('application_deadline')
                      ->orWhere('application_deadline', '>=', Carbon::now());
            })
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->limit($limit)
            ->get();
    }

    /**
     * Get intakes grouped by year
     */
    public function getIntakesGroupedByYear()
    {
        $intakes = CourseIntakes::with('course.department')
            ->where('is_active', true)
            ->where('year', '>=', 2026)
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();

        return $intakes->groupBy('year');
    }

    /**
     * Check if intakes exist for a year range
     */
    public function intakesExistForYearRange($startYear, $endYear)
    {
        $count = CourseIntakes::whereBetween('year', [$startYear, $endYear])
            ->count();

        return $count > 0;
    }

    /**
     * Get intake statistics
     */
    public function getIntakeStats()
    {
        $stats = [
            '2026_total' => CourseIntakes::where('year', 2026)->count(),
            '2026_active' => CourseIntakes::where('year', 2026)->where('is_active', true)->count(),
            '2027_total' => CourseIntakes::where('year', 2027)->count(),
            '2027_active' => CourseIntakes::where('year', 2027)->where('is_active', true)->count(),
            'by_course' => CourseIntakes::whereIn('year', [2026, 2027])
                ->selectRaw('course_id, year, count(*) as total')
                ->groupBy('course_id', 'year')
                ->get()
        ];

        return $stats;
    }
}
