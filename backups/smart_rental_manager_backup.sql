-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2026 at 01:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_rental_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash via password_hash()',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '03001111111', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'active', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(2, 'Suspended Admin (demo)', 'inactive.admin@example.com', '03009911223', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'inactive', '2026-08-27 20:23:50', '2026-08-27 20:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `agreements`
--

CREATE TABLE `agreements` (
  `id` int(10) UNSIGNED NOT NULL,
  `landlord_id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `rent_amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL COMMENT 'Recurring monthly due date (day tracked via DATE, logic reads day-of-month)',
  `penalty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `agreement_file` varchar(255) DEFAULT NULL COMMENT 'Stored filename of uploaded PDF/DOC/DOCX',
  `extracted_text` text DEFAULT NULL COMMENT 'Reserved for Member 3 OCR/text extraction output',
  `status` enum('active','pending','terminated','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agreements`
--

INSERT INTO `agreements` (`id`, `landlord_id`, `tenant_id`, `rent_amount`, `due_date`, `penalty`, `agreement_file`, `extracted_text`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 25000.00, '2026-09-01', 500.00, NULL, NULL, 'active', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(2, 1, 4, 30000.00, '2026-08-20', 500.00, NULL, NULL, 'active', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(3, 2, 5, 18000.00, '2026-09-05', 300.00, NULL, NULL, 'pending', '2026-08-27 20:23:50', '2026-08-27 20:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'User who filed the complaint',
  `agreement_id` int(10) UNSIGNED NOT NULL,
  `voice_file` varchar(255) DEFAULT NULL COMMENT 'Stored filename of uploaded voice complaint (optional)',
  `complaint_text` text DEFAULT NULL,
  `ai_suggestion` text DEFAULT NULL COMMENT 'Filled in later by Member 3 AI service',
  `status` enum('open','in_review','resolved','rejected') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `agreement_id`, `voice_file`, `complaint_text`, `ai_suggestion`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 'Water leakage in the bathroom ceiling.', NULL, 'open', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(2, 4, 2, NULL, 'کرایہ داری معاہدے میں بجلی کے میٹر کا مسئلہ ہے۔', NULL, 'open', '2026-08-27 20:23:50', '2026-08-27 20:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` enum('rent_reminder','payment','complaint','repair','system') NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 3, 'Rent Reminder', 'Your rent of 25000 is due soon.', 'rent_reminder', 0, '2026-08-27 20:23:51'),
(2, 3, 'Payment Received', 'Your payment of 25000 was recorded.', 'payment', 1, '2026-08-27 20:23:51'),
(3, 4, 'Complaint Update', 'Your complaint status has changed to open.', 'complaint', 0, '2026-08-27 20:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(150) NOT NULL COMMENT 'email address of the account requesting reset',
  `account_type` enum('user','admin') NOT NULL DEFAULT 'user' COMMENT 'user = tenant/landlord (users table), admin = admins table',
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `agreement_id` int(10) UNSIGNED NOT NULL,
  `payer_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL COMMENT 'e.g. QR, cash, bank_transfer',
  `transaction_reference` varchar(100) NOT NULL,
  `qr_receipt` varchar(255) DEFAULT NULL COMMENT 'Stored filename/path of generated QR receipt image (Member 5)',
  `status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `agreement_id`, `payer_id`, `amount`, `payment_date`, `payment_method`, `transaction_reference`, `qr_receipt`, `status`, `created_at`) VALUES
(1, 1, 3, 25000.00, '2026-08-27 20:23:51', 'QR', 'TXN-DEMO-001', NULL, 'paid', '2026-08-27 20:23:51'),
(2, 2, 4, 30000.00, '2026-08-27 20:23:51', 'bank_transfer', 'TXN-DEMO-002', NULL, 'pending', '2026-08-27 20:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `repairs`
--

CREATE TABLE `repairs` (
  `id` int(10) UNSIGNED NOT NULL,
  `reported_by` int(10) UNSIGNED NOT NULL,
  `agreement_id` int(10) UNSIGNED NOT NULL,
  `photo` varchar(255) DEFAULT NULL COMMENT 'Stored filename of uploaded repair photo (optional)',
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `estimated_cost` decimal(12,2) DEFAULT NULL,
  `status` enum('reported','in_progress','completed','cancelled') NOT NULL DEFAULT 'reported',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `repairs`
--

INSERT INTO `repairs` (`id`, `reported_by`, `agreement_id`, `photo`, `issue_description`, `priority`, `estimated_cost`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 'Kitchen tap is broken and leaking constantly.', 'high', 3500.00, 'reported', '2026-08-27 20:23:51', '2026-08-27 20:23:51'),
(2, 5, 3, NULL, 'Roman Urdu: geyser kaam nahi kar raha.', 'medium', 5000.00, 'reported', '2026-08-27 20:23:51', '2026-08-27 20:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `risk_scores`
--

CREATE TABLE `risk_scores` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `agreement_id` int(10) UNSIGNED NOT NULL,
  `risk_level` enum('low','medium','high') NOT NULL,
  `reason` text DEFAULT NULL,
  `score` decimal(5,2) NOT NULL COMMENT 'Numeric score e.g. 0-100, set by AI service',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `risk_scores`
--

INSERT INTO `risk_scores` (`id`, `user_id`, `agreement_id`, `risk_level`, `reason`, `score`, `created_at`) VALUES
(1, 3, 1, 'low', 'Consistent on-time payment history.', 12.50, '2026-08-27 20:23:51'),
(2, 4, 2, 'medium', 'One late payment in the last 6 months.', 45.00, '2026-08-27 20:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash via password_hash()',
  `role` enum('tenant','landlord') NOT NULL DEFAULT 'tenant',
  `language` enum('en','ur','roman_ur') NOT NULL DEFAULT 'en',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `role`, `language`, `created_at`, `updated_at`) VALUES
(1, 'Ali Landlord', '03002222222', 'ali.landlord@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'landlord', 'en', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(2, 'Sara Landlord', '03003333333', 'sara.landlord@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'landlord', 'ur', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(3, 'Bilal Tenant', '03004444444', 'bilal.tenant@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant', 'en', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(4, 'Ayesha Tenant', '03005555555', 'ayesha.tenant@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant', 'ur', '2026-08-27 20:23:50', '2026-08-27 20:23:50'),
(5, 'Usman Tenant', '03006666666', 'usman.tenant@example.com', '$2y$10$8K1p/a0dURXAM7fZfeM.p.q9j/N.zTIvhOKtEFY6E5x9vI2rW.LMy', 'tenant', 'roman_ur', '2026-08-27 20:23:50', '2026-08-27 20:23:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_email` (`email`),
  ADD KEY `idx_admins_status` (`status`);

--
-- Indexes for table `agreements`
--
ALTER TABLE `agreements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agreements_landlord` (`landlord_id`),
  ADD KEY `idx_agreements_tenant` (`tenant_id`),
  ADD KEY `idx_agreements_status` (`status`),
  ADD KEY `idx_agreements_due_date` (`due_date`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaints_user` (`user_id`),
  ADD KEY `idx_complaints_agreement` (`agreement_id`),
  ADD KEY `idx_complaints_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_type` (`type`),
  ADD KEY `idx_notifications_is_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_identifier` (`identifier`),
  ADD KEY `idx_password_resets_otp` (`otp`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payments_txn_ref` (`transaction_reference`),
  ADD KEY `idx_payments_agreement` (`agreement_id`),
  ADD KEY `idx_payments_payer` (`payer_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Indexes for table `repairs`
--
ALTER TABLE `repairs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_repairs_reported_by` (`reported_by`),
  ADD KEY `idx_repairs_agreement` (`agreement_id`),
  ADD KEY `idx_repairs_status` (`status`),
  ADD KEY `idx_repairs_priority` (`priority`);

--
-- Indexes for table `risk_scores`
--
ALTER TABLE `risk_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_risk_user` (`user_id`),
  ADD KEY `idx_risk_agreement` (`agreement_id`),
  ADD KEY `idx_risk_level` (`risk_level`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_phone` (`phone`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `agreements`
--
ALTER TABLE `agreements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `repairs`
--
ALTER TABLE `repairs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `risk_scores`
--
ALTER TABLE `risk_scores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreements`
--
ALTER TABLE `agreements`
  ADD CONSTRAINT `fk_agreements_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agreements_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaints_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `agreements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_complaints_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `agreements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_payer` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repairs`
--
ALTER TABLE `repairs`
  ADD CONSTRAINT `fk_repairs_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `agreements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_repairs_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `risk_scores`
--
ALTER TABLE `risk_scores`
  ADD CONSTRAINT `fk_risk_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `agreements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_risk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
