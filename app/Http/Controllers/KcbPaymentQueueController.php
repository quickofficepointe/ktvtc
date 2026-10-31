<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\KcbIpn;
use App\Models\Student;
use App\Services\Payments\KcbIpnRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Finance view of every KCB payment received, and the place where
 * unallocated / quarantined payments are assigned or set aside.
 */
class KcbPaymentQueueController extends Controller
{
    public function __construct(private KcbIpnRouter $router) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'attention');

        $query = KcbIpn::query()->latest('id');

        if ($status === 'attention') {
            $query->awaitingFinance();
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($till = $request->query('till')) {
            $query->where('till_number', $till);
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('transaction_id', 'like', "%{$q}%")
                  ->orWhere('business_key', 'like', "%{$q}%")
                  ->orWhere('debit_msisdn', 'like', "%{$q}%")
                  ->orWhere('first_name', 'like', "%{$q}%");
            });
        }

        $payments = $query->paginate(25)->withQueryString();

        $counts = KcbIpn::selectRaw('status, COUNT(*) as n, SUM(transaction_amount) as total')
            ->groupBy('status')->get()->keyBy('status');

        $tills = config('kcb.tills');

        return view('ktvtc.finance.payments.kcb-index', compact('payments', 'counts', 'status', 'tills'));
    }

    public function show(Request $request, KcbIpn $ipn)
    {
        $searchStudents = collect();
        $searchCards = collect();

        if ($q = trim((string) $request->query('q'))) {
            $searchStudents = Student::where('student_number', 'like', "%{$q}%")
                ->orWhere('legacy_student_code', 'like', "%{$q}%")
                ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$q}%"])
                ->limit(10)->get();

            $searchCards = CardAccount::with('student')
                ->where('card_number', 'like', "%{$q}%")
                ->orWhereHas('student', function ($s) use ($q) {
                    $s->where('admission_number', $q)
                      ->orWhere('full_name', 'like', "%{$q}%");
                })
                ->limit(10)->get();
        }

        // Refresh suggestions for open items (students/cards may have been added since)
        $suggestions = $ipn->needsAttention() ? $this->router->suggestionsFor($ipn) : ($ipn->suggestions ?? []);
        $fundingType = $this->router->fundingTypeOf($ipn) ?? 'C';

        return view('ktvtc.finance.payments.kcb-show', compact('ipn', 'suggestions', 'searchStudents', 'searchCards', 'fundingType'));
    }

    public function assignStudent(Request $request, KcbIpn $ipn)
    {
        $data = $request->validate(['student_id' => 'required|exists:students,id']);

        try {
            $this->router->assignToStudent($ipn, Student::findOrFail($data['student_id']), Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance.kcb-payments.index')
            ->with('success', "Payment {$ipn->transaction_id} applied to student fees.");
    }

    public function assignCard(Request $request, KcbIpn $ipn)
    {
        $data = $request->validate([
            'card_account_id' => 'required|exists:card_accounts,id',
            'funding_type'    => 'nullable|in:T,C',
        ]);

        try {
            $this->router->assignToCard($ipn, CardAccount::findOrFail($data['card_account_id']), Auth::id(), $data['funding_type'] ?? null);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance.kcb-payments.index')
            ->with('success', "Payment {$ipn->transaction_id} credited to the card.");
    }

    /**
     * Take back a payment that was credited to the wrong student or card; it returns to the queue.
     */
    public function undo(Request $request, KcbIpn $ipn)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:255']);

        try {
            $this->router->undoCredit($ipn, $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance.kcb-payments.show', $ipn)
            ->with('success', "Payment {$ipn->transaction_id} taken back. Apply it to the right one below.");
    }

    public function ignore(Request $request, KcbIpn $ipn)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:255']);

        try {
            $this->router->ignore($ipn, $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance.kcb-payments.index')
            ->with('success', "Payment {$ipn->transaction_id} set aside.");
    }
}
