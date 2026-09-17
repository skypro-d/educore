<?php
/**
 * Cron Job Entry Point for EduCore Remote Update Checking & Automated Upgrades
 * Runs every 6 hours: `0 * /6 * * * php /path/to/EduCore/cron/update_check.php`
 *
 * Checks EduCore Live for available releases matching the school node's channel,
 * downloads release notes, and applies mandatory or auto-approved updates safely.
 *
 * @package EduCore\Cron
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli' && !defined('CRON_ALLOWED')) {
    require_once __DIR__ . '/../config/config.php';
    $cronToken = $_GET['token'] ?? '';
    $expectedSecret = defined('CRON_SECRET') && CRON_SECRET !== '' ? CRON_SECRET : 'educore_cron_secret_2026';
    if (empty($cronToken) || !hash_equals($expectedSecret, $cronToken)) {
        header('HTTP/1.1 403 Forbidden');
        echo "CLI access only or valid cron token required.";
        exit;
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/../updater/UpdateChecker.php';
require_once __DIR__ . '/../updater/UpdateInstaller.php';

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Starting EduCore remote update check...\n";

$check = UpdateChecker::check(true);

if ($check['update_available'] ?? false) {
    $latest = $check['latest_version'] ?? 'unknown';
    $mandatory = (bool)($check['mandatory'] ?? false);
    $autoUpdate = defined('AUTO_UPDATE_ENABLED') ? AUTO_UPDATE_ENABLED : false;

    echo "[{$timestamp}] New version available: v{$latest} (Mandatory: " . ($mandatory ? 'YES' : 'NO') . ")\n";

    if ($mandatory || $autoUpdate) {
        echo "[{$timestamp}] Automated upgrade triggered (Reason: " . ($mandatory ? 'Mandatory Patch' : 'Auto-Update Enabled') . ")...\n";
        $installer = new UpdateInstaller();
        $result = $installer->applyUpdate(
            $latest,
            $check['download_url'] ?? '',
            $check['sha256'] ?? '',
            $check['signature'] ?? '',
            'cron_auto_updater'
        );

        echo "[{$timestamp}] Update Result: " . ($result['message'] ?? 'Done') . "\n";
    } else {
        echo "[{$timestamp}] Update v{$latest} is available for manual installation in the Admin Dashboard.\n";
        try {
            $db = Database::connect();
            $msg = "EduCore software update v{$latest} is now available for download. Visit Updates & Upgrades in the admin menu to review release notes and install.";
            $stmt = $db->prepare("SELECT id FROM notifications WHERE user_type='admin' AND title LIKE ? LIMIT 1");
            $stmt->execute(["%v{$latest}%"]);
            if (!$stmt->fetch()) {
                $insNotif = $db->prepare("INSERT INTO notifications (user_type, user_id, title, message, is_read, created_at) VALUES ('admin', 0, ?, ?, 0, NOW())");
                $insNotif->execute(["EduCore Update Available: v{$latest}", $msg]);
            }
        } catch (Throwable $e) {
            // Ignore if notifications table is unavailable
        }
    }
} else {
    echo "[{$timestamp}] EduCore is up to date (Current: v" . (defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0') . ").\n";
}

echo "[{$timestamp}] Update check completed.\n";
