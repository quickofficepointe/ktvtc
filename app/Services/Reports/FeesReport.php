<?php

namespace App\Services\Reports;

use App\Services\Students\EnrollmentCreator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * School fees report.
 *
 * Money received is exact (completed fee payments). Balances follow the rule agreed with
 * accounts (2026-10-28): a balance is only "confirmed" when the student is billed per term
 * by the new system, or accounts has checked and confirmed an old whole-course balance.
 * Old imported balances nobody has confirmed are shown apart, never mixed into the totals.
 */
class FeesReport
{
    public const AGE_BUCKETS = ['0–30 days' => 30, '31–60 days' => 60, '61–90 days' => 90, 'Over 90 days' => PHP_INT_MAX];

    public function build(Carbon $from, Carbon $to, ?int $campusId = null, ?int $courseId = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = collect(CarbonPeriod::create($from->copy(), $to->copy()->startOfDay()))->map->toDateString()->values();

        $payments = $this->payments($from, $to, $campusId, $courseId);
        $total = round($payments->sum('amount'), 2);

        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days->count() - 1)->startOfDay();
        $prevTotal = round($this->payments($prevFrom, $prevTo, $campusId, $courseId)->sum('amount'), 2);

        $balances = $this->balances($campusId, $courseId);

        return [
            'from' => $from, 'to' => $to, 'days' => $days, 'single' => $days->count() === 1,
            'campusId' => $campusId, 'courseId' => $courseId,
            'campusName' => $campusId ? DB::table('campuses')->where('id', $campusId)->value('name') : null,
            'courseName' => $courseId ? DB::table('courses')->where('id', $courseId)->value('name') : null,

            // ---- money received in the period
            'payments' => $payments,
            'total' => $total,
            'count' => $payments->count(),
            'students' => $payments->pluck('student_id')->filter()->unique()->count(),
            'prevFrom' => $prevFrom, 'prevTo' => $prevTo, 'prevTotal' => $prevTotal,
            'change' => $prevTotal > 0 ? round(($total - $prevTotal) / $prevTotal * 100, 1) : null,
            'byDay' => $days->map(fn ($d) => ['date' => $d, 'total' => round($payments->where('date', $d)->sum('amount'), 2), 'count' => $payments->where('date', $d)->count()]),
            'byMethod' => $this->group($payments, 'method_label'),
            'byCourse' => $this->group($payments, 'course_name')->take(15),
            'byCampus' => $this->group($payments, 'campus_name'),
            'byRecorder' => $this->group($payments, 'recorded_label'),
            'reversed' => $this->reversed($from, $to, $campusId, $courseId),
            'adjustments' => $this->adjustments($from, $to, $campusId, $courseId),
            'unmatched' => $this->unmatched(),

            // ---- balances right now
            'balances' => $balances,
            'terms' => $this->terms($balances['ledger'], $campusId, $courseId),
            'months' => $this->monthlyTrend($to, $campusId, $courseId),
        ];
    }

    // ------------------------------------------------------------------ money received

    private function scoped($query, ?int $campusId, ?int $courseId, string $enrollmentAlias = 'e')
    {
        return $query
            ->when($campusId, fn ($q) => $q->where("{$enrollmentAlias}.campus_id", $campusId))
            ->when($courseId, fn ($q) => $q->where("{$enrollmentAlias}.course_id", $courseId));
    }

    public function payments(Carbon $from, Carbon $to, ?int $campusId = null, ?int $courseId = null): Collection
    {
        $q = DB::table('fee_payments as p')
            ->leftJoin('enrollments as e', 'e.id', '=', 'p.enrollment_id')
            ->leftJoin('students as s', 's.id', '=', 'p.student_id')
            ->leftJoin('campuses as c', 'c.id', '=', 'e.campus_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.recorded_by')
            ->whereNull('p.deleted_at')->where('p.status', 'completed')
            ->whereBetween('p.payment_date', [$from, $to]);

        return $this->scoped($q, $campusId, $courseId)
            ->orderBy('p.payment_date')
            ->get(['p.id', 'p.student_id', 'p.enrollment_id', 'p.amount', 'p.payment_date', 'p.receipt_number', 'p.payment_method', 'p.transaction_code',
                'p.payer_name', 'p.kcb_transaction_id', 's.student_number', 's.first_name', 's.last_name', 'e.course_name', 'c.name as campus_name', 'u.name as recorder'])
            ->map(function ($p) {
                $p->amount = (float) $p->amount;
                $p->date = Carbon::parse($p->payment_date)->toDateString();
                $p->student_name = trim("{$p->first_name} {$p->last_name}");
                $p->course_name = $p->course_name ?: 'No course';
                $p->campus_name = $p->campus_name ?: 'Campus not set';
                $p->method_label = match ($p->payment_method) {
                    'kcb' => 'KCB (M-Pesa to till)',
                    'mpesa' => 'M-Pesa (recorded by hand)',
                    'cash' => 'Cash',
                    'bank', 'bank_transfer', 'bank_deposit' => 'Bank',
                    'cheque' => 'Cheque',
                    default => ucfirst(str_replace('_', ' ', (string) $p->payment_method)) ?: 'Other',
                };
                $p->recorded_label = $p->recorder ?: ($p->payment_method === 'kcb' ? 'Automatic (KCB)' : 'Unknown');
                return $p;
            });
    }

    private function group(Collection $payments, string $key): Collection
    {
        $total = $payments->sum('amount') ?: 1;
        return $payments->groupBy($key)->map(fn ($rows, $label) => [
            'label' => $label,
            'count' => $rows->count(),
            'total' => round($rows->sum('amount'), 2),
            'share' => round($rows->sum('amount') / $total * 100, 1),
        ])->sortByDesc('total')->values();
    }

    private function reversed(Carbon $from, Carbon $to, ?int $campusId, ?int $courseId): array
    {
        $q = DB::table('fee_payments as p')->leftJoin('enrollments as e', 'e.id', '=', 'p.enrollment_id')
            ->whereNull('p.deleted_at')->whereIn('p.status', ['reversed', 'refunded', 'cancelled'])->whereBetween('p.payment_date', [$from, $to]);
        $q = $this->scoped($q, $campusId, $courseId);
        return ['count' => (clone $q)->count(), 'total' => round((float) $q->sum('p.amount'), 2)];
    }

    private function adjustments(Carbon $from, Carbon $to, ?int $campusId, ?int $courseId): Collection
    {
        $labels = [
            'bursary' => 'Bursaries / sponsorships', 'write_off' => 'Written off', 'charge' => 'Extra charges',
            'refund' => 'Refunds', 'transfer_in' => 'Transferred in', 'transfer_out' => 'Transferred out',
        ];
        $q = DB::table('fee_adjustments as a')->leftJoin('enrollments as e', 'e.id', '=', 'a.enrollment_id')->whereBetween('a.created_at', [$from, $to]);
        return $this->scoped($q, $campusId, $courseId)
            ->selectRaw('a.type, COUNT(*) n, SUM(a.fees_change) fees, SUM(a.paid_change) paid')->groupBy('a.type')->get()
            ->map(fn ($r) => ['label' => $labels[$r->type] ?? ucfirst(str_replace('_', ' ', $r->type)), 'count' => (int) $r->n, 'fees' => round((float) $r->fees, 2), 'paid' => round((float) $r->paid, 2)]);
    }

    /** School-fee payments KCB reported that are not on any student yet. */
    private function unmatched(): array
    {
        $q = DB::table('kcb_ipns')->where('till_number', config('kcb.tills.school_fees'))
            ->whereIn('status', ['unallocated', 'quarantined'])->where(fn ($w) => $w->whereNull('is_historical')->orWhere('is_historical', false));
        return [
            'count' => (clone $q)->count(),
            'total' => round((float) (clone $q)->sum('transaction_amount'), 2),
            'rows' => (clone $q)->orderBy('transaction_date')->limit(40)
                ->get(['transaction_id', 'transaction_amount', 'transaction_date', 'account_reference', 'first_name', 'last_name', 'status', 'status_reason']),
        ];
    }

    // ------------------------------------------------------------------ balances

    /**
     * Every open enrollment that owes or is in credit, split by how far the balance can be trusted,
     * with per-term detail (payments applied to terms oldest first, as TermLedger does).
     */
    public function balances(?int $campusId = null, ?int $courseId = null): array
    {
        $q = DB::table('enrollments as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->leftJoin('campuses as c', 'c.id', '=', 'e.campus_id')
            ->whereNull('e.deleted_at')->whereNull('s.deleted_at')->whereNull('s.merged_into_id')
            ->whereIn('e.status', EnrollmentCreator::OPEN_STATUSES);
        $rows = $this->scoped($q, $campusId, $courseId)
            ->get(['e.id', 'e.student_id', 'e.course_id', 'e.course_name', 'e.campus_id', 'e.billing_mode', 'e.balance_confirmed_at', 'e.total_fees', 'e.amount_paid',
                'e.start_date', 'e.enrollment_date', 's.student_number', 's.first_name', 's.last_name', 's.phone', 's.next_of_kin_phone', 'c.name as campus_name']);

        $regs = DB::table('term_registrations')->whereIn('enrollment_id', $rows->pluck('id'))->where('status', 'registered')
            ->orderBy('term_number')->get(['enrollment_id', 'term_number', 'starts_on', 'ends_on', 'fee_amount'])->groupBy('enrollment_id');
        $lastPaid = DB::table('fee_payments')->whereIn('enrollment_id', $rows->pluck('id'))->where('status', 'completed')->whereNull('deleted_at')
            ->groupBy('enrollment_id')->selectRaw('enrollment_id, MAX(payment_date) d')->pluck('d', 'enrollment_id');

        $today = today();
        $ledger = collect();
        $list = $rows->map(function ($e) use ($regs, $lastPaid, $today, &$ledger) {
            $balance = round((float) $e->total_fees - (float) $e->amount_paid, 2);
            $trusted = $e->billing_mode === 'term' || $e->balance_confirmed_at !== null;

            // Apply what was paid to the registered terms, oldest first
            $remaining = (float) $e->amount_paid;
            $oldestUnpaid = null;
            foreach ($regs[$e->id] ?? [] as $r) {
                $fee = round((float) $r->fee_amount, 2);
                $paid = round(min($fee, max(0, $remaining)), 2);
                $remaining -= $paid;
                $ledger->push(['enrollment_id' => $e->id, 'trusted' => $trusted, 'starts_on' => $r->starts_on, 'term' => $r->term_number, 'fee' => $fee, 'paid' => $paid, 'balance' => round($fee - $paid, 2)]);
                if ($fee - $paid > 0.009 && !$oldestUnpaid) {
                    $oldestUnpaid = $r->starts_on;
                }
            }

            $owingSince = $oldestUnpaid ?? $e->balance_confirmed_at ?? $e->start_date ?? $e->enrollment_date;
            $age = $owingSince ? max(0, (int) Carbon::parse($owingSince)->diffInDays($today, false)) : null;

            return (object) [
                'enrollment_id' => $e->id,
                'student_id' => $e->student_id,
                'student_number' => $e->student_number,
                'name' => trim("{$e->first_name} {$e->last_name}"),
                'phone' => $e->phone ?: $e->next_of_kin_phone,
                'course_id' => $e->course_id,
                'course' => $e->course_name ?: 'No course',
                'campus' => $e->campus_name ?: 'Campus not set',
                'billing' => $e->billing_mode === 'term' ? 'Per term' : 'Whole course',
                'trusted' => $trusted,
                'fees' => round((float) $e->total_fees, 2),
                'paid' => round((float) $e->amount_paid, 2),
                'balance' => $balance,
                'age' => $age,
                'bucket' => $age === null ? 'Unknown' : collect(self::AGE_BUCKETS)->search(fn ($max) => $age <= $max),
                'last_paid' => $lastPaid[$e->id] ?? null,
            ];
        });

        $owing = $list->where('balance', '>', 0);
        $confirmed = $owing->where('trusted', true)->sortByDesc('balance')->values();
        $unconfirmed = $owing->where('trusted', false);
        $credit = $list->where('balance', '<', 0);

        $courses = $list->where('trusted', true)->groupBy('course')->map(fn ($rows, $course) => [
            'course' => $course,
            'students' => $rows->count(),
            'fees' => round($rows->sum('fees'), 2),
            'paid' => round($rows->sum('paid'), 2),
            'owed' => round($rows->where('balance', '>', 0)->sum('balance'), 2),
            'owing' => $rows->where('balance', '>', 0)->count(),
            'rate' => $rows->sum('fees') > 0 ? round(min($rows->sum('paid'), $rows->sum('fees')) / $rows->sum('fees') * 100, 1) : null,
        ])->sortByDesc('owed')->values();

        return [
            'confirmed' => $confirmed,
            'confirmedTotal' => round($confirmed->sum('balance'), 2),
            'unconfirmedCount' => $unconfirmed->count(),
            'unconfirmedTotal' => round($unconfirmed->sum('balance'), 2),
            'creditCount' => $credit->count(),
            'creditTotal' => round(abs($credit->sum('balance')), 2),
            'openCount' => $list->count(),
            'ageing' => collect(array_keys(self::AGE_BUCKETS))->push('Unknown')->map(fn ($b) => [
                'bucket' => $b, 'count' => $confirmed->where('bucket', $b)->count(), 'total' => round($confirmed->where('bucket', $b)->sum('balance'), 2),
            ])->filter(fn ($a) => $a['bucket'] !== 'Unknown' || $a['count'] > 0)->values(),
            'byCourse' => $courses,
            'ledger' => $ledger,
        ];
    }

    // ------------------------------------------------------------------ terms and trends

    /**
     * Calendar terms (quarters): money collected in the term, and for confirmed balances
     * what was billed for terms starting in it, how much of that is paid, and what is still owed.
     */
    private function terms(Collection $ledger, ?int $campusId, ?int $courseId): Collection
    {
        $terms = DB::table('academic_terms')->whereNull('deleted_at')->where('start_date', '<=', today()->addMonths(3))
            ->orderByDesc('start_date')->get(['name', 'start_date', 'end_date', 'fee_due_date'])
            ->unique(fn ($t) => $t->start_date . $t->end_date)->take(6)->values();
        $trusted = $ledger->where('trusted', true);

        return $terms->map(function ($t) use ($trusted, $campusId, $courseId) {
            $in = $trusted->filter(fn ($r) => $r['starts_on'] >= $t->start_date && $r['starts_on'] <= $t->end_date);
            $collected = $this->payments(Carbon::parse($t->start_date), Carbon::parse($t->end_date), $campusId, $courseId)->sum('amount');
            $billed = $in->sum('fee');
            return [
                'name' => $t->name,
                'from' => Carbon::parse($t->start_date),
                'to' => Carbon::parse($t->end_date),
                'current' => today()->between(Carbon::parse($t->start_date), Carbon::parse($t->end_date)),
                'collected' => round($collected, 2),
                'registered' => $in->pluck('enrollment_id')->unique()->count(),
                'billed' => round($billed, 2),
                'paid' => round($in->sum('paid'), 2),
                'balance' => round($in->sum('balance'), 2),
                'rate' => $billed > 0 ? round($in->sum('paid') / $billed * 100, 1) : null,
            ];
        });
    }

    private function monthlyTrend(Carbon $to, ?int $campusId, ?int $courseId): Collection
    {
        $end = $to->copy()->endOfMonth();
        $start = $end->copy()->subMonthsNoOverflow(11)->startOfMonth();
        $byMonth = $this->payments($start, $end, $campusId, $courseId)->groupBy(fn ($p) => substr($p->date, 0, 7));
        return collect(range(0, 11))->map(function ($i) use ($start, $byMonth) {
            $m = $start->copy()->addMonthsNoOverflow($i);
            $rows = $byMonth[$m->format('Y-m')] ?? collect();
            return ['label' => $m->format('M y'), 'total' => round($rows->sum('amount'), 2), 'count' => $rows->count()];
        });
    }

    // ------------------------------------------------------------------ one student

    /** Everything for one student's fee statement. */
    public function statement(int $studentId): array
    {
        $student = DB::table('students as s')->leftJoin('campuses as c', 'c.id', '=', 's.campus_id')->where('s.id', $studentId)
            ->first(['s.*', 'c.name as campus_name']);
        abort_unless($student, 404);

        $enrollments = DB::table('enrollments')->where('student_id', $studentId)->whereNull('deleted_at')->orderBy('enrollment_date')->get()
            ->map(function ($e) {
                $regs = DB::table('term_registrations')->where('enrollment_id', $e->id)->where('status', 'registered')->orderBy('term_number')->get();
                $remaining = (float) $e->amount_paid;
                $terms = $regs->map(function ($r) use (&$remaining) {
                    $fee = round((float) $r->fee_amount, 2);
                    $paid = round(min($fee, max(0, $remaining)), 2);
                    $remaining -= $paid;
                    return ['term' => $r->term_number, 'from' => $r->starts_on, 'to' => $r->ends_on, 'fee' => $fee, 'paid' => $paid, 'balance' => round($fee - $paid, 2)];
                });
                $e->terms = $terms;
                $e->balance_now = round((float) $e->total_fees - (float) $e->amount_paid, 2);
                $e->trusted = $e->billing_mode === 'term' || $e->balance_confirmed_at !== null;
                $e->payments = DB::table('fee_payments')->where('enrollment_id', $e->id)->whereNull('deleted_at')->orderBy('payment_date')->get();
                $e->adjustments = DB::table('fee_adjustments')->where('enrollment_id', $e->id)->orderBy('created_at')->get();
                return $e;
            });

        return ['student' => $student, 'enrollments' => $enrollments];
    }
}
