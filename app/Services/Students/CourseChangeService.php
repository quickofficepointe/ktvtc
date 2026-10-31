<?php

namespace App\Services\Students;

use App\Models\Course;
use App\Models\CourseChangeRequest;
use App\Models\Enrollment;
use App\Models\FeeAdjustment;
use App\Models\Student;
use App\Models\TermRegistration;
use App\Models\User;
use App\Services\Payments\FeeAdjustmentService;
use App\Services\SmsService;
use App\Services\Terms\TermRegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Course change requests, from the student portal:
 *
 *   student asks  ->  academics (picks the starting term)  ->  finance (how much paid money moves)
 *                 ->  admissions (final approval)
 *
 * On final approval the student gets a new enrollment on the new course with its own
 * number (which also becomes their student number and login if the old course's number was),
 * starts in the chosen term, and the old enrollment is closed as 'transferred'.
 * Any stage can turn the request down with a reason; the student is texted either way.
 */
class CourseChangeService
{
    public function __construct(
        private EnrollmentCreator $enrollments,
        private TermRegistrationService $terms,
        private FeeAdjustmentService $fees,
    ) {}

    public function submit(Student $student, Enrollment $from, Course $to, string $reason): CourseChangeRequest
    {
        if ($from->student_id !== $student->id || !in_array($from->status, EnrollmentCreator::OPEN_STATUSES, true)) {
            throw new \DomainException('You can only change from a course you are currently on.');
        }
        if ((int) $from->course_id === (int) $to->id) {
            throw new \DomainException('Choose a different course from the one you are on.');
        }
        if ($this->enrollments->openEnrollment($student, $to)) {
            throw new \DomainException("You are already enrolled in {$to->name}.");
        }
        if (CourseChangeRequest::pending()->where('from_enrollment_id', $from->id)->exists()) {
            throw new \DomainException('You already have a course change request waiting for approval.');
        }

        return CourseChangeRequest::create([
            'student_id'         => $student->id,
            'from_enrollment_id' => $from->id,
            'to_course_id'       => $to->id,
            'reason'             => $reason,
            'status'             => CourseChangeRequest::STAGE_ACADEMICS,
        ]);
    }

    public function cancel(CourseChangeRequest $request, Student $student): void
    {
        if ($request->student_id !== $student->id || !$request->isPending()) {
            throw new \DomainException('This request can no longer be withdrawn.');
        }
        $request->update(['status' => CourseChangeRequest::CANCELLED]);
    }

    /**
     * Academics: is the new course right for the student, and which term do they start in?
     */
    public function approveAcademics(CourseChangeRequest $request, User $user, int $startTerm, ?string $note): CourseChangeRequest
    {
        $this->assertStage($request, $user, CourseChangeRequest::STAGE_ACADEMICS);
        $course = $request->toCourse;
        if ($course->hasTermSetup() && ($startTerm < 1 || $startTerm > $course->term_count)) {
            throw new \DomainException("{$course->name} has {$course->term_count} terms.");
        }

        $request->update([
            'start_term' => $startTerm, 'academics_note' => $note, 'academics_by' => $user->id, 'academics_at' => now(),
            'status' => CourseChangeRequest::STAGE_FINANCE,
        ]);
        return $request;
    }

    /**
     * Finance: how much of what was paid on the old course moves to the new one,
     * and whether anything still owed on the old course is cleared.
     */
    public function approveFinance(CourseChangeRequest $request, User $user, float $amountMoved, bool $clearOldBalance, ?string $note): CourseChangeRequest
    {
        $this->assertStage($request, $user, CourseChangeRequest::STAGE_FINANCE);
        $paid = (float) $request->fromEnrollment->amount_paid;
        $amountMoved = round($amountMoved, 2);
        if ($amountMoved < 0 || $amountMoved > $paid) {
            throw new \DomainException('Amount moved must be between 0 and what was paid on the old course (KES ' . number_format($paid, 2) . ').');
        }

        $request->update([
            'amount_moved' => $amountMoved, 'clear_old_balance' => $clearOldBalance, 'finance_note' => $note,
            'finance_by' => $user->id, 'finance_at' => now(), 'status' => CourseChangeRequest::STAGE_ADMIN,
        ]);
        return $request;
    }

    /**
     * Admissions: final approval. Creates the new enrollment and closes the old one.
     */
    public function approveAdmin(CourseChangeRequest $request, User $user, ?string $note): CourseChangeRequest
    {
        $this->assertStage($request, $user, CourseChangeRequest::STAGE_ADMIN);

        $request = DB::transaction(function () use ($request, $user, $note) {
            $request = CourseChangeRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $this->assertStage($request, $user, CourseChangeRequest::STAGE_ADMIN);

            $old = Enrollment::whereKey($request->from_enrollment_id)->lockForUpdate()->firstOrFail();
            $student = $request->student;
            $course = $request->toCourse;
            if (!$course->hasTermSetup()) {
                throw new \DomainException("{$course->name} has not been split into terms yet. Finance sets this up under Term Fees, then approve again.");
            }
            $startTerm = max(1, (int) $request->start_term);
            $label = "Course change from {$old->course_name} ({$old->student_number})";

            // Start date placing the chosen term at today, so term dates and fees line up
            $start = today();
            if ($startTerm > 1) {
                $back = ($startTerm - 1) * $course->term_length;
                $start = $course->term_length_unit === 'weeks' ? today()->subWeeks($back) : today()->subMonthsNoOverflow($back);
            }

            $new = $this->enrollments->create($student, $course, [
                'intake_year'     => now()->year,
                'intake_month'    => now()->format('F'),
                'enrollment_date' => today(),
                'start_date'      => $start,
                'total_fees'      => 0,
                'billing_mode'    => 'term',
                'study_mode'      => $old->study_mode,
                'campus_id'       => $old->campus_id,
                'sponsorship_type' => $old->sponsorship_type,
                'student_type'    => 'transfer',
            ], $user->id, replacesNumber: $old->student_number === $student->student_number);

            $this->terms->startAt($new, $startTerm, $user->id, $label);

            $this->moveMoney($request, $old, $new, $user->id, $label);
            $this->closeOld($old, $request);

            $request->update([
                'admin_note' => $note, 'admin_by' => $user->id, 'admin_at' => now(),
                'status' => CourseChangeRequest::APPROVED, 'new_enrollment_id' => $new->id,
            ]);

            return $request;
        });

        DB::afterCommit(fn () => $this->notify($request->fresh(['student', 'toCourse', 'newEnrollment'])));
        return $request;
    }

    public function reject(CourseChangeRequest $request, User $user, string $reason): CourseChangeRequest
    {
        if (!$request->isPending()) {
            throw new \DomainException('This request has already been decided.');
        }
        $this->assertStage($request, $user, $request->status);
        $request->update([
            'status' => CourseChangeRequest::REJECTED, 'rejected_at_stage' => $request->status, 'rejection_reason' => $reason,
            ($request->status . '_by') => $user->id, ($request->status . '_at') => now(),
        ]);
        $this->notify($request->fresh(['student', 'toCourse']));
        return $request;
    }

    private function moveMoney(CourseChangeRequest $request, Enrollment $old, Enrollment $new, int $userId, string $label): void
    {
        $moved = round((float) $request->amount_moved, 2);
        if ($moved > 0) {
            $this->fees->changePaid($old->id, -$moved);
            $this->fees->changePaid($new->id, $moved);
            foreach ([[$old, FeeAdjustment::TRANSFER_OUT, -$moved], [$new, FeeAdjustment::TRANSFER_IN, $moved]] as [$e, $type, $change]) {
                FeeAdjustment::create([
                    'student_id' => $e->student_id, 'enrollment_id' => $e->id, 'type' => $type, 'paid_change' => $change,
                    'reason' => $label . ($request->finance_note ? ". {$request->finance_note}" : ''),
                    'course_change_request_id' => $request->id, 'created_by' => $request->finance_by ?? $userId,
                ]);
            }
        }

        $owed = round((float) Enrollment::whereKey($old->id)->value('balance'), 2);
        if ($request->clear_old_balance && $owed > 0) {
            $this->fees->changeFees($old->id, -$owed);
            FeeAdjustment::create([
                'student_id' => $old->student_id, 'enrollment_id' => $old->id, 'type' => FeeAdjustment::WRITE_OFF, 'fees_change' => -$owed,
                'reason' => $label, 'course_change_request_id' => $request->id, 'created_by' => $request->finance_by ?? $userId,
            ]);
        }
    }

    private function closeOld(Enrollment $old, CourseChangeRequest $request): void
    {
        Enrollment::whereKey($old->id)->update(['status' => 'transferred', 'is_active' => false]);

        // Terms of the old course not yet finished stop here (no more fees, marks or evaluations)
        TermRegistration::where('enrollment_id', $old->id)->active()->where('starts_on', '>', today())
            ->update(['status' => 'cancelled', 'override_reason' => "Course change request #{$request->id}"]);
    }

    private function assertStage(CourseChangeRequest $request, User $user, string $stage): void
    {
        if ($request->status !== $stage) {
            throw new \DomainException('This request is not waiting for ' . (CourseChangeRequest::STAGE_NAMES[$stage] ?? $stage) . '.');
        }
        if (!$request->awaits($user)) {
            throw new \DomainException('Only ' . CourseChangeRequest::STAGE_NAMES[$stage] . ' can approve this step.');
        }
    }

    private function notify(CourseChangeRequest $request): void
    {
        $student = $request->student;
        if (!$student?->phone) {
            return;
        }

        $message = $request->status === CourseChangeRequest::APPROVED
            ? "Dear {$student->full_name}, your change to {$request->toCourse->name} has been approved. Your new registration number is "
                . "{$request->newEnrollment->student_number}" . ($request->start_term > 1 ? ", starting in Term {$request->start_term}" : '') . '. KTVTC'
            : "Dear {$student->full_name}, your request to change to {$request->toCourse->name} was not approved: {$request->rejection_reason}. KTVTC";

        try {
            app(SmsService::class)->sendSingleSms($student->phone, $message);
        } catch (\Throwable $e) {
            Log::warning('Course change SMS failed', ['request_id' => $request->id, 'error' => $e->getMessage()]);
        }
    }
}
