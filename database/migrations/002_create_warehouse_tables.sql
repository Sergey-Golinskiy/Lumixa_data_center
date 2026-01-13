-- LMS Database Schema v1.0
-- Warehouse Tables: Items, Lots, Partners, Stock, Documents

-- =====================================================
-- Units of Measure
-- =====================================================
CREATE TABLE IF NOT EXISTS `units` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_units_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Item Types Enum Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `item_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Items (SKU) Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `item_type_id` INT UNSIGNED NOT NULL,
    `unit_id` INT UNSIGNED NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `min_stock_level` DECIMAL(15,4) NULL DEFAULT 0,
    `default_price` DECIMAL(15,4) NULL DEFAULT 0,
    `barcode` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_items_sku` (`sku`),
    INDEX `idx_items_name` (`name`),
    INDEX `idx_items_type` (`item_type_id`),
    INDEX `idx_items_is_active` (`is_active`),
    FOREIGN KEY (`item_type_id`) REFERENCES `item_types`(`id`),
    FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Item Attributes (for materials like color, diameter)
-- =====================================================
CREATE TABLE IF NOT EXISTS `item_attributes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT UNSIGNED NOT NULL,
    `attribute_name` VARCHAR(100) NOT NULL,
    `attribute_value` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_item_attributes_item` (`item_id`),
    INDEX `idx_item_attributes_name` (`attribute_name`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Partners (Suppliers/Customers)
-- =====================================================
CREATE TABLE IF NOT EXISTS `partners` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('supplier', 'customer', 'both') NOT NULL DEFAULT 'supplier',
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `country` VARCHAR(100) NULL,
    `tax_id` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_partners_code` (`code`),
    INDEX `idx_partners_name` (`name`),
    INDEX `idx_partners_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Warehouses
-- =====================================================
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_warehouses_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Warehouse Locations (optional: shelves, bins)
-- =====================================================
CREATE TABLE IF NOT EXISTS `warehouse_locations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_location_warehouse_code` (`warehouse_id`, `code`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Lots/Batches (especially for materials)
-- =====================================================
CREATE TABLE IF NOT EXISTS `lots` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_number` VARCHAR(100) NOT NULL,
    `color` VARCHAR(100) NULL,
    `supplier_id` INT UNSIGNED NULL,
    `received_date` DATE NULL,
    `expiry_date` DATE NULL,
    `purchase_price` DECIMAL(15,4) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_lots_item_lot` (`item_id`, `lot_number`),
    INDEX `idx_lots_item` (`item_id`),
    INDEX `idx_lots_lot_number` (`lot_number`),
    INDEX `idx_lots_color` (`color`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`supplier_id`) REFERENCES `partners`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Stock (Current inventory levels)
-- =====================================================
CREATE TABLE IF NOT EXISTS `stock` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `location_id` INT UNSIGNED NULL,
    `on_hand` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `reserved` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_stock_item_lot_warehouse` (`item_id`, `lot_id`, `warehouse_id`, `location_id`),
    INDEX `idx_stock_item` (`item_id`),
    INDEX `idx_stock_lot` (`lot_id`),
    INDEX `idx_stock_warehouse` (`warehouse_id`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`location_id`) REFERENCES `warehouse_locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Document Types
-- =====================================================
CREATE TABLE IF NOT EXISTS `document_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `direction` ENUM('in', 'out', 'transfer', 'adjust') NOT NULL,
    `affects_stock` TINYINT(1) NOT NULL DEFAULT 1,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Documents (Receipt, Issue, Transfer, Stocktake)
-- =====================================================
CREATE TABLE IF NOT EXISTS `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_number` VARCHAR(50) NOT NULL UNIQUE,
    `document_type_id` INT UNSIGNED NOT NULL,
    `status` ENUM('draft', 'posted', 'cancelled') NOT NULL DEFAULT 'draft',
    `warehouse_id` INT UNSIGNED NOT NULL,
    `target_warehouse_id` INT UNSIGNED NULL,
    `partner_id` INT UNSIGNED NULL,
    `document_date` DATE NOT NULL,
    `reference` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `posted_at` DATETIME NULL,
    `posted_by` INT UNSIGNED NULL,
    `cancelled_at` DATETIME NULL,
    `cancelled_by` INT UNSIGNED NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_documents_number` (`document_number`),
    INDEX `idx_documents_type` (`document_type_id`),
    INDEX `idx_documents_status` (`status`),
    INDEX `idx_documents_date` (`document_date`),
    INDEX `idx_documents_warehouse` (`warehouse_id`),
    FOREIGN KEY (`document_type_id`) REFERENCES `document_types`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`target_warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`partner_id`) REFERENCES `partners`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`posted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`cancelled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Document Lines
-- =====================================================
CREATE TABLE IF NOT EXISTS `document_lines` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `line_number` INT NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `unit_price` DECIMAL(15,4) NULL,
    `total_price` DECIMAL(15,4) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_document_lines_document` (`document_id`),
    INDEX `idx_document_lines_item` (`item_id`),
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Stock Postings (Immutable transaction log)
-- =====================================================
CREATE TABLE IF NOT EXISTS `stock_postings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `document_line_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `location_id` INT UNSIGNED NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `direction` ENUM('in', 'out') NOT NULL,
    `unit_cost` DECIMAL(15,4) NULL,
    `posted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_postings_document` (`document_id`),
    INDEX `idx_postings_item` (`item_id`),
    INDEX `idx_postings_lot` (`lot_id`),
    INDEX `idx_postings_warehouse` (`warehouse_id`),
    INDEX `idx_postings_date` (`posted_at`),
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`),
    FOREIGN KEY (`document_line_id`) REFERENCES `document_lines`(`id`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`location_id`) REFERENCES `warehouse_locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Reservations (for Print Queue and Production)
-- =====================================================
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `source_type` VARCHAR(50) NOT NULL,
    `source_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'active',
    `fulfilled_quantity` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_reservations_item` (`item_id`),
    INDEX `idx_reservations_lot` (`lot_id`),
    INDEX `idx_reservations_source` (`source_type`, `source_id`),
    INDEX `idx_reservations_status` (`status`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
