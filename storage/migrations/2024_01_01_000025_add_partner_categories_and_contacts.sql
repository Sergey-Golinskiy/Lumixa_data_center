-- =====================================================
-- Lumixa LMS - Partner Categories and Additional Contacts
-- =====================================================

-- Partner categories (industries/business types)
CREATE TABLE IF NOT EXISTS partner_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add category_id to partners table
ALTER TABLE partners
    ADD COLUMN category_id INT UNSIGNED NULL AFTER type,
    ADD CONSTRAINT fk_partners_category
        FOREIGN KEY (category_id) REFERENCES partner_categories(id)
        ON DELETE SET NULL,
    ADD INDEX idx_category_id (category_id);

-- Partner additional contacts
CREATE TABLE IF NOT EXISTS partner_contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    social_media VARCHAR(255),
    notes TEXT,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
    INDEX idx_partner_id (partner_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default categories
INSERT INTO partner_categories (name, description) VALUES
('Производство', 'Производственные предприятия'),
('Торговля', 'Оптовая и розничная торговля'),
('Услуги', 'Сервисные компании'),
('IT и технологии', 'IT-компании и технологические стартапы'),
('Строительство', 'Строительные и девелоперские компании'),
('Транспорт и логистика', 'Транспортные и логистические компании'),
('Другое', 'Прочие виды деятельности');
