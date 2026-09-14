-- Migration 010: Parent Attendance Notifications (SMS, Email, WhatsApp)
-- Additive feature: Extends parent contact details and notification logs without touching existing SMS tables

CREATE TABLE IF NOT EXISTS `attendance_notification_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `student_id` int unsigned NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `attendance_id` int unsigned DEFAULT NULL,
  `exit_log_id` int unsigned DEFAULT NULL,
  `notification_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attendance',
  `channel` enum('sms','email','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` enum('check_in','check_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_identifier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_response` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_anl_school` (`school_id`),
  KEY `idx_anl_student` (`student_id`),
  KEY `idx_anl_status` (`status`),
  KEY `idx_anl_event` (`event`),
  KEY `idx_anl_channel` (`channel`),
  KEY `idx_anl_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `applicants`
  ADD COLUMN `parent_whatsapp` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `parent_email`,
  ADD COLUMN `notify_sms` tinyint(1) NOT NULL DEFAULT '1' AFTER `parent_whatsapp`,
  ADD COLUMN `notify_email` tinyint(1) NOT NULL DEFAULT '1' AFTER `notify_sms`,
  ADD COLUMN `notify_whatsapp` tinyint(1) NOT NULL DEFAULT '1' AFTER `notify_email`;

INSERT INTO `app_configs` (`setting_key`, `setting_value`) VALUES
  ('attendance_email_enabled', '0'),
  ('attendance_whatsapp_enabled', '0'),
  ('notify_on_checkin', '1'),
  ('notify_on_checkout', '1'),
  ('whatsapp_provider', 'stub'),
  ('whatsapp_api_url', 'https://graph.facebook.com/v20.0/me/messages'),
  ('whatsapp_api_token', ''),
  ('whatsapp_phone_number_id', ''),
  ('whatsapp_sender_id', ''),
  ('attendance_email_subject_checkin', 'EduCore Attendance Alert — {{student_name}}'),
  ('attendance_email_template_checkin', 'Dear {{parent_name}},\n\nYour child, {{student_name}}, has checked into {{school_name}}.\n\nDate: {{date}}\nTime: {{time}}\nStatus: {{status}}\n\nThank you,\n{{school_name}}\nPowered by EduCore'),
  ('attendance_email_subject_checkout', 'EduCore Attendance Alert — {{student_name}}'),
  ('attendance_email_template_checkout', 'Dear {{parent_name}},\n\nYour child, {{student_name}}, has left the school premises.\n\nDate: {{date}}\nTime: {{time}}\nStatus: Checked Out\n\nThank you,\n{{school_name}}\nPowered by EduCore'),
  ('attendance_whatsapp_template_checkin', 'EduCore Attendance Alert\nHello {{parent_name}}, your child {{student_name}} has checked into {{school_name}}.\nDate: {{date}}\nTime: {{time}}'),
  ('attendance_whatsapp_template_checkout', 'EduCore Attendance Alert\nHello {{parent_name}}, your child {{student_name}} has left {{school_name}}.\nDate: {{date}}\nTime: {{time}}')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
