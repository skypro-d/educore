<?php
declare(strict_types=1);

/**
 * UpdateLock — Prevents concurrent or duplicate update executions
 *
 * @package EduCore\Updater
 */

final class UpdateLock
{
    private string $lockFile;
    private int $staleSeconds;

    public function __construct(?string $lockFile = null, int $staleSeconds = 1800)
    {
        $dir = dirname(__DIR__) . '/storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $this->lockFile = $lockFile ?? ($dir . '/update.lock');
        $this->staleSeconds = $staleSeconds;
    }

    /**
     * Check if an update is currently locked / running
     */
    public function isLocked(): bool
    {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        $mtime = filemtime($this->lockFile);
        if ($mtime !== false && (time() - $mtime) > $this->staleSeconds) {
            // Lock is stale (> 30 minutes old) — automatically release
            $this->release();
            return false;
        }

        return true;
    }

    /**
     * Acquire update execution lock
     *
     * @throws RuntimeException If lock cannot be acquired
     */
    public function acquire(string $targetVersion = ''): bool
    {
        if ($this->isLocked()) {
            throw new RuntimeException("An update is already in progress. Please wait for it to complete.");
        }

        $payload = json_encode([
            'pid' => getmypid(),
            'target_version' => $targetVersion,
            'acquired_at' => date('Y-m-d H:i:s'),
            'timestamp' => time()
        ], JSON_PRETTY_PRINT);

        $fp = @fopen($this->lockFile, 'x');
        if ($fp === false) {
            // Check again in case of race condition
            if ($this->isLocked()) {
                throw new RuntimeException("Could not acquire update lock. An update process is running.");
            }
            @file_put_contents($this->lockFile, $payload);
            return true;
        }

        fwrite($fp, $payload);
        fclose($fp);
        return true;
    }

    /**
     * Release update lock
     */
    public function release(): bool
    {
        if (file_exists($this->lockFile)) {
            return @unlink($this->lockFile);
        }
        return true;
    }
}
