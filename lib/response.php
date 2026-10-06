<?php

declare(strict_types=1);

function applyShopifyResponseHeaders(mixed $response): void
{
    foreach ((array) ($response->headers ?? []) as $name => $value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                header($name . ': ' . $item, false);
            }
        } else {
            header($name . ': ' . $value);
        }
    }
}

function sendShopifyResult(mixed $result): never
{
    $response = $result->response;

    http_response_code($response->status);
    applyShopifyResponseHeaders($response);

    echo $response->body;
    exit;
}

function sendJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
