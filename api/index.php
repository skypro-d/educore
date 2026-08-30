<?php
/**
 * api/index.php
 * EduCore Android POS Attendance API Controller & Router
 *
 * Implements endpoints for device authentication, remote configuration,
 * scanner workflows, synchronization and telemetry tracking.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SmsService.php';
require_once __DIR__ . '/../config/AttendanceRules.php';

$route = trim($_GET['route'] ?? '', '/');
$route = preg_replace('#^api/?#', '', $route);
$db = Database::connect();

// Helper to return json error response
function json_error(string $message, int $code = 400): never
{
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// Helper to return json success response
function json_success(array $data = []): never
{
    echo json_encode(array_merge([
        'status' => 'success'
    ], $data));
    exit;
}

// Helper to check device request authorization & signatures
function authorize_device(PDO $db): array
{
    $headers = getallheaders();
    
    // Support either Headers or $_POST/$_GET parameters
    $token     = trim($headers['X-Device-Token'] ?? $_REQUEST['device_token'] ?? '');
    $schoolId  = (int) ($headers['X-School-Id'] ?? $_REQUEST['school_id'] ?? 0);
    $timestamp = (int) ($headers['X-Timestamp'] ?? $_REQUEST['timestamp'] ?? 0);
    $sig       = trim($headers['X-Signature'] ?? $_REQUEST['signature'] ?? '');

    if ($token === '' || $schoolId === 0 || $timestamp === 0 || $sig === '') {
        json_error('Missing authentication parameters (X-Device-Token, X-School-Id, X-Timestamp, X-Signature required).', 401);
    }

    // Guard: replay protection (allow 15 mins drift for POS client clock delays)
    if (abs(time() - $timestamp) > 900) {
        json_error('Request timestamp mismatch. Ensure POS terminal device clock is synchronized.', 401);
    }

    // Look up device record
    $stmt = $db->prepare("SELECT * FROM attendance_devices WHERE device_token = ? AND school_id = ? LIMIT 1");
    $stmt->execute([$token, $schoolId]);
    $device = $stmt->fetch();

    if (!$device) {
        json_error('Unauthorized device token / School registry mismatch.', 401);
    }

    if ($device['status'] === 'blocked') {
        json_error('This device is not authorized.', 403);
    }

    // Verify cryptographic request signature
    // Expected signature pattern: sha256(device_token + school_id + timestamp)
    $expected = hash('sha256', $token . $schoolId . $timestamp);
    if (!hash_equals($expected, $sig)) {
        json_error('Cryptographic signature verification failed.', 403);
    }

    // Update last seen telemetry automatically on every request
    $battery = isset($_REQUEST['battery_level']) ? (int) $_REQUEST['battery_level'] : null;
    if ($battery !== null) {
        $upd = $db->prepare("UPDATE attendance_devices SET last_seen = NOW(), battery_level = ? WHERE id = ?");
        $upd->execute([$battery, $device['id']]);
    } else {
        $upd = $db->prepare("UPDATE attendance_devices SET last_seen = NOW() WHERE id = ?");
        $upd->execute([$device['id']]);
    }

    // Lock active school context for SchoolContext resolver
    SchoolContext::set($schoolId);

    return $device;
}

// ── Routing Endpoints ─────────────────────────────────────────────────────────

switch ($route) {
    
    // 0. SaaS License Server Validation
    case 'license/verify':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method Not Allowed. Must be POST.', 405);
        }
        $licKey = trim($_POST['license_key'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if ($licKey === '' || $apiKey === '') {
            json_error('License Key and API Key are required.');
        }

        $stmt = $db->prepare(
            "SELECT l.*, s.school_name, s.status AS school_status, s.domain 
             FROM school_licenses l
             JOIN schools s ON s.id = l.school_id
             WHERE l.license_key = ? AND s.api_key = ? LIMIT 1"
        );
        $stmt->execute([$licKey, $apiKey]);
        $lic = $stmt->fetch();

        if (!$lic) {
            json_error('Invalid license key or API key mapping.', 403);
        }

        $isActive = (int) $lic['is_active'] === 1 && strtotime($lic['expires_at']) >= time();

        json_success([
            'valid' => $isActive,
            'school_name' => $lic['school_name'],
            'plan' => $lic['plan'],
            'domain' => $lic['domain'],
            'expires_at' => $lic['expires_at'],
            'grace_days' => (int) $lic['grace_days']
        ]);
        break;

    case 'license/updates':
        // Return latest OTA release package
        $stmt = $db->query("SELECT * FROM system_updates WHERE is_published = 1 ORDER BY released_at DESC LIMIT 1");
        $update = $stmt->fetch();

        if (!$update) {
            json_success([
                'latest_version' => '2.0.0',
                'release_notes' => 'Stable production build.',
                'download_url' => null
            ]);
        }

        json_success([
            'latest_version' => $update['version'],
            'release_notes' => $update['release_notes'],
            'download_url' => $update['zip_package_path'] ? url($update['zip_package_path']) : null,
            'sql_migration_url' => $update['sql_migration_path'] ? url($update['sql_migration_path']) : null,
            'apk_url' => $update['apk_path'] ? url($update['apk_path']) : null
        ]);
        break;

    // 1. Device Registration / Login
    case 'device/login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method Not Allowed. Must be POST.', 451);
        }

        $schoolCode     = trim($_POST['school_code'] ?? '');
        $activationCode = trim($_POST['activation_code'] ?? '');
        $model          = trim($_POST['device_model'] ?? 'POS Terminal');
        $androidVer     = trim($_POST['android_version'] ?? '9.0');
        $serial         = trim($_POST['serial_number'] ?? '');
        $battery        = isset($_POST['battery_level']) ? (int) $_POST['battery_level'] : null;

        if ($schoolCode === '' || $activationCode === '') {
            json_error('School Code and Activation Code are required.');
        }

        // Resolve School registry ID
        $schStmt = $db->prepare("SELECT id, school_name FROM schools WHERE school_code = ? AND status = 'active' LIMIT 1");
        $schStmt->execute([$schoolCode]);
        $school = $schStmt->fetch();

        if (!$school) {
            json_error('School context active registry profile not found.');
        }

        $schoolId = (int) $school['id'];

        // Verify activation code
        $devStmt = $db->prepare("SELECT * FROM attendance_devices WHERE school_id = ? AND activation_code = ? LIMIT 1");
        $devStmt->execute([$schoolId, $activationCode]);
        $device = $devStmt->fetch();

        if (!$device) {
            json_error('Invalid activation code.');
        }

        if ($device['status'] === 'blocked') {
            json_error('This device profile is blocked by administration.', 403);
        }

        // Generate dynamic secure Device Token
        $newToken = 'SKY-DEV-' . bin2hex(random_bytes(24));

        // Update device telemetry parameters
        $upd = $db->prepare(
            "UPDATE attendance_devices 
             SET device_token = ?, device_model = ?, android_version = ?, 
                 serial_number = ?, battery_level = ?, status = 'active', 
                 last_seen = NOW(), activation_code = NULL 
             WHERE id = ?"
        );
        $upd->execute([$newToken, $model, $androidVer, $serial, $battery, $device['id']]);

        json_success([
            'device_token' => $newToken,
            'school_id' => $schoolId,
            'school_name' => $school['school_name'],
            'location' => $device['location'] ?: 'Gate'
        ]);
        break;

    // 2. Attendance scan submittal
    case 'device/attendance':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method Not Allowed. Must be POST.', 451);
        }

        $device = authorize_device($db);
        $schoolId = (int) $device['school_id'];
        $qrData = trim($_POST['qr_code'] ?? '');
        $posTime = trim($_POST['scan_time'] ?? date('H:i'));

        if ($qrData === '') {
            json_error('QR Code is empty.');
        }

        // Parse token if it's a URL
        if (str_contains($qrData, 'token=')) {
            if (preg_match('/[?&]token=([^&]+)/', $qrData, $matches)) {
                $qrData = urldecode($matches[1]);
            } else {
                $parts = parse_url($qrData);
                if (isset($parts['query'])) {
                    parse_str($parts['query'], $queryVars);
                    if (isset($queryVars['token'])) {
                        $qrData = trim($queryVars['token']);
                    }
                }
            }
        }
        $qrData = trim($qrData);

        // Locate enrolled student matching token
        $studStmt = $db->prepare(
            "SELECT a.*, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.school_id = ? AND a.qr_data = ? AND a.status = 'Enrolled'
             LIMIT 1"
        );
        $studStmt->execute([$schoolId, $qrData]);
        $student = $studStmt->fetch();

        if (!$student && str_starts_with($qrData, 'ATTENDANCE-STD-')) {
            $parts = explode('-', $qrData);
            $studentId = isset($parts[2]) ? (int)$parts[2] : 0;
            if ($studentId > 0) {
                $studStmtLegacy = $db->prepare(
                    "SELECT a.*, c.name AS class_name
                     FROM applicants a
                     LEFT JOIN classes c ON c.id = a.class_id
                     WHERE a.school_id = ? AND a.id = ? AND (a.qr_data IS NULL OR a.qr_data = '') AND a.status = 'Enrolled'
                     LIMIT 1"
                );
                $studStmtLegacy->execute([$schoolId, $studentId]);
                $student = $studStmtLegacy->fetch();
            }
        }

        if (!$student) {
            json_error('Student Not Found.', 404);
        }

        $today = date('Y-m-d');
        $studentId = (int) $student['id'];

        // Prevent duplicate scans for same session/day
        $chkStmt = $db->prepare("SELECT status, time_in FROM attendance WHERE school_id = ? AND applicant_id = ? AND date = ? LIMIT 1");
        $chkStmt->execute([$schoolId, $studentId, $today]);
        $existing = $chkStmt->fetch();

        if ($existing) {
            json_success([
                'attendance_status' => 'already',
                'message' => 'Attendance Already Recorded Today.',
                'student_name' => trim($student['first_name'] . ' ' . $student['last_name']),
                'class' => $student['class_name'] ?? 'General',
                'admission_number' => $student['application_number'],
                'passport_photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
                'time_in' => date('g:i A', strtotime($existing['time_in'])),
                'status_label' => $existing['status']
            ]);
        }

        // Determine Present / Late / Denied status based on school time rules
        $resolvedStatus = AttendanceRules::resolveStatus($posTime);

        if ($resolvedStatus === 'Denied') {
            json_error('Attendance closes after ' . AttendanceRules::format(setting('attendance_close_time', '09:00')), 400);
        }

        // Write record
        $ins = $db->prepare(
            "INSERT INTO attendance (school_id, applicant_id, class_id, date, time_in, status, alert_sent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
        );
        $ins->execute([
            $schoolId,
            $studentId,
            $student['class_id'] ?? null,
            $today,
            $posTime,
            $resolvedStatus
        ]);
        $attId = (int) $db->lastInsertId();

        // Update telemetry values on device
        $db->prepare("UPDATE attendance_devices SET last_scan_time = NOW() WHERE id = ?")->execute([$device['id']]);

        // Background Checkin SMS notice trigger
        register_shutdown_function(function() use ($db, $student, $posTime, $resolvedStatus, $attId): void {
            try {
                send_checkin_sms($db, $student, $posTime, $resolvedStatus, $attId);
            } catch (Throwable $e) {
                error_log('[POS API] Background SMS notice failed: ' . $e->getMessage());
            }
        });

        json_success([
            'attendance_status' => 'success',
            'message' => 'Attendance Successful',
            'student_name' => trim($student['first_name'] . ' ' . $student['last_name']),
            'class' => $student['class_name'] ?? 'General',
            'admission_number' => $student['application_number'],
            'passport_photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
            'time_in' => date('g:i A', strtotime($posTime)),
            'status_label' => $resolvedStatus
        ]);
        break;

    // 2b. POS Device Student Exit Scanner Endpoint
    case 'device/exit':
        $device = authorize_device($db);
        $schoolId = (int) $device['school_id'];

        $qrData = trim($_POST['qr_data'] ?? $_POST['token'] ?? '');
        if ($qrData === '') {
            json_error('Missing student QR code or token parameter.', 400);
        }

        // Parse token if full URL was scanned
        if (str_contains($qrData, 'token=')) {
            if (preg_match('/[?&]token=([^&]+)/', $qrData, $m)) {
                $qrData = urldecode($m[1]);
            }
        }
        $token = trim($qrData);

        $stmt = $db->prepare(
            "SELECT a.*, c.name AS class_name 
             FROM applicants a 
             LEFT JOIN classes c ON c.id = a.class_id 
             WHERE (a.qr_data = ? OR a.admission_number = ? OR a.application_number = ?)
             LIMIT 1"
        );
        $stmt->execute([$token, $token, $token]);
        $student = $stmt->fetch();

        if (!$student && str_starts_with($token, 'ATTENDANCE-STD-')) {
            $parts = explode('-', $token);
            $studentId = isset($parts[2]) ? (int) $parts[2] : 0;
            if ($studentId > 0) {
                $stmt = $db->prepare("SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id WHERE a.id = ? LIMIT 1");
                $stmt->execute([$studentId]);
                $student = $stmt->fetch();
            }
        }

        if (!$student) {
            json_error('Student record not found.', 404);
        }

        if ($student['status'] !== 'Enrolled') {
            json_error('Student is not currently enrolled (Status: ' . $student['status'] . ')', 400);
        }

        $studentId = (int) $student['id'];
        $today = date('Y-m-d');
        $nowTime = date('H:i:s');

        // Duplicate Exit Check
        $chk = $db->prepare("SELECT el.*, g.gate_name FROM student_exit_logs el LEFT JOIN school_gates g ON g.id = el.gate_id WHERE el.student_id = ? AND el.exit_date = ? LIMIT 1");
        $chk->execute([$studentId, $today]);
        $existing = $chk->fetch();
        if ($existing) {
            json_error('Already Checked Out! Student departed today at ' . date('g:i A', strtotime($existing['exit_time'])), 409);
        }

        $normalCloseTime = setting('exit_normal_time', setting('school_close_time', '14:30'));
        $currentTimeShort = date('H:i');
        $isEarly = ($currentTimeShort < $normalCloseTime);
        $exitType = $isEarly ? 'early' : 'normal';

        $reason = trim($_POST['exit_reason'] ?? ($isEarly ? 'Early Departure' : 'Normal Dismissal'));
        $pickupName = trim($_POST['pickup_person_name'] ?? '');
        $gateName = $device['location'] ?: 'Gate';

        $ins = $db->prepare(
            "INSERT INTO student_exit_logs
                (school_id, student_id, attendance_id, pickup_person_id, pickup_person_name,
                 exit_type, exit_reason, exit_reason_notes, exit_date, exit_time, exited_at,
                 gate_id, gate_name, scanned_by, scanned_by_name, scan_method, qr_token,
                 verification_status, sms_status, created_at)
             VALUES
                (?, ?, NULL, NULL, ?, ?, ?, NULL, ?, ?, NOW(), NULL, ?, NULL, ?, 'api_device', ?, 'verified', 'pending', NOW())"
        );
        $deviceName = $device['device_name'] ?: 'POS Terminal';
        $ins->execute([
            $schoolId, $studentId, $pickupName,
            $exitType, $reason, $today, $nowTime,
            $gateName, $deviceName, $token
        ]);
        $exitLogId = (int) $db->lastInsertId();

        // Background Exit SMS trigger
        register_shutdown_function(function() use ($db, $student, $exitType, $today, $nowTime, $reason, $pickupName, $exitLogId): void {
            try {
                $exitData = [
                    'exit_type' => $exitType,
                    'exit_date' => $today,
                    'exit_time' => $nowTime,
                    'exit_reason' => $reason,
                    'pickup_person_name' => $pickupName
                ];
                send_exit_sms($db, $student, $exitData, $exitLogId);
            } catch (Throwable $e) {
                error_log('[POS API] Background Exit SMS failed: ' . $e->getMessage());
            }
        });

        json_success([
            'exit_status' => 'success',
            'message' => 'Student Exit Verified & Logged',
            'student_name' => trim($student['first_name'] . ' ' . $student['last_name']),
            'class' => $student['class_name'] ?? 'General',
            'admission_number' => $student['admission_number'] ?: $student['application_number'],
            'exit_time' => date('g:i A', strtotime($nowTime)),
            'exit_type' => $exitType,
            'gate_name' => $gateName
        ]);
        break;

    // 3. Remote configuration sync
    case 'device/config':
        $device = authorize_device($db);
        $schoolId = (int) $device['school_id'];

        $times = AttendanceRules::getTimes();
        
        json_success([
            'school_name' => setting('school_name', 'EduCore School'),
            'location' => $device['location'] ?: 'Gate',
            'branding' => [
                'primary_color' => setting('primary_color', '#0b3d91'),
                'secondary_color' => setting('secondary_color', '#f4b942')
            ],
            'time_rules' => [
                'open_time' => $times['open'],
                'ontime_until' => $times['ontime_until'],
                'late_from' => $times['late_from'],
                'close_time' => $times['close']
            ]
        ]);
        break;

    // 4. Remote Status Ping
    case 'device/status':
        $device = authorize_device($db);
        json_success([
            'device_status' => $device['status'],
            'location' => $device['location'] ?: 'Gate'
        ]);
        break;

    // 5. Offline sync submittal
    case 'device/sync':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method Not Allowed. Must be POST.', 451);
        }

        $device = authorize_device($db);
        $schoolId = (int) $device['school_id'];

        $rawData = file_get_contents('php://input');
        $payload = json_decode($rawData, true);
        $records = $payload['records'] ?? [];

        if (empty($records)) {
            json_error('Empty sync payload records.');
        }

        $synced = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $r) {
            $qrData = trim($r['qr_code'] ?? '');
            $scanTime = trim($r['scan_time'] ?? '');
            $scanDate = trim($r['scan_date'] ?? date('Y-m-d'));

            if ($qrData === '' || $scanTime === '') {
                $failed++;
                continue;
            }

            // Parse token if it's a URL
            if (str_contains($qrData, 'token=')) {
                if (preg_match('/[?&]token=([^&]+)/', $qrData, $matches)) {
                    $qrData = urldecode($matches[1]);
                } else {
                    $parts = parse_url($qrData);
                    if (isset($parts['query'])) {
                        parse_str($parts['query'], $queryVars);
                        if (isset($queryVars['token'])) {
                            $qrData = trim($queryVars['token']);
                        }
                    }
                }
            }
            $qrData = trim($qrData);

            // Find student
            $studStmt = $db->prepare(
                "SELECT id, class_id FROM applicants 
                 WHERE school_id = ? AND qr_data = ? AND status = 'Enrolled' LIMIT 1"
            );
            $studStmt->execute([$schoolId, $qrData]);
            $student = $studStmt->fetch();

            if (!$student && str_starts_with($qrData, 'ATTENDANCE-STD-')) {
                $parts = explode('-', $qrData);
                $studentId = isset($parts[2]) ? (int)$parts[2] : 0;
                if ($studentId > 0) {
                    $studStmtLegacy = $db->prepare(
                        "SELECT id, class_id FROM applicants 
                         WHERE school_id = ? AND id = ? AND (qr_data IS NULL OR qr_data = '') AND status = 'Enrolled'
                         LIMIT 1"
                    );
                    $studStmtLegacy->execute([$schoolId, $studentId]);
                    $student = $studStmtLegacy->fetch();
                }
            }

            if (!$student) {
                $failed++;
                $errors[] = "Student QR not registered: " . $qrData;
                continue;
            }

            $studentId = (int) $student['id'];

            // Prevent duplicate records for date
            $chkStmt = $db->prepare("SELECT id FROM attendance WHERE school_id = ? AND applicant_id = ? AND date = ? LIMIT 1");
            $chkStmt->execute([$schoolId, $studentId, $scanDate]);
            if ($chkStmt->fetch()) {
                $synced++; // Treat as success since already exists
                continue;
            }

            // Resolve attendance status
            $resolvedStatus = AttendanceRules::resolveStatus($scanTime);

            try {
                $ins = $db->prepare(
                    "INSERT INTO attendance (school_id, applicant_id, class_id, date, time_in, status, alert_sent, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
                );
                $ins->execute([
                    $schoolId,
                    $studentId,
                    $student['class_id'] ?? null,
                    $scanDate,
                    $scanTime,
                    $resolvedStatus
                ]);
                $synced++;
            } catch (Throwable $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        json_success([
            'synced_count' => $synced,
            'failed_count' => $failed,
            'errors' => $errors
        ]);
        break;

    // 6. Version details
    case 'device/version':
        $device = authorize_device($db);
        json_success([
            'firmware_version' => $device['firmware_version'] ?: '2.0.0',
            'latest_version'   => '2.1.0',
            'update_available' => false
        ]);
        break;

    // ── Release Infrastructure ────────────────────────────────────────────────

    /**
     * POST v1/releases/register
     *
     * Called exclusively by GitHub Actions after a successful release build.
     * Registers a new immutable release record including the exact SHA256 of
     * the ZIP that was uploaded to GitHub Releases.
     *
     * Authentication: Authorization: Bearer <RELEASE_API_SECRET>
     * Body: JSON
     *
     * Returns 201 on success, 400 on validation error, 401 on auth failure,
     * 409 on duplicate version.
     *
     * NOTE: This endpoint calls ensure_system_releases_table() before every
     * database operation. This mirrors UpdateInstaller::ensureHistoryTable()
     * and guarantees the table exists even on a fresh installation before any
     * school has applied an update (i.e., before MigrationRunner has run).
     */
    case 'v1/releases/register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method Not Allowed. Must be POST.', 405);
        }

        // ── Table Bootstrap (idempotent, runs in microseconds if table exists) ─
        ensure_system_releases_table($db);

        // ── Authentication ────────────────────────────────────────────────────
        $headers = getallheaders();
        $authHeader = trim($headers['Authorization'] ?? $headers['authorization'] ?? '');
        $bearerToken = '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            $bearerToken = substr($authHeader, 7);
        }

        $releaseApiSecret = getEnvConfig('RELEASE_API_SECRET', '');
        if ($releaseApiSecret === '' || $bearerToken === '') {
            json_error('Unauthorized: missing or unconfigured API secret.', 401);
        }
        if (!hash_equals($releaseApiSecret, $bearerToken)) {
            json_error('Unauthorized: invalid Bearer token.', 401);
        }

        // ── Parse JSON body ───────────────────────────────────────────────────
        $body = (string) file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            json_error('Invalid JSON body.', 400);
        }

        // ── Input extraction ──────────────────────────────────────────────────
        $rVersion      = trim((string) ($data['version']           ?? ''));
        $rDownloadUrl  = trim((string) ($data['download_url']      ?? ''));
        $rDownloadFile = trim((string) ($data['download_file']     ?? ''));
        $rSha256       = strtolower(trim((string) ($data['sha256'] ?? '')));
        $rSignature    = trim((string) ($data['signature']         ?? ''));
        $rChannel      = trim((string) ($data['release_channel']   ?? 'stable'));
        $rMandatory    = isset($data['mandatory']) ? (int)(bool)$data['mandatory'] : 0;
        $rMinPhp       = trim((string) ($data['min_php_version']   ?? '8.3.0'));
        $rMinMysql     = trim((string) ($data['min_mysql_version'] ?? '8.0.0'));
        $rNotes        = trim((string) ($data['release_notes']     ?? ''));

        // ── Validation ────────────────────────────────────────────────────────
        // Semantic version: 1.0.0 or 1.0.0-beta.1 (dot-only 4-part versions rejected)
        if (!preg_match('/^\d+\.\d+\.\d+(-[0-9A-Za-z][0-9A-Za-z.\-]*)?$/', $rVersion)) {
            json_error('Invalid version format. Expected semver, e.g. 1.0.2 or 1.0.2-beta.1.', 400);
        }
        if ($rDownloadUrl === '') {
            json_error('download_url is required.', 400);
        }
        if (!filter_var($rDownloadUrl, FILTER_VALIDATE_URL)) {
            json_error('download_url must be a valid URL.', 400);
        }
        if ($rDownloadFile === '') {
            json_error('download_file is required.', 400);
        }
        // SHA256 must be exactly 64 lowercase hex characters
        if (!preg_match('/^[0-9a-f]{64}$/', $rSha256)) {
            json_error('sha256 must be a 64-character lowercase hex string.', 400);
        }
        if (!in_array($rChannel, ['stable', 'beta', 'canary'], true)) {
            json_error('release_channel must be one of: stable, beta, canary.', 400);
        }

        // ── Duplicate version guard (immutable releases) ──────────────────────
        $chkStmt = $db->prepare('SELECT `id`, `sha256` FROM `system_releases` WHERE `version` = ? LIMIT 1');
        $chkStmt->execute([$rVersion]);
        $existing = $chkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            http_response_code(409);
            echo json_encode([
                'status'  => 'error',
                'message' => "Version {$rVersion} already exists and is immutable. Releases cannot be overwritten."
            ]);
            exit;
        }

        // ── Insert ────────────────────────────────────────────────────────────
        $insStmt = $db->prepare("
            INSERT INTO `system_releases`
                (`version`, `download_url`, `download_file`, `sha256`, `signature`,
                 `release_channel`, `mandatory`, `min_php_version`, `min_mysql_version`,
                 `release_notes`, `is_published`, `released_at`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW())
        ");
        $insStmt->execute([
            $rVersion, $rDownloadUrl, $rDownloadFile, $rSha256, $rSignature ?: null,
            $rChannel, $rMandatory, $rMinPhp, $rMinMysql, $rNotes ?: null
        ]);

        http_response_code(201);
        echo json_encode([
            'status'          => 'success',
            'message'         => "Release {$rVersion} registered successfully.",
            'version'         => $rVersion,
            'release_channel' => $rChannel,
            'mandatory'       => (bool) $rMandatory
        ]);
        exit;

    /**
     * GET v1/releases/latest
     *
     * Returns the latest published release for the requested channel.
     * Called by school installations to check for updates.
     * No authentication required (public metadata only — no secrets returned).
     *
     * Query params:
     *   channel  stable|beta|canary  (default: stable)
     */
    case 'v1/releases/latest':
        ensure_system_releases_table($db);

        $channel = trim($_GET['channel'] ?? 'stable');
        if (!in_array($channel, ['stable', 'beta', 'canary'], true)) {
            $channel = 'stable';
        }

        $latStmt = $db->prepare("
            SELECT `version`, `download_url`, `download_file`, `sha256`, `signature`,
                   `release_channel`, `mandatory`, `min_php_version`, `min_mysql_version`,
                   `release_notes`, `released_at`
            FROM `system_releases`
            WHERE `release_channel` = ? AND `is_published` = 1
            ORDER BY `released_at` DESC
            LIMIT 1
        ");
        $latStmt->execute([$channel]);
        $release = $latStmt->fetch(PDO::FETCH_ASSOC);

        if (!$release) {
            json_success([
                'update_available' => false,
                'channel'          => $channel,
                'message'          => 'No published releases found for this channel.'
            ]);
        }

        json_success([
            'update_available'  => true,
            'version'           => $release['version'],
            'download_url'      => $release['download_url'],
            'download_file'     => $release['download_file'],
            'sha256'            => $release['sha256'],
            'signature'         => $release['signature'] ?? '',
            'release_channel'   => $release['release_channel'],
            'mandatory'         => (bool) $release['mandatory'],
            'min_php_version'   => $release['min_php_version'],
            'min_mysql_version' => $release['min_mysql_version'],
            'release_notes'     => $release['release_notes'] ?? '',
            'released_at'       => $release['released_at']
        ]);
        break;

    /**
     * GET/POST v1/updates/check
     *
     * Called by UpdateChecker::check() via ApiKeyService::sendSecureRequest().
     *
     * UpdateChecker contract (exact field names it reads from the response):
     *   $response['success']             bool
     *   $response['latest_version']      string  (used in version_compare)
     *   $response['sha256']              string  (stored in cache, passed to UpdateInstaller)
     *   $response['checksum']            string  (alias, also stored as 'sha256')
     *   $response['download_url']        string
     *   $response['signature']           string
     *   $response['mandatory']           bool
     *   $response['release_channel']     string
     *   $response['minimum_php_version'] string  (note: 'minimum_' prefix in this field)
     *   $response['release_notes']       string
     *   $response['release_date']        string  (Y-m-d)
     *
     * Body params (from ApiKeyService::sendSecureRequest):
     *   current_version, release_channel, installation_id, api_key
     */
    case 'v1/updates/check':
        ensure_system_releases_table($db);

        // Accepts both GET and POST (ApiKeyService sends POST with JSON body)
        $rawBody  = file_get_contents('php://input');
        $postData = json_decode($rawBody ?: '{}', true) ?: [];

        $currentVersion = ltrim(trim((string) ($postData['current_version'] ?? ($_GET['current_version'] ?? '0.0.0'))), 'v');
        $requestChannel = trim((string) ($postData['release_channel'] ?? ($_GET['channel'] ?? 'stable')));
        if (!in_array($requestChannel, ['stable', 'beta', 'canary'], true)) {
            $requestChannel = 'stable';
        }

        // Fetch the latest published release for this channel
        $updStmt = $db->prepare("
            SELECT `version`, `download_url`, `download_file`, `sha256`, `signature`,
                   `release_channel`, `mandatory`, `min_php_version`, `min_mysql_version`,
                   `release_notes`, `released_at`
            FROM `system_releases`
            WHERE `release_channel` = ? AND `is_published` = 1
            ORDER BY `released_at` DESC
            LIMIT 1
        ");
        $updStmt->execute([$requestChannel]);
        $latestRelease = $updStmt->fetch(PDO::FETCH_ASSOC);

        if (!$latestRelease) {
            // No releases registered yet — return current version, no update
            json_success([
                'success'             => true,
                'update_available'    => false,
                'current_version'     => $currentVersion,
                'latest_version'      => $currentVersion,
                'release_channel'     => $requestChannel,
                'mandatory'           => false,
                'minimum_php_version' => '8.3.0',
                'release_date'        => date('Y-m-d'),
                'release_notes'       => 'No updates available.',
                'checksum'            => '',
                'sha256'              => '',
                'signature'           => '',
                'download_url'        => ''
            ]);
        }

        $latestVersion   = ltrim((string) $latestRelease['version'], 'v');
        $updateAvailable = version_compare($latestVersion, $currentVersion, '>');

        // Return the exact fields UpdateChecker::check() reads from $response
        json_success([
            'success'             => true,
            'update_available'    => $updateAvailable,
            'current_version'     => $currentVersion,
            'latest_version'      => $latestVersion,
            'release_channel'     => $latestRelease['release_channel'],
            'mandatory'           => (bool) $latestRelease['mandatory'],
            'minimum_php_version' => $latestRelease['min_php_version'],
            'release_date'        => date('Y-m-d', strtotime($latestRelease['released_at'])),
            'release_notes'       => $latestRelease['release_notes'] ?? '',
            // Both field names — UpdateChecker reads: $response['sha256'] ?? ($response['checksum'] ?? '')
            'sha256'              => $latestRelease['sha256'],
            'checksum'            => $latestRelease['sha256'],
            'signature'           => $latestRelease['signature'] ?? '',
            'download_url'        => $latestRelease['download_url'],
            'download_file'       => $latestRelease['download_file']
        ]);
        break;

    default:
        json_error('Route not found / Endpoint undefined.', 404);
}
