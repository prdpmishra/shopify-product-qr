<?php

declare(strict_types=1);

function shopifyGraphQL(string $query, string $shop, string $accessToken, array $variables = [], $invalidTokenResponse = null): array
{
    $config = require __DIR__ . '/../config/config.php';

    $result = shopifyApp()->adminGraphQLRequest(
        $query,
        shop: $shop,
        accessToken: $accessToken,
        apiVersion: $config['shopify_api_version'],
        variables: $variables,
        invalidTokenResponse: $invalidTokenResponse
    );

    if (!$result->ok) {
        if ($result->log->code === 'unauthorized') {
            deleteAccessToken($shop, 'offline');
        }

        sendShopifyResult($result);
    }

    return $result->data;
}

function deleteAccessToken(string $shop, string $accessMode = 'offline'): void {
    $pdo = db();

    $stmt = $pdo->prepare(
        'DELETE FROM shops
         WHERE shop = :shop
           AND access_mode = :access_mode'
    );

    $stmt->execute([
        'shop' => $shop,
        'access_mode' => $accessMode,
    ]);
}

function getProducts(string $shop, string $accessToken, $invalidTokenResponse = null): array
{
    $query = <<<'GRAPHQL'
    query GetProducts($first: Int!) {
        products(first: $first, sortKey: TITLE) {
            nodes {
                id
                title
                handle
                onlineStoreUrl
                variants(first: 50) {
                    nodes {
                        id
                        title
                        displayName
                    }
                }
            }
            pageInfo {
                hasNextPage
                endCursor
            }
        }
    }
    GRAPHQL;

    $data = shopifyGraphQL(
        $query,
        $shop,
        $accessToken,
        [
            'first' => 50,
        ],
        $invalidTokenResponse
    );

    return $data['products']['nodes'] ?? [];
}

function getProduct(string $shop, string $accessToken, string $productGid, $invalidTokenResponse = null): ?array
{
    $query = <<<'GRAPHQL'
    query getProduct($id: ID!) {
        product(id: $id) {
            id
            title
            handle
            onlineStoreUrl
            variants(first: 100) {
                nodes {
                    id
                    title
                    displayName
                    legacyResourceId
                }
            }
        }
    }
    GRAPHQL;

    $data = shopifyGraphQL(
        $query,
        $shop,
        $accessToken,
        [
            'id' => $productGid,
        ],
        $invalidTokenResponse
    );

    return $data['product'] ?? null;
}
