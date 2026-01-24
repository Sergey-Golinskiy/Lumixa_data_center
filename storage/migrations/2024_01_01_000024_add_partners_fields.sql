-- =====================================================
-- Lumixa LMS - Add missing fields to partners table
-- =====================================================

ALTER TABLE partners
    ADD COLUMN tax_id VARCHAR(100) NULL AFTER address,
    ADD COLUMN contact_person VARCHAR(255) NULL AFTER tax_id,
    ADD INDEX idx_tax_id (tax_id);
