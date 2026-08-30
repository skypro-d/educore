<?php
// views/teacher/classList.php
?>
<div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h3 class="mb-0" style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">
            <i class="ti ti-users" style="color: var(--teacher-primary); margin-right: 8px;"></i>
            My Class Assignments
        </h3>
        <span class="badge bg-teal-subtle text-teal-emphasis py-2 px-3 border border-teal-subtle rounded-pill" style="background:#e6fffa; color:#0d9488; font-weight:600; font-size:12px;">
            Academic Year: <?= e($academicYear) ?>
        </span>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($assignments)): ?>
            <div class="text-center py-5 text-muted">
                <i class="ti ti-users" style="font-size: 3rem; display: block; margin-bottom: 12px; color: #cbd5e1;"></i>
                <p class="mb-0">You have no classes or subjects assigned to you for this academic year.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="table-light text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; font-weight: 700; color: #64748b;">
                        <tr>
                            <th class="ps-4 py-3">Class Name</th>
                            <th class="py-3">Assigned Subject</th>
                            <th class="py-3">Subject Code</th>
                            <th class="py-3">Role</th>
                            <th class="pe-4 py-3 text-end">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $idx => $assign): ?>
                            <tr>
                                <td class="ps-4 py-3 font-semibold" style="font-weight: 600; color: #1e293b;">
                                    <?= e($assign['class_name']) ?>
                                </td>
                                <td class="py-3 text-secondary">
                                    <?= $assign['subject_name'] ? e($assign['subject_name']) : '<em class="text-muted">None (Form Teacher Only)</em>' ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($assign['subject_code']): ?>
                                        <span class="badge bg-light text-dark font-monospace"><?= e($assign['subject_code']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($assign['is_form_teacher']): ?>
                                        <span class="badge bg-warning text-warning-emphasis border border-warning-subtle" style="background:#fffbeb; color:#d97706; padding: 4px 10px; font-weight: 600;">Form Teacher</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="background:#f1f5f9; color:#475569; padding: 4px 10px; font-weight: 600;">Subject Teacher</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= url("teacher/attendance?class_id={$assign['class_id']}") ?>" class="btn btn-sm btn-outline-primary" style="font-weight:600; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                            <i class="ti ti-calendar-check" style="font-size:14px;"></i> Attendance
                                        </a>
                                        <?php if ($assign['subject_id']): ?>
                                            <a href="<?= url("teacher/results?assignment_idx={$idx}") ?>" class="btn btn-sm btn-outline-teal" style="font-weight:600; font-size:12px; border-radius:6px; color:#0f766e; border-color:#0f766e; display:inline-flex; align-items:center; gap:4px;">
                                                <i class="ti ti-report-analytics" style="font-size:14px;"></i> Results
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
