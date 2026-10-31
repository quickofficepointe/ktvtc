<?php

namespace App\Services\Terms;

use App\Models\TermRegistration;
use App\Services\Academics\EvaluationService;

/**
 * Exam card for a term = that term 100% paid AND every trainer of the
 * student's units that term evaluated.
 */
class ExamCardGate
{
    public function __construct(private TermLedger $ledger) {}

    /**
     * @return array{allowed: bool, reasons: string[], balance: float}
     */
    public function check(TermRegistration $registration): array
    {
        if ($registration->isExempt()) {
            return ['allowed' => false, 'reasons' => ['Exempt: carried over from the previous course, no exam this term.'], 'balance' => 0.0];
        }

        $reasons = [];

        $row = $this->ledger->forEnrollment($registration->enrollment)->get($registration->term_number);
        $balance = $row['balance'] ?? (float) $registration->fee_amount;
        if ($balance > 0) {
            $reasons[] = 'Clear Term ' . $registration->term_number . ' fees: KES ' . number_format($balance, 2) . ' outstanding.';
        }

        foreach ($this->pendingEvaluations($registration) as $pending) {
            $reasons[] = $pending;
        }

        return ['allowed' => empty($reasons), 'reasons' => $reasons, 'balance' => (float) $balance];
    }

    /**
     * Trainer evaluations the student still owes for this term
     * (units with no trainer assigned don't need one).
     *
     * @return string[]
     */
    protected function pendingEvaluations(TermRegistration $registration): array
    {
        return app(EvaluationService::class)->pendingFor($registration);
    }
}
