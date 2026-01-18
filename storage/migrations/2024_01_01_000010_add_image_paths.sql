-- =====================================================
-- Lumixa LMS - Add image paths
-- =====================================================

-- Items
SET @items_img_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'items'
      AND column_name = 'image_path'
);
SET @items_img_sql := IF(
    @items_img_exists = 0,
    'ALTER TABLE items ADD COLUMN image_path VARCHAR(255) NULL AFTER reorder_point',
    'SELECT 1'
);
PREPARE items_img_stmt FROM @items_img_sql;
EXECUTE items_img_stmt;
DEALLOCATE PREPARE items_img_stmt;

-- Products
SET @products_img_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'products'
      AND column_name = 'image_path'
);
SET @products_img_sql := IF(
    @products_img_exists = 0,
    'ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL AFTER description',
    'SELECT 1'
);
PREPARE products_img_stmt FROM @products_img_sql;
EXECUTE products_img_stmt;
DEALLOCATE PREPARE products_img_stmt;

-- Variants
SET @variants_img_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'variants'
      AND column_name = 'image_path'
);
SET @variants_img_sql := IF(
    @variants_img_exists = 0,
    'ALTER TABLE variants ADD COLUMN image_path VARCHAR(255) NULL AFTER name',
    'SELECT 1'
);
PREPARE variants_img_stmt FROM @variants_img_sql;
EXECUTE variants_img_stmt;
DEALLOCATE PREPARE variants_img_stmt;

-- BOM
SET @bom_img_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bom'
      AND column_name = 'image_path'
);
SET @bom_img_sql := IF(
    @bom_img_exists = 0,
    'ALTER TABLE bom ADD COLUMN image_path VARCHAR(255) NULL AFTER notes',
    'SELECT 1'
);
PREPARE bom_img_stmt FROM @bom_img_sql;
EXECUTE bom_img_stmt;
DEALLOCATE PREPARE bom_img_stmt;

-- Routing
SET @routing_img_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'routing'
      AND column_name = 'image_path'
);
SET @routing_img_sql := IF(
    @routing_img_exists = 0,
    'ALTER TABLE routing ADD COLUMN image_path VARCHAR(255) NULL AFTER notes',
    'SELECT 1'
);
PREPARE routing_img_stmt FROM @routing_img_sql;
EXECUTE routing_img_stmt;
DEALLOCATE PREPARE routing_img_stmt;
