<?php
/**
 * Admin Portal — Remote Software Updates & Fleet Health
 *
 * @var array $updateInfo
 * @var array $history
 * @var array $pendingMigrations
 * @var array $executedMigrations
 * @var array $licenseData
 * @var array $graceInfo
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">System Updates &amp; Node Health</h1>
        <p class="text-muted small mb-0">Manage automatic updates, database migrations, backups, and licensing synchronization with EduCore Live.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('admin/updates?check=now') ?>" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-3">
            <i class="ti ti-refresh me-1"></i> Check for Updates
        </a>
        <form method="POST" action="<?= url('admin/updates') ?>" onsubmit="return confirm('Create an immediate full database and codebase snapshot backup?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_backup">
            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm rounded-3">
                <i class="ti ti-archive me-1"></i> Backup System
            </button>
        </form>
    </div>
</div>

<?= ApiKeyService::renderOfflineNotice() ?>

<!-- Top Metrics Row -->
<div class="row g-3 mb-4">
    <!-- Version Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Current Version</span>
                <span class="badge bg-primary-subtle text-primary font-monospace fs-7">v<?= EDUCORE_VERSION ?></span>
            </div>
            <h4 class="fw-bold text-dark mb-1 font-monospace">EduCore v<?= EDUCORE_VERSION ?></h4>
            <div class="text-muted small">Channel: <strong class="text-dark text-capitalize"><?= defined('RELEASE_CHANNEL') ? RELEASE_CHANNEL : 'stable' ?></strong></div>
        </div>
    </div>

    <!-- License Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">EduCore Live</span>
                <?php if (($licenseData['status'] ?? '') === 'active'): ?>
                    <span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check-filled me-1"></i> Active</span>
                <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning"><i class="ti ti-alert-circle me-1"></i> <?= htmlspecialchars($licenseData['status'] ?? 'Offline') ?></span>
                <?php endif; ?>
            </div>
            <h4 class="fw-bold text-dark mb-1 text-capitalize"><?= htmlspecialchars($licenseData['plan'] ?? 'Basic') ?> Plan</h4>
            <div class="text-muted small">Expiry: <?= !empty($licenseData['expires_at']) ? date('d M Y', strtotime((string)$licenseData['expires_at'])) : 'Perpetual' ?></div>
        </div>
    </div>

    <!-- Node ID Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">School Node ID</span>
                <i class="ti ti-server text-muted fs-5"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1 font-monospace text-truncate" title="<?= htmlspecialchars(ApiKeyService::getInstallationId()) ?>">
                <?= htmlspecialchars(ApiKeyService::getInstallationId()) ?>
            </h6>
            <div class="text-muted small">Domain: <strong class="text-dark"><?= htmlspecialchars($licenseData['domain'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?></strong></div>
        </div>
    </div>

    <!-- Migrations Card -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Database Schema</span>
                <?php if (empty($pendingMigrations)): ?>
                    <span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i> Up to date</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle me-1"></i> <?= count($pendingMigrations) ?> Pending</span>
                <?php endif; ?>
            </div>
            <h4 class="fw-bold text-dark mb-1"><?= count($executedMigrations) ?> Applied</h4>
            <div class="text-muted small"><?= empty($pendingMigrations) ? 'All schema scripts active' : count($pendingMigrations) . ' update(s) ready to run' ?></div>
        </div>
    </div>
</div>

<!-- Pending Migrations Action Banner (if any) -->
<?php if (!empty($pendingMigrations)): ?>
    <div class="card border-warning border-2 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-inline-flex align-items-center px-2 py-1 rounded bg-warning bg-opacity-10 text-warning-emphasis fw-bold small mb-2">
                        <i class="ti ti-database-cog me-1"></i> ACTION REQUIRED
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Database Migrations Pending Execution (<?= count($pendingMigrations) ?>)</h5>
                    <p class="text-muted small mb-0">Your codebase has new database migrations that have not yet been applied to your school database:</p>
                    <ul class="mb-0 mt-2 ps-3 small text-muted font-monospace">
                        <?php foreach ($pendingMigrations as $pMig): ?>
                            <li><strong class="text-dark"><?= htmlspecialchars($pMig) ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <form method="POST" action="<?= url('admin/updates') ?>" onsubmit="return confirm('Execute all pending database migrations now?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="run_migrations">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 py-2 shadow-sm rounded-3">
                            <i class="ti ti-player-play me-1"></i> Apply Migrations Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Update Available Banner (if newer release exists) -->
<?php if (!empty($updateInfo['update_available'])): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #fff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 mb-3">
                        <span class="badge bg-warning text-dark me-2">NEW RELEASE</span>
                        <span class="small fw-bold">EduCore v<?= htmlspecialchars($updateInfo['latest_version'] ?? '') ?> is now available!</span>
                    </div>
                    <h3 class="fw-bold mb-2">Software Upgrade Available (v<?= htmlspecialchars($updateInfo['latest_version'] ?? '') ?>)</h3>
                    <p class="text-light text-opacity-75 mb-3" style="max-width: 650px;">
                        <?= nl2br(htmlspecialchars($updateInfo['release_notes'] ?? 'Contains important feature enhancements, bug fixes, and security improvements.')) ?>
                    </p>
                    <div class="d-flex flex-wrap gap-3 text-light text-opacity-75 small">
                        <div><i class="ti ti-calendar me-1"></i> Released: <?= htmlspecialchars($updateInfo['release_date'] ?? date('Y-m-d')) ?></div>
                        <div><i class="ti ti-code me-1"></i> Min PHP: <?= htmlspecialchars($updateInfo['minimum_php_version'] ?? '8.3.0') ?></div>
                        <div><i class="ti ti-shield-check me-1"></i> Verified SHA256 Checksum</div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <form method="POST" action="<?= url('admin/updates') ?>" onsubmit="return confirm('Upgrade EduCore to v<?= htmlspecialchars($updateInfo['latest_version']) ?>? System will automatically create a full backup and apply all migrations.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="apply_update">
                        <input type="hidden" name="target_version" value="<?= htmlspecialchars($updateInfo['latest_version'] ?? '') ?>">
                        <input type="hidden" name="download_url" value="<?= htmlspecialchars($updateInfo['download_url'] ?? '') ?>">
                        <input type="hidden" name="sha256" value="<?= htmlspecialchars($updateInfo['sha256'] ?? '') ?>">
                        <input type="hidden" name="signature" value="<?= htmlspecialchars($updateInfo['signature'] ?? '') ?>">

                        <button type="submit" class="btn btn-warning btn-lg px-4 py-3 fw-bold rounded-3 shadow-lg">
                            <i class="ti ti-cloud-download me-2 fs-5"></i> Upgrade to v<?= htmlspecialchars($updateInfo['latest_version']) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px; height:48px; border-radius:12px; background:#ecfdf5; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ti ti-circle-check"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Your School Installation is Up to Date</h5>
                <p class="text-muted small mb-0">Running the latest release <strong>v<?= EDUCORE_VERSION ?></strong> on the <span class="badge bg-secondary font-monospace"><?= defined('RELEASE_CHANNEL') ? RELEASE_CHANNEL : 'stable' ?></span> channel.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Database Schema Migrations Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-1">Database Schema Ledger</h5>
            <p class="text-muted small mb-0">All versioned schema migrations registered and applied to this installation.</p>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary font-monospace"><?= count($executedMigrations) ?> Applied</span>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Migration Script</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Batch</th>
                        <th class="border-0 text-end">Executed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $allFiles = (new MigrationRunner())->getAllMigrationFiles();
                    if (empty($allFiles)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">No migration scripts found in database/migrations/.</td>
                        </tr>
                    <?php else:
                        foreach ($allFiles as $idx => $mFile):
                            $isApplied = in_array($mFile, $executedMigrations, true);
                    ?>
                        <tr>
                            <td class="text-muted small font-monospace"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-dark font-monospace"><?= htmlspecialchars($mFile) ?></strong>
                            </td>
                            <td>
                                <?php if ($isApplied): ?>
                                    <span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i> Applied</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted font-monospace"><?= $isApplied ? 'Batch 1' : '—' ?></td>
                            <td class="text-end small text-muted font-monospace"><?= $isApplied ? 'Recorded' : '<span class="text-warning">Awaiting Execution</span>' ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update & Deployment History Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-1">Upgrade &amp; Maintenance Audit History</h5>
            <p class="text-muted small mb-0">Audit trail of all software updates, migrations, and automated backups on this school node.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Date &amp; Time</th>
                        <th class="border-0">Version Transition</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Backup Snapshot</th>
                        <th class="border-0">Executed Migrations</th>
                        <th class="border-0 text-end">Initiated By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No previous update records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td class="text-muted small font-monospace"><?= htmlspecialchars($row['started_at'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-secondary font-monospace">v<?= htmlspecialchars($row['from_version'] ?? '1.0.0') ?></span>
                                    <i class="ti ti-arrow-right text-muted mx-1"></i>
                                    <span class="badge bg-primary font-monospace">v<?= htmlspecialchars($row['to_version'] ?? '1.0.0') ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'completed'): ?>
                                        <span class="badge bg-success"><i class="ti ti-check me-1"></i> Completed</span>
                                    <?php elseif ($row['status'] === 'rolled_back'): ?>
                                        <span class="badge bg-warning text-dark"><i class="ti ti-rotate-clockwise me-1"></i> Rolled Back</span>
                                    <?php elseif ($row['status'] === 'failed'): ?>
                                        <span class="badge bg-danger"><i class="ti ti-x me-1"></i> Failed</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($row['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?php if (!empty($row['backup_path'])): ?>
                                        <i class="ti ti-archive me-1"></i><?= htmlspecialchars(basename($row['backup_path'])) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted" style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($row['executed_migrations'] ?: 'None') ?>
                                </td>
                                <td class="text-end small text-muted font-monospace"><?= htmlspecialchars($row['initiated_by'] ?? 'admin') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
