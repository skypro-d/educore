<?php
/**
 * Automated Verification Suite for EduCore Installation, Licensing, & Remote Update System
 */
declare(strict_types=1);

echo "======================================================================\n";
echo "EDUCORE AUTOMATED VERIFICATION SUITE — PHP " . PHP_VERSION . "\n";
echo "======================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $testName, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$testName} — {$details}\n";
    }
}

// -----------------------------------------------------------------------------
// TEST 1: Version Definition and Authoritative Source
// -----------------------------------------------------------------------------
echo "1. Testing Version Source & Definitions...\n";
require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/../config/config.php';

assertTest("EDUCORE_VERSION is defined", defined('EDUCORE_VERSION') && EDUCORE_VERSION !== '', "Value: " . (defined('EDUCORE_VERSION') ? EDUCORE_VERSION : 'null'));
assertTest("EDUCORE_MIN_PHP is 8.3.0+", defined('EDUCORE_MIN_PHP') && version_compare(EDUCORE_MIN_PHP, '8.3.0', '>='), "Value: " . (defined('EDUCORE_MIN_PHP') ? EDUCORE_MIN_PHP : 'null'));
assertTest("EDUCORE_LIVE_URL is configured", defined('EDUCORE_LIVE_URL') && EDUCORE_LIVE_URL !== '', "Value: " . (defined('EDUCORE_LIVE_URL') ? EDUCORE_LIVE_URL : 'null'));

// -----------------------------------------------------------------------------
// TEST 2: Database Connection & MigrationRunner
// -----------------------------------------------------------------------------
echo "\n2. Testing Database Connection & Migration Engine...\n";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../updater/MigrationRunner.php';

$pdo = Database::connect();
assertTest("Database connects via PDO", $pdo instanceof PDO);

$migrationRunner = new MigrationRunner($pdo);
$migrationRunner->ensureMigrationsTable();
$allMigs = $migrationRunner->getAllMigrationFiles();
assertTest("Found versioned migration files in database/migrations/", count($allMigs) >= 3, "Count: " . count($allMigs));

$result = $migrationRunner->runPending();
assertTest("MigrationRunner executed pending migrations successfully", $result['success'] === true, $result['message']);

$executed = $migrationRunner->getExecutedMigrations();
assertTest("Executed migrations tracked in database ledger", count($executed) >= 1, "Count: " . count($executed));

// -----------------------------------------------------------------------------
// TEST 3: HealthChecker Diagnostics
// -----------------------------------------------------------------------------
echo "\n3. Testing System HealthChecker Diagnostics...\n";
require_once __DIR__ . '/../updater/HealthChecker.php';

$healthChecker = new HealthChecker($pdo);
$health = $healthChecker->runChecks();
assertTest("HealthChecker reports overall healthy system", $health['healthy'] === true, "Errors: " . implode('; ', $health['errors']));
assertTest("HealthChecker verified database connection", ($health['checks']['database_connection'] ?? '') === 'PASS');
assertTest("HealthChecker verified core files", ($health['checks']['core_files'] ?? '') === 'PASS');

// -----------------------------------------------------------------------------
// TEST 4: BackupManager Snapshot Creation
// -----------------------------------------------------------------------------
echo "\n4. Testing Pre-Update Backup Snapshot Engine...\n";
require_once __DIR__ . '/../updater/BackupManager.php';

$backupMgr = new BackupManager($pdo);
$backupResult = $backupMgr->createBackup('1.0.0_test');
assertTest("BackupManager created full snapshot", $backupResult['success'] === true);
assertTest("Database SQL snapshot generated", file_exists($backupResult['db_file']));
assertTest("Codebase ZIP archive generated", file_exists($backupResult['files_zip']));
assertTest("Backup snapshot size > 0", $backupResult['total_size_bytes'] > 100, "Size: " . $backupResult['total_size_bytes'] . " bytes");

// -----------------------------------------------------------------------------
// TEST 5: RollbackManager Recovery
// -----------------------------------------------------------------------------
echo "\n5. Testing Rollback Recovery Engine...\n";
require_once __DIR__ . '/../updater/RollbackManager.php';

$rollbackMgr = new RollbackManager($pdo);
$rollbackResult = $rollbackMgr->rollback($backupResult['backup_dir']);
assertTest("RollbackManager restored files and database cleanly", $rollbackResult['success'] === true, $rollbackResult['message']);

// Clean up test backup
$backupMgr->recursiveDeleteDir($backupResult['backup_dir']);

// -----------------------------------------------------------------------------
// TEST 6: Licensing & Feature Gate Access Controls
// -----------------------------------------------------------------------------
echo "\n6. Testing Licensing, Offline Grace, & Feature Entitlements...\n";
require_once __DIR__ . '/../config/ApiKeyService.php';
require_once __DIR__ . '/../config/FeatureManager.php';

$lic = ApiKeyService::loadLocalLicense();
assertTest("Local license loader returns structured array", is_array($lic));
assertTest("Installation ID is available", ApiKeyService::getInstallationId() !== '');

// Feature Gating Checks
assertTest("Core feature 'students' is ALWAYS enabled", FeatureManager::hasFeature('students') === true);
assertTest("Core feature 'attendance' is ALWAYS enabled", FeatureManager::hasFeature('attendance') === true);
assertTest("Core feature 'fees' is ALWAYS enabled", FeatureManager::hasFeature('fees') === true);

// -----------------------------------------------------------------------------
// TEST 7: UpdateLock Concurrency Protection
// -----------------------------------------------------------------------------
echo "\n7. Testing UpdateLock Concurrency Controls...\n";
require_once __DIR__ . '/../updater/UpdateLock.php';

$lock = new UpdateLock();
$lock->release(); // clean start
assertTest("Lock is initially unlocked", !$lock->isLocked());

$lock->acquire('1.0.1');
assertTest("Lock is locked after acquire", $lock->isLocked());

$exceptionThrown = false;
try {
    $lock->acquire('1.0.2');
} catch (RuntimeException $e) {
    $exceptionThrown = true;
}
assertTest("Attempting concurrent lock throws RuntimeException", $exceptionThrown);

$lock->release();
assertTest("Lock released cleanly", !$lock->isLocked());

// -----------------------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------------------
echo "\n======================================================================\n";
echo "TEST RESULTS SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================================\n";

exit($failCount === 0 ? 0 : 1);
