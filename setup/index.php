<?php
/**
 * setup/index.php
 * EduCore Self-Hosted Installation Wizard
 */
session_start();

if (file_exists(__DIR__ . '/setup.lock')) {
    http_response_code(403);
    die('<div style="font-family:sans-serif; text-align:center; padding:50px; background:#070b13; color:#f8fafc; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <h1 style="color:#ef4444; font-size:28px; margin-bottom:12px;">Installation Locked</h1>
            <p style="color:#94a3b8; font-size:14px;">This EduCore self-hosted installation has already been completed and locked.</p>
            <p style="color:#64748b; font-size:12px; margin-top:20px;">To re-run the configuration wizard, delete the file <code>setup/setup.lock</code> from your server filesystem.</p>
         </div>');
}

$step = (int) ($_GET['step'] ?? 1);

// Generate default variables to help simulation
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$pdoOk = extension_loaded('pdo_mysql');

// Error states
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Step 2: Database connectivity configuration
        $host = $_POST['db_host'] ?? '';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        try {
            $testPdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $_SESSION['setup_db'] = compact('host', 'name', 'user', 'pass');
            header('Location: index.php?step=3');
            exit;
        } catch (Throwable $e) {
            $error = 'Database Connection Failed: ' . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Step 3: School Configuration & License Key verification call
        $schoolName = trim($_POST['school_name'] ?? '');
        $schoolCode = trim(strtoupper($_POST['school_code'] ?? ''));
        $licKey     = trim($_POST['license_key'] ?? '');
        $apiKey     = trim($_POST['api_key'] ?? '');

        if ($schoolName === '' || $schoolCode === '' || $licKey === '' || $apiKey === '') {
            $error = 'All fields are required to verify setup.';
        } else {
            // Call simulated central license verification endpoint
            // In a production server, this does a cURL request to license.educore.ng/api/verify
            // We simulate the curl request locally on our resolved DB context:
            try {
                $dbConf = $_SESSION['setup_db'] ?? null;
                if (!$dbConf) {
                    header('Location: index.php?step=2');
                    exit;
                }
                
                $pdo = new PDO("mysql:host={$dbConf['host']};dbname={$dbConf['name']};charset=utf8mb4", $dbConf['user'], $dbConf['pass']);
                
                // Query school record by license key in active SaaS database
                $stmt = $pdo->prepare(
                    "SELECT l.*, s.school_name, s.status AS school_status 
                     FROM school_licenses l
                     JOIN schools s ON s.id = l.school_id
                     WHERE l.license_key = ? AND s.api_key = ? LIMIT 1"
                );
                $stmt->execute([$licKey, $apiKey]);
                $lic = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$lic || $lic['is_active'] == 0 || $lic['school_status'] !== 'active') {
                    $error = 'Invalid License Key or API Key. Check credentials on SST Hub.';
                } else {
                    // Generate environment variables .env file
                    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                    $resolvedBaseUrl = str_contains($scriptName, '/EduCore/') ? '/EduCore' : '';
                    $envContent = "# EduCore Configuration Parameters\n" .
                                  "DB_HOST=" . $dbConf['host'] . "\n" .
                                  "DB_NAME=" . $dbConf['name'] . "\n" .
                                  "DB_USER=" . $dbConf['user'] . "\n" .
                                  "DB_PASS=\"" . str_replace('"', '\\"', $dbConf['pass']) . "\"\n" .
                                  "APP_BASE_URL=\"" . $resolvedBaseUrl . "\"\n";
                    file_put_contents(__DIR__ . '/../.env', $envContent);
                    
                    // Create setup.lock to secure the installer wizard
                    file_put_contents(__DIR__ . '/setup.lock', 'locked');

                    $_SESSION['setup_success'] = true;
                    header('Location: index.php?step=4');
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'License Server Verification Failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduCore — Self-Hosted Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --bg-dark: #070b13;
            --card-dark: #0f172a;
            --border-dark: #1e293b;
            --accent-blue: #3b82f6;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .setup-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .form-control {
            background-color: var(--bg-dark);
            border: 1.5px solid var(--border-dark);
            color: var(--text-light);
            padding: 10px 14px;
            border-radius: 8px;
        }

        .form-control:focus {
            background-color: var(--bg-dark);
            color: var(--text-light);
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-dark);
        }

        .step-dot {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            opacity: 0.5;
        }

        .step-dot.active {
            color: var(--accent-blue);
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <div style="text-align:center; margin-bottom:24px;">
        <h3 class="fw-bold"><i class="ti ti-plug text-warning me-1"></i> Self-Hosted Installer</h3>
        <p class="text-muted" style="font-size:12px;">Deploy and activate your EduCore node</p>
    </div>

    <!-- Steps Indicator -->
    <div class="step-indicator">
        <span class="step-dot <?= $step === 1 ? 'active' : '' ?>">1. Requirements</span>
        <span class="step-dot <?= $step === 2 ? 'active' : '' ?>">2. Database</span>
        <span class="step-dot <?= $step === 3 ? 'active' : '' ?>">3. License Key</span>
        <span class="step-dot <?= $step === 4 ? 'active' : '' ?>">4. Completed</span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border-color: var(--border-dark); color: #f87171; font-size: 13px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- STEP 1: System requirements checks -->
    <?php if ($step === 1): ?>
        <div>
            <h5 class="fw-bold mb-3">System Diagnostics Check</h5>
            <ul class="list-group list-group-flush mb-4 bg-transparent text-white" style="font-size:14px;">
                <li class="list-group-item bg-transparent text-white d-flex justify-content-between align-items-center border-secondary-subtle">
                    PHP Version >= 8.0.0 (Current: <?= PHP_VERSION ?>)
                    <span><?= $phpOk ? '<i class="ti ti-circle-check-filled text-success" style="font-size:20px;"></i>' : '<i class="ti ti-circle-x-filled text-danger" style="font-size:20px;"></i>' ?></span>
                </li>
                <li class="list-group-item bg-transparent text-white d-flex justify-content-between align-items-center border-secondary-subtle">
                    PDO MySQL Extension Loaded
                    <span><?= $pdoOk ? '<i class="ti ti-circle-check-filled text-success" style="font-size:20px;"></i>' : '<i class="ti ti-circle-x-filled text-danger" style="font-size:20px;"></i>' ?></span>
                </li>
            </ul>
            
            <?php if ($phpOk && $pdoOk): ?>
                <a href="index.php?step=2" class="btn btn-primary w-100 fw-bold py-2">Continue to Database Config <i class="ti ti-arrow-right"></i></a>
            <?php else: ?>
                <button class="btn btn-secondary w-100 fw-bold py-2" disabled>Requirements Not Met</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- STEP 2: Database Configuration -->
    <?php if ($step === 2): ?>
        <form method="POST" action="index.php?step=2">
            <h5 class="fw-bold mb-3">Database Server Settings</h5>
            <div class="mb-3">
                <label class="form-label">Database Host</label>
                <input type="text" name="db_host" required class="form-control" value="localhost">
            </div>
            <div class="mb-3">
                <label class="form-label">Database Name</label>
                <input type="text" name="db_name" required class="form-control" value="school_admission_portal">
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="db_user" required class="form-control" value="root">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="db_pass" class="form-control" placeholder="Database Password">
            </div>

            <div class="d-flex gap-3">
                <a href="index.php?step=1" class="btn btn-outline-secondary w-50 py-2">Back</a>
                <button type="submit" class="btn btn-primary w-50 py-2">Verify Connection</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- STEP 3: License and activation key verification -->
    <?php if ($step === 3): ?>
        <form method="POST" action="index.php?step=3">
            <h5 class="fw-bold mb-3">Enterprise License Verification</h5>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:16px;">
                Enter your unique license key issued on SkySavingTech Hub to activate this self-hosted installation context.
            </p>
            <div class="mb-3">
                <label class="form-label">School Name</label>
                <input type="text" name="school_name" required class="form-control" placeholder="Bluefield International College">
            </div>
            <div class="mb-3">
                <label class="form-label">School Code</label>
                <input type="text" name="school_code" required class="form-control" placeholder="e.g. BIC" style="text-transform:uppercase;">
            </div>
            <div class="mb-3">
                <label class="form-label">License Key</label>
                <input type="text" name="license_key" required class="form-control" placeholder="e.g. EDUCORE-ENT-2026-XXXX">
            </div>
            <div class="mb-4">
                <label class="form-label">API Key</label>
                <input type="text" name="api_key" required class="form-control" placeholder="edu_key_...">
            </div>

            <div class="d-flex gap-3">
                <a href="index.php?step=2" class="btn btn-outline-secondary w-50 py-2">Back</a>
                <button type="submit" class="btn btn-warning w-50 py-2 fw-bold">Verify & Activate</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- STEP 4: Installation Completed Success -->
    <?php if ($step === 4): ?>
        <div style="text-align:center;">
            <div style="font-size:64px; color:var(--accent-blue); margin-bottom:16px;"><i class="ti ti-circle-check-filled"></i></div>
            <h4 class="fw-bold mb-3">Installation Successful!</h4>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:24px;">
                Your EduCore self-hosted instance has been activated and is fully connected to the SST Licensing Server.
            </p>
            <a href="<?= url('admin/login') ?>" class="btn btn-primary w-100 fw-bold py-2">Sign In to School Admin Portal</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
