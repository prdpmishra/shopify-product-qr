<?php

declare(strict_types=1);

$auth = authenticateAppHome();

$shop = $auth['shop'];
$accessToken = $auth['access_token'];

$shopId = getShopId($shop);

$qrCodes = getQRCodes($shopId);

require __DIR__ . '/../views/dashboard.php';
