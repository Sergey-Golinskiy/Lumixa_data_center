-- =====================================================
-- Lumixa LMS - Remove lots/batches support
-- =====================================================

SET @schema := DATABASE();

-- Drop lot_id foreign keys and columns
SET @fk_doc_lines := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @schema
      AND table_name = 'document_lines'
      AND column_name = 'lot_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(@fk_doc_lines IS NOT NULL, CONCAT('ALTER TABLE document_lines DROP FOREIGN KEY ', @fk_doc_lines), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_doc_lines := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'document_lines' AND column_name = 'lot_id'
);
SET @sql := IF(@col_doc_lines > 0, 'ALTER TABLE document_lines DROP COLUMN lot_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_stock_moves := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @schema
      AND table_name = 'stock_movements'
      AND column_name = 'lot_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(@fk_stock_moves IS NOT NULL, CONCAT('ALTER TABLE stock_movements DROP FOREIGN KEY ', @fk_stock_moves), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_stock_moves := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'stock_movements' AND column_name = 'lot_id'
);
SET @sql := IF(@col_stock_moves > 0, 'ALTER TABLE stock_movements DROP COLUMN lot_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_reservations := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @schema
      AND table_name = 'reservations'
      AND column_name = 'lot_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(@fk_reservations IS NOT NULL, CONCAT('ALTER TABLE reservations DROP FOREIGN KEY ', @fk_reservations), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_reservations := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'reservations' AND column_name = 'lot_id'
);
SET @sql := IF(@col_reservations > 0, 'ALTER TABLE reservations DROP COLUMN lot_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_stock_balances := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @schema
      AND table_name = 'stock_balances'
      AND column_name = 'lot_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(@fk_stock_balances IS NOT NULL, CONCAT('ALTER TABLE stock_balances DROP FOREIGN KEY ', @fk_stock_balances), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_stock_balances := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'stock_balances' AND column_name = 'lot_id'
);
SET @sql := IF(@col_stock_balances > 0, 'ALTER TABLE stock_balances DROP COLUMN lot_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_material := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @schema
      AND table_name = 'material_consumption'
      AND column_name = 'lot_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(@fk_material IS NOT NULL, CONCAT('ALTER TABLE material_consumption DROP FOREIGN KEY ', @fk_material), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_material := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'material_consumption' AND column_name = 'lot_id'
);
SET @sql := IF(@col_material > 0, 'ALTER TABLE material_consumption DROP COLUMN lot_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop lots table
DROP TABLE IF EXISTS lots;

-- Remove track_lots column from items if present
SET @track_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = @schema AND table_name = 'items' AND column_name = 'track_lots'
);
SET @sql := IF(@track_exists > 0, 'ALTER TABLE items DROP COLUMN track_lots', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Remove lot-related permissions
DELETE rp
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
WHERE p.code IN ('warehouse.lots.view', 'warehouse.lots.create', 'warehouse.lots.edit');

DELETE FROM permissions WHERE code IN ('warehouse.lots.view', 'warehouse.lots.create', 'warehouse.lots.edit');
