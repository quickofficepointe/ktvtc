<?php

namespace App\Http\Controllers;

use App\Models\KcbIpn;
use App\Services\Payments\KcbIpnRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Receives KCB Buni IPNs for both tills (school fees and cafeteria/cards).
 *
 * Routes:
 *   POST /api/kcb/ipn/payment-notification
 *   POST /api/kcb/card/ipn
 *
 * KCB delivers both tills to whichever URL is registered, so both URLs land here
 * and KcbIpnRouter decides by till number. We acknowledge every IPN we manage to
 * record (statusCode 0), including unallocated ones; otherwise KCB keeps retrying
 * a payment we have already stored.
 */
class KcbIpnController extends Controller
{
    public function __construct(private KcbIpnRouter $router) {}

    public function handle(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();
        $header  = $payload['header'] ?? [];

        // Headers are logged (names only, plus whether a signature came) so we can confirm what KCB sends.
        Log::info('KCB IPN received', [
            'ip'                => $request->ip(),
            'header_names'      => array_keys($request->headers->all()),
            'signature_present' => $request->hasHeader('signature'),
            'payload'           => $payload,
        ]);

        try {
            $ipn = $this->router->receive(
                $payload,
                $request->getContent(),
                $request->ip(),
                $request->header('signature'),
            );
        } catch (\Throwable $e) {
            Log::error('KCB IPN could not be recorded: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'payload' => $payload,
            ]);

            // Not stored: let KCB retry
            return $this->respond($header, '1', 'Temporary error, please retry', null, 500);
        }

        Log::info('KCB IPN routed', [
            'ipn_id' => $ipn->id,
            'till'   => $ipn->till_number,
            'status' => $ipn->status,
            'reason' => $ipn->status_reason,
        ]);

        return $this->respond($header, '0', $this->messageFor($ipn), $ipn->transaction_id);
    }

    private function messageFor(KcbIpn $ipn): string
    {
        return match ($ipn->status) {
            KcbIpn::STATUS_CREDITED, KcbIpn::STATUS_LINKED, KcbIpn::STATUS_DUPLICATE => 'Notification processed',
            default => 'Notification received',
        };
    }

    private function respond(array $header, string $code, string $message, ?string $transactionId, int $http = 200)
    {
        return response()->json([
            'header' => [
                'messageID'                => $header['messageID'] ?? (string) Str::uuid(),
                'originatorConversationID' => $header['originatorConversationID'] ?? '',
                'statusCode'               => $code,
                'statusMessage'            => $message,
            ],
            'responsePayload' => [
                'transactionInfo' => [
                    'transactionId' => $transactionId,
                ],
            ],
        ], $http);
    }
}
