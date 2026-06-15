-- =============================================================
-- iSkout Database Schema — iskout_db
-- Run this in phpMyAdmin or MySQL CLI after creating the DB
-- =============================================================

CREATE DATABASE IF NOT EXISTS `iskout_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `iskout_db`;

-- ── Users ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `webmail`       VARCHAR(120)     NOT NULL,
  `password_hash` VARCHAR(255)     NOT NULL,
  `is_verified`   TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_webmail` (`webmail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── OTP Tokens ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `otp_tokens` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `webmail`    VARCHAR(120)  NOT NULL,
  `token`      CHAR(4)       NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_webmail_token` (`webmail`, `token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Remember Tokens ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `token`      VARCHAR(64)   NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `fk_rt_user` (`user_id`),
  CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Listings ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `listings` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120)  NOT NULL,
  `description` TEXT,
  `location`    VARCHAR(200)  NOT NULL,
  `zone`        ENUM('inside','outside') NOT NULL DEFAULT 'outside',
  `open_time`   TIME,
  `close_time`  TIME,
  `image_path`  VARCHAR(255)  DEFAULT NULL,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Listing Categories ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `listing_categories` (
  `listing_id` INT UNSIGNED NOT NULL,
  `category`   ENUM('food','study','print','rentals','supplies','repair') NOT NULL,
  PRIMARY KEY (`listing_id`, `category`),
  CONSTRAINT `fk_lc_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Listing Tags ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `listing_tags` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `listing_id` INT UNSIGNED  NOT NULL,
  `tag`        VARCHAR(80)   NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lt_listing` (`listing_id`),
  CONSTRAINT `fk_lt_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Reports ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reports` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id`           INT UNSIGNED NOT NULL,
  `reported_by_user_id`  INT UNSIGNED NOT NULL,
  `reason`               VARCHAR(100) NOT NULL,
  `details`              TEXT,
  `status`               ENUM('pending','reviewed') NOT NULL DEFAULT 'pending',
  `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rpt_listing` (`listing_id`),
  KEY `fk_rpt_user`    (`reported_by_user_id`),
  CONSTRAINT `fk_rpt_listing` FOREIGN KEY (`listing_id`)          REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rpt_user`    FOREIGN KEY (`reported_by_user_id`) REFERENCES `users`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- SEED DATA — Sample listings for immediate demo
-- =============================================================
INSERT INTO `listings` (`id`, `name`, `description`, `location`, `zone`, `open_time`, `close_time`, `image_path`) VALUES
(1, 'Kape Kuripot', 'Budget-friendly coffee shop chain designed for students. Famous for its ultra-affordable menu starting under ₱50. Cozy study-friendly ambiance with free WiFi and no time limit.', 'PUP Main – Lagoon Area (Stall 3)', 'outside', '08:00:00', '19:00:00', NULL),
(2, 'Tindahan ng Papel', 'Your one-stop school supplies store near PUP. Carries engineering supplies, lanyards, notebooks, and tingi items.', 'Sta. Mesa, beside Angels Burger', 'outside', '08:00:00', '21:00:00', NULL),
(3, 'Print, Xerox, Bawang, Toyo', 'Affordable printing and binding shop. Offers piso print, rush hardbound, photocopy, and all types of binding.', 'Sta. Mesa, beside 7-Eleven Teresa', 'outside', '07:00:00', '19:00:00', NULL),
(4, 'Study Hub – Library Annex', 'Free study area with fast WiFi, multiple power sockets, and air conditioning. No time limit for PUP students.', 'PUP Main – Library Building Annex', 'inside', '08:00:00', '20:00:00', NULL),
(5, 'Hardbound Express', 'Fast turnaround thesis and book binding services. Rush hardbound done in as little as 2 hours.', 'Sta. Mesa, near PUP Gate 2', 'outside', '08:00:00', '17:00:00', NULL),
(6, 'Iskolar Supplies Co.', 'Complete student essentials — uniforms, PUP lanyards, ID holders, and engineering materials. Tingi available!', 'PUP Main – Covered Walk (Stall 5)', 'inside', '08:00:00', '17:00:00', NULL),
(7, 'TechFix PUP', 'Phone and gadget repair shop catering to PUP students. Same-day fix available for most repairs at student rates.', 'Sta. Mesa, near PUP Gate 1', 'outside', '09:00:00', '17:00:00', NULL);

INSERT INTO `listing_categories` (`listing_id`, `category`) VALUES
(1, 'food'), (1, 'study'),
(2, 'supplies'),
(3, 'print'), (3, 'supplies'),
(4, 'study'),
(5, 'print'),
(6, 'supplies'),
(7, 'repair');

INSERT INTO `listing_tags` (`listing_id`, `tag`) VALUES
(1, 'Free WiFi'), (1, 'Seating Available'), (1, 'Airconditioned'), (1, 'No Time Limit'), (1, 'Under 100'),
(2, 'Tingi Available'), (2, 'Lanyards'), (2, 'Engineering Supplies'),
(3, 'Piso Print'), (3, 'Rush Hardbound'), (3, 'Photocopy'), (3, 'Book Binding'), (3, 'Tingi Available'),
(4, 'Free WiFi'), (4, 'Sockets Available'), (4, 'Airconditioned'), (4, 'No Time Limit'),
(5, 'Rush Hardbound'), (5, 'Book Binding'), (5, 'Photocopy'),
(6, 'Tingi Available'), (6, 'Uniforms'), (6, 'Lanyards'), (6, 'Engineering Supplies'),
(7, 'Same-Day Fix'), (7, 'Student Rates'), (7, 'Android/iOS'), (7, 'Laptop Specialist');
