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
     * Build the full attendance verification URL for a given token.
     */
    public static function buildScanUrl(string $token): string
    {
        $token = trim($token);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Check if school has a public domain/URL configured
        $configuredDomain = trim((string) setting('school_domain', setting('domain', '')));
        if ($configuredDomain !== '' && $configuredDomain !== 'localhost' && !str_starts_with($configuredDomain, '127.')) {
            $configuredDomain = preg_replace('#^https?://#', '', $configuredDomain);
            $configuredDomain = preg_replace('#/.*$#', '', $configuredDomain);
            if ($configuredDomain !== '') {
                $host = $configuredDomain;
            }
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        if ($baseUrl === '' && ($host === 'localhost' || str_starts_with($host, '127.'))) {
            $baseUrl = '/EduCore';
        }
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
     * Automatically self-heals missing tokens and missing image files.
     *
     * @param array<string, mixed>|int $studentOrId
     * @return array{
     *     id: int,
     *     token: string,
     *     relative_path: string,
     *     full_path: string,
     *     img_url: string,
     *     scan_url: string,
     *     has_qr: bool
     * }
     */
    /**
     * Ensure a student has a valid unique attendance QR token and high-res image file.
     * Generates machine-optimized QR code encoding the direct attendance token for instant
     * 2D scanner / hardware terminal decoding, along with a web URL QR variant.
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

        // 1. Ensure token exists
        $token = trim((string) ($student['qr_data'] ?? ''));
        if ($token === '') {
            $salt = substr(md5(($student['admission_number'] ?? '') . '_' . $studentId . '_' . uniqid()), 0, 8);
            $token = 'ATTENDANCE-STD-' . $studentId . '-' . $salt;
            $pdo->prepare("UPDATE applicants SET qr_data = ? WHERE id = ?")->execute([$token, $studentId]);
            $student['qr_data'] = $token;
        }

        // 2. Determine file paths
        $qrRelative = 'qrcodes/std_' . $studentId . '.png';
        $qrFullPath = UPLOAD_PATH . $qrRelative;
        $qrUrlRelative = 'qrcodes/std_url_' . $studentId . '.png';
        $qrUrlFullPath = UPLOAD_PATH . $qrUrlRelative;
        $scanUrl = self::buildScanUrl($token);

        // 3. Generate machine-optimized token QR (direct token payload: large modules, instant hardware scanner read)
        $needsRegen = $forceRegen || !file_exists($qrFullPath) || filesize($qrFullPath) < 100;
        if ($needsRegen) {
            self::generatePng($token, $qrFullPath, self::DEFAULT_LEVEL, self::DEFAULT_SIZE, self::DEFAULT_MARGIN);
            self::generatePng($scanUrl, $qrUrlFullPath, self::DEFAULT_LEVEL, 6, self::DEFAULT_MARGIN);
            $pdo->prepare("UPDATE applicants SET qr_code = ? WHERE id = ?")->execute([$qrRelative, $studentId]);
            $student['qr_code'] = $qrRelative;
        } elseif (empty($student['qr_code'])) {
            $pdo->prepare("UPDATE applicants SET qr_code = ? WHERE id = ?")->execute([$qrRelative, $studentId]);
            $student['qr_code'] = $qrRelative;
        }

        $hasQr = file_exists($qrFullPath) && filesize($qrFullPath) > 0;
        $imgUrl = url('uploads/' . $qrRelative);
        $urlImgUrl = url('uploads/' . $qrUrlRelative);

        return [
            'id'            => $studentId,
            'token'         => $token,
            'relative_path' => $qrRelative,
            'full_path'     => $qrFullPath,
            'img_url'       => $imgUrl,
            'url_img_url'   => $urlImgUrl,
            'scan_url'      => $scanUrl,
            'has_qr'        => $hasQr,
        ];
    }

    /**
     * Ensure a staff member has a valid unique attendance QR token and high-res image file.
     * Automatically self-heals missing tokens and missing image files.
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

        // 1. Ensure token exists
        $token = trim((string) ($staff['qr_data'] ?? ''));
        if ($token === '') {
            $salt = substr(md5(($staff['staff_id'] ?? '') . '_' . $staffId . '_' . uniqid()), 0, 8);
            $token = 'ATTENDANCE-STF-' . $staffId . '-' . $salt;
            $pdo->prepare("UPDATE staff SET qr_data = ? WHERE id = ?")->execute([$token, $staffId]);
            $staff['qr_data'] = $token;
        }

        // 2. Determine file paths
        $qrRelative = 'qrcodes/stf_' . $staffId . '.png';
        $qrFullPath = UPLOAD_PATH . $qrRelative;
        $qrUrlRelative = 'qrcodes/stf_url_' . $staffId . '.png';
        $qrUrlFullPath = UPLOAD_PATH . $qrUrlRelative;
        $scanUrl = self::buildScanUrl($token);

        // 3. Generate machine-optimized token QR
        $needsRegen = $forceRegen || !file_exists($qrFullPath) || filesize($qrFullPath) < 100;
        if ($needsRegen) {
            self::generatePng($token, $qrFullPath, self::DEFAULT_LEVEL, self::DEFAULT_SIZE, self::DEFAULT_MARGIN);
            self::generatePng($scanUrl, $qrUrlFullPath, self::DEFAULT_LEVEL, 6, self::DEFAULT_MARGIN);
        }

        $hasQr = file_exists($qrFullPath) && filesize($qrFullPath) > 0;
        $imgUrl = url('uploads/' . $qrRelative);
        $urlImgUrl = url('uploads/' . $qrUrlRelative);

        return [
            'id'            => $staffId,
            'token'         => $token,
            'relative_path' => $qrRelative,
            'full_path'     => $qrFullPath,
            'img_url'       => $imgUrl,
            'url_img_url'   => $urlImgUrl,
            'scan_url'      => $scanUrl,
            'has_qr'        => $hasQr,
        ];
    }

    /**
     * Batch repair/regenerate all enrolled students and active staff with high-contrast,
     * machine-optimized QR codes and tokens.
     *
     * @param bool $forceRegen Set to true to regenerate all existing files with machine-optimized format.
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
            $qrPath = !empty($row['qr_code']) ? UPLOAD_PATH . $row['qr_code'] : '';
            if ($forceRegen || empty($row['qr_data']) || empty($row['qr_code']) || !file_exists($qrPath) || filesize($qrPath) < 100) {
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
