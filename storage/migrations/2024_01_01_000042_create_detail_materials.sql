-- =====================================================
-- Lumixa LMS - Create detail_materials table for multi-material support
-- =====================================================

CREATE TABLE IF NOT EXISTS detail_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    detail_id INT UNSIGNED NOT NULL,
    material_item_id INT UNSIGNED NOT NULL,
    material_qty_grams DECIMAL(10,2) NOT NULL DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_detail_materials_detail (detail_id),
    FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing single-material data into the new table
INSERT IGNORE INTO detail_materials (detail_id, material_item_id, material_qty_grams, sort_order)
SELECT id, material_item_id, material_qty_grams, 0
FROM details
WHERE material_item_id IS NOT NULL AND material_item_id > 0;
