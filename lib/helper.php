<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Build an internal App Home URL while preserving the Shopify context needed
 * by the SDK's patch-id-token navigation flow. Never include id_token here.
 */
function appHomeUrl(string $path, array $extra = []): string
{
    if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
        throw new InvalidArgumentException('App Home path must be root-relative.');
    }

    $current = [];
    foreach (['shop', 'host', 'embedded'] as $key) {
        if (isset($_GET[$key]) && is_string($_GET[$key]) && $_GET[$key] !== '') {
            $current[$key] = $_GET[$key];
        }
    }

    foreach ($extra as $key => $value) {
        if ($value !== null && $value !== '') {
            $current[$key] = (string) $value;
        }
    }

    if (!$current) {
        return $path;
    }

    return $path . '?' . http_build_query($current);
}
