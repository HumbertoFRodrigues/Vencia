<?php

/*
 * How plan invoices are paid. "manual" (default): the customer pays by
 * M-Pesa/e-Mola/transfer and the super-admin confirms it in /admin.
 * "mpesa": the customer also gets "Pagar já com M-Pesa" (C2B: a PIN prompt
 * on their phone) and the plan turns on by itself; manual stays as backup.
 * The M-Pesa keys come from the Vodacom developer portal and live only here
 * (.env), never in the database.
 */
return [
    'gateway' => env('PAGAMENTOS_GATEWAY', 'manual'),

    'mpesa' => [
        'api_key' => env('MPESA_API_KEY'),
        'public_key' => env('MPESA_PUBLIC_KEY'),
        'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE'),
        'sandbox' => (bool) env('MPESA_SANDBOX', true),
        // The customer has up to ~2 minutes to type the PIN.
        'timeout' => (int) env('MPESA_TIMEOUT', 150),
    ],
];
