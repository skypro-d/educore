<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/SmsService.php';
require_once __DIR__ . '/AttendanceRules.php';
require_once __DIR__ . '/LicenseGuard.php';
require_once __DIR__ . '/PlatformSettings.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/Email.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    if ($path !== '' && preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $path = ltrim($path, '/');
    return (BASE_URL === '' ? '' : BASE_URL) . '/' . $path;
}

function redirect(string $path): never
{
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Method Not Allowed. This action requires a POST request.']));
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    if (!admin()) {
        redirect('admin/login');
    }
    LicenseGuard::checkAndSync();
    if (!LicenseGuard::allowAdminLogin()) {
        flash('danger', 'Your 30-day offline license grace period has expired. System is locked.');
    }
}

function render(string $view, array $data = [], ?string $layout = 'public'): void
{
    extract($data);
    ob_start();
    require __DIR__ . '/../views/' . $view . '.php';
    $content = ob_get_clean();
    if ($layout === null || $layout === '' || $layout === 'none') {
        echo $content;
        return;
    }
    require __DIR__ . '/../views/layouts/' . $layout . '.php';
}

function generate_application_number(PDO $db): string
{
    $year = date('Y');
    $schoolInfo = SchoolContext::info();
    $schoolCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($schoolInfo['school_code'] ?? 'SCH')));
    $schoolCode = substr($schoolCode !== '' ? $schoolCode : 'SCH', 0, 6);
    $schoolId = SchoolContext::id() ?: 1;
    
    $stmt = $db->prepare("SELECT COUNT(*) + 1 AS next_no FROM applicants WHERE school_id = ? AND YEAR(created_at) = ?");
    $stmt->execute([$schoolId, $year]);
    $row = $stmt->fetch();
    $nextNo = (int) ($row['next_no'] ?? 1);
    
    $candidate = $schoolCode . $year . str_pad((string) $nextNo, 5, '0', STR_PAD_LEFT);

    // Collision check
    $stmtCheck = $db->prepare("SELECT id FROM applicants WHERE application_number = ? LIMIT 1");
    $stmtCheck->execute([$candidate]);
    if ($stmtCheck->fetch()) {
        $stmtMax = $db->prepare("SELECT application_number FROM applicants WHERE application_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtMax->execute([$schoolCode . $year . '%']);
        $maxRow = $stmtMax->fetch();
        if ($maxRow && preg_match('/(\d{5})$/', (string)$maxRow['application_number'], $matches)) {
            $candidate = $schoolCode . $year . str_pad((string) ((int)$matches[1] + 1), 5, '0', STR_PAD_LEFT);
        } else {
            $candidate = $schoolCode . $year . str_pad((string) ($nextNo + rand(1, 99)), 5, '0', STR_PAD_LEFT);
        }
    }
    
    return $candidate;
}

