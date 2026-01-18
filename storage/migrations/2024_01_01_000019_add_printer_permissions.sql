-- =====================================================
-- Lumixa LMS - Printer permissions
-- =====================================================

INSERT INTO permissions (code, name, module) VALUES
('admin.printers.view', 'View Printers', 'admin'),
('admin.printers.create', 'Create Printers', 'admin'),
('admin.printers.edit', 'Edit Printers', 'admin'),
('admin.printers.delete', 'Delete Printers', 'admin');
