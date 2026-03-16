-- ============================================================
-- Media Converter - Auth & Subscription Module
-- Run this SQL once on your database to set up the module
-- ============================================================

CREATE DATABASE IF NOT EXISTS mediaconverter_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediaconverter_db;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(100) NOT NULL,
    `email`             VARCHAR(191) NOT NULL UNIQUE,
    `password`          VARCHAR(255) NOT NULL,
    `email_verified`    TINYINT(1) DEFAULT 0,
    `verify_token`      VARCHAR(64) DEFAULT NULL,
    `reset_token`       VARCHAR(64) DEFAULT NULL,
    `reset_expires`     DATETIME DEFAULT NULL,
    `plan`              ENUM('free','pro','business') DEFAULT 'free',
    `plan_expires`      DATETIME DEFAULT NULL,
    `conversions_today` INT UNSIGNED DEFAULT 0,
    `conversions_total` INT UNSIGNED DEFAULT 0,
    `last_conversion_date` DATE DEFAULT NULL,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (`email`),
    INDEX idx_plan (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscriptions/orders table
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `plan`          ENUM('pro','business') NOT NULL,
    `status`        ENUM('active','cancelled','expired') DEFAULT 'active',
    `amount`        DECIMAL(10,2) NOT NULL,
    `currency`      VARCHAR(10) DEFAULT 'USD',
    `payment_ref`   VARCHAR(255) DEFAULT NULL,
    `starts_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at`    DATETIME DEFAULT NULL,
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX idx_user_id (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Conversion logs
CREATE TABLE IF NOT EXISTS `conversion_logs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `from_format`   VARCHAR(20) NOT NULL,
    `to_format`     VARCHAR(20) NOT NULL,
    `file_name`     VARCHAR(255) DEFAULT NULL,
    `file_size`     INT UNSIGNED DEFAULT NULL,
    `status`        ENUM('success','failed') DEFAULT 'success',
    `ip_address`    VARCHAR(45) DEFAULT NULL,
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX idx_user_id (`user_id`),
    INDEX idx_created_at (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
