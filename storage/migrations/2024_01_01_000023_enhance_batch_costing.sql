-- =====================================================
-- Lumixa LMS - Enhanced Batch Costing
-- =====================================================

-- Add costing method to items (default for each item)
ALTER TABLE items
    ADD COLUMN costing_method ENUM('FIFO', 'LIFO', 'WEIGHTED_AVG', 'MANUAL') DEFAULT 'FIFO' AFTER reorder_point,
    ADD COLUMN allow_method_override TINYINT(1) DEFAULT 1 AFTER costing_method;

-- Add status and expiration to inventory batches
ALTER TABLE inventory_batches
    ADD COLUMN status ENUM('active', 'depleted', 'expired', 'quarantine') DEFAULT 'active' AFTER unit_cost,
    ADD COLUMN expiry_date DATE NULL AFTER status,
    ADD COLUMN notes TEXT AFTER expiry_date,
    ADD INDEX idx_status (status),
    ADD INDEX idx_expiry_date (expiry_date);

-- Add allocation method tracking to issue allocations
ALTER TABLE inventory_issue_allocations
    ADD COLUMN allocation_method ENUM('FIFO', 'LIFO', 'WEIGHTED_AVG', 'MANUAL') NOT NULL DEFAULT 'FIFO' AFTER document_line_id,
    ADD COLUMN allocated_by INT UNSIGNED NULL AFTER allocation_method,
    ADD FOREIGN KEY (allocated_by) REFERENCES users(id) ON DELETE SET NULL;

-- Create table for tracking batch reservations
CREATE TABLE IF NOT EXISTS inventory_batch_reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    reserved_for_type ENUM('print_job', 'production_order', 'document', 'manual') NOT NULL,
    reserved_for_id INT UNSIGNED NULL,
    document_id INT UNSIGNED NULL,
    status ENUM('active', 'fulfilled', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fulfilled_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_item_id (item_id),
    INDEX idx_status (status),
    INDEX idx_reserved_for (reserved_for_type, reserved_for_id),
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create view for batch availability
CREATE OR REPLACE VIEW v_batch_availability AS
SELECT
    b.id as batch_id,
    b.item_id,
    b.batch_code,
    b.received_date,
    b.supplier_id,
    b.unit_cost,
    b.qty_received,
    b.qty_available,
    b.status,
    b.expiry_date,
    COALESCE(SUM(br.quantity), 0) as qty_reserved,
    (b.qty_available - COALESCE(SUM(br.quantity), 0)) as qty_unreserved,
    i.sku,
    i.name as item_name,
    i.costing_method as default_costing_method,
    p.name as supplier_name
FROM inventory_batches b
JOIN items i ON b.item_id = i.id
LEFT JOIN partners p ON b.supplier_id = p.id
LEFT JOIN inventory_batch_reservations br ON b.id = br.batch_id AND br.status = 'active'
GROUP BY b.id, b.item_id, b.batch_code, b.received_date, b.supplier_id, b.unit_cost,
         b.qty_received, b.qty_available, b.status, b.expiry_date, i.sku, i.name,
         i.costing_method, p.name;

-- Update settings with new costing options
UPDATE settings SET value = 'FIFO' WHERE `key` = 'inventory_issue_method' AND value NOT IN ('FIFO', 'LIFO', 'WEIGHTED_AVG', 'MANUAL');

-- Add additional settings
INSERT INTO settings (`key`, value, type, description) VALUES
('inventory_track_expiry', '0', 'boolean', 'Track and enforce batch expiry dates'),
('inventory_block_expired', '1', 'boolean', 'Block allocation of expired batches'),
('inventory_warn_expiry_days', '30', 'integer', 'Warn when batch expires within X days')
ON DUPLICATE KEY UPDATE `key` = `key`;
