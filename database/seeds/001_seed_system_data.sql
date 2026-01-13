-- LMS Seed Data v1.0
-- Initial system data: roles, permissions, statuses, types

-- =====================================================
-- Roles
-- =====================================================
INSERT INTO `roles` (`code`, `name`, `description`, `is_system`) VALUES
('admin', 'Administrator', 'Full system access including user management and backups', 1),
('accountant', 'Accountant/Warehouse', 'Warehouse operations, documents, stock management', 1),
('manager', 'Manager', 'Products, BOM, routing, production planning, costing', 1),
('worker', 'Worker', 'Execute tasks, work with print queue', 1),
('viewer', 'Viewer', 'Read-only access', 1);

-- =====================================================
-- Permissions
-- =====================================================
INSERT INTO `permissions` (`code`, `name`, `module`, `description`) VALUES
-- Admin module
('admin.access', 'Access Admin Panel', 'admin', 'Can access the admin panel'),
('admin.users.view', 'View Users', 'admin', 'Can view users list'),
('admin.users.create', 'Create Users', 'admin', 'Can create new users'),
('admin.users.edit', 'Edit Users', 'admin', 'Can edit user details'),
('admin.users.delete', 'Delete Users', 'admin', 'Can delete users'),
('admin.roles.manage', 'Manage Roles', 'admin', 'Can manage roles and permissions'),
('admin.custom_fields.manage', 'Manage Custom Fields', 'admin', 'Can create and edit custom fields'),
('admin.templates.manage', 'Manage UI Templates', 'admin', 'Can configure UI templates'),
('admin.workflows.manage', 'Manage Workflows', 'admin', 'Can configure workflows'),
('admin.audit.view', 'View Audit Log', 'admin', 'Can view audit log'),
('admin.backups.create', 'Create Backups', 'admin', 'Can create system backups'),
('admin.backups.restore', 'Restore Backups', 'admin', 'Can restore from backups'),
('admin.settings.manage', 'Manage Settings', 'admin', 'Can edit system settings'),

-- Warehouse module
('warehouse.items.view', 'View Items', 'warehouse', 'Can view items/SKU'),
('warehouse.items.create', 'Create Items', 'warehouse', 'Can create new items'),
('warehouse.items.edit', 'Edit Items', 'warehouse', 'Can edit item details'),
('warehouse.items.delete', 'Delete Items', 'warehouse', 'Can delete items'),
('warehouse.lots.view', 'View Lots', 'warehouse', 'Can view lots/batches'),
('warehouse.lots.create', 'Create Lots', 'warehouse', 'Can create new lots'),
('warehouse.lots.edit', 'Edit Lots', 'warehouse', 'Can edit lot details'),
('warehouse.stock.view', 'View Stock', 'warehouse', 'Can view stock levels'),
('warehouse.partners.view', 'View Partners', 'warehouse', 'Can view partners'),
('warehouse.partners.create', 'Create Partners', 'warehouse', 'Can create partners'),
('warehouse.partners.edit', 'Edit Partners', 'warehouse', 'Can edit partners'),
('warehouse.documents.view', 'View Documents', 'warehouse', 'Can view warehouse documents'),
('warehouse.documents.create', 'Create Documents', 'warehouse', 'Can create documents'),
('warehouse.documents.edit', 'Edit Documents', 'warehouse', 'Can edit draft documents'),
('warehouse.documents.post', 'Post Documents', 'warehouse', 'Can post documents'),
('warehouse.documents.cancel', 'Cancel Documents', 'warehouse', 'Can cancel posted documents'),

-- Catalog module
('catalog.products.view', 'View Products', 'catalog', 'Can view products'),
('catalog.products.create', 'Create Products', 'catalog', 'Can create products'),
('catalog.products.edit', 'Edit Products', 'catalog', 'Can edit products'),
('catalog.variants.view', 'View Variants', 'catalog', 'Can view variants'),
('catalog.variants.create', 'Create Variants', 'catalog', 'Can create variants'),
('catalog.variants.edit', 'Edit Variants', 'catalog', 'Can edit variants'),
('catalog.bom.view', 'View BOM', 'catalog', 'Can view bills of materials'),
('catalog.bom.create', 'Create BOM', 'catalog', 'Can create BOM revisions'),
('catalog.bom.edit', 'Edit BOM', 'catalog', 'Can edit draft BOM'),
('catalog.bom.activate', 'Activate BOM', 'catalog', 'Can activate BOM revision'),
('catalog.routing.view', 'View Routing', 'catalog', 'Can view routing'),
('catalog.routing.create', 'Create Routing', 'catalog', 'Can create routing revisions'),
('catalog.routing.edit', 'Edit Routing', 'catalog', 'Can edit draft routing'),
('catalog.routing.activate', 'Activate Routing', 'catalog', 'Can activate routing revision'),

