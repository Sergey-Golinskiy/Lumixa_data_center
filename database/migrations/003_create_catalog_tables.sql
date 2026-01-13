-- LMS Database Schema v1.0
-- Catalog Tables: Products, Variants, BOM, Routing

-- =====================================================
-- Products (Lamp models)
-- =====================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NULL,
    `status` ENUM('draft', 'active', 'discontinued') NOT NULL DEFAULT 'draft',
    `image_path` VARCHAR(500) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_products_code` (`code`),
    INDEX `idx_products_name` (`name`),
    INDEX `idx_products_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Product Variants (color/size/version combinations)
-- =====================================================
CREATE TABLE IF NOT EXISTS `variants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `sku` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `color` VARCHAR(100) NULL,
    `size` VARCHAR(50) NULL,
    `version` VARCHAR(50) NULL,
    `status` ENUM('draft', 'active', 'discontinued') NOT NULL DEFAULT 'draft',
    `active_bom_id` INT UNSIGNED NULL,
    `active_routing_id` INT UNSIGNED NULL,
    `base_price` DECIMAL(15,4) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_variants_product` (`product_id`),
    INDEX `idx_variants_sku` (`sku`),
    INDEX `idx_variants_status` (`status`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Variant Attributes (custom attributes per variant)
-- =====================================================
CREATE TABLE IF NOT EXISTS `variant_attributes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `variant_id` INT UNSIGNED NOT NULL,
    `attribute_name` VARCHAR(100) NOT NULL,
    `attribute_value` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_variant_attributes_variant` (`variant_id`),
    FOREIGN KEY (`variant_id`) REFERENCES `variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOM (Bill of Materials) Headers
-- =====================================================
CREATE TABLE IF NOT EXISTS `bom` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `variant_id` INT UNSIGNED NOT NULL,
    `revision` VARCHAR(20) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `status` ENUM('draft', 'active', 'archived') NOT NULL DEFAULT 'draft',
    `effective_date` DATE NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `approved_by` INT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_bom_variant_revision` (`variant_id`, `revision`),
    INDEX `idx_bom_variant` (`variant_id`),
    INDEX `idx_bom_status` (`status`),
    FOREIGN KEY (`variant_id`) REFERENCES `variants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOM Lines (Components)
-- =====================================================
CREATE TABLE IF NOT EXISTS `bom_lines` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bom_id` INT UNSIGNED NOT NULL,
    `line_number` INT NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `unit_id` INT UNSIGNED NOT NULL,
    `waste_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `waste_fixed` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `is_optional` TINYINT(1) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bom_lines_bom` (`bom_id`),
    INDEX `idx_bom_lines_item` (`item_id`),
    FOREIGN KEY (`bom_id`) REFERENCES `bom`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Routing Headers
-- =====================================================
CREATE TABLE IF NOT EXISTS `routing` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `variant_id` INT UNSIGNED NOT NULL,
    `revision` VARCHAR(20) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `status` ENUM('draft', 'active', 'archived') NOT NULL DEFAULT 'draft',
    `effective_date` DATE NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `approved_by` INT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_routing_variant_revision` (`variant_id`, `revision`),
    INDEX `idx_routing_variant` (`variant_id`),
    INDEX `idx_routing_status` (`status`),
    FOREIGN KEY (`variant_id`) REFERENCES `variants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Operation Types
-- =====================================================
CREATE TABLE IF NOT EXISTS `operation_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `default_setup_time` INT NULL,
    `default_run_time` INT NULL,
    `hourly_rate` DECIMAL(15,4) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_operation_types_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Routing Operations
-- =====================================================
CREATE TABLE IF NOT EXISTS `routing_operations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `routing_id` INT UNSIGNED NOT NULL,
    `step_number` INT NOT NULL,
    `operation_type_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `instructions` TEXT NULL,
    `setup_time_minutes` INT NULL DEFAULT 0,
    `run_time_minutes` INT NULL DEFAULT 0,
    `resource_required` VARCHAR(255) NULL,
    `is_quality_check` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_routing_operations_routing` (`routing_id`),
    INDEX `idx_routing_operations_step` (`step_number`),
    FOREIGN KEY (`routing_id`) REFERENCES `routing`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Operation Checklist Items
-- =====================================================
CREATE TABLE IF NOT EXISTS `operation_checklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `operation_id` INT UNSIGNED NOT NULL,
    `sequence` INT NOT NULL,
    `description` VARCHAR(500) NOT NULL,
    `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_operation_checklist_operation` (`operation_id`),
    FOREIGN KEY (`operation_id`) REFERENCES `routing_operations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Operation Attachments
-- =====================================================
CREATE TABLE IF NOT EXISTS `operation_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `operation_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_operation_attachments_operation` (`operation_id`),
    FOREIGN KEY (`operation_id`) REFERENCES `routing_operations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign keys for active BOM and Routing in variants
ALTER TABLE `variants`
    ADD FOREIGN KEY (`active_bom_id`) REFERENCES `bom`(`id`) ON DELETE SET NULL,
    ADD FOREIGN KEY (`active_routing_id`) REFERENCES `routing`(`id`) ON DELETE SET NULL;
