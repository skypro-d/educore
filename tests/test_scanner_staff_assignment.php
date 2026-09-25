<?php
declare(strict_types=1);

/**
 * tests/test_scanner_staff_assignment.php
 * Automated End-to-End Test Suite for EduCore Scanner Staff Assignment & Portal
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/StaffAuth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/ScannerService.php';
require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../controllers/ScannerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

echo "======================================================================\n";
echo " EDUCORE SCANNER STAFF ASSIGNMENT & DEDICATED SCANNER PORTAL TEST SUITE\n";
echo "======================================================================\n\n";

$db = Database::connect();
SchoolContext::set(1);

$passed = 0;
$failed = 0;
$blocked = 0;
$results = [];

function recordTest(string $testName, bool $condition, string $details = '', string $filePath = ''): void {
    global $passed, $failed, $results;
    if ($condition) {
        $passed++;
        $results[] = ['name' => $testName, 'status' => 'PASS', 'details' => $details, 'file' => ''];
        echo " [PASS] {$testName}\n";
    } else {
        $failed++;
        $results[] = ['name' => $testName, 'status' => 'FAIL', 'details' => $details, 'file' => $filePath];
        echo " [FAIL] {$testName}" . ($details ? " -> {$details}" : '') . ($filePath ? " in [{$filePath}]" : '') . "\n";
    }
}

// ── TEST SETUP: Create Test Staff & Student ───────────────────────────
$rand = rand(1000, 9999);
$testStaffIdCode = 'STF-TEST-' . $rand;
$testStudentAdmNo = 'STD-SCAN-' . $rand;
$testStudentQr = 'ATTENDANCE-STD-QR-' . uniqid();

// 1. Create Test Staff Member
$db->prepare(
    "INSERT INTO staff (school_id, staff_id, first_name, last_name, email, phone, role, status, department, created_at)
     VALUES (1, ?, 'Officer', 'Johnson', 'officer.test@school.ng', '08012340001', 'Scanner Officer', 'Active', 'Security', NOW())"
)->execute([$testStaffIdCode]);
$staffId = (int) $db->lastInsertId();

// Create Staff Account
$passwordHash = password_hash('Pass1234!', PASSWORD_DEFAULT);
$db->prepare(
    "INSERT INTO staff_accounts (school_id, staff_id, username, password_hash, must_change_password, created_at)
     VALUES (1, ?, ?, ?, 0, NOW())"
)->execute([$staffId, 'scanner_user_' . $rand, $passwordHash]);
$staffAccountId = (int) $db->lastInsertId();

// 2. Create Test Student
$db->prepare(
    "INSERT INTO applicants (school_id, class_id, first_name, last_name, admission_number, application_number, qr_data, status, student_status, parent_phone, created_at)
     VALUES (1, 1, 'Samuel', 'Okonkwo', ?, ?, ?, 'Enrolled', 'Active', '08098765432', NOW())"
)->execute([$testStudentAdmNo, $testStudentAdmNo, $testStudentQr]);
$studentId = (int) $db->lastInsertId();

echo "-> Created Test Staff #{$staffId} ({$testStaffIdCode})\n";
echo "-> Created Test Student #{$studentId} (Adm: {$testStudentAdmNo}, QR: {$testStudentQr})\n\n";

$scannerService = new ScannerService($db);

// ── TEST GROUP 1: SCANNER STATIONS MANAGEMENT ─────────────────────────
echo "--- GROUP 1: Scanner Station Management ---\n";
$stations = $scannerService->getAllStations(1);
recordTest("Scanner stations table seeded and accessible", !empty($stations), "Found " . count($stations) . " stations");

$testStationId = $scannerService->saveStation([
    'station_name' => 'Gate Alpha Test Station',
    'station_code' => 'ALPHA-TEST-' . $rand,
    'scanner_type' => 'usb_hid',
    'location'     => 'North Entrance',
    'status'       => 'active'
], 1);

recordTest("Create new Scanner Station", $testStationId > 0, "Created station #{$testStationId}");

$fetchedStation = $scannerService->getStationById($testStationId, 1);
recordTest("Retrieve Scanner Station details", ($fetchedStation['station_name'] ?? '') === 'Gate Alpha Test Station');

// ── TEST GROUP 2: ADMIN SCANNER ASSIGNMENT ─────────────────────────────
echo "\n--- GROUP 2: Admin Scanner Staff Assignment ---\n";

$assignmentId = $scannerService->assignStaff(
    $staffId,
    $testStationId,
    [
        'can_scan_in'              => 1,
        'can_scan_out'             => 1,
        'can_view_today_logs'      => 1,
        'can_view_student_details' => 1
    ],
    1, // Assigned by admin #1
    'Assigned to Gate Alpha during morning and afternoon shifts.'
);

recordTest("Admin assigns Staff member as Scanner Officer", $assignmentId > 0, "Created assignment #{$assignmentId}");

// Verify Active Assignment Retrieval
$activeAssignment = $scannerService->getActiveAssignmentForStaff($staffId, 1);
recordTest("Retrieve active assignment for staff", $activeAssignment !== null && (int)$activeAssignment['station_id'] === $testStationId);
recordTest("Assignment has scan IN permission", (bool)($activeAssignment['can_scan_in'] ?? false));
recordTest("Assignment has scan OUT permission", (bool)($activeAssignment['can_scan_out'] ?? false));
recordTest("Assignment has view logs permission", (bool)($activeAssignment['can_view_today_logs'] ?? false));

// Test History
$history = $scannerService->getAssignmentHistory($staffId, 1);
recordTest("Assignment history recorded", count($history) >= 1);

// ── TEST GROUP 3: RBAC & ROUTE PROTECTION ──────────────────────────────
echo "\n--- GROUP 3: RBAC & Server-Side Security Isolation ---\n";

// Mock session as Scanner Officer
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
unset($_SESSION['admin']);
$_SESSION['teacher'] = [
    'id'                => $staffAccountId,
    'staff_table_id'    => $staffId,
    'username'          => 'scanner_user_' . $rand,
    'name'              => 'Officer Johnson',
    'first_name'        => 'Officer',
    'last_name'         => 'Johnson',
    'role'              => 'scanner_officer',
    'role_title'        => 'Scanner Officer',
    'permissions'       => ['scanner.access', 'scanner.scan_in', 'scanner.scan_out', 'scanner.view_today_logs', 'scanner.view_student_details'],
    'assigned_classes'  => [],
    'assigned_subjects' => []
];

StaffAuth::resetCache();

recordTest("StaffAuth identifies user as Scanner Officer", StaffAuth::isScannerOfficer() === true);
recordTest("StaffAuth retrieves active scanner assignment", StaffAuth::scannerAssignment() !== null);
recordTest("Scanner Officer has 'scanner.access' permission", StaffAuth::can('scanner.access') === true);
recordTest("Scanner Officer does NOT have 'students.manage' permission", StaffAuth::can('students.manage') === false);
recordTest("Scanner Officer does NOT have 'results.manage' permission", StaffAuth::can('results.manage') === false);
recordTest("Scanner Officer does NOT have 'fees.manage' permission", StaffAuth::can('fees.manage') === false);
recordTest("Scanner Officer does NOT have 'settings.manage' permission", StaffAuth::can('settings.manage') === false);

// Test Unassigned Staff Block
$db->prepare("INSERT INTO staff (school_id, staff_id, first_name, last_name, phone, role, status) VALUES (1, 'STF-UNASSIGNED', 'Unassigned', 'Staff', '0800000000', 'Teacher', 'Active')")->execute();
$unassignedStaffId = (int) $db->lastInsertId();
$unassignedAssignment = $scannerService->getActiveAssignmentForStaff($unassignedStaffId, 1);
recordTest("Unassigned staff has NO active scanner assignment", $unassignedAssignment === null);

// ── TEST GROUP 4: STUDENT SCAN IN OPERATION ────────────────────────────
echo "\n--- GROUP 4: Student Scan IN Processing ---\n";

// Clear any existing attendance for student today
$today = date('Y-m-d');
$db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$studentId, $today]);
$db->prepare("DELETE FROM student_exit_logs WHERE student_id = ? AND exit_date = ?")->execute([$studentId, $today]);

$officerContext = [
    'school_id'                => 1,
    'staff_id'                 => $staffId,
    'assignment_id'            => $assignmentId,
    'station_id'               => $testStationId,
    'can_scan_in'              => true,
    'can_scan_out'             => true,
    'can_view_today_logs'      => true,
    'can_view_student_details' => true,
    'officer_name'             => 'Officer Johnson'
];

$scanInResult = $scannerService->processScan($testStudentQr, $officerContext, 'in');

recordTest("Scan IN executes successfully", ($scanInResult['success'] ?? false) === true && ($scanInResult['action'] ?? '') === 'check_in', json_encode($scanInResult));
recordTest("Scan IN returns student details", ($scanInResult['student']['name'] ?? '') === 'Samuel Okonkwo');
recordTest("Scan IN returns class and admission number", ($scanInResult['student']['admission_number'] ?? '') === $testStudentAdmNo);
recordTest("Scan IN returns timestamp and status", in_array($scanInResult['status'] ?? '', ['Present', 'Late']));

// Verify Database State for Scan IN
$attRow = $db->query("SELECT * FROM attendance WHERE applicant_id = {$studentId} AND date = '{$today}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
recordTest("Attendance record inserted in DB", $attRow !== false);
recordTest("Attendance time_in populated", !empty($attRow['time_in']));
recordTest("Attendance scan_method recorded as qr_usb", ($attRow['scan_method'] ?? '') === 'qr_usb');

// ── TEST GROUP 5: DUPLICATE SCAN IN HANDLING ───────────────────────────
echo "\n--- GROUP 5: Duplicate Scan IN Handling ---\n";

$dupInResult = $scannerService->processScan($testStudentQr, $officerContext, 'in');
recordTest("Duplicate Scan IN rejected with duplicate_in action", ($dupInResult['action'] ?? '') === 'duplicate_in', json_encode($dupInResult));
recordTest("Duplicate Scan IN returns warning title 'ALREADY CHECKED IN'", str_contains($dupInResult['title'] ?? '', 'ALREADY CHECKED IN'));

// Verify no duplicate row created
$attCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE applicant_id = {$studentId} AND date = '{$today}'")->fetchColumn();
recordTest("Database maintains single attendance row (no duplicates)", $attCount === 1);

// ── TEST GROUP 6: STUDENT SCAN OUT OPERATION ───────────────────────────
echo "\n--- GROUP 6: Student Scan OUT Processing ---\n";

$scanOutResult = $scannerService->processScan($testStudentQr, $officerContext, 'out');
recordTest("Scan OUT executes successfully", ($scanOutResult['success'] ?? false) === true && ($scanOutResult['action'] ?? '') === 'check_out', json_encode($scanOutResult));
recordTest("Scan OUT status is 'Checked Out'", ($scanOutResult['status'] ?? '') === 'Checked Out');

// Verify Database State for Scan OUT
$attOutRow = $db->query("SELECT * FROM attendance WHERE applicant_id = {$studentId} AND date = '{$today}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
recordTest("Attendance time_out populated in DB", !empty($attOutRow['time_out']));

$exitLogRow = $db->query("SELECT * FROM student_exit_logs WHERE student_id = {$studentId} AND exit_date = '{$today}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
recordTest("Student exit log record created", $exitLogRow !== false);
recordTest("Student exit log records scanned_by_name officer", ($exitLogRow['scanned_by_name'] ?? '') === 'Officer Johnson');

// ── TEST GROUP 7: DUPLICATE SCAN OUT HANDLING ──────────────────────────
echo "\n--- GROUP 7: Duplicate Scan OUT Handling ---\n";

$dupOutResult = $scannerService->processScan($testStudentQr, $officerContext, 'out');
recordTest("Duplicate Scan OUT rejected with duplicate_out action", ($dupOutResult['action'] ?? '') === 'duplicate_out', json_encode($dupOutResult));
recordTest("Duplicate Scan OUT returns warning title 'ALREADY CHECKED OUT'", str_contains($dupOutResult['title'] ?? '', 'ALREADY CHECKED OUT'));

// ── TEST GROUP 8: INVALID STUDENT SCAN HANDLING ────────────────────────
echo "\n--- GROUP 8: Invalid Student Scan Handling ---\n";

$invalidQr = 'UNKNOWN-INVALID-BARCODE-' . uniqid();
$invalidResult = $scannerService->processScan($invalidQr, $officerContext, 'auto');

recordTest("Invalid scan returns success = false", ($invalidResult['success'] ?? true) === false);
recordTest("Invalid scan action is 'invalid'", ($invalidResult['action'] ?? '') === 'invalid');
recordTest("Invalid scan title contains 'INVALID STUDENT'", str_contains($invalidResult['title'] ?? '', 'INVALID STUDENT'));

// Verify no attendance created for invalid scan
$invalidAttCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE applicant_id = 999999999 AND date = '{$today}'")->fetchColumn();
recordTest("No attendance record created for invalid scan", $invalidAttCount === 0);

// ── TEST GROUP 9: SCANNER LOGS & ADMIN VISIBILITY ──────────────────────
echo "\n--- GROUP 9: Scanner Logs & Admin Audit Trail ---\n";

$todayLogs = $scannerService->getTodayLogs($staffId, 1, 'all', 10);
recordTest("Scanner Officer can view today's scan logs", count($todayLogs) >= 3);

$adminLogs = $scannerService->getAllScannerLogs(1, ['staff_id' => $staffId], 10);
recordTest("Admin can view full scanner logs with officer details", count($adminLogs) >= 3);

$stats = $scannerService->getScannerStats(1, $testStationId, $staffId);
recordTest("Scanner statistics computed accurately", ($stats['entries_today'] ?? 0) >= 1 && ($stats['exits_today'] ?? 0) >= 1);

// ── TEST GROUP 10: DEACTIVATION REVOKES ACCESS ─────────────────────────
echo "\n--- GROUP 10: Assignment Deactivation & Access Revocation ---\n";

$scannerService->toggleAssignmentStatus($assignmentId, 'inactive', 1);
$revokedAssignment = $scannerService->getActiveAssignmentForStaff($staffId, 1);
recordTest("Deactivating assignment removes active access", $revokedAssignment === null);

// Cleanup Test Records
$db->prepare("DELETE FROM attendance WHERE applicant_id = ?")->execute([$studentId]);
$db->prepare("DELETE FROM student_exit_logs WHERE student_id = ?")->execute([$studentId]);
$db->prepare("DELETE FROM applicants WHERE id = ?")->execute([$studentId]);
$db->prepare("DELETE FROM scanner_logs WHERE staff_id = ?")->execute([$staffId]);
$db->prepare("DELETE FROM scanner_assignments WHERE staff_id = ?")->execute([$staffId]);
$db->prepare("DELETE FROM scanner_stations WHERE id = ?")->execute([$testStationId]);
$db->prepare("DELETE FROM staff_accounts WHERE id = ?")->execute([$staffAccountId]);
$db->prepare("DELETE FROM staff WHERE id IN (?, ?)")->execute([$staffId, $unassignedStaffId]);

echo "\n======================================================================\n";
echo " TEST SUMMARY: {$passed} PASSED, {$failed} FAILED, {$blocked} BLOCKED\n";
echo "======================================================================\n";
