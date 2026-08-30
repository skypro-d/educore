<?php
declare(strict_types=1);

/**
 * MigrationRunner — EduCore Database Migration Engine
 *
 * Scans database/migrations/ directory, compares against the migrations table,
 * executes pending migrations in deterministic order, tracks execution logs and checksums.
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

final class MigrationRunner
{
    private PDO $db;
    private string $migrationsDir;

    public function __construct(?PDO $db = null, ?string $migrationsDir = null)
    {
        $this->db = $db ?? Database::connect();
        $this->migrationsDir = $migrationsDir ?? dirname(__DIR__) . '/database/migrations';
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure the migrations tracking table exists
     */
    public function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` VARCHAR(191) NOT NULL,
            `batch` INT UNSIGNED NOT NULL DEFAULT 1,
            `checksum` VARCHAR(64) DEFAULT NULL,
            `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_migration_name` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->db->exec($sql);
    }

    /**
     * Get list of all migration filenames in database/migrations/
     */
    public function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }

        $files = scandir($this->migrationsDir) ?: [];
        $migrationFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (str_ends_with($file, '.sql') || str_ends_with($file, '.php')) {
                $migrationFiles[] = $file;
            }
        }

        sort($migrationFiles, SORT_NATURAL | SORT_FLAG_CASE);
        return $migrationFiles;
    }

    /**
     * Get array of already executed migration names from database
     */
    public function getExecutedMigrations(): array
    {
        $this->ensureMigrationsTable();
        $stmt = $this->db->query("SELECT `migration` FROM `migrations` ORDER BY `id` ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
     * Get list of pending migrations that have not run yet
     */
    public function getPendingMigrations(): array
    {
        $all = $this->getAllMigrationFiles();
        $executed = $this->getExecutedMigrations();
        return array_values(array_diff($all, $executed));
    }

    /**
     * Get the next batch number
     */
    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(`batch`) FROM `migrations`");
        $max = $stmt ? (int)$stmt->fetchColumn() : 0;
        return $max + 1;
    }

    /**
     * Execute all pending migrations
     *
     * @return array ['success' => bool, 'executed' => array, 'message' => string, 'errors' => array]
     */
    public function runPending(): array
    {
        $pending = $this->getPendingMigrations();
        if (empty($pending)) {
            return [
                'success' => true,
                'executed' => [],
                'message' => 'Database is up to date. No pending migrations.',
                'errors' => []
            ];
        }

        $batch = $this->getNextBatchNumber();
        $executed = [];
        $errors = [];

        foreach ($pending as $file) {
            $filePath = $this->migrationsDir . '/' . $file;
            $checksum = file_exists($filePath) ? hash_file('sha256', $filePath) : '';

            try {
                if (str_ends_with($file, '.sql')) {
                    $this->executeSqlFile($filePath);
                } elseif (str_ends_with($file, '.php')) {
                    $this->executePhpFile($filePath);
                }

                // Record into migrations table
                $stmt = $this->db->prepare("
                    INSERT INTO `migrations` (`migration`, `batch`, `checksum`, `executed_at`)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$file, $batch, $checksum]);

                $executed[] = $file;
            } catch (Throwable $e) {
                $errorMsg = "Migration failed on {$file}: " . $e->getMessage();
                $errors[] = $errorMsg;

                return [
                    'success' => false,
                    'executed' => $executed,
                    'message' => $errorMsg,
                    'errors' => $errors,
                    'failed_file' => $file
                ];
            }
        }

        return [
            'success' => true,
            'executed' => $executed,
            'message' => 'Successfully executed ' . count($executed) . ' migration(s).',
            'errors' => []
        ];
    }

    /**
     * Safely execute an SQL migration file query-by-query
     */
    private function executeSqlFile(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        if ($sql === false || trim($sql) === '') {
            return;
        }

        // 1. Strip UTF-8 BOM if present
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

        // 2. Remove multi-line comments /* ... */
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        // 3. Remove single-line comments (-- or #) line by line
        $lines = explode("\n", $sql);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '--') || str_starts_with($trimmedLine, '#')) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $cleanSql = implode("\n", $cleanLines);

        // 4. Split queries by semicolon followed by line break or end of file
        $queries = preg_split('/;\s*[\r\n]+/', $cleanSql);

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query !== '') {
                try {
                    $this->db->exec($query);
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    // Ignore benign already exists or duplicate key errors during additive migrations
                    if (!str_contains($msg, 'already exists') && 
                        !str_contains($msg, 'Duplicate column') && 
                        !str_contains($msg, 'Duplicate key') &&
                        !str_contains($msg, 'Duplicate entry')) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Safely execute a PHP migration file
     */
    private function executePhpFile(string $filePath): void
    {
        $pdo = $this->db;
        $result = require $filePath;
        if (is_callable($result)) {
            $result($pdo);
        }
    }

    /**
     * Seed baseline migration without executing it if schema was imported directly
     */
    public function recordBaseline(string $file): void
    {
        $this->ensureMigrationsTable();
        $filePath = $this->migrationsDir . '/' . $file;
        $checksum = file_exists($filePath) ? hash_file('sha256', $filePath) : '';

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO `migrations` (`migration`, `batch`, `checksum`, `executed_at`)
            VALUES (?, 1, ?, NOW())
        ");
        $stmt->execute([$file, $checksum]);
    }

    /**
     * Helper to ensure database is up to date with all pending migrations
     */
    public static function ensureUpToDate(?PDO $pdo = null): void
    {
        try {
            $runner = new self($pdo);
            $pending = $runner->getPendingMigrations();
            if (!empty($pending)) {
                $runner->runPending();
            }
        } catch (Throwable $e) {
            // Silently fallback if database is not reachable or still installing
        }
    }
}
