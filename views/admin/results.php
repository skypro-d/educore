<div class="sa-top-bar">
    <div><h1>Academic Results</h1><p>Enter and manage student scores, grades and term remarks</p></div>
    <div class="sa-top-actions">
        <a class="sa-btn" href="<?= url('admin/subjects') ?>"><i class="ti ti-books"></i> Manage Subjects</a>
    </div>
</div>

<!-- Filter -->
<div class="sa-card" style="margin-bottom:20px;">
    <div class="sa-card-title"><i class="ti ti-filter"></i> Select Class, Term & Year</div>
    <form method="GET" action="<?= url('admin/results') ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="route" value="results">
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">CLASS</label>
            <select name="class_id" class="form-select form-select-sm" style="min-width:150px;">
                <option value="">Select class…</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">TERM</label>
            <select name="term" class="form-select form-select-sm">
                <?php foreach (['First','Second','Third'] as $t): ?>
                    <option value="<?= $t ?>" <?= $t === $term ? 'selected' : '' ?>><?= $t ?> Term</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">ACADEMIC YEAR</label>
            <input type="text" name="year" value="<?= e($year) ?>" placeholder="2024/2025" class="form-control form-control-sm" style="width:110px;">
        </div>
        <button type="submit" class="sa-btn sa-btn-primary" style="height:32px;">Load</button>
    </form>
</div>

<?php if ($students && $subjects): ?>
<!-- Student List -->
<div class="sa-card">
    <div class="sa-card-title"><i class="ti ti-users"></i> Students — Click to enter scores</div>
    <div class="table-responsive">
    <table class="app-table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Student</th>
                <th style="width:120px;">Reg No.</th>
                <th style="width:100px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $i => $s): ?>
            <tr>
                <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
                <td style="font-weight:500;"><?= e($s['first_name'].' '.$s['last_name']) ?></td>
                <td style="color:#888;font-size:12px;"><?= e($s['application_number']) ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="sa-btn" style="font-size:11px;padding:4px 10px;" onclick="openScoreModal(<?= $s['id'] ?>, '<?= e(addslashes($s['first_name'].' '.$s['last_name'])) ?>')"><i class="ti ti-edit"></i> Scores</button>
                        <a class="sa-btn" style="font-size:11px;padding:4px 10px;" href="<?= url('admin/result-sheet/'.$s['id'].'?year='.urlencode($year).'&term='.urlencode($term)) ?>" target="_blank"><i class="ti ti-printer"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Score Entry Modal -->
<div class="modal fade" id="scoreModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background:#0b3d91;color:#fff;">
                <h5 class="modal-title"><i class="ti ti-pencil" style="margin-right:8px;"></i><span id="scoreModalName"></span> — Score Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/results') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="applicant_id" id="scoreApplicantId">
                <input type="hidden" name="academic_year" value="<?= e($year) ?>">
                <input type="hidden" name="term" value="<?= e($term) ?>">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <div class="modal-body" style="padding:0;">
                    <div style="overflow-x:auto;">
                    <table class="app-table">
                        <thead>
                            <tr style="background:#f9fafb;">
                                <th>Subject</th>
                                <th style="text-align:center;">CA1 (/<?= setting('ca1_max',10) ?>)</th>
                                <th style="text-align:center;">CA2 (/<?= setting('ca2_max',10) ?>)</th>
                                <th style="text-align:center;">Assgn (/<?= setting('assignment_max',10) ?>)</th>
                                <th style="text-align:center;">Mid-Term (/<?= setting('mid_term_max',10) ?>)</th>
                                <th style="text-align:center;">Exam (/<?= setting('exam_max',60) ?>)</th>
                                <th style="text-align:center;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subj): ?>
                            <tr>
                                <td style="font-size:13px;font-weight:500;"><?= e($subj['name']) ?></td>
                                <?php foreach (['ca1','ca2','assignment','mid_term','exam'] as $field): ?>
                                <td style="text-align:center;">
                                    <input type="number" name="scores[<?= $subj['id'] ?>][<?= $field ?>]" min="0" max="<?= setting($field.'_max', $field==='exam'?'60':'10') ?>" step="0.5" class="form-control form-control-sm score-input" data-subj="<?= $subj['id'] ?>" style="width:70px;margin:0 auto;text-align:center;">
                                </td>
                                <?php endforeach; ?>
                                <td style="text-align:center;font-weight:700;color:#0b3d91;" id="total-<?= $subj['id'] ?>">—</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save Scores</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScoreModal(id, name) {
    document.getElementById('scoreApplicantId').value = id;
    document.getElementById('scoreModalName').textContent = name;
    new bootstrap.Modal(document.getElementById('scoreModal')).show();
}
document.querySelectorAll('.score-input').forEach(input => {
    input.addEventListener('input', () => {
        const subj = input.dataset.subj;
        let total = 0;
        document.querySelectorAll(`.score-input[data-subj="${subj}"]`).forEach(i => {
            total += parseFloat(i.value || 0);
        });
        const cell = document.getElementById('total-' + subj);
        if (cell) { cell.textContent = total.toFixed(1); cell.style.color = total >= 50 ? '#16a34a' : '#dc2626'; }
    });
});
</script>
<?php elseif ($classId): ?>
    <div class="sa-card" style="text-align:center;padding:48px;"><p style="color:#9ca3af;">No enrolled students in this class or no subjects configured.</p></div>
<?php else: ?>
    <div class="sa-card" style="text-align:center;padding:48px;">
        <i class="ti ti-report-analytics" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
        <p style="color:#9ca3af;">Select a class, term and academic year to enter results.</p>
    </div>
<?php endif; ?>
