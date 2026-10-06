<?php

declare(strict_types=1);

$auth = authenticateAppHome();

$shop = $auth['shop'];
$accessToken = $auth['access_token'];
$shopId = getShopId($shop);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['ok' => false, 'error' => 'Method Not Allowed.'], 405);
}

$code = $_GET['code'] ?? '';

$qr = getQRCodeByCode($shopId, $code);

if (!$qr) {
    sendJson(['ok' => false, 'error' => 'QR code not found.'], 404);
}

$title = trim((string) ($_POST['title'] ?? ''));
$productGid = trim((string) ($_POST['product_gid'] ?? ''));
$variantGid = trim((string) ($_POST['variant_gid'] ?? ''));
$destinationType = (string) ($_POST['destination_type'] ?? 'product');

if ($title === '') {
    sendJson(['ok' => false, 'error' => 'Title is required.'], 422);
}

if ($productGid === '') {
    sendJson(['ok' => false, 'error' => 'Product is required.'], 422);
}

if (!in_array($destinationType, ['product', 'cart'], true)) {
    sendJson(['ok' => false, 'error' => 'Invalid destination type.'], 422);
}

if ($destinationType === 'cart' && $variantGid === '') {
    sendJson(['ok' => false, 'error' => 'Select a product variant for a cart QR code.'], 422);
}

$product = getProduct(
    $shop,
    $accessToken['token'],
    $productGid,
    $auth['verify_result']->invalidTokenResponse
);

if (!$product) {
    sendJson(['ok' => false, 'error' => 'Product not found.'], 404);
}

$variant = null;

if ($variantGid !== '') {
    foreach ($product['variants']['nodes'] as $candidate) {
        if ($candidate['id'] === $variantGid) {
            $variant = $candidate;
            break;
        }
    }

    if (!$variant) {
        sendJson(['ok' => false, 'error' => 'Variant does not belong to this product.'], 422);
    }
}

updateQRCode(
    code: $code,
    shopId: $shopId,
    title: $title,
    productGid: $product['id'],
    productTitle: $product['title'],
    variantGid: $variant['id'] ?? null,
    variantTitle: $variant['title'] ?? $variant['displayName'] ?? null,
    destinationType: $destinationType
);

sendJson([
    'ok' => true,
    'redirect' => appHomeUrl('/qrcodes/' . rawurlencode($code), ['shop' => $shop]),
]);

