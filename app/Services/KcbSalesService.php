<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Models\KcbBuniTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KcbSalesService extends KcbService
{
    /**
     * Initiate MPesa payment for a sale
     */
    public function initiateSalePayment(Sale $sale, $phoneNumber, $amount = null)
    {
        try {
            Log::info('Initiating KCB sale payment', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'phone_number' => $phoneNumber
            ]);

            $amount = $amount ?? $sale->total_amount;

            // Generate KCB invoice number (using the same format as events)
            $invoiceNumber = $this->generateKcbInvoiceNumber($sale);

            // Validate and format phone number
            $formattedPhone = $this->validatePhoneNumber($phoneNumber);
            if (!$formattedPhone) {
                return ['error' => 'Invalid phone number format. Please use format: 0712 345 678'];
            }

            $paymentData = [
                'phone_number' => $formattedPhone,
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'description' => 'Payment for Sale #' . $sale->invoice_number,
            ];

            // Use the specific sales payment initiation method
            $result = $this->initiateSalesPayment($paymentData);

            if (isset($result['error'])) {
                Log::error('KCB sale payment initiation failed', [
                    'sale_id' => $sale->id,
                    'error' => $result['error']
                ]);
                return $result;
            }

            DB::beginTransaction();

            // Update sale with KCB payment info
            $sale->update([
                'checkout_request_id' => $result['checkout_request_id'] ?? null,
                'merchant_request_id' => $result['merchant_request_id'] ?? null,
                'payment_method' => 'mpesa',
                'payment_status' => 'pending',
                'payment_requested_at' => now(),
                'kcb_response' => json_encode($result),
                'kcb_invoice_number' => $invoiceNumber, // Store the KCB invoice number
            ]);

            // Create KCB transaction record
            KcbBuniTransaction::create([
                'sale_id' => $sale->id,
                'merchant_request_id' => $result['merchant_request_id'],
                'checkout_request_id' => $result['checkout_request_id'],
                'phone_number' => $formattedPhone,
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'transaction_type' => 'sale_payment',
                'status' => 'initiated',
                'request_data' => json_encode([
                    'sale_id' => $sale->id,
                    'sale_invoice' => $sale->invoice_number,
                    'kcb_invoice' => $invoiceNumber,
                    'shop_id' => $sale->shop_id,
                    'business_section_id' => $sale->business_section_id,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone,
                    'total_items' => $sale->total_items,
                    'total_amount' => $sale->total_amount
                ]),
            ]);

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'sale_id' => $sale->id,
                'transaction_number' => $this->generateTransactionNumber($sale),
                'payment_method' => 'mpesa',
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'checkout_request_id' => $result['checkout_request_id'] ?? null,
                'merchant_request_id' => $result['merchant_request_id'] ?? null,
                'kcb_invoice_number' => $invoiceNumber,
                'status' => 'pending',
                'recorded_by' => auth()->id() ?? $sale->created_by,
            ]);

            DB::commit();

            Log::info('KCB sale payment initiated successfully', [
                'sale_id' => $sale->id,
                'checkout_request_id' => $result['checkout_request_id'],
                'transaction_id' => $paymentTransaction->id,
                'kcb_invoice' => $invoiceNumber
            ]);

            return [
                'success' => true,
                'merchant_request_id' => $result['merchant_request_id'],
                'checkout_request_id' => $result['checkout_request_id'],
                'kcb_invoice_number' => $invoiceNumber,
                'payment_transaction_id' => $paymentTransaction->id,
                'message' => 'Payment request sent. Please check your phone to complete payment.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KCB sale payment initiation exception: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'exception' => $e
            ]);
            return ['error' => 'Failed to initiate payment. Please try again.'];
        }
    }

    /**
     * Generate KCB invoice number for sales (matches event format)
     */
    public function generateKcbInvoiceNumber(Sale $sale)
    {
        // Format: 7664166-SALE-{saleId}-{shopId}-{timestamp}
        // Matches your event format: 7664166-EVT-{eventId}-{applicationId}-{timestamp}
        return '7664166-SALE-' . $sale->id . '-' . ($sale->shop_id ?? '0') . '-' . time();
    }

    /**
     * Generate transaction number
     */
    private function generateTransactionNumber(Sale $sale)
    {
        return 'TXN-' . now()->format('Ymd') . '-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Initiate sales payment specifically
     */
    public function initiateSalesPayment(array $data)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Payment service authentication failed'];
        }

        $stkPushUrl = $this->baseUrl . '/mm/api/request/1.0.0/stkpush';

        try {
            $amountString = number_format($data['amount'], 0, '', '');

            // Build callback URL for sales payments
            $callbackUrl = rtrim($this->callbackBaseUrl, '/') . route('sales.payment.callback', [], false);

            $payload = [
                "phoneNumber" => $data['phone_number'],
                "amount" => $amountString,
                "invoiceNumber" => $data['invoice_number'], // This is the KCB invoice number
                "sharedShortCode" => true,
                "orgShortCode" => "",
                "orgPassKey" => "",
                "callbackUrl" => $callbackUrl,
                "transactionDescription" => substr($data['description'] ?? 'Sale Payment', 0, 30)
            ];

            Log::info('KCB Buni Sales STK Push Request:', $payload);

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->withOptions([
                    'verify' => false, // Disable SSL for development
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->post($stkPushUrl, $payload);

            $responseBody = $response->json();
            Log::info('KCB Buni Sales STK Push Response', $responseBody);

            if (isset($responseBody['fault'])) {
                $errorMessage = $responseBody['fault']['message'] ?? 'Payment request failed';
                Log::error('KCB Buni Sales STK Push Fault:', $responseBody);
                return ['error' => $errorMessage];
            }

            $statusCode = $responseBody['header']['statusCode'] ?? '1';
            if ($response->failed() || $statusCode !== '0') {
                Log::error('KCB Buni Sales STK Push Failed', [
                    'http_status' => $response->status(),
                    'response_body' => $responseBody,
                    'request_payload' => $payload,
                ]);
                return [
                    'error' => $responseBody['header']['statusDescription']
                        ?? $responseBody['response']['ResponseDescription']
                        ?? 'Failed to initiate payment'
                ];
            }

            return [
                'success' => true,
                'merchant_request_id' => $responseBody['response']['MerchantRequestID'],
                'checkout_request_id' => $responseBody['response']['CheckoutRequestID'] ?? null,
                'response_code' => $responseBody['response']['ResponseCode'] ?? null,
                'response_description' => $responseBody['response']['ResponseDescription'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('KCB Buni Sales STK Push Exception: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return ['error' => 'An unexpected error occurred. Please try again.'];
        }
    }

    /**
     * Handle sales payment callback
     */
    public function handleSalePaymentCallback($callbackData)
    {
        Log::info('=== KCB SALE PAYMENT CALLBACK ===', $callbackData);

        $stkCallback = $callbackData['Body']['stkCallback'] ?? null;

        if (!$stkCallback) {
            Log::error('Invalid KCB Buni callback format for sale', $callbackData);
            return false;
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        if (!$checkoutRequestId || $resultCode === null) {
            Log::warning('Missing required callback fields in sale callback', $stkCallback);
            return false;
        }

        // Find transaction by checkout_request_id
        $transaction = KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->with('sale')
            ->first();

        if (!$transaction) {
            Log::error('Transaction not found for sale callback', ['checkout_request_id' => $checkoutRequestId]);
            return false;
        }

        // Update transaction status
        $updateData = [
            'result_code' => $resultCode,
            'result_description' => $stkCallback['ResultDesc'] ?? '',
            'callback_data' => json_encode($callbackData),
        ];

        DB::beginTransaction();

        try {
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

                // Update sale
                $sale = $transaction->sale;
                if ($sale) {
                    $sale->update([
                        'payment_status' => 'paid',
                        'mpesa_receipt' => $metadata['MpesaReceiptNumber'] ?? null,
                        'transaction_id' => $metadata['MpesaReceiptNumber'] ?? null,
                        'payment_confirmed_at' => now(),
                        'kcb_response' => json_encode($callbackData),
                    ]);

                    // Update payment transaction
                    $paymentTransaction = PaymentTransaction::where('sale_id', $sale->id)
                        ->where('checkout_request_id', $checkoutRequestId)
                        ->first();

                    if ($paymentTransaction) {
                        $paymentTransaction->update([
                            'status' => 'completed',
                            'mpesa_receipt' => $metadata['MpesaReceiptNumber'] ?? null,
                            'transaction_id' => $metadata['MpesaReceiptNumber'] ?? null,
                            'phone_number' => $metadata['PhoneNumber'] ?? $paymentTransaction->phone_number,
                            'amount' => isset($metadata['Amount']) ? ($metadata['Amount'] / 100) : $paymentTransaction->amount,
                            'completed_at' => now(),
                            'kcb_response' => json_encode($callbackData),
                        ]);
                    }

                    // Update inventory for non-production items
                    $this->updateInventoryAfterSale($sale);

                    Log::info('Sale payment completed successfully', [
                        'sale_id' => $sale->id,
                        'sale_invoice' => $sale->invoice_number,
                        'kcb_invoice' => $transaction->invoice_number,
                        'receipt' => $updateData['mpesa_receipt_number']
                    ]);
                }

            } else {
                // Payment failed
                $updateData['status'] = 'failed';

                // Update sale
                $sale = $transaction->sale;
                if ($sale) {
                    $sale->update([
                        'payment_status' => 'failed',
                        'kcb_response' => json_encode($callbackData),
                    ]);

                    // Update payment transaction
                    $paymentTransaction = PaymentTransaction::where('sale_id', $sale->id)
                        ->where('checkout_request_id', $checkoutRequestId)
                        ->first();

                    if ($paymentTransaction) {
                        $paymentTransaction->update([
                            'status' => 'failed',
                            'kcb_response' => json_encode($callbackData),
                        ]);
                    }
                }

                Log::warning('Sale payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $resultCode,
                    'result_description' => $stkCallback['ResultDesc'] ?? 'Unknown error',
                    'kcb_invoice' => $transaction->invoice_number
                ]);
            }

            $transaction->update($updateData);
            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing sale payment callback: ' . $e->getMessage(), [
                'checkout_request_id' => $checkoutRequestId,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Check sales payment status
     */
   /**
 * Check sales payment status - SIMPLIFIED VERSION
 */
public function checkSalePaymentStatus($checkoutRequestId)
{
    Log::info('Checking sale payment status', ['checkout_request_id' => $checkoutRequestId]);

    try {
        $transaction = \App\Models\KcbBuniTransaction::where('checkout_request_id', $checkoutRequestId)
            ->with('sale')
            ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => 'Transaction not found'
            ];
        }

        // FOR DEBUGGING - Log what we found
        Log::info('Transaction found', [
            'id' => $transaction->id,
            'status' => $transaction->status,
            'receipt' => $transaction->mpesa_receipt_number,
            'updated_at' => $transaction->updated_at
        ]);

        // Return in the exact format frontend expects
        $response = [
            'status' => $transaction->status, // 'completed', 'failed', 'initiated'
            'result_code' => $transaction->result_code,
            'result_description' => $transaction->result_description,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'kcb_invoice_number' => $transaction->invoice_number,
            'sale_status' => $transaction->sale ? $transaction->sale->payment_status : null,
            'sale_id' => $transaction->sale ? $transaction->sale->id : null,
            'sale_invoice' => $transaction->sale ? $transaction->sale->invoice_number : null,
            'message' => $this->getStatusMessage($transaction->status, $transaction->result_description)
        ];

        // Add success flag based on status
        if ($transaction->status === 'completed') {
            $response['success'] = true;
        } else if ($transaction->status === 'failed') {
            $response['success'] = false;
        } else {
            $response['success'] = true; // Still polling
        }

        return $response;

    } catch (\Exception $e) {
        Log::error('Error checking sale payment status: ' . $e->getMessage());
        return [
            'success' => false,
            'status' => 'error',
            'message' => 'Error checking payment status'
        ];
    }
}

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
     * Update inventory after successful sale
     */
    private function updateInventoryAfterSale(Sale $sale)
    {
        try {
            foreach ($sale->items as $item) {
                $product = $item->product;

                // Only deduct inventory for trackable items (gift shop, not cafeteria food)
                if ($product && $product->track_inventory && !$item->is_production_item) {
                    // Create inventory movement
                    \App\Models\InventoryMovement::create([
                        'movement_number' => 'SALE-' . $sale->invoice_number,
                        'product_id' => $product->id,
                        'shop_id' => $sale->shop_id,
                        'movement_type' => 'sale',
                        'quantity' => $item->quantity,
                        'unit' => $product->unit,
                        'unit_cost' => $product->cost_price,
                        'total_cost' => $product->cost_price * $item->quantity,
                        'previous_stock' => $product->current_stock,
                        'new_stock' => $product->current_stock - $item->quantity,
                        'reference_number' => $sale->invoice_number,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'recorded_by' => $sale->cashier_id ?? $sale->created_by,
                        'notes' => 'Auto-deducted from sale #' . $sale->invoice_number,
                    ]);

                    // Update product stock
                    $product->decrement('current_stock', $item->quantity);

                    Log::info('Inventory updated for sale item', [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity_sold' => $item->quantity,
                        'new_stock' => $product->current_stock - $item->quantity
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating inventory after sale: ' . $e->getMessage(), [
                'sale_id' => $sale->id
            ]);
        }
    }

    /**
     * Format transaction date from M-Pesa format
     */
    private function formatTransactionDate($transactionDate)
    {
        if (strlen($transactionDate) === 14) {
            try {
                return \Carbon\Carbon::createFromFormat('YmdHis', $transactionDate);
            } catch (\Exception $e) {
                Log::warning('Failed to parse transaction date: ' . $transactionDate);
                return now();
            }
        }
        return now();
    }

    /**
     * Retry failed payment for a sale
     */
    public function retrySalePayment($saleId, $phoneNumber = null)
    {
        try {
            $sale = Sale::find($saleId);

            if (!$sale) {
                return ['error' => 'Sale not found'];
            }

            if (!in_array($sale->payment_status, ['failed', 'pending'])) {
                return ['error' => 'Cannot retry payment for this sale status'];
            }

            // Use provided phone number or the original one
            $phoneToUse = $phoneNumber ?: $sale->customer_phone;

            if (!$phoneToUse) {
                return ['error' => 'Phone number is required for payment'];
            }

            $paymentResult = $this->initiateSalePayment($sale, $phoneToUse);

            if (isset($paymentResult['error'])) {
                return $paymentResult;
            }

            return [
                'success' => true,
                'message' => 'Payment retry initiated successfully',
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'kcb_invoice_number' => $paymentResult['kcb_invoice_number'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Sale payment retry failed: ' . $e->getMessage());
            return ['error' => 'Failed to retry payment. Please try again.'];
        }
    }

    /**
     * Find transaction by KCB invoice number
     */
    public function findTransactionByKcbInvoice($invoiceNumber)
    {
        return KcbBuniTransaction::where('invoice_number', $invoiceNumber)
            ->with('sale')
            ->first();
    }
}
