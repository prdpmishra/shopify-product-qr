<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';

switch (true) {
    case $path === '/':
    case $path === '/app':
        require __DIR__ . '/../controllers/dashboard.php';
        break;

    case $path === '/qrcodes/create':
        require __DIR__ . '/../controllers/create.php';
        break;

    case preg_match(
        '#^/qrcodes/([a-f0-9]{24})\/edit$#',
        $path,
        $matches
    ):
        $_GET['code'] = $matches[1];

        require __DIR__ . '/../controllers/edit.php';
        break;

    case preg_match(
        '#^/qrcodes/([a-f0-9]{24})\/update$#',
        $path,
        $matches
    ):
        $_GET['code'] = $matches[1];

        require __DIR__ . '/../controllers/update.php';
        break;

    case $path === '/qrcodes/delete':
        require __DIR__ . '/../controllers/delete.php';
        break;

    case preg_match(
        '#^/qrcodes/([a-f0-9]{24})$#',
        $path,
        $matches
    ):
        $_GET['code'] = $matches[1];

        require __DIR__ . '/../controllers/show.php';
        break;

    case preg_match(
        '#^/proxy/([a-f0-9]{24})$#',
        $path,
        $matches
    ):
        $_GET['code'] = $matches[1];

        require __DIR__ . '/../controllers/scan.php';
        break;

    case $path === '/proxy/qr_code':
        require __DIR__ . '/../controllers/qr_code.php';

    case $path === '/auth/patch-id-token':
        require __DIR__ . '/../controllers/patch-id-token.php';
        break;

    case $path === '/webhooks/app-uninstalled':
        require __DIR__ . '/../webhooks/app-uninstalled.php';
        break;

    case $path === '/webhooks/customers-data-request':
        require __DIR__ . '/../webhooks/customers-data-request.php';
        break;

    case $path === '/webhooks/customers-redact':
        require __DIR__ . '/../webhooks/customers-redact.php';
        break;

    case $path === '/webhooks/shop-redact':
        require __DIR__ . '/../webhooks/shop-redact.php';
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
