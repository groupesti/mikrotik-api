<?php

declare(strict_types=1);

return [
    'default' => env('MIKROTIK_CONNECTION', 'main'),
    'connections' => [
        'main' => [
            'transport' => env('MIKROTIK_TRANSPORT', 'rest'), // rest|api
            'host' => env('MIKROTIK_HOST', '192.168.88.1'),
            'port' => (int) env('MIKROTIK_PORT', 443),
            'username' => env('MIKROTIK_USERNAME', 'admin'),
            'password' => env('MIKROTIK_PASSWORD', ''),
            'tls' => (bool) env('MIKROTIK_TLS', true),
            'verify_tls' => (bool) env('MIKROTIK_VERIFY_TLS', false),
            'timeout_seconds' => (float) env('MIKROTIK_TIMEOUT', 10),
            'retry_max' => (int) env('MIKROTIK_RETRY_MAX', 2),
            'retry_backoff_ms' => (int) env('MIKROTIK_RETRY_BACKOFF_MS', 150),
            'log_enabled' => (bool) env('MIKROTIK_LOG_ENABLED', true),
            'log_channel' => env('MIKROTIK_LOG_CHANNEL', 'stack'),
        ],
    ],
];
