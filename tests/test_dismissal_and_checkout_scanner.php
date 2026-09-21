<?php
declare(strict_types=1);

// tests/test_dismissal_and_checkout_scanner.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../controllers/PublicController.php';

if (!isset($argv[1])) {
    echo "========================================================\n";
    echo "EduCore: Student & Staff Dismissal & Check-Out Test\n";
    echo "========================================================\n\n";
}

$db = Database::connect();
$today = date('Y-m-d');
$nowTime = date('H:i:s');

// 1. Fetch or create a test student
$stmt = $db->query("SELECT * FROM applicants WHERE status = 'Enrolled' ORDER BY id ASC LIMIT 1");
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "No enrolled student found.\n";
    exit(1);
}

$studentId = (int)$student['id'];
$studentQr = $student['qr_data'] ?: ('ATTENDANCE-STD-' . $studentId);
if (!isset($argv[1])) {
    echo "[OK] Found enrolled student: ID #{$studentId} - {$student['first_name']} {$student['last_name']}\n";
    echo "[OK] Student QR Token: {$studentQr}\n\n";
}

// Clear today's attendance and exit logs for clean testing
if (!isset($argv[1])) {
    $db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$studentId, $today]);
    $db->prepare("DELETE FROM student_exit_logs WHERE student_id = ? AND exit_date = ?")->execute([$studentId, $today]);
}

// Run subprocess tests for clean exit isolation
$cases = [
    'test_afternoon_checkout' => function($db, $studentId, $studentQr, $today) {
        // Morning checkin
        $db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$studentId, $today]);
        $db->prepare("DELETE FROM student_exit_logs WHERE student_id = ? AND exit_date = ?")->execute([$studentId, $today]);
        $db->prepare("INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, status, scan_method, alert_sent, marked_by, created_at) VALUES (?, 1, 1, ?, '07:45:00', 'Present', 'qr_usb', 1, 1, NOW())")->execute([$studentId, $today]);

        $_SESSION['admin'] = ['id' => 1, 'name' => 'Admin', 'role' => 'admin'];
        $_POST['qr_data'] = $studentQr;
        $controller = new AttendanceController();
        $ref = new ReflectionMethod($controller, 'processScanAjax');
        $ref->invoke($controller);
    },
    'test_duplicate_checkout' => function($db, $studentId, $studentQr, $today) {
        $_SESSION['admin'] = ['id' => 1, 'name' => 'Admin', 'role' => 'admin'];
        $_POST['qr_data'] = $studentQr;
        $controller = new AttendanceController();
        $ref = new ReflectionMethod($controller, 'processScanAjax');
        $ref->invoke($controller);
    },
    'test_direct_dismissal_no_morning' => function($db, $studentId, $studentQr, $today) {
        $db->prepare("DELETE FROM attendance WHERE applicant_id = ? AND date = ?")->execute([$studentId, $today]);
        $db->prepare("DELETE FROM student_exit_logs WHERE student_id = ? AND exit_date = ?")->execute([$studentId, $today]);

        $_SESSION['admin'] = ['id' => 1, 'name' => 'Admin', 'role' => 'admin'];
        $_POST['qr_data'] = $studentQr;
        $controller = new AttendanceController();
        $ref = new ReflectionMethod($controller, 'processScanAjax');
        $ref->invoke($controller);
    },
    'test_staff_dismissal' => function($db, $studentId, $studentQr, $today) {
        $stmtStaff = $db->query("SELECT * FROM staff WHERE status = 'Active' ORDER BY id ASC LIMIT 1");
        $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);
        $staffId = (int)$staff['id'];
        $staffQr = $staff['qr_data'] ?: ('ATTENDANCE-STF-' . $staffId);
        $db->prepare("DELETE FROM staff_attendance WHERE staff_id = ? AND date = ?")->execute([$staffId, $today]);

        $_SESSION['admin'] = ['id' => 1, 'name' => 'Admin', 'role' => 'admin'];
        $_POST['qr_data'] = $staffQr;
        $controller = new AttendanceController();
        $ref = new ReflectionMethod($controller, 'processScanAjax');
        $ref->invoke($controller);
    }
];

if (isset($argv[1]) && isset($cases[$argv[1]])) {
    $cases[$argv[1]]($db, $studentId, $studentQr, $today);
    exit(0);
}

// Master runner
foreach ($cases as $name => $fn) {
    echo "--- Testing {$name} ---\n";
    $cmd = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe tests/test_dismissal_and_checkout_scanner.php ' . escapeshellarg($name);
    $out = shell_exec($cmd);
    echo "Output: " . trim($out ?: 'EMPTY') . "\n";
    $decoded = json_decode(trim($out ?: ''), true);
    assert($decoded !== null, "Response must be valid JSON for {$name}");
    if ($name === 'test_duplicate_checkout') {
        assert($decoded['action'] === 'duplicate_out', "Action must be duplicate_out for {$name}");
        echo "[OK] {$name} PASSED!\n\n";
    } else {
        assert($decoded['success'] === true && $decoded['action'] === 'check_out', "Action must be check_out for {$name}");
        echo "[OK] {$name} PASSED!\n\n";
    }
}

echo "========================================================\n";
echo "ALL DISMISSAL & CHECK-OUT TESTS PASSED PERFECTLY! ✓\n";
echo "========================================================\n";

