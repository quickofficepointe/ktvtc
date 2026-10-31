<?php

namespace App\Services\Students;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Creates a course enrollment. Used by the admin enrollment form and by
 * "Admit" on an online application, so both follow the same rules:
 *
 *   - a student can't be enrolled twice in the same course while that enrollment is still open;
 *   - each enrollment gets its own number (COURSECODE/NNN/YEAR); the first one also becomes
 *     the student's number and login, later ones never change it.
 */
class EnrollmentCreator
{
    /** Statuses that mean the student is still on the course. */
    public const OPEN_STATUSES = ['active', 'pending', 'suspended', 'deferred'];

    public function __construct(private StudentNumberService $numbers) {}

    public function openEnrollment(Student $student, Course $course): ?Enrollment
    {
        return Enrollment::where('student_id', $student->id)->where('course_id', $course->id)
            ->whereIn('status', self::OPEN_STATUSES)->first();
    }

    /**
     * @param array $attributes enrollment fields from the form (total_fees, intake_year, intake_month, dates, mode...)
     * @throws \DomainException when the student already has an open enrollment on this course
     */
    public function create(Student $student, Course $course, array $attributes, ?int $userId = null): Enrollment
    {
        return DB::transaction(function () use ($student, $course, $attributes, $userId) {
            $student = Student::whereKey($student->id)->lockForUpdate()->firstOrFail();

            if ($existing = $this->openEnrollment($student, $course)) {
                throw new \DomainException(sprintf(
                    '%s is already enrolled in %s (%s, %s intake %s). Open that enrollment instead of creating another.',
                    $student->full_name, $course->name, $existing->student_number, $existing->intake_month, $existing->intake_year
                ));
            }

            $year = (int) ($attributes['intake_year'] ?? now()->year);
            [$number, $becomesStudentNumber] = $this->numbers->forNewEnrollment($student, $course, $year);

            if ($becomesStudentNumber) {
                $old = $student->student_number;
                $student->student_number = $number;
                $student->save();
                // Keep the login in step if the student already has an account
                User::where('student_id', $student->id)->where('username', $old)->update(['username' => $number]);
            }

            $enrollment = Enrollment::create(array_merge([
                'status'           => 'active',
                'study_mode'       => 'full_time',
                'student_type'     => 'new',
                'sponsorship_type' => 'self',
                'amount_paid'      => 0,
            ], $attributes, [
                'student_id'     => $student->id,
                'course_id'      => $course->id,
                'student_name'   => $student->full_name,
                'student_number' => $number,
                'course_name'    => $course->name,
                'course_code'    => $this->numbers->courseCode($course),
                'department'     => $course->department?->name,
                'is_active'      => ($attributes['status'] ?? 'active') === 'active',
                'fee_snapshot'   => [
                    'total_fees'  => (float) ($attributes['total_fees'] ?? 0),
                    'course_name' => $course->name,
                    'course_code' => $course->code,
                    'intake'      => ($attributes['intake_month'] ?? '') . ' ' . $year,
                    'created_by'  => $userId ? User::find($userId)?->name : 'System',
                    'created_at'  => now()->toDateTimeString(),
                ],
            ]));

            Log::info('Enrollment created', ['enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'number' => $number, 'by' => $userId]);

            // The student can sign in from now on; their login details are texted once
            app(StudentAccountService::class)->afterEnrollment($student);

            return $enrollment;
        });
    }
}
