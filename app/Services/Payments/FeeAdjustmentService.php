<?php

namespace App\Services\Payments;

use App\Models\Enrollment;
use App\Models\FeeAdjustment;
use App\Models\TermRegistration;
use Illuminate\Support\Facades\DB;

/**
 * Changes to what a student owes or has paid that are not payments. Each is a
 * FeeAdjustment row, so statements show who changed what and why.
 *
 * Term adjustments change that term's fee (so the 50% rule and the exam card see
 * them) and the enrollment total. Enrollment totals are written with the query
 * builder because Enrollment refuses model edits to locked fees (meant for hand edits).
 */
class FeeAdjustmentService
{
    /**
     * Discount, waiver or bursary (lowers the term fee) or an extra charge (raises it).
     */
    public function adjustTerm(TermRegistration $registration, string $type, float $amount, string $reason, int $userId): FeeAdjustment
    {
        if (!array_key_exists($type, FeeAdjustment::TERM_TYPES)) {
            throw new \DomainException('Unknown adjustment type.');
        }
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \DomainException('Enter an amount above zero.');
        }

        return DB::transaction(function () use ($registration, $type, $amount, $reason, $userId) {
            $registration = TermRegistration::whereKey($registration->id)->lockForUpdate()->firstOrFail();
            if ($registration->status !== 'registered') {
                throw new \DomainException("Term {$registration->term_number} is not an active registration.");
            }
            $delta = $type === FeeAdjustment::CHARGE ? $amount : -$amount;
            if ((float) $registration->fee_amount + $delta < 0) {
                throw new \DomainException('Term ' . $registration->term_number . ' fee is only KES ' . number_format($registration->fee_amount, 2) . '; it cannot go below zero.');
            }

            $registration->update(['fee_amount' => round((float) $registration->fee_amount + $delta, 2)]);
            $this->changeFees($registration->enrollment_id, $delta);

            return FeeAdjustment::create([
                'student_id'           => $registration->student_id,
                'enrollment_id'        => $registration->enrollment_id,
                'term_registration_id' => $registration->id,
                'type'                 => $type,
                'fees_change'          => $delta,
                'reason'               => $reason,
                'created_by'           => $userId,
            ]);
        });
    }

    /**
     * Pay back money the student has paid beyond their fees.
     */
    public function refund(Enrollment $enrollment, float $amount, string $method, ?string $reference, string $reason, int $userId): FeeAdjustment
    {
        $amount = round($amount, 2);

        return DB::transaction(function () use ($enrollment, $amount, $method, $reference, $reason, $userId) {
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $credit = round((float) $enrollment->amount_paid - (float) $enrollment->total_fees, 2);
            if ($amount <= 0 || $amount > $credit) {
                throw new \DomainException('Only money paid beyond the fees can be refunded: KES ' . number_format(max(0, $credit), 2) . ' on this course.');
            }

            $this->changePaid($enrollment->id, -$amount);

            return FeeAdjustment::create([
                'student_id'    => $enrollment->student_id,
                'enrollment_id' => $enrollment->id,
                'type'          => FeeAdjustment::REFUND,
                'paid_change'   => -$amount,
                'reason'        => $reason,
                'method'        => $method,
                'reference'     => $reference,
                'created_by'    => $userId,
            ]);
        });
    }

    /** Net term adjustments on an enrollment (used to share a course fee across terms). */
    public function termChanges(Enrollment $enrollment): float
    {
        return (float) FeeAdjustment::where('enrollment_id', $enrollment->id)->whereNotNull('term_registration_id')->sum('fees_change');
    }

    /** Net adjustments already made to one term registration. */
    public function changesForRegistration(?int $registrationId): float
    {
        return $registrationId ? (float) FeeAdjustment::where('term_registration_id', $registrationId)->sum('fees_change') : 0.0;
    }

    public function changeFees(int $enrollmentId, float $delta): void
    {
        Enrollment::whereKey($enrollmentId)->update([
            'total_fees' => DB::raw('ROUND(total_fees + ' . round($delta, 2) . ', 2)'),
            'balance'    => DB::raw('ROUND(total_fees - amount_paid, 2)'),
        ]);
    }

    public function changePaid(int $enrollmentId, float $delta): void
    {
        Enrollment::whereKey($enrollmentId)->update([
            'amount_paid' => DB::raw('ROUND(amount_paid + ' . round($delta, 2) . ', 2)'),
            'balance'     => DB::raw('ROUND(total_fees - amount_paid, 2)'),
        ]);
    }
}
