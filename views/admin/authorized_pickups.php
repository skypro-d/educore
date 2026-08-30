<?php
// views/admin/authorized_pickups.php
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.5rem; letter-spacing: -0.02em;">
            <i class="ti ti-user-shield text-primary me-2"></i>Authorized Pickups &amp; Guardians
        </h3>
        <p class="text-muted mb-0 small">Register approved guardians, drivers, and family members authorized to pick up students from the school gate.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-primary btn-sm rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#pickupModal" onclick="resetPickupForm()">
            <i class="ti ti-plus me-1"></i> Add Authorized Person
        </button>
    </div>
</div>

<!-- Student Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('admin/authorized-pickups') ?>" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-muted mb-1">Filter by Student</label>
                <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Students --</option>
                    <?php foreach ($students as $std): ?>
                        <option value="<?= $std['id'] ?>" <?= $studentId == $std['id'] ? 'selected' : '' ?>>
                            <?= e($std['first_name'] . ' ' . $std['last_name']) ?> (<?= e($std['admission_number'] ?: $std['application_number']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-3 d-flex align-items-end">
                <?php if ($studentId > 0): ?>
                    <a href="<?= url('admin/authorized-pickups') ?>" class="btn btn-outline-secondary btn-sm">Clear Filter</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Pickups Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Student</th>
                    <th>Authorized Collector</th>
                    <th>Relationship</th>
                    <th>Contact Phone</th>
                    <th>ID / Card No</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pickups)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ti ti-user-x d-block mb-2 fs-2 text-secondary opacity-50"></i>
                            No authorized pickup persons found. Click "Add Authorized Person" to register one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pickups as $p): ?>
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">
                                <a href="<?= url('admin/applications/' . $p['student_id']) ?>" class="text-decoration-none text-dark">
                                    <?= e($p['first_name'] . ' ' . $p['last_name']) ?>
                                </a>
                                <div class="text-muted" style="font-size: 11px;">
                                    <?= e($p['class_name'] ?: 'General') ?> &bull; <?= e($p['admission_number'] ?: $p['application_number']) ?>
                                </div>
                            </td>
                            <td class="fw-bold text-dark">
                                <i class="ti ti-user-check text-primary me-1"></i><?= e($p['name']) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($p['relationship'] ?: 'Guardian') ?></span>
                            </td>
                            <td><?= mask_phone($p['phone']) ?></td>
                            <td class="font-monospace text-muted"><?= e($p['id_card_number'] ?: '—') ?></td>
                            <td>
                                <?php if ($p['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-light btn-sm py-1 px-2 text-primary me-1" onclick='editPickup(<?= json_encode($p) ?>)' title="Edit Pickup Person">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form method="POST" action="<?= url('admin/authorized-pickups/' . $p['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this authorized pickup person?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-light btn-sm py-1 px-2 text-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Pickup Modal -->
<div class="modal fade" id="pickupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= url('admin/authorized-pickups') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="pickupId" value="0">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="pickupModalTitle">
                        <i class="ti ti-user-shield text-primary me-1"></i> Add Authorized Pickup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="pickupStudentId" class="form-select" required>
                            <option value="">-- Choose Student --</option>
                            <?php foreach ($students as $std): ?>
                                <option value="<?= $std['id'] ?>" <?= $studentId == $std['id'] ? 'selected' : '' ?>>
                                    <?= e($std['first_name'] . ' ' . $std['last_name']) ?> (<?= e($std['admission_number'] ?: $std['application_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Authorized Person Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="pickupName" class="form-control" placeholder="e.g. Uncle John Doe / Driver Musa" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Relationship</label>
                            <select name="relationship" id="pickupRelationship" class="form-select">
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Driver">Official Driver</option>
                                <option value="Sibling">Elder Sibling</option>
                                <option value="Grandparent">Grandparent</option>
                                <option value="Other">Other Authorized Relative</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="pickupPhone" class="form-control" placeholder="08012345678" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ID / NIN / Driver's License Number</label>
                        <input type="text" name="id_card_number" id="pickupIdCard" class="form-control" placeholder="Optional identification number">
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="pickupActive" value="1" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="pickupActive">Authorized for active pickup</label>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Pickup Guardian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetPickupForm() {
    document.getElementById('pickupModalTitle').innerHTML = '<i class="ti ti-user-shield text-primary me-1"></i> Add Authorized Pickup';
    document.getElementById('pickupId').value = '0';
    document.getElementById('pickupName').value = '';
    document.getElementById('pickupRelationship').value = 'Guardian';
    document.getElementById('pickupPhone').value = '';
    document.getElementById('pickupIdCard').value = '';
    document.getElementById('pickupActive').checked = true;
}

function editPickup(p) {
    document.getElementById('pickupModalTitle').innerHTML = '<i class="ti ti-edit text-primary me-1"></i> Edit Authorized Pickup';
    document.getElementById('pickupId').value = p.id;
    document.getElementById('pickupStudentId').value = p.student_id;
    document.getElementById('pickupName').value = p.name;
    document.getElementById('pickupRelationship').value = p.relationship || 'Guardian';
    document.getElementById('pickupPhone').value = p.phone || '';
    document.getElementById('pickupIdCard').value = p.id_card_number || '';
    document.getElementById('pickupActive').checked = (p.is_active == 1);
    const modal = new bootstrap.Modal(document.getElementById('pickupModal'));
    modal.show();
}
</script>
