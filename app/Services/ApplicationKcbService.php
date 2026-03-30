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
        // Format: 7664166-APP-{applicationId}-{timestamp}
        return '7664166-APP-' . $application->id . '-' . time();
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

        // Find transaction by checkout_request_id
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->with('application.course')
            ->with('application.campus')
            ->first();

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

            // Update transaction
            $transaction->update($updateData);

            // Update application (optional - you might want to mark as paid)
            $application = $transaction->application;
            if ($application) {
                // You could add a 'payment_status' field to applications table if needed
                Log::info('Application payment completed successfully', [
                    'application_id' => $application->id,
                    'application_number' => $application->application_number,
                    'receipt' => $updateData['mpesa_receipt_number']
                ]);

                // Send SMS notifications
                $this->sendPaymentSuccessSms($application, $transaction);
            }

        } else {
            // Payment failed
            $updateData['status'] = 'failed';
            $transaction->update($updateData);

            Log::warning('Application payment failed', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_description' => $stkCallback['ResultDesc'] ?? 'Unknown error'
            ]);

            // Send failure SMS
            $application = $transaction->application;
            if ($application) {
                $this->sendPaymentFailedSms($application);
            }
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
            $applicantMessage .= "Course: {$application->course->name}\n";
            $applicantMessage .= "Your application is now complete. We'll contact you soon.\n";
            $applicantMessage .= "Thank you! www.ktvtc.ac.ke";

            $this->smsService->sendSingleSms($application->phone, $applicantMessage);

            // Send to admins
            $adminMessage = "APPLICATION FEE PAID\n";
            $adminMessage .= "App No: {$application->application_number}\n";
            $adminMessage .= "Name: {$application->first_name} {$application->last_name}\n";
            $adminMessage .= "Amount: KES 500\n";
            $adminMessage .= "Receipt: {$transaction->mpesa_receipt_number}\n";
            $adminMessage .= "Course: {$application->course->name}\n";
            $adminMessage .= "Campus: {$application->campus->name}\n";
            $adminMessage .= "Intake: {$application->intake_period}\n";
            $adminMessage .= "Time: " . now()->format('d/m/Y H:i');

            foreach ($this->smsService->getAdminPhones() as $adminPhone) {
                $this->smsService->sendSingleSms($adminPhone, $adminMessage);
            }

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
     * Check payment status
     */
    public function checkPaymentStatus($checkoutRequestId)
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

        // If still pending, check with KCB
        if ($transaction->status === 'initiated') {
            $statusResponse = $this->kcbService->checkTransactionStatus($checkoutRequestId);

            if (isset($statusResponse['response']['Body']['stkCallback'])) {
                $callbackData = $statusResponse['response'];
                // You could update the transaction here if needed
            }
        }

        return [
            'success' => true,
            'status' => $transaction->status,
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'application_id' => $transaction->application_id,
            'application_number' => $transaction->application->application_number ?? null,
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
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get latest payment for application
     */
    public function getLatestPayment(Application $application)
    {
        return KcbBuniTransaction::where('application_id', $application->id)
            ->latest()
            ->first();
    }

    /**
     * Get all payments for application
     */
    public function getApplicationPayments(Application $application)
    {
        return KcbBuniTransaction::where('application_id', $application->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
