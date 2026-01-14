-- Production Tables Migration
-- Orders, Tasks, Print Queue
-- Note: All user FK columns must be INT UNSIGNED to match users.id type

-- Production Orders
CREATE TABLE IF NOT EXISTS production_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    variant_id INT NOT NULL,
    bom_id INT,
    routing_id INT,
    quantity DECIMAL(15,4) NOT NULL,
    completed_quantity DECIMAL(15,4) DEFAULT 0,
    planned_start DATE,
    planned_end DATE,
    actual_start DATETIME,
    actual_end DATETIME,
    status ENUM('draft', 'planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE RESTRICT,
    FOREIGN KEY (bom_id) REFERENCES bom(id) ON DELETE SET NULL,
    FOREIGN KEY (routing_id) REFERENCES routing(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_variant (variant_id),
    INDEX idx_status (status),
    INDEX idx_planned (planned_start, planned_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Production Tasks (operations to be performed)
CREATE TABLE IF NOT EXISTS production_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    routing_operation_id INT,
    operation_number INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    work_center VARCHAR(100),
    planned_quantity DECIMAL(15,4) NOT NULL,
    completed_quantity DECIMAL(15,4) DEFAULT 0,
    setup_time_minutes INT DEFAULT 0,
    run_time_minutes INT DEFAULT 0,
    actual_start DATETIME,
    actual_end DATETIME,
    status ENUM('pending', 'in_progress', 'completed', 'skipped') DEFAULT 'pending',
    assigned_to INT UNSIGNED,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES production_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (routing_operation_id) REFERENCES routing_operations(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Material Consumption (materials used in production)
-- Note: item_id and lot_id must be INT UNSIGNED to match warehouse table IDs
CREATE TABLE IF NOT EXISTS material_consumption (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    task_id INT,
    item_id INT UNSIGNED NOT NULL,
    lot_id INT UNSIGNED,
    planned_quantity DECIMAL(15,6) NOT NULL,
    actual_quantity DECIMAL(15,6) DEFAULT 0,
    unit_cost DECIMAL(15,4) DEFAULT 0,
    consumed_at DATETIME,
    consumed_by INT UNSIGNED,
    notes VARCHAR(500),
    FOREIGN KEY (order_id) REFERENCES production_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES production_tasks(id) ON DELETE SET NULL,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE SET NULL,
    FOREIGN KEY (consumed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Print Queue (3D printing jobs)
CREATE TABLE IF NOT EXISTS print_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_number VARCHAR(50) NOT NULL UNIQUE,
    order_id INT,
    variant_id INT,
    printer VARCHAR(100),
    material VARCHAR(100),
    quantity INT DEFAULT 1,
    estimated_time_minutes INT DEFAULT 0,
    actual_time_minutes INT DEFAULT 0,
    file_path VARCHAR(500),
    status ENUM('queued', 'printing', 'completed', 'failed', 'cancelled') DEFAULT 'queued',
    priority INT DEFAULT 0,
    started_at DATETIME,
    completed_at DATETIME,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES production_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_job_number (job_number),
    INDEX idx_status (status),
    INDEX idx_printer (printer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Printers (available 3D printers)
CREATE TABLE IF NOT EXISTS printers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    type VARCHAR(100),
    status ENUM('available', 'busy', 'maintenance', 'offline') DEFAULT 'available',
    current_job_id INT,
    hourly_rate DECIMAL(15,4) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_job_id) REFERENCES print_queue(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Sequences
INSERT INTO document_sequences (type, prefix, next_number)
VALUES ('production_order', 'PO', 1)
ON DUPLICATE KEY UPDATE type = type;

INSERT INTO document_sequences (type, prefix, next_number)
VALUES ('print_job', 'PJ', 1)
ON DUPLICATE KEY UPDATE type = type;
