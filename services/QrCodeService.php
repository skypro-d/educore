<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/phpqrcode.php';

/**
 * Class QrCodeService
 *
 * Enterprise QR Code generator & token manager for EduCore.
 * Ensures consistent, high-contrast, scannable QR codes for Students and Staff
 * across ID cards, profile views, and mobile/USB scanners.
 */
final class QrCodeService
{
    public const DEFAULT_LEVEL = 'M'; // 15% error recovery
    public const DEFAULT_SIZE = 8;     // 8px per module for high DPI sharpness
    public const DEFAULT_MARGIN = 4;   // 4 modules quiet zone (ISO/IEC standard)

    /**
     * Resolve the public domain / host for attendance scan links.
     * Prioritizes active HTTP_HOST on live servers, then APP_URL, then database configurations.
     */
    public static function resolvePublicHost(): string
    {
        $httpHost = !empty($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : '';
        $httpHostNoPort = preg_replace('/:\d+$/', '', $httpHost);
        $isLocalRequest = ($httpHostNoPort === 'localhost' || str_starts_with($httpHostNoPort, '127.') || $httpHostNoPort === '::1' || $httpHostNoPort === '[::1]');

        // 1. If HTTP_HOST is present and is a public domain (live server HTTP request)
        if ($httpHost !== '' && !$isLocalRequest) {
            return $httpHost;
        }

        // 2. Check APP_URL from .env
        $envAppUrl = getEnvConfig('APP_URL');
        if ($envAppUrl !== '') {
            $parsed = parse_url($envAppUrl);
            if (!empty($parsed['host'])) {
                $h = $parsed['host'] . (!empty($parsed['port']) && $parsed['port'] !== 80 && $parsed['port'] !== 443 ? ':' . $parsed['port'] : '');
                $hNoPort = preg_replace('/:\d+$/', '', $h);
                if ($hNoPort !== 'localhost' && !str_starts_with($hNoPort, '127.')) {
                    return $h;
                }
            }
        }

        // 3. Check school_settings table: explicit school_domain (not default localhost)
        $dbDomain = trim((string) setting('school_domain', setting('domain', '')));
        if ($dbDomain !== '' && $dbDomain !== 'localhost' && !str_starts_with($dbDomain, '127.')) {
            $dbDomain = preg_replace('#^https?://#i', '', $dbDomain);
            $dbDomain = preg_replace('#/.*$#', '', $dbDomain);
            if ($dbDomain !== '') {
                return $dbDomain;
            }
        }

        // 4. If we are running in CLI / background (no HTTP_HOST) and school has a website
        if ($httpHost === '') {
            $dbWebsite = trim((string) setting('school_website', setting('website', '')));
            if ($dbWebsite !== '' && !str_contains($dbWebsite, 'localhost') && !str_contains($dbWebsite, '127.0.0.1')) {
                $parsed = parse_url($dbWebsite);
                if (!empty($parsed['host']) && $parsed['host'] !== 'localhost' && !str_starts_with($parsed['host'], '127.')) {
                    return $parsed['host'];
                }
            }
        }

        // 5. Fallback: whatever HTTP_HOST is or 'localhost'
        return $httpHost !== '' ? $httpHost : 'localhost';
    }

    /**
     * Resolve the protocol scheme (https vs http).
     * Defaults to https for any non-localhost host or behind SSL reverse proxies.
     */
    public static function resolvePublicScheme(string $host): string
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return 'https';
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
            return 'https';
        }
        if (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return 'https';
        }

        // Check if APP_URL explicitly specifies http://
        $envAppUrl = getEnvConfig('APP_URL');
        if ($envAppUrl !== '' && str_starts_with($envAppUrl, 'http://')) {
            return 'http';
        }

        // If host is a public domain (not localhost/127.0.0.1), modern live sites use HTTPS
        $hNoPort = preg_replace('/:\d+$/', '', $host);
        if ($hNoPort !== 'localhost' && !str_starts_with($hNoPort, '127.') && $hNoPort !== '::1' && $hNoPort !== '[::1]') {
            return 'https';
        }

