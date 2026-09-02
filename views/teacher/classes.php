<?php
// views/teacher/classes.php — My Classes Overview
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">My Classes</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Classes and subjects assigned to you for the <?= e($academicYear) ?> academic session</p>
    </div>
    <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle py-2 px-3 rounded-pill" style="background:#e6fffa; color:#0d9488; font-weight:700; font-size:12px;">
        Academic Session: <?= e($academicYear) ?>
    </span>
</div>

<?php if (empty($assignments)): ?>
    <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius:14px; background:#fff;">
        <i class="ti ti-school-off fs-1 d-block mb-3 text-secondary opacity-50"></i>
        <h4 class="fw-bold" style="font-size:1.1rem; color:#1e293b;">No Class Assignments Found</h4>
        <p class="mb-0 text-muted" style="font-size:13px;">You have not been assigned to any classroom or subject for this session. Please contact school administration.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($assignments as $idx => $assign): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 p-4" style="border-radius:14px; background:#fff; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size:11px;">
                                Class ID: <?= (int)$assign['class_id'] ?>
                            </div>
                            <?php if (!empty($assign['is_form_teacher'])): ?>
                                <span class="badge bg-warning text-dark py-1 px-2 fw-bold" style="font-size:11px;">
                                    <i class="ti ti-star me-1"></i> Class Teacher
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary border py-1 px-2 fw-bold" style="font-size:11px;">
                                    Subject Teacher
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="fw-bold mb-1" style="font-size:1.25rem; color:#0f172a;">
                            <?= e($assign['class_name']) ?>
                        </h3>
                        <div style="font-size:13px; color:#64748b; margin-bottom:14px;">
                            <?= $assign['subject_name'] ? '<i class="ti ti-book me-1"></i>' . e($assign['subject_name']) : '<em>No Subject Attached (Class Teacher)</em>' ?>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                            <span style="font-size:12px; color:#64748b;">Enrolled Students</span>
                            <span class="fw-bold" style="font-size:14px; color:#0f172a;">
                                <?= (int)($assign['student_count'] ?? 0) ?> Students
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <a href="<?= url('teacher/classes/' . (int)$assign['class_id']) ?>" class="btn btn-sm btn-primary flex-grow-1" style="font-weight:600; font-size:12px; border-radius:8px;">
                            <i class="ti ti-eye me-1"></i> View Class
                        </a>
                        <a href="<?= url('teacher/attendance?class_id=' . (int)$assign['class_id']) ?>" class="btn btn-sm btn-outline-primary" style="font-weight:600; font-size:12px; border-radius:8px;">
                            <i class="ti ti-calendar-check me-1"></i> Attendance
                        </a>
                        <?php if (!empty($assign['subject_id'])): ?>
                            <a href="<?= url('teacher/results?assignment_idx=' . $idx) ?>" class="btn btn-sm btn-outline-teal" style="font-weight:600; font-size:12px; border-radius:8px; border-color:#0f766e; color:#0f766e;">
                                <i class="ti ti-report-analytics me-1"></i> Results
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
