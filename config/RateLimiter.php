<?php
declare(strict_types=1);

final class RateLimiter
{
    private static ?string $limitDir = null;

    private static function getLimitDir(): string
    {
        if (self::$limitDir === null) {
            $dir = __DIR__ . '/../logs/rate_limits';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
                @file_put_contents($dir . '/.htaccess', "Deny from all\n");
            }
            self::$limitDir = $dir;
        }
        return self::$limitDir;
    }

    /**
     * Check if client exceeds rate limit.
     *
     * @param string $key Identifier for rate limit bucket (e.g. 'login', 'api_scan')
     * @param int $maxAttempts Maximum number of attempts allowed in $decaySeconds window
     * @param int $decaySeconds Time window size in seconds
     * @return bool True if limit exceeded, false otherwise
     */
    public static function check(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ipHash = hash('sha256', $ip);
        $file = self::getLimitDir() . '/' . $key . '_' . $ipHash . '.json';

        $now = time();
        $attempts = [];

        if (is_file($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    // Filter out expired timestamps
                    foreach ($data as $timestamp) {
                        if ($now - $timestamp < $decaySeconds) {
                            $attempts[] = $timestamp;
                        }
                    }
                }
            }
        }

        if (count($attempts) >= $maxAttempts) {
            return true;
        }

        // Record current attempt
        $attempts[] = $now;
        @file_put_contents($file, json_encode($attempts));
        return false;
    }
}
