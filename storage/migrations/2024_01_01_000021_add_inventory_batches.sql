-- =====================================================
-- Lumixa LMS - Inventory batches and costing
-- =====================================================

CREATE TABLE IF NOT EXISTS inventory_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    batch_code VARCHAR(50) NOT NULL,
    received_date DATE NOT NULL,
    supplier_id INT UNSIGNED NULL,
    source_type ENUM('receipt', 'issue', 'adjustment', 'stocktake', 'production', 'order', 'manual') DEFAULT 'receipt',
    source_id INT UNSIGNED NULL,
    qty_received DECIMAL(15,4) NOT NULL DEFAULT 0,
    qty_available DECIMAL(15,4) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(15,4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_item_id (item_id),
    INDEX idx_batch_code (batch_code),
    INDEX idx_received_date (received_date),
    INDEX idx_qty_available (qty_available),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES partners(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_batch_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    document_id INT UNSIGNED NULL,
    document_line_id INT UNSIGNED NULL,
    movement_type ENUM('in', 'out', 'adjust') NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_cost DECIMAL(15,4) NOT NULL DEFAULT 0,
    balance_before DECIMAL(15,4) NOT NULL DEFAULT 0,
    balance_after DECIMAL(15,4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_id (batch_id),
    INDEX idx_item_id (item_id),
    INDEX idx_document_id (document_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL,
    FOREIGN KEY (document_line_id) REFERENCES document_lines(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_issue_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id INT UNSIGNED NOT NULL,
    document_line_id INT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_cost DECIMAL(15,4) NOT NULL DEFAULT 0,
    total_cost DECIMAL(15,4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document_id (document_id),
    INDEX idx_batch_id (batch_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (document_line_id) REFERENCES document_lines(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE documents
    ADD COLUMN costing_method VARCHAR(20) NULL AFTER notes,
    ADD COLUMN issue_source_type VARCHAR(30) NULL AFTER costing_method,
    ADD COLUMN issue_source_id INT UNSIGNED NULL AFTER issue_source_type;
