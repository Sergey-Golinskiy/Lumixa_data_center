-- =====================================================
-- Lumixa LMS - Item option values
-- =====================================================

CREATE TABLE IF NOT EXISTS item_option_values (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_filament TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_name (group_key, name),
    INDEX idx_group_key (group_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default material/plastic types
INSERT INTO item_option_values (group_key, name, is_active, is_filament, created_at) VALUES
('material', 'Filament', 1, 1, NOW()),
('material', 'Metal', 1, 0, NOW()),
('plastic_type', 'PETG', 1, 0, NOW()),
('plastic_type', 'PLA', 1, 0, NOW());
