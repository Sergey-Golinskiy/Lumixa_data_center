-- =====================================================
-- Lumixa LMS - Product Categories
-- =====================================================

CREATE TABLE IF NOT EXISTS product_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'products'
      AND column_name = 'category_id'
);
SET @col_sql := IF(
    @col_exists = 0,
    'ALTER TABLE products ADD COLUMN category_id INT UNSIGNED NULL AFTER description',
    'SELECT 1'
);
PREPARE col_stmt FROM @col_sql;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'products'
      AND index_name = 'idx_category_id'
);
SET @idx_sql := IF(
    @idx_exists = 0,
    'ALTER TABLE products ADD INDEX idx_category_id (category_id)',
    'SELECT 1'
);
PREPARE idx_stmt FROM @idx_sql;
EXECUTE idx_stmt;
DEALLOCATE PREPARE idx_stmt;

SET @fk_exists := (
    SELECT COUNT(*) FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'products'
      AND constraint_name = 'fk_products_category_id'
      AND constraint_type = 'FOREIGN KEY'
);
SET @fk_sql := IF(
    @fk_exists = 0,
    'ALTER TABLE products ADD CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

INSERT INTO product_categories (name, is_active)
SELECT DISTINCT category COLLATE utf8mb4_unicode_ci, 1
FROM products
WHERE category IS NOT NULL AND category != ''
ON DUPLICATE KEY UPDATE name = VALUES(name);

UPDATE products p
JOIN product_categories pc ON pc.name = (p.category COLLATE utf8mb4_unicode_ci)
SET p.category_id = pc.id
WHERE p.category IS NOT NULL AND p.category != '';
