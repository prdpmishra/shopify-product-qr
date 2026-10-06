CREATE TABLE shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop VARCHAR(255) NOT NULL UNIQUE,
    access_mode VARCHAR(20) NOT NULL DEFAULT 'offline',
    access_token TEXT NOT NULL,
    scope TEXT NULL,
    refresh_token TEXT NULL,
    expires_at DATETIME NULL,
    refresh_token_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE qr_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL,
    title VARCHAR(255) NOT NULL,

    product_gid VARCHAR(255) NOT NULL,
    product_title VARCHAR(255) NOT NULL,

    variant_gid VARCHAR(255) NULL,
    variant_title VARCHAR(255) NULL,

    destination_type ENUM('product', 'cart') NOT NULL DEFAULT 'product',

    scans BIGINT UNSIGNED NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_shop_code (shop_id, code),

    CONSTRAINT fk_qr_shop
        FOREIGN KEY (shop_id)
        REFERENCES shops(id)
        ON DELETE CASCADE
);
