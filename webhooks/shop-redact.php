<?php

declare(strict_types=1);

$request = shopifyRequest();

$result = shopifyApp()->verifyWebhookReq($request);

if (!$result->ok) {
    sendShopifyResult($result);
}

$shop = $result->shop;

$pdo = db();

$stmt = $pdo->prepare('DELETE FROM shops WHERE shop = :shop');

$stmt->execute([
    'shop' => $shop,
]);

http_response_code(200);

echo 'OK';
