<?php

/*
 * Staff phone numbers that get SMS alerts, by department. Comma-separated; override in .env.
 */
$list = fn (string $key, string $default) => array_values(array_filter(array_map('trim', explode(',', env($key, $default)))));

return [
    // Cafeteria till sales and online/pre-orders
    'cafeteria' => $list('SMS_ALERT_CAFETERIA', '0117190478'),

    // School fee payments as they arrive, and any payment that could not be matched
    'fees' => $list('SMS_ALERT_FEES', '0792248340'),

    // Online applications and application-fee payments
    'applications' => $list('SMS_ALERT_APPLICATIONS', '0118946430'),

    // Everything else (e.g. events)
    'admin' => $list('SMS_ALERT_ADMIN', '0708112014,0792248340'),
];
