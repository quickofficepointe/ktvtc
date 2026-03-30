<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Models\KcbBuniTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesPaymentService
{
    protected $kcbSalesService;

    public function __construct(KcbSalesService $kcbSalesService)
    {
        $this->kcbSalesService = $kcbSalesService;
    }

    /**
     * Process sale and initiate payment
     */
    public function processSaleAndPayment(Sale $sale, $phoneNumber, $amount = null)
    {
        try {
            DB::beginTransaction();

            // Validate sale can accept payment
            if ($sale->payment_status === 'paid') {
                return ['error' => 'Sale has already been paid'];
            }

            if ($sale->payment_status === 'cancelled') {
                return ['error' => 'Sale has been cancelled'];
            }

            $amount = $amount ?? $sale->total_amount;

            // Validate phone number
            $formattedPhone = $this->kcbSalesService->validatePhoneNumber($phoneNumber);
            if (!$formattedPhone) {
                return ['error' => 'Invalid phone number format. Please use format: 0712 345 678'];
            }

            // Update sale with customer phone
            if (!$sale->customer_phone) {
                $sale->update([
                    'customer_phone' => $phoneNumber
                ]);
            }

            DB::commit();

            // Initiate KCB payment
            $paymentResult = $this->kcbSalesService->initiateSalePayment($sale, $phoneNumber, $amount);

            if (isset($paymentResult['error'])) {
                return $paymentResult;
            }

            return [
                'success' => true,
                'sale' => $sale,
                'payment' => $paymentResult,
                'message' => 'Payment request sent successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales Payment Service Error: ' . $e->getMessage());
            return ['error' => 'Failed to process payment. Please try again.'];
        }
    }

    /**
     * Handle payment callback for sales
     */
    public function handleSalePaymentCallback($callbackData)
    {
        return $this->kcbSalesService->handleSalePaymentCallback($callbackData);
    }

    /**
     * Check payment status for sale
     */
    public function checkSalePaymentStatus($checkoutRequestId)
    {
        return $this->kcbSalesService->checkSalePaymentStatus($checkoutRequestId);
    }

    /**
     * Get sale payment status
     */
    public function getSalePaymentStatus($saleId)
    {
        try {
            $sale = Sale::with(['kcbTransactions' => function($query) {
                $query->latest();
            }, 'paymentTransactions' => function($query) {
                $query->latest();
            }])->find($saleId);

            if (!$sale) {
                return [
                    'success' => false,
                    'message' => 'Sale not found'
                ];
            }

            $latestKcbTransaction = $sale->kcbTransactions->first();
            $latestPaymentTransaction = $sale->paymentTransactions->first();

            return [
                'success' => true,
                'sale_status' => $sale->payment_status,
                'invoice_number' => $sale->invoice_number,
                'total_amount' => $sale->total_amount,
                'mpesa_receipt' => $sale->mpesa_receipt,
                'kcb_invoice_number' => $sale->kcb_invoice_number,
                'latest_kcb_transaction' => $latestKcbTransaction ? [
                    'status' => $latestKcbTransaction->status,
                    'result_code' => $latestKcbTransaction->result_code,
                    'result_description' => $latestKcbTransaction->result_description,
                    'mpesa_receipt_number' => $latestKcbTransaction->mpesa_receipt_number,
                    'invoice_number' => $latestKcbTransaction->invoice_number,
                    'created_at' => $latestKcbTransaction->created_at
                ] : null,
                'latest_payment_transaction' => $latestPaymentTransaction ? [
                    'status' => $latestPaymentTransaction->status,
                    'amount' => $latestPaymentTransaction->amount,
                    'mpesa_receipt' => $latestPaymentTransaction->mpesa_receipt,
                    'kcb_invoice_number' => $latestPaymentTransaction->kcb_invoice_number,
                    'created_at' => $latestPaymentTransaction->created_at
                ] : null
            ];

        } catch (\Exception $e) {
            Log::error('Get sale payment status failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve payment status'
            ];
        }
    }

    /**
     * Cancel sale payment
     */
    public function cancelSalePayment($saleId)
    {
        try {
            DB::beginTransaction();

            $sale = Sale::find($saleId);

            if (!$sale) {
                return ['error' => 'Sale not found'];
            }

            // Only allow cancellation for pending payments
            if (!in_array($sale->payment_status, ['pending', 'failed'])) {
                return ['error' => 'Cannot cancel sale with current status: ' . $sale->payment_status];
            }

            // Update sale status
            $sale->update([
                'payment_status' => 'cancelled',
                'notes' => $sale->notes . ' | Payment cancelled by user'
            ]);

            // Update KCB transactions
            KcbBuniTransaction::where('sale_id', $saleId)
                ->where('status', 'initiated')
                ->update([
                    'status' => 'cancelled',
                    'result_description' => 'Payment cancelled'
                ]);

            // Update payment transactions
            PaymentTransaction::where('sale_id', $saleId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled'
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Sale payment cancelled successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale payment cancellation failed: ' . $e->getMessage());
            return ['error' => 'Failed to cancel payment. Please try again.'];
        }
    }

    /**
     * Get payment summary for sales
     */
    public function getSalesPaymentSummary($shopId = null, $startDate = null, $endDate = null)
    {
        try {
            $query = KcbBuniTransaction::with('sale')
                ->where('transaction_type', 'sale_payment');

            if ($shopId) {
                $query->whereHas('sale', function($q) use ($shopId) {
                    $q->where('shop_id', $shopId);
                });
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $transactions = $query->get();

            $summary = [
                'total_transactions' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'successful_payments' => $transactions->where('status', 'completed')->count(),
                'failed_payments' => $transactions->where('status', 'failed')->count(),
                'pending_payments' => $transactions->where('status', 'initiated')->count(),
                'cancelled_payments' => $transactions->where('status', 'cancelled')->count(),
            ];

            return [
                'success' => true,
                'summary' => $summary,
                'recent_transactions' => $transactions->take(10)
            ];

        } catch (\Exception $e) {
            Log::error('Get sales payment summary failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve payment summary'
            ];
        }
    }

    /**
     * Verify payment manually
     */
    public function verifyAndUpdatePayment($saleId, $mpesaReferenceNumber)
    {
        try {
            DB::beginTransaction();

            $sale = Sale::find($saleId);

            if (!$sale) {
                return ['error' => 'Sale not found'];
            }

            // Update sale
            $sale->update([
                'payment_status' => 'paid',
                'mpesa_receipt' => $mpesaReferenceNumber,
                'transaction_id' => $mpesaReferenceNumber,
                'payment_confirmed_at' => now(),
                'notes' => $sale->notes . ' | Payment verified manually - Reference: ' . $mpesaReferenceNumber
            ]);

            // Update KCB transactions
            KcbBuniTransaction::where('sale_id', $saleId)
                ->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $mpesaReferenceNumber,
                    'result_description' => 'Payment verified manually'
                ]);

            // Update payment transactions
            PaymentTransaction::where('sale_id', $saleId)
                ->update([
                    'status' => 'completed',
                    'mpesa_receipt' => $mpesaReferenceNumber,
                    'transaction_id' => $mpesaReferenceNumber,
                    'completed_at' => now(),
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment verified and sale confirmed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual payment verification failed: ' . $e->getMessage());
            return ['error' => 'Failed to verify payment. Please try again.'];
        }
    }

    /**
     * Find transaction by KCB invoice
     */
    public function findTransactionByKcbInvoice($invoiceNumber)
    {
        return $this->kcbSalesService->findTransactionByKcbInvoice($invoiceNumber);
    }
}
