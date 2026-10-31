<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\FareDisbursement;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Finance view of how cafeteria money comes in and is spent:
 * card top-ups per wallet, card purchases (by day / class / student / product),
 * POS sales by payment method, and what is still held on cards.
 */
class CafeteriaAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $range = [$from, $to];

        // ---- Card top-ups, split by wallet (older rows only carry the type in metadata)
        $topups = ['transport' => 0.0, 'canteen' => 0.0];
        CardTransaction::where('transaction_type', 'funding')->where('status', 'completed')
            ->whereBetween('created_at', $range)
            ->select(['id', 'amount', 'wallet', 'metadata'])
            ->chunkById(500, function ($rows) use (&$topups) {
                foreach ($rows as $r) {
                    $wallet = $r->wallet ?? (self::fundingType($r->metadata) === 'T' ? 'transport' : 'canteen');
                    $topups[$wallet] += (float) $r->amount;
                }
            });

        // ---- Card purchases (canteen spending)
        $purchases = CardTransaction::query()
            ->where('card_transactions.transaction_type', 'purchase')
            ->where('card_transactions.status', 'completed')
            ->whereBetween('card_transactions.created_at', $range);

        $refunds = (float) CardTransaction::where('transaction_type', 'refund')->where('status', 'completed')
            ->whereBetween('created_at', $range)->sum('amount');

        $cardSpend = [
            'total'   => (float) (clone $purchases)->sum('amount') - $refunds,
            'count'   => (clone $purchases)->count(),
            'buyers'  => (clone $purchases)->distinct('card_account_id')->count('card_account_id'),
            'refunds' => $refunds,
        ];
        $cardSpend['average'] = $cardSpend['count'] ? $cardSpend['total'] / $cardSpend['count'] : 0;

        $byClass = (clone $purchases)
            ->join('card_accounts', 'card_accounts.id', '=', 'card_transactions.card_account_id')
            ->selectRaw("COALESCE(card_accounts.student_class, 'Unknown') as class, COUNT(*) as n, SUM(card_transactions.amount) as total, COUNT(DISTINCT card_accounts.id) as students")
            ->groupBy('class')->orderByDesc('total')->get();

        $topStudents = (clone $purchases)
            ->join('card_accounts', 'card_accounts.id', '=', 'card_transactions.card_account_id')
            ->selectRaw('card_accounts.id, card_accounts.student_name, card_accounts.student_class, card_accounts.canteen_balance, COUNT(*) as n, SUM(card_transactions.amount) as total')
            ->groupBy('card_accounts.id', 'card_accounts.student_name', 'card_accounts.student_class', 'card_accounts.canteen_balance')
            ->orderByDesc('total')->limit(10)->get();

        $byHour = (clone $purchases)
            ->selectRaw('HOUR(card_transactions.created_at) as h, COUNT(*) as n, SUM(card_transactions.amount) as total')
            ->groupBy('h')->orderBy('h')->get()->keyBy('h');

        // Products bought with cards live in the transaction metadata
        $cardProducts = [];
        (clone $purchases)->select(['card_transactions.id', 'card_transactions.amount', 'card_transactions.metadata', 'card_transactions.description'])
            ->chunkById(500, function ($rows) use (&$cardProducts) {
                foreach ($rows as $r) {
                    $items = self::decode($r->metadata)['items'] ?? null;
                    if (!$items) {
                        $key = 'Custom amount (no items)';
                        $cardProducts[$key]['qty'] = ($cardProducts[$key]['qty'] ?? 0) + 1;
                        $cardProducts[$key]['total'] = ($cardProducts[$key]['total'] ?? 0) + (float) $r->amount;
                        continue;
                    }
                    foreach ($items as $i) {
                        $key = $i['product_name'] ?? 'Unknown';
                        $cardProducts[$key]['qty'] = ($cardProducts[$key]['qty'] ?? 0) + (float) ($i['quantity'] ?? 1);
                        $cardProducts[$key]['total'] = ($cardProducts[$key]['total'] ?? 0) + (float) ($i['total_price'] ?? 0);
                    }
                }
            }, 'card_transactions.id', 'id');
        uasort($cardProducts, fn ($a, $b) => $b['total'] <=> $a['total']);
        $cardProducts = array_slice($cardProducts, 0, 15, true);

        // ---- POS / online sales (cash, M-Pesa)
        $paidSales = DB::table('sales')->whereNull('deleted_at')->where('payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), $range);

        $salesByMethod = (clone $paidSales)
            ->selectRaw('payment_method, channel, COUNT(*) as n, SUM(total_amount) as total')
            ->groupBy('payment_method', 'channel')->orderByDesc('total')->get();

        $salesProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')->where('sales.payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(sales.sale_date, sales.created_at)'), $range)
            ->selectRaw('sale_items.product_name, SUM(sale_items.quantity) as qty, SUM(sale_items.total_price) as total')
            ->groupBy('sale_items.product_name')->orderByDesc('total')->limit(15)->get();

        // ---- Daily trend: card purchases vs paid POS sales
        $cardDaily = (clone $purchases)->selectRaw('DATE(card_transactions.created_at) as d, SUM(card_transactions.amount) as total')
            ->groupBy('d')->pluck('total', 'd');
        $salesDaily = (clone $paidSales)->selectRaw('DATE(COALESCE(sale_date, created_at)) as d, SUM(total_amount) as total')
            ->groupBy('d')->pluck('total', 'd');
        $days = collect(CarbonPeriod::create($from, '1 day', $to->copy()->startOfDay()))->map->toDateString();
        $daily = [
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->values(),
            'card'   => $days->map(fn ($d) => (float) ($cardDaily[$d] ?? 0))->values(),
            'sales'  => $days->map(fn ($d) => (float) ($salesDaily[$d] ?? 0))->values(),
        ];

        // ---- Money held on cards right now
        $held = [
            'transport' => (float) CardAccount::whereNotNull('wallets_split_at')->sum('transport_balance'),
            'canteen'   => (float) CardAccount::whereNotNull('wallets_split_at')->sum('canteen_balance'),
            'unsplit'   => (float) CardAccount::whereNull('wallets_split_at')->sum('balance'),
            'low'       => CardAccount::whereNotNull('wallets_split_at')->where('is_active', true)
                ->whereColumn('canteen_balance', '<', 'low_balance_threshold')->count(),
        ];

        $fare = FareDisbursement::given()->whereBetween('given_on', [$from->toDateString(), $to->toDateString()]);
        $fareGiven = ['cash' => (float) (clone $fare)->sum('amount'), 'fees' => (float) (clone $fare)->sum('withdrawal_fee')];

        return view('ktvtc.finance.cafeteria-analytics', compact(
            'from', 'to', 'topups', 'cardSpend', 'byClass', 'topStudents', 'byHour', 'cardProducts',
            'salesByMethod', 'salesProducts', 'daily', 'held', 'fareGiven'
        ));
    }

    private static function decode($value): array
    {
        for ($i = 0; $i < 2 && is_string($value); $i++) {
            $value = json_decode($value, true);
        }
        return is_array($value) ? $value : [];
    }

    private static function fundingType($metadata): ?string
    {
        return strtoupper((string) (self::decode($metadata)['funding_type'] ?? '')) ?: null;
    }
}
