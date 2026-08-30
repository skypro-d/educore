<?php
declare(strict_types=1);

/**
 * UpdateInstaller — Master Remote Software Upgrade Orchestrator
 *
 * Coordinates license verification, locking, maintenance mode, full backups,
 * package download, checksum validation, safe file extraction, database migrations,
 * health checking, error recovery, rollback, and live server telemetry.
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ApiKeyService.php';
require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/UpdateLock.php';
require_once __DIR__ . '/UpdateLogger.php';
require_once __DIR__ . '/BackupManager.php';
require_once __DIR__ . '/RollbackManager.php';
require_once __DIR__ . '/UpdateDownloader.php';
require_once __DIR__ . '/MigrationRunner.php';
require_once __DIR__ . '/HealthChecker.php';

final class UpdateInstaller
{
    private PDO $db;
    private UpdateLock $lock;
    private BackupManager $backupMgr;
    private RollbackManager $rollbackMgr;
    private UpdateDownloader $downloader;
    private MigrationRunner $migrationRunner;
    private HealthChecker $healthChecker;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->lock = new UpdateLock();
        $this->backupMgr = new BackupManager($this->db);
        $this->rollbackMgr = new RollbackManager($this->db);
        $this->downloader = new UpdateDownloader();
        $this->migrationRunner = new MigrationRunner($this->db);
        $this->healthChecker = new HealthChecker($this->db);
        $this->ensureHistoryTable();
    }

    private function ensureHistoryTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `system_update_history` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `from_version` VARCHAR(32) NOT NULL,
            `to_version` VARCHAR(32) NOT NULL,
            `status` ENUM('started', 'completed', 'rolled_back', 'failed') NOT NULL DEFAULT 'started',
            `backup_path` VARCHAR(255) DEFAULT NULL,
            `backup_size_bytes` BIGINT UNSIGNED DEFAULT NULL,
            `executed_migrations` TEXT DEFAULT NULL,
            `log_summary` TEXT DEFAULT NULL,
            `initiated_by` VARCHAR(64) DEFAULT 'system_admin',
            `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `completed_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_update_status` (`status`),
            KEY `idx_update_date` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->db->exec($sql);
    }

    /**
     * Execute full remote upgrade workflow
     *
     * @param string $targetVersion
     * @param string $downloadUrl
     * @param string $expectedSha256
     * @param string $expectedSignature
     * @param string $initiatedBy
     * @return array ['success' => bool, 'status' => string, 'message' => string, 'from_version' => string, 'to_version' => string, 'backup_path' => string]
     */
    public function applyUpdate(
        string $targetVersion,
        string $downloadUrl,
        string $expectedSha256 = '',
        string $expectedSignature = '',
        string $initiatedBy = 'system_admin'
    ): array {
        $fromVersion = defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0';
        $logger = new UpdateLogger($targetVersion);
        $logger->log("Initiating EduCore update process from v{$fromVersion} to v{$targetVersion}...");

        $rootDir = realpath(dirname(__DIR__));
        $storageDir = $rootDir . '/storage';
        $maintLock = $storageDir . '/maintenance.lock';
        $backupDir = '';
        $historyId = null;

        try {
            // 1. Check License Eligibility
            $logger->log("Step 1: Validating school node license entitlement...");
            $licValidation = ApiKeyService::validate();
            if (!$licValidation['success']) {
                throw new RuntimeException("License verification failed: " . ($licValidation['status'] ?? 'unlicensed'));
            }
            $logger->log("License valid (Plan: " . ($licValidation['plan'] ?? 'active') . ").");

            // 2. Acquire Update Lock
            $logger->log("Step 2: Acquiring execution lock...");
            $this->lock->acquire($targetVersion);

            // Record update started in DB
            $stmt = $this->db->prepare("
                INSERT INTO `system_update_history` (`from_version`, `to_version`, `status`, `initiated_by`, `started_at`)
                VALUES (?, ?, 'started', ?, NOW())
            ");
            $stmt->execute([$fromVersion, $targetVersion, $initiatedBy]);
            $historyId = (int)$this->db->lastInsertId();

            // 3. Enable Maintenance Mode
            $logger->log("Step 3: Activating maintenance mode...");
            @file_put_contents($maintLock, json_encode([
                'maintenance' => true,
                'target_version' => $targetVersion,
                'activated_at' => date('Y-m-d H:i:s')
            ]));

            // 4. Create Pre-Update Snapshot Backup
            $logger->log("Step 4: Creating pre-update backup snapshot (Database + Files)...");
            try {
                $backupResult = $this->backupMgr->createBackup($fromVersion);
                if (!empty($backupResult['backup_dir'])) {
                    $backupDir = $backupResult['backup_dir'];
                    $logger->log("Backup snapshot successfully stored in: " . basename($backupDir) . " (" . round(($backupResult['total_size_bytes'] ?? 0) / 1024, 1) . " KB)");

                    // Update backup path in history record
                    if ($historyId) {
                        $this->db->prepare("UPDATE `system_update_history` SET `backup_path` = ?, `backup_size_bytes` = ? WHERE `id` = ?")
                                 ->execute([$backupDir, $backupResult['total_size_bytes'] ?? 0, $historyId]);
                    }
                }
                if (!empty($backupResult['error'])) {
                    $logger->log("Backup notice: " . $backupResult['error'], 'WARNING');
                }
            } catch (Throwable $be) {
                $logger->log("Pre-update backup notice: " . $be->getMessage() . ". Proceeding with upgrade.", 'WARNING');
            }

            // 5. Download and Cryptographically Verify Release Package
            $logger->log("Step 5: Downloading release package v{$targetVersion}...");
            $downloadResult = $this->downloader->download($downloadUrl, $targetVersion, $expectedSha256, $expectedSignature);
            if (!$downloadResult['success']) {
                throw new RuntimeException("Download failed: " . $downloadResult['message']);
            }
            $zipPath = $downloadResult['zip_path'];
            $logger->log("Download verified (SHA256: " . substr($downloadResult['sha256'], 0, 16) . "...).");

            // 6. Safe ZIP Extraction & File Replacement
            $logger->log("Step 6: Extracting update files safely...");
            $this->extractAndApplyFiles($zipPath, $rootDir, $logger);
            @unlink($zipPath);
            $logger->log("Application files updated.");

            // 7. Run Database Migrations
            $logger->log("Step 7: Applying database migrations...");
            $migResult = $this->migrationRunner->runPending();
            if (!$migResult['success']) {
                throw new RuntimeException("Database migration execution failed: " . ($migResult['failed_file'] ?? 'unknown'));
            }
            $executedMigs = implode(', ', $migResult['executed']);
            $logger->log("Migrations completed. Applied: " . ($executedMigs ?: 'None (schema up to date)'));

            // 8. Run Post-Update Health Checks
            $logger->log("Step 8: Running comprehensive system health diagnostics...");
            $health = $this->healthChecker->runChecks();
            if (!$health['healthy']) {
                $errDetails = implode('; ', $health['errors']);
                throw new RuntimeException("Post-update health check diagnostics failed: " . $errDetails);
            }
            $logger->log("System diagnostics passed 100%. All subsystems operational.");

            // 9. Update version.php single source of truth
            $versionFile = $rootDir . '/version.php';
            $versionContent = "<?php\ndeclare(strict_types=1);\n\n/**\n * EduCore Software Version\n */\ndefine('EDUCORE_VERSION', '{$targetVersion}');\ndefine('EDUCORE_BUILD', '" . date('Ymd.H') . "');\ndefine('EDUCORE_MIN_PHP', '8.3.0');\ndefine('EDUCORE_MIN_MYSQL', '8.0.0');\ndefine('EDUCORE_DEFAULT_CHANNEL', 'stable');\n";
            file_put_contents($versionFile, $versionContent);

            // 10. Update local update cache
            $cacheFile = dirname(__DIR__) . '/config/cache/update_check.json';
            if (file_exists($cacheFile)) {
                $cached = json_decode(file_get_contents($cacheFile), true) ?: [];
                $cached['update_available'] = false;
                $cached['current_version'] = $targetVersion;
                file_put_contents($cacheFile, json_encode($cached, JSON_PRETTY_PRINT));
            }

            // 11. Finalize and Record Success
            $logger->log("Update to v{$targetVersion} completed successfully!");
            $summary = $logger->getSummary();

            if ($historyId) {
                $this->db->prepare("
                    UPDATE `system_update_history` 
                    SET `status` = 'completed', `executed_migrations` = ?, `log_summary` = ?, `completed_at` = NOW()
                    WHERE `id` = ?
                ")->execute([$executedMigs, $summary, $historyId]);
            }

            // Report telemetry to Live Server
            $logger->reportToLive($fromVersion, $targetVersion, 'success', $summary);

            // Disable Maintenance Mode & Release Lock
            @unlink($maintLock);
            $this->lock->release();

            return [
                'success' => true,
                'status' => 'completed',
                'message' => "EduCore successfully upgraded from v{$fromVersion} to v{$targetVersion}.",
                'from_version' => $fromVersion,
                'to_version' => $targetVersion,
                'backup_path' => $backupDir,
                'executed_migrations' => $migResult['executed']
            ];

        } catch (Throwable $e) {
            $errorMsg = $e->getMessage();
            $logger->log("CRITICAL UPDATE FAILURE: {$errorMsg}", 'ERROR');

            $rollbackSuccess = false;
            if (!empty($backupDir) && is_dir($backupDir)) {
                $logger->log("Triggering automatic failure recovery & rollback...", 'WARNING');
                $rollbackResult = $this->rollbackMgr->rollback($backupDir);
                $rollbackSuccess = $rollbackResult['success'];
                $logger->log("Rollback result: " . $rollbackResult['message'], $rollbackSuccess ? 'INFO' : 'ERROR');
            }

            $summary = $logger->getSummary();

            if ($historyId) {
                $this->db->prepare("
                    UPDATE `system_update_history` 
                    SET `status` = ?, `log_summary` = ?, `completed_at` = NOW()
                    WHERE `id` = ?
                ")->execute([$rollbackSuccess ? 'rolled_back' : 'failed', $summary, $historyId]);
            }

            // Report failure to Live Server
            $logger->reportToLive($fromVersion, $targetVersion, $rollbackSuccess ? 'rolled_back' : 'failed', $summary);

            // Clean up locks
            @unlink($maintLock);
            $this->lock->release();

            return [
                'success' => false,
                'status' => $rollbackSuccess ? 'rolled_back' : 'failed',
                'message' => "Update halted: {$errorMsg}" . ($rollbackSuccess ? " (System safely restored to v{$fromVersion})" : ""),
                'from_version' => $fromVersion,
                'to_version' => $targetVersion,
                'backup_path' => $backupDir,
                'error' => $errorMsg
            ];
        }
    }

    /**
     * Safely extract ZIP update contents with path traversal protection
     */
    private function extractAndApplyFiles(string $zipPath, string $targetRootDir, UpdateLogger $logger): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Failed to open update ZIP package.");
        }

        // Protected paths that must NEVER be overwritten by an update package
        $protectedPaths = [
            '.env',
            'config/cache/license.json',
            'storage/backups',
            'storage/updates',
            'uploads',
            'logs'
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalizedName = str_replace('\\', '/', $entryName);

            // Path traversal defense
            if (str_contains($normalizedName, '..') || str_starts_with($normalizedName, '/')) {
                $logger->log("Skipping unsafe zip entry: {$normalizedName}", 'WARNING');
                continue;
            }

            // Check if matches protected path
            $isProtected = false;
            foreach ($protectedPaths as $protected) {
                if ($normalizedName === $protected || str_starts_with($normalizedName, $protected . '/')) {
                    $isProtected = true;
                    break;
                }
            }

            if ($isProtected) {
                $logger->log("Preserving user data / configuration file: {$normalizedName}");
                continue;
            }

            $destPath = $targetRootDir . '/' . $normalizedName;

            if (str_ends_with($normalizedName, '/')) {
                if (!is_dir($destPath)) {
                    @mkdir($destPath, 0755, true);
                }
            } else {
                $parentDir = dirname($destPath);
                if (!is_dir($parentDir)) {
                    @mkdir($parentDir, 0755, true);
                }
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($destPath, $content);
                }
            }
        }

        $zip->close();
    }
}
