<?php
// app/Http/Controllers/CardFundingIpnController.php

namespace App\Http\Controllers;

use App\Models\HighSchoolKcbTransaction;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Models\HighSchoolStudent;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardFundingIpnController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Handle KCB Till IPN for High School Card Funding
     * Paybill: 522533
     * Account Number Format: 8118445#T101 (Transport) or 8118445#C101 (Canteen)
     */
    public function handlePaymentNotification(Request $request)
    {
        Log::info('=== HIGH SCHOOL CARD FUNDING IPN RECEIVED ===', [
            'all' => $request->all(),
            'json' => $request->json()->all(),
            'content' => $request->getContent()
        ]);

        try {
            $payload = $this->extractPayload($request);

            Log::info('Extracted payload', $payload);

            $transaction = $this->createTransactionRecord($payload);

            $result = $this->reconcilePayment($transaction);

            Log::info('High School Card Funding IPN Processed', ['result' => $result]);

            return response()->json([
                'header' => [
                    'messageID' => $payload['messageID'] ?? $request->input('header.messageID') ?? null,
                    'statusCode' => $result['success'] ? '0' : '1',
                    'statusMessage' => $result['success'] ? 'Notification received successfully' : ($result['message'] ?? 'Processing failed')
                ],
                'responsePayload' => [
                    'transactionInfo' => [
                        'transactionId' => $transaction->transaction_reference ?? $payload['transaction_reference'] ?? null,
                        'student_name' => $result['data']['student_name'] ?? null,
                        'admission_number' => $result['data']['admission_number'] ?? null,
                        'new_balance' => $result['data']['new_balance'] ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('High School Card Funding IPN Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $request->input('header.messageID') ?? null,
                    'statusCode' => '1',
                    'statusMessage' => 'Internal server error'
                ]
            ], 500);
        }
    }

    /**
     * Extract payload from multiple possible formats
     */
    private function extractPayload(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $payload = $request->all();
        }

        if (empty($payload) && $request->getContent()) {
            $decoded = json_decode($request->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        if (isset($payload['requestPayload'])) {
            $requestPayload = $payload['requestPayload'];

            if (isset($payload['header'])) {
                $payload['messageID'] = $payload['header']['messageID'] ?? null;
                $payload['originatorConversationID'] = $payload['header']['originatorConversationID'] ?? null;
                $payload['channelCode'] = $payload['header']['channelCode'] ?? null;
                $payload['timeStamp'] = $payload['header']['timeStamp'] ?? null;
            }

            if (isset($requestPayload['additionalData']['notificationData'])) {
                $notificationData = $requestPayload['additionalData']['notificationData'];
                $payload = array_merge($payload, $notificationData);
            }
        }

        if (isset($payload['Body']['stkCallback'])) {
            $payload = array_merge($payload, $payload['Body']['stkCallback']);
        }

        return $payload;
    }

    /**
     * Create transaction record using HighSchoolKcbTransaction
     */
    private function createTransactionRecord($payload)
    {
        Log::info('Creating High School Card Funding transaction record', $payload);

        $transactionReference = $payload['transaction_reference'] ?? $payload['TransID'] ?? $payload['transaction_id'] ?? null;
        $invoiceNumber = $payload['customer_reference'] ?? $payload['BillRefNumber'] ?? $payload['businessKey'] ?? $payload['invoice_number'] ?? null;
        $transactionAmount = $payload['transaction_amount'] ?? $payload['TransAmount'] ?? $payload['amount'] ?? null;
        $phoneNumber = $payload['customer_mobile_number'] ?? $payload['MSISDN'] ?? $payload['debitMSISDN'] ?? null;
        $customerName = $payload['customer_name'] ?? $payload['FirstName'] ?? $payload['firstName'] ?? null;
        $narration = $payload['narration'] ?? $payload['Narration'] ?? 'Card funding via M-Pesa';
        $resultCode = $payload['result_code'] ?? $payload['ResultCode'] ?? 0;
        $resultDesc = $payload['result_description'] ?? $payload['ResultDesc'] ?? 'Payment successful';

        Log::info('Parsing invoice number', ['invoice_number' => $invoiceNumber]);

        $admissionNumber = null;
        $fundingType = 'G';
        $tillNumber = null;

        if ($invoiceNumber) {
            if (preg_match('/^(\d+)#([A-Za-z])(\d+)$/', $invoiceNumber, $matches)) {
                $tillNumber = $matches[1];
                $fundingType = strtoupper($matches[2]);
                $admissionNumber = $matches[3];
                Log::info('Parsed invoice with type', [
                    'till' => $tillNumber,
                    'type' => $fundingType,
                    'admission' => $admissionNumber
                ]);
            } elseif (preg_match('/^(\d+)#(\d+)$/', $invoiceNumber, $matches)) {
                $tillNumber = $matches[1];
                $admissionNumber = $matches[2];
                Log::info('Parsed invoice without type', [
                    'till' => $tillNumber,
                    'admission' => $admissionNumber
                ]);
            } elseif (preg_match('/^([A-Za-z])(\d+)$/', $invoiceNumber, $matches)) {
                $fundingType = strtoupper($matches[1]);
                $admissionNumber = $matches[2];
                Log::info('Parsed type + admission', [
                    'type' => $fundingType,
                    'admission' => $admissionNumber
                ]);
            } else {
                $admissionNumber = $invoiceNumber;
                Log::info('Using invoice as admission', ['admission' => $admissionNumber]);
            }
        }

        if (!$admissionNumber) {
            $admissionNumber = $payload['customer_reference'] ?? $payload['BillRefNumber'] ?? null;
            Log::info('Using customer_reference as admission', ['admission' => $admissionNumber]);
        }

        Log::info('Final parsed data', [
            'admission_number' => $admissionNumber,
            'funding_type' => $fundingType,
            'till_number' => $tillNumber
        ]);

        $existing = HighSchoolKcbTransaction::where('transaction_reference', $transactionReference)->first();
        if ($existing) {
            Log::warning('Transaction already exists', ['transaction_reference' => $transactionReference]);
            return $existing;
        }

        $transaction = HighSchoolKcbTransaction::create([
            'transaction_reference' => $transactionReference,
            'invoice_number' => $invoiceNumber,
            'amount' => $transactionAmount,
            'phone_number' => $phoneNumber,
            'customer_reference' => $invoiceNumber,
            'customer_name' => $customerName,
            'transaction_type' => 'card_funding',
            'status' => 'pending',
            'result_code' => $resultCode,
            'result_description' => $resultDesc,
            'request_data' => json_encode([
                'admission_number' => $admissionNumber,
                'funding_type' => $fundingType,
                'funding_type_label' => $fundingType === 'T' ? 'Transport' : ($fundingType === 'C' ? 'Canteen' : 'General'),
                'till_number' => $tillNumber,
                'narration' => $narration,
                'raw_payload' => $payload,
            ]),
            'callback_data' => json_encode($payload),
        ]);

        Log::info('HighSchoolKcbTransaction created', [
            'transaction_id' => $transaction->id,
            'admission_number' => $admissionNumber,
            'funding_type' => $fundingType
        ]);

        return $transaction;
    }

    /**
     * Reconcile IPN payment with card account
     */
    private function reconcilePayment($transaction)
    {
        DB::beginTransaction();

        try {
            Log::info('Starting reconciliation', [
                'transaction_id' => $transaction->id,
                'transaction_status' => $transaction->status,
                'transaction_amount' => $transaction->amount
            ]);

            if ($transaction->status === 'completed') {
                Log::info('Transaction already processed', ['transaction_id' => $transaction->id]);
                DB::commit();
                return ['success' => true, 'message' => 'Already processed'];
            }

            $requestData = $transaction->request_data;

            if (is_string($requestData)) {
                $requestData = json_decode($requestData, true);
            }

            if (empty($requestData) || !is_array($requestData)) {
                $requestData = $transaction->callback_data;
                if (is_string($requestData)) {
                    $requestData = json_decode($requestData, true);
                }
            }

            $admissionNumber = $requestData['admission_number'] ?? null;
            $fundingType = $requestData['funding_type'] ?? 'G';
            $typeLabel = $requestData['funding_type_label'] ?? ($fundingType === 'T' ? 'Transport' : ($fundingType === 'C' ? 'Canteen' : 'General'));

            if (!$admissionNumber && $transaction->invoice_number) {
                $invoiceNumber = $transaction->invoice_number;
                Log::info('Extracting from invoice_number', ['invoice_number' => $invoiceNumber]);

                if (strpos($invoiceNumber, '#') !== false) {
                    $parts = explode('#', $invoiceNumber);
                    if (count($parts) == 2) {
                        $suffix = $parts[1];
                        if (strlen($suffix) > 0) {
                            $firstChar = strtoupper($suffix[0]);
                            if ($firstChar === 'T' || $firstChar === 'C') {
                                $admissionNumber = substr($suffix, 1);
                                $fundingType = $firstChar;
                                $typeLabel = $fundingType === 'T' ? 'Transport' : 'Canteen';
                                Log::info('Extracted from invoice with type', [
                                    'admission' => $admissionNumber,
                                    'type' => $typeLabel
                                ]);
                            } else {
                                $admissionNumber = $suffix;
                                Log::info('Extracted from invoice without type', [
                                    'admission' => $admissionNumber
                                ]);
                            }
                        }
                    }
                } else {
                    $admissionNumber = $invoiceNumber;
                    Log::info('Using invoice_number as admission', ['admission' => $admissionNumber]);
                }
            }

            Log::info('Reconciling payment', [
                'admission_number' => $admissionNumber,
                'funding_type' => $typeLabel,
                'amount' => $transaction->amount,
                'transaction_id' => $transaction->id
            ]);

            if (!$admissionNumber) {
                Log::error('No admission number found', [
                    'transaction_id' => $transaction->id,
                    'request_data' => $requestData,
                    'invoice_number' => $transaction->invoice_number
                ]);
                $transaction->status = 'failed';
                $transaction->result_description = 'No admission number found';
                $transaction->save();
                DB::commit();
                return ['success' => false, 'message' => 'No admission number found'];
            }

            $student = HighSchoolStudent::where('admission_number', $admissionNumber)->first();

            if (!$student) {
                Log::error('Student not found', ['admission_number' => $admissionNumber]);
                $transaction->status = 'failed';
                $transaction->result_description = 'Student not found: ' . $admissionNumber;
                $transaction->save();
                DB::commit();
                return ['success' => false, 'message' => 'Student not found: ' . $admissionNumber];
            }

            Log::info('Student found', [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'admission' => $student->admission_number
            ]);

            // ✅ LOCK THE CARD ROW — prevents two transactions reading the
            //    same balance in parallel (row-level lock for InnoDB).
            $card = CardAccount::where('high_school_student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if (!$card) {
                Log::error('Card not found', ['student_id' => $student->id]);
                $transaction->status = 'failed';
                $transaction->result_description = 'Card not found';
                $transaction->save();
                DB::commit();
                return ['success' => false, 'message' => 'Card not found'];
            }

            Log::info('Card found', [
                'card_id' => $card->id,
                'card_number' => $card->card_number,
                'current_balance' => $card->balance
            ]);

            if (!$card->is_active) {
                Log::error('Card is inactive', ['card_id' => $card->id]);
                $transaction->status = 'failed';
                $transaction->result_description = 'Card is inactive';
                $transaction->save();
                DB::commit();
                return ['success' => false, 'message' => 'Card is inactive'];
            }

            $amount = $transaction->amount;
            $mpesaReceipt = $transaction->transaction_reference;

            // ─────────────────────────────────────────────────────────────
            // SAME-FLOW DUPLICATE CHECK
            // Have we already inserted a card_transaction with this exact
            // receipt from a PREVIOUS IPN delivery (KCB retries)?
            // ─────────────────────────────────────────────────────────────
            $existingCardTransaction = CardTransaction::where('mpesa_receipt', $mpesaReceipt)
                ->lockForUpdate()
                ->first();

            if ($existingCardTransaction) {
                Log::warning('Duplicate IPN receipt detected', ['mpesa_receipt' => $mpesaReceipt]);
                $transaction->status = 'completed';
                $transaction->mpesa_receipt_number = $mpesaReceipt;
                $transaction->completed_at = now();
                $transaction->card_account_id = $card->id;
                $transaction->high_school_student_id = $student->id;
                $transaction->save();
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Duplicate IPN already processed',
                    'data' => [
                        'student_name' => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'amount' => $amount,
                        'new_balance' => $card->balance,
                    ]
                ];
            }

            // ═════════════════════════════════════════════════════════════
            // 🔒 CROSS-FLOW DUPLICATE CHECK  ← THE ACTUAL FIX
            // The STK Push flow writes a DIFFERENT receipt number for the
            // SAME payment. Look for a funding on this card, same amount,
            // created in the last 90 seconds, that came from the STK flow.
            // ═════════════════════════════════════════════════════════════
            $stkDuplicate = $this->findStkDuplicate($card, $amount, $mpesaReceipt);

            if ($stkDuplicate) {
                Log::warning('🚫 IPN skipped — STK flow already funded this payment', [
                    'ipn_transaction_id'   => $transaction->id,
                    'ipn_receipt'          => $mpesaReceipt,
                    'existing_card_txn_id' => $stkDuplicate->id,
                    'existing_receipt'     => $stkDuplicate->mpesa_receipt,
                    'existing_reference'   => $stkDuplicate->reference,
                    'amount'               => $amount,
                    'card_id'              => $card->id,
                ]);

                $transaction->status = 'completed';
                $transaction->mpesa_receipt_number = $mpesaReceipt;
                $transaction->result_code = 0;
                $transaction->result_description =
                    'Duplicate — already funded via STK (receipt: ' . $stkDuplicate->mpesa_receipt . ')';
                $transaction->completed_at = now();
                $transaction->card_account_id = $card->id;
                $transaction->high_school_student_id = $student->id;
                $transaction->save();

                // Audit trail — records the skip without touching balance
                CardAuditLog::log(
                    $card->id,
                    'funding_duplicate_skipped',
                    "IPN skipped as duplicate of STK funding (STK receipt: {$stkDuplicate->mpesa_receipt}, IPN receipt: {$mpesaReceipt})",
                    $card->balance,
                    $card->balance, // no change
                    [
                        'amount' => $amount,
                        'ipn_transaction_id' => $transaction->id,
                        'existing_card_transaction_id' => $stkDuplicate->id,
                        'existing_receipt' => $stkDuplicate->mpesa_receipt,
                        'ipn_receipt' => $mpesaReceipt,
                    ]
                );

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Duplicate prevented — STK flow already processed',
                    'data' => [
                        'student_name'     => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'amount'           => $amount,
                        'new_balance'      => $card->balance, // unchanged
                    ]
                ];
            }
            // ═════════════════════════════════════════════════════════════

            // ✅ FUND THE CARD
            $balanceBefore = $card->balance;
            $card->balance += $amount;
            $card->total_funded += $amount;
            $card->last_funded_at = now();
            $card->save();

            Log::info('✅ Card funded', [
                'old_balance' => $balanceBefore,
                'new_balance' => $card->balance,
                'amount_added' => $amount
            ]);

            $reference = CardTransaction::generateReference('IPN');
            $cardTransaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $card->balance,
                'description' => "Paybill 522533 - {$typeLabel} funding - Receipt: {$mpesaReceipt}",
                'reference' => $reference,
                'mpesa_receipt' => $mpesaReceipt,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'phone_number' => $transaction->phone_number,
                    'invoice_number' => $transaction->invoice_number,
                    'admission_number' => $student->admission_number,
                    'ipn_transaction_id' => $transaction->id,
                ])
            ]);

            $transaction->status = 'completed';
            $transaction->mpesa_receipt_number = $mpesaReceipt;
            $transaction->completed_at = now();
            $transaction->card_account_id = $card->id;
            $transaction->high_school_student_id = $student->id;
            $transaction->save();

            CardAuditLog::log(
                $card->id,
                'funding_completed',
                "Paybill 522533 Funding: KES {$amount} ({$typeLabel}) - Receipt: {$mpesaReceipt}",
                $balanceBefore,
                $card->balance,
                [
                    'amount' => $amount,
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'mpesa_receipt' => $mpesaReceipt,
                    'transaction_id' => $transaction->id,
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'paybill' => '522533',
                ]
            );

            DB::commit();

            Log::info('✅ High School Card Funding completed successfully!', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'amount' => $amount,
                'new_balance' => $card->balance,
                'funding_type' => $typeLabel,
            ]);

            if ($student->parent_phone) {
                try {
                    $smsService = app(\App\Services\SmsService::class);
                    $smsService->sendFundingConfirmation(
                        $student,
                        $amount,
                        $fundingType,
                        $mpesaReceipt,
                        $card->balance
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS', ['error' => $e->getMessage()]);
                }
            }

            return [
                'success' => true,
                'message' => 'Payment reconciled successfully',
                'data' => [
                    'student_name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'amount' => $amount,
                    'funding_type' => $typeLabel,
                    'new_balance' => $card->balance,
                    'receipt' => $mpesaReceipt,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ IPN reconciliation error: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($transaction)) {
                $transaction->status = 'failed';
                $transaction->result_description = $e->getMessage();
                $transaction->save();
            }

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find an existing STK-push funding for the same payment.
     *
     * KCB delivers two callbacks per payment:
     *   - STK Push → receipt like "UI94U61GVH", reference like "8118445#C1255"
     *   - IPN       → receipt like "FT26252ZNK2N", reference like "IPN2026..."
     *
     * Both carry the same amount and hit the same card within seconds.
     * Look for the STK one before we credit again.
     */
    private function findStkDuplicate(CardAccount $card, $amount, $currentReceipt)
    {
        return CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'funding')
            ->where('status', 'completed')
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subSeconds(90))
            // STK flow writes references like "8118445#..." or its metadata
            // contains a checkout_request_id
            ->where(function ($q) {
                $q->where('reference', 'LIKE', '8118445%')
                  ->orWhere('metadata', 'LIKE', '%checkout_request_id%');
            })
            // Exclude our own incoming receipt (defensive)
            ->where('mpesa_receipt', '!=', $currentReceipt)
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Check IPN status
     */
    public function checkStatus($transactionId)
    {
        $transaction = HighSchoolKcbTransaction::where('transaction_reference', $transactionId)
            ->orWhere('mpesa_receipt_number', $transactionId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'transaction_reference' => $transaction->transaction_reference,
                'amount' => $transaction->amount,
                'invoice_number' => $transaction->invoice_number,
                'status' => $transaction->status,
                'mpesa_receipt' => $transaction->mpesa_receipt_number,
                'created_at' => $transaction->created_at,
                'completed_at' => $transaction->completed_at,
            ]
        ]);
    }
}
