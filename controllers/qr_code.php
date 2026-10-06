<?php

declare(strict_types=1);

use chillerlan\QRCode\QRCode;

$request = shopifyRequest();

$result = shopifyApp()->verifyAppProxyReq($request);

if (!$result->ok) {
    sendShopifyResult($result);
}

$shop = $result->shop;
$product_id = $_GET['product_id'] ?? null;
$variant_id = $_GET['variant_id'] ?? null;

if (!$product_id) {
    sendJson(['ok' => false, 'error' => 'Product not found.'], 404);
}

$productGid = "gid://shopify/Product/" . $product_id;
$variantGid = $variant_id ? "gid://shopify/ProductVariant/" . $variant_id : null;

$pdo = db();

$stmt = $pdo->prepare('SELECT q.*, s.id as shop_id FROM qr_codes q INNER JOIN shops s ON s.id = q.shop_id WHERE s.shop = :shop AND product_gid = :product_gid AND variant_gid = :variant_gid AND q.active = 1 LIMIT 1');

$stmt->execute([
    'shop' => $shop,
    'product_gid' => $productGid,
    'variant_gid' => $variantGid
]);

$qr = $stmt->fetch();

if ($qr) {
    $scanUrl = 'https://' . $shop . '.myshopify.com/apps/qr-code/' . $qr['code'];

    $qrImage = (new QRCode())->render($scanUrl);

    $message = e($qrImage);
} else {
    $message = '';
}

sendJson(['ok' => true, 'message' => $message]);
