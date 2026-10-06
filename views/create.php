<?php

ob_start();

?>

<div class="card">
    <h1>Create QR Code</h1>
    <form method="post" action="/qrcodes/create" data-app-form>
        <div class="error" data-form-error role="alert"></div>
        <p>
            <label>
                QR Code Title
            </label>
        </p>
        <p>
            <input
                type="text"
                name="title"
                required
                style="width:100%;padding:10px;"
                placeholder="Red Snowboard QR"
            >
        </p>
        <p>
            <label>
                Product
            </label>
        </p>
        <p>
            <select
                name="product_gid"
                id="product"
                required
                style="width:100%;padding:10px;"
            >
                <option value="">
                    Select product
                </option>
                <?php foreach ($products as $product): ?>
                    <option
                        value="<?= e($product['id']) ?>"
                        data-variants="<?= e(
                            json_encode(
                                $product['variants']['nodes']
                            )
                        ) ?>"
                    >
                        <?= e($product['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>
                Variant
            </label>
        </p>
        <p>
            <select
                name="variant_gid"
                id="variant"
                style="width:100%;padding:10px;"
            >
                <option value="">
                    Default product URL
                </option>
            </select>
        </p>
        <p>
            <label>
                Destination
            </label>
        </p>
        <p>
            <select
                name="destination_type"
                style="width:100%;padding:10px;"
            >
                <option value="product">
                    Product page
                </option>
                <option value="cart">
                    Add to cart
                </option>
            </select>
        </p>
        <p>
            <button type="submit">
                Create QR Code
            </button>
            <a href="<?= e(appHomeUrl('/')) ?>">
                Cancel
            </a>
        </p>
    </form>
</div>

<script>

const productSelect =
    document.getElementById('product');

const variantSelect =
    document.getElementById('variant');

productSelect.addEventListener(
    'change',
    function () {

        variantSelect.innerHTML =
            '<option value="">Default product URL</option>';

        if (!this.value) {
            return;
        }

        const option =
            this.options[this.selectedIndex];

        const variants =
            JSON.parse(
                option.dataset.variants || '[]'
            );

        variants.forEach(function (variant) {

            const item =
                document.createElement('option');

            item.value = variant.id;

            item.textContent =
                variant.title ||
                variant.displayName;

            variantSelect.appendChild(item);

        });

    }
);

</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
