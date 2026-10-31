<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeeAdjustment;
use App\Models\TermRegistration;
use App\Services\Payments\FeeAdjustmentService;
use App\Services\Terms\ExamCardGate;
use App\Services\Terms\TermLedger;
use App\Services\Terms\TermRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Finance / admin view of a student's terms on one enrollment:
 * register the next term (Term 1 for new intakes), override the 50% rule
 * with a reason, or cancel a registration made by mistake.
 */
class EnrollmentTermController extends Controller
{
    public function __construct(
        private TermRegistrationService $terms,
        private TermLedger $ledger,
        private ExamCardGate $examCards,
        private FeeAdjustmentService $adjustments,
    ) {}

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'course']);
        $this->terms->syncLegacy($enrollment);
        $enrollment->refresh();

        $ledger = $this->ledger->forEnrollment($enrollment)
            ->map(fn ($row) => $row + ['exam_card' => $this->examCards->check($row['registration'])]);
        $next   = $this->terms->nextTermStatus($enrollment);
        $credit = $this->ledger->credit($enrollment);
        $layout = Auth::user()->role == 7 ? 'ktvtc.finance.layouts.app' : 'ktvtc.admin.layout.adminlayout';
        $adjustments = FeeAdjustment::with(['termRegistration', 'creator'])->where('enrollment_id', $enrollment->id)->latest('id')->get();
        $isFinance = (int) Auth::user()->role === 7;

        return view('ktvtc.enrollment-terms.show', compact('enrollment', 'ledger', 'next', 'credit', 'layout', 'adjustments', 'isFinance'));
    }

    public function register(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate(['override_reason' => 'nullable|string|min:5|max:255']);

        try {
            $reg = $this->terms->staffRegister($enrollment, Auth::id(), $data['override_reason'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Registered for Term {$reg->term_number}. Fee: KES " . number_format($reg->fee_amount, 2) . '.');
    }

    /**
     * Discount, waiver, bursary or extra charge on one registered term.
     */
    public function adjust(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'term_registration_id' => 'required|integer',
            'type'                 => 'required|in:' . implode(',', array_keys(FeeAdjustment::TERM_TYPES)),
            'amount'               => 'required|numeric|min:1',
            'reason'               => 'required|string|min:5|max:255',
        ]);
        $registration = TermRegistration::where('enrollment_id', $enrollment->id)->findOrFail($data['term_registration_id']);

        try {
            $adj = $this->adjustments->adjustTerm($registration, $data['type'], (float) $data['amount'], $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$adj->label}: Term {$registration->term_number} fee is now KES " . number_format($registration->fresh()->fee_amount, 2) . '.');
    }

    /**
     * Pay back money paid beyond the fees.
     */
    public function refund(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:1',
            'method'    => 'required|in:cash,mpesa,bank',
            'reference' => 'nullable|required_if:method,mpesa,bank|string|max:100',
            'reason'    => 'required|string|min:5|max:255',
        ]);

        try {
            $this->adjustments->refund($enrollment, (float) $data['amount'], $data['method'], $data['reference'] ?? null, $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Refund of KES ' . number_format($data['amount'], 2) . ' recorded.');
    }

    public function cancel(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:255']);

        try {
            $reg = $this->terms->cancelLatest($enrollment, $data['reason'], Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Term {$reg->term_number} registration cancelled.");
    }
}