function upload_file(string $field, string $folder, array $allowed = []): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_UPLOAD_SIZE) {
        $err = $file['error'] ?? UPLOAD_ERR_OK;
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE || $file['size'] > MAX_UPLOAD_SIZE) {
            throw new RuntimeException('Upload failed: File is larger than allowed limit (2MB).');
        }
        throw new RuntimeException('Upload failed with error code: ' . $err);
    }

    // Default image types if none provided
    if (empty($allowed)) {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/x-png'=> 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];
    }

    $mimeToExtDefault = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/pjpeg'=> 'jpg',
        'image/png'  => 'png',
        'image/x-png'=> 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt',
        'text/csv'   => 'csv',
        'application/zip' => 'zip',
    ];

    $extToMimeDefault = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jfif' => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'zip'  => 'application/zip',
    ];

    $normalizedAllowed = [];
    $allowedExts = [];

    foreach ($allowed as $k => $v) {
        if (is_numeric($k)) {
            // Flat entry: e.g. 'jpg', 'png', or 'image/jpeg'
            $val = strtolower(trim((string)$v));
            if (str_contains($val, '/')) {
                $ext = $mimeToExtDefault[$val] ?? 'bin';
                $normalizedAllowed[$val] = $ext;
                $allowedExts[] = $ext;
            } else {
                $ext = ltrim($val, '.');
                $allowedExts[] = $ext;
                if (isset($extToMimeDefault[$ext])) {
                    $normalizedAllowed[$extToMimeDefault[$ext]] = $ext;
                }
            }
        } elseif (str_contains((string)$k, '/')) {
            // Mime => Ext format: ['image/jpeg' => 'jpg']
            $mimeK = strtolower(trim((string)$k));
            $extV  = strtolower(ltrim(trim((string)$v), '.'));
            $normalizedAllowed[$mimeK] = $extV;
            $allowedExts[] = $extV;
            if ($extV === 'jpg' || $extV === 'jpeg') {
                $normalizedAllowed['image/pjpeg'] = 'jpg';
                $normalizedAllowed['image/jpg'] = 'jpg';
                $allowedExts[] = 'jpg';
                $allowedExts[] = 'jpeg';
            }
            if ($extV === 'png') {
                $normalizedAllowed['image/x-png'] = 'png';
            }
        } else {
            // Ext => Mime format: ['jpg' => 'image/jpeg', 'png' => 'image/png']
            $extK  = strtolower(ltrim(trim((string)$k), '.'));
            $mimeV = strtolower(trim((string)$v));
            $normalizedAllowed[$mimeV] = $extK;
            $allowedExts[] = $extK;
            if ($extK === 'jpg' || $extK === 'jpeg') {
                $normalizedAllowed['image/pjpeg'] = 'jpg';
                $normalizedAllowed['image/jpg'] = 'jpg';
                $allowedExts[] = 'jpg';
                $allowedExts[] = 'jpeg';
            }
            if ($extK === 'png') {
                $normalizedAllowed['image/x-png'] = 'png';
            }
        }
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = strtolower((string)$finfo->file($file['tmp_name']));
    $rawExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $extensionAliases = [
        'jpeg' => 'jpg',
        'jfif' => 'jpg',
        'jpe'  => 'jpg',
        'docm' => 'docx',
    ];
    $normalizedExtension = $extensionAliases[$rawExtension] ?? $rawExtension;
    $allowedExtsList = array_unique(array_filter($allowedExts));

    $resolvedExt = null;
    if (isset($normalizedAllowed[$detectedMime])) {
        $resolvedExt = $normalizedAllowed[$detectedMime];
    } else {
        $fallbackMimes = [
            'application/octet-stream',
            'application/zip',
            'application/x-zip-compressed',
            'binary/octet-stream',
        ];
        $isAllowedExt = in_array($normalizedExtension, $allowedExtsList, true) || in_array($rawExtension, $allowedExtsList, true);
        $isImageExt = in_array($normalizedExtension, ['jpg', 'png', 'webp', 'gif'], true);
        $isValidImg = !$isImageExt || (@getimagesize($file['tmp_name']) !== false);

        if ($isAllowedExt && $isValidImg && (in_array($detectedMime, $fallbackMimes, true) || str_starts_with($detectedMime, 'image/'))) {
            $resolvedExt = $normalizedExtension;
        }
    }

    if (!$resolvedExt) {
        $displayAllowed = !empty($allowedExtsList) ? strtoupper(implode(', ', $allowedExtsList)) : strtoupper(implode(', ', array_unique(array_values($normalizedAllowed))));
        throw new RuntimeException('Invalid file type uploaded for ' . str_replace('_', ' ', $field) . '. Detected: ' . $detectedMime . ' (.' . $rawExtension . '). Allowed files: ' . $displayAllowed . '.');
    }

    $name = bin2hex(random_bytes(12)) . '.' . $resolvedExt;
    $targetDir = UPLOAD_PATH . trim($folder, '/') . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetDir . $name)) {
        throw new RuntimeException('Unable to save uploaded file.');
    }

    return trim($folder, '/') . '/' . $name;
}

