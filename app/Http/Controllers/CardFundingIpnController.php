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

            // Create transaction record
            $transaction = $this->createTransactionRecord($payload);

            // Reconcile the payment
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

        // Handle KCB wrapper format
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

        // Get all possible fields
        $transactionReference = $payload['transaction_reference'] ?? $payload['TransID'] ?? $payload['transaction_id'] ?? null;
        $invoiceNumber = $payload['customer_reference'] ?? $payload['BillRefNumber'] ?? $payload['businessKey'] ?? $payload['invoice_number'] ?? null;
        $transactionAmount = $payload['transaction_amount'] ?? $payload['TransAmount'] ?? $payload['amount'] ?? null;
        $phoneNumber = $payload['customer_mobile_number'] ?? $payload['MSISDN'] ?? $payload['debitMSISDN'] ?? null;
        $customerName = $payload['customer_name'] ?? $payload['FirstName'] ?? $payload['firstName'] ?? null;
        $narration = $payload['narration'] ?? $payload['Narration'] ?? 'Card funding via M-Pesa';
        $resultCode = $payload['result_code'] ?? $payload['ResultCode'] ?? 0;
        $resultDesc = $payload['result_description'] ?? $payload['ResultDesc'] ?? 'Payment successful';

        Log::info('Parsing invoice number', ['invoice_number' => $invoiceNumber]);

        // Parse invoice number: 8118445#T101 or 8118445#C101
        $admissionNumber = null;
        $fundingType = 'G';
        $tillNumber = null;

        if ($invoiceNumber) {
            // Pattern 1: 8118445#T101
            if (preg_match('/^(\d+)#([A-Za-z])(\d+)$/', $invoiceNumber, $matches)) {
                $tillNumber = $matches[1];
                $fundingType = strtoupper($matches[2]);
                $admissionNumber = $matches[3];
                Log::info('Parsed invoice with type', [
                    'till' => $tillNumber,
                    'type' => $fundingType,
                    'admission' => $admissionNumber
                ]);
            } 
            // Pattern 2: 8118445#101
            elseif (preg_match('/^(\d+)#(\d+)$/', $invoiceNumber, $matches)) {
                $tillNumber = $matches[1];
                $admissionNumber = $matches[2];
                Log::info('Parsed invoice without type', [
                    'till' => $tillNumber,
                    'admission' => $admissionNumber
                ]);
            }
            // Pattern 3: T101 or C101
            elseif (preg_match('/^([A-Za-z])(\d+)$/', $invoiceNumber, $matches)) {
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

        // If still null, try customer_reference
        if (!$admissionNumber) {
            $admissionNumber = $payload['customer_reference'] ?? $payload['BillRefNumber'] ?? null;
            Log::info('Using customer_reference as admission', ['admission' => $admissionNumber]);
        }

        Log::info('Final parsed data', [
            'admission_number' => $admissionNumber,
            'funding_type' => $fundingType,
            'till_number' => $tillNumber
        ]);

        // Check if transaction already exists
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

            // Check if already processed
            if ($transaction->status === 'completed') {
                Log::info('Transaction already processed', ['transaction_id' => $transaction->id]);
                DB::commit();
                return ['success' => true, 'message' => 'Already processed'];
            }

            // ✅ Get request data properly
            $requestData = $transaction->request_data;
            
            // Decode if it's a string
            if (is_string($requestData)) {
                $requestData = json_decode($requestData, true);
            }
            
            // If still empty, try callback_data
            if (empty($requestData) || !is_array($requestData)) {
                $requestData = $transaction->callback_data;
                if (is_string($requestData)) {
                    $requestData = json_decode($requestData, true);
                }
            }

            // ✅ Extract admission number
            $admissionNumber = $requestData['admission_number'] ?? null;
            $fundingType = $requestData['funding_type'] ?? 'G';
            $typeLabel = $requestData['funding_type_label'] ?? ($fundingType === 'T' ? 'Transport' : ($fundingType === 'C' ? 'Canteen' : 'General'));

            // ✅ If still null, try to extract from invoice_number
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

            // Find student by admission number
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

            // Find card
            $card = CardAccount::where('high_school_student_id', $student->id)->first();

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

            // Check if card is active
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

            // Check for duplicate card transaction
            $existingCardTransaction = CardTransaction::where('mpesa_receipt', $mpesaReceipt)->first();
            if ($existingCardTransaction) {
                Log::warning('Duplicate detected', ['mpesa_receipt' => $mpesaReceipt]);
                $transaction->status = 'completed';
                $transaction->mpesa_receipt_number = $mpesaReceipt;
                $transaction->completed_at = now();
                $transaction->card_account_id = $card->id;
                $transaction->high_school_student_id = $student->id;
                $transaction->save();
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Duplicate transaction already processed',
                    'data' => [
                        'student_name' => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'amount' => $amount,
                        'new_balance' => $card->balance,
                    ]
                ];
            }

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

            // Create card transaction
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

            // Update HighSchoolKcbTransaction
            $transaction->status = 'completed';
            $transaction->mpesa_receipt_number = $mpesaReceipt;
            $transaction->completed_at = now();
            $transaction->card_account_id = $card->id;
            $transaction->high_school_student_id = $student->id;
            $transaction->save();

            // Log audit
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

            // Send SMS notification
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