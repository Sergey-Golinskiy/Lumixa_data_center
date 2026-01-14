-- =====================================================
-- Fix User FK Columns Migration
-- =====================================================
-- This migration fixes FK compatibility issues where columns
-- referencing users(id) were created as INT instead of INT UNSIGNED.
-- MySQL requires FK columns to have identical types including signedness.
--
-- Note: These statements use DROP FOREIGN KEY IF EXISTS which requires
-- MySQL 8.0.16+ or MariaDB 10.0.2+. For older versions, errors on
-- non-existent FKs are safely ignored.
-- =====================================================

-- Disable FK checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- Fix bom.created_by if table exists
ALTER TABLE bom MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix routing.created_by if table exists
ALTER TABLE routing MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix production_orders.created_by if table exists
ALTER TABLE production_orders MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix production_tasks.assigned_to if table exists
ALTER TABLE production_tasks MODIFY COLUMN assigned_to INT UNSIGNED NULL;

-- Fix material_consumption.consumed_by if table exists
ALTER TABLE material_consumption MODIFY COLUMN consumed_by INT UNSIGNED NULL;

-- Fix print_queue.created_by if table exists
ALTER TABLE print_queue MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;