function school_website_url(): string
{
    // 1. Check custom school website URL configured by the school
    $customWeb = trim(setting('school_website', setting('website', '')));
    if ($customWeb !== '') {
        if (preg_match('#^https?://#i', $customWeb)) {
            return $customWeb;
        }
        if (str_starts_with($customWeb, '//')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https:' : 'http:';
            return $scheme . $customWeb;
        }
        if (str_starts_with($customWeb, '/')) {
            return url($customWeb);
        }
        if (str_contains($customWeb, '.')) {
            return 'https://' . ltrim($customWeb, '/');
        }
        return url($customWeb);
    }

    // 2. Check school domain
    $schoolInfo = SchoolContext::info();
    $schoolDomain = trim($schoolInfo['domain'] ?? '');
    if ($schoolDomain !== '' && $schoolDomain !== 'localhost' && $schoolDomain !== '127.0.0.1') {
        return 'https://' . $schoolDomain;
    }

    // 3. Fallback to school portal landing page
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $schoolCode = $schoolInfo['school_code'] ?? '';
    if (!empty($schoolCode)) {
        return $scheme . '://' . $host . BASE_URL . '/?school_code=' . urlencode($schoolCode);
    }

    return url('');
}

function setting(string $key, $default = '', bool $reload = false)
{
    static $schoolRow = null;
    static $appConfigs = null;

    if ($reload) {
        $schoolRow = null;
        $appConfigs = null;
    }

    if ($schoolRow === null) {
        try {
            $stmt = Database::connect()->query("SELECT * FROM school_settings WHERE id = 1 LIMIT 1");
            $schoolRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            $schoolRow = [];
        }
    }

    if (array_key_exists($key, $schoolRow) && $schoolRow[$key] !== null && $schoolRow[$key] !== '') {
        return (string) $schoolRow[$key];
    }

    $aliases = [
        'academic_year' => 'academic_session',
        'school_logo' => 'logo',
        'school_website' => 'website',
    ];
    if (isset($aliases[$key]) && array_key_exists($aliases[$key], $schoolRow) && $schoolRow[$aliases[$key]] !== null && $schoolRow[$aliases[$key]] !== '') {
        return (string) $schoolRow[$aliases[$key]];
    }

    if ($appConfigs === null) {
        $appConfigs = [];
        try {
            $stmt = Database::connect()->query("SELECT setting_key, setting_value FROM app_configs");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $appConfigs[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $appConfigs = [];
        }
    }

    return (string) ($appConfigs[$key] ?? $default);
}

function setting_clear_cache(): void
{
    setting('', '', true);
}

function settings_map(): array
{
    try {
        $map = [];
        $stmt = Database::connect()->query("SELECT * FROM school_settings WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $map = $row;
            if (!empty($row['website']) && empty($map['school_website'])) {
                $map['school_website'] = $row['website'];
            }
            if (!empty($row['logo']) && empty($map['school_logo'])) {
                $map['school_logo'] = $row['logo'];
            }
            if (!empty($row['academic_session']) && empty($map['academic_year'])) {
                $map['academic_year'] = $row['academic_session'];
            }
        }

        $stmtApp = Database::connect()->query("SELECT setting_key, setting_value FROM app_configs");
        foreach ($stmtApp->fetchAll(PDO::FETCH_ASSOC) as $cfg) {
            $map[$cfg['setting_key']] = $cfg['setting_value'];
        }

        if (!empty($map['school_website']) && empty($map['website'])) {
            $map['website'] = $map['school_website'];
        } elseif (!empty($map['website']) && empty($map['school_website'])) {
            $map['school_website'] = $map['website'];
        }

        return $map;
    } catch (Throwable $e) {
        return [];
    }
}

function school_logo_url(): ?string
{
    $logo = trim(setting('school_logo', '') ?: setting('logo', ''));
    if ($logo === '') {
        return null;
    }
    if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
        return $logo;
    }
    $clean = ltrim(str_replace(['uploads/', 'uploads\\'], '', $logo), '/\\');
    if ($clean === '') {
        return null;
    }
    if (defined('UPLOAD_PATH') && is_dir(UPLOAD_PATH)) {
        if (!file_exists(UPLOAD_PATH . $clean)) {
            if (file_exists(UPLOAD_PATH . basename($clean))) {
                $clean = basename($clean);
            } else {
                return null;
            }
        }
    }
    return url('uploads/' . $clean);
}

