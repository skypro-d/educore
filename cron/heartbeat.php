<?php
/**
 * Cron Job Entry Point for EduCore Installation
 * Runs every 5 minutes: '* /5 * * * * php /path/to/EduCore/cron/heartbeat.php'
 *
 * Transmits system heartbeat and syncs licensing status with EduCore Live Server.
 *
 * @package EduCore\Cron
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../version.php';

if (php_sapi_name() !== 'cli' && !defined('CRON_ALLOWED')) {
    $cronToken = $_GET['token'] ?? '';
    $expectedSecret = defined('CRON_SECRET') && CRON_SECRET !== '' ? CRON_SECRET : 'educore_cron_secret_2026';
    if (empty($cronToken) || !hash_equals($expectedSecret, $cronToken)) {
        header('HTTP/1.1 403 Forbidden');
        echo "CLI access only or valid cron token required.";
        exit;
    }
}

require_once __DIR__ . '/../config/HeartbeatService.php';
require_once __DIR__ . '/../config/ApiKeyService.php';

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Starting EduCore background telemetry sync (v" . EDUCORE_VERSION . ")...\n";

// 1. Send Heartbeat
$hbResult = HeartbeatService::send();
echo "[{$timestamp}] Heartbeat status: " . ($hbResult['message'] ?? 'Done') . "\n";

// 2. Sync License Validation
$valResult = ApiKeyService::validateOnline();
echo "[{$timestamp}] License sync status: " . ($valResult['status'] ?? 'Checked') . "\n";

echo "[{$timestamp}] Telemetry cron completed successfully.\n";
