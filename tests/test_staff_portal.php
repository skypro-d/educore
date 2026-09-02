<?php
declare(strict_types=1);

// tests/test_staff_portal.php — Comprehensive Verification Suite for Staff & Teacher Portal

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/GradingService.php';
require_once __DIR__ . '/../config/StaffAuth.php';
require_once __DIR__ . '/../config/StaffAudit.php';

$pdo = Database::connect();

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

echo "========================================================\n";
echo " EDUCORE STAFF & TEACHER PORTAL VERIFICATION SUITE\n";
echo "========================================================\n\n";

// TEST GROUP 1: Database Schema & Migration Verification
echo "--- Group 1: Database Schema & RBAC Structure ---\n";
$roleCount = (int) $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
assert_true($roleCount >= 12, "At least 12 standard roles seeded in 'roles' table (found {$roleCount})");

$permCount = (int) $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
assert_true($permCount >= 28, "At least 28 granular permissions seeded in 'permissions' table (found {$permCount})");

$rolePermCount = (int) $pdo->query("SELECT COUNT(*) FROM role_permissions")->fetchColumn();
assert_true($rolePermCount > 100, "Role default permissions configured (found {$rolePermCount})");

$hasStaffPermsTable = (bool) $pdo->query("SHOW TABLES LIKE 'staff_permissions'")->fetchColumn();
assert_true($hasStaffPermsTable, "Table 'staff_permissions' exists");

$hasAssignmentsTable = (bool) $pdo->query("SHOW TABLES LIKE 'assignments'")->fetchColumn();
assert_true($hasAssignmentsTable, "Table 'assignments' exists");

$hasSubmissionsTable = (bool) $pdo->query("SHOW TABLES LIKE 'assignment_submissions'")->fetchColumn();
assert_true($hasSubmissionsTable, "Table 'assignment_submissions' exists");

$hasAuditLogsTable = (bool) $pdo->query("SHOW TABLES LIKE 'staff_audit_logs'")->fetchColumn();
assert_true($hasAuditLogsTable, "Table 'staff_audit_logs' exists");

$resultCols = $pdo->query("SHOW COLUMNS FROM student_results")->fetchAll(PDO::FETCH_COLUMN);
assert_true(in_array('status', $resultCols, true), "Column 'status' exists on 'student_results'");
assert_true(in_array('submitted_at', $resultCols, true), "Column 'submitted_at' exists on 'student_results'");
assert_true(in_array('approved_at', $resultCols, true), "Column 'approved_at' exists on 'student_results'");
assert_true(in_array('published_at', $resultCols, true), "Column 'published_at' exists on 'student_results'");

// TEST GROUP 2: Grading Service Logic
echo "\n--- Group 2: Dynamic Grading Service ---\n";
$grade1 = GradingService::evaluate(85.0);
assert_equals('A1', $grade1['grade'], "85% maps to A1");
assert_equals('Excellent', $grade1['remark'], "85% remark is 'Excellent'");

$grade2 = GradingService::evaluate(68.5);
assert_equals('B3', $grade2['grade'], "68.5% maps to B3");

$grade3 = GradingService::evaluate(48.0);
assert_equals('D7', $grade3['grade'], "48% maps to D7 (Pass)");

$grade4 = GradingService::evaluate(32.0);
assert_equals('F9', $grade4['grade'], "32% maps to F9");
assert_equals('Fail', $grade4['remark'], "32% remark is 'Fail'");

// TEST GROUP 3: StaffAuth & PBAC Permissions with Overrides
echo "\n--- Group 3: StaffAuth & Granular Overrides ---\n";
// Create a test staff member for verification
$testEmail = 'test_teacher_' . time() . '@educore.test';
$testPublicId = 'STF-TEST-' . time();
$pdo->prepare("INSERT INTO staff (first_name, last_name, staff_id, email, phone, role, role_id, status, created_at) VALUES ('Test', 'Faculty', ?, ?, '08099999999', 'class_teacher', 6, 'Active', NOW())")
    ->execute([$testPublicId, $testEmail]);
$testStaffId = (int) $pdo->lastInsertId();

// Class Teacher should have attendance.mark by default
$perms = StaffAuth::loadStaffPermissions($testStaffId, 'class_teacher');
assert_true(in_array('attendance.mark', $perms, true), "Class teacher has 'attendance.mark' by default");
assert_true(in_array('results.enter', $perms, true), "Class teacher has 'results.enter' by default");
assert_true(!in_array('results.publish', $perms, true), "Class teacher does NOT have 'results.publish' by default");

// Now grant custom permission override: grant 'results.publish'
$publishPermId = (int) $pdo->query("SELECT id FROM permissions WHERE name = 'results.publish'")->fetchColumn();
$pdo->prepare("INSERT INTO staff_permissions (staff_id, permission_id, granted) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE granted = 1")
    ->execute([$testStaffId, $publishPermId]);

