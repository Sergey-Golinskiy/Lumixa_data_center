-- =====================================================
-- Lumixa LMS - Catalog Details
-- =====================================================

CREATE TABLE IF NOT EXISTS details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    detail_type ENUM('purchased', 'printed') NOT NULL,
    material_item_id INT UNSIGNED NULL,
    material_qty_grams DECIMAL(15,4) DEFAULT 0,
    print_time_minutes INT DEFAULT 0,
    print_parameters TEXT,
    image_path VARCHAR(255) NULL,
    model_path VARCHAR(255) NULL,
    item_id INT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku),
    INDEX idx_detail_type (detail_type),
    INDEX idx_material_item (material_item_id),
    INDEX idx_item_id (item_id),
    FOREIGN KEY (material_item_id) REFERENCES items(id) ON DELETE SET NULL,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
