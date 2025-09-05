-- Laravel version: uma_musume_planner.sql
-- Database schema for Uma Musume Race Planner (Laravel migration compatible)
-- Author: IzzatFirdaus
-- Last Updated: 2025-08-04

-- Create DB if not exists, and switch to it
CREATE DATABASE IF NOT EXISTS `uma_musume_planner` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uma_musume_planner`;

-- ===========================
-- USERS & AUTH
-- ===========================
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` VARCHAR(255) PRIMARY KEY,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================
-- CACHE & JOBS (Framework)
-- ===========================
CREATE TABLE IF NOT EXISTS `cache` (
    `key` VARCHAR(255) PRIMARY KEY,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` VARCHAR(255) PRIMARY KEY,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(255) NOT NULL UNIQUE,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================
-- APP LOOKUP TABLES
-- ===========================
CREATE TABLE IF NOT EXISTS `moods` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `label` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `conditions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `label` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `strategies` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `label` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================
-- CORE PLANNER TABLES
-- ===========================

CREATE TABLE IF NOT EXISTS `plans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `plan_title` VARCHAR(255),
    `turn_before` INT,
    `race_name` VARCHAR(255),
    `name` VARCHAR(255),
    `career_stage` ENUM('predebut', 'junior', 'classic', 'senior', 'finale'),
    `class` ENUM('debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend'),
    `time_of_day` VARCHAR(50),
    `month` VARCHAR(50),
    `total_available_skill_points` INT,
    `acquire_skill` ENUM('YES', 'NO') DEFAULT 'NO',
    `mood_id` BIGINT UNSIGNED NULL,
    `condition_id` BIGINT UNSIGNED NULL,
    `energy` TINYINT,
    `race_day` ENUM('yes', 'no') DEFAULT 'no',
    `goal` VARCHAR(255),
    `strategy_id` BIGINT UNSIGNED NULL,
    `growth_rate_speed` INT DEFAULT 0,
    `growth_rate_stamina` INT DEFAULT 0,
    `growth_rate_power` INT DEFAULT 0,
    `growth_rate_guts` INT DEFAULT 0,
    `growth_rate_wit` INT DEFAULT 0,
    `status` ENUM('Planning', 'Active', 'Finished', 'Draft', 'Abandoned') DEFAULT 'Planning',
    `source` VARCHAR(255),
    `trainee_image_path` VARCHAR(255),
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mood_id`) REFERENCES `moods`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`condition_id`) REFERENCES `conditions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`strategy_id`) REFERENCES `strategies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attributes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `attribute_name` VARCHAR(50) NOT NULL,
    `value` INT NOT NULL,
    `grade` VARCHAR(10),
    UNIQUE KEY `uq_plan_attribute` (`plan_id`, `attribute_name`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `skill_reference` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `skill_name` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `stat_type` VARCHAR(20),
    `best_for` TEXT,
    `tag` VARCHAR(5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `skills` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `skill_reference_id` BIGINT UNSIGNED NOT NULL,
    `sp_cost` VARCHAR(50),
    `acquired` ENUM('yes', 'no') DEFAULT 'no',
    `tag` VARCHAR(50),
    `notes` TEXT,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skill_reference_id`) REFERENCES `skill_reference`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `race_predictions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `race_name` VARCHAR(255),
    `venue` VARCHAR(255),
    `ground` VARCHAR(50),
    `distance` VARCHAR(50),
    `track_condition` VARCHAR(50),
    `direction` VARCHAR(50),
    `speed` VARCHAR(10) DEFAULT '○',
    `stamina` VARCHAR(10) DEFAULT '○',
    `power` VARCHAR(10) DEFAULT '○',
    `guts` VARCHAR(10) DEFAULT '○',
    `wit` VARCHAR(10) DEFAULT '○',
    `comment` TEXT,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `description` TEXT NOT NULL,
    `icon_class` VARCHAR(50) DEFAULT 'bi-info-circle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `terrain_grades` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `terrain` VARCHAR(50) NOT NULL,
    `grade` VARCHAR(10),
    UNIQUE KEY `uq_plan_terrain` (`plan_id`, `terrain`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `distance_grades` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `distance` VARCHAR(50) NOT NULL,
    `grade` VARCHAR(10),
    UNIQUE KEY `uq_plan_distance` (`plan_id`, `distance`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `style_grades` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `style` VARCHAR(50) NOT NULL,
    `grade` VARCHAR(10),
    UNIQUE KEY `uq_plan_style` (`plan_id`, `style`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `goals` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `goal` VARCHAR(255),
    `result` VARCHAR(255) DEFAULT 'Pending',
    UNIQUE KEY `uq_plan_goal` (`plan_id`, `goal`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `turns` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` BIGINT UNSIGNED NOT NULL,
    `turn_number` INT NOT NULL,
    `speed` INT DEFAULT 0,
    `stamina` INT DEFAULT 0,
    `power` INT DEFAULT 0,
    `guts` INT DEFAULT 0,
    `wit` INT DEFAULT 0,
    UNIQUE KEY `uq_plan_turn` (`plan_id`, `turn_number`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
