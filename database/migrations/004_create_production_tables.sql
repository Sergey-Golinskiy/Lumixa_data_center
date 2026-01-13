-- LMS Database Schema v1.0
-- Production Tables: Orders, Tasks, Print Queue

-- =====================================================
-- Production Order Statuses
-- =====================================================
CREATE TABLE IF NOT EXISTS `production_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_final` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Production Orders
-- =====================================================
CREATE TABLE IF NOT EXISTS `production_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `variant_id` INT UNSIGNED NOT NULL,
    `bom_id` INT UNSIGNED NULL,
    `routing_id` INT UNSIGNED NULL,
    `quantity_planned` DECIMAL(15,4) NOT NULL,
    `quantity_completed` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `quantity_scrapped` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `status_id` INT UNSIGNED NOT NULL,
    `priority` INT NOT NULL DEFAULT 0,
    `planned_start_date` DATE NULL,
    `planned_end_date` DATE NULL,
    `actual_start_date` DATE NULL,
    `actual_end_date` DATE NULL,
    `due_date` DATE NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_production_orders_number` (`order_number`),
    INDEX `idx_production_orders_variant` (`variant_id`),
    INDEX `idx_production_orders_status` (`status_id`),
    INDEX `idx_production_orders_due_date` (`due_date`),
    FOREIGN KEY (`variant_id`) REFERENCES `variants`(`id`),
    FOREIGN KEY (`bom_id`) REFERENCES `bom`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`routing_id`) REFERENCES `routing`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`status_id`) REFERENCES `production_statuses`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Task Statuses
-- =====================================================
CREATE TABLE IF NOT EXISTS `task_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_final` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Production Tasks
-- =====================================================
CREATE TABLE IF NOT EXISTS `production_tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_number` VARCHAR(50) NOT NULL UNIQUE,
    `production_order_id` INT UNSIGNED NULL,
    `operation_type_id` INT UNSIGNED NULL,
    `routing_operation_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `instructions` TEXT NULL,
    `status_id` INT UNSIGNED NOT NULL,
    `assigned_to` INT UNSIGNED NULL,
    `priority` INT NOT NULL DEFAULT 0,
    `planned_start_date` DATETIME NULL,
    `planned_end_date` DATETIME NULL,
    `actual_start_date` DATETIME NULL,
    `actual_end_date` DATETIME NULL,
    `estimated_minutes` INT NULL,
    `actual_minutes` INT NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_production_tasks_number` (`task_number`),
    INDEX `idx_production_tasks_order` (`production_order_id`),
    INDEX `idx_production_tasks_status` (`status_id`),
    INDEX `idx_production_tasks_assigned` (`assigned_to`),
    FOREIGN KEY (`production_order_id`) REFERENCES `production_orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`routing_operation_id`) REFERENCES `routing_operations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`status_id`) REFERENCES `task_statuses`(`id`),
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Task Checklist Results
-- =====================================================
CREATE TABLE IF NOT EXISTS `task_checklist_results` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `checklist_item_id` INT UNSIGNED NULL,
    `description` VARCHAR(500) NOT NULL,
    `is_checked` TINYINT(1) NOT NULL DEFAULT 0,
    `checked_at` DATETIME NULL,
    `checked_by` INT UNSIGNED NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_task_checklist_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `production_tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`checklist_item_id`) REFERENCES `operation_checklist`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`checked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Task Comments
-- =====================================================
CREATE TABLE IF NOT EXISTS `task_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_task_comments_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `production_tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Task Attachments
-- =====================================================
CREATE TABLE IF NOT EXISTS `task_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_task_attachments_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `production_tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Print Job Statuses
-- =====================================================
CREATE TABLE IF NOT EXISTS `print_job_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_final` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Print Queue (Print Jobs)
-- =====================================================
CREATE TABLE IF NOT EXISTS `print_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `job_number` VARCHAR(50) NOT NULL UNIQUE,
    `production_order_id` INT UNSIGNED NULL,
    `production_task_id` INT UNSIGNED NULL,
    `variant_id` INT UNSIGNED NULL,
    `part_item_id` INT UNSIGNED NULL,
    `material_item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `color` VARCHAR(100) NULL,
    `quantity_units` DECIMAL(15,4) NOT NULL,
    `planned_grams` DECIMAL(15,4) NOT NULL,
    `actual_grams` DECIMAL(15,4) NULL,
    `reserved_grams` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `reservation_id` INT UNSIGNED NULL,
    `status_id` INT UNSIGNED NOT NULL,
    `printer_name` VARCHAR(100) NULL,
    `file_path` VARCHAR(500) NULL,
    `notes` TEXT NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `assigned_to` INT UNSIGNED NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_print_jobs_number` (`job_number`),
    INDEX `idx_print_jobs_order` (`production_order_id`),
    INDEX `idx_print_jobs_status` (`status_id`),
    INDEX `idx_print_jobs_material` (`material_item_id`),
    INDEX `idx_print_jobs_lot` (`lot_id`),
    FOREIGN KEY (`production_order_id`) REFERENCES `production_orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`production_task_id`) REFERENCES `production_tasks`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`variant_id`) REFERENCES `variants`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`part_item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`material_item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`status_id`) REFERENCES `print_job_statuses`(`id`),
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Print Job Material Consumptions
-- =====================================================
CREATE TABLE IF NOT EXISTS `print_job_consumptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `print_job_id` INT UNSIGNED NOT NULL,
    `document_id` INT UNSIGNED NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `lot_id` INT UNSIGNED NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_print_job_consumptions_job` (`print_job_id`),
    FOREIGN KEY (`print_job_id`) REFERENCES `print_jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    FOREIGN KEY (`lot_id`) REFERENCES `lots`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
