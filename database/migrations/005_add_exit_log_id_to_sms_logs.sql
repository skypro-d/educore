-- ============================================================================
-- Migration 005: Add exit_log_id column to sms_logs table
-- Created: 2026-08-30
-- Ensures SMS logging links correctly to student exit & pickup records
-- ============================================================================

ALTER TABLE `sms_logs` ADD COLUMN `exit_log_id` INT UNSIGNED DEFAULT NULL AFTER `attendance_id`;
ALTER TABLE `sms_logs` ADD KEY `idx_sms_exit` (`exit_log_id`);
