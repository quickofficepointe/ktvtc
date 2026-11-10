<?php

namespace App\Services\Payments;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\HighSchoolStudent;
use App\Models\KcbBuniTransaction;
use App\Models\KcbIpn;
use App\Models\KcbTransaction;
use App\Models\PreOrder;
use App\Models\Student;
use App\Services\Cards\CardWallet;
use App\Services\Cards\InsufficientWalletBalance;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Single entry point for every KCB IPN, whichever URL it arrives on.
 *
 *   till 7664166 (school fees) -> college student's fee account
 *   till 8118445 (cafeteria)   -> high-school card top-up, or a POS sale / pre-order paid by STK
 *
 * The IPN is the ONLY thing that credits money. STK callbacks only update the
 * status shown to the payer. Anything that can't be matched with certainty
 * waits in the finance "Unallocated payments" queue.
 */
class KcbIpnRouter
{
    public function __construct(
        private SchoolFeeAllocator $feeAllocator,
        private CardFundingCreditor $cardCreditor,
        private CardWallet $cardWallet,
    ) {}

    /**
     * Record an IPN and route it. Safe to call again with the same payload.
     *
     * @param bool $historical recovered from logs/old tables: never auto-credited, always reviewed by finance
     */
    public function receive(array $payload, ?string $rawBody = null, ?string $ip = null, ?string $signature = null, bool $historical = false): KcbIpn
    {
        $data = KcbIpnPayload::fromArray($payload);

        if ($data->transactionId && $existing = KcbIpn::where('transaction_id', $data->transactionId)->first()) {
            // A KCB retry of a payment whose routing failed last time: try again
            return $existing->status === KcbIpn::STATUS_RECEIVED && !$existing->is_historical
                ? $this->routeSafely($existing)
                : $existing;
        }

        $signatureStatus = $this->checkSignature($rawBody, $signature);

        try {
            $ipn = KcbIpn::create([
                'message_id'                 => $data->header['messageID'] ?? null,
                'originator_conversation_id' => $data->header['originatorConversationID'] ?? null,
                'channel_code'               => $data->header['channelCode'] ?? null,
                'business_key'               => $data->accountRaw,
                'business_key_type'          => $data->notification['businessKeyType'] ?? null,
                'till_number'                => $data->tillNumber,
                'account_reference'          => $data->accountReference,
                'status'                     => KcbIpn::STATUS_RECEIVED,
                'source_ip'                  => $ip,
                'signature_status'           => $signatureStatus,
                'is_historical'              => $historical,
                'debit_msisdn'               => $data->msisdn,
                'transaction_amount'         => $data->amount,
                'transaction_date'           => $data->transactionDate,
                'transaction_id'             => $data->transactionId,
                'first_name'                 => $data->firstName,
                'middle_name'                => $data->middleName,
                'last_name'                  => $data->lastName,
                'currency'                   => $data->notification['currency'] ?? 'KES',
                'narration'                  => $data->notification['narration'] ?? null,
                'transaction_type'           => $data->notification['transactionType'] ?? null,
                'invoice_number'             => $data->accountRaw,
                'raw_payload'                => $payload,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Same IPN delivered twice at the same moment
            return KcbIpn::where('transaction_id', $data->transactionId)->firstOrFail();
        }

        if (!$data->transactionId || $data->amount <= 0) {
            $ipn->markOutcome(KcbIpn::STATUS_QUARANTINED, 'IPN is missing the transaction ID or amount.');
            return $ipn;
        }

        if ($problem = $this->sourceProblem($ip, $signatureStatus)) {
            $ipn->route = $this->guessRoute($ipn);
            $ipn->suggestions = $this->suggestionsFor($ipn);
            $ipn->markOutcome(KcbIpn::STATUS_QUARANTINED, $problem);
            Log::warning('KCB IPN quarantined', ['ipn_id' => $ipn->id, 'reason' => $problem]);
            return $ipn;
        }

        if ($historical) {
            return $this->routeHistorical($ipn);
        }

        return $this->routeSafely($ipn);
    }

    /**
     * Re-check a quarantined IPN against the current source rules (e.g. after KCB's address
     * was added to KCB_IPN_ALLOWED_IPS). If it now passes it is routed like a new IPN;
     * otherwise it stays quarantined. Returns null when it was not eligible.
     */
    public function releaseQuarantined(KcbIpn $ipn, bool $dryRun = false): ?KcbIpn
    {
        if ($ipn->status !== KcbIpn::STATUS_QUARANTINED || $ipn->is_historical || !$ipn->transaction_id || (float) $ipn->transaction_amount <= 0) {
            return null;
        }
        if ($this->sourceProblem($ipn->source_ip, (string) $ipn->signature_status)) {
            return null;
        }
        if ($dryRun) {
            return $ipn;
        }

        $locked = DB::transaction(function () use ($ipn) {
            $row = KcbIpn::whereKey($ipn->id)->lockForUpdate()->first();
            if (!$row || $row->status !== KcbIpn::STATUS_QUARANTINED) {
                return null;
            }
            $row->forceFill(['status' => KcbIpn::STATUS_RECEIVED, 'status_reason' => null, 'suggestions' => null])->save();
            return $row;
        });

        return $locked ? $this->routeSafely($locked) : null;
    }

    /**
     * The payment is already stored, so a failure while applying it must not leave it
     * stuck as "received" where nobody sees it: it goes to the finance queue instead.
     */
    private function routeSafely(KcbIpn $ipn): KcbIpn
    {
        try {
            return $this->route($ipn);
        } catch (\Throwable $e) {
            Log::error('KCB IPN could not be applied', ['ipn_id' => $ipn->id, 'error' => $e->getMessage()]);
            $ipn = $ipn->fresh();
            $ipn->route ??= $this->guessRoute($ipn);
            $ipn->suggestions = rescue(fn () => $this->suggestionsFor($ipn), [], false);
            $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, 'Could not be applied automatically (' . Str::limit($e->getMessage(), 150) . '). Assign it by hand.');
            return $ipn;
        }
    }

    /**
     * Recovered IPNs never credit automatically: finance may already have typed
     * the payment in by hand. Only clear-cut cases are closed here.
     */
    private function routeHistorical(KcbIpn $ipn): KcbIpn
    {
        $ipn->route = $this->guessRoute($ipn);

        if ($link = $this->findStkOrder($ipn)) {
            $ipn->route = KcbIpn::ROUTE_STK_LINK;
            $ipn->markOutcome(KcbIpn::STATUS_LINKED, 'Payment for ' . class_basename($link) . ' #' . $link->id, $link::class, $link->id);
            return $ipn;
        }

        if ($reason = $this->alreadyRecorded($ipn)) {
            $ipn->markOutcome(KcbIpn::STATUS_DUPLICATE, $reason);
            return $ipn;
        }

        $ipn->suggestions = $this->suggestionsFor($ipn);
        $ipn->markOutcome(
            KcbIpn::STATUS_UNALLOCATED,
            'Recovered from history. Check it was not already recorded by hand before assigning.'
        );
        return $ipn;
    }

    /**
     * Was this bank transaction already applied by the handlers this router replaced?
     */
    private function alreadyRecorded(KcbIpn $ipn): ?string
    {
        $id = $ipn->transaction_id;

        return match (true) {
            KcbTransaction::where('transaction_reference', $id)->whereNotNull('fee_payment_id')->exists()
                => 'Already applied to fees by the previous IPN handler.',
            FeePayment::where('transaction_code', $id)->exists()
                => 'Already recorded as a fee payment.',
            CardTransaction::where('mpesa_receipt', $id)->exists()
                => 'Already credited to a card by the previous IPN handler.',
            default => null,
        };
    }

    /**
     * Automatic routing for a freshly received IPN.
     */
    public function route(KcbIpn $ipn): KcbIpn
    {
        return DB::transaction(function () use ($ipn) {
            $ipn = KcbIpn::whereKey($ipn->id)->lockForUpdate()->first();
            if ($ipn->status !== KcbIpn::STATUS_RECEIVED) {
                return $ipn;
            }

            if ($link = $this->findStkOrder($ipn)) {
                $ipn->route = KcbIpn::ROUTE_STK_LINK;
                $ipn->markOutcome(KcbIpn::STATUS_LINKED, 'Payment for ' . class_basename($link) . ' #' . $link->id, $link::class, $link->id);
                return $ipn;
            }

            if ($ipn->till_number === config('kcb.tills.school_fees')) {
                return $this->routeSchoolFees($ipn);
            }

            if ($ipn->till_number === config('kcb.tills.cafeteria')) {
                return $this->routeCafeteria($ipn);
            }

            $ipn->route = KcbIpn::ROUTE_UNKNOWN;
            $ipn->suggestions = $this->suggestionsFor($ipn);
            $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, "Unknown till number: {$ipn->till_number}");
            return $ipn;
        });
    }

