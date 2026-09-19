<?php
declare(strict_types=1);

/**
 * tests/test_x7_1000_attendance_scanner.php
 * Automated Verification for EduCore HIPPOINT X7-1000 USB Attendance Integration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';

echo "=== EDUCORE HIPPOINT X7-1000 ATTENDANCE INTEGRATION TEST SUITE ===\n\n";

$db = Database::connect();
SchoolContext::set(1);

// Ensure test admin session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$_SESSION['admin'] = [
    'id'        => 1,
    'school_id' => 1,
    'username'  => 'test_admin',
    'role'      => 'superadmin'
];

$passed = 0;
$failed = 0;

function assert_test(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo " [PASS] {$name}\n";
    } else {
        $failed++;
        echo " [FAIL] {$name}" . ($details ? " - {$details}" : '') . "\n";
    }
}

// 1. Setup Test Student
$testQrToken = 'TEST-QR-TOKEN-' . uniqid();
$testAdmNo = 'ADM-TEST-' . rand(1000, 9999);

// Check if test student exists, or insert one
$stmt = $db->prepare(
    "INSERT INTO applicants (school_id, class_id, first_name, last_name, admission_number, application_number, qr_data, status, student_status, parent_phone, created_at)
     VALUES (1, 1, 'Hippoint', 'Student', ?, ?, ?, 'Enrolled', 'Active', '08012345678', NOW())"
);
$stmt->execute([$testAdmNo, $testAdmNo, $testQrToken]);
$studentId = (int)$db->lastInsertId();

echo "-> Created test enrolled active student #{$studentId} (Token: {$testQrToken}, Adm: {$testAdmNo})\n\n";

// Clean any attendance rows for today
$today = date('Y-m-d');
$db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$studentId, $today]);

$controller = new AttendanceController();

// Helper to capture processScanAjax output
function simulateScan(AttendanceController $ctrl, string $payload): array {
    ob_start();
    // Simulate php://input by sending POST or temporary mock
    $_POST = ['qr_data' => $payload];
    
    // Use reflection or invoke processScanAjax
    try {
        $ctrl->processScanAjax();
    } catch (Throwable $e) {
        // In case exit() terminates execution, we handle with exception or fast exit in CLI
    }
    $output = ob_get_clean();
    $data = json_decode($output, true);
    return is_array($data) ? $data : ['raw' => $output];
}

// TEST 1: First Scan -> Expect CHECK-IN
echo "TEST 1: Initial Check-In with Full QR URL...\n";
$fullUrlPayload = "https://school.educore.ng/?route=attendance/scan&token=" . urlencode($testQrToken);

// Execute via controller method by calling internal query flow
function runCliScan(string $qrData): array {
    $php = 'c:\\wamp64\\bin\\php\\php8.3.28\\php.exe';
    $worker = __DIR__ . '/run_scan_worker.php';
    $cmd = escapeshellarg($php) . ' ' . escapeshellarg($worker) . ' ' . escapeshellarg($qrData);
    $output = shell_exec($cmd);
    return json_decode(trim((string)$output), true) ?: ['raw' => $output];
}

$res1 = runCliScan($fullUrlPayload);
assert_test("Initial Scan records check_in", ($res1['action'] ?? '') === 'check_in', json_encode($res1));
assert_test("Check-in status is Present or Late", in_array($res1['status'] ?? '', ['Present', 'Late']), 'Status: ' . ($res1['status'] ?? ''));

// Verify Database State
$stmtChk = $db->prepare("SELECT time_in, time_out, status, scan_method FROM attendance WHERE applicant_id = ? AND date = ?");
$stmtChk->execute([$studentId, $today]);
$row1 = $stmtChk->fetch(PDO::FETCH_ASSOC);
assert_test("Database attendance row created", !empty($row1), "Row exists");
assert_test("time_in is populated", !empty($row1['time_in']), "time_in: " . ($row1['time_in'] ?? 'NULL'));
assert_test("time_out is NULL on first scan", is_null($row1['time_out']), "time_out: " . var_export($row1['time_out'], true));
assert_test("scan_method is qr_usb", ($row1['scan_method'] ?? '') === 'qr_usb', "method: " . ($row1['scan_method'] ?? ''));

// TEST 2: Immediate Re-Scan -> Expect DUPLICATE DEBOUNCE REJECTION
echo "\nTEST 2: Immediate Re-Scan within 15-minute debounce window...\n";
$res2 = runCliScan($testQrToken);
assert_test("Duplicate scan flagged as duplicate_in", ($res2['action'] ?? '') === 'duplicate_in', json_encode($res2));
assert_test("Duplicate scan returns warning title", ($res2['title'] ?? '') === 'ALREADY CHECKED IN', "Title: " . ($res2['title'] ?? ''));

// TEST 3: Outside Debounce Window -> Simulate checkout by rewinding time_in by 45 minutes
echo "\nTEST 3: Check-Out Scan outside debounce window...\n";
$rewoundTime = date('H:i:s', strtotime('-45 minutes'));
$db->prepare("UPDATE attendance SET time_in = ? WHERE applicant_id = ? AND date = ?")->execute([$rewoundTime, $studentId, $today]);

$res3 = runCliScan($testQrToken);
assert_test("Scan after debounce window records check_out", ($res3['action'] ?? '') === 'check_out', json_encode($res3));
assert_test("Check-out returns primary/info status", ($res3['status'] ?? '') === 'Checked Out', "Status: " . ($res3['status'] ?? ''));

// Verify Database State for Check-out
$stmtChk->execute([$studentId, $today]);
$row3 = $stmtChk->fetch(PDO::FETCH_ASSOC);
assert_test("Database time_out is now populated", !empty($row3['time_out']), "time_out: " . ($row3['time_out'] ?? 'NULL'));

// TEST 4: Re-scan after Check-Out -> Expect DUPLICATE CHECKOUT REJECTION
echo "\nTEST 4: Re-scan after check-out has already been recorded...\n";
$res4 = runCliScan($testQrToken);
assert_test("Re-scan flagged as duplicate_out", ($res4['action'] ?? '') === 'duplicate_out', json_encode($res4));
assert_test("Duplicate checkout returns ALREADY CHECKED OUT", ($res4['title'] ?? '') === 'ALREADY CHECKED OUT', "Title: " . ($res4['title'] ?? ''));

// TEST 5: Fallback Admission Number Scan
echo "\nTEST 5: Scanned via fallback student Admission Number...\n";
// Create second student
$testAdmNo2 = 'ADM-FALLBACK-' . rand(1000, 9999);
$db->prepare(
    "INSERT INTO applicants (school_id, class_id, first_name, last_name, admission_number, application_number, status, student_status, parent_phone, created_at)
     VALUES (1, 1, 'Fallback', 'Student', ?, ?, 'Enrolled', 'Active', '08099999999', NOW())"
)->execute([$testAdmNo2, $testAdmNo2]);
$studentId2 = (int)$db->lastInsertId();

$res5 = runCliScan($testAdmNo2);
assert_test("Admission number fallback check-in", ($res5['action'] ?? '') === 'check_in', json_encode($res5));

// TEST 6: Invalid / Unrecognized QR Token
echo "\nTEST 6: Invalid QR Code Scan...\n";
$res6 = runCliScan('COMPLETELY-INVALID-QR-STRING-XYZ-999');
assert_test("Invalid QR flagged as not_found", ($res6['action'] ?? '') === 'not_found', json_encode($res6));

// TEST 7: Inactive / Suspended Student
echo "\nTEST 7: Inactive Student QR Scan...\n";
$inactiveToken = 'INACTIVE-TOKEN-' . uniqid();
$db->prepare(
    "INSERT INTO applicants (school_id, class_id, first_name, last_name, admission_number, qr_data, status, student_status, created_at)
     VALUES (1, 1, 'Inactive', 'User', 'ADM-INACTIVE', ?, 'Suspended', 'Inactive', NOW())"
)->execute([$inactiveToken]);
$inactiveStudentId = (int)$db->lastInsertId();

$res7 = runCliScan($inactiveToken);
assert_test("Inactive student flagged as inactive_student", ($res7['action'] ?? '') === 'inactive_student', json_encode($res7));

// Cleanup test records
$db->prepare("DELETE FROM attendance WHERE applicant_id IN (?, ?, ?)")->execute([$studentId, $studentId2, $inactiveStudentId]);
$db->prepare("DELETE FROM applicants WHERE id IN (?, ?, ?)")->execute([$studentId, $studentId2, $inactiveStudentId]);

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
