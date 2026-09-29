<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/QrCodeService.php';
require_once __DIR__ . '/../services/ScannerService.php';

echo "======================================================================\n";
echo " EDUCORE PURE TOKEN QR TEST SUITE\n";
echo "======================================================================\n";

$passed = 0;
$failed = 0;

function assertTest(string $title, bool $condition, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo " [PASS] {$title}\n";
    } else {
        $failed++;
        echo " [FAIL] {$title} — Detail: {$detail}\n";
    }
}

$db = Database::connect();

// ── TEST 1: Student 8873 Pure Token QR Generation ──────────────────────
echo "\n--- TEST 1: Student 8873 (ATTENDANCE-STD-QR-6ab57787b15d4) Pure Token Generation ---\n";
$st8873 = $db->query("SELECT * FROM applicants WHERE id = 8873 LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$st8873) {
    // Recreate student 8873 if missing
    $db->prepare("INSERT INTO applicants (id, school_id, class_id, first_name, last_name, admission_number, application_number, qr_data, status, student_status, parent_phone, created_at) VALUES (8873, 1, 1, 'Samuel', 'Okonkwo', 'STD-SCAN-7893', 'APP-7893', 'ATTENDANCE-STD-QR-6ab57787b15d4', 'Enrolled', 'Active', '08098765432', NOW())")->execute();
    $st8873 = $db->query("SELECT * FROM applicants WHERE id = 8873 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

$qrResult = QrCodeService::ensureStudentQr(8873, true);

assertTest("ensureStudentQr returns token ATTENDANCE-STD-QR-6ab57787b15d4", $qrResult['token'] === 'ATTENDANCE-STD-QR-6ab57787b15d4', "Got: {$qrResult['token']}");
assertTest("QR image file exists on disk", file_exists($qrResult['full_path']) && filesize($qrResult['full_path']) > 100, "Path: {$qrResult['full_path']}");
assertTest("img_url points to direct token file std_8873.png", str_contains($qrResult['img_url'], 'std_8873.png'), "Got: {$qrResult['img_url']}");
assertTest("img_url contains zero URL QR prefix", !str_contains($qrResult['img_url'], 'std_url_'), "Got: {$qrResult['img_url']}");

// ── TEST 2: Attendance Scanner Pure Token Decoding ─────────────────────
echo "\n--- TEST 2: Attendance Scanner Processing Pure Token ---\n";
$scannerService = new ScannerService($db);
$officerContext = [
    'school_id'                => 1,
    'staff_id'                 => 1,
    'assignment_id'            => 1,
    'station_id'               => 1,
    'can_scan_in'              => true,
    'can_scan_out'             => true,
    'can_view_today_logs'      => true,
    'can_view_student_details' => true,
    'officer_name'             => 'Officer Test'
];

// Scan pure token string
$scanResponse = $scannerService->processScan('ATTENDANCE-STD-QR-6ab57787b15d4', $officerContext, 'auto');

assertTest(
    "Scanner processes pure token successfully",
    in_array($scanResponse['action'] ?? '', ['check_in', 'check_out', 'duplicate_in', 'duplicate_out']),
    "Response: " . json_encode($scanResponse)
);
assertTest(
    "Scanner correctly identifies Samuel Okonkwo",
    ($scanResponse['student']['name'] ?? '') === 'Samuel Okonkwo',
    "Got: " . ($scanResponse['student']['name'] ?? 'None')
);

// ── TEST 3: Batch Regeneration of All Enrolled Students & Staff ────────
echo "\n--- TEST 3: Batch Repair All Missing / Stale QRs ---\n";
$repairRes = QrCodeService::repairAllMissing(true);
assertTest("repairAllMissing processes students", $repairRes['students_fixed'] >= 1, "Students fixed: {$repairRes['students_fixed']}");
assertTest("repairAllMissing processes staff", $repairRes['staff_fixed'] >= 1, "Staff fixed: {$repairRes['staff_fixed']}");

echo "\n======================================================================\n";
echo " TEST SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================================\n";

if ($failed === 0) {
    echo "\n>>> ALL PURE TOKEN QR TESTS PASSED! <<<\n\n";
} else {
    exit(1);
}
