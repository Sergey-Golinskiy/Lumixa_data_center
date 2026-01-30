-- Sales Orders Permissions

INSERT IGNORE INTO permissions (name, description, category) VALUES
('sales.orders.view', 'View sales orders', 'Sales'),
('sales.orders.create', 'Create sales orders', 'Sales'),
('sales.orders.edit', 'Edit sales orders', 'Sales'),
('sales.orders.delete', 'Delete sales orders', 'Sales'),
('sales.integrations.manage', 'Manage integrations (WooCommerce, etc.)', 'Sales');

-- Grant permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin'
AND p.name IN (
    'sales.orders.view',
    'sales.orders.create',
    'sales.orders.edit',
    'sales.orders.delete',
    'sales.integrations.manage'
);
