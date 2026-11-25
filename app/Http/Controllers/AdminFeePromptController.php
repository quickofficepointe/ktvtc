<?php

namespace App\Http\Controllers;

use App\Models\KcbBuniTransaction;
use App\Models\Student;
use App\Services\Payments\FeePromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admissions collecting school fees by M-Pesa from the student's page (any amount).
 * Before enrollment the money is held as a deposit; enrolling applies it to the fees.
 */
class AdminFeePromptController extends Controller
{
    public function __construct(private FeePromptService $prompts) {}

    public function send(Request $request, Student $student)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:500000',
            'phone'  => 'required|string|max:20',
        ]);

        try {
            $tx = $this->prompts->send($student, $data['phone'], (float) $data['amount'], Auth::id());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['id' => $tx->id] + $this->prompts->status($tx));
    }

    public function status(Student $student, KcbBuniTransaction $transaction)
    {
        abort_unless($transaction->invoice_number === $this->prompts->account($student), 404);

        return response()->json($this->prompts->status($transaction));
    }

    public function textDetails(Request $request, Student $student)
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);
        try {
            $this->prompts->textDetails($student, $data['phone']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment details texted to ' . $data['phone'] . '.');
    }
}
