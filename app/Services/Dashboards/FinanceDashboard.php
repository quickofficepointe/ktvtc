<?php

namespace App\Services\Dashboards;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbIpn;
use App\Services\Students\EnrollmentCreator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Figures for the finance dashboard.
 *
 * Two different sources are kept apart on purpose:
 * - "received" = payment records (fee_payments, KCB IPNs, card top-ups...): money that actually came in.
 * - "billed / paid / outstanding" = what the enrollment records say. Older amounts were typed in by
 *   hand (some wrongly), so they are shown as the enrollment position, not as receipts.
 */
class FinanceDashboard
{
    public const PERIODS = ['today' => 'Today', 'week' => 'This week', 'month' => 'This month', 'year' => 'This year'];

    public function build(string $period, ?int $campusId = null): array
    {
        [$from, $to] = $this->range($period);

        return [
            'period' => $period,
            'periodLabel' => self::PERIODS[$period],
            'from' => $from,
            'to' => $to,
            'received' => $this->received($from, $to),
            'fees' => $this->feePosition($campusId),
            'trend' => $this->monthlyTrend($campusId),
            'byCourse' => $this->byCourse($from, $to, $campusId),
            'byCampus' => $this->byCampus(),
            'aging' => $this->aging($campusId),
            'topBalances' => $this->topBalances($campusId),
            'terms' => $this->terms(),
            'kcb' => $this->kcbHealth($from, $to),
            'cards' => $this->cards($from, $to),
            'recent' => FeePayment::with(['student', 'enrollment'])->where('status', 'completed')
                ->orderByDesc('payment_date')->orderByDesc('id')->limit(10)->get(),
        ];
    }

