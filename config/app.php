<?php

declare(strict_types=1);

return [
    'name' => (string) env('APP_NAME', 'SecurePanel PHP'),
    'env' => (string) env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
    'timezone' => (string) env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'public_registration' => (bool) env('PUBLIC_REGISTRATION', true),
    'install_key' => (string) env('INSTALL_KEY', ''),
    'session' => [
        'name' => (string) env('SESSION_NAME', 'SECUREPANELSESSID'),
        'idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', 1800),
        'absolute_timeout' => (int) env('SESSION_ABSOLUTE_TIMEOUT', 28800),
    ],
];
