<?php
/**
 * views/admin/scanner_assignments.php
 * Admin — Scanner Staff Assignment & Station Management
 */
?>

<div class="sa-top-bar d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1"><i class="ti ti-scan text-primary me-2"></i> Scanner Staff Assignments</h1>
        <p class="text-muted small mb-0">Assign staff officers to gate scanning stations and manage attendance scanner access</p>
    </div>
    <div class="sa-top-actions d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('admin/scanner-logs') ?>" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="ti ti-history me-1"></i> View Scanner Logs
        </a>
        <button class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#stationsModal">
            <i class="ti ti-building me-1"></i> Scanner Stations (<?= count($stations ?? []) ?>)
        </button>
        <button class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#assignOfficerModal" onclick="prepareAssignModal()">
            <i class="ti ti-user-plus me-1"></i> Assign Scanner Officer
        </button>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Total Assignments</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= count($assignments ?? []) ?></div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-4">
                    <i class="ti ti-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Active Officers</div>
                    <div class="fs-4 fw-bold text-success mt-1">
                        <?= count(array_filter($assignments ?? [], fn($a) => $a['status'] === 'active')) ?>
                    </div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-3 fs-4">
                    <i class="ti ti-user-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Active Stations</div>
                    <div class="fs-4 fw-bold text-info mt-1"><?= count($stations ?? []) ?></div>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-3 fs-4">
                    <i class="ti ti-barrier-block"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Today's Scans</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= number_format((int)($stats['total_scans'] ?? 0)) ?></div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-3 fs-4">
                    <i class="ti ti-device-heart-monitor"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assignments Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold text-dark mb-0"><i class="ti ti-list-check text-primary me-2"></i> Current Staff Scanner Assignments</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Staff Member</th>
                    <th>Staff ID</th>
                    <th>Scanner / Station</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th>Assigned By</th>
                    <th>Assigned Date</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="ti ti-user-x" style="font-size:36px; display:block; margin-bottom:8px;"></i>
                            No scanner assignments found. Click "Assign Scanner Officer" to provision a staff member.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $as): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($as['passport_photo'])): ?>
                                        <img src="<?= url('uploads/' . $as['passport_photo']) ?>" class="rounded-circle" style="width:38px; height:38px; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:38px; height:38px; font-size:13px;">
                                            <?= strtoupper(substr($as['first_name'] ?? 'S', 0, 1) . substr($as['last_name'] ?? 'O', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($as['first_name'] . ' ' . $as['last_name']) ?></div>
                                        <div class="text-muted small"><?= e($as['department'] ?: 'Staff Member') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border"><?= e($as['public_staff_id'] ?: '—') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($as['station_name'])): ?>
                                    <div class="fw-bold text-primary"><i class="ti ti-qrcode me-1"></i> <?= e($as['station_name']) ?></div>
                                    <div class="text-muted small"><?= e($as['station_location'] ?: $as['station_code']) ?></div>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">All Active Stations</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php if ($as['can_scan_in']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" title="Can Scan In">IN</span>
                                    <?php endif; ?>
                                    <?php if ($as['can_scan_out']): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="Can Scan Out">OUT</span>
                                    <?php endif; ?>
                                    <?php if ($as['can_view_today_logs']): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" title="View Today Logs">Logs</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($as['status'] === 'active'): ?>
                                    <span class="badge bg-success text-white px-2 py-1"><i class="ti ti-check me-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white px-2 py-1"><i class="ti ti-ban me-1"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="small text-muted"><?= e($as['assigned_by_username'] ?: 'Administrator') ?></span>
                            </td>
                            <td>
                                <span class="small font-monospace text-muted"><?= date('M j, Y g:i A', strtotime($as['assigned_at'])) ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick='openEditModal(<?= json_encode($as) ?>)'>
                                                <i class="ti ti-edit text-primary me-2"></i> Edit Assignment
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= url('admin/scanner-assignments/' . $as['id'] . '/toggle') ?>" onsubmit="return confirm('Toggle status for this scanner assignment?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="<?= $as['status'] === 'active' ? 'inactive' : 'active' ?>">
                                                <button type="submit" class="dropdown-item">
                                                    <?php if ($as['status'] === 'active'): ?>
                                                        <i class="ti ti-player-pause text-warning me-2"></i> Deactivate
                                                    <?php else: ?>
                                                        <i class="ti ti-player-play text-success me-2"></i> Activate
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= url('admin/scanner-logs?staff_id=' . $as['staff_id']) ?>">
                                                <i class="ti ti-history text-info me-2"></i> View Logs
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('admin/scanner-assignments/' . $as['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to revoke and remove this scanner assignment?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ti ti-trash me-2"></i> Remove Assignment
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Assign / Edit Scanner Officer -->
<div class="modal fade" id="assignOfficerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="<?= url('admin/scanner-assignments/save') ?>" id="assignOfficerForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="assignmentId" value="0">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="assignModalTitle"><i class="ti ti-user-plus text-primary me-2"></i> Assign Scanner Officer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Staff Selector -->
                    <div class="mb-3" id="staffSelectGroup">
                        <label class="form-label fw-bold small text-muted text-uppercase">Staff Member <span class="text-danger">*</span></label>
                        <select name="staff_id" id="staffSelect" class="form-select" required>
                            <option value="">-- Select Active Staff Member --</option>
                            <?php foreach ($activeStaff ?? [] as $stf): ?>
                                <option value="<?= $stf['id'] ?>">
                                    <?= e($stf['first_name'] . ' ' . $stf['last_name']) ?> (<?= e($stf['staff_id'] ?: 'ID#' . $stf['id']) ?>) — <?= e($stf['role'] ?? 'Staff') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Only active staff accounts can be assigned.</div>
                    </div>

                    <!-- Station Selector -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Scanner Station</label>
                        <select name="station_id" id="stationSelect" class="form-select">
                            <option value="">-- All / Any Available Station --</option>
                            <?php foreach ($stations ?? [] as $stn): ?>
                                <option value="<?= $stn['id'] ?>">
                                    <?= e($stn['station_name']) ?> (<?= e($stn['station_code']) ?>) — <?= e($stn['location'] ?: 'Gate') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Permissions</label>
                        <div class="card p-3 border rounded-3 bg-light">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="can_scan_in" value="1" id="permScanIn" checked>
                                <label class="form-check-label fw-semibold" for="permScanIn">
                                    Scan Student In (Arrival Check-In)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="can_scan_out" value="1" id="permScanOut" checked>
                                <label class="form-check-label fw-semibold" for="permScanOut">
                                    Scan Student Out (Departure Check-Out)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="can_view_today_logs" value="1" id="permViewLogs" checked>
                                <label class="form-check-label fw-semibold" for="permViewLogs">
                                    View Today's Scan Logs in Scanner Portal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_view_student_details" value="1" id="permViewDetails" checked>
                                <label class="form-check-label fw-semibold" for="permViewDetails">
                                    View Student Profile &amp; Photo on Scan
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Assignment Status</label>
                        <select name="status" id="assignmentStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted text-uppercase">Notes / Instructions</label>
                        <textarea name="notes" id="assignmentNotes" class="form-control" rows="2" placeholder="Optional notes for this assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" id="saveAssignmentBtn">
                        <i class="ti ti-device-floppy me-1"></i> Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Scanner Stations Management -->
<div class="modal fade" id="stationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold"><i class="ti ti-building text-primary me-2"></i> Scanner Stations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Add Station Form -->
                <form method="POST" action="<?= url('admin/scanner-stations/save') ?>" class="mb-4 p-3 bg-light rounded-3 border">
                    <?= csrf_field() ?>
                    <div class="fw-bold mb-2 small text-uppercase text-muted"><i class="ti ti-plus"></i> Add New Station</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="station_name" class="form-control form-control-sm" placeholder="Station Name (e.g. Main Gate)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="station_code" class="form-control form-control-sm text-uppercase" placeholder="Code (e.g. GATE-01)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="location" class="form-control form-control-sm" placeholder="Location Description">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Add</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Station Name</th>
                                <th>Code</th>
                                <th>Location</th>
                                <th>Active Officers</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stations ?? [] as $stn): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($stn['station_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= e($stn['station_code']) ?></span></td>
                                    <td><?= e($stn['location'] ?: '—') ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= (int)($stn['active_officers_count'] ?? 0) ?> Assigned</span></td>
                                    <td>
                                        <span class="badge <?= $stn['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst(e($stn['status'])) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="<?= url('admin/scanner-stations/' . $stn['id'] . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Delete this scanner station?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Station">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function prepareAssignModal() {
    document.getElementById('assignOfficerForm').reset();
    document.getElementById('assignmentId').value = '0';
    document.getElementById('assignModalTitle').innerHTML = '<i class="ti ti-user-plus text-primary me-2"></i> Assign Scanner Officer';
    document.getElementById('staffSelectGroup').style.display = 'block';
    document.getElementById('staffSelect').required = true;
}

function openEditModal(data) {
    document.getElementById('assignmentId').value = data.id || '0';
    document.getElementById('assignModalTitle').innerHTML = '<i class="ti ti-edit text-primary me-2"></i> Edit Scanner Assignment';
    
    document.getElementById('staffSelect').value = data.staff_id || '';
    document.getElementById('staffSelectGroup').style.display = 'block';
    document.getElementById('stationSelect').value = data.station_id || '';
    document.getElementById('assignmentStatus').value = data.status || 'active';
    document.getElementById('assignmentNotes').value = data.notes || '';

    document.getElementById('permScanIn').checked = parseInt(data.can_scan_in) === 1;
    document.getElementById('permScanOut').checked = parseInt(data.can_scan_out) === 1;
    document.getElementById('permViewLogs').checked = parseInt(data.can_view_today_logs) === 1;
    document.getElementById('permViewDetails').checked = parseInt(data.can_view_student_details) === 1;

    const modal = new bootstrap.Modal(document.getElementById('assignOfficerModal'));
    modal.show();
}
</script>