    // ------------------------------------------------------------------
    // Finance actions from the Unallocated queue
    // ------------------------------------------------------------------

    public function assignToStudent(KcbIpn $ipn, Student $student, int $userId): KcbIpn
    {
        return DB::transaction(function () use ($ipn, $student, $userId) {
            $ipn = $this->lockForResolution($ipn);
            $payments = $this->feeAllocator->allocate($student, $ipn, $userId);
            $ipn->route = KcbIpn::ROUTE_SCHOOL_FEES;
            $this->resolve($ipn, $userId);
            $ipn->markOutcome(KcbIpn::STATUS_CREDITED, "Assigned to {$student->student_number} by finance", FeePayment::class, $payments->first()->id);
            return $ipn;
        });
    }

    /**
     * @param string|null $fundingType 'T' or 'C' chosen by finance; otherwise taken from the account typed
     */
    public function assignToCard(KcbIpn $ipn, CardAccount $card, int $userId, ?string $fundingType = null): KcbIpn
    {
        return DB::transaction(function () use ($ipn, $card, $userId, $fundingType) {
            $ipn = $this->lockForResolution($ipn);
            $type = $fundingType ?? $this->fundingTypeOf($ipn);
            $tx = $this->cardCreditor->credit($card, $ipn, $type, $userId);
            $ipn->route = KcbIpn::ROUTE_CARD_FUNDING;
            $this->resolve($ipn, $userId);
            $ipn->markOutcome(KcbIpn::STATUS_CREDITED, "Assigned to card {$card->card_number} by finance", CardTransaction::class, $tx->id);
            return $ipn;
        });
    }

