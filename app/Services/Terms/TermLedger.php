<?php

namespace App\Services\Terms;

use App\Models\Enrollment;
use Illuminate\Support\Collection;

/**
 * How much of each registered term is paid.
 *
 * Payments are recorded against the enrollment (fee payments, KCB IPNs, finance
 * receipts all keep working unchanged). They are applied to the registered
 * terms oldest first, so a term only counts as paid once earlier terms are cleared.
 */
class TermLedger
{
    /**
     * @return Collection<int, array{registration: \App\Models\TermRegistration, term: int, fee: float, paid: float, balance: float, percent: float}>
     *         keyed by term number
     */
    public function forEnrollment(Enrollment $enrollment): Collection
    {
        $remaining = round((float) $enrollment->amount_paid, 2);
        $rows = collect();

        foreach ($enrollment->termRegistrations()->active()->get() as $reg) {
            $fee  = round((float) $reg->fee_amount, 2);
            $paid = round(min($fee, max(0, $remaining)), 2);
            $remaining = round($remaining - $paid, 2);

            $rows->put($reg->term_number, [
                'registration' => $reg,
                'term'         => $reg->term_number,
                'fee'          => $fee,
                'paid'         => $paid,
                'balance'      => round($fee - $paid, 2),
                'percent'      => $fee > 0 ? round($paid / $fee * 100, 1) : 100.0,
            ]);
        }

        return $rows;
    }

    /** Money paid beyond every registered term (carried into future terms). */
    public function credit(Enrollment $enrollment): float
    {
        $billed = (float) $enrollment->termRegistrations()->active()->sum('fee_amount');
        return max(0, round((float) $enrollment->amount_paid - $billed, 2));
    }
}
