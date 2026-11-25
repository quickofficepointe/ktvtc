<?php
// app/Services/ApplicationKcbService.php

namespace App\Services;

use App\Models\Application;
use App\Models\KcbBuniTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApplicationKcbService
{
    protected $kcbService;
    protected $smsService;

    public function __construct(KcbService $kcbService, SmsService $smsService)
    {
        $this->kcbService = $kcbService;
        $this->smsService = $smsService;
    }

    /**
     * Initiate payment for application
     */
    public function initiateApplicationPayment(Application $application, $phoneNumber)
    {
        try {
            Log::info('Initiating application payment', ['application_id' => $application->id]);

            // A prompt sent moments ago may still be on the phone: don't send a second one
            $recent = KcbBuniTransaction::where('application_id', $application->id)
                ->where('transaction_type', 'application_fee')
                ->where('status', 'initiated')
                ->where('created_at', '>=', now()->subSeconds(90))
                ->latest('id')->first();
            if ($recent) {
                return [
                    'success' => true,
                    'transaction' => $recent,
                    'merchant_request_id' => $recent->merchant_request_id,
                    'checkout_request_id' => $recent->checkout_request_id,
                    'message' => 'A payment request was just sent to ' . $recent->phone_number . '. Please check your phone.',
                ];
            }

            // Generate invoice number for application
            $invoiceNumber = $this->generateApplicationInvoiceNumber($application);

            // Validate and format phone number
            $formattedPhone = $this->kcbService->validatePhoneNumber($phoneNumber);
            if (!$formattedPhone) {
                return ['error' => 'Invalid phone number format. Please use format: 0712 345 678'];
            }

            // Prepare callback URL
            $callbackUrl = route('application.payment.callback');

            // Initiate STK Push - Fixed amount KES 500
            $stkResponse = $this->kcbService->initiateStkPush(
                $formattedPhone,
                500,
                $invoiceNumber,
                $callbackUrl,
                'Application Fee - ' . $application->application_number
            );

            if (isset($stkResponse['error'])) {
                Log::error('Application payment initiation failed', [
                    'application_id' => $application->id,
                    'error' => $stkResponse['error']
                ]);
                return ['error' => $stkResponse['error']];
            }

            // Create transaction record
            $transaction = KcbBuniTransaction::create([
                'application_id' => $application->id,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                'checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
                'phone_number' => $formattedPhone,
                'amount' => 500,
                'invoice_number' => $invoiceNumber,
                'transaction_type' => 'application_fee',
                'status' => 'initiated',
                'request_data' => json_encode([
                    'application_id' => $application->id,
                    'application_number' => $application->application_number,
                    'name' => $application->first_name . ' ' . $application->last_name,
                    'email' => $application->email,
                    'course_id' => $application->course_id,
                    'course_name' => $application->course->name ?? 'N/A',
                    'campus_id' => $application->campus_id,
                    'campus_name' => $application->campus->name ?? 'N/A',
                    'intake_period' => $application->intake_period,
                    'study_mode' => $application->study_mode
                ]),
            ]);

            Log::info('Application payment initiated successfully', [
                'application_id' => $application->id,
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $transaction->checkout_request_id
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'merchant_request_id' => $transaction->merchant_request_id,
                'checkout_request_id' => $transaction->checkout_request_id,
                'message' => 'Payment request sent. Please check your phone to complete payment.',
            ];

        } catch (\Exception $e) {
            Log::error('Application payment initiation exception', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Payment service temporarily unavailable. Please try again.'];
        }
    }

    /**
     * Generate invoice number for application
     */
    private function generateApplicationInvoiceNumber(Application $application)
    {
        // Format: 7664166-APP-{applicationId}-{timestamp}{nn}; never repeated, so the bank's IPN finds the one prompt it paid
        do {
            $invoice = '7664166-APP-' . $application->id . '-' . time() . random_int(10, 99);
        } while (KcbBuniTransaction::where('invoice_number', $invoice)->exists());
        return $invoice;
    }

    /**
     * Handle payment callback
     */
    public function handleApplicationPaymentCallback($callbackData)
    {
        Log::info('Application Payment Callback Data:', $callbackData);

        $stkCallback = $callbackData['Body']['stkCallback'] ?? null;

        if (!$stkCallback) {
            Log::error('Invalid KCB callback format for application', $callbackData);
            return false;
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        if (!$checkoutRequestId || $resultCode === null) {
            Log::warning('Missing required callback fields', $stkCallback);
            return false;
        }

        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->where('transaction_type', 'application_fee')
            ->first();

        if (!$transaction) {
            Log::error('Transaction not found for callback', ['checkout_request_id' => $checkoutRequestId]);
            return false;
        }

        if ($transaction->status === 'completed') {
            return true; // already recorded (callback repeated, or the bank's IPN came first)
        }

        if (\App\Models\KcbBuniTransaction::isSuccess($resultCode)) {
            $metadata = [];
            foreach ($stkCallback['CallbackMetadata']['Item'] ?? [] as $item) {
                if (isset($item['Name'], $item['Value'])) {
                    $metadata[$item['Name']] = $item['Value'];
                }
            }
            $this->markPaid(
                $transaction,
                $metadata['MpesaReceiptNumber'] ?? null,
                isset($metadata['TransactionDate']) ? $this->formatTransactionDate((string) $metadata['TransactionDate']) : now(),
                ['result_code' => $resultCode, 'result_description' => $stkCallback['ResultDesc'] ?? '', 'callback_data' => $callbackData]
            );
            return true;
        }

        $transaction->update([
            'status' => 'failed',
            'result_code' => $resultCode,
            'result_description' => $stkCallback['ResultDesc'] ?? '',
            'callback_data' => $callbackData,
        ]);

        Log::warning('Application payment failed', [
            'checkout_request_id' => $checkoutRequestId,
            'result_code' => $resultCode,
            'result_description' => $stkCallback['ResultDesc'] ?? 'Unknown error'
        ]);

        // Cancelled on the phone (1032) needs no SMS: they are looking at the page
        if ($resultCode != 1032 && $application = $transaction->courseApplication) {
            $this->sendPaymentFailedSms($application);
        }

        return true;
    }

    /**
     * Record the KES 500 as paid, once. Used by the STK callback and, as a backup,
     * by the bank's IPN for the same invoice. Texts the applicant and admissions.
     */
    public function markPaid(KcbBuniTransaction $transaction, ?string $receipt, $paidAt = null, array $extra = [], bool $notify = true): bool
    {
        $updated = DB::transaction(function () use ($transaction, $receipt, $paidAt, $extra) {
            $locked = KcbBuniTransaction::whereKey($transaction->id)->lockForUpdate()->first();
            if (!$locked || $locked->status === 'completed') {
                return false;
            }
            $locked->update($extra + [
                'status' => 'completed',
                'mpesa_receipt_number' => $receipt ?: $locked->mpesa_receipt_number,
                'transaction_date' => $paidAt ?: now(),
            ]);
            $transaction->setRawAttributes($locked->getAttributes(), true);
            return true;
        });

        if (!$updated) {
            return false;
        }

        $application = $transaction->courseApplication()->with(['course', 'campus'])->first();
        Log::info('Application payment completed', [
            'application_id' => $transaction->application_id,
            'application_number' => $application?->application_number,
            'receipt' => $transaction->mpesa_receipt_number,
        ]);

        if ($application && $notify) {
            $this->sendPaymentSuccessSms($application, $transaction);
        }
        return true;
    }

    /**
     * Send payment success SMS
     */
    private function sendPaymentSuccessSms(Application $application, KcbBuniTransaction $transaction)
    {
        try {
            // Send to applicant
            $applicantMessage = "Kenswed: Application fee payment confirmed!\n";
            $applicantMessage .= "App No: {$application->application_number}\n";
            $applicantMessage .= "Amount: KES 500\n";
            $applicantMessage .= "Receipt: {$transaction->mpesa_receipt_number}\n";
            $applicantMessage .= "Course: " . ($application->course->name ?? '-') . "\n";
            $applicantMessage .= "Your application is now complete. We'll contact you soon.\n";
            $applicantMessage .= "Thank you! www.ktvtc.ac.ke";

            $this->smsService->sendSingleSms($application->phone, $applicantMessage);

            // Send to admins
            $adminMessage = "APPLICATION FEE PAID\n";
            $adminMessage .= "App No: {$application->application_number}\n";
            $adminMessage .= "Name: {$application->first_name} {$application->last_name}\n";
            $adminMessage .= "Amount: KES 500\n";
            $adminMessage .= "Receipt: {$transaction->mpesa_receipt_number}\n";
            $adminMessage .= "Course: " . ($application->course->name ?? '-') . "\n";
            $adminMessage .= "Campus: " . ($application->campus->name ?? '-') . "\n";
            $adminMessage .= "Intake: {$application->intake_period}\n";
            $adminMessage .= "Time: " . now()->format('d/m/Y H:i');

            $this->smsService->alert('applications', $adminMessage);

        } catch (\Exception $e) {
            Log::error('Failed to send payment success SMS: ' . $e->getMessage(), [
                'application_id' => $application->id
            ]);
        }
    }

    /**
     * Send payment failed SMS
     */
    private function sendPaymentFailedSms(Application $application)
    {
        try {
            $message = "Kenswed: Payment for application {$application->application_number} was unsuccessful. ";
            $message .= "Please try again or contact support. www.ktvtc.ac.ke";

            $this->smsService->sendSingleSms($application->phone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send payment failure SMS: ' . $e->getMessage());
        }
    }

    /**
     * Format transaction date from M-Pesa
     */
    private function formatTransactionDate($transactionDate)
    {
        if (strlen($transactionDate) === 14) {
            try {
                return \Carbon\Carbon::createFromFormat('YmdHis', $transactionDate);
            } catch (\Exception $e) {
                return now();
            }
        }
        return now();
    }

    /**
     * Status of a prompt (by checkout request id), or of the application's latest prompt.
     * The page polls this; the callback (or the bank's IPN) is what moves it on.
     */
    public function checkPaymentStatus(?string $checkoutRequestId, $applicationId = null)
    {
        $query = KcbBuniTransaction::where('transaction_type', 'application_fee')->with('courseApplication');
        $transaction = $checkoutRequestId
            ? $query->where('checkout_request_id', $checkoutRequestId)->first()
            : ($applicationId ? $query->where('application_id', $applicationId)->orderByRaw("status = 'completed' desc")->latest('id')->first() : null);

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
            'application_id' => $transaction->application_id,
            'application_number' => $transaction->courseApplication->application_number ?? null,
            'amount' => $transaction->amount,
            'message' => $this->getStatusMessage($transaction->status, $transaction->result_description)
        ];
    }

    /**
     * Get status message
     */
    private function getStatusMessage($status, $description)
    {
        switch ($status) {
            case 'completed':
                return 'Payment confirmed successfully!';
            case 'failed':
                return 'Payment failed: ' . $description;
            case 'initiated':
                return 'Waiting for payment confirmation...';
            default:
                return 'Checking payment status...';
        }
    }

    /**
     * Check if application has completed payment
     */
    public function hasCompletedPayment(Application $application)
    {
        return KcbBuniTransaction::where('application_id', $application->id)
            ->where('transaction_type', 'application_fee')
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get latest payment for application
     */
    public function getLatestPayment(Application $application)
    {
        return $application->payments()->latest('id')->first();
    }

    /**
     * Get all payments for application
     */
    public function getApplicationPayments(Application $application)
    {
        return $application->payments()->orderBy('created_at', 'desc')->get();
    }
}
