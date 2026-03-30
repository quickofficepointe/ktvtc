<?php
// app/Traits/SendsSmsNotifications.php

namespace App\Traits;

use App\Services\SmsService;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

trait SendsSmsNotifications
{
    /**
     * Send order confirmation SMS to customer
     */
    public function sendOrderConfirmationSms(Sale $sale)
    {
        try {
            if (!$sale->customer_phone) {
                Log::warning('No customer phone number for SMS notification', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number
                ]);
                return false;
            }

            $smsService = app(SmsService::class);
            $message = $smsService->generateOrderConfirmationMessage($sale);

            $result = $smsService->sendSingleSms($sale->customer_phone, $message);

            if ($result['success']) {
                Log::info(' Order confirmation SMS sent to customer', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number,
                    'phone' => substr($sale->customer_phone, 0, 6) . '...'
                ]);
                return true;
            } else {
                Log::error('❌ Failed to send order confirmation SMS to customer', [
                    'sale_id' => $sale->id,
                    'error' => $result['message']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exception in sendOrderConfirmationSms: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order notification to admins
     */
    public function sendAdminOrderNotification(Sale $sale)
    {
        try {
            $smsService = app(SmsService::class);
            $result = $smsService->sendAdminOrderNotification($sale);

            if ($result['success'] || ($result['success_count'] ?? 0) > 0) {
                Log::info(' Admin notifications sent successfully', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number,
                    'success_count' => $result['success_count'] ?? 0,
                    'total_admins' => $result['total_admins'] ?? 0
                ]);
                return true;
            } else {
                Log::warning('❌ Failed to send admin notifications', [
                    'sale_id' => $sale->id,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exception in sendAdminOrderNotification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmationSms(Sale $sale)
    {
        try {
            if (!$sale->customer_phone) {
                return false;
            }

            $smsService = app(SmsService::class);
            $message = $smsService->generatePaymentConfirmationMessage($sale);

            $result = $smsService->sendSingleSms($sale->customer_phone, $message);

            if ($result['success']) {
                Log::info('Payment confirmation SMS sent', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number
                ]);
                return true;
            } else {
                Log::error(' Failed to send payment confirmation SMS', [
                    'sale_id' => $sale->id,
                    'error' => $result['message']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exception in sendPaymentConfirmationSms: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order status update SMS
     */
    public function sendOrderStatusUpdateSms(Sale $sale, $newStatus)
    {
        try {
            if (!$sale->customer_phone) {
                return false;
            }

            $statusMessages = [
                'confirmed' => "Your order #{$sale->invoice_number} has been confirmed and is being prepared.",
                'preparing' => "Your order #{$sale->invoice_number} is now being prepared.",
                'ready' => "Your order #{$sale->invoice_number} is ready for pickup/delivery.",
                'out_for_delivery' => "Your order #{$sale->invoice_number} is out for delivery.",
                'delivered' => "Your order #{$sale->invoice_number} has been delivered. Thank you!",
                'picked_up' => "Your order #{$sale->invoice_number} has been picked up. Enjoy your meal!",
                'cancelled' => "Your order #{$sale->invoice_number} has been cancelled. Please contact us for assistance."
            ];

            $message = "ORDER UPDATE\n";
            $message .= "====================\n";
            $message .= "Invoice: {$sale->invoice_number}\n";
            $message .= "Status: " . strtoupper($newStatus) . "\n";

            if (isset($statusMessages[$newStatus])) {
                $message .= $statusMessages[$newStatus] . "\n";
            }

            $message .= "====================\n";
            $message .= "Kenswed Cafeteria";

            $smsService = app(SmsService::class);
            $result = $smsService->sendSingleSms($sale->customer_phone, $message);

            if ($result['success']) {
                Log::info(' Order status update SMS sent', [
                    'sale_id' => $sale->id,
                    'status' => $newStatus
                ]);
                return true;
            } else {
                Log::error('❌ Failed to send status update SMS', [
                    'sale_id' => $sale->id,
                    'error' => $result['message']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exception in sendOrderStatusUpdateSms: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if SMS was already sent (using internal_notes instead of SMS logs)
     */
    private function smsAlreadySent(Sale $sale, $type)
    {
        // Check internal_notes for SMS sent indication
        $note = $sale->internal_notes ?? '';
        return str_contains($note, "SMS_{$type}_SENT");
    }

    /**
     * Mark SMS as sent in internal_notes
     */
    private function markSmsAsSent(Sale $sale, $type)
    {
        $currentNote = $sale->internal_notes ?? '';
        $newNote = $currentNote . " SMS_{$type}_SENT:" . now()->format('Y-m-d H:i:s') . " ";
        $sale->update(['internal_notes' => $newNote]);
    }
}
