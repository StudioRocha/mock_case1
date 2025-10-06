<?php

return [
    'public_key' => env('STRIPE_PUBLIC_KEY'),
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'konbini_expiry_hours' => env('STRIPE_KONBINI_EXPIRY_HOURS', 24), // コンビニ支払期限（時間）

// 1 = 1時間後
// 6 = 6時間後
// 24 = 24時間後（デフォルト）
// 72 = 3日後
// 168 = 7日後
];
