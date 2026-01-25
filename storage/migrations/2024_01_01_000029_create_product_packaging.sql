-- =====================================================
-- Lumixa LMS - Product Packaging
-- =====================================================

-- Product packaging table - links products to packaging items from warehouse
CREATE TABLE IF NOT EXISTS product_packaging (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL COMMENT 'Must match products.id type (signed INT)',
    item_id INT UNSIGNED NOT NULL COMMENT 'Reference to items table (packaging type only)',
    quantity DECIMAL(15,4) NOT NULL DEFAULT 1,
    unit_cost DECIMAL(15,4) DEFAULT 0 COMMENT 'Calculated or overridden unit cost',
    cost_override TINYINT(1) DEFAULT 0 COMMENT 'If true, unit_cost is manually set',
    sort_order INT DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_product_id (product_id),
    INDEX idx_item_id (item_id),

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add packaging_cost column to products table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'packaging_cost');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN packaging_cost DECIMAL(15,4) DEFAULT 0 COMMENT ''Total packaging cost''',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add permission for packaging management
INSERT IGNORE INTO permissions (name, description, module, created_at) VALUES
('catalog.products.packaging', 'Manage product packaging', 'catalog', NOW());

-- Grant to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name = 'catalog.products.packaging';
