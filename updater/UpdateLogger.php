<?php
declare(strict_types=1);

/**
 * UpdateLogger — Structured update logging & telemetry reporter
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/ApiKeyService.php';

final class UpdateLogger
{
    private string $logFilePath;
    private array $inMemoryLogs = [];

    public function __construct(string $targetVersion)
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logFilePath = $logDir . '/update_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $targetVersion) . '_' . date('Ymd_His') . '.log';
    }

    /**
     * Record a log entry
     */
    public function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] [{$level}] {$message}";
        $this->inMemoryLogs[] = $entry;

        @file_put_contents($this->logFilePath, $entry . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Get all accumulated in-memory logs
     */
    public function getLogs(): array
    {
        return $this->inMemoryLogs;
    }

    /**
     * Get full log summary string
     */
    public function getSummary(): string
    {
        return implode("\n", $this->inMemoryLogs);
    }

    /**
     * Report update status summary to EduCore Live Server
     */
    public function reportToLive(string $fromVersion, string $toVersion, string $status, string $summary = ''): void
    {
        try {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            if (str_contains($domain, ':')) {
                $domain = explode(':', $domain)[0];
            }

            ApiKeyService::sendSecureRequest('api/v1/updates/log', [
                'installation_id' => ApiKeyService::getInstallationId(),
                'domain' => $domain,
                'from_version' => $fromVersion,
                'to_version' => $toVersion,
                'status' => $status,
                'log_summary' => $summary ?: $this->getSummary()
            ]);
        } catch (Throwable $e) {
            // Live reporting error is non-fatal to the update
        }
    }
}
