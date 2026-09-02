<?php
// views/teacher/assignment_view.php — Assignment Details & Submissions Grading
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('teacher/assignments') ?>" class="text-decoration-none text-muted" style="font-size:12px; font-weight:600;">
            ← Back to Assignments
        </a>
        <h1 class="mt-1" style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">
            <?= e($assignment['title']) ?>
        </h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">
            Class: <strong><?= e($assignment['class_name']) ?></strong> &nbsp;|&nbsp; Subject: <strong><?= e($assignment['subject_name']) ?></strong>
        </p>
    </div>
    <div class="text-end">
        <span class="badge bg-light text-dark border py-2 px-3 font-monospace" style="font-size:12px;">
            Due Date: <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
        </span>
    </div>
</div>

<!-- Assignment Instructions Card -->
<div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:14px; background:#fff;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h4 class="h6 fw-bold mb-0" style="color:#0f172a;">Instructions & Materials</h4>
        <span class="badge bg-primary-subtle text-primary border" style="font-size:11px;">
            Max Score: <?= e($assignment['max_score']) ?> pts
        </span>
    </div>
    <p class="text-muted" style="font-size:13px; line-height:1.5;">
        <?= nl2br(e($assignment['instructions'] ?: 'No detailed instructions provided.')) ?>
    </p>
    <?php if (!empty($assignment['attachment'])): ?>
        <div class="pt-2">
            <a href="<?= url('uploads/' . e($assignment['attachment'])) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:6px; font-size:12px;">
                <i class="ti ti-download me-1"></i> Download Attached Material
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Student Submissions Card -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
        <h4 class="h6 fw-bold mb-0" style="color:#0f172a;">Student Submissions (<?= count($submissions) ?>)</h4>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b;">
                <tr>
                    <th class="ps-4 py-3">Student</th>
                    <th class="py-3">Submitted At</th>
                    <th class="py-3">Submission</th>
                    <th class="py-3 text-center">Score</th>
                    <th class="py-3">Feedback</th>
                    <th class="pe-4 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-file-certificate fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No submissions recorded for this assignment yet.
                        </td>
                    </tr>
                <?php else: foreach ($submissions as $sub): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold" style="font-size:13.5px; color:#0f172a;">
                                <?= e($sub['first_name'] . ' ' . $sub['last_name']) ?>
                            </div>
                            <small class="text-muted font-monospace" style="font-size:11px;"><?= e($sub['admission_number']) ?></small>
                        </td>
                        <td class="py-3" style="font-size:12px; color:#64748b;">
                            <?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?>
                        </td>
                        <td class="py-3" style="font-size:12.5px;">
                            <?php if (!empty($sub['submission_text'])): ?>
                                <span class="text-truncate d-inline-block" style="max-width:200px;">
                                    <?= e($sub['submission_text']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($sub['attachment'])): ?>
                                <div>
                                    <a href="<?= url('uploads/' . e($sub['attachment'])) ?>" target="_blank" style="font-size:11.5px;">
                                        <i class="ti ti-paperclip"></i> View Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if ($sub['score'] !== null): ?>
                                <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle py-1 px-2" style="background:#e6fffa; color:#0d9488; font-size:12px; font-weight:700;">
                                    <?= e($sub['score']) ?> / <?= e($assignment['max_score']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark py-1 px-2 font-weight-bold" style="font-size:11px;">Ungraded</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-muted" style="font-size:12px;">
                            <?= e($sub['feedback'] ?: '—') ?>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="border-radius:6px; font-size:12px; font-weight:600;" onclick='openGradeModal(<?= json_encode($sub, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                <i class="ti ti-check me-1"></i> Grade
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Grading Modal -->
<div class="modal fade" id="gradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#0f172a;">Grade Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('teacher/assignments/' . (int)$assignment['id'] . '/grade') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="submission_id" id="gradeSubmissionId" value="0">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Student</label>
                        <input type="text" id="gradeStudentName" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Score (Max: <?= e($assignment['max_score']) ?>)</label>
                        <input type="number" step="0.5" min="0" max="<?= e($assignment['max_score']) ?>" name="score" id="gradeScoreInput" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Teacher Feedback</label>
                        <textarea name="feedback" id="gradeFeedbackInput" class="form-control" rows="3" placeholder="Notes for the student..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openGradeModal(sub) {
    document.getElementById('gradeSubmissionId').value = sub.id;
    document.getElementById('gradeStudentName').value = (sub.first_name || '') + ' ' + (sub.last_name || '');
    document.getElementById('gradeScoreInput').value = sub.score !== null ? sub.score : '';
    document.getElementById('gradeFeedbackInput').value = sub.feedback || '';
    new bootstrap.Modal(document.getElementById('gradeModal')).show();
}
</script>
