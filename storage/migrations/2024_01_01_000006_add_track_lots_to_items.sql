-- =====================================================
-- Lumixa LMS - Add track_lots to items
-- =====================================================

ALTER TABLE items
    ADD COLUMN track_lots TINYINT(1) DEFAULT 0 AFTER reorder_point,
    ADD INDEX idx_track_lots (track_lots);
