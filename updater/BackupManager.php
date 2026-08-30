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
        $this->storageDir = dirname(__DIR__) . '/storage/backups';
        $this->retentionLimit = $retentionLimit;

        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Create full pre-update snapshot (Database + Application files)
     *
     * @param string $currentVersion
     * @return array ['success' => bool, 'backup_dir' => string, 'db_file' => string, 'files_zip' => string, 'total_size_bytes' => int]
     */
    public function createBackup(string $currentVersion = ''): array
    {
        $version = $currentVersion ?: (defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0');
        $timestamp = date('Ymd_His');
        $targetDir = $this->storageDir . '/backup_v' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $version) . '_' . $timestamp;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // 1. Dump Database
        $dbFile = $targetDir . '/database_snapshot.sql';
        $this->dumpDatabase($dbFile);

        // 2. Archive Application Files
        $filesZip = $targetDir . '/application_files.zip';
        $this->archiveFiles($filesZip);

        // 3. Metadata manifest
        $manifest = [
            'version' => $version,
            'created_at' => date('Y-m-d H:i:s'),
            'db_file' => basename($dbFile),
            'files_zip' => basename($filesZip),
            'db_size' => file_exists($dbFile) ? filesize($dbFile) : 0,
            'files_size' => file_exists($filesZip) ? filesize($filesZip) : 0
        ];
        file_put_contents($targetDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $totalSize = ($manifest['db_size'] ?? 0) + ($manifest['files_size'] ?? 0);

        // 4. Prune old backups
        $this->pruneOldBackups();

        return [
            'success' => file_exists($dbFile) && file_exists($filesZip),
            'backup_dir' => $targetDir,
            'db_file' => $dbFile,
            'files_zip' => $filesZip,
            'total_size_bytes' => $totalSize
        ];
    }

    /**
     * Export database schema and table rows using PDO
     */
    public function dumpDatabase(string $outputFile): void
    {
        $tables = [];
        $stmt = $this->db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- EduCore Pre-Update Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // Get CREATE TABLE
            $createStmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            $createSql = $createRow[1] ?? '';

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            // Fetch Rows
            $rowsStmt = $this->db->query("SELECT * FROM `{$table}`");
            while ($data = $rowsStmt->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(fn($col) => "`" . str_replace("`", "``", $col) . "`", array_keys($data));
                $values = array_map(function($val) {
                    if ($val === null) return 'NULL';
                    return $this->db->quote((string)$val);
                }, array_values($data));

                $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        file_put_contents($outputFile, $sql, LOCK_EX);
    }

    /**
     * Archive core codebase into ZIP archive
     */
    private function archiveFiles(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create backup zip archive at {$zipPath}");
        }

        $rootDir = realpath(dirname(__DIR__));
        $directoriesToInclude = ['controllers', 'models', 'views', 'config', 'updater', 'admin', 'parent', 'student', 'teacher', 'webhook', 'database'];
        $filesToInclude = ['version.php', 'index.php'];

        foreach ($filesToInclude as $file) {
            $fullPath = $rootDir . '/' . $file;
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $file);
            }
        }

        foreach ($directoriesToInclude as $dirName) {
            $dirPath = $rootDir . '/' . $dirName;
            if (is_dir($dirPath)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $item) {
                    $itemPath = $item->getRealPath();
                    $relativePath = substr($itemPath, strlen($rootDir) + 1);
                    $relativePath = str_replace('\\', '/', $relativePath);

                    // Skip sensitive files or caches from backup zip
                    if (str_contains($relativePath, 'config/cache/') || str_contains($relativePath, '.lock')) {
                        continue;
                    }

                    if ($item->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($itemPath, $relativePath);
                    }
                }
            }
        }

        $zip->close();
    }

    /**
     * Remove older backups beyond retention limit
     */
    private function pruneOldBackups(): void
    {
        $dirs = glob($this->storageDir . '/backup_v*', GLOB_ONLYDIR) ?: [];
        if (count($dirs) <= $this->retentionLimit) {
            return;
        }

        // Sort by directory modification time descending
        usort($dirs, fn($a, $b) => filemtime($b) <=> filemtime($a));

        $toRemove = array_slice($dirs, $this->retentionLimit);
        foreach ($toRemove as $oldDir) {
            $this->recursiveDeleteDir($oldDir);
        }
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
