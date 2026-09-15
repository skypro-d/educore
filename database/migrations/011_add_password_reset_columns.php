<?php
declare(strict_types=1);

/**
 * Migration 011: Add reset_token and reset_expires columns to student_accounts, staff_accounts, and parent_accounts.
 */
return function (PDO $pdo): void {
    $tables = [
        'student_accounts',
        'staff_accounts',
        'parent_accounts',
    ];

    foreach ($tables as $table) {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('reset_token', $cols, true)) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `reset_token` VARCHAR(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `must_change_password`");
        }
        
        if (!in_array('reset_expires', $cols, true)) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `reset_expires` DATETIME DEFAULT NULL AFTER `reset_token`");
        }
    }
};
