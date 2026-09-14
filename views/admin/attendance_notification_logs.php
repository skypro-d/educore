<?php
/**
 * views/admin/attendance_notification_logs.php
 * Attendance Notification History & Multi-Channel Audit Logs
 */
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Attendance Notification Logs</h1>
        <p class="text-muted small mb-0">Track and manage automated parent notifications across SMS, Email, and WhatsApp.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/attendance-settings?tab=notifications') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
            <i class="ti ti-settings"></i> Notification Settings
        </a>
        <a href="<?= url('admin/attendance') ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="ti ti-calendar-check"></i> Daily Attendance
        </a>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Alerts</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format((int) ($stats['total'] ?? 0)) ?></h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="ti ti-bell-ringing fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">SMS Dispatches</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format((int) ($stats['sms'] ?? 0)) ?></h3>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="ti ti-message-dots fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Email Alerts</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format((int) ($stats['email'] ?? 0)) ?></h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="ti ti-mail fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">WhatsApp Alerts</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format((int) ($stats['whatsapp'] ?? 0)) ?></h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="ti ti-brand-whatsapp fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('admin/attendance-notification-logs') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Search Student / Recipient</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" name="student" class="form-control border-start-0" placeholder="Name, ID, or contact..." value="<?= e($studentSearch) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1">Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="<?= e($dateFilter) ?>">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1">Event</label>
                <select name="event" class="form-select form-select-sm">
                    <option value="">All Events</option>
                    <option value="check_in" <?= $eventFilter === 'check_in' ? 'selected' : '' ?>>Check In</option>
                    <option value="check_out" <?= $eventFilter === 'check_out' ? 'selected' : '' ?>>Check Out</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1">Channel</label>
                <select name="channel" class="form-select form-select-sm">
                    <option value="">All Channels</option>
                    <option value="sms" <?= $channelFilter === 'sms' ? 'selected' : '' ?>>SMS</option>
                    <option value="email" <?= $channelFilter === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="whatsapp" <?= $channelFilter === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="sent" <?= $statusFilter === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-filter"></i></button>
                <?php if ($studentSearch !== '' || $dateFilter !== '' || $eventFilter !== '' || $channelFilter !== '' || $statusFilter !== ''): ?>
                    <a href="<?= url('admin/attendance-notification-logs') ?>" class="btn btn-light btn-sm" title="Clear Filters"><i class="ti ti-x"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; color: #64748b;">
                <tr>
                    <th class="ps-3">Student</th>
                    <th>Parent / Guardian</th>
                    <th>Event</th>
                    <th>Channel</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Date &amp; Time</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="ti ti-inbox fs-1 d-block mb-2 opacity-50"></i>
                            <div class="fw-semibold">No attendance notifications found</div>
                            <small>Trigger student check-in or exit scans to generate automated parent notifications.</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $studentName = trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''));
                            if ($studentName === '') $studentName = 'Student #' . $log['student_id'];
                            $parentName = trim($log['parent_name'] ?? 'Parent/Guardian');
                            $createdTs = strtotime($log['created_at']);
                            $dateFmt = date('M j, Y', $createdTs);
                            $timeFmt = date('g:i A', $createdTs);

                            // Badges styling
                            $channelBadge = match ($log['channel']) {
                                'sms'      => '<span class="badge bg-info-subtle text-info border border-info-subtle fw-semibold px-2 py-1"><i class="ti ti-message-dots me-1"></i>SMS</span>',
                                'email'    => '<span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-semibold px-2 py-1"><i class="ti ti-mail me-1"></i>Email</span>',
                                'whatsapp' => '<span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-1"><i class="ti ti-brand-whatsapp me-1"></i>WhatsApp</span>',
                                default    => '<span class="badge bg-secondary">' . e($log['channel']) . '</span>',
                            };

                            $eventBadge = match ($log['event']) {
                                'check_in'  => '<span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><i class="ti ti-login me-1"></i>Check In</span>',
                                'check_out' => '<span class="badge bg-secondary-subtle text-secondary fw-semibold px-2 py-1"><i class="ti ti-logout me-1"></i>Check Out</span>',
                                default     => '<span class="badge bg-light text-dark">' . e($log['event']) . '</span>',
                            };

                            $statusBadge = match ($log['status']) {
                                'sent'    => '<span class="badge bg-success text-white fw-semibold px-2 py-1"><i class="ti ti-check me-1"></i>Sent</span>',
                                'failed'  => '<span class="badge bg-danger text-white fw-semibold px-2 py-1" title="' . e($log['error_message'] ?? '') . '"><i class="ti ti-alert-circle me-1"></i>Failed</span>',
                                default   => '<span class="badge bg-warning text-dark fw-semibold px-2 py-1"><i class="ti ti-clock me-1"></i>Pending</span>',
                            };
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($log['passport_photo'])): ?>
                                        <img src="<?= url('uploads/' . $log['passport_photo']) ?>" class="rounded-circle border" width="34" height="34" style="object-fit:cover;" alt="Photo">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold border" style="width:34px;height:34px;font-size:11px;">
                                            <?= strtoupper(substr($studentName, 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= url('admin/applications/' . (int) $log['student_id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                            <?= e($studentName) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <?= e($log['class_name'] ?? 'General') ?> &bull; <?= e($log['admission_number'] ?: $log['application_number'] ?: '#' . $log['student_id']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium text-dark"><?= e($parentName) ?></span>
                            </td>
                            <td>
                                <?= $eventBadge ?>
                            </td>
                            <td>
                                <?= $channelBadge ?>
                            </td>
                            <td>
                                <span class="font-monospace small"><?= e($log['recipient'] ?: '—') ?></span>
                            </td>
                            <td>
                                <?= $statusBadge ?>
                                <?php if ($log['status'] === 'failed' && !empty($log['error_message'])): ?>
                                    <div class="text-danger small mt-1 text-truncate" style="max-width: 180px;" title="<?= e($log['error_message']) ?>">
                                        <?= e($log['error_message']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-medium text-dark"><?= $timeFmt ?></div>
                                <div class="text-muted small" style="font-size: 11px;"><?= $dateFmt ?></div>
                            </td>
                            <td class="text-end pe-3">
                                <?php if ($log['status'] === 'failed'): ?>
                                    <form method="POST" action="<?= url('admin/attendance-notification-logs/retry') ?>" style="display:inline;" onsubmit="return confirm('Retry sending this <?= strtoupper($log['channel']) ?> notification to <?= e($log['recipient']) ?>?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="log_id" value="<?= (int) $log['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 py-1 px-2" title="Retry Dispatch">
                                            <i class="ti ti-rotate"></i> Retry
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-muted" data-bs-toggle="modal" data-bs-target="#logModal<?= (int) $log['id'] ?>" title="View Payload">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- Modal for viewing payload / details -->
                                <div class="modal fade" id="logModal<?= (int) $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom">
                                                <h6 class="modal-title fw-bold">Notification Details #<?= (int) $log['id'] ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body small">
                                                <div class="mb-2"><strong>Recipient:</strong> <span class="font-monospace"><?= e($log['recipient']) ?></span> (<?= strtoupper($log['channel']) ?>)</div>
                                                <div class="mb-2"><strong>Event:</strong> <?= e($log['event']) ?> | <strong>Status:</strong> <?= e($log['status']) ?></div>
                                                <div class="mb-2"><strong>Sent At:</strong> <?= e($log['sent_at'] ?: 'Not recorded') ?></div>
                                                <div class="mb-3">
                                                    <strong>Message Body:</strong>
                                                    <div class="p-2 bg-light rounded border mt-1 font-monospace" style="white-space:pre-wrap;"><?= e($log['message'] ?: 'No message text stored.') ?></div>
                                                </div>
                                                <?php if (!empty($log['provider_response'])): ?>
                                                    <div>
                                                        <strong>Provider Response:</strong>
                                                        <div class="p-2 bg-light rounded border mt-1 font-monospace text-muted" style="max-height:120px;overflow-y:auto;"><?= e($log['provider_response']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($log['error_message'])): ?>
                                                    <div class="mt-2 text-danger">
                                                        <strong>Error:</strong> <?= e($log['error_message']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer border-top p-2">
                                                <?php if ($log['status'] === 'failed'): ?>
                                                    <form method="POST" action="<?= url('admin/attendance-notification-logs/retry') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="log_id" value="<?= (int) $log['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="ti ti-rotate"></i> Retry Notification</button>
                                                    </form>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-3">
            <div class="small text-muted">
                Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total <?= number_format($totalLogs) ?> alerts)
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('admin/attendance-notification-logs?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/attendance-notification-logs?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('admin/attendance-notification-logs?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
