<?php

namespace App\Services;

use App\Models\EventApplication;
use App\Models\EventApplicationAttendee;
use App\Models\KcbBuniTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventPaymentService
{
    protected $kcbService;

    public function __construct(KcbService $kcbService)
    {
        $this->kcbService = $kcbService;
    }

    /**
     * Process event application and initiate payment
     */
    public function processEventApplicationAndPayment($data, $event)
    {
        try {
            DB::beginTransaction();

            // Calculate total amount
            $numberOfPeople = $data['number_of_people'];
            $totalAmount = $this->calculateTotalAmount($event, $numberOfPeople);

            // Create event application
            $application = EventApplication::create([
                'event_id' => $event->id,
                'parent_name' => $data['parent_name'],
                'parent_contact' => $data['parent_contact'],
                'parent_email' => $data['parent_email'],
                'number_of_people' => $numberOfPeople,
                'total_amount' => $totalAmount,
                'application_status' => 'pending_payment',
                'notes' => 'Payment pending via KCB M-Pesa'
            ]);

            // Create attendees
            foreach ($data['attendees'] as $attendeeData) {
                EventApplicationAttendee::create([
                    'event_application_id' => $application->id,
                    'name' => $attendeeData['name'],
                    'school' => $attendeeData['school'],
                    'age' => $attendeeData['age'],
                ]);
            }

            Log::info('Event application created', [
                'application_id' => $application->id,
                'event_id' => $event->id,
                'total_amount' => $totalAmount
            ]);

            // Process payment if it's a paid event
            if ($event->is_paid && $totalAmount > 0) {
                $paymentResult = $this->initiateEventPayment($application, $data['parent_contact']);

                if (isset($paymentResult['error'])) {
                    DB::rollBack();
                    return $paymentResult;
                }

                DB::commit();
                return [
                    'success' => true,
                    'application' => $application,
                    'payment' => $paymentResult
                ];
            }

            // For free events
            $application->update(['application_status' => 'confirmed']);
            DB::commit();

            return [
                'success' => true,
                'application' => $application,
                'message' => 'Application submitted successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Event Payment Service Error: ' . $e->getMessage());
            return ['error' => 'Failed to process event application. Please try again.'];
        }
    }

    /**
     * Calculate total amount for event registration
     */
    private function calculateTotalAmount($event, $numberOfPeople)
    {
        if (!$event->is_paid) {
            return 0;
        }

        $pricePerPerson = $event->price;

        // Check for early bird pricing
        if ($event->early_bird_price && $event->early_bird_end_date > now()) {
            $pricePerPerson = $event->early_bird_price;
        }

        return $pricePerPerson * $numberOfPeople;
    }

    /**
     * Initiate M-Pesa payment for event application
     */
    private function initiateEventPayment(EventApplication $application, $phoneNumber)
    {
        try {
            Log::info('Initiating event payment', ['application_id' => $application->id]);

            // Generate invoice number
            $invoiceNumber = $this->kcbService->generateEventInvoiceNumber(
                $application->event_id,
                $application->id
            );

            // Validate and format phone number
            $formattedPhone = $this->kcbService->validatePhoneNumber($phoneNumber);
            if (!$formattedPhone) {
                return ['error' => 'Invalid phone number format. Please use format: 0712 345 678'];
            }

            // Initiate STK Push
            $stkResponse = $this->kcbService->initiateEventPayment([
                'phone_number' => $formattedPhone,
                'amount' => $application->total_amount,
                'invoice_number' => $invoiceNumber,
                'description' => 'Kenswed Event - ' . $application->event->title,
            ]);

            if (isset($stkResponse['error'])) {
                $application->update([
                    'application_status' => 'payment_failed',
                    'notes' => $stkResponse['error']
                ]);
                return ['error' => $stkResponse['error']];
            }

            // Create KCB transaction record with application_id
            KcbBuniTransaction::create([
                'application_id' => $application->id,
                'merchant_request_id' => $stkResponse['merchant_request_id'],
                'checkout_request_id' => $stkResponse['checkout_request_id'],
                'phone_number' => $formattedPhone,
                'amount' => $application->total_amount,
                'invoice_number' => $invoiceNumber,
                'transaction_type' => 'event_registration',
                'status' => 'initiated',
                'request_data' => json_encode([
                    'application_id' => $application->id,
                    'event_id' => $application->event_id,
                    'event_title' => $application->event->title,
                    'parent_name' => $application->parent_name,
                    'parent_email' => $application->parent_email,
                    'number_of_people' => $application->number_of_people
                ]),
            ]);

            Log::info('Event payment initiated successfully', [
                'application_id' => $application->id,
                'merchant_request_id' => $stkResponse['merchant_request_id'],
                'checkout_request_id' => $stkResponse['checkout_request_id']
            ]);

            return [
                'success' => true,
                'merchant_request_id' => $stkResponse['merchant_request_id'],
                'checkout_request_id' => $stkResponse['checkout_request_id'],
                'message' => 'Payment request sent. Please check your phone to complete payment.',
            ];

        } catch (\Exception $e) {
            Log::error('Event payment initiation failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            $application->update([
                'application_status' => 'payment_failed',
                'notes' => 'Payment service temporarily unavailable'
            ]);
            return ['error' => 'Payment service temporarily unavailable. Please try again.'];
        }
    }

    /**
     * Handle payment callback for events
     */
    public function handleEventPaymentCallback($callbackData)
    {
        Log::info('Event Payment Callback Data:', $callbackData);

        $stkCallback = $callbackData['Body']['stkCallback'] ?? null;

        if (!$stkCallback) {
            Log::error('Invalid KCB Buni callback format for event', $callbackData);
            return false;
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        if (!$checkoutRequestId || $resultCode === null) {
            Log::warning('Missing required callback fields', $stkCallback);
            return false;
        }

        // Find transaction by checkout_request_id
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$transaction) {
            Log::error('Transaction not found for callback', ['checkout_request_id' => $checkoutRequestId]);
            return false;
        }

        // Update transaction status
        $updateData = [
            'result_code' => $resultCode,
            'result_description' => $stkCallback['ResultDesc'] ?? '',
            'callback_data' => json_encode($callbackData),
        ];

        if ($resultCode == 0) {
            // Payment successful
            $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
            $metadata = [];

            foreach ($callbackMetadata as $item) {
                if (isset($item['Name']) && isset($item['Value'])) {
                    $metadata[$item['Name']] = $item['Value'];
                }
            }

            $updateData['status'] = 'completed';
            $updateData['mpesa_receipt_number'] = $metadata['MpesaReceiptNumber'] ?? null;
            $updateData['transaction_date'] = isset($metadata['TransactionDate']) ?
                $this->formatTransactionDate($metadata['TransactionDate']) : now();

            // Update application status using the application_id from transaction
            $application = $transaction->application;
            if ($application) {
                $application->update([
                    'application_status' => 'confirmed',
                    'mpesa_reference_number' => $metadata['MpesaReceiptNumber'] ?? null,
                    'notes' => 'Payment completed successfully via KCB M-Pesa - Receipt: ' . ($metadata['MpesaReceiptNumber'] ?? 'N/A')
                ]);

                Log::info('Event payment completed successfully', [
                    'application_id' => $application->id,
                    'receipt' => $updateData['mpesa_receipt_number']
                ]);
            }

        } else {
            // Payment failed
            $updateData['status'] = 'failed';

            // Update application status
            $application = $transaction->application;
            if ($application) {
                $application->update([
                    'application_status' => 'payment_failed',
                    'notes' => 'Payment failed: ' . ($stkCallback['ResultDesc'] ?? 'Unknown error')
                ]);
            }

            Log::warning('Event payment failed', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_description' => $stkCallback['ResultDesc'] ?? 'Unknown error'
            ]);
        }

        $transaction->update($updateData);
        return true;
    }

    /**
     * Check payment status for event application
     */
    public function checkEventPaymentStatus($checkoutRequestId)
    {
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->with('application')
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        return [
            'success' => true,
            'status' => $transaction->status,
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'application_status' => $transaction->application ? $transaction->application->application_status : 'unknown',
            'application_id' => $transaction->application ? $transaction->application->id : 'unknown',
        ];
    }

    /**
     * Format transaction date from M-Pesa format
     */
    private function formatTransactionDate($transactionDate)
    {
        if (strlen($transactionDate) === 14) {
            try {
                return Carbon::createFromFormat('YmdHis', $transactionDate);
            } catch (\Exception $e) {
                Log::warning('Failed to parse transaction date: ' . $transactionDate);
                return now();
            }
        }
        return now();
    }

    /**
     * Get application payment status
     */
    public function getApplicationPaymentStatus($applicationId)
    {
        try {
            $application = EventApplication::with(['kcbTransactions' => function($query) {
                $query->latest();
            }])->find($applicationId);

            if (!$application) {
                return [
                    'success' => false,
                    'message' => 'Application not found'
                ];
            }

            $latestTransaction = $application->kcbTransactions->first();

            return [
                'success' => true,
                'application_status' => $application->application_status,
                'total_amount' => $application->total_amount,
                'mpesa_reference_number' => $application->mpesa_reference_number,
                'latest_transaction' => $latestTransaction ? [
                    'status' => $latestTransaction->status,
                    'result_code' => $latestTransaction->result_code,
                    'result_description' => $latestTransaction->result_description,
                    'mpesa_receipt_number' => $latestTransaction->mpesa_receipt_number,
                    'created_at' => $latestTransaction->created_at
                ] : null
            ];

        } catch (\Exception $e) {
            Log::error('Get application payment status failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve payment status'
            ];
        }
    }

    /**
     * Retry failed payment for an application
     */
    public function retryPayment($applicationId, $phoneNumber = null)
    {
        try {
            $application = EventApplication::find($applicationId);

            if (!$application) {
                return ['error' => 'Application not found'];
            }

            if ($application->application_status !== 'payment_failed') {
                return ['error' => 'Cannot retry payment for this application status'];
            }

            // Use provided phone number or the original one
            $phoneToUse = $phoneNumber ?: $application->parent_contact;

            $paymentResult = $this->initiateEventPayment($application, $phoneToUse);

            if (isset($paymentResult['error'])) {
                return $paymentResult;
            }

            return [
                'success' => true,
                'message' => 'Payment retry initiated successfully',
                'checkout_request_id' => $paymentResult['checkout_request_id']
            ];

        } catch (\Exception $e) {
            Log::error('Payment retry failed: ' . $e->getMessage());
            return ['error' => 'Failed to retry payment. Please try again.'];
        }
    }

    /**
     * Cancel pending payment and application
     */
    public function cancelApplication($applicationId)
    {
        try {
            DB::beginTransaction();

            $application = EventApplication::find($applicationId);

            if (!$application) {
                return ['error' => 'Application not found'];
            }

            // Only allow cancellation for pending payments
            if (!in_array($application->application_status, ['pending_payment', 'payment_failed'])) {
                return ['error' => 'Cannot cancel application with current status: ' . $application->application_status];
            }

            // Update application status
            $application->update([
                'application_status' => 'cancelled',
                'notes' => 'Application cancelled by user'
            ]);

            // Update any initiated transactions
            KcbBuniTransaction::where('application_id', $applicationId)
                ->where('status', 'initiated')
                ->update([
                    'status' => 'cancelled',
                    'result_description' => 'Payment cancelled due to application cancellation'
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Application cancelled successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application cancellation failed: ' . $e->getMessage());
            return ['error' => 'Failed to cancel application. Please try again.'];
        }
    }

    /**
     * Get payment summary for admin
     */
    public function getPaymentSummary($eventId = null)
    {
        try {
            $query = KcbBuniTransaction::with('application.event');

            if ($eventId) {
                $query->whereHas('application', function($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                });
            }

            $transactions = $query->where('status', 'completed')->get();

            $summary = [
                'total_transactions' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'successful_payments' => $transactions->where('status', 'completed')->count(),
                'failed_payments' => KcbBuniTransaction::where('status', 'failed')->count(),
                'pending_payments' => KcbBuniTransaction::where('status', 'initiated')->count(),
            ];

            return [
                'success' => true,
                'summary' => $summary,
                'recent_transactions' => $transactions->take(10)
            ];

        } catch (\Exception $e) {
            Log::error('Get payment summary failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve payment summary'
            ];
        }
    }

    /**
     * Verify payment and update application status manually
     */
    public function verifyAndUpdatePayment($applicationId, $mpesaReferenceNumber)
    {
        try {
            DB::beginTransaction();

            $application = EventApplication::find($applicationId);

            if (!$application) {
                return ['error' => 'Application not found'];
            }

            // Update application with manual payment verification
            $application->update([
                'application_status' => 'confirmed',
                'mpesa_reference_number' => $mpesaReferenceNumber,
                'notes' => 'Payment verified manually - Reference: ' . $mpesaReferenceNumber
            ]);

            // Update any related transactions
            KcbBuniTransaction::where('application_id', $applicationId)
                ->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReferenceNumber,
                    'result_description' => 'Payment verified manually'
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment verified and application confirmed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual payment verification failed: ' . $e->getMessage());
            return ['error' => 'Failed to verify payment. Please try again.'];
        }
    }

    /**
     * Get all transactions for an application
     */
    public function getApplicationTransactions($applicationId)
    {
        try {
            $transactions = KcbBuniTransaction::where('application_id', $applicationId)
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'success' => true,
                'transactions' => $transactions,
                'count' => $transactions->count()
            ];

        } catch (\Exception $e) {
            Log::error('Get application transactions failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve transactions'
            ];
        }
    }

    /**
     * Check if phone number has pending payments
     */
    public function hasPendingPayments($phoneNumber)
    {
        try {
            $formattedPhone = $this->kcbService->validatePhoneNumber($phoneNumber);

            $pendingCount = KcbBuniTransaction::where('phone_number', $formattedPhone)
                ->where('status', 'initiated')
                ->count();

            return [
                'success' => true,
                'has_pending' => $pendingCount > 0,
                'pending_count' => $pendingCount
            ];

        } catch (\Exception $e) {
            Log::error('Check pending payments failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check pending payments'
            ];
        }
    }
}
