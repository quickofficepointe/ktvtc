<?php

namespace App\Services\Terms;

use App\Models\Course;
use App\Models\CourseUnit;
use App\Models\TermRegistration;

/**
 * Changing how a course is split into terms, shared by Finance (term fees) and
 * the academic admin (units). Refuses changes that would move registered
 * students' term dates or orphan units.
 */
class CourseTermStructure
{
    /**
     * @param array{term_count: int, term_length: int, term_length_unit: string} $data
     * @throws \DomainException
     */
    public function update(Course $course, array $data): void
    {
        $registered = TermRegistration::where('course_id', $course->id)->active();

        if ((clone $registered)->exists()
            && ((int) $data['term_length'] !== (int) $course->term_length || $data['term_length_unit'] !== $course->term_length_unit)) {
            throw new \DomainException('Students are already registered for terms of this course, so the term length can no longer change (their term dates would move). You can still change the number of terms.');
        }

        $maxRegistered = (int) (clone $registered)->max('term_number');
        if ($data['term_count'] < $maxRegistered) {
            throw new \DomainException("A student is already registered for Term {$maxRegistered}; the course can't have fewer terms than that.");
        }

        $maxUnitTerm = (int) CourseUnit::where('course_id', $course->id)->max('term_number');
        if ($data['term_count'] < $maxUnitTerm) {
            throw new \DomainException("Units are set up for Term {$maxUnitTerm}; move or remove them before reducing the number of terms.");
        }

        $course->update([
            'term_count'       => (int) $data['term_count'],
            'term_length'      => (int) $data['term_length'],
            'term_length_unit' => $data['term_length_unit'],
        ]);
    }
}
