<?php

declare(strict_types=1);

use chillerlan\QRCode\QRCode;

function generateQRCode(string $url): string
{
    return (new QRCode())->render($url);
}

function createQRCode(int $shopId, string $title, string $productGid, string $productTitle, ?string $variantGid, ?string $variantTitle, string $destinationType): int
{
    $pdo = db();

    $code = bin2hex(random_bytes(12));

    $stmt = $pdo->prepare(
        'INSERT INTO qr_codes (
            shop_id,
            code,
            title,
            product_gid,
            product_title,
            variant_gid,
            variant_title,
            destination_type
        ) VALUES (
            :shop_id,
            :code,
            :title,
            :product_gid,
            :product_title,
            :variant_gid,
            :variant_title,
            :destination_type
        )'
    );

    $stmt->execute([
        'shop_id' => $shopId,
        'code' => $code,
        'title' => $title,
        'product_gid' => $productGid,
        'product_title' => $productTitle,
        'variant_gid' => $variantGid,
        'variant_title' => $variantTitle,
        'destination_type' => $destinationType
    ]);

    return (int) $pdo->lastInsertId();
}

function updateQRCode(string $code, int $shopId, string $title, string $productGid, string $productTitle, ?string $variantGid, ?string $variantTitle, string $destinationType): void
{
    $pdo = db();

    $stmt = $pdo->prepare(
        'UPDATE qr_codes SET
            title = :title,
            product_gid = :product_gid,
            product_title = :product_title,
            variant_gid = :variant_gid,
            variant_title = :variant_title,
            destination_type = :destination_type
            WHERE
            shop_id = :shop_id AND
            code = :code
        '
    );

    $stmt->execute([
        'title' => $title,
        'product_gid' => $productGid,
        'product_title' => $productTitle,
        'variant_gid' => $variantGid,
        'variant_title' => $variantTitle,
        'destination_type' => $destinationType,
        'shop_id' => $shopId,
        'code' => $code
    ]);
}

function getShopId(string $shop): int
{
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id FROM shops WHERE shop = :shop LIMIT 1');

    $stmt->execute([
        'shop' => $shop,
    ]);

    $id = $stmt->fetchColumn();

    if (!$id) {
        throw new RuntimeException('Shop installation not found.');
    }

    return (int) $id;
}

function getQRCodes(int $shopId): array
{
    $pdo = db();

    $stmt = $pdo->prepare('SELECT * FROM qr_codes WHERE shop_id = :shop_id ORDER BY id DESC');

    $stmt->execute([
        'shop_id' => $shopId,
    ]);

    return $stmt->fetchAll();
}

function getQRCodeByCode(int $shopId, string $code): ?array {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT * FROM qr_codes WHERE shop_id = :shop_id AND code = :code LIMIT 1');

    $stmt->execute([
        'shop_id' => $shopId,
        'code' => $code,
    ]);

    return $stmt->fetch() ?: null;
}

function getQRCodeCodeById(int $shopId, int $id): string {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT code FROM qr_codes WHERE id = :id AND shop_id = :shop_id LIMIT 1');

    $stmt->execute([
        'id' => $id,
        'shop_id' => $shopId,
    ]);

    $code = $stmt->fetchColumn();

    if (!$code) {
        throw new RuntimeException('QR code not found.');
    }

    return $code;
}

function incrementQRCodeScan(int $shopId, string $code): void {
    $pdo = db();

    $stmt = $pdo->prepare('UPDATE qr_codes SET scans = scans + 1 WHERE shop_id = :shop_id AND code = :code AND active = 1');

    $stmt->execute([
        'shop_id' => $shopId,
        'code' => $code,
    ]);
}

function deleteQRCode(int $shopId, int $id): void {
    $pdo = db();

    $stmt = $pdo->prepare('DELETE FROM qr_codes WHERE id = :id AND shop_id = :shop_id');

    $stmt->execute([
        'id' => $id,
        'shop_id' => $shopId,
    ]);
}