-- Production module
('production.orders.view', 'View Production Orders', 'production', 'Can view production orders'),
('production.orders.create', 'Create Production Orders', 'production', 'Can create production orders'),
('production.orders.edit', 'Edit Production Orders', 'production', 'Can edit production orders'),
('production.orders.start', 'Start Production Orders', 'production', 'Can start production orders'),
('production.orders.complete', 'Complete Production Orders', 'production', 'Can complete production orders'),
('production.orders.cancel', 'Cancel Production Orders', 'production', 'Can cancel production orders'),
('production.tasks.view', 'View Tasks', 'production', 'Can view tasks'),
('production.tasks.create', 'Create Tasks', 'production', 'Can create tasks'),
('production.tasks.edit', 'Edit Tasks', 'production', 'Can edit tasks'),
('production.tasks.assign', 'Assign Tasks', 'production', 'Can assign tasks to workers'),
('production.tasks.start', 'Start Tasks', 'production', 'Can start tasks'),
('production.tasks.complete', 'Complete Tasks', 'production', 'Can complete tasks'),
('production.print_queue.view', 'View Print Queue', 'production', 'Can view print queue'),
('production.print_queue.create', 'Create Print Jobs', 'production', 'Can create print jobs'),
('production.print_queue.edit', 'Edit Print Jobs', 'production', 'Can edit print jobs'),
('production.print_queue.start', 'Start Print Jobs', 'production', 'Can start print jobs'),
('production.print_queue.complete', 'Complete Print Jobs', 'production', 'Can complete print jobs'),
('production.print_queue.override', 'Override Print Job Limits', 'production', 'Can override material limits'),

-- Costing module
('costing.view', 'View Costing', 'costing', 'Can view cost calculations'),
('costing.planned.view', 'View Planned Cost', 'costing', 'Can view planned costs'),
('costing.actual.view', 'View Actual Cost', 'costing', 'Can view actual costs'),
('costing.compare', 'Compare Costs', 'costing', 'Can compare planned vs actual');

-- =====================================================
-- Role Permissions
-- =====================================================
-- Admin gets all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r CROSS JOIN `permissions` p WHERE r.code = 'admin';

-- Accountant/Warehouse permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'accountant' AND p.module = 'warehouse';

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'accountant' AND p.code IN ('catalog.products.view', 'catalog.variants.view', 'costing.view');

-- Manager permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'manager' AND p.module IN ('catalog', 'production', 'costing');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'manager' AND p.code LIKE 'warehouse.%.view';

-- Worker permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'worker' AND p.code IN (
    'production.tasks.view', 'production.tasks.start', 'production.tasks.complete',
    'production.print_queue.view', 'production.print_queue.start', 'production.print_queue.complete',
    'warehouse.stock.view', 'warehouse.items.view', 'warehouse.lots.view',
    'catalog.products.view', 'catalog.variants.view'
);

-- Viewer permissions (view only)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'viewer' AND p.code LIKE '%.view';

-- =====================================================
-- Units of Measure
-- =====================================================
INSERT INTO `units` (`code`, `name`, `description`) VALUES
('pcs', 'Pieces', 'Individual units'),
('g', 'Grams', 'Weight in grams'),
('kg', 'Kilograms', 'Weight in kilograms'),
('m', 'Meters', 'Length in meters'),
('cm', 'Centimeters', 'Length in centimeters'),
('set', 'Set', 'Complete set'),
('roll', 'Roll', 'Roll of material'),
('pack', 'Pack', 'Package/pack');

