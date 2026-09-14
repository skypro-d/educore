<?php
declare(strict_types=1);

/**
 * tests/test_direct_student_enrollment.php
 * Automated verification suite for Direct Student Enrollment & Old Students Onboarding.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/StudentEnrollmentService.php';
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
echo "TEST SUITE: DIRECT STUDENT ENROLLMENT & OLD STUDENTS ONBOARDING\n";
echo "================================================================================\n\n";

// Ensure we have a valid test class
$classes = (new ClassModel($pdo))->all();
if (empty($classes)) {
    $pdo->exec("INSERT INTO classes (school_id, name, sort_order) VALUES (1, 'Test Grade 1', 1)");
    $classes = (new ClassModel($pdo))->all();
}
$targetClass = $classes[0];
$classId = (int) $targetClass['id'];
$className = (string) $targetClass['name'];

$service = new StudentEnrollmentService($pdo);

$testApplicantIds = [];

// =============================================================================
// TEST 1: Direct Single Enrollment of Old Student with Custom Admission Number
// =============================================================================
echo "TEST 1: Direct Enrollment of Old Student (Custom Admission No & Past Date)\n";

$customAdmNo = 'SCH-OLD-' . rand(1000, 9999);
$customDate = '2022-09-15';
$studentData1 = [
    'first_name' => 'Emmanuel',
    'middle_name' => 'Kolawole',
    'last_name' => 'Ogunleye',
    'gender' => 'Male',
    'date_of_birth' => '2014-06-20',
    'class_id' => $classId,
    'admission_type' => 'Old Student',
    'admission_number' => $customAdmNo,
    'home_address' => '42 Olumo Rock Way, Abeokuta',
    'parent_name' => 'Elder Samuel Ogunleye',
    'parent_phone' => '08031112233',
    'parent_email' => 'samuel.ogunleye.' . rand(100, 999) . '@testmail.com',
    'enrolled_at' => $customDate,
];

try {
    $res1 = $service->enrolDirect($studentData1, null, false, false);
    $testApplicantIds[] = $res1['applicant_id'];

    assert_true($res1['applicant_id'] > 0, "Student inserted with valid ID ({$res1['applicant_id']})");
    assert_equals($customAdmNo, $res1['admission_number'], "Preserved existing custom admission number");
    assert_equals('Emmanuel Kolawole Ogunleye', $res1['student_name'], "Combined full student name matches");

    // Verify applicants table record
    $stmtCheckApp = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
    $stmtCheckApp->execute([$res1['applicant_id']]);
    $appRow = $stmtCheckApp->fetch();

    assert_equals('Enrolled', $appRow['status'], "Applicant status is Enrolled");
    assert_equals('Enrolled', $appRow['admission_status'], "Admission status is Enrolled");
    assert_equals('Completed', $appRow['enrollment_status'], "Enrollment status is Completed");
    assert_true(str_starts_with($appRow['enrolled_at'], $customDate), "Historical enrollment date preserved ({$appRow['enrolled_at']})");
    assert_true(!empty($appRow['qr_data']), "Attendance QR token generated");

    // Verify student_accounts record
    $stmtStud = $pdo->prepare("SELECT * FROM student_accounts WHERE applicant_id = ?");
    $stmtStud->execute([$res1['applicant_id']]);
    $studAcc = $stmtStud->fetch();
    assert_true((bool) $studAcc, "Student account created in student_accounts");
    assert_equals($res1['student_username'], $studAcc['username'], "Student account username matches");
    assert_true(password_verify($res1['student_password'], $studAcc['password_hash']), "Student password hashes correctly");
    assert_equals(1, (int) $studAcc['must_change_password'], "must_change_password flag set to 1");

    // Verify parent_accounts record
    $stmtParent = $pdo->prepare("SELECT * FROM parent_accounts WHERE applicant_id = ?");
    $stmtParent->execute([$res1['applicant_id']]);
    $parentAcc = $stmtParent->fetch();
    assert_true((bool) $parentAcc, "Parent account created in parent_accounts");
    assert_equals($studentData1['parent_email'], $parentAcc['email'], "Parent account email matches");
    assert_true(password_verify($res1['parent_password'], $parentAcc['password_hash']), "Parent password hashes correctly");

    // Verify admission_letters entry
    $stmtLetter = $pdo->prepare("SELECT admission_number FROM admission_letters WHERE applicant_id = ?");
    $stmtLetter->execute([$res1['applicant_id']]);
    $letterAdmNo = $stmtLetter->fetchColumn();
    assert_equals($customAdmNo, $letterAdmNo, "admission_letters entry synced with custom admission number");

} catch (Throwable $e) {
    assert_true(false, "Test 1 failed with exception: " . $e->getMessage());
}

// =============================================================================
// TEST 2: Direct Single Enrollment with Auto-Generated Admission Number
// =============================================================================
echo "\nTEST 2: Direct Enrollment with Auto-Generated Admission Number\n";

$studentData2 = [
    'first_name' => 'Amina',
    'last_name' => 'Yusuf',
    'gender' => 'Female',
    'date_of_birth' => '2016-11-03',
    'class_id' => $classId,
    'admission_type' => 'Direct Admission',
    'admission_number' => '', // Leave empty to auto-generate
    'home_address' => '15 Bompai Road, Kano',
    'parent_name' => 'Alhaji Yusuf Garba',
    'parent_phone' => '08029998877',
    'parent_email' => 'yusuf.garba.' . rand(100, 999) . '@testmail.com',
];

try {
    $res2 = $service->enrolDirect($studentData2, null, false, false);
    $testApplicantIds[] = $res2['applicant_id'];

    assert_true(!empty($res2['admission_number']), "Auto-generated admission number: {$res2['admission_number']}");
    assert_true(!empty($res2['student_username']), "Auto-generated student username: {$res2['student_username']}");
    assert_equals('Enrolled', $pdo->query("SELECT status FROM applicants WHERE id = {$res2['applicant_id']}")->fetchColumn(), "Status is Enrolled");

} catch (Throwable $e) {
    assert_true(false, "Test 2 failed with exception: " . $e->getMessage());
}

// =============================================================================
// TEST 3: Duplicate Admission Number & Validation Handling
// =============================================================================
echo "\nTEST 3: Duplicate Admission Number & Validation Checks\n";

$duplicateAdmAttempt = [
    'first_name' => 'Clone',
    'last_name' => 'Student',
    'gender' => 'Male',
    'date_of_birth' => '2015-01-01',
    'class_id' => $classId,
    'admission_number' => $customAdmNo, // Same admission number from Test 1!
    'home_address' => 'Some Address',
    'parent_name' => 'Parent Name',
    'parent_phone' => '08011111111',
    'parent_email' => 'duplicate.check@example.com',
];

$duplicateCaught = false;
try {
    $service->enrolDirect($duplicateAdmAttempt, null, false, false);
} catch (InvalidArgumentException $e) {
    if (str_contains($e->getMessage(), 'already assigned')) {
        $duplicateCaught = true;
    }
}
assert_true($duplicateCaught, "Duplicate admission number rejected with InvalidArgumentException");

$missingClassCaught = false;
try {
    $invalidData = $duplicateAdmAttempt;
    $invalidData['class_id'] = 999999;
    $service->enrolDirect($invalidData, null, false, false);
} catch (InvalidArgumentException $e) {
    $missingClassCaught = true;
}
assert_true($missingClassCaught, "Invalid class_id rejected");

// =============================================================================
// TEST 4: Sample CSV Template Generation
// =============================================================================
echo "\nTEST 4: Sample CSV Generation\n";

$sampleCsv = $service->generateSampleCsv();
assert_true(str_contains($sampleCsv, 'first_name,middle_name,last_name'), "CSV header row valid");
assert_true(str_contains($sampleCsv, 'Adeyemi'), "CSV contains sample row data");

// =============================================================================
// TEST 5: Bulk CSV Import for Old Students Migration
// =============================================================================
echo "\nTEST 5: Bulk CSV Import Migration\n";

$tmpCsvFile = tempnam(sys_get_temp_dir(), 'csv_test_');
$csvContent = "first_name,middle_name,last_name,gender,date_of_birth,class_name,admission_number,parent_name,parent_phone,parent_email,home_address,enrollment_date\n"
    . "Tunde,James,Balogun,Male,2015-03-10,{$className},SCH-BATCH-001,Mr. Ade Balogun,08033334444,balogun.p1@test.com,Lekki Phase 1 Lagos,2023-09-01\n"
    . "Ngozi,Grace,Okonkwo,Female,2016-07-22,{$className},SCH-BATCH-002,Mrs. Ifeoma Okonkwo,08055556666,okonkwo.p2@test.com,Independence Layout Enugu,2023-09-01\n"
    . "Ibrahim,Musa,Danjuma,Male,2015-12-05,{$className},,Alhaji Danjuma,08077778888,danjuma.p3@test.com,GRA Kaduna,2024-01-10\n";

file_put_contents($tmpCsvFile, $csvContent);

try {
    $importRes = $service->importCsv($tmpCsvFile, false, $classId);

    assert_equals(3, $importRes['enrolled_count'], "Batch enrolled 3 students from CSV");
    assert_equals(0, $importRes['failed_count'], "Zero failures during batch import");

    // Check that SCH-BATCH-001 exists and is enrolled
    $stmtBatch1 = $pdo->prepare("SELECT id, status, admission_number FROM applicants WHERE admission_number = 'SCH-BATCH-001'");
    $stmtBatch1->execute();
    $batchStudent = $stmtBatch1->fetch();
    assert_true((bool) $batchStudent, "SCH-BATCH-001 student found in database");
    assert_equals('Enrolled', $batchStudent['status'], "SCH-BATCH-001 status is Enrolled");
    $testApplicantIds[] = (int) $batchStudent['id'];

    // Collect other imported IDs for cleanup
    $stmtOthers = $pdo->query("SELECT id FROM applicants WHERE admission_number IN ('SCH-BATCH-002') OR (first_name = 'Ibrahim' AND last_name = 'Danjuma')");
    while ($id = $stmtOthers->fetchColumn()) {
        $testApplicantIds[] = (int) $id;
    }

} catch (Throwable $e) {
    assert_true(false, "Batch CSV import failed: " . $e->getMessage());
} finally {
    if (file_exists($tmpCsvFile)) {
        @unlink($tmpCsvFile);
    }
}

// =============================================================================
// CLEANUP: Remove test records
// =============================================================================
echo "\nCLEANUP: Removing test artifacts...\n";
if (!empty($testApplicantIds)) {
    $inClause = implode(',', array_map('intval', array_unique($testApplicantIds)));
    $pdo->exec("DELETE FROM student_accounts WHERE applicant_id IN ({$inClause})");
    $pdo->exec("DELETE FROM parent_accounts WHERE applicant_id IN ({$inClause})");
    $pdo->exec("DELETE FROM admission_letters WHERE applicant_id IN ({$inClause})");
    $pdo->exec("DELETE FROM applicants WHERE id IN ({$inClause})");
    echo "  Cleaned up " . count($testApplicantIds) . " test student records.\n";
}

echo "\n================================================================================\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "================================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