    private function range(string $period): array
    {
        return match ($period) {
            'today' => [today(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfDay()],
            default => [now()->startOfMonth(), now()->endOfDay()],
        };
    }

    /** Money that came in during the period, per stream. */
    private function received(Carbon $from, Carbon $to): array
    {
        $fees = FeePayment::where('status', 'completed')->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()]);
        $streams = [
            'School fees' => (float) (clone $fees)->sum('amount'),
            'Card top-ups' => (float) DB::table('card_transactions')->where('transaction_type', 'funding')->where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])->sum('amount'),
            'Cafeteria (cash & M-Pesa)' => (float) DB::table('sales')->where('payment_status', 'paid')->whereNull('deleted_at')
                ->where(fn ($q) => $q->whereNull('payment_method')->orWhere('payment_method', '!=', 'card'))
                ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$from, $to])->sum('total_amount'),
            'Application fees' => Schema::hasTable('application_payments') ? (float) DB::table('application_payments')
                ->whereIn('status', ['completed', 'paid', 'success'])->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$from, $to])->sum('amount') : 0.0,
            'Event fees' => Schema::hasTable('event_applications') ? (float) DB::table('event_applications')
                ->whereIn('application_status', ['confirmed', 'paid', 'approved'])->whereBetween('created_at', [$from, $to])->sum('total_amount') : 0.0,
            'Library fines' => Schema::hasTable('payments') ? (float) DB::table('payments')->where('payment_type', 'fine')
                ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])->sum('amount') : 0.0,
        ];

        $byChannel = (clone $fees)->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('payment_method')->orderByDesc('total')->get()
            ->map(fn ($r) => ['label' => match ($r->payment_method) {
                'kcb' => 'M-Pesa to KCB till (automatic)', 'mpesa' => 'M-Pesa (student portal)', 'cash' => 'Cash', 'bank' => 'Bank', default => ucfirst((string) $r->payment_method),
            }, 'value' => (float) $r->total, 'note' => $r->n . ' payment' . ($r->n == 1 ? '' : 's')])->all();

        return [
            'streams' => $streams,
            'total' => array_sum($streams),
            'feeCount' => (clone $fees)->count(),
            'feeToday' => (float) FeePayment::where('status', 'completed')->whereDate('payment_date', today())->sum('amount'),
            'byChannel' => $byChannel,
        ];
    }

    private function openEnrollments(?int $campusId)
    {
        return Enrollment::query()->whereIn('status', EnrollmentCreator::OPEN_STATUSES)
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId));
    }

    /** The enrollment position: billed, paid and outstanding on open enrollments. */
    private function feePosition(?int $campusId): array
    {
        $row = $this->openEnrollments($campusId)->selectRaw('
            COUNT(*) as enrollments,
            COALESCE(SUM(total_fees), 0) as billed,
            COALESCE(SUM(LEAST(amount_paid, total_fees)), 0) as paid,
            COALESCE(SUM(GREATEST(total_fees - amount_paid, 0)), 0) as outstanding,
            COALESCE(SUM(GREATEST(amount_paid - total_fees, 0)), 0) as credit,
            SUM(CASE WHEN total_fees - amount_paid > 0 THEN 1 ELSE 0 END) as owing,
            SUM(CASE WHEN total_fees > 0 AND total_fees - amount_paid <= 0 THEN 1 ELSE 0 END) as cleared,
            SUM(CASE WHEN total_fees > 0 AND amount_paid <= 0 THEN 1 ELSE 0 END) as nothing_paid')->first();

        return [
            'enrollments' => (int) $row->enrollments,
            'billed' => (float) $row->billed,
            'paid' => (float) $row->paid,
            'outstanding' => (float) $row->outstanding,
            'credit' => (float) $row->credit,
            'owing' => (int) $row->owing,
            'cleared' => (int) $row->cleared,
            'nothingPaid' => (int) $row->nothing_paid,
            'rate' => $row->billed > 0 ? round($row->paid / $row->billed * 100, 1) : 0,
        ];
    }

    /** School fees received per month for the last 12 months, automatic (KCB) vs recorded by staff. */
    private function monthlyTrend(?int $campusId): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $rows = FeePayment::where('fee_payments.status', 'completed')->where('payment_date', '>=', $start->toDateString())
            ->when($campusId, fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('campus_id', $campusId)))
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(CASE WHEN payment_method = 'kcb' THEN amount ELSE 0 END) as auto, SUM(CASE WHEN payment_method = 'kcb' THEN 0 ELSE amount END) as manual")
            ->groupBy('ym')->get()->keyBy('ym');

        $labels = $auto = $manual = [];
        for ($m = $start->copy(); $m <= now(); $m->addMonth()) {
            $key = $m->format('Y-m');
            $labels[] = $m->format('M y');
            $auto[] = (float) ($rows[$key]->auto ?? 0);
            $manual[] = (float) ($rows[$key]->manual ?? 0);
        }

        return compact('labels', 'auto', 'manual');
    }

    /** Received in the period and outstanding now, per course. */
    private function byCourse(Carbon $from, Carbon $to, ?int $campusId): array
    {
        $received = FeePayment::where('fee_payments.status', 'completed')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->join('enrollments', 'enrollments.id', '=', 'fee_payments.enrollment_id')
            ->when($campusId, fn ($q) => $q->where('enrollments.campus_id', $campusId))
            ->selectRaw('enrollments.course_name as label, SUM(fee_payments.amount) as value, COUNT(*) as n')
            ->groupBy('enrollments.course_name')->orderByDesc('value')->limit(8)->get()
            ->map(fn ($r) => ['label' => $r->label ?: 'Unknown course', 'value' => (float) $r->value, 'note' => $r->n . ' payment' . ($r->n == 1 ? '' : 's')])->all();

        $outstanding = $this->openEnrollments($campusId)->whereRaw('total_fees - amount_paid > 0')
            ->selectRaw('course_name as label, SUM(total_fees - amount_paid) as value, COUNT(*) as n')
            ->groupBy('course_name')->orderByDesc('value')->limit(8)->get()
            ->map(fn ($r) => ['label' => $r->label ?: 'Unknown course', 'value' => (float) $r->value, 'note' => $r->n . ' student' . ($r->n == 1 ? '' : 's') . ' owing'])->all();

        return compact('received', 'outstanding');
    }

    private function byCampus(): array
    {
        $names = DB::table('campuses')->pluck('name', 'id');

        return $this->openEnrollments(null)->selectRaw('campus_id, COUNT(*) as n, SUM(total_fees) as billed,
                SUM(LEAST(amount_paid, total_fees)) as paid, SUM(GREATEST(total_fees - amount_paid, 0)) as outstanding')
            ->groupBy('campus_id')->get()
            ->map(fn ($r) => [
                'name' => $names[$r->campus_id] ?? 'No campus set',
                'n' => (int) $r->n,
                'billed' => (float) $r->billed,
                'paid' => (float) $r->paid,
                'outstanding' => (float) $r->outstanding,
                'rate' => $r->billed > 0 ? round($r->paid / $r->billed * 100, 1) : 0,
            ])->sortByDesc('billed')->values()->all();
    }

    /** Outstanding balances by how long ago the student enrolled. */
    private function aging(?int $campusId): array
    {
        $date = 'COALESCE(enrollment_date, start_date, DATE(created_at))';
        $row = $this->openEnrollments($campusId)->whereRaw('total_fees - amount_paid > 0')->selectRaw("
            SUM(CASE WHEN $date >= ? THEN total_fees - amount_paid ELSE 0 END) as m3,
            SUM(CASE WHEN $date < ? AND $date >= ? THEN total_fees - amount_paid ELSE 0 END) as m6,
            SUM(CASE WHEN $date < ? AND $date >= ? THEN total_fees - amount_paid ELSE 0 END) as m12,
            SUM(CASE WHEN $date < ? THEN total_fees - amount_paid ELSE 0 END) as older", [
                now()->subMonths(3)->toDateString(),
                now()->subMonths(3)->toDateString(), now()->subMonths(6)->toDateString(),
                now()->subMonths(6)->toDateString(), now()->subMonths(12)->toDateString(),
                now()->subMonths(12)->toDateString(),
            ])->first();

        return [
            ['label' => 'Enrolled in the last 3 months', 'value' => (float) $row->m3],
            ['label' => '3 to 6 months ago', 'value' => (float) $row->m6],
            ['label' => '6 to 12 months ago', 'value' => (float) $row->m12],
            ['label' => 'Over a year ago', 'value' => (float) $row->older],
        ];
    }

    private function topBalances(?int $campusId)
    {
        $rows = $this->openEnrollments($campusId)->whereRaw('total_fees - amount_paid > 0')
            ->with('student:id,first_name,last_name,phone,student_number')
            ->orderByRaw('total_fees - amount_paid DESC')->limit(10)
            ->get(['id', 'student_id', 'student_name', 'student_number', 'course_name', 'total_fees', 'amount_paid']);
        $lastPaid = FeePayment::where('status', 'completed')->whereIn('enrollment_id', $rows->pluck('id'))
            ->selectRaw('enrollment_id, MAX(payment_date) as last')->groupBy('enrollment_id')->pluck('last', 'enrollment_id');

        return $rows->map(fn ($e) => [
            'enrollment' => $e,
            'name' => $e->student ? trim($e->student->first_name . ' ' . $e->student->last_name) : $e->student_name,
            'phone' => $e->student->phone ?? null,
            'balance' => (float) $e->total_fees - (float) $e->amount_paid,
            'lastPaid' => $lastPaid[$e->id] ?? null,
        ]);
    }

    /** Term registrations running today. */
    private function terms(): array
    {
        if (!Schema::hasTable('term_registrations')) {
            return ['current' => 0, 'self' => 0, 'thisMonth' => 0, 'examCards' => 0, 'billed' => 0.0];
        }
        $current = DB::table('term_registrations')->where('status', '!=', 'cancelled')
            ->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today());

        return [
            'current' => (clone $current)->count(),
            'billed' => (float) (clone $current)->sum('fee_amount'),
            'examCards' => (clone $current)->whereNotNull('exam_card_downloaded_at')->count(),
            'thisMonth' => DB::table('term_registrations')->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->startOfMonth())->count(),
            'self' => DB::table('term_registrations')->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->startOfMonth())
                ->where('registration_type', 'self')->count(),
        ];
    }

    /** Is the KCB feed alive, and what is waiting for finance? */
    private function kcbHealth(Carbon $from, Carbon $to): array
    {
        $fees = config('kcb.tills.school_fees', '7664166');
        $cards = config('kcb.tills.cafeteria', '8118445');
        $last = fn ($till) => KcbIpn::where('till_number', $till)->where('is_historical', false)->max('created_at');
        $waiting = KcbIpn::awaitingFinance();

        return [
            'lastFee' => ($v = $last($fees)) ? Carbon::parse($v) : null,
            'lastCard' => ($v = $last($cards)) ? Carbon::parse($v) : null,
            'waitingCount' => (clone $waiting)->count(),
            'waitingAmount' => (float) (clone $waiting)->sum('transaction_amount'),
            'creditedCount' => KcbIpn::where('status', KcbIpn::STATUS_CREDITED)->whereBetween('created_at', [$from, $to])->count(),
            'creditedAmount' => (float) KcbIpn::where('status', KcbIpn::STATUS_CREDITED)->whereBetween('created_at', [$from, $to])->sum('transaction_amount'),
        ];
    }

    /** High-school card wallets and transport fare. */
    private function cards(Carbon $from, Carbon $to): array
    {
        $wallets = DB::table('card_accounts')->selectRaw('COALESCE(SUM(transport_balance),0) as transport, COALESCE(SUM(canteen_balance),0) as canteen, COUNT(*) as n')->first();
        $fare = Schema::hasTable('fare_disbursements')
            ? DB::table('fare_disbursements')->where('status', '!=', 'reversed')->whereBetween('given_on', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('COALESCE(SUM(amount),0) as cash, COALESCE(SUM(withdrawal_fee),0) as fees, COUNT(*) as n')->first()
            : (object) ['cash' => 0, 'fees' => 0, 'n' => 0];
        $topups = DB::table('card_transactions')->where('transaction_type', 'funding')->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])->selectRaw("wallet, SUM(amount) as total")->groupBy('wallet')->pluck('total', 'wallet');
        $spent = (float) DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])->sum('amount');

        return [
            'cards' => (int) $wallets->n,
            'transport' => (float) $wallets->transport,
            'canteen' => (float) $wallets->canteen,
            'fareCash' => (float) $fare->cash,
            'fareFees' => (float) $fare->fees,
            'fareCount' => (int) $fare->n,
            'topupTransport' => (float) ($topups['transport'] ?? 0),
            'topupCanteen' => (float) ($topups['canteen'] ?? 0) + (float) ($topups[''] ?? 0),
            'canteenSpent' => $spent,
        ];
    }
}
