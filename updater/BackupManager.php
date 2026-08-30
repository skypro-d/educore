<?php
declare(strict_types=1);

/**
 * BackupManager — Pre-update database and application backup creator
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../version.php';

final class BackupManager
{
    private PDO $db;
    private string $storageDir;
    private int $retentionLimit;

    public function __construct(?PDO $db = null, int $retentionLimit = 3)
    {
        $this->db = $db ?? Database::connect();
        $this->retentionLimit = $retentionLimit;

        // Try primary storage directory, fallback to writable alternatives
        $candidates = [
            dirname(__DIR__) . '/storage/backups',
            dirname(__DIR__) . '/uploads/backups',
            dirname(__DIR__) . '/config/cache/backups',
            sys_get_temp_dir() . '/educore_backups'
        ];

        $this->storageDir = $candidates[0];
        foreach ($candidates as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                $this->storageDir = $dir;
                break;
            }
        }
    }

    /**
     * Create full pre-update snapshot (Database + Application files)
     *
     * @param string $currentVersion
     * @return array ['success' => bool, 'backup_dir' => string, 'db_file' => string, 'files_zip' => string, 'total_size_bytes' => int, 'error' => string]
     */
    public function createBackup(string $currentVersion = ''): array
    {
        $version = $currentVersion ?: (defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0');
        $timestamp = date('Ymd_His');
        $targetDir = $this->storageDir . '/backup_v' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $version) . '_' . $timestamp;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $error = '';
        $dbFile = $targetDir . '/database_snapshot.sql';
        $filesZip = $targetDir . '/application_files.zip';

        // 1. Dump Database
        try {
            $this->dumpDatabase($dbFile);
        } catch (Throwable $e) {
            $error = "DB Dump Notice: " . $e->getMessage();
        }

        // 2. Archive Application Files
        try {
            $this->archiveFiles($filesZip);
        } catch (Throwable $e) {
            $error .= ($error ? '; ' : '') . "File Archive Notice: " . $e->getMessage();
        }

        // 3. Metadata manifest
        $manifest = [
            'version' => $version,
            'created_at' => date('Y-m-d H:i:s'),
            'db_file' => basename($dbFile),
            'files_zip' => basename($filesZip),
            'db_size' => file_exists($dbFile) ? filesize($dbFile) : 0,
            'files_size' => file_exists($filesZip) ? filesize($filesZip) : 0
        ];
        @file_put_contents($targetDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $totalSize = ($manifest['db_size'] ?? 0) + ($manifest['files_size'] ?? 0);

        // 4. Prune old backups
        $this->pruneOldBackups();

        $dbOk = file_exists($dbFile) && filesize($dbFile) > 0;
        $success = $dbOk || is_dir($targetDir);

        return [
            'success' => $success,
            'backup_dir' => $targetDir,
            'db_file' => $dbFile,
            'files_zip' => $filesZip,
            'total_size_bytes' => $totalSize,
            'error' => $error
        ];
    }

    /**
     * Export database schema and table rows using PDO
     */
    public function dumpDatabase(string $outputFile): void
    {
        $tables = [];
        try {
            $stmt = $this->db->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        } catch (Throwable $e) {
            // Fallback
        }

        $sql = "-- EduCore Pre-Update Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            try {
                $createStmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createStmt ? $createStmt->fetch(PDO::FETCH_NUM) : null;
                $createSql = $createRow[1] ?? '';

                if (!empty($createSql)) {
                    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $sql .= $createSql . ";\n\n";

                    $rowsStmt = $this->db->query("SELECT * FROM `{$table}`");
                    if ($rowsStmt) {
                        while ($data = $rowsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $columns = array_map(fn($col) => "`" . str_replace("`", "``", (string)$col) . "`", array_keys($data));
                            $values = array_map(function($val) {
                                if ($val === null) return 'NULL';
                                return $this->db->quote((string)$val);
                            }, array_values($data));

                            $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                        }
                    }
                    $sql .= "\n";
                }
            } catch (Throwable $t) {
                // Continue dumping other tables safely
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $bytes = @file_put_contents($outputFile, $sql, LOCK_EX);
        if ($bytes === false) {
            throw new RuntimeException("Failed writing database dump to {$outputFile}.");
        }
    }

    /**
     * Archive core codebase into ZIP archive
     */
    private function archiveFiles(string $zipPath): void
    {
        if (!class_exists('ZipArchive')) {
            @file_put_contents(dirname($zipPath) . '/archive_note.txt', "ZipArchive extension not available. Database snapshot was preserved.");
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @file_put_contents(dirname($zipPath) . '/archive_note.txt', "Could not open zip archive for writing. Database snapshot was preserved.");
            return;
        }

        $rootDir = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
        $directoriesToInclude = ['controllers', 'models', 'views', 'config', 'updater', 'admin', 'parent', 'student', 'teacher', 'webhook', 'database'];
        $filesToInclude = ['version.php', 'index.php'];

        foreach ($filesToInclude as $file) {
            $fullPath = $rootDir . '/' . $file;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                $zip->addFile($fullPath, $file);
            }
        }

        foreach ($directoriesToInclude as $dirName) {
            $dirPath = $rootDir . '/' . $dirName;
            if (is_dir($dirPath) && is_readable($dirPath)) {
                try {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );

                    foreach ($iterator as $item) {
                        $itemPath = $item->getRealPath();
                        if (!$itemPath || !is_readable($itemPath)) continue;

                        $relativePath = substr($itemPath, strlen($rootDir) + 1);
                        $relativePath = str_replace('\\', '/', (string)$relativePath);

                        // Skip sensitive files, backups, caches or git directories
                        if (str_contains($relativePath, 'config/cache/') || 
                            str_contains($relativePath, 'storage/backups/') ||
                            str_contains($relativePath, 'storage/updates/') ||
                            str_contains($relativePath, '.lock') || 
                            str_contains($relativePath, '.git')) {
                            continue;
                        }

                        if ($item->isDir()) {
                            $zip->addEmptyDir($relativePath);
                        } else {
                            $zip->addFile($itemPath, $relativePath);
                        }
                    }
                } catch (Throwable $dirEx) {
                    // Continue with other directories
                }
            }
        }

        @$zip->close();
    }

    /**
     * Remove older backups beyond retention limit
     */
    private function pruneOldBackups(): void
    {
        try {
            $dirs = glob($this->storageDir . '/backup_v*', GLOB_ONLYDIR) ?: [];
            if (count($dirs) <= $this->retentionLimit) {
                return;
            }

            usort($dirs, fn($a, $b) => filemtime($b) <=> filemtime($a));

            $toRemove = array_slice($dirs, $this->retentionLimit);
            foreach ($toRemove as $oldDir) {
                $this->recursiveDeleteDir($oldDir);
            }
        } catch (Throwable $ignore) {}
    }

    /**
     * Recursively delete directory
     */
    public function recursiveDeleteDir(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->recursiveDeleteDir($path);
            } else {
                @unlink($path);
            }
        }
        return @rmdir($dir);
    }
}
