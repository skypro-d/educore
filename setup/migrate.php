<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../updater/MigrationRunner.php';

$runner = new MigrationRunner();
$allMigrations = $runner->getAllMigrationFiles();
$executedMigrations = $runner->getExecutedMigrations();
$pending = $runner->getPendingMigrations();

$runMessage = '';
$runSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    $result = $runner->runPending();
    $runSuccess = $result['success'];
    $runMessage = $result['message'];
    if (!empty($result['errors'])) {
        $runMessage .= '<br>' . implode('<br>', $result['errors']);
    }
    
    // Refresh pending list
    $pending = $runner->getPendingMigrations();
    $executedMigrations = $runner->getExecutedMigrations();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Migration Engine — EduCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .migration-card {
            max-width: 720px;
            margin: 50px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 35px;
        }
        .status-badge {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .migration-item {
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background-color: #f8fafc;
            margin-bottom: 10px;
        }
        .btn-primary {
            background-color: #0052cc;
            border-color: #0052cc;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #0040a8;
            border-color: #0040a8;
        }
    </style>
</head>
<body>
<main class="container">
    <div class="migration-card">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff; color:#0052cc; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ti ti-database-cog"></i>
            </div>
            <div>
                <h1 class="h4 mb-0" style="font-weight: 800; color: #0f172a;">Database Migration Engine</h1>
                <p class="text-muted mb-0" style="font-size:13.5px;">Deterministic, version-tracked database schema migrations for EduCore.</p>
            </div>
        </div>

        <?php if ($runSuccess === true): ?>
            <div class="alert alert-success d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="ti ti-circle-check-filled fs-4 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Migration Completed Successfully!</h6>
                    <p class="mb-1" style="font-size:13.5px;"><?= $runMessage ?></p>
                </div>
            </div>
        <?php elseif ($runSuccess === false): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="ti ti-alert-triangle-filled fs-4 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Migration Execution Notice</h6>
                    <p class="mb-0" style="font-size:13.5px;"><?= $runMessage ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($pending)): ?>
            <div class="text-center py-4">
                <div style="font-size:42px; color:#10b981;" class="mb-2">
                    <i class="ti ti-circle-check"></i>
                </div>
                <h5 style="font-weight:700; color:#0f172a;">Database Schema is 100% Up to Date</h5>
                <p class="text-muted small">All <?= count($executedMigrations) ?> versioned migration scripts have been applied to this school node.</p>
                <div class="mt-3">
                    <a href="<?= url('admin') ?>" class="btn btn-outline-secondary px-4 py-2" style="border-radius:8px; font-weight:600; font-size:14px;">Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <h6 class="mb-3 fw-bold text-dark">Pending Database Migrations (<?= count($pending) ?>)</h6>
            <div class="mb-4">
                <?php foreach ($pending as $file): ?>
                    <div class="migration-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-dark font-monospace" style="font-size:13.5px;"><?= htmlspecialchars($file) ?></span>
                        </div>
                        <span class="badge bg-warning text-dark status-badge">Pending</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="" class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <a href="<?= url('admin') ?>" class="text-secondary text-decoration-none" style="font-size:13.5px;"><i class="ti ti-arrow-left"></i> Skip for now</a>
                <button type="submit" name="run_migrations" value="1" class="btn btn-primary">
                    <i class="ti ti-player-play me-1"></i> Run Pending Migrations
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
