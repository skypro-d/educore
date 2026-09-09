-- Migration 009: Add admission_type column to applicants table
-- Ensures applicants table supports admission type categorization (e.g., Nursery, Primary, Junior Secondary, Senior Secondary, General)

ALTER TABLE `applicants`
ADD COLUMN `admission_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'General' AFTER `application_number`;
