-- =====================================================
-- Lumixa LMS - Add cost columns to printers table
-- =====================================================

-- Add power_watts column if not exists
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'printers'
    AND COLUMN_NAME = 'power_watts'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE printers ADD COLUMN power_watts DECIMAL(10,2) DEFAULT 0 AFTER model',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add electricity_cost_per_kwh column if not exists
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'printers'
    AND COLUMN_NAME = 'electricity_cost_per_kwh'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE printers ADD COLUMN electricity_cost_per_kwh DECIMAL(10,4) DEFAULT 0 AFTER power_watts',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add amortization_per_hour column if not exists
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'printers'
    AND COLUMN_NAME = 'amortization_per_hour'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE printers ADD COLUMN amortization_per_hour DECIMAL(15,4) DEFAULT 0 AFTER electricity_cost_per_kwh',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add maintenance_per_hour column if not exists
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'printers'
    AND COLUMN_NAME = 'maintenance_per_hour'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE printers ADD COLUMN maintenance_per_hour DECIMAL(15,4) DEFAULT 0 AFTER amortization_per_hour',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
