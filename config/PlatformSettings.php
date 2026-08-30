<?php
/**
 * config/PlatformSettings.php
 * Global SST Hub platform settings — NOT school-scoped.
 * Reads/writes to the `platform_settings` table.
 */
final class PlatformSettings
{
    private static array $cache = [];
    private static bool  $loaded = false;

    /**
     * Get a platform setting value.
     */
    public static function get(string $key, string $default = ''): string
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * Set / update a single platform setting.
     */
    public static function set(string $key, string $value): void
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "INSERT INTO platform_settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            $stmt->execute([$key, $value]);
            self::$cache[$key] = $value;
        } catch (Throwable $e) {
            error_log('[PlatformSettings] set() failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk-save an associative array of settings.
     */
    public static function bulkSet(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set((string) $key, (string) $value);
        }
    }

    /**
     * Return ALL settings as an associative array (for forms).
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$cache;
    }

    /**
     * Return settings filtered by group.
     */
    public static function group(string $group): array
    {
        if (!self::$loaded) {
            self::load();
        }
        $result = [];
        try {
            $db   = Database::connect();
            $stmt = $db->prepare("SELECT setting_key, setting_value FROM platform_settings WHERE setting_group = ?");
            $stmt->execute([$group]);
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {}
        return $result;
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private static function load(): void
    {
        self::$loaded = true;
        try {
            $db = Database::connect();
            foreach ($db->query("SELECT setting_key, setting_value FROM platform_settings") as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'] ?? '';
            }
        } catch (Throwable $e) {
            self::$cache = [];
        }
    }
}
