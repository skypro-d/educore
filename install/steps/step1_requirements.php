<?php
// Step 1: Server Requirement Check
declare(strict_types=1);

$phpVersion = PHP_VERSION;
$phpPass = version_compare($phpVersion, '8.3.0', '>=');

$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'curl' => extension_loaded('curl'),
    'openssl' => extension_loaded('openssl'),
    'json' => extension_loaded('json'),
    'mbstring' => extension_loaded('mbstring'),
    'gd' => extension_loaded('gd'),
    'zip' => extension_loaded('zip')
];

$allExtPass = !in_array(false, $extensions, true);

$rootDir = dirname(__DIR__, 2);

function checkDirectoryWritable(string $dirPath): bool {
    try {
        if (!file_exists($dirPath)) {
            @mkdir($dirPath, 0755, true);
        }
        return file_exists($dirPath) && is_writable($dirPath);
    } catch (Throwable $t) {
        return false;
    }
}

$directories = [
    'config' => checkDirectoryWritable($rootDir . '/config'),
    'config/cache' => checkDirectoryWritable($rootDir . '/config/cache'),
    'uploads' => checkDirectoryWritable($rootDir . '/uploads'),
    'logs' => checkDirectoryWritable($rootDir . '/logs'),
    'storage' => checkDirectoryWritable($rootDir . '/storage'),
    'storage/backups' => checkDirectoryWritable($rootDir . '/storage/backups'),
    'storage/updates' => checkDirectoryWritable($rootDir . '/storage/updates'),
];

$allDirPass = !in_array(false, $directories, true);

// Disk space check (min 100 MB free)
$freeBytes = @disk_free_space($rootDir);
$freeMb = $freeBytes !== false ? round($freeBytes / (1024 * 1024), 2) : 500;
$diskPass = $freeMb >= 100;

$canProceed = $phpPass && $allExtPass && $allDirPass && $diskPass;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($canProceed) {
        if (!headers_sent()) {
            header('Location: index.php?step=2');
        }
        echo '<script>window.location.href="index.php?step=2";</script>';
        exit;
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1 text-white">Step 1: Server Requirements</h4>
    <p class="text-muted small">Please verify that your server meets the minimum software specifications and directory write permissions.</p>
</div>

<div class="installer-section-card mb-4">
    <div class="installer-section-header">
        PHP Engine Compatibility
    </div>
    <div class="p-0">
        <div class="requirement-row">
            <div>
                <strong class="text-white">PHP 8.3+ Required</strong>
                <div class="text-muted small">Current Version: <?= htmlspecialchars($phpVersion) ?></div>
            </div>
            <div>
                <?php if ($phpPass): ?>
                    <span class="badge-pass"><i class="bi bi-check-circle-fill me-1"></i> Pass</span>
                <?php else: ?>
                    <span class="badge-fail"><i class="bi bi-x-circle-fill me-1"></i> Requires PHP 8.3+</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="requirement-row">
            <div>
                <strong class="text-white">Storage Disk Space</strong>
                <div class="text-muted small">Free Disk Space: <?= $freeMb ?> MB (Min: 100 MB)</div>
            </div>
            <div>
                <?php if ($diskPass): ?>
                    <span class="badge-pass"><i class="bi bi-check-circle-fill me-1"></i> Pass</span>
                <?php else: ?>
                    <span class="badge-fail"><i class="bi bi-x-circle-fill me-1"></i> Insufficient Space</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="installer-section-card mb-4">
    <div class="installer-section-header">
        Required PHP Extensions
    </div>
    <div class="p-0">
        <?php foreach ($extensions as $ext => $pass): ?>
            <div class="requirement-row">
                <div class="text-uppercase fw-bold text-white"><?= htmlspecialchars($ext) ?> Extension</div>
                <div>
                    <?php if ($pass): ?>
                        <span class="badge-pass"><i class="bi bi-check-circle-fill me-1"></i> Installed</span>
                    <?php else: ?>
                        <span class="badge-fail"><i class="bi bi-x-circle-fill me-1"></i> Missing</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="installer-section-card mb-4">
    <div class="installer-section-header">
        Directory Write Permissions
    </div>
    <div class="p-0">
        <?php foreach ($directories as $dir => $writable): ?>
            <div class="requirement-row">
                <div class="font-monospace text-white"><?= htmlspecialchars($dir) ?>/</div>
                <div>
                    <?php if ($writable): ?>
                        <span class="badge-pass"><i class="bi bi-check-circle-fill me-1"></i> Writable</span>
                    <?php else: ?>
                        <span class="badge-fail"><i class="bi bi-x-circle-fill me-1"></i> Not Writable</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<form method="POST" action="index.php?step=1">
    <div class="installer-footer">
        <button type="button" class="btn btn-secondary-custom" disabled>Back</button>
        <?php if ($canProceed): ?>
            <button type="submit" name="action" value="proceed_step1" class="btn btn-primary-custom">Continue to Database Setup <i class="bi bi-arrow-right ms-1"></i></button>
        <?php else: ?>
            <button type="button" class="btn btn-danger" onclick="window.location.reload();">Re-check Requirements</button>
        <?php endif; ?>
    </div>
</form>
