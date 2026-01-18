-- =====================================================
-- Lumixa LMS - Catalog detail permissions
-- =====================================================

INSERT INTO permissions (code, name, module) VALUES
('catalog.details.view', 'View Details', 'catalog'),
('catalog.details.create', 'Create Details', 'catalog'),
('catalog.details.edit', 'Edit Details', 'catalog'),
('catalog.details.delete', 'Delete Details', 'catalog');
