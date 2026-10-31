<?php

namespace App\Services\Payments;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbIpn;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a received KCB payment to a college student's fees.
 *
 * Rules (agreed with the college):
 *   - if the payer used one course enrollment's own number, that enrollment is paid;
 *   - otherwise the oldest outstanding enrollment is paid first;
 *   - anything left after all balances are cleared stays as a credit
 *     (negative balance) on the newest enrollment.
 * One FeePayment row is created per enrollment the money touches.
 */
class SchoolFeeAllocator
{
    /**
     * Must be called inside a DB transaction.
     *
     * @return Collection<FeePayment>
     * @throws \DomainException when the student has no enrollment to pay into
     */
    public function allocate(Student $student, KcbIpn $ipn, ?int $userId = null, ?Enrollment $only = null): Collection
    {
        // Paid with one course's own number: that course is paid first, then any
        // excess clears the student's other courses oldest-first. The student's own
        // number is also their first course's number, so parents use it for everything.
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('status', '!=', 'dropped')
            ->orderByRaw('start_date IS NULL, start_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($only) {
            $enrollments = $enrollments->sortBy(fn ($e) => $e->id === $only->id ? 0 : 1)->values();
        }

        if ($enrollments->isEmpty()) {
            throw new \DomainException("{$student->student_number} has no enrollment to pay into.");
        }

        $remaining = round((float) $ipn->transaction_amount, 2);
        $parts = [];

        foreach ($enrollments as $enrollment) {
            if ($remaining <= 0) {
                break;
            }
            $owed = round((float) $enrollment->total_fees - (float) $enrollment->amount_paid, 2);
            if ($owed <= 0) {
                continue;
            }
            $part = min($owed, $remaining);
            $parts[] = [$enrollment, $part];
            $remaining = round($remaining - $part, 2);
        }

        if ($remaining > 0) {
            // Credit stays on the course that was named, else the newest course
            $newest = $only ? $enrollments->first() : $enrollments->last();
            $existing = array_search($newest, array_column($parts, 0), true);
            if ($existing !== false) {
                $parts[$existing][1] = round($parts[$existing][1] + $remaining, 2);
            } else {
                $parts[] = [$newest, $remaining];
            }
        }

        $payments = collect();
        foreach ($parts as $i => [$enrollment, $part]) {
            $payments->push(FeePayment::create([
                'student_id'            => $student->id,
                'enrollment_id'         => $enrollment->id,
                'amount'                => $part,
                'payment_date'          => ($ipn->transaction_date ?? now())->toDateString(),
                'receipt_number'        => 'KCB-' . $ipn->transaction_id . ($i > 0 ? '-' . ($i + 1) : ''),
                'payment_method'        => 'kcb',
                'transaction_code'      => $ipn->transaction_id,
                'kcb_transaction_id'    => $ipn->transaction_id,
                'bill_reference_number' => $ipn->business_key,
                'payer_name'            => $ipn->payer_name ?: null,
                'payer_phone'           => $ipn->debit_msisdn,
                'payer_type'            => 'other',
                'status'                => 'completed',
                'is_verified'           => true,
                'verified_by'           => $userId,
                'verified_at'           => now(),
                'recorded_by'           => $userId,
                'import_source'         => 'kcb_ipn',
                'notes'                 => "KCB till {$ipn->till_number} payment {$ipn->transaction_id}"
                    . (count($parts) > 1 ? ' (split across ' . count($parts) . ' enrollments)' : ''),
            ]));

            $enrollment->amount_paid = round((float) $enrollment->amount_paid + $part, 2);
            $enrollment->save(); // balance is recalculated in Enrollment::saving
        }

        DB::afterCommit(fn () => $this->notify($student, $ipn, $enrollments->last()->fresh()));

        return $payments;
    }

    private function notify(Student $student, KcbIpn $ipn, Enrollment $latest): void
    {
        if (empty($student->phone)) {
            return;
        }

        try {
            $balance = (float) $latest->balance;
            $message = "Dear {$student->full_name}, we have received KES " . number_format($ipn->transaction_amount, 2)
                . " for school fees. Ref {$ipn->transaction_id}. "
                . ($balance < 0
                    ? 'Credit on account: KES ' . number_format(abs($balance), 2) . '.'
                    : 'Balance: KES ' . number_format($balance, 2) . '.')
                . ' KTVTC';

            app(SmsService::class)->sendSingleSms($student->phone, $message);
        } catch (\Throwable $e) {
            Log::warning('Fee payment SMS failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);
        }
    }
}
