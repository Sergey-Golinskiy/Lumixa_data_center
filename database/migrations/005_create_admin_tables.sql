-- LMS Database Schema v1.0
-- Admin Tables: Custom Fields, UI Templates, Workflows, Backups

-- =====================================================
-- Custom Fields Definitions
-- =====================================================
CREATE TABLE IF NOT EXISTS `custom_fields` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `field_code` VARCHAR(100) NOT NULL,
    `field_name` VARCHAR(255) NOT NULL,
    `field_type` ENUM('string', 'text', 'number', 'boolean', 'date', 'datetime', 'enum', 'reference', 'file') NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `default_value` TEXT NULL,
    `options` JSON NULL,
    `validation_rules` JSON NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `visible_roles` JSON NULL,
    `editable_roles` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_custom_fields_entity_code` (`entity_type`, `field_code`),
    INDEX `idx_custom_fields_entity` (`entity_type`),
    INDEX `idx_custom_fields_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Custom Field Values
-- =====================================================
CREATE TABLE IF NOT EXISTS `custom_values` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `custom_field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `value_string` VARCHAR(500) NULL,
    `value_text` TEXT NULL,
    `value_number` DECIMAL(20,6) NULL,
    `value_boolean` TINYINT(1) NULL,
    `value_date` DATE NULL,
    `value_datetime` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_custom_values_field_entity` (`custom_field_id`, `entity_type`, `entity_id`),
    INDEX `idx_custom_values_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- UI Templates
-- =====================================================
CREATE TABLE IF NOT EXISTS `ui_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `template_type` ENUM('list', 'card', 'form') NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `config` JSON NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `visible_roles` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ui_templates_entity` (`entity_type`),
    INDEX `idx_ui_templates_type` (`template_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Workflow Definitions
-- =====================================================
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_workflows_entity` (`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Workflow Statuses
-- =====================================================
CREATE TABLE IF NOT EXISTS `workflow_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_initial` TINYINT(1) NOT NULL DEFAULT 0,
    `is_final` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_workflow_statuses_code` (`workflow_id`, `code`),
    INDEX `idx_workflow_statuses_workflow` (`workflow_id`),
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Workflow Transitions
-- =====================================================
CREATE TABLE IF NOT EXISTS `workflow_transitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `from_status_id` INT UNSIGNED NOT NULL,
    `to_status_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NULL,
    `required_permission` VARCHAR(100) NULL,
    `required_roles` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_workflow_transitions` (`workflow_id`, `from_status_id`, `to_status_id`),
    INDEX `idx_workflow_transitions_workflow` (`workflow_id`),
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_status_id`) REFERENCES `workflow_statuses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_status_id`) REFERENCES `workflow_statuses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Backups Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `backups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `type` ENUM('manual', 'scheduled') NOT NULL DEFAULT 'manual',
    `includes_db` TINYINT(1) NOT NULL DEFAULT 1,
    `includes_files` TINYINT(1) NOT NULL DEFAULT 0,
    `db_tables` JSON NULL,
    `status` ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `error_message` TEXT NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_backups_status` (`status`),
    INDEX `idx_backups_created_at` (`created_at`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Backup Restore Log
-- =====================================================
CREATE TABLE IF NOT EXISTS `backup_restores` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `backup_id` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `error_message` TEXT NULL,
    `restored_by` INT UNSIGNED NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_backup_restores_backup` (`backup_id`),
    FOREIGN KEY (`backup_id`) REFERENCES `backups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`restored_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- File Attachments (generic for any entity)
-- =====================================================
CREATE TABLE IF NOT EXISTS `attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `uploaded_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_attachments_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
