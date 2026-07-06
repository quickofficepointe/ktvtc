<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\HighSchoolStudent;
use App\Models\CardTransaction;
use App\Models\CardDailyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardReportController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        $totalCards = CardAccount::count();
        $totalBalance = CardAccount::sum('balance');
        $totalStudents = HighSchoolStudent::count();
        $activeStudents = HighSchoolStudent::where('status', 'active')->count();

        // Today's transactions
        $todayTransactions = CardTransaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('amount');

        $todayCount = CardTransaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        // This month
        $monthTransactions = CardTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('amount');

        $monthCount = CardTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->count();

        return view('high-school.reports.index', compact(
            'totalCards',
            'totalBalance',
            'totalStudents',
            'activeStudents',
            'todayTransactions',
            'todayCount',
            'monthTransactions',
            'monthCount'
        ));
    }

    /**
     * Daily report
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $dailyUsage = CardDailyUsage::where('usage_date', $date)
            ->with('cardAccount.student')
            ->orderBy('total_spent', 'desc')
            ->get();

        $totalSpent = $dailyUsage->sum('total_spent');
        $totalTransactions = $dailyUsage->sum('transaction_count');
        $totalStudents = $dailyUsage->count();

        $topSpenders = $dailyUsage->take(10);

        return view('high-school.reports.daily', compact(
            'date',
            'dailyUsage',
            'totalSpent',
            'totalTransactions',
            'totalStudents',
            'topSpenders'
        ));
    }

    /**
     * Monthly report
     */
    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $dailyTotals = CardDailyUsage::whereMonth('usage_date', $month)
            ->whereYear('usage_date', $year)
            ->select(
                DB::raw('DATE(usage_date) as date'),
                DB::raw('SUM(total_spent) as total_spent'),
                DB::raw('SUM(transaction_count) as transaction_count'),
                DB::raw('COUNT(DISTINCT card_account_id) as active_cards')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalSpent = $dailyTotals->sum('total_spent');
        $totalTransactions = $dailyTotals->sum('transaction_count');
        $averageDaily = $dailyTotals->count() > 0 ? $totalSpent / $dailyTotals->count() : 0;

        return view('high-school.reports.monthly', compact(
            'month',
            'year',
            'dailyTotals',
            'totalSpent',
            'totalTransactions',
            'averageDaily'
        ));
    }

    /**
     * Student report
     */
    public function students(Request $request)
    {
        $students = HighSchoolStudent::with(['cardAccount', 'cardAccount.transactions' => function($q) {
            $q->where('status', 'completed');
        }])->get();

        $studentData = $students->map(function($student) {
            $card = $student->cardAccount;
            return [
                'student' => $student,
                'card' => $card,
                'balance' => $card->balance ?? 0,
                'total_spent' => $card->total_spent ?? 0,
                'total_funded' => $card->total_funded ?? 0,
                'transaction_count' => $card->transactions->count() ?? 0,
                'last_used' => $card->last_used_at ?? null,
            ];
        });

        return view('high-school.reports.students', compact('studentData'));
    }

    /**
     * Balance report
     */
    public function balances(Request $request)
    {
        $cards = CardAccount::with('student')
            ->orderBy('balance', 'desc')
            ->get();

        $totalBalance = $cards->sum('balance');

        return view('high-school.reports.balances', compact('cards', 'totalBalance'));
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
            ->get();

        return view('high-school.reports.low-balance', compact('cards', 'threshold'));
    }

    /**
     * Inactive cards report
     */
    public function inactive(Request $request)
    {
        $days = $request->get('days', 30);

        $cards = CardAccount::with('student')
            ->where(function($q) use ($days) {
                $q->where('last_used_at', '<', now()->subDays($days))
                  ->orWhereNull('last_used_at');
            })
            ->where('is_active', true)
            ->orderBy('last_used_at', 'desc')
            ->get();

        return view('high-school.reports.inactive', compact('cards', 'days'));
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        // TODO: Implement export
        return redirect()->back()->with('info', 'Export functionality coming soon');
    }
}
