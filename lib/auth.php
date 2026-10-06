<?php

declare(strict_types=1);

use Shopify\App\ShopifyApp;

function shopifyApp(): ShopifyApp
{
    static $shopify = null;

    if ($shopify instanceof ShopifyApp) {
        return $shopify;
    }

    $config = require __DIR__ . '/../config/config.php';

    $shopify = new ShopifyApp(
        clientId: $config['shopify_api_key'],
        clientSecret: $config['shopify_api_secret']
    );

    return $shopify;
}

/**
 * Convert the native PHP request into the request shape expected by
 * shopify/shopify-app-php. Do not alter the URL, body, or headers before
 * verification because the SDK verifies the request as received.
 */
function shopifyRequest(): array
{
    $headers = [];

    if (function_exists('getallheaders')) {
        $headers = getallheaders() ?: [];
    }

    // PHP's built-in server and some proxies can expose Authorization only
    // through HTTP_AUTHORIZATION. Preserve it for App Bridge fetch requests.
    if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        if (is_string($authorization) && $authorization !== '') {
            $headers['Authorization'] = $authorization;
        }
    }

    $scheme = 'http';

    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
    ) {
        $scheme = 'https';
    }

    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'headers' => $headers,
        'url' => $scheme . '://' . $host . $uri,
        'body' => file_get_contents('php://input') ?: '',
    ];
}

/**
 * Authenticate every App Home controller through Shopify's current
 * App Bridge + ID-token flow.
 */
function authenticateAppHome(): array
{
    $shopify = shopifyApp();
    $request = shopifyRequest();

    $result = $shopify->verifyAppHomeReq(
        $request,
        appHomePatchIdTokenPath: '/auth/patch-id-token'
    );

    if (!$result->ok) {
        sendShopifyResult($result);
    }

    // Shopify provides these headers for a successfully verified App Home
    // document request. They include the iframe frame-ancestors policy.
    applyShopifyResponseHeaders($result->response);

    $shop = $result->shop;
    $accessToken = getStoredAccessToken($shop, 'offline');

    if ($accessToken) {
        $refreshResult = $shopify->refreshTokenExchangedAccessToken($accessToken);

        if ($refreshResult->ok) {
            if (isset($refreshResult->accessToken)) {
                saveAccessToken($refreshResult->accessToken);
                $accessToken = $refreshResult->accessToken;
            }
        } elseif (in_array($refreshResult->log->code, ['invalid_grant', 'refresh_token_expired'], true)) {
            // The stored refresh token may have been rotated/invalidated.
            // Drop the stale token and exchange the current App Bridge ID token.
            deleteAccessToken($shop, 'offline');
            $accessToken = null;
        } else {
            sendShopifyResult($refreshResult);
        }
    }

    if (!$accessToken) {
        $exchangeResult = $shopify->exchangeUsingTokenExchange(
            accessMode: 'offline',
            idToken: $result->idToken,
            invalidTokenResponse: $result->invalidTokenResponse
        );

        if (!$exchangeResult->ok) {
            sendShopifyResult($exchangeResult);
        }

        saveAccessToken($exchangeResult->accessToken);
        $accessToken = $exchangeResult->accessToken;
    }

    return [
        'shop' => $shop,
        'access_token' => $accessToken,
        'verify_result' => $result,
        'request' => $request,
    ];
}

function saveAccessToken(object $token): void
{
    $pdo = db();

    $sql = "
        INSERT INTO shops (
            shop,
            access_mode,
            access_token,
            scope,
            refresh_token,
            expires_at,
            refresh_token_expires_at
        )
        VALUES (
            :shop,
            :access_mode,
            :access_token,
            :scope,
            :refresh_token,
            :expires_at,
            :refresh_token_expires_at
        )
        ON DUPLICATE KEY UPDATE
            access_mode = VALUES(access_mode),
            access_token = VALUES(access_token),
            scope = VALUES(scope),
            refresh_token = VALUES(refresh_token),
            expires_at = VALUES(expires_at),
            refresh_token_expires_at = VALUES(refresh_token_expires_at),
            updated_at = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'shop' => $token->shop,
        'access_mode' => $token->accessMode,
        'access_token' => $token->token,
        'scope' => $token->scope,
        'refresh_token' => $token->refreshToken,
        'expires_at' => toMysqlDateTime($token->expires),
        'refresh_token_expires_at' => toMysqlDateTime($token->refreshTokenExpires),
    ]);
}

function toMysqlDateTime(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
}

function getUsableOfflineAccessToken(string $shop): ?array
{
    $accessToken = getStoredAccessToken($shop, 'offline');

    if (!$accessToken) {
        return null;
    }

    $shopify = shopifyApp();
    $refreshResult = $shopify->refreshTokenExchangedAccessToken($accessToken);

    if ($refreshResult->ok) {
        if (isset($refreshResult->accessToken)) {
            saveAccessToken($refreshResult->accessToken);
            return [
                'shop' => $refreshResult->accessToken->shop,
                'accessMode' => $refreshResult->accessToken->accessMode,
                'token' => $refreshResult->accessToken->token,
                'scope' => $refreshResult->accessToken->scope,
                'refreshToken' => $refreshResult->accessToken->refreshToken,
                'expires' => $refreshResult->accessToken->expires,
                'refreshTokenExpires' => $refreshResult->accessToken->refreshTokenExpires,
                'user' => $refreshResult->accessToken->user,
            ];
        }

        return $accessToken;
    }

    if (in_array($refreshResult->log->code, ['invalid_grant', 'refresh_token_expired'], true)) {
        deleteAccessToken($shop, 'offline');
    }

    return null;
}

function getStoredAccessToken(string $shop, string $accessMode = 'offline'): ?array
{
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT *
         FROM shops
         WHERE shop = :shop
           AND access_mode = :access_mode
         LIMIT 1'
    );

    $stmt->execute([
        'shop' => $shop,
        'access_mode' => $accessMode,
    ]);

    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return [
        'shop' => $row['shop'],
        'accessMode' => $row['access_mode'],
        'token' => $row['access_token'],
        'scope' => $row['scope'],
        'refreshToken' => $row['refresh_token'],
        'expires' => $row['expires_at'],
        'refreshTokenExpires' => $row['refresh_token_expires_at'],
        'user' => null,
    ];
}
