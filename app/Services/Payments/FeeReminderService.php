<?php

namespace App\Services\Payments;

use App\Models\Enrollment;
use App\Services\SmsService;
use App\Services\Students\EnrollmentCreator;
use App\Services\Terms\TermLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fee balance reminders by SMS (decided 2026-10-28):
 * - only balances that can be trusted: billed per term by the new system, or an old
 *   whole-course balance that accounts has checked and confirmed;
 * - sent to the student and the parent/guardian (next of kin) phone;
 * - bulk sends skip anyone reminded in the last 7 days; finance can still text one by hand.
 */
class FeeReminderService
{
    public const MIN_DAYS_BETWEEN = 7;
    public const BATCH = 50;

    public function __construct(private SmsService $sms, private TermLedger $ledger)
    {
    }

    /** Open enrollments with a balance, any trust level. */
    public function owing()
    {
        return Enrollment::query()
            ->whereIn('enrollments.status', EnrollmentCreator::OPEN_STATUSES)
            ->whereRaw('enrollments.total_fees - enrollments.amount_paid > 0')
            ->whereHas('student', fn ($q) => $q->whereNull('merged_into_id'));
    }

    /** Balances that may be reminded about: per-term billing, or confirmed by accounts. */
    public function remindable()
    {
        return $this->owing()->where(fn ($q) => $q->where('billing_mode', 'term')->orWhereNotNull('balance_confirmed_at'));
    }

    /** Old whole-course balances still waiting for accounts to confirm them. */
    public function unconfirmed()
    {
        return $this->owing()->where(fn ($q) => $q->whereNull('billing_mode')->orWhere('billing_mode', '!=', 'term'))->whereNull('balance_confirmed_at');
    }

    /** Remindable and not reminded in the last week: what a bulk send works through. */
    public function dueForReminder()
    {
        return $this->remindable()->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('fee_reminders')
            ->whereColumn('fee_reminders.enrollment_id', 'enrollments.id')
            ->where('fee_reminders.created_at', '>=', now()->subDays(self::MIN_DAYS_BETWEEN)));
    }

    public function isRemindable(Enrollment $e): bool
    {
        return $e->billing_mode === 'term' || $e->balance_confirmed_at !== null;
    }

    /** The phones a reminder goes to: the student and the parent/guardian, without repeats. */
    public function phonesFor(Enrollment $e): array
    {
        $student = $e->student;
        $phones = [];
        foreach ([$student?->phone, $student?->next_of_kin_phone] as $phone) {
            $digits = preg_replace('/\D/', '', (string) $phone);
            if (strlen($digits) < 9) {
                continue;
            }
            $key = substr($digits, -9); // 07.. / 2547.. / +2547.. are the same line
            $phones[$key] ??= trim((string) $phone);
        }

        return array_values($phones);
    }

    public function message(Enrollment $e): string
    {
        $student = $e->student;
        $balance = round((float) $e->total_fees - (float) $e->amount_paid, 2);
        $till = config('kcb.tills.school_fees', '7664166');
        $paybill = config('kcb.paybill', '522533');

        $termText = '';
        if ($e->billing_mode === 'term') {
            $current = $this->ledger->forEnrollment($e)->filter(fn ($row) => $row['balance'] > 0)->last();
            if ($current && $current['registration']) {
                $termText = " (Term {$current['term']}, ends " . $current['registration']->ends_on?->format('d M') . ')';
            }
        }

        return "KTVTC fees reminder: {$student->first_name} {$student->last_name} ({$e->student_number}) has a balance of KES "
            . number_format($balance) . " for {$e->course_name}{$termText}.\n"
            . "Pay via M-Pesa Paybill {$paybill}, Account {$till}#{$e->student_number}.\n"
            . 'Already paid? Please ignore. Finance: ' . config('kcb.finance_phone');
    }

    /**
     * Text the reminder. $force skips the once-a-week rule (finance texting one student by hand).
     * @return array{ok: bool, message: string}
     */
    public function send(Enrollment $e, ?int $userId, bool $force = false): array
    {
        $e->loadMissing('student');
        $name = trim(($e->student->first_name ?? '') . ' ' . ($e->student->last_name ?? '')) ?: $e->student_name;
        $balance = round((float) $e->total_fees - (float) $e->amount_paid, 2);

        if ($balance <= 0) {
            return ['ok' => false, 'message' => "{$name} has no balance."];
        }
        if (!$this->isRemindable($e)) {
            return ['ok' => false, 'message' => "{$name}'s balance is an old whole-course figure: confirm it before sending reminders."];
        }
        if (!$force && $last = $this->lastReminder($e)) {
            if ($last->created_at->gt(now()->subDays(self::MIN_DAYS_BETWEEN))) {
                return ['ok' => false, 'message' => "{$name} was reminded on {$last->created_at->format('d M')}."];
            }
        }
        $phones = $this->phonesFor($e);
        if (!$phones) {
            return ['ok' => false, 'message' => "{$name} has no student or parent phone number."];
        }

        $message = $this->message($e);
        $sent = 0;
        foreach ($phones as $phone) {
            $result = rescue(fn () => $this->sms->sendSingleSms($phone, $message), ['success' => false], false);
            if ($result['success'] ?? false) {
                $sent++;
            } else {
                Log::warning('Fee reminder SMS failed', ['enrollment_id' => $e->id, 'phone' => $phone, 'error' => $result['message'] ?? null]);
            }
        }
        if ($sent === 0) {
            return ['ok' => false, 'message' => "The SMS to {$name} failed. Try again later."];
        }

        DB::table('fee_reminders')->insert([
            'student_id' => $e->student_id,
            'enrollment_id' => $e->id,
            'balance' => $balance,
            'phones' => json_encode($phones),
            'message' => $message,
            'sent_count' => $sent,
            'sent_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['ok' => true, 'message' => "Reminder sent to {$name} (" . implode(', ', $phones) . ').'];
    }

    public function lastReminder(Enrollment $e): ?object
    {
        $row = DB::table('fee_reminders')->where('enrollment_id', $e->id)->latest('id')->first();
        if ($row) {
            $row->created_at = \Illuminate\Support\Carbon::parse($row->created_at);
        }

        return $row;
    }

    /** Accounts has checked an old whole-course balance against receipts: it may now be reminded. */
    public function confirmBalance(Enrollment $e, int $userId): void
    {
        $e->forceFill(['balance_confirmed_at' => now(), 'balance_confirmed_by' => $userId])->save();
    }
}
