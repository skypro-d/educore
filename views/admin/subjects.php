<div class="sa-top-bar">
    <div><h1>Subject Management</h1><p>Configure subjects per class for result tracking</p></div>
</div>

<div style="display:grid;grid-template-columns:400px 1fr;gap:20px;align-items:start;">
    <!-- Add/Edit Subject -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-plus"></i> Add Subject</div>
        <form method="POST" action="<?= url('admin/subjects') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" style="font-size:13px;font-weight:600;">Subject Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="name" required class="form-control form-control-sm" placeholder="e.g. Mathematics">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:13px;font-weight:600;">Subject Code</label>
                <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. MTH">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:13px;font-weight:600;">Class (leave blank = all classes)</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="ti ti-device-floppy"></i> Save Subject</button>
        </form>
    </div>

    <!-- Subject List -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-books"></i> All Subjects (<?= count($subjects) ?>)</div>
        <div class="table-responsive">
        <table class="app-table" id="subjectTable">
            <thead>
                <tr><th>#</th><th>Subject</th><th>Code</th><th>Class</th><th style="width:80px;">Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $i => $s): ?>
                <tr>
                    <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
                    <td style="font-weight:500;"><?= e($s['name']) ?></td>
                    <td style="color:#888;"><?= e($s['code'] ?: '—') ?></td>
                    <td><?= e($s['class_name'] ?: 'All Classes') ?></td>
                    <td>
                        <form method="POST" action="<?= url('admin/subjects/'.$s['id'].'/delete') ?>" onsubmit="return confirm('Delete this subject?');" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="sa-btn" style="font-size:11px;padding:3px 8px;color:#dc2626;"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
