<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
'sms' => [
        'api_key' => env('SMS_API_KEY'),
        'partner_id' => env('SMS_PARTNER_ID'),
        'sender_id' => env('SMS_SENDER_ID'),
        'base_url' => env('SMS_BASE_URL', 'https://quicksms.advantasms.com/api/services'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
'kcb_buni' => [
    'base_url' => env('KCB_BUNI_BASE_URL', 'https://api.buni.kcbgroup.com'),
    'consumer_key' => env('KCB_BUNI_CONSUMER_KEY'),
    'consumer_secret' => env('KCB_BUNI_CONSUMER_SECRET'),
    'callback_base_url' => env('KCB_BUNI_CALLBACK_BASE_URL', env('APP_URL')),
],
];
