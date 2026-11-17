<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Student cards report, transport and canteen wallets kept apart.
 *
 * Every card transaction stores the wallet balance before and after it, so the change it made
 * (after - before) is exact whatever its type or sign. A wallet's balance at any moment is
 * today's balance minus everything that changed it since; that gives opening and closing
 * balances for any period and a reconciliation: opening + in - out = closing.
 */
class CardsReport
{
    public const WALLETS = ['transport' => 'Transport', 'canteen' => 'Canteen'];

    public const TYPES = [
        'funding' => 'Top-ups from parents',
        'purchase' => 'Canteen purchases',
        'disbursement' => 'Fares paid out (incl. withdrawal fee)',
        'transfer' => 'Moved between wallets',
        'adjustment' => 'Manual adjustments',
        'reversal' => 'Reversals and corrections',
        'refund' => 'Refunds',
    ];

    /** A card counts as inactive when not used for this many days. */
    public const INACTIVE_DAYS = 14;
    /** Default "low balance" when a card has no threshold of its own. */
    public const LOW_BALANCE = 50;

    public function build(Carbon $from, Carbon $to, ?string $class = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = collect(CarbonPeriod::create($from->copy(), $to->copy()->startOfDay()))->map->toDateString()->values();

        $cards = $this->cards($class);
        $cardIds = $cards->pluck('id');
        $tx = $this->transactions($from, $to, $cardIds);
        $after = $this->netSince($to, $cardIds);        // changes after the period, per wallet
        $inPeriod = $tx->groupBy('wallet')->map(fn ($r) => $r->sum('delta'));
        // Balances were split into the two wallets without transactions; before the last card was split
        // a per-wallet opening balance cannot be worked out.
        $splitAt = DB::table('card_accounts')->max('wallets_split_at');
        $openingKnown = !$splitAt || $from->gte(Carbon::parse($splitAt));
        $closingKnown = !$splitAt || $to->gte(Carbon::parse($splitAt));

        $wallets = collect(self::WALLETS)->map(function ($label, $w) use ($tx, $cards, $after, $inPeriod, $days, $openingKnown, $closingKnown) {
            $rows = $tx->where('wallet', $w);
            $closing = round($cards->sum($w . '_balance') - ($after[$w] ?? 0), 2);
            $opening = round($closing - ($inPeriod[$w] ?? 0), 2);
            $lines = $rows->groupBy('transaction_type')->map(fn ($r, $type) => [
                'type' => $type,
                'label' => self::TYPES[$type] ?? ucfirst($type),
                'count' => $r->count(),
                'in' => round($r->where('delta', '>', 0)->sum('delta'), 2),
                'out' => round(-$r->where('delta', '<', 0)->sum('delta'), 2),
            ])->sortBy(fn ($l) => array_search($l['type'], array_keys(self::TYPES)))->values();

            $topups = $rows->where('transaction_type', 'funding')->where('delta', '>', 0);
            $spent = $w === 'canteen' ? $rows->where('transaction_type', 'purchase') : $rows->where('transaction_type', 'disbursement');

            return [
                'key' => $w,
                'label' => $label,
                'opening' => $openingKnown ? $opening : null,
                'closing' => $closingKnown ? $closing : null,
                'in' => round($lines->sum('in'), 2),
                'out' => round($lines->sum('out'), 2),
                'lines' => $lines,
                'topups' => round($topups->sum('delta'), 2),
                'topupCount' => $topups->count(),
                'spent' => round(-$spent->sum('delta'), 2),
                'spentCount' => $spent->count(),
                'daily' => $days->map(fn ($d) => [
                    'date' => $d,
                    'in' => round($topups->where('date', $d)->sum('delta'), 2),
                    'out' => round(-$spent->where('date', $d)->sum('delta'), 2),
                ]),
                'held' => round($cards->sum($w . '_balance'), 2),
                'cardsWithMoney' => $cards->where($w . '_balance', '>', 0)->count(),
            ];
        });

        $fares = $this->fares($from, $to, $cardIds);

        return [
            'from' => $from, 'to' => $to, 'days' => $days, 'single' => $days->count() === 1,
            'class' => $class,
            'classes' => $this->classes(),
            'splitAt' => $splitAt ? Carbon::parse($splitAt) : null,
            'openingKnown' => $openingKnown,
            'wallets' => $wallets,
            'fares' => $fares,
            'topupSources' => $this->topupSources($tx),
            'canteenWhere' => $this->canteenWhere($tx),
            'byClass' => $this->byClass($tx, $cards),
            'cards' => $cards,
            'cardCount' => $cards->count(),
            'blocked' => $cards->where('is_blocked', true)->count(),
            'low' => $cards->filter(fn ($c) => $c->canteen_balance < $c->low_at)->sortBy('canteen_balance')->values(),
            'inactive' => $cards->filter(fn ($c) => !$c->last_used_at || Carbon::parse($c->last_used_at)->lt(now()->subDays(self::INACTIVE_DAYS)))
                ->sortByDesc(fn ($c) => $c->transport_balance + $c->canteen_balance)->values(),
            'topHolders' => $cards->sortByDesc(fn ($c) => $c->transport_balance + $c->canteen_balance)->take(10)->values(),
            'activeCards' => $tx->pluck('card_account_id')->unique()->count(),
            'months' => $this->monthlyTrend($to, $cardIds),
        ];
    }

