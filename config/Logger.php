<?php
declare(strict_types=1);

final class Logger
{
    private static ?string $logFile = null;

    private static function getLogFile(): string
    {
        if (self::$logFile === null) {
            $dir = __DIR__ . '/../logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
                @file_put_contents($dir . '/.htaccess', "Deny from all\n");
            }
            self::$logFile = $dir . '/app.log';
        }
        return self::$logFile;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[{$timestamp}] [{$level}] [IP: {$ip}] {$message}{$contextStr}" . PHP_EOL;
        @file_put_contents(self::getLogFile(), $logLine, FILE_APPEND);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warn(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
}
