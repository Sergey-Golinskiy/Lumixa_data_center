-- =====================================================
-- Lumixa LMS - Link details to printers
-- =====================================================

ALTER TABLE details
    ADD COLUMN printer_id INT UNSIGNED NULL AFTER material_item_id,
    ADD INDEX idx_printer_id (printer_id),
    ADD CONSTRAINT fk_details_printer_id
        FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL;
