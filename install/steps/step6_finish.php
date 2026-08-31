<?php
// Step 6: Finalize Installation & All-in-One Configuration Review
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/updater/MigrationRunner.php';

$errorMsg = '';
$installedSuccess = false;

// 1. Gather all state from persistent cache and session
$state = function_exists('get_installer_state') ? get_installer_state() : [];

$dbState = $_SESSION['install_db'] ?? ($state['install_db'] ?? []);
$schoolState = $_SESSION['install_school'] ?? ($state['install_school'] ?? []);
$adminState = $_SESSION['install_admin'] ?? ($state['install_admin'] ?? []);
$licenseState = $_SESSION['install_license'] ?? ($state['install_license'] ?? []);

// Pre-fill values
$dbHost = $dbState['host'] ?? 'localhost';
$dbName = $dbState['name'] ?? '';
$dbUser = $dbState['user'] ?? '';
$dbPass = $dbState['pass'] ?? '';

// Fallback to .env if dbName is empty
if (empty($dbName)) {
    $envFile = dirname(__DIR__, 2) . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('=', trim($line), 2);
            if (count($parts) === 2) {
                $k = trim($parts[0]);
                $v = trim(trim($parts[1]), '"\'');
                if ($k === 'DB_HOST' && !empty($v)) $dbHost = $v;
                if ($k === 'DB_NAME' && !empty($v)) $dbName = $v;
                if ($k === 'DB_USER' && !empty($v)) $dbUser = $v;
                if ($k === 'DB_PASS') $dbPass = $v;
            }
        }
    }
}

$schoolName = $schoolState['name'] ?? 'My Future My Pride Model School';
$schoolEmail = $schoolState['email'] ?? 'admin@myfuturemyprideschool.com';
$schoolPhone = $schoolState['phone'] ?? '+234 800 000 0000';
$schoolAddress = $schoolState['address'] ?? 'Campus Address, Nigeria';
$principalName = $schoolState['principal'] ?? 'School Principal';
$currency = $schoolState['currency'] ?? 'NGN';
$timezone = $schoolState['timezone'] ?? 'Africa/Lagos';
$academicSession = $schoolState['session'] ?? '2025/2026';

$adminName = $adminState['name'] ?? 'System Administrator';
$adminEmail = $adminState['email'] ?? 'admin@myfuturemyprideschool.com';
$adminPass = $adminState['plain_password'] ?? 'admin123';

