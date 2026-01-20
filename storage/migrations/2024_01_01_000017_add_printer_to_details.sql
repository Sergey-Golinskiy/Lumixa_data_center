-- =====================================================
-- Lumixa LMS - Link details to printers
-- =====================================================

ALTER TABLE printers
    MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE details
    ADD COLUMN printer_id BIGINT UNSIGNED NULL AFTER material_item_id,
    ADD INDEX idx_printer_id (printer_id),
    ADD CONSTRAINT fk_details_printer_id
        FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL;
