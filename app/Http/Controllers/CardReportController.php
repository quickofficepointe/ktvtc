<?php
// app/Http/Controllers/CardReportController.php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\HighSchoolStudent;
use App\Models\CardTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CardReportController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        $totalCards     = CardAccount::count();
        $totalBalance   = CardAccount::sum('balance');
        $totalStudents  = HighSchoolStudent::count();
        $activeStudents = HighSchoolStudent::where('status', 'active')->count();

        // TODAY: separate funding from purchases (previous version summed everything)
        $todayFunding = CardTransaction::whereDate('created_at', today())
            ->where('transaction_type', 'funding')
            ->where('status', 'completed')
            ->sum('amount');

        $todaySpend = CardTransaction::whereDate('created_at', today())
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->sum('amount');

        $todayCount = CardTransaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        // MONTH
        $monthFunding = CardTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('transaction_type', 'funding')
            ->where('status', 'completed')
            ->sum('amount');

        $monthSpend = CardTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->sum('amount');

        $monthCount = CardTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->count();

        // Duplicate count for the badge on the Card Transactions tile
        $duplicateCount = $this->countDuplicates();

        return view('ktvtc.finance.high-school.reports.index', compact(
            'totalCards',
            'totalBalance',
            'totalStudents',
            'activeStudents',
            'todayFunding',
            'todaySpend',
            'todayCount',
            'monthFunding',
            'monthSpend',
            'monthCount',
            'duplicateCount'
        ));
    }

    /**
     * Daily report — reads real data from card_transactions
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        // All completed purchases on this date
        $purchases = CardTransaction::with(['cardAccount.student'])
            ->whereDate('created_at', $date)
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Summary
        $totalSpent        = (float) $purchases->sum('amount');
        $totalTransactions = $purchases->count();
        $totalStudents     = $purchases->pluck('card_account_id')->unique()->count();
        $averagePerTxn     = $totalTransactions > 0 ? $totalSpent / $totalTransactions : 0;

        // Hourly breakdown (24 buckets)
        $hourlySpend = array_fill(0, 24, 0.0);
        $hourlyCount = array_fill(0, 24, 0);
        foreach ($purchases as $p) {
            $h = (int) $p->created_at->format('G');
            $hourlySpend[$h] += (float) $p->amount;
            $hourlyCount[$h] += 1;
        }

        // Top spenders (aggregated per card)
        $topSpenders = $purchases
            ->groupBy('card_account_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'student_name'      => $first->cardAccount->student->full_name      ?? 'Unknown',
                    'admission_number'  => $first->cardAccount->student->admission_number ?? '',
                    'card_number'       => $first->cardAccount->card_number             ?? '',
                    'total_spent'       => (float) $rows->sum('amount'),
                    'transaction_count' => $rows->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(10)
            ->values();

        // Product velocity for the day
        $productVelocity = [];
        foreach ($purchases as $p) {
            $meta  = is_array($p->metadata) ? $p->metadata : json_decode($p->metadata ?? '{}', true);
            $items = $meta['items'] ?? [];
            foreach ($items as $item) {
                $pid = $item['product_id'] ?? null;
                if (!$pid) continue;
                if (!isset($productVelocity[$pid])) {
                    $productVelocity[$pid] = [
                        'product_id'   => $pid,
                        'product_name' => $item['product_name'] ?? 'Unknown',
                        'product_code' => $item['product_code'] ?? '',
                        'quantity'     => 0,
                        'revenue'      => 0.0,
                    ];
                }
                $productVelocity[$pid]['quantity'] += (int) ($item['quantity'] ?? 0);
                $productVelocity[$pid]['revenue']  += (float) ($item['total_price'] ?? 0);
            }
        }
        usort($productVelocity, fn ($a, $b) => $b['quantity'] <=> $a['quantity']);
        $topProducts = array_slice($productVelocity, 0, 15);

        // Transaction rows for the table
        $usage = $purchases->map(function ($p) {
            return [
                'student_name'  => $p->cardAccount->student->full_name ?? 'Unknown',
                'student_class' => $p->cardAccount->student->class     ?? '',
                'amount'        => (float) $p->amount,
                'description'   => $p->description,
                'time'          => $p->created_at,
            ];
        });

        return view('ktvtc.finance.high-school.reports.daily', compact(
            'date',
            'purchases',
            'usage',
            'totalSpent',
            'totalTransactions',
            'totalStudents',
            'averagePerTxn',
            'hourlySpend',
            'hourlyCount',
            'topSpenders',
            'topProducts'
        ));
    }

    /**
     * Monthly report — reads real data + product velocity + peak hours
     */
    public function monthly(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // All purchases in range
        $purchases = CardTransaction::with(['cardAccount.student'])
            ->whereBetween('created_at', [$start, $end])
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->get();

        // Summary
        $totalSpent        = (float) $purchases->sum('amount');
        $totalTransactions = $purchases->count();
        $activeStudents    = $purchases->pluck('card_account_id')->unique()->count();
        $daysWithActivity  = $purchases->pluck('created_at')->map->toDateString()->unique()->count();
        $averageDaily      = $daysWithActivity > 0 ? $totalSpent / $daysWithActivity : 0;

        // Daily totals (for line chart)
               // Daily totals (for line chart) — keep as plain array of arrays
        $dailyTotals = $purchases
            ->groupBy(fn ($p) => $p->created_at->toDateString())
            ->map(function ($rows, $date) {
                return [
                    'date'              => $date,
                    'total_spent'       => (float) $rows->sum('amount'),
                    'transaction_count' => $rows->count(),
                    'active_cards'      => $rows->pluck('card_account_id')->unique()->count(),
                ];
            })
            ->sortKeys()
            ->values()
            ->all();   // ← converts to plain PHP array

        // Hourly peak
        $hourlySpend = array_fill(0, 24, 0.0);
        $hourlyCount = array_fill(0, 24, 0);
        foreach ($purchases as $p) {
            $h = (int) $p->created_at->format('G');
            $hourlySpend[$h] += (float) $p->amount;
            $hourlyCount[$h] += 1;
        }

        // Product velocity
        $productVelocity = [];
        foreach ($purchases as $p) {
            $meta  = is_array($p->metadata) ? $p->metadata : json_decode($p->metadata ?? '{}', true);
            $items = $meta['items'] ?? [];
            foreach ($items as $item) {
                $pid = $item['product_id'] ?? null;
                if (!$pid) continue;
                if (!isset($productVelocity[$pid])) {
                    $productVelocity[$pid] = [
                        'product_id'   => $pid,
                        'product_name' => $item['product_name'] ?? 'Unknown',
                        'product_code' => $item['product_code'] ?? '',
                        'quantity'     => 0,
                        'revenue'      => 0.0,
                    ];
                }
                $productVelocity[$pid]['quantity'] += (int) ($item['quantity'] ?? 0);
                $productVelocity[$pid]['revenue']  += (float) ($item['total_price'] ?? 0);
            }
        }

        // Attach current stock + compute days of stock left
        $productIds = array_keys($productVelocity);
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $daysElapsed = max(1, min(now()->day, $end->day));
        foreach ($productVelocity as $pid => &$row) {
            $product = $products->get($pid);
            $row['current_stock']   = (float) ($product->current_stock   ?? 0);
            $row['reorder_level']   = (float) ($product->reorder_level   ?? 0);
            $row['min_stock_level'] = (float) ($product->min_stock_level ?? 0);

            $dailyRate = $row['quantity'] / $daysElapsed;
            $row['daily_rate'] = round($dailyRate, 1);
            $row['days_of_stock'] = ($dailyRate > 0 && $product)
                ? (int) floor($row['current_stock'] / $dailyRate)
                : null;
        }
        unset($row);

        usort($productVelocity, fn ($a, $b) => $b['quantity'] <=> $a['quantity']);
        $topProducts = array_slice($productVelocity, 0, 20);

        // Top spenders of the month
        $topSpenders = $purchases
            ->groupBy('card_account_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'student_name'      => $first->cardAccount->student->full_name      ?? 'Unknown',
                    'admission_number'  => $first->cardAccount->student->admission_number ?? '',
                    'total_spent'       => (float) $rows->sum('amount'),
                    'transaction_count' => $rows->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(10)
            ->values();

        return view('ktvtc.finance.high-school.reports.monthly', compact(
            'month',
            'year',
            'totalSpent',
            'totalTransactions',
            'activeStudents',
            'averageDaily',
            'dailyTotals',
            'hourlySpend',
            'hourlyCount',
            'topProducts',
            'topSpenders'
        ));
    }

    /**
     * Student report
     */
    public function students(Request $request)
    {
        $students = HighSchoolStudent::with([
            'cardAccount',
            'cardAccount.transactions' => function ($q) {
                $q->where('status', 'completed');
            }
        ])->get();

        $studentData = $students->map(function ($student) {
            $card = $student->cardAccount;

            return [
                'student'           => $student,
                'card'              => $card,
                'balance'           => $card->balance       ?? 0,
                'total_spent'       => $card->total_spent   ?? 0,
                'total_funded'      => $card->total_funded  ?? 0,
                'transaction_count' => $card ? $card->transactions->count() : 0,
                'last_used'         => $card->last_used_at  ?? null,
            ];
        });

        return view('ktvtc.finance.high-school.reports.students', compact('studentData'));
    }

    /**
     * Balance report
     */
    public function balances(Request $request)
    {
        $cards = CardAccount::with('student')
            ->orderBy('balance', 'desc')
            ->paginate(20);

        $totalBalance = CardAccount::sum('balance');

        return view('ktvtc.finance.high-school.reports.balances', compact('cards', 'totalBalance'));
    }

    /**
     * Low balance report
     */
    public function lowBalance(Request $request)
    {
        $threshold = $request->get('threshold', 100);

        $cards = CardAccount::with('student')
            ->where('balance', '<', $threshold)
            ->orderBy('balance', 'asc')
            ->paginate(20);

        $zeroBalance = CardAccount::where('balance', 0)->count();

        return view('ktvtc.finance.high-school.reports.low-balance',
            compact('cards', 'threshold', 'zeroBalance'));
    }

    /**
     * Inactive cards report
     */
    public function inactive(Request $request)
    {
        $days = $request->get('days', 30);

        $baseQuery = function () use ($days) {
            return CardAccount::where(function ($q) use ($days) {
                $q->where('last_used_at', '<', now()->subDays($days))
                  ->orWhereNull('last_used_at');
            })->where('is_active', true);
        };

        $cards = $baseQuery()
            ->with('student')
            ->orderBy('last_used_at', 'desc')
            ->paginate(20);

        $zeroBalance = (clone $baseQuery())->where('balance', 0)->count();

        return view('ktvtc.finance.high-school.reports.inactive',
            compact('cards', 'days', 'zeroBalance'));
    }

    /**
     * All card transactions with M-Pesa code, funding type (T/C)
     * and duplicate detection.
     */
    public function transactions(Request $request)
    {
        $query = CardTransaction::query()
            ->with(['cardAccount.student'])
            ->select('card_transactions.*')
            ->selectRaw('(
                SELECT COUNT(*) FROM card_transactions AS ct2
                WHERE ct2.card_account_id = card_transactions.card_account_id
                  AND ct2.transaction_type = "funding"
                  AND card_transactions.transaction_type = "funding"
                  AND ct2.amount = card_transactions.amount
                  AND ct2.id != card_transactions.id
                  AND ABS(TIMESTAMPDIFF(SECOND, ct2.created_at, card_transactions.created_at)) <= 90
                  AND (
                        (card_transactions.reference LIKE "8118445%" AND ct2.reference LIKE "IPN%")
                     OR (card_transactions.reference LIKE "IPN%" AND ct2.reference LIKE "8118445%")
                  )
            ) AS duplicate_sibling_count'
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mpesa_receipt', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('cardAccount.student', function ($s) use ($search) {
                      $s->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('admission_number', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('cardAccount', function ($c) use ($search) {
                      $c->where('card_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('card_id')) {
            $query->where('card_account_id', $request->card_id);
        }

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('funding_type')) {
            $ft = strtoupper($request->funding_type);
            $query->where(function ($q) use ($ft) {
                $q->where('reference', 'LIKE', "8118445#{$ft}%")
                  ->orWhereRaw("JSON_EXTRACT(metadata, '$.funding_type') = ?", [$ft]);
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->get('duplicates_only') === '1') {
            $query->havingRaw('duplicate_sibling_count > 0');
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'total_count'     => CardTransaction::count(),
            'total_funding'   => CardTransaction::where('transaction_type', 'funding')
                                    ->where('status', 'completed')->sum('amount'),
            'total_spend'     => CardTransaction::where('transaction_type', 'purchase')
                                    ->where('status', 'completed')->sum('amount'),
            'duplicate_count' => $this->countDuplicates(),
        ];

        return view('ktvtc.finance.high-school.reports.transactions',
            compact('transactions', 'stats'));
    }

    /**
     * Export filtered transactions to CSV.
     */
    public function transactionsExport(Request $request)
    {
        $filename = 'card_transactions_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date', 'Card Number', 'Student Name', 'Admission No.',
                'Type', 'Funding Type', 'Amount',
                'Balance Before', 'Balance After',
                'M-Pesa Receipt', 'Reference', 'Status',
            ]);

            $query = CardTransaction::with(['cardAccount.student'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->to);
            }
            if ($request->filled('type')) {
                $query->where('transaction_type', $request->type);
            }

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    $metadata = is_array($t->metadata)
                        ? $t->metadata
                        : json_decode($t->metadata ?? '{}', true);

                    $fundingType = $metadata['funding_type']
                        ?? (preg_match('/#([TC])\d+$/', $t->reference ?? '', $m) ? $m[1] : '')
                        ?: '—';

                    fputcsv($handle, [
                        $t->created_at?->format('Y-m-d H:i:s'),
                        $t->cardAccount->card_number              ?? '—',
                        $t->cardAccount->student->full_name       ?? '—',
                        $t->cardAccount->student->admission_number ?? '—',
                        $t->transaction_type,
                        $fundingType,
                        number_format($t->amount, 2, '.', ''),
                        number_format($t->balance_before, 2, '.', ''),
                        number_format($t->balance_after, 2, '.', ''),
                        $t->mpesa_receipt ?? '—',
                        $t->reference     ?? '—',
                        $t->status,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export report (stub for existing behavior)
     */
    public function export(Request $request)
    {
        return redirect()->back()->with('info', 'Export functionality coming soon');
    }

    /**
     * Count total duplicate fundings currently unresolved.
     */
    protected function countDuplicates(): int
    {
        return DB::table('card_transactions as ct1')
            ->join('card_transactions as ct2', function ($join) {
                $join->on('ct1.card_account_id', '=', 'ct2.card_account_id')
                     ->on('ct1.id', '<', 'ct2.id')
                     ->on('ct1.amount', '=', 'ct2.amount')
                     ->where('ct1.transaction_type', '=', 'funding')
                     ->where('ct2.transaction_type', '=', 'funding')
                     ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, ct1.created_at, ct2.created_at)) <= 90')
                     ->where('ct1.reference', 'LIKE', '8118445%')
                     ->where('ct2.reference', 'LIKE', 'IPN%');
            })
            ->where('ct1.status', 'completed')
            ->where('ct2.status', 'completed')
            ->count();
    }
}
