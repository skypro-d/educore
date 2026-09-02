<?php
// views/teacher/assignments.php — Assignments Management List
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Classroom Assignments</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Create, distribute, and grade assignments for your assigned classes</p>
    </div>
    <?php if (staff_can('assignments.create')): ?>
        <a href="<?= url('teacher/assignments/create') ?>" class="btn btn-primary" style="font-weight:600; font-size:13px; border-radius:8px;">
            <i class="ti ti-plus me-1"></i> Create Assignment
        </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.5px;">
                <tr>
                    <th class="ps-4 py-3">Assignment Title</th>
                    <th class="py-3">Class & Subject</th>
                    <th class="py-3">Due Date</th>
                    <th class="py-3 text-center">Submissions</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="pe-4 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-notebook fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No assignments posted yet. Click "Create Assignment" to assign work to your students.
                        </td>
                    </tr>
                <?php else: foreach ($assignments as $a): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <a href="<?= url('teacher/assignments/' . (int)$a['id']) ?>" class="fw-bold text-decoration-none" style="font-size:13.5px; color:#0f172a;">
                                <?= e($a['title']) ?>
                            </a>
                            <div class="text-muted" style="font-size:11.5px;">Max Score: <?= e($a['max_score']) ?> pts</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border"><?= e($a['class_name']) ?></span>
                            <div style="font-size:12px; color:#64748b; margin-top:2px;"><?= e($a['subject_name']) ?></div>
                        </td>
                        <td class="py-3" style="font-size:12.5px;">
                            <?php $isOverdue = strtotime($a['due_date']) < time(); ?>
                            <span class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>">
                                <?= date('M d, Y', strtotime($a['due_date'])) ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle py-1 px-2" style="background:#e6fffa; color:#0d9488; font-size:11.5px;">
                                <?= (int)$a['graded_count'] ?> / <?= (int)$a['submission_count'] ?> Graded
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size:11px;">
                                <?= ucfirst(e($a['status'])) ?>
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="<?= url('teacher/assignments/' . (int)$a['id']) ?>" class="btn btn-sm btn-light border py-1 px-2" style="font-size:12px; font-weight:600; border-radius:6px;">
                                <i class="ti ti-eye me-1"></i> View Submissions
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
