-- =====================================================
-- Lumixa LMS - Detail Configurations
-- =====================================================

-- Detail configurations (variants of the same detail)
CREATE TABLE IF NOT EXISTS detail_configurations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    detail_id INT UNSIGNED NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    material_id INT UNSIGNED NULL,
    material_color VARCHAR(100),
    image_path VARCHAR(255),
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES item_option_values(id) ON DELETE SET NULL,
    INDEX idx_detail_id (detail_id),
    INDEX idx_sku (sku),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update BOM lines to reference configuration instead of detail
ALTER TABLE bom_lines
    ADD COLUMN detail_configuration_id INT UNSIGNED NULL AFTER item_id,
    ADD CONSTRAINT fk_bom_lines_detail_config
        FOREIGN KEY (detail_configuration_id) REFERENCES detail_configurations(id)
        ON DELETE RESTRICT,
    ADD INDEX idx_detail_configuration_id (detail_configuration_id);
