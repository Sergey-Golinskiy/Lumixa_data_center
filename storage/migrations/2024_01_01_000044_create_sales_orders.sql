-- Sales Orders Migration
-- Customer orders aggregated from various sources (WooCommerce, Instagram, offline, manual)

-- Sales Orders
CREATE TABLE IF NOT EXISTS sales_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    external_id VARCHAR(100) DEFAULT NULL COMMENT 'External ID from source system (e.g., WooCommerce order ID)',
    source ENUM('woocommerce', 'instagram', 'offline', 'manual') NOT NULL DEFAULT 'manual',
    source_url VARCHAR(500) DEFAULT NULL COMMENT 'Link to order in source system',

    -- Customer info
    customer_name VARCHAR(200) DEFAULT NULL,
    customer_email VARCHAR(200) DEFAULT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,

    -- Shipping address
    shipping_address TEXT DEFAULT NULL,
    shipping_city VARCHAR(100) DEFAULT NULL,
    shipping_country VARCHAR(100) DEFAULT NULL,
    shipping_postal_code VARCHAR(20) DEFAULT NULL,

    -- Billing address (optional, may differ from shipping)
    billing_address TEXT DEFAULT NULL,
    billing_city VARCHAR(100) DEFAULT NULL,
    billing_country VARCHAR(100) DEFAULT NULL,
    billing_postal_code VARCHAR(20) DEFAULT NULL,

    -- Order totals
    subtotal DECIMAL(15,2) DEFAULT 0,
    shipping_cost DECIMAL(15,2) DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'UAH',

    -- Status tracking
    status ENUM('pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'partial', 'refunded', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(100) DEFAULT NULL,

    -- Tracking
    shipping_method VARCHAR(200) DEFAULT NULL,
    tracking_number VARCHAR(100) DEFAULT NULL,
    tracking_url VARCHAR(500) DEFAULT NULL,

    -- Dates
    ordered_at DATETIME DEFAULT NULL COMMENT 'Date order was placed (from source)',
    paid_at DATETIME DEFAULT NULL,
    shipped_at DATETIME DEFAULT NULL,
    delivered_at DATETIME DEFAULT NULL,

    -- Metadata
    notes TEXT DEFAULT NULL,
    internal_notes TEXT DEFAULT NULL COMMENT 'Internal notes not visible to customer',
    meta_data JSON DEFAULT NULL COMMENT 'Additional source-specific data',

    -- System fields
    created_by INT UNSIGNED DEFAULT NULL,
    updated_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    synced_at DATETIME DEFAULT NULL COMMENT 'Last sync from external source',

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_order_number (order_number),
    INDEX idx_external_id (external_id),
    INDEX idx_source (source),
    INDEX idx_status (status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_ordered_at (ordered_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Order Items
CREATE TABLE IF NOT EXISTS sales_order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,

    -- Product reference (can be linked to local catalog or just text)
    product_id INT DEFAULT NULL COMMENT 'Reference to local products table',
    external_product_id VARCHAR(100) DEFAULT NULL COMMENT 'Product ID from source system',

    -- Item details
    sku VARCHAR(100) DEFAULT NULL,
    name VARCHAR(500) NOT NULL,
    variant_info VARCHAR(500) DEFAULT NULL COMMENT 'Color, size, etc.',

    -- Quantities and pricing
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,

    -- Production tracking
    production_order_id INT DEFAULT NULL COMMENT 'Link to production order if created',

    -- Metadata
    meta_data JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (production_order_id) REFERENCES production_orders(id) ON DELETE SET NULL,

    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Integration Settings (for WooCommerce and other sources)
CREATE TABLE IF NOT EXISTS integration_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    integration_type VARCHAR(50) NOT NULL COMMENT 'woocommerce, instagram_api, etc.',
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT DEFAULT NULL,
    is_encrypted TINYINT(1) DEFAULT 0 COMMENT 'Whether value is encrypted (for API keys)',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_integration_setting (integration_type, setting_key),
    INDEX idx_integration_type (integration_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Log (for tracking integration sync history)
CREATE TABLE IF NOT EXISTS integration_sync_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    integration_type VARCHAR(50) NOT NULL,
    sync_type ENUM('full', 'incremental', 'single') NOT NULL DEFAULT 'incremental',
    status ENUM('started', 'completed', 'failed') NOT NULL,
    records_processed INT DEFAULT 0,
    records_created INT DEFAULT 0,
    records_updated INT DEFAULT 0,
    records_failed INT DEFAULT 0,
    error_message TEXT DEFAULT NULL,
    started_at DATETIME NOT NULL,
    completed_at DATETIME DEFAULT NULL,
    triggered_by INT UNSIGNED DEFAULT NULL COMMENT 'User who triggered sync, NULL for automatic',

    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_integration_type (integration_type),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Sequence for sales orders
INSERT INTO document_sequences (type, prefix, next_number)
VALUES ('sales_order', 'SO', 1)
ON DUPLICATE KEY UPDATE type = type;
