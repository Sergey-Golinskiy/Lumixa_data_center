-- =====================================================
-- Lumixa LMS - Add color column to item_option_values
-- =====================================================

ALTER TABLE item_option_values ADD COLUMN color VARCHAR(7) DEFAULT NULL AFTER name;
