<?php

use chillerlan\QRCode\QRCode;

$qrImage = (new QRCode())->render($scanUrl);

ob_start();
?>

<div class="card">

    <p>
        <a href="<?= e(appHomeUrl('/')) ?>">
            ← Back to QR Codes
        </a>
    </p>

    <h1>
        <?= e($qr['title']) ?>
    </h1>

    <p>
        Product:
        <strong>
            <?= e($qr['product_title']) ?>
        </strong>
    </p>

    <?php if ($qr['variant_title']): ?>

        <p>
            Variant:
            <?= e($qr['variant_title']) ?>
        </p>

    <?php endif; ?>

    <p>
        Scans:
        <strong>
            <?= (int) $qr['scans'] ?>
        </strong>
    </p>

    <p>
        Destination:
        <?= e($qr['destination_type']) ?>
    </p>

    <p>
        Scan URL:
    </p>

    <p>
        <code>
            <?= e($scanUrl) ?>
        </code>
    </p>

    <p>
        <img
            class="qr"
            src="<?= e($qrImage) ?>"
            alt="QR Code"
        >
    </p>

    <p>

        <a
            href="<?= e($qrImage) ?>"
            download="qr-<?= e($qr['code']) ?>.svg"
        >
            Download QR
        </a>

    </p>

    <hr>

    <form
        method="post"
        action="/qrcodes/delete"
        data-app-form
        data-confirm="Delete this QR code?"
    >
        <div class="error" data-form-error role="alert"></div>

        <input
            type="hidden"
            name="id"
            value="<?= (int) $qr['id'] ?>"
        >
        <button type="submit">
            Delete
        </button>

    </form>

</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
