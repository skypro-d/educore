<?php
declare(strict_types=1);

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}

// Load environment file .env if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Strip quotes if any
            if (preg_match('/^"([^"]*)"$/', $value, $m) || preg_match("/^'([^']*)'$/", $value, $m)) {
                $value = $m[1];
            }
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('getEnvConfig')) {
    function getEnvConfig(string $key, string $default = ''): string
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string)$_SERVER[$key];
        }
        $val = @getenv($key);
        if ($val !== false && $val !== null && $val !== '') {
            return (string)$val;
        }
        return $default;
    }
}

require_once __DIR__ . '/Logger.php';

define('APP_NAME', 'EduCore');

$configuredBaseUrl = getEnvConfig('APP_BASE_URL');
if ($configuredBaseUrl === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $configuredBaseUrl = str_contains($scriptName, '/EduCore/') ? '/EduCore' : '';
}
define('BASE_URL', rtrim($configuredBaseUrl, '/'));

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

$hostName = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
$isLocalHost = str_starts_with($hostName, 'localhost')
    || str_starts_with($hostName, '127.0.0.1')
    || str_starts_with($hostName, '[::1]');

// Production Environment Verification
if (!$isLocalHost) {
    $requiredEnv = ['DB_HOST', 'DB_NAME', 'DB_USER'];
    foreach ($requiredEnv as $envVar) {
        if (getEnvConfig($envVar) === '') {
            if (!file_exists(__DIR__ . '/../.env')) {
                header('HTTP/1.1 500 Internal Server Error');
                exit("Configuration Error: Mandatory file <code>.env</code> is missing from EduCore root directory. Please delete <code>install/installation.lock</code> and re-run the Web Installer at <a href='../install'>/install</a>.");
            }
            header('HTTP/1.1 500 Internal Server Error');
            exit("Production Environment Configuration Error: Missing mandatory variable {$envVar} in <code>.env</code>.");
        }
    }
}

require_once __DIR__ . '/../version.php';

define('DB_HOST', getEnvConfig('DB_HOST', 'localhost'));
define('DB_NAME', getEnvConfig('DB_NAME', $isLocalHost ? 'school_admission_portal' : ''));
define('DB_USER', getEnvConfig('DB_USER', $isLocalHost ? 'root' : ''));
define('DB_PASS', getEnvConfig('DB_PASS', ''));

// EduCore Live & Licensing Configuration
define('EDUCORE_LIVE_URL', rtrim(getEnvConfig('EDUCORE_LIVE_URL', getEnvConfig('LICENSE_SERVER_URL', 'http://localhost/EduCore-LicenseServer')), '/'));
define('INSTALLATION_ID', getEnvConfig('INSTALLATION_ID', ''));
define('RELEASE_CHANNEL', getEnvConfig('RELEASE_CHANNEL', EDUCORE_DEFAULT_CHANNEL));
define('OFFLINE_GRACE_DAYS', (int)getEnvConfig('OFFLINE_GRACE_DAYS', '30'));
define('AUTO_UPDATE_ENABLED', filter_var(getEnvConfig('AUTO_UPDATE', 'false'), FILTER_VALIDATE_BOOLEAN));
define('CRON_SECRET', getEnvConfig('CRON_SECRET', ''));

define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: '');
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');
define('PAYMENT_ENVIRONMENT', getenv('PAYMENT_ENVIRONMENT') ?: 'test');

define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 465));
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'smtps');
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: '');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: APP_NAME);

define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE') ?: 'false', FILTER_VALIDATE_BOOLEAN));

date_default_timezone_set('Africa/Lagos');

// Global Error & Exception Handling
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    Logger::error($message);

    // Suppress minor notices and non-fatal warnings so page execution continues cleanly
    if (in_array($errno, [E_NOTICE, E_USER_NOTICE, E_WARNING, E_USER_WARNING, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        return true;
    }

    if (DEBUG_MODE) {
        echo "<pre>{$message}</pre>";
    } else {
        echo "<div style='padding:20px; background:#fee2e2; border:1px solid #f87171; color:#991b1b; border-radius:8px; margin:20px; font-family:sans-serif;'><strong>Server Notice:</strong> " . htmlspecialchars($errstr) . "</div>";
        exit;
    }
    return true;
});

set_exception_handler(function (Throwable $exception): void {
    $message = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\nStack trace:\n" . $exception->getTraceAsString();
    Logger::error($message);
    
    echo "<div style='padding:20px; background:#fee2e2; border:1px solid #f87171; color:#991b1b; border-radius:8px; margin:20px; font-family:sans-serif;'><h4 style='margin:0 0 10px 0; font-weight:bold;'>Application Error</h4><p style='margin:0 0 8px 0;'>" . htmlspecialchars($exception->getMessage()) . "</p><small style='color:#7f1d1d;'>File: " . htmlspecialchars($exception->getFile()) . " (Line " . $exception->getLine() . ")</small></div>";
    exit;
});

// Security Headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https: data:; frame-ancestors 'none';");
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Session timeout validation (1 hour default)
$sessionTimeout = (int)(getenv('SESSION_TIMEOUT') ?: 3600);
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    session_unset();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
$_SESSION['last_activity'] = time();
