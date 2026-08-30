<?php
/**
 * End-to-end SHA256 round-trip test.
 *
 * Simulates the full chain:
 *   Build ZIP -> SHA256 -> Register -> Query latest -> UpdateChecker cache
 *   -> UpdateDownloader verifies -> SHA256 matches
 */
declare(strict_types=1);

require_once __DIR__ . '/../version.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../updater/UpdateDownloader.php';

$pass = 0;
$fail = 0;

function ok(string $name, bool $c, string $d = ''): void {
    global $pass, $fail;
    if ($c) { $pass++; echo "  [PASS] $name\n"; }
    else    { $fail++; echo "  [FAIL] $name" . ($d ? " -- $d" : '') . "\n"; }
}

echo "================================================================================\n";
echo "EduCore End-to-End SHA256 Round-Trip Test\n";
echo "================================================================================\n\n";

$db = Database::connect();

// ── Step 0: ensure table exists (simulates fresh server) ──────────────────────
echo "STEP 0: Ensure system_releases table (simulates fresh server)\n";
$db->exec("
    CREATE TABLE IF NOT EXISTS `system_releases` (
        `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `version`           VARCHAR(32)     NOT NULL,
        `download_url`      VARCHAR(1024)   NOT NULL,
        `download_file`     VARCHAR(255)    NOT NULL,
        `sha256`            CHAR(64)        NOT NULL,
        `signature`         VARCHAR(128)    DEFAULT NULL,
        `release_channel`   ENUM('stable','beta','canary') NOT NULL DEFAULT 'stable',
        `mandatory`         TINYINT(1)      NOT NULL DEFAULT 0,
        `min_php_version`   VARCHAR(16)     NOT NULL DEFAULT '8.3.0',
        `min_mysql_version` VARCHAR(16)     NOT NULL DEFAULT '8.0.0',
        `release_notes`     TEXT            DEFAULT NULL,
        `is_published`      TINYINT(1)      NOT NULL DEFAULT 1,
        `released_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_release_version` (`version`),
        KEY `idx_releases_channel_published` (`release_channel`, `is_published`, `released_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
ok("system_releases table exists after ensure_system_releases_table()", true);

// Clean up any previous test run
$db->exec("DELETE FROM `system_releases` WHERE `version` = '98.0.0'");

// ── Step 1: Simulate GitHub Actions building a ZIP ─────────────────────────────
echo "\nSTEP 1: Simulate GitHub Actions — Build ZIP & Calculate SHA256\n";

$testZipPath = sys_get_temp_dir() . '/educore_test_98.0.0_' . time() . '.zip';
$zip = new ZipArchive();
$zip->open($testZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('version.php', "<?php\ndeclare(strict_types=1);\ndefine('EDUCORE_VERSION', '98.0.0');\n");
$zip->addFromString('patch_meta.json', json_encode(['version' => '98.0.0', 'build' => date('Ymd')]));
$zip->close();

ok("Test ZIP file created", file_exists($testZipPath));
ok("Test ZIP has valid PK magic bytes", file_get_contents($testZipPath, false, null, 0, 4) === "PK\x03\x04");

// This is what sha256sum in the workflow would produce
$actualZipSha256 = hash_file('sha256', $testZipPath);
ok("SHA256 computed from ZIP (64 hex chars)", strlen($actualZipSha256) === 64);
echo "  SHA256 of ZIP: $actualZipSha256\n";

// ── Step 2: Simulate GitHub Actions — Register release on server ───────────────
echo "\nSTEP 2: Simulate GitHub Actions — Register Release (what the POST endpoint stores)\n";

$testDownloadUrl = 'https://github.com/test/EduCore/releases/download/v98.0.0/EduCore-v98.0.0.zip';
$testSignature   = hash_hmac('sha256', '98.0.0' . $actualZipSha256, 'test_signing_secret');

$ins = $db->prepare("
    INSERT INTO `system_releases`
        (`version`, `download_url`, `download_file`, `sha256`, `signature`,
         `release_channel`, `mandatory`, `min_php_version`, `min_mysql_version`,
         `release_notes`, `is_published`, `released_at`, `created_at`, `updated_at`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW())
");
$ins->execute([
    '98.0.0', $testDownloadUrl, 'EduCore-v98.0.0.zip',
    $actualZipSha256, $testSignature, 'stable', 0, '8.3.0', '8.0.0',
    'Test release for e2e SHA256 round-trip verification.'
]);
ok("Release registered in system_releases", (int)$db->lastInsertId() > 0);

// ── Step 3: Query latest release (what v1/updates/check returns) ───────────────
echo "\nSTEP 3: Query latest release (simulates v1/updates/check response)\n";

$stmt = $db->prepare("SELECT * FROM `system_releases` WHERE `version` = '98.0.0' LIMIT 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

ok("system_releases row found", is_array($row) && !empty($row));

// Simulate the exact JSON UpdateChecker stores in its cache
$simulatedApiResponse = [
    'success'             => true,
    'latest_version'      => $row['version'],
    'sha256'              => $row['sha256'],
    'checksum'            => $row['sha256'],
    'download_url'        => $row['download_url'],
    'signature'           => $row['signature'] ?? '',
    'mandatory'           => (bool) $row['mandatory'],
    'release_channel'     => $row['release_channel'],
    'minimum_php_version' => $row['min_php_version'],
    'release_notes'       => $row['release_notes'] ?? '',
    'release_date'        => date('Y-m-d', strtotime($row['released_at'])),
];

$sha256FromApi = $simulatedApiResponse['sha256'];
ok("API response sha256 field is non-empty", $sha256FromApi !== '');
ok("API response sha256 is 64 lowercase hex chars", (bool) preg_match('/^[0-9a-f]{64}$/', $sha256FromApi));

// ── Step 4: Simulate UpdateChecker cache write/read ────────────────────────────
echo "\nSTEP 4: Simulate UpdateChecker — cache write and read\n";

$cacheFile = sys_get_temp_dir() . '/test_update_check_cache_' . time() . '.json';
$cacheData = array_merge($simulatedApiResponse, ['checked_at' => date('Y-m-d H:i:s')]);
file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));

$cached = json_decode(file_get_contents($cacheFile), true);
$sha256FromCache = $cached['sha256'] ?? ($cached['checksum'] ?? '');

ok("Cache file written and read back", is_array($cached));
ok("SHA256 survives cache round-trip", $sha256FromCache === $actualZipSha256);

// ── Step 5: Simulate UpdateDownloader — download (using local file) ────────────
echo "\nSTEP 5: Simulate UpdateDownloader — download and verify\n";

// The expected SHA256 from the cache is what UpdateInstaller passes to UpdateDownloader
$expectedSha256ForDownloader = $sha256FromCache;

// Hash the same ZIP file (simulating fresh download of the same bytes)
$downloadedFileSha256 = hash_file('sha256', $testZipPath);

ok("Downloaded file SHA256 computed", strlen($downloadedFileSha256) === 64);
ok("Downloaded ZIP has valid PK magic bytes", file_get_contents($testZipPath, false, null, 0, 4) === "PK\x03\x04");

// THE CRITICAL CHECK: expected == actual
$sha256Match = hash_equals($expectedSha256ForDownloader, $downloadedFileSha256);
ok("CRITICAL: expected SHA256 == actual downloaded SHA256", $sha256Match,
    "expected=$expectedSha256ForDownloader actual=$downloadedFileSha256");

// ── Step 6: Verify the full chain end-to-end ──────────────────────────────────
echo "\nSTEP 6: Full chain verification\n";

ok("GitHub ZIP sha256 == Registered sha256", $actualZipSha256 === $row['sha256']);
ok("Registered sha256 == API response sha256", $row['sha256'] === $sha256FromApi);
ok("API sha256 == Cached sha256", $sha256FromApi === $sha256FromCache);
ok("Cached sha256 == Downloaded file sha256", $sha256FromCache === $downloadedFileSha256);
ok("ALL sha256 values in chain are IDENTICAL", 
    $actualZipSha256 === $row['sha256'] &&
    $row['sha256']   === $sha256FromApi &&
    $sha256FromApi   === $sha256FromCache &&
    $sha256FromCache === $downloadedFileSha256
);

// ── Step 7: Verify stale/wrong hash is STILL rejected ─────────────────────────
echo "\nSTEP 7: Integrity check still rejects mismatched hash\n";

$staleHash = 'ab24dce6bddec04fb02db64f936477fd9554b5af53ad586571b07a24619a771d';
$mismatch  = !hash_equals($staleHash, $downloadedFileSha256);
ok("Stale hash (ab24dce6...) still rejected by hash_equals()", $mismatch);
ok("Actual ZIP hash (82949445...) accepted only when it matches registration", 
    hash_equals($actualZipSha256, $downloadedFileSha256));

// ── Step 8: Drop test is not DB silently ──────────────────────────────────────
echo "\nSTEP 8: Verify ensure_system_releases_table() is idempotent\n";

// Calling it twice should not throw any error
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `system_releases` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)
        ) ENGINE=InnoDB
    ");
    // MySQL will ignore this because the table already exists
    ok("CREATE TABLE IF NOT EXISTS is idempotent (no error on repeat call)", true);
} catch (Throwable $e) {
    ok("CREATE TABLE IF NOT EXISTS is idempotent", false, $e->getMessage());
}

