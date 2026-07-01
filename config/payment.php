<?php

return [

    // paymob
    'paymob' => [
        'base_url'      => env('PAYMOB_BASE_URL'),
        'api_key'       => env('PAYMOB_API_KEY'),
        'secret_key'    => env('PAYMOB_SECRET_KEY'),
        'public_key'    => env('PAYMOB_PUBLIC_KEY'),
        'hmac_secret'   => env('PAYMOB_SECRET_KEY'),
        'integrations'  => [
            (int) env('PAYMOB_CARD_INTEGRATION_ID'),
        ]
    ]
];
