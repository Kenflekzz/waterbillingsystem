<?php

return [

    /* mode switch */
    'mode' => env('PAYMONGO_MODE', 'test'),   // test | live

    /* keys */
    'test_secret' => env('PAYMONGO_SECRET_KEY'),
    'test_public' => env('PAYMONGO_PUBLIC_KEY'),

    'live_secret' => env('PAYMONGO_LIVE_SECRET_KEY'),
    'live_public' => env('PAYMONGO_LIVE_PUBLIC_KEY'),

    /* same url for both environments */
    'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com'),

    /* webhook */
    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
];