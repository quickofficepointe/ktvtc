<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Everything the cafeteria report shows, for a date range (both days included).
 *
 * Every cafeteria sale becomes one "ticket", whatever way it was paid:
 *   till cash / till M-Pesa / till credit  -> sales (sale_type pos)
 *   online orders                          -> sales (sale_type online)
 *   pre-orders paid by M-Pesa              -> sales (sale_type preorder)
 *   student card at the counter            -> card_transactions (purchase, items in metadata)
 *   pre-orders paid by student card        -> pre_orders + pre_order_items
 * Only paid sales count; soft-deleted (duplicate) sales are left out.
 */
class CafeteriaReport
{
    public const CHANNELS = [
        'till_cash'      => ['Till · cash', '#16a34a'],
        'till_mpesa'     => ['Till · M-Pesa', '#2563eb'],
        'till_credit'    => ['Till · credit', '#64748b'],
        'card_counter'   => ['Student card · counter', '#b91c1c'],
        'preorder_mpesa' => ['Pre-order · M-Pesa', '#7c3aed'],
        'preorder_card'  => ['Pre-order · student card', '#db2777'],
        'online'         => ['Online order', '#d97706'],
    ];

    /** Reorder rule: buy when stock is at/below the reorder level, or lasts fewer than this many days. */
    public const DAYS_WARNING = 3;
    /** Suggested purchase covers this many days of sales. */
    public const DAYS_COVER = 7;
    /** Sales speed is measured over this many recent days. */
    public const SPEED_WINDOW = 14;

    private ?Collection $products = null;

    public function build(Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = collect(CarbonPeriod::create($from->copy(), $to->copy()->startOfDay()))->map->toDateString()->values();

        $tickets = $this->tickets($from, $to, true);
        $total = round($tickets->sum('amount'), 2);

        // Same length of time just before, for comparison
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days->count() - 1)->startOfDay();
        $prevTotal = round($this->tickets($prevFrom, $prevTo, false)->sum('amount'), 2);

        $items = $this->itemsSold($tickets);
        $byDay = $this->byDay($tickets, $days);
        $stock = $this->stock();

