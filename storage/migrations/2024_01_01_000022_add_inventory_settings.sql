-- =====================================================
-- Lumixa LMS - Inventory settings
-- =====================================================

INSERT INTO settings (`key`, value, type, description) VALUES
('inventory_issue_method', 'FIFO', 'string', 'Default inventory issue costing method'),
('inventory_allow_issue_method_override', '1', 'boolean', 'Allow per-document issue method overrides')
ON DUPLICATE KEY UPDATE `key` = `key`;
