-- =====================================================================
-- MEMBER 5 ADDITION — Local Market Repair Rates
-- Run this AFTER smart_rental_manager.sql has already been imported.
-- =====================================================================

USE smart_rental_manager;

DROP TABLE IF EXISTS repair_rates;
CREATE TABLE repair_rates (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name       VARCHAR(150)        NOT NULL COMMENT 'e.g. Tap Repair, Geyser Repair',
    category        VARCHAR(100)        NULL COMMENT 'e.g. Plumbing, Electrical, Carpentry',
    low_cost        DECIMAL(10,2)       NOT NULL,
    high_cost       DECIMAL(10,2)       NOT NULL,
    unit            VARCHAR(50)         NULL COMMENT 'e.g. per visit, per fixture, per sq ft',
    region          VARCHAR(100)        NOT NULL DEFAULT 'Lahore',
    updated_by      INT UNSIGNED        NULL COMMENT 'Landlord/admin who last updated this rate',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_repair_rates_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_repair_rates_category (category),
    KEY idx_repair_rates_region (region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data — approximate Lahore local market rates (demo values)
INSERT INTO repair_rates (item_name, category, low_cost, high_cost, unit, region) VALUES
('Tap / Faucet Repair',         'Plumbing',    500.00,   1500.00, 'per fixture', 'Lahore'),
('Pipe Leakage Fix',            'Plumbing',    1000.00,  3500.00, 'per visit',   'Lahore'),
('Geyser Repair',               'Plumbing',    2000.00,  6000.00, 'per visit',   'Lahore'),
('Electrical Switch/Socket',    'Electrical',  300.00,   1000.00, 'per fixture', 'Lahore'),
('Wiring Fault Repair',         'Electrical',  1500.00,  5000.00, 'per visit',   'Lahore'),
('Ceiling Fan Repair',          'Electrical',  800.00,   2500.00, 'per fixture', 'Lahore'),
('Door Lock Repair/Replace',    'Carpentry',   500.00,   2000.00, 'per fixture', 'Lahore'),
('Window Glass Replacement',    'Carpentry',   1000.00,  4000.00, 'per fixture', 'Lahore'),
('Wall Paint Touch-up',         'Paint',       3000.00,  10000.00,'per room',    'Lahore'),
('Wall Crack / Seepage Repair', 'Structural',  5000.00,  20000.00,'per wall',    'Lahore');
