-- ============================================================================
-- Migration 002: Student Exit Verification & Authorized Pickups
-- Created: 2026-08-29
-- Multi-Tenant Compatible: Includes school_id and proper constraints
-- ============================================================================

-- 1. School Gates Table
CREATE TABLE IF NOT EXISTS `school_gates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `gate_name` VARCHAR(100) NOT NULL,
  `gate_code` VARCHAR(30) DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gates_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student Authorized Pickups Table
CREATE TABLE IF NOT EXISTS `student_authorized_pickups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `student_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(140) NOT NULL,
  `relationship` VARCHAR(80) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `id_card_number` VARCHAR(80) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pickup_student` (`student_id`),
  KEY `idx_pickup_school` (`school_id`),
  CONSTRAINT `fk_pickup_student` FOREIGN KEY (`student_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Student Exit Logs Table
CREATE TABLE IF NOT EXISTS `student_exit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `student_id` INT UNSIGNED NOT NULL,
  `attendance_id` INT UNSIGNED DEFAULT NULL,
  `pickup_person_id` INT UNSIGNED DEFAULT NULL,
  `pickup_person_name` VARCHAR(140) DEFAULT NULL,
  `exit_type` ENUM('normal','early','manual') NOT NULL DEFAULT 'normal',
  `exit_reason` VARCHAR(150) DEFAULT NULL,
  `exit_reason_notes` TEXT DEFAULT NULL,
  `exit_date` DATE NOT NULL,
  `exit_time` TIME NOT NULL,
  `exited_at` DATETIME NOT NULL,
  `gate_id` INT UNSIGNED DEFAULT NULL,
  `gate_name` VARCHAR(100) DEFAULT NULL,
  `device_id` INT UNSIGNED DEFAULT NULL,
  `scanned_by` INT UNSIGNED DEFAULT NULL,
  `scanned_by_name` VARCHAR(120) DEFAULT NULL,
  `scan_method` ENUM('qr_camera','qr_usb','manual','api_device') NOT NULL DEFAULT 'qr_usb',
  `qr_token` VARCHAR(100) DEFAULT NULL,
  `verification_status` ENUM('verified','flagged','manual_override') NOT NULL DEFAULT 'verified',
  `sms_status` ENUM('sent','failed','pending','skipped') NOT NULL DEFAULT 'pending',
  `sms_log_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_exit_day` (`school_id`, `student_id`, `exit_date`),
  KEY `idx_exit_date` (`exit_date`),
  KEY `idx_exit_school_date` (`school_id`, `exit_date`),
  KEY `idx_exit_student` (`student_id`),
  KEY `idx_exit_type` (`exit_type`),
  KEY `idx_exit_gate` (`gate_id`),
  KEY `idx_exit_scanned_by` (`scanned_by`),
  CONSTRAINT `fk_exit_student` FOREIGN KEY (`student_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exit_attendance` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exit_pickup` FOREIGN KEY (`pickup_person_id`) REFERENCES `student_authorized_pickups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exit_gate` FOREIGN KEY (`gate_id`) REFERENCES `school_gates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exit_scanned_by` FOREIGN KEY (`scanned_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add exit_log_id column to sms_logs if missing
ALTER TABLE `sms_logs` ADD COLUMN `exit_log_id` INT UNSIGNED DEFAULT NULL AFTER `attendance_id`;
ALTER TABLE `sms_logs` ADD KEY `idx_sms_exit` (`exit_log_id`);

-- 5. Seed Default School Gates for School 1 if none exist
INSERT IGNORE INTO `school_gates` (`id`, `school_id`, `gate_name`, `gate_code`, `location`, `status`, `created_at`)
VALUES 
(1, 1, 'Main Gate', 'GATE-01', 'Front Entrance', 'active', NOW()),
(2, 1, 'Secondary Gate', 'GATE-02', 'East Wing', 'active', NOW()),
(3, 1, 'Staff & Bus Gate', 'GATE-03', 'Transit Area', 'active', NOW());

-- 5. Seed App Configs for Exit Tracking & SMS
INSERT INTO `app_configs` (`setting_key`, `setting_value`) VALUES
('exit_tracking_enabled', '1'),
('exit_normal_time', '14:30'),
('exit_sms_enabled', '1'),
('early_exit_sms_enabled', '1'),
('exit_require_pickup_verification', '0'),
('exit_allow_manual', '1'),
('exit_require_entry_record', '0'),
('exit_sms_template_normal', 'EduCore Alert: {student_name} has left {school_name} today at {exit_time}. Thank you.'),
('exit_sms_template_early', 'EduCore Alert: {student_name} has left {school_name} today at {exit_time} as an early exit ({reason}).')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
