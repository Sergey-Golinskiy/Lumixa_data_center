-- =====================================================
-- Lumixa LMS - Detail routing (technology)
-- =====================================================

CREATE TABLE IF NOT EXISTS detail_routing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    detail_id INT UNSIGNED NOT NULL,
    version VARCHAR(20) DEFAULT '1.0',
    name VARCHAR(200),
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    effective_date DATE,
    notes TEXT,
    image_path VARCHAR(255) NULL,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_detail (detail_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detail_routing_operations (
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
    FOREIGN KEY (routing_id) REFERENCES detail_routing(id) ON DELETE CASCADE,
    INDEX idx_routing (routing_id),
    INDEX idx_operation (operation_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
