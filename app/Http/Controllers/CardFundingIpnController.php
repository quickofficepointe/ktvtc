<?php
// app/Http/Controllers/CardFundingIpnController.php

namespace App\Http\Controllers;

use App\Models\KcbIpn;
use App\Services\CardFundingIpnReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CardFundingIpnController extends Controller
{
    protected $cardFundingIpnService;

    public function __construct(CardFundingIpnReconciliationService $cardFundingIpnService)
    {
        $this->cardFundingIpnService = $cardFundingIpnService;
    }

    /**
     * Handle KCB Till IPN for card funding (Till 7722609)
     */
    public function handlePaymentNotification(Request $request)
    {
        Log::info('Card Funding IPN Received', $request->all());

        try {
            // Create IPN record
            $ipn = $this->createIpnRecord($request);

            // Reconcile the payment
            $result = $this->cardFundingIpnService->reconcile($ipn);

            Log::info('Card Funding IPN Processed', ['result' => $result]);

            return response()->json([
                'header' => [
                    'messageID' => $request->input('header.messageID'),
                    'statusCode' => '0',
                    'statusMessage' => 'Notification received'
                ],
                'responsePayload' => [
                    'transactionInfo' => [
                        'transactionId' => $ipn->transaction_id
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Card Funding IPN Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'header' => [
                    'messageID' => $request->input('header.messageID'),
                    'statusCode' => '1',
                    'statusMessage' => 'Internal server error'
                ]
            ], 500);
        }
    }

    /**
     * Create IPN record from KCB request
     */
    private function createIpnRecord(Request $request)
    {
        $notificationData = $request->input('requestPayload.additionalData.notificationData', []);
        $headerData = $request->input('header', []);

        // Extract invoice number from businessKey
        $invoiceNumber = $notificationData['businessKey'] ?? null;
        $tillNumber = null;

        if ($invoiceNumber) {
            // Extract till number from invoice (e.g., 7722609#T123 -> 7722609)
            if (preg_match('/^(\d+)#/', $invoiceNumber, $matches)) {
                $tillNumber = $matches[1];
            }
        }

        return KcbIpn::create([
            'transaction_id' => $notificationData['transactionID'] ?? null,
            'invoice_number' => $invoiceNumber,
            'transaction_amount' => $notificationData['transactionAmt'] ?? null,
            'transaction_date' => $notificationData['transactionDate'] ?? null,
            'debit_msisdn' => $notificationData['debitMSISDN'] ?? null,
            'first_name' => $notificationData['firstName'] ?? null,
            'middle_name' => $notificationData['middleName'] ?? null,
            'last_name' => $notificationData['lastName'] ?? null,
            'till_number' => $tillNumber,
            'currency' => $notificationData['currency'] ?? 'KES',
            'narration' => $notificationData['narration'] ?? null,
            'transaction_type' => $notificationData['transactionType'] ?? null,
            'balance' => $notificationData['balance'] ?? null,
            'message_id' => $headerData['messageID'] ?? null,
            'originator_conversation_id' => $headerData['originatorConversationID'] ?? null,
            'channel_code' => $headerData['channelCode'] ?? null,
            'timestamp' => $headerData['timeStamp'] ?? null,
            'raw_data' => json_encode($request->all())
        ]);
    }

    /**
     * Check IPN status (Optional)
     */
    public function checkStatus($transactionId)
    {
        $ipn = KcbIpn::where('transaction_id', $transactionId)->first();

        if (!$ipn) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ipn
        ]);
    }
}