        return [
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'single' => $days->count() === 1,
            'total' => $total,
            'count' => $tickets->count(),
            'average' => $tickets->count() ? round($total / $tickets->count(), 2) : 0,
            'prevFrom' => $prevFrom,
            'prevTo' => $prevTo,
            'prevTotal' => $prevTotal,
            'change' => $prevTotal > 0 ? round(($total - $prevTotal) / $prevTotal * 100, 1) : null,
            'channels' => $this->channels($tickets, $days),
            'byDay' => $byDay,
            'bestDay' => $byDay->sortByDesc('total')->first(),
            'hours' => $this->hours($tickets),
            'weekdays' => $this->weekdays($byDay),
            'cashiers' => $this->cashiers($tickets),
            'items' => $items,
            'itemsTotal' => round($items->sum('revenue'), 2),
            'cost' => round($items->sum('cost'), 2),
            'profit' => round($items->whereNotNull('profit')->sum('profit'), 2),
            'costedRevenue' => round($items->whereNotNull('profit')->sum('revenue'), 2),
            'unitemised' => round($total - $items->sum('revenue'), 2),
            'topByRevenue' => $items->sortByDesc('revenue')->take(10)->values(),
            'topByQty' => $items->sortByDesc('qty')->take(10)->values(),
            'slow' => $this->slowMovers($items, $stock),
            'stock' => $stock,
            'stockValue' => round($stock->sum('value'), 2),
            'reorder' => $stock->where('reorder', true)->sortBy('days_left')->values(),
            'reorderCost' => round($stock->where('reorder', true)->sum('order_cost'), 2),
            // Out of stock but not selling lately and no reorder level: someone should decide if still needed
            'outIdle' => $stock->where('status', 'Out of stock')->where('reorder', false)->values(),
            'unpaid' => $this->unpaid($from, $to),
            'purchases' => $this->purchases($from, $to),
            'weeks' => $this->weeklyTrend($to),
            'months' => $this->monthlyTrend($to),
        ];
    }

    // ------------------------------------------------------------------ tickets

    /**
     * @return Collection<int, array{at: Carbon, date: string, channel: string, amount: float, cashier: string, items: array}>
     */
    public function tickets(Carbon $from, Carbon $to, bool $withItems): Collection
    {
        $tickets = collect();
        $users = DB::table('users')->pluck('name', 'id');

        // Till, online and M-Pesa pre-orders
        $sales = DB::table('sales')->whereNull('deleted_at')->where('payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$from, $to])
            ->get(['id', 'sale_type', 'payment_method', 'total_amount', 'cashier_id', 'created_by', DB::raw('COALESCE(sale_date, created_at) as at')]);
        $saleItems = $withItems && $sales->isNotEmpty()
            ? DB::table('sale_items')->whereIn('sale_id', $sales->pluck('id'))->get(['sale_id', 'product_id', 'product_name', 'quantity', 'final_price', 'total_price'])->groupBy('sale_id')
            : collect();

        foreach ($sales as $s) {
            $channel = match ($s->sale_type) {
                'online' => 'online',
                'preorder' => 'preorder_mpesa',
                default => match ($s->payment_method) {
                    'cash' => 'till_cash',
                    'credit' => 'till_credit',
                    default => 'till_mpesa',
                },
            };
            if ($s->payment_method === 'card') {
                continue; // card money is counted from the card transactions
            }
            $tickets->push($this->ticket($s->at, $channel, $s->total_amount, $users[$s->cashier_id ?? $s->created_by] ?? 'Unknown',
                ($saleItems[$s->id] ?? collect())->map(fn ($i) => [$i->product_id, $i->product_name, (float) $i->quantity, (float) ($i->final_price ?? $i->total_price)])->all()));
        }

        // Student card at the counter
        if (Schema::hasTable('card_transactions')) {
            $cards = DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')
                ->where(fn ($q) => $q->whereNull('location')->orWhere('location', '!=', 'pre_order_portal'))
                ->whereBetween('created_at', [$from, $to])
                ->get(['amount', 'metadata', 'processed_by', 'created_at']);
            foreach ($cards as $c) {
                $meta = json_decode($c->metadata ?? '', true) ?: [];
                $items = collect($meta['items'] ?? [])->map(fn ($i) => [$i['product_id'] ?? null, $i['product_name'] ?? 'Item', (float) ($i['quantity'] ?? 1), (float) ($i['total_price'] ?? 0)])->all();
                $tickets->push($this->ticket($c->created_at, 'card_counter', abs((float) $c->amount),
                    $meta['scanned_by_name'] ?? ($users[$c->processed_by] ?? 'Card scanner'), $withItems ? $items : []));
            }
        }

        // Pre-orders paid by student card (rejected or refunded ones drop out by status)
        $pre = DB::table('pre_orders')->where('payment_method', 'card')->whereIn('status', ['paid', 'accepted', 'ready', 'completed'])
            ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$from, $to])
            ->get(['id', 'total_amount', 'accepted_by_user_id', DB::raw('COALESCE(paid_at, created_at) as at')]);
        $preItems = $withItems && $pre->isNotEmpty()
            ? DB::table('pre_order_items')->whereIn('pre_order_id', $pre->pluck('id'))->get()->groupBy('pre_order_id')
            : collect();
        foreach ($pre as $p) {
            $tickets->push($this->ticket($p->at, 'preorder_card', $p->total_amount, $users[$p->accepted_by_user_id] ?? 'Pre-order portal',
                ($preItems[$p->id] ?? collect())->map(fn ($i) => [$i->product_id, $i->product_name, (float) $i->quantity, (float) $i->total_price])->all()));
        }

        return $tickets->sortBy('at')->values();
    }

    private function ticket($at, string $channel, $amount, string $cashier, array $items): array
    {
        $at = Carbon::parse($at);
        return ['at' => $at, 'date' => $at->toDateString(), 'channel' => $channel, 'amount' => round((float) $amount, 2), 'cashier' => $cashier, 'items' => $items];
    }

    // ------------------------------------------------------------------ sales views

    private function channels(Collection $tickets, Collection $days): Collection
    {
        return collect(self::CHANNELS)->map(function ($meta, $key) use ($tickets, $days) {
            $rows = $tickets->where('channel', $key);
            $perDay = $rows->groupBy('date')->map->sum('amount');
            return [
                'key' => $key,
                'label' => $meta[0],
                'color' => $meta[1],
                'count' => $rows->count(),
                'total' => round($rows->sum('amount'), 2),
                'days' => $days->mapWithKeys(fn ($d) => [$d => round((float) ($perDay[$d] ?? 0), 2)])->all(),
            ];
        })->filter(fn ($c) => $c['count'] > 0)->values();
    }

    private function byDay(Collection $tickets, Collection $days): Collection
    {
        $grouped = $tickets->groupBy('date');
        return $days->map(function ($d) use ($grouped) {
            $rows = $grouped[$d] ?? collect();
            $items = $this->itemsSold($rows);
            return [
                'date' => $d,
                'total' => round($rows->sum('amount'), 2),
                'count' => $rows->count(),
                'channels' => collect(self::CHANNELS)->keys()->mapWithKeys(fn ($k) => [$k => round($rows->where('channel', $k)->sum('amount'), 2)])->all(),
                'items' => $items->sortByDesc('qty')->values(),
            ];
        });
    }

    private function hours(Collection $tickets): Collection
    {
        $grouped = $tickets->groupBy(fn ($t) => (int) $t['at']->format('G'));
        $used = $grouped->keys();
        if ($used->isEmpty()) {
            return collect();
        }
        return collect(range(max(5, $used->min()), min(22, max($used->max(), 6))))->map(fn ($h) => [
            'hour' => $h,
            'label' => sprintf('%02d:00', $h),
            'count' => ($grouped[$h] ?? collect())->count(),
            'total' => round(($grouped[$h] ?? collect())->sum('amount'), 2),
        ]);
    }

    private function weekdays(Collection $byDay): Collection
    {
        $names = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $grouped = $byDay->groupBy(fn ($d) => Carbon::parse($d['date'])->dayOfWeekIso - 1);
        return collect($names)->map(function ($name, $i) use ($grouped) {
            $rows = $grouped[$i] ?? collect();
            $open = $rows->where('total', '>', 0);
            return ['day' => $name, 'average' => $open->count() ? round($open->avg('total'), 2) : 0, 'days' => $open->count()];
        });
    }

    private function cashiers(Collection $tickets): Collection
    {
        return $tickets->groupBy('cashier')->map(fn ($rows, $name) => [
            'name' => $name,
            'count' => $rows->count(),
            'total' => round($rows->sum('amount'), 2),
            'cash' => round($rows->where('channel', 'till_cash')->sum('amount'), 2),
            'mpesa' => round($rows->whereIn('channel', ['till_mpesa', 'preorder_mpesa', 'online'])->sum('amount'), 2),
            'card' => round($rows->whereIn('channel', ['card_counter', 'preorder_card'])->sum('amount'), 2),
        ])->sortByDesc('total')->values();
    }

    /** Items sold, one row per product, with cost and profit where the cost price is known. */
    private function itemsSold(Collection $tickets): Collection
    {
        $products = $this->products();
        $rows = [];
        foreach ($tickets as $t) {
            foreach ($t['items'] as [$pid, $name, $qty, $amount]) {
                $key = $pid ?: 'name:' . strtolower(trim($name));
                $rows[$key] ??= ['product_id' => $pid, 'name' => $products[$pid]->product_name ?? $name, 'qty' => 0, 'revenue' => 0, 'tickets' => 0];
                $rows[$key]['qty'] += $qty;
                $rows[$key]['revenue'] += $amount;
                $rows[$key]['tickets']++;
            }
        }

        return collect($rows)->map(function ($r) use ($products) {
            $p = $products[$r['product_id']] ?? null;
            $unitCost = $p && (float) $p->cost_price > 0 ? (float) $p->cost_price : null;
            $cost = $unitCost !== null ? round($unitCost * $r['qty'], 2) : null;
            return $r + [
                'category' => $p->category ?? '—',
                'unit' => $p->unit ?? '',
                'avg_price' => $r['qty'] > 0 ? round($r['revenue'] / $r['qty'], 2) : 0,
                'cost' => $cost,
                'profit' => $cost !== null ? round($r['revenue'] - $cost, 2) : null,
                'margin' => $cost !== null && $r['revenue'] > 0 ? round(($r['revenue'] - $cost) / $r['revenue'] * 100, 1) : null,
            ];
        })->map(fn ($r) => array_merge($r, ['qty' => round($r['qty'], 3), 'revenue' => round($r['revenue'], 2)]))
            ->sortByDesc('revenue')->values();
    }

    // ------------------------------------------------------------------ stock

    private function products(): Collection
    {
        return $this->products ??= DB::table('products as p')->leftJoin('product_categories as c', 'c.id', '=', 'p.category_id')
            ->get(['p.id', 'p.product_name', 'p.product_code', 'p.unit', 'p.cost_price', 'p.selling_price', 'p.current_stock', 'p.min_stock_level',
                'p.reorder_level', 'p.track_inventory', 'p.is_production_item', 'p.is_active', 'p.deleted_at', 'c.category_name as category'])
            ->keyBy('id');
    }

    /**
     * Stock on hand for active stocked items, with how fast each sells (last SPEED_WINDOW days)
     * and whether it must be bought: at/below its reorder level, or lasting fewer than DAYS_WARNING days.
     */
    public function stock(): Collection
    {
        $windowFrom = now()->subDays(self::SPEED_WINDOW)->startOfDay();
        $sold = $this->itemsSold($this->tickets($windowFrom, now(), true))->keyBy('product_id');

        return $this->products()
            ->filter(fn ($p) => $p->is_active && !$p->deleted_at && $p->track_inventory && !$p->is_production_item)
            ->map(function ($p) use ($sold) {
                $stock = (float) $p->current_stock;
                $perDay = round(((float) ($sold[$p->id]['qty'] ?? 0)) / self::SPEED_WINDOW, 2);
                $daysLeft = $perDay > 0 ? round(max($stock, 0) / $perDay, 1) : null;
                $reorderLevel = (float) $p->reorder_level;
                $atLevel = $reorderLevel > 0 && $stock <= $reorderLevel;
                $runningOut = $perDay > 0 && $daysLeft < self::DAYS_WARNING;
                $reorder = $atLevel || $runningOut || ($stock <= 0 && ($perDay > 0 || $reorderLevel > 0));
                $target = max(ceil($perDay * self::DAYS_COVER), $reorderLevel * 2, (float) $p->min_stock_level);
                $orderQty = $reorder ? max(ceil($target - max($stock, 0)), 0) : 0;
                $cost = (float) $p->cost_price;

                return [
                    'product_id' => $p->id,
                    'name' => $p->product_name,
                    'code' => $p->product_code,
                    'category' => $p->category ?? '—',
                    'unit' => $p->unit ?: 'piece',
                    'stock' => $stock,
                    'cost_price' => $cost,
                    'selling_price' => (float) $p->selling_price,
                    'value' => round(max($stock, 0) * $cost, 2),
                    'reorder_level' => $reorderLevel,
                    'per_day' => $perDay,
                    'days_left' => $daysLeft,
                    'status' => $stock <= 0 ? 'Out of stock' : ($reorder ? 'Low' : 'OK'),
                    'reorder' => $reorder,
                    'why' => $stock <= 0 ? 'Out of stock' : ($atLevel ? 'At/below reorder level' : ($runningOut ? "Runs out in {$daysLeft} days" : '')),
                    'order_qty' => $orderQty,
                    'order_cost' => round($orderQty * $cost, 2),
                ];
            })
            ->sortBy([[fn ($s) => ['Out of stock' => 0, 'Low' => 1, 'OK' => 2][$s['status']], 'asc'], ['name', 'asc']])->values();
    }

    private function slowMovers(Collection $items, Collection $stock): Collection
    {
        $sold = $items->keyBy('product_id');
        return $stock->filter(fn ($s) => $s['stock'] > 0)
            ->map(fn ($s) => $s + ['sold' => (float) ($sold[$s['product_id']]['qty'] ?? 0)])
            ->sortBy([['sold', 'asc'], ['value', 'desc']])
            ->take(10)->values();
    }

    // ------------------------------------------------------------------ other

    private function unpaid(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('sales')->whereNull('deleted_at')->whereIn('payment_status', ['failed', 'pending', 'cancelled'])
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_status, COUNT(*) n, SUM(total_amount) total')->groupBy('payment_status')->get()->keyBy('payment_status');
        return collect(['failed', 'pending', 'cancelled'])->mapWithKeys(fn ($s) => [$s => ['count' => (int) ($rows[$s]->n ?? 0), 'total' => round((float) ($rows[$s]->total ?? 0), 2)]])->all();
    }

    private function purchases(Carbon $from, Carbon $to): array
    {
        $po = DB::table('purchase_orders')->whereNull('deleted_at')->whereNotIn('status', ['cancelled', 'draft', 'rejected'])
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()]);
        $direct = DB::table('direct_purchases')->whereNull('deleted_at')->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()]);
        return [
            'orders' => (clone $po)->count(),
            'orders_total' => round((float) (clone $po)->sum('total_amount'), 2),
            'direct' => (clone $direct)->count(),
            'direct_total' => round((float) (clone $direct)->sum('total_amount'), 2),
            'total' => round((float) $po->sum('total_amount') + (float) $direct->sum('total_amount'), 2),
        ];
    }

    /** The 12 weeks up to the end of the period. */
    private function weeklyTrend(Carbon $to): Collection
    {
        $end = $to->copy()->endOfWeek();
        $start = $end->copy()->subWeeks(11)->startOfWeek();
        $tickets = $this->tickets($start, $end, false)->groupBy(fn ($t) => $t['at']->copy()->startOfWeek()->toDateString());
        return collect(range(0, 11))->map(function ($i) use ($start, $tickets) {
            $w = $start->copy()->addWeeks($i)->toDateString();
            return ['label' => Carbon::parse($w)->format('j M'), 'total' => round(($tickets[$w] ?? collect())->sum('amount'), 2)];
        });
    }

    /** The 6 months up to the end of the period. */
    private function monthlyTrend(Carbon $to): Collection
    {
        $end = $to->copy()->endOfMonth();
        $start = $end->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $tickets = $this->tickets($start, $end, false)->groupBy(fn ($t) => $t['at']->format('Y-m'));
        return collect(range(0, 5))->map(function ($i) use ($start, $tickets) {
            $m = $start->copy()->addMonthsNoOverflow($i);
            $rows = $tickets[$m->format('Y-m')] ?? collect();
            return ['label' => $m->format('M Y'), 'total' => round($rows->sum('amount'), 2), 'count' => $rows->count()];
        });
    }
}
