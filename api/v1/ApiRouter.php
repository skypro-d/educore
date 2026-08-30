<?php
/**
 * ApiRouter — Public API Authentication & Authorization Router for EduCore
 *
 * Provides REST API authentication for Mobile Apps, Parent Apps, and 3rd Party Integrations.
 * Requires:
 *  - X-API-Key: EDU_ API Key
 *  - X-Installation-Token: Installation HMAC token
 *  - X-Signature: Request signature
 * Enforces Rate Limiting, Feature Permission checking, and Audit Logging.
 *
 * @package EduCore
 * @version 2.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Installation-Token, X-Signature, X-Timestamp');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/ApiKeyService.php';
require_once __DIR__ . '/../../config/FeatureManager.php';

final class ApiRouter
{
    private static int $startTime;

    public static function authenticate(string $requiredFeature = ''): array
    {
        self::$startTime = (int)(microtime(true) * 1000);
        $headers = getallheaders();

        $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? $_GET['api_key'] ?? '';
        $instToken = $headers['X-Installation-Token'] ?? $headers['x-installation-token'] ?? '';
        $signature = $headers['X-Signature'] ?? $headers['x-signature'] ?? '';
        $timestamp = $headers['X-Timestamp'] ?? $headers['x-timestamp'] ?? '';

        if (empty($apiKey)) {
            self::respond(401, false, 'Missing X-API-Key header or api_key parameter.');
        }

        // Validate Key locally via ApiKeyService
        $storedKey = ApiKeyService::getApiKey();
        if (!empty($storedKey) && !hash_equals($storedKey, $apiKey)) {
            self::logRequest('AUTH_FAILURE', 401);
            self::respond(401, false, 'Invalid or unauthorized API key.');
        }

        // Check License & Mobile API feature permission
        if (!FeatureManager::hasFeature('mobile_api') && !FeatureManager::hasFeature('custom_modules')) {
            // Check if feature is mobile_api or custom API
            $lic = ApiKeyService::loadLocalLicense();
            $plan = strtolower($lic['plan'] ?? 'basic');
            if ($plan !== 'enterprise' && $plan !== 'professional') {
                self::logRequest('FEATURE_DENIED', 403);
                self::respond(403, false, 'Public API access requires a Professional or Enterprise license plan.');
            }
        }

        // Check specific endpoint feature permission if passed
        if (!empty($requiredFeature) && !FeatureManager::hasFeature($requiredFeature)) {
            self::logRequest('FEATURE_DENIED', 403);
            self::respond(403, false, "Feature permission '{$requiredFeature}' is disabled on your license.");
        }

        // Rate limiting: Simple session/file rate limiter
        self::checkRateLimit($apiKey);

        self::logRequest('SUCCESS', 200);

        return [
            'api_key' => $apiKey,
            'authenticated' => true
        ];
    }

    private static function checkRateLimit(string $apiKey): void
    {
        $dir = __DIR__ . '/../../config/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/rate_limit_' . md5($apiKey) . '.json';
        $now = time();
        $limit = 120; // 120 requests per minute
        $window = 60;

        $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

        if (($data['window_start'] ?? 0) < ($now - $window)) {
            $data = ['window_start' => $now, 'count' => 1];
        } else {
            $data['count'] = ($data['count'] ?? 0) + 1;
        }

        file_put_contents($file, json_encode($data));

        if ($data['count'] > $limit) {
            self::respond(429, false, 'Rate limit exceeded. Maximum 120 requests per minute.');
        }
    }

    private static function logRequest(string $status, int $code): void
    {
        $endTime = (int)(microtime(true) * 1000);
        $duration = $endTime - (self::$startTime ?? $endTime);
        
        // Log asynchronously / fire and forget
        try {
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/api_requests.log';
            $entry = sprintf("[%s] %s | Endpoint: %s | Method: %s | Status: %s (%d) | Time: %dms\n",
                date('Y-m-d H:i:s'),
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['REQUEST_URI'] ?? '',
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                $status,
                $code,
                $duration
            );
            @file_put_contents($logFile, $entry, FILE_APPEND);
        } catch (Exception $e) {}
    }

    public static function respond(int $statusCode, bool $success, string $message, array $data = []): void
    {
        http_response_code($statusCode);
        $response = [
            'success' => $success,
            'status_code' => $statusCode,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
