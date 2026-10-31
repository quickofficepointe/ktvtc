<?php

namespace App\Services\Terms;

use App\Models\CourseTermFee;
use App\Models\Enrollment;
use App\Models\TermRegistration;
use App\Services\Payments\FeeAdjustmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Registers students for terms.
 *
 * Rules (agreed with the college):
 *   - terms are registered in order, one at a time;
 *   - a student may register for the next term only when the CURRENT term is at least 50% paid,
 *     from 14 days before the current term ends;
 *   - staff may register a student who doesn't meet the rule, with a written reason;
 *   - 'course' billing (all existing students): the course fee is shared equally across terms;
 *   - 'term' billing (enrollment created with fees 0): each term's fee is billed on registration.
 */
class TermRegistrationService
{
    public const THRESHOLD_PERCENT = 50;
    public const OPENS_DAYS_BEFORE_END = 14;

    public function __construct(private TermCalendar $calendar, private TermLedger $ledger) {}

    /**
     * Whether the student can self-register for the next term, and why not.
     *
     * @return array{allowed: bool, next_term: ?int, reasons: string[], fee: ?float, opens_on: ?Carbon}
     */
    public function nextTermStatus(Enrollment $enrollment): array
    {
        $course  = $enrollment->course;
        $reasons = [];

        if (!$course || !$course->hasTermSetup()) {
            return ['allowed' => false, 'next_term' => null, 'reasons' => ['Your course has not been split into terms yet. Contact the finance office.'], 'fee' => null, 'opens_on' => null];
        }

        $registrations = $enrollment->termRegistrations()->active()->get();
        $current = $registrations->max('term_number') ?? 0;
        $next = $current + 1;

        if ($next > $course->term_count) {
            return ['allowed' => false, 'next_term' => null, 'reasons' => ['You are registered for every term of your course.'], 'fee' => null, 'opens_on' => null];
        }

        $intake = $this->calendar->intakeDate($enrollment);
        $opensOn = null;

        if ($current > 0) {
            [, $currentEnd] = $this->calendar->termDates($course, $intake, $current);
            $opensOn = $currentEnd->copy()->subDays(self::OPENS_DAYS_BEFORE_END);
            if (today()->lt($opensOn)) {
                $reasons[] = "Registration for Term {$next} opens on {$opensOn->format('d M Y')}.";
            }

            $row = $this->ledger->forEnrollment($enrollment)->get($current);
            if ($row && $row['percent'] < self::THRESHOLD_PERCENT) {
                $needed = round($row['fee'] * self::THRESHOLD_PERCENT / 100 - $row['paid'], 2);
                $reasons[] = sprintf('Pay at least %d%% of Term %d first: KES %s more (you have paid %.1f%%).',
                    self::THRESHOLD_PERCENT, $current, number_format($needed, 2), $row['percent']);
            }
        }

        try {
            [$fee] = $this->feeFor($enrollment, $next);
        } catch (\DomainException $e) {
            $reasons[] = $e->getMessage();
            $fee = null;
        }

        return ['allowed' => empty($reasons), 'next_term' => $next, 'reasons' => $reasons, 'fee' => $fee, 'opens_on' => $opensOn];
    }

    /**
     * Student registering themselves: all rules apply.
     */
    public function selfRegister(Enrollment $enrollment, int $userId): TermRegistration
    {
        return DB::transaction(function () use ($enrollment, $userId) {
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $status = $this->nextTermStatus($enrollment);
            if (!$status['allowed']) {
                throw new \DomainException(implode(' ', $status['reasons']));
            }
            return $this->create($enrollment, $status['next_term'], TermRegistration::TYPE_SELF, $userId);
        });
    }

