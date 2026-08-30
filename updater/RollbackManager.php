<?php
declare(strict_types=1);

/**
 * RollbackManager — System restoration engine on update failure
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/database.php';

final class RollbackManager
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Restore system from a backup snapshot directory
     *
     * @param string $backupDir
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function rollback(string $backupDir): array
    {
        if (!is_dir($backupDir)) {
            return [
                'success' => false,
                'message' => "Backup directory not found at {$backupDir}",
                'errors' => ["Backup directory missing."]
            ];
        }

        $errors = [];
        $filesRestored = false;
        $dbRestored = false;

        // 1. Restore Application Files from ZIP
        $filesZip = $backupDir . '/application_files.zip';
        if (file_exists($filesZip)) {
            try {
                $zip = new ZipArchive();
                if ($zip->open($filesZip) === true) {
                    $rootDir = dirname(__DIR__);
                    $zip->extractTo($rootDir);
                    $zip->close();
                    $filesRestored = true;
                } else {
                    $errors[] = "Failed opening application backup zip.";
                }
            } catch (Throwable $e) {
                $errors[] = "File extraction error: " . $e->getMessage();
            }
        }

        // 2. Restore Database from SQL snapshot
        $dbSqlFile = $backupDir . '/database_snapshot.sql';
        if (file_exists($dbSqlFile)) {
            try {
                $sql = file_get_contents($dbSqlFile);
                if ($sql !== false && trim($sql) !== '') {
                    $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
                    $queries = preg_split('/;\s*[\r\n]+/', $sql);
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if ($query !== '' && !str_starts_with($query, '--')) {
                            $this->db->exec($query);
                        }
                    }
                    $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
                    $dbRestored = true;
                }
            } catch (Throwable $e) {
                $errors[] = "Database restore error: " . $e->getMessage();
            }
        }

        // 3. Remove maintenance lock
        $maintLock = dirname(__DIR__) . '/storage/maintenance.lock';
        if (file_exists($maintLock)) {
            @unlink($maintLock);
        }

        $success = $filesRestored && $dbRestored;

        return [
            'success' => $success,
            'message' => $success ? 'System successfully rolled back to previous stable state.' : 'Partial rollback encountered issues.',
            'files_restored' => $filesRestored,
            'db_restored' => $dbRestored,
            'errors' => $errors
        ];
    }
}
