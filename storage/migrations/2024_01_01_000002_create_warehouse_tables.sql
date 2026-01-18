-- =====================================================
-- Lumixa LMS - Warehouse Tables Migration
-- =====================================================

-- Partners (suppliers/customers)
CREATE TABLE IF NOT EXISTS partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    type ENUM('supplier', 'customer', 'both') DEFAULT 'supplier',
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items (SKU)
CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    type ENUM('material', 'component', 'part', 'consumable', 'packaging') NOT NULL,
    unit ENUM('pcs', 'g', 'm', 'set', 'kg', 'l') DEFAULT 'pcs',
    description TEXT,
    min_stock DECIMAL(15,4) DEFAULT 0,
    reorder_point DECIMAL(15,4) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku),
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item attributes (for materials: color, diameter, brand, etc.)
CREATE TABLE IF NOT EXISTS item_attributes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item_attribute (item_id, attribute_name),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lots/Batches
CREATE TABLE IF NOT EXISTS lots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    lot_number VARCHAR(100) NOT NULL,
    color VARCHAR(100),
    received_at DATE,
    expiry_date DATE,
    supplier_id INT UNSIGNED,
    purchase_price DECIMAL(15,4) DEFAULT 0,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item_lot (item_id, lot_number),
    INDEX idx_lot_number (lot_number),
    INDEX idx_item_id (item_id),
    INDEX idx_color (color),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES partners(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock balances (per item/lot)
CREATE TABLE IF NOT EXISTS stock_balances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    lot_id INT UNSIGNED,
    on_hand DECIMAL(15,4) DEFAULT 0,
    reserved DECIMAL(15,4) DEFAULT 0,
    avg_cost DECIMAL(15,4) DEFAULT 0,
    last_movement_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item_lot_stock (item_id, lot_id),
    INDEX idx_item_id (item_id),
    INDEX idx_lot_id (lot_id),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents
CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_number VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('receipt', 'issue', 'transfer', 'stocktake', 'adjustment') NOT NULL,
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft',
    partner_id INT UNSIGNED,
    document_date DATE NOT NULL,
    notes TEXT,
    total_amount DECIMAL(15,4) DEFAULT 0,
    posted_at TIMESTAMP NULL,
    posted_by INT UNSIGNED,
    cancelled_at TIMESTAMP NULL,
    cancelled_by INT UNSIGNED,
    cancel_reason TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_document_number (document_number),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_document_date (document_date),
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document lines
CREATE TABLE IF NOT EXISTS document_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id INT UNSIGNED NOT NULL,
    line_number INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    lot_id INT UNSIGNED,
    quantity DECIMAL(15,4) NOT NULL,
    unit_price DECIMAL(15,4) DEFAULT 0,
    total_price DECIMAL(15,4) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document_id (document_id),
    INDEX idx_item_id (item_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock movements (postings - immutable log)
CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id INT UNSIGNED NOT NULL,
    document_line_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    lot_id INT UNSIGNED,
    movement_type ENUM('in', 'out', 'reserve', 'unreserve') NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_cost DECIMAL(15,4) DEFAULT 0,
    balance_before DECIMAL(15,4) DEFAULT 0,
    balance_after DECIMAL(15,4) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document_id (document_id),
    INDEX idx_item_id (item_id),
    INDEX idx_lot_id (lot_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE RESTRICT,
    FOREIGN KEY (document_line_id) REFERENCES document_lines(id) ON DELETE RESTRICT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations (for print queue and production)
CREATE TABLE IF NOT EXISTS reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    lot_id INT UNSIGNED,
    quantity DECIMAL(15,4) NOT NULL,
    reserved_for_type ENUM('print_job', 'production_order', 'manual') NOT NULL,
    reserved_for_id INT UNSIGNED,
    status ENUM('active', 'fulfilled', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fulfilled_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    INDEX idx_item_id (item_id),
    INDEX idx_status (status),
    INDEX idx_reserved_for (reserved_for_type, reserved_for_id),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document number sequences
CREATE TABLE IF NOT EXISTS document_sequences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE,
    prefix VARCHAR(10) NOT NULL,
    current_number INT UNSIGNED DEFAULT 0,
    year INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default sequences
INSERT INTO document_sequences (type, prefix, current_number, year) VALUES
('receipt', 'RCP', 0, YEAR(NOW())),
('issue', 'ISS', 0, YEAR(NOW())),
('transfer', 'TRF', 0, YEAR(NOW())),
('stocktake', 'STK', 0, YEAR(NOW())),
('adjustment', 'ADJ', 0, YEAR(NOW()));
