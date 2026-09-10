<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\KcbTransaction;
use App\Models\Student;
use App\Models\HighSchoolKcbTransaction;
use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Services\CardFundingService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KcbIpnController extends Controller
{
    protected $cardFundingService;

    public function __construct(CardFundingService $cardFundingService)
    {
        $this->cardFundingService = $cardFundingService;
    }

    /**
     * Handle KCB IPN payment notification.
     *
     * This endpoint handles BOTH:
     * 1. College student fee payments
     * 2. High school card funding
     *
     * Route: POST /api/kcb/ipn/payment-notification
     */
    public function handlePaymentNotification(Request $request)
    {
        Log::info('=================================================');
        Log::info('KCB IPN RECEIVED');
        Log::info('=================================================', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            // Extract KCB payload
            $header = $request->input('header', []);
            $requestPayload = $request->input('requestPayload', []);
            $primaryData = $requestPayload['primaryData'] ?? [];
            $notificationData = $requestPayload['additionalData']['notificationData'] ?? [];

            $messageId = $header['messageID'] ?? null;
            $transactionId = $notificationData['transactionID'] ?? null;
            $businessKey = $notificationData['businessKey'] ?? $primaryData['businessKey'] ?? null;
            $debitMSISDN = $notificationData['debitMSISDN'] ?? null;
            $amount = $notificationData['transactionAmt'] ?? null;
            $firstName = $notificationData['firstName'] ?? null;
            $currency = $notificationData['currency'] ?? 'KES';
            $transactionDate = $notificationData['transactionDate'] ?? null;
            $balance = $notificationData['balance'] ?? null;
            $narration = $notificationData['narration'] ?? null;

            // ============================================================
            // CHECK FOR DUPLICATE IPN (Prevent duplicate processing)
            // ============================================================
            if ($transactionId) {
                // Check if this transaction already exists in college table
                $existingCollege = KcbTransaction::where('transaction_reference', $transactionId)->first();
                if ($existingCollege) {
                    Log::info('KCB IPN: Duplicate college transaction - skipping', [
                        'transaction_id' => $transactionId,
                        'existing_id' => $existingCollege->id,
                    ]);
                    return response()->json([
                        'header' => [
                            'messageID' => $messageId,
                            'statusCode' => '0',
                            'statusMessage' => 'Duplicate transaction - already processed'
                        ]
                    ], 200);
                }

                // Check if this transaction already exists in high school table
                $existingHighSchool = HighSchoolKcbTransaction::where('transaction_reference', $transactionId)->first();
                if ($existingHighSchool) {
                    Log::info('KCB IPN: Duplicate high school transaction - skipping', [
                        'transaction_id' => $transactionId,
                        'existing_id' => $existingHighSchool->id,
                    ]);
                    return response()->json([
                        'header' => [
                            'messageID' => $messageId,
                            'statusCode' => '0',
                            'statusMessage' => 'Duplicate transaction - already processed'
                        ]
                    ], 200);
                }
            }

            // ============================================================
            // DETERMINE IF THIS IS HIGH SCHOOL OR COLLEGE
            // ============================================================
            $isHighSchool = false;
            $studentNumber = null;
            $fundingType = null;

            if ($businessKey) {
                // Check for high school format: 8118445#T101 or 8118445#C101 or 8118445#101
                if (strpos($businessKey, '#') !== false) {
                    $parts = explode('#', $businessKey);
                    if (count($parts) == 2) {
                        $suffix = $parts[1];
                        
                        // Check if suffix has T or C prefix (T101, C101)
                        if (preg_match('/^([A-Za-z])(\d+)$/', $suffix, $matches)) {
                            $fundingType = strtoupper($matches[1]); // T or C
                            $studentNumber = $suffix; // T101 or C101 (keep full for search)
                            $isHighSchool = true;
                            Log::info('KCB IPN: HIGH SCHOOL FORMAT DETECTED', [
                                'business_key' => $businessKey,
                                'student_number' => $studentNumber,
                                'funding_type' => $fundingType,
                            ]);
                        } 
                        // Check if suffix is just numbers (101)
                        elseif (is_numeric($suffix)) {
                            $studentNumber = $suffix;
                            $fundingType = 'T'; // Default to Transport if no prefix
                            $isHighSchool = true;
                            Log::info('KCB IPN: HIGH SCHOOL FORMAT DETECTED (numeric only)', [
                                'business_key' => $businessKey,
                                'student_number' => $studentNumber,
                            ]);
                        }
                        // Check if suffix is T101 or C101 with prefix
                        elseif (strlen($suffix) > 1 && in_array(strtoupper($suffix[0]), ['T', 'C'])) {
                            $fundingType = strtoupper($suffix[0]);
                            $studentNumber = $suffix;
                            $isHighSchool = true;
                            Log::info('KCB IPN: HIGH SCHOOL FORMAT DETECTED (prefix)', [
                                'business_key' => $businessKey,
                                'student_number' => $studentNumber,
                                'funding_type' => $fundingType,
                            ]);
                        }
                    }
                } else {
                    // No # - could be college student number or just the till number
                    if (strlen($businessKey) > 7) { // 8118445 is 7 digits
                        $studentNumber = $businessKey;
                        Log::info('KCB IPN: POSSIBLE COLLEGE FORMAT', [
                            'business_key' => $businessKey,
                            'student_number' => $studentNumber,
                        ]);
                    } else {
                        Log::warning('KCB IPN: Invalid business key format', [
                            'business_key' => $businessKey,
                        ]);
                        return response()->json([
                            'header' => [
                                'messageID' => $messageId,
                                'statusCode' => '1',
                                'statusMessage' => 'Invalid business key format'
                            ]
                        ], 400);
                    }
                }
            }

            // ============================================================
            // HIGH SCHOOL: Process card funding
            // ============================================================
            if ($isHighSchool && $studentNumber) {
                Log::info('KCB IPN: PROCESSING HIGH SCHOOL CARD FUNDING', [
                    'student_number' => $studentNumber,
                    'amount' => $amount,
                    'phone' => $debitMSISDN,
                    'transaction_id' => $transactionId,
                ]);

                return $this->handleHighSchoolFunding(
                    $studentNumber,
                    $fundingType,
                    $notificationData,
                    $request->all()
                );
            }

            // ============================================================
            // COLLEGE: Process fee payment
            // ============================================================
            if ($studentNumber) {
                Log::info('KCB IPN: PROCESSING COLLEGE PAYMENT', [
                    'student_number' => $studentNumber,
                    'amount' => $amount,
                    'phone' => $debitMSISDN,
                    'transaction_id' => $transactionId,
                ]);

                return $this->handleCollegePayment(
                    $studentNumber,
                    $notificationData,
                    $request->all()
                );
            }

            // ============================================================
            // FALLBACK: Try to find by phone number for high school
            // ============================================================
            if ($debitMSISDN) {
                Log::info('KCB IPN: FALLBACK - Searching high school student by phone', [
                    'phone' => $debitMSISDN,
                ]);

                $student = HighSchoolStudent::where('parent_phone', $debitMSISDN)->first();
                
                if ($student) {
                    $studentNumber = $student->admission_number;
                    Log::info('KCB IPN: FALLBACK - Found student by phone', [
                        'student_id' => $student->id,
                        'admission_number' => $studentNumber,
                    ]);
                    
                    return $this->handleHighSchoolFunding(
                        $studentNumber,
                        'T',
                        $notificationData,
                        $request->all()
                    );
                }
            }

            // ============================================================
            // NO MATCH FOUND
            // ============================================================
            Log::warning('KCB IPN: No matching student found', [
                'business_key' => $businessKey,
                'student_number' => $studentNumber,
                'phone' => $debitMSISDN,
                'is_high_school' => $isHighSchool,
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $messageId,
                    'statusCode' => '1',
                    'statusMessage' => 'Student not found'
                ]
            ], 404);

        } catch (\Exception $e) {
            Log::error('KCB IPN ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
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
     * Handle high school card funding
     */
    private function handleHighSchoolFunding(
        string $studentNumber,
        ?string $fundingType,
        array $notificationData,
        array $payload
    ) {
        DB::beginTransaction();

        try {
            $transactionId = $notificationData['transactionID'] ?? null;
            $amount = $notificationData['transactionAmt'] ?? null;
            $debitMSISDN = $notificationData['debitMSISDN'] ?? null;
            $firstName = $notificationData['firstName'] ?? null;
            $businessKey = $notificationData['businessKey'] ?? null;
            $transactionDate = $notificationData['transactionDate'] ?? null;

            // ------------------------------------------------------------
            // 1. Check for duplicate high school transaction
            // ------------------------------------------------------------
            if ($transactionId) {
                $existing = HighSchoolKcbTransaction::where('transaction_reference', $transactionId)->first();
                if ($existing) {
                    Log::info('KCB IPN: Duplicate high school transaction - already processed', [
                        'transaction_id' => $transactionId,
                        'existing_id' => $existing->id,
                    ]);
                    DB::commit();
                    return response()->json([
                        'header' => [
                            'messageID' => $notificationData['transactionID'] ?? null,
                            'statusCode' => '0',
                            'statusMessage' => 'Duplicate transaction - already processed'
                        ]
                    ], 200);
                }
            }

            // ------------------------------------------------------------
            // 2. Find the student
            // ------------------------------------------------------------
            $student = null;
            
            // Try exact match on admission_number
            $student = HighSchoolStudent::where('admission_number', $studentNumber)
                ->orWhere('admission_number', (string)$studentNumber)
                ->orWhere('admission_number', (int)$studentNumber)
                ->first();

            // If not found, try stripping prefix (T101 -> 101)
            if (!$student && preg_match('/^[A-Za-z](\d+)$/', $studentNumber, $matches)) {
                $numericOnly = $matches[1];
                $student = HighSchoolStudent::where('admission_number', $numericOnly)
                    ->orWhere('admission_number', (int)$numericOnly)
                    ->first();
                Log::info('KCB IPN: Stripped prefix for student search', [
                    'original' => $studentNumber,
                    'stripped' => $numericOnly,
                    'found' => $student ? 'YES' : 'NO',
                ]);
            }

            // If still not found, try by phone
            if (!$student && $debitMSISDN) {
                $student = HighSchoolStudent::where('parent_phone', $debitMSISDN)->first();
                Log::info('KCB IPN: Searching by phone', [
                    'phone' => $debitMSISDN,
                    'found' => $student ? 'YES' : 'NO',
                ]);
            }

            if (!$student) {
                Log::error('KCB IPN: High school student not found', [
                    'student_number' => $studentNumber,
                    'phone' => $debitMSISDN,
                ]);

                // Create failed transaction record
                HighSchoolKcbTransaction::create([
                    'transaction_reference' => $transactionId,
                    'invoice_number' => $businessKey,
                    'amount' => $amount,
                    'phone_number' => $debitMSISDN,
                    'customer_name' => $firstName,
                    'customer_reference' => $businessKey,
                    'transaction_type' => 'card_funding',
                    'status' => 'failed',
                    'result_code' => '1',
                    'result_description' => 'Student not found: ' . $studentNumber,
                    'request_data' => json_encode([
                        'admission_number' => $studentNumber,
                        'funding_type' => $fundingType,
                        'search_attempts' => 'admission_number, stripped, phone',
                    ]),
                    'callback_data' => json_encode($payload),
                ]);

                DB::commit();

                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '1',
                        'statusMessage' => 'Student not found'
                    ]
                ], 404);
            }

            Log::info('KCB IPN: High school student found', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'name' => $student->full_name,
                'phone' => $student->parent_phone,
            ]);

            // ------------------------------------------------------------
            // 3. Find the card
            // ------------------------------------------------------------
            $card = CardAccount::where('high_school_student_id', $student->id)->first();

            if (!$card) {
                Log::error('KCB IPN: Card not found for student', [
                    'student_id' => $student->id,
                ]);
                DB::commit();
                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '1',
                        'statusMessage' => 'Card not found'
                    ]
                ], 404);
            }

            if (!$card->is_active) {
                Log::error('KCB IPN: Card is inactive', [
                    'card_id' => $card->id,
                ]);
                DB::commit();
                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '1',
                        'statusMessage' => 'Card is inactive'
                    ]
                ], 400);
            }

            // ------------------------------------------------------------
            // 4. Check for duplicate card transaction
            // ------------------------------------------------------------
            $existingCardTx = CardTransaction::where('mpesa_receipt', $transactionId)->first();
            if ($existingCardTx) {
                Log::warning('KCB IPN: Duplicate card transaction', [
                    'mpesa_receipt' => $transactionId,
                ]);
                DB::commit();
                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '0',
                        'statusMessage' => 'Duplicate transaction - already processed'
                    ]
                ], 200);
            }

            // ------------------------------------------------------------
            // 5. Fund the card
            // ------------------------------------------------------------
            $balanceBefore = $card->balance;
            $card->balance += $amount;
            $card->total_funded += $amount;
            $card->last_funded_at = now();
            $card->save();

            Log::info('KCB IPN: Card funded', [
                'card_id' => $card->id,
                'old_balance' => $balanceBefore,
                'new_balance' => $card->balance,
                'amount' => $amount,
            ]);

            // ------------------------------------------------------------
            // 6. Create CardTransaction
            // ------------------------------------------------------------
            $typeLabel = $fundingType === 'C' ? 'Canteen' : 'Transport';
            $reference = CardTransaction::generateReference('IPN');

            $cardTransaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $card->balance,
                'description' => "Paybill 522533 - {$typeLabel} funding - Receipt: {$transactionId}",
                'reference' => $reference,
                'mpesa_receipt' => $transactionId,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'phone_number' => $debitMSISDN,
                    'invoice_number' => $businessKey,
                    'admission_number' => $student->admission_number,
                    'ipn_transaction_id' => $transactionId,
                ]),
            ]);

            // ------------------------------------------------------------
            // 7. Create HighSchoolKcbTransaction record
            // ------------------------------------------------------------
            $hsTransaction = HighSchoolKcbTransaction::create([
                'transaction_reference' => $transactionId,
                'invoice_number' => $businessKey,
                'amount' => $amount,
                'phone_number' => $debitMSISDN,
                'customer_name' => $firstName,
                'customer_reference' => $businessKey,
                'transaction_type' => 'card_funding',
                'status' => 'completed',
                'result_code' => '0',
                'result_description' => 'Payment successful',
                'mpesa_receipt_number' => $transactionId,
                'completed_at' => now(),
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'request_data' => json_encode([
                    'admission_number' => $student->admission_number,
                    'funding_type' => $fundingType,
                    'funding_type_label' => $typeLabel,
                    'student_name' => $student->full_name,
                ]),
                'callback_data' => json_encode($payload),
            ]);

            // ------------------------------------------------------------
            // 8. Audit log
            // ------------------------------------------------------------
            CardAuditLog::log(
                $card->id,
                'funding_completed',
                "Paybill 522533 Funding: KES {$amount} ({$typeLabel}) - Receipt: {$transactionId}",
                $balanceBefore,
                $card->balance,
                [
                    'amount' => $amount,
                    'funding_type' => $fundingType,
                    'type_label' => $typeLabel,
                    'mpesa_receipt' => $transactionId,
                    'transaction_id' => $hsTransaction->id,
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'paybill' => '522533',
                ]
            );

            DB::commit();

            Log::info('KCB IPN: HIGH SCHOOL CARD FUNDING COMPLETED', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'amount' => $amount,
                'new_balance' => $card->balance,
                'funding_type' => $typeLabel,
                'receipt' => $transactionId,
            ]);

            // ------------------------------------------------------------
            // 9. Send SMS notification
            // ------------------------------------------------------------
            if ($student->parent_phone) {
                try {
                    $smsService = app(SmsService::class);
                    $smsService->sendFundingConfirmation(
                        $student,
                        $amount,
                        $fundingType,
                        $transactionId,
                        $card->balance
                    );
                } catch (\Exception $e) {
                    Log::error('KCB IPN: Failed to send SMS', [
                        'error' => $e->getMessage(),
                        'student_id' => $student->id,
                    ]);
                }
            }

            return response()->json([
                'header' => [
                    'messageID' => $notificationData['transactionID'] ?? null,
                    'statusCode' => '0',
                    'statusMessage' => 'Payment processed successfully'
                ],
                'responsePayload' => [
                    'transactionInfo' => [
                        'transactionId' => $transactionId,
                        'student_name' => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'new_balance' => $card->balance,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KCB IPN: High school funding error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $notificationData['transactionID'] ?? null,
                    'statusCode' => '1',
                    'statusMessage' => 'Internal server error'
                ]
            ], 500);
        }
    }

    /**
     * Handle college fee payment
     */
    private function handleCollegePayment(
        string $studentNumber,
        array $notificationData,
        array $payload
    ) {
        DB::beginTransaction();

        try {
            $transactionId = $notificationData['transactionID'] ?? null;
            $amount = $notificationData['transactionAmt'] ?? null;
            $debitMSISDN = $notificationData['debitMSISDN'] ?? null;
            $firstName = $notificationData['firstName'] ?? null;
            $businessKey = $notificationData['businessKey'] ?? null;
            $transactionDate = $notificationData['transactionDate'] ?? null;

            // ------------------------------------------------------------
            // 1. Check for duplicate college transaction
            // ------------------------------------------------------------
            if ($transactionId) {
                $existing = KcbTransaction::where('transaction_reference', $transactionId)->first();
                if ($existing) {
                    Log::info('KCB IPN: Duplicate college transaction - already processed', [
                        'transaction_id' => $transactionId,
                        'existing_id' => $existing->id,
                    ]);
                    DB::commit();
                    return response()->json([
                        'header' => [
                            'messageID' => $notificationData['transactionID'] ?? null,
                            'statusCode' => '0',
                            'statusMessage' => 'Duplicate transaction - already processed'
                        ]
                    ], 200);
                }
            }

            // ------------------------------------------------------------
            // 2. Find college student
            // ------------------------------------------------------------
            $student = Student::where('student_number', $studentNumber)->first();

            if (!$student) {
                Log::error('KCB IPN: College student not found', [
                    'student_number' => $studentNumber,
                ]);

                // Create failed transaction
                KcbTransaction::create([
                    'transaction_reference' => $transactionId,
                    'student_number' => $studentNumber,
                    'amount' => $amount,
                    'phone_number' => $debitMSISDN,
                    'customer_name' => $firstName,
                    'customer_reference' => $businessKey,
                    'status' => 'failed',
                    'result_description' => 'Student not found',
                    'raw_payload' => json_encode($payload),
                ]);

                DB::commit();

                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '1',
                        'statusMessage' => 'Student not found'
                    ]
                ], 404);
            }

            // ------------------------------------------------------------
            // 3. Process college payment
            // ------------------------------------------------------------
            // Find enrollment
            $enrollment = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$enrollment) {
                Log::error('KCB IPN: No active enrollment found', [
                    'student_id' => $student->id,
                ]);
                DB::commit();
                return response()->json([
                    'header' => [
                        'messageID' => $notificationData['transactionID'] ?? null,
                        'statusCode' => '1',
                        'statusMessage' => 'No active enrollment'
                    ]
                ], 404);
            }

            // Create fee payment
            $feePayment = FeePayment::create([
                'enrollment_id' => $enrollment->id,
                'amount' => $amount,
                'payment_date' => now(),
                'payment_method' => 'mpesa',
                'reference_number' => $transactionId,
                'status' => 'completed',
                'notes' => "KCB IPN payment - {$firstName}",
            ]);

            // Create KcbTransaction record
            KcbTransaction::create([
                'transaction_reference' => $transactionId,
                'student_number' => $studentNumber,
                'amount' => $amount,
                'phone_number' => $debitMSISDN,
                'customer_name' => $firstName,
                'customer_reference' => $businessKey,
                'status' => 'completed',
                'result_description' => 'Payment successful',
                'fee_payment_id' => $feePayment->id,
                'raw_payload' => json_encode($payload),
            ]);

            DB::commit();

            Log::info('KCB IPN: COLLEGE PAYMENT COMPLETED', [
                'student_id' => $student->id,
                'student_number' => $student->student_number,
                'amount' => $amount,
                'fee_payment_id' => $feePayment->id,
                'receipt' => $transactionId,
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $notificationData['transactionID'] ?? null,
                    'statusCode' => '0',
                    'statusMessage' => 'Payment processed successfully'
                ],
                'responsePayload' => [
                    'transactionInfo' => [
                        'transactionId' => $transactionId,
                        'student_name' => $student->full_name,
                        'student_number' => $student->student_number,
                        'amount' => $amount,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KCB IPN: College payment error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $notificationData['transactionID'] ?? null,
                    'statusCode' => '1',
                    'statusMessage' => 'Internal server error'
                ]
            ], 500);
        }
    }
}