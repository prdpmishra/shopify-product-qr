<?php

declare(strict_types=1);

$result = shopifyApp()->appHomePatchIdToken(
    shopifyRequest()
);

sendShopifyResult($result);
