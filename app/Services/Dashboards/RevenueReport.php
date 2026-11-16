<?php

namespace App\Services\Dashboards;

use App\Models\FeePayment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finance revenue report for a date range (both days included).
 *
 *  money: what actually came in (fees, paid till/online/pre-order sales, card top-ups, application and event fees)
 *  cafeteria: what the cafeteria sold, by channel, including purchases paid from student cards.
 *             Card purchases spend money that already came in as a top-up, so they are not added to money again.
 */
class RevenueReport
{
    /** Paid sales by where they were made: [label, sale_type, which payment methods] */
    private const SALE_CHANNELS = [
        'till_cash'      => ['Till (POS) · cash', 'pos', 'cash'],
        'till_mpesa'     => ['Till (POS) · M-Pesa', 'pos', 'mpesa'],
        'online'         => ['Online orders (website)', 'online', 'mpesa'],
        'preorder_mpesa' => ['Pre-orders · M-Pesa', 'preorder', 'mpesa'],
    ];

    public function build(Carbon $from, Carbon $to): array
    {
        $days = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()))->map->toDateString()->values();
        $series = [];

        // ---- Sales recorded at the tills, online and for M-Pesa pre-orders (paid only)
        $sales = DB::table('sales')->whereNull('deleted_at')->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(COALESCE(sale_date, created_at))'), [$from->toDateString(), $to->toDateString()])
            ->selectRaw("DATE(COALESCE(sale_date, created_at)) d, sale_type, channel, payment_method, COUNT(*) n, SUM(total_amount) total")
            ->groupBy('d', 'sale_type', 'channel', 'payment_method')->get();

        // The till's "channel" field is not reliable (prompt sales were saved as gift_shop), so every
        // POS sale counts as the till. Card-paid sales are counted from the card transactions instead.
        foreach (self::SALE_CHANNELS as $key => [$label, $type, $methods]) {
            $rows = $sales->filter(fn ($r) => $r->sale_type === $type && $r->payment_method !== 'card'
                && ($methods === 'cash' ? $r->payment_method === 'cash' : $r->payment_method !== 'cash' || $type !== 'pos'));
            $series[$key] = $this->byDay($rows, $days, $label);
        }

        // ---- Paid from student cards: at the counter, and card pre-orders (from the orders, so rejected/refunded ones drop out)
        $cardCounter = Schema::hasTable('card_transactions') ? DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')
            ->where(fn ($q) => $q->whereNull('location')->orWhere('location', '!=', 'pre_order_portal'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(created_at) d, COUNT(*) n, SUM(ABS(amount)) total')->groupBy('d')->get() : collect();
        $series['card_counter'] = $this->byDay($cardCounter, $days, 'Student cards · at the counter');

        $cardPre = DB::table('pre_orders')->where('payment_method', 'card')->whereIn('status', ['paid', 'accepted', 'ready', 'completed'])
            ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) d, COUNT(*) n, SUM(total_amount) total')->groupBy('d')->get();
        $series['preorder_card'] = $this->byDay($cardPre, $days, 'Pre-orders · student card');

        // ---- Other money in
        $fees = FeePayment::where('status', 'completed')->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(payment_date) d, COUNT(*) n, SUM(amount) total')->groupBy('d')->get();
        $series['fees'] = $this->byDay($fees, $days, 'School fees');

        foreach (['transport' => 'Card top-ups · transport', 'canteen' => 'Card top-ups · canteen'] as $wallet => $label) {
            $rows = Schema::hasTable('card_transactions') ? DB::table('card_transactions')->where('transaction_type', 'funding')->where('status', 'completed')
                ->where(fn ($q) => $wallet === 'canteen' ? $q->where('wallet', 'canteen')->orWhereNull('wallet') : $q->where('wallet', 'transport'))
                ->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()])
                ->selectRaw('DATE(created_at) d, COUNT(*) n, SUM(amount) total')->groupBy('d')->get() : collect();
            $series['topup_' . $wallet] = $this->byDay($rows, $days, $label);
        }

        $apps = Schema::hasTable('application_payments') ? DB::table('application_payments')->whereIn('status', ['completed', 'paid', 'success'])
            ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) d, COUNT(*) n, SUM(amount) total')->groupBy('d')->get() : collect();
        $series['applications'] = $this->byDay($apps, $days, 'Application fees');

        $events = Schema::hasTable('event_applications') ? DB::table('event_applications')->whereIn('application_status', ['confirmed', 'paid', 'approved'])
            ->where('total_amount', '>', 0)->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(created_at) d, COUNT(*) n, SUM(total_amount) total')->groupBy('d')->get() : collect();
        $series['events'] = $this->byDay($events, $days, 'Event fees');

        $moneyKeys = ['fees', 'till_cash', 'till_mpesa', 'online', 'preorder_mpesa', 'topup_transport', 'topup_canteen', 'applications', 'events'];
        $cafeKeys = ['till_cash', 'till_mpesa', 'card_counter', 'preorder_mpesa', 'preorder_card', 'online'];

        $pick = fn (array $keys) => collect($keys)->mapWithKeys(fn ($k) => [$k => $series[$k]]);
        $money = $pick($moneyKeys);
        $cafe = $pick($cafeKeys);

        $daily = $days->map(fn ($d) => [
            'date' => $d,
            'money' => round($money->sum(fn ($s) => $s['days'][$d]), 2),
            'cafeteria' => round($cafe->sum(fn ($s) => $s['days'][$d]), 2),
            'cards' => round($series['card_counter']['days'][$d] + $series['preorder_card']['days'][$d], 2),
        ]);

        return [
            'from' => $from, 'to' => $to, 'days' => $days,
            'money' => $money, 'moneyTotal' => round($money->sum('total'), 2),
            'cafeteria' => $cafe, 'cafeteriaTotal' => round($cafe->sum('total'), 2),
            'cardSpend' => round($series['card_counter']['total'] + $series['preorder_card']['total'], 2),
            'daily' => $daily,
        ];
    }

    /** Spread grouped rows over every day in the range. */
    private function byDay($rows, $days, string $label): array
    {
        $byDate = collect($rows)->groupBy('d');
        $perDay = $days->mapWithKeys(fn ($d) => [$d => round((float) ($byDate[$d] ?? collect())->sum('total'), 2)])->all();

        return [
            'label' => $label,
            'days' => $perDay,
            'total' => round(array_sum($perDay), 2),
            'count' => (int) collect($rows)->sum('n'),
        ];
    }
}
