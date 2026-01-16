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

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED NULL AFTER description,
    ADD INDEX IF NOT EXISTS idx_category_id (category_id);

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
SELECT DISTINCT category, 1
FROM products
WHERE category IS NOT NULL AND category != ''
ON DUPLICATE KEY UPDATE name = VALUES(name);

UPDATE products p
JOIN product_categories pc ON pc.name = p.category
SET p.category_id = pc.id
WHERE p.category IS NOT NULL AND p.category != '';
