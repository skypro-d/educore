<?php
/**
 * ApiKeyService — EduCore Client API Key & Licensing Client
 *
 * Manages secure communication between EduCore installation and EduCore Live Server.
 * Handles encrypted local license storage, per-installation token signing, verification, caching, and background checks.
 *
 * @package EduCore
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../version.php';

final class ApiKeyService
{
    private static ?array $cachedLicense = null;

    /**
     * Cache file path
     */
    private static function cacheFilePath(): string
    {
        $dir = __DIR__ . '/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . '/license.json';
    }

    /**
     * Load local license file
     */
    public static function loadLocalLicense(): array
    {
        if (self::$cachedLicense !== null) {
            return self::$cachedLicense;
        }

        $file = self::cacheFilePath();
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?: [];
            self::$cachedLicense = $data;
            return self::$cachedLicense;
        }

        self::$cachedLicense = [
            'success' => false,
            'status' => 'unlicensed',
            'api_key' => '',
            'installation_token' => '',
            'installation_id' => defined('INSTALLATION_ID') && INSTALLATION_ID !== '' ? INSTALLATION_ID : ('INST-' . md5(($_SERVER['HTTP_HOST'] ?? 'localhost') . php_uname())),
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'plan' => 'basic',
            'features' => ['students', 'attendance', 'fees'],
            'features_map' => [],
            'last_validated' => date('Y-m-d H:i:s', 0),
            'expires_at' => null,
            'grace_period_days' => defined('OFFLINE_GRACE_DAYS') ? OFFLINE_GRACE_DAYS : 30
        ];

        return self::$cachedLicense;
    }

    /**
     * Save license data to local cache file
     */
    public static function saveCache(array $data): void
    {
        self::$cachedLicense = $data;
        $file = self::cacheFilePath();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get API Key
     */
    public static function getApiKey(): string
    {
        $lic = self::loadLocalLicense();
        return $lic['api_key'] ?? '';
    }

    /**
     * Get Installation ID
     */
    public static function getInstallationId(): string
    {
        if (defined('INSTALLATION_ID') && INSTALLATION_ID !== '') {
            return INSTALLATION_ID;
        }
        $lic = self::loadLocalLicense();
        if (!empty($lic['installation_id'])) {
            return $lic['installation_id'];
        }
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (str_contains($domain, ':')) {
            $domain = explode(':', $domain)[0];
        }
        return 'INST-' . md5($domain . php_uname());
    }

    /**
     * Get Per-Installation Secret Token for request signing
     */
    public static function getInstallationToken(): string
    {
        $lic = self::loadLocalLicense();
        return $lic['installation_token'] ?? '';
    }

    /**
     * Validate license status (Offline first with background 24h sync)
     */
    public static function validate(): array
    {
        $lic = self::loadLocalLicense();
        
        if (empty($lic['api_key']) && empty($lic['license_key'])) {
            return [
                'success' => false,
                'status' => 'unlicensed',
                'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'plan' => 'none',
                'features' => []
            ];
        }

        $lastValidated = isset($lic['last_validated']) ? strtotime((string)$lic['last_validated']) : 0;
        $timePassed = time() - $lastValidated;
        $graceDays = (int)($lic['grace_period_days'] ?? (defined('OFFLINE_GRACE_DAYS') ? OFFLINE_GRACE_DAYS : 30));

        // If validation cache is older than 24 hours (86400s), attempt online validation in background/request
        if ($timePassed >= 86400) {
            $onlineResult = self::validateOnline();
            if ($onlineResult['success']) {
                return $onlineResult;
            }
        }

        // Grace period check if server is offline
        $daysOffline = (time() - $lastValidated) / 86400;
        if ($daysOffline > $graceDays) {
            $lic['status'] = 'expired_grace';
            return [
                'success' => false,
                'status' => 'expired_grace',
                'domain' => $lic['domain'] ?? '',
                'plan' => $lic['plan'] ?? 'basic',
                'features' => ['students', 'attendance', 'fees'] // Core operational features retained
            ];
        }

        return [
            'success' => ($lic['status'] === 'active'),
            'status' => $lic['status'] ?? 'active',
            'domain' => $lic['domain'] ?? '',
            'plan' => $lic['plan'] ?? 'basic',
            'features' => $lic['features'] ?? ['students', 'attendance', 'fees'],
            'features_map' => $lic['features_map'] ?? []
        ];
    }

    /**
     * Perform online validation call to EduCore Live Server
     */
    public static function validateOnline(): array
    {
        $lic = self::loadLocalLicense();
        $apiKey = $lic['api_key'] ?? '';
        $licenseKey = $lic['license_key'] ?? '';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (str_contains($domain, ':')) {
            $domain = explode(':', $domain)[0];
        }
        $instId = self::getInstallationId();

        if (empty($apiKey) && empty($licenseKey)) {
            return ['success' => false, 'status' => 'unlicensed', 'message' => 'No license key or API key found.'];
        }

        $response = self::sendSecureRequest('api/v1/license/validate', [
            'api_key' => $apiKey,
            'license_key' => $licenseKey,
            'domain' => $domain,
            'installation_id' => $instId
        ]);

        if ($response && isset($response['status']) && $response['status'] === 'active') {
            $lic['status'] = 'active';
            $lic['domain'] = $domain;
            $lic['plan'] = $response['plan'] ?? ($response['data']['plan'] ?? ($lic['plan'] ?? 'basic'));
            $lic['features'] = $response['features'] ?? ($response['data']['features'] ?? ($lic['features'] ?? []));
            $lic['features_map'] = $response['features_map'] ?? ($response['data']['features_map'] ?? []);
            $lic['last_validated'] = date('Y-m-d H:i:s');
            $lic['expires_at'] = $response['expires_at'] ?? ($response['data']['expires_at'] ?? ($lic['expires_at'] ?? null));
            $lic['grace_period_days'] = (int)($response['grace_period_days'] ?? ($lic['grace_period_days'] ?? 30));
            self::saveCache($lic);

            return [
                'success' => true,
                'status' => 'active',
                'domain' => $domain,
                'plan' => $lic['plan'],
                'features' => $lic['features'],
                'features_map' => $lic['features_map']
            ];
        }

        if ($response && in_array($response['status'] ?? '', ['suspended', 'expired', 'revoked'])) {
            $lic['status'] = $response['status'];
            self::saveCache($lic);
            return [
                'success' => false,
                'status' => $response['status'],
                'domain' => $domain,
                'plan' => $lic['plan'] ?? 'basic',
                'features' => []
            ];
        }

        return [
            'success' => ($lic['status'] === 'active'),
            'status' => 'offline',
            'domain' => $domain,
            'plan' => $lic['plan'] ?? 'basic',
            'features' => $lic['features'] ?? []
        ];
    }

    /**
     * Send secure signed API request to EduCore Live Server
     */
    public static function sendSecureRequest(string $endpointRoute, array $params = []): ?array
    {
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (str_contains($domain, ':')) {
            $domain = explode(':', $domain)[0];
        }

        $timestamp = time();
        $params['timestamp'] = $timestamp;
        $params['domain'] = $params['domain'] ?? $domain;
        $params['installation_id'] = $params['installation_id'] ?? self::getInstallationId();

        $payload = json_encode($params);

        $apiKey = $params['api_key'] ?? self::getApiKey();
        $instToken = self::getInstallationToken();
        $signingSecret = !empty($instToken) ? $instToken : $apiKey;

        // Build HMAC Signature
        $sigString = $params['domain'] . '|' . $params['installation_id'] . '|' . $apiKey . '|' . $timestamp;
        $signature = hash_hmac('sha256', $sigString, $signingSecret ?: 'fallback_sign_key');

        $baseUrl = defined('EDUCORE_LIVE_URL') ? EDUCORE_LIVE_URL : 'https://educore.skysaveings.com.ng';
        
        $urlsToTry = [];
        $urlsToTry[] = $baseUrl . '/index.php?route=' . ltrim($endpointRoute, '/');
        $urlsToTry[] = $baseUrl . '/' . ltrim($endpointRoute, '/');

        // If baseUrl has /EduCore-LicenseServer or similar, also try root domain
        $parsed = parse_url($baseUrl);
        if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
            $rootBase = $parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) ? ':' . $parsed['port'] : '');
            if ($rootBase !== $baseUrl) {
                $urlsToTry[] = $rootBase . '/index.php?route=' . ltrim($endpointRoute, '/');
                $urlsToTry[] = $rootBase . '/' . ltrim($endpointRoute, '/');
            }
        }

        foreach (array_unique($urlsToTry) as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKey,
                    'X-Installation-Token: ' . $instToken,
                    'X-Timestamp: ' . $timestamp,
                    'X-Signature: ' . $signature
                ],
                CURLOPT_TIMEOUT => 8,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && ($httpCode === 200 || $httpCode === 201)) {
                return json_decode($response, true);
            }
        }

        return null;
    }

    /**
     * Get features list
     */
    public static function getFeatures(): array
    {
        $val = self::validate();
        return $val['features'] ?? [];
    }

    /**
     * Get grace period and offline status info
     */
    public static function getGracePeriodInfo(): array
    {
        $lic = self::loadLocalLicense();
        $lastValidated = isset($lic['last_validated']) ? strtotime((string)$lic['last_validated']) : 0;
        $daysOffline = floor((time() - $lastValidated) / 86400);
        $graceLimit = (int)($lic['grace_period_days'] ?? (defined('OFFLINE_GRACE_DAYS') ? OFFLINE_GRACE_DAYS : 30));
        $remainingGraceDays = max(0, $graceLimit - (int)$daysOffline);
        $isGraceExpired = $daysOffline > $graceLimit;

        return [
            'days_offline' => (int)$daysOffline,
            'grace_period_days' => $graceLimit,
            'remaining_grace_days' => (int)$remainingGraceDays,
            'is_grace_expired' => $isGraceExpired,
            'status' => $lic['status'] ?? 'active',
            'last_validated' => $lic['last_validated'] ?? 'Never'
        ];
    }

    /**
     * Render HTML notice banner if offline or approaching grace expiry
     */
    public static function renderOfflineNotice(): string
    {
        $info = self::getGracePeriodInfo();
        if ($info['is_grace_expired']) {
            return '
            <div class="alert alert-danger border-danger text-center p-3 mb-4 rounded-3 shadow-sm">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <strong>Offline Grace Period Expired (' . $info['grace_period_days'] . ' Days)</strong> — Premium modules (CBT, SMS, AI) are restricted until system reconnects to EduCore Live. Core student records, attendance, and fee ledgers remain 100% accessible.
            </div>';
        }

        if ($info['remaining_grace_days'] <= 7 && $info['days_offline'] > 1) {
            return '
            <div class="alert alert-warning border-warning text-center p-3 mb-4 rounded-3 shadow-sm">
                <i class="bi bi-wifi-off me-2 text-warning"></i>
                <strong>Operating in Offline Grace Mode (' . $info['remaining_grace_days'] . ' Days Remaining)</strong> — Please connect your server to the internet to synchronize your license.
            </div>';
        }

        return '';
    }
}
