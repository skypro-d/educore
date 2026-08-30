<?php
// views/admin/exit_logs.php
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.5rem; letter-spacing: -0.02em;">
            <i class="ti ti-history text-primary me-2"></i>Student Exit Logs &amp; Movement Audit
        </h3>
        <p class="text-muted mb-0 small">Real-time gate checkout logs, early dismissal records, pickup authorizations, and parent SMS delivery tracking.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
        <a href="<?= url('admin/exit-scanner') ?>" class="btn btn-primary btn-sm rounded-3 fw-semibold">
            <i class="ti ti-door-exit me-1"></i> Open Gate Scanner
        </a>
        <a href="<?= url('admin/exit-logs/export?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm rounded-3 fw-semibold">
            <i class="ti ti-file-spreadsheet me-1"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Quick Metric KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase letter-spacing-1">Exits Today</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format($totalToday) ?></h3>
                </div>
                <div class="p-3 rounded-circle bg-primary-subtle text-primary">
                    <i class="ti ti-door-exit fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase letter-spacing-1">Early Dismissals Today</div>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= number_format($earlyToday) ?></h3>
                </div>
                <div class="p-3 rounded-circle bg-warning-subtle text-warning">
                    <i class="ti ti-clock-exclamation fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase letter-spacing-1">Failed SMS Notifications</div>
                    <h3 class="fw-bold <?= $smsFailed > 0 ? 'text-danger' : 'text-success' ?> mb-0 mt-1"><?= number_format($smsFailed) ?></h3>
                </div>
                <div class="p-3 rounded-circle <?= $smsFailed > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>">
                    <i class="ti <?= $smsFailed > 0 ? 'ti-message-x' : 'ti-message-check' ?> fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="<?= url('admin/exit-logs') ?>" class="row g-3 align-items-end">
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">Class</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">School Gate</label>
                <select name="gate_id" class="form-select form-select-sm">
                    <option value="">All Gates</option>
                    <?php foreach ($gates as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $gateId == $g['id'] ? 'selected' : '' ?>><?= e($g['gate_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">Exit Type</label>
                <select name="exit_type" class="form-select form-select-sm">
                    <option value="">All Exit Types</option>
                    <option value="normal" <?= $exitType === 'normal' ? 'selected' : '' ?>>Normal Dismissal</option>
                    <option value="early" <?= $exitType === 'early' ? 'selected' : '' ?>>Early Exit</option>
                    <option value="manual" <?= $exitType === 'manual' ? 'selected' : '' ?>>Manual Override</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">SMS Status</label>
                <select name="sms_status" class="form-select form-select-sm">
                    <option value="">All SMS Status</option>
                    <option value="sent" <?= $smsStatus === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= $smsStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="disabled" <?= $smsStatus === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <div class="col-12 col-lg-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by student name, admission number, or pickup person..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="ti ti-filter me-1"></i> Apply Filter
                </button>
                <a href="<?= url('admin/exit-logs') ?>" class="btn btn-light btn-sm px-3 text-muted">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Data Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="ti ti-list-check text-primary me-2"></i>Exit Records (<?= number_format($totalLogs) ?> total)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Student</th>
                    <th>Class</th>
                    <th>Date &amp; Time</th>
                    <th>Exit Type</th>
                    <th>Reason / Notes</th>
                    <th>Pickup Person</th>
                    <th>Gate &amp; Staff</th>
                    <th>Parent SMS</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="ti ti-folder-off d-block mb-2 fs-2 text-secondary opacity-50"></i>
                            No exit logs matched your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar rounded-circle overflow-hidden flex-shrink-0" style="width: 38px; height: 38px; background: #e2e8f0;">
                                        <?php if ($log['passport_photo']): ?>
                                            <img src="<?= url('uploads/' . $log['passport_photo']) ?>" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold small">
                                                <?= strtoupper(substr($log['first_name'], 0, 1) . substr($log['last_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="<?= url('admin/applications/' . $log['student_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= e($log['first_name'] . ' ' . $log['last_name']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;">
                                            ID: <?= e($log['admission_number'] ?: $log['application_number']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= e($log['class_name'] ?: 'General') ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= date('M j, Y', strtotime($log['exit_date'])) ?></div>
                                <div class="text-muted small"><?= date('g:i:s A', strtotime($log['exit_time'])) ?></div>
                            </td>
                            <td>
                                <?php if ($log['exit_type'] === 'early'): ?>
                                    <span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1 rounded-pill">
                                        <i class="ti ti-clock-exclamation me-1"></i> Early Exit
                                    </span>
                                <?php elseif ($log['exit_type'] === 'manual'): ?>
                                    <span class="badge bg-info-subtle text-info fw-bold px-2 py-1 rounded-pill">
                                        <i class="ti ti-manual-gearbox me-1"></i> Manual
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill">
                                        <i class="ti ti-check me-1"></i> Normal
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark fw-medium"><?= e($log['exit_reason'] ?: '—') ?></div>
                                <?php if ($log['exit_reason_notes']): ?>
                                    <div class="text-muted small" style="font-size: 11px;"><?= e($log['exit_reason_notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['pickup_person_name']): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="ti ti-user-check text-primary me-1"></i><?= e($log['pickup_person_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($log['gate_name'] ?: 'Gate') ?></div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    <i class="ti ti-user me-1"></i><?= e($log['staff_name'] ?: ($log['scanned_by_name'] ?: 'Staff')) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($log['sms_status'] === 'sent'): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill">
                                        <i class="ti ti-check me-1"></i> Sent
                                    </span>
                                <?php elseif ($log['sms_status'] === 'failed'): ?>
                                    <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1 rounded-pill">
                                        <i class="ti ti-x me-1"></i> Failed
                                    </span>
                                <?php elseif ($log['sms_status'] === 'disabled'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">Disabled</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill"><?= ucfirst($log['sms_status']) ?></span>
                                <?php endif; ?>
                                <div class="text-muted mt-1" style="font-size: 10px;"><?= mask_phone($log['parent_phone']) ?></div>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($log['sms_status'] === 'failed'): ?>
                                    <form method="POST" action="<?= url('admin/exit-logs/sms-retry') ?>" class="d-inline" onsubmit="return confirm('Resend SMS notification to parent?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="exit_log_id" value="<?= $log['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Retry sending SMS">
                                            <i class="ti ti-rotate-clockwise me-1"></i> Retry SMS
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="<?= url('admin/applications/' . $log['student_id']) ?>" class="btn btn-light btn-sm py-1 px-2 text-muted" title="View Student Profile">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing <?= ($offset + 1) ?> to <?= min($totalLogs, $offset + $perPage) ?> of <?= $totalLogs ?> records
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('admin/exit-logs?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/exit-logs?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('admin/exit-logs?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
