<?php
declare(strict_types=1);

/**
 * UpdateChecker — Remote version & release query engine
 *
 * @package EduCore\Updater
 */

require_once __DIR__ . '/../config/ApiKeyService.php';
require_once __DIR__ . '/../version.php';

final class UpdateChecker
{
    private static function cacheFilePath(): string
    {
        $dir = dirname(__DIR__) . '/config/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . '/update_check.json';
    }

    /**
     * Check for available updates
     *
     * @param bool $forceRefresh Bypass local 6-hour cache if true
     * @return array
     */
    public static function check(bool $forceRefresh = false): array
    {
        $cacheFile = self::cacheFilePath();

        // Check local cache (5 minutes = 300 seconds)
        if (!$forceRefresh && file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['checked_at'])) {
                if ((time() - strtotime((string)$cached['checked_at'])) < 300) {
                    return $cached;
                }
            }
        }

        $currentVersion = defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0';
        $normalizedCurrent = ltrim((string)$currentVersion, 'v');
        $channel = defined('RELEASE_CHANNEL') ? RELEASE_CHANNEL : 'stable';
        $instId = ApiKeyService::getInstallationId();
        $apiKey = ApiKeyService::getApiKey();

        $response = ApiKeyService::sendSecureRequest('api/v1/updates/check', [
            'installation_id' => $instId,
            'api_key' => $apiKey,
            'current_version' => $normalizedCurrent,
            'release_channel' => $channel,
            'php_version' => PHP_VERSION
        ]);

        if ($response && ($response['success'] ?? false)) {
            $latestVersion = ltrim((string)($response['latest_version'] ?? $normalizedCurrent), 'v');
            $isNewer = version_compare($latestVersion, $normalizedCurrent, '>');

            $data = [
                'success' => true,
                'update_available' => $isNewer,
                'current_version' => $normalizedCurrent,
                'latest_version' => $latestVersion,
                'release_channel' => $response['release_channel'] ?? $channel,
                'mandatory' => (bool)($response['mandatory'] ?? false),
                'minimum_php_version' => $response['minimum_php_version'] ?? '8.3.0',
                'release_date' => $response['release_date'] ?? date('Y-m-d'),
                'release_notes' => $response['release_notes'] ?? 'General maintenance and feature enhancements.',
                'checksum' => $response['checksum'] ?? ($response['sha256'] ?? ''),
                'sha256' => $response['sha256'] ?? ($response['checksum'] ?? ''),
                'signature' => $response['signature'] ?? '',
                'download_url' => $response['download_url'] ?? '',
                'checked_at' => date('Y-m-d H:i:s')
            ];

            file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }

        // Offline or unreachable fallback
        return [
            'success' => false,
            'update_available' => false,
            'current_version' => $currentVersion,
            'latest_version' => $currentVersion,
            'message' => 'Unable to contact EduCore Live Server for update check.',
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }
}
