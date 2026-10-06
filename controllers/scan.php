<?php

declare(strict_types=1);

$request = shopifyRequest();

$result = shopifyApp()->verifyAppProxyReq($request);

if (!$result->ok) {
    sendShopifyResult($result);
}

$shop = $result->shop;
$code = $_GET['code'] ?? '';

if (!preg_match('/^[a-f0-9]{24}$/', $code)) {
    http_response_code(404);
    exit('QR code not found');
}

$pdo = db();

$stmt = $pdo->prepare('SELECT q.*, s.id AS shop_id FROM qr_codes q INNER JOIN shops s ON s.id = q.shop_id WHERE s.shop = :shop AND q.code = :code AND q.active = 1 LIMIT 1');

$stmt->execute([
    'shop' => $shop,
    'code' => $code
]);

$qr = $stmt->fetch();

if (!$qr) {
    http_response_code(404);
    exit('QR code not found.');
}

$accessToken = getUsableOfflineAccessToken($shop);

if (!$accessToken) {
    http_response_code(401);
    exit('App authorization is unavailable.');
}

$product = getProduct($shop, $accessToken['token'], $qr['product_gid']);

if (!$product) {
    http_response_code(404);
    exit('Product is no longer available.');
}

$destination = $product['onlineStoreUrl'];

if (!$destination) {
    $destination = 'https://' . $shop . '.myshopify.com/products/' . $product['handle'];
}

if ($qr['variant_gid']) {
    $variant = null;

    foreach ($product['variants']['nodes'] as $candidate) {

        if ($candidate['id'] === $qr['variant_gid']) {
            $variant = $candidate;
            break;
        }
    }

    if (!$variant) {
        http_response_code(404);
        exit('Product variant is no longer available.');
    }

    if ($qr['destination_type'] === 'cart') {
        $destination = 'https://' . $shop . '.myshopify.com/cart/' . $variant['legacyResourceId'] . ':1';
    } else {
        $separator = str_contains($destination, '?') ? '&' : '?';
        $destination .= $separator . 'variant=' . rawurlencode($variant['legacyResourceId']);
    }
}

incrementQRCodeScan((int) $qr['shop_id'], $qr['code']);

header('Location: ' . $destination, true, 302);

exit;
