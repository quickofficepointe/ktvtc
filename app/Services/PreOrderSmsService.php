<?php
// app/Services/PreOrderSmsService.php

namespace App\Services;

use App\Models\PreOrderAccount;
use App\Models\PreOrder;
use Illuminate\Support\Facades\Log;

class PreOrderSmsService
{
    protected SmsService $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    // ─── First-visit verification ───
    public function sendVerificationCode(string $phone, string $code, string $firstName = ''): array
    {
        $greeting = $firstName ? "Hi {$firstName}," : "Hi,";

        $message = "{$greeting} your KTVTC Pre-Order verification code is:\n\n"
                 . "{$code}\n\n"
                 . "Enter it on the portal to finish setting up your PIN. "
                 . "This code expires in 10 minutes.\n\n"
                 . "KTVTC";

        return $this->sms->sendSingleSms($phone, $message);
    }

    // ─── PIN reset ───
    public function sendPinResetCode(string $phone, string $code, string $firstName = ''): array
    {
        $greeting = $firstName ? "Hi {$firstName}," : "Hi,";

        $message = "{$greeting} your KTVTC Pre-Order PIN reset code is:\n\n"
                 . "{$code}\n\n"
                 . "Enter it on the portal to set a new PIN. "
                 . "This code expires in 10 minutes. "
                 . "If you didn't request this, ignore this message.\n\n"
                 . "KTVTC";

        return $this->sms->sendSingleSms($phone, $message);
    }

    // ─── Order placed (customer) ───
    public function sendOrderPlacedNotification(PreOrder $order): array
    {
        $account = $order->account;
        if (!$account || !$account->phone) {
            return ['success' => false, 'message' => 'No phone on account'];
        }

        $itemsText = $order->items
            ->take(3)
            ->map(fn($i) => "{$i->quantity}x {$i->product_name}")
            ->implode(', ');

        if ($order->items->count() > 3) {
            $extra = $order->items->count() - 3;
            $itemsText .= " +{$extra} more";
        }

        $message = "KTVTC Pre-Order Confirmed\n"
                 . "Order: {$order->order_number}\n"
                 . "Items: {$itemsText}\n"
                 . "Total: KES " . number_format($order->total_amount, 2) . "\n"
                 . "Pickup: Cafeteria\n\n"
                 . "Track your order on the portal.\n\n"
                 . "KTVTC";

        return $this->sms->sendSingleSms($account->phone, $message);
    }

    // ─── Order ready (customer) ───
    public function sendOrderReadyNotification(PreOrder $order): array
    {
        $account = $order->account;
        if (!$account || !$account->phone) {
            return ['success' => false, 'message' => 'No phone on account'];
        }

        $message = "KTVTC Pre-Order READY\n"
                 . "Order: {$order->order_number}\n"
                 . "Total: KES " . number_format($order->total_amount, 2) . "\n\n"
                 . "Collect it at the cafeteria. Show this order number.\n\n"
                 . "KTVTC";

        return $this->sms->sendSingleSms($account->phone, $message);
    }

    // ─── Order cancelled (customer) ───
    public function sendOrderCancelledNotification(PreOrder $order, string $reason = ''): array
    {
        $account = $order->account;
        if (!$account || !$account->phone) {
            return ['success' => false, 'message' => 'No phone on account'];
        }

        $message = "KTVTC Pre-Order CANCELLED\n"
                 . "Order: {$order->order_number}\n";

        if ($reason) {
            $message .= "Reason: {$reason}\n";
        }

        $message .= "\nAny payment will be refunded per our policy.\n\nKTVTC";

        return $this->sms->sendSingleSms($account->phone, $message);
    }

    // ─── Payment failed (customer) ───
    public function sendPaymentFailedNotification(PreOrder $order, string $reason = ''): array
    {
        $account = $order->account;
        if (!$account || !$account->phone) {
            return ['success' => false, 'message' => 'No phone on account'];
        }

        $message = "KTVTC Pre-Order Payment FAILED\n"
                 . "Order: {$order->order_number}\n";

        if ($reason) {
            $message .= "Reason: {$reason}\n";
        }

        $message .= "\nYou can retry on the portal.\n\nKTVTC";

        return $this->sms->sendSingleSms($account->phone, $message);
    }

    // ─── New order (cafeteria admins) ───
    public function sendNewOrderToCafeteria(PreOrder $order): array
    {
        $account = $order->account;
        $customerName = $account?->display_name ?? 'Unknown';
        $customerType = $account?->type_label ?? 'Customer';

        $itemsText = $order->items
            ->take(3)
            ->map(fn($i) => "{$i->quantity}x {$i->product_name}")
            ->implode(', ');

        if ($order->items->count() > 3) {
            $extra = $order->items->count() - 3;
            $itemsText .= " +{$extra} more";
        }

        $message = "NEW PRE-ORDER\n"
                 . "Order: {$order->order_number}\n"
                 . "Customer: {$customerName} ({$customerType})\n"
                 . "Items: {$itemsText}\n"
                 . "Total: KES " . number_format($order->total_amount, 2) . "\n"
                 . "Payment: " . strtoupper($order->payment_method ?? 'PENDING') . "\n\n"
                 . "Open the cafeteria portal to accept.";

        $results = [];
        foreach ($this->sms->getAdminPhones() as $adminPhone) {
            $results[$adminPhone] = $this->sms->sendSingleSms($adminPhone, $message);
        }

        return [
            'success' => count(array_filter($results, fn($r) => $r['success'] ?? false)) > 0,
            'results' => $results,
        ];
    }
}