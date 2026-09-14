<?php
declare(strict_types=1);

/**
 * tests/test_multi_class_fee_setup.php
 * Automated verification suite for Multi-Class Fee Structure Configuration.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
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
echo "TEST SUITE: MULTI-CLASS FEE STRUCTURE CONFIGURATION\n";
echo "================================================================================\n\n";

// Get available classes or create test classes if fewer than 3 exist
$classModel = new ClassModel($pdo);
$classes = $classModel->all();
while (count($classes) < 3) {
    $idx = count($classes) + 1;
    $pdo->exec("INSERT INTO classes (school_id, name, sort_order) VALUES (1, 'Test Fee Class {$idx}', {$idx})");
    $classes = $classModel->all();
}

$c1 = (int) $classes[0]['id'];
$c2 = (int) $classes[1]['id'];
$c3 = (int) $classes[2]['id'];
$selectedClassIds = [$c1, $c2, $c3];

$testFeeName = 'Lab Equipment Fee ' . rand(1000, 9999);
$testAmount = 15000.00;
$testTerm = 'First';
$testYear = '2024/2025';

$phpBinary = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';

// Helper to run AdminController::saveFeeStructure in an isolated sub-process
function executeSaveFee(string $phpBinary, array $postData): void {
    $baseDir = str_replace('\\', '/', dirname(__DIR__));
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
        . '$c->saveFeeStructure(); ';

    $tmpFile = tempnam(sys_get_temp_dir(), 'test_fee_') . '.php';
    file_put_contents($tmpFile, $code);
    exec("\"{$phpBinary}\" \"{$tmpFile}\" 2>&1", $out, $ret);
    @unlink($tmpFile);
}

// =============================================================================
// TEST 1: Save Fee Structure for 3 Specific Classes at a Go
// =============================================================================
echo "TEST 1: Configure Fee for 3 Selected Classes at a go\n";

$postData1 = [
    'csrf_token' => 'valid_test_token',
    'fee_name' => $testFeeName,
    'amount' => $testAmount,
    'term' => $testTerm,
    'academic_year' => $testYear,
    'class_ids' => $selectedClassIds,
    'is_optional' => '0',
];

executeSaveFee($phpBinary, $postData1);

// Check database records created
$stmt = $pdo->prepare("SELECT * FROM fee_structures WHERE fee_name = ? ORDER BY class_id ASC");
$stmt->execute([$testFeeName]);
$rows = $stmt->fetchAll();

assert_equals(3, count($rows), "Exactly 3 fee structure records created at once");

$createdClassIds = array_map('intval', array_column($rows, 'class_id'));
sort($createdClassIds);
$expectedClassIds = $selectedClassIds;
sort($expectedClassIds);

assert_equals($expectedClassIds, $createdClassIds, "Fee records correctly assigned to all 3 selected classes");
foreach ($rows as $r) {
    assert_equals((float)$testAmount, (float)$r['amount'], "Fee amount is NGN {$testAmount} for class {$r['class_id']}");
    assert_equals($testTerm, $r['term'], "Term is {$testTerm}");
    assert_equals($testYear, $r['academic_year'], "Academic year is {$testYear}");
}

// =============================================================================
// TEST 2: Save Fee Structure for All Classes (Universal Fee)
// =============================================================================
echo "\nTEST 2: Configure Universal Fee (All Classes)\n";

$universalFeeName = 'Sports Development Fee ' . rand(1000, 9999);
$postData2 = [
    'csrf_token' => 'valid_test_token',
    'fee_name' => $universalFeeName,
    'amount' => 5000.00,
    'term' => 'Second',
    'academic_year' => $testYear,
    'all_classes' => '1',
    'class_ids' => [],
];

executeSaveFee($phpBinary, $postData2);

$stmtUni = $pdo->prepare("SELECT * FROM fee_structures WHERE fee_name = ?");
$stmtUni->execute([$universalFeeName]);
$uniRow = $stmtUni->fetch();

assert_true((bool) $uniRow, "Universal fee record created");
assert_true($uniRow['class_id'] === null, "class_id is NULL for All Classes universal fee");
assert_equals(5000.0, (float) $uniRow['amount'], "Universal fee amount is 5000.00");

// =============================================================================
// CLEANUP: Remove test fee records
// =============================================================================
echo "\nCLEANUP: Removing test fee records...\n";
$pdo->prepare("DELETE FROM fee_structures WHERE fee_name IN (?, ?)")->execute([$testFeeName, $universalFeeName]);
echo "  Cleaned up test fee records.\n";

echo "\n================================================================================\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "================================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
