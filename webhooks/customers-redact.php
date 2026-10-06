<?php

declare(strict_types=1);

$request = shopifyRequest();

$result = shopifyApp()->verifyWebhookReq(
    $request
);

if (!$result->ok) {
    sendShopifyResult($result);
}

/*
 * This app does not currently store customer PII.
 */

http_response_code(200);

echo 'OK';
