<?php

namespace App\Services\Payments;

use Carbon\Carbon;

/**
 * Parsed KCB Buni IPN.
 *
 * Real payload shape:
 *   header: messageID, originatorConversationID, channelCode, timeStamp
 *   requestPayload.primaryData.businessKey             => till number, e.g. "7664166"
 *   requestPayload.additionalData.notificationData:
 *       businessKey  => what the payer typed, e.g. "7664166#NT/950/2026" or just "7664166"
 *       transactionID, transactionAmt, debitMSISDN, firstName, middleName, lastName,
 *       transactionDate ("Aug 10, 2026 7:30:30 AM"), currency, narration, transactionType
 */
class KcbIpnPayload
{
    public function __construct(
        public readonly ?string $tillNumber,
        public readonly ?string $accountRaw,
        public readonly ?string $accountReference,
        public readonly ?string $transactionId,
        public readonly float $amount,
        public readonly ?string $msisdn,
        public readonly ?string $firstName,
        public readonly ?string $middleName,
        public readonly ?string $lastName,
        public readonly ?Carbon $transactionDate,
        public readonly array $header,
        public readonly array $notification,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $payload): self
    {
        $header       = $payload['header'] ?? [];
        $primary      = $payload['requestPayload']['primaryData'] ?? [];
        $notification = $payload['requestPayload']['additionalData']['notificationData'] ?? [];

        $accountRaw = self::clean($notification['businessKey'] ?? null);
        [$tillFromAccount, $reference] = self::splitAccount($accountRaw);

        $till = self::clean($primary['businessKey'] ?? null);
        if (!$till || !ctype_digit($till)) {
            $till = $tillFromAccount;
        }

        return new self(
            tillNumber:       $till,
            accountRaw:       $accountRaw,
            accountReference: $reference,
            transactionId:    self::clean($notification['transactionID'] ?? null),
            amount:           (float) ($notification['transactionAmt'] ?? 0),
            msisdn:           self::clean($notification['debitMSISDN'] ?? null),
            firstName:        self::clean($notification['firstName'] ?? null),
            middleName:       self::clean($notification['middleName'] ?? null),
            lastName:         self::clean($notification['lastName'] ?? null),
            transactionDate:  self::parseDate($notification['transactionDate'] ?? null),
            header:           $header,
            notification:     $notification,
            raw:              $payload,
        );
    }

    /**
     * "7664166#NT/950/2026" => ["7664166", "NT/950/2026"]; "7664166" => ["7664166", null].
     * Payers sometimes use "-" or a space instead of "#".
     */
    private static function splitAccount(?string $account): array
    {
        if (!$account) {
            return [null, null];
        }

        if (preg_match('/^(\d{5,8})\s*[#\-\s]\s*(.+)$/', $account, $m)) {
            return [$m[1], trim($m[2]) ?: null];
        }

        if (ctype_digit($account)) {
            return [$account, null];
        }

        // No till prefix at all: treat the whole thing as the reference
        return [null, $account];
    }

    private static function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(str_replace('\\/', '/', (string) $value));
        return $value === '' ? null : $value;
    }

    private static function parseDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::parse($value, 'Africa/Nairobi');
        } catch (\Throwable) {
            return null;
        }
    }
}
