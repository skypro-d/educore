<?php
// views/teacher/class_view.php — Class Detail & Student Roster
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('teacher/classes') ?>" class="text-decoration-none text-muted" style="font-size:12px; font-weight:600;">
            ← Back to My Classes
        </a>
        <h1 class="mt-1" style="font-size:1.5rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">
            <?= e($class['name']) ?> — Student Roster
        </h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">
            <?= count($students) ?> enrolled active students
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('teacher/attendance?class_id=' . (int)$class['id']) ?>" class="btn btn-primary btn-sm" style="font-weight:600; border-radius:8px;">
            <i class="ti ti-calendar-check me-1"></i> Mark Attendance
        </a>
        <a href="<?= url('teacher/results?class_id=' . (int)$class['id']) ?>" class="btn btn-outline-teal btn-sm" style="font-weight:600; border-radius:8px; border-color:#0f766e; color:#0f766e;">
            <i class="ti ti-report-analytics me-1"></i> Class Results
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
            <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                <h4 class="h6 fw-bold mb-0" style="color:#0f172a;">Student Roster (<?= count($students) ?>)</h4>
                <input type="text" id="rosterSearch" class="form-control form-control-sm" placeholder="Search name or ID..." style="width:200px; border-radius:6px;">
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rosterTable">
                    <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b;">
                        <tr>
                            <th class="ps-4 py-3">Student</th>
                            <th class="py-3">Admission No</th>
                            <th class="py-3">Gender</th>
                            <th class="py-3">Status</th>
                            <th class="pe-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No enrolled students in this class.</td></tr>
                        <?php else: foreach ($students as $st): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($st['passport_photo'])): ?>
                                            <img src="<?= url('uploads/' . e($st['passport_photo'])) ?>" alt="Photo" style="width:38px;height:38px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0;">
                                        <?php else: ?>
                                            <div style="width:38px;height:38px;border-radius:8px;background:#f1f5f9;color:#64748b;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:13px;">
                                                <?= strtoupper(substr($st['first_name'], 0, 1) . substr($st['last_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight:700; color:#0f172a; font-size:13.5px;">
                                                <?= e($st['first_name'] . ' ' . $st['last_name']) ?>
                                            </div>
                                            <small class="text-muted" style="font-size:11px;"><?= e($st['gender']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 font-monospace" style="font-size:12px; font-weight:600; color:#334155;">
                                    <?= e($st['admission_number'] ?: 'Pending') ?>
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
                                    <a href="<?= url('teacher/students/' . (int)$st['id']) ?>" class="btn btn-sm btn-light border py-1 px-2" style="font-size:12px; font-weight:600; border-radius:6px;">
                                        <i class="ti ti-user-circle me-1"></i> Profile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar: Class Assignments & Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Recent Assignments</h4>
            <?php if (empty($assignments)): ?>
                <p class="text-muted mb-0" style="font-size:12.5px;">No active assignments for this class yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($assignments as $asg): ?>
                        <div class="p-2 border rounded-3 bg-light">
                            <div class="fw-bold" style="font-size:13px; color:#0f172a;"><?= e($asg['title']) ?></div>
                            <div class="text-muted" style="font-size:11.5px;">Due: <?= date('M d, Y', strtotime($asg['due_date'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('rosterSearch')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll('#rosterTable tbody tr');
    rows.forEach(r => {
        const txt = r.innerText.toLowerCase();
        r.style.display = txt.includes(term) ? '' : 'none';
    });
});
</script>
