-- External Order Statuses Migration
-- Store order statuses from external systems (WooCommerce, etc.) with mapping to internal statuses

CREATE TABLE IF NOT EXISTS external_order_statuses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    integration_type VARCHAR(50) NOT NULL COMMENT 'woocommerce, instagram, etc.',
    external_code VARCHAR(100) NOT NULL COMMENT 'Status code from external system (e.g., wc-processing)',
    external_name VARCHAR(200) NOT NULL COMMENT 'Display name from external system',
    internal_status ENUM('pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded') DEFAULT NULL COMMENT 'Mapped internal status',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Whether to sync orders with this status',
    order_count INT DEFAULT 0 COMMENT 'Number of orders with this status (from last sync)',
    last_synced_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_integration_status (integration_type, external_code),
    INDEX idx_integration_type (integration_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default WooCommerce status mappings
INSERT INTO external_order_statuses (integration_type, external_code, external_name, internal_status, is_active) VALUES
('woocommerce', 'pending', 'Pending payment', 'pending', 1),
('woocommerce', 'processing', 'Processing', 'processing', 1),
('woocommerce', 'on-hold', 'On hold', 'on_hold', 1),
('woocommerce', 'completed', 'Completed', 'completed', 1),
('woocommerce', 'cancelled', 'Cancelled', 'cancelled', 1),
('woocommerce', 'refunded', 'Refunded', 'refunded', 1),
('woocommerce', 'failed', 'Failed', 'cancelled', 0)
ON DUPLICATE KEY UPDATE external_name = VALUES(external_name);
