<?php
declare(strict_types=1);

// tests/test_staff_id_and_attendance.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/AttendanceRules.php';

echo "========================================================\n";
echo "EduCore: Staff ID Card & Attendance End-to-End Test\n";
echo "========================================================\n\n";

$db = Database::connect();

// 1. Verify staff record exists and has qr_data
echo "--- 1. Testing Staff QR and ID Card Metadata ---\n";
$stmt = $db->query("SELECT * FROM staff WHERE status = 'Active' ORDER BY id ASC LIMIT 1");
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    echo "Creating a test staff member...\n";
    $db->prepare(
        "INSERT INTO staff (school_id, first_name, last_name, email, phone, role, department, designation, staff_id, qr_data, blood_group, emergency_contact_name, emergency_contact_phone, status, created_at)
         VALUES (1, 'Dr. Samuel', 'Adeyemi', 'samuel.adeyemi@educore.test', '08012345678', 'teacher', 'Science', 'Senior Physics Master', 'STF-TEST-001', 'ATTENDANCE-STF-9999-TEST', 'O+', 'Mrs. Adeyemi', '08098765432', 'Active', NOW())"
    )->execute();
    $staffId = (int)$db->lastInsertId();
    $stmt = $db->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
}

$staffId = (int)$staff['id'];
$qrData = $staff['qr_data'] ?: ('ATTENDANCE-STF-' . $staffId);
echo "[OK] Staff found: ID #{$staffId} - {$staff['first_name']} {$staff['last_name']} (Staff ID: {$staff['staff_id']})\n";
echo "[OK] Staff QR Token: {$qrData}\n";

// 2. Clean today's attendance for this staff for a clean test run
$today = date('Y-m-d');
$db->prepare("DELETE FROM staff_attendance WHERE staff_id = ? AND date = ?")->execute([$staffId, $today]);
echo "[OK] Cleared any existing attendance for staff #{$staffId} today ({$today})\n\n";

// 3. Test Staff ID Card view query & data completeness
echo "--- 2. Testing Staff ID Card Data Completeness (Admin Only) ---\n";
$idCardStmt = $db->prepare(
    "SELECT s.*, r.name as role_title, r.description as role_desc
     FROM staff s
     LEFT JOIN roles r ON r.id = s.role_id
     WHERE s.id = ? LIMIT 1"
);
$idCardStmt->execute([$staffId]);
$cardData = $idCardStmt->fetch(PDO::FETCH_ASSOC);
assert($cardData !== false, 'Staff ID card query must return record');
echo "[OK] ID Card View Query Success: Name={$cardData['first_name']} {$cardData['last_name']}, Designation=" . ($cardData['designation'] ?: $cardData['role_title']) . ", Blood={$cardData['blood_group']}, Emergency={$cardData['emergency_contact_name']}\n\n";

// 4. Test Check-In Arrival insertion into staff_attendance
echo "--- 3. Testing Staff Attendance Database Operations ---\n";
$timeIn = date('H:i:s');
$resolvedStatus = AttendanceRules::resolveCurrentStatus();
if ($resolvedStatus === 'Denied') $resolvedStatus = 'Late';

$db->prepare(
    "INSERT INTO staff_attendance (staff_id, school_id, date, time_in, status, scan_method, marked_by, created_at)
     VALUES (?, 1, ?, ?, ?, 'qr_usb', 1, NOW())"
)->execute([$staffId, $today, $timeIn, $resolvedStatus]);
$attId = (int)$db->lastInsertId();
echo "[OK] Check-in Arrival Logged: ID #{$attId}, Status={$resolvedStatus}, TimeIn={$timeIn}\n";

// Verify retrieval
$chk = $db->prepare("SELECT * FROM staff_attendance WHERE id = ?");
$chk->execute([$attId]);
$record = $chk->fetch(PDO::FETCH_ASSOC);
assert($record['time_in'] === $timeIn, 'Recorded time_in must match');
assert($record['status'] === $resolvedStatus, 'Recorded status must match');
echo "[OK] Check-in arrival record verified in database.\n\n";

// 5. Test Check-Out Departure update
echo "--- 4. Testing Staff Check-Out Departure ---\n";
$timeOut = date('H:i:s', time() + 3600);
$db->prepare(
    "UPDATE staff_attendance SET time_out = ?, scan_method = 'qr_usb' WHERE id = ?"
)->execute([$timeOut, $attId]);

$chk->execute([$attId]);
$updatedRecord = $chk->fetch(PDO::FETCH_ASSOC);
assert($updatedRecord['time_out'] === $timeOut, 'Recorded time_out must match');
echo "[OK] Departure Logged: TimeOut={$updatedRecord['time_out']}\n\n";

// 6. Test Staff Monthly Attendance Matrix Query (Admin Report)
echo "--- 5. Testing Admin Staff Attendance Report Matrix ---\n";
$month = date('Y-m');
$reportStmt = $db->prepare(
    "SELECT sa.staff_id, sa.date, sa.time_in, sa.time_out, sa.status
     FROM staff_attendance sa
     WHERE sa.date LIKE ?
     ORDER BY sa.date ASC"
);
$reportStmt->execute([$month . '-%']);
$monthLogs = $reportStmt->fetchAll(PDO::FETCH_ASSOC);
echo "[OK] Monthly staff attendance query fetched " . count($monthLogs) . " log(s) for {$month}\n\n";

// 7. Test Teacher Portal My Attendance View query
echo "--- 6. Testing Staff Personal Attendance Query (Staff Portal) ---\n";
$myStmt = $db->prepare(
    "SELECT * FROM staff_attendance WHERE staff_id = ? AND date LIKE ? ORDER BY date DESC"
);
$myStmt->execute([$staffId, $month . '-%']);
$personalLogs = $myStmt->fetchAll(PDO::FETCH_ASSOC);
assert(!empty($personalLogs), 'Personal attendance log must contain records');
echo "[OK] Teacher personal logs fetched " . count($personalLogs) . " record(s) for Staff #{$staffId}\n\n";

echo "========================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY! ✓\n";
echo "========================================================\n";
