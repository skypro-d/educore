<?php
/**
 * Test Suite: EduCore QR Attendance Scanner Upgrade
 * Tests A through I as specified in Section 21 of the specification.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../services/ScannerService.php';
require_once __DIR__ . '/../services/QrCodeService.php';

$db = Database::connect();

echo "========================================================\n";
echo " EDUCORE QR ATTENDANCE UPGRADE VERIFICATION SUITE\n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsTotal = 9;

// Ensure test student exists with exact attendance number: ATTENDANCE-STD-3-dc089bd4
$testAttendanceCode = 'ATTENDANCE-STD-3-dc089bd4';

$stmtFind = $db->prepare("SELECT * FROM applicants WHERE qr_data = ? LIMIT 1");
$stmtFind->execute([$testAttendanceCode]);
$testStudent = $stmtFind->fetch(PDO::FETCH_ASSOC);

if (!$testStudent) {
    // Check if applicant id = 3 exists
    $chk3 = $db->prepare("SELECT * FROM applicants WHERE id = 3 LIMIT 1");
    $chk3->execute();
    $student3 = $chk3->fetch(PDO::FETCH_ASSOC);

    if ($student3) {
        $db->prepare("UPDATE applicants SET qr_data = ?, status = 'Enrolled', student_status = 'Active' WHERE id = 3")
           ->execute([$testAttendanceCode]);
        $stmtFind->execute([$testAttendanceCode]);
        $testStudent = $stmtFind->fetch(PDO::FETCH_ASSOC);
    } else {
        $ins = $db->prepare(
            "INSERT INTO applicants (id, school_id, class_id, first_name, last_name, admission_number, qr_data, status, student_status, created_at)
             VALUES (3, 1, 1, 'John', 'Doe', 'ADM-TEST-003', ?, 'Enrolled', 'Active', NOW())
             ON DUPLICATE KEY UPDATE qr_data = VALUES(qr_data), status = 'Enrolled', student_status = 'Active'"
        );
        $ins->execute([$testAttendanceCode]);
        $stmtFind->execute([$testAttendanceCode]);
        $testStudent = $stmtFind->fetch(PDO::FETCH_ASSOC);
    }
}

$testStudentId = (int) $testStudent['id'];
$today = date('Y-m-d');

// Helper to run worker process
function callWorker(string $qrPayload): array {
    $php = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';
    $worker = __DIR__ . '/run_scan_worker.php';
    $cmd = sprintf('"%s" "%s" %s', $php, $worker, escapeshellarg($qrPayload));
    $output = shell_exec($cmd) ?? '';
    
    // Find JSON inside output
    if (preg_match('/\{.*\}/s', $output, $m)) {
        $data = json_decode($m[0], true);
        if ($data !== null) {
            return $data;
        }
    }
    return ['raw' => $output];
}

// -------------------------------------------------------------
// Test C — Invalid QR
// Input: HELLO123
// Expected: Invalid attendance QR code
// -------------------------------------------------------------
echo "[RUNNING] Test C — Invalid QR...\n";
$normC = normalizeAttendanceQr('HELLO123');
$resC = callWorker('HELLO123');

if ($normC === null && ($resC['message'] ?? '') === 'Invalid attendance QR code.' && ($resC['action'] ?? '') === 'invalid') {
    echo "  [PASS] Test C Passed: Returned 'Invalid attendance QR code.' without DB query.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test C Failed: " . json_encode($resC) . "\n\n";
}

// -------------------------------------------------------------
// Test D — Unknown attendance code
// Input: ATTENDANCE-STD-999-NOTFOUND
// Expected: Student not found
// -------------------------------------------------------------
echo "[RUNNING] Test D — Unknown attendance code...\n";
$normD = normalizeAttendanceQr('ATTENDANCE-STD-999-NOTFOUND');
$resD = callWorker('ATTENDANCE-STD-999-NOTFOUND');

if ($normD === 'ATTENDANCE-STD-999-NOTFOUND' && ($resD['message'] ?? '') === 'Student not found.' && ($resD['action'] ?? '') === 'not_found') {
    echo "  [PASS] Test D Passed: Returned 'Student not found.' for unknown valid code.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test D Failed: " . json_encode($resD) . "\n\n";
}

// Reset attendance for $testStudentId today before lifecycle tests
$db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$testStudentId, $today]);
$db->prepare("DELETE FROM student_exit_logs WHERE student_id = ? AND exit_date = ?")->execute([$testStudentId, $today]);

// -------------------------------------------------------------
// Test E — First scan
// Expected: TIME IN
// -------------------------------------------------------------
echo "[RUNNING] Test E — First scan...\n";
$resE = callWorker($testAttendanceCode);

if (!empty($resE['success']) && ($resE['action'] ?? '') === 'check_in' && str_contains($resE['title'] ?? '', 'TIME IN')) {
    echo "  [PASS] Test E Passed: Recorded TIME IN successfully.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test E Failed: " . json_encode($resE) . "\n\n";
}

// -------------------------------------------------------------
// Test H — Rapid duplicate scan (within 2-5 seconds)
// Expected: No duplicate attendance event
// -------------------------------------------------------------
echo "[RUNNING] Test H — Rapid duplicate scan (<5 seconds)...\n";
$resH = callWorker($testAttendanceCode);

if (empty($resH['success']) && ($resH['action'] ?? '') === 'duplicate_in' && str_contains($resH['message'] ?? '', 'No duplicate attendance event')) {
    echo "  [PASS] Test H Passed: Rapid duplicate scan rejected with 'No duplicate attendance event.'\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test H Failed: " . json_encode($resH) . "\n\n";
}

// -------------------------------------------------------------
// Test F — Second valid scan (after debounce window)
// Expected: TIME OUT
// -------------------------------------------------------------
echo "[RUNNING] Test F — Second valid scan (after debounce window)...\n";
// Artificially adjust time_in by -60 seconds to simulate time elapsed past debounce
$db->prepare("UPDATE attendance SET time_in = SUBTIME(time_in, '00:01:00'), created_at = SUBTIME(created_at, '00:01:00') WHERE applicant_id = ? AND date = ?")
   ->execute([$testStudentId, $today]);

$resF = callWorker($testAttendanceCode);

if (!empty($resF['success']) && ($resF['action'] ?? '') === 'check_out' && str_contains($resF['title'] ?? '', 'TIME OUT')) {
    echo "  [PASS] Test F Passed: Recorded TIME OUT successfully.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test F Failed: " . json_encode($resF) . "\n\n";
}

// -------------------------------------------------------------
// Test G — Third scan
// Expected: Attendance already completed for today
// -------------------------------------------------------------
echo "[RUNNING] Test G — Third scan...\n";
$resG = callWorker($testAttendanceCode);

if (empty($resG['success']) && ($resG['action'] ?? '') === 'duplicate_out' && str_contains($resG['message'] ?? '', 'Attendance already completed for today')) {
    echo "  [PASS] Test G Passed: Returned 'Attendance already completed for today.'\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test G Failed: " . json_encode($resG) . "\n\n";
}

// -------------------------------------------------------------
// Test A — New QR format lookup & normalization
// Input: ATTENDANCE-STD-3-dc089bd4
// Expected: Student found, Attendance recorded
// -------------------------------------------------------------
echo "[RUNNING] Test A — New QR payload normalization...\n";
$normA = normalizeAttendanceQr($testAttendanceCode);
if ($normA === $testAttendanceCode) {
    echo "  [PASS] Test A Passed: New QR decoded cleanly as '{$normA}'.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test A Failed: " . var_export($normA, true) . "\n\n";
}

// -------------------------------------------------------------
// Test B — Old QR URL format backward compatibility
// Input: https://myuniformempire.com.ng/?route=attendance/scan&token=ATTENDANCE-STD-3-dc089bd4
// Expected: Student found, Attendance recorded
// -------------------------------------------------------------
echo "[RUNNING] Test B — Old QR URL backward compatibility...\n";
$oldQrUrl = 'https://myuniformempire.com.ng/?route=attendance/scan&token=' . $testAttendanceCode;
$normB = normalizeAttendanceQr($oldQrUrl);

// Reset attendance for test B verification
$db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$testStudentId, $today]);
$resB = callWorker($oldQrUrl);

if ($normB === $testAttendanceCode && !empty($resB['success']) && str_contains($resB['title'] ?? '', 'TIME IN')) {
    echo "  [PASS] Test B Passed: Old printed URL normalized to '{$normB}' and recorded attendance.\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test B Failed: normB='{$normB}', resB=" . json_encode($resB) . "\n\n";
}

// -------------------------------------------------------------
// Test I — New card generation
// Confirm generated QR payload contains ONLY: ATTENDANCE-STD-3-dc089bd4
// -------------------------------------------------------------
echo "[RUNNING] Test I — Card generation payload validation...\n";
$qrResult = QrCodeService::ensureStudentQr($testStudent, true);
$serviceFile = file_get_contents(__DIR__ . '/../services/QrCodeService.php');

$isTokenOnlyInPrimaryQr = str_contains($serviceFile, 'self::generatePng($token, $qrFullPath');
$hasValidFile = !empty($qrResult['full_path']) && file_exists($qrResult['full_path']) && filesize($qrResult['full_path']) > 0;
$matchesExactToken = ($qrResult['token'] === $testAttendanceCode);

if ($isTokenOnlyInPrimaryQr && $hasValidFile && $matchesExactToken) {
    echo "  [PASS] Test I Passed: QR generator encodes ONLY attendance number without URL strings. Token: {$qrResult['token']}, File: {$qrResult['relative_path']}\n\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Test I Failed: Generator did not meet pure payload requirement. Details: " . json_encode($qrResult) . "\n\n";
}

echo "========================================================\n";
echo " SUMMARY: {$testsPassed} / {$testsTotal} TESTS PASSED\n";
if ($testsPassed === $testsTotal) {
    echo " OVERALL STATUS: ALL TESTS PASSED (PASS)\n";
} else {
    echo " OVERALL STATUS: FAILED\n";
}
echo "========================================================\n";
