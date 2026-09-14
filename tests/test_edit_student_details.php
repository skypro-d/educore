<?php
declare(strict_types=1);

/**
 * tests/test_edit_student_details.php
 * Automated verification suite for Editing Enrolled Student Details.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/StudentEnrollmentService.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../models/ClassModel.php';

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

echo "================================================================================\n";
echo "TEST SUITE: EDIT ENROLLED STUDENT DETAILS\n";
echo "================================================================================\n\n";

$classes = (new ClassModel($pdo))->all();
$class1 = (int) $classes[0]['id'];
$class2 = isset($classes[1]) ? (int) $classes[1]['id'] : $class1;

// 1. Enroll a test student first
$service = new StudentEnrollmentService($pdo);
$initAdmNo = 'SCH-EDIT-' . rand(1000, 9999);
$initData = [
    'first_name' => 'InitialFirst',
    'last_name' => 'InitialLast',
    'gender' => 'Male',
    'date_of_birth' => '2015-02-10',
    'class_id' => $class1,
    'admission_type' => 'Old Student',
    'admission_number' => $initAdmNo,
    'home_address' => 'Old Address 123',
    'parent_name' => 'Old Parent',
    'parent_phone' => '08011223344',
    'parent_email' => 'old.parent.' . rand(100, 999) . '@testmail.com',
];

$res = $service->enrolDirect($initData, null, false, false);
$studentId = $res['applicant_id'];

assert_true($studentId > 0, "Test student enrolled with ID {$studentId}");

$phpBinary = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';
$baseDir = str_replace('\\', '/', dirname(__DIR__));

function executeUpdateStudent(string $phpBinary, string $baseDir, int $studentId, array $postData): void {
    $code = '<?php '
        . '$baseDir = ' . var_export($baseDir, true) . '; '
        . 'require_once $baseDir . "/config/config.php"; '
        . 'require_once $baseDir . "/config/database.php"; '
        . 'require_once $baseDir . "/config/helpers.php"; '
        . 'require_once $baseDir . "/controllers/AdminController.php"; '
        . 'SchoolContext::set(1); '
        . 'session_start(); '
        . '$_SESSION["admin"] = ["id" => 1, "name" => "Admin", "role" => "system_admin", "school_id" => 1]; '
        . '$_SESSION["csrf_token"] = "valid_test_token"; '
        . '$_SERVER["REQUEST_METHOD"] = "POST"; '
        . '$_POST = ' . var_export($postData, true) . '; '
        . '$c = new AdminController(); '
        . '$c->updateStudent(' . $studentId . '); ';

    $tmpFile = tempnam(sys_get_temp_dir(), 'test_edit_') . '.php';
    file_put_contents($tmpFile, $code);
    exec("\"{$phpBinary}\" \"{$tmpFile}\" 2>&1", $out, $ret);
    @unlink($tmpFile);
}

// =============================================================================
// TEST 1: Update Student Personal, Academic, and Parent Details
// =============================================================================
echo "\nTEST 1: Update Enrolled Student Details & Verify Multi-Table Sync\n";

$newAdmNo = 'SCH-EDIT-UPDATED-' . rand(100, 999);
$newUsername = 'STD-UPDATED-' . rand(100, 999);
$newParentPhone = '08099887766';
$newParentEmail = 'updated.parent.' . rand(100, 999) . '@testmail.com';

$updateData = [
    'csrf_token' => 'valid_test_token',
    'first_name' => 'UpdatedFirst',
    'middle_name' => 'MiddleName',
    'last_name' => 'UpdatedLast',
    'gender' => 'Female',
    'date_of_birth' => '2015-08-20',
    'nationality' => 'Ghanaian',
    'state_of_origin' => 'Accra',
    'local_government' => 'Central',
    'home_address' => '45 New Boulevard, Victoria Island',
    'class_id' => $class2,
    'admission_type' => 'Transfer',
    'admission_number' => $newAdmNo,
    'student_username' => $newUsername,
    'student_status' => 'Active',
    'parent_name' => 'Dr. Updated Parent',
    'parent_phone' => $newParentPhone,
    'parent_email' => $newParentEmail,
    'father_name' => 'Mr. Father',
    'mother_name' => 'Mrs. Mother',
    'parent_occupation' => 'Architect',
    'blood_group' => 'O+',
    'allergies' => 'None',
    'special_needs' => 'Extra reading time',
    'emergency_name' => 'Auntie Mary',
    'emergency_phone' => '08055443322',
    'enrolled_at' => '2023-01-15',
];

executeUpdateStudent($phpBinary, $baseDir, $studentId, $updateData);

// 1. Verify applicants table
$stmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
$stmt->execute([$studentId]);
$updatedApp = $stmt->fetch();

assert_equals('UpdatedFirst', $updatedApp['first_name'], "First name updated in applicants");
assert_equals('UpdatedLast', $updatedApp['last_name'], "Last name updated in applicants");
assert_equals('Female', $updatedApp['gender'], "Gender updated to Female");
assert_equals('Ghanaian', $updatedApp['nationality'], "Nationality updated");
assert_equals($class2, (int) $updatedApp['class_id'], "Target class updated");
assert_equals($newAdmNo, $updatedApp['admission_number'], "Admission number updated in applicants");
assert_equals($newUsername, $updatedApp['student_username'], "Student username updated in applicants");
assert_equals($newParentEmail, $updatedApp['parent_email'], "Parent email updated in applicants");
assert_equals($newParentPhone, $updatedApp['parent_phone'], "Parent phone updated in applicants");
assert_equals('Transfer', $updatedApp['admission_type'], "Admission type updated");
assert_equals('O+', $updatedApp['blood_group'], "Blood group updated");

// 2. Verify student_accounts table synced
$stmtStud = $pdo->prepare("SELECT username FROM student_accounts WHERE applicant_id = ?");
$stmtStud->execute([$studentId]);
$studUser = $stmtStud->fetchColumn();
assert_equals($newUsername, $studUser, "student_accounts.username correctly synced");

// 3. Verify parent_accounts table synced
$stmtParent = $pdo->prepare("SELECT phone, email FROM parent_accounts WHERE applicant_id = ?");
$stmtParent->execute([$studentId]);
$parentAcc = $stmtParent->fetch();
assert_equals($newParentPhone, $parentAcc['phone'], "parent_accounts.phone correctly synced");
assert_equals($newParentEmail, $parentAcc['email'], "parent_accounts.email correctly synced");

// 4. Verify admission_letters table synced
$stmtLetter = $pdo->prepare("SELECT admission_number FROM admission_letters WHERE applicant_id = ?");
$stmtLetter->execute([$studentId]);
$letterNo = $stmtLetter->fetchColumn();
assert_equals($newAdmNo, $letterNo, "admission_letters.admission_number correctly synced");

// =============================================================================
// TEST 2: Render Student Edit Page
// =============================================================================
echo "\nTEST 2: Render Student Edit Page HTML\n";

$_SESSION['admin'] = [
    'id' => 1,
    'name' => 'School Administrator',
    'email' => 'admin@school.test',
    'role' => 'system_admin',
    'school_id' => 1
];

ob_start();
$c = new AdminController();
$c->editStudent($studentId);
$html = ob_get_clean();

assert_true(str_contains($html, 'Edit Student Details'), "Edit page contains title");
assert_true(str_contains($html, 'UpdatedFirst'), "Edit page contains pre-filled student first name");
assert_true(str_contains($html, $newAdmNo), "Edit page contains pre-filled admission number");
assert_true(str_contains($html, $newParentEmail), "Edit page contains pre-filled parent email");

// =============================================================================
// CLEANUP: Remove test records
// =============================================================================
echo "\nCLEANUP: Removing test student record...\n";
$pdo->exec("DELETE FROM student_accounts WHERE applicant_id = {$studentId}");
$pdo->exec("DELETE FROM parent_accounts WHERE applicant_id = {$studentId}");
$pdo->exec("DELETE FROM admission_letters WHERE applicant_id = {$studentId}");
$pdo->exec("DELETE FROM applicants WHERE id = {$studentId}");
echo "  Cleaned up test student #{$studentId}.\n";

echo "\n================================================================================\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "================================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
