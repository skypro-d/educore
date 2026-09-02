-- ============================================================================
-- Migration 007: Staff Portal, RBAC, Assignments & Audit Logging
-- Description: Complete role-based & permission-based access control,
--              staff permission overrides, assignments, result workflows,
--              and staff audit logging for EduCore.
-- ============================================================================

-- 1. Standard Roles
INSERT INTO `roles` (`name`, `description`) VALUES
('super_admin', 'Full system access and school configuration'),
('proprietor', 'School-wide operational access and oversight'),
('principal', 'School administration, academics, staff supervision and reporting'),
('vice_principal', 'Administrative and academic management according to assigned permissions'),
('head_teacher', 'Management of assigned classes, teachers, and departments'),
('class_teacher', 'Management of assigned class, students, attendance, and performance'),
('subject_teacher', 'Management of assigned subjects, results, and assignments'),
('accountant', 'Finance, fees, and accounting modules'),
('receptionist', 'Front desk, admissions inquiry, and basic student info'),
('librarian', 'Library catalog and book circulation management'),
('nurse', 'Clinic, health records, and student medical care'),
('driver', 'Transport routes and bus logistics')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 2. Granular Permissions
INSERT INTO `permissions` (`name`, `description`) VALUES
('students.view', 'View student lists and assigned student profiles'),
('students.create', 'Register or enroll new students'),
('students.edit', 'Edit student records'),
('students.delete', 'Delete or archive student records'),
('attendance.view', 'View attendance registers and logs'),
('attendance.mark', 'Mark student attendance and check-in'),
('attendance.edit', 'Modify previously marked attendance records'),
('results.view', 'View academic results and score sheets'),
('results.enter', 'Enter and save draft student results'),
('results.edit', 'Modify draft student results'),
('results.submit', 'Submit draft results for administrative approval'),
('results.approve', 'Approve submitted academic results'),
('results.publish', 'Publish approved results to students and parents'),
('assignments.view', 'View classroom assignments and submissions'),
('assignments.create', 'Create and publish classroom assignments'),
('assignments.edit', 'Edit existing assignments'),
('assignments.delete', 'Delete assignments'),
('assignments.grade', 'Grade student assignment submissions and provide feedback'),
('classes.view', 'View class rosters and details'),
('classes.manage', 'Create and configure classes'),
('timetable.view', 'View teaching timetables and schedules'),
('timetable.manage', 'Create and modify school timetables'),
('messages.view', 'View conversations and messages'),
('messages.send', 'Send messages to parents and students'),
('announcements.view', 'View school and staff announcements'),
('announcements.create', 'Create and broadcast announcements'),
('reports.view', 'View academic and operational reports'),
('reports.generate', 'Generate and export school reports')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 3. Default Role Permissions Mapping (pure SQL set queries)
-- super_admin & proprietor: All permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r CROSS JOIN `permissions` p
WHERE r.name IN ('super_admin', 'proprietor');

-- principal: Academic supervision, attendance, results approval/publish, announcements, classes, timetable
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view', 'students.edit',
    'attendance.view', 'attendance.mark', 'attendance.edit',
    'results.view', 'results.enter', 'results.edit', 'results.approve', 'results.publish',
    'assignments.view', 'assignments.grade',
    'classes.view', 'classes.manage',
    'timetable.view', 'timetable.manage',
    'messages.view', 'messages.send',
    'announcements.view', 'announcements.create',
    'reports.view', 'reports.generate'
) WHERE r.name = 'principal';

-- vice_principal: Administration & academic supervision
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view',
    'attendance.view', 'attendance.mark', 'attendance.edit',
    'results.view', 'results.enter', 'results.edit', 'results.approve',
    'assignments.view', 'assignments.grade',
    'classes.view',
    'timetable.view',
    'messages.view', 'messages.send',
    'announcements.view', 'announcements.create',
    'reports.view'
) WHERE r.name = 'vice_principal';

-- head_teacher: Assigned classes/teachers/departments management
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view',
    'attendance.view', 'attendance.mark', 'attendance.edit',
    'results.view', 'results.enter', 'results.edit', 'results.approve',
    'assignments.view', 'assignments.create', 'assignments.grade',
    'classes.view',
    'timetable.view',
    'messages.view', 'messages.send',
    'announcements.view', 'announcements.create',
    'reports.view'
) WHERE r.name = 'head_teacher';

