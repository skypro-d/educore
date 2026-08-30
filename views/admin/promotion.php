<div class="sa-top-bar">
    <div>
        <h1>Student Promotion & Graduation</h1>
        <p>Promote students to next class, repeat, or graduate them at the end of the term</p>
    </div>
</div>

<div class="row g-4">
    <!-- Selection & Promotion Form -->
    <div class="col-md-7">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-users"></i> Select Students to Process</div>
            
            <form method="GET" action="<?= url('admin/promotion') ?>" class="row g-2 align-items-end mb-4">
                <div class="col-md-8">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Current Class</label>
                    <select name="class_id" required class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="sa-btn sa-btn-primary w-100 justify-content-center" style="padding: 7.5px;"><i class="ti ti-filter"></i> Load Students</button>
                </div>
            </form>

            <?php if ($classId): ?>
                <?php if ($students): ?>
                    <form method="POST" action="<?= url('admin/promotion') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="from_class_id" value="<?= $classId ?>">
                        
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                            <div class="form-check mb-2" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 8px;">
                                <input class="form-check-input" type="checkbox" id="selectAllStudents">
                                <label class="form-check-label" for="selectAllStudents" style="font-weight: 700; font-size:13px; color:#1e293b;">Select All Students</label>
                            </div>
                            <?php foreach ($students as $stud): ?>
                                <div class="form-check py-1">
                                    <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="<?= $stud['id'] ?>" id="stud_<?= $stud['id'] ?>">
                                    <label class="form-check-label" for="stud_<?= $stud['id'] ?>" style="font-size:13px;">
                                        <?= e($stud['first_name'] . ' ' . $stud['last_name']) ?> <span style="color:#64748b; font-size:11px;">(<?= e($stud['application_number']) ?>)</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="sa-card" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px;">
                            <h5 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;"><i class="ti ti-adjustments"></i> Promotion Action</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px; font-weight:600;">Action</label>
                                    <select name="action" id="promoAction" class="form-select form-select-sm" required>
                                        <option value="Promoted">Promote to Next Class</option>
                                        <option value="Repeated">Repeat Class</option>
                                        <option value="Graduated">Graduate / Alumni</option>
                                        <option value="Withdrawn">Withdrawn</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="targetClassGroup">
                                    <label class="form-label" style="font-size:12px; font-weight:600;">Target Next Class</label>
                                    <select name="to_class_id" class="form-select form-select-sm" id="toClassId">
                                        <option value="">-- Choose Class --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px; font-weight:600;">Academic Year</label>
                                    <input type="text" name="academic_year" required class="form-control form-control-sm" value="<?= e(setting('academic_year', '')) ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" style="font-size:12px; font-weight:600;">Remarks / Notes</label>
                                    <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. End of 3rd term promotion process..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="sa-btn sa-btn-primary w-100 justify-content-center mt-3"><i class="ti ti-check"></i> Execute Promotion Action</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p style="color:#94a3b8; text-align:center; padding: 40px 0;">No active enrolled students found in this class.</p>
                <?php endif; ?>
            <?php else: ?>
                <p style="color:#94a3b8; text-align:center; padding: 40px 0;">Please select a class from the dropdown to view students.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- History / Logs -->
    <div class="col-md-5">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-history"></i> Recent Promotion History</div>
            <div style="max-height: 600px; overflow-y: auto;">
                <?php if ($history): foreach ($history as $h):
                    $badgeBg = ['Promoted'=>'#e0f2fe','Repeated'=>'#fee2e2','Graduated'=>'#dcfce7','Withdrawn'=>'#f1f5f9'];
                    $badgeCol = ['Promoted'=>'#0369a1','Repeated'=>'#b91c1c','Graduated'=>'#15803d','Withdrawn'=>'#475569'];
                ?>
                    <div style="border-bottom:1px solid #f1f5f9; padding: 12px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <strong style="font-size:13px; color:#1e293b;"><?= e($h['first_name'].' '.$h['last_name']) ?></strong>
                            <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:12px; background:<?= $badgeBg[$h['action']] ?? '#f3f4f6' ?>; color:<?= $badgeCol[$h['action']] ?? '#374151' ?>;"><?= e($h['action']) ?></span>
                        </div>
                        <div style="font-size:11px; color:#64748b; margin-top:4px;">
                            Class: <?= e($h['from_class'] ?: '—') ?> → <?= e($h['to_class'] ?: ($h['action'] === 'Graduated' ? 'Alumni' : '—')) ?><br>
                            Year: <?= e($h['academic_year']) ?> &nbsp;|&nbsp; Date: <?= date('M j, Y H:i', strtotime($h['promoted_at'])) ?>
                        </div>
                        <?php if ($h['remarks']): ?>
                            <div style="font-size:11px; font-style:italic; color:#94a3b8; background:#f8fafc; border-radius:4px; padding:4px 8px; margin-top:6px;">
                                "<?= e($h['remarks']) ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; else: ?>
                    <p style="color:#94a3b8; text-align:center; padding: 40px 0;">No promotion logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const promoAction = document.getElementById('promoAction');
    const targetClassGroup = document.getElementById('targetClassGroup');
    const toClassId = document.getElementById('toClassId');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (promoAction) {
        promoAction.addEventListener('change', function() {
            if (this.value === 'Graduated' || this.value === 'Withdrawn') {
                targetClassGroup.style.display = 'none';
                toClassId.required = false;
            } else {
                targetClassGroup.style.display = 'block';
                toClassId.required = true;
            }
        });
    }
});
</script>
