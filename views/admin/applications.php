<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Applications</h1>
    <a class="btn btn-outline-primary" href="<?= url('admin/export') ?>">Export Excel/CSV</a>
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
            <thead><tr><th>Application No</th><th>Name</th><th>Class</th><th>Parent</th><th>Status</th><th>Enrollment</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($applications as $row): ?>
                <tr>
                    <td><?= e($row['application_number']) ?></td>
                    <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= e($row['class_name']) ?></td>
                    <td><?= e($row['parent_phone']) ?></td>
                    <td><span class="status <?= e(strtolower(str_replace(' ', '-', $row['status']))) ?>"><?= e($row['status']) ?></span></td>
                    <td><?= e($row['enrollment_status'] ?? 'Pending') ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/applications/' . $row['id']) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
