<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-0"><?= ($filters['status'] === 'Enrolled') ? 'Enrolled Students' : 'Applications' ?></h1>
        <p class="text-muted small mb-0"><?= ($filters['status'] === 'Enrolled') ? 'Manage all enrolled students, credentials, and digital ID cards.' : 'Review online admissions, schedule interviews, and process enrollments.' ?></p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="POST" action="<?= url('admin/students/reset-all-passwords') ?>" onsubmit="return confirm('Reset ALL student portal passwords to their respective last names (lowercase)?');" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-warning btn-sm rounded-3 shadow-sm text-dark" title="Reset all student portal passwords to student surname">
                <i class="ti ti-key me-1"></i> Reset Passwords to Surname
            </button>
        </form>
        <a class="btn btn-outline-primary btn-sm rounded-3 shadow-sm" href="<?= url('admin/export') ?>"><i class="ti ti-file-export me-1"></i> Export</a>
        <a class="btn btn-outline-secondary btn-sm rounded-3 shadow-sm" href="<?= url('admin/students/sample-csv') ?>"><i class="ti ti-download me-1"></i> CSV Template</a>
        <a class="btn btn-primary btn-sm rounded-3 shadow-sm" href="<?= url('admin/students/enrol') ?>"><i class="ti ti-user-plus me-1"></i> Direct Enrollment</a>
    </div>
</div>
<form class="panel mb-4" method="get" action="<?= url('admin/applications') ?>">
    <div class="row g-3">
        <div class="col-md-4"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, phone, number"></div>
        <div class="col-md-3"><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option value="<?= e($class['id']) ?>" <?= (string) $filters['class_id'] === (string) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="status"><option value="">All Status</option><?php foreach (['Submitted','Under Review','Awaiting Exam','Exam Completed','Interview Scheduled','Approved','Rejected','Enrolled','Terminated'] as $status): ?><option <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </div>
</form>
<div class="panel">
    <div class="table-responsive">
        <table class="table align-middle data-table">
            <thead><tr><th>Application No</th><th>Name</th><th>Type</th><th>Class</th><th>Parent</th><th>Status</th><th>Enrollment</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($applications as $row): ?>
                <tr>
                    <td><?= e($row['application_number']) ?></td>
                    <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= e(!empty($row['admission_type']) ? $row['admission_type'] : 'General') ?></span></td>
                    <td><?= e($row['class_name']) ?></td>
                    <td><?= e($row['parent_phone']) ?></td>
                    <td><span class="status <?= e(strtolower(str_replace(' ', '-', $row['status']))) ?>"><?= e($row['status']) ?></span></td>
                    <td><?= e($row['enrollment_status'] ?? 'Pending') ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <a class="btn btn-sm btn-outline-primary px-2 py-1" href="<?= url('admin/applications/' . $row['id']) ?>">View</a>
                            <a class="btn btn-sm btn-outline-secondary px-2 py-1" href="<?= url('admin/students/' . $row['id'] . '/edit') ?>" title="Edit Student Details"><i class="ti ti-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Profile / Duplicate" onclick="confirmDeleteStudent(<?= $row['id'] ?>, '<?= e(addslashes($row['first_name'] . ' ' . $row['last_name'])) ?>', '<?= e($row['application_number']) ?>')">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Confirm Delete Student -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ti ti-alert-triangle me-1"></i> Delete Student Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteStudentForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="mb-2 text-dark">Are you sure you want to permanently delete the profile for <strong id="deleteStudentName">—</strong> (<span id="deleteStudentAppNo" class="font-monospace"></span>)?</p>
                    <div class="alert alert-warning small mb-0">
                        <i class="ti ti-info-circle me-1"></i> <strong>Warning:</strong> This will remove duplicate profile records, enrollment data, portal credentials, and associated academic entries. This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold"><i class="ti ti-trash me-1"></i> Yes, Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteStudent(id, name, appNo) {
    document.getElementById('deleteStudentName').innerText = name;
    document.getElementById('deleteStudentAppNo').innerText = appNo;
    document.getElementById('deleteStudentForm').action = '<?= url("admin/applications/") ?>' + id + '/delete';
    new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
}
</script>
