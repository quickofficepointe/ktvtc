<?php
// app/Services/KcbService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KcbService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $callbackBaseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.kcb_buni.base_url', 'https://api.buni.kcbgroup.com');
        $this->consumerKey = config('services.kcb_buni.consumer_key');
        $this->consumerSecret = config('services.kcb_buni.consumer_secret');
        $this->callbackBaseUrl = config('services.kcb_buni.callback_base_url', config('app.url'));
    }

    protected function getAccessToken()
    {
        $cachedToken = Cache::get('kcb_buni_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $authUrl = $this->baseUrl . '/token?grant_type=client_credentials';

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->post($authUrl);

            if ($response->failed()) {
                Log::error('KCB Buni Auth Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $authUrl
                ]);
                return null;
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? 3600;

            if ($accessToken) {
                Cache::put('kcb_buni_access_token', $accessToken, $expiresIn * 0.95);
                return $accessToken;
            }

            Log::error('KCB Buni Auth - No access token in response', $tokenData);
            return null;

        } catch (\Exception $e) {
            Log::error('KCB Buni Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate phone number format for M-Pesa
     */
    public function validatePhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);

        // Convert to 254 format if needed
        if (strlen($cleanNumber) === 9 && $cleanNumber[0] === '7') {
            $cleanNumber = '254' . $cleanNumber;
        } elseif (strlen($cleanNumber) === 10 && $cleanNumber[0] === '0') {
            $cleanNumber = '254' . substr($cleanNumber, 1);
        }

        // Validate final format
        if (strlen($cleanNumber) === 12 && substr($cleanNumber, 0, 3) === '254') {
            return $cleanNumber;
        }

        return false;
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($checkoutRequestId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Payment service authentication failed'];
        }

        try {
            $statusUrl = $this->baseUrl . '/mm/api/query/1.0.0/stkpush';

            $payload = [
                "checkoutRequestID" => $checkoutRequestId
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->post($statusUrl, $payload);

            $responseBody = $response->json();
            Log::info('KCB Buni Transaction Status Check:', $responseBody);

            if (isset($responseBody['fault'])) {
                return ['error' => $responseBody['fault']['message'] ?? 'Status check failed'];
            }

            return [
                'success' => true,
                'response' => $responseBody
            ];

        } catch (\Exception $e) {
            Log::error('KCB Buni Status Check Exception: ' . $e->getMessage());
            return ['error' => 'Failed to check transaction status'];
        }
    }

    /**
     * Initiate STK Push for any payment (GENERIC METHOD)
     */
    public function initiateStkPush($phoneNumber, $amount, $invoiceNumber, $callbackUrl, $description = 'Payment')
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Payment service authentication failed'];
        }

        $stkPushUrl = $this->baseUrl . '/mm/api/request/1.0.0/stkpush';

        try {
            $amountString = number_format($amount, 0, '', '');

            $payload = [
                "phoneNumber" => $phoneNumber,
                "amount" => $amountString,
                "invoiceNumber" => $invoiceNumber,
                "sharedShortCode" => true,
                "orgShortCode" => "",
                "orgPassKey" => "",
                "callbackUrl" => $callbackUrl,
                "transactionDescription" => substr($description, 0, 30)
            ];

            Log::info('KCB STK Push Request:', $payload);

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->post($stkPushUrl, $payload);

            $responseBody = $response->json();
            Log::info('KCB STK Push Response:', $responseBody);

            if (isset($responseBody['fault'])) {
                $errorMessage = $responseBody['fault']['message'] ?? 'Payment request failed';
                Log::error('KCB STK Push Fault:', $responseBody);
                return ['error' => $errorMessage];
            }

            $statusCode = $responseBody['header']['statusCode'] ?? '1';
            if ($response->failed() || $statusCode !== '0') {
                Log::error('KCB STK Push Failed', [
                    'http_status' => $response->status(),
                    'response_body' => $responseBody
                ]);
                return [
                    'error' => $responseBody['header']['statusDescription']
                        ?? $responseBody['response']['ResponseDescription']
                        ?? 'Failed to initiate payment'
                ];
            }

            return [
                'success' => true,
                'MerchantRequestID' => $responseBody['response']['MerchantRequestID'],
                'CheckoutRequestID' => $responseBody['response']['CheckoutRequestID'] ?? null,
                'ResponseCode' => $responseBody['response']['ResponseCode'] ?? null,
                'ResponseDescription' => $responseBody['response']['ResponseDescription'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('KCB STK Push Exception: ' . $e->getMessage());
            return ['error' => 'An unexpected error occurred. Please try again.'];
        }
    }

    /**
     * Generate invoice number for event payments (SPECIFIC TO EVENTS)
     */
    public function generateEventInvoiceNumber($eventId, $applicationId)
    {
        return '7664166-EVT-' . $eventId . '-' . $applicationId . '-' . time();
    }
}
