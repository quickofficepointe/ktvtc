<?php

namespace App\Services\Terms;

use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;

/**
 * Term dates counted from each student's own intake.
 * A course split into 3 terms of 3 months: a 10 March intake has
 * Term 1 = 10 Mar–9 Jun, Term 2 = 10 Jun–9 Sep, Term 3 = 10 Sep–9 Dec.
 */
class TermCalendar
{
    public function intakeDate(Enrollment $enrollment): Carbon
    {
        return Carbon::parse($enrollment->start_date ?? $enrollment->enrollment_date ?? $enrollment->created_at)->startOfDay();
    }

    /** @return array{0: Carbon, 1: Carbon} [first day, last day] of term $n */
    public function termDates(Course $course, Carbon $intake, int $n): array
    {
        $start = $this->add($intake, $course, ($n - 1) * $course->term_length);
        $end   = $this->add($intake, $course, $n * $course->term_length)->subDay();
        return [$start, $end];
    }

    /**
     * Which term the student is in on $date. Returns term_count + 1 once the course is over.
     */
    public function termNumberOn(Course $course, Carbon $intake, ?Carbon $date = null): int
    {
        $date ??= today();
        for ($n = 1; $n <= $course->term_count; $n++) {
            [, $end] = $this->termDates($course, $intake, $n);
            if ($date->lte($end)) {
                return $n;
            }
        }
        return $course->term_count + 1;
    }

    private function add(Carbon $from, Course $course, int $amount): Carbon
    {
        return $course->term_length_unit === 'weeks'
            ? $from->copy()->addWeeks($amount)
            : $from->copy()->addMonthsNoOverflow($amount);
    }
}
