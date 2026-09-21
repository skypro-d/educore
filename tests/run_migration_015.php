<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::connect();
    $fn = require __DIR__ . '/../database/migrations/015_add_staff_id_card_and_attendance.php';
    if (is_callable($fn)) {
        $fn($pdo);
    }
    
    // Register in migrations table if exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `migration` VARCHAR(191) NOT NULL,
        `batch` INT UNSIGNED NOT NULL DEFAULT 1,
        `checksum` VARCHAR(64) DEFAULT NULL,
        `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_migration_name` (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->prepare("INSERT IGNORE INTO `migrations` (`migration`, `batch`, `executed_at`) VALUES ('015_add_staff_id_card_and_attendance.php', 1, NOW())")->execute();

    echo "SUCCESS: Migration 015 applied successfully.\n";

    // Show staff columns
    $cols = $pdo->query("SHOW COLUMNS FROM staff")->fetchAll(PDO::FETCH_COLUMN);
    echo "Staff Columns: " . implode(', ', $cols) . "\n";

    // Show staff_attendance columns
    $attCols = $pdo->query("SHOW COLUMNS FROM staff_attendance")->fetchAll(PDO::FETCH_COLUMN);
    echo "Staff Attendance Columns: " . implode(', ', $attCols) . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
