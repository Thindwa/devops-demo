<?php

$originsEnv = trim((string) env('CORS_ALLOWED_ORIGINS', ''));
$allowedOrigins =
    $originsEnv === '*'
        ? ['*']
        : array_values(array_filter(array_map('trim', explode(',', $originsEnv))));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // For local dev we allow Vite defaults; for staging/prod set CORS_ALLOWED_ORIGINS
    // in your environment (Coolify) to your frontend domain(s), comma-separated.
    'allowed_origins' => $allowedOrigins ?: [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

