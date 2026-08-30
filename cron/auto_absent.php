<?php
/**
 * EduCore — Auto-Absent Cron Script
 * ====================================
 * Run this daily after attendance closing time to automatically mark
 * enrolled students with no check-in as Absent and notify parents via SMS.
 *
 * SETUP (Windows Task Scheduler):
 *   Program:   C:\wamp64\bin\php\phpX.X.X\php.exe
 *   Arguments: C:\wamp64\www\EduCore\cron\auto_absent.php
 *   Schedule:  Daily at 09:15 AM (15 minutes after attendance closes)
 *
 * SETUP (Linux/cPanel Cron):
 *   15 9 * * 1-5 /usr/bin/php /path/to/EduCore/cron/auto_absent.php >> /path/to/EduCore/cron/auto_absent.log 2>&1
 *
 * The script is safe to run multiple times — INSERT IGNORE prevents duplicates.
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────
define('CRON_MODE', true);

// Simulate minimal $_SERVER for helpers that need HTTP_HOST
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/EduCore/cron/auto_absent.php';
$_SERVER['REQUEST_METHOD'] = 'CLI';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';

// ── Guard: only run if auto-absent is enabled ─────────────────────────────────
if (setting('auto_absent_enabled', '1') === '0') {
    echo '[' . date('Y-m-d H:i:s') . '] Auto-absent is DISABLED in settings. Exiting.' . PHP_EOL;
    exit(0);
}

// ── Guard: only run if attendance window is actually closed ───────────────────
if (AttendanceRules::isWindowOpen()) {
    $closeTime = AttendanceRules::format(setting('attendance_close_time', '09:00'));
    echo '[' . date('Y-m-d H:i:s') . "] Attendance window is still open. Run after {$closeTime}. Exiting." . PHP_EOL;
    exit(0);
}

// ── Run ───────────────────────────────────────────────────────────────────────
$date = $argv[1] ?? date('Y-m-d'); // Allow passing a specific date as CLI arg

echo '[' . date('Y-m-d H:i:s') . "] EduCore Auto-Absent Cron — Processing date: {$date}" . PHP_EOL;

$controller = new AttendanceController();
$summary    = $controller->processAutoAbsent($date);

echo '[' . date('Y-m-d H:i:s') . "] Done." . PHP_EOL;
echo "  Marked absent  : {$summary['processed']}" . PHP_EOL;
echo "  Skipped        : {$summary['skipped']}" . PHP_EOL;

if (!empty($summary['errors'])) {
    echo "  Errors:" . PHP_EOL;
    foreach ($summary['errors'] as $err) {
        echo "    - {$err}" . PHP_EOL;
    }
}

exit(0);
