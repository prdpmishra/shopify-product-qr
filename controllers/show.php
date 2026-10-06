<?php

declare(strict_types=1);

$auth = authenticateAppHome();

$shop = $auth['shop'];

$shopId = getShopId($shop);

$code = $_GET['code'] ?? '';

$qr = getQRCodeByCode($shopId, $code);

if (!$qr) {
    http_response_code(404);
    exit('QR code not found.');
}

$config = require __DIR__ . '/../config/config.php';

$scanUrl = 'https://' . $shop . '.myshopify.com/apps/qr-code/' . $qr['code'];

require __DIR__ . '/../views/show.php';
