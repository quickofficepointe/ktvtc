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
    public function generateForAllCourses($yearsAhead = 2)
    {
        $courses = Course::active()->get();
        $results = [
            'total_courses' => $courses->count(),
            'intakes_created' => 0,
            'intakes_updated' => 0
        ];

        foreach ($courses as $course) {
            $result = $this->generateForCourse($course, $yearsAhead);
            $results['intakes_created'] += $result['created'];
            $results['intakes_updated'] += $result['updated'];
        }

        return $results;
    }

    /**
     * Generate monthly intakes for a specific course
     */
    public function generateForCourse(Course $course, $yearsAhead = 2)
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $created = 0;
        $updated = 0;

        for ($year = $currentYear; $year <= $currentYear + $yearsAhead; $year++) {
            $startMonth = ($year == $currentYear) ? $currentMonth : 1;

            for ($month = $startMonth; $month <= 12; $month++) {
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

                    $course->intakes()->create([
                        'month' => $monthName,
                        'year' => $year,
                        'application_deadline' => $applicationDeadline,
                        'notes' => 'Monthly intake for ' . $course->name,
                        'is_active' => true,
                        'created_by' => 1, // System user
                    ]);

                    $created++;
                } else {
                    // Update existing intake if needed
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
     */
    public function getAllUpcomingIntakes($limit = 50)
    {
        return CourseIntakes::with('course.department')
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('application_deadline')
                      ->orWhere('application_deadline', '>=', Carbon::now());
            })
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->limit($limit)
            ->get();
    }
}
