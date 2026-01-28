-- Multi-material support for detail operations (routing)
CREATE TABLE IF NOT EXISTS detail_operation_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    operation_id INT NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_op_materials_operation (operation_id),
    FOREIGN KEY (operation_id) REFERENCES detail_operations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing single-material data to the new table
INSERT IGNORE INTO detail_operation_materials (operation_id, material_id, sort_order)
SELECT id, material_id, 0
FROM detail_operations
WHERE material_id IS NOT NULL AND material_id > 0;
