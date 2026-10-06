<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="shopify-api-key" content="<?= e($config['shopify_api_key']) ?>">

        <!-- Current App Bridge: the API key must be supplied to the script. -->
        <script
            src="https://cdn.shopify.com/shopifycloud/app-bridge.js"
            data-api-key="<?= e($config['shopify_api_key']) ?>"
        ></script>
        <script src="https://cdn.shopify.com/shopifycloud/polaris.js"></script>

        <title>QR Code Manager</title>

        <style>
            body { margin: 0; font-family: system-ui, sans-serif; background: #f6f6f7; }
            main { max-width: 1100px; margin: 0 auto; padding: 32px; }
            .card { background: white; border: 1px solid #ddd; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { text-align: left; padding: 14px; border-bottom: 1px solid #eee; }
            img.qr { width: 180px; height: 180px; }
            .actions { display: flex; gap: 10px; align-items: center; }
            .error { color: #b42318; margin: 12px 0; }
            button[disabled] { opacity: .6; cursor: wait; }
            button {
                padding: 10px;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <main>
            <?= $content ?? '' ?>
        </main>

        <script>
        (() => {
            // App Bridge's current script automatically attaches an ID token to
            // fetch/XHR requests. Mutation forms therefore use fetch instead of
            // ordinary document POST navigation, which would not carry the token.
            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('form[data-app-form]');
                if (!form) return;

                event.preventDefault();

                if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                    return;
                }

                const button = form.querySelector('button[type="submit"]');
                const originalText = button ? button.textContent : '';
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Working…';
                }

                const errorBox = form.querySelector('[data-form-error]');
                if (errorBox) errorBox.textContent = '';

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: new FormData(form),
                        headers: { 'Accept': 'application/json' },
                    });

                    const contentType = response.headers.get('content-type') || '';
                    let payload;

                    if (contentType.includes('application/json')) {
                        payload = await response.json();
                    } else {
                        const text = await response.text();
                        throw new Error(text || `Request failed (${response.status})`);
                    }

                    if (!response.ok || payload.ok === false) {
                        throw new Error(payload.error || `Request failed (${response.status})`);
                    }

                    if (payload.redirect) {
                        // Document navigation intentionally goes through the
                        // normal App Home verification/patch flow when needed.
                        window.location.assign(payload.redirect);
                        return;
                    }
                } catch (error) {
                    if (errorBox) {
                        errorBox.textContent = error instanceof Error ? error.message : 'Request failed.';
                    } else {
                        window.alert(error instanceof Error ? error.message : 'Request failed.');
                    }
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                }
            });
        })();
        </script>
    </body>
</html>
