# QR Code Manager — App Bridge / App Home Fix

This version is a plain-PHP embedded Shopify app using `shopify/shopify-app-php` 1.0.x.

## What was fixed

- App Bridge is loaded in the common layout with the required `data-api-key`.
- Every App Home controller goes through `verifyAppHomeReq()` with `/auth/patch-id-token`.
- The successful App Home verification response headers are copied to the rendered response, including the iframe `Content-Security-Policy`.
- `/auth/patch-id-token` uses Shopify's built-in `appHomePatchIdToken()` helper.
- Internal URLs no longer trust or manually parse `shop` from the browser. The verified shop comes from the SDK; links preserve only the Shopify context needed for the patch flow.
- Create/delete POST operations use App Bridge-authenticated `fetch()` instead of ordinary document POST navigation. This prevents a POST from being converted into a patch-token GET and losing its form body.
- The frontend redirects after a successful mutation using a normal document navigation, which can safely enter the SDK's patch-token flow if a fresh ID token is required.
- Stored expiring offline tokens are refreshed. If Shopify reports `invalid_grant` or an expired refresh token, the stale token is removed and the current App Bridge ID token is exchanged again.
- Shopify GraphQL requests continue to pass the SDK-provided `invalidTokenResponse` so App Bridge can retry an invalid-session request.
- API version is aligned to the current stable `2026-10` version.

## Important

The app URL in `shopify.app.toml` is environment-specific. For local development, run Shopify CLI so it can update development URLs/tunnel settings. Do not copy a tunnel URL from one machine into another machine's configuration.

## Run

1. Create the MySQL database using `database/schema.sql`.
2. Create `.env` from your local credentials/tunnel configuration:

```env
SHOPIFY_API_KEY=your_client_id
SHOPIFY_API_SECRET=your_client_secret
SHOPIFY_APP_URL=https://your-current-tunnel-url
SHOPIFY_API_VERSION=2026-10

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_qr
DB_USERNAME=root
DB_PASSWORD=
```

3. Install dependencies if `vendor/` is not present:

```bash
composer install
```

4. Start the app through Shopify CLI:

```bash
shopify app dev
```

5. Open the embedded app from the Shopify admin.

## Expected authentication flow

```text
Shopify Admin
  -> App Bridge
  -> PHP App Home request
  -> verifyAppHomeReq()
  -> token exchange / refresh
  -> GraphQL Admin API
  -> PHP view
```

For ordinary full-page links, the current Shopify PHP package may intentionally use `/auth/patch-id-token` to obtain a fresh ID token. That is normal. Mutation forms use App Bridge-authenticated `fetch()` so the POST request carries an ID token directly.

Do not put ID tokens into URLs.