-- =====================================================
-- Item Types
-- =====================================================
INSERT INTO `item_types` (`code`, `name`, `description`, `sort_order`) VALUES
('material', 'Material', 'Raw materials like plastic filament', 1),
('component', 'Component', 'Purchased components and parts', 2),
('part', 'Part', 'Manufactured/printed parts', 3),
('consumable', 'Consumable', 'Consumables like glue, tape, etc.', 4),
('packaging', 'Packaging', 'Packaging materials', 5);

-- =====================================================
-- Document Types
-- =====================================================
INSERT INTO `document_types` (`code`, `name`, `direction`, `affects_stock`, `description`) VALUES
('receipt', 'Receipt', 'in', 1, 'Incoming goods from supplier'),
('issue', 'Issue/Write-off', 'out', 1, 'Outgoing goods or write-off'),
('transfer', 'Transfer', 'transfer', 1, 'Transfer between warehouses'),
('stocktake', 'Stocktake/Adjustment', 'adjust', 1, 'Inventory adjustment'),
('return', 'Return to Supplier', 'out', 1, 'Return goods to supplier');

-- =====================================================
-- Default Warehouse
-- =====================================================
INSERT INTO `warehouses` (`code`, `name`, `address`, `is_default`, `is_active`) VALUES
('MAIN', 'Main Warehouse', 'Main production facility', 1, 1);

-- =====================================================
-- Operation Types
-- =====================================================
INSERT INTO `operation_types` (`code`, `name`, `description`, `default_setup_time`, `default_run_time`, `hourly_rate`) VALUES
('PRINT', '3D Printing', '3D printing operation', 5, 0, 50.00),
('ASSEMBLY', 'Assembly', 'Component assembly', 2, 5, 40.00),
('QC', 'Quality Control', 'Quality inspection and testing', 0, 3, 45.00),
('PACK', 'Packaging', 'Product packaging', 1, 2, 35.00),
('PREP', 'Preparation', 'Material preparation', 2, 0, 35.00);

-- =====================================================
-- Production Order Statuses
-- =====================================================
INSERT INTO `production_statuses` (`code`, `name`, `color`, `sort_order`, `is_final`) VALUES
('draft', 'Draft', '#6c757d', 1, 0),
('planned', 'Planned', '#17a2b8', 2, 0),
('in_progress', 'In Progress', '#ffc107', 3, 0),
('completed', 'Completed', '#28a745', 4, 1),
('cancelled', 'Cancelled', '#dc3545', 5, 1);

-- =====================================================
-- Task Statuses
-- =====================================================
INSERT INTO `task_statuses` (`code`, `name`, `color`, `sort_order`, `is_final`) VALUES
('pending', 'Pending', '#6c757d', 1, 0),
('assigned', 'Assigned', '#17a2b8', 2, 0),
('in_progress', 'In Progress', '#ffc107', 3, 0),
('completed', 'Completed', '#28a745', 4, 1),
('cancelled', 'Cancelled', '#dc3545', 5, 1);

-- =====================================================
-- Print Job Statuses
-- =====================================================
INSERT INTO `print_job_statuses` (`code`, `name`, `color`, `sort_order`, `is_final`) VALUES
('queued', 'Queued', '#6c757d', 1, 0),
('reserved', 'Reserved', '#17a2b8', 2, 0),
('printing', 'Printing', '#ffc107', 3, 0),
('completed', 'Completed', '#28a745', 4, 1),
('cancelled', 'Cancelled', '#dc3545', 5, 1),
('failed', 'Failed', '#dc3545', 6, 1);

-- =====================================================
-- Default Settings
-- =====================================================
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('app.name', 'Lumixa Manufacturing System', 'string', 'general', 'Application name'),
('app.timezone', 'Europe/Kiev', 'string', 'general', 'Application timezone'),
('app.currency', 'UAH', 'string', 'general', 'Default currency'),
('stock.allow_negative', '0', 'boolean', 'warehouse', 'Allow negative stock'),
('stock.default_cost_method', 'AVG', 'string', 'warehouse', 'Default cost calculation method (AVG/FIFO)'),
('costing.overhead_rate', '20', 'number', 'costing', 'Default overhead rate (%)'),
('costing.labor_rate', '150', 'number', 'costing', 'Default labor rate per hour'),
('backup.retention_days', '30', 'number', 'backup', 'Number of days to keep backups'),
('backup.include_files', '1', 'boolean', 'backup', 'Include uploaded files in backups');
