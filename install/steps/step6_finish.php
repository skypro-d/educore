<?php
// Step 6: Finalize Installation
declare(strict_types=1);

$errorMsg = '';
$installedSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dbSession = $_SESSION['install_db'] ?? [
            'host' => 'localhost',
            'name' => 'educore_school',
            'user' => 'root',
            'pass' => ''
        ];
        $schoolSession = $_SESSION['install_school'] ?? [
            'name' => 'EduCore Academy',
            'email' => 'admin@school.com',
            'phone' => '+234 800 000 0000',
            'address' => '1 Excellence Way',
            'principal' => 'Dr. Principal',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'session' => '2025/2026'
        ];
        $adminSession = $_SESSION['install_admin'] ?? [
            'name' => 'System Administrator',
            'email' => 'admin@school.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT)
        ];
        $licenseSession = $_SESSION['install_license'] ?? [
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'key' => 'SKY-BASIC-1111-2222-3333',
            'server_url' => 'http://localhost/EduCore-LicenseServer',
            'installation_id' => $_SESSION['install_id'] ?? ('edc_inst_' . bin2hex(random_bytes(16))),
            'api_key' => '',
            'installation_token' => '',
            'plan' => 'basic',
            'features' => ['students', 'attendance', 'fees'],
            'grace_period_days' => 30
        ];

        $installationId = $licenseSession['installation_id'] ?? ($_SESSION['install_id'] ?? ('edc_inst_' . bin2hex(random_bytes(16))));
        $liveServerUrl = rtrim($licenseSession['server_url'] ?? 'http://localhost/EduCore-LicenseServer', '/');

        // 1. Connect to Database & Save Data
        $dsn = "mysql:host={$dbSession['host']};dbname={$dbSession['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbSession['user'], $dbSession['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Insert/Update school_settings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM school_settings");
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        $schoolName = !empty($schoolSession['name']) ? $schoolSession['name'] : 'EduCore Academy';
        $schoolEmail = !empty($schoolSession['email']) ? $schoolSession['email'] : 'admin@school.com';
        $schoolPhone = !empty($schoolSession['phone']) ? $schoolSession['phone'] : '+234 800 000 0000';
        $schoolAddress = !empty($schoolSession['address']) ? $schoolSession['address'] : '1 Excellence Way';
        $principal = !empty($schoolSession['principal']) ? $schoolSession['principal'] : 'Dr. Principal';
        $currency = !empty($schoolSession['currency']) ? $schoolSession['currency'] : 'NGN';
        $timezone = !empty($schoolSession['timezone']) ? $schoolSession['timezone'] : 'Africa/Lagos';
        $sessionStr = !empty($schoolSession['session']) ? $schoolSession['session'] : '2025/2026';
        $domain = !empty($licenseSession['domain']) ? $licenseSession['domain'] : ($_SERVER['HTTP_HOST'] ?? 'localhost');

        if ($count > 0) {
            $updateSql = "UPDATE school_settings SET 
                school_name = ?, email = ?, phone = ?, address = ?, principal_name = ?,
                currency = ?, timezone = ?, academic_session = ?, domain = ? WHERE id = 1";
            $pdo->prepare($updateSql)->execute([
                $schoolName, $schoolEmail, $schoolPhone, $schoolAddress, $principal,
                $currency, $timezone, $sessionStr, $domain
            ]);
        } else {
            $insertSql = "INSERT INTO school_settings (id, school_name, email, phone, address, principal_name, currency, timezone, academic_session, domain)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($insertSql)->execute([
                $schoolName, $schoolEmail, $schoolPhone, $schoolAddress, $principal,
                $currency, $timezone, $sessionStr, $domain
            ]);
        }

        // Insert / Update System Administrator
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$adminSession['email']]);
        $existingAdmin = $stmt->fetch();

        if ($existingAdmin) {
            $stmt = $pdo->prepare("UPDATE admins SET name = ?, password_hash = ?, role = 'system_admin' WHERE id = ?");
            $stmt->execute([$adminSession['name'], $adminSession['password'], $existingAdmin['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO admins (school_id, name, email, password_hash, role) VALUES (1, ?, ?, ?, 'system_admin')");
            $stmt->execute([$adminSession['name'], $adminSession['email'], $adminSession['password']]);
        }

        // 2. Save Encrypted/Structured Local License Cache
        $cacheDir = dirname(__DIR__, 2) . '/config/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $licenseCacheFile = $cacheDir . '/license.json';
        $cacheData = [
            'success' => true,
            'status' => 'active',
            'license_key' => $licenseSession['key'],
            'domain' => $licenseSession['domain'],
            'installation_id' => $installationId,
            'api_key' => $licenseSession['api_key'] ?? '',
            'installation_token' => $licenseSession['installation_token'] ?? '',
            'plan' => $licenseSession['plan'] ?? 'basic',
            'features' => $licenseSession['features'] ?? ['students', 'attendance', 'fees'],
            'features_map' => $licenseSession['features_map'] ?? [],
            'grace_period_days' => (int)($licenseSession['grace_period_days'] ?? 30),
            'expires_at' => $licenseSession['expires_at'] ?? null,
            'last_validated' => date('Y-m-d H:i:s'),
            'details' => $licenseSession['raw_response'] ?? []
        ];
        file_put_contents($licenseCacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));

        // 3. Generate .env Environment File
        $cronSecret = 'cron_' . bin2hex(random_bytes(16));
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $resolvedBaseUrl = str_contains($scriptName, '/EduCore/') ? '/EduCore' : '';

        $envFile = dirname(__DIR__, 2) . '/.env';
        $envContent = "# EduCore System Environment Configuration\n" .
                      "DB_HOST=" . ($dbSession['host'] ?? 'localhost') . "\n" .
                      "DB_NAME=" . ($dbSession['name'] ?? 'educore_school') . "\n" .
                      "DB_USER=" . ($dbSession['user'] ?? 'root') . "\n" .
                      "DB_PASS=\"" . str_replace('"', '\\"', (string)($dbSession['pass'] ?? '')) . "\"\n" .
                      "APP_BASE_URL=\"" . $resolvedBaseUrl . "\"\n" .
                      "INSTALLATION_ID=" . $installationId . "\n" .
                      "EDUCORE_LIVE_URL=" . $liveServerUrl . "\n" .
                      "RELEASE_CHANNEL=stable\n" .
                      "OFFLINE_GRACE_DAYS=" . (int)($licenseSession['grace_period_days'] ?? 30) . "\n" .
                      "AUTO_UPDATE=false\n" .
                      "CRON_SECRET=" . $cronSecret . "\n" .
                      "DEBUG_MODE=false\n" .
                      "SESSION_TIMEOUT=3600\n";
        file_put_contents($envFile, $envContent);

        // 4. Generate installation.lock
        $lockFile = dirname(__DIR__) . '/installation.lock';
        $lockContent = "EduCore Installed on " . date('Y-m-d H:i:s') . "\nDomain: " . $licenseSession['domain'] . "\nInstallation ID: " . $installationId . "\nLicense Key: " . $licenseSession['key'];
        file_put_contents($lockFile, $lockContent);

        // 5. Send initial registration heartbeat ping to Live Server
        try {
            $hbUrl = $liveServerUrl . '/index.php?route=api/v1/heartbeat';
            $hbCh = curl_init($hbUrl);
            curl_setopt_array($hbCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'api_key' => $licenseSession['api_key'] ?? '',
                    'domain' => $domain,
                    'installation_id' => $installationId,
                    'version' => '1.0.0',
                    'php_version' => PHP_VERSION,
                    'active_users' => 1,
                    'timestamp' => time()
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 4
            ]);
            @curl_exec($hbCh);
            @curl_close($hbCh);
        } catch (Throwable $t) {
            // Heartbeat failure during install is non-fatal
        }

        // Clear installer session
        unset($_SESSION['install_db'], $_SESSION['install_school'], $_SESSION['install_admin'], $_SESSION['install_license'], $_SESSION['install_id']);
        $installedSuccess = true;

    } catch (Throwable $e) {
        $errorMsg = "Finalization Error: " . $e->getMessage();
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1 text-white">Step 6: Finish Installation</h4>
    <p class="text-muted small">Review your configuration summary and finalize the node deployment.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger mb-4"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<?php if ($installedSuccess): ?>
    <div class="text-center py-4">
        <div class="mb-3 text-success">
            <i class="bi bi-check-circle-fill display-1"></i>
        </div>
        <h3 class="fw-bold text-white mb-2">EduCore Node Successfully Installed!</h3>
        <p class="text-muted mb-4">Your school node has been activated with EduCore Live, local configuration saved, and initial database migrations applied.</p>

        <a href="../admin" class="btn btn-primary-custom btn-lg">Go to Administrator Dashboard <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
<?php else: ?>
    <div class="installer-section-card mb-4">
        <div class="installer-section-header">Installation Summary</div>
        <div class="card-body">
            <ul class="list-group list-group-flush bg-transparent">
                <li class="list-group-item bg-transparent text-light border-secondary">
                    <strong>Database:</strong> <?= htmlspecialchars($_SESSION['install_db']['host'] ?? 'localhost') ?> / <?= htmlspecialchars($_SESSION['install_db']['name'] ?? '') ?>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary">
                    <strong>School Name:</strong> <?= htmlspecialchars($_SESSION['install_school']['name'] ?? '') ?>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary">
                    <strong>System Administrator:</strong> <?= htmlspecialchars($_SESSION['install_admin']['email'] ?? '') ?>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary">
                    <strong>Installation ID:</strong> <span class="font-monospace text-warning"><?= htmlspecialchars($_SESSION['install_id'] ?? 'Generating on finish...') ?></span>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary">
                    <strong>License Key:</strong> <span class="font-monospace"><?= htmlspecialchars($_SESSION['install_license']['key'] ?? '') ?></span>
                </li>
            </ul>
        </div>
    </div>

    <form method="POST" action="index.php?step=6">
        <div class="installer-footer">
            <a href="index.php?step=5" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
            <button type="submit" name="action" value="complete_installation" class="btn btn-primary-custom">Complete Setup & Write Configuration <i class="bi bi-check2-all ms-1"></i></button>
        </div>
    </form>
<?php endif; ?>