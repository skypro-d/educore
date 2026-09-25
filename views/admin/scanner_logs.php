<?php
/**
 * views/admin/scanner_logs.php
 * Admin Attendance Visibility — Complete Scanner Activity Audit Trail
 */
?>

<div class="sa-top-bar d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1"><i class="ti ti-history text-primary me-2"></i> Scanner Activity Logs</h1>
        <p class="text-muted small mb-0">Complete audit trail of all student and staff scanner operations across all gates and kiosks</p>
    </div>
    <div class="sa-top-actions d-flex align-items-center gap-2">
        <a href="<?= url('admin/scanner-assignments') ?>" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="ti ti-users me-1"></i> Scanner Staff Assignments
        </a>
        <a href="<?= url('admin/scanner-logs/export?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-bold">
            <i class="ti ti-file-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('admin/scanner-logs') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Student / Card</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, ID or Token..." value="<?= e($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Station</label>
                <select name="station_id" class="form-select form-select-sm">
                    <option value="">All Stations</option>
                    <?php foreach ($stations ?? [] as $stn): ?>
                        <option value="<?= $stn['id'] ?>" <?= (isset($_GET['station_id']) && (string)$_GET['station_id'] === (string)$stn['id']) ? 'selected' : '' ?>>
                            <?= e($stn['station_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Officer</label>
                <select name="staff_id" class="form-select form-select-sm">
                    <option value="">All Officers</option>
                    <?php foreach ($officers ?? [] as $off): ?>
                        <option value="<?= $off['id'] ?>" <?= (isset($_GET['staff_id']) && (string)$_GET['staff_id'] === (string)$off['id']) ? 'selected' : '' ?>>
                            <?= e($off['first_name'] . ' ' . $off['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Action</label>
                <select name="scan_action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    <option value="check_in" <?= (isset($_GET['scan_action']) && $_GET['scan_action'] === 'check_in') ? 'selected' : '' ?>>Check-In</option>
                    <option value="check_out" <?= (isset($_GET['scan_action']) && $_GET['scan_action'] === 'check_out') ? 'selected' : '' ?>>Check-Out</option>
                    <option value="duplicate_in" <?= (isset($_GET['scan_action']) && $_GET['scan_action'] === 'duplicate_in') ? 'selected' : '' ?>>Duplicate In</option>
                    <option value="duplicate_out" <?= (isset($_GET['scan_action']) && $_GET['scan_action'] === 'duplicate_out') ? 'selected' : '' ?>>Duplicate Out</option>
                    <option value="invalid" <?= (isset($_GET['scan_action']) && $_GET['scan_action'] === 'invalid') ? 'selected' : '' ?>>Invalid Scan</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                    <i class="ti ti-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4" style="width: 140px;">Timestamp</th>
                    <th>Officer</th>
                    <th>Station</th>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>Action</th>
                    <th>Result / Notes</th>
                    <th>Notification</th>
                    <th class="text-end pe-4">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="ti ti-database-off" style="font-size:36px; display:block; margin-bottom:8px;"></i>
                            No scanner activity records match the selected filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $actionBadge = match ($log['scan_action']) {
                                'check_in'     => '<span class="badge bg-success-subtle text-success border border-success-subtle">Check-In</span>',
                                'check_out'    => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Check-Out</span>',
                                'duplicate_in' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Duplicate In</span>',
                                'duplicate_out'=> '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Duplicate Out</span>',
                                'invalid'      => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Invalid Card</span>',
                                default        => '<span class="badge bg-secondary-subtle text-secondary border">' . e($log['scan_action']) . '</span>',
                            };
                        ?>
                        <tr>
                            <td class="ps-4 font-monospace text-muted small">
                                <?= date('M j, g:i:s A', strtotime($log['scanned_at'])) ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">
                                    <?= e(($log['officer_first_name'] ? $log['officer_first_name'] . ' ' . $log['officer_last_name'] : 'System / Admin')) ?>
                                </div>
                                <?php if (!empty($log['officer_staff_id'])): ?>
                                    <div class="text-muted font-monospace" style="font-size:11px;"><?= e($log['officer_staff_id']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= e($log['station_name'] ?: 'Gate Kiosk') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($log['student_name'] ?: 'Unknown') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border font-monospace"><?= e($log['student_admission_no'] ?: ($log['identifier_scanned'] ?: '—')) ?></span>
                            </td>
                            <td>
                                <span class="small"><?= e($log['class_name'] ?: '—') ?></span>
                            </td>
                            <td><?= $actionBadge ?></td>
                            <td>
                                <span class="small text-muted fw-semibold"><?= e($log['response_message'] ?: 'Processed successfully') ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 small">
                                    <span class="badge bg-light text-muted border"><i class="ti ti-message-2"></i> <?= e(ucfirst($log['sms_status'] ?? 'None')) ?></span>
                                    <span class="badge bg-light text-muted border"><i class="ti ti-mail"></i> <?= e(ucfirst($log['email_status'] ?? 'None')) ?></span>
                                </div>
                            </td>
                            <td class="text-end pe-4 font-monospace text-muted small">
                                <?= e($log['ip_address'] ?: '127.0.0.1') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
