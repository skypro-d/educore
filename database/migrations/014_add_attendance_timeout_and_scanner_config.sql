-- ============================================================================
-- Migration 014: Attendance Time Out, Scanner Mode & Debounce Configurations
-- Enables native HIPPOINT X7-1000 USB automatic IN/OUT tracking
-- ============================================================================

ALTER TABLE `attendance`
  ADD COLUMN `time_out` TIME DEFAULT NULL AFTER `time_in`,
  ADD COLUMN `timeout_alert_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `alert_sent`,
  ADD COLUMN `scan_method` ENUM('qr_usb', 'qr_camera', 'manual') NOT NULL DEFAULT 'qr_usb' AFTER `timeout_alert_sent`;

INSERT INTO `app_configs` (`setting_key`, `setting_value`) VALUES
  ('attendance_debounce_minutes', '15'),
  ('attendance_auto_toggle_inout', '1'),
  ('attendance_checkout_sms_enabled', '1'),
  ('attendance_checkout_email_enabled', '1'),
  ('attendance_scanner_audio_feedback', '1'),
  ('attendance_scanner_auto_reset_seconds', '3')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
