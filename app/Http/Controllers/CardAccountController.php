<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\HighSchoolStudent;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CardAccountController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Display list of cards
     */
    public function index(Request $request)
    {
        $query = CardAccount::with('student');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'locked') {
                $query->where('is_locked', true);
            } elseif ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('card_number', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('student_admission_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class')) {
            $query->where('student_class', $request->class);
        }

        $cards = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $totalCards = CardAccount::count();
        $activeCards = CardAccount::where('is_active', true)->count();
        $lockedCards = CardAccount::where('is_locked', true)->count();
        $blockedCards = CardAccount::where('is_blocked', true)->count();
        $totalBalance = CardAccount::sum('balance');

        $classes = CardAccount::select('student_class')->distinct()->whereNotNull('student_class')->pluck('student_class');

        return view('ktvtc.finance.high-school.cards.index', compact(
            'cards',
            'totalCards',
            'activeCards',
            'lockedCards',
            'blockedCards',
            'totalBalance',
            'classes'
        ));
    }

    /**
     * Show create card form
     */
    public function create(Request $request)
    {
        $student = null;
        if ($request->filled('student_id')) {
            $student = HighSchoolStudent::findOrFail($request->student_id);
        }

        $students = HighSchoolStudent::whereDoesntHave('cardAccount')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('ktvtc.finance.high-school.cards.create', compact('students', 'student'));
    }

    /**
     * Store a new card
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:high_school_students,id',
            'daily_limit' => 'nullable|numeric|min:0',
            'per_transaction_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student = HighSchoolStudent::findOrFail($request->student_id);

        // Check if student already has a card
        if ($student->cardAccount) {
            return redirect()->back()
                ->with('error', 'Student already has a card: ' . $student->cardAccount->card_number);
        }

        try {
            $card = $this->cardService->createCardForStudent(
                $student,
                $request->daily_limit ?? 500,
                $request->per_transaction_limit ?? 300
            );

            return redirect()->route('high-school.cards.show', $card)
                ->with('success', 'Card issued successfully! Card number: ' . $card->card_number);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to issue card: ' . $e->getMessage());
        }
    }

    /**
     * Show card details
     */
    public function show(CardAccount $cardAccount)
    {
        $card = $cardAccount;
        $card->load(['student', 'transactions' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(20);
        }]);

        $dailyUsage = $card->dailyUsage()->where('usage_date', today())->first();

        return view('ktvtc.finance.high-school.cards.show', compact('card', 'dailyUsage'));
    }

    /**
     * Show edit card form
     */
    public function edit(CardAccount $cardAccount)
    {
        return view('ktvtc.finance.high-school.cards.edit', compact('cardAccount'));
    }

    /**
     * Update card
     */
    public function update(Request $request, CardAccount $cardAccount)
    {
        $validator = Validator::make($request->all(), [
            'daily_limit' => 'required|numeric|min:0',
            'per_transaction_limit' => 'required|numeric|min:0',
            'low_balance_threshold' => 'required|numeric|min:0',
            'minimum_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $cardAccount->update([
                'daily_limit' => $request->daily_limit,
                'per_transaction_limit' => $request->per_transaction_limit,
                'low_balance_threshold' => $request->low_balance_threshold,
                'minimum_balance' => $request->minimum_balance,
            ]);

            // Log audit
            CardAuditLog::log(
                $cardAccount->id,
                'limit_change',
                'Card limits updated',
                null,
                json_encode($request->all())
            );

            return redirect()->route('high-school.cards.show', $cardAccount)
                ->with('success', 'Card updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update card: ' . $e->getMessage());
        }
    }

    /**
     * Delete card
     */
    public function destroy(CardAccount $cardAccount)
    {
        try {
            $cardAccount->delete();
            return redirect()->route('high-school.cards.index')
                ->with('success', 'Card deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete card: ' . $e->getMessage());
        }
    }

    /**
     * Activate card
     */
    public function activate(CardAccount $cardAccount)
    {
        $cardAccount->update(['is_active' => true]);

        CardAuditLog::log($cardAccount->id, 'activate', 'Card activated');

        return redirect()->back()->with('success', 'Card activated successfully');
    }

    /**
     * Deactivate card
     */
    public function deactivate(CardAccount $cardAccount)
    {
        $cardAccount->update(['is_active' => false]);

        CardAuditLog::log($cardAccount->id, 'deactivate', 'Card deactivated');

        return redirect()->back()->with('success', 'Card deactivated successfully');
    }

    /**
     * Lock card
     */
    public function lock(CardAccount $cardAccount)
    {
        $cardAccount->update(['is_locked' => true]);

        CardAuditLog::log($cardAccount->id, 'lock', 'Card locked');

        return redirect()->back()->with('success', 'Card locked successfully');
    }

    /**
     * Unlock card
     */
    public function unlock(CardAccount $cardAccount)
    {
        $cardAccount->update(['is_locked' => false]);

        CardAuditLog::log($cardAccount->id, 'unlock', 'Card unlocked');

        return redirect()->back()->with('success', 'Card unlocked successfully');
    }

    /**
     * Block card
     */
    public function block(Request $request, CardAccount $cardAccount)
    {
        $request->validate(['reason' => 'required|string']);

        $cardAccount->update([
            'is_blocked' => true,
            'blocked_reason' => $request->reason,
            'blocked_at' => now(),
            'blocked_by' => auth()->id(),
        ]);

        CardAuditLog::log(
            $cardAccount->id,
            'block',
            'Card blocked: ' . $request->reason
        );

        return redirect()->back()->with('success', 'Card blocked successfully');
    }

    /**
     * Unblock card
     */
    public function unblock(CardAccount $cardAccount)
    {
        $cardAccount->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);

        CardAuditLog::log($cardAccount->id, 'unblock', 'Card unblocked');

        return redirect()->back()->with('success', 'Card unblocked successfully');
    }

    /**
     * Adjust card balance
     */
    public function adjustBalance(Request $request, CardAccount $cardAccount)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:255',
            'type' => 'required|in:add,subtract'
        ]);

        try {
            $amount = $request->type === 'add' ? $request->amount : -$request->amount;

            $result = $this->cardService->adjustBalance(
                $cardAccount,
                $amount,
                $request->reason,
                auth()->id()
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Balance adjusted successfully. New balance: KES ' . number_format($result['new_balance'], 2));
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to adjust balance: ' . $e->getMessage());
        }
    }

    /**
     * Get card balance (API)
     */
    public function getBalance(CardAccount $cardAccount)
    {
        return response()->json([
            'card_number' => $cardAccount->card_number,
            'balance' => $cardAccount->balance,
            'daily_limit' => $cardAccount->daily_limit,
            'today_spent' => $cardAccount->today_spent,
            'remaining_daily' => $cardAccount->daily_limit - $cardAccount->today_spent
        ]);
    }

    /**
     * Get card transactions
     */
    public function transactions(CardAccount $cardAccount, Request $request)
    {
        $limit = $request->get('limit', 50);
        $transactions = $cardAccount->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($request->ajax()) {
            return response()->json($transactions);
        }

        return view('ktvtc.finance.high-school.cards.transactions', compact('cardAccount', 'transactions'));
    }

    /**
     * Bulk issue cards
     */
    public function bulkIssue(Request $request)
    {
        $studentIds = $request->student_ids;
        if (empty($studentIds)) {
            return redirect()->back()->with('error', 'No students selected');
        }

        $issued = 0;
        $failed = 0;

        foreach ($studentIds as $studentId) {
            $student = HighSchoolStudent::find($studentId);
            if ($student && !$student->cardAccount) {
                try {
                    $this->cardService->createCardForStudent($student);
                    $issued++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        }

        return redirect()->back()
            ->with('success', "Cards issued: $issued, Failed: $failed");
    }

    // ==================== API ENDPOINTS (POS Scanning) ====================

    /**
     * API: Find by admission number
     */
    public function findByAdmission($admission)
    {
        $student = HighSchoolStudent::where('admission_number', $admission)
            ->with('cardAccount')
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $student->full_name,
                'class' => $student->class,
                'admission' => $student->admission_number,
                'photo' => $student->profile_picture ? asset('storage/' . $student->profile_picture) : null,
            ],
            'card' => $student->cardAccount ? [
                'id' => $student->cardAccount->id,
                'balance' => $student->cardAccount->balance,
                'daily_limit' => $student->cardAccount->daily_limit,
                'today_spent' => $student->cardAccount->today_spent,
                'is_active' => $student->cardAccount->is_active,
                'is_locked' => $student->cardAccount->is_locked,
            ] : null
        ]);
    }

    /**
     * API: Find by card number
     */
    public function findByCardNumber($cardNumber)
    {
        $card = CardAccount::where('card_number', $cardNumber)
            ->orWhere('account_number', $cardNumber)
            ->with('student')
            ->first();

        if (!$card) {
            return response()->json(['success' => false, 'message' => 'Card not found'], 404);
        }

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $card->student_name,
                'class' => $card->student_class,
                'admission' => $card->student_admission_number,
                'photo' => $card->student_photo ? asset('storage/' . $card->student_photo) : null,
            ],
            'card' => [
                'id' => $card->id,
                'balance' => $card->balance,
                'daily_limit' => $card->daily_limit,
                'today_spent' => $card->today_spent,
                'is_active' => $card->is_active,
                'is_locked' => $card->is_locked,
            ]
        ]);
    }

    /**
     * API: Scan card (for POS)
     */
    public function scan(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string'
        ]);

        return $this->findByCardNumber($request->card_number);
    }

    /**
     * API: Process purchase (for POS)
     */
    public function processPurchase(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:card_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'sale_id' => 'nullable|exists:sales,id',
            'items' => 'nullable|array'
        ]);

        $card = CardAccount::findOrFail($request->card_id);

        $result = $this->cardService->processPurchase(
            $card,
            $request->amount,
            $request->sale_id,
            $request->items ?? []
        );

        return response()->json($result);
    }

    /**
     * API: Get student transactions
     */
    public function studentTransactions(HighSchoolStudent $student, Request $request)
    {
        $limit = $request->get('limit', 20);
        $transactions = $student->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }
}
