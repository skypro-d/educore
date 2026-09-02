<div class="sa-top-bar mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color:#0f172a;">Configurable Grading Scale</h1>
        <p class="text-muted mb-0" style="font-size:13.5px;">Configure the minimum and maximum score thresholds, letter grades, remarks, and grade points.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="sa-btn" href="<?= url('admin/class-subjects') ?>" style="border-radius:8px;">
            <i class="ti ti-checklist"></i> Class Subjects
        </a>
        <a class="sa-btn" href="<?= url('admin/assessment-components') ?>" style="border-radius:8px;">
            <i class="ti ti-adjustments"></i> Assessment Components
        </a>
    </div>
</div>

<div class="sa-card p-4 shadow-sm border-0" style="border-radius:14px; background:#fff; max-width:960px;">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
            <h5 class="fw-bold mb-0" style="color:#0f172a;">Grading Rules Matrix</h5>
            <small class="text-muted">EduCore automatically calculates student grades and remarks using these thresholds.</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addGradeRow()">
            <i class="ti ti-plus me-1"></i> Add Grade Level
        </button>
    </div>

    <form method="POST" action="<?= url('admin/grading-rules') ?>">
        <?= csrf_field() ?>
        <div class="table-responsive">
            <table class="table align-middle" id="gradingRulesTable">
                <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#475569;">
                    <tr>
                        <th style="width:120px;">Min Score</th>
                        <th style="width:120px;">Max Score</th>
                        <th style="width:100px;">Grade</th>
                        <th style="min-width:180px;">Remark</th>
                        <th style="width:110px;">Grade Point</th>
                        <th style="width:60px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $i => $r): ?>
                        <tr>
                            <td>
                                <input type="number" step="0.01" min="0" max="100" name="min_score[]" 
                                       class="form-control form-control-sm text-center font-monospace fw-bold" 
                                       value="<?= e($r['min_score']) ?>" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" max="100" name="max_score[]" 
                                       class="form-control form-control-sm text-center font-monospace fw-bold" 
                                       value="<?= e($r['max_score']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="grade[]" 
                                       class="form-control form-control-sm text-center fw-bold" 
                                       value="<?= e($r['grade']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="remark[]" 
                                       class="form-control form-control-sm" 
                                       value="<?= e($r['remark']) ?>" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" max="5" name="grade_point[]" 
                                       class="form-control form-control-sm text-center font-monospace" 
                                       value="<?= e($r['grade_point']) ?>">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('tr').remove()">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="text-muted" style="font-size:12px;">
                <i class="ti ti-info-circle me-1"></i> Thresholds are evaluated in descending order of minimum score.
            </div>
            <button type="submit" class="sa-btn sa-btn-primary px-4 py-2" style="border-radius:8px; font-weight:700;">
                <i class="ti ti-device-floppy me-1"></i> Save Grading Scale
            </button>
        </div>
    </form>
</div>

<script>
function addGradeRow() {
    const tbody = document.querySelector('#gradingRulesTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="number" step="0.01" min="0" max="100" name="min_score[]" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" required></td>
        <td><input type="number" step="0.01" min="0" max="100" name="max_score[]" class="form-control form-control-sm text-center font-monospace fw-bold" value="100" required></td>
        <td><input type="text" name="grade[]" class="form-control form-control-sm text-center fw-bold" value="A" required></td>
        <td><input type="text" name="remark[]" class="form-control form-control-sm" value="Pass" required></td>
        <td><input type="number" step="0.01" min="0" max="5" name="grade_point[]" class="form-control form-control-sm text-center font-monospace" value="0"></td>
        <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('tr').remove()"><i class="ti ti-trash fs-5"></i></button></td>
    `;
    tbody.appendChild(tr);
}
</script>
