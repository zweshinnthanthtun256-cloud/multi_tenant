<?php

return [
    'plans' => [
        'starter' => ['name' => 'Starter', 'seats' => 5, 'ai_requests' => 50],
        'growth' => ['name' => 'Growth', 'seats' => 20, 'ai_requests' => 250],
        'business' => ['name' => 'Business', 'seats' => 50, 'ai_requests' => 1000],
    ],
    'currency' => env('SAAS_CURRENCY', 'USD'),
    'payment_instructions' => env('BILLING_PAYMENT_INSTRUCTIONS'),
    'support_email' => env('SUPPORT_EMAIL'),
    'openai_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL'),
    'ai_enabled' => env('AI_ENABLED', false),
];