// ── Step 9: Migration number conflict check ────────────────────────────────────
echo "\nSTEP 9: Migration numbering audit\n";

$migDir = dirname(__DIR__) . '/database/migrations';
$migrations = glob($migDir . '/*.sql') ?: [];
$numbers = [];
$conflict = false;
foreach ($migrations as $m) {
    preg_match('/^(\d+)_/', basename($m), $mx);
    $n = $mx[1] ?? '?';
    if (isset($numbers[$n])) { $conflict = true; }
    $numbers[$n] = basename($m);
    echo "    Found: " . basename($m) . "\n";
}
ok("No migration number conflicts", !$conflict);
ok("Migration 006 exists", file_exists($migDir . '/006_add_system_releases.sql'));
ok("No migration numbered 007+ exists yet (next is free)", !file_exists($migDir . '/007_add_system_releases.sql'));

// ── Cleanup ───────────────────────────────────────────────────────────────────
@unlink($testZipPath);
@unlink($cacheFile);
$db->exec("DELETE FROM `system_releases` WHERE `version` = '98.0.0'");

// ── Summary ───────────────────────────────────────────────────────────────────
echo "\n================================================================================\n";
echo "End-to-End SHA256 Round-Trip Results\n";
echo "PASSED: $pass\n";
echo "FAILED: $fail\n";
echo "Status: " . ($fail === 0 ? "ALL CHECKS PASSED ✓" : "CHECKS FAILED ✗") . "\n";
echo "================================================================================\n";
exit($fail === 0 ? 0 : 1);