    /**
     * Take back what a credited payment applied (wrong student or wrong card) and put the
     * payment back in the queue, so finance can apply it to the right one.
     *
     * @throws \DomainException when it was not credited, or the card money has already been spent
     */
    public function undoCredit(KcbIpn $ipn, string $reason, int $userId): KcbIpn
    {
        return DB::transaction(function () use ($ipn, $reason, $userId) {
            $ipn = KcbIpn::whereKey($ipn->id)->lockForUpdate()->firstOrFail();
            if ($ipn->status !== KcbIpn::STATUS_CREDITED) {
                throw new \DomainException('Only a payment that was credited can be undone.');
            }

            $payments = FeePayment::where('transaction_code', $ipn->transaction_id)->where('import_source', 'kcb_ipn')
                ->where('status', '!=', 'reversed')->lockForUpdate()->get();
            foreach ($payments as $payment) {
                $enrollment = Enrollment::whereKey($payment->enrollment_id)->lockForUpdate()->first();
                $enrollment->amount_paid = $enrollment->amount_paid - $payment->amount;
                $enrollment->save(); // balance is recalculated in Enrollment::saving
                $payment->update(['status' => 'reversed', 'reversal_reason' => $reason, 'reversed_at' => now(), 'reversed_by' => $userId]);
            }

            if ($ipn->matched_record_type === CardTransaction::class && $tx = CardTransaction::find($ipn->matched_record_id)) {
                $card = CardAccount::findOrFail($tx->card_account_id);
                try {
                    $this->cardWallet->debit($card, $tx->wallet ?? CardWallet::CANTEEN, (float) $tx->amount, 'reversal', [
                        'reference'   => CardTransaction::generateReference('UNDO'),
                        'description' => "Top-up {$ipn->transaction_id} taken back: {$reason}",
                        'metadata'    => ['kcb_ipn_id' => $ipn->id, 'undoes_transaction_id' => $tx->id],
                    ], $userId);
                } catch (InsufficientWalletBalance) {
                    throw new \DomainException("Card {$card->card_number} no longer has KES " . number_format($tx->amount, 2)
                        . " in its {$tx->wallet} wallet (it has been spent). Correct it with a balance adjustment instead.");
                }
            }

            $ipn->resolved_by = $userId;
            $ipn->resolved_at = now();
            $ipn->suggestions = $this->suggestionsFor($ipn);
            $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, 'Reversed by finance: ' . Str::limit($reason, 200));
            return $ipn;
        });
    }

    public function ignore(KcbIpn $ipn, string $reason, int $userId): KcbIpn
    {
        return DB::transaction(function () use ($ipn, $reason, $userId) {
            $ipn = $this->lockForResolution($ipn);
            $this->resolve($ipn, $userId);
            $ipn->markOutcome(KcbIpn::STATUS_IGNORED, $reason);
            return $ipn;
        });
    }

    // ------------------------------------------------------------------
    // Routing per till
    // ------------------------------------------------------------------

    private function routeSchoolFees(KcbIpn $ipn): KcbIpn
    {
        $ipn->route = KcbIpn::ROUTE_SCHOOL_FEES;

        if ($reason = $this->alreadyRecorded($ipn)) {
            $ipn->markOutcome(KcbIpn::STATUS_DUPLICATE, $reason);
            return $ipn;
        }

        $students = $this->studentsByReference($ipn->account_reference);

        if ($students->count() === 1) {
            $student = $students->first();
            try {
                $payments = $this->feeAllocator->allocate($student, $ipn, null, $this->enrollmentByReference($ipn->account_reference));
            } catch (\DomainException $e) {
                $ipn->suggestions = [$this->studentSuggestion($student, 'Account number matches')];
                $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, $e->getMessage());
                return $ipn;
            }
            $ipn->markOutcome(KcbIpn::STATUS_CREDITED, "Fees for {$student->student_number}", FeePayment::class, $payments->first()->id);
            return $ipn;
        }

        $ipn->suggestions = $this->suggestionsFor($ipn);
        $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, match (true) {
            $students->count() > 1        => 'Account number matches more than one student.',
            $ipn->account_reference === null => 'No account number was entered.',
            default                       => "No student with number \"{$ipn->account_reference}\".",
        });
        return $ipn;
    }

    private function routeCafeteria(KcbIpn $ipn): KcbIpn
    {
        $ipn->route = KcbIpn::ROUTE_CARD_FUNDING;

        if ($reason = $this->alreadyRecorded($ipn)) {
            $ipn->markOutcome(KcbIpn::STATUS_DUPLICATE, $reason);
            return $ipn;
        }

        [$admission, $type] = $this->parseAdmission($ipn->account_reference);
        $student = $admission ? HighSchoolStudent::where('admission_number', $admission)->first() : null;
        $card    = $student?->cardAccount;

        if ($card && $card->is_active && !$card->is_blocked) {
            $tx = $this->cardCreditor->credit($card, $ipn, $type);
            $ipn->markOutcome(KcbIpn::STATUS_CREDITED, "Card {$card->card_number} ({$student->admission_number})", CardTransaction::class, $tx->id);
            return $ipn;
        }

        $ipn->suggestions = $this->suggestionsFor($ipn);
        $ipn->markOutcome(KcbIpn::STATUS_UNALLOCATED, match (true) {
            $card !== null                         => "Card for admission {$admission} is inactive or blocked.",
            $student !== null                      => "Admission {$admission} has no card.",
            $admission !== null                    => "No high-school student with admission {$admission}.",
            $this->studentsByReference($ipn->account_reference)->isNotEmpty()
                                                   => 'A college student number was paid into the cafeteria till.',
            $ipn->account_reference === null       => 'No account number was entered.',
            default                                => "Unrecognised account \"{$ipn->account_reference}\".",
        });
        return $ipn;
    }

    // ------------------------------------------------------------------
    // Matching helpers
    // ------------------------------------------------------------------

    /**
     * A sale, pre-order, application or event paid by STK push. Those flows
     * complete the order from their own STK callback, so the IPN is only linked.
     */
    private function findStkOrder(KcbIpn $ipn)
    {
        if (!$ipn->account_reference) {
            return null;
        }

        $candidates = array_unique(array_filter([
            $ipn->business_key,
            $ipn->till_number . '-' . $ipn->account_reference,
            $ipn->till_number . '#' . $ipn->account_reference,
            $ipn->account_reference,
        ]));

        return KcbBuniTransaction::whereIn('invoice_number', $candidates)
                ->where('transaction_type', '!=', 'school_fees')
                ->first()
            ?? PreOrder::whereIn('kcb_invoice_number', $candidates)->first();
    }

    /** The wallet letter typed after the till ("T1255" => "T"), or null. */
    public function fundingTypeOf(KcbIpn $ipn): ?string
    {
        return $this->parseAdmission($ipn->account_reference)[1];
    }

    /**
     * "T1255" => ["1255", "T"], "c 812" => ["812", "C"], "1255" => ["1255", null].
     * Admission numbers are 3-4 digits; 6-digit refs are POS sales.
     */
    private function parseAdmission(?string $reference): array
    {
        if (!$reference) {
            return [null, null];
        }
        // Whole reference must be the admission number, so "C123/2026" (a college number) never matches
        if (preg_match('/^([TC])?\s*0*(\d{1,5})$/i', trim($reference), $m)) {
            return [$m[2], $m[1] !== '' ? strtoupper($m[1]) : null];
        }
        return [null, null];
    }

    /**
     * Students whose CURRENT number (student number or one of their course enrollment numbers)
     * is the account typed. Old legacy codes from imports are not used here: some point at a
     * different person, so they are only offered to finance as suggestions.
     */
    private function studentsByReference(?string $reference)
    {
        if (!$reference) {
            return collect();
        }

        // Payers drop or change separators: "COS 822 2026", "cos8222026", "ICT//985/2026"
        $alnum = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $reference));
        $byNumber = fn ($q, $col) => $q->where($col, $reference)
            ->when(strlen($alnum) >= 5, fn ($w) => $w->orWhereRaw("UPPER(REGEXP_REPLACE($col, '[^A-Za-z0-9]', '')) = ?", [$alnum]));

        $students = Student::whereNull('merged_into_id')->where(fn ($q) => $byNumber($q, 'student_number'))->limit(3)->get();
        $viaEnrollment = Enrollment::where(fn ($q) => $byNumber($q, 'student_number'))->pluck('student_id');
        if ($viaEnrollment->isNotEmpty()) {
            $students = $students->merge(Student::whereNull('merged_into_id')->whereIn('id', $viaEnrollment)->get())->unique('id')->values();
        }

        return $students;
    }

    /** The course enrollment whose own number is the account typed, if exactly one. */
    private function enrollmentByReference(?string $reference): ?Enrollment
    {
        if (!$reference) {
            return null;
        }
        $alnum = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $reference));
        $matches = Enrollment::where(fn ($q) => $q->where('student_number', $reference)
            ->when(strlen($alnum) >= 5, fn ($w) => $w->orWhereRaw("UPPER(REGEXP_REPLACE(student_number, '[^A-Za-z0-9]', '')) = ?", [$alnum])))
            ->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function studentsByLegacyCode(?string $reference)
    {
        return $reference
            ? Student::whereNull('merged_into_id')->where('legacy_student_code', $reference)->limit(3)->get()
            : collect();
    }

    /**
     * Candidates shown to finance. Never applied automatically.
     */
    public function suggestionsFor(KcbIpn $ipn): array
    {
        $out = [];

        foreach ($this->studentsByReference($ipn->account_reference) as $s) {
            $out[] = $this->studentSuggestion($s, 'Account number matches');
        }
        foreach ($this->studentsByLegacyCode($ipn->account_reference) as $s) {
            $out[] = $this->studentSuggestion($s, 'Matches an OLD number from the import; check it is the same person');
        }

        [$admission] = $this->parseAdmission($ipn->account_reference);
        if ($admission && $hs = HighSchoolStudent::where('admission_number', $admission)->first()) {
            if ($hs->cardAccount) {
                $out[] = $this->cardSuggestion($hs->cardAccount, $hs, 'Admission number matches');
            }
        }

        $phone = substr(preg_replace('/\D/', '', (string) $ipn->debit_msisdn), -9);
        if (strlen($phone) === 9) {
            $match = "RIGHT(REGEXP_REPLACE(%s, '[^0-9]', ''), 9) = ?";
            $students = Student::where(function ($q) use ($match, $phone) {
                foreach (['phone', 'next_of_kin_phone', 'emergency_contact_phone', 'emergency_contact_phone_alt'] as $col) {
                    $q->orWhereRaw(sprintf($match, $col), [$phone]);
                }
            })->limit(5)->get();
            foreach ($students as $s) {
                $out[] = $this->studentSuggestion($s, 'Payer phone matches a phone on this student');
            }

            $hsStudents = HighSchoolStudent::whereRaw(sprintf($match, 'parent_phone'), [$phone])->with('cardAccount')->limit(5)->get();
            foreach ($hsStudents as $hs) {
                if ($hs->cardAccount) {
                    $out[] = $this->cardSuggestion($hs->cardAccount, $hs, 'Payer phone matches parent phone');
                }
            }
        }

        // One entry per target, keeping the strongest reason
        return array_values(collect($out)->unique(fn ($s) => $s['type'] . ':' . $s['id'])->all());
    }

    private function studentSuggestion(Student $s, string $why): array
    {
        return ['type' => 'student', 'id' => $s->id, 'label' => "{$s->full_name} ({$s->student_number})", 'why' => $why];
    }

    private function cardSuggestion(CardAccount $card, HighSchoolStudent $hs, string $why): array
    {
        return ['type' => 'card', 'id' => $card->id, 'label' => "{$hs->full_name} (adm {$hs->admission_number}, card {$card->card_number})", 'why' => $why];
    }

    private function guessRoute(KcbIpn $ipn): string
    {
        return match ($ipn->till_number) {
            config('kcb.tills.school_fees') => KcbIpn::ROUTE_SCHOOL_FEES,
            config('kcb.tills.cafeteria')   => KcbIpn::ROUTE_CARD_FUNDING,
            default                         => KcbIpn::ROUTE_UNKNOWN,
        };
    }

    // ------------------------------------------------------------------
    // Source verification
    // ------------------------------------------------------------------

    private function checkSignature(?string $rawBody, ?string $signature): string
    {
        $key = config('kcb.ipn.public_key');
        $publicKey = $key ? @openssl_pkey_get_public($key) : false;

        if (!$publicKey) {
            return 'not_configured';
        }
        if (!$signature || $rawBody === null) {
            return 'missing';
        }

        $ok = openssl_verify($rawBody, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
        return $ok ? 'verified' : 'invalid';
    }

    private function sourceProblem(?string $ip, string $signatureStatus): ?string
    {
        if ($signatureStatus === 'invalid') {
            return 'Signature check failed.';
        }
        if (config('kcb.ipn.require_signature') && $signatureStatus !== 'verified') {
            return 'Signature required but ' . str_replace('_', ' ', $signatureStatus) . '.';
        }

        $allowed = config('kcb.ipn.allowed_ips', []);
        if ($allowed && $ip !== null && !in_array($ip, $allowed, true)) {
            return "Sent from an unknown IP address ({$ip}).";
        }

        return null;
    }

    private function lockForResolution(KcbIpn $ipn): KcbIpn
    {
        $ipn = KcbIpn::whereKey($ipn->id)->lockForUpdate()->firstOrFail();
        if (!$ipn->needsAttention()) {
            throw new \DomainException('This payment has already been resolved.');
        }
        return $ipn;
    }

    private function resolve(KcbIpn $ipn, int $userId): void
    {
        $ipn->resolved_by = $userId;
        $ipn->resolved_at = now();
    }
}
