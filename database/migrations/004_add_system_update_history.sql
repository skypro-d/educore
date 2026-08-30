-- ============================================================================
-- Migration 004: System Update History Table
-- Description: Tracks local software upgrades, rollbacks, and backups
-- ============================================================================

CREATE TABLE IF NOT EXISTS `system_update_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_version` VARCHAR(32) NOT NULL,
  `to_version` VARCHAR(32) NOT NULL,
  `status` ENUM('started', 'completed', 'rolled_back', 'failed') NOT NULL DEFAULT 'started',
  `backup_path` VARCHAR(255) DEFAULT NULL,
  `backup_size_bytes` BIGINT UNSIGNED DEFAULT NULL,
  `executed_migrations` TEXT DEFAULT NULL,
  `log_summary` TEXT DEFAULT NULL,
  `initiated_by` VARCHAR(64) DEFAULT 'system_admin',
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_update_status` (`status`),
  KEY `idx_update_date` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
