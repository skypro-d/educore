<?php
declare(strict_types=1);

/**
 * Migration 015: Add staff ID card fields and staff attendance arrival/departure tracking.
 */
return function (PDO $pdo): void {
    // 1. Ensure columns on `staff` table
    $staffCols = $pdo->query("SHOW COLUMNS FROM `staff`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('qr_data', $staffCols, true)) {
        $pdo->exec("ALTER TABLE `staff` ADD COLUMN `qr_data` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `staff_id`");
        $pdo->exec("ALTER TABLE `staff` ADD UNIQUE KEY `uq_staff_qr_data` (`qr_data`)");
    }

    if (!in_array('blood_group', $staffCols, true)) {
        $pdo->exec("ALTER TABLE `staff` ADD COLUMN `blood_group` VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `gender`");
    }

    if (!in_array('emergency_contact_name', $staffCols, true)) {
        $pdo->exec("ALTER TABLE `staff` ADD COLUMN `emergency_contact_name` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `phone`");
    }

    if (!in_array('emergency_contact_phone', $staffCols, true)) {
        $pdo->exec("ALTER TABLE `staff` ADD COLUMN `emergency_contact_phone` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `emergency_contact_name`");
    }

    if (!in_array('address', $staffCols, true)) {
        $pdo->exec("ALTER TABLE `staff` ADD COLUMN `address` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `emergency_contact_phone`");
    }

    // 2. Ensure columns on `staff_attendance` table
    $attCols = $pdo->query("SHOW COLUMNS FROM `staff_attendance`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('time_in', $attCols, true)) {
        $pdo->exec("ALTER TABLE `staff_attendance` ADD COLUMN `time_in` TIME DEFAULT NULL AFTER `date`");
    }

    if (!in_array('time_out', $attCols, true)) {
        $pdo->exec("ALTER TABLE `staff_attendance` ADD COLUMN `time_out` TIME DEFAULT NULL AFTER `time_in`");
    }

    if (!in_array('scan_method', $attCols, true)) {
        $pdo->exec("ALTER TABLE `staff_attendance` ADD COLUMN `scan_method` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual' AFTER `status`");
    }

    if (!in_array('marked_by', $attCols, true)) {
        $pdo->exec("ALTER TABLE `staff_attendance` ADD COLUMN `marked_by` INT UNSIGNED DEFAULT NULL AFTER `remark`");
    }

    // 3. Generate qr_data for existing staff members who do not have one
    $stmt = $pdo->query("SELECT id, staff_id, qr_data FROM `staff` WHERE `qr_data` IS NULL OR `qr_data` = ''");
    $staffList = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $upd = $pdo->prepare("UPDATE `staff` SET `qr_data` = ? WHERE `id` = ?");
    foreach ($staffList as $st) {
        $token = 'ATTENDANCE-STF-' . $st['id'] . '-' . substr(md5($st['staff_id'] . '_' . $st['id']), 0, 8);
        $upd->execute([$token, $st['id']]);
    }
};