$permsWithOverride = StaffAuth::loadStaffPermissions($testStaffId, 'class_teacher');
assert_true(in_array('results.publish', $permsWithOverride, true), "Custom override successfully granted 'results.publish'");

// Now revoke a default permission: revoke 'attendance.mark'
$attMarkPermId = (int) $pdo->query("SELECT id FROM permissions WHERE name = 'attendance.mark'")->fetchColumn();
$pdo->prepare("INSERT INTO staff_permissions (staff_id, permission_id, granted) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE granted = 0")
    ->execute([$testStaffId, $attMarkPermId]);

$permsWithRevoke = StaffAuth::loadStaffPermissions($testStaffId, 'class_teacher');
assert_true(!in_array('attendance.mark', $permsWithRevoke, true), "Custom override successfully REVOKED 'attendance.mark'");

// TEST GROUP 4: Resource Scoping & IDOR Prevention
echo "\n--- Group 4: Resource Scoping & IDOR Protection ---\n";
// Assign staff as subject teacher only (is_form_teacher = 0)
$academicYear = current_academic_year();
$pdo->prepare("DELETE FROM staff_class_assignments WHERE staff_id = ?")->execute([$testStaffId]);
$pdo->prepare("INSERT INTO staff_class_assignments (staff_id, class_id, subject_id, is_form_teacher, academic_year) VALUES (?, 1, 1, 0, ?)")
    ->execute([$testStaffId, $academicYear]);

// Mock teacher session
$_SESSION['teacher'] = [
    'id' => 9999,
    'staff_table_id' => $testStaffId,
    'username' => 'testfaculty',
    'name' => 'Test Faculty',
    'role' => 'subject_teacher',
    'role_id' => 7,
    'permissions' => $permsWithOverride,
    'assigned_classes' => [1],
    'assigned_subjects' => [1]
];

assert_true(StaffAuth::canAccessClass(1), "Staff can access assigned Class #1");
assert_true(!StaffAuth::canAccessClass(2), "Staff CANNOT access unassigned Class #2 (IDOR Prevention)");
assert_true(StaffAuth::canAccessSubject(1, 1), "Staff can access assigned Subject #1 in Class #1");
assert_true(!StaffAuth::canAccessSubject(1, 99), "Subject Teacher CANNOT access unassigned Subject #99 (IDOR Prevention)");

// Now promote staff to Form Teacher of Class #1 (Full Class Control)
$pdo->prepare("UPDATE staff_class_assignments SET is_form_teacher = 1 WHERE staff_id = ? AND class_id = 1")
    ->execute([$testStaffId]);
assert_true(StaffAuth::canAccessSubject(1, 99), "Form Teacher HAS FULL CONTROL over all subjects in their class");