    // ------------------------------------------------------------------ data

    /** Cards with the student's details. */
    public function cards(?string $class = null): Collection
    {
        $lastSpent = DB::table('card_transactions')->whereIn('transaction_type', ['purchase', 'disbursement'])->where('status', 'completed')
            ->groupBy('card_account_id')->selectRaw('card_account_id, MAX(created_at) last_spent');
        $lastTopup = DB::table('card_transactions')->where('transaction_type', 'funding')->where('status', 'completed')
            ->groupBy('card_account_id')->selectRaw('card_account_id, MAX(created_at) last_topup');

        return DB::table('card_accounts as c')
            ->leftJoin('high_school_students as s', 's.id', '=', 'c.high_school_student_id')
            ->leftJoinSub($lastSpent, 'ls', 'ls.card_account_id', '=', 'c.id')
            ->leftJoinSub($lastTopup, 'lt', 'lt.card_account_id', '=', 'c.id')
            ->when($class, fn ($q) => $q->where(DB::raw('COALESCE(s.class, c.student_class)'), $class))
            ->orderBy(DB::raw('COALESCE(s.full_name, c.student_name)'))
            ->get(['c.id', 'c.card_number', 'c.account_number', 'c.transport_balance', 'c.canteen_balance', 'c.is_active', 'c.is_blocked',
                'c.low_balance_threshold', 'c.high_school_student_id',
                DB::raw('COALESCE(ls.last_spent, c.last_used_at) as last_used_at'), DB::raw('COALESCE(lt.last_topup, c.last_funded_at) as last_funded_at'),
                DB::raw('COALESCE(s.full_name, c.student_name) as name'), DB::raw('COALESCE(s.admission_number, c.student_admission_number) as admission'),
                DB::raw('COALESCE(s.class, c.student_class) as class'), 's.stream', 's.parent_phone', 's.parent_name'])
            ->map(function ($c) {
                $c->transport_balance = (float) $c->transport_balance;
                $c->canteen_balance = (float) $c->canteen_balance;
                $c->is_blocked = (bool) $c->is_blocked;
                $c->low_at = (float) $c->low_balance_threshold > 0 ? (float) $c->low_balance_threshold : self::LOW_BALANCE;
                return $c;
            });
    }

