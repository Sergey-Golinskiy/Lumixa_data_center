-- Migration: Create product_operations and product_operation_components tables
-- Created: 2024-01-01

-- Product operations (assembly steps/routing)
CREATE TABLE IF NOT EXISTS product_operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    time_minutes INT NOT NULL DEFAULT 0,
    labor_rate DECIMAL(15,4) DEFAULT 0 COMMENT 'Cost per hour for this operation',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_product_operations_product (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Components used in each operation (links to product_components)
CREATE TABLE IF NOT EXISTS product_operation_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operation_id INT NOT NULL,
    component_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_operation_component (operation_id, component_id),
    INDEX idx_operation_components_operation (operation_id),
    INDEX idx_operation_components_component (component_id),
    FOREIGN KEY (operation_id) REFERENCES product_operations(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES product_components(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
