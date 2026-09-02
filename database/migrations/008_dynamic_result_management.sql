-- ============================================================================
-- EduCore Database Migration: 008_dynamic_result_management.sql
-- Supports dynamic class-subject configuration, student subject exceptions,
-- configurable assessment components, and dynamic grading rules.
-- ============================================================================

-- 1. Class Subjects Mapping Table
CREATE TABLE IF NOT EXISTS `class_subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `class_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `is_compulsory` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_class_subject` (`school_id`, `class_id`, `subject_id`),
    KEY `idx_cs_class` (`class_id`),
    KEY `idx_cs_subject` (`subject_id`),
    KEY `idx_cs_school` (`school_id`),
    CONSTRAINT `fk_cs_class` FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cs_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student-Specific Subject Exceptions & Electives
CREATE TABLE IF NOT EXISTS `student_subject_enrollments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `applicant_id` INT UNSIGNED NOT NULL,
    `class_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `academic_year` VARCHAR(20) NOT NULL,
    `is_exempt` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_student_subject` (`school_id`, `applicant_id`, `subject_id`, `academic_year`),
    KEY `idx_sse_applicant` (`applicant_id`),
    KEY `idx_sse_class` (`class_id`),
    KEY `idx_sse_subject` (`subject_id`),
    KEY `idx_sse_school` (`school_id`),
    CONSTRAINT `fk_sse_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sse_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Configurable Assessment Components
CREATE TABLE IF NOT EXISTS `assessment_components` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(60) NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `max_score` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `weight_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_assessment_component` (`school_id`, `code`),
    KEY `idx_ac_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Configurable Grading Rules
CREATE TABLE IF NOT EXISTS `grading_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `min_score` DECIMAL(5,2) NOT NULL,
    `max_score` DECIMAL(5,2) NOT NULL,
    `grade` VARCHAR(10) NOT NULL,
    `remark` VARCHAR(50) NOT NULL,
    `grade_point` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_gr_school` (`school_id`),
    KEY `idx_gr_min_max` (`min_score`, `max_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seed Standard Assessment Components
INSERT IGNORE INTO `assessment_components` (`school_id`, `name`, `code`, `max_score`, `weight_percent`, `sort_order`, `is_active`)
VALUES
(1, 'Test 1', 'ca1', 15.00, 15.00, 1, 1),
(1, 'Test 2', 'ca2', 10.00, 10.00, 2, 1),
(1, 'Assignment', 'assignment', 10.00, 10.00, 3, 1),
(1, 'Mid-Term', 'mid_term', 10.00, 10.00, 4, 1),
(1, 'Examination', 'exam', 55.00, 55.00, 5, 1);

-- 6. Seed Standard WAEC / NECO Grading Rules
INSERT IGNORE INTO `grading_rules` (`school_id`, `min_score`, `max_score`, `grade`, `remark`, `grade_point`, `sort_order`)
VALUES
(1, 75.00, 100.00, 'A1', 'Excellent', 4.00, 1),
(1, 70.00, 74.99,  'B2', 'Very Good', 3.50, 2),
(1, 65.00, 69.99,  'B3', 'Good',      3.00, 3),
(1, 60.00, 64.99,  'C4', 'Credit',    2.50, 4),
(1, 55.00, 59.99,  'C5', 'Credit',    2.00, 5),
(1, 50.00, 54.99,  'C6', 'Credit',    1.50, 6),
(1, 45.00, 49.99,  'D7', 'Pass',      1.00, 7),
(1, 40.00, 44.99,  'E8', 'Pass',      0.50, 8),
(1, 0.00,  39.99,  'F9', 'Fail',      0.00, 9);

-- 7. Seed Class Subjects (16 for Primary, 16 for JSS, 12 for SS)
-- Primary 1 (class_id: 1) -> 16 subjects (IDs 1 to 16)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 1, id, 1, id, 1 FROM `subjects` WHERE id BETWEEN 1 AND 16;

-- Primary 2 (class_id: 2) -> 16 subjects (IDs 1 to 16)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 2, id, 1, id, 1 FROM `subjects` WHERE id BETWEEN 1 AND 16;

-- JSS 1 (class_id: 4) -> 16 subjects (IDs 1 to 16)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 4, id, 1, id, 1 FROM `subjects` WHERE id BETWEEN 1 AND 16;

-- JSS 2 (class_id: 5) -> 16 subjects (IDs 1 to 16)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 5, id, 1, id, 1 FROM `subjects` WHERE id BETWEEN 1 AND 16;

-- SS 1 (class_id: 6) -> 12 subjects (English, Maths, Bio, Chem, Phys, F/Math, Econ, Geo, Govt, Lit, Comm, Acc)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 6, id, 1, id, 1 FROM `subjects` WHERE id IN (1, 2, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28);

-- SS 2 (class_id: 7) -> 12 subjects (English, Maths, Bio, Chem, Phys, F/Math, Econ, Geo, Govt, Lit, Comm, Acc)
INSERT IGNORE INTO `class_subjects` (`school_id`, `class_id`, `subject_id`, `is_compulsory`, `sort_order`, `is_active`)
SELECT 1, 7, id, 1, id, 1 FROM `subjects` WHERE id IN (1, 2, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28);
