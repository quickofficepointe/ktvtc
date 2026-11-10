<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KCB tills
    |--------------------------------------------------------------------------
    | Every IPN carries the till in requestPayload.primaryData.businessKey.
    | We route on it instead of guessing from the account text.
    */
    'tills' => [
        'school_fees' => env('KCB_TILL_SCHOOL_FEES', '7664166'),
        'cafeteria'   => env('KCB_TILL_CAFETERIA', '8118445'), // card top-ups + POS/pre-order sales
    ],

    // The M-Pesa paybill parents pay into; the account is "<till>#<student number>"
    // (shown in fee reminders, statements and the student portal).
    'paybill' => env('KCB_PAYBILL', '522533'),

    // Finance office number printed on fee reminders and statements
    'finance_phone' => env('FINANCE_PHONE', '0790148509'),

    /*
    |--------------------------------------------------------------------------
    | IPN source verification
    |--------------------------------------------------------------------------
    | IPNs from an IP outside the allow-list are still recorded, but held in
    | the finance queue as "quarantined" instead of crediting anyone.
    | Comma-separated addresses or ranges. KCB has sent from 196.216.222.14 and
    | 196.216.223.14, so the default is the block covering both.
    | Leave empty to accept any IP (not recommended).
    */
    'ipn' => [
        'allowed_ips' => array_filter(array_map('trim', explode(',', env('KCB_IPN_ALLOWED_IPS', '196.216.222.0/23')))),

        // PEM public key from KCB for the "signature" header (base64 RSA-SHA256 of the raw body).
        // Ignored until a valid key is configured.
        'public_key' => env('KCB_IPN_PUBLIC_KEY'),

        // When true, IPNs without a valid signature are quarantined.
        'require_signature' => (bool) env('KCB_IPN_REQUIRE_SIGNATURE', false),
    ],

];
