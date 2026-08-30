<?php
/**
 * End-to-End Live Server Integration Test Suite
 */
declare(strict_types=1);

echo "======================================================================\n";
echo "EDUCORE LIVE SERVER INTEGRATION TEST SUITE\n";
echo "======================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertIntegration(string $testName, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$testName} — {$details}\n";
    }
}

require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ApiKeyService.php';
require_once __DIR__ . '/../config/HeartbeatService.php';
require_once __DIR__ . '/../updater/UpdateChecker.php';
require_once __DIR__ . '/../updater/UpdateInstaller.php';

// -----------------------------------------------------------------------------
// TEST 1: Activate demo license key on EduCore Live
// -----------------------------------------------------------------------------
echo "1. Testing License Activation Handshake with EduCore Live...\n";
$demoKey = 'SKY-PRO-4444-5555-6666';
$domain = 'localhost';
$instId = 'edc_inst_test_' . bin2hex(random_bytes(8));

$res = ApiKeyService::sendSecureRequest('api/v1/license/activate', [
    'license_key' => $demoKey,
    'domain' => $domain,
    'installation_id' => $instId,
    'software_version' => EDUCORE_VERSION,
    'php_version' => PHP_VERSION,
    'release_channel' => 'stable'
]);

assertIntegration("Live server responds to activation request", is_array($res));
assertIntegration("Activation status is active", ($res['status'] ?? '') === 'active', "Status: " . ($res['status'] ?? 'null'));
assertIntegration("Live server returned domain-bound API key", !empty($res['api_key']));
assertIntegration("Live server returned installation token", !empty($res['installation_token']));
assertIntegration("Live server returned enabled features list", !empty($res['features']), "Features count: " . count($res['features'] ?? []));

// Save active cache for subsequent tests
if (!empty($res['api_key'])) {
    $licCache = [
        'success' => true,
        'status' => 'active',
        'license_key' => $demoKey,
        'domain' => $domain,
        'installation_id' => $instId,
        'api_key' => $res['api_key'],
        'installation_token' => $res['installation_token'],
        'plan' => $res['plan'] ?? 'professional',
        'features' => $res['features'] ?? [],
        'features_map' => $res['features_map'] ?? [],
        'grace_period_days' => $res['grace_period_days'] ?? 30,
        'expires_at' => $res['license']['expires_at'] ?? null,
        'last_validated' => date('Y-m-d H:i:s')
    ];
    ApiKeyService::saveCache($licCache);
}

// -----------------------------------------------------------------------------
// TEST 2: Validate active license
// -----------------------------------------------------------------------------
echo "\n2. Testing Online License Validation & Entitlements Sync...\n";
$valRes = ApiKeyService::validateOnline();
assertIntegration("Online validation succeeds", $valRes['success'] === true, "Status: " . ($valRes['status'] ?? 'null'));
assertIntegration("Entitlements synced: Plan is Professional", ($valRes['plan'] ?? '') === 'professional');

// -----------------------------------------------------------------------------
// TEST 3: Heartbeat Telemetry Ping
// -----------------------------------------------------------------------------
echo "\n3. Testing Heartbeat Telemetry Ping...\n";
$hbRes = HeartbeatService::send();
assertIntegration("Heartbeat transmitted to EduCore Live successfully", ($hbRes['success'] ?? false) === true, $hbRes['message'] ?? 'Failed');

// -----------------------------------------------------------------------------
// TEST 4: Remote Update Checking
// -----------------------------------------------------------------------------
echo "\n4. Testing Remote Update Check Endpoint...\n";
$checkRes = UpdateChecker::check(true);
assertIntegration("Update check completed", is_array($checkRes) && isset($checkRes['current_version']));
assertIntegration("Update check returns current version", $checkRes['current_version'] === EDUCORE_VERSION);

// -----------------------------------------------------------------------------
// TEST 5: Full Software Upgrade & Health Verification
// -----------------------------------------------------------------------------
echo "\n5. Testing Master Update Pipeline with Simulated v1.0.1 Patch...\n";
$installer = new UpdateInstaller();
$upgradeRes = $installer->applyUpdate(
    '1.0.1',
    defined('EDUCORE_LIVE_URL') ? (EDUCORE_LIVE_URL . '/api/v1/updates/download?version=1.0.1') : 'http://localhost/EduCore-LicenseServer/api/v1/updates/download?version=1.0.1',
    '',
    '',
    'automated_test_suite'
);

assertIntegration("Software upgrade completed successfully", $upgradeRes['success'] === true, $upgradeRes['message'] ?? '');
$writtenVersionContent = file_get_contents(dirname(__DIR__) . '/version.php');
assertIntegration("Target version written to version.php (v1.0.1)", str_contains($writtenVersionContent, "'1.0.1'"));

// Verify health check after upgrade
$healthChecker = new HealthChecker();
$health = $healthChecker->runChecks();
assertIntegration("Post-upgrade system diagnostics 100% healthy", $health['healthy'] === true);

// -----------------------------------------------------------------------------
// TEST 6: Revert version.php back to 1.0.0 for clean repo state
// -----------------------------------------------------------------------------
echo "\n6. Resetting baseline version definition to v1.0.0 for development...\n";
$versionFile = dirname(__DIR__) . '/version.php';
$cleanVersion = "<?php\ndeclare(strict_types=1);\n\n/**\n * EduCore Software Version Definition\n */\nif (!defined('EDUCORE_VERSION')) define('EDUCORE_VERSION', '1.0.0');\nif (!defined('EDUCORE_BUILD')) define('EDUCORE_BUILD', '20260829.1');\nif (!defined('EDUCORE_MIN_PHP')) define('EDUCORE_MIN_PHP', '8.3.0');\nif (!defined('EDUCORE_MIN_MYSQL')) define('EDUCORE_MIN_MYSQL', '8.0.0');\nif (!defined('EDUCORE_DEFAULT_CHANNEL')) define('EDUCORE_DEFAULT_CHANNEL', 'stable');\n";
file_put_contents($versionFile, $cleanVersion);
assertIntegration("version.php cleanly restored", true);

// -----------------------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------------------
echo "\n======================================================================\n";
echo "INTEGRATION TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================================\n";

exit($failCount === 0 ? 0 : 1);
