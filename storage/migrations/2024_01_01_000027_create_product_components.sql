-- =====================================================
-- Lumixa LMS - Product Composition (BOM)
-- =====================================================

-- Product composition table - links products to details and components
CREATE TABLE IF NOT EXISTS product_components (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    component_type ENUM('detail', 'item') NOT NULL COMMENT 'detail = from details table, item = from items table (purchased)',
    detail_id INT UNSIGNED NULL COMMENT 'Reference to details table when component_type = detail',
    item_id INT UNSIGNED NULL COMMENT 'Reference to items table when component_type = item',
    quantity DECIMAL(15,4) NOT NULL DEFAULT 1,
    unit_cost DECIMAL(15,4) DEFAULT 0 COMMENT 'Calculated or overridden unit cost',
    cost_override TINYINT(1) DEFAULT 0 COMMENT 'If true, unit_cost is manually set',
    sort_order INT DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_product_id (product_id),
    INDEX idx_detail_id (detail_id),
    INDEX idx_item_id (item_id),
    INDEX idx_component_type (component_type),

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to products table for cost tracking
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'production_cost');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN production_cost DECIMAL(15,4) DEFAULT 0 COMMENT ''Calculated production cost''',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'assembly_cost');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN assembly_cost DECIMAL(15,4) DEFAULT 0 COMMENT ''Assembly labor cost''',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add permissions
INSERT IGNORE INTO permissions (name, description, module, created_at) VALUES
('catalog.products.composition', 'Manage product composition', 'catalog', NOW());

-- Grant to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name = 'catalog.products.composition';
