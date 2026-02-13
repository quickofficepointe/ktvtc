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

        $message = "NEW ORDER ALERT\n";
        $message .= "====================\n";
        $message .= "Shop: {$shopName}\n";
        $message .= "Invoice: {$sale->invoice_number}\n";
        $message .= "Type: {$orderType}\n";
        $message .= "Customer: {$sale->customer_name}\n";
        $message .= "Phone: {$sale->customer_phone}\n";
        $message .= "Time: " . $sale->sale_date->format('H:i') . "\n";
        $message .= "Items: {$sale->total_items}\n";
        $message .= "Amount: KES " . number_format($sale->total_amount, 2) . "\n";
        $message .= "Payment: " . strtoupper($sale->payment_method) . " - " . strtoupper($sale->payment_status) . "\n";
        $message .= "Status: " . strtoupper($sale->order_status) . "\n";

        if ($sale->delivery_address) {
            $message .= "Delivery: {$sale->delivery_address}\n";
        }

        $message .= "====================\n";
        $message .= "Check system for details.\n";
        $message .= "Kenswed Cafeteria System";

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
    public function generateOrderConfirmationMessage(Sale $sale)
    {
        $items = '';
        $itemCount = 0;
        foreach ($sale->items as $index => $item) {
            $items .= ($index + 1) . ". {$item->product_name} x{$item->quantity} - KES " . number_format($item->final_price, 2) . "\n";
            $itemCount++;

            // Limit to first 5 items for SMS length
            if ($itemCount >= 5) {
                $remaining = $sale->total_items - 5;
                if ($remaining > 0) {
                    $items .= "...and {$remaining} more item(s)\n";
                }
                break;
            }
        }

        $message = "ORDER CONFIRMATION\n";
        $message .= "====================\n";
        $message .= "Invoice: {$sale->invoice_number}\n";
        $message .= "Date: " . $sale->sale_date->format('d/m/Y H:i') . "\n";
        $message .= "Items:\n{$items}";
        $message .= "Subtotal: KES " . number_format($sale->subtotal, 2) . "\n";

        if ($sale->tax_amount > 0) {
            $message .= "Tax: KES " . number_format($sale->tax_amount, 2) . "\n";
        }

        if ($sale->delivery_fee > 0) {
            $message .= "Delivery: KES " . number_format($sale->delivery_fee, 2) . "\n";
        }

        if ($sale->discount_amount > 0) {
            $message .= "Discount: -KES " . number_format($sale->discount_amount, 2) . "\n";
        }

        $message .= "TOTAL: KES " . number_format($sale->total_amount, 2) . "\n";
        $message .= "Payment: " . strtoupper($sale->payment_method) . "\n";

        if ($sale->mpesa_receipt) {
            $message .= "Receipt: {$sale->mpesa_receipt}\n";
        }

        $message .= "Status: " . strtoupper($sale->order_status) . "\n";

        if ($sale->delivery_address) {
            $message .= "Address: {$sale->delivery_address}\n";
        }

        $message .= "====================\n";
        $message .= "Thank you! \n";
        $message .= "Kenswed Cafeteria";

        return $message;
    }

    /**
     * Generate payment confirmation message
     */
    public function generatePaymentConfirmationMessage(Sale $sale)
    {
        $message = "PAYMENT CONFIRMED\n";
        $message .= "====================\n";
        $message .= "Invoice: {$sale->invoice_number}\n";
        $message .= "Amount: KES " . number_format($sale->total_amount, 2) . "\n";
        $message .= "Method: " . strtoupper($sale->payment_method) . "\n";

        if ($sale->mpesa_receipt) {
            $message .= "M-Pesa Receipt: {$sale->mpesa_receipt}\n";
        }

        $message .= "Date: " . now()->format('d/m/Y H:i') . "\n";
        $message .= "Status: " . strtoupper($sale->order_status) . "\n";
        $message .= "====================\n";
        $message .= "Thank you for your payment!\n";
        $message .= "Kenswed Cafeteria";

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
