<?php
// EduCore Web Installation Wizard Router
declare(strict_types=1);

ob_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);

// Polyfills for PHP 8 functions on older PHP versions
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_installer_state(): array
{
    $cacheFile = dirname(__DIR__) . '/config/cache/installer_state.json';
    $state = $_SESSION['installer_state'] ?? [];
    if (file_exists($cacheFile)) {
        $raw = @file_get_contents($cacheFile);
        if ($raw) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $state = array_merge($json, $state);
            }
        }
    }
    return $state;
}

function save_installer_state(array $data): void
{
    $cacheDir = dirname(__DIR__) . '/config/cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    $current = get_installer_state();
    $merged = array_merge($current, $data);
    $_SESSION['installer_state'] = $merged;
    @file_put_contents($cacheDir . '/installer_state.json', json_encode($merged, JSON_PRETTY_PRINT));
}

$lockFile = __DIR__ . '/installation.lock';

if (isset($_POST['unlock_installer']) || isset($_GET['unlock'])) {
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
    header('Location: index.php?step=1');
    exit;
}

if (file_exists($lockFile)) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EduCore - Installation Locked</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: #0f172a;
                color: #f8fafc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .lock-card {
                background: #1e293b;
                border: 1px solid #334155;
                border-radius: 20px;
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
                max-width: 520px;
                width: 100%;
                padding: 2.5rem;
                text-align: center;
            }
            .lock-icon {
                width: 76px;
                height: 76px;
                background: rgba(239, 68, 68, 0.15);
                border: 2px solid rgba(239, 68, 68, 0.4);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
                color: #ef4444;
                font-size: 36px;
            }
            .btn-portal {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 12px 20px;
                border-radius: 12px;
                font-weight: 700;
                text-decoration: none;
                transition: all 0.2s ease;
                font-size: 14px;
            }
            .btn-admin { background: #0052cc; color: white; }
            .btn-admin:hover { background: #0040a8; color: white; }
            .btn-reinstall { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
            .btn-reinstall:hover { background: rgba(239, 68, 68, 0.3); color: white; }
        </style>
    </head>
    <body>
        <div class="lock-card">
            <div class="lock-icon">
                <i class="ti ti-lock"></i>
            </div>
            <h2 class="fw-bold mb-2">Installation Locked</h2>
            <p class="text-slate-400 mb-4" style="color: #94a3b8; font-size: 14.5px;">
                EduCore has already been installed on this server. To protect system security, the web installer is currently locked.
            </p>

            <div class="d-grid gap-2 mb-4">
                <a href="../admin" class="btn-portal btn-admin"><i class="ti ti-dashboard"></i> Go to Admin Dashboard</a>
                <div class="d-flex gap-2">
                    <a href="../student" class="btn-portal flex-fill" style="background:#334155; color:white;"><i class="ti ti-school"></i> Student Portal</a>
                    <a href="../parent" class="btn-portal flex-fill" style="background:#334155; color:white;"><i class="ti ti-users"></i> Parent Portal</a>
                </div>
            </div>

            <hr style="border-color: #334155;" class="my-4">

            <form method="POST" onsubmit="return confirm('Removing the lock will allow re-running setup. Continue?');">
                <p class="small mb-3" style="color: #64748b;">Need to re-configure or run setup from scratch?</p>
                <button type="submit" name="unlock_installer" value="1" class="btn-portal btn-reinstall w-100">
                    <i class="ti ti-lock-open"></i> Unlock & Re-run Installation Wizard
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 6) {
    $step = 1;
}

$stepLabels = [
    1 => 'Requirements',
    2 => 'Database',
    3 => 'School Profile',
    4 => 'System Admin',
    5 => 'License Key',
    6 => 'Finish'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduCore Setup Wizard - Step <?= $step ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="installer.css?v=<?= time() ?>">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>EduCore School Management</h1>
        <p>Commercial Self-Hosted Software Installation Wizard</p>
    </div>

    <div class="installer-card">
        <div class="step-indicator">
            <?php foreach ($stepLabels as $num => $label): ?>
                <?php
                $statusClass = '';
                if ($num === $step) {
                    $statusClass = 'active';
                } elseif ($num < $step) {
                    $statusClass = 'completed';
                }
                ?>
                <div class="step-item <?= $statusClass ?>">
                    <div class="step-number">
                        <?php if ($num < $step): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?= $num ?>
                        <?php endif; ?>
                    </div>
                    <span class="step-label"><?= htmlspecialchars($label) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="installer-body">
            <?php
            // Map exact step filenames
            $stepFilesMap = [
                1 => __DIR__ . '/steps/step1_requirements.php',
                2 => __DIR__ . '/steps/step2_database.php',
                3 => __DIR__ . '/steps/step3_school.php',
                4 => __DIR__ . '/steps/step4_admin.php',
                5 => __DIR__ . '/steps/step5_license.php',
                6 => __DIR__ . '/steps/step6_finish.php',
            ];

            try {
                $targetFile = $stepFilesMap[$step] ?? $stepFilesMap[1];
                if (file_exists($targetFile)) {
                    require $targetFile;
                } else {
                    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Installer step view not found.</div>';
                }
            } catch (Throwable $e) {
                echo '<div class="alert alert-danger mb-0"><h5 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i> Installer Server Error</h5><p class="mb-2">A PHP runtime error occurred on your server while loading Step ' . (int)$step . ':</p><code class="d-block p-2 bg-dark text-danger rounded">' . htmlspecialchars($e->getMessage()) . '</code></div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
