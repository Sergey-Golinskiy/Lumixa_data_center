-- =====================================================
-- Add locale field to users table
-- =====================================================

ALTER TABLE users ADD COLUMN locale VARCHAR(5) DEFAULT 'en' AFTER name;

-- Update existing users to have default locale
UPDATE users SET locale = 'en' WHERE locale IS NULL;
