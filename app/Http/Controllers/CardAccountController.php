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
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class CardAccountController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Print QR code - supports full ID card view
     */
    public function printQr(CardAccount $cardAccount, Request $request)
    {
        $card = $cardAccount->load('student');

        if ($request->get('full') == 'true') {
            return view('ktvtc.finance.high-school.cards.print-id', compact('card'));
        }

        return view('ktvtc.finance.high-school.cards.print-qr', compact('card'));
    }

    /**
     * Print full ID card
     */
    public function printId(CardAccount $cardAccount)
    {
        $card = $cardAccount->load('student');
        return view('ktvtc.finance.high-school.cards.print-id', compact('card'));
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
        try {
            $query = HighSchoolStudent::with('cardAccount');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('admission_number', 'like', "%{$search}%")
                      ->orWhere('parent_phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('class')) {
                $query->where('class', $request->class);
            }

            if ($request->filled('card_status')) {
                if ($request->card_status === 'without_card') {
                    $query->doesntHave('cardAccount');
                } elseif ($request->card_status === 'with_card') {
                    $query->has('cardAccount');
                }
            }

            $query->where('status', 'active');

            $students = $query->orderBy('full_name')
                ->paginate(20)
                ->withQueryString();

            $totalStudents = HighSchoolStudent::where('status', 'active')->count();
            $withCards = HighSchoolStudent::where('status', 'active')->has('cardAccount')->count();
            $withoutCards = $totalStudents - $withCards;
            $qrGenerated = CardAccount::whereNotNull('qr_code')->count();

            $classes = HighSchoolStudent::select('class')
                ->distinct()
                ->whereNotNull('class')
                ->pluck('class')
                ->filter();

            return view('ktvtc.finance.high-school.cards.create', compact(
                'students',
                'totalStudents',
                'withCards',
                'withoutCards',
                'qrGenerated',
                'classes'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading create card page: ' . $e->getMessage());
            return back()->with('error', 'Failed to load page: ' . $e->getMessage());
        }
    }

    /**
     * Store a new card (single)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:high_school_students,id',
            'daily_limit' => 'nullable|numeric|min:0',
            'per_transaction_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student = HighSchoolStudent::findOrFail($request->student_id);

        if ($student->cardAccount) {
            $message = 'Student already has a card: ' . $student->cardAccount->card_number;
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $card = $this->cardService->createCardForStudent(
                $student,
                $request->daily_limit ?? 500,
                $request->per_transaction_limit ?? 300
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card issued successfully!',
                    'card_number' => $card->card_number,
                    'card_id' => $card->id
                ]);
            }

            return redirect()->route('finance.cards.show', $card)
                ->with('success', 'Card issued successfully! Card number: ' . $card->card_number);

        } catch (\Exception $e) {
            \Log::error('Single card issuance failed: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to issue card: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to issue card: ' . $e->getMessage());
        }
    }

    /**
     * Bulk issue cards - FIXED with JSON response
     */
    public function bulkIssue(Request $request)
    {
        $studentIds = $request->student_ids;

        if (empty($studentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No students selected'
            ], 400);
        }

        // Handle different input formats
        if (is_string($studentIds)) {
            $decoded = json_decode($studentIds, true);
            if (is_array($decoded)) {
                $studentIds = $decoded;
            } else {
                $studentIds = explode(',', $studentIds);
            }
        }

        if (!is_array($studentIds) || empty($studentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student data format'
            ], 400);
        }

        $studentIds = array_unique($studentIds);
        $issued = 0;
        $failed = 0;
        $errors = [];
        $successStudents = [];

        foreach ($studentIds as $studentId) {
            try {
                $student = HighSchoolStudent::find($studentId);

                if (!$student) {
                    $failed++;
                    $errors[] = "Student ID {$studentId}: Not found";
                    continue;
                }

                if ($student->cardAccount) {
                    $failed++;
                    $errors[] = "Student {$student->full_name} (ID: {$studentId}): Already has a card";
                    continue;
                }

                $card = $this->cardService->createCardForStudent($student, 500, 300);

                if ($card) {
                    $issued++;
                    $successStudents[] = $student->full_name;
                    \Log::info('Card issued successfully via bulk', [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'card_number' => $card->card_number
                    ]);
                } else {
                    $failed++;
                    $errors[] = "Student {$student->full_name}: Card creation failed";
                }

            } catch (\Exception $e) {
                $failed++;
                $studentName = $student->full_name ?? 'Unknown';
                $errors[] = "Student {$studentName} (ID: {$studentId}): " . $e->getMessage();
                \Log::error('Bulk card issuance error', [
                    'student_id' => $studentId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $response = [
            'success' => $issued > 0,
            'message' => "Cards issued: {$issued} successful, {$failed} failed.",
            'issued' => $issued,
            'failed' => $failed,
            'errors' => $errors,
            'success_students' => $successStudents
        ];

        \Log::info('Bulk card issuance summary', [
            'issued' => $issued,
            'failed' => $failed,
            'total_attempted' => count($studentIds)
        ]);

        return response()->json($response);
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

            CardAuditLog::log(
                $cardAccount->id,
                'limit_change',
                'Card limits updated',
                null,
                json_encode($request->all())
            );

            return redirect()->route('finance.cards.show', $cardAccount)
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
            return redirect()->route('finance.cards.index')
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

        CardAuditLog::log($cardAccount->id, 'block', 'Card blocked: ' . $request->reason);
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
     * Download single card as PDF
     */
    public function downloadPdf(CardAccount $cardAccount)
    {
        $card = $cardAccount->load('student');

        if (!$card->qr_code && $card->student) {
            $qrService = app(\App\Services\CardQrCodeService::class);
            $qrService->generateQrCode($card, $card->student);
            $card->refresh();
        }

        $pdf = Pdf::loadView('ktvtc.finance.high-school.cards.pdf-id-card', compact('card'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('ID-Card-' . $card->student_name . '.pdf');
    }

    /**
     * Download bulk cards as ZIP
     */
    public function downloadBulk(Request $request)
    {
          $timeout = env('EXPORT_TIMEOUT', 120);
    set_time_limit($timeout);
    ini_set('memory_limit', env('MEMORY_LIMIT', '512M'));
        $request->validate([
            'type' => 'required|in:class,all,selected',
            'class' => 'required_if:type,class|string|nullable',
            'card_ids' => 'required_if:type,selected|array|nullable',
            'card_ids.*' => 'exists:card_accounts,id'
        ]);

        $cards = collect();

        if ($request->type === 'class') {
            $cards = CardAccount::with('student')
                ->where('student_class', $request->class)
                ->where('is_active', true)
                ->get();

            if ($cards->isEmpty()) {
                return redirect()->back()->with('error', 'No cards found for class: ' . $request->class);
            }
            $zipName = 'ID-Cards-' . str_replace(' ', '_', $request->class) . '.zip';

        } elseif ($request->type === 'selected') {
            $cards = CardAccount::with('student')
                ->whereIn('id', $request->card_ids)
                ->get();

            if ($cards->isEmpty()) {
                return redirect()->back()->with('error', 'No cards selected');
            }
            $zipName = 'ID-Cards-Selected.zip';

        } else {
            $cards = CardAccount::with('student')
                ->where('is_active', true)
                ->get();

            if ($cards->isEmpty()) {
                return redirect()->back()->with('error', 'No active cards found');
            }
            $zipName = 'ID-Cards-All-' . date('Y-m-d') . '.zip';
        }

        // Generate QR codes for cards that don't have them
        $qrService = app(\App\Services\CardQrCodeService::class);
        foreach ($cards as $card) {
            if (!$card->qr_code && $card->student) {
                $qrService->generateQrCode($card, $card->student);
                $card->refresh();
            }
        }

        $zipPath = storage_path('app/temp/' . uniqid() . '.zip');

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Failed to create ZIP file');
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($cards as $card) {
            try {
                $pdf = Pdf::loadView('ktvtc.finance.high-school.cards.pdf-id-card', ['card' => $card]);
                $pdf->setPaper('a4', 'portrait');

                $pdfContent = $pdf->output();
                $fileName = $card->student_name . ' - ' . $card->card_number . '.pdf';
                $fileName = preg_replace('/[^a-zA-Z0-9\-_\. ]/', '', $fileName);

                $zip->addFromString($fileName, $pdfContent);
                $successCount++;

            } catch (\Exception $e) {
                $failedCount++;
                \Log::error('Failed to generate PDF for card: ' . $card->id, ['error' => $e->getMessage()]);
            }
        }

        $zip->close();

        if ($successCount === 0) {
            unlink($zipPath);
            return redirect()->back()->with('error', 'No PDFs were generated successfully.');
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    // ==================== API ENDPOINTS ====================

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

    public function scan(Request $request)
    {
        $request->validate(['card_number' => 'required|string']);
        return $this->findByCardNumber($request->card_number);
    }
/**
 * Get all students without cards (for bulk issue)
 */
public function getStudentsWithoutCards(Request $request)
{
    try {
        // The relationship should work now since both models use 'high_school_student_id'
        $students = HighSchoolStudent::where('status', 'active')
            ->whereDoesntHave('cardAccount')  // This uses the relationship correctly
            ->select('id', 'full_name', 'admission_number', 'class')
            ->orderBy('full_name')
            ->get();

        return response()->json([
            'success' => true,
            'student_ids' => $students->pluck('id')->toArray(),
            'students' => $students,
            'count' => $students->count()
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getStudentsWithoutCards: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch students: ' . $e->getMessage()
        ], 500);
    }
}
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
