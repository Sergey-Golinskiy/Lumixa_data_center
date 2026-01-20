-- =====================================================
-- Fix FK Column Types Migration
-- =====================================================
-- This migration fixes FK compatibility issues where columns
-- referencing tables from other migrations have mismatched types.
-- MySQL requires FK columns to have identical types including signedness.
--
-- Issues fixed:
-- 1. Columns referencing users(id) INT UNSIGNED - user FK columns
-- 2. Columns referencing items(id) INT UNSIGNED - warehouse FK columns
-- =====================================================

-- Disable FK checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Fix columns referencing users(id) INT UNSIGNED
-- =====================================================

-- Fix bom.created_by
ALTER TABLE bom MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix routing.created_by
ALTER TABLE routing MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix production_orders.created_by
ALTER TABLE production_orders MODIFY COLUMN created_by INT UNSIGNED NULL;

-- Fix production_tasks.assigned_to
ALTER TABLE production_tasks MODIFY COLUMN assigned_to INT UNSIGNED NULL;

-- Fix material_consumption.consumed_by
ALTER TABLE material_consumption MODIFY COLUMN consumed_by INT UNSIGNED NULL;

-- Fix print_queue.created_by
ALTER TABLE print_queue MODIFY COLUMN created_by INT UNSIGNED NULL;

-- =====================================================
-- Fix columns referencing items(id) INT UNSIGNED
-- =====================================================

-- Fix bom_lines.item_id
ALTER TABLE bom_lines MODIFY COLUMN item_id INT UNSIGNED NOT NULL;

-- Fix material_consumption.item_id
ALTER TABLE material_consumption MODIFY COLUMN item_id INT UNSIGNED NOT NULL;

-- Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;
