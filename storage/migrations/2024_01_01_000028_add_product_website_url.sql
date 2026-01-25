-- =====================================================
-- Lumixa LMS - Add website URL to products
-- =====================================================

-- Add website_url column to products table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'website_url');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN website_url VARCHAR(500) NULL COMMENT ''Link to product page in online store''',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
