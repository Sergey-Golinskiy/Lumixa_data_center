-- Sales Orders Permissions

INSERT IGNORE INTO permissions (code, name, module, description) VALUES
('sales.orders.view', 'View sales orders', 'sales', 'View customer orders from all sources'),
('sales.orders.create', 'Create sales orders', 'sales', 'Create manual sales orders'),
('sales.orders.edit', 'Edit sales orders', 'sales', 'Edit and update sales orders'),
('sales.orders.delete', 'Delete sales orders', 'sales', 'Delete sales orders'),
('sales.integrations.manage', 'Manage integrations', 'sales', 'Manage WooCommerce and other integrations');

-- Grant permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin'
AND p.code IN (
    'sales.orders.view',
    'sales.orders.create',
    'sales.orders.edit',
    'sales.orders.delete',
    'sales.integrations.manage'
);
