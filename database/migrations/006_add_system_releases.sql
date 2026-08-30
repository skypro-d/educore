-- ============================================================================
-- Migration 006: System Releases Catalog Table
-- Created: 2026-08-30
-- Stores published EduCore PHP application releases registered by GitHub Actions.
-- Distinct from system_updates (POS firmware OTA) -- this tracks the web app.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `system_releases` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `version`           VARCHAR(32)     NOT NULL,
    `download_url`      VARCHAR(1024)   NOT NULL,
    `download_file`     VARCHAR(255)    NOT NULL,
    `sha256`            CHAR(64)        NOT NULL,
    `signature`         VARCHAR(128)    DEFAULT NULL,
    `release_channel`   ENUM('stable','beta','canary') NOT NULL DEFAULT 'stable',
    `mandatory`         TINYINT(1)      NOT NULL DEFAULT 0,
    `min_php_version`   VARCHAR(16)     NOT NULL DEFAULT '8.3.0',
    `min_mysql_version` VARCHAR(16)     NOT NULL DEFAULT '8.0.0',
    `release_notes`     TEXT            DEFAULT NULL,
    `is_published`      TINYINT(1)      NOT NULL DEFAULT 1,
    `released_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_release_version` (`version`),
    KEY `idx_releases_channel_published` (`release_channel`, `is_published`, `released_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