function brand_css(): string
{
    $primary = setting('primary_color', '#0b3d91');
    $secondary = setting('secondary_color', '#f4b942');
    $sidebar = setting('sidebar_color', '#061a40');
    $button = setting('button_color', $primary);
    $dashboard = setting('dashboard_color', '#1056c2');

    return "--brand-primary: {$primary}; --brand-secondary: {$secondary}; --brand-sidebar: {$sidebar}; --brand-button: {$button}; --brand-dashboard: {$dashboard};";
}

function gateway_setting(string $key, string $fallback = ''): string
{
    $value = trim(setting($key, ''));
    return $value !== '' ? $value : $fallback;
}

function smtp_setting(string $key, string $fallback = ''): string
{
    $value = trim(setting($key, ''));
    return $value !== '' ? $value : $fallback;
}

function role_allows(string $permission): bool
{
    $role = $_SESSION['admin']['role'] ?? '';
    if ($role === 'super_admin' || $role === 'system_admin' || $role === 'admin') {
        return true;
    }

    $map = [
        'admission_officer' => ['applications', 'interviews', 'exams', 'letters'],
        'accountant' => ['payments', 'reports', 'fees'],
        'principal' => ['reports', 'applications', 'results', 'attendance', 'scanner_assignments', 'scanner_logs', 'exit_scanner', 'exit_logs', 'exit_settings'],
        'staff' => ['applications', 'attendance', 'results', 'exit_scanner', 'exit_logs'],
        'gate_officer' => ['exit_scanner', 'exit_logs', 'scanner'],
        'scanner_officer' => ['scanner'],
        'security' => ['exit_scanner', 'exit_logs'],
        'admin' => ['applications', 'classes', 'settings', 'payments', 'reports', 'interviews', 'exams', 'results', 'attendance', 'scanner_assignments', 'scanner_logs', 'fees', 'subjects', 'staff', 'communications', 'promotion', 'library', 'transport', 'inventory', 'exit_scanner', 'exit_logs', 'exit_settings', 'gates', 'authorized_pickups'],
    ];

    $allowedList = $map[$role] ?? [];
    if (in_array($permission, $allowedList, true)) {
        return true;
    }

    // Support granular permission aliases
    $aliases = [
        'view_exit_scanner'   => 'exit_scanner',
        'scan_student_exit'   => 'exit_scanner',
        'view_exit_logs'      => 'exit_logs',
        'manage_exit_settings'=> 'exit_settings',
        'retry_exit_sms'      => 'exit_logs',
        'export_exit_reports' => 'exit_logs',
        'scanner_assignments' => 'scanner_assignments',
        'scanner_logs'        => 'scanner_logs',
    ];

    if (isset($aliases[$permission]) && in_array($aliases[$permission], $allowedList, true)) {
        return true;
    }

    return false;
}

