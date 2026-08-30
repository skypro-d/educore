<?php
declare(strict_types=1);

/**
 * HealthChecker — Post-update health integrity validator
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../version.php';

final class HealthChecker
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Run all system health checks
     *
     * @return array ['healthy' => bool, 'checks' => array, 'errors' => array]
     */
    public function runChecks(): array
    {
        $checks = [];
        $errors = [];

        // 1. Database Connection Check
        try {
            $stmt = $this->db->query("SELECT 1");
            $dbOk = $stmt !== false;
            $checks['database_connection'] = $dbOk ? 'PASS' : 'FAIL';
            if (!$dbOk) {
                $errors[] = "Database ping failed.";
            }
        } catch (Throwable $e) {
            $checks['database_connection'] = 'FAIL';
            $errors[] = "Database error: " . $e->getMessage();
        }

        // 2. Critical Tables Check
        $criticalTables = ['school_settings', 'admins', 'applicants', 'classes', 'migrations'];
        $missingTables = [];
        foreach ($criticalTables as $table) {
            try {
                $stmt = $this->db->query("SELECT 1 FROM `{$table}` LIMIT 1");
                if ($stmt === false) {
                    $missingTables[] = $table;
                }
            } catch (Throwable $t) {
                $missingTables[] = $table;
            }
        }
        $checks['critical_tables'] = empty($missingTables) ? 'PASS' : 'FAIL';
        if (!empty($missingTables)) {
            $errors[] = "Missing critical tables: " . implode(', ', $missingTables);
        }

        // 3. Core Files Integrity Check
        $rootDir = dirname(__DIR__);
        $criticalFiles = [
            'version.php' => $rootDir . '/version.php',
            'config/config.php' => $rootDir . '/config/config.php',
            'config/database.php' => $rootDir . '/config/database.php',
            'config/ApiKeyService.php' => $rootDir . '/config/ApiKeyService.php',
            'updater/MigrationRunner.php' => $rootDir . '/updater/MigrationRunner.php'
        ];
        $missingFiles = [];
        foreach ($criticalFiles as $label => $path) {
            if (!file_exists($path)) {
                $missingFiles[] = $label;
            }
        }
        $checks['core_files'] = empty($missingFiles) ? 'PASS' : 'FAIL';
        if (!empty($missingFiles)) {
            $errors[] = "Missing core files: " . implode(', ', $missingFiles);
        }

        // 4. PHP Version & Extensions Check
        $phpOk = version_compare(PHP_VERSION, defined('EDUCORE_MIN_PHP') ? EDUCORE_MIN_PHP : '8.3.0', '>=');
        $pdoOk = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $checks['runtime_compatibility'] = ($phpOk && $pdoOk) ? 'PASS' : 'FAIL';
        if (!$phpOk) {
            $errors[] = "PHP version incompatible. Current: " . PHP_VERSION;
        }

        // 5. Check Disk Free Space
        $freeBytes = @disk_free_space($rootDir);
        $freeMb = $freeBytes !== false ? round($freeBytes / (1024 * 1024), 2) : 500;
        $diskOk = $freeMb >= 50;
        $checks['disk_space'] = $diskOk ? 'PASS' : 'WARNING';
        if (!$diskOk) {
            $errors[] = "Low disk space: {$freeMb} MB remaining.";
        }

        $healthy = !in_array('FAIL', $checks, true);

        return [
            'healthy' => $healthy,
            'checks' => $checks,
            'errors' => $errors,
            'version' => defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
