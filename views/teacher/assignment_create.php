<?php
// views/teacher/assignment_create.php — Create Assignment
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('teacher/assignments') ?>" class="text-decoration-none text-muted" style="font-size:12px; font-weight:600;">
            ← Back to Assignments
        </a>
        <h1 class="mt-1" style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Create New Assignment</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Publish learning tasks and instructions to students</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 mx-auto" style="border-radius:14px; background:#fff; max-width:800px;">
    <form method="POST" action="<?= url('teacher/assignments/create') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" style="font-size:12px; font-weight:600;">Target Class <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label" style="font-size:12px; font-weight:600;">Subject <span class="text-danger">*</span></label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Choose Subject --</option>
                    <?php foreach ($subjects as $sb): ?>
                        <option value="<?= $sb['id'] ?>"><?= e($sb['name']) ?> (<?= e($sb['code'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label" style="font-size:12px; font-weight:600;">Assignment Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Chapter 3 Calculus Exercises & Word Problems">
            </div>

            <div class="col-12">
                <label class="form-label" style="font-size:12px; font-weight:600;">Instructions & Problem Statements</label>
                <textarea name="instructions" class="form-control" rows="5" placeholder="Detailed instructions for student submission..."></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label" style="font-size:12px; font-weight:600;">Submission Deadline (Due Date) <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" style="font-size:12px; font-weight:600;">Maximum Score</label>
                <input type="number" name="max_score" class="form-control" value="100" min="1" step="1">
            </div>

            <div class="col-12">
                <label class="form-label" style="font-size:12px; font-weight:600;">Upload Learning Materials / Worksheet (PDF / DOC / DOCX)</label>
                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx">
                <small class="text-muted" style="font-size:11px;">Optional. Max file size: 2MB.</small>
            </div>

            <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="<?= url('teacher/assignments') ?>" class="btn btn-light" style="border-radius:8px;">Cancel</a>
                <button type="submit" class="btn btn-primary px-4" style="font-weight:600; border-radius:8px;">
                    <i class="ti ti-device-floppy me-1"></i> Publish Assignment
                </button>
            </div>
        </div>
    </form>
</div>