function require_permission(string $permission): void
{
    require_admin();
    if (!role_allows($permission)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function mask_phone(?string $phone): string
{
    $phone = trim((string) $phone);
    if (strlen($phone) <= 6) {
        return $phone !== '' ? '***' . substr($phone, -2) : '—';
    }
    return substr($phone, 0, 4) . '***' . substr($phone, -3);
}

/**
 * Normalize phone / WhatsApp numbers to international standard (Nigerian 08xxx -> 2348xxx).
 * Preserves already international numbers.
 */
function normalize_phone_number(?string $phone): string
{
    $phone = trim((string) $phone);
    if ($phone === '') {
        return '';
    }

    $digits = preg_replace('/\D/', '', $phone);
    if ($digits === '') {
        return '';
    }

    // Nigerian local 11 digits starting with 0 (e.g. 08012345678)
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        return '234' . substr($digits, 1);
    }

    // Already begins with country code or other international format
    return $digits;
}

function send_email_notice(string $to, string $subject, string $body): void
{
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        try {
            if (!class_exists('NotificationController')) {
                require_once __DIR__ . '/../controllers/NotificationController.php';
            }
            $nc = new NotificationController();
            $nc->sendWelcomeNotice(0, $to, $subject, $body);
        } catch (Throwable $e) {
            if (function_exists('mail')) {
                @mail($to, $subject, $body);
            } else {
                error_log('send_email_notice failed: ' . $e->getMessage());
            }
        }
    }
}

function send_sms_notice(string $phone, string $message, string $type = 'general', ?int $attendanceId = null): void
{
    // Master SMS switch — if disabled, only log
    if (setting('attendance_sms_enabled', '1') === '0') {
        error_log('[SMS disabled] To: ' . $phone . ' | ' . $message);
        return;
    }
    SmsService::send($phone, $message, $type, $attendanceId);
}

function log_sms(PDO $db, string $phone, string $name, string $message, string $status = 'sent', string $type = 'general', ?int $attendanceId = null): void
{
    // SmsService now handles its own logging via DB — this function kept for
    // backward-compatibility with manual/bulk SMS flows that pass a custom name.
    try {
        $db->prepare(
            "INSERT INTO sms_logs (recipient_phone, recipient_name, message, status, sms_type, attendance_id, sent_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
        )->execute([$phone, $name, $message, $status, $type, $attendanceId]);
    } catch (Throwable $e) {}
}

/**
 * Send a check-in (arrival) SMS to the parent of a student.
 * Checks: SMS enabled, checkin_sms_enabled, no duplicate (alert_sent).
 *
 * @param PDO    $db         Database connection
 * @param array  $student    Applicant row including first_name, last_name, parent_phone, class_id
 * @param string $timeIn     HH:MM string (e.g. '07:45')
 * @param string $status     'Present' or 'Late'
 * @param int    $attendanceId  attendance.id for deduplication tracking
 */
function send_checkin_sms(PDO $db, array $student, string $timeIn, string $status, int $attendanceId): void
{
    // Feature gate checks
    if (setting('attendance_sms_enabled', '1') === '0') return;
    if (setting('checkin_sms_enabled', '1') === '0') return;

    $phone = trim($student['parent_phone'] ?? '');
    if ($phone === '') return;

    $name     = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
    $school   = setting('school_name', 'Your School');
    $date     = date('D, M j Y');
    $timeFmt  = date('g:i A', strtotime($timeIn));

    $message  = SmsService::buildCheckinMessage($name, $school, $date, $timeFmt, $status);

    SmsService::send($phone, $message, 'checkin', $attendanceId);

    // Mark alert_sent = 1 to prevent duplicates
    try {
        $db->prepare("UPDATE attendance SET alert_sent = 1 WHERE id = ?")->execute([$attendanceId]);
    } catch (Throwable $e) {}
}

/**
 * Send an absent SMS to the parent of a student.
 * Checks: SMS enabled, absent_sms_enabled, no duplicate (alert_sent).
 *
 * @param PDO    $db          Database connection
 * @param array  $student     Applicant row including first_name, last_name, parent_phone
 * @param string $date        Y-m-d attendance date
 * @param int    $attendanceId attendance.id
 */
