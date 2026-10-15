<?php
// app/Http/Controllers/CardFundingRequestController.php

namespace App\Http\Controllers;

use App\Models\CardFundingRequest;
use App\Models\CardAccount;
use App\Models\HighSchoolKcbTransaction;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Services\CardService;
use App\Services\KcbCardFundingService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardFundingRequestController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

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
     * Mark funding request as completed
     */
    public function complete(Request $request, CardFundingRequest $cardFundingRequest)
    {
        $request->validate([
            'mpesa_receipt' => 'required|string'
        ]);

        if ($cardFundingRequest->status === 'completed') {
            return redirect()->back()->with('error', 'Funding already completed');
        }

        $result = $this->cardService->completeFunding(
            $cardFundingRequest->checkout_request_id,
            [
                'transaction_id' => $cardFundingRequest->ipn_transaction_id ?? 'MANUAL-' . time(),
                'mpesa_receipt' => $request->mpesa_receipt,
                'fee_payment_id' => null
            ]
        );

        if ($result['success']) {
            return redirect()->route('funding.show', $cardFundingRequest)
                ->with('success', 'Funding completed successfully');
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
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
        if (!$cardFundingRequest->canRetry()) {
            return redirect()->back()
                ->with('error', 'This request cannot be retried. Max retries reached or expired.');
        }

        $cardFundingRequest->incrementRetry();
        $cardFundingRequest->status = 'pending';
        $cardFundingRequest->save();

        $kcbService = app(KcbCardFundingService::class);
        $result = $kcbService->initiateSTKPush(
            $cardFundingRequest->parent_phone,
            $cardFundingRequest->amount,
            'Card Funding for ' . $cardFundingRequest->student_name,
            $cardFundingRequest->id
        );

        if (isset($result['error'])) {
            $cardFundingRequest->status = 'failed';
            $cardFundingRequest->failure_reason = $result['error'];
            $cardFundingRequest->save();

            return redirect()->back()->with('error', 'Retry failed: ' . $result['error']);
        }

        $cardFundingRequest->checkout_request_id = $result['checkout_request_id'];
        $cardFundingRequest->kcb_invoice_number = $result['kcb_invoice_number'] ?? null;
        $cardFundingRequest->status = 'processing';
        $cardFundingRequest->save();

        return redirect()->route('funding.show', $cardFundingRequest)
            ->with('success', 'Retry initiated successfully');
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
     * Complete high school funding with duplicate prevention + IPN cross-check
     */
    protected function completeHighSchoolFunding($transaction, $callback, $data)
    {
        try {
            DB::beginTransaction();

            // CHECK 1: already completed?
            if ($transaction->status === 'completed') {
                Log::info('High school funding already completed - skipping', [
                    'transaction_id' => $transaction->id
                ]);
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Already completed']);
            }

            // Extract metadata
            $metadata = $callback['CallbackMetadata']['Item'] ?? [];
            $mpesaReceipt = null;
            $amount = $transaction->amount;
            $transactionDate = null;

            foreach ($metadata as $item) {
                if (isset($item['Name']) && isset($item['Value'])) {
                    if ($item['Name'] == 'MpesaReceiptNumber') {
                        $mpesaReceipt = $item['Value'];
                    }
                    if ($item['Name'] == 'Amount') {
                        $amount = (float) $item['Value'];
                    }
                    if ($item['Name'] == 'TransactionDate') {
                        $transactionDate = $item['Value'];
                    }
                }
            }

            if (empty($mpesaReceipt)) {
                Log::error('No M-Pesa receipt in callback', [
                    'transaction_id' => $transaction->id,
                    'metadata' => $metadata
                ]);
                DB::rollBack();
                return response()->json(['error' => 'No M-Pesa receipt found'], 400);
            }

            // CHECK 2: same receipt already processed?
            $existingTransaction = CardTransaction::where('mpesa_receipt', $mpesaReceipt)->first();
            if ($existingTransaction) {
                Log::warning('⚠️ DUPLICATE TRANSACTION DETECTED - Skipping', [
                    'mpesa_receipt' => $mpesaReceipt,
                    'existing_transaction_id' => $existingTransaction->id,
                    'existing_reference' => $existingTransaction->reference,
                    'new_transaction_id' => $transaction->id
                ]);

                $transaction->status = 'completed';
                $transaction->mpesa_receipt_number = $mpesaReceipt;
                $transaction->result_code = 0;
                $transaction->result_description = 'Duplicate - already processed (existing transaction ID: ' . $existingTransaction->id . ')';
                $transaction->callback_data = json_encode($data);
                $transaction->completed_at = now();
                $transaction->save();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Duplicate already processed']);
            }

            // CHECK 3: same reference already processed?
            $reference = $transaction->invoice_number;
            if (!empty($reference)) {
                $existingByReference = CardTransaction::where('reference', $reference)->first();
                if ($existingByReference) {
                    Log::warning('⚠️ DUPLICATE BY REFERENCE DETECTED - Skipping', [
                        'reference' => $reference,
                        'existing_transaction_id' => $existingByReference->id,
                        'new_transaction_id' => $transaction->id
                    ]);

                    $transaction->status = 'completed';
                    $transaction->mpesa_receipt_number = $mpesaReceipt;
                    $transaction->result_code = 0;
                    $transaction->result_description = 'Duplicate by reference - already processed';
                    $transaction->callback_data = json_encode($data);
                    $transaction->completed_at = now();
                    $transaction->save();

                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Duplicate already processed']);
                }
            }

            // Get card WITH row lock
            $card = CardAccount::lockForUpdate()->find($transaction->card_account_id);
            if (!$card) {
                Log::error('Card not found for high school funding', [
                    'card_id' => $transaction->card_account_id
                ]);
                DB::rollBack();
                return response()->json(['error' => 'Card not found'], 404);
            }

            // CHECK 4 (NEW): IPN already credited this same payment?
            $recentFromIpn = CardTransaction::where('card_account_id', $card->id)
                ->where('transaction_type', 'funding')
                ->where('status', 'completed')
                ->where('amount', $amount)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->where('mpesa_receipt', 'LIKE', 'FT%')
                ->where(function ($q) use ($reference) {
                    $q->where('reference', 'LIKE', '8118445#%')
                      ->orWhere('description', 'LIKE', '%' . $reference . '%');
                })
                ->first();

            if ($recentFromIpn) {
                Log::warning('STK callback: SKIPPED — already credited via IPN', [
                    'card_id'      => $card->id,
                    'amount'       => $amount,
                    'reference'    => $reference,
                    'ipn_tx'       => $recentFromIpn->id,
                    'stk_txn'      => $transaction->id,
                ]);

                $transaction->status = 'completed';
                $transaction->mpesa_receipt_number = $mpesaReceipt;
                $transaction->result_code = 0;
                $transaction->result_description = 'Skipped — already credited via IPN (tx #' . $recentFromIpn->id . ')';
                $transaction->callback_data = json_encode($data);
                $transaction->completed_at = now();
                $transaction->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Already credited via IPN',
                ]);
            }

            // Log duplicate check summary
            Log::info('Duplicate check passed - proceeding with funding', [
                'transaction_id' => $transaction->id,
                'receipt' => $mpesaReceipt,
                'reference' => $reference,
                'amount' => $amount
            ]);

            // Get request data
            $requestData = $transaction->request_data ?? [];
            $typeLabel = $requestData['type_label'] ?? 'Funding';
            $fundingType = $requestData['funding_type'] ?? 'T';

            $student = $transaction->highSchoolStudent;
            $studentName = $student ? $student->full_name : 'Unknown Student';

            // Fund the card
            $balanceBefore = $card->balance;
            $card->balance += $amount;
            $card->total_funded += $amount;
            $card->last_funded_at = now();
            $card->save();

            // Create card transaction
            $cardTransaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $transaction->high_school_student_id,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $card->balance,
                'description' => "{$typeLabel} funding via M-Pesa - Receipt: {$mpesaReceipt} - Student: {$studentName}",
                'mpesa_receipt' => $mpesaReceipt,
                'reference' => $reference ?? $mpesaReceipt,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $transaction->checkout_request_id,
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'student_id' => $transaction->high_school_student_id,
                    'student_name' => $studentName,
                ])
            ]);

            // Update KCB transaction
            $transaction->status = 'completed';
            $transaction->mpesa_receipt_number = $mpesaReceipt;
            $transaction->result_code = 0;
            $transaction->result_description = 'Payment successful - Transaction ID: ' . $cardTransaction->id;
            $transaction->callback_data = json_encode($data);
            $transaction->completed_at = now();
            $transaction->save();

            // Audit
            CardAuditLog::log(
                $card->id,
                'funding_completed',
                "Card funded: KES {$amount} ({$typeLabel}) Receipt: {$mpesaReceipt} - Student: {$studentName}",
                $balanceBefore,
                $card->balance,
                [
                    'amount' => $amount,
                    'mpesa_receipt' => $mpesaReceipt,
                    'new_balance' => $card->balance,
                    'student_id' => $transaction->high_school_student_id,
                    'transaction_id' => $cardTransaction->id
                ]
            );

            DB::commit();

            Log::info('✅ High school funding completed successfully', [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'receipt' => $mpesaReceipt,
                'new_balance' => $card->balance,
                'student_id' => $transaction->high_school_student_id,
                'card_transaction_id' => $cardTransaction->id
            ]);

            $this->sendFundingSMS($student, $amount, $fundingType, $mpesaReceipt, $card->balance);

            return response()->json([
                'success' => true,
                'message' => 'Funding completed successfully',
                'receipt' => $mpesaReceipt,
                'new_balance' => $card->balance,
                'transaction_id' => $cardTransaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Failed to complete high school funding: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to complete funding: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send SMS with proper error handling (doesn't break transaction)
     */
    protected function sendFundingSMS($student, $amount, $fundingType, $mpesaReceipt, $newBalance)
    {
        if (!$student || !$student->parent_phone) {
            Log::warning('No student or phone number for SMS', [
                'student_id' => $student->id ?? null
            ]);
            return;
        }

        try {
            $smsService = app(SmsService::class);

            if (!$smsService) {
                Log::warning('SMS service not available');
                return;
            }

            $result = $smsService->sendFundingConfirmation(
                $student,
                $amount,
                $fundingType,
                $mpesaReceipt,
                $newBalance
            );

            Log::info('Funding confirmation SMS sent', [
                'student_id' => $student->id,
                'amount' => $amount,
                'type' => $fundingType,
                'success' => $result['success'] ?? false
            ]);

        } catch (\Exception $e) {
            Log::error('SMS sending failed but transaction completed successfully', [
                'student_id' => $student->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
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