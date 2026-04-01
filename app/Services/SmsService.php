<?php
// app/Services/SmsService.php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $apiKey;
    private $partnerID;
    private $baseUrl;
    private $senderId;

    // Add admin phone numbers
    private $adminPhones = [
        '0708112014',
        '0792248340'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key', env('SMS_API_KEY', '95fdeea310e7d411d62f3913fbc7be83'));
        $this->partnerID = config('services.sms.partner_id', env('SMS_PARTNER_ID', '12632'));
        $this->baseUrl = config('services.sms.base_url', env('SMS_BASE_URL', 'https://quicksms.advantasms.com/api/services'));
        $this->senderId = config('services.sms.sender_id', env('SMS_SENDER_ID', 'Kenswed'));

        Log::info('SMS Service Initialized', [
            'api_key' => substr($this->apiKey, 0, 8) . '...', // Log partial for security
            'partner_id' => $this->partnerID,
            'base_url' => $this->baseUrl,
            'sender_id' => $this->senderId,
            'admin_phones_count' => count($this->adminPhones)
        ]);
    }

    /**
     * Send single SMS
     */
    public function sendSingleSms($phone, $message)
    {
        try {
            // Validate configuration
            if (!$this->validateConfig()) {
                Log::error('SMS configuration invalid');
                return [
                    'success' => false,
                    'message' => 'SMS service not configured properly'
                ];
            }

            // Clean phone number
            $cleanPhone = $this->formatPhoneNumber($phone);

            if (!$cleanPhone) {
                Log::error('Invalid phone number format: ' . $phone);
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ];
            }

            $endpoint = $this->baseUrl . '/sendsms/';

            Log::info('Sending SMS', [
                'phone' => substr($cleanPhone, 0, 6) . '...',
                'message_length' => strlen($message),
                'endpoint' => $endpoint
            ]);

            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($endpoint, [
                    'apikey' => $this->apiKey,
                    'partnerID' => $this->partnerID,
                    'message' => $message,
                    'shortcode' => $this->senderId,
                    'mobile' => $cleanPhone
                ]);

            $responseData = $response->json();
            Log::info('SMS API Response:', $responseData);

            if ($response->successful()) {
                // Check response code
                if (isset($responseData['responses'][0]['respose-code']) &&
                    $responseData['responses'][0]['respose-code'] == 200) {

                    Log::info('SMS sent successfully', [
                        'message_id' => $responseData['responses'][0]['messageid'] ?? null,
                        'network_id' => $responseData['responses'][0]['networkid'] ?? null
                    ]);

                    return [
                        'success' => true,
                        'data' => $responseData,
                        'message_id' => $responseData['responses'][0]['messageid'] ?? null,
                        'message' => 'SMS sent successfully'
                    ];
                } else {
                    $error = $responseData['responses'][0]['response-description'] ?? 'Unknown error';
                    Log::error('SMS API returned error: ' . $error);
                    return [
                        'success' => false,
                        'message' => 'SMS failed: ' . $error
                    ];
                }
            } else {
                Log::error('SMS API HTTP Error: ' . $response->status() . ' - ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Failed to send SMS: HTTP ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error('SMS Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMS service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS to admins about new order
     */
    public function sendAdminOrderNotification(Sale $sale)
    {
        try {
            if (empty($this->adminPhones)) {
                Log::warning('No admin phone numbers configured for notifications');
                return [
                    'success' => false,
                    'message' => 'No admin phone numbers configured'
                ];
            }

            $message = $this->generateAdminOrderNotificationMessage($sale);
            $results = [];
            $successCount = 0;

            foreach ($this->adminPhones as $adminPhone) {
                $result = $this->sendSingleSms($adminPhone, $message);
                $results[$adminPhone] = $result;

                if ($result['success']) {
                    $successCount++;
                }
            }

            Log::info('Admin notifications sent', [
                'total_admins' => count($this->adminPhones),
                'successful' => $successCount,
                'failed' => count($this->adminPhones) - $successCount,
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_number
            ]);

            return [
                'success' => $successCount > 0,
                'results' => $results,
                'total_admins' => count($this->adminPhones),
                'success_count' => $successCount
            ];

        } catch (\Exception $e) {
            Log::error('Admin notification exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send admin notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate admin notification message
     */
    /**
 * Generate admin notification message
 */
public function generateAdminOrderNotificationMessage(Sale $sale)
{
    $shop = $sale->shop;
    $shopName = $shop ? $shop->shop_name : 'Unknown Shop';

    $orderTypeMap = [
        'pos' => 'POS Counter',
        'online' => 'Online',
        'mobile' => 'Mobile App',
        'preorder' => 'Pre-order',
        'delivery' => 'Delivery'
    ];

    $orderType = $orderTypeMap[$sale->sale_type] ?? ucfirst($sale->sale_type);

    $message = "New order received from {$shopName}. Invoice: {$sale->invoice_number}. Type: {$orderType}. Customer: {$sale->customer_name} ({$sale->customer_phone}). Total items: {$sale->total_items}. Amount: KES " . number_format($sale->total_amount, 2) . ". Payment: {$sale->payment_method} - {$sale->payment_status}. Order status: {$sale->order_status}.";

    if ($sale->delivery_address) {
        $message .= " Delivery address: {$sale->delivery_address}.";
    }

    $message .= " Please check the system for details. Kenswed Cafeteria.";

    return $message;
}

    /**
     * Send bulk SMS (up to 20 messages)
     */
    public function sendBulkSms($messages)
    {
        try {
            if (!$this->validateConfig()) {
                return [
                    'success' => false,
                    'message' => 'SMS service not configured properly'
                ];
            }

            $endpoint = $this->baseUrl . '/sendbulk/';

            $payload = [
                'count' => count($messages),
                'smslist' => []
            ];

            foreach ($messages as $index => $sms) {
                $cleanPhone = $this->formatPhoneNumber($sms['mobile']);

                if (!$cleanPhone) {
                    Log::warning('Skipping invalid phone: ' . ($sms['mobile'] ?? 'empty'));
                    continue;
                }

                $payload['smslist'][] = [
                    'partnerID' => $this->partnerID,
                    'apikey' => $this->apiKey,
                    'pass_type' => 'plain',
                    'clientsmsid' => $index + 1,
                    'mobile' => $cleanPhone,
                    'message' => $sms['message'],
                    'shortcode' => $this->senderId
                ];
            }

            if (empty($payload['smslist'])) {
                return [
                    'success' => false,
                    'message' => 'No valid phone numbers provided'
                ];
            }

            Log::info('Sending bulk SMS', [
                'count' => count($payload['smslist']),
                'endpoint' => $endpoint
            ]);

            $response = Http::timeout(60)
                ->retry(2, 100)
                ->post($endpoint, $payload);

            $responseData = $response->json();
            Log::info('Bulk SMS API Response:', $responseData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Bulk SMS sent successfully'
                ];
            } else {
                Log::error('Bulk SMS API Error: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Failed to send bulk SMS: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Bulk SMS Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bulk SMS service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get delivery report
     */
    public function getDeliveryReport($messageId)
    {
        try {
            if (!$this->validateConfig()) {
                return [
                    'success' => false,
                    'message' => 'SMS service not configured properly'
                ];
            }

            $endpoint = $this->baseUrl . '/getdlr/';

            $response = Http::post($endpoint, [
                'apikey' => $this->apiKey,
                'partnerID' => $this->partnerID,
                'messageID' => $messageId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['responses'][0]['response-description'] ?? 'Unknown'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get delivery report'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Delivery Report Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Delivery report error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get account balance
     */
    public function getBalance()
    {
        try {
            if (!$this->validateConfig()) {
                return [
                    'success' => false,
                    'message' => 'SMS service not configured properly'
                ];
            }

            $endpoint = $this->baseUrl . '/getbalance/';

            Log::info('Checking SMS balance');

            $response = Http::post($endpoint, [
                'apikey' => $this->apiKey,
                'partnerID' => $this->partnerID
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('SMS Balance Response:', $data);

                return [
                    'success' => true,
                    'data' => $data,
                    'balance' => $data['balance'] ?? null,
                    'credit' => $data['credit'] ?? null
                ];
            } else {
                Log::error('Balance check failed: ' . $response->status());
                return [
                    'success' => false,
                    'message' => 'Failed to get account balance'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Get Balance Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Balance check error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate configuration
     */
    private function validateConfig()
    {
        if (empty($this->apiKey) || empty($this->partnerID) || empty($this->baseUrl)) {
            Log::error('SMS configuration incomplete', [
                'has_api_key' => !empty($this->apiKey),
                'has_partner_id' => !empty($this->partnerID),
                'has_base_url' => !empty($this->baseUrl)
            ]);
            return false;
        }
        return true;
    }

    /**
     * Format phone number to 254 format
     */
    private function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $cleanNumber = preg_replace('/\D/', '', $phone);

        // Convert to 254 format
        if (strlen($cleanNumber) === 9 && substr($cleanNumber, 0, 1) === '7') {
            // 712345678 -> 254712345678
            return '254' . $cleanNumber;
        } elseif (strlen($cleanNumber) === 10 && substr($cleanNumber, 0, 1) === '0') {
            // 0712345678 -> 254712345678
            return '254' . substr($cleanNumber, 1);
        } elseif (strlen($cleanNumber) === 12 && substr($cleanNumber, 0, 3) === '254') {
            // Already in correct format
            return $cleanNumber;
        }

        Log::warning('Invalid phone number format', ['original' => $phone, 'cleaned' => $cleanNumber]);
        return null;
    }

    /**
     * Generate order confirmation message for customer
     */
 /**
 * Generate order confirmation message for customer
 */
public function generateOrderConfirmationMessage(Sale $sale)
{
    $items = '';
    $itemCount = 0;
    foreach ($sale->items as $index => $item) {
        $items .= "{$item->product_name} x{$item->quantity} - KES " . number_format($item->final_price, 2);
        $itemCount++;

        if ($itemCount < count($sale->items)) {
            $items .= ", ";
        }

        // Limit to first 5 items for SMS length
        if ($itemCount >= 5) {
            $remaining = $sale->total_items - 5;
            if ($remaining > 0) {
                $items .= " and {$remaining} more item(s)";
            }
            break;
        }
    }

    $message = "Dear {$sale->customer_name}, thank you for your order. Invoice: {$sale->invoice_number}. Items: {$items}. Total: KES " . number_format($sale->total_amount, 2) . ". Payment: {$sale->payment_method}.";

    if ($sale->mpesa_receipt) {
        $message .= " M-Pesa Receipt: {$sale->mpesa_receipt}.";
    }

    if ($sale->delivery_address) {
        $message .= " Delivery to: {$sale->delivery_address}.";
    }

    $message .= " Order status: {$sale->order_status}. We will notify you when your order is ready. Thank you for choosing Kenswed Cafeteria.";

    return $message;
}
/**
 * Generate admin application notification message
 */
public function generateAdminApplicationNotificationMessage($application, $course, $campusName)
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $applicationTime = $application->submitted_at->format('d/m/Y H:i');

    $message = "New application received from {$fullName} ({$application->phone}) for {$course->name} at {$campusName} campus. Application Number: {$application->application_number}. Intake: {$application->intake_period}. Mode: " . ucfirst(str_replace('_', ' ', $application->study_mode)) . ". Applied on {$applicationTime}. Registration fee of KES 500 pending. Please review in admin panel. Kenswed Technical College.";

    return $message;
}

/**
 * Generate applicant confirmation message
 */
public function generateApplicantConfirmationMessage($application, $course)
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $applicationTime = $application->submitted_at->format('d/m/Y H:i');

    $message = "Dear {$fullName}, thank you for applying to Kenswed Technical College. Your application for {$course->name} ({$application->intake_period} intake) has been received. Application Number: {$application->application_number}. To complete your application, please pay the registration fee of KES 500 via the link provided. Regards, Kenswed Technical College.";

    return $message;
}

/**
 * Generate payment confirmation message for application
 */
public function generateApplicationPaymentConfirmationMessage($application, $payment)
{
    $fullName = $application->first_name . ' ' . $application->last_name;

    $message = "Dear {$fullName}, payment of KES " . number_format($payment->amount, 2) . " for application {$application->application_number} has been confirmed. M-Pesa Receipt: {$payment->mpesa_receipt_number}. Your application is now complete and under review. We will contact you soon. Thank you for choosing Kenswed Technical College.";

    return $message;
}

/**
 * Generate application status update message
 */
public function generateApplicationStatusUpdateMessage($application)
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $courseName = $application->course ? $application->course->name : 'your course';

    if ($application->status === 'accepted') {
        $message = "Dear {$fullName}, congratulations! Your application for {$courseName} (App No: {$application->application_number}) has been accepted. Admission details will be sent to you shortly. Welcome to Kenswed Technical College.";
    } elseif ($application->status === 'rejected') {
        $message = "Dear {$fullName}, thank you for applying to Kenswed Technical College. We regret to inform you that your application for {$courseName} (App No: {$application->application_number}) was not successful. We wish you all the best in your future endeavors.";
    } elseif ($application->status === 'waiting_list') {
        $message = "Dear {$fullName}, your application for {$courseName} (App No: {$application->application_number}) has been placed on our waiting list. We will notify you immediately if a space becomes available. Thank you for your patience.";
    } else {
        $message = "Dear {$fullName}, your application for {$courseName} (App No: {$application->application_number}) is now {$application->status}. We will update you once a decision is made. Thank you for applying to Kenswed Technical College.";
    }

    return $message;
}
    /**
     * Generate payment confirmation message
     */
/**
 * Generate payment confirmation message
 */
public function generatePaymentConfirmationMessage(Sale $sale)
{
    $message = "Dear {$sale->customer_name}, payment of KES " . number_format($sale->total_amount, 2) . " for invoice {$sale->invoice_number} has been confirmed.";

    if ($sale->mpesa_receipt) {
        $message .= " M-Pesa Receipt: {$sale->mpesa_receipt}.";
    }

    $message .= " Payment method: {$sale->payment_method}. Your order is now being processed. Thank you for choosing Kenswed Cafeteria.";

    return $message;
}

    /**
     * Get admin phone numbers
     */
    public function getAdminPhones()
    {
        return $this->adminPhones;
    }
}
