-- Migration: Create detail_operations table for detail routing
-- Created: 2024-01-01

-- Detail operations (production steps for printing/manufacturing)
CREATE TABLE IF NOT EXISTS detail_operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    detail_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    time_minutes INT NOT NULL DEFAULT 0,
    labor_rate DECIMAL(15,4) DEFAULT 0 COMMENT 'Cost per hour for this operation',

    -- Resources that can be selected for this operation
    material_id INT UNSIGNED NULL COMMENT 'Material/filament for this operation',
    printer_id BIGINT UNSIGNED NULL COMMENT 'Printer for this operation',
    tool_id INT UNSIGNED NULL COMMENT 'Tool/instrument for this operation',

    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_detail_operations_detail (detail_id),
    INDEX idx_detail_operations_material (material_id),
    INDEX idx_detail_operations_printer (printer_id),
    INDEX idx_detail_operations_tool (tool_id),
    FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES items(id) ON DELETE SET NULL,
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL,
    FOREIGN KEY (tool_id) REFERENCES items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
