-- Order Statuses Migration
-- Customizable order statuses for the system

CREATE TABLE IF NOT EXISTS order_statuses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Status code (e.g., pending, processing)',
    name VARCHAR(100) NOT NULL COMMENT 'Display name',
    color VARCHAR(7) DEFAULT '#6b7280' COMMENT 'Color in HEX format for badges',
    description TEXT DEFAULT NULL COMMENT 'Description of the status',
    sort_order INT DEFAULT 0 COMMENT 'Order of display',
    is_default TINYINT(1) DEFAULT 0 COMMENT 'Default status for new orders',
    is_final TINYINT(1) DEFAULT 0 COMMENT 'Is this a final status (completed, cancelled)',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Whether this status can be used',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_code (code),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default order statuses
INSERT INTO order_statuses (code, name, color, description, sort_order, is_default, is_final, is_active) VALUES
('pending', 'Pending', '#f59e0b', 'Order received, awaiting processing', 10, 1, 0, 1),
('processing', 'Processing', '#3b82f6', 'Order is being processed', 20, 0, 0, 1),
('on_hold', 'On Hold', '#8b5cf6', 'Order is on hold', 30, 0, 0, 1),
('shipped', 'Shipped', '#06b6d4', 'Order has been shipped', 40, 0, 0, 1),
('delivered', 'Delivered', '#10b981', 'Order has been delivered', 50, 0, 0, 1),
('completed', 'Completed', '#22c55e', 'Order completed successfully', 60, 0, 1, 1),
('cancelled', 'Cancelled', '#ef4444', 'Order has been cancelled', 70, 0, 1, 1),
('refunded', 'Refunded', '#f97316', 'Order has been refunded', 80, 0, 1, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Add permissions for order statuses management
INSERT INTO permissions (code, name, module) VALUES
('admin.order_statuses.view', 'View Order Statuses', 'admin'),
('admin.order_statuses.create', 'Create Order Status', 'admin'),
('admin.order_statuses.edit', 'Edit Order Status', 'admin'),
('admin.order_statuses.delete', 'Delete Order Status', 'admin')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grant permissions to admin role
INSERT INTO role_permissions (role_id, permission_id)
SELECT
    (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1),
    id
FROM permissions
WHERE code IN ('admin.order_statuses.view', 'admin.order_statuses.create', 'admin.order_statuses.edit', 'admin.order_statuses.delete')
ON DUPLICATE KEY UPDATE role_id = role_id;
