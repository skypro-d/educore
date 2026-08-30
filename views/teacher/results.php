<?php
// views/teacher/results.php
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body py-4">
                <form method="GET" action="<?= url('teacher/results') ?>" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="assignment_idx" class="form-label font-semibold" style="font-weight:600; color:#475569;">Assigned Subject &amp; Class</label>
                        <select name="assignment_idx" id="assignment_idx" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($assignments as $idx => $assign): ?>
                                <option value="<?= $idx ?>" <?= $idx == $selectedIdx ? 'selected' : '' ?>>
                                    <?= e($assign['class_name']) ?> — <?= e($assign['subject_name']) ?> (<?= e($assign['subject_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="term" class="form-label font-semibold" style="font-weight:600; color:#475569;">Academic Term</label>
                        <select name="term" id="term" class="form-select" onchange="this.form.submit()">
                            <option value="First" <?= $term === 'First' ? 'selected' : '' ?>>First Term</option>
                            <option value="Second" <?= $term === 'Second' ? 'selected' : '' ?>>Second Term</option>
                            <option value="Third" <?= $term === 'Third' ? 'selected' : '' ?>>Third Term</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background:var(--teacher-primary); border-color:var(--teacher-primary); font-weight:600;">Load Students</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($selected): ?>
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1" style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">
                    Grade Entry — <?= e($selected['subject_name']) ?> (<?= e($selected['class_name']) ?>)
                </h3>
                <p class="text-muted mb-0" style="font-size:13px;">Enter continuous assessments and exam scores for the <?= e($term) ?> Term.</p>
            </div>
            <span class="badge bg-teal-subtle text-teal-emphasis py-2 px-3 border border-teal-subtle rounded-pill" style="background:#e6fffa; color:#0d9488; font-weight:600; font-size:12px;">
                Term: <?= e($term) ?> | Year: <?= e($academicYear) ?>
            </span>
        </div>

        <div class="card-body p-4">
            <?php if (empty($students)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-mood-empty" style="font-size:3rem; margin-bottom:8px; display:block;"></i>
                    No active enrolled students found in <?= e($selected['class_name']) ?>.
                </div>
            <?php else: ?>
                <form method="POST" action="<?= url('teacher/results/save') ?>" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="class_id" value="<?= (int) $selected['class_id'] ?>">
                    <input type="hidden" name="subject_id" value="<?= (int) $selected['subject_id'] ?>">
                    <input type="hidden" name="term" value="<?= e($term) ?>">
                    <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
                    <input type="hidden" name="selected_idx" value="<?= $selectedIdx ?>">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-uppercase text-center" style="font-size: 11px; font-weight: 700; color: #64748b;">
                                <tr>
                                    <th class="ps-3 text-start" style="min-width: 200px;">Student Name</th>
                                    <th style="width: 85px;">CA 1 (10)</th>
                                    <th style="width: 85px;">CA 2 (10)</th>
                                    <th style="width: 85px;">Assign (10)</th>
                                    <th style="width: 85px;">Mid-T (10)</th>
                                    <th style="width: 85px;">Exam (60)</th>
                                    <th style="width: 80px;">Total (100)</th>
                                    <th style="width: 65px;">Grade</th>
                                    <th>Teacher's Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <?php 
                                        $res = $existingResults[$student['id']] ?? null;
                                        $ca1 = $res ? $res['ca1'] : '';
                                        $ca2 = $res ? $res['ca2'] : '';
                                        $assign = $res ? $res['assignment'] : '';
                                        $mid = $res ? $res['mid_term'] : '';
                                        $exam = $res ? $res['exam'] : '';
                                        $total = $res ? $res['total'] : '';
                                        $grade = $res ? $res['grade'] : '';
                                        $tRemark = $res ? $res['teacher_remark'] : '';
                                    ?>
                                    <tr data-student-id="<?= $student['id'] ?>">
                                        <td class="ps-3 py-3 text-start">
                                            <div class="font-bold text-dark" style="font-weight: 600;"><?= e($student['last_name'] . ' ' . $student['first_name']) ?></div>
                                            <small class="text-muted"><?= e($student['admission_number']) ?></small>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="ca1[<?= $student['id'] ?>]" class="form-control form-control-sm text-center score-input" value="<?= e($ca1) ?>" oninput="calcTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="ca2[<?= $student['id'] ?>]" class="form-control form-control-sm text-center score-input" value="<?= e($ca2) ?>" oninput="calcTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="assignment[<?= $student['id'] ?>]" class="form-control form-control-sm text-center score-input" value="<?= e($assign) ?>" oninput="calcTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="mid_term[<?= $student['id'] ?>]" class="form-control form-control-sm text-center score-input" value="<?= e($mid) ?>" oninput="calcTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="60" name="exam[<?= $student['id'] ?>]" class="form-control form-control-sm text-center score-input" value="<?= e($exam) ?>" oninput="calcTotal(this)">
                                        </td>
                                        <td class="text-center font-bold text-teal-emphasis" style="font-weight: 700; color: #0d9488;">
                                            <span class="total-display"><?= $total !== '' ? (float)$total : '-' ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis grade-display" style="padding: 4px 8px; font-weight: 600; font-size: 11px;"><?= $grade !== '' ? e($grade) : '-' ?></span>
                                        </td>
                                        <td>
                                            <input type="text" name="teacher_remark[<?= $student['id'] ?>]" class="form-control form-control-sm" value="<?= e($tRemark) ?>" placeholder="e.g. Attentive and diligent">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted" style="font-size:12.5px;">
                            <i class="ti ti-info-circle"></i> Grades are calculated automatically: <strong>A</strong> (&gt;=70), <strong>B</strong> (&gt;=60), <strong>C</strong> (&gt;=50), <strong>D</strong> (&gt;=45), <strong>E</strong> (&gt;=40), <strong>F</strong> (&lt;40).
                        </div>
                        <button type="submit" class="btn btn-teal px-5" style="background:#0f766e; color:#fff; font-weight:600;">Save Class Results</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
    function calcTotal(input) {
        const row = input.closest('tr');
        const inputs = row.querySelectorAll('.score-input');
        
        let sum = 0;
        let hasValue = false;
        
        inputs.forEach(inp => {
            const val = parseFloat(inp.value);
            if (!isNaN(val)) {
                sum += val;
                hasValue = true;
            }
        });
        
        const totalDisplay = row.querySelector('.total-display');
        const gradeDisplay = row.querySelector('.grade-display');
        
        if (hasValue) {
            totalDisplay.innerText = sum.toFixed(1);
            
            // Determine grade
            let grade = 'F';
            let bgClass = 'bg-danger-subtle text-danger-emphasis';
            
            if (sum >= 70) {
                grade = 'A';
                bgClass = 'bg-success-subtle text-success-emphasis';
            } else if (sum >= 60) {
                grade = 'B';
                bgClass = 'bg-primary-subtle text-primary-emphasis';
            } else if (sum >= 50) {
                grade = 'C';
                bgClass = 'bg-info-subtle text-info-emphasis';
            } else if (sum >= 45) {
                grade = 'D';
                bgClass = 'bg-warning-subtle text-warning-emphasis';
            } else if (sum >= 40) {
                grade = 'E';
                bgClass = 'bg-secondary-subtle text-secondary-emphasis';
            }
            
            gradeDisplay.innerText = grade;
            gradeDisplay.className = 'badge ' + bgClass;
            
            // Bootstrap colors helper background style override
            if (grade === 'A') {
                gradeDisplay.style.background = '#d1fae5';
                gradeDisplay.style.color = '#065f46';
            } else if (grade === 'B') {
                gradeDisplay.style.background = '#dbeafe';
                gradeDisplay.style.color = '#1e40af';
            } else if (grade === 'C') {
                gradeDisplay.style.background = '#e0f2fe';
                gradeDisplay.style.color = '#0369a1';
            } else if (grade === 'D') {
                gradeDisplay.style.background = '#fef3c7';
                gradeDisplay.style.color = '#92400e';
            } else if (grade === 'E') {
                gradeDisplay.style.background = '#f1f5f9';
                gradeDisplay.style.color = '#475569';
            } else {
                gradeDisplay.style.background = '#fee2e2';
                gradeDisplay.style.color = '#991b1b';
            }
        } else {
            totalDisplay.innerText = '-';
            gradeDisplay.innerText = '-';
            gradeDisplay.className = 'badge bg-secondary-subtle text-secondary-emphasis';
            gradeDisplay.style.background = '';
            gradeDisplay.style.color = '';
        }
    }
</script>
