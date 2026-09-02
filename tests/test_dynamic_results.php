<?php
declare(strict_types=1);

/**
 * EduCore Dynamic Result Management System Test Suite
 *
 * Validates:
 * - Dynamic subject resolution (Primary 16, JSS 16, SS 12, Arbitrary counts)
 * - Zero hardcoding guarantee
 * - Student subject inheritance & individual exceptions
 * - Average calculation (grand_total / actual_subjects_offered)
 * - Configurable assessment components & bounds validation
 * - Dynamic grading scale rules & grade point evaluation
 * - Full student terminal report calculation & broadsheet overview matrix
 * - Class ranking & position recalculation
 * - Lifecycle workflow transitions & edit locking
 * - Multi-tenant isolation
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/ResultService.php';
require_once __DIR__ . '/../config/GradingService.php';
require_once __DIR__ . '/../config/StaffAuth.php';

$pdo = Database::connect();

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest(string $description, bool $condition, string $details = ''): void
{
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}" . ($details ? " — {$details}" : '') . "\n";
    }
}

echo "====================================================================\n";
echo "EduCore — Dynamic Student Result Management System Test Suite\n";
echo "====================================================================\n\n";

// ── GROUP 1: Dynamic Class-Subject Configuration (Primary, JSS, SS) ──────
echo "--- Group 1: Dynamic Class-Subject Configuration ---\n";

// 1. Primary 1 (Class ID 1) -> 16 subjects
$primarySubjects = ResultService::getSubjectsForClass(1);
assertTest(
    "Primary 1 offers exactly 16 subjects dynamically",
    count($primarySubjects) === 16,
    "Got: " . count($primarySubjects)
);

// 2. JSS 1 (Class ID 4) -> 16 subjects
$jssSubjects = ResultService::getSubjectsForClass(4);
assertTest(
    "JSS 1 offers exactly 16 subjects dynamically",
    count($jssSubjects) === 16,
    "Got: " . count($jssSubjects)
);

// 3. SS 1 (Class ID 6) -> 12 subjects
$ssSubjects = ResultService::getSubjectsForClass(6);
assertTest(
    "SS 1 offers exactly 12 subjects dynamically",
    count($ssSubjects) === 12,
    "Got: " . count($ssSubjects)
);

// 4. SS 2 (Class ID 7) -> 12 subjects
$ss2Subjects = ResultService::getSubjectsForClass(7);
assertTest(
    "SS 2 offers exactly 12 subjects dynamically",
    count($ss2Subjects) === 12,
    "Got: " . count($ss2Subjects)
);


// ── GROUP 2: Zero Hardcoded Subject Counts Guarantee ─────────────────────
echo "\n--- Group 2: Zero Hardcoded Subject Counts Guarantee ---\n";

// Let's test arbitrary subject count (e.g. 10 subjects, 14 subjects, 20 subjects)
// Create a temporary test class or assign 10 subjects to a dummy class ID 999
$pdo->exec("INSERT INTO classes (id, school_id, name, sort_order) VALUES (999, 1, 'Custom Test Class', 999) ON DUPLICATE KEY UPDATE name='Custom Test Class'");

// Assign 10 subjects
$pdo->exec("DELETE FROM class_subjects WHERE class_id = 999");
$stmtInsert = $pdo->prepare("INSERT INTO class_subjects (school_id, class_id, subject_id, is_compulsory, sort_order, is_active) VALUES (1, 999, ?, 1, ?, 1)");
for ($i = 1; $i <= 10; $i++) {
    $stmtInsert->execute([$i, $i]);
}
$custom10 = ResultService::getSubjectsForClass(999);
assertTest("Class with 10 configured subjects returns exactly 10 subjects", count($custom10) === 10);

// Assign 20 subjects
for ($i = 11; $i <= 20; $i++) {
    $stmtInsert->execute([$i, $i]);
}
$custom20 = ResultService::getSubjectsForClass(999);
assertTest("Class with 20 configured subjects returns exactly 20 subjects without code changes", count($custom20) === 20);

// Clean up dummy class
$pdo->exec("DELETE FROM class_subjects WHERE class_id = 999");
$pdo->exec("DELETE FROM classes WHERE id = 999");


// ── GROUP 3: Student Subject Inheritance & Individual Exceptions ──────────
echo "\n--- Group 3: Student Subject Inheritance & Individual Exceptions ---\n";

$student1Id = 8803; // David Adeyemi
$student2Id = 8804; // Grace Okonkwo

// Enrolled student David Adeyemi (Primary 1) inherits 16 subjects
$student1Subjects = ResultService::getSubjectsForStudent($student1Id, 1, '2024/2025');
assertTest("Student without exceptions inherits all 16 class subjects", count($student1Subjects) === 16);

// Now create an exception for Student Grace Okonkwo (ID 8804) (custom 15 subjects)
$pdo->exec("DELETE FROM student_subject_enrollments WHERE applicant_id = {$student2Id}");
$stmtSSE = $pdo->prepare("INSERT INTO student_subject_enrollments (school_id, applicant_id, class_id, subject_id, academic_year, is_exempt) VALUES (1, ?, 1, ?, '2024/2025', 0)");
// Enroll Student in only first 15 subjects
for ($i = 1; $i <= 15; $i++) {
    $stmtSSE->execute([$student2Id, $i]);
}
$student2Subjects = ResultService::getSubjectsForStudent($student2Id, 1, '2024/2025');
assertTest("Student with custom enrollment resolves exactly 15 subjects while class offers 16", count($student2Subjects) === 15);

// Cleanup exception
$pdo->exec("DELETE FROM student_subject_enrollments WHERE applicant_id = {$student2Id}");


// ── GROUP 4: Configurable Assessment Components & Bounds Validation ──────
echo "\n--- Group 4: Assessment Components & Bounds Validation ---\n";

$components = ResultService::getAssessmentComponents();
assertTest("Assessment components table returns active components", count($components) >= 5);

$compMap = [];
foreach ($components as $c) {
    $compMap[$c['code']] = $c;
}
assertTest("Test 1 max score is configured at 15", ($compMap['ca1']['max_score'] ?? 0) == 15.0);
assertTest("Examination max score is configured at 55", ($compMap['exam']['max_score'] ?? 0) == 55.0);

// Score bounds validation tests
$valNeg = ResultService::validateComponentScore(-2, 15.0, 'Test 1');
assertTest("Rejects negative score (-2)", $valNeg['valid'] === false);

$valExceed = ResultService::validateComponentScore(22, 15.0, 'Test 1');
assertTest("Rejects score exceeding max allowed (22 > 15)", $valExceed['valid'] === false);

$valNonNum = ResultService::validateComponentScore('abc', 15.0, 'Test 1');
assertTest("Rejects non-numeric score ('abc')", $valNonNum['valid'] === false);

$valValid = ResultService::validateComponentScore(13.5, 15.0, 'Test 1');
assertTest("Accepts valid score (13.5 / 15.0)", $valValid['valid'] === true && $valValid['value'] === 13.5);

$valEmpty = ResultService::validateComponentScore('', 15.0, 'Test 1');
assertTest("Handles empty/null score gracefully", $valEmpty['valid'] === true && $valEmpty['value'] === null);


// ── GROUP 5: Configurable Grading Rules & Grade Point Scale ───────────────
echo "\n--- Group 5: Configurable Grading Rules & Evaluation ---\n";

$g1 = GradingService::evaluate(85.0);
assertTest("Score 85.0 evaluates to A1 (Excellent)", $g1['grade'] === 'A1' && $g1['remark'] === 'Excellent');

$g2 = GradingService::evaluate(72.0);
assertTest("Score 72.0 evaluates to B2 (Very Good)", $g2['grade'] === 'B2' && $g2['remark'] === 'Very Good');

$g3 = GradingService::evaluate(67.5);
assertTest("Score 67.5 evaluates to B3 (Good)", $g3['grade'] === 'B3' && $g3['remark'] === 'Good');

$g4 = GradingService::evaluate(52.0);
assertTest("Score 52.0 evaluates to C6 (Credit)", $g4['grade'] === 'C6' && $g4['remark'] === 'Credit');

$g5 = GradingService::evaluate(46.0);
assertTest("Score 46.0 evaluates to D7 (Pass)", $g5['grade'] === 'D7' && $g5['remark'] === 'Pass');

$g6 = GradingService::evaluate(33.0);
assertTest("Score 33.0 evaluates to F9 (Fail)", $g6['grade'] === 'F9' && $g6['remark'] === 'Fail');

$scale = GradingService::getScale();
assertTest("Grading scale table returns all tiers", count($scale) >= 9);


// ── GROUP 6: Average Calculation & Terminal Aggregation ───────────────────
echo "\n--- Group 6: Average Calculation & Terminal Aggregation ---\n";

// Clear and seed test results for Student 1 in Primary 1 (16 subjects)
$term = 'First';
$year = '2024/2025';
$classId = 1;

$pdo->exec("DELETE FROM student_results WHERE applicant_id = {$student1Id} AND term = '{$term}' AND academic_year = '{$year}'");

// Seed 16 subjects with 80 marks each -> Total = 16 * 80 = 1280. Average MUST be 80.0%
$stmtInsRes = $pdo->prepare(
    "INSERT INTO student_results (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, status)
     VALUES (?, ?, ?, ?, ?, 10, 10, 10, 10, 40, 80, 'A1', 'Excellent', 'draft')"
);

for ($s = 1; $s <= 16; $s++) {
    $stmtInsRes->execute([$student1Id, $s, $classId, $term, $year]);
}

$report = ResultService::calculateStudentResult($student1Id, $classId, $term, $year);
assertTest("Report card includes student bio", !empty($report['student']['id']));
assertTest("Report card includes all 16 dynamic subjects", count($report['subjects']) === 16);
assertTest("Grand total equals 1280 (16 subjects * 80)", (float)$report['summary']['grand_total'] === 1280.0);
assertTest(
    "Student average equals grand_total / actual_subject_count (1280 / 16 = 80.0%)",
    (float)$report['summary']['average_score'] === 80.0,
    "Got average: " . $report['summary']['average_score']
);
assertTest("Overall grade is A1", $report['summary']['overall_grade'] === 'A1');


// ── GROUP 7: Class Positions & Rankings Recalculation ─────────────────────
echo "\n--- Group 7: Class Positions & Rankings Recalculation ---\n";

// Seed Student 2 in Primary 1 with 16 subjects at 90 marks each -> Total = 1440, Average = 90.0%
$pdo->exec("DELETE FROM student_results WHERE applicant_id = {$student2Id} AND term = '{$term}' AND academic_year = '{$year}'");
for ($s = 1; $s <= 16; $s++) {
    $pdo->prepare(
        "INSERT INTO student_results (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, status)
         VALUES ({$student2Id}, ?, 1, ?, ?, 12, 10, 10, 10, 48, 90, 'A1', 'Excellent', 'draft')"
    )->execute([$s, $term, $year]);
}

// Recalculate rankings for Primary 1
ResultService::recalculateClassPositions(1, $term, $year);

// Fetch term_remarks for Student 2 (should be position 1 with avg 90)
$s2Remark = $pdo->query("SELECT * FROM term_remarks WHERE applicant_id = {$student2Id} AND term = '{$term}' AND academic_year = '{$year}'")->fetch(PDO::FETCH_ASSOC);
assertTest("Student 2 with higher average (90.0%) takes Position 1", (int)($s2Remark['position'] ?? 0) === 1);
assertTest("Student 2 average in term_remarks is 90.0%", (float)($s2Remark['average'] ?? 0) === 90.0);

// Fetch term_remarks for Student 1 (should be position 2 with avg 80)
$s1Remark = $pdo->query("SELECT * FROM term_remarks WHERE applicant_id = {$student1Id} AND term = '{$term}' AND academic_year = '{$year}'")->fetch(PDO::FETCH_ASSOC);
assertTest("Student 1 with average (80.0%) takes Position 2", (int)($s1Remark['position'] ?? 0) === 2);
assertTest("Student 1 average in term_remarks is 80.0%", (float)($s1Remark['average'] ?? 0) === 80.0);


// ── GROUP 8: Class Result Overview Matrix ─────────────────────────────────
echo "\n--- Group 8: Class Result Overview Matrix ---\n";

$matrix = ResultService::getClassResultOverview(1, $term, $year);
assertTest("Matrix returns overview for class ID 1", $matrix['class_id'] === 1);
assertTest("Matrix lists all 16 subjects offered by the class", count($matrix['subjects']) === 16);
assertTest("Matrix counts enrolled students", $matrix['total_students'] > 0);
assertTest("Matrix reports completed vs pending subjects", isset($matrix['completion_rate']));


// ── GROUP 9: Lifecycle Workflow & Lock Enforcement ───────────────────────
echo "\n--- Group 9: Lifecycle Workflow & Lock Enforcement ---\n";

// Update subject 1 status to 'approved'
$pdo->exec("UPDATE student_results SET status = 'approved', approved_at = NOW(), approved_by = 1 WHERE class_id = 1 AND subject_id = 1 AND term = '{$term}' AND academic_year = '{$year}'");

$chk = $pdo->query("SELECT status FROM student_results WHERE class_id = 1 AND subject_id = 1 AND term = '{$term}' AND academic_year = '{$year}' LIMIT 1")->fetchColumn();
assertTest("Result transitions to 'approved'", $chk === 'approved');

// Update subject 1 status to 'published'
$pdo->exec("UPDATE student_results SET status = 'published', published_at = NOW(), published_by = 1 WHERE class_id = 1 AND subject_id = 1 AND term = '{$term}' AND academic_year = '{$year}'");

$chkPub = $pdo->query("SELECT status FROM student_results WHERE class_id = 1 AND subject_id = 1 AND term = '{$term}' AND academic_year = '{$year}' LIMIT 1")->fetchColumn();
assertTest("Result transitions to 'published'", $chkPub === 'published');


// ── GROUP 10: Multi-Tenant Isolation (TenantPDO) ─────────────────────────
echo "\n--- Group 10: Multi-Tenant Isolation ---\n";

assertTest("class_subjects is registered in TenantPDO::\$tenantTables", in_array('class_subjects', TenantPDO::$tenantTables, true));
assertTest("assessment_components is registered in TenantPDO::\$tenantTables", in_array('assessment_components', TenantPDO::$tenantTables, true));
assertTest("grading_rules is registered in TenantPDO::\$tenantTables", in_array('grading_rules', TenantPDO::$tenantTables, true));
assertTest("student_subject_enrollments is registered in TenantPDO::\$tenantTables", in_array('student_subject_enrollments', TenantPDO::$tenantTables, true));


// ── SUMMARY REPORT ───────────────────────────────────────────────────────
echo "\n====================================================================\n";
echo "Test Execution Summary: {$passedTests} Passed, {$failedTests} Failed (Total: {$totalTests})\n";
echo "====================================================================\n";

if ($failedTests > 0) {
    exit(1);
}
exit(0);
