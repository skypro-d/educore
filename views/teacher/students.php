<?php
// views/teacher/students.php — Central "My Students" Directory
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">My Students</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Enrolled students belonging to your authorized classes</p>
    </div>
    <span class="badge bg-light text-dark border py-2 px-3 fw-bold" style="border-radius:20px; font-size:12px;">
        Total: <?= count($students) ?> Students
    </span>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm p-3 mb-4" style="border-radius:14px; background:#fff;">
    <form method="GET" action="<?= url('teacher/students') ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search student name or ID..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="class_id" class="form-select form-select-sm">
                <option value="">All My Classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $classFilter === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="gender" class="form-select form-select-sm">
                <option value="">All Genders</option>
                <option value="Male" <?= $genderFilter === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $genderFilter === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Suspended" <?= $statusFilter === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                <option value="Withdrawn" <?= $statusFilter === 'Withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="ti ti-filter"></i></button>
            <a href="<?= url('teacher/students') ?>" class="btn btn-sm btn-light border"><i class="ti ti-rotate-clockwise"></i></a>
        </div>
    </form>
</div>

<!-- Students List Card -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.5px;">
                <tr>
                    <th class="ps-4 py-3">Student Name</th>
                    <th class="py-3">Admission No</th>
                    <th class="py-3">Class</th>
                    <th class="py-3">Gender</th>
                    <th class="py-3">Academic Status</th>
                    <th class="pe-4 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-users-minus fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No students match your selected filters.
                        </td>
                    </tr>
                <?php else: foreach ($students as $st): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($st['passport_photo'])): ?>
                                    <img src="<?= url('uploads/' . e($st['passport_photo'])) ?>" alt="Photo" style="width:38px;height:38px;border-radius:10px;object-fit:cover;border:1px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width:38px;height:38px;border-radius:10px;background:#f1f5f9;color:#475569;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:13px;">
                                        <?= strtoupper(substr($st['first_name'], 0, 1) . substr($st['last_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <a href="<?= url('teacher/students/' . (int)$st['id']) ?>" class="fw-bold text-decoration-none" style="color:#0f172a; font-size:13.5px;">
                                        <?= e($st['first_name'] . ' ' . $st['last_name']) ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 font-monospace" style="font-size:12px; font-weight:600; color:#334155;">
                            <?= e($st['admission_number'] ?: 'Pending') ?>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11.5px;">
                                <?= e($st['class_name']) ?>
                            </span>
                        </td>
                        <td class="py-3" style="font-size:12.5px; color:#475569;">
                            <?= e($st['gender']) ?>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size:11px;">
                                <?= e($st['student_status'] ?? 'Active') ?>
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="<?= url('teacher/students/' . (int)$st['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:12px; font-weight:600; border-radius:6px;">
                                <i class="ti ti-user-circle me-1"></i> Profile
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
