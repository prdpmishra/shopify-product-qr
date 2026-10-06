<?php

declare(strict_types=1);

$appEnv = static function (string $name, string $default = ''): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

    return is_string($value) && $value !== '' ? $value : $default;
};

return [
    'shopify_api_key' => $appEnv('SHOPIFY_API_KEY'),
    'shopify_api_secret' => $appEnv('SHOPIFY_API_SECRET'),
    'shopify_api_version' => $appEnv('SHOPIFY_API_VERSION', '2026-10'),

    'app_url' => rtrim($appEnv('SHOPIFY_APP_URL'), '/'),

    'db' => [
        'host' => $appEnv('DB_HOST', '127.0.0.1'),
        'port' => $appEnv('DB_PORT', '3306'),
        'database' => $appEnv('DB_DATABASE', 'shopify_qr'),
        'username' => $appEnv('DB_USERNAME', 'root'),
        'password' => $appEnv('DB_PASSWORD'),
    ],
];
