-- =====================================================
-- Lumixa LMS - Base Tables Migration
-- =====================================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    must_change_password TINYINT(1) DEFAULT 0,
    last_login_at TIMESTAMP NULL,
    login_attempts INT UNSIGNED DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    module VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-Permission relationship
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User-Role relationship
CREATE TABLE IF NOT EXISTS user_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_role (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    old_values JSON,
    new_values JSON,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_id VARCHAR(32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backups table
CREATE TABLE IF NOT EXISTS backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    type ENUM('full', 'db_only', 'files_only') DEFAULT 'full',
    size BIGINT UNSIGNED DEFAULT 0,
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    includes_db TINYINT(1) DEFAULT 1,
    includes_files TINYINT(1) DEFAULT 1,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO roles (name, slug, description, is_system) VALUES
('Administrator', 'admin', 'Full system access', 1),
('Manager', 'manager', 'Manage catalog, production, and costing', 1),
('Accountant', 'accountant', 'Manage warehouse and documents', 1),
('Worker', 'worker', 'Execute tasks and print jobs', 1),
('Viewer', 'viewer', 'Read-only access', 1);

-- Insert permissions
INSERT INTO permissions (code, name, module) VALUES
-- Dashboard
('dashboard.view', 'View Dashboard', 'dashboard'),

-- Admin
('admin.access', 'Access Admin Panel', 'admin'),
('admin.users.view', 'View Users', 'admin'),
('admin.users.create', 'Create Users', 'admin'),
('admin.users.edit', 'Edit Users', 'admin'),
('admin.users.delete', 'Delete Users', 'admin'),
('admin.roles.view', 'View Roles', 'admin'),
('admin.roles.edit', 'Edit Roles', 'admin'),
('admin.audit.view', 'View Audit Log', 'admin'),
('admin.backups.view', 'View Backups', 'admin'),
('admin.backups.create', 'Create Backups', 'admin'),
('admin.backups.restore', 'Restore Backups', 'admin'),
('admin.backups.delete', 'Delete Backups', 'admin'),
('admin.diagnostics.view', 'View Diagnostics', 'admin'),
('admin.settings.view', 'View Settings', 'admin'),
('admin.settings.edit', 'Edit Settings', 'admin'),

-- Warehouse
('warehouse.items.view', 'View Items', 'warehouse'),
('warehouse.items.create', 'Create Items', 'warehouse'),
('warehouse.items.edit', 'Edit Items', 'warehouse'),
('warehouse.items.delete', 'Delete Items', 'warehouse'),
('warehouse.stock.view', 'View Stock', 'warehouse'),
('warehouse.documents.view', 'View Documents', 'warehouse'),
('warehouse.documents.create', 'Create Documents', 'warehouse'),
('warehouse.documents.edit', 'Edit Documents', 'warehouse'),
('warehouse.documents.post', 'Post Documents', 'warehouse'),
('warehouse.documents.cancel', 'Cancel Documents', 'warehouse'),
('warehouse.partners.view', 'View Partners', 'warehouse'),
('warehouse.partners.create', 'Create Partners', 'warehouse'),
('warehouse.partners.edit', 'Edit Partners', 'warehouse'),

-- Catalog
('catalog.products.view', 'View Products', 'catalog'),
('catalog.products.create', 'Create Products', 'catalog'),
('catalog.products.edit', 'Edit Products', 'catalog'),
('catalog.products.delete', 'Delete Products', 'catalog'),
('catalog.variants.view', 'View Variants', 'catalog'),
('catalog.variants.create', 'Create Variants', 'catalog'),
('catalog.variants.edit', 'Edit Variants', 'catalog'),
('catalog.bom.view', 'View BOM', 'catalog'),
('catalog.bom.create', 'Create BOM', 'catalog'),
('catalog.bom.edit', 'Edit BOM', 'catalog'),
('catalog.bom.activate', 'Activate BOM', 'catalog'),
('catalog.routing.view', 'View Routing', 'catalog'),
('catalog.routing.create', 'Create Routing', 'catalog'),
('catalog.routing.edit', 'Edit Routing', 'catalog'),
('catalog.routing.activate', 'Activate Routing', 'catalog'),

-- Production
('production.orders.view', 'View Production Orders', 'production'),
('production.orders.create', 'Create Production Orders', 'production'),
('production.orders.edit', 'Edit Production Orders', 'production'),
('production.orders.manage', 'Manage Production Orders', 'production'),
('production.tasks.view', 'View Tasks', 'production'),
('production.tasks.update', 'Update Tasks', 'production'),
('production.print-queue.view', 'View Print Queue', 'production'),
('production.print-queue.create', 'Create Print Jobs', 'production'),
('production.print-queue.update', 'Update Print Jobs', 'production'),
('production.print-queue.manage', 'Manage Print Queue', 'production'),

-- Costing
('costing.view', 'View Costing', 'costing'),
('costing.reports', 'View Costing Reports', 'costing');

-- Insert default settings
INSERT INTO settings (`key`, value, type, description) VALUES
('app_installed', '0', 'boolean', 'Application installation status'),
('currency', 'UAH', 'string', 'Default currency'),
('currency_symbol', '₴', 'string', 'Currency symbol'),
('overhead_percent', '15', 'number', 'Default overhead percentage'),
('costing_method', 'AVG', 'string', 'Cost calculation method'),
('items_per_page', '25', 'number', 'Default pagination size');
