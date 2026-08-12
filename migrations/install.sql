-- ============================================================
-- VICOBA Standalone Application — Database Schema
-- Run this SQL in your MySQL database to create all tables
-- Replace 'vc_' prefix if you changed it in config.php
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

-- ── USERS (replaces wp_users) ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_users` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username`     VARCHAR(60)  NOT NULL,
    `email`        VARCHAR(100) NOT NULL,
    `password`     VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(250) NOT NULL DEFAULT '',
    `role`         ENUM('super_admin','group_admin','secretary','treasurer','member') NOT NULL DEFAULT 'member',
    `group_id`     INT UNSIGNED DEFAULT NULL,
    `status`       ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email`    (`email`),
    INDEX `idx_role`     (`role`),
    INDEX `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── GROUPS ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_groups` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`                 VARCHAR(200) NOT NULL,
    `registration_number`  VARCHAR(100) DEFAULT NULL,
    `region`               VARCHAR(100) DEFAULT NULL,
    `district`             VARCHAR(100) DEFAULT NULL,
    `ward`                 VARCHAR(100) DEFAULT NULL,
    `founding_date`        DATE         DEFAULT NULL,
    `currency`             VARCHAR(10)  NOT NULL DEFAULT 'TZS',
    `share_price`          DECIMAL(15,2) NOT NULL DEFAULT 1000.00,
    `loan_interest_rate`   DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
    `loan_interest_type`   ENUM('flat','reducing_balance') NOT NULL DEFAULT 'flat',
    `max_loan_multiplier`  TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `max_loan_period`      TINYINT UNSIGNED NOT NULL DEFAULT 12,
    `social_fund_per_meeting` DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
    `penalty_interest_multiplier` DECIMAL(4,2) NOT NULL DEFAULT 1.50,
    `cycle_start`          DATE DEFAULT NULL,
    `cycle_end`            DATE DEFAULT NULL,
    `status`               ENUM('active','suspended','completed') NOT NULL DEFAULT 'active',
    `created_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MEMBERS ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_members` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`          INT UNSIGNED NOT NULL,
    `user_id`           INT UNSIGNED NOT NULL,
    `member_number`     VARCHAR(30)  NOT NULL,
    `full_name`         VARCHAR(200) NOT NULL,
    `phone`             VARCHAR(20)  DEFAULT NULL,
    `email`             VARCHAR(100) DEFAULT NULL,
    `nida_number_enc`   TEXT         DEFAULT NULL,  -- AES-256 encrypted
    `dob`               DATE         DEFAULT NULL,
    `gender`            ENUM('male','female','other') DEFAULT NULL,
    `address`           VARCHAR(500) DEFAULT NULL,
    `role`              ENUM('super_admin','group_admin','secretary','treasurer','member') NOT NULL DEFAULT 'member',
    `status`            ENUM('active','suspended','alumni') NOT NULL DEFAULT 'active',
    `emergency_contact` VARCHAR(200) DEFAULT NULL,
    `emergency_phone`   VARCHAR(20)  DEFAULT NULL,
    `photo_url`         VARCHAR(500) DEFAULT NULL,
    `kyc_doc_url`       VARCHAR(500) DEFAULT NULL,
    `kyc_verified`      TINYINT(1)   NOT NULL DEFAULT 0,
    `joined_date`       DATE         DEFAULT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_member_num` (`group_id`, `member_number`),
    INDEX `idx_group_id` (`group_id`),
    INDEX `idx_user_id`  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SHARES ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_shares` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`        INT UNSIGNED NOT NULL,
    `member_id`       INT UNSIGNED NOT NULL,
    `meeting_id`      INT UNSIGNED DEFAULT NULL,
    `share_count`     INT UNSIGNED NOT NULL DEFAULT 1,
    `share_price`     DECIMAL(15,2) NOT NULL,
    `total_amount`    DECIMAL(15,2) NOT NULL,
    `payment_method`  ENUM('cash','mobile_money','bank') NOT NULL DEFAULT 'cash',
    `payment_reference` VARCHAR(100) DEFAULT NULL,
    `payment_date`    DATE NOT NULL,
    `recorded_by`     INT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_member` (`group_id`, `member_id`),
    INDEX `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LOANS ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_loans` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `loan_code`             VARCHAR(30) NOT NULL,
    `group_id`              INT UNSIGNED NOT NULL,
    `member_id`             INT UNSIGNED NOT NULL,
    `principal_amount`      DECIMAL(15,2) NOT NULL,
    `interest_rate`         DECIMAL(5,2)  NOT NULL,
    `interest_type`         ENUM('flat','reducing_balance') NOT NULL DEFAULT 'flat',
    `repayment_period_months` TINYINT UNSIGNED NOT NULL DEFAULT 6,
    `monthly_installment`   DECIMAL(15,2) DEFAULT NULL,
    `total_payable`         DECIMAL(15,2) DEFAULT NULL,
    `amount_paid`           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_remaining`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `purpose`               VARCHAR(500) DEFAULT NULL,
    `status`                ENUM('pending_guarantors','pending_treasurer','pending_chairman','active','overdue','completed','rejected') NOT NULL DEFAULT 'pending_guarantors',
    `disbursed_at`          DATETIME  DEFAULT NULL,
    `due_date`              DATE      DEFAULT NULL,
    `last_payment_date`     DATE      DEFAULT NULL,
    `penalty_applied`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `approved_by`           INT UNSIGNED DEFAULT NULL,
    `disbursed_by`          INT UNSIGNED DEFAULT NULL,
    `created_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_loan_code` (`loan_code`),
    INDEX `idx_group_member` (`group_id`, `member_id`),
    INDEX `idx_status`       (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LOAN GUARANTORS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_loan_guarantors` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `loan_id`     INT UNSIGNED NOT NULL,
    `guarantor_member_id` INT UNSIGNED NOT NULL,
    `status`      ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    `responded_at` DATETIME DEFAULT NULL,
    `notes`       TEXT DEFAULT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_loan_guarantor` (`loan_id`, `guarantor_member_id`),
    INDEX `idx_guarantor` (`guarantor_member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LOAN REPAYMENTS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_loan_repayments` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `loan_id`          INT UNSIGNED NOT NULL,
    `payment_date`     DATE NOT NULL,
    `amount_paid`      DECIMAL(15,2) NOT NULL,
    `principal_paid`   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `interest_paid`    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `balance_after`    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_method`   ENUM('cash','mobile_money','bank') NOT NULL DEFAULT 'cash',
    `payment_reference` VARCHAR(100) DEFAULT NULL,
    `received_by`      INT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_loan_id` (`loan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── FINE TYPES ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_fine_types` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`    INT UNSIGNED NOT NULL,
    `name`        VARCHAR(200) NOT NULL,
    `amount`      DECIMAL(10,2) NOT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── FINES ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_fines` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`      INT UNSIGNED NOT NULL,
    `member_id`     INT UNSIGNED NOT NULL,
    `fine_type_id`  INT UNSIGNED DEFAULT NULL,
    `meeting_id`    INT UNSIGNED DEFAULT NULL,
    `amount`        DECIMAL(10,2) NOT NULL,
    `reason`        VARCHAR(500) NOT NULL,
    `status`        ENUM('pending','paid','waived') NOT NULL DEFAULT 'pending',
    `paid_at`       DATETIME DEFAULT NULL,
    `payment_method` ENUM('cash','mobile_money','bank') DEFAULT NULL,
    `issued_by`     INT UNSIGNED NOT NULL,
    `waived_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_member` (`group_id`, `member_id`),
    INDEX `idx_status`       (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MEETINGS ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_meetings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`      INT UNSIGNED NOT NULL,
    `meeting_code`  VARCHAR(30) NOT NULL,
    `meeting_date`  DATE NOT NULL,
    `location`      VARCHAR(300) DEFAULT NULL,
    `agenda`        TEXT DEFAULT NULL,
    `minutes`       TEXT DEFAULT NULL,
    `status`        ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    `absence_fine_amount` DECIMAL(10,2) DEFAULT NULL,
    `created_by`    INT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_group_id`    (`group_id`),
    INDEX `idx_meeting_date` (`meeting_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MEETING ATTENDANCE ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_meeting_attendance` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `meeting_id`  INT UNSIGNED NOT NULL,
    `member_id`   INT UNSIGNED NOT NULL,
    `status`      ENUM('present','absent','excused') NOT NULL DEFAULT 'present',
    `recorded_by` INT UNSIGNED NOT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_meeting_member` (`meeting_id`, `member_id`),
    INDEX `idx_member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SOCIAL FUND ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_social_fund` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`      INT UNSIGNED NOT NULL,
    `member_id`     INT UNSIGNED DEFAULT NULL,
    `meeting_id`    INT UNSIGNED DEFAULT NULL,
    `type`          ENUM('contribution','disbursement','expense') NOT NULL,
    `amount`        DECIMAL(15,2) NOT NULL,
    `reason`        VARCHAR(500) DEFAULT NULL,
    `status`        ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
    `requested_by`  INT UNSIGNED DEFAULT NULL,
    `approved_by`   INT UNSIGNED DEFAULT NULL,
    `approved_at`   DATETIME DEFAULT NULL,
    `payment_method` ENUM('cash','mobile_money','bank') DEFAULT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_id` (`group_id`),
    INDEX `idx_type`     (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TRANSACTIONS (General Ledger) ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_transactions` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_code`  VARCHAR(30) NOT NULL,
    `group_id`          INT UNSIGNED NOT NULL,
    `member_id`         INT UNSIGNED DEFAULT NULL,
    `type`              VARCHAR(50) NOT NULL,
    `amount`            DECIMAL(15,2) NOT NULL,
    `payment_method`    ENUM('cash','mobile_money','bank') NOT NULL DEFAULT 'cash',
    `payment_reference` VARCHAR(100) DEFAULT NULL,
    `description`       VARCHAR(500) DEFAULT NULL,
    `account`           ENUM('cash','mobile_money','bank') NOT NULL DEFAULT 'cash',
    `recorded_by`       INT UNSIGNED NOT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_txn_code` (`transaction_code`),
    INDEX `idx_group_date` (`group_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SHARE-OUTS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_shareouts` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`          INT UNSIGNED NOT NULL,
    `shareout_date`     DATE NOT NULL,
    `total_shares_pool` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_interest_pool` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_fines_pool`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `operating_costs`   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `bad_debt_provision` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `net_distributable` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_shares`      INT UNSIGNED NOT NULL DEFAULT 0,
    `status`            ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    `finalized_by`      INT UNSIGNED DEFAULT NULL,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SHARE-OUT DETAILS ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_shareout_details` (
    `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shareout_id`           INT UNSIGNED NOT NULL,
    `member_id`             INT UNSIGNED NOT NULL,
    `total_member_shares`   INT UNSIGNED NOT NULL DEFAULT 0,
    `share_ratio`           DECIMAL(10,8) NOT NULL DEFAULT 0.00000000,
    `gross_payout`          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `active_loan_deduction` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `net_payout`            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `is_paid`               TINYINT(1)    NOT NULL DEFAULT 0,
    `paid_at`               DATETIME DEFAULT NULL,
    INDEX `idx_shareout_id` (`shareout_id`),
    INDEX `idx_member_id`   (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── NOTIFICATIONS ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_notifications` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`   INT UNSIGNED DEFAULT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `title`      VARCHAR(200) NOT NULL,
    `message`    TEXT NOT NULL,
    `type`       VARCHAR(50) DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_unread` (`user_id`, `is_read`),
    INDEX `idx_group_id`    (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── AUDIT LOG ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vc_audit_log` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id`     INT UNSIGNED DEFAULT NULL,
    `user_id`      INT UNSIGNED NOT NULL,
    `action`       VARCHAR(100) NOT NULL,
    `entity_type`  VARCHAR(50)  DEFAULT NULL,
    `entity_id`    INT UNSIGNED DEFAULT NULL,
    `old_value`    TEXT DEFAULT NULL,
    `new_value`    TEXT DEFAULT NULL,
    `ip_address`   VARCHAR(45) DEFAULT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_group_action` (`group_id`, `action`),
    INDEX `idx_user_id`      (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DEFAULT SUPER ADMIN USER ─────────────────────────────────────────────────
-- Password: Admin@1234 (CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN!)
INSERT IGNORE INTO `vc_users` (`username`, `email`, `password`, `display_name`, `role`, `status`)
VALUES ('admin', 'admin@vicoba.local', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin', 'active');

-- ── DEFAULT FINE TYPES (seeded on group creation via PHP) ────────────────────
-- These will be inserted automatically when a new group is created
