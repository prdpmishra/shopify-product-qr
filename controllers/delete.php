<?php

declare(strict_types=1);

$auth = authenticateAppHome();
$shopId = getShopId($auth['shop']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['ok' => false, 'error' => 'Method Not Allowed.'], 405);
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    sendJson(['ok' => false, 'error' => 'Invalid QR code.'], 400);
}

deleteQRCode($shopId, $id);

sendJson([
    'ok' => true,
    'redirect' => appHomeUrl('/', ['shop' => $auth['shop']]),
]);