    /** Transactions that changed a wallet in the period, with the exact change each made. */
    private function transactions(Carbon $from, Carbon $to, Collection $cardIds): Collection
    {
        return DB::table('card_transactions')->whereIn('card_account_id', $cardIds)
            ->whereIn('status', ['completed', 'reversed'])->whereNotNull('wallet')
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'card_account_id', 'transaction_type', 'wallet', 'amount', 'balance_before', 'balance_after', 'location', 'funding_request_id',
                'ipn_transaction_id', 'mpesa_receipt', 'processed_by', 'created_at'])
            ->map(function ($t) {
                $t->delta = round((float) $t->balance_after - (float) $t->balance_before, 2);
                $t->date = substr($t->created_at, 0, 10);
                return $t;
            })->filter(fn ($t) => abs($t->delta) > 0.0001)->values();
    }

    /** Net change per wallet after a moment (to work back from today's balances). */
    private function netSince(Carbon $after, Collection $cardIds): Collection
    {
        return DB::table('card_transactions')->whereIn('card_account_id', $cardIds)
            ->whereIn('status', ['completed', 'reversed'])->whereNotNull('wallet')->where('created_at', '>', $after)
            ->groupBy('wallet')->selectRaw('wallet, SUM(balance_after - balance_before) net')->pluck('net', 'wallet')->map(fn ($v) => (float) $v);
    }

    /** Cash fares handed out (transport wallet). */
    private function fares(Carbon $from, Carbon $to, Collection $cardIds): array
    {
        $rows = DB::table('fare_disbursements')->whereIn('card_account_id', $cardIds)->where('status', 'given')
            ->whereBetween('given_on', [$from->toDateString(), $to->toDateString()]);
        $reversed = DB::table('fare_disbursements')->whereIn('card_account_id', $cardIds)->where('status', 'reversed')
            ->whereBetween('reversed_at', [$from, $to]);
        return [
            'count' => (clone $rows)->count(),
            'students' => (clone $rows)->distinct('card_account_id')->count('card_account_id'),
            'amount' => round((float) (clone $rows)->sum('amount'), 2),
            'fees' => round((float) (clone $rows)->sum('withdrawal_fee'), 2),
            'total' => round((float) (clone $rows)->sum('total_deducted'), 2),
            'byDay' => (clone $rows)->groupBy('given_on')->selectRaw('given_on d, COUNT(*) n, SUM(amount) amount, SUM(withdrawal_fee) fees')->orderBy('given_on')->get(),
            'reversedCount' => (clone $reversed)->count(),
            'reversedTotal' => round((float) (clone $reversed)->sum('total_deducted'), 2),
        ];
    }

    private function topupSources(Collection $tx): Collection
    {
        return $tx->where('transaction_type', 'funding')->where('delta', '>', 0)
            ->groupBy(fn ($t) => $t->funding_request_id ? 'Parent paid an M-Pesa prompt' : ($t->ipn_transaction_id || $t->mpesa_receipt ? 'Parent paid to the till' : 'Recorded by staff'))
            ->map(fn ($r, $label) => ['label' => $label, 'count' => $r->count(), 'total' => round($r->sum('delta'), 2),
                'transport' => round($r->where('wallet', 'transport')->sum('delta'), 2), 'canteen' => round($r->where('wallet', 'canteen')->sum('delta'), 2)])
            ->sortByDesc('total')->values();
    }

    private function canteenWhere(Collection $tx): Collection
    {
        return $tx->where('transaction_type', 'purchase')
            ->groupBy(fn ($t) => $t->location === 'pre_order_portal' ? 'Pre-orders' : 'Cafeteria counter')
            ->map(fn ($r, $label) => ['label' => $label, 'count' => $r->count(), 'total' => round(-$r->sum('delta'), 2)])->values();
    }

    private function byClass(Collection $tx, Collection $cards): Collection
    {
        $classOf = $cards->pluck('class', 'id');
        return $cards->groupBy(fn ($c) => $c->class ?: 'No class')->map(function ($group, $class) use ($tx) {
            $rows = $tx->whereIn('card_account_id', $group->pluck('id'));
            return [
                'class' => $class,
                'cards' => $group->count(),
                'topups' => round($rows->where('transaction_type', 'funding')->where('delta', '>', 0)->sum('delta'), 2),
                'canteen' => round(-$rows->where('transaction_type', 'purchase')->sum('delta'), 2),
                'fares' => round(-$rows->where('transaction_type', 'disbursement')->sum('delta'), 2),
                'transport_held' => round($group->sum('transport_balance'), 2),
                'canteen_held' => round($group->sum('canteen_balance'), 2),
            ];
        })->sortBy('class')->values();
    }

    private function classes(): Collection
    {
        return DB::table('card_accounts as c')->leftJoin('high_school_students as s', 's.id', '=', 'c.high_school_student_id')
            ->selectRaw('DISTINCT COALESCE(s.class, c.student_class) as class')->pluck('class')->filter()->sort()->values();
    }

    private function monthlyTrend(Carbon $to, Collection $cardIds): Collection
    {
        $end = $to->copy()->endOfMonth();
        $start = $end->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $rows = $this->transactions($start, $end, $cardIds)->groupBy(fn ($t) => substr($t->date, 0, 7));
        return collect(range(0, 5))->map(function ($i) use ($start, $rows) {
            $m = $start->copy()->addMonthsNoOverflow($i);
            $r = $rows[$m->format('Y-m')] ?? collect();
            $topups = $r->where('transaction_type', 'funding')->where('delta', '>', 0);
            return [
                'label' => $m->format('M Y'),
                'transport_in' => round($topups->where('wallet', 'transport')->sum('delta'), 2),
                'canteen_in' => round($topups->where('wallet', 'canteen')->sum('delta'), 2),
                'canteen_spent' => round(-$r->where('transaction_type', 'purchase')->sum('delta'), 2),
                'fares' => round(-$r->where('transaction_type', 'disbursement')->sum('delta'), 2),
            ];
        });
    }

    // ------------------------------------------------------------------ one card

    /** One card's statement for a period, per wallet with running balances. */
    public function statement(int $cardId, Carbon $from, Carbon $to): array
    {
        $card = $this->cards()->firstWhere('id', $cardId);
        abort_unless($card, 404);
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $tx = DB::table('card_transactions')->where('card_account_id', $cardId)->whereIn('status', ['completed', 'reversed'])->whereNotNull('wallet')
            ->whereBetween('created_at', [$from, $to])->orderBy('created_at')->orderBy('id')
            ->get(['id', 'transaction_type', 'wallet', 'balance_before', 'balance_after', 'description', 'mpesa_receipt', 'reference', 'metadata', 'created_at']);
        $after = $this->netSince($to, collect([$cardId]));
        $splitAt = DB::table('card_accounts')->where('id', $cardId)->value('wallets_split_at');
        $known = !$splitAt || $from->gte(Carbon::parse($splitAt));

        $wallets = collect(self::WALLETS)->map(function ($label, $w) use ($tx, $card, $after, $known) {
            $rows = $tx->where('wallet', $w)->values();
            $closing = round($card->{$w . '_balance'} - ($after[$w] ?? 0), 2);
            $opening = round($closing - $rows->sum(fn ($t) => (float) $t->balance_after - (float) $t->balance_before), 2);
            $running = $opening;
            $lines = $rows->map(function ($t) use (&$running) {
                $delta = round((float) $t->balance_after - (float) $t->balance_before, 2);
                $running = round($running + $delta, 2);
                $meta = json_decode($t->metadata ?? '', true) ?: [];
                $items = collect($meta['items'] ?? [])->map(fn ($i) => ($i['product_name'] ?? 'Item') . ' x' . (float) ($i['quantity'] ?? 1))->implode(', ');
                return [
                    'at' => Carbon::parse($t->created_at),
                    'what' => self::TYPES[$t->transaction_type] ?? ucfirst($t->transaction_type),
                    'detail' => $items ?: ($t->mpesa_receipt ?: ($t->description ?: $t->reference)),
                    'in' => $delta > 0 ? $delta : 0,
                    'out' => $delta < 0 ? -$delta : 0,
                    'balance' => $running,
                ];
            });
            if (!$known) {
                $lines = $lines->map(fn ($l) => array_merge($l, ['balance' => null]));
            }
            return ['key' => $w, 'label' => $label, 'opening' => $known ? $opening : null, 'closing' => $closing, 'lines' => $lines,
                'in' => round($lines->sum('in'), 2), 'out' => round($lines->sum('out'), 2)];
        });

        return ['card' => $card, 'from' => $from, 'to' => $to, 'wallets' => $wallets, 'splitAt' => $splitAt ? Carbon::parse($splitAt) : null, 'known' => $known,
            'fares' => DB::table('fare_disbursements')->where('card_account_id', $cardId)->whereBetween('given_on', [$from->toDateString(), $to->toDateString()])->orderBy('given_on')->get()];
    }
}
