<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
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

        return view('ktvtc.enrollment-terms.show', compact('enrollment', 'ledger', 'next', 'credit', 'layout'));
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
