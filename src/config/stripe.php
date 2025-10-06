<?php

return [
    'public_key' => env('STRIPE_PUBLIC_KEY'),
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    
    
    // 環境別設定
    'environment' => env('STRIPE_ENVIRONMENT', 'test'), // test or live
    'webhook_enabled' => env('STRIPE_WEBHOOK_ENABLED', false), // Webhook有効化フラグ
];