function send_absent_sms(PDO $db, array $student, string $date, int $attendanceId): void
{
    if (setting('attendance_sms_enabled', '1') === '0') return;
    if (setting('absent_sms_enabled', '1') === '0') return;

    $phone = trim($student['parent_phone'] ?? '');
    if ($phone === '') return;

    $name    = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
    $school  = setting('school_name', 'Your School');
    $dateStr = date('D, M j Y', strtotime($date));

    $message = SmsService::buildAbsentMessage($name, $school, $dateStr);

    SmsService::send($phone, $message, 'absent', $attendanceId);

    try {
        $db->prepare("UPDATE attendance SET alert_sent = 1 WHERE id = ?")->execute([$attendanceId]);
    } catch (Throwable $e) {}
}

/**
 * Send an exit SMS to the parent of a student.
 * Checks: SMS enabled, exit_sms_enabled, early_exit_sms_enabled.
 *
 * @param PDO    $db        Database connection
 * @param array  $student   Applicant row including first_name, last_name, parent_phone, class_name
 * @param array  $exitData  Exit details: exit_type, exit_date, exit_time, exit_reason, pickup_person_name
 * @param int    $exitLogId student_exit_logs.id
 * @return bool Whether SMS send request was successfully initiated
 */
function send_exit_sms(PDO $db, array $student, array $exitData, int $exitLogId): bool
{
    if (setting('attendance_sms_enabled', '1') === '0') return false;
    if (setting('exit_sms_enabled', '1') === '0') return false;

    $exitType = $exitData['exit_type'] ?? 'normal';
    if ($exitType === 'early' && setting('early_exit_sms_enabled', '1') === '0') {
        return false;
    }

    $phone = trim($student['parent_phone'] ?? '');
    if ($phone === '') return false;

    $name         = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
    $school       = setting('school_name', 'EduCore School');
    $dateStr      = date('D, M j Y', strtotime($exitData['exit_date'] ?? date('Y-m-d')));
    $timeFmt      = date('g:i A', strtotime($exitData['exit_time'] ?? date('H:i:s')));
    $reason       = $exitData['exit_reason'] ?? null;
    $pickupPerson = $exitData['pickup_person_name'] ?? null;
    $className    = $student['class_name'] ?? '';

    $message = SmsService::buildExitMessage($name, $school, $dateStr, $timeFmt, $exitType, $reason, $pickupPerson, $className);

    $result = SmsService::send($phone, $message, 'exit', null, $exitLogId);

    $newSmsStatus = $result['success'] ? 'sent' : 'failed';
    $smsLogId = $result['sms_log_id'] ?? null;

    try {
        if ($smsLogId) {
            $db->prepare("UPDATE student_exit_logs SET sms_status = ?, sms_log_id = ? WHERE id = ?")
               ->execute([$newSmsStatus, $smsLogId, $exitLogId]);
        } else {
            $db->prepare("UPDATE student_exit_logs SET sms_status = ? WHERE id = ?")
               ->execute([$newSmsStatus, $exitLogId]);
        }
    } catch (Throwable $e) {}

    return (bool) $result['success'];
}

function current_academic_year(): string
{
    return setting('academic_year', date('Y') . '/' . (date('Y') + 1));
}

function current_term(): string
{
    return setting('current_term', 'First');
}

function generate_receipt_number(PDO $db): string
{
    $year = date('Y');
    $prefix = 'RCT-' . $year . '-';

    $stmt = $db->prepare("SELECT receipt_number FROM student_fee_payments WHERE receipt_number LIKE ?");
    $stmt->execute([$prefix . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $maxNum = 0;
    foreach ($rows as $rct) {
        if (preg_match('/^RCT-\d{4}-(\d+)$/', (string)$rct, $m)) {
            $val = (int)$m[1];
            if ($val > $maxNum) {
                $maxNum = $val;
            }
        }
    }

    $count = (int) $db->query("SELECT COUNT(*) FROM student_fee_payments")->fetchColumn();
    $nextNum = max($maxNum + 1, $count + 1);
    $candidate = $prefix . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);

    $checkStmt = $db->prepare("SELECT id FROM student_fee_payments WHERE receipt_number = ? LIMIT 1");
    while (true) {
        $checkStmt->execute([$candidate]);
        if (!$checkStmt->fetch()) {
            break;
        }
        $nextNum++;
        $candidate = $prefix . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);
    }

    return $candidate;
}

