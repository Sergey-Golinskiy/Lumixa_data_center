-- =====================================================
-- Lumixa LMS - Printers park
-- =====================================================

CREATE TABLE IF NOT EXISTS printers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    model VARCHAR(150),
    power_watts DECIMAL(10,2) DEFAULT 0,
    electricity_cost_per_kwh DECIMAL(10,4) DEFAULT 0,
    amortization_per_hour DECIMAL(15,4) DEFAULT 0,
    maintenance_per_hour DECIMAL(15,4) DEFAULT 0,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