    /**
     * Finance/admin registering a student. The 50% rule and the date window can be
     * waived, but only with a reason.
     */
    public function staffRegister(Enrollment $enrollment, int $userId, ?string $overrideReason = null): TermRegistration
    {
        return DB::transaction(function () use ($enrollment, $userId, $overrideReason) {
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $status = $this->nextTermStatus($enrollment);
            if ($status['next_term'] === null) {
                throw new \DomainException(implode(' ', $status['reasons']));
            }

            // Only the fee/payment/date rules may be waived; missing setup can't be
            $blocking = array_filter($status['reasons'], fn ($r) => !str_starts_with($r, 'Pay at least') && !str_starts_with($r, 'Registration for Term'));
            if ($blocking) {
                throw new \DomainException(implode(' ', $blocking));
            }
            if (!$status['allowed'] && !$overrideReason) {
                throw new \DomainException(implode(' ', $status['reasons']) . ' Give a reason to register anyway.');
            }

            return $this->create($enrollment, $status['next_term'], TermRegistration::TYPE_STAFF, $userId, $status['allowed'] ? null : $overrideReason);
        });
    }

    /**
     * A student joining a course part-way (course change): earlier terms are recorded as
     * exempt (no fee, no marks, no evaluations) and $startTerm is registered and billed.
     * The enrollment's start date must already place $startTerm at today.
     */
    public function startAt(Enrollment $enrollment, int $startTerm, int $userId, string $note): TermRegistration
    {
        $course = $enrollment->course;
        if (!$course || !$course->hasTermSetup()) {
            throw new \DomainException(($course->name ?? 'The new course') . ' has not been split into terms yet. Finance sets this up under Term Fees.');
        }
        if ($startTerm < 1 || $startTerm > $course->term_count) {
            throw new \DomainException("{$course->name} has {$course->term_count} terms; Term {$startTerm} does not exist.");
        }

        return DB::transaction(function () use ($enrollment, $course, $startTerm, $userId, $note) {
            $intake = $this->calendar->intakeDate($enrollment);
            for ($n = 1; $n < $startTerm; $n++) {
                [$start, $end] = $this->calendar->termDates($course, $intake, $n);
                TermRegistration::updateOrCreate(
                    ['enrollment_id' => $enrollment->id, 'term_number' => $n],
                    [
                        'student_id' => $enrollment->student_id, 'course_id' => $course->id,
                        'starts_on' => $start, 'ends_on' => $end, 'fee_amount' => 0, 'bills_enrollment' => false,
                        'registration_type' => TermRegistration::TYPE_EXEMPT, 'status' => 'registered',
                        'registered_by' => $userId, 'override_reason' => $note,
                    ]
                );
            }

            return $this->create($enrollment, $startTerm, TermRegistration::TYPE_STAFF, $userId, $note);
        });
    }

    /**
     * Existing (course-billed) students: register every term up to today
     * automatically, so they start the new system in the right term.
     */
    public function syncLegacy(Enrollment $enrollment): int
    {
        $course = $enrollment->course;
        if ($enrollment->billing_mode !== 'course' || !$course || !$course->hasTermSetup() || (float) $enrollment->total_fees <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($enrollment, $course) {
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $upTo = min($course->term_count, $this->calendar->termNumberOn($course, $this->calendar->intakeDate($enrollment)));
            $have = $enrollment->termRegistrations()->active()->pluck('term_number')->all();

            $created = 0;
            for ($n = 1; $n <= $upTo; $n++) {
                if (!in_array($n, $have, true)) {
                    $this->create($enrollment, $n, TermRegistration::TYPE_LEGACY, null);
                    $created++;
                }
            }
            return $created;
        });
    }

    /**
     * Undo the latest registration (e.g. registered by mistake). Term-billed fees are removed again.
     */
    public function cancelLatest(Enrollment $enrollment, string $reason, int $userId): TermRegistration
    {
        return DB::transaction(function () use ($enrollment, $reason, $userId) {
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            // reorder(): the relation already sorts ascending, which would make this pick Term 1
            $reg = $enrollment->termRegistrations()->active()->reorder('term_number', 'desc')->first();
            if (!$reg) {
                throw new \DomainException('There is no term registration to cancel.');
            }

            if ($reg->bills_enrollment) {
                $this->changeBilledTotal($enrollment, -(float) $reg->fee_amount);
            }
            $reg->update([
                'status'          => 'cancelled',
                'override_reason' => trim(($reg->override_reason ? $reg->override_reason . ' | ' : '') . "Cancelled by user {$userId}: {$reason}"),
            ]);
            return $reg;
        });
    }

