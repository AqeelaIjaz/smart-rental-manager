-- =====================================================================
-- SMART RENTAL MANAGER — DATABASE SCHEMA
-- Author: Member 2 (Meeral - Backend Developer)
-- Handoff to: Member 4 (Fatima - Database Design) for review/refinement
-- Charset: utf8mb4 (required for Urdu / Roman Urdu / English text)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS smart_rental_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smart_rental_manager;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- TABLE: users
-- Purpose: Stores Tenant and Landlord accounts (role column distinguishes
-- the two). As of v2, Admin accounts moved to their own `admins` table
-- (see below) so admin login is database-driven and manageable from an
-- admin dashboard, instead of a single hardcoded admin.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150)        NOT NULL,
    phone           VARCHAR(20)         NOT NULL,
    email           VARCHAR(150)        NOT NULL,
    password        VARCHAR(255)        NOT NULL COMMENT 'bcrypt hash via password_hash()',
    role            ENUM('tenant','landlord') NOT NULL DEFAULT 'tenant',
    language        ENUM('en','ur','roman_ur') NOT NULL DEFAULT 'en',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: admins
-- Purpose: [NEW v2] Dedicated table for Admin accounts, separate from
-- `users`. This replaces a single hardcoded admin login — admins can
-- now be added or deactivated by an existing admin via the backend
-- (see backend/admin/*.php). `status` controls whether an admin can
-- currently log in (inactive admins are blocked at login).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150)        NOT NULL,
    email           VARCHAR(150)        NOT NULL,
    phone           VARCHAR(20)         NULL,
    password        VARCHAR(255)        NOT NULL COMMENT 'bcrypt hash via password_hash()',
    status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admins_email (email),
    KEY idx_admins_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: password_resets
-- Purpose: [NEW v2] Tracks OTP-based forgot/reset-password requests for
-- BOTH account types. `account_type` tells the backend which table
-- (`users` or `admins`) to look up and update. Not tied by foreign key
-- to either table on purpose — an OTP request should succeed as a
-- standalone log entry even if timing/race conditions occur.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier      VARCHAR(150)        NOT NULL COMMENT 'email address of the account requesting reset',
    account_type    ENUM('user','admin') NOT NULL DEFAULT 'user' COMMENT 'user = tenant/landlord (users table), admin = admins table',
    otp             VARCHAR(6)          NOT NULL,
    expires_at      DATETIME            NOT NULL,
    used            TINYINT(1)          NOT NULL DEFAULT 0,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_password_resets_identifier (identifier),
    KEY idx_password_resets_otp (otp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: agreements
-- Purpose: Represents a rental contract between a landlord and a
-- tenant. Root record that complaints, repairs, payments, and risk
-- scores are typically attached to.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS agreements;
CREATE TABLE agreements (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    landlord_id     INT UNSIGNED        NOT NULL,
    tenant_id       INT UNSIGNED        NOT NULL,
    rent_amount     DECIMAL(12,2)       NOT NULL,
    due_date        DATE                NOT NULL COMMENT 'Recurring monthly due date (day tracked via DATE, logic reads day-of-month)',
    penalty         DECIMAL(12,2)       NOT NULL DEFAULT 0.00,
    agreement_file  VARCHAR(255)        NULL COMMENT 'Stored filename of uploaded PDF/DOC/DOCX',
    extracted_text  TEXT                NULL COMMENT 'Reserved for Member 3 OCR/text extraction output',
    status          ENUM('active','pending','terminated','expired') NOT NULL DEFAULT 'pending',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_agreements_landlord FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_agreements_tenant   FOREIGN KEY (tenant_id)   REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_agreements_landlord (landlord_id),
    KEY idx_agreements_tenant (tenant_id),
    KEY idx_agreements_status (status),
    KEY idx_agreements_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: complaints
-- Purpose: Tenant/landlord complaints tied to an agreement. Supports
-- an optional voice recording and an AI-generated suggestion field
-- that Member 3's AI service fills in later.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS complaints;
CREATE TABLE complaints (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL COMMENT 'User who filed the complaint',
    agreement_id    INT UNSIGNED        NOT NULL,
    voice_file      VARCHAR(255)        NULL COMMENT 'Stored filename of uploaded voice complaint (optional)',
    complaint_text  TEXT                NULL,
    ai_suggestion   TEXT                NULL COMMENT 'Filled in later by Member 3 AI service',
    status          ENUM('open','in_review','resolved','rejected') NOT NULL DEFAULT 'open',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_user      FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_complaints_agreement FOREIGN KEY (agreement_id) REFERENCES agreements(id) ON DELETE CASCADE,
    KEY idx_complaints_user (user_id),
    KEY idx_complaints_agreement (agreement_id),
    KEY idx_complaints_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: repairs
-- Purpose: Maintenance/repair reports tied to an agreement, with
-- optional photo evidence and a priority level.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS repairs;
CREATE TABLE repairs (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reported_by         INT UNSIGNED        NOT NULL,
    agreement_id        INT UNSIGNED        NOT NULL,
    photo               VARCHAR(255)        NULL COMMENT 'Stored filename of uploaded repair photo (optional)',
    issue_description   TEXT                NOT NULL,
    priority            ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    estimated_cost      DECIMAL(12,2)       NULL,
    status              ENUM('reported','in_progress','completed','cancelled') NOT NULL DEFAULT 'reported',
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_repairs_user      FOREIGN KEY (reported_by)  REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_repairs_agreement FOREIGN KEY (agreement_id) REFERENCES agreements(id) ON DELETE CASCADE,
    KEY idx_repairs_reported_by (reported_by),
    KEY idx_repairs_agreement (agreement_id),
    KEY idx_repairs_status (status),
    KEY idx_repairs_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: payments
-- Purpose: Records rent payments made against an agreement. Prototype
-- only — no real payment gateway is integrated.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agreement_id            INT UNSIGNED        NOT NULL,
    payer_id                INT UNSIGNED        NOT NULL,
    amount                  DECIMAL(12,2)       NOT NULL,
    payment_date            DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_method          VARCHAR(50)         NOT NULL COMMENT 'e.g. QR, cash, bank_transfer',
    transaction_reference   VARCHAR(100)        NOT NULL,
    qr_receipt              VARCHAR(255)        NULL COMMENT 'Stored filename/path of generated QR receipt image (Member 5)',
    status                  ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    created_at              DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_agreement FOREIGN KEY (agreement_id) REFERENCES agreements(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_payer     FOREIGN KEY (payer_id)     REFERENCES users(id)      ON DELETE CASCADE,
    UNIQUE KEY uq_payments_txn_ref (transaction_reference),
    KEY idx_payments_agreement (agreement_id),
    KEY idx_payments_payer (payer_id),
    KEY idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: risk_scores
-- Purpose: Stores AI-generated tenant/agreement risk assessments.
-- Backend only stores/retrieves; Member 3 computes the actual score.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS risk_scores;
CREATE TABLE risk_scores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    agreement_id    INT UNSIGNED        NOT NULL,
    risk_level      ENUM('low','medium','high') NOT NULL,
    reason          TEXT                NULL,
    score           DECIMAL(5,2)        NOT NULL COMMENT 'Numeric score e.g. 0-100, set by AI service',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_risk_user      FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_risk_agreement FOREIGN KEY (agreement_id) REFERENCES agreements(id) ON DELETE CASCADE,
    KEY idx_risk_user (user_id),
    KEY idx_risk_agreement (agreement_id),
    KEY idx_risk_level (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABLE: notifications
-- Purpose: In-app notifications for users (rent reminders, payment
-- confirmations, complaint/repair updates, system messages).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    title           VARCHAR(150)        NOT NULL,
    message         TEXT                NOT NULL,
    type            ENUM('rent_reminder','payment','complaint','repair','system') NOT NULL DEFAULT 'system',
    is_read         TINYINT(1)          NOT NULL DEFAULT 0,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_notifications_user (user_id),
    KEY idx_notifications_type (type),
    KEY idx_notifications_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- DEMO / SEED DATA (development only — fictional data)
-- Password for ALL demo users below is: Test12345
-- Hash generated with PHP password_hash('Test12345', PASSWORD_BCRYPT)
-- =====================================================================

INSERT INTO users (name, phone, email, password, role, language) VALUES
('Ali Landlord',     '03002222222', 'ali.landlord@example.com','$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'landlord', 'en'),
('Sara Landlord',    '03003333333', 'sara.landlord@example.com','$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'landlord', 'ur'),
('Bilal Tenant',     '03004444444', 'bilal.tenant@example.com','$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant',   'en'),
('Ayesha Tenant',    '03005555555', 'ayesha.tenant@example.com','$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant',   'ur'),
('Usman Tenant',     '03006666666', 'usman.tenant@example.com','$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant',   'roman_ur');

-- [NEW v2] Bootstrap admin — since admin creation now requires an existing
-- logged-in admin (see backend/admin/create-admin.php), at least one admin
-- must be seeded directly here to avoid a chicken-and-egg problem on a
-- fresh install. This is the ONLY admin allowed to exist without having
-- been created through the app itself.
INSERT INTO admins (name, phone, email, password, status) VALUES
('Admin User', '03001111111', 'admin@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'active'),
('Suspended Admin (demo)', '03009911223', 'inactive.admin@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'inactive');

-- NOTE: The bcrypt hash above is illustrative. Run demo_seed.php (see /backend/demo_seed.php)
-- to regenerate real hashes locally with password_hash(), because bcrypt hashes are
-- salt-randomized per generation and hand-copied hashes here may not verify correctly
-- on every PHP build. demo_seed.php is the authoritative way to create demo accounts.

INSERT INTO agreements (landlord_id, tenant_id, rent_amount, due_date, penalty, status) VALUES
(1, 3, 25000.00, '2026-09-01', 500.00, 'active'),
(1, 4, 30000.00, '2026-08-20', 500.00, 'active'),
(2, 5, 18000.00, '2026-09-05', 300.00, 'pending');

INSERT INTO complaints (user_id, agreement_id, complaint_text, status) VALUES
(3, 1, 'Water leakage in the bathroom ceiling.', 'open'),
(4, 2, 'کرایہ داری معاہدے میں بجلی کے میٹر کا مسئلہ ہے۔', 'open');

INSERT INTO repairs (reported_by, agreement_id, issue_description, priority, estimated_cost, status) VALUES
(3, 1, 'Kitchen tap is broken and leaking constantly.', 'high', 3500.00, 'reported'),
(5, 3, 'Roman Urdu: geyser kaam nahi kar raha.', 'medium', 5000.00, 'reported');

INSERT INTO payments (agreement_id, payer_id, amount, payment_method, transaction_reference, status) VALUES
(1, 3, 25000.00, 'QR', 'TXN-DEMO-001', 'paid'),
(2, 4, 30000.00, 'bank_transfer', 'TXN-DEMO-002', 'pending');

INSERT INTO risk_scores (user_id, agreement_id, risk_level, reason, score) VALUES
(3, 1, 'low', 'Consistent on-time payment history.', 12.50),
(4, 2, 'medium', 'One late payment in the last 6 months.', 45.00);

INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(3, 'Rent Reminder', 'Your rent of 25000 is due soon.', 'rent_reminder', 0),
(3, 'Payment Received', 'Your payment of 25000 was recorded.', 'payment', 1),
(4, 'Complaint Update', 'Your complaint status has changed to open.', 'complaint', 0);
