<?php
/**
 * AIUsageManager — EduCore Standalone AI Request Manager
 *
 * Tracks AI feature usage limits locally and communicates with License Server.
 *
 * @package EduCore
 * @version 2.0.0
 */

require_once __DIR__ . '/ApiKeyService.php';
require_once __DIR__ . '/FeatureManager.php';

final class AIUsageManager
{
    /**
     * Check if AI usage is within allowed plan limit.
     *
     * @return array
     */
    public static function checkLimit(): array
    {
        if (!FeatureManager::hasFeature('ai_assistant')) {
            return [
                'allowed' => false,
                'message' => 'AI Assistant is not included in your current license plan. Upgrade to Professional or Enterprise.',
                'used' => 0,
                'limit' => 0
            ];
        }

        $licData = ApiKeyService::loadLocalLicense();
        $plan = strtolower($licData['plan'] ?? 'basic');

        if ($plan === 'enterprise') {
            return [
                'allowed' => true,
                'message' => 'Unlimited AI access on Enterprise Plan.',
                'used' => self::getLocalUsage(),
                'limit' => null
            ];
        }

        $monthlyLimit = FeatureManager::getFeatureLimit('ai') ?? 500;
        $currentUsage = self::getLocalUsage();

        if ($currentUsage >= $monthlyLimit) {
            return [
                'allowed' => false,
                'message' => "Monthly AI limit of {$monthlyLimit} requests reached. Resets on the 1st of next month.",
                'used' => $currentUsage,
                'limit' => $monthlyLimit
            ];
        }

        return [
            'allowed' => true,
            'message' => 'AI access allowed.',
            'used' => $currentUsage,
            'limit' => $monthlyLimit,
            'remaining' => $monthlyLimit - $currentUsage
        ];
    }

    /**
     * Increment local and server AI usage count.
     *
     * @return bool
     */
    public static function increaseUsage(): bool
    {
        $check = self::checkLimit();
        if (!$check['allowed']) {
            return false;
        }

        $file = self::usageFilePath();
        $month = date('Y-m');
        $data = self::loadUsageData();

        if (($data['month'] ?? '') !== $month) {
            $data = ['month' => $month, 'used' => 0];
        }

        $data['used'] = ($data['used'] ?? 0) + 1;
        file_put_contents($file, json_encode($data));

        // Optionally notify License Server asynchronously
        try {
            ApiKeyService::sendSecureRequest('api/v1/license/ai-usage', [
                'api_key' => ApiKeyService::getApiKey(),
                'increment' => 1
            ]);
        } catch (Exception $e) {
            // Ignore offline errors
        }

        return true;
    }

    /**
     * Reset monthly usage counter.
     */
    public static function resetMonthlyUsage(): void
    {
        $file = self::usageFilePath();
        $data = [
            'month' => date('Y-m'),
            'used' => 0
        ];
        file_put_contents($file, json_encode($data));
    }

    /**
     * Get current month's local usage count
     */
    private static function getLocalUsage(): int
    {
        $data = self::loadUsageData();
        $currentMonth = date('Y-m');

        if (($data['month'] ?? '') !== $currentMonth) {
            return 0;
        }

        return (int)($data['used'] ?? 0);
    }

    private static function loadUsageData(): array
    {
        $file = self::usageFilePath();
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?: [];
        }
        return ['month' => date('Y-m'), 'used' => 0];
    }

    private static function usageFilePath(): string
    {
        $dir = __DIR__ . '/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . '/ai_usage.json';
    }
}
