-- =====================================================
-- Lumixa LMS - Product Collection Permissions
-- =====================================================

INSERT IGNORE INTO permissions (name, description, module, created_at) VALUES
('admin.product_collections.view', 'View product collections', 'admin', NOW()),
('admin.product_collections.create', 'Create product collections', 'admin', NOW()),
('admin.product_collections.edit', 'Edit product collections', 'admin', NOW()),
('admin.product_collections.delete', 'Delete product collections', 'admin', NOW());

-- Grant all collection permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'admin'
  AND p.name IN (
    'admin.product_collections.view',
    'admin.product_collections.create',
    'admin.product_collections.edit',
    'admin.product_collections.delete'
  );
