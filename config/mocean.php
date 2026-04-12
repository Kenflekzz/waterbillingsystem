<?php

return [
    'token'  => env('MOCEAN_API_TOKEN'),
    'sender' => env('MOCEAN_SENDER', 'VERIFYAPI'),
    'url'    => env('MOCEAN_URL', 'https://rest.moceanapi.com/rest/2/sms'),
];