$liveServerUrl = $licenseState['server_url'] ?? 'https://educore.skysaveings.com.ng';
$licenseKey = $licenseState['key'] ?? 'SKY-BASIC-1111-2222-3333';
$installationId = $licenseState['installation_id'] ?? ($_SESSION['install_id'] ?? ('edc_inst_' . bin2hex(random_bytes(16))));
$domain = $licenseState['domain'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
if (str_contains($domain, ':')) {
    $domain = explode(':', $domain)[0];
}

// Handle final form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Read directly from submitted POST to ensure zero data loss
        $dbHost = trim($_POST['db_host'] ?? $dbHost);
        $dbName = trim($_POST['db_name'] ?? $dbName);
        $dbUser = trim($_POST['db_user'] ?? $dbUser);
        $dbPass = (string)($_POST['db_pass'] ?? $dbPass);

        $schoolName = trim($_POST['school_name'] ?? $schoolName);
        $schoolEmail = trim($_POST['school_email'] ?? $schoolEmail);
        $schoolPhone = trim($_POST['school_phone'] ?? $schoolPhone);
        $schoolAddress = trim($_POST['school_address'] ?? $schoolAddress);
        $principalName = trim($_POST['principal_name'] ?? $principalName);
        $academicSession = trim($_POST['academic_session'] ?? $academicSession);
        $currency = trim($_POST['currency'] ?? $currency);
        $timezone = trim($_POST['timezone'] ?? $timezone);

        $adminName = trim($_POST['admin_name'] ?? $adminName);
        $adminEmail = trim($_POST['admin_email'] ?? $adminEmail);
        $adminPass = (string)($_POST['admin_pass'] ?? $adminPass);

        $liveServerUrl = rtrim(trim($_POST['license_server_url'] ?? $liveServerUrl), '/');
        $licenseKey = trim($_POST['license_key'] ?? $licenseKey);

        if (empty($dbName) || empty($dbUser)) {
            throw new Exception("Please enter your MySQL Database Name and Database Username.");
        }
        if (empty($schoolName) || empty($schoolEmail)) {
            throw new Exception("Please enter your School Name and Official Email.");
        }
        if (empty($adminEmail) || empty($adminPass)) {
            throw new Exception("Please enter your Administrator Email and Password.");
        }

        // 1. Connect to MySQL Database
        $pdo = null;
        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $exDirect) {
            $dsn = "mysql:host={$dbHost};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
        }

        // 2. Check and import base database tables if not existing
        $hasTables = false;
        try {
            $chk = $pdo->query("SHOW TABLES LIKE 'school_settings'");
            $hasTables = ($chk && $chk->rowCount() > 0);
        } catch (Throwable $t) {
            $hasTables = false;
        }

        if (!$hasTables) {
            $schemaPath = dirname(__DIR__, 2) . '/database/educore_school_schema.sql';
            if (file_exists($schemaPath)) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                $sql = file_get_contents($schemaPath);
                $queries = preg_split('/;\s*[\r\n]+/', $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if ($query !== '' && !str_starts_with($query, '--') && !str_starts_with($query, '/*')) {
                        try {
                            $pdo->exec($query);
                        } catch (PDOException $ex) {}
                    }
                }
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            }
        }

        // Run migrations
        try {
            $migrationRunner = new MigrationRunner($pdo);
            $migrationRunner->recordBaseline('001_initial_schema.sql');
            $migrationRunner->runPending();
        } catch (Throwable $migEx) {}

        // 3. Save / Update school_settings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM school_settings");
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            $updateSql = "UPDATE school_settings SET 
                school_name = ?, email = ?, phone = ?, address = ?, principal_name = ?,
                currency = ?, timezone = ?, academic_session = ?, domain = ? WHERE id = 1";
            $pdo->prepare($updateSql)->execute([
                $schoolName, $schoolEmail, $schoolPhone, $schoolAddress, $principalName,
                $currency, $timezone, $academicSession, $domain
            ]);
        } else {
            $insertSql = "INSERT INTO school_settings (id, school_name, email, phone, address, principal_name, currency, timezone, academic_session, domain)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($insertSql)->execute([
                $schoolName, $schoolEmail, $schoolPhone, $schoolAddress, $principalName,
                $currency, $timezone, $academicSession, $domain
            ]);
        }

        // 4. Save / Update Administrator Account
        $adminPasswordHash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$adminEmail]);
        $existingAdmin = $stmt->fetch();

        if ($existingAdmin) {
            $stmt = $pdo->prepare("UPDATE admins SET name = ?, password_hash = ?, role = 'system_admin' WHERE id = ?");
            $stmt->execute([$adminName, $adminPasswordHash, $existingAdmin['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO admins (school_id, name, email, password_hash, role) VALUES (1, ?, ?, ?, 'system_admin')");
            $stmt->execute([$adminName, $adminEmail, $adminPasswordHash]);
        }

        // 5. Save License Cache
        $cacheDir = dirname(__DIR__, 2) . '/config/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $licenseCacheFile = $cacheDir . '/license.json';
        $cacheData = [
            'success' => true,
            'status' => 'active',
            'license_key' => $licenseKey,
            'domain' => $domain,
            'installation_id' => $installationId,
            'api_key' => $licenseState['api_key'] ?? '',
            'installation_token' => $licenseState['installation_token'] ?? '',
            'plan' => $licenseState['plan'] ?? 'basic',
            'features' => $licenseState['features'] ?? ['students', 'attendance', 'fees'],
            'features_map' => $licenseState['features_map'] ?? [],
            'grace_period_days' => (int)($licenseState['grace_period_days'] ?? 30),
            'expires_at' => $licenseState['expires_at'] ?? null,
            'last_validated' => date('Y-m-d H:i:s'),
            'details' => $licenseState['raw_response'] ?? []
        ];
        file_put_contents($licenseCacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));

        // 6. Generate .env File
        $cronSecret = 'cron_' . bin2hex(random_bytes(16));
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $resolvedBaseUrl = str_contains($scriptName, '/EduCore/') ? '/EduCore' : '';

        $envFile = dirname(__DIR__, 2) . '/.env';
        $envContent = "# EduCore System Environment Configuration\n" .
                      "DB_HOST=" . $dbHost . "\n" .
                      "DB_NAME=" . $dbName . "\n" .
                      "DB_USER=" . $dbUser . "\n" .
                      "DB_PASS=\"" . str_replace('"', '\\"', $dbPass) . "\"\n" .
                      "APP_BASE_URL=\"" . $resolvedBaseUrl . "\"\n" .
                      "INSTALLATION_ID=" . $installationId . "\n" .
                      "EDUCORE_LIVE_URL=" . $liveServerUrl . "\n" .
                      "RELEASE_CHANNEL=stable\n" .
                      "OFFLINE_GRACE_DAYS=" . (int)($licenseState['grace_period_days'] ?? 30) . "\n" .
                      "AUTO_UPDATE=false\n" .
                      "CRON_SECRET=" . $cronSecret . "\n" .
                      "DEBUG_MODE=false\n" .
                      "SESSION_TIMEOUT=3600\n";
        file_put_contents($envFile, $envContent);

        // 7. Generate installation.lock
        $lockFile = dirname(__DIR__) . '/installation.lock';
        $lockContent = "EduCore Installed on " . date('Y-m-d H:i:s') . "\nDomain: " . $domain . "\nInstallation ID: " . $installationId . "\nLicense Key: " . $licenseKey;
        file_put_contents($lockFile, $lockContent);

        // 8. Clean temporary installer state cache
        $cacheFile = dirname(__DIR__, 2) . '/config/cache/installer_state.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
        unset($_SESSION['install_db'], $_SESSION['install_school'], $_SESSION['install_admin'], $_SESSION['install_license'], $_SESSION['install_id'], $_SESSION['installer_state']);

        $installedSuccess = true;

    } catch (Throwable $e) {
        $errorMsg = "Installation Error: " . $e->getMessage();
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1 text-white">Step 6: Review Configuration &amp; Finalize</h4>
    <p class="text-muted small">Verify and adjust your deployment parameters below. Clicking <strong>Complete Setup</strong> will write configuration files, build tables, and activate your school.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger mb-4 d-flex align-items-start gap-2 shadow-sm">
        <i class="bi bi-exclamation-octagon-fill fs-5 mt-1"></i>
        <div><?= htmlspecialchars($errorMsg) ?></div>
    </div>
<?php endif; ?>

<?php if ($installedSuccess): ?>
    <div class="text-center py-5">
        <div class="mb-3 text-success">
            <i class="bi bi-check-circle-fill display-1"></i>
        </div>
        <h3 class="fw-bold text-white mb-2">EduCore Node Successfully Installed!</h3>
        <p class="text-muted mb-4">Your school management system is fully installed, local database configured, and system administrator account created.</p>

        <a href="../admin" class="btn btn-primary-custom btn-lg px-4">Go to Administrator Dashboard <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
<?php else: ?>

    <form method="POST" action="index.php?step=6">

        <!-- 1. MySQL Database Configuration -->
        <div class="installer-section-card mb-4">
            <div class="installer-section-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-database me-2"></i> 1. MySQL Database Connection</span>
                <span class="badge bg-primary">MySQL 8+</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($dbHost) ?>" required placeholder="localhost">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($dbName) ?>" required placeholder="e.g. cpaneluser_educore">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Database Username</label>
                        <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($dbUser) ?>" required placeholder="e.g. cpaneluser_dbuser">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Database Password</label>
                        <div class="input-group">
                            <input type="password" id="finish_db_pass" name="db_pass" class="form-control" value="<?= htmlspecialchars($dbPass) ?>" placeholder="Database User Password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('finish_db_pass', this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. School Profile Details -->
        <div class="installer-section-card mb-4">
            <div class="installer-section-header">
                <i class="bi bi-building me-2"></i> 2. Institution Profile
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">School Name</label>
                        <input type="text" name="school_name" class="form-control" value="<?= htmlspecialchars($schoolName) ?>" required placeholder="School Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">School Official Email</label>
                        <input type="email" name="school_email" class="form-control" value="<?= htmlspecialchars($schoolEmail) ?>" required placeholder="info@school.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Official Phone Number</label>
                        <input type="text" name="school_phone" class="form-control" value="<?= htmlspecialchars($schoolPhone) ?>" required placeholder="+234 800 000 0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Principal / Head of School</label>
                        <input type="text" name="principal_name" class="form-control" value="<?= htmlspecialchars($principalName) ?>" required placeholder="Principal Name">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-light small fw-semibold">Campus Address</label>
                        <input type="text" name="school_address" class="form-control" value="<?= htmlspecialchars($schoolAddress) ?>" required placeholder="Address">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-light small fw-semibold">Academic Session</label>
                        <input type="text" name="academic_session" class="form-control" value="<?= htmlspecialchars($academicSession) ?>" required placeholder="2025/2026">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. System Administrator Account -->
        <div class="installer-section-card mb-4">
            <div class="installer-section-header">
                <i class="bi bi-person-badge me-2"></i> 3. System Administrator Login Account
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-light small fw-semibold">Admin Full Name</label>
                        <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($adminName) ?>" required placeholder="System Administrator">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-light small fw-semibold">Admin Email (Login Username)</label>
                        <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($adminEmail) ?>" required placeholder="admin@school.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-light small fw-semibold">Admin Password</label>
                        <div class="input-group">
                            <input type="password" id="finish_admin_pass" name="admin_pass" class="form-control" value="<?= htmlspecialchars($adminPass) ?>" required placeholder="Minimum 6 characters">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('finish_admin_pass', this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Licensing & Live Server -->
        <div class="installer-section-card mb-4">
            <div class="installer-section-header">
                <i class="bi bi-shield-check me-2"></i> 4. EduCore Live Licensing
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">EduCore Live Server URL</label>
                        <input type="url" name="license_server_url" class="form-control" value="<?= htmlspecialchars($liveServerUrl) ?>" required placeholder="https://educore.skysaveings.com.ng">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-semibold">Activation / License Key</label>
                        <input type="text" name="license_key" class="form-control font-monospace" value="<?= htmlspecialchars($licenseKey) ?>" required placeholder="SKY-BASIC-1111-2222-3333">
                    </div>
                </div>
            </div>
        </div>

        <div class="installer-footer mt-4">
            <a href="index.php?step=5" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
            <button type="submit" name="action" value="complete_installation" class="btn btn-primary-custom btn-lg">
                Complete Setup &amp; Write Configuration <i class="bi bi-check2-all ms-1"></i>
            </button>
        </div>
    </form>
<?php endif; ?>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
}
</script>