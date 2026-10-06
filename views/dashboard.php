<?php

ob_start();

?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>QR Codes</h1>
            <p>Generate and manage product QR codes.</p>
        </div>
        <a href="<?= e(appHomeUrl('/qrcodes/create')) ?>">
            <button type="button">Create QR Code</button>
        </a>
    </div>

    <?php if (empty($qrCodes)): ?>
        <p>You haven't created any QR codes yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Product</th>
                <th>Destination</th>
                <th>Scans</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($qrCodes as $qr): ?>
                <tr>
                    <td><?= e($qr['title']) ?></td>
                    <td>
                        <?= e($qr['product_title']) ?>
                        <?php if ($qr['variant_title']): ?>
                            <br><small><?= e($qr['variant_title']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e($qr['destination_type']) ?></td>
                    <td><?= (int) $qr['scans'] ?></td>
                    <td>
                        <a href="<?= e(appHomeUrl('/qrcodes/' . $qr['code'])) ?>">View</a>
                        &nbsp;&nbsp;<a href="<?= e(appHomeUrl('/qrcodes/' . $qr['code'] . '/edit')) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