-- class_teacher: Class management, student view, attendance mark, results enter/submit, assignments
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view',
    'attendance.view', 'attendance.mark',
    'results.view', 'results.enter', 'results.edit', 'results.submit',
    'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.grade',
    'classes.view',
    'timetable.view',
    'messages.view', 'messages.send',
    'announcements.view', 'announcements.create'
) WHERE r.name = 'class_teacher';

-- subject_teacher: Assigned subjects only, results enter/submit, assignments create/grade
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view',
    'results.view', 'results.enter', 'results.edit', 'results.submit',
    'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.grade',
    'classes.view',
    'timetable.view',
    'announcements.view'
) WHERE r.name = 'subject_teacher';

-- receptionist: Student viewing, basic admissions inquiry
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view', 'students.create',
    'announcements.view',
    'messages.view', 'messages.send'
) WHERE r.name = 'receptionist';

-- librarian: View students and announcements
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN (
    'students.view',
    'announcements.view'
) WHERE r.name = 'librarian';

-- 4. Staff Permissions Table (Granular direct overrides per staff)
CREATE TABLE IF NOT EXISTS `staff_permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `staff_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  `granted` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_perm` (`staff_id`, `permission_id`),
  KEY `idx_staff_perm_school` (`school_id`),
  KEY `idx_staff_perm_pid` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Add role_id to staff table
ALTER TABLE `staff` ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `role`;

-- 6. Add results workflow columns to student_results table
ALTER TABLE `student_results` 
  ADD COLUMN `status` ENUM('draft', 'submitted', 'approved', 'published') NOT NULL DEFAULT 'draft' AFTER `teacher_remark`,
  ADD COLUMN `submitted_at` DATETIME NULL AFTER `status`,
  ADD COLUMN `submitted_by` INT UNSIGNED NULL AFTER `submitted_at`,
  ADD COLUMN `approved_at` DATETIME NULL AFTER `submitted_by`,
  ADD COLUMN `approved_by` INT UNSIGNED NULL AFTER `approved_at`,
  ADD COLUMN `published_at` DATETIME NULL AFTER `approved_by`,
  ADD COLUMN `published_by` INT UNSIGNED NULL AFTER `published_at`;

-- 7. Assignments Table
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `class_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `instructions` TEXT NULL,
  `due_date` DATE NOT NULL,
  `attachment` VARCHAR(255) NULL,
  `max_score` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  `status` ENUM('active', 'closed', 'archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_assign_school` (`school_id`),
  KEY `idx_assign_class` (`class_id`),
  KEY `idx_assign_subject` (`subject_id`),
  KEY `idx_assign_teacher` (`teacher_id`),
  KEY `idx_assign_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Assignment Submissions Table
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `assignment_id` INT UNSIGNED NOT NULL,
  `applicant_id` INT UNSIGNED NOT NULL,
  `submission_text` TEXT NULL,
  `attachment` VARCHAR(255) NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `score` DECIMAL(5,2) NULL,
  `feedback` TEXT NULL,
  `graded_by` INT UNSIGNED NULL,
  `graded_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assign_sub_student` (`assignment_id`, `applicant_id`),
  KEY `idx_assign_sub_school` (`school_id`),
  KEY `idx_assign_sub_student` (`applicant_id`),
  KEY `idx_assign_sub_grader` (`graded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Staff Audit Logs Table
CREATE TABLE IF NOT EXISTS `staff_audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `staff_id` INT UNSIGNED NULL,
  `action` VARCHAR(120) NOT NULL,
  `resource_type` VARCHAR(60) NOT NULL,
  `resource_id` INT UNSIGNED NULL,
  `details` TEXT NULL,
  `previous_value` TEXT NULL,
  `new_value` TEXT NULL,
  `ip_address` VARCHAR(64) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sal_school` (`school_id`),
  KEY `idx_sal_staff` (`staff_id`),
  KEY `idx_sal_action` (`action`),
  KEY `idx_sal_resource` (`resource_type`, `resource_id`),
  KEY `idx_sal_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