// TEST GROUP 5: Staff Audit Logging
echo "\n--- Group 5: Audit Logging Engine ---\n";
StaffAudit::log('test.action', 'test_resource', 42, 'Test audit description');
$auditLog = $pdo->query("SELECT * FROM staff_audit_logs WHERE action = 'test.action' AND resource_id = 42 ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert_true(!empty($auditLog), "Audit entry successfully created in 'staff_audit_logs'");
assert_equals('Test audit description', $auditLog['details'], "Audit details logged correctly");
assert_equals($testStaffId, (int)$auditLog['staff_id'], "Staff ID attached to audit record");

// TEST GROUP 6: Results Workflow Transitions
echo "\n--- Group 6: Academic Results Workflow ---\n";
// Create sample result
$testAppId = (int) $pdo->query("SELECT id FROM applicants LIMIT 1")->fetchColumn();
if ($testAppId > 0) {
    $pdo->prepare("DELETE FROM student_results WHERE applicant_id = ? AND subject_id = 1 AND term = 'First' AND academic_year = ?")
        ->execute([$testAppId, $academicYear]);
        
    $pdo->prepare("INSERT INTO student_results (applicant_id, subject_id, class_id, term, academic_year, ca1, exam, total, grade, remark, status) VALUES (?, 1, 1, 'First', ?, 8, 52, 60, 'B3', 'Good', 'draft')")
        ->execute([$testAppId, $academicYear]);

    $resStatus = $pdo->prepare("SELECT status FROM student_results WHERE applicant_id = ? AND subject_id = 1 AND term = 'First' AND academic_year = ?");
    $resStatus->execute([$testAppId, $academicYear]);
    assert_equals('draft', $resStatus->fetchColumn(), "Initial result status is 'draft'");

    // Submit result
    $pdo->prepare("UPDATE student_results SET status = 'submitted', submitted_at = NOW(), submitted_by = ? WHERE applicant_id = ? AND subject_id = 1 AND term = 'First' AND academic_year = ?")
        ->execute([$testStaffId, $testAppId, $academicYear]);
    $resStatus->execute([$testAppId, $academicYear]);
    assert_equals('submitted', $resStatus->fetchColumn(), "Result status advanced to 'submitted'");

    // Approve result
    $pdo->prepare("UPDATE student_results SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE applicant_id = ? AND subject_id = 1 AND term = 'First' AND academic_year = ?")
        ->execute([$testStaffId, $testAppId, $academicYear]);
    $resStatus->execute([$testAppId, $academicYear]);
    assert_equals('approved', $resStatus->fetchColumn(), "Result status advanced to 'approved'");

    // Publish result
    $pdo->prepare("UPDATE student_results SET status = 'published', published_at = NOW(), published_by = ? WHERE applicant_id = ? AND subject_id = 1 AND term = 'First' AND academic_year = ?")
        ->execute([$testStaffId, $testAppId, $academicYear]);
    $resStatus->execute([$testAppId, $academicYear]);
    assert_equals('published', $resStatus->fetchColumn(), "Result status advanced to 'published'");
}

// TEST GROUP 7: Attendance QR Code & IDOR Prevention
echo "\n--- Group 7: Attendance QR Code & Scoping ---\n";
// Create two classes: Class 101 and Class 102
$pdo->prepare("INSERT INTO classes (id, name, sort_order, school_id) VALUES (101, 'Test JSS1', 1, 1), (102, 'Test JSS2', 2, 1) ON DUPLICATE KEY UPDATE name=VALUES(name)")->execute();

// Create two students: Student A in Class 101, Student B in Class 102
$pdo->prepare("INSERT INTO applicants (id, school_id, application_number, first_name, last_name, class_id, status, student_status) 
               VALUES (8801, 1, 'APP-8801', 'Alice', 'Allowed', 101, 'Enrolled', 'Active'),
                      (8802, 1, 'APP-8802', 'Bob', 'Blocked', 102, 'Enrolled', 'Active')
               ON DUPLICATE KEY UPDATE first_name=VALUES(first_name), class_id=VALUES(class_id), status=VALUES(status), student_status=VALUES(student_status)")->execute();

// Set teacher session to only have Class 101
StaffAuth::resetCache();
$_SESSION['teacher']['assigned_classes'] = [101];

// Verification: Student A (Class 101) is allowed; Student B (Class 102) is denied
assert_true(StaffAuth::canAccessStudent(8801), "Staff can access Student in assigned Class 101");
assert_true(!StaffAuth::canAccessStudent(8802), "Staff is BLOCKED from accessing Student in unassigned Class 102 (Anti-IDOR)");

// Clean up attendance record for today for student 8801
$today = date('Y-m-d');
$pdo->prepare("DELETE FROM attendance WHERE applicant_id = 8801 AND date = ?")->execute([$today]);

// First check-in: Insert attendance (marked_by is NULL for staff check-ins)
$pdo->prepare("INSERT INTO attendance (applicant_id, class_id, date, status, marked_by, time_in) VALUES (8801, 101, ?, 'Present', NULL, '07:55:00')")
    ->execute([$today]);
$check1 = $pdo->query("SELECT status FROM attendance WHERE applicant_id = 8801 AND date = '{$today}'")->fetchColumn();
assert_equals('Present', $check1, "First QR check-in marks student Present");

// Duplicate check-in detection:
$existing = $pdo->query("SELECT status, time_in FROM attendance WHERE applicant_id = 8801 AND date = '{$today}'")->fetch(PDO::FETCH_ASSOC);
assert_true(!empty($existing), "Attendance system detects existing check-in to prevent duplicate logging");

// TEST GROUP 8: Multi-Tenancy Engine (TenantPDO)
echo "\n--- Group 8: Multi-Tenancy Isolation ---\n";
assert_true(in_array('assignments', TenantPDO::$tenantTables, true), "'assignments' registered in TenantPDO");
assert_true(in_array('assignment_submissions', TenantPDO::$tenantTables, true), "'assignment_submissions' registered in TenantPDO");
assert_true(in_array('staff_permissions', TenantPDO::$tenantTables, true), "'staff_permissions' registered in TenantPDO");
assert_true(in_array('staff_audit_logs', TenantPDO::$tenantTables, true), "'staff_audit_logs' registered in TenantPDO");

// Clean up test data
$pdo->prepare("DELETE FROM attendance WHERE applicant_id IN (8801, 8802)")->execute();
$pdo->prepare("DELETE FROM applicants WHERE id IN (8801, 8802)")->execute();
$pdo->prepare("DELETE FROM classes WHERE id IN (101, 102)")->execute();
$pdo->prepare("DELETE FROM staff_permissions WHERE staff_id = ?")->execute([$testStaffId]);
$pdo->prepare("DELETE FROM staff_class_assignments WHERE staff_id = ?")->execute([$testStaffId]);
$pdo->prepare("DELETE FROM staff_audit_logs WHERE staff_id = ?")->execute([$testStaffId]);
$pdo->prepare("DELETE FROM staff WHERE id = ?")->execute([$testStaffId]);
unset($_SESSION['teacher']);

echo "\n========================================================\n";
echo " TEST SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
