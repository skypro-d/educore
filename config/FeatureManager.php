<?php
/**
 * FeatureManager — EduCore Standalone Client Feature Gate
 *
 * Controls feature access and limits within the EduCore installation based on
 * the active license plan and local license cache.
 *
 * @package EduCore
 */
declare(strict_types=1);

require_once __DIR__ . '/ApiKeyService.php';

final class FeatureManager
{
    /**
     * Core operational features that are ALWAYS enabled regardless of license plan or offline state
     */
    private const CORE_FEATURES = [
        'student_management',
        'students',
        'teacher_management',
        'teachers',
        'attendance',
        'fees',
        'classes',
        'academic',
        'school_records'
    ];

    /**
     * Feature plan defaults map
     */
    private const PLAN_FEATURES = [
        'basic' => [
            'student_management' => true,
            'students' => true,
            'teacher_management' => true,
            'teachers' => true,
            'attendance' => true,
            'fees' => true,
            'classes' => true,
            'academic' => true,
            'cbt' => false,
            'sms' => false,
            'qr_attendance' => false,
            'reports' => false,
            'analytics' => false,
            'parent_portal' => false,
            'hostel' => false,
            'payroll' => false,
            'library' => false,
            'transport' => false,
            'mobile_api' => false,
            'ai_assistant' => false,
            'custom_modules' => false,
        ],
        'professional' => [
            'student_management' => true,
            'students' => true,
            'teacher_management' => true,
            'teachers' => true,
            'attendance' => true,
            'fees' => true,
            'classes' => true,
            'academic' => true,
            'cbt' => true,
            'sms' => true,
            'qr_attendance' => true,
            'reports' => true,
            'analytics' => true,
            'parent_portal' => true,
            'library' => true,
            'hostel' => false,
            'payroll' => false,
            'transport' => false,
            'mobile_api' => false,
            'ai_assistant' => true,
            'custom_modules' => false,
        ],
        'enterprise' => [
            'student_management' => true,
            'students' => true,
            'teacher_management' => true,
            'teachers' => true,
            'attendance' => true,
            'fees' => true,
            'classes' => true,
            'academic' => true,
            'cbt' => true,
            'sms' => true,
            'qr_attendance' => true,
            'reports' => true,
            'analytics' => true,
            'parent_portal' => true,
            'hostel' => true,
            'payroll' => true,
            'library' => true,
            'transport' => true,
            'mobile_api' => true,
            'ai_assistant' => true,
            'custom_modules' => true,
        ]
    ];

    /**
     * Check if the current license has access to a feature.
     *
     * @param string $feature
     * @return bool
     */
    public static function hasFeature(string $feature): bool
    {
        $normalized = strtolower(trim($feature));

        // 1. Core operational features are never locked
        if (in_array($normalized, self::CORE_FEATURES, true)) {
            return true;
        }

        // 2. Validate current license state
        $validation = ApiKeyService::validate();
        if (!$validation['success']) {
            return false;
        }

        // 3. Check dynamically synced features from license cache
        $features = $validation['features'] ?? [];
        if (in_array($normalized, $features, true)) {
            return true;
        }

        // 4. Check detailed features_map if present
        $featuresMap = $validation['features_map'] ?? [];
        if (isset($featuresMap[$normalized])) {
            $status = $featuresMap[$normalized]['status'] ?? $featuresMap[$normalized];
            return $status === 'enabled' || $status === true || $status === 1 || $status === '1';
        }

        // 5. Fallback to plan default matrix
        $plan = strtolower($validation['plan'] ?? 'basic');
        $planMatrix = self::PLAN_FEATURES[$plan] ?? self::PLAN_FEATURES['basic'];

        return (bool)($planMatrix[$normalized] ?? false);
    }

    /**
     * Get numeric limit for a feature (e.g. max students, teachers)
     */
    public static function getFeatureLimit(string $feature): ?int
    {
        $validation = ApiKeyService::validate();
        $featuresMap = $validation['features_map'] ?? [];
        $normalized = strtolower(trim($feature));

        if (isset($featuresMap[$normalized]['limit_value'])) {
            return $featuresMap[$normalized]['limit_value'] !== null ? (int)$featuresMap[$normalized]['limit_value'] : null;
        }

        return null;
    }

    /**
     * Check if current usage is within permitted feature limits
     */
    public static function checkUsage(string $feature, int $currentCount): bool
    {
        $limit = self::getFeatureLimit($feature);
        if ($limit === null) {
            return true;
        }
        return $currentCount < $limit;
    }
}
