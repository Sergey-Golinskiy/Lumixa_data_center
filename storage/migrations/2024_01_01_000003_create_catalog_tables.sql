-- Catalog Tables Migration
-- Products, Variants, BOM, Routing

-- Products (finished goods catalog)
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    base_price DECIMAL(15,4) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product Variants (e.g., size/color combinations)
CREATE TABLE IF NOT EXISTS variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    attributes JSON,
    base_price DECIMAL(15,4) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_product (product_id),
    INDEX idx_sku (sku),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bill of Materials (BOM)
-- Note: created_by must be INT UNSIGNED to match users.id type for FK compatibility
CREATE TABLE IF NOT EXISTS bom (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_id INT NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    name VARCHAR(200),
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    effective_date DATE,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_variant (variant_id),
    INDEX idx_status (status),
    INDEX idx_effective (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- BOM Lines (materials required)
CREATE TABLE IF NOT EXISTS bom_lines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bom_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity DECIMAL(15,6) NOT NULL,
    unit_cost DECIMAL(15,4) DEFAULT 0,
    waste_percent DECIMAL(5,2) DEFAULT 0,
    notes VARCHAR(500),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (bom_id) REFERENCES bom(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    INDEX idx_bom (bom_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Routing (production operations sequence)
-- Note: created_by must be INT UNSIGNED to match users.id type for FK compatibility
CREATE TABLE IF NOT EXISTS routing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_id INT NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    name VARCHAR(200),
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    effective_date DATE,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_variant (variant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Routing Operations (steps in production)
CREATE TABLE IF NOT EXISTS routing_operations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    routing_id INT NOT NULL,
    operation_number INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    work_center VARCHAR(100),
    setup_time_minutes INT DEFAULT 0,
    run_time_minutes INT DEFAULT 0,
    labor_cost DECIMAL(15,4) DEFAULT 0,
    overhead_cost DECIMAL(15,4) DEFAULT 0,
    instructions TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (routing_id) REFERENCES routing(id) ON DELETE CASCADE,
    INDEX idx_routing (routing_id),
    INDEX idx_operation (operation_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Work Centers (production areas/machines)
CREATE TABLE IF NOT EXISTS work_centers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    hourly_rate DECIMAL(15,4) DEFAULT 0,
    overhead_rate DECIMAL(15,4) DEFAULT 0,
    capacity_per_hour DECIMAL(15,4) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Variant Costing (calculated costs)
CREATE TABLE IF NOT EXISTS variant_costs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_id INT NOT NULL,
    bom_id INT,
    routing_id INT,
    material_cost DECIMAL(15,4) DEFAULT 0,
    labor_cost DECIMAL(15,4) DEFAULT 0,
    overhead_cost DECIMAL(15,4) DEFAULT 0,
    total_cost DECIMAL(15,4) DEFAULT 0,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE CASCADE,
    FOREIGN KEY (bom_id) REFERENCES bom(id) ON DELETE SET NULL,
    FOREIGN KEY (routing_id) REFERENCES routing(id) ON DELETE SET NULL,
    INDEX idx_variant (variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
