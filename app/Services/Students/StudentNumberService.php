<?php

namespace App\Services\Students;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * Student numbers: COURSECODE/NNN/YEAR, where NNN is one running number for
 * the whole college (number_sequences 'student'). Each course enrollment has its
 * own number; a student's first number stays their login.
 */
class StudentNumberService
{
    /** Next unused running number. Must run inside a transaction (row lock). */
    public function nextRunning(): int
    {
        return DB::transaction(function () {
            $row = DB::table('number_sequences')->where('name', 'student')->lockForUpdate()->first();
            if (!$row) {
                throw new \RuntimeException('Student number sequence missing; run the 2026_10_28_150000 migration.');
            }

            $n = (int) $row->next_value;
            while ($this->runningNumberUsed($n)) {
                $n++;
            }
            DB::table('number_sequences')->where('name', 'student')->update(['next_value' => $n + 1, 'updated_at' => now()]);

            return $n;
        });
    }

    public function courseCode(?Course $course): string
    {
        $code = strtoupper(trim((string) $course?->code, " /\t"));
        return $code !== '' ? $code : 'STU';
    }

    public function format(string $code, int $running, int $year): string
    {
        return "{$code}/{$running}/{$year}";
    }

    /**
     * Number for a new enrollment.
     * First course: reuses the running number the student was given at registration
     * (a bare "987") and becomes their student number too. Later courses: a new number.
     *
     * @return array{0: string, 1: bool} [number, true if it should also become the student's number]
     */
    public function forNewEnrollment(Student $student, Course $course, int $year): array
    {
        $isFirst = !Enrollment::where('student_id', $student->id)->exists();
        $current = trim((string) $student->student_number);

        if ($isFirst && ctype_digit($current)) {
            $number = $this->format($this->courseCode($course), (int) $current, $year);
            if (!$this->numberTaken($number, $student->id)) {
                return [$number, true];
            }
        }

        $number = $this->format($this->courseCode($course), $this->nextRunning(), $year);
        return [$number, $isFirst && !str_contains($current, '/')];
    }

    public function numberTaken(string $number, ?int $exceptStudentId = null): bool
    {
        return Student::withTrashed()->where('student_number', $number)->when($exceptStudentId, fn ($q) => $q->where('id', '!=', $exceptStudentId))->exists()
            || Enrollment::withTrashed()->where('student_number', $number)->exists();
    }

    /** Is running number $n already part of any student/enrollment/legacy number ("987" or "X/987/2026")? */
    private function runningNumberUsed(int $n): bool
    {
        $match = fn ($q, $col) => $q->where($col, (string) $n)->orWhere($col, 'like', "%/{$n}/%");

        return Student::withTrashed()->where(fn ($q) => $match($q, 'student_number'))->exists()
            || Student::withTrashed()->where(fn ($q) => $match($q, 'legacy_student_code'))->exists()
            || Enrollment::withTrashed()->where(fn ($q) => $match($q, 'student_number'))->exists();
    }
}
