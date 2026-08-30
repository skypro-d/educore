<?php
// views/admin/gates.php
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.5rem; letter-spacing: -0.02em;">
            <i class="ti ti-barrier-block text-primary me-2"></i>School Gates &amp; Checkpoints
        </h3>
        <p class="text-muted mb-0 small">Manage entry and exit gate checkpoints across your school campus for attendance and dismissal verification.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-primary btn-sm rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#gateModal" onclick="resetGateForm()">
            <i class="ti ti-plus me-1"></i> Add New Gate
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Gate Name</th>
                    <th>Code / Tag</th>
                    <th>Location / Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gates)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-barrier-block-off d-block mb-2 fs-2 text-secondary opacity-50"></i>
                            No school gates configured yet. Click "Add New Gate" to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gates as $g): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <i class="ti ti-door-exit text-primary me-2"></i><?= e($g['gate_name']) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= e($g['gate_code'] ?: '—') ?></span>
                            </td>
                            <td class="text-muted"><?= e($g['location'] ?: 'Campus Perimeter') ?></td>
                            <td>
                                <?php if ($g['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= date('M j, Y', strtotime($g['created_at'])) ?></td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-light btn-sm py-1 px-2 text-primary me-1" onclick='editGate(<?= json_encode($g) ?>)' title="Edit Gate">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form method="POST" action="<?= url('admin/gates/' . $g['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this gate?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-light btn-sm py-1 px-2 text-danger" title="Delete Gate">
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

<!-- Add / Edit Gate Modal -->
<div class="modal fade" id="gateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= url('admin/gates') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="gateId" value="0">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="gateModalTitle">
                        <i class="ti ti-barrier-block text-primary me-1"></i> Add School Gate
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Gate Name <span class="text-danger">*</span></label>
                        <input type="text" name="gate_name" id="gateName" class="form-control" placeholder="e.g. Main Front Gate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Gate Code / Reference</label>
                        <input type="text" name="gate_code" id="gateCode" class="form-control" placeholder="e.g. GATE-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Location Description</label>
                        <input type="text" name="location" id="gateLocation" class="form-control" placeholder="e.g. North Wing Entrance">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Status</label>
                        <select name="status" id="gateStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Gate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetGateForm() {
    document.getElementById('gateModalTitle').innerHTML = '<i class="ti ti-barrier-block text-primary me-1"></i> Add School Gate';
    document.getElementById('gateId').value = '0';
    document.getElementById('gateName').value = '';
    document.getElementById('gateCode').value = '';
    document.getElementById('gateLocation').value = '';
    document.getElementById('gateStatus').value = 'active';
}

function editGate(g) {
    document.getElementById('gateModalTitle').innerHTML = '<i class="ti ti-edit text-primary me-1"></i> Edit School Gate';
    document.getElementById('gateId').value = g.id;
    document.getElementById('gateName').value = g.gate_name;
    document.getElementById('gateCode').value = g.gate_code || '';
    document.getElementById('gateLocation').value = g.location || '';
    document.getElementById('gateStatus').value = g.status;
    const modal = new bootstrap.Modal(document.getElementById('gateModal'));
    modal.show();
}
</script>