function generate_staff_id(PDO $db): string
{
    $year = date('Y');
    $prefix = 'STF-' . $year . '-';

    $stmt = $db->prepare("SELECT staff_id FROM staff WHERE staff_id LIKE ?");
    $stmt->execute([$prefix . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $maxNum = 0;
    foreach ($rows as $sid) {
        if (preg_match('/^STF-\d{4}-(\d+)$/', (string)$sid, $m)) {
            $val = (int)$m[1];
            if ($val > $maxNum) {
                $maxNum = $val;
            }
        }
    }

    $count = (int) $db->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $nextNum = max($maxNum + 1, $count + 1);
    $candidate = $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);

    $checkStmt = $db->prepare("SELECT id FROM staff WHERE staff_id = ? LIMIT 1");
    while (true) {
        $checkStmt->execute([$candidate]);
        if (!$checkStmt->fetch()) {
            break;
        }
        $nextNum++;
        $candidate = $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }

    return $candidate;
}

function generate_temp_password(): string
{
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    $symbols = '!@#$%^&*';
    
    $password = '';
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $digits[random_int(0, strlen($digits) - 1)];
    $password .= $symbols[random_int(0, strlen($symbols) - 1)];
    
    $all = $lowercase . $uppercase . $digits . $symbols;
    for ($i = 0; $i < 4; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    
    return str_shuffle($password);
}

function create_notification(PDO $db, string $userType, int $userId, string $title, string $message): void
{
    try {
        $stmt = $db->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userType, $userId, $title, $message]);
    } catch (Throwable $e) {
        error_log("Failed to create notification: " . $e->getMessage());
    }
}

function system_setting(string $key, string $default = ''): string
{
    static $sysSettings = null;
    if ($sysSettings === null) {
        $sysSettings = [];
        try {
            foreach (Database::connect()->query('SELECT setting_key, setting_value FROM system_settings') as $row) {
                $sysSettings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $sysSettings = [];
        }
    }
    return $sysSettings[$key] ?? $default;
}

function platform_setting(string $key, string $default = ''): string
{
    return PlatformSettings::get($key, $default);
}

function platform_settings_map(): array
{
    return PlatformSettings::all();
}

function platform_gateway_setting(string $key, string $default = ''): string
{
    // 1. Check school / tenant setting first (school_settings or app_configs)
    $val = setting($key, '');
    if ($val !== '') {
        return $val;
    }

    // 2. Check system / platform level setting (system_settings)
    $sysVal = system_setting($key, '');
    if ($sysVal !== '') {
        return $sysVal;
    }

    // 3. Check environment variable
    $envVal = getenv($key) ?: getenv(strtoupper($key));
    if ($envVal !== false && $envVal !== '') {
        return (string) $envVal;
    }

    return $default;
}

function customer(): ?array
{
    return $_SESSION['customer'] ?? null;
}

function require_customer(): void
{
    if (!customer()) {
        redirect('portal/login');
    }
}

function generate_invoice_number(PDO $db): string
{
    $prefix = platform_setting('invoice_prefix', 'INV');
    $year   = date('Y');
    $fullPrefix = $prefix . '-' . $year . '-';

    $stmt = $db->prepare("SELECT invoice_number FROM customer_invoices WHERE invoice_number LIKE ?");
    $stmt->execute([$fullPrefix . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $maxNum = 0;
    foreach ($rows as $inv) {
        if (preg_match('/-(\d+)$/', (string)$inv, $m)) {
            $val = (int)$m[1];
            if ($val > $maxNum) {
                $maxNum = $val;
            }
        }
    }

    $count = (int) $db->query("SELECT COUNT(*) FROM customer_invoices WHERE YEAR(created_at) = {$year}")->fetchColumn();
    $nextNum = max($maxNum + 1, $count + 1);
    $candidate = $fullPrefix . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);

    $checkStmt = $db->prepare("SELECT id FROM customer_invoices WHERE invoice_number = ? LIMIT 1");
    while (true) {
        $checkStmt->execute([$candidate]);
        if (!$checkStmt->fetch()) {
            break;
        }
        $nextNum++;
        $candidate = $fullPrefix . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);
    }

    return $candidate;
}

function generate_customer_api_key(): string
{
    return 'sk_live_' . bin2hex(random_bytes(20));
}

function generate_installation_id(): string
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $seg   = fn(int $n) => implode('', array_map(fn() => $chars[random_int(0, strlen($chars)-1)], range(1, $n)));
    return 'INS-' . $seg(4) . '-' . $seg(4);
}

