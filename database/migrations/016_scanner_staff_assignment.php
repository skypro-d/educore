<?php
declare(strict_types=1);

/**
 * Migration 016: Scanner Staff Assignment, Stations, Roles & Dedicated Scanner Logs
 *
 * Implements dedicated Scanner Officer role, granular scanner permissions,
 * scanner stations, staff scanner assignments, and scanner audit logging.
 */
return function (PDO $pdo): void {
    // 1. Create `scanner_stations` table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `scanner_stations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `station_name` VARCHAR(100) NOT NULL,
            `station_code` VARCHAR(50) NOT NULL,
            `scanner_type` ENUM('usb_hid', 'camera_qr', 'pos_device', 'manual') NOT NULL DEFAULT 'usb_hid',
            `location` VARCHAR(150) DEFAULT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `notes` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_school_station_code` (`school_id`, `station_code`),
            KEY `idx_school_status` (`school_id`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Seed default scanner stations if table is empty
    $stCount = (int) $pdo->query("SELECT COUNT(*) FROM `scanner_stations`")->fetchColumn();
    if ($stCount === 0) {
        $pdo->exec("
            INSERT INTO `scanner_stations` (`school_id`, `station_name`, `station_code`, `scanner_type`, `location`, `status`, `created_at`)
            VALUES
            (1, 'Main Gate Scanner', 'MAIN-GATE-01', 'usb_hid', 'Main Entrance Gate', 'active', NOW()),
            (1, 'Secondary Gate Scanner', 'SEC-GATE-02', 'usb_hid', 'Rear / Bus Gate', 'active', NOW()),
            (1, 'Boarding House Scanner', 'HOSTEL-01', 'usb_hid', 'Hostel / Dormitory Entrance', 'active', NOW()),
            (1, 'Front Desk / Reception Scanner', 'RECEPT-01', 'usb_hid', 'Administrative Reception', 'active', NOW());
        ");
    }

    // 2. Create `scanner_assignments` table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `scanner_assignments` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `staff_id` INT UNSIGNED NOT NULL,
            `station_id` INT UNSIGNED DEFAULT NULL,
            `assigned_by` INT UNSIGNED DEFAULT NULL,
            `status` ENUM('active', 'inactive', 'revoked') NOT NULL DEFAULT 'active',
            `can_scan_in` TINYINT(1) NOT NULL DEFAULT 1,
            `can_scan_out` TINYINT(1) NOT NULL DEFAULT 1,
            `can_view_today_logs` TINYINT(1) NOT NULL DEFAULT 1,
            `can_view_student_details` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` TEXT DEFAULT NULL,
            `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_school_staff_status` (`school_id`, `staff_id`, `status`),
            KEY `idx_station_status` (`station_id`, `status`),
            KEY `idx_assigned_by` (`assigned_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Create `scanner_logs` table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `scanner_logs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `assignment_id` INT UNSIGNED DEFAULT NULL,
            `staff_id` INT UNSIGNED DEFAULT NULL,
            `station_id` INT UNSIGNED DEFAULT NULL,
            `student_id` INT UNSIGNED DEFAULT NULL,
            `person_type` ENUM('student', 'staff', 'unknown') NOT NULL DEFAULT 'student',
            `scan_mode` ENUM('auto', 'in', 'out') NOT NULL DEFAULT 'auto',
            `scan_action` ENUM('check_in', 'check_out', 'duplicate_in', 'duplicate_out', 'invalid', 'inactive', 'denied') NOT NULL,
            `status` ENUM('success', 'warning', 'danger') NOT NULL DEFAULT 'success',
            `identifier_scanned` VARCHAR(255) DEFAULT NULL,
            `attendance_id` INT UNSIGNED DEFAULT NULL,
            `exit_log_id` INT UNSIGNED DEFAULT NULL,
            `student_name` VARCHAR(150) DEFAULT NULL,
            `student_admission_no` VARCHAR(60) DEFAULT NULL,
            `class_name` VARCHAR(100) DEFAULT NULL,
            `sms_status` VARCHAR(50) DEFAULT 'skipped',
            `email_status` VARCHAR(50) DEFAULT 'skipped',
            `response_message` TEXT DEFAULT NULL,
            `ip_address` VARCHAR(64) DEFAULT NULL,
            `date` DATE NOT NULL,
            `scanned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_school_date` (`school_id`, `date`),
            KEY `idx_staff_date` (`staff_id`, `date`),
            KEY `idx_station_date` (`station_id`, `date`),
            KEY `idx_scan_action` (`scan_action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Ensure Dedicated Role `Scanner Officer`
    $pdo->exec("
        INSERT INTO `roles` (`name`, `description`)
        VALUES ('scanner_officer', 'Dedicated student attendance and gate scanner operator')
        ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);
    ");

    // 5. Register Granular Scanner Permissions
    $permissions = [
        ['scanner.access', 'Access the dedicated scanner kiosk portal'],
        ['scanner.scan_in', 'Scan and verify student arrival check-in'],
        ['scanner.scan_out', 'Scan and verify student departure check-out'],
        ['scanner.view_today_logs', 'View today scan activity logs in scanner portal'],
        ['scanner.view_student_details', 'View student profile and guardian contact details upon scan']
    ];

    $permStmt = $pdo->prepare("
        INSERT INTO `permissions` (`name`, `description`)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)
    ");

    foreach ($permissions as $p) {
        $permStmt->execute([$p[0], $p[1]]);
    }

    // 6. Map Permissions to `scanner_officer` role in `role_permissions`
    $roleId = $pdo->query("SELECT id FROM `roles` WHERE `name` = 'scanner_officer' LIMIT 1")->fetchColumn();
    if ($roleId) {
        $rolePermStmt = $pdo->prepare("
            INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
            SELECT ?, id FROM `permissions` WHERE `name` IN (
                'scanner.access',
                'scanner.scan_in',
                'scanner.scan_out',
                'scanner.view_today_logs',
                'scanner.view_student_details'
            )
        ");
        $rolePermStmt->execute([$roleId]);
    }
};
