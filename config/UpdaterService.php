<?php
declare(strict_types=1);

/**
 * UpdaterService — EduCore Auto-Update Manager Facade
 *
 * Provides backward-compatible interface integrating with the modular updater suite:
 * UpdateChecker, UpdateDownloader, UpdateInstaller, BackupManager, MigrationRunner, HealthChecker, and RollbackManager.
 *
 * @package EduCore
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/../updater/UpdateChecker.php';
require_once __DIR__ . '/../updater/UpdateInstaller.php';
require_once __DIR__ . '/../updater/BackupManager.php';
require_once __DIR__ . '/../updater/MigrationRunner.php';
require_once __DIR__ . '/../updater/HealthChecker.php';

class UpdaterService
{
    /**
     * Check if a newer version of EduCore is available
     */
    public static function checkUpdate(bool $forceRefresh = false): array
    {
        return UpdateChecker::check($forceRefresh);
    }

    /**
     * Download, verify, backup, and apply software update package
     */
    public static function applyUpdate(
        string $downloadUrl,
        string $expectedSha256 = '',
        string $expectedSignature = '',
        string $targetVersion = ''
    ): array {
        if (empty($targetVersion)) {
            $check = self::checkUpdate(true);
            $targetVersion = $check['latest_version'] ?? '1.0.1';
            if (empty($downloadUrl)) {
                $downloadUrl = $check['download_url'] ?? '';
            }
            if (empty($expectedSha256)) {
                $expectedSha256 = $check['sha256'] ?? '';
            }
            if (empty($expectedSignature)) {
                $expectedSignature = $check['signature'] ?? '';
            }
        }

        $installer = new UpdateInstaller();
        return $installer->applyUpdate($targetVersion, $downloadUrl, $expectedSha256, $expectedSignature);
    }
}
