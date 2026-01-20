-- =====================================================
-- Lumixa LMS - Product category permissions
-- =====================================================

INSERT INTO permissions (code, name, module) VALUES
('admin.product_categories.view', 'View Product Categories', 'admin'),
('admin.product_categories.create', 'Create Product Categories', 'admin'),
('admin.product_categories.edit', 'Edit Product Categories', 'admin'),
('admin.product_categories.delete', 'Delete Product Categories', 'admin');
