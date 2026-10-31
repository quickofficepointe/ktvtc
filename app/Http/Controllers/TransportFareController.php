<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\FareDisbursement;
use App\Models\WithdrawalFeeTier;
use App\Services\Cards\CardWallet;
use App\Services\Cards\FareDisbursementService;
use App\Services\Cards\InsufficientWalletBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Finance: transport money on high-school cards, and the cash fare handed out against it.
 */
class TransportFareController extends Controller
{
    public function __construct(private FareDisbursementService $fares) {}

    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);

        $cards = CardAccount::query()
            ->with('student')
            ->when($request->filled('class'), fn ($q) => $q->where('student_class', $request->class))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(fn ($w) => $w->where('student_name', 'like', "%{$term}%")
                    ->orWhere('student_admission_number', $term)
                    ->orWhere('card_number', 'like', "%{$term}%"));
            })
            ->orderByDesc('transport_balance')
            ->paginate(30)->withQueryString();

        $funded = CardTransaction::where('wallet', CardWallet::TRANSPORT)->where('transaction_type', 'funding')
            ->where('status', 'completed')->whereBetween('created_at', [$from, $to->copy()->endOfDay()])->sum('amount');

        $given = FareDisbursement::given()->whereBetween('given_on', [$from, $to]);
        $summary = [
            'liability' => (float) CardAccount::sum('transport_balance'),
            'funded'    => (float) $funded,
            'given'     => (float) (clone $given)->sum('amount'),
            'fees'      => (float) (clone $given)->sum('withdrawal_fee'),
            'count'     => (clone $given)->count(),
            'unsplit'   => CardAccount::whereNull('wallets_split_at')->count(),
        ];

        $recent = FareDisbursement::with(['student', 'recorder'])->latest('id')->limit(15)->get();
        $classes = CardAccount::whereNotNull('student_class')->distinct()->orderBy('student_class')->pluck('student_class');

        return view('ktvtc.finance.transport.index', compact('cards', 'summary', 'recent', 'classes', 'from', 'to'));
    }

    public function give(Request $request)
    {
        $card = $request->filled('card') ? CardAccount::with('student')->find($request->card) : null;
        if ($card) {
            app(CardWallet::class)->balanceOf($card, CardWallet::TRANSPORT); // split old balance if needed
        }

        $matches = collect();
        if (!$card && $request->filled('q')) {
            $matches = CardAccount::with('student')
                ->where('student_name', 'like', "%{$request->q}%")
                ->orWhere('student_admission_number', $request->q)
                ->orWhere('card_number', 'like', "%{$request->q}%")
                ->limit(15)->get();
        }

        $history = $card
            ? FareDisbursement::where('card_account_id', $card->id)->latest('given_on')->latest('id')->limit(20)->get()
            : collect();

        $feeTiers = WithdrawalFeeTier::orderBy('min_amount')->get(['min_amount', 'max_amount', 'fee']);

        return view('ktvtc.finance.transport.give', compact('card', 'matches', 'history', 'feeTiers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'card_account_id' => 'required|exists:card_accounts,id',
            'amount'          => 'required|numeric|min:1|max:50000',
            'given_on'        => 'required|date|before_or_equal:today',
            'notes'           => 'nullable|string|max:255',
            'charge_fee'      => 'nullable|boolean',
            'allow_negative'  => 'nullable|boolean',
        ]);

        $card = CardAccount::findOrFail($data['card_account_id']);
        $givenOn = Carbon::parse($data['given_on']);
        $allowNegative = $request->boolean('allow_negative');

        if ($allowNegative && !$givenOn->lt(today()) && empty($data['notes'])) {
            return back()->withInput()->with('error', 'Going below zero is only for recording past fare. Add a note explaining it.');
        }

        try {
            $d = $this->fares->give($card, (float) $data['amount'], $givenOn, Auth::id(), $data['notes'] ?? null,
                $request->boolean('charge_fee', true), $allowNegative);
        } catch (InsufficientWalletBalance $e) {
            return back()->withInput()->with('error', $e->getMessage()
                . ' If this fare was already given in the past, tick "Record even if balance goes below zero".');
        }

        return redirect()->route('finance.transport.give', ['card' => $card->id])->with('success', sprintf(
            'Recorded KES %s fare%s for %s. Transport balance now KES %s.',
            number_format($d->amount, 2),
            $d->withdrawal_fee > 0 ? ' + KES ' . number_format($d->withdrawal_fee, 2) . ' withdrawal fee' : '',
            $card->student_name,
            number_format($card->fresh()->transport_balance, 2)
        ));
    }

    public function batch(Request $request)
    {
        $classes = CardAccount::whereNotNull('student_class')->distinct()->orderBy('student_class')->pluck('student_class');
        $cards = collect();

        if ($request->filled('class')) {
            $cards = CardAccount::with('student')->where('student_class', $request->class)
                ->where('is_active', true)->orderBy('student_name')->get();
            $wallet = app(CardWallet::class);
            $cards->each(fn ($c) => $wallet->balanceOf($c, CardWallet::TRANSPORT));
        }

        $feeTiers = WithdrawalFeeTier::orderBy('min_amount')->get(['min_amount', 'max_amount', 'fee']);

        return view('ktvtc.finance.transport.batch', compact('classes', 'cards', 'feeTiers'));
    }

    public function batchStore(Request $request)
    {
        $data = $request->validate([
            'given_on'       => 'required|date|before_or_equal:today',
            'notes'          => 'nullable|string|max:255',
            'charge_fee'     => 'nullable|boolean',
            'allow_negative' => 'nullable|boolean',
            'amounts'        => 'required|array',
            'amounts.*'      => 'nullable|numeric|min:0|max:50000',
        ]);

        $amounts = array_filter($data['amounts'], fn ($a) => (float) $a > 0);
        if (!$amounts) {
            return back()->withInput()->with('error', 'Enter an amount for at least one student.');
        }

        try {
            $done = $this->fares->giveBatch($amounts, Carbon::parse($data['given_on']), Auth::id(), $data['notes'] ?? null,
                $request->boolean('charge_fee', true), $request->boolean('allow_negative'));
        } catch (InsufficientWalletBalance $e) {
            return back()->withInput()->with('error', 'Nothing was saved. One student does not have enough transport money: ' . $e->getMessage());
        }

        return redirect()->route('finance.transport.index')->with('success', sprintf(
            'Recorded fare for %d students: KES %s given + KES %s withdrawal fees (batch %s).',
            count($done),
            number_format(collect($done)->sum('amount'), 2),
            number_format(collect($done)->sum('withdrawal_fee'), 2),
            $done[0]->batch_reference
        ));
    }

    public function reverse(Request $request, FareDisbursement $disbursement)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:255']);

        try {
            $this->fares->reverse($disbursement, $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Fare record reversed; KES ' . number_format($disbursement->total_deducted, 2) . ' returned to transport.');
    }

    public function fees()
    {
        $tiers = WithdrawalFeeTier::orderBy('min_amount')->get();
        return view('ktvtc.finance.transport.fees', compact('tiers'));
    }

    public function updateFees(Request $request)
    {
        $data = $request->validate([
            'tiers'              => 'required|array|min:1',
            'tiers.*.min_amount' => 'required|numeric|min:0',
            'tiers.*.max_amount' => 'required|numeric|gte:tiers.*.min_amount',
            'tiers.*.fee'        => 'required|numeric|min:0',
        ]);

        $tiers = collect($data['tiers'])->sortBy('min_amount')->values();
        foreach ($tiers as $i => $t) {
            if ($i > 0 && $t['min_amount'] <= $tiers[$i - 1]['max_amount']) {
                return back()->withInput()->with('error', "Bands overlap at KES {$t['min_amount']}.");
            }
        }

        DB::transaction(function () use ($tiers) {
            WithdrawalFeeTier::query()->delete();
            foreach ($tiers as $t) {
                WithdrawalFeeTier::create($t);
            }
        });

        return back()->with('success', 'Withdrawal charges saved.');
    }

    private function period(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('to') ? Carbon::parse($request->to)->startOfDay() : today();
        return [$from, $to];
    }
}
