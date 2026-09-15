<?php
declare(strict_types=1);

/**
 * tests/test_all_portals_forgot_password.php
 * Automated verification suite for Unified Forgot Password across Parent, Student, and Staff portals.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Database::connect();
SchoolContext::set(1);

$passed = 0;
$failed = 0;

function assert_true(bool $condition, string $testName): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        echo " [FAIL] {$testName}\n";
        $failed++;
    }
}

function assert_equals($expected, $actual, string $testName): void {
    global $passed, $failed;
    if ($expected === $actual) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        $expStr = is_scalar($expected) ? (string)$expected : json_encode($expected);
        $actStr = is_scalar($actual) ? (string)$actual : json_encode($actual);
        echo " [FAIL] {$testName} (Expected '{$expStr}', got '{$actStr}')\n";
        $failed++;
    }
}

$phpBinary = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';
$baseDir = str_replace('\\', '/', dirname(__DIR__));

function runControllerAction(string $phpBinary, string $baseDir, string $controllerClass, string $methodName, array $postData, ?string $tokenParam = null): void {
    $code = '<?php '
        . '$baseDir = ' . var_export($baseDir, true) . '; '
        . 'require_once $baseDir . "/config/config.php"; '
        . 'require_once $baseDir . "/config/database.php"; '
        . 'require_once $baseDir . "/config/helpers.php"; '
        . 'require_once $baseDir . "/controllers/' . $controllerClass . '.php"; '
        . 'SchoolContext::set(1); '
        . 'session_start(); '
        . '$_SESSION["csrf_token"] = "valid_test_token"; '
        . '$_SERVER["REQUEST_METHOD"] = "POST"; '
        . '$_POST = ' . var_export($postData, true) . '; '
        . 'if (' . var_export($tokenParam !== null, true) . ') { $_GET["token"] = ' . var_export($tokenParam, true) . '; } '
        . '$c = new ' . $controllerClass . '(); '
        . '$c->' . $methodName . '(); ';

    $tmpFile = tempnam(sys_get_temp_dir(), 'test_pwd_') . '.php';
    file_put_contents($tmpFile, $code);
    exec("\"{$phpBinary}\" \"{$tmpFile}\" 2>&1", $out, $ret);
    @unlink($tmpFile);
}

echo "================================================================================\n";
echo "TEST SUITE: UNIFIED FORGOT PASSWORD (PARENT, STUDENT, STAFF)\n";
echo "================================================================================\n\n";

// Ensure test class exists
$testClass = $pdo->query("SELECT id FROM classes WHERE school_id = 1 LIMIT 1")->fetch();
$classId = $testClass ? (int) $testClass['id'] : 1;

// -----------------------------------------------------------------------------
// GROUP 1: Database Schema Verification
// -----------------------------------------------------------------------------
echo "--- Group 1: Database Schema Verification ---\n";

$pCols = $pdo->query("SHOW COLUMNS FROM parent_accounts")->fetchAll(PDO::FETCH_COLUMN);
assert_true(in_array('reset_token', $pCols, true), "parent_accounts has reset_token column");
assert_true(in_array('reset_expires', $pCols, true), "parent_accounts has reset_expires column");

$sCols = $pdo->query("SHOW COLUMNS FROM student_accounts")->fetchAll(PDO::FETCH_COLUMN);
assert_true(in_array('reset_token', $sCols, true), "student_accounts has reset_token column");
assert_true(in_array('reset_expires', $sCols, true), "student_accounts has reset_expires column");

$stfCols = $pdo->query("SHOW COLUMNS FROM staff_accounts")->fetchAll(PDO::FETCH_COLUMN);
assert_true(in_array('reset_token', $stfCols, true), "staff_accounts has reset_token column");
assert_true(in_array('reset_expires', $stfCols, true), "staff_accounts has reset_expires column");


// -----------------------------------------------------------------------------
// GROUP 2: Parent Portal Password Reset Flow
// -----------------------------------------------------------------------------
echo "\n--- Group 2: Parent Portal Password Reset Flow ---\n";

$testParentEmail = 'testparent_' . rand(10000, 99999) . '@schooltest.com';
$initialParentPass = 'InitialPass123';
$newParentPass = 'NewSecurePass456';

// Create test applicant and parent
$pdo->prepare("INSERT INTO applicants (school_id, application_number, first_name, last_name, class_id, parent_email, parent_phone, status, created_at)
               VALUES (1, ?, 'TestParentStudent', 'Smith', ?, ?, '08012345678', 'Enrolled', NOW())")
    ->execute(['APP-PRNT-' . rand(1000, 9999), $classId, $testParentEmail]);
$testApplicantId = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO parent_accounts (school_id, applicant_id, phone, email, password_hash, created_at)
               VALUES (1, ?, '08012345678', ?, ?, NOW())")
    ->execute([$testApplicantId, $testParentEmail, password_hash($initialParentPass, PASSWORD_BCRYPT)]);
$parentAccountId = (int) $pdo->lastInsertId();

// 1. Simulate Parent Reset Request
runControllerAction($phpBinary, $baseDir, 'ParentController', 'resetSend', [
    'csrf_token' => 'valid_test_token',
    'email' => $testParentEmail
]);

$stmtParent = $pdo->prepare("SELECT reset_token, reset_expires FROM parent_accounts WHERE id = ?");
$stmtParent->execute([$parentAccountId]);
$parentRow = $stmtParent->fetch();

assert_true(!empty($parentRow['reset_token']), "Parent reset_token generated successfully");
assert_true(!empty($parentRow['reset_expires']), "Parent reset_expires timestamp set");
$parentResetToken = (string) $parentRow['reset_token'];

// 2. Simulate Submitting Valid Password Reset
runControllerAction($phpBinary, $baseDir, 'ParentController', 'resetSave', [
    'csrf_token' => 'valid_test_token',
    'token' => $parentResetToken,
    'password' => $newParentPass,
    'password_confirmation' => $newParentPass
]);

$stmtParentAfter = $pdo->prepare("SELECT password_hash, reset_token, reset_expires FROM parent_accounts WHERE id = ?");
$stmtParentAfter->execute([$parentAccountId]);
$parentAfter = $stmtParentAfter->fetch();

assert_true(password_verify($newParentPass, (string) $parentAfter['password_hash']), "Parent password hash updated with new password");
assert_true(empty($parentAfter['reset_token']), "Parent reset_token cleared after use");
assert_true(empty($parentAfter['reset_expires']), "Parent reset_expires cleared after use");


// -----------------------------------------------------------------------------
// GROUP 3: Student Portal Password Reset Flow
// -----------------------------------------------------------------------------
echo "\n--- Group 3: Student Portal Password Reset Flow ---\n";

$testStudentUser = 'SCH-STD-' . rand(10000, 99999);
$testStudentEmail = 'studentparent_' . rand(10000, 99999) . '@schooltest.com';
$initialStudentPass = 'OldStudentPass123';
$newStudentPass = 'NewStudentPass789';

$pdo->prepare("INSERT INTO applicants (school_id, application_number, admission_number, first_name, last_name, class_id, parent_email, parent_phone, status, created_at)
               VALUES (1, ?, ?, 'Alice', 'StudentTest', ?, ?, '08099887766', 'Enrolled', NOW())")
    ->execute(['APP-STD-' . rand(1000, 9999), 'ADM-' . rand(1000, 9999), $classId, $testStudentEmail]);
$studentAppId = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO student_accounts (school_id, applicant_id, username, password_hash, must_change_password, created_at)
               VALUES (1, ?, ?, ?, 1, NOW())")
    ->execute([$studentAppId, $testStudentUser, password_hash($initialStudentPass, PASSWORD_BCRYPT)]);
$studentAccId = (int) $pdo->lastInsertId();

// 1. Simulate Student Reset Request by Username
runControllerAction($phpBinary, $baseDir, 'StudentController', 'resetSend', [
    'csrf_token' => 'valid_test_token',
    'identifier' => $testStudentUser
]);

$stmtStud = $pdo->prepare("SELECT reset_token, reset_expires FROM student_accounts WHERE id = ?");
$stmtStud->execute([$studentAccId]);
$studRow = $stmtStud->fetch();

assert_true(!empty($studRow['reset_token']), "Student reset_token generated successfully");
assert_true(!empty($studRow['reset_expires']), "Student reset_expires timestamp set");
$studentResetToken = (string) $studRow['reset_token'];

// 2. Simulate Submitting Valid Student Password Reset
runControllerAction($phpBinary, $baseDir, 'StudentController', 'resetSave', [
    'csrf_token' => 'valid_test_token',
    'token' => $studentResetToken,
    'password' => $newStudentPass,
    'password_confirmation' => $newStudentPass
]);

$stmtStudAfter = $pdo->prepare("SELECT password_hash, reset_token, reset_expires, must_change_password FROM student_accounts WHERE id = ?");
$stmtStudAfter->execute([$studentAccId]);
$studAfter = $stmtStudAfter->fetch();

assert_true(password_verify($newStudentPass, (string) $studAfter['password_hash']), "Student password hash updated with new password");
assert_true(empty($studAfter['reset_token']), "Student reset_token cleared after use");
assert_true(empty($studAfter['reset_expires']), "Student reset_expires cleared after use");
assert_equals(0, (int) $studAfter['must_change_password'], "Student must_change_password reset to 0");


// -----------------------------------------------------------------------------
// GROUP 4: Staff / Teacher Portal Password Reset Flow
// -----------------------------------------------------------------------------
echo "\n--- Group 4: Staff / Teacher Portal Password Reset Flow ---\n";

$testStaffUsername = 'STF-TCHR-' . rand(10000, 99999);
$testStaffEmail = 'teacher_' . rand(10000, 99999) . '@schooltest.com';
$initialStaffPass = 'OldStaffPass123';
$newStaffPass = 'NewStaffPass888';

$pdo->prepare("INSERT INTO staff (school_id, staff_id, first_name, last_name, email, phone, role, status, created_at)
               VALUES (1, ?, 'TeacherDavid', 'Okafor', ?, '08055443322', 'Teacher', 'Active', NOW())")
    ->execute(['STF-ID-' . rand(1000, 9999), $testStaffEmail]);
$staffTableId = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO staff_accounts (school_id, staff_id, username, password_hash, must_change_password, created_at)
               VALUES (1, ?, ?, ?, 1, NOW())")
    ->execute([$staffTableId, $testStaffUsername, password_hash($initialStaffPass, PASSWORD_BCRYPT)]);
$staffAccId = (int) $pdo->lastInsertId();

// 1. Simulate Staff Reset Request by Email
runControllerAction($phpBinary, $baseDir, 'TeacherController', 'resetSend', [
    'csrf_token' => 'valid_test_token',
    'identifier' => $testStaffEmail
]);

$stmtStaff = $pdo->prepare("SELECT reset_token, reset_expires FROM staff_accounts WHERE id = ?");
$stmtStaff->execute([$staffAccId]);
$staffRow = $stmtStaff->fetch();

assert_true(!empty($staffRow['reset_token']), "Staff reset_token generated successfully");
assert_true(!empty($staffRow['reset_expires']), "Staff reset_expires timestamp set");
$staffResetToken = (string) $staffRow['reset_token'];

// 2. Simulate Submitting Valid Staff Password Reset
runControllerAction($phpBinary, $baseDir, 'TeacherController', 'resetSave', [
    'csrf_token' => 'valid_test_token',
    'token' => $staffResetToken,
    'password' => $newStaffPass,
    'password_confirmation' => $newStaffPass
]);

$stmtStaffAfter = $pdo->prepare("SELECT password_hash, reset_token, reset_expires, must_change_password FROM staff_accounts WHERE id = ?");
$stmtStaffAfter->execute([$staffAccId]);
$staffAfter = $stmtStaffAfter->fetch();

assert_true(password_verify($newStaffPass, (string) $staffAfter['password_hash']), "Staff password hash updated with new password");
assert_true(empty($staffAfter['reset_token']), "Staff reset_token cleared after use");
assert_true(empty($staffAfter['reset_expires']), "Staff reset_expires cleared after use");
assert_equals(0, (int) $staffAfter['must_change_password'], "Staff must_change_password reset to 0");


// -----------------------------------------------------------------------------
// GROUP 5: Security & Expiry Guard Verification
// -----------------------------------------------------------------------------
echo "\n--- Group 5: Security & Expiry Guard Verification ---\n";

// Expired token test
$expiredToken = 'EXPIRED-TOKEN-' . rand(1000, 9999);
$pdo->prepare("UPDATE student_accounts SET reset_token = ?, reset_expires = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE id = ?")
    ->execute([$expiredToken, $studentAccId]);

$stmtExpired = $pdo->prepare("SELECT id FROM student_accounts WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
$stmtExpired->execute([$expiredToken]);
assert_true($stmtExpired->fetch() === false, "Expired reset token rejected by database validation");

// Non-existent token test
$fakeToken = 'FAKE-TOKEN-9999999999';
$stmtFake = $pdo->prepare("SELECT id FROM staff_accounts WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
$stmtFake->execute([$fakeToken]);
assert_true($stmtFake->fetch() === false, "Non-existent token rejected by database validation");


// -----------------------------------------------------------------------------
// CLEANUP
// -----------------------------------------------------------------------------
echo "\nCLEANUP: Removing test artifacts...\n";
$pdo->exec("DELETE FROM parent_accounts WHERE id = {$parentAccountId}");
$pdo->exec("DELETE FROM student_accounts WHERE id = {$studentAccId}");
$pdo->exec("DELETE FROM staff_accounts WHERE id = {$staffAccId}");
$pdo->exec("DELETE FROM applicants WHERE id IN ({$testApplicantId}, {$studentAppId})");
$pdo->exec("DELETE FROM staff WHERE id = {$staffTableId}");
echo "  Cleaned up test accounts successfully.\n";

echo "\n================================================================================\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "================================================================================\n";
