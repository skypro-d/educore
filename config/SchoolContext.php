<?php
/**
 * SchoolContext — EduCore Single School Information Resolver
 *
 * Provides current institution settings from `school_settings` table.
 */
final class SchoolContext
{
    private static ?int $schoolId = 1;
    private static ?array $schoolInfo = null;

    public static function isResolved(): bool
    {
        return true;
    }

    public static function id(): int
    {
        return self::$schoolId ?? 1;
    }

    /**
     * Get single school settings profile
     */
    public static function info(): array
    {
        if (self::$schoolInfo !== null) {
            return self::$schoolInfo;
        }

        try {
            $db = Database::connect();
            $stmt = $db->query("SELECT * FROM school_settings WHERE id = 1 LIMIT 1");
            $info = $stmt->fetch();
            if ($info) {
                self::$schoolInfo = $info;
            } else {
                self::$schoolInfo = [
                    'id' => 1,
                    'school_name' => 'EduCore School',
                    'school_code' => 'SCH-001',
                    'motto' => 'Excellence & Innovation',
                    'currency' => 'NGN',
                    'timezone' => 'Africa/Lagos'
                ];
            }
        } catch (Throwable $e) {
            self::$schoolInfo = [];
        }

        return self::$schoolInfo;
    }

    public static function set(int $id): void
    {
        self::$schoolId = $id;
        self::$schoolInfo = null;
    }

    public static function reset(): void
    {
        self::$schoolId = 1;
        self::$schoolInfo = null;
    }
}