function generate_download_token(): string
{
    return bin2hex(random_bytes(32));
}

require_once __DIR__ . '/GradingService.php';
require_once __DIR__ . '/ResultService.php';
require_once __DIR__ . '/StaffAudit.php';
require_once __DIR__ . '/StaffAuth.php';

function staff_user(): ?array
{
    return StaffAuth::user();
}

function staff_can(string $permission): bool
{
    return StaffAuth::can($permission);
}

function staff_can_class(int $classId): bool
{
    return StaffAuth::canAccessClass($classId);
}

function staff_can_subject(int $classId, int $subjectId): bool
{
    return StaffAuth::canAccessSubject($classId, $subjectId);
}

function staff_can_student(int $studentId): bool
{
    return StaffAuth::canAccessStudent($studentId);
}

function require_staff(bool $checkPasswordForce = true): void
{
    StaffAuth::requireAuth($checkPasswordForce);
}

function require_staff_permission(string $permission): void
{
    StaffAuth::requirePermission($permission);
}

function require_staff_class(int $classId): void
{
    StaffAuth::requireClass($classId);
}

function require_staff_subject(int $classId, int $subjectId): void
{
    StaffAuth::requireSubject($classId, $subjectId);
}

function require_staff_student(int $studentId): array
{
    return StaffAuth::requireStudent($studentId);
}

function parent_linked_children(): array
{
    if (empty($_SESSION['parent'])) {
        return [];
    }
    $db = Database::connect();
    $email = trim((string) ($_SESSION['parent']['email'] ?? ''));
    $phone = trim((string) ($_SESSION['parent']['phone'] ?? ''));
    $currentId = (int) ($_SESSION['parent']['applicant_id'] ?? 0);

    $whereClauses = [];
    $params = [];

    if ($email !== '') {
        $whereClauses[] = "(pa.email = ? AND pa.email != '')";
        $params[] = $email;
        $whereClauses[] = "(a.parent_email = ? AND a.parent_email != '')";
        $params[] = $email;
    }

    if ($phone !== '') {
        $whereClauses[] = "(pa.phone = ? AND pa.phone != '')";
        $params[] = $phone;
        $whereClauses[] = "(a.parent_phone = ? AND a.parent_phone != '')";
        $params[] = $phone;
    }

    if ($currentId > 0) {
        $whereClauses[] = "a.id = ?";
        $params[] = $currentId;
    }

    if (empty($whereClauses)) {
        return [];
    }

    $sql = "SELECT DISTINCT a.id, a.first_name, a.last_name, a.middle_name, a.application_number,
                            a.admission_number, a.status, a.passport_photo, a.class_id, c.name AS class_name
            FROM applicants a
            LEFT JOIN classes c ON c.id = a.class_id
            LEFT JOIN parent_accounts pa ON pa.applicant_id = a.id
            WHERE " . implode(' OR ', $whereClauses) . "
            ORDER BY a.first_name ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}



