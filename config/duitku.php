<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Duitku Payment Gateway
    |--------------------------------------------------------------------------
    | Sandbox credentials dapat diambil dari https://sandbox.duitku.com.
    | Production: DUITKU_SANDBOX=false dan pakai endpoint payment.duitku.com.
    */

    'merchant_code' => env('DUITKU_MERCHANT_CODE', 'DUITKUXXXXXX'),

    'api_key' => env('DUITKU_API_KEY', ''),

    'sandbox' => env('DUITKU_SANDBOX', true),

    'base_url' => env('DUITKU_SANDBOX', true)
        ? 'https://sandbox.duitku.com'
        : 'https://payment.duitku.com',

    'notify_url' => env('DUITKU_NOTIFY_URL', 'https://api.topspeak.app/api/v1/subscriptions/duitku-callback'),

    'return_url' => env('DUITKU_RETURN_URL', 'https://api.topspeak.app/subscription/result'),

];