    /**
     * @return array{0: float, 1: ?CourseTermFee, 2: ?array} [amount, fee version, items]
     */
    public function feeFor(Enrollment $enrollment, int $termNumber): array
    {
        $course = $enrollment->course;

        if ($this->isCourseBilled($enrollment)) {
            // A discount or charge on one term changed the total; it must not spread to the other terms
            $base = (float) $enrollment->total_fees - app(FeeAdjustmentService::class)->termChanges($enrollment);
            return [round($base / $course->term_count, 2), null, null];
        }

        $intake = $this->calendar->intakeDate($enrollment);
        $versions = CourseTermFee::where('course_id', $course->id)->where('term_number', $termNumber);
        $fee = (clone $versions)->whereDate('effective_from', '<=', $intake)->orderByDesc('effective_from')->first()
            ?? (clone $versions)->orderBy('effective_from')->first(); // fees set after this intake: use the earliest

        if (!$fee) {
            throw new \DomainException("Fees for Term {$termNumber} of {$course->name} have not been set. Contact the finance office.");
        }

        return [(float) $fee->total, $fee, $fee->items];
    }

    private function create(Enrollment $enrollment, int $n, string $type, ?int $userId, ?string $overrideReason = null): TermRegistration
    {
        $course = $enrollment->course;

        // First registration of an enrollment created with no course fee switches it to per-term billing
        if ($enrollment->billing_mode === 'course' && (float) $enrollment->total_fees <= 0
            && !$enrollment->termRegistrations()->exists()) {
            Enrollment::whereKey($enrollment->id)->update(['billing_mode' => 'term']);
            $enrollment->billing_mode = 'term';
        }

        [$amount, $feeVersion, $items] = $this->feeFor($enrollment, $n);
        // Re-registering a cancelled term keeps the discounts/charges already made on it
        $existingId = TermRegistration::where('enrollment_id', $enrollment->id)->where('term_number', $n)->value('id');
        $amount = round($amount + app(FeeAdjustmentService::class)->changesForRegistration($existingId), 2);
        [$start, $end] = $this->calendar->termDates($course, $this->calendar->intakeDate($enrollment), $n);
        $bills = $enrollment->billing_mode === 'term';

        $reg = TermRegistration::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'term_number' => $n],
            [
                'student_id'         => $enrollment->student_id,
                'course_id'          => $course->id,
                'starts_on'          => $start,
                'ends_on'            => $end,
                'fee_amount'         => $amount,
                'fee_items'          => $items,
                'bills_enrollment'   => $bills,
                'course_term_fee_id' => $feeVersion?->id,
                'registration_type'  => $type,
                'status'             => 'registered',
                'registered_by'      => $userId,
                'override_reason'    => $overrideReason,
                'exam_card_downloaded_at' => null,
            ]
        );

        if ($bills) {
            $this->changeBilledTotal($enrollment, $amount);
        }

        return $reg;
    }

    private function isCourseBilled(Enrollment $enrollment): bool
    {
        return $enrollment->billing_mode !== 'term';
    }

    /**
     * Adds/removes a term fee on the enrollment. Goes through the query builder on purpose:
     * Enrollment refuses model updates to total_fees once fees are locked, which is meant
     * for manual edits, not for billing a newly registered term.
     */
    private function changeBilledTotal(Enrollment $enrollment, float $delta): void
    {
        $total = round((float) $enrollment->total_fees + $delta, 2);
        Enrollment::whereKey($enrollment->id)->update([
            'total_fees' => $total,
            'balance'    => round($total - (float) $enrollment->amount_paid, 2),
        ]);
        $enrollment->total_fees = $total;
        $enrollment->balance = round($total - (float) $enrollment->amount_paid, 2);
        $enrollment->syncOriginal();
    }
}