        return 'http';
    }

    /**
     * Resolve the base URL path (e.g. '' on root domains or '/EduCore' in subdirectories).
     */
    public static function resolvePublicBaseUrl(string $host): string
    {
        $envAppUrl = getEnvConfig('APP_URL');
        if ($envAppUrl !== '') {
            $path = parse_url($envAppUrl, PHP_URL_PATH);
            if ($path !== null && $path !== '/' && $path !== '') {
                return rtrim($path, '/');
            }
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $hNoPort = preg_replace('/:\d+$/', '', $host);
        $isLocal = ($hNoPort === 'localhost' || str_starts_with($hNoPort, '127.') || $hNoPort === '::1' || $hNoPort === '[::1]');

        if ($isLocal) {
            if ($baseUrl === '') {
                $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                return str_contains($scriptName, '/EduCore') ? '/EduCore' : '';
            }
            return $baseUrl;
        }

        // On a live server root domain: if BASE_URL is literally '/EduCore' but script is not in '/EduCore', strip it
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if ($baseUrl === '/EduCore' && !str_contains($scriptName, '/EduCore/')) {
            return '';
        }

        return $baseUrl;
    }

    /**
     * Sanitized filesystem-safe identifier for current host.
     */
    public static function getSafeHost(): string
    {
        $host = self::resolvePublicHost();
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($host));
    }

    /**
     * Build the full attendance verification URL for a given token.
     */
    public static function buildScanUrl(string $token): string
    {
        $token = trim($token);
        $host = self::resolvePublicHost();
        $scheme = self::resolvePublicScheme($host);
        $baseUrl = self::resolvePublicBaseUrl($host);

        return $scheme . '://' . $host . $baseUrl . '/?route=attendance/scan&token=' . urlencode($token);
    }

    /**
     * Generate a crisp, high-resolution PNG QR Code on disk.
     */
    public static function generatePng(
        string $data,
        string $outputPath,
        string $level = self::DEFAULT_LEVEL,
        int $size = self::DEFAULT_SIZE,
        int $margin = self::DEFAULT_MARGIN
    ): bool {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        try {
            if (extension_loaded('gd') && function_exists('imagecreate')) {
                QRcode::png($data, $outputPath, $level, $size, $margin);
                return file_exists($outputPath) && filesize($outputPath) > 0;
            }
        } catch (Throwable $e) {
            error_log("QrCodeService::generatePng GD error: " . $e->getMessage());
        }

        // Fallback: If GD missing or failed, write via remote provider if online
        try {
            $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=' . $margin . '&ecc=' . strtoupper($level) . '&data=' . urlencode($data);
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $imgData = @file_get_contents($apiUrl, false, $ctx);
            if ($imgData !== false && strlen($imgData) > 50) {
                file_put_contents($outputPath, $imgData);
                return true;
            }
        } catch (Throwable $ex) {
            error_log("QrCodeService::generatePng remote fallback error: " . $ex->getMessage());
        }

        return false;
    }

    /**
     * Generate an inline vector SVG string for lossless, infinite-scaling display.
     */
    public static function generateSvg(
        string $data,
        ?string $outputPath = null,
        string $level = self::DEFAULT_LEVEL,
        int $size = 4,
        int $margin = self::DEFAULT_MARGIN
    ): string {
        try {
            $enc = QRencode::factory($level, $size, $margin);
            ob_start();
            $enc->encodeSVG($data, $outputPath ?: false, false);
            $svg = (string) ob_get_clean();
            return trim($svg);
        } catch (Throwable $e) {
            error_log("QrCodeService::generateSvg error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Ensure a student has a valid unique attendance QR token and high-res image file.
     * The primary QR code encodes ONLY the attendance token / number (e.g. ATTENDANCE-STD-QR-6ab57787b15d4)
     * without any URL prefix, ensuring instant 2D hardware scanning and clean phone camera decoding.
     *
     * @param array<string, mixed>|int $studentOrId
     * @param bool $forceRegen
     * @return array{
     *     id: int,
     *     token: string,
     *     relative_path: string,
     *     full_path: string,
     *     img_url: string,
     *     url_img_url: string,
     *     token_img_url: string,
     *     scan_url: string,
     *     has_qr: bool
     * }
     */
    public static function ensureStudentQr(array|int $studentOrId, bool $forceRegen = false): array
    {
        $pdo = Database::connect();
        $student = is_array($studentOrId) ? $studentOrId : [];
        $studentId = is_int($studentOrId) ? $studentOrId : (int) ($student['id'] ?? 0);

        if ($studentId <= 0) {
            throw new InvalidArgumentException("Invalid student ID for QR generation.");
        }

        // Fetch fresh record if not passed full array
        if (empty($student['qr_data']) || empty($student['admission_number'])) {
            $stmt = $pdo->prepare("SELECT id, admission_number, application_number, first_name, last_name, qr_code, qr_data FROM applicants WHERE id = ? LIMIT 1");
            $stmt->execute([$studentId]);
            $dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dbRow) {
                $student = array_merge($student, $dbRow);
            }
        }

        // 1. Ensure token exists (pure attendance identifier / number)
        $token = trim((string) ($student['qr_data'] ?? ''));
        if ($token === '') {
            $salt = substr(md5(($student['admission_number'] ?? '') . '_' . $studentId . '_' . uniqid()), 0, 8);
            $token = 'ATTENDANCE-STD-' . $studentId . '-' . $salt;
            $pdo->prepare("UPDATE applicants SET qr_data = ? WHERE id = ?")->execute([$token, $studentId]);
            $student['qr_data'] = $token;
        }

        // 2. Primary QR code encodes ONLY the token/number (no URL, high DPI, instant read)
        $qrRelative = 'qrcodes/std_' . $studentId . '.png';
        $qrFullPath = UPLOAD_PATH . $qrRelative;

        // Secondary URL QR variant
        $scanUrl = self::buildScanUrl($token);
        $urlRelative = 'qrcodes/std_url_' . $studentId . '.png';
        $urlFullPath = UPLOAD_PATH . $urlRelative;

        // Regenerate if forceRegen or if file is missing/empty
        $needsRegen = $forceRegen 
            || !file_exists($qrFullPath) 
            || filesize($qrFullPath) < 100;

        if ($needsRegen) {
            // Primary QR encodes ONLY the pure token number (e.g. ATTENDANCE-STD-QR-6ab57787b15d4)
            self::generatePng($token, $qrFullPath, self::DEFAULT_LEVEL, self::DEFAULT_SIZE, self::DEFAULT_MARGIN);
            // Secondary URL variant
            self::generatePng($scanUrl, $urlFullPath, self::DEFAULT_LEVEL, 6, self::DEFAULT_MARGIN);
            
            // Record in database
            $pdo->prepare("UPDATE applicants SET qr_code = ? WHERE id = ?")->execute([$qrRelative, $studentId]);
            $student['qr_code'] = $qrRelative;
        } elseif (empty($student['qr_code']) || $student['qr_code'] !== $qrRelative) {
            $pdo->prepare("UPDATE applicants SET qr_code = ? WHERE id = ?")->execute([$qrRelative, $studentId]);
            $student['qr_code'] = $qrRelative;
        }

        $imgUrl = url('uploads/' . $qrRelative);
        $urlImgUrl = url('uploads/' . $urlRelative);
        $hasQr = file_exists($qrFullPath) && filesize($qrFullPath) > 0;

        return [
            'id'            => $studentId,
            'token'         => $token,
            'relative_path' => $qrRelative,
            'full_path'     => $qrFullPath,
            'img_url'       => $imgUrl,       // Pure token/number QR code for ID cards and profiles
            'url_img_url'   => $urlImgUrl,   // URL variant
            'token_img_url' => $imgUrl,       // Pure token/number QR code
            'scan_url'      => $scanUrl,
            'has_qr'        => $hasQr,
        ];
    }

    /**
     * Ensure a staff member has a valid unique attendance QR token and high-res image file.
     * The primary QR code encodes ONLY the attendance token / number (e.g. ATTENDANCE-STF-9-...)
     * without any URL prefix.
     *
     * @param array<string, mixed>|int $staffOrId
     * @param bool $forceRegen
     * @return array{
     *     id: int,
     *     token: string,
     *     relative_path: string,
     *     full_path: string,
     *     img_url: string,
     *     url_img_url: string,
     *     token_img_url: string,
     *     scan_url: string,
     *     has_qr: bool
     * }
     */
    public static function ensureStaffQr(array|int $staffOrId, bool $forceRegen = false): array
    {
        $pdo = Database::connect();
        $staff = is_array($staffOrId) ? $staffOrId : [];
        $staffId = is_int($staffOrId) ? $staffOrId : (int) ($staff['id'] ?? 0);

        if ($staffId <= 0) {
            throw new InvalidArgumentException("Invalid staff ID for QR generation.");
        }

        if (empty($staff['qr_data']) || empty($staff['staff_id'])) {
            $stmt = $pdo->prepare("SELECT id, staff_id, first_name, last_name, qr_data FROM staff WHERE id = ? LIMIT 1");
            $stmt->execute([$staffId]);
            $dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dbRow) {
                $staff = array_merge($staff, $dbRow);
            }
        }

        // 1. Ensure token exists (pure attendance identifier / number)
        $token = trim((string) ($staff['qr_data'] ?? ''));
        if ($token === '') {
            $salt = substr(md5(($staff['staff_id'] ?? '') . '_' . $staffId . '_' . uniqid()), 0, 8);
            $token = 'ATTENDANCE-STF-' . $staffId . '-' . $salt;
            $pdo->prepare("UPDATE staff SET qr_data = ? WHERE id = ?")->execute([$token, $staffId]);
            $staff['qr_data'] = $token;
        }

        // 2. Primary QR code encodes ONLY the token/number (no URL)
        $qrRelative = 'qrcodes/stf_' . $staffId . '.png';
        $qrFullPath = UPLOAD_PATH . $qrRelative;

        // Secondary URL QR variant
        $scanUrl = self::buildScanUrl($token);
        $urlRelative = 'qrcodes/stf_url_' . $staffId . '.png';
        $urlFullPath = UPLOAD_PATH . $urlRelative;

        $needsRegen = $forceRegen 
            || !file_exists($qrFullPath) 
            || filesize($qrFullPath) < 100;

        if ($needsRegen) {
            // Encode ONLY the pure token number
            self::generatePng($token, $qrFullPath, self::DEFAULT_LEVEL, self::DEFAULT_SIZE, self::DEFAULT_MARGIN);
            self::generatePng($scanUrl, $urlFullPath, self::DEFAULT_LEVEL, 6, self::DEFAULT_MARGIN);
        }

        $imgUrl = url('uploads/' . $qrRelative);
        $urlImgUrl = url('uploads/' . $urlRelative);
        $hasQr = file_exists($qrFullPath) && filesize($qrFullPath) > 0;

        return [
            'id'            => $staffId,
            'token'         => $token,
            'relative_path' => $qrRelative,
            'full_path'     => $qrFullPath,
            'img_url'       => $imgUrl,       // Pure token/number QR code
            'url_img_url'   => $urlImgUrl,   // URL variant
            'token_img_url' => $imgUrl,       // Pure token/number QR code
            'scan_url'      => $scanUrl,
            'has_qr'        => $hasQr,
        ];
    }

    /**
     * Batch repair/regenerate all enrolled students and active staff with high-contrast,
     * machine-optimized QR codes encoding ONLY the attendance token/number.
     *
     * @param bool $forceRegen Set to true to regenerate all existing files.
     * @return array{students_fixed: int, staff_fixed: int}
     */
    public static function repairAllMissing(bool $forceRegen = false): array
    {
        $pdo = Database::connect();
        $studentsFixed = 0;
        $staffFixed = 0;

        // 1. Students (Enrolled or Active)
        $stmt = $pdo->query("SELECT id, admission_number, application_number, first_name, last_name, qr_code, qr_data FROM applicants WHERE status = 'Enrolled' OR student_status = 'Active'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $qrPath = UPLOAD_PATH . 'qrcodes/std_' . $row['id'] . '.png';
            if ($forceRegen || empty($row['qr_data']) || !file_exists($qrPath) || filesize($qrPath) < 100) {
                self::ensureStudentQr($row, $forceRegen);
                $studentsFixed++;
            }
        }

        // 2. Staff (Active)
        $stmtStaff = $pdo->query("SELECT id, staff_id, first_name, last_name, qr_data FROM staff WHERE status = 'Active'");
        while ($stf = $stmtStaff->fetch(PDO::FETCH_ASSOC)) {
            $qrPath = UPLOAD_PATH . 'qrcodes/stf_' . $stf['id'] . '.png';
            if ($forceRegen || empty($stf['qr_data']) || !file_exists($qrPath) || filesize($qrPath) < 100) {
                self::ensureStaffQr($stf, $forceRegen);
                $staffFixed++;
            }
        }

        return [
            'students_fixed' => $studentsFixed,
            'staff_fixed'    => $staffFixed,
        ];
    }
}
