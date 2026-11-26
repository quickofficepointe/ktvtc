<?php

namespace App\Services\Students;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admissions correcting an enrollment that was put on the wrong course by mistake (typing or
 * import). Not a student's course change (that has its own approval route): the enrollment keeps
 * its payments and running number; only the course, the course code in the number and, if
 * given, the whole-course fee change. Every correction is written into the enrollment's remarks.
 */
class CourseCorrection
{
    public function __construct(private StudentNumberService $numbers) {}

    /**
     * @return array{old_number: string, new_number: string, message: string}
     * @throws \DomainException
     */
    public function correct(Enrollment $enrollment, Course $to, string $reason, ?float $newTotal, User $by): array
    {
        return DB::transaction(function () use ($enrollment, $to, $reason, $newTotal, $by) {
            $e = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $from = $e->course;

            if ((int) $e->course_id === (int) $to->id) {
                throw new \DomainException("This enrollment is already on {$to->name}.");
            }
            $clash = Enrollment::where('student_id', $e->student_id)->where('course_id', $to->id)->whereKeyNot($e->id)
                ->whereIn('status', EnrollmentCreator::OPEN_STATUSES)->first();
            if ($clash) {
                throw new \DomainException("The student already has an open {$to->name} enrollment ({$clash->student_number}). Close the wrong one instead of moving it.");
            }
            if (DB::table('unit_results')->where('enrollment_id', $e->id)->exists()) {
                throw new \DomainException('This enrollment already has marks for its units. Ask the academics office before moving it to another course.');
            }

            // Same running number, the right course code: SHEP/930/2026 -> PA/930/2026
            $old = (string) $e->student_number;
            $new = $old;
            if (preg_match('#^[^/]+/(\d+)/(\d{4})$#', $old, $m)) {
                $candidate = $this->numbers->format($this->numbers->courseCode($to), (int) $m[1], (int) $m[2]);
                if ($candidate !== $old && $this->numbers->numberTaken($candidate, $e->student_id)) {
                    throw new \DomainException("The number {$candidate} is already used by another student.");
                }
                $new = $candidate;
            }

            $note = now()->format('d M Y') . ": course corrected from " . ($from->name ?? 'unknown') . " ({$old}) to {$to->name} ({$new}) by {$by->name}. Reason: {$reason}";
            $e->forceFill([
                'course_id'      => $to->id,
                'course_name'    => $to->name,
                'course_code'    => $this->numbers->courseCode($to),
                'department'     => $to->department?->name ?? $e->department,
                'student_number' => $new,
                'remarks'        => trim(($e->remarks ? $e->remarks . ' | ' : '') . $note),
            ]);

            $feeNote = '';
            if ($newTotal !== null && $e->billing_mode !== 'term' && round($newTotal, 2) !== round((float) $e->total_fees, 2)) {
                $feeNote = ' Fee changed from KES ' . number_format((float) $e->total_fees) . ' to KES ' . number_format($newTotal) . '.';
                $e->forceFill([
                    'original_fees' => $e->original_fees ?: $e->total_fees,
                    'total_fees' => $newTotal,
                    'fees_modified_at' => now(),
                    'fees_modified_by' => $by->id,
                    'fee_modification_reason' => 'Course corrected: ' . $reason,
                ]);
            }
            // A correction, not a fee change on a locked enrollment: saved without the lock check
            Enrollment::withoutEvents(function () use ($e) {
                $e->balance = (float) $e->total_fees - (float) $e->amount_paid;
                $e->save();
            });

            // Registered terms follow the enrollment (their billed fees stay as they are)
            DB::table('term_registrations')->where('enrollment_id', $e->id)->update(['course_id' => $to->id, 'updated_at' => now()]);

            // The enrollment's number may also be the student's login
            $student = Student::lockForUpdate()->find($e->student_id);
            if ($student && $student->student_number === $old && $new !== $old) {
                $student->student_number = $new;
                $student->save();
                User::where('student_id', $student->id)->where('username', $old)->update(['username' => $new]);
            }

            Log::info('Enrollment course corrected', ['enrollment_id' => $e->id, 'from' => $from?->id, 'to' => $to->id,
                'old_number' => $old, 'new_number' => $new, 'by' => $by->id, 'reason' => $reason, 'new_total' => $newTotal]);

            $message = "Moved to {$to->name}." . ($new !== $old ? " Number is now {$new} (was {$old}); payments with the old number still reach the student." : '') . $feeNote;

            return ['old_number' => $old, 'new_number' => $new, 'message' => $message];
        });
    }
}
