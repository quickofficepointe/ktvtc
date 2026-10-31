<?php
// app/Http/Controllers/CardFundingRequestController.php

namespace App\Http\Controllers;

use App\Models\CardFundingRequest;
use App\Models\CardAccount;
use App\Models\HighSchoolKcbTransaction;
use App\Models\CardAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CardFundingRequestController extends Controller
{

    /**
     * List all funding requests
     */
    public function index(Request $request)
    {
        $query = CardFundingRequest::with('cardAccount.student');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('parent_phone', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        $pending = CardFundingRequest::whereIn('status', ['pending', 'processing'])->count();
        $completed = CardFundingRequest::where('status', 'completed')->count();
        $failed = CardFundingRequest::where('status', 'failed')->count();

        return view('ktvtc.finance.high-school.funding.index', compact('requests', 'pending', 'completed', 'failed'));
    }

    /**
     * Show funding request details
     */
    public function show(CardFundingRequest $cardFundingRequest)
    {
        $cardFundingRequest->load('cardAccount.student');
        return view('ktvtc.finance.high-school.funding.show', compact('cardFundingRequest'));
    }

    /**
     * Manual completion is no longer allowed: a card is only credited from a real
     * KCB payment, assigned in Finance > KCB Payments.
     */
    public function complete(Request $request, CardFundingRequest $cardFundingRequest)
    {
        return redirect()->route('finance.kcb-payments.index', ['q' => $cardFundingRequest->parent_phone])
            ->with('info', 'Cards are credited from the received KCB payment. Find it here and assign it to the card.');
    }

    /**
     * Mark funding request as failed
     */
    public function fail(Request $request, CardFundingRequest $cardFundingRequest)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        if ($cardFundingRequest->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot fail completed funding');
        }

        $cardFundingRequest->status = 'failed';
        $cardFundingRequest->failure_reason = $request->reason;
        $cardFundingRequest->save();

        return redirect()->route('funding.show', $cardFundingRequest)
            ->with('success', 'Funding marked as failed');
    }

    /**
     * Retry funding request
     */
    public function retry(CardFundingRequest $cardFundingRequest)
    {
        // The STK service this used (KcbCardFundingService) never existed, so retry always crashed.
        // Parents start a new payment from their funding link instead.
        return redirect()->back()
            ->with('info', 'Ask the parent to pay again from their funding link: ' . route('parent.fund.portal', $cardFundingRequest->cardAccount->student->admission_number ?? ''));
    }

    /**
     * Pending funding requests
     */
    public function pending()
    {
        $requests = CardFundingRequest::whereIn('status', ['pending', 'processing'])
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.high-school.funding.pending', compact('requests'));
    }

    /**
     * Failed funding requests
     */
    public function failed()
    {
        $requests = CardFundingRequest::where('status', 'failed')
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.high-school.funding.failed', compact('requests'));
    }

    /**
     * Completed funding requests
     */
    public function completed()
    {
        $requests = CardFundingRequest::where('status', 'completed')
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('ktvtc.finance.high-school.funding.completed', compact('requests'));
    }

    /**
     * Export funding requests
     */
    public function export(Request $request)
    {
        return redirect()->back()->with('info', 'Export functionality coming soon');
    }

    // ============================================================
    // KCB CALLBACK
    // ============================================================

    /**
     * Handle KCB callback for card funding
     * Route: POST /api/kcb/card/funding/callback
     */
    public function handleCallback(Request $request)
    {
        Log::info('=== CARD FUNDING CALLBACK RECEIVED ===', $request->all());

        try {
            $data = $request->all();
            $callback = $data['Body']['stkCallback'] ?? null;

            if (!$callback) {
                Log::error('Invalid card funding callback structure', $data);
                return response()->json(['error' => 'Invalid callback'], 400);
            }

            $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
            $resultCode = $callback['ResultCode'] ?? null;
            $resultDesc = $callback['ResultDesc'] ?? '';

            if (!$checkoutRequestId) {
                Log::error('Missing CheckoutRequestID in card funding callback');
                return response()->json(['error' => 'Missing CheckoutRequestID'], 400);
            }

            Log::info('=== PROCESSING CARD FUNDING CALLBACK ===', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc
            ]);

            $transaction = HighSchoolKcbTransaction::where('checkout_request_id', $checkoutRequestId)
                ->where('transaction_type', 'card_funding')
                ->first();

            if ($transaction) {
                return $this->handleHighSchoolFundingCallback($transaction, $callback, $data);
            }

            $fundingRequest = CardFundingRequest::where('checkout_request_id', $checkoutRequestId)->first();

            if ($fundingRequest) {
                return $this->handleFundingRequestCallback($fundingRequest, $callback, $data);
            }

            Log::error('Card funding transaction not found for callback', [
                'checkout_request_id' => $checkoutRequestId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to process card funding callback: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle high school funding callback
     */
    protected function handleHighSchoolFundingCallback($transaction, $callback, $data)
    {
        $resultCode = $callback['ResultCode'] ?? null;

        if ($resultCode == 0) {
            return $this->completeHighSchoolFunding($transaction, $callback, $data);
        } else {
            return $this->failHighSchoolFunding($transaction, $callback, $data);
        }
    }

    /**
     * STK callback for a successful card top-up: status only.
     *
     * The card is credited when KCB's IPN for the same payment arrives
     * (KcbIpnRouter). Crediting here as well is what caused double credits.
     */
    protected function completeHighSchoolFunding($transaction, $callback, $data)
    {
        if ($transaction->status === 'completed') {
            return response()->json(['success' => true, 'message' => 'Already confirmed']);
        }

        $mpesaReceipt = null;
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (($item['Name'] ?? null) === 'MpesaReceiptNumber') {
                $mpesaReceipt = $item['Value'] ?? null;
            }
        }

        $transaction->status = 'completed';
        $transaction->mpesa_receipt_number = $mpesaReceipt;
        $transaction->result_code = 0;
        $transaction->result_description = 'Paid. Card is credited when the bank notification (IPN) arrives.';
        $transaction->callback_data = json_encode($data);
        $transaction->completed_at = now();
        $transaction->save();

        Log::info('Card funding STK confirmed; awaiting IPN to credit', [
            'transaction_id' => $transaction->id,
            'receipt'        => $mpesaReceipt,
            'amount'         => $transaction->amount,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment confirmed']);
    }

    /**
     * Fail high school funding
     */
    protected function failHighSchoolFunding($transaction, $callback, $data)
    {
        try {
            $resultCode = $callback['ResultCode'] ?? null;
            $resultDesc = $callback['ResultDesc'] ?? 'Payment failed';

            $transaction->status = 'failed';
            $transaction->result_code = $resultCode;
            $transaction->result_description = $resultDesc;
            $transaction->callback_data = json_encode($data);
            $transaction->save();

            $card = CardAccount::find($transaction->card_account_id);
            if ($card) {
                CardAuditLog::log(
                    $card->id,
                    'funding_failed',
                    "Card funding failed: {$resultDesc}",
                    null,
                    $card->card_number,
                    [
                        'amount' => $transaction->amount,
                        'result_code' => $resultCode,
                        'result_description' => $resultDesc,
                    ]
                );
            }

            Log::warning('High school funding failed', [
                'transaction_id' => $transaction->id,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $resultDesc
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process failed funding: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process'], 500);
        }
    }

    /**
     * Handle CardFundingRequest callback (legacy system)
     */
    protected function handleFundingRequestCallback($fundingRequest, $callback, $data)
    {
        $resultCode = $callback['ResultCode'] ?? null;
        $resultDesc = $callback['ResultDesc'] ?? '';

        if ($resultCode == 0) {
            $metadata = $callback['CallbackMetadata']['Item'] ?? [];
            $mpesaReceipt = null;
            foreach ($metadata as $item) {
                if (isset($item['Name']) && $item['Name'] == 'MpesaReceiptNumber') {
                    $mpesaReceipt = $item['Value'];
                }
            }

            $fundingRequest->status = 'completed';
            $fundingRequest->mpesa_receipt = $mpesaReceipt;
            $fundingRequest->completed_at = now();
            $fundingRequest->save();

            Log::info('Funding request completed via callback', [
                'request_id' => $fundingRequest->id,
                'receipt' => $mpesaReceipt
            ]);

            return response()->json(['success' => true, 'message' => 'Funding completed']);
        } else {
            $fundingRequest->status = 'failed';
            $fundingRequest->failure_reason = $resultDesc;
            $fundingRequest->save();

            Log::warning('Funding request failed via callback', [
                'request_id' => $fundingRequest->id,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc
            ]);

            return response()->json(['success' => false, 'message' => 'Payment failed']);
        }
    }

    /**
     * Check funding status
     * Route: POST /api/kcb/card/funding/status
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        $transaction = HighSchoolKcbTransaction::where('checkout_request_id', $request->checkout_request_id)
            ->where('transaction_type', 'card_funding')
            ->first();

        if ($transaction) {
            return response()->json([
                'success' => true,
                'status' => $transaction->status,
                'result_code' => $transaction->result_code,
                'result_description' => $transaction->result_description,
                'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
                'amount' => $transaction->amount,
            ]);
        }

        $fundingRequest = CardFundingRequest::where('checkout_request_id', $request->checkout_request_id)->first();

        if ($fundingRequest) {
            return response()->json([
                'success' => true,
                'status' => $fundingRequest->status,
                'amount' => $fundingRequest->amount,
                'mpesa_receipt' => $fundingRequest->mpesa_receipt,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transaction not found'
        ], 404);
    }
}